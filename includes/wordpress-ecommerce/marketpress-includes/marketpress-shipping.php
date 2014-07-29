<?php
/*
MarketPress Shipping Plugin Base Class
*/
if (!class_exists('MP_Shipping_API')) {

  class MP_Shipping_API {

    //private shipping method name. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = '';

    //public name of your method, for lists and such.
    var $public_name = '';

    //set to true if you need to use the shipping_metabox() method to add per-product shipping options
    var $use_metabox = false;

		//set to true if you want to add per-product weight shipping field
    var $use_weight = false;


    /****** Below are the public methods you may overwrite via a plugin ******/

    /**
     * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
     */
    function on_creation() {

		}

    /**
     * Echo anything you want to add to the top of the shipping screen
     */
		function before_shipping_form($content) {

    }

    /**
     * Echo anything you want to add to the bottom of the shipping screen
     */
		function after_shipping_form($content) {

    }

    /**
     * Echo a table row with any extra shipping fields you need to add to the form
     */
		function extra_shipping_field($content) {

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
      //it is required to override this method if $use_metabox is set to true
      if ($this->use_metabox)
        wp_die( __("You must override the shipping_metabox() method in your {$this->public_name} shipping plugin if \$use_metabox is set to true!", 'mp') );
    }

    /**
     * Save any per-product shipping fields from the shipping metabox using update_post_meta
     *
     * @param array|string $shipping_meta, save anything from the $_POST global
     * return array|string $shipping_meta
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
      //it is required to override this method
      wp_die( __("You must override the calculate_shipping() method in your {$this->public_name} shipping plugin!", 'mp') );
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

			$shipping_options = array();

      return $shipping_options;
    }

		/****** Do not override any of these private methods please! ******/
		////////////////////////////////////////////////////////////////////

		function _weight_shipping_metabox($shipping_meta, $settings) {
      global $mp;

			echo '<p>';
			if ($mp->get_setting('shipping->system') == 'metric') {
				?>
				<label><?php _e('Weight (Kilograms)', 'mp'); ?>:<br />
				<input type="text" size="6" id="mp_shipping_weight" name="mp_shipping_weight" value="<?php echo isset($shipping_meta['weight']) ? $shipping_meta['weight'] : '0'; ?>" />
				</label>
				<?php
			} else {
				if ( isset($shipping_meta['weight']) ) {
					$pounds = intval($shipping_meta['weight']);
					$oz = floatval( ($shipping_meta['weight'] - $pounds) * 16);
				} else {
					$pounds = $oz = '';
				}
				?>
				<?php _e('Product Weight:', 'mp'); ?><br />
				<label><input type="text" size="2" name="mp_shipping_weight_pounds" value="<?php echo $pounds; ?>" /> <?php _e('Pounds', 'mp'); ?></label><br />
				<label><input type="text" size="2" name="mp_shipping_weight_oz" value="<?php echo $oz; ?>" /> <?php _e('Ounces', 'mp'); ?></label>
				<?php
			}
			echo '</p>';
    }

		function _weight_save_shipping_metabox($shipping_meta) {
			global $mp;

			//process extra per item shipping
			if ($mp->get_setting('shipping->system') == 'metric') {
				$shipping_meta['weight'] = (!empty($_POST['mp_shipping_weight'])) ? round($_POST['mp_shipping_weight'], 2) : 0;
			} else {
				$pounds = (!empty($_POST['mp_shipping_weight_pounds'])) ? floatval($_POST['mp_shipping_weight_pounds']) : 0;
				$oz = (!empty($_POST['mp_shipping_weight_oz'])) ? floatval($_POST['mp_shipping_weight_oz']) : 0;
				$oz = $oz / 16;
				$shipping_meta['weight'] = floatval($pounds + $oz);
			}

			return $shipping_meta;
    }

    //DO NOT override the construct! instead use the on_creation() method.
    function __construct() {
      global $mp;

			$this->on_creation();

      add_filter( 'mp_checkout_before_shipping', array(&$this, 'before_shipping_form') );
      add_filter( 'mp_checkout_after_shipping', array(&$this, 'after_shipping_form') );
      add_filter( 'mp_checkout_shipping_field', array(&$this, 'extra_shipping_field') );
      add_action( 'mp_shipping_process', array(&$this, 'process_shipping_form') );
      add_action( 'mp_shipping_settings', array(&$this, 'shipping_settings_box') );
      add_filter( 'mp_shipping_settings_filter', array(&$this, 'process_shipping_settings') );

			add_filter( "mp_calculate_shipping_{$this->plugin_name}", array(&$this, 'calculate_shipping'), 10, 10 );

			add_filter( "mp_shipping_options_{$this->plugin_name}", array(&$this, 'shipping_options'), 10, 7 );

			//private
			if ($this->use_weight && !$mp->weight_printed) {
				add_action( 'mp_shipping_metabox', array(&$this, '_weight_shipping_metabox'), 10, 2 );
				add_filter( 'mp_save_shipping_meta', array(&$this, '_weight_save_shipping_metabox') );
				$mp->weight_printed = true;
			}

      if ($this->use_metabox) {
        add_action( 'mp_shipping_metabox', array(&$this, 'shipping_metabox'), 10, 2 );
        add_filter( 'mp_save_shipping_meta', array(&$this, 'save_shipping_metabox') );
      }

  	}
  }

}

