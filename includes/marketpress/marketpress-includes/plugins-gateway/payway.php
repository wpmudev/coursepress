<?php
/*
MarketPress Payway Gateway Plugin
Author: Mindblaze(Rashid Ali)
*/

class MP_Gateway_PayWay extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'payway';
  
  //name of your gateway, for the admin side.
  var $admin_name = 'payway';
  
  //public name of your gateway, for lists and such.
  var $public_name = 'payway';
  
  //url for an image for your checkout method. Displayed on method form
  var $method_img_url = '';

  //url for an submit button image for your checkout method. Displayed on checkout form if set
  var $method_button_img_url = '';
  
  //whether or not ssl is needed for checkout page
  var $force_ssl = true;
  
  //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
  var $ipn_url;
  
	//whether if this is the only enabled gateway it can skip the payment_form step
  var $skip_form = true;
  
  /****** Below are the public methods you may overwrite via a plugin ******/

  /**
   * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   */
  function on_creation() {
		global $mp;
		$settings = get_option('mp_settings');
		//set names here to be able to translate
		$this->admin_name = __('PayWay', 'mp');
		$this->public_name = __('PayWay', 'mp');
		$this->method_img_url 			= 	$mp->plugin_url . 'images/ideal.png';
		$this->method_button_img_url 	=	$mp->plugin_url . 'images/ideal.png';
		$this->merchant_id 				= $settings['gateways']['payway']['merchant_id'];
		$this->ideal_hash 				= $settings['gateways']['payway']['ideal_hash'];
		$this->returnURL = mp_checkout_step_url('confirm-checkout');
		$this->cancelURL = mp_checkout_step_url('checkout') . "?cancel=1";
		$this->errorURL	 = mp_checkout_step_url('checkout') . "?err=1";
		
	}
	
  /**
   * Return fields you need to add to the payment screen, like your credit card info fields
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function payment_form($cart, $shipping_info) {
    global $mp;
		if (isset($_GET['cancel']))
			echo '<div class="mp_checkout_error">' . __('Your PayWay transaction has been canceled.', 'mp') . '</div>';
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
		global $mp;
		$settings = get_option('mp_settings');

		$order_id = $mp->generate_order_id();
		$parameters = array();
		$parameters['username'] = $settings["gateways"]["payway"]["_USERNAME_"];
		$parameters['password'] = $settings["gateways"]["payway"]["_PASSWORD_"];
		$parameters['biller_code'] = $settings["gateways"]["payway"]["_BILLER_CODE_"];
		$parameters['merchant_id'] = $settings["gateways"]["payway"]["merchantId"];
		$parameters['paypal_email'] = $paypalEmail;
		$parameters['payment_reference'] = $order_id;
		$parameters['payment_reference_change'] = 'false';
		$parameters['surcharge_rates'] = 'VI/MC=0.0,AX=1.5,DC=1.5';
		
		$i = 1;
		$coupon_code = $mp->get_coupon_code();
		
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				$price = $mp->coupon_value_product($coupon_code, $data['price'], $product_id);			
				$items[] = array(
					'itemNumber'.$i => $data['SKU'], // Article number
					'itemDescription'.$i => $data['name'], // Description
					'itemQuantity'.$i => $data['quantity'], // Quantity
					'itemPrice'.$i =>  round($price*100) // Article price in cents
				);
				if ( $data['quantity'] != 0 && $data['quantity'] != null )
				{
					$parameters[$data['name']] = $data['quantity'] . ',' . $price / $data['quantity'];
				}
				$i++;
				$totals[] = $price * $data['quantity'];
			}
		}
		$total = array_sum($totals);

		//shipping line
    $shipping_tax = 0;
    if ( ($shipping_price = $mp->shipping_price(false)) !== false ) {
			$total += $shipping_price;
			$shipping_tax = ($mp->shipping_tax_price($shipping_price) - $shipping_price);
			
			$parameters["Shipping"] = '1,'.$shipping_price;
			//Add shipping as separate product
			$items[] = array(
				'itemNumber'.$i => '99999998', // Product number
				'itemDescription'.$i => __('Shipping', 'mp'), // Description
				'itemQuantity'.$i => 1, // Quantity
				'itemPrice'.$i => round($shipping_price*100) // Product price in cents
			);
			$i++;
    }

    //tax line if tax inclusive pricing is off. It it's on it would screw up the totals
    if ( ! $mp->get_setting('tax->tax_inclusive') ) {
    	$tax_price = ($mp->tax_price(false) + $shipping_tax);
			$total += $tax_price;
			
			if ( ! empty($tax_price) )
				$parameters["Tax"] = '1,'.$tax_price;
				
			//Add tax as separate product
			$items[] = array(
				'itemNumber'.$i => '99999999', // Product number
				'itemDescription'.$i => __('Tax', 'mp'), // Description
				'itemQuantity'.$i => 1, // Quantity
				'itemPrice'.$i => round($tax_price*100)  // Product price in cents
			);			
    }
    				
		$total = round($total * 100);

		// Hand-off to the PayWay payment page
		
		$token 			= getToken( $parameters );
		$payWayBaseUrl 	= 'https://www.payway.com.au/';
		if ( $TAILORED )
			{
				$_SESSION['token'] = $token;
				$handOffUrl = './enterCCDetails.php?';
			}
			else
			{
				$handOffUrl = $payWayBaseUrl . "MakePayment?";
			}
			
			
		//setup transients for ipn in case checkout doesn't redirect (ipn should come within 12 hrs!)
		set_transient('mp_order_'. $order_id . '_cart', $cart, 60*60*12);
		set_transient('mp_order_'. $order_id . '_shipping', $shipping_info, 60*60*12);
		set_transient('mp_order_'. $order_id . '_userid', $current_user->ID, 60*60*12);
		
		$handOffUrl = $handOffUrl . "biller_code=" .
		$parameters['biller_code'] . "&token=" . urlencode( $token );
		debugLog( "Hand-off URL: " . $handOffUrl );
		session_write_close();
		wp_redirect($handOffUrl);
		exit;
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
		$settings 	= get_option('mp_settings');
		$timestamp 	= time();
		$totals 	= array();
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				$totals[] = $mp->before_tax_price($data['price'], $product_id) * $data['quantity'];
			}
		}
		$total = array_sum($totals);
		if ( $coupon 	= $mp->coupon_value($mp->get_coupon_code(), $total) ) {
			$total 		= $coupon['new_total'];
		}
		//shipping line
		if ( ($shipping_price = $mp->shipping_price()) !== false ) {
			$total = $total + $shipping_price;
		}
		//tax line
		if ( ($tax_price = $mp->tax_price()) !== false ) {
			$total = $total + $tax_price;
		}
		$payment_info['gateway_public_name'] = $this->public_name;
		$payment_info['gateway_private_name'] = $this->admin_name;
		$payment_info['status'][$timestamp] = __('Invoiced', 'mp');
		$payment_info['total'] = $total;
		$payment_info['currency'] = $settings['currency'];
		$payment_info['method'] = __('iDEAL', 'mp');
		$payment_info['transaction_id'] = $order_id;
		//create our order now
		$result = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, false);
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
		global $mp;
	}
	
  /**
   * Runs before page load incase you need to run any scripts before loading the success message page
   */
	function order_confirmation($order) { 
  }
	/**
   * Filters the order confirmation email message body. You may want to append something to
   *  the message. Optional
   *
   * Don't forget to return!
   */
	function order_confirmation_email($msg, $order) {
    global $mp;
	$settings = get_option('mp_settings');
    return $msg;
  }
  
  /**
   * Return any html you want to show on the confirmation screen after checkout. This
   * should be a payment details box and message.
   *
   * Don't forget to return!
   */
	function order_confirmation_msg($content, $order) {
    global $mp;
    $settings = get_option('mp_settings');
    return $content;
  }
	
	/**
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box($settings) {
    global $mp;
    ?>
    <div class="postbox">
    	<h3 class='handle'><span><?php _e('Payway Settings', 'mp'); ?></span></h3>
      <div class="inside">
	      <table class="form-table">
		     <tr>
				  <th scope="row"><label for="encryptionKey"><?php _e('Encryption Key', 'mp') ?></label></th>
				  <td>	  		 
		          <input value="<?php echo esc_attr($settings['gateways']['payway']['encryptionKey']); ?>" name="mp[gateways][payway][encryptionKey]" id="encryptionKey" type="text" />   
				</td>
	         </tr>
			  <tr>
				  <th scope="row"><label for="_LOG_DIR_"><?php _e('Log Directory', 'mp') ?></label></th>
				  <td>	  		 
					  <input value="<?php echo esc_attr($settings['gateways']['payway']['_LOG_DIR_']); ?>" name="mp[gateways][payway][_LOG_DIR_]" id="_LOG_DIR_" type="text" />   
				  </td>
			  </tr>
			  <tr>
				  <th scope="row"><label for="_BILLER_CODE_"><?php _e('Biller Code', 'mp') ?></label></th>
				  <td>	  		 
					  <input value="<?php echo esc_attr($settings['gateways']['payway']['_BILLER_CODE_']); ?>" name="mp[gateways][payway][_BILLER_CODE_]" id="_BILLER_CODE_" type="text" />   
				  </td>
			  </tr>			  
			  <tr>
				  <th scope="row"><label for="_USERNAME_"><?php _e('User Name', 'mp') ?></label></th>
				  <td>	  		 
					  <input value="<?php echo esc_attr($settings['gateways']['payway']['_USERNAME_']); ?>" name="mp[gateways][payway][_USERNAME_]" id="_USERNAME_" type="text" />   
				  </td>
			  </tr>			  
			  <tr>
				  <th scope="row"><label for="_PASSWORD_"><?php _e('Password', 'mp') ?></label></th>
				  <td>	  		 
					  <input value="<?php echo esc_attr($settings['gateways']['payway']['_PASSWORD_']); ?>" name="mp[gateways][payway][_PASSWORD_]" id="_PASSWORD_" type="text" />   
				  </td>
			  </tr>			  
			  <tr>
				  <th scope="row"><label for="_CA_FILE_"><?php _e('Full path of the cacerts.crt file', 'mp') ?></label></th>
				  <td>	  		 
					  <input value="<?php echo esc_attr($settings['gateways']['payway']['_CA_FILE_']); ?>" name="mp[gateways][payway][_CA_FILE_]" id="_CA_FILE_" type="text" />   
				  </td>
			  </tr>			  
			  <tr>
				  <th scope="row"><label for="merchantId"><?php _e('Merchant ID', 'mp') ?></label></th>
				  <td>	  		 
					  <input value="<?php echo esc_attr($settings['gateways']['payway']['merchantId']); ?>" name="mp[gateways][payway][merchantId]" id="merchantId" type="text" />   
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
   * Use to handle any payment returns to the ipn_url. Do not display anything here. If you encounter errors
   *  return the proper headers. Exits after.
   */
	function process_ipn_return() {
		global $mp;
		$settings 				= get_option('mp_settings');
		$encryptedParametersText = $_GET['EncryptedParameters'];
		$signatureText 	=	$_GET['Signature'];
		$settings 		= 	get_option('mp_settings');
		$encryptionKey	=	esc_attr($settings['gateways']['payway']['encryptionKey']);
		$parameters 	= 	decrypt_parameters( $encryptionKey, $encryptedParametersText, $signatureText );
		$data			=	$encryptionKey .' : '. $encryptedParametersText.' : '. $signatureText;
		foreach($parameters as $key=>$value)
		{
			$data	.= $key.'	=>'.$value.'\r'; 	
		}
		$myFile	 = "ipnpostadata.txt";
		$fh		 = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $data);
		fclose($fh);
		
		if (empty($parameters)) {
			header('HTTP/1.0 403 Forbidden');
			exit('We were unable to authenticate the request');
		}
		/*
		if ($parameters['remote_ip'] != '202.166.167.58') {
			header('HTTP/1.0 403 Forbidden');
			exit('Invalid request');
		}
		*/
		if (isset($parameters['payment_number'])) {
			//setup our payment details
			$payment_info['gateway_public_name'] 	= $this->public_name;
			$payment_info['gateway_private_name'] 	= $this->admin_name;
			$payment_info['method'] 				= isset($parameters['card_type']) ? $parameters['card_type'] : __('PayWay balance, Credit Card, or Instant Transfer', 'mp');
			$payment_info['transaction_id'] 		= $parameters['payment_number']; 
			$timestamp 	= time();
			$order_id 	= $parameters['payment_number'];
			//setup status
			switch ($parameters['summary_code']) {
				case '0':
				$status = __('Processed - The payment has been completed, and the funds have been added successfully to your Moneybookers account balance.', 'mp');
				$create_order = true;
				$paid = true;
				break;				
				case '1':
				$status = __('Cancelled - The payment was cancelled manually by the sender in their online account history or was auto-cancelled after 14 days pending.', 'mp');
				$create_order = false;
				$paid = false;
				break;
				
				case '2':
				$status = __('Failed - A payment was rejected due to an error.', 'mp');
				$create_order = false;
				$paid = false;
				break;
				
				case '3':
				$status = __('Rejected - A payment was rejected.', 'mp');
				$create_order = false;
				$paid = false;
				break;
				
				default:
				// case: various error cases
				$create_order = false;
				$paid = false;
			}
			
			//status's are stored as an array with unix timestamp as key
			$payment_info['status'][$timestamp] = $status;
			$payment_info['total'] 				= $payment_amount['amount'];
			$payment_info['currency'] 			= '$';
			
			if ($mp->get_order($order_id)) {
				$mp->update_order_payment_status($order_id, $status, $paid);
			} else if ($create_order) {
				//succesful payment, create our order now
				$cart = get_transient('mp_order_' . $order_id . '_cart');
				$shipping_info = get_transient('mp_order_' . $order_id . '_shipping');
				$user_id = get_transient('mp_order_' . $order_id . '_userid');
				$success = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, $paid, $user_id);
				
				//if successful delete transients
				if ($success) {
					delete_transient('mp_order_' . $order_id . '_cart');
					delete_transient('mp_order_' . $order_id . '_shipping');
					delete_transient('mp_order_' . $order_id . '_userid');
				}
			}
			
			//if we get this far return success so ipns don't get resent
			header('HTTP/1.0 200 OK');
			exit('Successfully recieved!');
		} else {
			header('HTTP/1.0 403 Forbidden');
			exit('Invalid request');
		}
	}
}

