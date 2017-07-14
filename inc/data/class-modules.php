<?php
/**
 * Class CoursePress_Data_Modules
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Modules extends CoursePress_Utility {
	public function get_all_modules_ids_by_type( $module_type, $course_id = 0 ) {
		global $CoursePress_Core;
		$args = array(
			'post_type' => $CoursePress_Core->step_post_type,
			'fields' => 'ids',
			'suppress_filters' => true,
			'nopaging' => true,
			'meta_key' => 'module_type',
			'meta_value' => $module_type,
		);
		if ( ! empty( $course_id ) ) {
			$courses = new CoursePress_Data_Courses();
			$units = $courses->get_units( $course_id, array( 'any' ), true );
			if ( empty( $units ) ) {
				return array();
			}
			$args['post_parent__in'] = $units;
		}
		$modules = new WP_Query( $args );
		return $modules->posts;

		return $modules;
	}
}
