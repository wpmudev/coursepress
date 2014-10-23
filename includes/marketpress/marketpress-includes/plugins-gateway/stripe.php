<?php
/*
	MarketPress Stripe Gateway Plugin
	Author: Aaron Edwards, Marko Miljus
 */

class MP_Gateway_Stripe extends MP_Gateway_API {

		//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
		var $plugin_name = 'stripe';
		//name of your gateway, for the admin side.
		var $admin_name = '';
		//public name of your gateway, for lists and such.
		var $public_name = '';
		//url for an image for your checkout method. Displayed on checkout form if set
		var $method_img_url = '';
		//url for an submit button image for your checkout method. Displayed on checkout form if set
		var $method_button_img_url = '';
		//whether or not ssl is needed for checkout page
		var $force_ssl;
		//always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
		var $ipn_url;
		//whether if this is the only enabled gateway it can skip the payment_form step
		var $skip_form = false;
		//api vars
		var $publishable_key, $private_key, $currency;

		/**
		 * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
		 */
		function on_creation() {
				global $mp;
				$settings = get_option('mp_settings');

				//set names here to be able to translate
				$this->admin_name = __('Stripe', 'mp');
				$this->public_name = __('Credit Card', 'mp');

				$this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
				$this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';

				if (isset($settings['gateways']['stripe']['publishable_key'])) {
						$this->publishable_key = $settings['gateways']['stripe']['publishable_key'];
						$this->private_key = $settings['gateways']['stripe']['private_key'];
				}
				$this->force_ssl = (bool) ( isset($settings['gateways']['stripe']['is_ssl']) && $settings['gateways']['stripe']['is_ssl'] );
				$this->currency = isset($settings['gateways']['stripe']['currency']) ? $settings['gateways']['stripe']['currency'] : 'USD';

				add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		}

		function enqueue_scripts() {
				global $mp;

				if (!is_admin() && get_query_var('pagename') == 'cart' && get_query_var('checkoutstep') == 'checkout') {

						wp_enqueue_script('js-stripe', 'https://js.stripe.com/v1/', array('jquery'));
						wp_enqueue_script('stripe-token', $mp->plugin_url . 'plugins-gateway/stripe-files/stripe_token.js', array('js-stripe', 'jquery'));
						wp_localize_script('stripe-token', 'stripe', array('publisher_key' => $this->publishable_key,
								'name' => __('Please enter the full Cardholder Name.', 'mp'),
								'number' => __('Please enter a valid Credit Card Number.', 'mp'),
								'expiration' => __('Please choose a valid expiration date.', 'mp'),
								'cvv2' => __('Please enter a valid card security code. This is the 3 digits on the signature panel, or 4 digits on the front of Amex cards.', 'mp')
						));
				}
		}

