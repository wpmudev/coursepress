<?php
/*
MarketPress Google Checkout Gateway Plugin
Author: Aaron Edwards
*/
  
class MP_Gateway_GoogleCheckout extends MP_Gateway_API {
	//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
	var $plugin_name = 'google-checkout';
	
	//name of your gateway, for the admin side.
	var $admin_name = '';
  
	//public name of your gateway, for lists and such.
	var $public_name = '';
	
	//url for an image for your checkout method. Displayed on checkout form if set
	var $method_img_url = '';
  
	//url for an submit button image for your checkout method. Displayed on checkout form if set
	var $method_button_img_url = '';
	
	//always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
	var $ipn_url;
	
	//whether if this is the only enabled gateway it can skip the payment_form step
	var $skip_form = true;
	
	//Google cart
	var $googleCart;
	
	//API response
	var $response;
	
	//Response array
	var $results  = array();
	var $approved;
	var $declined;
	var $error;
	var $method;

	//api vars
	var $server_type, $API_Merchant_id, $API_Merchant_key, $version, $currencyCode, $API_URL;
	
	
	/**
	* Runs when your class is instantiated. Use to setup your plugin instead of __construct()
	* Sets up the google cart
	*/
	function on_creation() {
		global $mp;
		$settings = get_option('mp_settings');
		
		//set names here to be able to translate
		$this->admin_name = __('Google Checkout', 'mp');
		$this->public_name = __('Google Checkout', 'mp');
		$this->method_img_url = $mp->plugin_url . 'images/google_checkout.gif';
    $locale = ($this->currencyCode == 'USD') ? 'en_US' : 'en_GB';
		/*
		require_once($mp->plugin_dir .'plugins-gateway/google-checkout-library/googlecart.php');
		require_once($mp->plugin_dir .'plugins-gateway/google-checkout-library/googleitem.php');
		require_once($mp->plugin_dir .'plugins-gateway/google-checkout-library/googleresponse.php');
		require_once($mp->plugin_dir .'plugins-gateway/google-checkout-library/googlemerchantcalculations.php');
		require_once($mp->plugin_dir .'plugins-gateway/google-checkout-library/googleresult.php');
		require_once($mp->plugin_dir .'plugins-gateway/google-checkout-library/googlerequest.php');
		*/
		
		if (isset($settings['gateways']['google-checkout'] ) ) {
			$this->API_Merchant_id = $settings['gateways']['google-checkout']['merchant_id'];
			$this->API_Merchant_key = $settings['gateways']['google-checkout']['merchant_key'];
			$this->server_type = $settings['gateways']['google-checkout']['server_type'];
			$this->currencyCode = $settings['gateways']['google-checkout']['currency'];
			
			if(strtolower($this->server_type) == "sandbox") {
				$this->API_URL = "https://sandbox.google.com/checkout/";
				$this->method_button_img_url = "http://checkout.google.com/buttons/checkout.gif?merchant_id={$this->API_Merchant_id}&w=180&h=46&style=trans&variant=text&loc=$locale";
			} else {
				$this->API_URL=  "https://checkout.google.com/";
				$this->method_button_img_url = "http://sandbox.google.com/checkout/buttons/checkout.gif?merchant_id={$this->API_Merchant_id}&w=180&h=46&style=trans&variant=text&loc=$locale";
			}
		}
		
	}
	
