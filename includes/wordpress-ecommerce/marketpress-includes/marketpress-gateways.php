<?php
/*
MarketPress Payment Gateway Plugin Base Class
*/
if(!class_exists('MP_Gateway_API')) {

  class MP_Gateway_API {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = '';
    
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
    
    //only required for global capable gateways. The maximum stores that can checkout at once
    var $max_stores = 1;
    
    /****** Below are the public methods you may overwrite via a plugin ******/

		function network_settings_save( $settings ) {
			return $settings;
		}
		
    /**
     * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
     */
    function on_creation() {
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
      wp_die( __("You must override the process_payment_form() method in your {$this->admin_name} payment gateway plugin!", 'mp') );
    }
    
    /**
     * Return the chosen payment details here for final confirmation. You probably don't need
     *  to post anything in the form as it should be in your $_SESSION var already.
     *
     * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
     * @param array $shipping_info. Contains shipping info and email in case you need it
     */
		function confirm_payment_form($cart, $shipping_info) {
      wp_die( __("You must override the confirm_payment_form() method in your {$this->admin_name} payment gateway plugin!", 'mp') );
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
      wp_die( __("You must override the process_payment() method in your {$this->admin_name} payment gateway plugin!", 'mp') );
    }

    /**
     * Runs before page load incase you need to run any scripts before loading the success message page
     */
		function order_confirmation($order) {
      wp_die( __("You must override the order_confirmation() method in your {$this->admin_name} payment gateway plugin!", 'mp') );
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
      wp_die( __("You must override the order_confirmation_msg() method in your {$this->admin_name} payment gateway plugin!", 'mp') );
    }
		
		/**
     * Echo a settings meta box with whatever settings you need for you gateway.
     *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
     *  You can access saved settings via $settings array.
     */
		function gateway_settings_box($settings) {

    }
    
    /**
     * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
     *  array. Don't forget to return!
     */
		function process_gateway_settings($settings) {
      return $settings;
    }

    /**
     * Filters posted data from your network settings form. Do anything you need to the $settings['gateways']['plugin_name']
     *  array. Don't forget to return!
     */
    function proces_network_gateway_settings( $settings ) {
	    return $settings;
    }
    
		/**
     * Use to handle any payment returns to the ipn_url. Do not display anything here. If you encounter errors
     *  return the proper headers. Exits after.
     */
		function process_ipn_return() {

    }
		
		/****** Do not override any of these private methods please! ******/
		
		//populates ipn_url var
		function _generate_ipn_url() {
			global $mp;
      $this->ipn_url = home_url($mp->get_setting('slugs->store') . '/payment-return/' . $this->plugin_name);
    }
    
		//populates ipn_url var
		function _payment_form_skip($var) {
			return $this->skip_form;
    }
    
		//creates the payment method selections
		function _payment_form_wrapper($content, $cart, $shipping_info) {
      global $mp, $mp_gateway_active_plugins;
      
      $hidden = (count((array)$mp_gateway_active_plugins) > 1 && $_SESSION['mp_payment_method'] != $this->plugin_name) ? ' style="display:none;"' : '';
        
      $content .= '<div class="mp_gateway_form" id="' . $this->plugin_name . '"' . $hidden . '>';
      $content .= $this->payment_form($cart, $shipping_info);

      $content .= '<p class="mp_cart_direct_checkout">';
      $content .= '<input type="submit" name="mp_payment_submit" id="mp_payment_confirm" value="' . __('Continue Checkout &raquo;', 'mp') . '" />';
      $content .= '</p></div>';
      
      return $content;
    }
    
    //calls the order_confirmation() method on the correct page
    function _checkout_confirmation_hook() {
      global $wp_query, $mp;

      if ($wp_query->query_vars['pagename'] == 'cart') {
        if (isset($wp_query->query_vars['checkoutstep']) && $wp_query->query_vars['checkoutstep'] == 'confirmation')
          do_action( 'mp_checkout_payment_pre_confirmation_' . $_SESSION['mp_payment_method'], $mp->get_order($_SESSION['mp_order']) );
      }
    }
    
    //DO NOT override the construct! instead use the on_creation() method.
    function __construct() {
    
      $this->_generate_ipn_url();
      
      //run plugin construct
      $this->on_creation();
      
      //check required vars
      if (empty($this->plugin_name) || empty($this->admin_name) || empty($this->public_name))
        wp_die( __("You must override all required vars in your {$this->admin_name} payment gateway plugin!", 'mp') );

      add_filter( 'mp_checkout_payment_form', array(&$this, '_payment_form_wrapper'), 10, 3 );
      add_action( 'template_redirect', array(&$this, '_checkout_confirmation_hook') );
      add_filter( 'mp_payment_form_skip_' . $this->plugin_name, array(&$this, '_payment_form_skip') );
      add_action( 'mp_payment_submit_' . $this->plugin_name, array(&$this, 'process_payment_form'), 10, 2 );
      add_filter( 'mp_checkout_confirm_payment_' . $this->plugin_name, array(&$this, 'confirm_payment_form'), 10, 2 );
      add_action( 'mp_payment_confirm_' . $this->plugin_name, array(&$this, 'process_payment'), 10, 2 );
      add_filter( 'mp_order_notification_' . $this->plugin_name, array(&$this, 'order_confirmation_email'), 10, 2 );
      add_action( 'mp_checkout_payment_pre_confirmation_' . $this->plugin_name, array(&$this, 'order_confirmation') );
      add_filter( 'mp_checkout_payment_confirmation_' . $this->plugin_name, array(&$this, 'order_confirmation_msg'), 10, 2 );
      add_action( 'mp_gateway_settings', array(&$this, 'gateway_settings_box') );
      add_action( 'mp_handle_payment_return_' . $this->plugin_name, array(&$this, 'process_ipn_return') );
      
      if ( is_network_admin() ) {
	      add_filter('mp_network_settings_save', array(&$this, 'network_settings_save'));
      } else {
	      add_filter('mp_gateway_settings_filter', array(&$this, 'process_gateway_settings'));
      }
  	}
  }
  
}

/**
 * Use this function to register your gateway plugin class
 *
 * @param string $class_name - the case sensitive name of your plugin class
 * @param string $plugin_name - the sanitized private name for your plugin
 * @param string $admin_name - pretty name of your gateway, for the admin side.
 * @param bool $global optional - whether the gateway supports global checkouts
 */
function mp_register_gateway_plugin($class_name, $plugin_name, $admin_name, $global = false, $demo = false) {
  global $mp_gateway_plugins;
  
  if (!is_array($mp_gateway_plugins)) {
		$mp_gateway_plugins = array();
	}
	
	if (class_exists($class_name)) {
		$mp_gateway_plugins[$plugin_name] = array($class_name, $admin_name, $global, $demo);
	} else {
		return false;
	}
}