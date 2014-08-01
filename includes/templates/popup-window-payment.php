<?php
	global $coursepress;
	global $mp;
	$course_id = $args['course_id'];
	$course = new Course($course_id);
	$product_id = $course->mp_product_id();

	$mp_settings = get_option('mp_settings');
	$gateways = !empty($mp_settings['gateways']['allowed']) ? $mp_settings['gateways']['allowed'] : false;
	$gateway_count = $gateways ? count( $gateways ) : 0;
	
	// Add course to cart
	$product = get_post($product_id);
	$quantity = 1;
	$variation = 0;
	
	// $cart = $mp->get_cart_cookie();
	$cart = array(); // remove all cart items
	$cart[ $product_id ][ $variation ] = $quantity;
	$mp->set_cart_cookie( $cart );

?>


<div class="cp_popup_title"><?php 1 == $gateway_count ? _e('Payment', 'cp') : _e('Payment Options', 'cp') ; ?></div>
<input type="hidden" name="signup-next-step" value="process_payment" />
<table class="popup-payment-info">
	<tr>
		<th><?php _e( 'Course', 'cp' ); ?></th><th><?php _e( 'Price', 'cp' ); ?></th>
	<tr></tr>
		<td><?php echo $course->details->post_title; ?></td><td><?php echo do_shortcode('[mp_product_price product_id="' . $product_id . '" label=""]'); ?></td>
	</tr>
</table>
<hr />

<?php

	foreach( $gateways as $gateway ){
		
		if( in_array( $gateway, array_keys( CoursePress::$gateway ) ) ) { 
		?>

			<button data-course-id="<?php echo $course_id; ?>" data-product-id="<?php echo $product_id; ?>" data-gateway="<?php echo $gateway; ?>" name="<?php echo $gateway; ?>-button" class="popup-payment-button"><?php echo CoursePress::$gateway[ $gateway ]['friendly']; ?></button>
		
		<?php
		}
		
	}
	
?>