/**
 * Use this function to register your shipping plugin class
 *
 * @param string $plugin_name - the sanitized private name for your plugin
 * @param string $class_name - the case sensitive name of your plugin class
 * @param string $public_name - the public name of the plugin for lists and such
 * @param bool $calculated - whether this is a calculated shipping module that can be selected by the user at checkout (UPS, USPS, FedEx, etc.)
 */
function mp_register_shipping_plugin($class_name, $plugin_name, $public_name, $calculated = false, $demo = false) {
  global $mp_shipping_plugins;

  if(!is_array($mp_shipping_plugins)) {
		$mp_shipping_plugins = array();
	}

	if(class_exists($class_name)) {
		$mp_shipping_plugins[$plugin_name] = array($class_name, $public_name, $calculated, $demo);
	} else {
		return false;
	}
}

/**
 * Shipping handler class
 */
class MP_Shipping_Handler {

	function __construct() {
		add_filter( 'mp_checkout_shipping_field', array(&$this, 'extra_shipping_box'), 99 ); //run last
		add_filter( 'mp_checkout_shipping_field_readonly', array(&$this, 'extra_shipping_box_label'), 99 ); //run last
		add_action( 'mp_shipping_process', array(&$this, 'process_shipping_form') );
		add_filter( 'mp_shipping_method_lbl', array(&$this, 'filter_method_lbl') );
		add_action( 'wp_ajax_nopriv_mp-shipping-options', array(&$this, 'shipping_sub_options') );
    add_action( 'wp_ajax_mp-shipping-options', array(&$this, 'shipping_sub_options') );
		add_action( 'mp_shipping_metabox', array(&$this, 'extra_shipping_metabox'), 10, 2 );
    add_filter( 'mp_save_shipping_meta', array(&$this, 'extra_save_shipping_metabox') );
	}

	function extra_shipping_box($content) {
		global $mp_shipping_active_plugins, $mp;

		if ( count((array)$mp_shipping_active_plugins) && $mp->get_setting('shipping->method') == 'calculated' ) {
			$content .= '<thead><tr>';
			$content .= '<th colspan="2">'. __('Choose a Shipping Method:', 'mp').'</th>';
			$content .= '</tr></thead>';
			$content .= '<tr>';
			$content .= '<td align="right">'.__('Shipping Method:', 'mp').'</td><td id="mp-shipping-select-td">';
			$content .= '<input type="hidden" name="action" value="mp-shipping-options" />';
			$content .= '<select name="shipping_option" id="mp-shipping-select">';
			$shipping_option = isset($_SESSION['mp_shipping_info']['shipping_option']) ? $_SESSION['mp_shipping_info']['shipping_option'] : '';
			foreach ($mp_shipping_active_plugins as $plugin) {
				$content .= '<option value="' . $plugin->plugin_name . '"'.selected($shipping_option, $plugin->plugin_name, false).'>' . esc_attr($plugin->public_name) . '</option>';
			}
			$content .= '</select>';
			$content .= ' <span id="mp-shipping-select-holder">' . $this->shipping_sub_options().'</span>';
			$content .= '</td></tr>';
		}
		return $content;
	}

	function extra_shipping_box_label($content) {
		global $mp_shipping_active_plugins, $mp;

		if ( $mp->get_setting('shipping->method') == 'calculated' && isset($_SESSION['mp_shipping_info']['shipping_option']) && isset($mp_shipping_active_plugins[$_SESSION['mp_shipping_info']['shipping_option']]) ) {
			$label = $mp_shipping_active_plugins[$_SESSION['mp_shipping_info']['shipping_option']]->public_name;

			if (isset($_SESSION['mp_shipping_info']['shipping_sub_option']))
				$label .= ' - ' . $_SESSION['mp_shipping_info']['shipping_sub_option'];

			$content .= '<tr>';
      $content .= '<td align="right">'.__('Shipping Method:', 'mp').'</td>';
      $content .= '<td>'.esc_attr($label).'</td>';
      $content .= '</tr>';
		}
		return $content;
	}

	function process_shipping_form() {
		if (isset($_POST['shipping_option']))
			$_SESSION['mp_shipping_info']['shipping_option'] = trim($_POST['shipping_option']);
		if (isset($_POST['shipping_sub_option'])) {
			$_SESSION['mp_shipping_info']['shipping_sub_option'] = trim($_POST['shipping_sub_option']);
		}
	}

