<?php
/*
MarketPress eWay Gateway Plugin
Author: Aaron Edwards (Incsub)
*/

class MP_Gateway_eWay_Shared extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'eway';

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


  /****** Below are the public methods you may overwrite via a plugin ******/

  /**
   * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   */
  function on_creation() {
    global $mp;
    $settings = get_option('mp_settings');

    //set names here to be able to translate
    $this->admin_name = __('eWay Shared Payments', 'mp');
    $this->public_name = __('Credit Card', 'mp');

    $this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
    $this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';
 
    $this->returnURL = mp_checkout_step_url('confirmation');
  	$this->cancelURL = mp_checkout_step_url('checkout') . "?eway-cancel=1";

    //set api urls
  	if ($settings['gateways']['eway']['mode'] == 'sandbox')	{
  		$this->CustomerID = '87654321';
  		$this->UserName = 'TestAccount';
  	} else {
  		$this->CustomerID = $settings['gateways']['eway']['CustomerID'];
  		$this->UserName = $settings['gateways']['eway']['UserName'];
    }
  }

/**
   * Return fields you need to add to the payment screen, like your credit card info fields
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function payment_form($cart, $shipping_info) {
    global $mp;
    if (isset($_GET['eway-cancel'])) {
      echo '<div class="mp_checkout_error">' . __('Your eWay transaction has been canceled.', 'mp') . '</div>';
    }
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
    return '<img src="'.$mp->plugin_url . 'images/ewaylogo.png" border="0" alt="'.__('Checkout with eWay', 'mp').'">';
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
    
    $order_id = $mp->generate_order_id();
    
    $params = array();
		$params['CustomerID'] = $this->CustomerID;
  	$params['UserName'] = $this->UserName;
		$params['MerchantInvoice'] = $order_id;
		$params['MerchantReference'] = $order_id;
		$params['Currency'] = $settings['gateways']['eway']['Currency'];
		$params['Language'] = $settings['gateways']['eway']['Language'];
		$params['ReturnURL'] = $this->returnURL;
		$params['CancelURL'] = $this->cancelURL;
		$params['ModifiableCustomerDetails'] = 'false';
		$params['InvoiceDescription'] = sprintf(__('%s Store Purchase - Order ID: %s', 'mp'), get_bloginfo('name'), $order_id); //cart name
		
		if (!empty($settings['gateways']['eway']['CompanyName']))
			$params['CompanyName'] = $settings['gateways']['eway']['CompanyName'];
			
		if (!empty($settings['gateways']['eway']['PageTitle']))
			$params['PageTitle'] = $settings['gateways']['eway']['PageTitle'];
			
		if (!empty($settings['gateways']['eway']['PageDescription']))
			$params['PageDescription'] = $settings['gateways']['eway']['PageDescription'];
			
		if (!empty($settings['gateways']['eway']['PageFooter']))
			$params['PageFooter'] = $settings['gateways']['eway']['PageFooter'];
			
		if (!empty($settings['gateways']['eway']['CompanyLogo']))
			$params['CompanyLogo'] = $settings['gateways']['eway']['CompanyLogo'];
			
		if (!empty($settings['gateways']['eway']['PageBanner']))
			$params['PageBanner'] = $settings['gateways']['eway']['PageBanner'];
		
		$params['CustomerEmail'] = $shipping_info['email'];
		
		//add shipping info if set
		if (!$mp->download_only_cart($cart) && $mp->get_setting('shipping->method') != 'none' && isset($shipping_info['name'])) {	
			$names = explode(' ', $shipping_info['name']);
			$params['CustomerFirstName'] = $names[0];
			$params['CustomerLastName'] = $names[count($names)-1]; //grab last name
			$params['CustomerAddress'] = $shipping_info['address1'];
			if (!empty($shipping_info['address2']))
				$params['CustomerAddress'] = $params['CustomerAddress'] . " " . $shipping_info['address2'];
			$params['CustomerPhone'] = $shipping_info['phone'];
			$params['CustomerPostCode'] = $shipping_info['zip'];
			$params['CustomerCity'] = $shipping_info['city'];
			$params['CustomerState'] = $shipping_info['state'];
			$params['CustomerCountry'] = $shipping_info['country'];
		}
    
    $totals = array();
		$product_count = 0;
		$coupon_code = $mp->get_coupon_code();
		
    foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);			
				$totals[] = $price;
				$product_count++;
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
				
    $params['Amount'] = number_format( round( $total, 2 ), 2, '.', '');
    
    $result = $this->api_call('https://au.ewaygateway.com/Request', $params);
		
		if ($result) {
			libxml_use_internal_errors(true);
			$xml = new SimpleXMLElement($result);
			if (!$xml) {
				$mp->cart_checkout_error( __('There was a problem parsing the response from eWay. Please try again.', 'mp') );
				return false;
			}

			if ($xml->Result == 'True') {
				wp_redirect($xml->URI);
				exit;
			} else {
				$mp->cart_checkout_error( sprintf(__('There was a problem setting up the transaction with eWay: %s', 'mp'), $xml->Error) );
				return false;
			}
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
    $content = '';
		
		if (!$order)
			return '<p><a href="'.mp_checkout_step_url('confirm-checkout').'">' . __('Please go back and try again.', 'mp') . '</a></p>';
		
    if ($order->post_status == 'order_received') {
      $content .= '<p>' . sprintf(__('Your payment via eWay for this order totaling %s is in progress. Here is the latest status:', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
      $statuses = $order->mp_payment_info['status'];
      krsort($statuses); //sort with latest status at the top
      $status = reset($statuses);
      $timestamp = key($statuses);
      $content .= '<p><strong>' . $mp->format_date($timestamp) . ':</strong> ' . esc_html($status) . '</p>';
    } else {
      $content .= '<p>' . sprintf(__('Your payment for this order totaling %s is complete. The transaction number is <strong>%s</strong>.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']), $order->mp_payment_info['transaction_id']) . '</p>';
    }
    return $content;
  }
  
  /**
   * Runs before page load incase you need to run any scripts before loading the success message page
   */
	function order_confirmation($order) {
    global $mp;
    
		if (isset($_POST['AccessPaymentCode'])) {
			$settings = get_option('mp_settings');
			$params = array();
			$params['CustomerID'] = $this->CustomerID;
			$params['UserName'] = $this->UserName;
			$params['AccessPaymentCode'] = $_POST['AccessPaymentCode'];
			
			$result = $this->api_call('https://au.ewaygateway.com/Result', $params);
			if ($result) {
				libxml_use_internal_errors(true);
				$xml = new SimpleXMLElement($result);
				if (!$xml) {
					$mp->cart_checkout_error( __('There was a problem parsing the response from eWay. Please try again.', 'mp') );
					return false;
				}

				if ($xml->TrxnStatus == 'True') {	
					$status = __('Received - The order has been received, awaiting payment confirmation.', 'mp');
					//setup our payment details
					$payment_info['gateway_public_name'] = $this->public_name;
					$payment_info['gateway_private_name'] = $this->admin_name;
					$payment_info['method'] = __('Credit Card', 'mp');
					$payment_info['transaction_id'] = (string)$xml->TrxnNumber;
					$timestamp = time();
					$payment_info['status'][$timestamp] = sprintf(__('Paid - The card has been processed - %s', 'mp'), (string)$xml->TrxnResponseMessage);
					$payment_info['total'] = (string)$xml->ReturnAmount;
					$payment_info['currency'] = $settings['gateways']['eway']['Currency'];
					
					$order = $mp->create_order($_SESSION['mp_order'], $mp->get_cart_contents(), $_SESSION['mp_shipping_info'], $payment_info, true);
				} else {
					$mp->cart_checkout_error( sprintf(__('There was a problem with your credit card information: %s', 'mp'), $xml->TrxnResponseMessage) );
					wp_redirect($this->cancelURL);
					exit;
				}
			}
		}
  }

	/**
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box($settings) {
    global $mp;
    ?>
    <div id="mp_paypal_express" class="postbox">
      <h3 class='hndle'><span><?php _e('eWay Shared Payments Settings', 'mp'); ?></span></h3>
      <div class="inside">
        <span class="description"><?php _e('The Hosted Page is a webpage hosted on eWAY\'s side eliminating the need for merchants to capture, transmit or store credit card numbers. At the checkout time the merchant automatically redirects the customer to the Hosted Page where they would enter their details and have the transaction processed. Upon completion of the transaction the customer is redirected back to the MarketPress checkout confirmation page.', 'mp') ?></span>
        <table class="form-table">
          <tr>
	        <th scope="row"><?php _e('eWay Currency', 'mp') ?></th>
	        <td>
	          <select name="mp[gateways][eway][Currency]">
	          <?php
	          $sel_currency = ($settings['gateways']['eway']['Currency']) ? $settings['gateways']['eway']['Currency'] : $settings['currency'];
	          $currencies = array(
	              'NZD' => 'NZD - New Zealand Dollar',
	              'AUD' => 'AUD - Australian Dollar',
	              'CAD' => 'CAD - Canadian Dollar',
	              'EUR' => 'EUR - Euro',
	              'GBP' => 'GBP - Pound Sterling',
	              'HKD' => 'HKD - Hong Kong Dollar',
	              'JPY' => 'JPY - Japanese Yen',
	              'SGD' => 'SGD - Singapore Dollar',
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
					<th scope="row"><?php _e('Gateway Mode', 'mp') ?></th>
					<td>
					<span class="description"><?php _e('Note when testing in sandbox mode it will use the default eWay test API credentials. You must also test in AUD currency as that is what the sandbox account is in.', 'mp') ?> <a href="http://www.eway.com.au/Developer/Testing/" target="_blank"><?php _e('It is important that you read and follow the testing instructions &raquo;', 'mp') ?></a></span><br />
					<select name="mp[gateways][eway][mode]">
	          <option value="sandbox"<?php selected($settings['gateways']['eway']['mode'], 'sandbox') ?>><?php _e('Sandbox', 'mp') ?></option>
	          <option value="live"<?php selected($settings['gateways']['eway']['mode'], 'live') ?>><?php _e('Live', 'mp') ?></option>
	        </select>
					</td>
	        </tr>
	        <tr>
					<th scope="row"><?php _e('Live API Credentials', 'mp') ?></th>
					<td>
	  				<p><label><?php _e('eWay Username', 'mp') ?><br />
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['UserName']); ?>" size="30" name="mp[gateways][eway][UserName]" type="text" />
	          </label></p>
	          <p><label><?php _e('Customer ID', 'mp') ?><br />
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['CustomerID']); ?>" size="20" name="mp[gateways][eway][CustomerID]" type="text" />
	          </label></p>
	        </td>
	        </tr>
					<tr>
  				<th scope="row"><?php _e('eWay Hosted Payment Page Language', 'mp') ?></th>
  				<td>
            <select name="mp[gateways][eway][Language]">
            <?php
            $sel_locale = ($settings['gateways']['eway']['Language']) ? $settings['gateways']['eway']['Language'] : 'EN';
            $locales = array(
              'EN'	=> 'English',
              'ES'	=> 'Spanish',
              'FR'	=> 'French',
              'DE'	=> 'German',
              'NL'	=> 'Dutch'
            );
            foreach ($locales as $k => $v) {
                echo '		<option value="' . $k . '"' . ($k == $sel_locale ? ' selected' : '') . '>' . $v . '</option>' . "\n";
            }
            ?>
            </select>
  				</td>
          </tr>
					<tr>
					<th scope="row"><?php _e('Company Name', 'mp') ?></th>
					<td>
	  				<span class="description"><?php _e('This will be displayed as the company the customer is purchasing from, including this is highly recommended.', 'mp') ?></span>
	          <p>
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['CompanyName']); ?>" size="50" name="mp[gateways][eway][CompanyName]" type="text" />
	          </p>
	        </td>
	        </tr>
					<th scope="row"><?php _e('Page Title (optional)', 'mp') ?></th>
					<td>
	  				<span class="description"><?php _e('This value is used to populate the browsers title bar at the top of the screen.', 'mp') ?></span>
	          <p>
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['PageTitle']); ?>" size="50" name="mp[gateways][eway][PageTitle]" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr>
					<th scope="row"><?php _e('Page Description (optional)', 'mp') ?></th>
					<td>
	  				<span class="description"><?php _e('This value is used to populate the browsers title bar at the top of the screen.', 'mp') ?></span>
	          <p>
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['PageDescription']); ?>" size="150" name="mp[gateways][eway][PageDescription]" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr>
					<th scope="row"><?php _e('Page Footer (optional)', 'mp') ?></th>
					<td>
	  				<span class="description"><?php _e('The page footer text can be customised and populated below the customer\'s order details. Useful for contact information.', 'mp') ?></span>
	          <p>
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['PageFooter']); ?>" size="100" name="mp[gateways][eway][PageFooter]" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr>
					<th scope="row"><?php _e('Company Logo (optional)', 'mp') ?></th>
					<td>
	  				<span class="description"><?php _e('The url of the image can be hosted on your website and pass the secure https:// path of the image to be displayed at the top of the website. This is the second image block on the webpage and is restricted to 960px X 65px. A default secure image is used if none is supplied.', 'mp') ?></span>
	          <p>
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['CompanyLogo']); ?>" size="80" name="mp[gateways][eway][CompanyLogo]" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr>
					<th scope="row"><?php _e('Page Banner Image (optional)', 'mp') ?></th>
					<td>
	  				<span class="description"><?php _e('The url of the image can be hosted on your website and pass the secure https:// path of the image to be displayed at the top of the website. This is the second image block on the webpage and is restricted to 960px X 65px. A default secure image is used if none is supplied.', 'mp') ?></span>
	          <p>
	          <input value="<?php echo esc_attr($settings['gateways']['eway']['PageBanner']); ?>" size="80" name="mp[gateways][eway][PageBanner]" type="text" />
	          </p>
	        </td>
	        </tr>
	        <tr>
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
  }

	function api_call($url, $fields) {
	  global $mp;

	  $param_list = array();
    foreach ($fields as $k => $v) {
      $param_list[] = "{$k}=".rawurlencode($v);
    }

    $url .= '?' . implode('&', $param_list);
		
	  //build args
	  $args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | eWay Shared Payments Gateway/{$mp->version}";
	  $args['sslverify'] = false;
	  $args['timeout'] = 60;

	  //use built in WP http class to work with most server setups
	  $response = wp_remote_get($url, $args);
	  if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
	    $mp->cart_checkout_error( __('There was a problem connecting to eWay. Please try again.', 'mp') );
	    return false;
	  } else {
	    return $response['body'];
	  }
	}


}

//register gateway only if SimpleXML module installed
if (class_exists("SimpleXMLElement"))
	mp_register_gateway_plugin( 'MP_Gateway_eWay_Shared', 'eway', __('eWay Shared Payments', 'mp') );