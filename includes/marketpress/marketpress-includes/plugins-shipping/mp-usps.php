<?php
/*
MarketPress USPS Calculated Shipping Plugin
Author: Arnold Bailey (Incsub)
*/
class MP_Shipping_USPS extends MP_Shipping_API {

	//private shipping method name. Lowercase alpha (a-z) and dashes (-) only please!
	public $plugin_name = 'usps';

	//public name of your method, for lists and such.
	public $public_name = '';

	//set to true if you need to use the shipping_metabox() method to add per-product shipping options
	public $use_metabox = true;

	//set to true if you want to add per-product extra shipping cost field
	public $use_extra = true;

	//set to true if you want to add per-product weight shipping field
	public $use_weight = true;

	//Test sandboxed URI for USPS Rates API (Currently boken of RateV4 at USPS)
	public $test_uri = 'http://testing.shippingapis.com/ShippingAPITest.dll';

	//Production Live URI for USPS Rates API
	public $production_uri = 'http://production.shippingapis.com/ShippingAPI.dll';

	// Defines the available shipping Services and their display names
	public $services = '';

	// Maximum weight for a single Package
	public $max_weight = 70;

	public $weight = 0;
	public $pound = 0;
	public $ounces = 0;
	public $width = 0;
	public $length = 0;
	public $height = 0;
	public $girth = 0;
	public $machinable = 'true';
	public $size = 'REGULAR';
	public $origin_zip = '';
	public $destination_zip = '';

	public $domestic_handling = 0;
	public $intl_handling = 0;

	private $settings = '';
	private $usps_settings;

	private $pkg_count = 0;
	private $pkg_weight = 0;
	private $pkg_max = 0;
	private $pkg_dims = array(12,12,12);

	/**
	* Runs when your class is instantiated. Use to setup your plugin instead of __construct()
	*/
	function on_creation() {
		//set name here to be able to translate
		$this->public_name = __('USPS', 'mp');

		//Key is the enumeration for the USPS Services XML field
		$this->services = array(
		'Express Mail' =>
		new USPS_Service( 3, __('Express Mail', 'mp'),                                          __('(1-2 days)','mp') ),

		'Express Mail Hold For Pickup' =>
		new USPS_Service( 2, __('Express Mail Hold For Pickup', 'mp'),                          __('(1-2 days)','mp') ),

		'Express Mail Sunday/Holiday Delivery' =>
		new USPS_Service( 3, __('Express Mail Sunday/Holiday Delivery', 'mp'),                  __('(1-2 days)','mp') ),

		'Express Mail Flat Rate Boxes' =>
		new USPS_Service( 55, __('Express Mail Flat Rate Boxes', 'mp'),                         __('(1-2 days)','mp'), 50 ),

		'Express Mail Flat Rate Boxes Hold For Pickup' =>
		new USPS_Service( 56, __('Express Mail Flat Rate Boxes Hold For Pickup', 'mp'),         __('(1-2 days)','mp'), 50 ),

		'Express Mail Sunday/Holiday Delivery Flat Rate Boxes' =>
		new USPS_Service( 57, __('Express Mail Sunday/Holiday Delivery Flat Rate Boxes', 'mp'), __('(1-2 days)','mp') ),

		'Priority Mail' =>
		new USPS_Service( 1, __('Priority Mail', 'mp'),                                         __('(2-4) days','mp') ),

		'Priority Mail Large Flat Rate Box' =>
		new USPS_Service( 22, __('Priority Mail Large Flat Rate Box', 'mp'),                    __('(2-4 days)','mp'), 30 ),

		'Priority Mail Medium Flat Rate Box' =>
		new USPS_Service( 17, __('Priority Mail Medium Flat Rate Box', 'mp'),                   __('(2-4 days)','mp'), 20 ),

		'Priority Mail Small Flat Rate Box' =>
		new USPS_Service( 28, __('Priority Mail Small Flat Rate Box', 'mp'),                    __('(2-4 days)','mp'),  3 ),

		'Padded Flat Rate Envelope' =>
		new USPS_Service( 29, __('Priority Mail Padded Flat Rate Envelope', 'mp'),              __('(2-4 days)','mp'),  2 ),

		'First-Class Mail Parcel' =>
		new USPS_Service( 0, __('First-Class Mail Parcel', 'mp'),                               __('(2-4 days)','mp') ),

		'Media Mail' =>
		new USPS_Service( 6, __('Media Mail', 'mp'),                                            ''),

		'Library Mail' =>
		new USPS_Service( 7, __('Library Mail', 'mp'),                                          ''),

		);

		$this->intl_services = array(
		'Express Mail International' =>
		new USPS_Service( 1,  __('Express Mail International', 'mp') ),

		'Express Mail International Flat Rate Boxes' =>
		new USPS_Service( 26, __('Express Mail International Flat Rate Boxes', 'mp'),                     '',                   50 ),

		'Priority Mail International' =>
		new USPS_Service( 2,  __('Priority Mail International', 'mp') ),

		'Priority Mail International Large Flat Rate Boxes' =>
		new USPS_Service( 11, __('Priority Mail International Large Flat Rate Boxes', 'mp'),              '',                   30 ),

		'Priority Mail International Medium Flat Rate Boxes' =>
		new USPS_Service( 9,  __('Priority Mail International Medium Flat Rate Boxes', 'mp'),             '',                   20 ),

		'Priority Mail International Small Flat Rate Boxes' =>
		new USPS_Service( 16, __('Priority Mail International Small Flat Rate Boxes', 'mp'),              '',                    3 ),

		'Priority Mail Express International Padded Flat Rate Envelope' =>
		new USPS_Service( 27, __('Priority Mail Express International Padded Flat Rate Envelope', 'mp'),  __('(3-5 days)','mp'), 2 ),

		'First Class International Parcel' =>
		new USPS_Service( 15, __('First Class International Parcel', 'mp') ),

		);

		// Get settings for convenience sake
		$this->settings = get_option('mp_settings');
		$this->usps_settings = isset($this->settings['shipping']['usps']) ? $this->settings['shipping']['usps'] : array();
	}

