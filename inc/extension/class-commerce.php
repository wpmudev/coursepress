<?php
/**
 * Class CoursePress_Extension_Commerce
 *
 * This class handle some behavior reserved for commerce plugins.
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension_Commerce {
	public function __construct() {
		add_shortcode( 'mp_product_price', array( $this, 'product_price' ) );
		add_shortcode( 'mp_buy_button', array( $this, 'product_price' ) );
	}

	public function product_price() {
		if ( current_user_can( 'activate_plugins' ) ) {
			return sprintf(
				'<div Class="message">%s</div>',
				esc_html__( 'This course is marked as "paid", but there is no e-commerce plugin installed.', 'cp' )
			);
		}
		return '';
	}
}
