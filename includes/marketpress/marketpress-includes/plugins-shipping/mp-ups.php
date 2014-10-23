<?php
/*
MarketPress UPS Calculated Shipping Plugin
Author: Arnold Bailey (Incsub)
*/
class MP_Shipping_UPS extends MP_Shipping_API {

	//private shipping method name. Lowercase alpha (a-z) and dashes (-) only please!
	public $plugin_name = 'ups';

	//public name of your method, for lists and such.
	public $public_name = '';

	//set to true if you need to use the shipping_metabox() method to add per-product shipping options
	public $use_metabox = true;

	//set to true if you want to add per-product extra shipping cost field
	public $use_extra = true;

	//set to true if you want to add per-product weight shipping field
	public $use_weight = true;

	//Test sandboxed URI for UPS Rates API
	public $sandbox_uri = 'https://wwwcie.ups.com/ups.app/xml/Rate';

	//Production Live URI for UPS Rates API
	public $production_uri = 'https://onlinetools.ups.com/ups.app/xml/Rate';

	// Defines the available shipping Services and their display names
	public $services = array();

	private $settings = '';
	private $ups_settings;

	//Set to display any errors in the Rate calculations.
	private $rate_error = '';

	/**
	* Runs when your class is instantiated. Use to setup your plugin instead of __construct()
	*/
	function on_creation() {
		global $mp;
		
		//set name here to be able to translate
		$this->public_name = __('UPS', 'mp');

		//US Domestic services
		$this->services = array(

		'Next Day Air'           => new UPS_Service('01', __('Next Day Air', 'mp'),           __('(Next Day)', 'mp')),
		'2nd Day Air'            => new UPS_Service('02', __('2nd Day Air', 'mp'),            __('(2 Days)', 'mp')),
		'Ground'                 => new UPS_Service('03', __('Ground', 'mp'),                 __('(1-5 Days)', 'mp')),
		'3 Day Select'           => new UPS_Service('12', __('3 Day Select', 'mp'),           __('(3 Days)', 'mp')),
		'Next Day Air Saver'     => new UPS_Service('13', __('Next Day Air Saver', 'mp'),     __('(1 Day)', 'mp')),
		'Next Day Air Early AM'  => new UPS_Service('14', __('Next Day Air Early AM', 'mp'),  __('(1 Days)', 'mp')),
		'2nd Day Air AM'         => new UPS_Service('59', __('2nd Day Air AM', 'mp'),         __('(2 Days)', 'mp')),
		'Worldwide Express' 		 => new UPS_Service('07', __('Worldwide Express', 'mp'),      __('(1-3 Days)', 'mp')),
		'Worldwide Expedited'    => new UPS_Service('08', __('Worldwide Expedited', 'mp'),    __('(2-5 Days)', 'mp') ),
		'Standard'               => new UPS_Service('11', __('Standard', 'mp'),               __('(Scheduled)', 'mp') ),
		'Worldwide Express Plus' => new UPS_Service('54', __('Worldwide Express Plus', 'mp'), __('(1-3 Days)', 'mp') ),
		'Saver'                  => new UPS_Service('65', __('Saver', 'mp'),                  __('(1-5 Days)', 'mp') ),
		);
		
		//		//International Services
		//		$this->intl_services = array(
		//		'Worldwide Express' 		 => new UPS_Service('07', __('Worldwide Express', 'mp') ),
		//		'Worldwide Expedited'    => new UPS_Service('08', __('Worldwide Expedited', 'mp') ),
		//		'Standard'               => new UPS_Service('11', __('Standard', 'mp') ),
		//		'Worldwide Express Plus' => new UPS_Service('54', __('Worldwide Express Plus', 'mp') ),
		//		'Saver'                  => new UPS_Service('65', __('Saver', 'mp') ),
		//		);

		// Get settings for convenience sake
		$this->settings = get_option('mp_settings');
		$this->ups_settings =& $this->settings['shipping']['ups'];
		
		// Added in 2.9.5.1 - make sure we set the old default which was daily pickup
		if ( ! isset($this->ups_settings['pickup_type']) ) {
			$this->ups_settings['pickup_type'] = '01';
			update_option('mp_settings', $this->settings);
		}
	}

