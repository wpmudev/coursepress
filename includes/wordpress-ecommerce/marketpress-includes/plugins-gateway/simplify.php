<?php
/*
MarketPress Simplify Gateway Plugin
Author: MasterCard International Incorporated
*/

/*
 * Copyright (c) 2013, MasterCard International Incorporated
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are 
 * permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of 
 * conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its 
 * contributors may be used to endorse or promote products derived from this software 
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING 
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF 
 * SUCH DAMAGE.
 */

class MP_Gateway_Simplify extends MP_Gateway_API {

	var $plugin_name = 'simplify';
	var $admin_name = '';
	var $public_name = '';
	var $method_img_url = '';
	var $method_button_img_url = '';
	var $force_ssl;
	var $ipn_url;
	var $skip_form = false;
	var $publishable_key,
		$private_key,
		$currency;

	function on_creation() {
		global $mp;
		$settings = get_option('mp_settings');
		$this->admin_name = __('Simplify', 'mp');
		$this->public_name = __('Credit Card', 'mp');
		$this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
		$this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';
		$this->publishable_key = $mp->get_setting('gateways->simplify->publishable_key');
		$this->private_key = $mp->get_setting('gateways->simplify->private_key');
		$this->force_ssl = $mp->get_setting('gateways->simplify->is_ssl');
		$this->currency = $mp->get_setting('gateways->simplify->currency', 'USD');
		add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_scripts') );
	}

