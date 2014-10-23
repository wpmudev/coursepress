<?php
/*
MarketPress Example Shipping Plugin Template
*/

class MP_Shipping_FedEx extends MP_Shipping_API {

	//private shipping method name. Lowercase alpha (a-z) and dashes (-) only please!
	public $plugin_name = 'fedex';

	//public name of your method, for lists and such.
	public $public_name = '';

	//set to true if you need to use the shipping_metabox() method to add per-product shipping options
	public $use_metabox = true;

	//set to true if you want to add per-product weight shipping field
	public $use_weight = true;

	//set to true if you want to add per-product extra shipping cost field
	public $use_extra = true;

	public $sandbox_uri = 'https://wsbeta.fedex.com:443/web-services';

	public $production_uri = 'https://ws.fedex.com:443/web-services';

	/**
	* Runs when your class is instantiated. Use to setup your plugin instead of __construct()
	*/
	function on_creation() {
		//declare here for translation
		$this->public_name = __('FedEx', 'mp');
		//US Domestic services
		$this->services = array(
		'FIRST_OVERNIGHT'                   => new FedEx_Service('FIRST_OVERNIGHT',        __('First Overnight', 'mp'),        __('(1 Day am)', 'mp') ),
		'PRIORITY_OVERNIGHT'                => new FedEx_Service('PRIORITY_OVERNIGHT',     __('Priority Overnight', 'mp'),     __('(1 Day am )', 'mp') ),
		'STANDARD_OVERNIGHT'                => new FedEx_Service('STANDARD_OVERNIGHT',     __('Standard Overnight', 'mp'),     __('(1 Day)', 'mp') ),
		//		'FEDEX_1_DAY_FREIGHT'               => new FedEx_Service('FEDEX_1_DAY_FREIGHT',    __('Fedex 1 Day Freight', 'mp'),    __('(1 Day)', 'mp')),
		'FEDEX_2_DAY_AM'                    => new FedEx_Service('FEDEX_2_DAY_AM',         __('Fedex 2 Day AM', 'mp'),         __('(2 Days am)', 'mp')),
		'FEDEX_2_DAY'                       => new FedEx_Service('FEDEX_2_DAY',            __('Fedex 2 Day', 'mp'),            __('(2 Days)', 'mp')),
		//		'FEDEX_2_DAY_FREIGHT'               => new FedEx_Service('FEDEX_2_DAY_FREIGHT',    __('Fedex 2 Day Freight', 'mp'),    __('(2 Days)', 'mp')),
		//		'FEDEX_3_DAY_FREIGHT'               => new FedEx_Service('FEDEX_3_DAY_FREIGHT',    __('Fedex 3 Day Freight', 'mp'),    __('(3 Days)', 'mp')),
		'FEDEX_EXPRESS_SAVER'               => new FedEx_Service('FEDEX_EXPRESS_SAVER',    __('Fedex Express Saver', 'mp'),    __('(3 Days)', 'mp')),
		//		'FEDEX_FIRST_FREIGHT'               => new FedEx_Service('FEDEX_FIRST_FREIGHT',    __('Fedex First Freight', 'mp'),    __('(1-3 Days)', 'mp')),
		//		'FEDEX_FREIGHT_ECONOMY'             => new FedEx_Service('FEDEX_FREIGHT_ECONOMY',  __('Fedex Freight Economy', 'mp'),  __('(2-5 Days)', 'mp') ),
		//		'FEDEX_FREIGHT_PRIORITY'            => new FedEx_Service('FEDEX_FREIGHT_PRIORITY', __('Fedex Freight Priority', 'mp'), __('(Scheduled)', 'mp') ),
		'FEDEX_GROUND'                      => new FedEx_Service('FEDEX_GROUND',           __('Fedex Ground', 'mp'),           __('(1-7 Days)', 'mp') ),
		'GROUND_HOME_DELIVERY'              => new FedEx_Service('GROUND_HOME_DELIVERY',   __('Ground Home Delivery', 'mp'),   __('(1-5 Days)', 'mp') ),
		'SMART_POST'                        => new FedEx_Service('SMART_POST',             __('Smart Post', 'mp'),             __('(2-7 Days)', 'mp') ),
		);

		//International Services
		$this->intl_services = array(
		'INTERNATIONAL_ECONOMY'             => new FedEx_Service('INTERNATIONAL_ECONOMY',  __('International Economy', 'mp'),  __('(5 Days)', 'mp') ),
		//		'INTERNATIONAL_ECONOMY_FREIGHT'     => new FedEx_Service('INTERNATIONAL_ECONOMY_FREIGHT', __('International Economy Freight', 'mp'),                  __('(1-5 Days)', 'mp') ),
		'INTERNATIONAL_FIRST'               => new FedEx_Service('INTERNATIONAL_FIRST',    __('International First', 'mp'),     __('(1-3 Days)', 'mp') ),
		'INTERNATIONAL_PRIORITY'            => new FedEx_Service('INTERNATIONAL_PRIORITY', __('International Priority', 'mp'),  __('(1-3 Days)', 'mp') ),
		//		'INTERNATIONAL_PRIORITY_FREIGHT'    => new FedEx_Service('INTERNATIONAL_PRIORITY_FREIGHT', __('International Priority Freight', 'mp'),                  __('(1-5 Days)', 'mp') ),
		'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => new FedEx_Service('EUROPE_FIRST_INTERNATIONAL_PRIORITY', __('Europe First International Priority', 'mp'),     __('(Next Day)', 'mp')),
		);

		// Get settings for convenience sake
		$this->settings = get_option('mp_settings');
		$this->fedex_settings = $this->settings['shipping']['fedex'];

	}