	function shipping_sub_options() {
		global $mp_shipping_active_plugins, $mp;

		$first = reset($mp_shipping_active_plugins);
		$selected = isset($_POST['shipping_option']) ? $_POST['shipping_option'] : (isset($_SESSION['mp_shipping_info']['shipping_option']) ? $_SESSION['mp_shipping_info']['shipping_option'] : $first->plugin_name);

		//get address
    $meta = get_user_meta(get_current_user_id(), 'mp_shipping_info', true);
		$address1 = isset($_POST['address1']) ? trim(stripslashes($_POST['address1'])) : (isset($_SESSION['mp_shipping_info']['address1']) ? $_SESSION['mp_shipping_info']['address1'] : $meta['address1']);
		$address2 = isset($_POST['address2']) ? trim(stripslashes($_POST['address2'])) : (isset($_SESSION['mp_shipping_info']['address2']) ? $_SESSION['mp_shipping_info']['address2'] : $meta['address2']);
		$city = isset($_POST['city']) ? trim(stripslashes($_POST['city'])) : (isset($_SESSION['mp_shipping_info']['city']) ? $_SESSION['mp_shipping_info']['city'] : $meta['city']);
		$state = isset($_POST['state']) ? trim(stripslashes($_POST['state'])) : (isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : $meta['state']);
		$zip = isset($_POST['zip']) ? trim(stripslashes($_POST['zip'])) : (isset($_SESSION['mp_shipping_info']['zip']) ? $_SESSION['mp_shipping_info']['zip'] : $meta['zip']);
		$country = isset($_POST['country']) ? trim($_POST['country']) : (isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : $meta['country']);

		//Pick up any service specific fields
		do_action( 'mp_shipping_process' );

		$options = apply_filters("mp_shipping_options_$selected", $mp->get_cart_contents(), $address1, $address2, $city, $state, $zip, $country);

		$content = '';
		if ( count( $options ) && ! array_key_exists('error', $options) ) {  //If one of the keys is 'error' then it contains an error message from calculated rates.

			if (defined('DOING_AJAX')) {
				header('Content-Type: text/html');
			}

			$content .= '<select name="shipping_sub_option" size="' . max(count($options), 4) . '">'; //4 min because of safari

			//Make sure the $_SESSION suboption is still in the available rates
			$suboption = isset($_SESSION['mp_shipping_info']['shipping_sub_option']) ? $_SESSION['mp_shipping_info']['shipping_sub_option'] : '';
			$suboption = array_key_exists($suboption, $options) ? $suboption : '';

			$ndx = 0;
			foreach ($options as $key => $name) {
				$selected = ($ndx == 0 && empty($suboption) ) ? true :  ($suboption == $key); //Nothing selected pick the first one.
				$content .= '<option value="' . $key . '"'. selected($selected, true, false) . '>' . esc_attr($name) . '</option>';
				$ndx++;
			}
			$content .= '</select>';
		} else{
			if (defined('DOING_AJAX')) {
				header('Content-Type: application/json');
				$content = json_encode(array('error' => $options['error']) );
			} else {
			$content .= $options['error'];
			$content .= '<input type="hidden" name="no_shipping_options" value="1" />';
			}
			$content .= apply_filters('mp_checkout_error_no_shipping_options', '');
		}


		if (defined('DOING_AJAX'))
			die($content);
		else
			return $content;
	}

	function filter_method_lbl() {
		global $mp_shipping_active_plugins;

		if ( isset($_SESSION['mp_shipping_info']['shipping_option']) && isset($mp_shipping_active_plugins[$_SESSION['mp_shipping_info']['shipping_option']]) ) {
			return $mp_shipping_active_plugins[$_SESSION['mp_shipping_info']['shipping_option']]->public_name;
		}
	}

	function extra_shipping_metabox($shipping_meta, $settings) {
		global $mp;
		?>
		<p>
		<label><?php _e('Extra Shipping Cost', 'mp'); ?>:<br />
		<?php echo $mp->format_currency(); ?><input type="text" size="6" id="mp_extra_shipping_cost" name="mp_extra_shipping_cost" value="<?php echo !empty($shipping_meta['extra_cost']) ? $mp->display_currency($shipping_meta['extra_cost']) : '0.00'; ?>" />
		</label>
		</p>
		<?php
	}

	function extra_save_shipping_metabox($shipping_meta) {
		//process extra per item shipping
		$shipping_meta['extra_cost'] = (!empty($_POST['mp_extra_shipping_cost'])) ? round($_POST['mp_extra_shipping_cost'], 2) : 0;
		return $shipping_meta;
	}
}
$mpsh = new MP_Shipping_Handler();