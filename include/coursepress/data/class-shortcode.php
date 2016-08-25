<?php
/**
 * Shortcode functions.
 *
 * @package CoursePress
 */

/**
 * Initializes CoursePress shortcodes.
 */
class CoursePress_Data_Shortcode {

	/**
	 * Load the individual shortcode modules.
	 * For better maintenance and performance the shortcodes are split into
	 * multiple files instead of having one huge file.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		CoursePress_Data_Shortcode_Course::init();
		CoursePress_Data_Shortcode_CourseTemplate::init();
		CoursePress_Data_Shortcode_Instructor::init();
		CoursePress_Data_Shortcode_Student::init();
		CoursePress_Data_Shortcode_Template::init();
		CoursePress_Data_Shortcode_Unit::init();
	}
}
