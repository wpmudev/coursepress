<?php
/*
MarketPress PayPal Express Gateway Plugin
Author: Aaron Edwards ( Incsub )
*/

class MP_Gateway_Paypal_Express extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha ( a-z ) and dashes ( - ) only please!
  var $plugin_name = 'paypal-express';

  //name of your gateway, for the admin side.
  var $admin_name = '';

  //public name of your gateway, for lists and such.
  var $public_name = '';

  //url for an image for your checkout method. Displayed on checkout form if set
  var $method_img_url = '';

  //url for an submit button image for your checkout method. Displayed on checkout form if set
  var $method_button_img_url = '';

  //whether or not ssl is needed for checkout page
  var $force_ssl = false;

  //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
  var $ipn_url;

  //whether if this is the only enabled gateway it can skip the payment_form step
  var $skip_form = true;

  //only required for global capable gateways. The maximum stores that can checkout at once
  var $max_stores = 10;

  // Payment action
  var $payment_action = 'Sale';

  //paypal vars
  var $API_Username, $API_Password, $API_Signature, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $paypalURL, $version, $currencyCode, $locale;

  /****** Below are the public methods you may overwrite via a plugin ******/

  /**
   * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   */
  function on_creation() {
    global $mp;
    $settings = get_option( 'mp_settings' );

    //set names here to be able to translate
    $this->admin_name = __( 'PayPal Express Checkout', 'mp' );
    $this->public_name = __( 'PayPal', 'mp' );

    //dynamic button img, see: https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECButtonIntegration
    $this->method_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=' . get_locale();
    $this->method_button_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=' . get_locale();

    //set paypal vars
    /** @todo Set all array keys to resolve Undefined indexes notice */;
    if ( $mp->global_cart )
      $settings = get_site_option( 'mp_network_settings' );

    $this->API_Username = isset( $settings['gateways']['paypal-express']['api_user'] ) ? $settings['gateways']['paypal-express']['api_user'] : '';
    $this->API_Password = isset( $settings['gateways']['paypal-express']['api_pass'] ) ? $settings['gateways']['paypal-express']['api_pass'] : '';
    $this->API_Signature = isset( $settings['gateways']['paypal-express']['api_sig'] ) ? $settings['gateways']['paypal-express']['api_sig'] : '';
    $this->currencyCode = isset( $settings['gateways']['paypal-express']['currency'] ) ? $settings['gateways']['paypal-express']['currency'] : '';
    $this->locale = isset( $settings['gateways']['paypal-express']['locale'] ) ? $settings['gateways']['paypal-express']['locale'] : '';
    $this->returnURL = urlencode( mp_checkout_step_url( 'confirm-checkout' ) );
  	$this->cancelURL = urlencode( mp_checkout_step_url( 'checkout' ) ) . "?cancel=1";
    $this->version = "69.0"; //api version

    //set api urls
  	if ( isset( $settings['gateways']['paypal-express']['mode'] ) && $settings['gateways']['paypal-express']['mode'] == 'sandbox' )	{
  		$this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
  		$this->paypalURL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
  	} else {
  		$this->API_Endpoint = "https://api-3t.paypal.com/nvp";
  		$this->paypalURL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
    }
  }

	/**
   * Echo fields you need to add to the payment screen, like your credit card info fields.
   *  If you don't need to add form fields set $skip_form to true so this page can be skipped
   *  at checkout.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function payment_form( $global_cart, $shipping_info ) {
    if ( isset( $_GET['cancel'] ) )
      echo '<div class="mp_checkout_error">' . __( 'Your PayPal transaction has been canceled.', 'mp' ) . '</div>';
  }

  /**
   * Use this to authorize ordered transactions.
   *
   * @param array $order Contains the list of order ids
   */
  function process_payment_authorize( $orders ) {
    if ( is_array( $orders ) ) {
      foreach ( $orders as $order ) {
				$transaction_id = $order['transaction_id'];
				$amount = $order['amount'];

				$authorization = $this->DoAuthorization( $transaction_id, $amount );

				switch ( $result["PAYMENTSTATUS"] ) {
				  case 'Canceled-Reversal':
				    $status = __( 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.', 'mp' );
				    $authorized = true;
				    break;
				  case 'Expired':
				    $status = __( 'The authorization period for this payment has been reached.', 'mp' );
				    $authorized = false;
				    break;
				  case 'Voided':
				    $status = __( 'An authorization for this transaction has been voided.', 'mp' );
				    $authorized = false;
				    break;
				  case 'Failed':
				    $status = __( 'The payment has failed. This happens only if the payment was made from your customer\'s bank account.', 'mp' );
				    $authorized = false;
				    break;
				  case 'Partially-Refunded':
				    $status = __( 'The payment has been partially refunded.', 'mp' );
				    $authorized = true;
				    break;
				  case 'In-Progress':
				    $status = __( 'The transaction has not terminated, e.g. an authorization may be awaiting completion.', 'mp' );
				    $authorized = false;
				    break;
				  case 'Completed':
				    $status = __( 'The payment has been completed, and the funds have been added successfully to your account balance.', 'mp' );
				    $authorized = true;
				    break;
				  case 'Processed':
				    $status = __( 'A payment has been accepted.', 'mp' );
				    $authorized = true;
				    break;
				  case 'Reversed':
				    $status = __( 'A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer', 'mp' );
				    $reverse_reasons = array(
				      'none' => '',
				      'chargeback' => __( 'A reversal has occurred on this transaction due to a chargeback by your customer.', 'mp' ),
				      'guarantee' => __( 'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.', 'mp' ),
				      'buyer-complaint' => __( 'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.', 'mp' ),
				      'refund' => __( 'A reversal has occurred on this transaction because you have given the customer a refund.', 'mp' ),
				      'other' => __( 'A reversal has occurred on this transaction due to an unknown reason.', 'mp' )
				      );
				    $status .= ': ' . $reverse_reasons[$result["REASONCODE"]];
				    $authorized = false;
				    break;
				  case 'Refunded':
				    $status = __( 'You refunded the payment.', 'mp' );
				    $authorized = false;
				    break;
				  case 'Denied':
				    $status = __( 'You denied the payment when it was marked as pending.', 'mp' );
				    $authorized = false;
				    break;
				  case 'Pending':
				    $pending_str = array(
				      'address' => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'mp' ),
				      'authorization' => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'mp' ),
				      'echeck' => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'mp' ),
				      'intl' => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'mp' ),
				      'multi-currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'mp' ),
				      'order' => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'mp' ),
				      'paymentreview' => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'mp' ),
				      'unilateral' => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'mp' ),
				      'upgrade' => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'mp' ),
				      'verify' => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'mp' ),
				      'other' => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'mp' ),
				      '*' => ''
				    );
				    $status = __( 'The payment is pending', 'mp' );
						if ( isset( $pending_str[$result["PENDINGREASON"]] ) )
							$status .= ': ' . $pending_str[$result["PENDINGREASON"]];
				    $authorized = false;
				    break;
				  default:
				    // case: various error cases
				    $authorized = false;
				}

				if ( $authorized ) {
				  update_post_meta( $order['order_id'], 'mp_deal', 'authorized' );
				  update_post_meta( $order['order_id'], 'mp_deal_authorization_id', $authorization['TRANSACTIONID'] );
				}
      }
    }
  }

  /**
   * Use this to capture authorized transactions.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $authorizations Contains the list of authorization ids
   */
  function process_payment_capture( $authorizations ) {
    if ( is_array( $authorizations ) ) {
      foreach ( $authorizations as $authorization ) {
				$transaction_id = $authorization['transaction_id'];
				$amount = $authorization['amount'];

				$capture = $this->DoCapture( $transaction_id, $amount );

				update_post_meta( $authorization['deal_id'], 'mp_deal', 'captured' );
      }
    }
  }

  /**
   * Use this to process any fields you added. Use the $_POST global,
   *  and be sure to save it to both the $_SESSION and usermeta if logged in.
   *  DO NOT save credit card details to usermeta as it's not PCI compliant.
   *  Call $mp->cart_checkout_error( $msg, $context ); to handle errors. If no errors
   *  it will redirect to the next step.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function process_payment_form( $global_cart, $shipping_info ) {
    global $mp;

    //create order id for paypal invoice
    $order_id = $mp->generate_order_id();
    /*
    foreach ( $global_cart as $bid => $cart ) {
      foreach ( $cart as $product_id => $data ) {
				if ( 'deal' == get_post_type( $product_id ) ) {
				  $this->payment_action = 'Order';
				}
      }
    }
    */
    //set it up with PayPal
    $result = $this->SetExpressCheckout( $global_cart, $shipping_info, $order_id );

    //check response
    if( $result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning" )	{
      $token = urldecode( $result["TOKEN"] );
      $this->RedirectToPayPal( $token );
    } else { //whoops, error
      for ( $i = 0; $i <= 5; $i++ ) { //print the first 5 errors
        if ( isset( $result["L_ERRORCODE$i"] ) ) {
          $error .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - {$result["L_LONGMESSAGE$i"]}</li>";
        }
      }
      $error = '<br /><ul>' . $error . '</ul>';
      $mp->cart_checkout_error( __( 'There was a problem connecting to PayPal to setup your purchase. Please try again.', 'mp' ) . $error );
    }
  }

  /**
   * Return the chosen payment details here for final confirmation. You probably don't need
   *  to post anything in the form as it should be in your $_SESSION var already.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function confirm_payment_form( $global_cart, $shipping_info ) {
    global $mp;

    $content = '';

    if ( isset( $_GET['token'] ) && isset( $_GET['PayerID'] ) ) {
      $_SESSION['token'] = $_GET['token'];
      $_SESSION['PayerID'] = $_GET['PayerID'];

      //get details from PayPal
      $result = $this->GetExpressCheckoutDetails( $_SESSION['token'] );

      //check response
  		if( $result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning" )	{

        $account_name = ( $result["BUSINESS"] ) ? $result["BUSINESS"] : $result["EMAIL"];

        //set final amount
        $_SESSION['final_amt'] = 0;
				$_SESSION['store_count'] = 0;

				for ( $i=0; $i<10; $i++ ) {
				  if ( !isset( $result['PAYMENTREQUEST_'.$i.'_AMT'] ) ) {
				    continue;
				  }
				  $_SESSION['final_amt'] += $result['PAYMENTREQUEST_'.$i.'_AMT'];
					$_SESSION['store_count']++;
				}

        //print payment details
        $content .= '<p>' . sprintf( __( 'Please confirm your final payment for this order totaling %s. It will be made via your "%s" PayPal account.', 'mp' ), $mp->format_currency( '', $_SESSION['final_amt'] ), $account_name ) . '</p>';

  		} else { //whoops, error
        for ( $i = 0; $i <= 5; $i++ ) { //print the first 5 errors
          if ( isset( $result["L_ERRORCODE$i"] ) )
            $error .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - {$result["L_LONGMESSAGE$i"]}</li>";
        }
        $error = '<br /><ul>' . $error . '</ul>';
        $content .= '<div class="mp_checkout_error">' . sprintf( __( 'There was a problem with your PayPal transaction. Please <a href="%s">go back and try again</a>.', 'mp' ), mp_checkout_step_url( 'checkout' ) ) . $error . '</div>';
      }

    } else {
      $content .= '<div class="mp_checkout_error">' . sprintf( __( 'Whoops, looks like you skipped a step! Please <a href="%s">go back and try again</a>.', 'mp' ), mp_checkout_step_url( 'checkout' ) ) . '</div>';
    }

    return $content;
  }

  /**
   * Use this to do the final payment. Create the order then process the payment. If
   *  you know the payment is successful right away go ahead and change the order status
   *  as well.
   *  Call $mp->cart_checkout_error( $msg, $context ); to handle errors. If no errors
   *  it will redirect to the next step.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function process_payment( $global_cart, $shipping_info ) {
    global $mp, $blog_id, $site_id, $switched_stack, $switched;
		
	  $blog_id = ( is_multisite() ) ? $blog_id : 1;
	  $current_blog_id = $blog_id;

	  if ( !$mp->global_cart )
	  	$selected_cart[$blog_id] = $global_cart;
	  else
	    $selected_cart = $global_cart;

    if ( isset( $_SESSION['token'] ) && isset( $_SESSION['PayerID'] ) && isset( $_SESSION['final_amt'] ) ) {
      //attempt the final payment
      $result = $this->DoExpressCheckoutPayment( $_SESSION['token'], $_SESSION['PayerID'] );

      //check response
      if( $result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning" )	{

        //setup our payment details
  			$payment_info['gateway_public_name'] = $this->public_name;
        $payment_info['gateway_private_name'] = $this->admin_name;
				for ( $i=0; $i<10; $i++ ) {
				  if ( !isset( $result['PAYMENTINFO_'.$i.'_PAYMENTTYPE'] ) ) {
				    continue;
				  }
				  $payment_info['method'] = ( $result["PAYMENTINFO_{$i}_PAYMENTTYPE"] == 'echeck' ) ? __( 'eCheck', 'mp' ) : __( 'PayPal balance, Credit Card, or Instant Transfer', 'mp' );
				  $payment_info['transaction_id'] = $result["PAYMENTINFO_{$i}_TRANSACTIONID"];

				  $timestamp = time();//strtotime( $result["PAYMENTINFO_{$i}_ORDERTIME"] );
				  //setup status
				  switch ( $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] ) {
				    case 'Canceled-Reversal':
				      $status = __( 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.', 'mp' );
				      $paid = true;
				      break;
				    case 'Expired':
				      $status = __( 'The authorization period for this payment has been reached.', 'mp' );
				      $paid = false;
				      break;
				    case 'Voided':
				      $status = __( 'An authorization for this transaction has been voided.', 'mp' );
				      $paid = false;
				      break;
				    case 'Failed':
				      $status = __( 'The payment has failed. This happens only if the payment was made from your customer\'s bank account.', 'mp' );
				      $paid = false;
				      break;
				    case 'Partially-Refunded':
				      $status = __( 'The payment has been partially refunded.', 'mp' );
				      $paid = true;
				      break;
				    case 'In-Progress':
				      $status = __( 'The transaction has not terminated, e.g. an authorization may be awaiting completion.', 'mp' );
				      $paid = false;
				      break;
				    case 'Completed':
				      $status = __( 'The payment has been completed, and the funds have been added successfully to your account balance.', 'mp' );
				      $paid = true;
				      break;
				    case 'Processed':
				      $status = __( 'A payment has been accepted.', 'mp' );
				      $paid = true;
				      break;
				    case 'Reversed':
				      $status = __( 'A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer:', 'mp' );
				      $reverse_reasons = array(
								'none' => '',
								'chargeback' => __( 'A reversal has occurred on this transaction due to a chargeback by your customer.', 'mp' ),
								'guarantee' => __( 'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.', 'mp' ),
								'buyer-complaint' => __( 'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.', 'mp' ),
								'refund' => __( 'A reversal has occurred on this transaction because you have given the customer a refund.', 'mp' ),
								'other' => __( 'A reversal has occurred on this transaction due to an unknown reason.', 'mp' )
								);
				      $status .= '<br />' . $reverse_reasons[$result["PAYMENTINFO_{$i}_REASONCODE"]];
				      $paid = false;
				      break;
				    case 'Refunded':
				      $status = __( 'You refunded the payment.', 'mp' );
				      $paid = false;
				      break;
				    case 'Denied':
				      $status = __( 'You denied the payment when it was marked as pending.', 'mp' );
				      $paid = false;
				      break;
				    case 'Pending':
				      $pending_str = array(
								'address' => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'mp' ),
								'authorization' => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'mp' ),
								'echeck' => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'mp' ),
								'intl' => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'mp' ),
								'multi-currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'mp' ),
								'order' => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'mp' ),
								'paymentreview' => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'mp' ),
								'unilateral' => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'mp' ),
								'upgrade' => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'mp' ),
								'verify' => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'mp' ),
								'other' => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'mp' ),
								'*' => ''
				      );
				      $status = __( 'The payment is pending.', 'mp' );
				      $status .= '<br />' . $pending_str[$result["PAYMENTINFO_{$i}_PENDINGREASON"]];
				      $paid = false;
				      break;
				    default:
				      // case: various error cases
				      $paid = false;
				  }
				  $status = $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] . ': '. $status;

				  //status's are stored as an array with unix timestamp as key
				  $payment_info['status'] = array();
				  $payment_info['status'][$timestamp] = $status;
				  $payment_info['currency'] = $result["PAYMENTINFO_{$i}_CURRENCYCODE"];
				  $payment_info['total'] = $result["PAYMENTINFO_{$i}_AMT"];

				  $payment_info['note'] = $result["NOTE"]; //optional, only shown if gateway supports it

					//figure out blog_id of this payment to put the order into it
          $unique_id = ( $result["PAYMENTINFO_{$i}_PAYMENTREQUESTID"] ) ? $result["PAYMENTINFO_{$i}_PAYMENTREQUESTID"] : $result["PAYMENTREQUEST_{$i}_PAYMENTREQUESTID"]; //paypal docs messed up, not sure which is valid return
					@list( $bid, $order_id ) = explode( ':', $unique_id );
			
          if ( is_multisite() )	
						switch_to_blog( $bid, true );

					//succesful payment, create our order now
	        $mp->create_order( $_SESSION['mp_order'], $selected_cart[$bid], $shipping_info, $payment_info, $paid );
				}	
		
        if ( is_multisite() )
    			switch_to_blog( $current_blog_id, true );
				
        //success. Do nothing, it will take us to the confirmation page
      } else { //whoops, error

				for ( $i = 0; $i <= 5; $i++ ) { //print the first 5 errors
          if ( isset( $result["L_ERRORCODE$i"] ) )
            $error .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - ".stripslashes( $result["L_LONGMESSAGE$i"] )."</li>";
        }
        $error = '<br /><ul>' . $error . '</ul>';
        $mp->cart_checkout_error( sprintf( __( 'There was a problem finalizing your purchase with PayPal. Please <a href="%s">go back and try again</a>.', 'mp' ), mp_checkout_step_url( 'checkout' ) ) . $error );
      }
    } else {
      $mp->cart_checkout_error( sprintf( __( 'There was a problem finalizing your purchase with PayPal. Please <a href="%s">go back and try again</a>.', 'mp' ), mp_checkout_step_url( 'checkout' ) ) );
    }
  }

	/**
   * Runs before page load incase you need to run any scripts before loading the success message page
   */
	function order_confirmation( $order ) {

  }

  /**
   * Filters the order confirmation email message body. You may want to append something to
   *  the message. Optional
   *
   * Don't forget to return!
   */
	function order_confirmation_email( $msg, $order ) {
    return $msg;
  }

  /**
   * Return any html you want to show on the confirmation screen after checkout. This
   *  should be a payment details box and message.
   *
   * Don't forget to return!
   */
  function order_confirmation_msg( $content, $order ) {
    global $mp;

    if ( $mp->global_cart ) {
		  $content .= '<p>' . sprintf( __( 'Your order( s ) for %s store( s ) totaling %s were successful.', 'mp' ), $_SESSION['store_count'], $mp->format_currency( $this->currencyCode, $_SESSION['final_amt'] ) ) . '</p>';
			/* TODO - create a list of sep store orders*/
	  } else {
	    if ( $order->post_status == 'order_received' ) {
	      $content .= '<p>' . sprintf( __( 'Your PayPal payment for this order totaling %s is not yet complete. Here is the latest status:', 'mp' ), $mp->format_currency( $order->mp_payment_info['currency'], $order->mp_payment_info['total'] ) ) . '</p>';
	      $statuses = $order->mp_payment_info['status'];
	      krsort( $statuses ); //sort with latest status at the top
	      $status = reset( $statuses );
	      $timestamp = key( $statuses );
	      $content .= '<p><strong>' . $mp->format_date( $timestamp ) . ':</strong> ' . esc_html( $status ) . '</p>';
	    } else {
	      $content .= '<p>' . sprintf( __( 'Your PayPal payment for this order totaling %s is complete. The PayPal transaction number is <strong>%s</strong>.', 'mp' ), $mp->format_currency( $order->mp_payment_info['currency'], $order->mp_payment_info['total'] ), $order->mp_payment_info['transaction_id'] ) . '</p>';
	    }
		}
    return $content;
  }

	/**
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box( $settings ) {
    global $mp;
    ?>
    <div id="mp_paypal_express" class="postbox">
      <script type="text/javascript">
    	  jQuery( document ).ready( function ( $ ) {
      		$( '#mp-hdr-bdr' ).ColorPicker( {
          	onSubmit: function( hsb, hex, rgb, el ) {
          		$( el ).val( hex );
          		$( el ).ColorPickerHide();
          	},
          	onBeforeShow: function () {
          		$( this ).ColorPickerSetColor( this.value );
          	},
            onChange: function ( hsb, hex, rgb ) {
          		$( '#mp-hdr-bdr' ).val( hex );
          	}
          } )
          .bind( 'keyup', function() {
          	$( this ).ColorPickerSetColor( this.value );
          } );
          $( '#mp-hdr-bck' ).ColorPicker( {
          	onSubmit: function( hsb, hex, rgb, el ) {
          		$( el ).val( hex );
          		$( el ).ColorPickerHide();
          	},
          	onBeforeShow: function () {
          		$( this ).ColorPickerSetColor( this.value );
          	},
            onChange: function ( hsb, hex, rgb ) {
          		$( '#mp-hdr-bck' ).val( hex );
          	}
          } )
          .bind( 'keyup', function() {
          	$( this ).ColorPickerSetColor( this.value );
          } );
          $( '#mp-pg-bck' ).ColorPicker( {
          	onSubmit: function( hsb, hex, rgb, el ) {
          		$( el ).val( hex );
          		$( el ).ColorPickerHide();
          	},
          	onBeforeShow: function () {
          		$( this ).ColorPickerSetColor( this.value );
          	},
            onChange: function ( hsb, hex, rgb ) {
          		$( '#mp-pg-bck' ).val( hex );
          	}
          } )
          .bind( 'keyup', function() {
          	$( this ).ColorPickerSetColor( this.value );
          } );
    		} );
    	</script>
      <h3 class='hndle'><span><?php _e( 'PayPal Express Checkout Settings', 'mp' ); ?></span></h3>
      <div class="inside">
        <span class="description"><?php _e( 'Express Checkout is PayPal\'s premier checkout solution, which streamlines the checkout process for buyers and keeps them on your site after making a purchase. Unlike PayPal Pro, there are no additional fees to use Express Checkout, though you may need to do a free upgrade to a business account. <a target="_blank" href="https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECGettingStarted">More Info &raquo;</a>', 'mp' ) ?></span>
        <table class="form-table">
          <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
  				<th scope="row"><?php _e( 'PayPal Site', 'mp' ) ?></th>
  				<td>
            <select name="mp[gateways][paypal-express][locale]">
            <?php
            $sel_locale = $mp->get_setting( 'gateways->paypal-express->locale', $mp->get_setting( 'base_country' ) );
            $locales = array(
              'AR'	=> 'Argentina',
              'AU'	=> 'Australia',
              'AT'	=> 'Austria',
              'BE'	=> 'Belgium',
              'BR'	=> 'Brazil',
              'CA'	=> 'Canada',
              'CN'	=> 'China',
							'FI'	=> 'Finland',
              'FR'	=> 'France',
              'DE'	=> 'Germany',
              'HK'	=> 'Hong Kong',
              'IE'	=> 'Ireland',
              'IL'	=> 'Israel',
              'IT'	=> 'Italy',
              'JP'	=> 'Japan',
              'MX'	=> 'Mexico',
              'NL'	=> 'Netherlands',
              'NZ'	=> 'New Zealand',
							'PL'	=> 'Poland',
							'RU'	=> 'Russia',
              'SG'	=> 'Singapore',
              'ES'	=> 'Spain',
              'SE'	=> 'Sweden',
              'CH'	=> 'Switzerland',
							'TR' 	=> 'Turkey',
              'GB'	=> 'United Kingdom',
              'US'	=> 'United States'
            );

            foreach ( $locales as $k => $v ) {
                echo '		<option value="' . $k . '"' . ( $k == $sel_locale ? ' selected' : '' ) . '>' . esc_html( $v ) . '</option>' . "\n";
            }
            ?>
            </select>
  				</td>
          </tr>
          <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
	        <th scope="row"><?php _e( 'Paypal Currency', 'mp' ) ?></th>
	        <td>
	          <select name="mp[gateways][paypal-express][currency]">
	          <?php
	          $sel_currency = $mp->get_setting( 'gateways->paypal-express->currency', $mp->get_setting( 'currency' ) );
	          $currencies = array(
	              'AUD' => 'AUD - Australian Dollar',
	              'BRL' => 'BRL - Brazilian Real',
	              'CAD' => 'CAD - Canadian Dollar',
	              'CHF' => 'CHF - Swiss Franc',
	              'CZK' => 'CZK - Czech Koruna',
	              'DKK' => 'DKK - Danish Krone',
	              'EUR' => 'EUR - Euro',
	              'GBP' => 'GBP - Pound Sterling',
	              'ILS' => 'ILS - Israeli Shekel',
	              'HKD' => 'HKD - Hong Kong Dollar',
	              'HUF' => 'HUF - Hungarian Forint',
	              'JPY' => 'JPY - Japanese Yen',
	              'MYR' => 'MYR - Malaysian Ringgits',
	              'MXN' => 'MXN - Mexican Peso',
	              'NOK' => 'NOK - Norwegian Krone',
	              'NZD' => 'NZD - New Zealand Dollar',
	              'PHP' => 'PHP - Philippine Pesos',
	              'PLN' => 'PLN - Polish Zloty',
								'RUB' => 'RUB - Russian Rubles',
	              'SEK' => 'SEK - Swedish Krona',
	              'SGD' => 'SGD - Singapore Dollar',
	              'TWD' => 'TWD - Taiwan New Dollars',
	              'THB' => 'THB - Thai Baht',
								'TRY' => 'TRY - Turkish lira',
	              'USD' => 'USD - U.S. Dollar'
	          );

	          foreach ( $currencies as $k => $v ) {
	              echo '		<option value="' . $k . '"' . ( $k == $sel_currency ? ' selected' : '' ) . '>' . esc_html( $v ) . '</option>' . "\n";
	          }
	          ?>
	          </select>
	        </td>
	        </tr>
	        <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
					<th scope="row"><?php _e( 'PayPal Mode', 'mp' ) ?></th>
					<td>
					<select name="mp[gateways][paypal-express][mode]">
	          <option value="sandbox"<?php selected( $mp->get_setting( 'gateways->paypal-express->mode' ), 'sandbox' ) ?>><?php _e( 'Sandbox', 'mp' ) ?></option>
	          <option value="live"<?php selected( $mp->get_setting( 'gateways->paypal-express->mode' ), 'live' ) ?>><?php _e( 'Live', 'mp' ) ?></option>
	        </select>
					</td>
	        </tr>
					<tr<?php echo ( $mp->global_cart ) ? '' : ' style="display:none;"'; ?>>
					<th scope="row"><?php _e( 'PayPal Merchant E-mail', 'mp' ) ?></th>
					<td>
					<input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->merchant_email' ) ); ?>" size="30" name="mp[gateways][paypal-express][merchant_email]" type="text" />
					</td>
	        </tr>
	        <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
					<th scope="row"><?php _e( 'PayPal API Credentials', 'mp' ) ?></th>
					<td>
	  				<span class="description"><?php _e( 'You must login to PayPal and create an API signature to get your credentials. <a target="_blank" href="https://www.x.com/developers/paypal/documentation-tools/express-checkout/integration-guide/ECAPICredentials">Instructions &raquo;</a>', 'mp' ) ?></span>
	          <p><label><?php _e( 'API Username', 'mp' ) ?><br />
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->api_user' ) ); ?>" size="30" name="mp[gateways][paypal-express][api_user]" type="text" />
	          </label></p>
	          <p><label><?php _e( 'API Password', 'mp' ) ?><br />
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->api_pass' ) ); ?>" size="20" name="mp[gateways][paypal-express][api_pass]" type="text" />
	          </label></p>
	          <p><label><?php _e( 'Signature', 'mp' ) ?><br />
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->api_sig' ) ); ?>" size="70" name="mp[gateways][paypal-express][api_sig]" type="text" />
	          </label></p>
	        </td>
	        </tr>
	        <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
					<th scope="row"><?php _e( 'PayPal Header Image ( optional )', 'mp' ) ?></th>
					<td>
	  				<span class="description"><?php _e( 'URL for an image you want to appear at the top left of the payment page. The image has a maximum size of 750 pixels wide by 90 pixels high. PayPal recommends that you provide an image that is stored on a secure ( https ) server. If you do not specify an image, the business name is displayed.', 'mp' ) ?></span>
	          <p>
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->header_img' ) ); ?>" size="80" name="mp[gateways][paypal-express][header_img]" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
					<th scope="row"><?php _e( 'PayPal Header Border Color ( optional )', 'mp' ) ?></th>
					<td>
	  				<span class="description"><?php _e( 'Sets the border color around the header of the payment page. The border is a 2-pixel perimeter around the header space, which is 750 pixels wide by 90 pixels high. By default, the color is black.', 'mp' ) ?></span>
	          <p>
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->header_border' ) ); ?>" size="6" maxlength="6" name="mp[gateways][paypal-express][header_border]" id="mp-hdr-bdr" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
					<th scope="row"><?php _e( 'PayPal Header Background Color ( optional )', 'mp' ) ?></th>
					<td>
	  				<span class="description"><?php _e( 'Sets the background color for the header of the payment page. By default, the color is white.', 'mp' ) ?></span>
	          <p>
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->header_back' ) ); ?>" size="6" maxlength="6" name="mp[gateways][paypal-express][header_back]" id="mp-hdr-bck" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr<?php echo ( $mp->global_cart ) ? ' style="display:none;"' : ''; ?>>
					<th scope="row"><?php _e( 'PayPal Page Background Color ( optional )', 'mp' ) ?></th>
					<td>
	  				<span class="description"><?php _e( 'Sets the background color for the payment page. By default, the color is white.', 'mp' ) ?></span>
	          <p>
	          <input value="<?php echo esc_attr( $mp->get_setting( 'gateways->paypal-express->page_back' ) ); ?>" size="6" maxlength="6" name="mp[gateways][paypal-express][page_back]" id="mp-pg-bck" type="text" />
	          </p>
	        </td>
	        </tr>
        </table>
      </div>
    </div>
    <?php
  }

  /**
   * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
   *  array. Don't forget to return!
   */
	function process_gateway_settings( $settings ) {

    return $settings;
  }

	/**
   * Use to handle any payment returns from your gateway to the ipn_url. Do not echo anything here. If you encounter errors
   *  return the proper headers to your ipn sender. Exits after.
   */
	function process_ipn_return() {
    global $mp;

    // PayPal IPN handling code
    if ( isset( $_POST['payment_status'] ) || isset( $_POST['txn_type'] ) ) {

			if ( $mp->get_setting( 'gateways->paypal-express->mode' ) == 'sandbox' ) {
        $domain = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$domain = 'https://www.paypal.com/cgi-bin/webscr';
			}

			$req = 'cmd=_notify-validate';
			if ( !isset( $_POST ) ) $_POST = $HTTP_POST_VARS;
			foreach ( $_POST as $k => $v ) {
				if ( get_magic_quotes_gpc() ) $v = stripslashes( $v );
				$req .= '&' . $k . '=' . urlencode( $v );
			}

      $args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | PayPal Express Plugin/{$mp->version}";
      $args['body'] = $req;
      $args['sslverify'] = false;
			$args['timeout'] = 30;

      //use built in WP http class to work with most server setups
    	$response = wp_remote_post( $domain, $args );

    	//check results
    	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 || $response['body'] != 'VERIFIED' ) {
        header( "HTTP/1.1 503 Service Unavailable" );
        _e( 'There was a problem verifying the IPN string with PayPal. Please try again.', 'mp' );
        exit;
      }

			// process PayPal response
			switch ( $_POST['payment_status'] ) {

			  case 'Canceled-Reversal':
          $status = __( 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.', 'mp' );
          $paid = true;
					break;

        case 'Expired':
          $status = __( 'The authorization period for this payment has been reached.', 'mp' );
          $paid = false;
					break;

        case 'Voided':
          $status = __( 'An authorization for this transaction has been voided.', 'mp' );
          $paid = false;
					break;

        case 'Failed':
          $status = __( "The payment has failed. This happens only if the payment was made from your customer's bank account.", 'mp' );
          $paid = false;
					break;

   			case 'Partially-Refunded':
          $status = __( 'The payment has been partially refunded.', 'mp' );
          $paid = true;
					break;

				case 'In-Progress':
          $status = __( 'The transaction has not terminated, e.g. an authorization may be awaiting completion.', 'mp' );
          $paid = false;
					break;

				case 'Completed':
          $status = __( 'The payment has been completed, and the funds have been added successfully to your account balance.', 'mp' );
          $paid = true;
					break;

				case 'Processed':
					$status = __( 'A payment has been accepted.', 'mp' );
          $paid = true;
					break;

				case 'Reversed':
					$status = __( 'A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer:', 'mp' );
          $reverse_reasons = array(
            'none' => '',
            'chargeback' => __( 'A reversal has occurred on this transaction due to a chargeback by your customer.', 'mp' ),
            'guarantee' => __( 'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.', 'mp' ),
            'buyer-complaint' => __( 'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.', 'mp' ),
            'refund' => __( 'A reversal has occurred on this transaction because you have given the customer a refund.', 'mp' ),
            'other' => __( 'A reversal has occurred on this transaction due to an unknown reason.', 'mp' )
            );
          $status .= '<br />' . $reverse_reasons[$result["PAYMENTINFO_0_REASONCODE"]];
          $paid = false;
					break;

				case 'Refunded':
					$status = __( 'You refunded the payment.', 'mp' );
          $paid = false;
					break;

				case 'Denied':
					$status = __( 'You denied the payment when it was marked as pending.', 'mp' );
          $paid = false;
					break;

				case 'Pending':
					$pending_str = array(
						'address' => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'mp' ),
						'authorization' => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'mp' ),
						'echeck' => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'mp' ),
						'intl' => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'mp' ),
						'multi-currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'mp' ),
            'order' => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'mp' ),
            'paymentreview' => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'mp' ),
            'unilateral' => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'mp' ),
						'upgrade' => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'mp' ),
						'verify' => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'mp' ),
						'other' => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'mp' ),
            '*' => ''
						);
          $status = __( 'The payment is pending.', 'mp' );
          $status .= '<br />' . $pending_str[$_POST["pending_reason"]];
          $paid = false;
					break;

				default:
					// case: various error cases
			}
      $status = $_POST['payment_status'] . ': '. $status;

      //record transaction
      $mp->update_order_payment_status( $_POST['invoice'], $status, $paid );

		} else {
			// Did not find expected POST variables. Possible access attempt from a non PayPal site.
			header( 'Status: 404 Not Found' );
			echo 'Error: Missing POST variables. Identification is not possible.';
			exit;
		}
  }

  /**** PayPal API methods *****/


	//Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
  function SetExpressCheckout( $global_cart, $shipping_info, $order_id )	{
    global $mp, $blog_id;
	  $blog_id = ( is_multisite() ) ? $blog_id : 1;
	  $current_blog_id = $blog_id;

	  if ( !$mp->global_cart ) {
	  	$selected_cart[$blog_id] = $global_cart;
	  	$settings = get_option( 'mp_settings' );
	  } else {
	    $selected_cart = $global_cart;
      $settings = get_site_option( 'mp_network_settings' );
    }

    $nvpstr = "";
    $nvpstr .= "&ReturnUrl=" . $this->returnURL;
    $nvpstr .= "&CANCELURL=" . $this->cancelURL;
    $nvpstr .= "&ADDROVERRIDE=1";
    $nvpstr .= "&NOSHIPPING=2";
    $nvpstr .= "&LANDINGPAGE=Billing";
    $nvpstr .= "&SOLUTIONTYPE=Sole";
    $nvpstr .= "&LOCALECODE=" . $this->locale;
    $nvpstr .= "&EMAIL=" . urlencode( $shipping_info['email'] );

    //formatting
    $nvpstr .= "&HDRIMG=" . urlencode( $settings['gateways']['paypal-express']['header_img'] );
    $nvpstr .= "&HDRBORDERCOLOR=" . urlencode( $settings['gateways']['paypal-express']['header_border'] );
    $nvpstr .= "&HDRBACKCOLOR=" . urlencode( $settings['gateways']['paypal-express']['header_back'] );
    $nvpstr .= "&PAYFLOWCOLOR=" . urlencode( $settings['gateways']['paypal-express']['page_back'] );
		
    //loop through cart items
    $j = 0;
		$request = '';
    foreach ( $selected_cart as $bid => $cart ) {
      if ( !is_array( $cart ) || count( $cart ) == 0 ) {
				continue;
      }
      if ( is_multisite() ) {
				switch_to_blog( $bid );
      }

			$merchant_email = $mp->get_setting( 'gateways->paypal-express->merchant_email' );
			//if a seller hasn't configured paypal skip
			if ( $mp->global_cart && empty( $merchant_email ) )
				continue;
			
      $totals = array();
			
      $request .= "&PAYMENTREQUEST_{$j}_SELLERID=" . $bid;
      $request .= "&PAYMENTREQUEST_{$j}_SELLERPAYPALACCOUNTID=" . $merchant_email;
      $request .= "&PAYMENTREQUEST_{$j}_PAYMENTACTION=" . $this->payment_action;
      $request .= "&PAYMENTREQUEST_{$j}_CURRENCYCODE=" . $this->currencyCode;
      $request .= "&PAYMENTREQUEST_{$j}_NOTIFYURL=" . $this->ipn_url;  //this is supposed to be in DoExpressCheckoutPayment, but I put it here as well as docs are lacking
			
			if ( !$mp->download_only_cart( $cart ) && $mp->get_setting( 'shipping->method' ) != 'none' ) {
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTONAME=" . $this->trim_name( $shipping_info['name'], 32 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOSTREET=" . $this->trim_name( $shipping_info['address1'], 100 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOSTREET2=" . $this->trim_name( $shipping_info['address2'], 100 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOCITY=" . $this->trim_name( $shipping_info['city'], 40 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOSTATE=" . $this->trim_name( $shipping_info['state'], 40 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOCOUNTRYCODE=" . $this->trim_name( $shipping_info['country'], 2 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOZIP=" . $this->trim_name( $shipping_info['zip'], 20 );
				$request .= "&PAYMENTREQUEST_{$j}_SHIPTOPHONENUM=" . $this->trim_name( $shipping_info['phone'], 20 );
			}

      $i = 0;
      foreach ( $cart as $product_id => $variations ) {
        foreach ( $variations as $variation => $data ) {
					//skip free products to avoid paypal error
					if ( $data['price'] <= 0 )
						continue;
					
					//we're sending tax included prices here is tax included is on, as paypal messes up rounding
				  $totals[] = $data['price'] * $data['quantity'];
				  $request .= "&L_PAYMENTREQUEST_{$j}_NAME$i=" . $this->trim_name( $data['name'] );
				  $request .= "&L_PAYMENTREQUEST_{$j}_AMT$i=" . urlencode( $data['price'] );
				  $request .= "&L_PAYMENTREQUEST_{$j}_NUMBER$i=" . urlencode( $data['SKU'] );
				  $request .= "&L_PAYMENTREQUEST_{$j}_QTY$i=" . urlencode( $data['quantity'] );
				  $request .= "&L_PAYMENTREQUEST_{$j}_ITEMURL$i=" . urlencode( $data['url'] );
				  $request .= "&L_PAYMENTREQUEST_{$j}_ITEMCATEGORY$i=Physical";
				  $i++;
				}
      }
      $total = array_sum( $totals );

      //coupon line
      if ( $coupon = $mp->coupon_value( $mp->get_coupon_code(), $total ) ) {
				if ( false === strpos( $coupon['discount'], '%' ) )
					$discount = preg_replace( "/&( [A-Za-z]+|#x[\dA-Fa-f]+|#\d+ );/", "", $coupon['discount'] ) . ' ' . $this->currencyCode;
				
				$coupon_total = ( $coupon['new_total'] <= 0 ) ? '0.01' : $coupon['new_total'];//if coupon makes it 0 then change to 1 cent to avoid errors
				
				$request .= "&L_PAYMENTREQUEST_{$j}_NAME$i=" . urlencode( sprintf( __( '%s Coupon discount' ), $discount ) );
				$request .= "&L_PAYMENTREQUEST_{$j}_AMT$i=" . urlencode( $coupon_total-$total );
				$request .= "&L_PAYMENTREQUEST_{$j}_NUMBER$i=" . urlencode( $mp->get_coupon_code() );
				$request .= "&L_PAYMENTREQUEST_{$j}_QTY$i=1";
				
				$total = $coupon_total;
      }

      $request .= "&PAYMENTREQUEST_{$j}_ITEMAMT=" . $total; //items subtotal

      //shipping line
      if ( ( $shipping_price = $mp->shipping_price( false ) ) !== false ) {
				
				//adjust price if tax inclusive is on
				if ( $mp->get_setting( 'tax->tax_inclusive' ) )
					$shipping_price = $mp->shipping_tax_price( $shipping_price );
					
				$total = $total + $shipping_price;
				$request .= "&PAYMENTREQUEST_{$j}_SHIPPINGAMT=" . $shipping_price; //shipping total
      }

      //tax line if tax inclusive pricing is off. It it's on it would screw up the totals
      if ( !$mp->get_setting( 'tax->tax_inclusive' ) && ( $tax_price = $mp->tax_price( false ) ) !== false ) {
				$total = $total + $tax_price;
				$request .= "&PAYMENTREQUEST_{$j}_TAXAMT=" . $tax_price; //taxes total
      }

      //order details
      $request .= "&PAYMENTREQUEST_{$j}_DESC=" . $this->trim_name( sprintf( __( '%s Store Purchase - Order ID: %s', 'mp' ), get_bloginfo( 'name' ), $order_id ) ); //cart name
      $request .= "&PAYMENTREQUEST_{$j}_AMT=" . $total; //cart total
      $request .= "&PAYMENTREQUEST_{$j}_INVNUM=" . $order_id;
      $request .= "&PAYMENTREQUEST_{$j}_PAYMENTREQUESTID=" . $bid . ":" . $order_id;

      if ( $this->payment_action == 'Sale' ) {
				$request .= "&PAYMENTREQUEST_{$j}_ALLOWEDPAYMENTMETHOD=InstantPaymentOnly";
      }
      $j++;
    }

    if ( is_multisite() )
      switch_to_blog( $current_blog_id );
		
		$nvpstr .= $request;
		$_SESSION['nvpstr'] = $request;
		
    //'---------------------------------------------------------------------------------------------------------------
    //' Make the API call to PayPal
    //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.
    //' If an error occured, show the resulting errors
    //'---------------------------------------------------------------------------------------------------------------
    $resArray = $this->api_call( "SetExpressCheckout", $nvpstr );
    $ack = strtoupper( $resArray["ACK"] );
    if( $ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING" )	{
      $token = urldecode( $resArray["TOKEN"] );
      $_SESSION['TOKEN'] = $token;
    }
    return $resArray;
  }

	//Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
	function GetExpressCheckoutDetails( $token )	{
		//'--------------------------------------------------------------
		//' At this point, the buyer has completed authorizing the payment
		//' at PayPal.  The function will call PayPal to obtain the details
		//' of the authorization, incuding any shipping information of the
		//' buyer.  Remember, the authorization is not a completed transaction
		//' at this state - the buyer still needs an additional step to finalize
		//' the transaction
		//'--------------------------------------------------------------

	    //'---------------------------------------------------------------------------
		//' Build a second API request to PayPal, using the token as the
		//'  ID to get the details on the payment authorization
		//'---------------------------------------------------------------------------
	  $nvpstr = "&TOKEN=" . $token;

		//'---------------------------------------------------------------------------
		//' Make the API call and store the results in an array.
		//'	If the call was a success, show the authorization details, and provide
		//' 	an action to complete the payment.
		//'	If failed, show the error
		//'---------------------------------------------------------------------------
    $resArray = $this->api_call( "GetExpressCheckoutDetails", $nvpstr );
    $ack = strtoupper( $resArray["ACK"] );
		if( $ack == "SUCCESS" || $ack=="SUCCESSWITHWARNING" ) {
			$_SESSION['payer_id'] =	$resArray['PAYERID'];
		}
		return $resArray;
	}


	//Purpose: 	Prepares the parameters for the DoExpressCheckoutPayment API Call.
	function DoExpressCheckoutPayment( $token, $payer_id ) {
		
		$nvpstr  = '&TOKEN=' . urlencode( $token );
	  $nvpstr .= '&PAYERID=' . urlencode( $payer_id );
		$nvpstr .= $_SESSION['nvpstr'];

	  /* Make the call to PayPal to finalize payment
	    */
	  return $this->api_call( "DoExpressCheckoutPayment", $nvpstr );
	}

	//Purpose: 	Prepares the parameters for the DoAuthorization API Call.
	function DoAuthorization( $transaction_id, $final_amt ) {

	  $nvpstr .= '&TRANSACTIONID=' . urlencode( $transaction_id );
	  $nvpstr .= '&AMT=' . $final_amt;
	  $nvpstr .= '&TRANSACTIONENTITY=Order';
	  $nvpstr .= '&CURRENCYCODE=' . $this->currencyCode;

	  /* Make the call to PayPal to finalize payment
	   */
	  return $this->api_call( "DoAuthorization", $nvpstr );
	}

	//Purpose: 	Prepares the parameters for the DoCapture API Call.
	function DoCapture( $transaction_id, $final_amt ) {

	  $nvpstr .= '&AUTHORIZATIONID=' . urlencode( $transaction_id );
	  $nvpstr .= '&AMT=' . $final_amt;
	  $nvpstr .= '&CURRENCYCODE=' . $this->currencyCode;
	  $nvpstr .= '&COMPLETETYPE=Complete';

	  /* Make the call to PayPal to finalize payment
	   */
	  return $this->api_call( "DoCapture", $nvpstr );
	}

	/**
	  '-------------------------------------------------------------------------------------------------------------------------------------------
	  * $this->api_call: Function to perform the API call to PayPal using API signature
	  * @methodName is name of API  method.
	  * @nvpStr is nvp string.
	  * returns an associtive array containing the response from the server.
	  '-------------------------------------------------------------------------------------------------------------------------------------------
	*/
	function api_call( $methodName, $nvpStr ) {
	  global $mp;

	  //NVPRequest for submitting to server
	  $query_string = "METHOD=" . urlencode( $methodName ) . "&VERSION=" . urlencode( $this->version ) . "&PWD=" . urlencode( $this->API_Password ) . "&USER=" . urlencode( $this->API_Username ) . "&SIGNATURE=" . urlencode( $this->API_Signature ) . $nvpStr;
	  //build args
	  $args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | PayPal Express Plugin/{$mp->version}";
	  $args['body'] = $query_string;
	  $args['sslverify'] = false;
	  $args['timeout'] = 60;
		
		//allow easy debugging
		if ( defined( "MP_DEBUG_API_$methodName" ) ) {
			var_dump( $this->deformatNVP( $query_string ) );
			die;
		}
		
	  //use built in WP http class to work with most server setups
	  $response = wp_remote_post( $this->API_Endpoint, $args );
	  if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
	    $mp->cart_checkout_error( __( 'There was a problem connecting to PayPal. Please try again.', 'mp' ) );
	    return false;
	  } else {
	    //convert NVPResponse to an Associative Array
	    $nvpResArray = $this->deformatNVP( $response['body'] );
	    return $nvpResArray;
	  }
	}

	/*'----------------------------------------------------------------------------------
	 Purpose: Redirects to PayPal.com site.
	 Inputs:  NVP string.
	 Returns:
	----------------------------------------------------------------------------------
	*/
	function RedirectToPayPal( $token ) {
	  // Redirect to paypal.com here
	  $payPalURL = $this->paypalURL . $token;
	  wp_redirect( $payPalURL );
	  exit;
	}


	//This function will take NVPString and convert it to an Associative Array and it will decode the response.
	function deformatNVP( $nvpstr ) {
		parse_str( $nvpstr, $nvpArray );
		return $nvpArray;
	}
	
	function trim_name( $name, $length = 127 ) {
		while ( strlen( urlencode( $name ) ) > $length )
			$name = substr( $name, 0, -1 );
		
		return urlencode( $name );	
	}
	
}

//register shipping plugin
mp_register_gateway_plugin( 'MP_Gateway_Paypal_Express', 'paypal-express', __( 'PayPal Express Checkout', 'mp' ), true );

if ( is_multisite() ) {
	//tie into network settings form
	add_action( 'mp_network_gateway_settings', 'psts_pe_network_gateway_settings_box' );
}

function psts_pe_network_gateway_settings_box( $settings ) {
  global $mp;
  ?>
  <script type="text/javascript">
	  jQuery( document ).ready( function( $ ) {
      $( "#gbl_gw_paypal-express" ).change( function() {
        $( "#mp-main-form" ).submit();
  		} );
    } );
	</script>
	<?php
	$hide = false;
  if ( !$settings['global_cart'] || $settings['global_gateway'] != 'paypal-express' )
    $hide = true;
  ?>
  <div id="mp_paypal_express" class="postbox"<?php echo ( $hide ) ? ' style="display:none;"' : ''; ?>>
    <script type="text/javascript">
  	  jQuery( document ).ready( function ( $ ) {
    		$( '#mp-hdr-bdr' ).ColorPicker( {
        	onSubmit: function( hsb, hex, rgb, el ) {
        		$( el ).val( hex );
        		$( el ).ColorPickerHide();
        	},
        	onBeforeShow: function () {
        		$( this ).ColorPickerSetColor( this.value );
        	},
          onChange: function ( hsb, hex, rgb ) {
        		$( '#mp-hdr-bdr' ).val( hex );
        	}
        } )
        .bind( 'keyup', function() {
        	$( this ).ColorPickerSetColor( this.value );
        } );
        $( '#mp-hdr-bck' ).ColorPicker( {
        	onSubmit: function( hsb, hex, rgb, el ) {
        		$( el ).val( hex );
        		$( el ).ColorPickerHide();
        	},
        	onBeforeShow: function () {
        		$( this ).ColorPickerSetColor( this.value );
        	},
          onChange: function ( hsb, hex, rgb ) {
        		$( '#mp-hdr-bck' ).val( hex );
        	}
        } )
        .bind( 'keyup', function() {
        	$( this ).ColorPickerSetColor( this.value );
        } );
        $( '#mp-pg-bck' ).ColorPicker( {
        	onSubmit: function( hsb, hex, rgb, el ) {
        		$( el ).val( hex );
        		$( el ).ColorPickerHide();
        	},
        	onBeforeShow: function () {
        		$( this ).ColorPickerSetColor( this.value );
        	},
          onChange: function ( hsb, hex, rgb ) {
        		$( '#mp-pg-bck' ).val( hex );
        	}
        } )
        .bind( 'keyup', function() {
        	$( this ).ColorPickerSetColor( this.value );
        } );
  		} );
  	</script>
    <h3 class='hndle'><span><?php _e( 'PayPal Express Checkout Global Cart Settings', 'mp' ); ?></span></h3>
    <div class="inside">
      <span class="description"><?php _e( 'Express Checkout is PayPal\'s premier checkout solution, which streamlines the checkout process for buyers and keeps them on your site after making a purchase. Unlike PayPal Pro, there are no additional fees to use Express Checkout, though you may need to do a free upgrade to a business account. This gateway allows carts from up to 10 stores to checkout at once using parallel payments. <a target="_blank" href="https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECGettingStarted">More Info &raquo;</a>', 'mp' ) ?></span>
      <table class="form-table">
        <tr>
				<th scope="row"><?php _e( 'PayPal Site', 'mp' ) ?></th>
				<td>
          <select name="mp[gateways][paypal-express][locale]">
          <?php
          $sel_locale = ( $settings['gateways']['paypal-express']['locale'] ) ? $settings['gateways']['paypal-express']['locale'] : $mp->get_setting( 'base_country', 'US' );
          $locales = array(
						'AR'	=> 'Argentina',
						'AU'	=> 'Australia',
						'AT'	=> 'Austria',
						'BE'	=> 'Belgium',
						'BR'	=> 'Brazil',
						'CA'	=> 'Canada',
						'CN'	=> 'China',
						'FI'	=> 'Finland',
						'FR'	=> 'France',
						'DE'	=> 'Germany',
						'HK'	=> 'Hong Kong',
						'IL'	=> 'Israel',
						'IT'	=> 'Italy',
						'JP'	=> 'Japan',
						'MX'	=> 'Mexico',
						'NL'	=> 'Netherlands',
						'NZ'	=> 'New Zealand',
						'PL'	=> 'Poland',
						'RU'	=> 'Russia',
						'SG'	=> 'Singapore',
						'ES'	=> 'Spain',
						'SE'	=> 'Sweden',
						'CH'	=> 'Switzerland',
						'TR' 	=> 'Turkey',
						'GB'	=> 'United Kingdom',
						'US'	=> 'United States'
					);
					
          foreach ( $locales as $k => $v ) {
              echo '		<option value="' . $k . '"' . ( $k == $sel_locale ? ' selected' : '' ) . '>' . esc_html( $v ) . '</option>' . "\n";
          }
          ?>
          </select>
				</td>
        </tr>
        <tr>
        <th scope="row"><?php _e( 'Paypal Currency', 'mp' ) ?></th>
        <td>
          <select name="mp[gateways][paypal-express][currency]">
          <?php
          $sel_currency = ( $settings['gateways']['paypal-express']['currency'] ) ? $settings['gateways']['paypal-express']['currency'] : $mp->get_setting( 'currency' );
	        $currencies = array(
	              'AUD' => 'AUD - Australian Dollar',
	              'BRL' => 'BRL - Brazilian Real',
	              'CAD' => 'CAD - Canadian Dollar',
	              'CHF' => 'CHF - Swiss Franc',
	              'CZK' => 'CZK - Czech Koruna',
	              'DKK' => 'DKK - Danish Krone',
	              'EUR' => 'EUR - Euro',
	              'GBP' => 'GBP - Pound Sterling',
	              'ILS' => 'ILS - Israeli Shekel',
	              'HKD' => 'HKD - Hong Kong Dollar',
	              'HUF' => 'HUF - Hungarian Forint',
	              'JPY' => 'JPY - Japanese Yen',
	              'MYR' => 'MYR - Malaysian Ringgits',
	              'MXN' => 'MXN - Mexican Peso',
	              'NOK' => 'NOK - Norwegian Krone',
	              'NZD' => 'NZD - New Zealand Dollar',
	              'PHP' => 'PHP - Philippine Pesos',
	              'PLN' => 'PLN - Polish Zloty',
								'RUB' => 'RUB - Russian Rubles',
	              'SEK' => 'SEK - Swedish Krona',
	              'SGD' => 'SGD - Singapore Dollar',
	              'TWD' => 'TWD - Taiwan New Dollars',
	              'THB' => 'THB - Thai Baht',
								'TRY' => 'TRY - Turkish lira',
	              'USD' => 'USD - U.S. Dollar'
	          );

          foreach ( $currencies as $k => $v ) {
              echo '		<option value="' . $k . '"' . ( $k == $sel_currency ? ' selected' : '' ) . '>' . esc_html( $v ) . '</option>' . "\n";
          }
          ?>
          </select>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e( 'PayPal Mode', 'mp' ) ?></th>
				<td>
				<select name="mp[gateways][paypal-express][mode]">
          <option value="sandbox"<?php selected( $settings['gateways']['paypal-express']['mode'], 'sandbox' ) ?>><?php _e( 'Sandbox', 'mp' ) ?></option>
          <option value="live"<?php selected( $settings['gateways']['paypal-express']['mode'], 'live' ) ?>><?php _e( 'Live', 'mp' ) ?></option>
        </select>
				</td>
        </tr>
        <tr>
				<th scope="row"><?php _e( 'PayPal API Credentials', 'mp' ) ?></th>
				<td>
  				<span class="description"><?php _e( 'You must login to PayPal and create an API signature to get your credentials. <a target="_blank" href="https://www.x.com/developers/paypal/documentation-tools/express-checkout/integration-guide/ECAPICredentials">Instructions &raquo;</a>', 'mp' ) ?></span>
          <p><label><?php _e( 'API Username', 'mp' ) ?><br />
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['api_user'] ) ? $settings['gateways']['paypal-express']['api_user'] : $mp->get_setting( 'gateways->paypal-express->api_user' ) ); ?>" size="30" name="mp[gateways][paypal-express][api_user]" type="text" />
          </label></p>
          <p><label><?php _e( 'API Password', 'mp' ) ?><br />
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['api_pass'] ) ? $settings['gateways']['paypal-express']['api_pass'] : $mp->get_setting( 'gateways->paypal-express->api_pass' ) ); ?>" size="20" name="mp[gateways][paypal-express][api_pass]" type="text" />
          </label></p>
          <p><label><?php _e( 'Signature', 'mp' ) ?><br />
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['api_sig'] ) ? $settings['gateways']['paypal-express']['api_sig'] : $mp->get_setting( 'gateways->paypal-express->api_sig' ) ); ?>" size="70" name="mp[gateways][paypal-express][api_sig]" type="text" />
          </label></p>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e( 'PayPal Header Image ( optional )', 'mp' ) ?></th>
				<td>
  				<span class="description"><?php _e( 'URL for an image you want to appear at the top left of the payment page. The image has a maximum size of 750 pixels wide by 90 pixels high. PayPal recommends that you provide an image that is stored on a secure ( https ) server. If you do not specify an image, the business name is displayed.', 'mp' ) ?></span>
          <p>
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['header_img'] ) ? $settings['gateways']['paypal-express']['header_img'] : $mp->get_setting( 'gateways->paypal-express->header_img' ) ); ?>" size="80" name="mp[gateways][paypal-express][header_img]" type="text" />
          </p>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e( 'PayPal Header Border Color ( optional )', 'mp' ) ?></th>
				<td>
  				<span class="description"><?php _e( 'Sets the border color around the header of the payment page. The border is a 2-pixel perimeter around the header space, which is 750 pixels wide by 90 pixels high. By default, the color is black.', 'mp' ) ?></span>
          <p>
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['header_border'] ) ? $settings['gateways']['paypal-express']['header_border'] : $mp->get_setting( 'gateways->paypal-express->header_border' ) ); ?>" size="6" maxlength="6" name="mp[gateways][paypal-express][header_border]" id="mp-hdr-bdr" type="text" />
          </p>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e( 'PayPal Header Background Color ( optional )', 'mp' ) ?></th>
				<td>
  				<span class="description"><?php _e( 'Sets the background color for the header of the payment page. By default, the color is white.', 'mp' ) ?></span>
          <p>
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['header_back'] ) ? $settings['gateways']['paypal-express']['header_back'] : $mp->get_setting( 'gateways->paypal-express->header_back' ) ); ?>" size="6" maxlength="6" name="mp[gateways][paypal-express][header_back]" id="mp-hdr-bck" type="text" />
          </p>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e( 'PayPal Page Background Color ( optional )', 'mp' ) ?></th>
				<td>
  				<span class="description"><?php _e( 'Sets the background color for the payment page. By default, the color is white.', 'mp' ) ?></span>
          <p>
          <input value="<?php echo esc_attr( ( $settings['gateways']['paypal-express']['page_back'] ) ? $settings['gateways']['paypal-express']['page_back'] : $mp->get_setting( 'gateways->paypal-express->page_back' ) ); ?>" size="6" maxlength="6" name="mp[gateways][paypal-express][page_back]" id="mp-pg-bck" type="text" />
          </p>
        </td>
        </tr>
      </table>
    </div>
  </div>
  <?php
}