<?php
/**
 * Class CoursePress_Data_Core
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Core extends CoursePress_Utility {
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
	}

	function add_query_vars( $vars ) {
		$vars[] = 'unit';
		$vars[] = 'unit-archive';
		$vars[] = 'coursename';

		return $vars;
	}

	function add_rewrite_rules( $rules ) {
		$course_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		$new_rules = array();

		// Unit
		$unit_slug = coursepress_get_setting( 'slugs/units', 'units' );
		$unit = '^' . $course_slug . '/([^/]*)/' . $unit_slug . '/([^/]*)/?';
		$new_rules[ $unit ] = 'index.php?coursename=$matches[1]&unit=$matches[2]';

		$unit = '^' . $course_slug . '/([^/]*)/' . $unit_slug . '/?';
		$new_rules[ $unit ] = 'index.php?coursename=$matches[1]&unit-archive=1';


		return array_merge( $new_rules, $rules );
	}
}