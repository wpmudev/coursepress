<?php
/*
	MarketPress Bitpay Gateway
  Author: UmeshSingla
 * Handles registering and options for Bitpay integration into payment gateway
 */
global $mp;

class MP_Gateway_Bitpay extends MP_Gateway_API {

	//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
	var $plugin_name = 'bitpay';
	//name of your gateway, for the admin side.
	var $admin_name = 'Bitpay (alpha)';
	//public name of your gateway, for lists and such.
	var $public_name = '';
	//url for an image for your checkout method. Displayed on checkout form if set
	var $method_img_url = '';
	//always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
	var $ipn_url;
	//whether if this is the only enabled gateway it can skip the payment_form step
	var $skip_form = true;
	//api vars
	var $private_key = '';

  var $currency = '';
	//Transaction Speed for bitpay
	var $transactionSpeed = '';
	//Notification option for bitpay
	var $fullNotifications = '';
	//Redirection message for Bitpay
	var $redirectMessage = '';

	/**
	 * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
	 */
	function on_creation() {
		global $mp;
		$this->method_img_url    = $mp->plugin_url . 'images/bitcoin.png';
		$this->private_key       = $mp->get_setting( 'gateways->bitpay->private_key' );
		$this->public_name       = $mp->get_setting( 'gateways->bitpay->public_name', __('Bitcoin', 'mp') );
		$this->transactionSpeed  = $mp->get_setting( 'gateways->bitpay->transactionSpeed' );
		$this->fullNotifications = $mp->get_setting( 'gateways->bitpay->fullNotifications' );
		$this->redirectMessage   = $mp->get_setting( 'gateways->bitpay->redirectMessage' );
		add_action( 'wp_ajax_bitpay_update_invoice', array( $this, 'update_invoice' ) );
	}