	function default_boxes() {
		// Initialize the default boxes if nothing there
		if(count($this->usps_settings['boxes']['name']) <= 1)
		{
			$this->usps_settings['boxes'] =
			array (
			'name' =>
			array (
			0 => __('Flat Rate Small', 'mp'),
			1 => __('Flat Rate Medium 1', 'mp'),
			2 => __('Flat Rate Medium 2', 'mp'),
			3 => __('Flat Rate Large', 'mp'),
			),
			'size' =>
			array (
			0 => '8x6x1.6',
			1 => '11x8.5x5.5',
			2 => '13.6x11.9.5x3.4',
			3 => '12x12x5.5',
			),
			'weight' =>
			array (
			0 => '3',
			1 => '20',
			2 => '20',
			3 => '30',
			),
			);
		}
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

	/*
	* Echos one row for boxes data. If $key is non-numeric then emit a blank row for new entry
	*
	* @ $key
	*
	* @ returns HTML for one row
	*/
	private function box_row_html($key=''){

		$name = '';
		$size = '';
		$weight = '';

		if ( is_numeric($key) ){
			$name = $this->usps_settings['boxes']['name'][$key];
			$size = $this->usps_settings['boxes']['size'][$key];
			$weight = $this->usps_settings['boxes']['weight'][$key];
			if (empty($name) && empty($size) &empty($weight)) return''; //rows blank, don't need it
		}
		?>
		<tr class="variation">
			<td class="mp_box_name">
				<input type="text" name="mp[shipping][usps][boxes][name][]" value="<?php echo esc_attr($name); ?>" size="18" maxlength="20" />
			</td>
			<td class="mp_box_dimensions">
				<label>
					<input type="text" name="mp[shipping][usps][boxes][size][]" value="<?php echo esc_attr($size); ?>" size="10" maxlength="20" />
					<?php echo $this->get_units_length(); ?>
				</label>
			</td>
			<td class="mp_box_weight">
				<label>
					<input type="text" name="mp[shipping][usps][boxes][weight][]" value="<?php echo esc_attr($weight); ?>" size="6" maxlength="10" />
					<?php echo $this->get_units_weight(); ?>
				</label>
			</td>
			<?php if ( is_numeric($key) ): ?>

			<td class="mp_box_remove">
				<a onclick="uspsDeleteBox(this);" href="#mp_shipping_boxes_table" title="<?php _e('Remove Box', 'mp'); ?>" > </a>
			</td>

			<?php else: ?>

			<td class="mp_box_add">
				<a onclick="uspsAddBox(this);" href="#mp_shipping_boxes_table" title="<?php _e('Add Box', 'mp'); ?>" > </a>
			</td>

			<?php endif; ?>
		</tr>
		<?php
	}

	/**
	* Echo a settings meta box with whatever settings you need for you shipping module.
	*  Form field names should be prefixed with mp[shipping][plugin_name], like "mp[shipping][plugin_name][mysetting]".
	*  You can access saved settings via $settings array.
	*/
	function shipping_settings_box($settings) {
		global $mp;

		$this->settings = $settings;
		$this->usps_settings = $this->settings['shipping']['usps'];
		$system = $this->settings['shipping']['system']; //Current Unit settings english | metric

		?>

		<script type="text/javascript">
			//Remove a row in the Boxes table
			function uspsDeleteBox(row)
			{
				var i = row.parentNode.parentNode.rowIndex;
				document.getElementById('mp_shipping_boxes_table').deleteRow(i);
			}

			function uspsAddBox(row)
			{
				//Adds an Empty Row
				var clone = row.parentNode.parentNode.cloneNode(true);
				document.getElementById('mp_shipping_boxes_table').appendChild(clone);
				var fields = clone.getElementsByTagName('input');
				for(i = 0; i < fields.length; i++)
				{
					fields[i].value = '';
				}
			}
		</script>

		<div id="mp_usps_rate" class="postbox">
			<h3 class='hndle'><span><?php _e('USPS Settings', 'mp'); ?></span></h3>
			<div class="inside">
				<img src="<?php echo $mp->plugin_url; ?>images/usps.png" />
				<p class="description">
					<?php _e('Using this USPS Shipping calculator requires requesting an Ecommerce API Username and Password. Get your free set of credentials <a target="_blank" href="https://secure.shippingapis.com/registration/">here &raquo;</a>', 'mp') ?><br />
					<?php _e('The password is no longer used for the API, just the username which you should enter below.', 'mp'); ?>
					<?php _e('The USPS test site has not yet been updated and currently doesn\'t work. You should just request activating your credentials with USPS and go live.', 'mp') ?>
				</p>

				<input type="hidden" name="mp_shipping_usps_meta" value="1" />
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e('USPS Username', 'mp') ?></th>
							<td><input type="text" name="mp[shipping][usps][api_username]" value="<?php echo esc_attr($this->usps_settings['api_username']); ?>" size="20" maxlength="20" /></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('USPS Maximum Weight per Package', 'mp') ?></th>

							<td>
								<input type="text" name="mp[shipping][usps][max_weight]" value="<?php echo esc_attr($this->usps_settings['max_weight']); ?>" size="20" maxlength="20" />
								<?php echo $this->get_units_weight(); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('USPS Request Mode', 'mp') ?></th>
							<td>
								<label>
									<input type="radio" name="mp[shipping][usps][online]" value="online" <?php checked($this->usps_settings['online'], 'online'); ?> />
									<?php _e('Online rates','mp'); ?>
								</label>&nbsp;&nbsp;&nbsp;
								<label>
									<input type="radio" name="mp[shipping][usps][online]" value="retail" <?php checked($this->usps_settings['online'], 'retail'); ?> />
									<?php _e('Retail rates','mp'); ?>
								</label>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('USPS Offered Domestic Services', 'mp') ?></th>
							<td>
								<?php foreach($this->services as $service => $detail): ?>
								<label>
									<input type="checkbox" name="mp[shipping][usps][services][<?php echo $service; ?>]" value="1" <?php checked( $this->usps_settings['services'][$service] ); ?> />&nbsp;<?php echo $detail->name . $detail->delivery; ?>
								</label>

								<?php
								if(isset($detail->max_weight) ):
								$max_weight = empty($this->usps_settings['flat_weights'][$service]) ? $detail->max_weight : $this->usps_settings['flat_weights'][$service];
								?>
								<?php _e('@ Max', 'mp'); ?> <input type="text" size="1" name="mp[shipping][usps][flat_weights][<?php echo $service; ?>]" value="<?php esc_attr_e( $max_weight); ?>" />
								<?php echo $this->get_units_weight(); ?>
								<?php endif; ?>

								<br />
								<?php endforeach;	?>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Handling Charge per Domestic Shipment ', 'mp') ?></th>
							<td>
								<input type="text" name="mp[shipping][usps][domestic_handling]" value="<?php echo (empty($this->usps_settings['domestic_handling']) ) ? '0.00' : esc_attr($this->usps_settings['domestic_handling']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('USPS Offered International Services', 'mp') ?></th>
							<td>
								<?php foreach($this->intl_services as $service => $detail): ?>
								<label>
									<input type="checkbox" name="mp[shipping][usps][intl_services][<?php echo $service; ?>]" value="1" <?php checked($this->usps_settings['intl_services'][$service]); ?> />&nbsp;<?php echo $detail->name; ?>
								</label>

								<?php
								if(isset($detail->max_weight) ):
								$max_weight = empty($this->usps_settings['flat_weights'][$service]) ? $detail->max_weight : $this->usps_settings['flat_weights'][$service];
								?>
								<?php _e('@ Max', 'mp'); ?> <input type="text" size="1" name="mp[shipping][usps][flat_weights][<?php echo $service; ?>]" value="<?php esc_attr_e($max_weight); ?>" />
								<?php echo $this->get_units_weight(); ?>
								<?php endif; ?>

								<br />
								<?php endforeach;	?>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Handling Charge per Interntional Shipment', 'mp') ?></th>
							<td>
								<input type="text" name="mp[shipping][usps][intl_handling]" value="<?php echo (empty($this->usps_settings['intl_handling']) ) ? '0.00' : esc_attr($this->usps_settings['intl_handling']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row" colspan="2">
								<?php _e('Standard Boxes and Weight Limits', 'mp') ?>
								<p>
									<span class="description">
										<?php _e('Enter your standard box sizes as LengthxWidthxHeight', 'mp') ?>
										( <b>12x8x6</b> )
										<?php _e('For each box defined enter the maximum weight it can contain.', 'mp') ?>
										<?php _e('Total weight selects the box size used for calculating Shipping costs.', 'mp') ?>
									</span>
								</p>
							</th>
						</tr>
						<tr>
							<td colspan="2">
								<table class="widefat" id="mp_shipping_boxes_table">
									<thead>
										<tr>
											<th scope="col" class="mp_box_name"><?php _e('Box Name', 'mp'); ?></th>
											<th scope="col" class="mp_box_dimensions"><?php _e('Box Dimensions', 'mp'); ?></th>
											<th scope="col" class="mp_box_weight"><?php _e('Max Weight per Box', 'mp'); ?></th>
											<th scope="col" class="mp_box_remove"></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$this->default_boxes();
										if ($this->usps_settings['boxes']) {
											foreach ( $this->usps_settings['boxes']['name'] as $key => $value){
												$this->box_row_html($key);
											}
										}
										//Add blank line for new entries. The non numeric $key says it's not in the array.
										$this->box_row_html('');
										?>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
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
		//handle domestic services checkboxes
		foreach ( $this->services as $service => $detail ) {
			if ( !isset($_POST['mp']['shipping']['usps']['services'][$service]) )
				$settings['shipping']['usps']['services'][$service] = 0;	
		}
		
		//handle international services checkboxes
		foreach ( $this->intl_services as $service => $detail ) {
			if ( !isset($_POST['mp']['shipping']['usps']['intl_services'][$service]) )
				$settings['shipping']['usps']['intl_services'][$service] = 0;	
		}

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
	*
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
	*
	* return float $price
	*/
	function calculate_shipping($price, $total, $cart, $address1, $address2, $city, $state, $zip, $country) {
		global $mp;


		if(! $this->crc_ok())
		{
			//Price added to this object
			$this->shipping_options($cart, $address1, $address2, $city, $state, $zip, $country);
		}

		$price = floatval($_SESSION['mp_shipping_info']['shipping_cost']);
		return $price;
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
		global $mp;

		//Check the shipping options to see if we already have a valid shipping price
		//
		/*
		if($this->crc_ok())
		{
		// Shipping prices still valid just use them
		// Format the returned array for display in the drop down
		$shipping_options = array();
		foreach ($_SESSION['mp_shipping_options'] as $service => $item) {
		$shipping_options[$service] = $this->format_shipping_option($service, $item['rate'], $item['delivery'], $item['handling']);
		}
		//All done
		return $shipping_options;
		}
		*/

		// Not ok then calculate them
		$settings = get_option('mp_settings');

		$this->weight = 0;
		$this->pound = 0;
		$this->ounces = 0;
		$this->width = 0;
		$this->length = 0;
		$this->height = 0;
		$this->girth = 0;
		$this->pkg_max = 0;
		$this->machinable = 'true';
		$this->size = 'REGULAR';

		$this->country = $country;
		$this->destination_zip = $zip;

		if( is_array($cart) ) {
			foreach ($cart as $product_id => $variations) {
				$shipping_meta = get_post_meta($product_id, 'mp_shipping', true);
				foreach($variations as $variation => $product) {
					$this->pkg_max = max($this->pkg_max, floatval($shipping_meta['weight']));
					$qty = $product['quantity'];
					$this->weight += floatval($shipping_meta['weight']) * $qty;
				}
			}
		}

		//If whole shipment is zero weight then there's nothing to ship. Return Free Shipping
		if($this->weight == 0){ //Nothing to ship
			$_SESSION['mp_shipping_info']['shipping_sub_option'] = __('Free Shipping', 'mp');
			$_SESSION['mp_shipping_info']['shipping_cost'] =  0;
			return array(__('Free Shipping', 'mp') => __('Free Shipping - 0.00', 'mp') );
		}

		// Got our totals  make sure we're in decimal pounds.
		$this->weight = $this->as_pounds($this->weight);
		$this->pkg_max = $this->as_pounds($this->pkg_max);

		//USPS won't accept a zero weight Package
		$this->weight = max($this->weight, 0.1);

		if (in_array($this->settings['base_country'], array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI'))){
			// Can't use zip+4
			$this->settings['base_zip'] = substr($this->settings['base_zip'], 0, 5);
		}

		if (in_array($this->country, array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI'))){
			// Can't use zip+4
			$this->destination_zip = substr($this->destination_zip, 0, 5);
			$shipping_options = $this->ratev4_request();
		} else {
			$shipping_options = $this->ratev2_request();
		}
		return $shipping_options;
	}


	/**For uasort below
	*/
	function compare_rates($a, $b){
		if($a['rate'] == $b['rate']) return 0;
		return ($a['rate'] < $b['rate']) ? -1 : 1;
	}

	function calculate_packages(){

		$this->usps_settings['max_weight'] = ( empty($this->usps_settings['max_weight'])) ? 50 : $this->usps_settings['max_weight'];

		//Assume equal size packages. Find the best matching box size
		$diff = floatval($this->usps_settings['max_weight']);
		$found = -1;
		$largest = -1.0;

		//See if it fits in one box
		foreach($this->usps_settings['boxes']['weight'] as $key => $weight) {
			//Find largest
			if( $weight > $largest) {
				$largest = $weight;
				$found = $key;
			}
			//If weight less
			if( floatval($this->weight) <= floatval($weight) ) {
				$found = $key;
				break;
			}
		}

		$allowed_weight = min($this->usps_settings['boxes']['weight'][$found], $this->usps_settings['max_weight']);

		if($allowed_weight >= $this->weight || $allowed_weight <= 0){
			$this->pkg_count = 1;
			$this->pkg_weight = $this->weight;
		} else {
			$this->pkg_count = ceil($this->weight / $allowed_weight); // Avoid zero
			$this->pkg_weight = $this->weight / $this->pkg_count;
		}

		//found our box
		$this->pkg_dims = explode('x', strtolower($this->usps_settings['boxes']['size'][$found]));
		foreach($this->pkg_dims as &$dim) $dim = $this->as_inches($dim);
		sort($this->pkg_dims); //Sort so two lowest values are used for Girth

		// Fixup pounds by converting multiples of 16 ounces to pounds
		$this->pounds = intval($this->pkg_weight);
		$this->ounces = round(($this->pkg_weight - $this->pounds) * 16);

		//If > 35 ponds it's a not machinable
		$this->machinable = ($this->pkg_weight > 35) ? 'false' : $this->machinable;
		//If largest dimension > 12 inches it's not machinable
		$this->machinable = ($this->pkg_dims[2] > 12) ? 'false' : $this->machinable;
	}

	/**
	* For USPS RateV4 Request Takes the set of allowed Shipping options and mp_settings and makes the API call to USPS
	* Return the set of valid shipping options for this order with prices added.
	*
	* @param array $shipping_options
	* @param array $settings
	*
	* return array $shipping_options
	*/
	function ratev4_request(){
		global $mp;
		
		$settings = get_option('mp_settings');
		$shipping_options = isset($settings['shipping']['usps']['services']) ? (array) $settings['shipping']['usps']['services'] : array();
		$temp = array();
		
		//determine which options are enabled
		foreach ( $shipping_options as $service => $enabled ) {
			if ( $enabled )
				$temp[$service] = $enabled;
		}
		
		$shipping_options = $temp;
		
		if ( count($shipping_options) == 0 )
			//no services enabled - bail
			return array();
			
		$this->calculate_packages();

		//Build XML. **Despite being XML the order of elements is important in a RateV4 request**
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$root = $dom->appendChild($dom->createElement('RateV4Request'));
		$root->setAttribute('USERID', $this->usps_settings['api_username']);
		$root->appendChild($dom->createElement('Revision','2'));


		//foreach( $shipping_options as $service => $name)

		$service = ($this->usps_shipping['online'] == 'online') ? 'ONLINE' : 'ALL';
		$package = $root->appendChild($dom->createElement('Package'));
		$package->setAttribute('ID', $service);
		$package->appendChild($dom->createElement('Service', $service));
		$package->appendChild($dom->createElement('ZipOrigination', $this->settings['base_zip']));
		$package->appendChild($dom->createElement('ZipDestination', $this->destination_zip));
		$package->appendChild($dom->createElement('Pounds', $this->pounds));
		$package->appendChild($dom->createElement('Ounces', $this->ounces));

		// If greater than 12" it's a LARGE parcel otherwise REGULAR
		$this->size = ($this->pkg_dims[2] > 12) ? 'LARGE' : 'REGULAR';

		$this->container = $this->size == 'LARGE' ? 'RECTANGULAR' : 'VARIABLE';

		$package->appendChild($dom->createElement('Container', $this->container));

		$package->appendChild($dom->createElement('Size', $this->size));

		if($this->size == 'LARGE')
		{
			$package->appendChild($dom->createElement('Width', $this->pkg_dims[1]));
			$package->appendChild($dom->createElement('Length', $this->pkg_dims[2]));
			$package->appendChild($dom->createElement('Height', $this->pkg_dims[0]));
			$package->appendChild($dom->createElement('Girth', 2 * ($this->pkg_dims[0] + $this->pkg_dims[1])));

			$package->appendChild($dom->createElement('Value', $total));  //For insurance?
		}

		$package->appendChild($dom->createElement('Machinable', $this->machinable));

		//We have the XML make the call
		$url = $this->production_uri . '?API=RateV4&XML=' . urlencode($dom->saveXML());

		$response = wp_remote_request($url, array('headers' => array('Content-Type: text/xml')) );
		if (is_wp_error($response)){
			return array('error' => '<div class="mp_checkout_error">' . $response->get_error_message() . '</div>');
		}
		else
		{
			$loaded = ($response['response']['code'] == '200');
			$body = $response['body'];
			if(! $loaded){
				return array('error' => '<div class="mp_checkout_error">' . $response['response']['code'] . "&mdash;" . $response['response']['message'] . '</div>');
			}
		}

		if ($loaded){
			libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			$dom->encoding = 'utf-8';
			$dom->loadHTML($body);
			libxml_clear_errors();
		}

		//Process the return XML

		//Clear any old price
		unset($_SESSION['mp_shipping_info']['shipping_cost']);

		$xpath = new DOMXPath($dom);
		
		//Make SESSION copy with just prices and delivery

		if(! is_array($shipping_options)) $shipping_options = array();
		$mp_shipping_options = $shipping_options;

		foreach($shipping_options as $service => $option){

			$box_count = $this->pkg_count;

			//Check for flat rate boxes
			if( isset( $this->services[$service]->max_weight ) ){ //Is it flat rate
				$max_weight = $this->as_pounds($this->usps_settings['flat_weights'][$service]);
				if( $this->pkg_max <= $max_weight ){
					$box_count = ceil($this->weight / $max_weight);
				}
			}

			$nodes = $xpath->query('//postage[@classid="' . $this->services[$service]->code . '"]/rate');
			$rate = floatval($nodes->item(0)->textContent) * $box_count;
			
			if ( $this->services[$service]->code == '0' ) {
				/* First class mail returns 4 sub types (Stamped Letter, Parcel,
				Large Envelope, Postcards). We need to get the PARCEL sub type or too low of
				a rate will get returned */
				$nodes_type = $xpath->query('//postage[@classid="' . $this->services[$service]->code . '"]/mailservice');
				
				for ( $i = 0; $i < $nodes_type->length; $i++ ) {
					$type = $nodes_type->item($i)->textContent;
					if ( strpos($type, 'Parcel') !== false ) {
						$rate = floatval($nodes->item($i)->textContent) * $box_count;
						break;
					}
				}
			}
			
			if ( $rate == 0 ) {  //Not available for this combination
				unset($mp_shipping_options[$service]);
			} else {
				$handling = floatval($this->usps_settings['domestic_handling']) * $box_count; // Add handling times number of packages.
				$delivery = $this->services[$service]->delivery;
				$mp_shipping_options[$service] = array('rate' => $rate, 'delivery' => $delivery, 'handling' => $handling);

				//match it up if there is already a selection
				if (! empty($_SESSION['mp_shipping_info']['shipping_sub_option'])){
					if ($_SESSION['mp_shipping_info']['shipping_sub_option'] == $service){
						$_SESSION['mp_shipping_info']['shipping_cost'] =  $rate + $handling;
					}
				}
			}
		}
		
		uasort($mp_shipping_options, array($this,'compare_rates') );

		$shipping_options = array();
		foreach($mp_shipping_options as $service => $options){
			$shipping_options[$service] = $this->format_shipping_option($service, $options['rate'], $options['delivery'], $options['handling']);
		}

		//Update the session. Save the currently calculated CRCs
		$_SESSION['mp_shipping_options'] = $mp_shipping_options;
		$_SESSION['mp_cart_crc'] = $this->crc($mp->get_cart_cookie());
		$_SESSION['mp_shipping_crc'] = $this->crc($_SESSION['mp_shipping_info']);

		unset($xpath);
		unset($dom);

		return $shipping_options;
	}

	/**
	* For USPS RateV4 Request Takes the set of allowed Shipping options and mp_settings and makes the API call to USPS
	* Return the set of valid shipping options for this order with prices added.
	*
	* @param array $shipping_options
	* @param array $settings
	*
	* return array $shipping_options
	*/
	function ratev2_request(){
		global $mp;

		$settings = get_option('mp_settings');
		$shipping_options = isset($settings['shipping']['usps']['intl_services']) ? (array) $settings['shipping']['usps']['intl_services'] : array();
		$temp = array();
		
		//determine which options are enabled
		foreach ( $shipping_options as $service => $enabled ) {
			if ( $enabled )
				$temp[$service] = $enabled;
		}
		
		$shipping_options = $temp;
		
		if ( count($shipping_options) == 0 )
			//no services enabled - bail
			return array();

		$this->calculate_packages();

		//Build XML. **Despite being XML the order of elements is important in a RateV2 request**
		$dom = new DOMDocument('1.0', 'utf-8');
		$root = $dom->appendChild($dom->createElement('IntlRateV2Request'));
		$root->setAttribute('USERID', $this->usps_settings['api_username']);
		$root->appendChild($dom->createElement('Revision','2'));


		//foreach( $shipping_options as $service => $name)

		$mail_type = 'All'; //($this->usps_shipping['online'] == 'online') ? 'ONLINE' : 'ALL';
		$package = $root->appendChild($dom->createElement('Package'));
		$package->setAttribute('ID', $mail_type);
		$package->appendChild($dom->createElement('Pounds', $this->pounds));
		$package->appendChild($dom->createElement('Ounces', $this->ounces));

		$package->appendChild($dom->createElement('Machinable', $this->machinable));
		$package->appendChild($dom->createElement('MailType', $mail_type));

		$gxg =$dom->createElement('GXG');
		$gxg->appendChild($dom->createElement('POBoxFlag', $this->po_box));
		$gxg->appendChild($dom->createElement('GiftFlag', $this->gift));
		//$package->appendChild($gxg);

		$package->appendChild($dom->createElement('ValueOfContents', $total));  //For insurance?
		$package->appendChild($dom->createElement('Country', $mp->countries[$this->country]));

		// If greater than 12" it's a LARGE parcel otherwise REGULAR
		$this->size = ($this->pkg_dims[2] > 12) ? 'LARGE' : 'REGULAR';

		$this->container = 'RECTANGULAR';  //$this->size == 'LARGE' ? 'RECTANGULAR' : 'VARIABLE';

		$package->appendChild($dom->createElement('Container', $this->container));
		$package->appendChild($dom->createElement('Size', $this->size));

		//if($this->size == 'LARGE')
		{
			$package->appendChild($dom->createElement('Width', $this->pkg_dims[1]));
			$package->appendChild($dom->createElement('Length', $this->pkg_dims[2]));
			$package->appendChild($dom->createElement('Height', $this->pkg_dims[0]));
			$package->appendChild($dom->createElement('Girth', 2 * ($this->pkg_dims[0] + $this->pkg_dims[1])));

		}
		$package->appendChild($dom->createElement('OriginZip', $this->settings['base_zip']));
		$package->appendChild($dom->createElement('CommercialFlag', 'N'));


		//We have the XML make the call
		$url = $this->production_uri . '?API=IntlRateV2&XML=' . urlencode($dom->saveXML());

		$response = wp_remote_request($url, array('headers' => array('Content-Type: text/xml')) );
		if (is_wp_error($response)){
			return array('error' => '<div class="mp_checkout_error">' . $response->get_error_message() . '</div>');
		}
		else
		{
			$loaded = ($response['response']['code'] == '200');
			$body = $response['body'];
			if(! $loaded){
				return array('error' => '<div class="mp_checkout_error">' . $response['response']['code'] . "&mdash;" . $response['response']['message'] . '</div>');
			}
		}

		if ($loaded){

			libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			$dom->encoding = 'utf-8';
			$dom->loadHTML($body);
			libxml_clear_errors();
		}

		//Process the return XML

		//Clear any old price
		unset($_SESSION['mp_shipping_info']['shipping_cost']);

		$xpath = new DOMXPath($dom);

		//Make SESSION copy with just prices
		if(! is_array($shipping_options)) $shipping_options = array();

		$mp_shipping_options = $shipping_options;

		foreach($shipping_options as $service => $option){

			$box_count = $this->pkg_count;

			//Check for flat rate boxes
			if( isset( $this->intl_services[$service]->max_weight ) ){ //Is it flat rate
				$max_weight = $this->as_pounds($this->usps_settings['flat_weights'][$service]);
				if( $this->pkg_max <= $max_weight ){
					$box_count = ceil($this->weight / $max_weight);
				}
			}

			$nodes = $xpath->query('//service[@id="' . $this->intl_services[$service]->code . '"]/postage');
			$rate = floatval($nodes->item(0)->textContent) * $box_count;

			$nodes = $xpath->query('//service[@id="' . $this->intl_services[$service]->code . '"]/svccommitments');
			$delivery = str_replace(' ', '', $nodes->item(0)->textContent);
			$delivery = '(' . str_replace('businessdays',') days', $delivery);

			if($rate == 0){  //Not available for this combination
				unset($mp_shipping_options[$service]);
			}
			else
			{
				$handling = floatval($this->usps_settings['intl_handling']) * $box_count; // Add handling times number of packages.
				$mp_shipping_options[$service] = array('rate' => $rate, 'delivery' => $delivery, 'handling' => $handling);

				//match it up if there is already a selection
				if (! empty($_SESSION['mp_shipping_info']['shipping_sub_option'])){
					if ($_SESSION['mp_shipping_info']['shipping_sub_option'] == $service){
						$_SESSION['mp_shipping_info']['shipping_cost'] =  $rate + $handling;
					}
				}
			}
		}

		uasort($mp_shipping_options, array($this, 'compare_rates') );

		$shipping_options = array();
		foreach($mp_shipping_options as $service => $options){
			$shipping_options[$service] = $this->format_shipping_option($service, $options['rate'], $options['delivery'], $options['handling']);
		}

		//Update the session. Save the currently calculated CRCs
		$_SESSION['mp_shipping_options'] = $mp_shipping_options;
		$_SESSION['mp_cart_crc'] = $this->crc($mp->get_cart_cookie());
		$_SESSION['mp_shipping_crc'] = $this->crc($_SESSION['mp_shipping_info']);

		unset($xpath);
		unset($dom);

		return $shipping_options;
	}

	/**
	* Tests the $_SESSION cart cookie and mp_shipping_info to see if the data changed since last calculated
	* Returns true if the either the crc for cart or shipping info has changed
	*
	* @return boolean true | false
	*/
	private function crc_ok(){
		global $mp;

		//Assume it changed
		$result = false;

		//Check the shipping options to see if we already have a valid shipping price
		if(isset($_SESSION['mp_shipping_options'])){
			//We have a set of prices. Are they still valid?
			//Did the cart change since last calculation
			if ( is_numeric($_SESSION['mp_shipping_info']['shipping_cost'])){

				if($_SESSION['mp_cart_crc'] == $this->crc($mp->get_cart_cookie())){
					//Did the shipping info change
					if($_SESSION['mp_shipping_crc'] == $this->crc($_SESSION['mp_shipping_info'])){
						$result = true;
					}
				}
			}
		}
		return $result;
	}

	/**Used to detect changes in shopping cart between calculations
	* @param (mixed) $item to calculate CRC of
	*
	* @return CRC32 of the serialized item
	*/
	public function crc($item = ''){
		return crc32(serialize($item));
	}

	// Conversion Helpers

	/**
	* Formats a choice for the Shipping options dropdown
	* @param array $shipping_option, a $this->services key
	* @param float $price, the price to display
	*
	* @return string, Formatted string with shipping method name delivery time and price
	*
	*/
	private function format_shipping_option($shipping_option = '', $price = '', $delivery = '', $handling=''){
		global $mp;
		if ( isset($this->services[$shipping_option])){
			$option = $this->services[$shipping_option]->name;
		}
		elseif ( isset($this->intl_services[$shipping_option])){
			$option = $this->intl_services[$shipping_option]->name;
		}

		$price = is_numeric($price) ? $price : 0;
		$handling = is_numeric($handling) ? $handling : 0;
		$total = $price + $handling;
		
		if ( $mp->get_setting('tax->tax_inclusive') && $mp->get_setting('tax->tax_shipping') ) {
			$total = $mp->shipping_tax_price($total);
		}

		$option .=  sprintf(__(' %1$s - %2$s', 'mp'), $delivery, $mp->format_currency('', $total));
		return $option;
	}

	/**
	* Returns an inch measurement depending on the current setting of [shipping] [system]
	* @param float $units
	*
	* @return float, Converted to the current units_used
	*/
	private function as_inches($units){
		$units = ($this->settings['shipping']['system'] == 'metric') ? floatval($units) / 2.54 : floatval($units);
		return round($units,2);
	}

	/**
	* Returns a pounds measurement depending on the current setting of [shipping] [system]
	* @param float $units
	*
	* @return float, Converted to pounds
	*/
	private function as_pounds($units){
		$units = ($this->settings['shipping']['system'] == 'metric') ? floatval($units) * 2.2 : floatval($units);
		return round($units, 2);
	}

	/**
	* Returns a the string describing the units of weight for the [mp_shipping][system] in effect
	*
	* @return string
	*/
	private function get_units_weight(){
		return ($this->settings['shipping']['system'] == 'english') ? __('Pounds','mp') : __('Kilograms', 'mp');
	}

	/**
	* Returns a the string describing the units of length for the [mp_shipping][system] in effect
	*
	* @return string
	*/
	private function get_units_length(){
		return ($this->settings['shipping']['system'] == 'english') ? __('Inches','mp') : __('Centimeters', 'mp');
	}

} //End MP_Shipping_USPS

if(! class_exists('USPS_Service') ):
class USPS_Service
{
	public $code;
	public $name;
	public $delivery;
	public $max_weight;

	function __construct($code, $name, $delivery = '', $max_weight = null)
	{
		$this->code = $code;
		$this->name = $name;
		$this->delivery = $delivery;
		$this->max_weight = $max_weight;

	}
}
endif;


//register plugin as calculated. Only in US and US Possesions
$settings = get_option('mp_settings');
if ( in_array( $settings['base_country'], array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI', 'VI') ) ) {
	mp_register_shipping_plugin( 'MP_Shipping_USPS', 'usps', __('USPS', 'mp'), true );
}