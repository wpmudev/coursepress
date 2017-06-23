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
	 * return array of allowed enrollment restrictions.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID
	 *
	 * @return string
	 */
	public function get_enrollment_types_array( $course_id = 0 ) {
		$enrollment_types = array(
			'manually' => __( 'Manually added only', 'CP_TD' ),
		);
		if ( coursepress_users_can_register() ) {
			$enrollment_types = array_merge( $enrollment_types, array(
				'anyone' => __( 'Any registered users', 'CP_TD' ),
				'passcode' => __( 'Any registered users with a pass code', 'CP_TD' ),
				'prerequisite' => __( 'Registered users who completed the prerequisite course(s)', 'CP_TD' ),
			) );
		} else {
			$enrollment_types = array_merge( $enrollment_types, array(
				'registered' => __( 'Any registered users', 'CP_TD' ),
				'passcode' => __( 'Any registered users with a pass code', 'CP_TD' ),
				'prerequisite' => __( 'Registered users who completed the prerequisite course(s)', 'CP_TD' ),
			) );
		}
		$enrollment_types = apply_filters( 'coursepress_course_enrollment_types', $enrollment_types, $course_id );
		return $enrollment_types;
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
