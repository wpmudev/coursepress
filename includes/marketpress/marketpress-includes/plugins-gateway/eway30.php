<?php
/*
MarketPress eWay Rapid 3.0 Payments Gateway Plugin
Author: Mariusz Maniu (Incsub)
*/

class MP_Gateway_eWay30 extends MP_Gateway_API {

	//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
	var $plugin_name = 'eway30';

	//name of your gateway, for the admin side.
	var $admin_name = '';

	//public name of your gateway, for lists and such.
	var $public_name = '';

	//url for an image for your checkout method. Displayed on checkout form if set
	var $method_img_url = '';

	//url for an submit button image for your checkout method. Displayed on checkout form if set
	var $method_button_img_url = '';

	//whether or not ssl is needed for checkout page
	var $force_ssl = true;

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
		$this->admin_name = __('eWay Rapid 3.0 Payments (beta)', 'mp');
		$this->public_name = __('Credit Card', 'mp');
		
		$this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
		$this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';
		
		$this->returnURL = mp_checkout_step_url('confirmation');
		
		//sets eway api settings
		if ($mp->get_setting('gateways->eway30->mode') == 'rapid30live') {
			$this->UserAPIKey = $mp->get_setting('gateways->eway30->UserAPIKeyLive');
			$this->UserPassword = $mp->get_setting('gateways->eway30->UserPasswordLive');
			$this->LiveMode = true;
		} else if ($mp->get_setting('gateways->eway30->mode') == 'rapid30sandbox') {
			$this->UserAPIKey = $mp->get_setting('gateways->eway30->UserAPIKeySandbox');
			$this->UserPassword = $mp->get_setting('gateways->eway30->UserPasswordSandbox');
			$this->LiveMode = false;
		} else {
			$this->UserAPIKey = '';
			$this->UserPassword = '';
			$this->LiveMode = false;	
		}
	}

	/**
	* Return fields you need to add to the payment screen, like your credit card info fields
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function payment_form($cart, $shipping_info) {
		global $mp;
		
		$name = isset($_SESSION['mp_shipping_info']['name']) ? $_SESSION['mp_shipping_info']['name'] : '';
		
		$content = '';
				
		$content .= '
		<table class="mp_cart_billing">
		<thead>
			<tr>
			<td align="right">'.__('Cardholder Name:', 'mp').'*</td><td>
				'.apply_filters( 'mp_checkout_error_name', '' ).'
				<input size="35" name="card_name" type="text" value="'.esc_attr($name).'" /> </td>
			</tr>
		
			<tr>
			<td align="right">'.__('Credit Card Number:', 'mp').'*</td>
			<td>
				'.apply_filters( 'mp_checkout_error_card_num', '' ).'
				<input name="card_num"
				 id="card_num" class="credit_card_number input_field noautocomplete"
				 type="text" size="22" maxlength="22" />
				<div class="hide_after_success nocard cardimage"	id="cardimage" style="background: url('.$mp->plugin_url.'images/card_array.png) no-repeat;"></div></td>
			</tr>
		
			<tr>
			<td align="right">'.__('Expiration Date:', 'mp').'*</td>
			<td>
			'.apply_filters( 'mp_checkout_error_exp', '' ).'
			<label class="inputLabel" for="exp_month">'.__('Month', 'mp').'</label>
				<select name="exp_month" id="exp_month">
					'.$this->_print_month_dropdown().'
				</select>
				<label class="inputLabel" for="exp_year">'.__('Year', 'mp').'</label>
				<select name="exp_year" id="exp_year">
					'.$this->_print_year_dropdown('', true).'
				</select>
				</td>
			</tr>
		
			<tr>
			<td align="right">'.__('Security Code:', 'mp').'</td>
			<td>'.apply_filters( 'mp_checkout_error_card_code', '' ).'
			<input id="card_code" name="card_code" class="input_field noautocomplete"
				 style="width: 70px;" type="text" size="4" maxlength="4" /></td>
			</tr>
		
		</tbody>
		</table>';

		return $content;
	}
	
	/**
	 * Use this to process any fields you added. Use the $_POST global,
	 *	and be sure to save it to both the $_SESSION and usermeta if logged in.
	 *	DO NOT save credit card details to usermeta as it's not PCI compliant.
	 *	Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	 *	it will redirect to the next step.
	 *
	 * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info. Contains shipping info and email in case you need it
	 */
	function process_payment_form($cart, $shipping_info) {
		global $mp;
			
		if (!isset($_POST['exp_month']) || !isset($_POST['exp_year']) || empty($_POST['exp_month']) || empty($_POST['exp_year'])) {
			$mp->cart_checkout_error( __('Please select your credit card expiration date.', 'mp'), 'exp');
		}
		
		if (!isset($_POST['card_name']) || empty($_POST['card_name'])) {
			$mp->cart_checkout_error( __('Please enter your cardholder name', 'mp'), 'card_name');
		}
			
		if (!isset($_POST['card_code']) || empty($_POST['card_code'])) {
			$mp->cart_checkout_error( __('Please enter your credit card security code', 'mp'), 'card_code');
		}
	
		if (!isset($_POST['card_num']) || empty($_POST['card_num'])) {
			$mp->cart_checkout_error( __('Please enter your credit card number', 'mp'), 'card_num');
		}
		
		if (!$mp->checkout_error) {
			if (
				($this->_get_card_type($_POST['card_num']) == "American Express" && strlen($_POST['card_code']) != 4) ||
				($this->_get_card_type($_POST['card_num']) != "American Express" && strlen($_POST['card_code']) != 3)
			) {
				$mp->cart_checkout_error(__('Please enter a valid credit card security code', 'mp'), 'card_code');
			}
		}

		if (!$mp->checkout_error) {
			$_SESSION['card_name'] = $_POST['card_name'];
			$_SESSION['card_num'] = $_POST['card_num'];
			$_SESSION['card_code'] = $_POST['card_code'];
			$_SESSION['exp_month'] = $_POST['exp_month'];
			$_SESSION['exp_year'] = $_POST['exp_year'];
		}
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
		
		//print payment details
		$content = '';
		
		$content .= '<table class="mp_cart_shipping">';
		$content .= '<thead><tr>';
		$content .= '<th>'.__('Credit Card Information:', 'mp').'</th>';
		$content .= '<th align="right"><a href="'.mp_checkout_step_url('checkout').'">'.__('Edit', 'mp').'</a></th>';
		$content .= '</tr></thead>';
		$content .= '<tbody>';
		$content .= '<tr>';
		$content .= '<td align="right">'.__('Cardholder Name:', 'mp').'</td><td>';
		$content .= esc_attr($_SESSION['card_name']).' </td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align="right">'.__('Card Number:', 'mp').'</td><td>';
		$content .= $this->_get_card_type($_SESSION['card_num']).' ending in '. substr($_SESSION['card_num'], strlen($_SESSION['card_num'])-4, 4).'</td>';
		$content .= '</tr>';
		$content .= '</tbody>';
		$content .= '</table>';	
		$content .= '<img src="'.$mp->plugin_url . 'images/ewaylogo.png" border="0" alt="'.__('Checkout with eWay', 'mp').'">';
		
		return $content;
	}
	

	/**
	 * Print the months
	 */
	function _print_month_dropdown($sel='') {
		$output =	"<option value=''>--</option>";
		$output .=	"<option " . ($sel==1?' selected':'') . " value='01'>01 - Jan</option>";
		$output .=	"<option " . ($sel==2?' selected':'') . "	value='02'>02 - Feb</option>";
		$output .=	"<option " . ($sel==3?' selected':'') . "	value='03'>03 - Mar</option>";
		$output .=	"<option " . ($sel==4?' selected':'') . "	value='04'>04 - Apr</option>";
		$output .=	"<option " . ($sel==5?' selected':'') . "	value='05'>05 - May</option>";
		$output .=	"<option " . ($sel==6?' selected':'') . "	value='06'>06 - Jun</option>";
		$output .=	"<option " . ($sel==7?' selected':'') . "	value='07'>07 - Jul</option>";
		$output .=	"<option " . ($sel==8?' selected':'') . "	value='08'>08 - Aug</option>";
		$output .=	"<option " . ($sel==9?' selected':'') . "	value='09'>09 - Sep</option>";
		$output .=	"<option " . ($sel==10?' selected':'') . "	value='10'>10 - Oct</option>";
		$output .=	"<option " . ($sel==11?' selected':'') . "	value='11'>11 - Nov</option>";
		$output .=	"<option " . ($sel==12?' selected':'') . "	value='12'>12 - Dec</option>";

		return($output);
	}

	/**
	 * Print the years
	 */
	function _print_year_dropdown($sel='', $pfp = false) {
		$localDate=getdate();
		$minYear = $localDate["year"];
		$maxYear = $minYear + 15;

		$output = "<option value=''>--</option>";
		for($i=$minYear; $i<$maxYear; $i++) {
				if ($pfp) {
						$output .= "<option value='". substr($i, 0, 4) ."'".($sel==(substr($i, 0, 4))?' selected':'').
						">". $i ."</option>";
				} else {
						$output .= "<option value='". substr($i, 2, 2) ."'".($sel==(substr($i, 2, 2))?' selected':'').
				">". $i ."</option>";
				}
		}
		return($output);
	}
	
	/**
	 * Checks if Credit Card is OK
	 */	
	function _get_card_type($number) {
		$num_length = strlen($number);
		
		if ($num_length > 10 && preg_match('/[0-9]+/', $number) >= 1) {
			if((substr($number, 0, 1) == '4') && (($num_length == 13)||($num_length == 16))) {
				return "Visa";
			} else if((substr($number, 0, 1) == '5' && ((substr($number, 1, 1) >= '1') && (substr($number, 1, 1) <= '5'))) && ($num_length == 16)) {
				return "Mastercard";
			} else if(substr($number, 0, 4) == "6011" && ($num_length == 16)) {
				return "Discover Card";
			} else if((substr($number, 0, 1) == '3' && ((substr($number, 1, 1) == '4') || (substr($number, 1, 1) == '7'))) && ($num_length == 15)) {
				return "American Express";
			}
		}
		return "";
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
		global $mp, $current_user;
		
		$timestamp = time();
		
		$order_id = $mp->generate_order_id();
		
		$totals = array();
		$product_count = 0;
		$coupon_code = $mp->get_coupon_code();
		
		foreach ($cart as $product_id => $variations) {
			foreach ($variations as $data) {
				$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);			
				$totals[] = $price;
				$product_count++;
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
    
		$amount = number_format( round( $total, 2 ), 2, '.', '');
			
		require_once($mp->plugin_dir . "plugins-gateway/eway/RapidAPI.php");

		$eway_service = new RapidAPI($this->LiveMode, $this->UserAPIKey, $this->UserPassword);
	
		// Create AccessCode Request Object
		$request = new CreateAccessCodeRequest();
	
		$request->Customer->Reference = 'MarketPress';
		if (!$mp->download_only_cart($cart) && $mp->get_setting('shipping->method') != 'none' && isset($shipping_info['name'])) {	
			list($first_name, $last_name) = split(' ', $shipping_info['name'], 2);
				
			$request->Customer->FirstName = $first_name;
			$request->Customer->LastName = $last_name;
			$request->Customer->Street1 = $shipping_info['address1'];
			if (!empty($shipping_info['address2']))
				$request->Customer->Street2 = $shipping_info['address1'] . " " . $shipping_info['address2'];
			$request->Customer->Phone = $shipping_info['phone'];	
			$request->Customer->City = $shipping_info['city'];
			$request->Customer->State = $shipping_info['state'];
			$request->Customer->PostalCode = $shipping_info['zip'];
			$request->Customer->Country = $shipping_info['country'];		
		}
		$request->Customer->Email = $shipping_info['email'];
		$request->Customer->Mobile = '';
	
		// require field
		$request->ShippingAddress->FirstName = '';
		$request->ShippingAddress->LastName = '';
		$request->ShippingAddress->Street1 = '';
		$request->ShippingAddress->Street2 = '';
		$request->ShippingAddress->City = '';
		$request->ShippingAddress->State = '';
		$request->ShippingAddress->PostalCode = '';
		$request->ShippingAddress->Country = '';
		$request->ShippingAddress->Email = '';
		$request->ShippingAddress->Phone = '';
	
		$request->ShippingAddress->ShippingMethod = "Unknown";
	
		$request->Payment->TotalAmount = $amount*100;
		$request->Payment->InvoiceNumber = $order_id;
		$request->Payment->InvoiceDescription = '';
		$request->Payment->InvoiceReference = '';
	
		$request->Payment->CurrencyCode = $mp->get_setting('gateways->eway30->Currency');
	
		$request->RedirectUrl = $this->returnURL;
		$request->Method = 'ProcessPayment';
	
		//Call RapidAPI
		$result = $eway_service->CreateAccessCode($request);
	
		if (isset($result->Errors)) {
			//Get Error Messages from Error Code. Error Code Mappings are in the Config.ini file
			$ErrorArray = explode(",", trim($result->Errors));
			$lblError = "";
			foreach ( $ErrorArray as $error ) {
				if (isset($eway_service->APIConfig[$error]))
					$lblError .= $error." ".$eway_service->APIConfig[$error]."<br>";
				else
					$lblError .= $error;
			}
		}
	
		if (isset($lblError)) {
			$mp->cart_checkout_error( __('There was a problem parsing the response from eWay. Please try again.', 'mp').' ('.$lblError.')' );

			return false;
		}
	
		// send post
		$post = array(
			'EWAY_ACCESSCODE' => $result->AccessCode,
			'EWAY_CARDNAME'   => $_SESSION['card_name'],
			'EWAY_CARDNUMBER' => $_SESSION['card_num'],
			'EWAY_CARDEXPIRYMONTH'  => $_SESSION['exp_month'],
			'EWAY_CARDEXPIRYYEAR'   => $_SESSION['exp_year'],
			'EWAY_CARDCVN'    => $_SESSION['card_code'] ,
		);
	
		$field_string = '';
		foreach ($post as $key => $value) {
			$field_string .= "$key=" . urlencode( $value ) . "&";
		}
		
		$response = wp_remote_post( $result->FormActionURL, array(
				'timeout' => 20,
				'headers' => 0,
				'sslverify' => false,
				'body' => rtrim($field_string, "& ")
			)
		);
		
		if ( is_wp_error( $response ) ) {
			$mp->cart_checkout_error( __('There was a problem parsing the response from eWay. Please try again.', 'mp').' ('.$lblError.')' );
			
			return false;
		}
	
		$isError = false;
		$request = new GetAccessCodeResultRequest();
		$request->AccessCode = $result->AccessCode;
	
		//Call RapidAPI to get the result
		$result = $eway_service->GetAccessCodeResult($request);
	
		// Check if any error returns
		if (isset($result->Errors)) {
			// Get Error Messages from Error Code. Error Code Mappings are in the Config.ini file
			$ErrorArray = explode(",", $result->Errors);
			$lblError = "";
			$isError = true;
			foreach ( $ErrorArray as $error ) {
				$lblError .= $eway_service->APIConfig[$error].". ";
			}
		}
	
		if ( ! $isError ) {
			if ( ! $result->TransactionStatus ) {
				$isError = true;
				$lblError = "Payment Declined - " . $result->ResponseCode;
			}
		}
	
		if ( $isError ) {
			$mp->cart_checkout_error( __('There was a problem parsing the response from eWay. Please try again.', 'mp').' ('.$lblError.')' );

			return false;
		} else {
			$status = __('The payment has been completed, and the funds have been added successfully to your account balance.', 'mp');
			$paid = true;
			
			$payment_info['gateway_public_name'] = $this->public_name;
			$payment_info['gateway_private_name'] = $this->admin_name;
			$payment_info['method'] = "eWay payment";
			$payment_info['status'][$timestamp] = "paid";
			$payment_info['total'] = $amount;
			$payment_info['currency'] = $mp->get_setting('gateways->eway30->Currency');
			$payment_info['transaction_id'] = $result->TransactionID;
	  
			$result = $mp->create_order($_SESSION['mp_order'], $cart, $shipping_info, $payment_info, $paid);		
		}
	}
	
	/**
	 * Filters the order confirmation email message body. You may want to append something to
	 *	the message. Optional
	 *
	 * Don't forget to return!
	 */
	function order_confirmation_email($msg, $order) {
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
		$content = '';
		
		if (!$order)
			return '<p><a href="'.mp_checkout_step_url('confirm-checkout').'">' . __('Please go back and try again.', 'mp') . '</a></p>';
		
		if ($order->post_status == 'order_received') {
			$content .= '<p>' . sprintf(__('Your payment via eWay for this order totaling %s is in progress. Here is the latest status:', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
			$statuses = $order->mp_payment_info['status'];
			krsort($statuses); //sort with latest status at the top
			$status = reset($statuses);
			$timestamp = key($statuses);
			$content .= '<p><strong>' . $mp->format_date($timestamp) . ':</strong> ' . esc_html($status) . '</p>';
		} else {
			$content .= '<p>' . sprintf(__('Your payment for this order totaling %s is complete. The transaction number is <strong>%s</strong>.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']), $order->mp_payment_info['transaction_id']) . '</p>';
		}
		return $content;
	}
	
	/**
	 * Runs before page load incase you need to run any scripts before loading the success message page
	 */
	function order_confirmation($order) {
		global $mp;
	}

	/**
	 * Echo a settings meta box with whatever settings you need for you gateway.
	 *	Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
	 *	You can access saved settings via $settings array.
	 */
	function gateway_settings_box($settings) {
		global $mp;				
		?>
		<div id="mp_paypal_express" class="postbox">
			<h3 class='hndle'><span><?php _e('eWay Rapid 3.0 Payments Settings', 'mp'); ?></span></h3>
			<div class="inside">
				<span class="description"><?php _e('eWay Rapid 3.0 Payments lets merchants recieve credit card payments through eWay without need for users to leave the shop. Note this gateway requires a valid SSL certificate configured for this site.', 'mp') ?></span>
				<table class="form-table">
					<tr>
					<th scope="row"><?php _e('eWay Currency', 'mp') ?></th>
					<td>
						<select name="mp[gateways][eway30][Currency]">
						<?php
						$sel_currency = $mp->get_setting('gateways->eway30->Currency', $settings['currency']);
						$currencies = array(
								'NZD' => 'NZD - New Zealand Dollar',
								'AUD' => 'AUD - Australian Dollar',
								'CAD' => 'CAD - Canadian Dollar',
								'EUR' => 'EUR - Euro',
								'GBP' => 'GBP - Pound Sterling',
								'HKD' => 'HKD - Hong Kong Dollar',
								'JPY' => 'JPY - Japanese Yen',
								'SGD' => 'SGD - Singapore Dollar',
								'USD' => 'USD - U.S. Dollar'
						);
						foreach ($currencies as $k => $v) {
								echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
						}
						?>
						</select>
					</td>
					</tr>
					<tr>
					<th scope="row"><?php _e('Gateway Mode', 'mp') ?></th>
					<td>
					<span class="description"><?php _e('Rapid 3.0 method keeps User on your page through whole payment process. ', 'mp') ?><a href="http://www.eway.com.au/developers/api/rapid-3-0" target="_blank"><?php _e('More about Rapid 3.0 API. ', 'mp') ?></a></span><br />
					<select name="mp[gateways][eway30][mode]">
						<option value="rapid30sandbox"<?php selected($mp->get_setting('gateways->eway30->mode', 'rapid30sandbox'), 'rapid30sandbox') ?>><?php _e('Sandbox Rapid 3.0', 'mp') ?></option>
						<option value="rapid30live"<?php selected($mp->get_setting('gateways->eway30->mode'), 'rapid30live') ?>><?php _e('Live Rapid 3.0', 'mp') ?></option>
					</select>
					</td>
					</tr>
					<tr>
					<th scope="row"><?php _e('Live Rapid 3.0 API Credentials', 'mp') ?></th>
					<td>
						<p><label><?php _e('eWay User API Key', 'mp') ?><br />
						<input value="<?php echo esc_attr($mp->get_setting('gateways->eway30->UserAPIKeyLive')); ?>" size="30" name="mp[gateways][eway30][UserAPIKeyLive]" type="text" />
						</label></p>
						<p><label><?php _e('eWay User Password', 'mp') ?><br />
						<input value="<?php echo esc_attr($mp->get_setting('gateways->eway30->UserPasswordLive')); ?>" size="20" name="mp[gateways][eway30][UserPasswordLive]" type="text" />
						</label></p>
					</td>
					</tr>
					<tr>
					<th scope="row"><?php _e('Sandbox Rapid 3.0 API Credentials', 'mp') ?></th>
					<td>
						<p><label><?php _e('eWay User API Key', 'mp') ?><br />
						<input value="<?php echo esc_attr($mp->get_setting('gateways->eway30->UserAPIKeySandbox')); ?>" size="30" name="mp[gateways][eway30][UserAPIKeySandbox]" type="text" />
						</label></p>
						<p><label><?php _e('eWay User Password', 'mp') ?><br />
						<input value="<?php echo esc_attr($mp->get_setting('gateways->eway30->UserPasswordSandbox')); ?>" size="20" name="mp[gateways][eway30][UserPasswordSandbox]" type="text" />
						</label></p>
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
		return $settings;
	}

	/**
	 * Use to handle any payment returns from your gateway to the ipn_url. Do not echo anything here. If you encounter errors
	 *	return the proper headers to your ipn sender. Exits after.
	 */
	function process_ipn_return() {
		global $mp;
	}
}

mp_register_gateway_plugin( 'MP_Gateway_eWay30', 'eway30', __('eWay Rapid 3.0 Payments (beta)', 'mp') );