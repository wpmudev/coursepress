<?php
/*
  MarketPress Moneybookers Gateway Plugin
  Author: Aaron Edwards
 */

class MP_Gateway_Moneybookers extends MP_Gateway_API {

    //private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
    var $plugin_name = 'moneybookers';
    //name of your gateway, for the admin side.
    var $admin_name = '';
    //public name of your gateway, for lists and such.
    var $public_name = '';
    //url for an image for your checkout method. Displayed on checkout form if set
    var $method_img_url = '';
    //url for an submit button image for your checkout method. Displayed on checkout form if set
    var $method_button_img_url = '';
    //always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
    var $ipn_url;
    //whether if this is the only enabled gateway it can skip the payment_form step
    var $skip_form = true;
    //api vars
    var $API_Email, $API_Language, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $version, $currencyCode, $locale, $confirmationNote;

    /*     * **** Below are the public methods you may overwrite via a plugin ***** */

    /**
     * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
     */
    function on_creation() {
        global $mp;
        $settings = get_option('mp_settings');

        //set names here to be able to translate
        $this->admin_name = __('Moneybookers', 'mp');
        $this->public_name = __('Moneybookers', 'mp');

        $this->method_img_url = $mp->plugin_url . 'images/moneybookers.gif';
        $this->method_button_img_url = $mp->plugin_url . 'images/moneybookers-button.gif';

        if (isset($settings['gateways']['moneybookers'])) {
            $this->currencyCode = $settings['gateways']['moneybookers']['currency'];
            $this->API_Email = $settings['gateways']['moneybookers']['email'];
            $this->confirmationNote = $settings['gateways']['moneybookers']['confirmationNote'];
            $this->API_Language = $settings['gateways']['moneybookers']['language'];
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
        if (isset($_GET['moneybookers_cancel'])) {
            echo '<div class="mp_checkout_error">' . __('Your Moneybookers transaction has been canceled.', 'mp') . '</div>';
        }
    }

    /**
     * Use this to process any fields you added. Use the $_POST global,
     *  and be sure to save it to both the $_SESSION and usermeta if logged in.
     *  DO NOT save credit card details to usermeta as it's not PCI compliant.
     *  Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
     *  it will redirect to the next step.
     *
     * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
     * @param array $shipping_info. Contains shipping info and email in case you need it
     */
    function process_payment_form($cart, $shipping_info) {
        global $mp;
    }

    /**
     * Return the chosen payment details here for final confirmation. You probably don't need
     *  to post anything in the form as it should be in your $_SESSION var already.
     *
     * @param array $cart. Contains the cart contents for the current blog, global cart if $mp->global_cart is true
     * @param array $shipping_info. Contains shipping info and email in case you need it
     */
    function confirm_payment_form($cart, $shipping_info) {
        global $mp;
        //print payment details
        return '<img src="' . $this->method_img_url . '" border="0" alt="' . __('Checkout with Moneybookers', 'mp') . '">';
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
        global $mp, $current_user;

        $timestamp = time();
        $settings = get_option('mp_settings');

        $url = "https://www.moneybookers.com/app/payment.pl";

        $order_id = $mp->generate_order_id();

        $params = array();
        $params['transaction_id'] = $order_id;
        $params['pay_to_email'] = $this->API_Email;
        $params['currency'] = $this->currencyCode;
        $params['language'] = $this->API_Language;
        $params['return_url'] = mp_checkout_step_url('confirmation');
        $params['return_url_text'] = __('Complete Checkout', 'mp');
        $params['cancel_url'] = mp_checkout_step_url('checkout');
        $params['status_url'] = $this->ipn_url;
        $params['confirmation_note'] = $this->confirmationNote;

        if (isset($settings['gateways']['moneybookers']['logourl']) && !empty($settings['gateways']['moneybookers']['logourl']))
            $params['logo_url'] = $settings['gateways']['moneybookers']['logourl'];

        if (isset($settings['gateways']['moneybookers']['business-name']) && !empty($settings['gateways']['moneybookers']['business-name']))
            $params['recipient_description'] = $settings['gateways']['moneybookers']['business-name'];

        $params['pay_from_email'] = $shipping_info['email'];

        if (!$mp->download_only_cart($cart) && $mp->get_setting('shipping->method') != 'none' && isset($shipping_info['name'])) {
            $names = explode(' ', $shipping_info['name']);
            $params['firstname'] = $names[0];
            $params['lastname'] = $names[count($names) - 1]; //grab last name
            $params['address'] = $shipping_info['address1'];
            $params['phone_number'] = $shipping_info['phone'];
            $params['postal_code'] = $shipping_info['zip'];
            $params['city'] = $shipping_info['city'];
            $params['state'] = $shipping_info['state'];
        }

        $totals = array();
        $product_count = 0;
        $coupon_code = $mp->get_coupon_code();
        
        foreach ($cart as $product_id => $variations) {
            foreach ($variations as $data) {
								$price = $mp->coupon_value_product($coupon_code, $data['price'] * $data['quantity'], $product_id);
            
                //we're sending tax included prices here if tax included is on
                $totals[] = $price;
                $product_count++;
            }
        }

        $params["detail1_text"] = $order_id;
        $params["detail1_description"] = __('Order ID:', 'mp');

        $total = array_sum($totals);

        $i = 2;
        $params["amount{$i}"] = $mp->display_currency($total);
        $params["amount{$i}_description"] = sprintf(__('Cart Subtotal for %d Items:', 'mp'), $product_count);
        $i++;

				//shipping line
		    $shipping_tax = 0;
		    if ( ($shipping_price = $mp->shipping_price(false)) !== false ) {
					$total += $shipping_price;
					$shipping_tax = ($mp->shipping_tax_price($shipping_price) - $shipping_price);
					
          $params["amount{$i}"] = $mp->display_currency($shipping_price);
          $params["amount{$i}_description"] = __('Shipping & Handling:', 'mp');
          $i++;					
		    }
		
		    //tax line if tax inclusive pricing is off. It it's on it would screw up the totals
		    $tax_price = ($mp->tax_price(false) + $shipping_tax);
		    if ( ! $mp->get_setting('tax->tax_inclusive') ) {
					$total += $tax_price;
          $params["amount{$i}"] = $mp->display_currency($tax_price);
          $params["amount{$i}_description"] = __('Taxes:', 'mp');
          $i++;					
		    } else {
          $params["detail3_text"] = $mp->display_currency($tax_price);
          $params["detail3_description"] = __('Taxes:', 'mp');
          $i++;			    
		    }

        $params['amount'] = $total;

        $param_list = array();

        foreach ($params as $k => $v) {
            $param_list[] = "{$k}=" . rawurlencode($v);
        }

        $param_str = implode('&', $param_list);

        //setup transients for ipn in case checkout doesn't redirect (ipn should come within 12 hrs!)
        set_transient('mp_order_' . $order_id . '_cart', $cart, 60 * 60 * 12);
        set_transient('mp_order_' . $order_id . '_shipping', $shipping_info, 60 * 60 * 12);
        set_transient('mp_order_' . $order_id . '_userid', $current_user->ID, 60 * 60 * 12);

        wp_redirect("{$url}?{$param_str}");
        exit(0);
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
        $content = '';
        if ($order->post_status == 'order_received') {
            $content .= '<p>' . sprintf(__('Your payment via Moneybookers for this order totaling %s is in progress. Here is the latest status:', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total'])) . '</p>';
            $statuses = $order->mp_payment_info['status'];
            krsort($statuses); //sort with latest status at the top
            $status = reset($statuses);
            $timestamp = key($statuses);
            $content .= '<p><strong>' . $mp->format_date($timestamp) . ':</strong> ' . esc_html($status) . '</p>';
        } else {
            $content .= '<p>' . sprintf(__('Your payment via Moneybookers for this order totaling %s is complete. The transaction number is <strong>%s</strong>.', 'mp'), $mp->format_currency($order->mp_payment_info['currency'], $order->mp_payment_info['total']), $order->mp_payment_info['transaction_id']) . '</p>';
        }
        return $content;
    }

    /**
     * Runs before page load incase you need to run any scripts before loading the success message page
     */
    function order_confirmation($order) {
        global $mp;

        //check if not created already by IPN, and create it
        if (!$order) {
            //get totals
            $cart = $mp->get_cart_contents();
            foreach ($cart as $product_id => $variations) {
                foreach ($variations as $data) {
                    $totals[] = $mp->before_tax_price($data['price'], $product_id) * $data['quantity'];
                }
            }
            $total = array_sum($totals);

            if ($coupon = $mp->coupon_value($mp->get_coupon_code(), $total)) {
                $total = $coupon['new_total'];
            }

            //shipping line
            if (($shipping_price = $mp->shipping_price()) !== false) {
                $total = $total + $shipping_price;
            }

            //tax line
            if (($tax_price = $mp->tax_price()) !== false) {
                $total = $total + $tax_price;
            }

            $status = __('Received - The order has been received, awaiting payment confirmation.', 'mp');
            //setup our payment details
            $payment_info['gateway_public_name'] = $this->public_name;
            $payment_info['gateway_private_name'] = $this->admin_name;
            $payment_info['method'] = __('Moneybookers balance, Credit Card, or Instant Transfer', 'mp');
            $payment_info['transaction_id'] = $_SESSION['mp_order'];
            $timestamp = time();
            $payment_info['status'][$timestamp] = $status;
            $payment_info['total'] = $total;
            $payment_info['currency'] = $this->currencyCode;

            $order = $mp->create_order($_SESSION['mp_order'], $cart, $_SESSION['mp_shipping_info'], $payment_info, false);
            //if successful delete transients
            if ($order) {
                delete_transient('mp_order_' . $order_id . '_cart');
                delete_transient('mp_order_' . $order_id . '_shipping');
                delete_transient('mp_order_' . $order_id . '_userid');
            }
        } else {
            $mp->set_cart_cookie(Array());
        }
    }

    /**
     * Echo a settings meta box with whatever settings you need for you gateway.
     *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
     *  You can access saved settings via $settings array.
     */
    function gateway_settings_box($settings) {
        global $mp;
        ?>
        <div id="mp_moneybookers" class="postbox">
            <h3 class='handle'><span><?php _e('Moneybookers Settings', 'mp'); ?></span></h3>
            <div class="inside">
                <span class="description"><?php _e('Resell your inventory via Moneybookers.com.', 'mp') ?></span>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label><?php _e('Moneybookers Email', 'mp') ?></label></th>
                        <td>
                            <span class="description"><?php print sprintf(__('You must use your valid Moneybookers merchant email. <a target="_blank" href="%s">Instructions &raquo;</a>', 'mp'), "http://www.moneybookers.com/app/help.pl?s=m_paymentoptions"); ?></span><br />
                            <input value="<?php echo esc_attr($settings['gateways']['moneybookers']['email']); ?>" size="30" name="mp[gateways][moneybookers][email]" type="text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label><?php _e('Secret Word', 'mp') ?></label></th>
                        <td>
                            <span class="description"><?php print sprintf(__('The secret word must match the word submitted in the "Merchant Tools" section of your <a target="_blank" href="%s">Moneybookers account</a>.', 'mp'), "https://www.moneybookers.com/app/"); ?></span><br />
                            <input value="<?php echo esc_attr($settings['gateways']['moneybookers']['secret-word']); ?>" size="10" maxlength="10" name="mp[gateways][moneybookers][secret-word]" type="text" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Currency', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('Selecting a currency other than that used for your store may cause problems at checkout.', 'mp'); ?></span><br />
                            <select name="mp[gateways][moneybookers][currency]">
        <?php
        $sel_currency = ($settings['gateways']['moneybookers']['currency']) ? $settings['gateways']['moneybookers']['currency'] : $settings['currency'];
        $currencies = array(
            "AED" => 'AED - Utd. Arab Emir. Dirham',
            "AUD" => 'AUD - Australian Dollar',
            "BGN" => 'BGN - Bulgarian Leva',
            "CAD" => 'CAD - Canadian Dollar',
            "CHF" => 'CHF - Swiss Franc',
            "CZK" => 'CZK - Czech Koruna',
            "DKK" => 'DKK - Danish Krone',
            "EEK" => 'EEK - Estonian Kroon',
            "EUR" => 'EUR - Euro',
            "GBP" => 'GBP - British Pound',
            "HKD" => 'HKD - Hong Kong Dollar',
            "HRK" => 'HRK - Croatian Kuna',
            "HUF" => 'HUF - Hungarian Forint',
            "ILS" => 'ILS - Israeli Shekel',
            "INR" => 'INR - Indian Rupee',
            "ISK" => 'ISK - Iceland Krona',
            "JOD" => 'JOD - Jordanian Dinar',
            "JPY" => 'JPY - Japanese Yen',
            "KRW" => 'KRW - South-Korean Won',
            "LTL" => 'LTL - Lithuanian Litas',
            "LVL" => 'LVL - Latvian Lat',
            "MAD" => 'MAD - Moroccan Dirham',
            "MYR" => 'MYR - Malaysian Ringgit',
            "NZD" => 'NZD - New Zealand Dollar',
            "NOK" => 'NOK - Norwegian Krone ',
            "OMR" => 'OMR - Omani Rial',
            "PLN" => 'PLN - Polish Zloty',
            "QAR" => 'QAR - Qatari Rial',
            "RON" => 'RON - Romanian Leu New',
            "RSD" => 'RSD - Serbian dinar',
            "SAR" => 'SAR - Saudi Riyal',
            "SEK" => 'SEK - Swedish Krona',
            "SGD" => 'SGD - Singapore Dollar',
            "SKK" => 'SKK - Slovakian Koruna',
            "THB" => 'THB - Thailand Baht',
            "TND" => 'TND - Tunisian Dinar',
            "TRY" => 'TRY - New Turkish Lira',
            "TWD" => 'TWD - Taiwan Dollar',
            "USD" => 'USD - U.S. Dollar',
            "ZAR" => 'ZAR - South-African Rand'
        );

        foreach ($currencies as $k => $v) {
            echo '		<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
        }
        ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Language', 'mp') ?></th>
                        <td>
                            <select name="mp[gateways][moneybookers][language]">
        <?php
        $sel_language = ($settings['gateways']['moneybookers']['language']) ? $settings['gateways']['moneybookers']['language'] : $settings['language'];
        $languages = array(
            "CN" => 'Chinese',
            "CZ" => 'Czech',
            "DA" => 'Danish',
            "NL" => 'Dutch',
            "EN" => 'English',
            "FI" => 'Finnish',
            "FR" => 'French',
            "DE" => 'German',
            "GR" => 'Greek',
            "IT" => 'Italian',
            "PL" => 'Polish',
            "RO" => 'Romainian',
            "RU" => 'Russian',
            "ES" => 'Spanish',
            "SV" => 'Swedish',
            "TR" => 'Turkish'
        );

        foreach ($languages as $k => $v) {
            echo '		<option value="' . $k . '"' . ($k == $sel_language ? ' selected' : '') . '>' . esc_html($v) . '</option>' . "\n";
        }
        ?>
                            </select>
                        </td>
                    </tr>
                    <th scope="row"><?php _e('Merchant Name (optional)', 'mp') ?></th>
                    <td>
                        <span class="description"><?php _e('The name of this store, which will be shown on the gateway. If no value is submitted, the account email will be shown as the recipient of the payment.', 'mp') ?></span>
                        <p>
                            <input value="<?php echo esc_attr($settings['gateways']['moneybookers']['business-name']); ?>" size="30" maxlength="30" name="mp[gateways][moneybookers][business-name]" type="text" />
                        </p>
                    </td>
                    </tr>
                    <th scope="row"><?php _e('Logo Image (optional)', 'mp') ?></th>
                    <td>
                        <span class="description"><?php _e('The URL of the logo which you would like to appear at the top of the payment form. The logo must be accessible via HTTPS otherwise it will not be shown. For best integration results we recommend that you use a logo with dimensions up to 200px in width and 50px in height.', 'mp') ?></span>
                        <p>
                            <input value="<?php echo esc_attr($settings['gateways']['moneybookers']['logourl']); ?>" size="80" maxlength="240" name="mp[gateways][moneybookers][logourl]" type="text" />
                        </p>
                    </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Confirmation Note (optional)', 'mp') ?></th>
                        <td>
                            <span class="description"><?php _e('Shown to the customer on the confirmation screen - the end step of the process - a note, confirmation number, or any other message. Line breaks &lt;br&gt; may be used for longer messages.', 'mp'); ?></span><br />
                            <textarea class="mp_emails_txt" name="mp[gateways][moneybookers][confirmationNote]"><?php echo esc_textarea($settings['gateways']['moneybookers']['confirmationNote']); ?></textarea>
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
     * INS and payment return
     */
    function process_ipn_return() {
        global $mp;
        $settings = get_option('mp_settings');

        if ($_SERVER['HTTP_USER_AGENT'] != 'Moneybookers Merchant Payment Agent') {
            header('HTTP/1.0 403 Forbidden');
            exit('Invalid request');
        }

        if (isset($_POST['transaction_id'])) {
            $checksum = strtoupper(md5($_POST['merchant_id'] . $_POST['transaction_id'] . strtoupper(md5($settings['gateways']['moneybookers']['secret-word'])) . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status']));
            if ($_POST['md5sig'] != $checksum) {
                header('HTTP/1.0 403 Forbidden');
                exit('We were unable to authenticate the request');
            }

            //setup our payment details
            $payment_info['gateway_public_name'] = $this->public_name;
            $payment_info['gateway_private_name'] = $this->admin_name;
            $payment_info['method'] = isset($_POST['payment_type']) ? $_POST['payment_type'] : __('Moneybookers balance, Credit Card, or Instant Transfer', 'mp');
            $payment_info['transaction_id'] = isset($_POST['mb_transaction_id']) ? $_POST['mb_transaction_id'] : $_POST['transaction_id'];

            $timestamp = time();
            $order_id = $_POST['transaction_id'];

            //setup status
            switch ($_POST['status']) {

                case '2':
                    $status = __('Processed - The payment has been completed, and the funds have been added successfully to your Moneybookers account balance.', 'mp');
                    $create_order = true;
                    $paid = true;
                    break;

                case '0':
                    $status = __('Pending - The payment is pending. It can take 2-3 days for bank transfers to complete.', 'mp');
                    $create_order = true;
                    $paid = false;
                    break;

                case '-1':
                    $status = __('Cancelled - The payment was cancelled manually by the sender in their online account history or was auto-cancelled after 14 days pending.', 'mp');
                    $create_order = false;
                    $paid = false;
                    break;

                case '-2':
                    $status = __('Failed - The Credit Card or Direct Debit transaction was declined.', 'mp');
                    $create_order = false;
                    $paid = false;
                    break;

                case '-3':
                    $status = __('Chargeback - A payment was reversed due to a chargeback. The funds have been removed from your account balance and returned to the buyer.', 'mp');
                    $create_order = false;
                    $paid = false;
                    break;

                default:
                    // case: various error cases
                    $create_order = false;
                    $paid = false;
            }

            //status's are stored as an array with unix timestamp as key
            $payment_info['status'][$timestamp] = $status;
            $payment_info['total'] = $_POST['amount'];
            $payment_info['currency'] = $_POST['currency'];

            if ($mp->get_order($order_id)) {
                $mp->update_order_payment_status($order_id, $status, $paid);
            } else if ($create_order) {
                //succesful payment, create our order now
                $cart = get_transient('mp_order_' . $order_id . '_cart');
                $shipping_info = get_transient('mp_order_' . $order_id . '_shipping');
                $user_id = get_transient('mp_order_' . $order_id . '_userid');
                $success = $mp->create_order($order_id, $cart, $shipping_info, $payment_info, $paid, $user_id);

                //if successful delete transients
                if ($success) {
                    delete_transient('mp_order_' . $order_id . '_cart');
                    delete_transient('mp_order_' . $order_id . '_shipping');
                    delete_transient('mp_order_' . $order_id . '_userid');
                }
            }

            //if we get this far return success so ipns don't get resent
            header('HTTP/1.0 200 OK');
            exit('Successfully recieved!');
        } else {
            header('HTTP/1.0 403 Forbidden');
            exit('Invalid request');
        }
    }

}

//register payment gateway plugin
mp_register_gateway_plugin('MP_Gateway_Moneybookers', 'moneybookers', __('Skrill (Moneybookers)', 'mp'));