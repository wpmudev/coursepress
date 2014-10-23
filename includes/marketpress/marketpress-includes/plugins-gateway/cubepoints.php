<?php
/*
MarketPress CubePoints Plugin
Requires the CubePoints plugin: http://wordpress.org/extend/plugins/cubepoints/
Author: David Mallonee (Incsub)
*/

class MP_Gateway_CubePoints extends MP_Gateway_API {

  //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'cubepoints';

  //name of your gateway, for the admin side.
  var $admin_name = 'CubePoints';

  //public name of your gateway, for lists and such.
  var $public_name = 'CubePoints';

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
		$settings = get_option('mp_settings');

		//set names here to be able to translate
		$this->admin_name = __('CubePoints', 'mp');
		$this->public_name = (!empty($settings['gateways']['cubepoints']['name'])) ? $settings['gateways']['cubepoints']['name'] : __('CubePoints', 'mp');

    $this->method_img_url = $mp->plugin_url . 'images/cubepoints.png';
	}

  /**
   * Return fields you need to add to the payment screen, like your credit card info fields
   *
   * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
   * @param array $shipping_info. Contains shipping info and email in case you need it
   */
  function payment_form($cart, $shipping_info) {
    global $mp;
    $settings = get_option('mp_settings');
    return $settings['gateways']['cubepoints']['instructions'];
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
			
		$uid = cp_currentUser();
		return '<div id="mp_cp_points">' . __('Your current points: ', 'mp') . cp_getPoints ( cp_currentUser() ) . '</div>';
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
    $settings = get_option('mp_settings');
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
				
	  //get CubePoints user
	  $uid = cp_currentUser();
	  //test for CubePoints amount
	  if ( cp_getPoints ( cp_currentUser() ) >= $total ) {
						
			//subtract $total from user's CubePoints
			cp_points( 'custom', $uid, -$total, sprintf(__('%s Store Purchase', 'mp'), get_bloginfo('name')) );
			
			//create MarketPress order
			$order_id = $mp->generate_order_id();
			$payment_info['gateway_public_name'] = $this->public_name;
			$payment_info['gateway_private_name'] = $this->admin_name;
			$payment_info['status'][$timestamp] = __("Paid", 'mp');
			$payment_info['total'] = $total;
			$payment_info['currency'] = $settings['currency'];
			$payment_info['method'] = __('CubePoints', 'mp');
			$payment_info['transaction_id'] = $order_id;
			$paid = true;
			//create our order now
			$result = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, $paid);
	  } else {
		//insuffient CubePoints
		$mp->cart_checkout_error( sprintf(__('Sorry, but you do not appear to have enough points to complete this purchase!', 'mp'), mp_checkout_step_url('checkout')) );
	}
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

	  if (isset($settings['gateways']['cubepoints']['email']))
		  $msg = $mp->filter_email($order, $settings['gateways']['cubepoints']['email']);
		else
		  $msg = $settings['email']['new_order_txt'];

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
    $settings = get_option('mp_settings');

		$uid = cp_currentUser();
		$cp_points = '<div id="mp_cp_points">' . __('Your current points: ', 'mp') . cp_getPoints ( cp_currentUser() ) . '</div>';
    return $cp_points . $content . str_replace( 'TOTAL', $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']), $settings['gateways']['cubepoints']['confirmation'] );
  }

	/**
   * Echo a settings meta box with whatever settings you need for your gateway.
   *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function gateway_settings_box($settings) {
    global $mp;
    $settings = get_option('mp_settings');
		if (empty($settings['gateways']['cubepoints']['name']))
		  $settings['gateways']['cubepoints']['name'] = __('CubePoints', 'mp');

		if (!isset($settings['gateways']['cubepoints']['email']))
		  $settings['gateways']['cubepoints']['email'] = $settings['email']['new_order_txt'];

    ?>
    <div id="mp_cubepoints_payments" class="postbox mp-pages-msgs">
    	<h3 class='handle'><span><?php _e('CubePoints Settings', 'mp'); ?></span></h3>
      <div class="inside">
	      <span class="description"><?php _e('Accept CubePoints as payment(requires the CubePoints plugin).', 'mp') ?></span>
	      <table class="form-table">
		      <tr>
						<th scope="row"><label for="cubepoints-name"><?php _e('Method Name', 'mp') ?></label></th>
						<td>
		  				<span class="description"><?php _e('Enter a public name for this payment method that is displayed to users - No HTML', 'mp') ?></span>
		          <p>
		          <input value="<?php echo esc_attr($settings['gateways']['cubepoints']['name']); ?>" style="width: 100%;" name="mp[gateways][cubepoints][name]" id="cubepoints-name" type="text" />
		          </p>
		        </td>
	        </tr>
		      <tr>
		        <th scope="row"><label for="cubepoints-instructions"><?php _e('User Instructions', 'mp') ?></label></th>
		        <td>
		        <span class="description"><?php _e('These are the CubePoints instructions to display on the payments screen - HTML allowed', 'mp') ?></span>
	          <p>
	            <textarea id="cubepoints-instructions" name="mp[gateways][cubepoints][instructions]" class="mp_msgs_txt"><?php echo esc_textarea($settings['gateways']['cubepoints']['instructions']); ?></textarea>
	          </p>
	        	</td>
	        </tr>
	        <tr>
		        <th scope="row"><label for="cubepoints-confirmation"><?php _e('Confirmation User Instructions', 'mp') ?></label></th>
		        <td>
		        <span class="description"><?php _e('These are the CubePoints to display on the order confirmation screen. TOTAL will be replaced with the order total. - HTML allowed', 'mp') ?></span>
	          <p>
	            <textarea id="cubepoints-confirmation" name="mp[gateways][cubepoints][confirmation]" class="mp_msgs_txt"><?php echo esc_textarea($settings['gateways']['cubepoints']['confirmation']); ?></textarea>
	          </p>
	        	</td>
	        </tr>
	        <tr>
		        <th scope="row"><label for="cubepoints-email"><?php _e('Order Confirmation Email', 'mp') ?></label></th>
		        <td>
		        <span class="description"><?php _e('This is the email text to send to those who have made CubePoints checkouts. You should include your CubePoints instructions here. It overrides the default order checkout email. These codes will be replaced with order details: CUSTOMERNAME, ORDERID, ORDERINFO, SHIPPINGINFO, PAYMENTINFO, TOTAL, TRACKINGURL. No HTML allowed.', 'mp') ?></span>
	          <p>
	            <textarea id="cubepoints-email" name="mp[gateways][cubepoints][email]" class="mp_emails_txt"><?php echo esc_textarea($settings['gateways']['cubepoints']['email']); ?></textarea>
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

		//no html in public name
  	$settings['gateways']['cubepoints']['name'] = stripslashes(wp_filter_nohtml_kses($settings['gateways']['cubepoints']['name']));

		//filter html if needed
		if (!current_user_can('unfiltered_html')) {
			$settings['gateways']['cubepoints']['instructions'] = wp_filter_post_kses($settings['gateways']['cubepoints']['instructions']);
			$settings['gateways']['cubepoints']['confirmation'] = wp_filter_post_kses($settings['gateways']['cubepoints']['confirmation']);
		}

		//no html in email
  	$settings['gateways']['cubepoints']['email'] = stripslashes(wp_filter_nohtml_kses($settings['gateways']['cubepoints']['email']));

    return $settings;
  }

	/**
   * Use to handle any payment returns to the ipn_url. Do not display anything here. If you encounter errors
   *  return the proper headers. Exits after.
   */
	function process_ipn_return() {

  }
}

if (function_exists( 'cp_ready' ) ) {
	mp_register_gateway_plugin( 'MP_Gateway_CubePoints', 'cubepoints', __('CubePoints', 'mp') );
}