	function default_boxes(){
		// Initialize the default boxes if nothing there
		if(count($this->ups_settings['boxes']['name']) < 2)
		{
			$this->ups_settings['boxes'] = array (
			'name' =>
			array (
			0 => 'Small Express',
			1 => 'Medium Express',
			2 => 'Large Express',
			3 => 'UPS 10KG',
			4 => 'UPS 25KG',
			),
			'size' =>
			array (
			0 => '13x11x2',
			1 => '15x11x3',
			2 => '18x13x3',
			3 => '17x13x11',
			4 => '19x17x14',
			),
			'weight' =>
			array (
			0 => '10',
			1 => '20',
			2 => '30',
			3 => '22',
			4 => '55',
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
			$name = $this->ups_settings['boxes']['name'][$key];
			$size = $this->ups_settings['boxes']['size'][$key];
			$weight = $this->ups_settings['boxes']['weight'][$key];
			if (empty($name) && empty($size) &empty($weight)) return''; //rows blank, don't need it
		}
		?>
		<tr class="variation">
			<td class="mp_box_name">
				<input type="text" name="mp[shipping][ups][boxes][name][]" value="<?php echo esc_attr($name); ?>" size="18" maxlength="20" />
			</td>
			<td class="mp_box_dimensions">
				<label>
					<input type="text" name="mp[shipping][ups][boxes][size][]" value="<?php echo esc_attr($size); ?>" size="10" maxlength="20" />
					<?php echo $this->get_units_length(); ?>
				</label>
			</td>
			<td class="mp_box_weight">
				<label>
					<input type="text" name="mp[shipping][ups][boxes][weight][]" value="<?php echo esc_attr($weight); ?>" size="6" maxlength="10" />
					<?php echo $this->get_units_weight(); ?>
				</label>
			</td>
			<?php if ( is_numeric($key) ): ?>

			<td class="mp_box_remove">
				<a onclick="upsDeleteBox(this);" href="#mp_ups_boxes_table" title="<?php _e('Remove Box', 'mp'); ?>" ></a>
			</td>

			<?php else: ?>

			<td class="mp_box_add">
				<a onclick="upsAddBox(this);" href="#mp_ups_boxes_table" title="<?php _e('Add Box', 'mp'); ?>" ></a>
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
	function shipping_settings_box($settings)
	{
		global $mp;

		$this->settings = $settings;
		$this->ups_settings = $this->settings['shipping']['ups'];
		$system = $this->settings['shipping']['system']; //Current Unit settings english | metric
		?>

		<script type="text/javascript">
			//Remove a row in the Boxes table
			function upsDeleteBox(row)
			{
				var i = row.parentNode.parentNode.rowIndex;
				document.getElementById('mp_ups_boxes_table').deleteRow(i);
			}

			function upsAddBox(row)
			{
				//Adds an Empty Row
				var clone = row.parentNode.parentNode.cloneNode(true);
				document.getElementById('mp_ups_boxes_table').appendChild(clone);
				var fields = clone.getElementsByTagName('input');
				for(i = 0; i < fields.length; i++)
				{
					fields[i].value = '';
				}
			}
		</script>

		<div id="mp_ups_rate" class="postbox">
			<h3 class='hndle'><span><?php _e('UPS Settings', 'mp'); ?></span></h3>
			<div class="inside">
				<img src="<?php echo $mp->plugin_url; ?>images/ups.jpg" />
				<input type="hidden" name="mp_shipping_ups_meta" value="1" />
				<table class="form-table">
					<tbody>
						<tr>
							<th colspan="2">
								<?php _e('In order to use UPS, you will need a UPS Developer Kit access key ', 'mp') ?>
								<?php _e('and the UPS user ID and password associated with the access key.  Set these up for free <a href="https://www.ups.com/upsdeveloperkit" target="_blank">here</a>.  ', 'mp') ?>
								<?php _e('If this information is missing or incorrect, an error will appear during the checkout process and the buyer will not be able to complete the transaction.', 'mp') ?>
							</th>
						</tr>

						<tr>
							<th scope="row">
								<?php _e('UPS Sandbox Mode', 'mp') ?>
							</th>
							<td>
								<label><input type="radio" name="mp[shipping][ups][sandbox]" value="1" <?php checked(! empty($this->ups_settings['sandbox'])); ?> /> <?php _e('Sandbox', 'mp'); ?></label><br />
								<label><input type="radio" name="mp[shipping][ups][sandbox]" value="0" <?php checked(empty($this->ups_settings['sandbox'])); ?> /> <?php _e('Production', 'mp'); ?></label>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php _e('UPS Developer Kit Access Key', 'mp') ?>
							</th>
							<td>
								<input type="text" name="mp[shipping][ups][api_key]" value="<?php echo esc_attr($this->ups_settings['api_key']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php _e('UPS User ID', 'mp') ?>
							</th>
							<td>
								<input type="text" name="mp[shipping][ups][user_id]" value="<?php echo esc_attr($this->ups_settings['user_id']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php _e('UPS Password', 'mp') ?>
							</th>
							<td>
								<input type="password" name="mp[shipping][ups][password]" value="<?php echo esc_attr($this->ups_settings['password']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row">
								<?php _e('UPS Shipper Number', 'mp') ?>
								<br /><span class="description"><?php _e('Required if using negotiated rates','mp'); ?></span>
							</th>
							<td>
								<input type="text" name="mp[shipping][ups][shipper_number]" value="<?php echo esc_attr($this->ups_settings['shipper_number']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('UPS Offered Domestic Services', 'mp') ?></th>
							<td>
								<?php foreach($this->services as $name => $service) : ?>
								<label>
									<input type="checkbox" name="mp[shipping][ups][services][<?php echo $name; ?>]" value="1" <?php checked($this->ups_settings['services'][$name], 1); ?> />&nbsp;<?php echo $service->name; ?>
								</label><br />
								<?php endforeach; ?>
							</td>
						</tr>
						
						<tr>
							<th scope="row">
								<?php _e('Pickup type', 'mp') ?>
								<br /><span class="description"><?php _e('For the most accurate rates, please select the appropriate pick up type for your business.','mp'); ?></span>
							</th>
							<td>
								<?php foreach ( array('01' => __('Daily Pickup', 'mp'), '03' => __('Customer Counter', 'mp'), '06' => __('One Time Pickup', 'mp')) as $code => $label ) : ?>
								<label>
									<input type="radio" name="mp[shipping][ups][pickup_type]" value="01" <?php checked($code, $this->ups_settings['pickup_type']); ?> /> <?php echo $label; ?>
								</label><br />
								<?php endforeach; ?>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Handling Charge per Box Shipped', 'mp') ?></th>
							<td>
								<input type="text" name="mp[shipping][ups][domestic_handling]" value="<?php echo (empty($this->ups_settings['domestic_handling']) ) ? '0.00' : esc_attr($this->ups_settings['domestic_handling']); ?>" size="20" maxlength="20" />
							</td>
						</tr>
						<!--
						<tr>
						<th scope="row">
						<?php _e('Default Weight (for products whose weights are not specified)', 'mp') ?>
						</th>
						<td>
						<input type="text" name="mp[shipping][ups][default_weight]" value="<?php echo esc_attr($this->ups_settings['default_weight']); ?>" size="20" maxlength="20" />
						</td>
						</tr>
						-->
						<tr>
							<td colspan="2">
								<?php _e('Standard Boxes and Weight Limits', 'mp') ?>
								<p>
									<span class="description">
										<?php _e('Enter your standard box sizes as LengthxWidthxHeight', 'mp') ?> (<b>e.g. 12x8x6</b>)
										<?php _e('For each box defined enter the maximum weight it can contain.', 'mp') ?>
									</span>
								</p>
								<table class="widefat" id="mp_ups_boxes_table">
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
										if($this->ups_settings['boxes'])
										{
											foreach($this->ups_settings['boxes']['name'] as $key => $value)
											{
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

						<tr>
							<th scope="row" colspan="2">
								<p>
									<span class="description">
										<?php _e('Note: the shipping prices this plugin calculates are estimates.  If they are consistently too low or too high, please check that the list of boxes above and the product weights are accurate and complete.', 'mp') ?>
									</span>
								</p>
							</th>
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
		foreach ( $this->services as $service => $class ) {
			if ( isset($_POST['mp']['shipping']['ups']['services'][$service]) ) {
				$settings['shipping']['ups']['services'][$service] = 1;
			} else {
				$settings['shipping']['ups']['services'][$service] = 0;
			}
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

		$shipping_options = array();

		$this->address1 = $address1;
		$this->address2 = $address2;
		$this->city = $city;
		$this->state = $state;
		$this->destination_zip = $zip;
		$this->country = $country;

		if( is_array($cart) ) {
			$shipping_meta['weight'] = (is_numeric($shipping_meta['weight']) ) ? $shipping_meta['weight'] : 0;
			foreach ($cart as $product_id => $variations) {
				$shipping_meta = get_post_meta($product_id, 'mp_shipping', true);
				foreach($variations as $variation => $product) {
					$qty = $product['quantity'];
					$weight = (empty($shipping_meta['weight']) ) ? $this->ups_settings['default_weight'] : $shipping_meta['weight'];
					$this->weight += floatval($weight) * $qty;
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

		//ups won't accept a zero weight Package
		$this->weight = ($this->weight == 0) ? 0.1 : $this->weight;

		$max_weight = floatval($this->ups[max_weight]);
		$max_weight = ($max_weight > 0) ? $max_weight : 75;

		//Properties should already be converted to weight in decimal pounds and Pounds and Ounces
		//Figure out how many boxes
		$this->pkg_count = ceil($this->weight / $max_weight); // Avoid zero
		// Equal size packages.
		$this->pkg_weight = $this->weight / $this->pkg_count;

		// Fixup pounds by converting multiples of 16 ounces to pounds
		$this->pounds = intval($this->pkg_weight);
		$this->ounces = round(($this->pkg_weight - $this->pounds) * 16);

		if($this->settings['base_country'] == 'US') {
			// Can't use zip+4
			$this->settings['base_zip'] = substr($this->settings['base_zip'], 0, 5);
		}

		if($this->country == 'US') {
			// Can't use zip+4
			$this->destination_zip = substr($this->destination_zip, 0, 5);
		}

		$shipping_options = $this->rate_request();
		
		return $shipping_options;

	}

	/**For uasort below
	*/
	function compare_rates($a, $b){
		if($a['rate'] == $b['rate']) return 0;
		return ($a['rate'] < $b['rate']) ? -1 : 1;
	}

	/**
	* rate_request - Makes the actual call to UPS
	*/
	function rate_request() {
		global $mp;

		$shipping_options = array_filter($this->ups_settings['services'], create_function('$val', 'return ($val == 1);'));

		//Assume equal size packages. Find the best matching box size
		$this->ups_settings['max_weight'] = ( empty($this->ups_settings['max_weight'])) ? 50 : $this->ups_settings['max_weight'];
		$diff = floatval($this->ups_settings['max_weight']);
		$found = -1;
		$largest = -1.0;

		foreach($this->ups_settings['boxes']['weight'] as $key => $weight) {
			//			//Find largest
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

		if($this->ups_settings['boxes']['weight'][$found] >= $this->weight){
			$this->pkg_count = 1;
			$this->pkg_weight = $this->weight;
		} else {
			$this->pkg_count = ceil($this->weight / $this->ups_settings['boxes']['weight'][$found]); // Avoid zero
			$this->pkg_weight = $this->weight / $this->pkg_count;
		}

		// Fixup pounds by converting multiples of 16 ounces to pounds
		$this->pounds = intval($this->pkg_weight);
		$this->ounces = round(($this->pkg_weight - $this->pounds) * 16);

		//found our box
		$dims = explode('x', strtolower($this->ups_settings['boxes']['size'][$found]));
		foreach($dims as &$dim) $dim = $this->as_inches($dim);

		sort($dims); //Sort so two lowest values are used for Girth

		//Build Authorization XML
		$auth_dom = new DOMDocument('1.0', 'utf-8');
		$auth_dom->formatOutput = true;
		$root = $auth_dom->appendChild($auth_dom->createElement('AccessRequest'));
		$root->setAttribute('xml:lang', 'en-US');
		$root->appendChild($auth_dom->createElement('AccessLicenseNumber',$this->ups_settings['api_key']));
		$root->appendChild($auth_dom->createElement('UserId',$this->ups_settings['user_id']));
		$root->appendChild($auth_dom->createElement('Password',$this->ups_settings['password']));

		//Rate request XML
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$root = $dom->appendChild($dom->createElement('RatingServiceSelectionRequest'));
		$root->setAttribute('xml:lang', 'en-US');

		$request = $root->appendChild($dom->createElement('Request'));

		$transaction = $request->appendChild($dom->createElement('TransactionReference'));
		$transaction->appendChild($dom->createElement('CustomerContext','MarketPress Rate Request'));
		$transaction->appendChild($dom->createElement('XpciVersion','1.0001'));

		$request->appendChild($dom->createElement('RequestAction', 'Rate'));
		$request->appendChild($dom->createElement('RequestOption', 'Shop'));

		$pickup = $root->appendChild($dom->createElement('PickupType'));
		$pickup->appendChild($dom->createElement('Code', $this->ups_settings['pickup_type']));

		//Shipper
		$shipment = $root->appendChild($dom->createElement('Shipment'));
		$shipment->appendChild($dom->createElement('NegotiatedRatesIndicator'));
		$shipper = $shipment->appendChild($dom->createElement('Shipper'));
		$shipper->appendChild($dom->createElement('ShipperNumber', htmlentities($this->ups_settings['shipper_number'])));
		$address = $shipper->appendChild($dom->createElement('Address'));
		$address->appendChild($dom->createElement('StateProvinceCode', $this->settings['base_province']));
		$address->appendChild($dom->createElement('PostalCode', $this->settings['base_zip']));
		$address->appendChild($dom->createElement('CountryCode', $this->settings['base_country']));
		//Ship to
		$shipto = $shipment->appendChild($dom->createElement('ShipTo'));
		$address = $shipto->appendChild($dom->createElement('Address'));
		$address->appendChild($dom->createElement('AddressLine1', $this->address1));
		$address->appendChild($dom->createElement('AddressLine2', $this->address2));
		$address->appendChild($dom->createElement('City', $this->city));
		$address->appendChild($dom->createElement('StateProvinceCode', $this->state));
		$address->appendChild($dom->createElement('PostalCode', $this->destination_zip));
		$address->appendChild($dom->createElement('CountryCode', $this->country));
		//Ship from
		$shipfrom = $shipment->appendChild($dom->createElement('ShipFrom'));
		$address = $shipfrom->appendChild($dom->createElement('Address'));
		$address->appendChild($dom->createElement('StateProvinceCode', $this->settings['base_province']));
		$address->appendChild($dom->createElement('PostalCode', $this->settings['base_zip']));
		$address->appendChild($dom->createElement('CountryCode', $this->settings['base_country']));
		//Package
		$package = $shipment->appendChild($dom->createElement('Package'));

		$packaging_type = $package->appendChild($dom->createElement('PackagingType') );
		$packaging_type->appendChild($dom->createElement('Code', '00'));

		//Dimensions
		$dimensions = $package->appendChild($dom->createElement('Dimensions') );
		$uom = $dimensions->appendChild($dom->createElement('UnitOfMeasurement') );
		$uom->appendChild($dom->createElement('Code', ( $mp->get_setting('shipping->system', 'english') == 'english' ) ? 'IN' : 'CM'));
		$dimensions->appendChild($dom->createElement('Length', $dims[1]) );
		$dimensions->appendChild($dom->createElement('Width', $dims[2]) );
		$dimensions->appendChild($dom->createElement('Height', $dims[0]) );
		
		//Weight
		$package_weight = $package->appendChild($dom->createElement('PackageWeight') );
		$uom = $package_weight->appendChild($dom->createElement('UnitOfMeasurement') );
		$uom->appendChild($dom->createElement('Code', ( $mp->get_setting('shipping->system', 'english') == 'english' ) ? 'LBS' : 'KGS'));
		$package_weight->appendChild($dom->createElement('Weight', $this->pkg_weight) );


		//We have the XML make the call
		$url = ($this->ups_settings['sandbox']) ? $this->sandbox_uri : $this->production_uri;

		$response = wp_remote_request($url, array(
			'headers' => array('Content-Type: text/xml'),
			'method' => 'POST',
			'body' => $auth_dom->saveXML() . $dom->saveXML(),
			'sslverify' => false,
		));
		
		if (is_wp_error($response)){
			return array('error' => '<div class="mp_checkout_error">UPS: ' . $response->get_error_message() . '</div>');
		}
		else
		{
			$loaded = ($response['response']['code'] == '200');
			$body = $response['body'];
			if(! $loaded){
				return array('error' => '<div class="mp_checkout_error">UPS: ' . $response['response']['code'] . "&mdash;" . $response['response']['message'] . '</div>');
			}
		}

		if ($loaded){

			libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			$dom->encoding = 'utf-8';
			$dom->formatOutput = true;
			$dom->loadHTML($body);
			libxml_clear_errors();
		}

		//Process the return XML
		//Clear any old price
		unset($_SESSION['mp_shipping_info']['shipping_cost']);

		$xpath = new DOMXPath($dom);

		//Check for errors
		$nodes = $xpath->query('//responsestatuscode');
		if($nodes->item(0)->textContent == '0'){
			$nodes = $xpath->query('//errordescription');
			$this->rate_error = $nodes->item(0)->textContent;
			return array('error' => '<div class="mp_checkout_error">' . $this->rate_error . '</div>');
		}

		//Good to go
		//Make SESSION copy with just prices and delivery

		if(! is_array($shipping_options)) $shipping_options = array();
		$mp_shipping_options = $shipping_options;
		foreach($shipping_options as $service => $option){
			$nodes = $xpath->query('//ratedshipment[service/code="' . $this->services[$service]->code . '"]/totalcharges/monetaryvalue');
			$rate = floatval($nodes->item(0)->textContent) * $this->pkg_count;

			if($rate == 0){  //Not available for this combination
				unset($mp_shipping_options[$service]);
			}
			else
			{
				$handling = floatval($this->ups_settings['domestic_handling']) * $this->pkg_count; // Add handling times number of packages.
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

	/**Used to detect changes in shopping cart between calculations
	* @param (mixed) $item to calculate CRC of
	*
	* @return CRC32 of the serialized item
	*/
	public function crc($item = ''){
		return crc32(serialize($item));
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
		if ( isset($this->services[$shipping_option]) ) {
			$option = $this->services[$shipping_option]->name;
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

} //End MP_Shipping_UPS


if(! class_exists('UPS_Service') ):
class UPS_Service
{
	public $code;
	public $name;
	public $delivery;
	public $rate;

	function __construct($code, $name, $delivery, $rate = null)
	{
		$this->code = $code;
		$this->name = $name;
		$this->delivery = $delivery;
		$this->rate = $rate;

	}
}
endif;

if(! class_exists('Box_Size') ):
class Box_Size
{
	public $length;
	public $width;
	public $height;

	function __construct($length, $width, $height)
	{
		$this->length = $length;
		$this->width = $width;
		$this->height = $height;
	}
}
endif;

//register plugin only in US and US Possesions

$settings = get_option('mp_settings');

//if(in_array($settings['base_country'], array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI')))
{
	mp_register_shipping_plugin('MP_Shipping_UPS', 'ups', __('UPS (beta)', 'mp'), true);
}
