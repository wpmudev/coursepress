<?php
/**
 * Helper functions.
 * Integrate other plugins with CoursePress.
 *
 * Note: This file is only loaded, if the Woo-Integration is enabled in
 * CoursePress - so we do not need to check if setting 'woocommerce/use' is true.
 *
 * @package  CoursePress
 */

/**
 * Integrates WooCommerce with CoursePress.
 */
class CoursePress_Helper_Integration_WooCommerce {

	/**
	 * Initialize integration for WooCommerce checkout.
	 *
	 * @since  2.0.0
	 */
	static public function init() {
		// NOT DONE YET...
	}
}

/**
 * Template functions
 */

if ( ! function_exists( 'cp_use_woo' ) ) {
	$active_plugins = apply_filters(
		'active_plugins',
		get_option( 'active_plugins' )
	);

	if ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
		function cp_use_woo() {
			return true;
		}
	} else {
		function cp_use_woo() {
			return false;
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
