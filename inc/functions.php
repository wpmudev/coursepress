<?php
/**
 * CoursePress functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */
if ( ! function_exists( 'coursepress_get_setting' ) ) :
	/**
	 * Get coursepress global setting.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function coursepress_get_setting( $key, $default = '' ) {
		$settings = coursepress_get_option( 'coursepress_settings' );

		return coursepress_get_array_val( $settings, $key, $default );
	}
endif;

if ( ! function_exists( 'coursepress_get_courses' ) ) :
	function coursepress_get_courses( $args = array() ) {
		global $CoursePress_Data_Courses;

		return $CoursePress_Data_Courses->get_courses( $args );
	}
endif;

if ( ! function_exists( 'coursepress_get_course' ) ) :
	/**
	 * Get course data object.
	 *
	 * @param int $course_id
	 *
	 * @return WP_Error|CoursePress_Course
	 */
	function coursepress_get_course( $course_id = 0 ) {
		global $CoursePress_Course;

		if ( is_null( $course_id ) || empty( $course_id ) )
			return new WP_Error( 'invalid_course_id', __( 'Invalid course ID!', 'cp' ) );

		if ( $CoursePress_Course instanceof CoursePress_Course
			&& $course_id == $CoursePress_Course->__get( 'ID' ) )
			return $CoursePress_Course;

		return new CoursePress_Course( $course_id );
	}
endif;

if ( ! function_exists( 'coursepress_get_course_url' ) ) :
	/**
	 * Returns the course's URL structure.
	 *
	 * @param $course_id
	 *
	 * @return false|string
	 */
	function coursepress_get_course_url( $course_id ) {
		$main_slug = coursepress_get_setting( 'slugs/course', 'courses' );
		$slug = get_post_field( 'post_name', $course_id );

		return home_url() . trailingslashit( $main_slug ) . trailingslashit( $slug );
	}
endif;
