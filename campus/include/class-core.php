<?php
/**
 * CampusPress specific module.
 *
 * @package CoursePressCampus
 */

/**
 * Initialize the CampusPress extras.
 */
class CoursePressCampus_Core {

	/**
	 * Hook up the Campus options!
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		// CampusPress does uses the custom signup option.
		add_filter( 'coursepress_custom_signup', '__return_true' );
	}
}
