<?php
/*
MarketPress Example Shipping Plugin Template
*/

class MP_Shipping_Pickup extends MP_Shipping_API {

  //private shipping method name. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'pickup';
  
  //public name of your method, for lists and such.
  var $public_name = '';
  
  //set to true if you need to use the shipping_metabox() method to add per-product shipping options
  var $use_metabox = true;
	
	//set to true if you want to add per-product weight shipping field
	var $use_weight = true;


  	/**
   	 * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   	 */
  	function on_creation() {
		//declare here for translation
	  	$this->public_name = __('Pickup', 'mp');
		
		//filter the confirmation message
		add_filter('mp_checkout_shipping_field_readonly', array(&$this, 'show_instructions_confirm_page' ));
		//hook into the order status
		add_action('mp_order_status_output',array(&$this, 'show_instructions_order_status' ));
		
		//filter the email output
		add_filter('mp_order_notification_body', array(&$this, 'add_instructions_to_order_email'), 10, 2 );
	}
	
	
	/**
	 * Show the special instructions on the confirm page
	 */
	function show_instructions_confirm_page( $content ) {
		global $mp;
		$settings = $mp->get_setting('shipping');
		
		$instructions = isset( $settings['pickup']['pickup-instructions'] ) ? $settings['pickup']['pickup-instructions'] : false;
		if($instructions) {
			$content .= '<tr><td>'.__('Pickup Instructions', 'mp').'</td><td>'.esc_textarea($instructions).'</td></tr>';
		}
		return $content;
	}
	
	/**
	 * Hook in to show the order instructions on the order status screen
	 */
	function show_instructions_order_status($order) {
		global $mp;
		
		$used_pickup = ( isset( $order->mp_shipping_info['shipping_option'] ) && $order->mp_shipping_info['shipping_option'] == 'pickup' ) ? true : false;
		
		if( $used_pickup ) {
			$settings = $mp->get_setting('shipping');
			echo '<h3>'.__('Pickup Instructions', 'mp').'</h3><ul><li>'.esc_attr($settings['pickup']['pickup-instructions']).'</li></ul>';
		}
		
	}
	
	/**
	 * Filter the new order email 
	 */
	function add_instructions_to_order_email($text, $order) {
		global $mp;
		$used_pickup = ( isset( $order->mp_shipping_info['shipping_option'] ) && $order->mp_shipping_info['shipping_option'] == 'pickup' ) ? true : false;
		
		if( $used_pickup ) {
			$settings = $mp->get_setting('shipping');
			$text .= "\n\n" .__('Pickup Instructions', 'mp');
			$text .= "\n".esc_attr($settings['pickup']['pickup-instructions']);
		}
		
		return $text;
	}
	
	

  /**
   * Echo anything you want to add to the top of the shipping screen
   */
	function before_shipping_form($content) {
		return $content;
  }
  
  /**
   * Echo anything you want to add to the bottom of the shipping screen
   */
	function after_shipping_form($content) {
		return $content;
  }
  
  /**
   * Echo a table row with any extra shipping fields you need to add to the shipping checkout form
   */
	function extra_shipping_field($content) {
		return $content;
  }
  
  /**
   * Use this to process any additional field you may add. Use the $_POST global,
   *  and be sure to save it to both the cookie and usermeta if logged in.
   */
	function process_shipping_form() {

  }
	
	/**
   * Echo a settings meta box with whatever settings you need for you shipping module.
   *  Form field names should be prefixed with mp[shipping][plugin_name], like "mp[shipping][plugin_name][mysetting]".
   *  You can access saved settings via $settings array.
   */
	function shipping_settings_box($settings) {
		global $mp;
		$settings = $mp->get_setting('shipping');
		
		$instructions = isset( $settings['pickup']['pickup-instructions'] ) ? $settings['pickup']['pickup-instructions'] : '';
		?>
		<div class="postbox">
			<h3 class="hndle"><?php _e('Pickup Option','mp');?></h3>
				<div class="inside">
					<p class="description">
						<?php _e('This option allows your customers to indicate that they will pick-up their order at your place of business.', 'mp') ?>
					</p>
				</div>
				<table class="form-table">
					<tbody>
							<tr>
                            	<th scope="row"><?php _e('Pickup Fee','mp');?></th>
								<td><?php echo $mp->format_currency();?><input type="text" name="mp[shipping][pickup][processing-fee]" size="5" value="<?php echo isset( $settings['pickup']['processing-fee'] ) ? $mp->display_currency( floatval ( $settings['pickup']['processing-fee'] ) ) : '0.00';?>"/></td>
							</tr>
                            <tr>
                            	<th scope="row"><?php _e('Pickup Instructions','mp');?></th>
                            	<td>
                                	<textarea class="widefat" name="mp[shipping][pickup][pickup-instructions]"><?php echo esc_textarea( $instructions );?></textarea>
                                    <span><?php _e('Special instructions for customers','mp');?></span>
                                </td>
                            </tr>
						</tbody>
				</table>
		</div>
		<?php

  }
  
  /**
   * Filters posted data from your form. Do anything you need to the $settings['shipping']['plugin_name']
   *  array. Don't forget to return!
   */
	function process_shipping_settings($settings) {

    return $settings;
  }
  
  /**
   * Echo any per-product shipping fields you need to add to the product edit screen shipping metabox
   *
   * @param array $shipping_meta, the contents of the post meta. Use to retrieve any previously saved product meta
   * @param array $settings, access saved settings via $settings array.
   */
	function shipping_metabox($shipping_meta, $settings) {

  }

  /**
   * Save any per-product shipping fields from the shipping metabox using update_post_meta
   *
   * @param array $shipping_meta, save anything from the $_POST global
   * return array $shipping_meta
   */
	function save_shipping_metabox($shipping_meta) {

    return $shipping_meta;
  }
  
  /**
		* Use this function to return your calculated price as an integer or float
		*
		* @param int $price, always 0. Modify this and return
		* @param float $total, cart total after any coupons and before tax
		* @param array $cart, the contents of the shopping cart for advanced calculations
		* @param string $address1
		* @param string $address2
		* @param string $city
		* @param string $state, state/province/region
		* @param string $zip, postal code
		* @param string $country, ISO 3166-1 alpha-2 country code
		* @param string $selected_option, if a calculated shipping module, passes the currently selected sub shipping option if set
		*
		* return float $price
		*/
	function calculate_shipping($price, $total, $cart, $address1, $address2, $city, $state, $zip, $country, $selected_option) {
    	global $mp;
		$settings = $mp->get_setting('shipping');
		$fee = isset( $settings['pickup']['processing-fee'] ) ? esc_attr( $settings['pickup']['processing-fee'] ) : 0;
    	return floatval( $fee );
	}
	
	/**
		* For calculated shipping modules, use this method to return an associative array of the sub-options. The key will be what's saved as selected
		*  in the session. Note the shipping parameters won't always be set. If they are, add the prices to the labels for each option.
		*
		* @param array $cart, the contents of the shopping cart for advanced calculations
		* @param string $address1
		* @param string $address2
		* @param string $city
		* @param string $state, state/province/region
		* @param string $zip, postal code
		* @param string $country, ISO 3166-1 alpha-2 country code
		*
		* return array $shipping_options 
		*/
	function shipping_options($cart, $address1, $address2, $city, $state, $zip, $country) {
		
		$shipping_options = array('in-store' => __('In Store', 'mp') );
		return $shipping_options;
	}
	
}

//register plugin - uncomment to register
mp_register_shipping_plugin( 'MP_Shipping_Pickup', 'pickup', __('Pickup', 'mp'), true );