	function enqueue_scripts() {
		global $mp;
		if(!is_admin() && get_query_var('pagename') == 'cart' && get_query_var('checkoutstep') == 'checkout') {
			wp_enqueue_script('js-simplify', 'https://www.simplify.com/commerce/v1/simplify.js', array('jquery'));
			wp_enqueue_script('simplify-token', $mp->plugin_url . 'plugins-gateway/simplify-files/simplify_token.js', array('js-simplify', 'jquery'));
			wp_localize_script('simplify-token', 'simplify', array('publicKey' => $this->publishable_key));
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
		$name = isset($_SESSION['mp_shipping_info']['name']) ? $_SESSION['mp_shipping_info']['name'] : '';
		$content .= '<div class="row-fluid">';
			$content .= '<div class="span6 offset3">';
				$content .= '<label for="cc-number">' . __('Credit Card Number', 'mp') . ': </label><input class="input-block-level" id="cc-number" type="text" maxlength="20" autocomplete="off" value="" placeholder="' . __('Card Number', 'mp') . '" autofocus />';
				$content .= '<div class="row-fluid">';
					$content .= '<div class="span4"><label for="cc-cvc">' . __('CVC', 'mp') . ': </label><input class="input-block-level" id="cc-cvc" type="text" maxlength="3" autocomplete="off" value="" placeholder="' . __('CVC', 'mp') . '" /></div>';
					$content .= '<div class="span4"><label>' . __('Expiry Date', 'mp') . ': </label><select class="input-block-level" id="cc-exp-month">' . $this->_print_month_dropdown() . '</select> - <select class="input-block-level" id="cc-exp-year">' . $this->_print_year_dropdown() . '</select></div>';
				$content .= '</div>';
			$content .= '</div>';
		$content .= '</div>';
		return $content;
	}

	/**
	* Return the chosen payment details here for final confirmation. You probably don't need
	* to post anything in the form as it should be in your $_SESSION var already.
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function confirm_payment_form($cart, $shipping_info) {
		global $mp;

		// Token MUST be set at this point
		if(!isset($_SESSION['simplifyToken'])) {
			$mp->cart_checkout_error(__('The Simplify Token was not generated correctly. Please go back and try again.', 'mp'));
			return false;
		}

		// Setup the Simplify API
		if(!class_exists('Simplify')) {
			require_once($mp->plugin_dir . "plugins-gateway/simplify-files/lib/Simplify.php");
		}
		Simplify::$publicKey = $this->publishable_key;
		Simplify::$privateKey = $this->private_key;

		try {
			$token  = Simplify_CardToken::findCardToken($_SESSION['simplifyToken']);
		} catch (Exception $e) {
			$mp->cart_checkout_error(sprintf(__('%s. Please go back and try again.', 'mp'), $e->getMessage()));
			return false;
		}

		$content = '<table class="mp_cart_billing table table-striped table-bordered table-hover">';
			$content .= '<thead>';
				$content .= '<tr>';
					$content .= '<th>' . __('Billing Information:', 'mp') . '</th>';
					$content .= '<th align="right" class="align-right"><a href="' . mp_checkout_step_url('checkout') . '"> ' . __('Edit', 'mp') . '</a></th>';
				$content .= '</tr>';
			$content .= '</thead>';
			$content .= '<tbody>';
				$content .= '<tr>';
					$content .= '<td align="right" class="span4 align-right">' . __('Card Type:', 'mp') . '</td>';
					$content .= '<td>' . sprintf(__('%1$s', 'mp'), $token->card->type) . '</td>';
				$content .= '</tr>';
				$content .= '<tr>';
					$content .= '<td align="right" class="span4 align-right">' . __('Last 4 Digits:', 'mp') . '</td>';
					$content .= '<td>' . sprintf(__('%1$s', 'mp'), $token->card->last4) . '</td>';
				$content .= '</tr>';
				$content .= '<tr>';
					$content .= '<td align="right" class="span4 align-right">' . __('Expires:', 'mp') . '</td>';
					$content .= '<td>' . sprintf(__('%1$s/%2$s', 'mp'), $token->card->expMonth, $token->card->expYear) . '</td>';
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
		for($i=$minYear; $i<$maxYear; $i++) {
			if($pfp) {
				$output .= "<option value='" . substr($i, 0, 4) . "'" .($sel==(substr($i, 0, 4))?' selected':'') . ">" . $i . "</option>";
			} else {
				$output .= "<option value='" . substr($i, 2, 2) . "'" .($sel==(substr($i, 2, 2))?' selected':'') . ">" . $i . "</option>";
			}
		}
		return($output);
	}

	/**
	* Print the months
	*/
	function _print_month_dropdown($sel='') {
		$output =  "<option value=''>--</option>";
		$output .=  "<option " . ($sel==1?' selected':'') . " value='01'>01 - Jan</option>";
		$output .=  "<option " . ($sel==2?' selected':'') . "  value='02'>02 - Feb</option>";
		$output .=  "<option " . ($sel==3?' selected':'') . "  value='03'>03 - Mar</option>";
		$output .=  "<option " . ($sel==4?' selected':'') . "  value='04'>04 - Apr</option>";
		$output .=  "<option " . ($sel==5?' selected':'') . "  value='05'>05 - May</option>";
		$output .=  "<option " . ($sel==6?' selected':'') . "  value='06'>06 - Jun</option>";
		$output .=  "<option " . ($sel==7?' selected':'') . "  value='07'>07 - Jul</option>";
		$output .=  "<option " . ($sel==8?' selected':'') . "  value='08'>08 - Aug</option>";
		$output .=  "<option " . ($sel==9?' selected':'') . "  value='09'>09 - Sep</option>";
		$output .=  "<option " . ($sel==10?' selected':'') . "  value='10'>10 - Oct</option>";
		$output .=  "<option " . ($sel==11?' selected':'') . "  value='11'>11 - Nov</option>";
		$output .=  "<option " . ($sel==12?' selected':'') . "  value='12'>12 - Dec</option>";
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

		if(!isset($_POST['simplifyToken'])) {
			$mp->cart_checkout_error(__('The Simplify Token was not generated correctly. Please try again.', 'mp'));
		} elseif(!$mp->checkout_error) {
			$_SESSION['simplifyToken'] = $_POST['simplifyToken'];
		}
	}

	/**
	* Filters the order confirmation email message body. You may want to append something to
	* the message. Optional
	*
	* Don't forget to return!
	*/
	function order_confirmation_email($msg, $order) {
		return $msg;
	}

	/**
	* Return any html you want to show on the confirmation screen after checkout. This
	* should be a payment details box and message.
	*
	* Don't forget to return!
	*/
	function order_confirmation_msg($content, $order) {
		global $mp;
		if($order->post_status == 'order_paid') {
			$content .= '<p>' . sprintf(__('Your payment for this order totaling %s is complete.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
		}
		return $content;
	}

	/**
	* Echo a settings meta box with whatever settings you need for you gateway.
	* Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
	* You can access saved settings via $settings array.
	*/
	function gateway_settings_box($settings) {
		global $mp;
		?>
		<div class="postbox">
			<h3 class='hndle' style="background: #222; box-shadow: inset 0px 15px 15px #333; text-shadow: 0px 1px 0px #000; color: #ccc;">
				<img style="width: 100px; float: left; padding: 5px; padding-right: 25px;" src="<?php echo $mp->plugin_url . 'images/simplify.png'; ?>" />
				<span style="color: #fff;"><?php _e('Simplify Commerce', 'mp') ?> <em><?php _e('by MasterCard', 'mp') ?></em></span> - <span style="color: #ccc;" class="description"><?php _e('Simplify helps merchants to accept online payments from Mastercard, Visa, American Express, Discover, JCB, and Diners Club cards. It\'s that simple. We offer a merchant account and payment gateway in a single, secure package so you can concentrate on what really matters to your business. Only supports USD currently.', 'mp'); ?> <a style="color: #fff;" href="https://www.simplify.com/commerce/login/signup" target="_blank"><?php _e('Signup for Simplify Commerce &raquo;', 'mp') ?></a></span>
				<br style="clear: both;" />
			</h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Simplify API Credentials', 'mp') ?></th>
						<td>
							<span class="description"><?php _e('Login to Simplify to <a target="_blank" href="https://www.simplify.com/commerce/app#/account/apiKeys">get your API credentials</a>. Enter your test credentials, then live ones when ready.', 'mp') ?></span>
							<p><label><?php _e('Private Key', 'mp') ?><br /><input value="<?php echo esc_attr($mp->get_setting('gateways->simplify->private_key')); ?>" size="70" name="mp[gateways][simplify][private_key]" type="text" /></label></p>
							<p><label><?php _e('Public Key', 'mp') ?><br /><input value="<?php echo esc_attr($mp->get_setting('gateways->simplify->publishable_key')); ?>" size="70" name="mp[gateways][simplify][publishable_key]" type="text" /></label></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Simplify SSL Mode', 'mp') ?></th>
						<td>
							<span class="description"><?php _e('When in live mode, although it is not required, Simplify recommends you have an SSL certificate.', 'mp'); ?></span><br/>
							<select name="mp[gateways][simplify][is_ssl]">
								<option value="1"<?php selected($mp->get_setting('gateways->simplify->is_ssl'), 1); ?>><?php _e('Force SSL', 'mp') ?></option>
								<option value="0"<?php selected($mp->get_setting('gateways->simplify->is_ssl', 0), 0); ?>><?php _e('No SSL', 'mp') ?></option>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}

	/**
	* Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
	* array. Don't forget to return!
	*/
	function process_gateway_settings($settings) {
		return $settings;
	}

	/**
	* Use this to do the final payment. Create the order then process the payment. If
	* you know the payment is successful right away go ahead and change the order status
	* as well.
	* Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	* it will redirect to the next step.
	*
	* @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	* @param array $shipping_info. Contains shipping info and email in case you need it
	*/
	function process_payment($cart, $shipping_info) {
		global $mp;
		$settings = get_option('mp_settings');

		// Token MUST be set at this point
		if(!isset($_SESSION['simplifyToken'])) {
			$mp->cart_checkout_error(__('The Simplify Token was not generated correctly. Please go back and try again.', 'mp'));
			return false;
		}

		// Setup the Simplify API
		if(!class_exists('Simplify')) {
			require_once($mp->plugin_dir . "plugins-gateway/simplify-files/lib/Simplify.php");
		}
		Simplify::$publicKey = $this->publishable_key;
		Simplify::$privateKey = $this->private_key;

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
			$token = $SESSION['simplifyToken'];
			$charge = Simplify_Payment::createPayment(array(
				'amount' => $total * 100,
				'token' => $_SESSION['simplifyToken'],
				'description' => sprintf(__('%s Store Purchase - Order ID: %s, Email: %s', 'mp'), get_bloginfo('name'), $order_id, $_SESSION['mp_shipping_info']['email']),
				'currency' => $this->currency
				));

			if($charge->paymentStatus == 'APPROVED') {
				$payment_info = array();
				$payment_info['gateway_public_name'] = $this->public_name;
				$payment_info['gateway_private_name'] = $this->admin_name;
				$payment_info['method'] = sprintf(__('%1$s Card ending in %2$s - Expires %3$s', 'mp'), $charge->card->type, $charge->card->last4, $charge->card->expMonth . '/' . $charge->card->expYear);
				$payment_info['transaction_id'] = $charge->id;
				$timestamp = time();
				$payment_info['status'][$timestamp] = __('Paid', 'mp');
				$payment_info['total'] = $total;
				$payment_info['currency'] = $this->currency;
				$order = $mp->create_order(
					$order_id,
					$cart,
					$_SESSION['mp_shipping_info'],
					$payment_info,
					true
					);
				unset($_SESSION['simplifyToken']);
				$mp->set_cart_cookie(Array());
			}
		} catch (Exception $e) {
			unset($_SESSION['simplifyToken']);
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

mp_register_gateway_plugin('MP_Gateway_Simplify', 'simplify', __('Simplify Commerce by MasterCard', 'mp'));
