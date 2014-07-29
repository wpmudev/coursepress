<?php
/*
MarketPress Flat-Rate Shipping Plugin
Author: Aaron Edwards (Incsub)
*/
class MP_Shipping_Flat_Rate extends MP_Shipping_API {

  //private shipping method name. Lowercase alpha (a-z) and dashes (-) only please!
  var $plugin_name = 'flat-rate';

  //public name of your method, for lists and such.
  var $public_name = '';

  //set to true if you need to use the shipping_metabox() method to add per-product shipping options
  var $use_metabox = false;

	//set to true if you want to add per-product weight shipping field
	var $use_weight = false;

  /**
   * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
   */
  function on_creation() {
    //set name here to be able to translate
    $this->public_name = __('Flat Rate', 'mp');
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
    ?>
    <div id="mp_flat_rate" class="postbox">
      <h3 class='hndle'><span><?php _e('Flat Rate Settings', 'mp'); ?></span></h3>
      <div class="inside">
        <span class="description"><?php _e('Be sure to enter a shipping price for every option or those customers may get free shipping.', 'mp') ?></span>
        <table class="form-table">
    <?php
    switch ($mp->get_setting('base_country')) {
      case 'US':
        ?>
          <tr>
  				<th scope="row"><?php _e('Lower 48 States', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][lower_48]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->lower_48')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('Hawaii and Alaska', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][hi_ak]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->hi_ak')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('Canada', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][canada]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->canada')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('International', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][international]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->international')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
        <?php
        break;

      case 'CA':
        ?>
          <tr>
  				<th scope="row"><?php _e('In Country', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][in_country]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->in_country')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('United States', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][usa]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->usa')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('International', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][international]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->international')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
        <?php
        break;

      default:
        //in european union
        if ( in_array($mp->get_setting('base_country'), $mp->eu_countries) ) {
          ?>
          <tr>
  				<th scope="row"><?php _e('In Country', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][in_country]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->in_country')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('European Union', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][eu]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->eu')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('International', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][international]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->international')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <?php
        } else { //all other countries
          ?>
          <tr>
  				<th scope="row"><?php _e('In Country', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][in_country]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->in_country')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <tr>
  				<th scope="row"><?php _e('International', 'mp') ?></th>
  				<td>
  				<?php echo $mp->format_currency(); ?><input type="text" name="mp[shipping][flat-rate][international]" value="<?php echo esc_attr($mp->get_setting('shipping->flat-rate->international')); ?>" size="5" maxlength="10" />
    			</td>
          </tr>
          <?php
        }
        break;
    }
    ?>
        </table>
      </div>
    </div>
    <?php
  }

  /**
   * Filters posted data from your form. Do anything you need to the $settings['shipping']['plugin_name']
   *  array. Don't forget to return!
   */
	function process_shipping_settings($settings) {
		//sanitize the price fields
		function sanitize_rates(&$value, $key) {
			global $mp;
			if (!is_array($value))
				$value = $mp->display_currency(preg_replace('/[^0-9.]/', '', $value));
		}
		
		if (is_array($settings['shipping']['flat-rate']))
			array_walk_recursive($settings['shipping']['flat-rate'], 'sanitize_rates');
			
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

    switch ($mp->get_setting('base_country')) {
      case 'US':
        if ($country == 'US') {
          //price based on state
          if ($state == 'HI' || $state == 'AK')
            $price = $mp->get_setting('shipping->flat-rate->hi_ak');
          else
            $price = $mp->get_setting('shipping->flat-rate->lower_48');
        } else if ($country == 'CA') {
          $price = $mp->get_setting('shipping->flat-rate->canada');
        } else {
          $price = $mp->get_setting('shipping->flat-rate->international');
        }
        break;

      case 'CA':
        if ($country == 'CA') {
          $price = $mp->get_setting('shipping->flat-rate->in_country');
        } else if ($country == 'US') {
          $price = $mp->get_setting('shipping->flat-rate->usa');
        } else {
          $price = $mp->get_setting('shipping->flat-rate->international');
        }
        break;

      default:
        //in european union
        if ( in_array($mp->get_setting('base_country'), $mp->eu_countries) ) {
          if ($country == $mp->get_setting('base_country')) {
            $price = $mp->get_setting('shipping->flat-rate->in_country');
          } else if (in_array($country, $mp->eu_countries)) {
            $price = $mp->get_setting('shipping->flat-rate->eu');
          } else {
            $price = $mp->get_setting('shipping->flat-rate->international');
          }
        } else { //all other countries
          if ($country == $mp->get_setting('base_country')) {
            $price = $mp->get_setting('shipping->flat-rate->in_country');
          } else {
            $price = $mp->get_setting('shipping->flat-rate->international');
          }
        }
        break;
    }

    return $price;
  }
}

//register plugin
mp_register_shipping_plugin( 'MP_Shipping_Flat_Rate', 'flat-rate', __('Flat Rate', 'mp') );