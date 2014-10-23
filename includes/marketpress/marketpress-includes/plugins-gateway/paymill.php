<?php
/*
  MarketPress Paymill Gateway Plugin
  Author: Marko Miljus
 */

class MP_Gateway_Paymill extends MP_Gateway_API {

//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'paymill';
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
        $this->admin_name = __('Paymill (beta)', 'mp');
        $this->public_name = __('Credit Card', 'mp');

        $this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';

        if (isset($settings['gateways']['paymill']['public_key'])) {
            $this->public_key = $settings['gateways']['paymill']['public_key'];
            $this->private_key = $settings['gateways']['paymill']['private_key'];
        }

        $this->force_ssl = (bool) ( isset($settings['gateways']['paymill']['is_ssl']) && $settings['gateways']['paymill']['is_ssl'] );
        $this->currency = isset($settings['gateways']['paymill']['currency']) ? $settings['gateways']['paymill']['currency'] : 'EUR';

        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
    }

    function enqueue_scripts() {
        global $mp;

        if (!is_admin() && get_query_var('pagename') == 'cart' && get_query_var('checkoutstep') == 'checkout') {

            wp_enqueue_script('js-paymill', 'https://bridge.paymill.com/', array('jquery'));
            wp_enqueue_script('paymill-token', $mp->plugin_url . 'plugins-gateway/paymill-files/paymill_token.js', array('js-paymill', 'jquery'));
            wp_localize_script('paymill-token', 'paymill_token', array(
                'public_key' => $this->public_key,
                'invalid_cc_number' => __('Please enter a valid Credit Card Number.', 'mp'),
                'invalid_expiration' => __('Please choose a valid Expiration Date.', 'mp'),
                'invalid_cvc' => __('Please enter a valid Card CVC', 'mp'),
                'expired_card' => __('Card is no longer valid or has expired', 'mp'),
                'invalid_cardholder' => __('Invalid cardholder', 'mp'),
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

        $content = '';

        $content .= '<div id="paymill_checkout_errors"></div>';

        $content .= '<table class="mp_cart_billing">
        <thead><tr>
          <th colspan="2">' . __('Enter Your Credit Card Information:', 'mp') . '</th>
        </tr></thead>
        <tbody>
          <tr>
          <td align="right">' . __('Cardholder Name:', 'mp') . '</td>
          <td><input size="35" class="card-holdername" type="text" value="' . esc_attr($name) . '" /> </td>
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
        $content .= '<input type="text" size="30" autocomplete="off" class="card-number"/>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('Expiration:', 'mp');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<select class="card-expiry-month">';
        $content .= $this->_print_month_dropdown();
        $content .= '</select>';
        $content .= '<span> / </span>';
        $content .= '<select class="card-expiry-year">';
        $content .= $this->_print_year_dropdown('', true);
        $content .= '</select>';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td>';
        $content .= __('CVC:', 'mp');
        $content .= '</td>';
        $content .= '<td>';
        $content .= '<input type="text" size="4" autocomplete="off" class="card-cvc" />';
        $content .= '<input type="hidden" class="currency" value="' . $this->currency . '" />';
        $content .= '<input type="hidden" class="amount" value="' . $total * 100 . '" />';
        $content .= '</td>';
        $content .= '</tr>';
        $content .= '</table>';
        $content .= '<span id="paymill_processing" style="display: none;float: right;"><img src="' . $mp->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'psts') . '</span>';
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
    function _print_year_dropdown($sel='', $pfp = false) {
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
    function _print_month_dropdown($sel='') {
        $output = "<option value=''>--</option>";
        $output .= "<option " . ($sel == 1 ? ' selected' : '') . " value='01'>01 - ".__('Jan', 'mp')."</option>";
        $output .= "<option " . ($sel == 2 ? ' selected' : '') . "  value='02'>02 - ".__('Feb', 'mp')."</option>";
        $output .= "<option " . ($sel == 3 ? ' selected' : '') . "  value='03'>03 - ".__('Mar', 'mp')."</option>";
        $output .= "<option " . ($sel == 4 ? ' selected' : '') . "  value='04'>04 - ".__('Apr', 'mp')."</option>";
        $output .= "<option " . ($sel == 5 ? ' selected' : '') . "  value='05'>05 - ".__('May', 'mp')."</option>";
        $output .= "<option " . ($sel == 6 ? ' selected' : '') . "  value='06'>06 - ".__('Jun', 'mp')."</option>";
        $output .= "<option " . ($sel == 7 ? ' selected' : '') . "  value='07'>07 - ".__('Jul', 'mp')."</option>";
        $output .= "<option " . ($sel == 8 ? ' selected' : '') . "  value='08'>08 - ".__('Aug', 'mp')."</option>";
        $output .= "<option " . ($sel == 9 ? ' selected' : '') . "  value='09'>09 - ".__('Sep', 'mp')."</option>";
        $output .= "<option " . ($sel == 10 ? ' selected' : '') . "  value='10'>10 - ".__('Oct', 'mp')."</option>";
        $output .= "<option " . ($sel == 11 ? ' selected' : '') . "  value='11'>11 - ".__('Nov', 'mp')."</option>";
        $output .= "<option " . ($sel == 12 ? ' selected' : '') . "  value='12'>12 - ".__('Dec', 'mp')."</option>";

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

        if (!isset($_POST['paymillToken']))
            $mp->cart_checkout_error(__('The Paymill Token was not generated correctly. Please try again.', 'mp'));

        //save to session
        if (!$mp->checkout_error) {
            $_SESSION['paymillToken'] = $_POST['paymillToken'];
        }
    }

    /**
     * Filters the order confirmation email message body. You may want to append something to
     *  the message. Optional
     *
     * Don't forget to return!
     */
    function order_confirmation_email($msg, $order = null) {
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
     * Echo a settings meta box with whatever settings you need for you gateway.
     *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
     *  You can access saved settings via $settings array.
     */
    function gateway_settings_box($settings) {
        global $mp;
        ?>
        <div class="postbox">
            <h3 class='hndle'><span><?php _e('Paymill', 'mp') ?></span> - <span class="description"><?php _e('Paymill makes it easy to start accepting credit cards directly on your site with full PCI compliance', 'mp'); ?></span></h3>
            <div class="inside">
                <p class="description"><?php _e("Accept Visa, MasterCard, Maestro UK, Discover and Solo cards directly on your site. You don't need a merchant account or gateway. Credit cards go directly to Paymill's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'mp'); ?> <a href="https://www.paymill.com/en-gb/support-3/worth-knowing/pci-security/" target="_blank"><?php _e('More Info &raquo;', 'mp') ?></a></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Paymill Mode', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('When in live mode Paymill recommends you have an SSL certificate setup for the site where the checkout form will be displayed.', 'mp'); ?> <a href="https://www.paymill.com/en-gb/support-3/support/faqs/" target="_blank"><?php _e('More Info &raquo;', 'mp') ?></a></span><br/>
                            <select name="mp[gateways][paymill][is_ssl]">
                                <option value="1"<?php selected($settings['gateways']['paymill']['is_ssl'], 1); ?>><?php _e('Force SSL (Live Site)', 'mp') ?></option>
                                <option value="0"<?php selected($settings['gateways']['paymill']['is_ssl'], 0); ?>><?php _e('No SSL (Testing)', 'mp') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Paymill API Credentials', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('You must login to Paymill to <a target="_blank" href="https://app.paymill.com/en-gb/auth/login">get your API credentials</a>. You can enter your test keys, then live ones when ready.', 'mp') ?></span>
                            <p><label><?php _e('Private key', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($settings['gateways']['paymill']['private_key']); ?>" size="70" name="mp[gateways][paymill][private_key]" type="text" />
                                </label></p>
                            <p><label><?php _e('Public key', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($settings['gateways']['paymill']['public_key']); ?>" size="70" name="mp[gateways][paymill][public_key]" type="text" />
                                </label></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Currency', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('Selecting a currency other than that used for your store and/or other than currency assigned by Paymill may cause problems at checkout. <a href="https://paymill.zendesk.com/entries/22598076-Which-currencies-countries-does-Paymill-accept-and-pay-out-">Read more</a>', 'mp'); ?></span><br />
                            <select name="mp[gateways][paymill][currency]">
                                <?php
                                $sel_currency = isset($settings['gateways']['paymill']['currency']) ? $settings['gateways']['paymill']['currency'] : $settings['currency'];
                                $currencies = array(
                                    "EUR" => 'EUR',
                                    "BGN" => 'BGN',
                                    "CZK" => 'CZK',
                                    "HRK" => 'HRK',
                                    "DKK" => 'DKK',
                                    "GIP" => 'GIP',
                                    "HUF" => 'HUF',
                                    "ISK" => 'ISK',
                                    "ILS" => 'ILS',
                                    "LVL" => 'LVL',
                                    "CHF" => 'CHF',
                                    "LTL" => 'LTL',
                                    "NOK" => 'NOK',
                                    "PLN" => 'PLN',
                                    "RON" => 'RON',
                                    "SEK" => 'SEK',
                                    "TRY" => 'TRY',
                                    "GBP" => 'GBP'
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
        if (!isset($_SESSION['paymillToken'])) {
            $mp->cart_checkout_error(__('The Paymill Token was not generated correctly. Please go back and try again.', 'mp'));
            return false;
        }

        define('PAYMILL_API_HOST', 'https://api.paymill.com/v2/');
        define('PAYMILL_API_KEY', $settings['gateways']['paymill']['private_key']);

        $token = $_SESSION['paymillToken'];

        if ($token) {
            require "paymill-files/lib/Services/Paymill/Transactions.php";
            $transactionsObject = new Services_Paymill_Transactions(PAYMILL_API_KEY, PAYMILL_API_HOST);

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
            if ($shipping_price = $mp->shipping_price()) {
                $total += $shipping_price;
            }

            //tax line
            if ($tax_price = $mp->tax_price()) {
                $total += $tax_price;
            }

            $order_id = $mp->generate_order_id();

            try {
                $params = array(
                    'amount' => $total * 100, //// I.e. 49 * 100 = 4900 Cents = 49 EUR
                    'currency' => strtolower($this->currency), // ISO 4217
                    'token' => $token,
                    'description' => sprintf(__('%s Store Purchase - Order ID: %s, Email: %s', 'mp'), get_bloginfo('name'), $order_id, $_SESSION['mp_shipping_info']['email'])
                );
                $charge = $transactionsObject->create($params);

                if ($charge['status'] == 'closed') {
                    //setup our payment details
                    $payment_info = array();
                    $payment_info['gateway_public_name'] = $this->public_name;
                    $payment_info['gateway_private_name'] = $this->admin_name;
                    $payment_info['method'] = sprintf(__('%1$s Card ending in %2$s - Expires %3$s', 'mp'), ucfirst($charge['payment']['card_type']), $charge['payment']['last4'], $charge['payment']['expire_month'] . '/' . $charge['payment']['expire_year']);
                    $payment_info['transaction_id'] = $charge['id'];
                    $timestamp = time();
                    $payment_info['status'][$timestamp] = __('Paid', 'mp');
                    $payment_info['total'] = $total;
                    $payment_info['currency'] = $this->currency;

                    $order = $mp->create_order($order_id, $cart, $_SESSION['mp_shipping_info'], $payment_info, true);
                    unset($_SESSION['paymillToken']);
                    $mp->set_cart_cookie(Array());
                }
            } catch (Exception $e) {
                unset($_SESSION['paymillToken']);
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
mp_register_gateway_plugin('MP_Gateway_Paymill', 'paymill', __('Paymill (beta)', 'mp'));