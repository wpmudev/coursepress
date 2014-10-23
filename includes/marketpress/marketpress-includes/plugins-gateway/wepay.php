<?php
/*
  MarketPress WePay Gateway Plugin
  Author: Marko Miljus (Incsub)
 */

class MP_Gateway_Wepay extends MP_Gateway_API {

    var $version = '1.0b';
    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'wepay';
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
    var $publishable_key, $private_key, $currency, $mode, $checkout_type;

    /**
     * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
     */
    function on_creation() {
        global $mp;

        //set names here to be able to translate
        $this->admin_name = __('WePay', 'mp');
        $this->public_name = __('Credit Card', 'mp');

        $this->method_img_url = $mp->plugin_url . 'images/credit_card.png';
        $this->method_button_img_url = $mp->plugin_url . 'images/cc-button.png';

        $this->client_id =  $mp->get_setting('gateways->wepay->client_id');
        $this->client_secret = $mp->get_setting('gateways->wepay->client_secret');
        $this->access_token = $mp->get_setting('gateways->wepay->access_token');
        $this->account_id = $mp->get_setting('gateways->wepay->account_id');
        $this->mode = $mp->get_setting('gateways->wepay->mode');
        $this->checkout_type = $mp->get_setting('gateways->wepay->checkout_type');

        $this->force_ssl = (bool) ( $mp->get_setting('gateways->wepay->is_ssl') );
        $this->currency = 'USD';//just USD for now

        add_action('wp_footer', array(&$this, 'enqueue_scripts'));
    }

    function enqueue_scripts() {
        global $mp;

        if (!is_admin() && get_query_var('pagename') == 'cart' && get_query_var('checkoutstep') == 'checkout') {

            wp_enqueue_script('wepay-tokenization', 'https://static.wepay.com/min/js/tokenization.v2.js', array('jquery'), $this->version, true);
            wp_enqueue_script('wepay-script', $mp->plugin_url . 'plugins-gateway/wepay-files/wepay.js', array('wepay-tokenization'));

            $meta = get_user_meta(get_current_user_id(), 'mp_shipping_info', true);

            $email = (!empty($_SESSION['mp_billing_info']['email'])) ? $_SESSION['mp_billing_info']['email'] : (!empty($meta['email']) ? $meta['email'] : $_SESSION['mp_shipping_info']['email']);
            $name = (!empty($_SESSION['mp_billing_info']['name'])) ? $_SESSION['mp_billing_info']['name'] : (!empty($meta['name']) ? $meta['name'] : $_SESSION['mp_shipping_info']['name']);

            $address1 = (isset($_SESSION['mp_shipping_info']['address1']) ? $_SESSION['mp_shipping_info']['address1'] : $meta['address1']);
            $city = (isset($_SESSION['mp_shipping_info']['city']) ? $_SESSION['mp_shipping_info']['city'] : $meta['city']);
            $state = (isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : $meta['state']);
            $zip = (isset($_SESSION['mp_shipping_info']['zip']) ? $_SESSION['mp_shipping_info']['zip'] : $meta['zip']);
            $country = (isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : $meta['country']);

            wp_localize_script('wepay-script', 'wepay_script', array(
                'mode' => $this->mode,
                'client_id' => $this->client_id,
                'user_name' => $name,
                'email' => $email,
                'address' => $address1,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'country' => $country
                    )
            );
        }
        ?>

        <?php
    }

