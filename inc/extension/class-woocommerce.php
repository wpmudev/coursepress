<?php
/**
 * Class CoursePress_Extension_WooCommerce
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension_WooCommerce {

	public function __construct() {
		add_action( 'coursepress_course_updated', array( $this, 'course_update' ), 10, 2 );
		add_filter( 'coursepress_default_course_meta', array( $this, 'add_course_default_fields' ) );
	}

	public function add_course_default_fields( $course_meta ) {
		/**
		 * paid course defaults
		 */
		$course_meta['mp_auto_sku'] = true;
		$course_meta['mp_product_price'] = '';
		$course_meta['mp_product_sale_price'] = '';
		$course_meta['mp_sale_price_enabled'] = false;
		$course_meta['mp_sku'] = '';
		return $course_meta;
	}


	public function course_update( $course_id, $course_meta ) {
		if ( isset( $course_meta['mp_sale_price_enabled'] ) && 'on' == $course_meta['mp_sale_price_enabled'] ) {
			$course_meta['mp_sku'] = sprintf( 'CP-%d', $course_id );
			update_post_meta( $course_id, 'course_settings', $course_meta );
		}
	}
}