		/**
		 * Return fields you need to add to the top of the payment screen, like your credit card info fields
		 *
		 * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
		 * @param array $shipping_info. Contains shipping info and email in case you need it
		 */
		function payment_form($cart, $shipping_info) {
				global $mp;
				$settings = get_option('mp_settings');

				//check to see if we're using the embedded form
				$embedded_form = isset($settings['gateways']['stripe']['embedded_form_type']) ? $settings['gateways']['stripe']['embedded_form_type'] : false;

				//if we are create the form button
				if ($settings['gateways']['stripe']['embedded_form_type']) {

						$totals = array();
						$description = '';
						$coupon = $mp->get_coupon_code();
						foreach ($cart as $product_id => $variations) {
								foreach ($variations as $variation => $data) {
										$totals[] = $mp->coupon_value_product($coupon, $data['price'] * $data['quantity'], $product_id) ;
								}
						}

						$total = array_sum($totals) * 100; //get the total as cents
						$content = '<script>
							jQuery(document).ready(function($){
								var gateway = $(".mp_choose_gateway").val();
								if( gateway	 === "stripe" ) {
									$("#stripe .mp_cart_direct_checkout").hide();
									$("#mp_payment_confirm").click(function(e){
										e.preventDefault();
										$("#mp_payment_form").submit();
									});
								}
								$(".mp_choose_gateway").change(function(){
									if($(this).val() === "stripe") {
										$("#stripe .mp_cart_direct_checkout").hide();
										$("#mp_payment_confirm").click(function(e){
											e.preventDefault();
											$("#mp_payment_form").submit();
										});
									}
								});
							});
						</script>
			<div align="right">
						<form action="" method="POST">
						<script
								src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
								data-key="' . $this->publishable_key . '"
								data-amount="' . $total . '"
								data-name="' . get_bloginfo('title') . '"
								data-description="' . __('Your Order', 'mp') . '"
								data-image="">
							</script>
						</form></div>';

						return $content;
				}

				$name = isset($_SESSION['mp_shipping_info']['name']) ? $_SESSION['mp_shipping_info']['name'] : '';

				$content = '';

				$content .= '<div id="stripe_checkout_errors"></div>';

				$content .= '<table class="mp_cart_billing">
				<thead><tr>
					<th colspan="2">' . __('Enter Your Credit Card Information:', 'mp') . '</th>
				</tr></thead>
				<tbody>
					<tr>
					<td align="right">' . __('Cardholder Name:', 'mp') . '</td><td>
					<input size="35" id="cc_name" type="text" value="' . esc_attr($name) . '" /> </td>
					</tr>';
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __('Card Number', 'mp');
				$content .= '</td>';
				$content .= '<td>';
				$content .= '<input type="text" size="30" autocomplete="off" id="cc_number"/>';
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __('Expiration:', 'mp');
				$content .= '</td>';
				$content .= '<td>';
				$content .= '<select id="cc_month">';
				$content .= $this->_print_month_dropdown();
				$content .= '</select>';
				$content .= '<span> / </span>';
				$content .= '<select id="cc_year">';
				$content .= $this->_print_year_dropdown('', true);
				$content .= '</select>';
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '<tr>';
				$content .= '<td>';
				$content .= __('CVC:', 'mp');
				$content .= '</td>';
				$content .= '<td>';
				$content .= '<input type="text" size="4" autocomplete="off" id="cc_cvv2" />';
				$content .= '</td>';
				$content .= '</tr>';
				$content .= '</table>';
				$content .= '<span id="stripe_processing" style="display: none;float: right;"><img src="' . $mp->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'psts') . '</span>';
				return $content;
		}

		/**
		 * Return the chosen payment details here for final confirmation. You probably don't need
		 *	to post anything in the form as it should be in your $_SESSION var already.
		 *
		 * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
		 * @param array $shipping_info. Contains shipping info and email in case you need it
		 */
		function confirm_payment_form($cart, $shipping_info) {
				global $mp;
				$settings = get_option('mp_settings');

				//make sure token is set at this point
				if (!isset($_SESSION['stripeToken'])) {
						$mp->cart_checkout_error(__('The Stripe Token was not generated correctly. Please go back and try again.', 'mp'));
						return false;
				}

				//setup the Stripe API
				if (!class_exists('Stripe')) {
						require_once($mp->plugin_dir . "plugins-gateway/stripe-files/lib/Stripe.php");
				}
				Stripe::setApiKey($this->private_key);
				try {
						$token = Stripe_Token::retrieve($_SESSION['stripeToken']);
				} catch (Exception $e) {
						$mp->cart_checkout_error(sprintf(__('%s. Please go back and try again.', 'mp'), $e->getMessage()));
						return false;
				}

				$content = '';
				$content .= '<table class="mp_cart_billing">';
				$content .= '<thead><tr>';
				$content .= '<th>' . __('Billing Information:', 'mp') . '</th>';
				$content .= '<th align="right"><a href="' . mp_checkout_step_url('checkout') . '">' . __('&laquo; Edit', 'mp') . '</a></th>';
				$content .= '</tr></thead>';
				$content .= '<tbody>';
				$content .= '<tr>';
				$content .= '<td align="right">' . __('Payment method:', 'mp') . '</td>';
				$content .= '<td>' . sprintf(__('Your <strong>%1$s Card</strong> ending in <strong>%2$s</strong>. Expires <strong>%3$s</strong>', 'mp'), $token->card->type, $token->card->last4, $token->card->exp_month . '/' . $token->card->exp_year) . '</td>';
				$content .= '</tr>';
				$content .= '</tbody>';
				$content .= '</table>';
				return $content;
		}

		/**
		 * Runs before page load incase you need to run any scripts before loading the success message page
		 */
		function order_confirmation($order) {
				
		}

		/**
		 * Print the years
		 */
		function _print_year_dropdown($sel = '', $pfp = false) {
				$localDate = getdate();
				$minYear = $localDate["year"];
				$maxYear = $minYear + 15;

				$output = "<option value=''>--</option>";
				for ($i = $minYear; $i < $maxYear; $i++) {
						if ($pfp) {
								$output .= "<option value='" . substr($i, 0, 4) . "'" . ($sel == (substr($i, 0, 4)) ? ' selected' : '') .
												">" . $i . "</option>";
						} else {
								$output .= "<option value='" . substr($i, 2, 2) . "'" . ($sel == (substr($i, 2, 2)) ? ' selected' : '') .
												">" . $i . "</option>";
						}
				}
				return($output);
		}

		/**
		 * Print the months
		 */
		function _print_month_dropdown($sel = '') {
				$output = "<option value=''>--</option>";
				$output .= "<option " . ($sel == 1 ? ' selected' : '') . " value='01'>01 - Jan</option>";
				$output .= "<option " . ($sel == 2 ? ' selected' : '') . "	value='02'>02 - Feb</option>";
				$output .= "<option " . ($sel == 3 ? ' selected' : '') . "	value='03'>03 - Mar</option>";
				$output .= "<option " . ($sel == 4 ? ' selected' : '') . "	value='04'>04 - Apr</option>";
				$output .= "<option " . ($sel == 5 ? ' selected' : '') . "	value='05'>05 - May</option>";
				$output .= "<option " . ($sel == 6 ? ' selected' : '') . "	value='06'>06 - Jun</option>";
				$output .= "<option " . ($sel == 7 ? ' selected' : '') . "	value='07'>07 - Jul</option>";
				$output .= "<option " . ($sel == 8 ? ' selected' : '') . "	value='08'>08 - Aug</option>";
				$output .= "<option " . ($sel == 9 ? ' selected' : '') . "	value='09'>09 - Sep</option>";
				$output .= "<option " . ($sel == 10 ? ' selected' : '') . "	 value='10'>10 - Oct</option>";
				$output .= "<option " . ($sel == 11 ? ' selected' : '') . "	 value='11'>11 - Nov</option>";
				$output .= "<option " . ($sel == 12 ? ' selected' : '') . "	 value='12'>12 - Dec</option>";

				return($output);
		}

		/**
		 * Use this to process any fields you added. Use the $_POST global,
		 * and be sure to save it to both the $_SESSION and usermeta if logged in.
		 * DO NOT save credit card details to usermeta as it's not PCI compliant.
		 * Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
		 * it will redirect to the next step.
		 *
		 * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
		 * @param array $shipping_info. Contains shipping info and email in case you need it
		 */
		function process_payment_form($cart, $shipping_info) {
				global $mp;
				$settings = get_option('mp_settings');

				if (!isset($_POST['stripeToken']))
						$mp->cart_checkout_error(__('The Stripe Token was not generated correctly. Please try again.', 'mp'));

				//save to session
				if (!$mp->checkout_error) {
						$_SESSION['stripeToken'] = $_POST['stripeToken'];
				}
		}

		/**
		 * Filters the order confirmation email message body. You may want to append something to
		 *	the message. Optional
		 *
		 * Don't forget to return!
		 */
		function order_confirmation_email($msg, $order = null) {
				return $msg;
		}

		/**
		 * Return any html you want to show on the confirmation screen after checkout. This
		 *	should be a payment details box and message.
		 *
		 * Don't forget to return!
		 */
		function order_confirmation_msg($content, $order) {
				global $mp;
				if ($order->post_status == 'order_paid')
						$content .= '<p>' . sprintf(__('Your payment for this order totaling %s is complete.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
				return $content;
		}

		/**
		 * Echo a settings meta box with whatever settings you need for you gateway.
		 *	Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
		 *	You can access saved settings via $settings array.
		 */
		function gateway_settings_box($settings) {
				global $mp;
				?>
				<div class="postbox">
						<h3 class='hndle'><span><?php _e('Stripe', 'mp') ?></span> - <span class="description"><?php _e('Stripe makes it easy to start accepting credit cards directly on your site with full PCI compliance', 'mp'); ?></span></h3>
						<div class="inside">
								<p class="description"><?php _e("Accept Visa, MasterCard, American Express, Discover, JCB, and Diners Club cards directly on your site. You don't need a merchant account or gateway. Stripe handles everything, including storing cards, subscriptions, and direct payouts to your bank account. Credit cards go directly to Stripe's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'mp'); ?> <a href="https://stripe.com/" target="_blank"><?php _e('More Info &raquo;', 'mp') ?></a></p>
								<table class="form-table">
										<tr valign="top">
												<th scope="row"><?php _e('Stripe Mode', 'mp') ?></th>
												<td>
														<span class="description"><?php _e('When in live mode Stripe recommends you have an SSL certificate setup for the site where the checkout form will be displayed.', 'mp'); ?> <a href="https://stripe.com/help/ssl" target="_blank"><?php _e('More Info &raquo;', 'mp') ?></a></span><br/>
														<select name="mp[gateways][stripe][is_ssl]">
																<option value="1"<?php selected($settings['gateways']['stripe']['is_ssl'], 1); ?>><?php _e('Force SSL (Live Site)', 'mp') ?></option>
																<option value="0"<?php selected($settings['gateways']['stripe']['is_ssl'], 0); ?>><?php _e('No SSL (Testing)', 'mp') ?></option>
														</select>
												</td>
										</tr>
										<tr>
												<th scope="row"><?php _e('Stripe API Credentials', 'mp') ?></th>
												<td>
														<span class="description"><?php _e('You must login to Stripe to <a target="_blank" href="https://manage.stripe.com/#account/apikeys">get your API credentials</a>. You can enter your test credentials, then live ones when ready.', 'mp') ?></span>
														<p><label><?php _e('Secret key', 'mp') ?><br />
																		<input value="<?php echo esc_attr($settings['gateways']['stripe']['private_key']); ?>" size="70" name="mp[gateways][stripe][private_key]" type="text" />
																</label></p>
														<p><label><?php _e('Publishable key', 'mp') ?><br />
																		<input value="<?php echo esc_attr($settings['gateways']['stripe']['publishable_key']); ?>" size="70" name="mp[gateways][stripe][publishable_key]" type="text" />
																</label></p>
												</td>
										</tr>
										<tr valign="top">
												<th scope="row"><?php _e('Currency', 'mp') ?></th>
												<td>
														<span class="description"><?php _e('Selecting a currency other than that used for your store may cause problems at checkout.', 'mp'); ?></span><br />
														<select name="mp[gateways][stripe][currency]">
																<?php
																$sel_currency = isset($settings['gateways']['stripe']['currency']) ? $settings['gateways']['stripe']['currency'] : $settings['currency'];
																$currencies = array(
																		"AED" => 'AED: United Arab Emirates Dirham',
																		"AFN" => 'AFN: Afghan Afghani*',
																		"ALL" => 'ALL: Albanian Lek',
																		"AMD" => 'AMD: Armenian Dram',
																		"ANG" => 'ANG: Netherlands Antillean Gulden',
																		"AOA" => 'AOA: Angolan Kwanza*',
																		"ARS" => 'ARS: Argentine Peso*',
																		"AUD" => 'AUD: Australian Dollar*',
																		"AWG" => 'AWG: Aruban Florin',
																		"AZN" => 'AZN: Azerbaijani Manat',
																		"BAM" => 'BAM: Bosnia & Herzegovina Convertible Mark',
																		"BBD" => 'BBD: Barbadian Dollar',
																		"BDT" => 'BDT: Bangladeshi Taka',
																		"BGN" => 'BGN: Bulgarian Lev',
																		"BIF" => 'BIF: Burundian Franc',
																		"BMD" => 'BMD: Bermudian Dollar',
																		"BND" => 'BND: Brunei Dollar',
																		"BOB" => 'BOB: Bolivian Boliviano*',
																		"BRL" => 'BRL: Brazilian Real*',
																		"BSD" => 'BSD: Bahamian Dollar',
																		"BWP" => 'BWP: Botswana Pula',
																		"BZD" => 'BZD: Belize Dollar',
																		"CAD" => 'CAD: Canadian Dollar*',
																		"CDF" => 'CDF: Congolese Franc',
																		"CHF" => 'CHF: Swiss Franc',
																		"CLP" => 'CLP: Chilean Peso*',
																		"CNY" => 'CNY: Chinese Renminbi Yuan',
																		"COP" => 'COP: Colombian Peso*',
																		"CRC" => 'CRC: Costa Rican Colón*',
																		"CVE" => 'CVE: Cape Verdean Escudo*',
																		"CZK" => 'CZK: Czech Koruna*',
																		"DJF" => 'DJF: Djiboutian Franc*',
																		"DKK" => 'DKK: Danish Krone',
																		"DOP" => 'DOP: Dominican Peso',
																		"DZD" => 'DZD: Algerian Dinar',
																		"EEK" => 'EEK: Estonian Kroon*',
																		"EGP" => 'EGP: Egyptian Pound',
																		"ETB" => 'ETB: Ethiopian Birr',
																		"EUR" => 'EUR: Euro',
																		"FJD" => 'FJD: Fijian Dollar',
																		"FKP" => 'FKP: Falkland Islands Pound*',
																		"GBP" => 'GBP: British Pound',
																		"GEL" => 'GEL: Georgian Lari',
																		"GIP" => 'GIP: Gibraltar Pound',
																		"GMD" => 'GMD: Gambian Dalasi',
																		"GNF" => 'GNF: Guinean Franc*',
																		"GTQ" => 'GTQ: Guatemalan Quetzal*',
																		"GYD" => 'GYD: Guyanese Dollar',
																		"HKD" => 'HKD: Hong Kong Dollar',
																		"HNL" => 'HNL: Honduran Lempira*',
																		"HRK" => 'HRK: Croatian Kuna',
																		"HTG" => 'HTG: Haitian Gourde',
																		"HUF" => 'HUF: Hungarian Forint',
																		"IDR" => 'IDR: Indonesian Rupiah',
																		"ILS" => 'ILS: Israeli New Sheqel',
																		"INR" => 'INR: Indian Rupee*',
																		"ISK" => 'ISK: Icelandic Króna',
																		"JMD" => 'JMD: Jamaican Dollar',
																		"JPY" => 'JPY: Japanese Yen',
																		"KES" => 'KES: Kenyan Shilling',
																		"KGS" => 'KGS: Kyrgyzstani Som',
																		"KHR" => 'KHR: Cambodian Riel',
																		"KMF" => 'KMF: Comorian Franc',
																		"KRW" => 'KRW: South Korean Won',
																		"KYD" => 'KYD: Cayman Islands Dollar',
																		"KZT" => 'KZT: Kazakhstani Tenge',
																		"LAK" => 'LAK: Lao Kip*',
																		"LBP" => 'LBP: Lebanese Pound',
																		"LKR" => 'LKR: Sri Lankan Rupee',
																		"LRD" => 'LRD: Liberian Dollar',
																		"LSL" => 'LSL: Lesotho Loti',
																		"LTL" => 'LTL: Lithuanian Litas',
																		"LVL" => 'LVL: Latvian Lats',
																		"MAD" => 'MAD: Moroccan Dirham',
																		"MDL" => 'MDL: Moldovan Leu',
																		"MGA" => 'MGA: Malagasy Ariary',
																		"MKD" => 'MKD: Macedonian Denar',
																		"MNT" => 'MNT: Mongolian Tögrög',
																		"MOP" => 'MOP: Macanese Pataca',
																		"MRO" => 'MRO: Mauritanian Ouguiya',
																		"MUR" => 'MUR: Mauritian Rupee*',
																		"MVR" => 'MVR: Maldivian Rufiyaa',
																		"MWK" => 'MWK: Malawian Kwacha',
																		"MXN" => 'MXN: Mexican Peso*',
																		"MYR" => 'MYR: Malaysian Ringgit',
																		"MZN" => 'MZN: Mozambican Metical',
																		"NAD" => 'NAD: Namibian Dollar',
																		"NGN" => 'NGN: Nigerian Naira',
																		"NIO" => 'NIO: Nicaraguan Córdoba*',
																		"NOK" => 'NOK: Norwegian Krone',
																		"NPR" => 'NPR: Nepalese Rupee',
																		"NZD" => 'NZD: New Zealand Dollar',
																		"PAB" => 'PAB: Panamanian Balboa*',
																		"PEN" => 'PEN: Peruvian Nuevo Sol*',
																		"PGK" => 'PGK: Papua New Guinean Kina',
																		"PHP" => 'PHP: Philippine Peso',
																		"PKR" => 'PKR: Pakistani Rupee',
																		"PLN" => 'PLN: Polish Złoty',
																		"PYG" => 'PYG: Paraguayan Guaraní*',
																		"QAR" => 'QAR: Qatari Riyal',
																		"RON" => 'RON: Romanian Leu',
																		"RSD" => 'RSD: Serbian Dinar',
																		"RUB" => 'RUB: Russian Ruble',
																		"RWF" => 'RWF: Rwandan Franc',
																		"SAR" => 'SAR: Saudi Riyal',
																		"SBD" => 'SBD: Solomon Islands Dollar',
																		"SCR" => 'SCR: Seychellois Rupee',
																		"SEK" => 'SEK: Swedish Krona',
																		"SGD" => 'SGD: Singapore Dollar',
																		"SHP" => 'SHP: Saint Helenian Pound*',
																		"SLL" => 'SLL: Sierra Leonean Leone',
																		"SOS" => 'SOS: Somali Shilling',
																		"SRD" => 'SRD: Surinamese Dollar*',
																		"STD" => 'STD: São Tomé and Príncipe Dobra',
																		"SVC" => 'SVC: Salvadoran Colón*',
																		"SZL" => 'SZL: Swazi Lilangeni',
																		"THB" => 'THB: Thai Baht',
																		"TJS" => 'TJS: Tajikistani Somoni',
																		"TOP" => 'TOP: Tongan Paʻanga',
																		"TRY" => 'TRY: Turkish Lira',
																		"TTD" => 'TTD: Trinidad and Tobago Dollar',
																		"TWD" => 'TWD: New Taiwan Dollar',
																		"TZS" => 'TZS: Tanzanian Shilling',
																		"UAH" => 'UAH: Ukrainian Hryvnia',
																		"UGX" => 'UGX: Ugandan Shilling',
																		"USD" => 'USD: United States Dollar',
																		"UYI" => 'UYI: Uruguayan Peso*',
																		"UZS" => 'UZS: Uzbekistani Som',
																		"VEF" => 'VEF: Venezuelan Bolívar*',
																		"VND" => 'VND: Vietnamese Đồng',
																		"VUV" => 'VUV: Vanuatu Vatu',
																		"WST" => 'WST: Samoan Tala',
																		"XAF" => 'XAF: Central African Cfa Franc',
																		"XCD" => 'XCD: East Caribbean Dollar',
																		"XOF" => 'XOF: West African Cfa Franc*',
																		"XPF" => 'XPF: Cfp Franc*',
																		"YER" => 'YER: Yemeni Rial',
																		"ZAR" => 'ZAR: South African Rand',
																		"ZMW" => 'ZMW: Zambian Kwacha',
																);

																foreach ($currencies as $k => $v) {
																		echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
																}
																?>
														</select>
												</td>
										</tr>
										<tr>
												<th scope="row"><?php _e('Form Options', 'mp'); ?></th>
												<td>
														<label for="stripe_form_type">
																<?php _e('Use Default Embedded Form', 'mp'); ?>
																<?php $form_type = isset($settings['gateways']['stripe']['embedded_form_type']) ? $settings['gateways']['stripe']['embedded_form_type'] : ''; ?>
																<input type="checkbox" id="stripe_form_type" name="mp[gateways][stripe][embedded_form_type]" value="default" <?php checked($form_type, 'default'); ?> /></label>
												</td>
										</tr>
								</table>		
						</div>
				</div>			
				<?php
		}

		/**
		 * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
		 *	array. Don't forget to return!
		 */
		function process_gateway_settings($settings) {
			$settings['gateways']['stripe']['embedded_form_type'] = ( ! isset($_POST['mp']['gateways']['stripe']['embedded_form_type']) ) ? '' : $_POST['mp']['gateways']['stripe']['embedded_form_type'];
			return $settings;
		}

		/**
		 * Use this to do the final payment. Create the order then process the payment. If
		 *	you know the payment is successful right away go ahead and change the order status
		 *	as well.
		 *	Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
		 *	it will redirect to the next step.
		 *
		 * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
		 * @param array $shipping_info. Contains shipping info and email in case you need it
		 */
		function process_payment($cart, $shipping_info) {
				global $mp;
				$settings = get_option('mp_settings');

				//make sure token is set at this point
				if (!isset($_SESSION['stripeToken'])) {
						$mp->cart_checkout_error(__('The Stripe Token was not generated correctly. Please go back and try again.', 'mp'));
						return false;
				}

				//setup the Stripe API
				if (!class_exists('Stripe')) {
						require_once($mp->plugin_dir . "plugins-gateway/stripe-files/lib/Stripe.php");
				}
				Stripe::setApiKey($this->private_key);

				$totals = array();
				$coupon_code = $mp->get_coupon_code();
				foreach ($cart as $product_id => $variations) {
					foreach ($variations as $variation => $data) {
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

				try {
						// create the charge on Stripe's servers - this will charge the user's card
						$charge = Stripe_Charge::create(array(
												"amount" => round($total * 100), // amount in cents, again
												"currency" => strtolower($this->currency),
												"card" => $_SESSION['stripeToken'],
												"description" => sprintf(__('%s Store Purchase - Order ID: %s, Email: %s', 'mp'), get_bloginfo('name'), $order_id, $_SESSION['mp_shipping_info']['email']))
						);

						if ($charge->paid == 'true') {

								//setup our payment details
								$payment_info = array();
								$payment_info['gateway_public_name'] = $this->public_name;
								$payment_info['gateway_private_name'] = $this->admin_name;
								$payment_info['method'] = sprintf(__('%1$s Card ending in %2$s - Expires %3$s', 'mp'), $charge->card->type, $charge->card->last4, $charge->card->exp_month . '/' . $charge->card->exp_year);
								$payment_info['transaction_id'] = $charge->id;
								$timestamp = time();
								$payment_info['status'][$timestamp] = __('Paid', 'mp');
								$payment_info['total'] = $total;
								$payment_info['currency'] = $this->currency;

								$order = $mp->create_order($order_id, $cart, $_SESSION['mp_shipping_info'], $payment_info, true);
								unset($_SESSION['stripeToken']);
								$mp->set_cart_cookie(Array());
						}
				} catch (Exception $e) {
						unset($_SESSION['stripeToken']);
						$mp->cart_checkout_error(sprintf(__('There was an error processing your card: "%s". Please <a href="%s">go back and try again</a>.', 'mp'), $e->getMessage(), mp_checkout_step_url('checkout')));
						return false;
				}
		}

		/**
		 * INS and payment return
		 */
		function process_ipn_return() {
				global $mp;
				$settings = get_option('mp_settings');
		}

}

//register payment gateway plugin
mp_register_gateway_plugin('MP_Gateway_Stripe', 'stripe', __('Stripe', 'mp'));