    /**
     * Return fields you need to add to the top of the payment screen, like your credit card info fields
     *
     * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
     * @param array $shipping_info. Contains shipping info and email in case you need it
     */
    function payment_form($cart, $shipping_info) {
        global $mp;

        require $mp->plugin_dir . 'plugins-gateway/wepay-files/wepay-sdk.php';

        if ($this->mode == 'staging') {
            WePay::useStaging($mp->client_id, $mp->client_secret);
        } else {
            WePay::useProduction($mp->client_id, $mp->client_secret);
        }

        $settings = get_option('mp_settings');

        $meta = get_user_meta(get_current_user_id(), 'mp_shipping_info', true);

        $email = (!empty($_SESSION['mp_billing_info']['email'])) ? $_SESSION['mp_billing_info']['email'] : (!empty($meta['email']) ? $meta['email'] : $_SESSION['mp_shipping_info']['email']);
        $name = (!empty($_SESSION['mp_billing_info']['name'])) ? $_SESSION['mp_billing_info']['name'] : (!empty($meta['name']) ? $meta['name'] : $_SESSION['mp_shipping_info']['name']);

        $address1 = (isset($_SESSION['mp_shipping_info']['address1']) ? $_SESSION['mp_shipping_info']['address1'] : $meta['address1']);
        $city = (isset($_SESSION['mp_shipping_info']['city']) ? $_SESSION['mp_shipping_info']['city'] : $meta['city']);
        $state = (isset($_SESSION['mp_shipping_info']['state']) ? $_SESSION['mp_shipping_info']['state'] : $meta['state']);
        $zip = (isset($_SESSION['mp_shipping_info']['zip']) ? $_SESSION['mp_shipping_info']['zip'] : $meta['zip']);
        $country = (isset($_SESSION['mp_shipping_info']['country']) ? $_SESSION['mp_shipping_info']['country'] : $meta['country']);

        $content = '';

        $content .= '<div id="wepay_checkout_errors"></div>';

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
        $content .= '<span id="wepay_processing" style="display: none;float: right;"><img src="' . $mp->plugin_url . 'images/loading.gif" /> ' . __('Processing...', 'mp') . '</span>';
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
        if (isset($_POST['payment_method_id'])) {
            $_SESSION['payment_method_id'] = $_POST['payment_method_id'];
        }
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
        $output .= "<option " . ($sel == 2 ? ' selected' : '') . "  value='02'>02 - Feb</option>";
        $output .= "<option " . ($sel == 3 ? ' selected' : '') . "  value='03'>03 - Mar</option>";
        $output .= "<option " . ($sel == 4 ? ' selected' : '') . "  value='04'>04 - Apr</option>";
        $output .= "<option " . ($sel == 5 ? ' selected' : '') . "  value='05'>05 - May</option>";
        $output .= "<option " . ($sel == 6 ? ' selected' : '') . "  value='06'>06 - Jun</option>";
        $output .= "<option " . ($sel == 7 ? ' selected' : '') . "  value='07'>07 - Jul</option>";
        $output .= "<option " . ($sel == 8 ? ' selected' : '') . "  value='08'>08 - Aug</option>";
        $output .= "<option " . ($sel == 9 ? ' selected' : '') . "  value='09'>09 - Sep</option>";
        $output .= "<option " . ($sel == 10 ? ' selected' : '') . "  value='10'>10 - Oct</option>";
        $output .= "<option " . ($sel == 11 ? ' selected' : '') . "  value='11'>11 - Nov</option>";
        $output .= "<option " . ($sel == 12 ? ' selected' : '') . "  value='12'>12 - Dec</option>";

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
        $_SESSION['payment_method_id'] = $_POST['payment_method_id'];
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
            <h3 class='hndle'><span><?php _e('WePay', 'mp') ?></span> - <span class="description"><?php _e('Wepay makes it easy to start accepting credit cards directly on your site with full PCI compliance', 'mp'); ?></span></h3>
            <div class="inside">
                <p class="description"><?php _e("Accept cards directly on your site. You don't need a merchant account or gateway. WePay handles everything including storing cards. Credit cards go directly to WePay's secure environment, and never hit your servers so you can avoid most PCI requirements.", 'mp'); ?> <a href="https://wepay.com/" target="_blank"><?php _e('More Info &raquo;', 'mp') ?></a></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('WePay Mode', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('Choose STAGING if you have registered the app on stage.wepay.com, or PRODUCTION if you registered on www.wepay.com', 'mp'); ?> </span><br/>
                            <select name="mp[gateways][wepay][mode]">
                                <option value="staging"<?php selected($mp->get_setting('gateways->wepay->mode'), 'staging'); ?>><?php _e('Staging', 'mp') ?></option>
                                <option value="production"<?php selected($mp->get_setting('gateways->wepay->mode'), 'production'); ?>><?php _e('Production', 'mp') ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Checkout Type', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('Choose type of payments', 'mp'); ?> </span><br/>
                            <select name="mp[gateways][wepay][checkout_type]">
                                <option value="GOODS"<?php selected($mp->get_setting('gateways->wepay->checkout_type'),'GOODS'); ?>><?php _e('Goods', 'mp') ?></option>
                                <option value="SERVICE"<?php selected($mp->get_setting('gateways->wepay->checkout_type'), 'SERVICE'); ?>><?php _e('Service', 'mp') ?></option>
                                <option value="PERSONAL"<?php selected($mp->get_setting('gateways->wepay->checkout_type'),'PERSONAL'); ?>><?php _e('Personal', 'mp') ?></option>
                                <option value="EVENT"<?php selected($mp->get_setting('gateways->wepay->checkout_type'), 'EVENT'); ?>><?php _e('Event', 'mp') ?></option>
                                <option value="DONATION"<?php selected($mp->get_setting('gateways->wepay->checkout_type'),'DONATION'); ?>><?php _e('Donation', 'mp') ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('WePay SSL', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('When in live mode it is recommended to use SSL certificate setup for the site where the checkout form will be displayed.', 'mp'); ?> </span><br/>
                            <select name="mp[gateways][wepay][is_ssl]">
                                <option value="1"<?php selected($mp->get_setting('gateways->wepay->is_ssl'), 1); ?>><?php _e('Force SSL', 'mp') ?></option>
                                <option value="0"<?php selected($mp->get_setting('gateways->wepay->is_ssl'), 0); ?>><?php _e('No SSL', 'mp') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('WePay API Credentials', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('You must login to WePay to <a target="_blank" href="https://www.wepay.com/">get your API credentials</a>. Make sure to check "Tokenize credit cards" option under "API Keys" section of your WePay app.', 'mp') ?></span>
                            <p>
                                <label><?php _e('Client ID', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($mp->get_setting('gateways->wepay->client_id')); ?>" size="70" name="mp[gateways][wepay][client_id]" type="text" />
                                </label>
                            </p>

                            <p>
                                <label><?php _e('Client Secret', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($mp->get_setting('gateways->wepay->client_secret')); ?>" size="70" name="mp[gateways][wepay][client_secret]" type="text" />
                                </label>
                            </p>

                            <p>
                                <label><?php _e('Access Token', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($mp->get_setting('gateways->wepay->access_token')); ?>" size="70" name="mp[gateways][wepay][access_token]" type="text" />
                                </label>
                            </p>

                            <p>
                                <label><?php _e('Account ID', 'mp') ?><br />
                                    <input value="<?php echo esc_attr($mp->get_setting('gateways->wepay->account_id')); ?>" size="70" name="mp[gateways][wepay][account_id]" type="text" />
                                </label>
                            </p>


                        </td>
                    </tr>


                </table>

                <input type="hidden" name="mp[gateways][wepay][currency]" value="USD">
            </div>
        </div>      
        <?php
    }

    /**
     * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
     *  array. Don't forget to return!
     */
		function process_gateway_settings($settings) {
			$settings['gateways']['wepay'] = array_map('trim', $settings['gateways']['wepay']);
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
        if (!isset($_SESSION['payment_method_id'])) {
            $mp->cart_checkout_error(__('The WePay Card Token was not generated correctly. Please go back and try again.', 'mp'));
            return false;
        }

        $order_id = $mp->generate_order_id();

        //Get the WePay SDK
        require $mp->plugin_dir .'plugins-gateway/wepay-files/wepay-sdk.php';


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


        try {

            // Application settings
            $account_id = $this->account_id;
            $client_id = $this->client_id;
            $client_secret = $this->client_secret;
            $access_token = $this->access_token;

            // Credit card id to charge
            $credit_card_id = $_SESSION['payment_method_id'];
						
            if ($this->mode == 'staging') {
                WePay::useStaging($this->client_id, $this->client_secret);
            } else {
                WePay::useProduction($this->client_id, $this->client_secret);
            }

            $wepay = new WePay($access_token);

            // charge the credit card
            $response = $wepay->request('checkout/create', array(
                'account_id' => $account_id,
                'amount' => number_format($total, 2, '.', ''),
                'currency' => 'USD',
                'short_description' => $order_id,
                'type' => $this->checkout_type,
                'payment_method_id' => $credit_card_id, // user's credit_card_id
                'payment_method_type' => 'credit_card'
            ));

            if (isset($response->state) && $response->state == 'authorized') {

                $credit_card_response = $wepay->request('/credit_card', array(
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'credit_card_id' => $_SESSION['payment_method_id'],
                ));

                //setup our payment details
                $payment_info = array();
                $payment_info['gateway_public_name'] = $this->public_name;
                $payment_info['gateway_private_name'] = $this->admin_name;
                $payment_info['method'] = sprintf(__('%1$s', 'mp'), $credit_card_response->credit_card_name);
                $payment_info['transaction_id'] = $order_id;
                $timestamp = time();
                $payment_info['status'][$timestamp] = __('Paid', 'mp');
                $payment_info['total'] = $total;
                $payment_info['currency'] = $this->currency;

                $order = $mp->create_order($order_id, $cart, $_SESSION['mp_shipping_info'], $payment_info, true);
                unset($_SESSION['payment_method_id']);
                $mp->set_cart_cookie(Array());
            }
        } catch (Exception $e) {
            unset($_SESSION['payment_method_id']);
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
mp_register_gateway_plugin('MP_Gateway_Wepay', 'wepay', __('WePay', 'mp'));
