<?php
/*
  MarketPress PIN Gateway (www.pin.net.au) Plugin
  Author: Marko Miljus (Incsub)
 */

class MP_Gateway_PIN extends MP_Gateway_API {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'PIN';
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
        $this->admin_name = __('PIN (beta)', 'mp');
        $this->public_name = __('Credit Card', 'mp');

        $this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';
        $this->public_key = $mp->get_setting('gateways->pin->public_key');
        $this->private_key = $mp->get_setting('gateways->pin->private_key');
        $this->force_ssl = $mp->get_setting('gateways->pin->is_ssl');
        $this->currency = $mp->get_setting('gateways->pin->currency', 'AUD');
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
    }

    function enqueue_scripts() {
        global $mp;

        if (!is_admin() && get_query_var('pagename') == 'cart' && get_query_var('checkoutstep') == 'checkout') {

            if ($mp->get_setting('gateways->pin->is_ssl')) {
                wp_enqueue_script('js-pin', 'https://api.pin.net.au/pin.js', array('jquery'));
            } else {
                wp_enqueue_script('js-pin', 'https://test-api.pin.net.au/pin.js', array('jquery'));
            }

            wp_enqueue_script('pin-handler', $mp->plugin_url . 'plugins-gateway/pin-files/pin-handler.js', array('js-pin', 'jquery'));
            wp_localize_script('pin-handler', 'pin_vars', array(
                'publishable_api_key' => $this->public_key,
                    )
            );
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
        $address1 = isset($_SESSION['mp_shipping_info']['address1']) ? $_SESSION['mp_shipping_info']['address1'] : '';
        $address2 = isset($_SESSION['mp_shipping_info']['address2']) ? $_SESSION['mp_shipping_info']['address2'] : '';
        $city = isset($_SESSION['mp_shipping_info']['city']) ? $_SESSION['mp_shipping_info']['city'] : '';
        $state = isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : '';
        $postcode = isset($_SESSION['mp_shipping_info']['zip']) ? $_SESSION['mp_shipping_info']['zip'] : '';
        $country = isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : '';

        $content = '';

        $content .= '<div id="pin_checkout_errors"><ul></ul></div>';

        $content .= '<table class="mp_cart_billing">
        <thead><tr>
          <th colspan="2">' . __('Enter Your Credit Card Information:', 'mp') . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td align="right">' . __('Cardholder Name:', 'mp') . '</td>
          <td><input size="35" id="cc-name" type="text" value="' . esc_attr($name) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td align="right">' . __('Address 1:', 'mp') . '</td>
          <td><input id="address-line1" type="text" value="' . esc_attr($address1) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td align="right">' . __('Address 2:', 'mp') . '</td>
          <td><input id="address-line2" type="text" value="' . esc_attr($address2) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td align="right">' . __('City:', 'mp') . '</td>
          <td><input id="address-city" type="text" value="' . esc_attr($city) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td align="right">' . __('State:', 'mp') . '</td>
          <td><input id="address-state" type="text" value="' . esc_attr($state) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td align="right">' . __('Postcode:', 'mp') . '</td>
          <td><input id="address-postcode" type="text" value="' . esc_attr($postcode) . '" /> </td>
          </tr>';

        $content .= '<tr>
          <td align="right">' . __('Country:', 'mp') . '</td>
          <td><input id="address-country" type="text" value="' . esc_attr($country) . '" /> </td>
          </tr>';

        $totals = array();
        foreach ($cart as $product_id => $variations) {
            foreach ($variations as $variation => $data) {
                $totals[] = $mp->before_tax_price($data['price'], $product_id) * $data['quantity'];
            }
        }

        $total = array_sum($totals);

        //coupon line
        if ($coupon = $mp->coupon_value($mp->get_coupon_code(), $total)) {
            $total = $coupon['new_total'];
        }

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
		       
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Card Number', 'mp');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="30" autocomplete="off" id="cc-number"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Expiration:', 'mp');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select id="cc-expiry-month">';
        $content .= $this->_print_month_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select id="cc-expiry-year">';
        $content .= $this->_print_year_dropdown('', true);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('CVC:', 'mp');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="4" autocomplete="off" id="cc-cvc" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<span id="pin_processing" style="display: none;float: right;"><img src="' . $mp->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'psts') . '</span>';
        return $content;
    }

    /**
     * Return the chosen payment details here for final confirmation. You probably don't need
     *  to post anything in the form as it should be in your $_SESSION var already.
     *
     * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
     * @param array $shipping_info. Contains shipping info and email in case you need it
     */
    function confirm_payment_form($cart, $shipping_info) {
        
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
        $output .= "<option " . ($sel == 1 ? ' selected' : '') . " value='01'>01 - " . __('Jan', 'mp') . "</option>";
        $output .= "<option " . ($sel == 2 ? ' selected' : '') . "  value='02'>02 - " . __('Feb', 'mp') . "</option>";
        $output .= "<option " . ($sel == 3 ? ' selected' : '') . "  value='03'>03 - " . __('Mar', 'mp') . "</option>";
        $output .= "<option " . ($sel == 4 ? ' selected' : '') . "  value='04'>04 - " . __('Apr', 'mp') . "</option>";
        $output .= "<option " . ($sel == 5 ? ' selected' : '') . "  value='05'>05 - " . __('May', 'mp') . "</option>";
        $output .= "<option " . ($sel == 6 ? ' selected' : '') . "  value='06'>06 - " . __('Jun', 'mp') . "</option>";
        $output .= "<option " . ($sel == 7 ? ' selected' : '') . "  value='07'>07 - " . __('Jul', 'mp') . "</option>";
        $output .= "<option " . ($sel == 8 ? ' selected' : '') . "  value='08'>08 - " . __('Aug', 'mp') . "</option>";
        $output .= "<option " . ($sel == 9 ? ' selected' : '') . "  value='09'>09 - " . __('Sep', 'mp') . "</option>";
        $output .= "<option " . ($sel == 10 ? ' selected' : '') . "  value='10'>10 - " . __('Oct', 'mp') . "</option>";
        $output .= "<option " . ($sel == 11 ? ' selected' : '') . "  value='11'>11 - " . __('Nov', 'mp') . "</option>";
        $output .= "<option " . ($sel == 12 ? ' selected' : '') . "  value='12'>12 - " . __('Dec', 'mp') . "</option>";

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

        if (!isset($_POST['card_token']))
            $mp->cart_checkout_error(__('The PIN Token was not generated correctly. Please try again.', 'mp'));

        //save to session
        if (!$mp->checkout_error) {
            $_SESSION['card_token'] = $_POST['card_token'];
            $_SESSION['ip_address'] = $_POST['ip_address'];
        }
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
        global $mp;
        if ($order->post_status == 'order_paid')
            $content .= '<p>' . sprintf(__('Your payment for this order totaling %s is complete.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
        return $content;
    }

    /**
     *  Echo a settings meta box with whatever settings you need for you gateway.
     *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
     *  You can access saved settings via $settings array.
     */
    function gateway_settings_box($settings) {
        global $mp;
        ?>
        <div class="postbox">
            <h3 class='hndle'><span><?php _e('PIN', 'mp') ?></span> - <span class="description"><?php _e('PIN makes it easy to start accepting credit card payments with Australia’s first all-in-one online payment system.', 'mp'); ?></span></h3>
            <div class="inside">
                <p class="description"><?php _e("Accept all major credit cards directly on your site. Your sales proceeds are deposited to any Australian bank account, no merchant account required.", 'mp'); ?> <a href="https://pin.net.au/" target="_blank"><?php _e('More Info &raquo;', 'mp') ?></a></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('PIN Mode', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('When in live mode PIN recommends you have an SSL certificate setup for the site where the checkout form will be displayed.', 'mp'); ?> </span><br/>
                            <select name="mp[gateways][pin][is_ssl]">
                                <option value="1"<?php selected($mp->get_setting('gateways->pin->is_ssl'), 1); ?>><?php _e('Force SSL (Live Site)', 'mp') ?></option>
                                <option value="0"<?php selected($mp->get_setting('gateways->pin->is_ssl', 0), 0); ?>><?php _e('No SSL (Testing)', 'mp') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('PIN API Credentials', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('You must login to PIN to <a target="_blank" href="https://dashboard.pin.net.au/account">get your API credentials</a>. You can enter your test keys, then live ones when ready.', 'mp') ?></span>
                            <p><label><?php _e('Secret API Key', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($mp->get_setting('gateways->pin->private_key')); ?>" size="70" name="mp[gateways][pin][private_key]" type="text" />
                                </label></p>
                            <p><label><?php _e('Publishable API Key', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($mp->get_setting('gateways->pin->public_key')); ?>" size="70" name="mp[gateways][pin][public_key]" type="text" />
                                </label></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Currency', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('Selecting a currency other than currency supported by PIN may cause problems at checkout.', 'mp'); ?></span><br />
                            <select name="mp[gateways][pin][currency]">
                                <?php
                                $sel_currency = $mp->get_setting('gateways->pin->currency', $settings['currency']);
                                $currencies = array(
                                    "AUD" => 'AUD',
                                    "NZD" => 'NZD',
                                    "USD" => 'USD',
                                    'SGD' => 'SGD',
                                    'GBP' => 'GBP',
                                    'EUR' => 'EUR'
                                );

                                foreach ($currencies as $k => $v) {
                                    echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
                                }
                                ?>
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
     *  array. Don't forget to return!
     */
    function process_gateway_settings($settings) {
        return $settings;
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
        global $mp;
        $settings = get_option('mp_settings');

        //make sure token is set at this point
        if (!isset($_SESSION['card_token'])) {
            $mp->cart_checkout_error(__('The PIN Token was not generated correctly. Please go back and try again.', 'mp'));
            return false;
        }

        if ($this->force_ssl) {
            define('PIN_API_CHARGE_URL', 'https://api.pin.net.au/1/charges');
        } else {
            define('PIN_API_CHARGE_URL', 'https://test-api.pin.net.au/1/charges');
        }

        define('PIN_API_KEY', $this->private_key);

        $token = $_SESSION['card_token'];

        if ($token) {

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

                $args = array(
                    'method' => 'POST',
                    'httpversion' => '1.1',
                    'timeout' => apply_filters('http_request_timeout', 30),
                    'blocking' => true,
                    'compress' => true,
                    'headers' => array('Authorization' => 'Basic ' . base64_encode(PIN_API_KEY . ':' . '')),
                    'body' => array(
                        'amount' => (int) $total * 100,
                        'currency' => strtolower($this->currency),
                        'description' => sprintf(__('%s Store Purchase - Order ID: %s, Email: %s', 'mp'), get_bloginfo('name'), $order_id, $_SESSION['mp_shipping_info']['email']),
                        'email' => $_SESSION['mp_shipping_info']['email'],
                        'ip_address' => $_SESSION['ip_address'],
                        'card_token' => $_SESSION['card_token']
                    ),
                    'cookies' => array()
                );

                $charge = wp_remote_post(PIN_API_CHARGE_URL, $args);

                $charge = json_decode($charge['body'], true);

                $charge = $charge['response'];

                if ($charge['success'] == true) {
                    //setup our payment details
                    $payment_info = array();
                    $payment_info['gateway_public_name'] = $this->public_name;
                    $payment_info['gateway_private_name'] = $this->admin_name;
                    $payment_info['method'] = sprintf(__('%1$s Card %2$s', 'mp'), ucfirst($charge['card']['scheme']), $charge['card']['display_number']);
                    $payment_info['transaction_id'] = $charge['token'];
                    $timestamp = time();
                    $payment_info['status'][$timestamp] = __('Paid', 'mp');
                    $payment_info['total'] = $total;
                    $payment_info['currency'] = $this->currency;

                    $order = $mp->create_order($order_id, $cart, $_SESSION['mp_shipping_info'], $payment_info, true);

                    unset($_SESSION['card_token']);
                    $mp->set_cart_cookie(Array());
                } else {
                    unset($_SESSION['card_token']);
                    $mp->cart_checkout_error(sprintf(__('There was an error processing your card. Please <a href="%s">go back and try again</a>.', 'mp'), mp_checkout_step_url('checkout')));
                    return false;
                }
            } catch (Exception $e) {
                unset($_SESSION['card_token']);
                $mp->cart_checkout_error(sprintf(__('There was an error processing your card: "%s". Please <a href="%s">go back and try again</a>.', 'mp'), $e->getMessage(), mp_checkout_step_url('checkout')));
                return false;
            }
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
mp_register_gateway_plugin('MP_Gateway_PIN', 'pin', __('PIN (beta)', 'mp'));