<?php
/*
MarketPress iDeal Gateway Plugin
Author: Remi Schouten
*/

class MP_Gateway_IDeal extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'ideal';
  
  //name of your gateway, for the admin side.
  var $admin_name = '';
  
  //public name of your gateway, for lists and such.
  var $public_name = '';
  
  //url for an image for your checkout method. Displayed on method form
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

		//set names here to be able to translate
		$this->admin_name = __('iDEAL (beta)', 'mp');
		$this->public_name = __('iDEAL', 'mp');

    	$this->method_img_url = $mp->plugin_url . 'images/ideal.png';
		$this->method_button_img_url = $mp->plugin_url . 'images/ideal.png';
		$this->merchant_id = $mp->get_setting('gateways->ideal->merchant_id');
		$this->ideal_hash = $mp->get_setting('gateways->ideal->ideal_hash');
		$this->returnURL = mp_checkout_step_url('confirm-checkout');
  		$this->cancelURL = mp_checkout_step_url('checkout') . "?cancel=1";
		$this->errorURL = mp_checkout_step_url('checkout') . "?err=1";
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
			echo '<div class="mp_checkout_error">' . __('Your iDEAL transaction has been canceled.', 'mp') . '</div>';

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
		
		$key = $this->ideal_hash; // Your hashkey or Secret key
		$merchantID = $this->merchant_id; //Your merchant ID
		$subID = '0'; //Almost always 0
		$purchaseID = $mp->generate_order_id(); //Order ID
		$paymentType = 'ideal'; //Always ideal
		$validUntil = date('Y-m-d\TG:i:s\Z', strtotime('+1 hour'));
		$coupon_code = $mp->get_coupon_code();
		
		$i = 1;
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				//we're sending tax included prices here isf tax included is on, to avoid rounding errors
				$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);
				$totals[] = $price;
				$items[] = array(
					'itemNumber'.$i => empty($data['SKU']) ? $product_id : substr($data['SKU'], 0, 12), // Article number
					'itemDescription'.$i => substr($data['name'], 0, 32), // Description
					'itemQuantity'.$i => $data['quantity'], // Quantity
					'itemPrice'.$i =>  round($price*100) // Artikel price in cents
				);
				$i++;
			}
		}
		$total = array_sum($totals);

		//shipping line
    $shipping_tax = 0;
    if ( ($shipping_price = $mp->shipping_price(false)) !== false ) {
			$total += $shipping_price;
			$shipping_tax = ($mp->shipping_tax_price($shipping_price) - $shipping_price);
			
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
			
			//Add tax as separate product
			$items[] = array(
				'itemNumber'.$i => '99999999', // Product number
				'itemDescription'.$i => __('Tax', 'mp'), // Description
				'itemQuantity'.$i => 1, // Quantity
				'itemPrice'.$i => round($tax_price*100)  // Product price in cents
			);
    }
	
		$total = round($total * 100);
		$shastring = "$key$merchantID$subID$total$purchaseID$paymentType$validUntil";
		
		$i = 1;
		foreach ($items as $item){
			$shastring .= $item['itemNumber'.$i].$item['itemDescription'.$i].$item['itemQuantity'.$i].$item['itemPrice'.$i];	
			$i++;
		}
		
		// Remove HTML Entities
		$shastring = html_entity_decode($shastring);

		// Remove space characters: "\t", "\n", "\r" and " "
		$shastring = str_replace(array("\t", "\n", "\r", " "), '', $shastring);

		// Generate hash
		$shasign = sha1($shastring);

		// Other variables not part of the hash
		$language = 'nl'; # preferred '' for consistent texts, also quicker
		$currency = 'EUR';
		$description = substr( sprintf(__('%s Store Purchase - Order ID: %s', 'mp'), get_bloginfo('name'), $order_id), 0, 32 );
		$urlSuccess = $this->returnURL;
		$urlCancel = $this->cancelURL;
		$urlError = $this->errorURL;
		
		//setup bank specific urls
		$test = ($mp->get_setting('gateways->ideal->mode', 'test') == 'test');
		if ($mp->get_setting('gateways->ideal->bank') == 'ing')
			$redirectURL = 'https://ideal' . ($test ? 'test' : '') . '.secure-ing.com/ideal/mpiPayInitIng.do?';
		else if ($mp->get_setting('gateways->ideal->bank') == 'rabo')
			$redirectURL = 'https://ideal' . ($test ? 'test' : '') . '.rabobank.nl/ideal/mpiPayInitRabo.do?';
		else if ($mp->get_setting('gateways->ideal->bank') == 'fries')
			$redirectURL = 'https://' . ($test ? 'test' : '') . 'idealkassa.frieslandbank.nl/ideal/mpiPayInitFriesland.do?';
		else if ($mp->get_setting('gateways->ideal->bank') == 'abn')
			$redirectURL = 'https://abnamro' . ($test ? '-test' : '') . '.ideal-payment.de/ideal/mpiPayInitFortis.do?';
		else
			$redirectURL = 'https://www.ideal-simulator.nl/lite/?';
			
		$redirectURL .= 'merchantID='.$merchantID;
		$redirectURL .='&subID='.$subID;
		$redirectURL .='&amount='.$total;
		$redirectURL .='&purchaseID='.$purchaseID;
		$redirectURL .='&language='.$language;
		$redirectURL .='&currency='.$currency;
		$redirectURL .='&description='.urlencode($description);
		$redirectURL .='&hash='.$shasign;
		$redirectURL .='&paymentType='.$paymentType;
		$redirectURL .='&validUntil='.$validUntil;
		
		$i = 1;
		foreach ($items as $item) {
			$redirectURL .= '&itemNumber'.$i.'='.$item['itemNumber'.$i].'&itemDescription'.$i.'='.$item['itemDescription'.$i].'&itemQuantity'.$i.'='.$item['itemQuantity'.$i].'&itemPrice'.$i.'='.$item['itemPrice'.$i];	
			$i++;
		}
	
		$redirectURL .='&urlSuccess='.urlencode($urlSuccess);
		$redirectURL .='&urlCancel='.urlencode($urlCancel);
		$redirectURL .='&urlError='.urlencode($urlError);
		
		//echo $redirectURL;
		wp_redirect($redirectURL);
		
		/*
		//create form
		$form = '
			<form method="post" action="https://ideal.secure-ing.com/ideal/mpiPayInitIng.do" name="form1">
			<!-- Vergeet na het uitvoeren van de testen niet de url in de ACTION te veranderen naar de productie-omgeving -->
			<input type="hidden" name="merchantID" value="'.$merchantID.'">
			<input type="hidden" name="subID" value="'.$subID.'">
			<input type="hidden" name="amount" value="'.$total.'">
			<input type="hidden" name="purchaseID" value="'.$purchaseID.'">
			<input type="hidden" name="language" value="'.$language.'">
			<input type="hidden" name="currency" value="'.$currency.'">
			<input type="hidden" name="description" value="'.$description.'">
			<input type="hidden" name="hash" value="'.$shasign.'">
			<input type="hidden" name="paymentType" value="'.$paymentType.'">
			<input type="hidden" name="validUntil" value="'.$validUntil.'">';
		
		//Add all products to the form
		$i = 1;
		foreach ($items as $item){
			$form .= '<input type="hidden" name="itemNumber'.$i.'" value="'.$item['itemNumber'.$i].'" />
			<input type="hidden" name="itemDescription'.$i.'" value="'.$item['itemDescription'.$i].'" />
			<input type="hidden" name="itemQuantity'.$i.'" value="'.$item['itemQuantity'.$i].'" />
			<input type="hidden" name="itemPrice'.$i.'" value="'.$item['itemPrice'.$i].'" />';	
			$i++;
		}		
		$form .= '
			<input type="hidden" name="urlSuccess" value="'.$urlSuccess.'" />
			<input type="hidden" name="urlCancel" value="'.$urlCancel.'" />
			<input type="hidden" name="urlError" value="'.$urlError.'" />
			<input type="submit" name="submit2" value="Verstuur" />
			</form>';
		echo $form;*/
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
		$timestamp = time();
	  
		$totals = array();
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				$totals[] = $mp->before_tax_price($data['price'], $product_id) * $data['quantity'];
			}
		}
		$total = array_sum($totals);
	
		if ( $coupon = $mp->coupon_value($mp->get_coupon_code(), $total) ) {
			$total = $coupon['new_total'];
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
		$payment_info['currency'] = $mp->get_setting('currency');
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
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box($settings) {
    global $mp;
    ?>
    <div class="postbox">
    	<h3 class='handle'><span><?php _e('iDEAL Settings', 'mp'); ?></span></h3>
      <div class="inside">
				<a href="http://www.ideal.nl/?lang=eng-GB" target="_blank"><img src="<?php echo $mp->plugin_url . 'images/ideal.png'; ?>" /></a>
        
	      <p class="description"><?php _e('To make it easier to pay for online products and services, the Dutch banking community has developed the iDEAL online payment method. iDEAL allows online payments to be made using online banking in EUR only.', 'mp') ?></p>
	      <table class="form-table">
					<tr>
					<th scope="row"><?php _e('Bank', 'mp') ?></th>
					<td>
					<select name="mp[gateways][ideal][bank]">
	          <option value="ing"<?php selected($mp->get_setting('gateways->ideal->bank'), 'ing') ?>><?php _e('ING Bank', 'mp') ?></option>
	          <option value="rabo"<?php selected($mp->get_setting('gateways->ideal->bank'), 'rabo') ?>><?php _e('Rabobank', 'mp') ?></option>
	          <option value="fries"<?php selected($mp->get_setting('gateways->ideal->bank'), 'fries') ?>><?php _e('Friesland Bank', 'mp') ?></option>
	          <option value="abn"<?php selected($mp->get_setting('gateways->ideal->bank'), 'abn') ?>><?php _e('ABN Amro Bank', 'mp') ?></option>
	          <option value="sim"<?php selected($mp->get_setting('gateways->ideal->bank'), 'sim') ?>><?php _e('iDEAL Simulator (for testing)', 'mp') ?></option>
	        </select>
					</td>
	        </tr>
		     <tr>
				  <th scope="row"><label for="ideal-key"><?php _e('Merchant ID', 'mp') ?></label></th>
				  <td>	  		 
		          <input value="<?php echo esc_attr($mp->get_setting('gateways->ideal->merchant_id')); ?>" name="mp[gateways][ideal][merchant_id]" id="merchant_id" type="text" />   
		        </td>
	         </tr>
					<tr>
				  <th scope="row"><label for="ideal-hash"><?php _e('iDEAL Secret Key', 'mp') ?></label></th>
				  <td>
						<input value="<?php echo esc_attr($mp->get_setting('gateways->ideal->ideal_hash')); ?>" name="mp[gateways][ideal][ideal_hash]" id="ideal_hash" type="text" />
					</td>
				 </tr>
					<tr>
					<th scope="row"><?php _e('Mode', 'mp') ?></th>
					<td>
					<select name="mp[gateways][ideal][mode]">
	          <option value="test"<?php selected($mp->get_setting('gateways->ideal->mode', 'test'), 'test') ?>><?php _e('Testing', 'mp') ?></option>
	          <option value="live"<?php selected($mp->get_setting('gateways->ideal->mode'), 'live') ?>><?php _e('Live', 'mp') ?></option>
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
   * Use to handle any payment returns to the ipn_url. Do not display anything here. If you encounter errors
   *  return the proper headers. Exits after.
   */
	function process_ipn_return() {

  }
}

mp_register_gateway_plugin( 'MP_Gateway_IDeal', 'ideal', __('iDEAL (beta)', 'mp') );