    /**
	 * Return fields you need to add to the top of the payment screen, like your credit card info fields
	 *
	 * @param array $cart          . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function payment_form( $cart, $shipping_info ) {
		$content = '<table class="mp_cart_billing">
            <thead>
                <tr>
                    <td colspan="2">' . $this->redirectMessage . '</td>
                </tr>
            </thead>
        </table>';

		return $content;
	}

	/**
	 * Return the chosen payment details here for final confirmation. You probably don't need
	 *    to post anything in the form as it should be in your $_SESSION var already.
	 *
	 * @param array $cart          . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 *
	 * @return bool|string|void
	 */
	function confirm_payment_form( $cart, $shipping_info ) {
		global $mp;
		$content  = '';
		$content .= '<table class="mp_cart_billing">';
		$content .= '<thead><tr>';
		$content .= '<th>' . __( 'Billing Information:', 'mp' ) . '</th>';
		$content .= '<th align="right"><a href="' . mp_checkout_step_url( 'checkout' ) . '">' . __( '&laquo; Edit', 'mp' ) . '</a></th>';
		$content .= '</tr></thead>';
		$content .= '<tbody>';
		$content .= '<tr>';
		$content .= '<td align="right">' . __( 'Payment method:', 'mp' ) . '</td>';
		$content .= '<td>' . $this->public_name . '</td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td align="right" colspan="2">' . $this->redirectMessage . '</td>';
		$content .= '</tr>';
		$content .= '</tbody>';
		$content .= '</table>';
		//Generate Invoice
		$totals      = array();
		$currency    = $mp->get_setting( 'currency' );
		$redirect    = mp_checkout_step_url( 'confirmation' );
		$coupon_code = $mp->get_coupon_code();
		foreach ( $cart as $product_id => $variations ) {
			foreach ( $variations as $variation => $data ) {
				$price     = $mp->coupon_value_product( $coupon_code, $data[ 'price' ] * $data[ 'quantity' ], $product_id );
				$totals[ ] = $price;
			}
		}
		$total = array_sum( $totals );

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

		$order_id          = $mp->generate_order_id();
		$notificationURL   = $this->ipn_url;

		$fullNotifications = $this->fullNotifications == 'yes' ? true : false;
		//Create Invoice and redirect to bitpay
		$options = array(
			'apiKey' => $this->private_key,
			'transactionSpeed' => $this->transactionSpeed,
			'currency' => $currency,
			'redirectURL' => $redirect,
			'notificationURL' => $notificationURL,
			'posData' => $order_id,
			'fullNotifications' => $fullNotifications,
			'buyerName' => $shipping_info[ 'name' ],
			'buyerAddress1' => $shipping_info[ 'address1' ],
			'buyerAddress2' => $shipping_info[ 'address2' ],
			'buyerCity' => $shipping_info[ 'city' ],
			'buyerState' => $shipping_info[ 'state' ],
			'buyerZip' => $shipping_info[ 'zip' ],
			'buyerCountry' => $shipping_info[ 'country' ],
			'buyerPhone' => $shipping_info[ 'phone' ],
			'buyerEmail' => $shipping_info[ 'email' ],
		);

		foreach ( array( 'buyerName', 'buyerAddress1', 'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 'buyerCountry', 'buyerPhone', 'buyerEmail' ) as $trunc ) {
			$options[ $trunc ] = substr( $options[ $trunc ], 0, 100 ); // api specifies max 100-char len
		}
		$invoice = $this->bitpay_create_invoice( $order_id, $total, $order_id, $options );

		//Invoice response from bitpay
		$bitpay_invoice_error = $bitpay_error_messages = '';

		if ( isset( $invoice->error ) ) {
			$bitpay_invoice_error = isset( $invoice->error->message ) ? $invoice->error->message : '';
			if ( ! empty( $invoice->error->messages ) ) {
				$bitpay_error_messages = '<br /><ul class="mp-bitpsy-error">';
				foreach ( $invoice->error->messages as $error_field => $error_message ) {
					$bitpay_error_messages .= '<li>' . $error_field . ' => ' . $error_message . '</li>';
				}
				$bitpay_error_messages .= '</ul>';
			}
			$mp->cart_checkout_error( sprintf( __( 'There was an error creating invoice: %s %s Please <a href="%s">go back and try again</a>.', 'mp' ), $bitpay_invoice_error, $bitpay_error_messages, mp_checkout_step_url( 'checkout' ) ) );

			return false;
		} else {
                    
			//Invoice obtained
			$_SESSION[ 'bitpayInvoiceId' ] = $invoice->id;

			//if iframe embed is enabled, process payment in same window
			$iframe = $mp->get_setting( 'gateways->bitpay->iframe' );

			if ( $iframe == 'yes' && $invoice->url ) {
				$url = add_query_arg( array( 'view' => 'iframe' ), $invoice->url );
				$content .= '<p>' . __( 'Invoice status:', 'mp' ) . ' <span id="bitpay-invoice-status"></span></p>
                                    <iframe id="bitpay-payment" src="' . $url . '" width="800" height="200"></iframe>
                                    <script type="text/javascript">
                                        jQuery("document").ready( function(){
                                            jQuery("#mp_payment_confirm").attr("disabled", "disabled");
                                        });

                                        window.addEventListener("message", function(event){
                                            jQuery("#bitpay-invoice-status").html(event.data.status);
                                            if(event.data.status == "paid" || event.data.status == "confirmed" || event.data.status == "complete" ){
                                                jQuery("#mp_payment_confirm").removeAttr("disabled");
                                                jQuery("#mp_payment_confirm").click();
                                            }
                                        }, false);
                                    </script>';
			} else {
				//Handle everything in process payment page
			}
		}

		return $content;
	}

	/**
	 * Runs before page load incase you need to run any scripts before loading the success message page
	 */
	function order_confirmation( $order ) {
		global $mp;
    
		$private_key = $mp->get_setting( 'gateways->bitpay->private_key' );
		//Create order if Invoice status is changed to paid
		$bitpayInvoiceId = isset( $_SESSION[ 'bitpayInvoiceId' ] ) ? $_SESSION[ 'bitpayInvoiceId' ] : '';

		//if no invoice id, display error
		if ( ! $bitpayInvoiceId ) {
			$mp->cart_checkout_error( __( 'We could not verify order invoice details, please try again or contact site administrator for help', 'mp' ) );
			wp_redirect( mp_checkout_step_url( 'confirm-checkout' ) );
			exit;
		}

		//get Invoice status
		$invoice = $this->bitpay_get_invoice( $bitpayInvoiceId, $private_key );

		//Check order Id for obtained Invoice
		if ( !isset($invoice->posData) || $_SESSION[ 'mp_order' ] != $invoice->posData ) {
			$mp->cart_checkout_error( __( 'Incorrect order invoice, please contact site administrator', 'mp' ) );
			wp_redirect( mp_checkout_step_url( 'confirm-checkout' ) );
			exit;
		}

		//Check invoice status
		$status = array(
				'paid',
				'confirmed',
				'complete'
		);
		if ( in_array( $invoice->status, $status ) ) {

      //setup our payment details
			$payment_info[ 'gateway_public_name' ]  = $this->public_name;
			$payment_info[ 'gateway_private_name' ] = $this->admin_name;
			$payment_info[ 'method' ]               = $mp->get_setting( 'gateways->bitpay->admin_name' );
			$payment_info[ 'transaction_id' ]       = $invoice->id;
			$timestamp                              = time();
			$payment_info[ 'total' ]                = $invoice->price;
			$payment_info[ 'total_btc' ]            = $invoice->btcPrice;
			$payment_info[ 'currency' ]             = $invoice->currency;
                        
			if ( $invoice->status == 'complete' ) {
				$payment_info[ 'status' ][ $timestamp ] = sprintf( __( '%s - The payment request has been processed - %s', 'mp' ), $invoice->status, $invoice->status );
			} else {
				$payment_info[ 'status' ][ $timestamp ] = sprintf( __( '%s - The payment request is under process. Bitpay invoice status - %s', 'mp' ), 'pending', $invoice->status );
			}
		
			$mp_shipping_info = isset( $_SESSION['mp_shipping_info'] ) ? $_SESSION['mp_shipping_info'] : '';
			$order = $mp->create_order( $_SESSION['mp_order'], $mp->get_cart_contents(), $mp_shipping_info, $payment_info, false );
		} else {

			switch ( $invoice->status ) {
				case 'new' :
					$message = 'Payment not recieved at BitPay.';
					break;
				case 'invalid':
					$message = 'Payment not processed, a refund has been initiated.';
					break;
				case 'expired' :
					$message = 'Invoice expired, please reorder.';
					break;
				default :
					$message = 'There was an error processing payment at ' . $bitpay->public_name . ', please reorder.';
					break;
			}
			$mp->cart_checkout_error( $message );
			wp_redirect( mp_checkout_step_url( 'confirm-checkout' ) );
			exit;
		}
	}

	/**
	 * Use this to process any fields you added. Use the $_POST global,
	 * and be sure to save it to both the $_SESSION and usermeta if logged in.
	 * DO NOT save credit card details to usermeta as it's not PCI compliant.
	 * Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	 * it will redirect to the next step.
	 *
	 * @param array $cart          . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function process_payment_form( $cart, $shipping_info ) {

	}

	/**
	 * Filters the order confirmation email message body. You may want to append something to
	 *    the message. Optional
	 *
	 * Don't forget to return!
	 */
	function order_confirmation_email( $msg, $order = null ) {
		return $msg;
	}

	/**
	 * Return any html you want to show on the confirmation screen after checkout. This
	 *    should be a payment details box and message.
	 *
	 * Don't forget to return!
	 */
	function order_confirmation_msg( $content, $order ) {
		global $mp;
		
		$bitpayInvoiceId = isset( $_SESSION[ 'bitpayInvoiceId' ] ) ? $_SESSION[ 'bitpayInvoiceId' ] : '';
    $private_key = $mp->get_setting('gateways->bitpay->private_key');
		
		//get Invoice status
		$invoice = $this->bitpay_get_invoice( $bitpayInvoiceId, $private_key );
		if ( ! isset( $invoice ) || ( isset($invoice->error) && $invoice->error  ) ) {
			$content .= __( 'We are unable to fetch invoice details for the order, please ask site administratior' . ' for any help', 'mp' );
		} else {
			//Message as per invoice status
			if ( $invoice->status == 'complete' && $order->post_status == 'order_paid' ) {
				$content .= '<p>' . sprintf( __( 'We have recieved your order. Your payment for this order totaling %s is complete.', 'mp' ), $mp->format_currency( $order->mp_payment_info[ 'currency' ], $order->mp_payment_info[ 'total' ] ) ) . '</p>';
			} else {
				$content .= __( 'The order has been received, awaiting payment confirmation from BitPay.', 'mp' );
			}
		}

		return $content;
	}

	/**
	 * Setting box for Bitpay in Payments tab
	 */
	function gateway_settings_box( $settings ) {
		global $mp;
		$transactionSpeed = $mp->get_setting( 'gateways->bitpay->transactionSpeed', 'high' );
		$fullNotifications = $mp->get_setting( 'gateways->bitpay->fullNotifications', 'no' );
		$redirectMessage = $mp->get_setting( 'gateways->bitpay->redirectMessage', 'You will be redirected to <a href="http://bitpay.com" title="">bitpay.com</a>, for bitcoin payment. It is completely safe.' );
		$iframe = $mp->get_setting( 'gateways->bitpay->iframe' );
		$debugging = $mp->get_setting( 'gateways->bitpay->debugging' );
		$private_key = $mp->get_setting( 'gateways->bitpay->private_key' );
		//Transaction Speed for Bitpay, refer https://bitpay.com/downloads/bitpayApi.pdf
		$tr_speed = array(
			'high' 		=> __('High', 'mp'),
			'medium' 	=> __('Medium', 'mp'),
			'low' 		=> __('Low', 'mp'),
		);
		?>
		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Bitpay', 'mp' ) ?></span> -
			<span class="description"><?php _e( 'BitPay is a Payment Service Provider (PSP) specializing in eCommerce and B2B solutions for virtual currencies.', 'mp' ); ?></span>
			</h3>

			<div class="inside">
				<p class="description">
				<?php _e( 'You can now accept a payment from any country on Earth, with no risk of fraud. To use Bitpay, you need to signup 
									on <a href="https://bitpay.com/start" title="Bitpay signup">Bitpay</a>.<br />After completing the signup process, you
									can get api keys at <a href="https://bitpay.com/api-keys" title="API keys">Bitpay API key</a>. 
									You can read more about Bitpay at <a href="https://bitpay.com/downloads/bitpayApi.pdf" title="Bitpay documentation">Bitpay API</a>.<br />
									<b>Bitpay requires SSL(https) for payment notifications to work.</b>', 'mp' ) ?>
				</p>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Bitpay Credentials', 'mp' ) ?></th>
						<td>
							<p><label><?php _e( 'API key', 'mp' ) ?><br/>
								<input value="<?php echo esc_attr( $private_key ); ?>" size="70" name="mp[gateways][bitpay][private_key]" type="text"/>
								</label>
							</p>
						</td>
					</tr>
					<tr>
					<th scope="row"><?php _e( 'Bitpay Options', 'mp' ) ?></th>
						<td>
							<p><label><?php _e( 'Transaction Speed', 'mp' ) ?><br/>
								<select name="mp[gateways][bitpay][transactionSpeed]"><?php
									foreach ( $tr_speed as $k => $v ) {
										?>
										<option value="<?php echo $k; ?>" <?php selected( $transactionSpeed, $k, true ); ?>><?php echo $v; ?></option>
										<?php
									} ?>
								</select><br/>
								<small><?php _e( 'Speed at which the bitcoin transaction registers as "confirmed" to the store. This overrides your merchant settings on the Bitpay website.', 'mp' ); ?></small>
								</label>
							</p>
							<br/>

							<p><label>
								<input type="checkbox" name="mp[gateways][bitpay][fullNotifications]" value="yes" <?php checked( 'yes', $fullNotifications, true ) ?>/>
								<?php _e( 'Full Notification', 'mp' ) ?><br/>
								<small><?php _e( 'If enabled, you will recieve an email for each status update on payment.', 'mp' ); ?></small>
								</label>
							</p>
							<br/>

							<p><label><?php _e( 'Message', 'mp' ) ?><br/>
								<textarea rows="5" cols="50" name="mp[gateways][bitpay][redirectMessage]"><?php echo stripslashes(esc_textarea($redirectMessage)); ?></textarea><br/>
									<small><?php _e( 'Displayed on payment page.', 'mp' ); ?></small>
								</label>
							</p>
							<br/>

							<p><label>
								<input type="checkbox" name="mp[gateways][bitpay][iframe]" value="yes" <?php checked( 'yes', $iframe, true ) ?>/><?php _e( 'Embed iframe for payment', 'mp' ) ?>
								<br/>
								<small><?php _e( 'If checked, iframe will be embeded at payment page, otherwise user will be redirected to BitPay site for payment.', 'mp' ); ?></small>
								</label>
							</p><br />
							<!--Enable Debugging-->
							<p><label>
								<input type="checkbox" name="mp[gateways][bitpay][debugging]" value="yes" <?php checked( 'yes', $debugging, true ) ?>/><?php _e( 'Debug Log', 'mp' ) ?>
								<br/>
								<small><?php _e( 'If checked, response fron bitpay will be stored in log file, keep it disabled unless required', 'mp' ); ?></small>
								</label>
							</p><br />
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
	 *    array. Don't forget to return!
	 */
	function process_gateway_settings( $settings ) {
		$settings['gateways']['bitpay']['fullNotifications'] = ( ! isset($_POST['mp']['gateways']['bitpay']['fullNotifications']) ) ? '' : $_POST['mp']['gateways']['bitpay']['fullNotifications'];
		$settings['gateways']['bitpay']['iframe'] = ( ! isset($_POST['mp']['gateways']['bitpay']['iframe']) ) ? '' : $_POST['mp']['gateways']['bitpay']['iframe'];
		$settings['gateways']['bitpay']['debugging'] = ( ! isset($_POST['mp']['gateways']['bitpay']['debugging']) ) ? '' : $_POST['mp']['gateways']['bitpay']['debugging'];
		
		return $settings;
	}

	/**
	 * Use this to do the final payment. Create the order then process the payment. If
	 *    you know the payment is successful right away go ahead and change the order status
	 *    as well.
	 *    Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	 *    it will redirect to the next step.
	 *
	 * @param array $cart          . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function process_payment( $cart, $shipping_info ) {
		//if iframe is disabled, redirect user to bitpay site
		global $mp;
		$iframe_enabled = $mp->get_setting( 'gateways->bitpay->iframe' );
		$private_key = $mp->get_setting( 'gateways->bitpay->private_key' );
		// If iframe is enabled, return
		if ( $iframe_enabled == 'yes' ) {
			return;
		}

		//Bitpay Invoice id
		$bitpayInvoiceId = isset( $_SESSION['bitpayInvoiceId'] ) ? $_SESSION['bitpayInvoiceId'] : '';
		if ( !$bitpayInvoiceId ) {
			return;
		}

		//get Invoice status
		$invoice = $this->bitpay_get_invoice( $bitpayInvoiceId, $private_key );

		//Check order Id for obtained Invoice
		if ( $_SESSION['mp_order'] != $invoice->posData ) {
			$mp->cart_checkout_error( __( 'Incorrect order invoice, please contact site administrator', 'mp' ) );
			wp_redirect( mp_checkout_step_url( 'confirm-checkout' ) );
			exit;
		}

		//If order status new, redirect user to bitpay
		if ( $invoice->status == 'new' ) {
			wp_redirect( $invoice->url );
			exit;
		}
	}
	
	/**
	 * Handles all the IPN from bitpay.com and updated the order status
	 * @global type $mp
	 * @return type
	 */
	function process_ipn_return() {
		global $mp;

		if ( !$mp->get_setting( 'gateways->bitpay' ) ) {
			//Just to keep a note
			$this->bitpay_log( 'Untracked Order, due to gateway inactivation' );

			return;
		}
		$private_key = $mp->get_setting( 'gateways->bitpay->private_key' );
		$response = $this->bitpay_verify_notification( $private_key );

		if ( isset( $response['error'] ) ) {
			$this->bitpay_log( $response );
		} else {
			$orderId = $response['posData'];
			$this->update_bitpay_payment_status( $orderId, $response['status'] );
		}
	}
	
	/**
	 * Send POST request to bitpay.com api
	 * @global type $mp
	 * @param type $url
	 * @param type $apiKey
	 * @param type $post
	 * @return type Invoice
	 */
	function bitpay_request_url( $url, $apiKey, $post = false ) {
		global $mp;
		$post = $post ? json_encode( $post ) : '';
		$params = array(
			'body' => $post,
			'method' => 'POST',
			'sslverify' => false,
			'timeout' => 30,
			'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $apiKey )
			)
		);
		$response = wp_remote_post( $url, $params );

		//If Debug Log enabled
		if ( $mp->get_setting( 'gateways->bitpay->debugging' ) ) {
			$this->bitpay_log( $response );
		}
		return $response;
	}

	/**
	 * Create Invoice using Order details at bitpay.com
	 *
	 * @param type  $orderId , Unique order id generated for each order by marketpress.
	 * @param type  $price   , Cart Total, including shipping cost (if any), sent in default currency set by site administrator.
	 * @param type  $posData Contains order id to match invoice against order id while confirming the order or
	 *                       updating invoice status for order
	 * @param type  $options , ('itemDesc', 'itemCode', 'notificationEmail', 'notificationURL', 'redirectURL', 'apiKey'
	 *                       'currency', 'physical', 'fullNotifications', 'transactionSpeed', 'buyerName',
	 *                       'buyerAddress1', 'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 'buyerEmail', 'buyerPhone')
	 *                       If a given option is not provided here, the value of that option will default to what is found in bp_options.php
	 *
	 * @return $response, invoice body recieved
	 */
	function bitpay_create_invoice( $orderId, $price, $posData, $options = array() ) {

		$pos = array( 'posData' => $posData );
		$pos[ 'hash' ] = crypt( serialize( $posData ), $options[ 'apiKey' ] );
		$options[ 'posData' ] = json_encode( $pos );

		$options[ 'orderID' ] = $orderId;
		$options[ 'price' ]   = $price;

		$postOptions = array(
			'orderID', 'itemDesc', 'itemCode', 'notificationEmail', 'notificationURL', 'redirectURL', 'posData', 'price', 'currency', 'physical', 'fullNotifications', 'transactionSpeed', 'buyerName', 'buyerAddress1', 'buyerAddress2', 'buyerCity', 'buyerState', 'buyerZip', 'buyerEmail', 'buyerPhone'
		);
		foreach ( $postOptions as $o ) {
			if ( array_key_exists( $o, $options ) ) {
				$post[ $o ] = $options[ $o ];
			}
		}

		$response = $this->bitpay_request_url( 'https://bitpay.com/api/invoice/', $options['apiKey'], $post );
		$response = json_decode($response['body']);

		if ( is_wp_error( $response ) ) {
			return array( 'error' => array(
					'message' => 'Connection error'
			) );
		}

		return $response;
	}
	
	/**
	 * Verify the recieved invoice against the hash recieved in posData
	 * @param type $apiKey
	 * @return $json, Invoice body
	 */
	function bitpay_verify_notification( $apiKey = false ) {
		if ( !$apiKey ) {
			return;
		}
		$post = file_get_contents( "php://input" );
		if ( ! $post ) {
			return array( 'error' => 'No post data' );
		}
		$json = json_decode( $post, true );
		if ( is_string( $json ) ) {
			return array( 'error' => $json );
		} // error

		if ( ! array_key_exists( 'posData', $json ) ) {
			return array( 'error' => 'no posData' );
		}

		// decode posData
		$posData = json_decode( $json[ 'posData' ], true );
		if ( $posData[ 'hash' ] != crypt( serialize( $posData[ 'posData' ] ), $apiKey ) ) {
			return array( 'error' => 'authentication failed (bad hash)' );
		}
		$json[ 'posData' ] = $posData[ 'posData' ];

		return $json;
	}

	/*
	 * Get bitpay invoice using GET method
	 * @param $invoiceid, obtained from bitpay_create_invoice
	 * @param bitpay api key, default false
	 * 
	 */
	function bitpay_get_invoice( $invoiceId, $apiKey = false ) {
		if ( !$apiKey ){
			return false;
		}
		$params = array(
			'body'       => '',
			'method'     => 'GET',
			'sslverify'  => false,
			'timeout'    => 30,
			'headers'    => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode( $apiKey )
			)
		);

		$invoice = wp_remote_get( 'https://bitpay.com/api/invoice/' . $invoiceId, $params );
		$body = '';
		if ( $invoice['response']['code'] != 400 ){
			//decode posData
			$body = json_decode($invoice['body']);

			$body->posData = json_decode( $body->posData, true );
			$body->posData = $body->posData['posData'];
		}
		return $body;
	}

	/**
	 * Debug Log for Bitpay Invoices
	 *
	 * @param type $contents
	 */
	function bitpay_log( $contents ) {
		$file = plugin_dir_path( __FILE__ ) . 'bplog.txt';
		@file_put_contents( $file, date( 'm-d H:i:s' ) . ": ", FILE_APPEND );
		if ( is_array( $contents ) ) {
			@file_put_contents( $file, var_export( $contents, true ) . "\n", FILE_APPEND );
		} else {
			if ( is_object( $contents ) ) {
				@file_put_contents( $file, json_encode( $contents ) . "\n", FILE_APPEND );
			} else {
				@file_put_contents( $file, $contents . "\n", FILE_APPEND );
			}
		}
	}

	/**
	* Updates Payment Status as per Invoice status
	* @global type $mp
	* @param type $orderId
	* @param type $invoice_status
	*/
	function update_bitpay_payment_status( $orderId, $invoice_status ) {
		global $mp;
		$order = $mp->get_order($orderId);
		switch ( $invoice_status ) {
			case 'paid':
				$status = sprintf( __( '%s - The payment request is under process. Bitpay invoice status - %s', 'mp' ), 'pending', $invoice_status );
				$mp->update_order_payment_status( $orderId, $status, false );
				break;
				
			case 'confirmed':
				$status = sprintf( __( '%s - The payment request is under process. Bitpay invoice status - %s', 'mp' ), 'pending', $invoice_status );
				$mp->update_order_payment_status( $orderId, $status, false );
				break;
				
			case 'complete':
				$status = sprintf( __( '%s - The payment request has been processed - %s', 'mp' ), $invoice_status, $invoice_status );
				if ( $order->post_status != 'order_paid' ) {
					$mp->update_order_payment_status( $orderId, $status, true );
				}
				break;
				
			case 'invalid':
				$status = sprintf( __( '%s - The payment not credited in merchants bitpay account, action required. Bitpay invoice status  - %s', 'mp' ), 'error', $invoice_status );
				$mp->update_order_payment_status( $orderId, $status, false );
				break;
				
			case 'expired':
				$status = sprintf( __( '%s - The payment request expired, - %s', 'mp' ), 'cancelled', $invoice_status );
				$mp->update_order_payment_status( $orderId, $status, false );
				$mp->update_order_status( $orderId, 'closed' );
				break;
		}
	}
}

//register payment gateway plugin
mp_register_gateway_plugin( 'MP_Gateway_Bitpay', 'bitpay', __( 'Bitpay (alpha)', 'mp' ) );