function getToken( $parameters ) {
	$payWayUrl = 'https://www.payway.com.au/';
	
	
	// Build the parameters string to pass to PayWay
	$parametersString = '';
	$init = true;
	foreach ( $parameters as $paramName => $paramValue )
	{
		if ( $init )
		{
			$init = false;
		}
		else
		{
			$parametersString = $parametersString . '&';
		}  
		$parametersString = $parametersString . urlencode($paramName) . '=' . urlencode($paramValue);
	}
	

	$args	=	array();
	$args['body'] = $parametersString;
	$args['sslverify'] = false;
	$args['timeout'] = 60;
	$args['method'] = 'POST';
	$args['httpversion'] ='1.0';
	$args['body'] 			= $parameters;
	$response			=	wp_remote_post( $payWayUrl . "RequestToken", $args);
	$responseText		=	$response["body"];
	
	debugLog( "Token Request POST: " . $parametersString );

	
	debugLog( "Token Response: " . $responseText );
	
	// Split the response into parameters
	$responseParameterArray = explode( "&", $responseText );
	$responseParameters 	= array();
	foreach ( $responseParameterArray as $responseParameter )
	{
		list( $paramName, $paramValue ) = explode( "=", $responseParameter, 2 );
		$responseParameters[ $paramName ] = $paramValue;
	}
	
	if ( array_key_exists( 'error', $responseParameters ) )
	{
		trigger_error( "Error getting token: " . $responseParameters['error'] );
	}        
	else
	{
		return $responseParameters['token'];
	}
}

function debugLog( $message ) {
	return;
	global $logDir;
	list($usec, $sec) = explode(" ", microtime());
	$dtime = date( "Y-m-d H:i:s." . sprintf( "%03d", (int)(1000 * $usec) ), $sec );
	$filename = $logDir . "/" . "net_" . date( "Ymd" ) . ".log";
	$fp = fopen( $filename, "a" ); 
	fputs( $fp, $dtime . ' ' . $message . "\r\n" ); 
	fclose( $fp );
}


//mp_register_gateway_plugin( 'MP_Gateway_PayWay', 'payway', __('PayWay (beta)', 'mp') );