<?php
/**
 * Class CoursePress_Data_Courses
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Courses extends CoursePress_Utility {

	public function __construct() {
	}

	/**
	 * Get enrollment type default.
	 *
	 * @since 2.0.0
	 *
	 * @param $integer $course_id Course ID
	 */
	public function get_enrollment_type_default( $course_id = 0 ) {
		$default = 'registered';
		if ( coursepress_users_can_register() ) {
			$default = 'anyone';
		}
		$default = coursepress_get_setting( 'course/enrollment_type_default', $default );
		return apply_filters( 'coursepress_course_enrollment_type_default', $default, $course_id );
	}

	/**
	 * get courses list
	 *
	 * @since 3.0.0
	 */
	public function get_list() {
		global $CoursePress_Core;
		$args = array(
			'post_type' => $CoursePress_Core->course_post_type,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
		);
		$list = array();
		$courses = new WP_Query( $args );
		if ( $courses->have_posts() ) {
			while ( $courses->have_posts() ) {
				$courses->the_post();
				$list[ get_the_ID() ] = get_the_title();
			}
		}
		return $list;
	}
}
