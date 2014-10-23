<?php
/*
MarketPress Manual Payments Gateway Plugin
Author: Aaron Edwards (Incsub)
*/

class MP_Gateway_ManualPayments extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'manual-payments';
  
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
  var $skip_form = false;
  
  /****** Below are the public methods you may overwrite via a plugin ******/

  /**
   * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   */
  function on_creation() {
		global $mp;

		//set names here to be able to translate
		$this->admin_name = __('Manual Payments', 'mp');
		$this->public_name = $mp->get_setting('gateways->manual-payments->name') ? $mp->get_setting('gateways->manual-payments->name', __('Manual Payment', 'mp')) : __('Manual Payment', 'mp');

    $this->method_img_url = $mp->plugin_url . 'images/manual-payment.png';
	}

  /**
   * Return fields you need to add to the payment screen, like your credit card info fields
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function payment_form($cart, $shipping_info) {
    global $mp;
    return do_shortcode($mp->get_setting('gateways->manual-payments->instructions'));
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
	  $timestamp = time();
	  
    $totals = array();
    $coupon_code = $mp->get_coupon_code();
    
    foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
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
    
		$order_id = $mp->generate_order_id();

    $payment_info['gateway_public_name'] = $this->public_name;
    $payment_info['gateway_private_name'] = $this->admin_name;
    $payment_info['status'][$timestamp] = __('Invoiced', 'mp');
    $payment_info['total'] = $total;
    $payment_info['currency'] = $mp->get_setting('currency');
	  $payment_info['method'] = __('Manual/Invoice', 'mp');
	  //$payment_info['transaction_id'] = $order_id;
	  
    //create our order now
    $result = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, false);
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
	  
	  if ($mp->get_setting('gateways->manual-payments->email'))
		  $msg = $mp->filter_email($order, $mp->get_setting('gateways->manual-payments->email'));
		  
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
    
    return $content . str_replace( 'TOTAL', $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']), $mp->get_setting('gateways->manual-payments->confirmation') );
  }
	
	/**
   * Echo a settings meta box with whatever settings you need for you gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box($settings) {
    global $mp;
    ?>
    <div id="mp_manual_payments" class="postbox mp-pages-msgs">
    	<h3 class='handle'><span><?php _e('Manual Payments Settings', 'mp'); ?></span></h3>
      <div class="inside">
	      <span class="description"><?php _e('Record payments manually, such as by Cash, Check, or EFT.', 'mp') ?></span>
	      <table class="form-table">
		      <tr>
						<th scope="row"><label for="manual-payments-name"><?php _e('Method Name', 'mp') ?></label></th>
						<td>
		  				<span class="description"><?php _e('Enter a public name for this payment method that is displayed to users - No HTML', 'mp') ?></span>
		          <p>
		          <input value="<?php echo esc_attr($mp->get_setting('gateways->manual-payments->name') ? $mp->get_setting('gateways->manual-payments->name', __('Manual Payment', 'mp')) : __('Manual Payment', 'mp')); ?>" style="width: 100%;" name="mp[gateways][manual-payments][name]" id="manual-payments-name" type="text" />
		          </p>
		        </td>
	        </tr>
		      <tr>
		        <th scope="row"><label for="manual-payments-instructions"><?php _e('User Instructions', 'mp') ?></label></th>
		        <td>
		        <span class="description"><?php _e('These are the manual payment instructions to display on the payments screen - HTML allowed', 'mp') ?></span>
	          <p>
							<?php wp_editor( $mp->get_setting('gateways->manual-payments->instructions'), 'manualpaymentsinstructions', array('textarea_name'=>'mp[gateways][manual-payments][instructions]') ); ?>
						</p>
	        	</td>
	        </tr>
	        <tr>
		        <th scope="row"><label for="manual-payments-confirmation"><?php _e('Confirmation User Instructions', 'mp') ?></label></th>
		        <td>
		        <span class="description"><?php _e('These are the manual payment instructions to display on the order confirmation screen. TOTAL will be replaced with the order total. - HTML allowed', 'mp') ?></span>
	          <?php wp_editor( $mp->get_setting('gateways->manual-payments->confirmation'), 'manualpaymentsconfirmation', array('textarea_name'=>'mp[gateways][manual-payments][confirmation]') ); ?> 
	        	</td>
	        </tr>
	        <tr>
		        <th scope="row"><label for="manual-payments-email"><?php _e('Order Confirmation Email', 'mp') ?></label></th>
		        <td>
		        <span class="description"><?php _e('This is the email text to send to those who have made manual payment checkouts. You should include your manual payment instructions here. It overrides the default order checkout email. These codes will be replaced with order details: CUSTOMERNAME, ORDERID, ORDERINFO, SHIPPINGINFO, PAYMENTINFO, TOTAL, TRACKINGURL. No HTML allowed.', 'mp') ?></span>
	          <p>
	            <textarea id="manual-payments-email" name="mp[gateways][manual-payments][email]" class="mp_emails_txt"><?php echo esc_textarea($mp->get_setting('gateways->manual-payments->email', $mp->get_setting('email->new_order_txt'))); ?></textarea>
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
	function process_gateway_settings($settings) {
		
		if ( isset( $settings['gateways']['manual-payments'] ) && !is_array( $settings['gateways']['manual-payments'] ) )
			return $settings;
		
		//strip slashes
    $settings['gateways']['manual-payments'] = array_map('stripslashes', (array)$settings['gateways']['manual-payments']);
		
		//no html in public name
  	$settings['gateways']['manual-payments']['name'] = stripslashes(wp_filter_nohtml_kses($settings['gateways']['manual-payments']['name']));
  	
		//filter html if needed
		if (!current_user_can('unfiltered_html')) {
			$settings['gateways']['manual-payments']['instructions'] = wp_filter_post_kses($settings['gateways']['manual-payments']['instructions']);
			$settings['gateways']['manual-payments']['confirmation'] = wp_filter_post_kses($settings['gateways']['manual-payments']['confirmation']);
		}
		
		$settings['gateways']['manual-payments']['instructions'] = wpautop($settings['gateways']['manual-payments']['instructions']);
		$settings['gateways']['manual-payments']['confirmation'] = wpautop($settings['gateways']['manual-payments']['confirmation']);
		
		//no html in email
  	$settings['gateways']['manual-payments']['email'] = stripslashes(wp_filter_nohtml_kses($settings['gateways']['manual-payments']['email']));	

    return $settings;
  }
  
	/**
   * Use to handle any payment returns to the ipn_url. Do not display anything here. If you encounter errors
   *  return the proper headers. Exits after.
   */
	function process_ipn_return() {

  }
}

mp_register_gateway_plugin( 'MP_Gateway_ManualPayments', 'manual-payments', __('Manual Payments', 'mp') );