	function default_boxes(){
		// Initialize the default boxes if nothing there
		if(count($this->fedex_settings['boxes']['name']) < 2)
		{
			$this->fedex_settings['boxes'] = array (
			'name' =>
			array (
			0 => 'Small Box',
			1 => 'Medium Box',
			2 => 'Large Box',
			3 => 'FedEx 10KG',
			4 => 'FedEx 25KG',
			),
			'size' =>
			array (
			0 => '12x11x2',
			1 => '13x12x3',
			2 => '18x13x3',
			3 => '16x16x10',
			4 => '22x17x13',
			),
			'weight' =>
			array (
			0 => '20',
			1 => '20',
			2 => '20',
			3 => '22',
			4 => '56',
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

		$this->residential = true;
		if ( empty($this->fedex_settings['commercial']) ) { //force residential
			$content .= '<input type="hidden" name="residential" value="1" />';
			$_SESSION['mp_shipping_info']['residential'] = true;
		} else {

			if ( isset($_SESSION['mp_shipping_info']['residential']) ) {
				$checked = $_SESSION['mp_shipping_info']['residential'];
			} else {
				$checked = true; //default to checked
				$_SESSION['mp_shipping_info']['residential'] = true;
			}

			$this->residential = $checked;

			$content .= '<tr>
			<td>' . __('Residential Delivery', 'mp') . '</td>
			<td>
			<input type="hidden" name="residential" value="0" />
			<input id="mp_residential" type="checkbox" name="residential" value="1" ' . checked($checked, true, false) .' />
			<small><em>' . __('Check if delivery is to a residence.', 'mp') . '</em></small>
			</td>
			</tr>';
		}

		return $content;
	}

	/**
	* Use this to process any additional field you may add. Use the $_POST global,
	*  and be sure to save it to both the cookie and usermeta if logged in.
	*/
	function process_shipping_form() {
		if(isset($_POST['residential']) ) {
			$_SESSION['mp_shipping_info']['residential'] = $_POST['residential'];
			$this->residential = $_SESSION['mp_shipping_info']['residential'];
		}
	}

	/**
	* Echoes one row for boxes data. If $key is non-numeric then emit a blank row for new entry
	*
	* @ $key
	*
	* @ returns HTML for one row
	*/
	private function box_row_html($key='') {

		$name = '';
		$size = '';
		$weight = '';

		if ( is_numeric($key) ){
			$name = $this->fedex_settings['boxes']['name'][$key];
			$size = $this->fedex_settings['boxes']['size'][$key];
			$weight = $this->fedex_settings['boxes']['weight'][$key];
			if (empty($name) && empty($size) &empty($weight)) return''; //rows blank, don't need it
		}
		?>
		<tr class="variation">
			<td class="mp_box_name">
				<input type="text" name="mp[shipping][fedex][boxes][name][]" value="<?php echo esc_attr($name); ?>" size="18" maxlength="20" />
			</td>
			<td class="mp_box_dimensions">
				<label>
					<input type="text" name="mp[shipping][fedex][boxes][size][]" value="<?php echo esc_attr($size); ?>" size="10" maxlength="20" />
					<?php echo $this->get_units_length(); ?>
				</label>
			</td>
			<td class="mp_box_weight">
				<label>
					<input type="text" name="mp[shipping][fedex][boxes][weight][]" value="<?php echo esc_attr($weight); ?>" size="6" maxlength="10" />
					<?php echo $this->get_units_weight(); ?>
				</label>
			</td>
			<?php if ( is_numeric($key) ): ?>

			<td class="mp_box_remove">
				<a onclick="fedexDeleteBox(this);" href="#mp_fedex_boxes_table" title="<?php _e('Remove Box', 'mp'); ?>" ></a>
			</td>

			<?php else: ?>

			<td class="mp_box_add">
				<a onclick="fedexAddBox(this);" href="#mp_fedex_boxes_table" title="<?php _e('Add Box', 'mp'); ?>" ></a>
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
		$this->fedex_settings = $this->settings['shipping']['fedex'];
		$system = $this->settings['shipping']['system']; //Current Unit settings english | metric

		?>

		<script type="text/javascript">
			//Remove a row in the Boxes table
			function fedexDeleteBox(row) {
				var i = row.parentNode.parentNode.rowIndex;
				document.getElementById('mp_shipping_boxes_table').deleteRow(i);
			}

			function fedexAddBox(row)
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

		<div id="mp_fedex_rate" class="postbox">
			<h3 class='hndle'><span><?php _e('FedEx Settings', 'mp'); ?></span></h3>
			<div class="inside">
				<img src="<?php echo $mp->plugin_url; ?>images/fedex.gif" />
				<p class="description">
					<?php _e('Using this FedEx Shipping calculator requires requesting an Ecommerce API Username and Password. Get your free set of credentials <a target="_blank" href="https://www.fedex.com/wpor/web/jsp/commonTC.jsp">here &raquo;</a>', 'mp') ?><br />
					<?php _e('You must test and then Activate your credentials with FedEx before going live.', 'mp') ?>
				</p>

				<input type="hidden" name="mp_shipping_fedex_meta" value="1" />
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e('FedEx Test Mode', 'mp') ?></th>
							<td>
								<select name="mp[shipping][fedex][mode]">
									<option value="sandbox" <?php selected($this->fedex_settings['mode'] == 'sandbox')?> ><?php esc_html_e('Sandbox', 'mp'); ?></option>
									<option value="production" <?php selected($this->fedex_settings['mode'] == 'production')?> ><?php esc_html_e('Production', 'mp'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx API Key', 'mp') ?></th>
							<td><input type="text" name="mp[shipping][fedex][api_key]" value="<?php esc_attr_e($this->fedex_settings['api_key']); ?>" size="40" maxlength="40" /></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx Password', 'mp') ?></th>
							<td><input type="text" name="mp[shipping][fedex][api_password]" value="<?php esc_attr_e($this->fedex_settings['api_password']); ?>" size="40" maxlength="40" /></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx Account ID', 'mp') ?></th>
							<td><input type="text" name="mp[shipping][fedex][account]" value="<?php esc_attr_e($this->fedex_settings['account']); ?>" size="40" maxlength="40" /></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx Meter ID', 'mp') ?></th>
							<td><input type="text" name="mp[shipping][fedex][meter]" value="<?php esc_attr_e($this->fedex_settings['meter']); ?>" size="40" maxlength="40" /></td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx Dropoff Type', 'mp') ?></th>
							<td>
								<select name="mp[shipping][fedex][dropoff]">
									<option value="REGULAR_PICKUP" <?php selected($this->fedex_settings['dropoff'] == 'REGULAR_PICKUP')?> ><?php esc_html_e('Regular Pickup', 'mp'); ?></option>
									<option value="BUSINESS_SERVICE_CENTER" <?php selected($this->fedex_settings['dropoff'] == 'BUSINESS_SERVICE_CENTER')?> ><?php esc_html_e('Business Service Center', 'mp'); ?></option>
									<option value="DROP_BOX" <?php selected($this->fedex_settings['dropoff'] == 'DROP_BOX')?> ><?php esc_html_e('Drop Box', 'mp'); ?></option>
									<option value="REQUEST_COURIER" <?php selected($this->fedex_settings['dropoff'] == 'REQUEST_COURIER')?> ><?php esc_html_e('Request Courier', 'mp'); ?></option>
									<option value="STATION" <?php selected($this->fedex_settings['dropoff'] == 'STATION')?> ><?php esc_html_e('Station', 'mp'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx Default Packaging', 'mp') ?></th>
							<td>
								<select name="mp[shipping][fedex][packaging]">
									<option value="YOUR_PACKAGING" <?php selected($this->fedex_settings['packaging'] == 'YOUR_PACKAGING')?> ><?php esc_html_e('Your Packaging', 'mp'); ?></option>
									<option value="FEDEX_TUBE" <?php selected($this->fedex_settings['packaging'] == 'FEDEX_TUBE')?> ><?php esc_html_e('FedEx Tube', 'mp'); ?></option>
									<option value="FEDEX_PAK" <?php selected($this->fedex_settings['packaging'] == 'FEDEX_PAK')?> ><?php esc_html_e('FedEx Pak', 'mp'); ?></option>
									<option value="FEDEX_ENVELOPE" <?php selected($this->fedex_settings['packaging'] == 'FEDEX_ENVELOPE')?> ><?php esc_html_e('FedEx Envelope', 'mp'); ?></option>
									<option value="FEDEX_BOX" <?php selected($this->fedex_settings['packaging'] == 'FEDEX_BOX')?> ><?php esc_html_e('FedEx Box', 'mp'); ?></option>
									<option value="FEDEX_25KG_BOX" <?php selected($this->fedex_settings['packaging'] == 'FEDEX_25KG_BOX')?> ><?php esc_html_e('FedEx 25kg Box', 'mp'); ?></option>
									<option value="FEDEX_10KG_BOX" <?php selected($this->fedex_settings['packaging'] == 'FEDEX_10KG_BOX')?> ><?php esc_html_e('FedEx 10kg Box', 'mp'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e('FedEx Maximum Weight per Package', 'mp') ?></th>

							<td>
								<input type="text" name="mp[shipping][fedex][max_weight]" value="<?php esc_attr_e($this->fedex_settings['max_weight']); ?>" size="20" maxlength="20" />
								<?php echo $this->get_units_weight(); ?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php _e('FedEx Allow Commercial Delivery', 'mp') ?>
								<span class="description"><?php _e('<br />When checked the customer can chose Residential or Commercial delivery with Residential the default. Unchecked it\'s only Residential rates.', 'mp'); ?></span>
							</th>
							<td>
								<label>
									<input type="checkbox" name="mp[shipping][fedex][commercial]" value="1" <?php checked($this->fedex_settings['commercial']); ?> />&nbsp;<?php echo $detail->name . ' ' .$detail->delivery; ?>&nbsp;<?php _e('Allow Commercial Delivery.', 'mp'); ?>
								</label><br />
							</td>
						</tr>

						<!--
						<tr>
						<th scope="row"><?php _e('FedEx Request Mode', 'mp') ?></th>
						<td>
						<label>
						<input type="radio" name="mp[shipping][fedex][online]" value="online" <?php checked($this->fedex_settings['online'], 'online'); ?> />
						<?php _e('Online rates','mp'); ?>
						</label>&nbsp;&nbsp;&nbsp;
						<label>
						<input type="radio" name="mp[shipping][fedex][online]" value="retail" <?php checked($this->fedex_settings['online'], 'retail'); ?> />
						<?php _e('Retail rates','mp'); ?>
						</label>
						</td>
						</tr>
						-->
						<tr>
							<th scope="row"><?php _e('FedEx Offered Domestic Services', 'mp') ?></th>
							<td>
								<?php foreach($this->services as $service => $detail): ?>
								<label>
									<input type="checkbox" name="mp[shipping][fedex][services][<?php echo $service; ?>]" value="1" <?php checked($this->fedex_settings['services'][$service]); ?> />&nbsp;<?php echo $detail->name . ' ' .$detail->delivery; ?>
								</label><br />
								<?php endforeach;	?>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Handling Charge per Domestic Shipment ', 'mp') ?></th>
							<td>
								<input type="text" name="mp[shipping][fedex][domestic_handling]" value="<?php echo (empty($this->fedex_settings['domestic_handling']) ) ? '0.00' : esc_attr($this->fedex_settings['domestic_handling']); ?>" size="20" maxlength="20" />
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('FedEx Offered International Services', 'mp') ?></th>
							<td>
								<?php foreach($this->intl_services as $service => $detail): ?>
								<label>
									<input type="checkbox" name="mp[shipping][fedex][services][<?php echo $service; ?>]" value="1" <?php checked($this->fedex_settings['services'][$service]); ?> />&nbsp;<?php echo $detail->name . ' ' . $detail->delivery; ?>
								</label><br />
								<?php endforeach;	?>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Handling Charge per Interntional Shipment', 'mp') ?></th>
							<td>
								<input type="text" name="mp[shipping][fedex][intl_handling]" value="<?php echo (empty($this->fedex_settings['intl_handling']) ) ? '0.00' : esc_attr($this->fedex_settings['intl_handling']); ?>" size="20" maxlength="20" />
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
										if ($this->fedex_settings['boxes']) {
											foreach ( $this->fedex_settings['boxes']['name'] as $key => $value){
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
		$services = array_merge($this->services, $this->intl_services);
		foreach ( $services as $service => $detail ) {
			$settings['shipping']['fedex']['services'][$service] = (int) isset($_POST['mp']['shipping']['fedex']['services'][$service]);
		}
		
		$settings['shipping']['fedex']['commercial'] = (int) isset($_POST['mp']['shipping']['fedex']['commercial']);
				
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

		$this->residential = $_SESSION['mp_shipping_info']['residential'];

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

		if (in_array($this->settings['base_country'], array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI'))){
			// Can't use zip+4
			$this->settings['base_zip'] = substr($this->settings['base_zip'], 0, 5);
		}

		if (in_array($this->country, array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI'))){
			// Can't use zip+4
			$this->destination_zip = substr($this->destination_zip, 0, 5);
		}
		if ($this->country == $this->settings['base_country']) {
			$shipping_options = $this->rate_request();
		} else {
			$shipping_options = $this->rate_request(true);
		}

		return $shipping_options;
	}

	function packages($dimensions, $weight){
		$height = (empty($dimensions[0]) ) ? 0 : $dimensions[0];
		$width = (empty($dimensions[1]) ) ? 0 : $dimensions[1];
		$length = (empty($dimensions[2]) ) ? 0 : $dimensions[2];

		$count = $this->pkg_count;
		$packages =
		'<v13:PackageCount>' . $count . '</v13:PackageCount>
		';

		for($i=0; $i < $count;$i++) {

			$packages .=
			'<v13:RequestedPackageLineItems>
			<v13:SequenceNumber>' . $count . '</v13:SequenceNumber>
			<v13:GroupNumber>1</v13:GroupNumber>
			<v13:GroupPackageCount>1</v13:GroupPackageCount>
			<v13:Weight>
			<v13:Units>LB</v13:Units>
			<v13:Value>' . $weight . '</v13:Value>
			</v13:Weight>
			<v13:Dimensions>
			<v13:Length>' . intval($length) . '</v13:Length>
			<v13:Width>' . intval($width) . '</v13:Width>
			<v13:Height>' . intval($height) . '</v13:Height>
			<v13:Units>IN</v13:Units>
			</v13:Dimensions>
			</v13:RequestedPackageLineItems>';
		}
		return $packages;
	}

	/**
	* rate_request - Makes the actual call to fedex
	*/
	function rate_request( $international = false) {
		global $mp;

		$shipping_options = $this->fedex_settings['services'];

		//Assume equal size packages. Find the best matching box size
		$this->fedex_settings['max_weight'] = ( empty($this->fedex_settings['max_weight'])) ? 50 : $this->fedex_settings['max_weight'];
		$diff = floatval($this->fedex_settings['max_weight']);
		$found = -1;
		$largest = -1.0;

		foreach( $this->fedex_settings['boxes']['weight'] as $key => $weight ) {
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

		$allowed_weight = min($this->fedex_settings['boxes']['weight'][$found], $this->fedex_settings['max_weight']);

		if($allowed_weight >= $this->weight){
			$this->pkg_count = 1;
			$this->pkg_weight = $this->weight;
		} else {
			$this->pkg_count = ceil($this->weight / $allowed_weight); // Avoid zero
			$this->pkg_weight = $this->weight / $this->pkg_count;
		}

		// Fixup pounds by converting multiples of 16 ounces to pounds
		$this->pounds = intval($this->pkg_weight);
		$this->ounces = round(($this->pkg_weight - $this->pounds) * 16);

		//found our box
		$dims = explode('x', strtolower($this->fedex_settings['boxes']['size'][$found]));
		foreach($dims as &$dim) $dim = $this->as_inches($dim);

		sort($dims); //Sort so two lowest values are used for Girth

		$packages = $this->packages($dims, $this->pkg_weight);

		//var_dump($this->fedex_settings['services']);

		$xml_req = '<?xml version="1.0" encoding="UTF-8"?>
		<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v13="http://fedex.com/ws/rate/v13">
		<SOAP-ENV:Body>
		<v13:RateRequest>
		<v13:WebAuthenticationDetail>
		<v13:UserCredential>
		<v13:Key>' . $this->fedex_settings['api_key'] . '</v13:Key>
		<v13:Password>' . $this->fedex_settings['api_password'] . '</v13:Password>
		</v13:UserCredential>
		</v13:WebAuthenticationDetail>
		<v13:ClientDetail>
		<v13:AccountNumber>' . $this->fedex_settings['account'] . '</v13:AccountNumber>
		<v13:MeterNumber>' . $this->fedex_settings['meter'] . '</v13:MeterNumber>
		</v13:ClientDetail>
		<v13:TransactionDetail>
		<v13:CustomerTransactionId>Marketpress Rates Request</v13:CustomerTransactionId>
		</v13:TransactionDetail>
		<v13:Version>
		<v13:ServiceId>crs</v13:ServiceId>
		<v13:Major>13</v13:Major>
		<v13:Intermediate>0</v13:Intermediate>
		<v13:Minor>0</v13:Minor>
		</v13:Version>
		<v13:RequestedShipment>
		<v13:DropoffType>' . $this->fedex_settings['dropoff'] . '</v13:DropoffType>
		<v13:PackagingType>' . $this->fedex_settings['packaging'] . '</v13:PackagingType>
		<v13:PreferredCurrency>' . $mp->get_setting('currency') . '</v13:PreferredCurrency>
		<v13:Shipper>
		<v13:Address>
		<v13:StateOrProvinceCode>' . $this->settings['base_province'] . '</v13:StateOrProvinceCode>
		<v13:PostalCode>' . $this->settings['base_zip'] . '</v13:PostalCode>
		<v13:CountryCode>' . $this->settings['base_country'] . '</v13:CountryCode>
		</v13:Address>
		</v13:Shipper>
		<v13:Recipient>
		<v13:Address>
		<v13:StreetLines>' . $this->address1 . '</v13:StreetLines>
		<v13:StreetLines>' . $this->address2 . '</v13:StreetLines>
		<v13:City>' . $this->city . '</v13:City>
		<v13:StateOrProvinceCode>' . $this->state . '</v13:StateOrProvinceCode>
		<v13:PostalCode>' . $this->destination_zip . '</v13:PostalCode>
		<v13:CountryCode>' . $this->country . '</v13:CountryCode>';

		if (!empty($this->residential)
		|| empty($this->fedex_settings['commercial']) ) {
			$xml_req .= '
			<v13:Residential>true</v13:Residential>';
		} else {
			$xml_req .= '
			<v13:Residential>false</v13:Residential>';
		}

		$xml_req .= '
		</v13:Address>
		</v13:Recipient>
		<v13:ShippingChargesPayment>
		<v13:PaymentType>SENDER</v13:PaymentType>
		<v13:Payor>
		<v13:ResponsibleParty>
		<v13:AccountNumber>' . $this->fedex_settings['account'] . '</v13:AccountNumber>
		</v13:ResponsibleParty>
		</v13:Payor>
		</v13:ShippingChargesPayment>
		<v13:RateRequestTypes>LIST</v13:RateRequestTypes>
		';
		$xml_req .= $packages . '

		</v13:RequestedShipment>
		</v13:RateRequest>
		</SOAP-ENV:Body>
		</SOAP-ENV:Envelope>';

		//print_r($xml_req);
		//We have the XML make the call
		$url = ($this->fedex_settings['mode'] == 'sandbox') ? $this->sandbox_uri : $this->production_uri;

		//var_dump($xml_req);
		$response = wp_remote_request($url, array(
		'headers' => array('Content-Type: text/xml'),
		'method' => 'POST',
		'body' => $xml_req,
		'sslverify' => false,
		)
		);

		if (is_wp_error($response)){
			return array('error' => '<div class="mp_checkout_error">' . $response->get_error_message() . '</div>');
		}
		else
		{
			$loaded = ($response['response']['code'] == '200');
			$body = $response['body'];
			if(! $loaded){
				return array('error' => '<div class="mp_checkout_error">FedEx: ' . $response['response']['code'] . "&mdash;" . $response['response']['message'] . '</div>');
			}
		}
		//var_dump($response);

		if ($loaded){

			libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			$dom->encoding = 'utf-8';
			$dom->formatOutput = true;
			$dom->loadHTML($body);
			libxml_clear_errors();
		}

		//Process the return XML

		//print_r($dom->saveXML());
		//Clear any old price
		unset($_SESSION['mp_shipping_info']['shipping_cost']);

		$xpath = new DOMXPath($dom);

		//Check for errors
		$nodes = $xpath->query('//highestseverity');
		if( in_array( $nodes->item(0)->textContent, array('ERROR', 'FAILURE', 'WARNING' ) ) ) {
			$nodes = $xpath->query('//message');
			$this->rate_error = $nodes->item(0)->textContent;
			return array('error' => '<div class="mp_checkout_error">FedEx: ' . $this->rate_error . '</div>');
		}

		//Good to go

		$service_set = ($international) ? $this->intl_services : $this->services;
		//Make SESSION copy with just prices and delivery
		//var_dump($service_set);
		if(! is_array($shipping_options)) $shipping_options = array();
		$mp_shipping_options = $shipping_options;
		foreach($shipping_options as $service => $option){
			$nodes = $xpath->query('//ratereplydetails[servicetype="' . $service_set[$service]->code . '"]//totalnetcharge/amount');
			$rate = floatval($nodes->item(0)->textContent);// * $this->pkg_count;

			if($rate == 0){  //Not available for this combination
				unset($mp_shipping_options[$service]);
			}
			else
			{
				$handling = ($international) ? $this->fedex_settings['intl_handling'] : $this->fedex_settings['domestic_handling'];
				$handling = floatval($handling) * $this->pkg_count; // Add handling times number of packages.
				$delivery = $service_set[$service]->delivery;
				$mp_shipping_options[$service] = array('rate' => $rate, 'delivery' => $delivery, 'handling' => $handling);

				//match it up if there is already a selection
				if (! empty($_SESSION['mp_shipping_info']['shipping_sub_option'])){
					if ($_SESSION['mp_shipping_info']['shipping_sub_option'] == $service){
						$_SESSION['mp_shipping_info']['shipping_cost'] =  $rate + $handling;
					}
				}
			}
		}

		//Sort low to high rate
		uasort($mp_shipping_options, array($this,'compare_rates') );

		//If no cost matched yet set to the first one which is now the cheapest.
		if( empty($_SESSION['mp_shipping_info']['shipping_cost']) ){
			//Get the first one
			reset($mp_shipping_options);
			$service = key($mp_shipping_options);
			$_SESSION['mp_shipping_info']['shipping_sub_option'] = $service;
			$_SESSION['mp_shipping_info']['shipping_cost'] =  $mp_shipping_options[$service]['rate'] + $mp_shipping_options[$service]['handling'];
		}

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

	/**For uasort above
	*/
	function compare_rates($a, $b){
		if($a['rate'] == $b['rate']) return 0;
		return ($a['rate'] < $b['rate']) ? -1 : 1;
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
			$total = $total * (1 + (float) $mp->get_setting('tax->rate'));
		}

		$option .=  sprintf(__(' %1$s - %2$s', 'mp'), $delivery, $mp->format_currency('', $total) );
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

}

if(! class_exists('FedEx_Service') ):
class FedEx_Service
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


//register plugin only in US and US Possesions

$settings = get_option('mp_settings');

//if(in_array($settings['base_country'], array('US','UM','AS','FM','GU','MH','MP','PW','PR','PI')))
{
	mp_register_shipping_plugin('MP_Shipping_FedEx', 'fedex', __('FedEx (beta)', 'mp'), true);
}