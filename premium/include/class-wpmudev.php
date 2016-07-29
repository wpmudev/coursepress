<?php
/**
 * Premium specific module.
 *
 * @package CoursePressPro
 */

/**
 * Initialize the WP-Academy extras.
 */
class CoursePressPro_Wpmudev {

	/**
	 * Hook up the WP-Academy options!
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		if ( ! CP_IS_WPMUDEV ) { return; }

		// WP-Academy does ues the custom signup option.
		add_filter( 'coursepress_custom_signup', '__return_true' );
	}
}
