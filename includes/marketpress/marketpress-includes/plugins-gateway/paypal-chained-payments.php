<?php
/*
MarketPress PayPal Chained Payments Gateway Plugin
Author: Aaron Edwards (Incsub)
*/

class MP_Gateway_Paypal_Chained_Payments extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'paypal-chained';
  
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
  
  //paypal vars
  var $API_Username, $API_Password, $API_Signature, $appId, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $paypalURL, $currencyCode, $locale;
    
  /****** Below are the public methods you may overwrite via a plugin ******/

  /**
   * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   */
  function on_creation() {
    global $mp;
    $network_settings = get_site_option( 'mp_network_settings' );
    
    //set names here to be able to translate
    if ( is_super_admin() )
      $this->admin_name = __('PayPal Chained Payments', 'mp');
    else
      $this->admin_name = __('PayPal', 'mp');
      
    $this->public_name = __('PayPal', 'mp');
    
    //dynamic button img, see: https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECButtonIntegration
    $this->method_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=' . get_locale();
    $this->method_button_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=' . get_locale();
    
    //set paypal vars
  	$this->currencyCode = $mp->get_setting('gateways->paypal-chained->currency');
  	$this->locale = $mp->get_setting('gateways->paypal-chained->locale');
    $this->returnURL = mp_checkout_step_url('confirmation');
	  $this->cancelURL = mp_checkout_step_url('checkout') . "?cancel=1";

    //set api urls
  	if ($mp->get_setting('gateways->paypal-chained->mode') == 'sandbox')	{
  		$this->API_Endpoint = "https://svcs.sandbox.paypal.com/AdaptivePayments/";
  		$this->paypalURL = "https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=";
  		$this->API_Username = $network_settings['gateways']['paypal-chained']['api_user_sandbox'];
    	$this->API_Password = $network_settings['gateways']['paypal-chained']['api_pass_sandbox'];
    	$this->API_Signature = $network_settings['gateways']['paypal-chained']['api_sig_sandbox'];
    	$this->appId = 'APP-80W284485P519543T'; //this is PayPals generic test app id for sandbox
  	} else {
  		$this->API_Endpoint = "https://svcs.paypal.com/AdaptivePayments/";
  		$this->paypalURL = "https://www.paypal.com/webscr?cmd=_ap-payment&paykey=";
  		$this->API_Username = $network_settings['gateways']['paypal-chained']['api_user'];
    	$this->API_Password = $network_settings['gateways']['paypal-chained']['api_pass'];
    	$this->API_Signature = $network_settings['gateways']['paypal-chained']['api_sig'];
    	$this->appId = $network_settings['gateways']['paypal-chained']['app_id'];
  	}
  	
  }

  /**
   * Return fields you need to add to the payment screen, like your credit card info fields.
   *  If you don't need to add form fields set $skip_form to true so this page can be skipped
   *  at checkout.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function payment_form($cart, $shipping_info) {
    if (isset($_GET['cancel']))
      echo '<div class="mp_checkout_error">' . __('Your PayPal transaction has been canceled.', 'mp') . '</div>';
  }
  
  /**
   * Use this to process any fields you added. Use the $_POST global,
   *  and be sure to save it to both the $_SESSION and usermeta if logged in.
   *  DO NOT save credit card details to usermeta as it's not PCI compliant.
   *  Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
   *  it will redirect to the next step.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
	function process_payment_form($cart, $shipping_info) {

  }
  
  /**
   * Return the chosen payment details here for final confirmation. You probably don't need
   *  to post anything in the form as it should be in your $_SESSION var already.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
	function confirm_payment_form($cart, $shipping_info) {
	  global $mp;

    //print payment details
    return '<a href="#" onclick="javascript:window.open(\'https://www.paypal.com/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside\',\'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350\');return false;"><img  src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" alt="PayPal"></a>';
  }

  /**
   * Use this to do the final payment. Create the order then process the payment. If
   *  you know the payment is successful right away go ahead and change the order status
   *  as well.
   *  Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
   *  it will redirect to the next step.
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
	function process_payment($cart, $shipping_info) {
    global $mp, $current_user;

    //create order id for paypal invoice
    $order_id = $mp->generate_order_id();

    //set it up with PayPal
    $result = $this->Pay($cart, $shipping_info, $order_id);

    //check response
		if ($result["responseEnvelope_ack"] == "Success" || $result["responseEnvelope_ack"] == "SuccessWithWarning") {
			$paykey = urldecode($result["payKey"]);
			$_SESSION['PAYKEY'] = $paykey;
			
			//setup transients for ipn in case checkout doesn't redirect (ipn should come within 12 hrs!)
			set_transient('mp_order_'. $order_id . '_cart', $cart, 60*60*12);
			set_transient('mp_order_'. $order_id . '_shipping', $shipping_info, 60*60*12);
			set_transient('mp_order_'. $order_id . '_shipping_total', $mp->shipping_price(), 60*60*12);
			set_transient('mp_order_'. $order_id . '_tax_total', $mp->tax_price(), 60*60*12);
			set_transient('mp_order_'. $order_id . '_userid', $current_user->ID, 60*60*12);
			set_transient('mp_order_'. $order_id . '_coupon', $mp->get_coupon_code(), 60*60*12);
			
			//go to paypal for final payment confirmation
      $this->RedirectToPayPal($paykey);
		} else { //whoops, error
      for ($i = 0; $i <= 5; $i++) { //print the first 5 errors
        if (isset($result["error($i)_message"]))
          $error .= "<li>{$result["error($i)_errorId"]} - {$result["error($i)_message"]}</li>";
      }
      $error = '<br /><ul>' . $error . '</ul>';
      $mp->cart_checkout_error( __('There was a problem connecting to PayPal to setup your purchase. Please try again.', 'mp') . $error );
    }
  }
	
	/**
   * Runs before page load incase you need to run any scripts before loading the success message page
   */
	function order_confirmation($order) {
    global $mp;

    //check if created already by IPN
    if (!$order) {

      $result = $this->PaymentDetails($_SESSION['PAYKEY']);

      if ($result["responseEnvelope_ack"] == "Success" || $result["responseEnvelope_ack"] == "SuccessWithWarning") {

        //setup our payment details
  		  $payment_info['gateway_public_name'] = $this->public_name;
        $payment_info['gateway_private_name'] = $this->admin_name;
  		  $payment_info['method'] = __('PayPal balance, Credit Card, or Instant Transfer', 'mp');
  		  $payment_info['transaction_id'] = $result["paymentInfoList_paymentInfo(0)_transactionId"];

  		  $timestamp = time();
				$order_id = $result["trackingId"];
				
        //setup status
        switch ($result["paymentInfoList_paymentInfo(0)_transactionStatus"]) {

  				case 'PARTIALLY_REFUNDED':
            $status = __('The payment has been partially refunded.', 'mp');
            $create_order = true;
            $paid = true;
  					break;

  				case 'COMPLETED':
            $status = __('The payment has been completed, and the funds have been added successfully to your account balance.', 'mp');
            $create_order = true;
            $paid = true;
  					break;

  				case 'PROCESSING':
  					$status = __('The transaction is in progress.', 'mp');
  					$create_order = true;
            $paid = true;
  					break;

  				case 'REVERSED':
  					$status = __('You refunded the payment.', 'mp');
  					$create_order = false;
            $paid = false;
  					break;

  				case 'DENIED':
  					$status = __('The transaction was rejected by the receiver (you).', 'mp');
  					$create_order = false;
            $paid = false;
  					break;

  				case 'PENDING':
  					$pending_str = array(
  						'ADDRESS_CONFIRMATION' => __('The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.', 'mp'),
  						'ECHECK' => __('The payment is pending because it was made by an eCheck that has not yet cleared.', 'mp'),
  						'INTERNATIONAL' => __('The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'mp'),
  						'MULTI_CURRENCY' => __('You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'mp'),
              'RISK' => __('The payment is pending while it is being reviewed by PayPal for risk.', 'mp'),
              'UNILATERAL' => __('The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'mp'),
  						'UPGRADE' => __('The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'mp'),
  						'VERIFY' => __('The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'mp'),
  						'OTHER' => __('The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'mp')
  						);
            $status = __('The payment is pending.', 'mp');
            $status .= '<br />' . $pending_str[$result["paymentInfoList_paymentInfo(0)_pendingReason"]];
            $create_order = true;
            $paid = false;
  					break;

  				default:
  					// case: various error cases
  					$create_order = false;
  					$paid = false;
  			}
  			$status = $result["paymentInfoList_paymentInfo(0)_transactionStatus"] . ': '. $status;

        //status's are stored as an array with unix timestamp as key
  		  $payment_info['status'][$timestamp] = $status;
  		  $payment_info['total'] = $result["paymentInfoList_paymentInfo(0)_receiver_amount"];
  		  $payment_info['currency'] = $result["currencyCode"];
				
        //succesful payment, create our order now
        if ($create_order) {
					$cart = get_transient('mp_order_' . $order_id . '_cart');
					$shipping_info = get_transient('mp_order_' . $order_id . '_shipping');
          $order_id = $mp->create_order($result["trackingId"], $cart, $shipping_info, $payment_info, $paid);
          delete_transient('mp_order_' . $order_id . '_cart');
          delete_transient('mp_order_' . $order_id . '_shipping');
          delete_transient('mp_order_' . $order_id . '_shipping_total');
          delete_transient('mp_order_' . $order_id . '_tax_total');
			  	delete_transient('mp_order_' . $order_id . '_userid');
			  	delete_transient('mp_order_' . $order_id . '_coupon');
				} else {
          $mp->cart_checkout_error( sprintf(__('Sorry, your order was not completed. Please <a href="%s">go back and try again</a>.', 'mp'), mp_checkout_step_url('checkout')) );
          return;
        }

  		} else { //whoops, error
        for ($i = 0; $i <= 5; $i++) { //print the first 5 errors
          if (isset($result["error($i)_message"]))
            $error .= "<li>{$result["error($i)_errorId"]} - {$result["error($i)_message"]}</li>";
        }
        $error = '<br /><ul>' . $error . '</ul>';
        $mp->cart_checkout_error( sprintf(__('There was a problem connecting to PayPal to check the status of your purchase. Please <a href="%s">check the status of your order here &raquo;</a>', 'mp') . $error, mp_orderstatus_link(false, true)) );
        return;
      }
    } else {
      $mp->set_cart_cookie(Array());
		}
  }
	
  /**
   * Filters the order confirmation email message body. You may want to append something to
   *  the message. Optional
   *
   * Don't forget to return!
   */
	function order_confirmation_email($msg, $order) {
    return $msg;
  }
  
  /**
   * Return any html you want to show on the confirmation screen after checkout. This
   *  should be a payment details box and message.
   *
   * Don't forget to return!
   */
  function order_confirmation_msg($content, $order) {
    global $mp;
    
    //order should exist or have been created at this point
    if ($order->post_status == 'order_received') {
      $content .= '<p>' . sprintf(__('Your PayPal payment for this order totaling %s is not yet complete. Here is the latest status:', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
      $statuses = $order->mp_payment_info['status'];
      krsort($statuses); //sort with latest status at the top
      $status = reset($statuses);
      $timestamp = key($statuses);
      $content .= '<p><strong>' . $mp->format_date($timestamp) . ':</strong> ' . esc_html($status) . '</p>';
    } else {
      $content .= '<p>' . sprintf(__('Your PayPal payment for this order totaling %s is complete. The PayPal transaction number is <strong>%s</strong>.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']), $order->mp_payment_info['transaction_id']) . '</p>';
    }
    
    return $content;
  }
	
	/**
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box($settings) {
    global $mp;
    $network_settings = get_site_option('mp_network_settings');
    ?>
    <div id="mp_paypal_adaptive" class="postbox">
      <h3 class='hndle'><span><?php _e('PayPal Settings', 'mp'); ?></span></h3>
      <div class="inside">
        <span class="description"><?php echo $network_settings['gateways']['paypal-chained']['msg']; ?></span>
        <table class="form-table">
          <tr valign="top">
        <th scope="row"><?php _e('Paypal Currency', 'mp') ?></th>
        <td>
          <select name="mp[gateways][paypal-chained][currency]">
          <?php
          $sel_currency = ($settings['gateways']['paypal-chained']['currency']) ? $settings['gateways']['paypal-chained']['currency'] : $settings['currency'];
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

          foreach ($currencies as $k => $v) {
              echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
          }
          ?>
          </select>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e('PayPal Mode', 'mp') ?></th>
				<td>
				<select name="mp[gateways][paypal-chained][mode]">
          <option value="sandbox"<?php selected($settings['gateways']['paypal-chained']['mode'], 'sandbox') ?>><?php _e('Sandbox', 'mp') ?></option>
          <option value="live"<?php selected($settings['gateways']['paypal-chained']['mode'], 'live') ?>><?php _e('Live', 'mp') ?></option>
        </select>
				</td>
        </tr>
        <tr>
				<th scope="row"><?php _e('PayPal Email Address', 'mp') ?></th>
				<td>
  				<span class="description"><?php _e('Please enter your PayPal email address or business id. If testing use your sandbox PayPal Email.', 'mp') ?></span><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['email']); ?>" size="40" name="mp[gateways][paypal-chained][email]" type="text" />
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
	function process_gateway_settings($settings) {

    return $settings;
  }
  
	/**
   * Use to handle any payment returns from your gateway to the ipn_url. Do not echo anything here. If you encounter errors
   *  return the proper headers to your ipn sender. Exits after.
   */
	function process_ipn_return() {
    global $mp;
    
    // PayPal IPN handling code
    if (isset($_POST['transaction_type']) && isset($_POST['tracking_id'])) {
      $settings = get_option('mp_settings');
      
			if ($settings['gateways']['paypal-chained']['mode'] == 'sandbox') {
				$domain = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$domain = 'https://www.paypal.com/cgi-bin/webscr';
			}

      // We need to pull raw data and build our own copy of $_POST in order to workaround of invalid POST keys that Adaptive IPN request uses.
      $raw_post_data = file_get_contents('php://input');

      $raw_post_array = explode('&', $raw_post_data);
      $_YOUR_POST = array();
      foreach ($raw_post_array as $keyval) {
        $keyval = explode ('=', $keyval);
        if (count($keyval) == 2)
           $_YOUR_POST[$keyval[0]] = urldecode($keyval[1]);
      }
      if (count($_YOUR_POST) < 3) {
        $_YOUR_POST = $_POST;
        $original_post_used = TRUE;
      } else {
        $original_post_used = FALSE;
      }

      // Build final $_req postback request
      if ($original_post_used) {
        $req = 'cmd=_notify-validate';
        foreach ($_YOUR_POST as $key => $value) {
          $value = urlencode(stripslashes($value));
          $req .= "&$key=$value";
        }
      } else {
        $req = $raw_post_data . '&cmd=_notify-validate';
      }

      $args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | PayPal Chained Payments Plugin/{$mp->version}";
      $args['body'] = $req;
      $args['sslverify'] = false;

      //use built in WP http class to work with most server setups
    	$response = wp_remote_post($domain, $args);
    	
    	//check results
    	if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200 || $response['body'] != 'VERIFIED') {
        header("HTTP/1.1 503 Service Unavailable");
        _e( 'There was a problem verifying the IPN string with PayPal. Please try again.', 'mp' );
        exit;
      }
      
      //no errors, so fix up our $_POST array
      $result = $this->decodePayPalIPN($raw_post_data);

      //setup our payment details
		  $payment_info['gateway_public_name'] = $this->public_name;
      $payment_info['gateway_private_name'] = $this->admin_name;
		  $payment_info['method'] = __('PayPal balance, Credit Card, or Instant Transfer', 'mp');
		  $payment_info['transaction_id'] = $result["transaction"][0]["id"];

		  $timestamp = time();
		  $order_id = $result["tracking_id"];
		  
      //setup status
      switch (strtoupper($result["transaction"][0]["status"])) {

				case 'PARTIALLY_REFUNDED':
          $status = __('The payment has been partially refunded.', 'mp');
          $create_order = true;
          $paid = true;
					break;

				case 'COMPLETED':
				case 'SUCCESS':
          $status = __('The payment has been completed, and the funds have been added successfully to your account balance.', 'mp');
          $create_order = true;
          $paid = true;
					break;

				case 'PROCESSING':
					$status = __('The transaction is in progress.', 'mp');
					$create_order = true;
          $paid = true;
					break;

				case 'REVERSED':
					$status = __('You refunded the payment.', 'mp');
					$create_order = false;
          $paid = false;
					break;

				case 'DENIED':
					$status = __('The transaction was rejected by the receiver (you).', 'mp');
					$create_order = false;
          $paid = false;
					break;

				case 'PENDING':
					$pending_str = array(
						'ADDRESS_CONFIRMATION' => __('The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile.', 'mp'),
						'ECHECK' => __('The payment is pending because it was made by an eCheck that has not yet cleared.', 'mp'),
						'INTERNATIONAL' => __('The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'mp'),
						'MULTI_CURRENCY' => __('You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'mp'),
            'RISK' => __('The payment is pending while it is being reviewed by PayPal for risk.', 'mp'),
            'UNILATERAL' => __('The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'mp'),
						'UPGRADE' => __('The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'mp'),
						'VERIFY' => __('The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'mp'),
						'OTHER' => __('The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'mp')
						);
          $status = __('The payment is pending', 'mp');
          $status .= ': ' . $pending_str[$result["transaction"][0]["pending_reason"]];
          $create_order = true;
          $paid = false;
					break;

				default:
					// case: various error cases
					$create_order = false;
					$paid = false;
			}
			$status = $result["transaction"][0]["status"] . ': '. $status;

      //status's are stored as an array with unix timestamp as key
		  $payment_info['status'][$timestamp] = $status;
		  $payment_info['total'] = substr($result["transaction"][0]["amount"], 4);
		  $payment_info['currency'] = substr($result["transaction"][0]["amount"], 0, 3);

      if ($mp->get_order($order_id)) {
        $mp->update_order_payment_status($order_id, $status, $paid);
      } else if ($create_order) {
        //succesful payment, create our order now
        $cart = get_transient('mp_order_' . $order_id . '_cart');
			  $shipping_info = get_transient('mp_order_' . $order_id . '_shipping');
			  $shipping_total = get_transient('mp_order_' . $order_id . '_shipping_total');
			  $tax_total = get_transient('mp_order_' . $order_id . '_tax_total');
			  $user_id = get_transient('mp_order_' . $order_id . '_userid');
			  $coupon_code = get_transient('mp_order_' . $order_id . '_coupon');
        $success = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, $paid, $user_id, $shipping_total, $tax_total, $coupon_code);
        
        //if successful delete transients
        if ($success) {
          delete_transient('mp_order_' . $order_id . '_cart');
          delete_transient('mp_order_' . $order_id . '_shipping');
          delete_transient('mp_order_' . $order_id . '_shipping_total');
          delete_transient('mp_order_' . $order_id . '_tax_total');
			  	delete_transient('mp_order_' . $order_id . '_userid');
			  	delete_transient('mp_order_' . $order_id . '_coupon');
        }
      }

		} else {
			// Did not find expected POST variables. Possible access attempt from a non PayPal site.
			//header('Status: 404 Not Found');
			echo 'Error: Missing POST variables. Identification is not possible.';
			exit;
		}
  }
  
  
  /**** PayPal API methods *****/
  
  function decodePayPalIPN($raw_post) {
    if (empty($raw_post)) {
        return array();
    }
    $post = array();
    $pairs = explode('&', $raw_post);
    foreach ($pairs as $pair) {
        list($key, $value) = explode('=', $pair, 2);
        $key = urldecode($key);
        $value = urldecode($value);
        # This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
        preg_match('/(\w+)(?:\[(\d+)\])?(?:\.(\w+))?/', $key, $key_parts);
        switch (count($key_parts)) {
            case 4:
                # Original key format: somekey[x].property
                # Converting to $post[somekey][x][property]
                if (!isset($post[$key_parts[1]])) {
                    $post[$key_parts[1]] = array($key_parts[2] => array($key_parts[3] => $value));
                } else if (!isset($post[$key_parts[1]][$key_parts[2]])) {
                    $post[$key_parts[1]][$key_parts[2]] = array($key_parts[3] => $value);
                } else {
                    $post[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
                }
                break;
            case 3:
                # Original key format: somekey[x]
                # Converting to $post[somkey][x]
                if (!isset($post[$key_parts[1]])) {
                    $post[$key_parts[1]] = array();
                }
                $post[$key_parts[1]][$key_parts[2]] = $value;
                break;
            default:
                # No special format
                $post[$key] = $value;
                break;
        }#switch
    }#foreach

    return $post;
  }

	//Purpose: 	Prepares the parameters for the Pay API Call.
	function Pay($cart, $shipping_info, $order_id) {
    global $mp;
    $settings = get_option('mp_settings');
    $network_settings = get_site_option('mp_network_settings');
    $coupon_code = $mp->get_coupon_code();
    
		$nvpstr = "actionType=PAY";
		$nvpstr .= "&returnUrl=" . $this->returnURL;
		$nvpstr .= "&cancelUrl=" . $this->cancelURL;
		$nvpstr .= "&ipnNotificationUrl=" . $this->ipn_url;
		$nvpstr .= "&currencyCode=" . $this->currencyCode;
		$nvpstr .= "&feesPayer=PRIMARYRECEIVER";
		$nvpstr .= "&trackingId=" . $order_id;
		$nvpstr .= "&memo=" . urlencode(sprintf(__('%s Store Purchase - Order ID: %s', 'mp'), get_bloginfo('name'), $order_id)); //cart name
    
	  //loop through cart items
	  
    foreach ($cart as $product_id => $variations) {
      foreach ($variations as $variation => $data) {
				$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);      
      	$totals[] = $price;
      }
    }
		$total = array_sum($totals);

		//shipping line
    $shipping_tax = 0;
    if ( ($shipping_price = $mp->shipping_price(false)) !== false ) {
			$total += $shipping_price;
			$shipping_tax = ($mp->shipping_tax_price($shipping_price) - $shipping_price);
    }

    //tax line if tax inclusive pricing is off. It it's on it would screw up the totals
    if ( ! $mp->get_setting('tax->tax_inclusive') ) {
    	$tax_price = ($mp->tax_price(false) + $shipping_tax);
			$total += $tax_price;
    }
        
		//calculate fees
    $fee = round( ($network_settings['gateways']['paypal-chained']['percentage'] * 0.01) * $total, 2);
    
    $nvpstr .= "&receiverList.receiver(0).email=" . urlencode($settings['gateways']['paypal-chained']['email']);
		$nvpstr .= "&receiverList.receiver(0).amount=" . round($total, 2);
		$nvpstr .= "&receiverList.receiver(0).invoiceId=" . $order_id;
		$nvpstr .= "&receiverList.receiver(0).paymentType=GOODS";
		$nvpstr .= "&receiverList.receiver(0).primary=true";
		
		$nvpstr .= "&receiverList.receiver(1).email=" . urlencode($network_settings['gateways']['paypal-chained']['email']);
		$nvpstr .= "&receiverList.receiver(1).amount=" . $fee;
		$nvpstr .= "&receiverList.receiver(1).paymentType=SERVICE";
		$nvpstr .= "&receiverList.receiver(1).primary=false";

    //make the call
	  return $this->api_call("Pay", $nvpstr);
	}
	
	//Purpose: 	Prepares the parameters for the Pay API Call.
	function PaymentDetails($paykey) {

		$nvpstr = "payKey=" . urlencode($paykey);

    //make the call
	  return $this->api_call("PaymentDetails", $nvpstr);
	}

	function api_call($methodName, $nvpStr) {
    global $mp;

    //build args
    $args['headers'] = array(
      'X-PAYPAL-SECURITY-USERID' => $this->API_Username,
      'X-PAYPAL-SECURITY-PASSWORD' => $this->API_Password,
      'X-PAYPAL-SECURITY-SIGNATURE' => $this->API_Signature,
      'X-PAYPAL-DEVICE-IPADDRESS' => $_SERVER['REMOTE_ADDR'],
      'X-PAYPAL-REQUEST-DATA-FORMAT' => 'NV',
      'X-PAYPAL-REQUEST-RESPONSE-FORMAT' => 'NV',
      'X-PAYPAL-APPLICATION-ID' => $this->appId
    );
  	$args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | PayPal Chained Payments Plugin/{$mp->version}";
    $args['body'] = $nvpStr . '&requestEnvelope.errorLanguage=en_US';
    $args['sslverify'] = false;
		$args['timeout'] = 60;
    
    //use built in WP http class to work with most server setups
  	$response = wp_remote_post($this->API_Endpoint . $methodName, $args);

  	if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
      $mp->cart_checkout_error( __('There was a problem connecting to PayPal. Please try again.', 'mp') );
      return false;
    } else {
      //convert NVPResponse to an Associative Array
		  $nvpResArray = $this->deformatNVP($response['body']);
		  return $nvpResArray;
    }


	}

	function RedirectToPayPal($token) {
		// Redirect to paypal.com here
		$payPalURL = $this->paypalURL . $token;
    //header("Location: ".$payPalURL);
		wp_redirect($payPalURL);
    exit;
	}

	//This function will take NVPString and convert it to an Associative Array and it will decode the response.
	function deformatNVP($nvpstr) {
		parse_str($nvpstr, $nvpArray);
		return $nvpArray;
	}

}

//only load on multisite
if ( is_multisite() ) {

  //set names here to be able to translate
  if ( is_super_admin() )
    $admin_name = __('PayPal Chained Payments', 'mp');
  else
    $admin_name = __('PayPal', 'mp');

  //register gateway plugin
  mp_register_gateway_plugin( 'MP_Gateway_Paypal_Chained_Payments', 'paypal-chained', $admin_name );
  
  //tie into network settings form
	add_action( 'mp_network_gateway_settings', 'pc_network_gateway_settings_box' );
	
	function pc_network_gateway_settings_box($settings) {
    global $mp;
    ?>
    <script type="text/javascript">
  	  jQuery(document).ready(function($) {
        $("#gbl_gw_paypal-chained, #gw_full_paypal-chained, #gw_supporter_paypal-chained, #gw_none_paypal-chained").change(function() {
          $("#mp-main-form").submit();
    		});
      });
  	</script>
		<?php
		//skip if not enabled
		$hide = false;
    if (($settings['allowed_gateways']['paypal-chained'] != 'full' && $settings['allowed_gateways']['paypal-chained'] != 'supporter' && $settings['global_gateway'] != 'paypal-chained') || $settings['global_cart'])
      $hide = true;

    if (!isset($settings['gateways']['paypal-chained']['msg']))
      $settings['gateways']['paypal-chained']['msg'] = __( 'Please be aware that we will deduct a ?% fee from the total of each transaction in addition to any fees PayPal may charge you. If for any reason you need to refund a customer for an order, please contact us with a screenshot of the refund receipt in your PayPal history as well as the Transaction ID of our fee deduction so we can issue you a refund. Thank you!', 'mp' );
    ?>
    <div id="mp_paypal_adaptive" class="postbox"<?php echo ($hide) ? ' style="display:none;"' : ''; ?>>
      <h3 class='hndle'><span><?php _e('PayPal Chained Payments Settings', 'mp'); ?></span></h3>
      <div class="inside">
        <span class="description"><?php _e('Using PayPal Chained Payments allows you as the multisite network owner to collect a predefined fee or percentage of all sales on network MarketPress stores! This is invisible to the customers who purchase items in a store, and all PayPal fees will be charged to the store owner. To use this option you must create API credentials, and you should make all other gateways unavailable or limited above.', 'mp') ?></span>
        <table class="form-table">
        <tr>
				<th scope="row"><?php _e('Fees To Collect', 'mp'); ?></th>
				<td>
          <span class="description"><?php _e('Enter a percentage of all store sales to collect as a fee. Decimals allowed.', 'mp') ?></span><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['percentage']); ?>" size="3" name="mp[gateways][paypal-chained][percentage]" type="text" />%
				</td>
        </tr>
        <tr>
				<th scope="row"><?php _e('PayPal Email Address', 'mp') ?></th>
				<td>
  				<span class="description"><?php _e('Please enter your PayPal email address or business id you want to recieve fees at.', 'mp') ?></span><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['email']); ?>" size="40" name="mp[gateways][paypal-chained][email]" type="text" />
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e('PayPal API Credentials', 'mp') ?></th>
				<td>
  				<span class="description"><?php _e('You must login to PayPal and create an API signature to get your credentials. <a target="_blank" href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/">Instructions &raquo;</a>', 'mp') ?></span>
          <p><label><?php _e('API Username', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['api_user']); ?>" size="30" name="mp[gateways][paypal-chained][api_user]" type="text" />
          </label></p>
          <p><label><?php _e('API Password', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['api_pass']); ?>" size="20" name="mp[gateways][paypal-chained][api_pass]" type="text" />
          </label></p>
          <p><label><?php _e('Signature', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['api_sig']); ?>" size="70" name="mp[gateways][paypal-chained][api_sig]" type="text" />
          </label></p>
          <span class="description"><?php _e('You must register this application with PayPal using your business account login to get an Application ID that will work with your API credentials. A bit of a hassle, but worth it! In the near future we will be looking for ways to simplify this process. <a target="_blank" href="https://apps.paypal.com/user/my-account/applications">Register then submit your application</a> while logged in to the developer portal.</a> Note that you do not need an Application ID for testing in sandbox mode. <a target="_blank" href="https://developer.paypal.com/docs/classic/lifecycle/goingLive/#register">More Information &raquo;</a>', 'mp') ?><br />
          <a href="<?php echo $mp->plugin_url . 'plugins-gateway/paypal-chained-payments-docs/readme.html'; ?>"><?php _e('View an example form &raquo;', 'mp'); ?></a>
          </span>
          <p><label><?php _e('Application ID', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['app_id']); ?>" size="50" name="mp[gateways][paypal-chained][app_id]" type="text" />
          </label></p>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e('PayPal Sandbox API Credentials', 'mp') ?></th>
				<td>
  				<span class="description"><?php _e('This is neccessary in case you or users want to test checkouts on their stores.', 'mp') ?></span>
          <p><label><?php _e('API Username', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['api_user_sandbox']); ?>" size="30" name="mp[gateways][paypal-chained][api_user_sandbox]" type="text" />
          </label></p>
          <p><label><?php _e('API Password', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['api_pass_sandbox']); ?>" size="20" name="mp[gateways][paypal-chained][api_pass_sandbox]" type="text" />
          </label></p>
          <p><label><?php _e('Signature', 'mp') ?><br />
          <input value="<?php echo esc_attr($settings['gateways']['paypal-chained']['api_sig_sandbox']); ?>" size="70" name="mp[gateways][paypal-chained][api_sig_sandbox]" type="text" />
          </label></p>
        </td>
        </tr>
        <tr>
				<th scope="row"><?php _e('Gateway Settings Page Message', 'mp'); ?></th>
				<td>
				<span class="description"><?php _e('This message is displayed at the top of the gateway settings page to store admins. It\'s a good place to inform them of your fees or put any sales messages. Optional, HTML allowed.', 'mp') ?></span><br />
        <textarea class="mp_msgs_txt" name="mp[gateways][paypal-chained][msg]"><?php echo esc_html($settings['gateways']['paypal-chained']['msg']); ?></textarea>
				</td>
        </tr>
        </table>
      </div>
    </div>
    <?php
  }
}