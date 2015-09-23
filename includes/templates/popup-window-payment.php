<?php
global $coursepress, $mp;

$course_id = 0;
if ( ! empty( $args ) ) {
	$course_id = (int) $args['course_id'];
} else {
	if ( isset( $_REQUEST['course_id'] ) ) {
		$course_id = ( int ) $_REQUEST['course_id'];
	}
}

$course     = new Course( $course_id );
$product_id = $course->mp_product_id();

$mp_settings   = get_option( 'mp_settings' );
$gateways      = ! empty( $mp_settings['gateways']['allowed'] ) ? $mp_settings['gateways']['allowed'] : false;
$gateway_count = $gateways ? count( $gateways ) : 0;

// Add course to cart
$product   = get_post( $product_id );
$quantity  = 1;
$variation = 0;

// $cart = $mp->get_cart_cookie();
$cart                              = array(); // remove all cart items
$cart[ $product_id ][ $variation ] = $quantity;
$mp->set_cart_cookie( $cart );
?>


<div class="cp_popup_title"><?php 1 == $gateway_count ? _e( 'Payment', 'cp' ) : _e( 'Payment Options', 'cp' ); ?></div>
<input type="hidden" name="signup-next-step" value="process_payment"/>
<table class="popup-payment-info">
	<tr>
		<th><?php _e( 'Course', 'cp' ); ?></th>
		<th><?php _e( 'Price', 'cp' ); ?></th>
	<tr></tr>
	<td><?php echo esc_html( $course->details->post_title ); ?></td>
	<td><?php echo do_shortcode( '[mp_product_price product_id="' . $product_id . '" label=""]' ); ?></td>
	</tr>
</table>
<hr/>

<?php
global $mp_gateway_active_plugins;
// cp_write_log( $mp_gateway_active_plugins );
// MP3 integration
foreach ( $mp_gateway_active_plugins as $gateway ) {
	?>

	<button data-course-id="<?php echo esc_attr( $course_id ); ?>" data-product-id="<?php echo esc_att( $product_id ); ?>" data-gateway="<?php echo esc_attr( $gateway->plugin_name ); ?>" name="<?php echo esc_attr( $gateway->plugin_name ); ?>-button" class="popup-payment-button"><?php echo esc_html( $gateway->public_name ); ?></button>

<?php
}
?>








