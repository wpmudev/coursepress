<?php
/**
 * Helper functions.
 * Integrate other plugins with CoursePress.
 *
 * @package  CoursePress
 */

/**
 * Integrates WooCommerce with CoursePress.
 */
class CoursePress_Helper_Integration_WooCommerce {
	static public function init() {

	}
}

if ( ! function_exists( 'cp_use_woo' ) ) {
	function cp_use_woo() {
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$use_woo = get_option( 'use_woo', 0 );
			if ( ! $use_woo ) {
				return false;
			} else {
				return true;
			}
		}
	}
}

if ( ! function_exists( 'cp_redirect_woo_to_course' ) ) {
	function cp_redirect_woo_to_course() {
		$redirect_woo_to_course = get_option( 'redirect_woo_to_course', 0 );
		if ( ! $redirect_woo_to_course ) {
			return false;
		} else {
			return true;
		}
	}
}