	/**
	* Use this to process any fields you added. Use the $_POST global,
	* and be sure to save it to both the $_SESSION and usermeta if logged in.
	* DO NOT save credit card details to usermeta as it's not PCI compliant.
	* Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	* it will redirect to the next step.
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function process_payment_form($cart, $shipping_info) {
		global $mp;
	}
	
	/**
    * Echo fields you need to add to the payment screen, like your credit card info fields
    *
    * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
    * @param array $shipping_info. Contains shipping info and email in case you need it
    */
	function payment_form($cart, $shipping_info) {
		global $mp;
		if (isset($_GET['googlecheckout_cancel'])) {
		  echo '<div class="mp_checkout_error">' . __('Your Google Checkout transaction has been canceled.', 'mp') . '</div>';
		}
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
		$timestamp = time();
		$settings = get_option('mp_settings');
		
		$url = $this->API_URL . "api/checkout/v2/merchantCheckoutForm/Merchant/" . $this->API_Merchant_id;
		$order_id = $mp->generate_order_id();
		
		$params = array();
		$params['_type'] = 'checkout-shopping-cart';
		$params['shopping-cart.merchant-private-data'] = $order_id;
		$params['checkout-flow-support.merchant-checkout-flow-support.edit-cart-url'] = mp_cart_link(false, true);
		$params["checkout-flow-support.merchant-checkout-flow-support.continue-shopping-url"] = mp_store_link(false, true);

    $params["checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.shipping-taxed"] = ($settings['tax']['tax_shipping']) ? 'true' : 'false';
    $params["checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.tax-areas.world-area-1"] = '';

		$totals = array();
		$item_params = array();
		$i = 1;
		$items = 0;
		$coupon_code = $mp->get_coupon_code();
		
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);			
				$totals[] = $price;
		    $item_params["shopping-cart.items.item-{$i}.item-name"] = $data['name'];
				$item_params["shopping-cart.items.item-{$i}.item-description"] = $data['url'];
				$item_params["shopping-cart.items.item-{$i}.unit-price"] = $price;
				$item_params["shopping-cart.items.item-{$i}.unit-price.currency"] = $this->currencyCode;
				$item_params["shopping-cart.items.item-{$i}.quantity"] = $data['quantity'];
				$item_params["shopping-cart.items.item-{$i}.merchant-item-id"] = $data['SKU'];
				$i++;
				$items++;
			}
		}
		
		$total = array_sum($totals);
    $params = array_merge($params, $item_params);

		//shipping line
    $shipping_tax = 0;
    if ( ($shipping_price = $mp->shipping_price(false)) !== false ) {
			$total += $shipping_price;
			$shipping_tax = ($mp->shipping_tax_price($shipping_price) - $shipping_price);
			$params["checkout-flow-support.merchant-checkout-flow-support.shipping-methods.flat-rate-shipping-1.price"] = $shipping_price;
			$params["checkout-flow-support.merchant-checkout-flow-support.shipping-methods.flat-rate-shipping-1.price.currency"] = $this->currencyCode;
			$params["checkout-flow-support.merchant-checkout-flow-support.shipping-methods.flat-rate-shipping-1.name"] = __('Standard Shipping', 'mp');			
    }

    //tax line if tax inclusive pricing is off. It it's on it would screw up the totals
    if ( ! $mp->get_setting('tax->tax_inclusive') ) {
    	$tax_price = ($mp->tax_price(false) + $shipping_tax);
			$total += $tax_price;
     	$params["checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.rate"] = $tax_price;			
    } else {
      $params["checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.rate"] = '0.00';	    
    }

		$param_list = array();
		foreach ($params as $k => $v) {
			$param_list[] = "{$k}=".rawurlencode($v);
		}

		$param_str = implode('&', $param_list);
    
		//setup transients for ipn in case checkout doesn't redirect (ipn should come within 12 hrs!)
		set_transient('mp_order_'. $order_id . '_cart', $cart, 60*60*12);
		set_transient('mp_order_'. $order_id . '_shipping', $shipping_info, 60*60*12);
		set_transient('mp_order_'. $order_id . '_userid', $current_user->ID, 60*60*12);
		
		$response = $this->google_api_request($param_str, $url);
		if ($response['_type'] == 'checkout-redirect') {
      wp_redirect($response['redirect-url']);
			exit;
		} else {
			$mp->cart_checkout_error( sprintf(__('There was a problem setting up your purchase with Google Checkout. Please try again or <a href="%s">select a different payment method</a>.<br/>%s', 'mp'), mp_checkout_step_url('checkout'), @$response['error-message']) );
		}
	}
	
	function google_api_request($param_str, $url) {
		global $mp;
		$args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | Google Checkout Payment Plugin/{$mp->version}";
		$args['body'] = $param_str;
		$args['timeout'] = 30;
		$args['sslverify'] = false;
    $args['headers']['Authorization'] = 'Basic ' . base64_encode($this->API_Merchant_id.':'.$this->API_Merchant_key);
    $args['headers']['Content-Type'] = 'application/xml;charset=UTF-8';
    $args['headers']['Accept'] = 'application/xml;charset=UTF-8';
		    
    //use built in WP http class to work with most server setups
    $response = wp_remote_post($url, $args);
		if (is_array($response) && isset($response['body'])) {
      parse_str($response['body'], $final_response);
      return $final_response;
    } else {
			return false;
    }
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
  	return '<img src="' . $this->method_button_img_url . '" alt="'.__('Pay via Google Checkout', 'mp').'" />';
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
		return $content;
	}
  
	/**
   * Runs before page load incase you need to run any scripts before loading the success message page
   */
	function order_confirmation($order) {
		global $mp;
	}
	
	
	/**
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
  function gateway_settings_box($settings) {
    global $mp;
    ?>
    <div id="mp_google_checkout" class="postbox">
      <h3 class='handle'><span><?php _e('Google Checkout Settings', 'mp'); ?></span></h3>
      <div class="inside">
        <span class="description"><?php _e('Resell your inventory via Google Checkout', 'mp') ?></span>
        <table class="form-table">
				  <tr>
				    <th scope="row"><?php _e('Mode', 'mp') ?></th>
				    <td>
			        <p>
			          <select name="mp[gateways][google-checkout][server_type]">
								<?php
								$server_types = array(
									"sandbox" => 'Sandbox',
									"live" => 'Live'
								);
								foreach ($server_types as $k => $v) {
								  echo '<option value="' . $k . '"' . ($k == $settings['gateways']['google-checkout']['server_type'] ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
								}
								?>
			          </select>
			        </p>
				    </td>
				  </tr>
				  <tr>
				    <th scope="row"><?php _e('Google Checkout Credentials', 'mp') ?></th>
				    <td>
			        <span class="description"><?php print sprintf(__('You must login to Google Checkout to obtain your merchant ID and merchant key. <a target="_blank" href="%s">Instructions &raquo;</a>', 'mp'), "http://code.google.com/apis/checkout/developer/Google_Checkout_Basic_HTML_Signing_Up.html"); ?></span>
				      <p>
							<label><?php _e('Merchant ID', 'mp') ?><br />
							  <input value="<?php echo esc_attr($settings['gateways']['google-checkout']['merchant_id']); ?>" size="30" name="mp[gateways][google-checkout][merchant_id]" type="text" />
							</label>
				      </p>
				      <p>
							<label><?php _e('Merchant Key', 'mp') ?><br />
							  <input value="<?php echo esc_attr($settings['gateways']['google-checkout']['merchant_key']); ?>" size="30" name="mp[gateways][google-checkout][merchant_key]" type="text" />
							</label>
				      </p>
				    </td>
				  </tr>
					<tr>
				    <th scope="row"><?php _e('Google Checkout API callback URL', 'mp') ?></th>
				    <td>
				    <span>
				    <span class="description"><?php _e('You must setup your API callback URL in Google Checkout to be able to process orders.', 'mp') ?></span>
          	<ul>
							<li><?php _e('Login to the Integration page of your Merchant Center <a target="_blank" href="https://sandbox.google.com/checkout/sell/settings?section=Integration">sandbox</a> or <a target="_blank" href="https://checkout.google.com/sell/settings?section=Integration">production</a> account. (You must set it in each)', 'mp') ?></li>
							<li><?php printf( __('Enter the URL for the web service in the <b>API callback URL</b> field: <strong>%s</strong>', 'mp'), $this->ipn_url); ?></li>
							<li><?php _e('Indicate the format as "<b>Notification as Serial Number</b>" and use <b>API Version 2.0</b>.', 'mp') ?></li>
							<li><?php _e('Save your settings.', 'mp') ?></li>
						</td>
				  </tr>
	          <tr valign="top">
	        <th scope="row"><?php _e('Google Checkout Currency', 'mp') ?></th>
	        <td>
	          <select name="mp[gateways][google-checkout][currency]">
	          <?php
	          $sel_currency = ($settings['gateways']['google-checkout']['currency']) ? $settings['gateways']['google-checkout']['currency'] : $settings['currency'];
	          $currencies = array(
							"USD" => 'USD - U.S. Dollar',
							"GBP" => 'GBP - British Pound'
	          );

	          foreach ($currencies as $k => $v) {
	              echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
	          }
	          ?>
	          </select>
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
   * IPN and payment return
   */
  function process_ipn_return() {
    global $mp;
    $settings = get_option('mp_settings');

    if (isset($_POST['serial-number'])) {

	    $url = $this->API_URL . "api/checkout/v2/reportsForm/Merchant/" . $this->API_Merchant_id;
			$param_str = '_type=notification-history-request&serial-number=' . urlencode($_POST['serial-number']);
			$response = $this->google_api_request($param_str, $url);

			if (!isset($response['_type'])) {
				header('HTTP/1.0 403 Forbidden');
				exit('We were unable to authenticate the request');
      }

		  $timestamp = time();
      $order_id = $response['google-order-number'];
			$payment_status = (isset($response['financial-order-state'])) ? $response['financial-order-state'] : $response['new-financial-order-state'];

			if ($payment_status) {
			
	      //setup status
	      switch ($payment_status) {

					case 'REVIEWING':
	          $status = __('Reviewing - Google Checkout is reviewing the customer and order to see if it can be charged.', 'mp');
	          $paid = false;
						break;

					case 'CHARGEABLE':
						$status = __('Chargeable - You can now charge the order in your Google Checkout account.', 'mp');
	          $paid = false;
						break;

					case 'PROCESSING':
						$status = __('Processing - Google Checkout is processing your charge request.', 'mp');
	          $paid = false;
						break;

					case 'CHARGED':
						$status = __('Charged - The payment has been completed, and the funds have been added successfully to your Google Checkout account balance.', 'mp');
	          $paid = true;
						break;

					case 'CHARGING':
						$status = __('Charging - The credit card is being charged.', 'mp');
	          $paid = false;
						break;
						
					case 'CANCELED':
	          $status = __('Cancelled - The order was cancelled.', 'mp');
	          $paid = false;
						break;

					default:
						// case: various error cases
						$status = $payment_status;
						$paid = false;
				}

	      //status's are stored as an array with unix timestamp as key
			  $payment_info['status'][$timestamp] = $status;

	      if ($mp->get_order($order_id)) {
	        $mp->update_order_payment_status($order_id, $status, $paid);
	        //marked shipped
		      if ($response['new-fulfillment-order-state'] == 'DELIVERED') {
		        $mp->update_order_status($order_id, 'shipped');
		      }
	      } else if ($response['_type'] == 'new-order-notification') {
	        //setup our payment details
				  $payment_info['gateway_public_name'] = $this->public_name;
		      $payment_info['gateway_private_name'] = $this->admin_name;
				  $payment_info['method'] = __('Credit Card', 'mp');
				  $payment_info['transaction_id'] = $order_id;
				  $payment_info['total'] = $response['order-total'];
				  $payment_info['currency'] = $response['order-total_currency'];
				  
          $temp_id = $response['shopping-cart_merchant-private-data'];
          
	        //succesful payment, create our order now
	        $cart = get_transient('mp_order_' . $temp_id . '_cart');
			  	$shipping_info = get_transient('mp_order_' . $temp_id . '_shipping');
				  $user_id = get_transient('mp_order_' . $temp_id . '_userid');
				  
				  /*
				  //get shipping info
				  $shipping_info['email'] = $response['buyer-shipping-address_email'];
				  $shipping_info['name'] = $response['buyer-shipping-address_contact-name'];
				  $shipping_info['address1'] = $response['buyer-shipping-address_address1'];
				  $shipping_info['address2'] = $response['buyer-shipping-address_address2'];
				  $shipping_info['city'] = $response['buyer-shipping-address_city'];
				  $shipping_info['state'] = $response['buyer-shipping-address_region'];
				  $shipping_info['zip'] = $response['buyer-shipping-address_postal-code'];
				  $shipping_info['country'] = $response['buyer-shipping-address_country-code'];
				  $shipping_info['phone'] = $response['buyer-shipping-address_phone'];
				  */
	        $success = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, $paid, $user_id);

					//if successful delete transients
	        if ($success) {
	          delete_transient('mp_order_' . $temp_id . '_cart');
        		delete_transient('mp_order_' . $temp_id . '_shipping');
				  	delete_transient('mp_order_' . $temp_id . '_userid');
	        }
	      }
	      
			}
			
      //if we get this far return success so ipns don't get resent
      header('HTTP/1.0 200 OK');
			die('<notification-acknowledgment xmlns="http://checkout.google.com/schema/2" serial-number="' . $_POST['serial-number'] . '" />');
    } else {
      header('HTTP/1.0 403 Forbidden');
			exit('Invalid request');
		}
  }
}

//register payment gateway plugin
//mp_register_gateway_plugin( 'MP_Gateway_GoogleCheckout', 'google-checkout', __('Google Checkout', 'mp') );