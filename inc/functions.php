<?php
/**
 * CoursePress functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */

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

function coursepress_get_courses( $args = array() ) {
	global $CoursePress_Data_Courses;

	$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );
	$args = wp_parse_args( array(
		'post_type' => $CoursePress_Data_Courses->__get( 'course_post_type' ),
		'posts_per_page' => $posts_per_page,
	), $args );

	$results = get_posts( $args );
	$courses = array();

	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			$courses[ $result->ID ] = new CoursePress_Course( $result );
		}
	}

	return $courses;
}

function coursepress_get_available_courses() {
	global $CoursePress_Data_Courses;
	$date_time_now = $CoursePress_Data_Courses->date_time_now();
	$args = array(
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'reletion' => 'AND',
				array(
					'meta_key' => 'course_start_date',
					'meta_value' => $date_time_now,
					'meta_compare' => '<=',
				),
				array(
					'meta_key' => 'course_end_date',
					'meta_value' => $date_time_now,
					'meta_compare' => '<=',
				),
			),
			array(
				'meta_key' => 'course_open_ended',
				'meta_value' => array( 1, 'on', 'yes' ),
				'meta_compare' => 'IN',
			)
		)
	);

	$courses = coursepress_get_courses( $args );

	return $courses;
}

/**
 * Get course data object.
 *
 * @param int $course_id
 *
 * @return WP_Error|CoursePress_Course
 */
function coursepress_get_course( $course_id = 0 ) {
	global $CoursePress_Course, $CoursePress_Data_Courses;

	if ( empty( $course_id ) ) {
		// Assume current course
		if ( $CoursePress_Course instanceof CoursePress_Course ) {
			return $CoursePress_Course;
		} else {
			// Try current post
			$course_id = get_the_ID();
		}
	}

	if ( $CoursePress_Course instanceof CoursePress_Course
	     && $course_id == $CoursePress_Course->__get( 'ID' ) )
			return $CoursePress_Course;

	if ( isset( $CoursePress_Data_Courses->courses[ $course_id ] ) )
		return $CoursePress_Data_Courses->courses[ $course_id ];

	$course = new CoursePress_Course( $course_id );

	if ( ! $course->__get( 'is_error' ) ) {
		$CoursePress_Data_Courses->courses[ $course_id ] = $course;

		return $course;
	}

	return $course->wp_error();
}

function coursepress_get_the_title( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_title' );

	return null;
}

function coursepress_get_course_summary( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_excerpt' );

	return null;
}

function coursepress_get_description( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_excerpt' );

	return null;
}

/**
 * Get the course structure hierarchy.
 *
 * @param int $course_id
 *
 * @return null|string
 */
function coursepress_get_course_structure( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) ) {
		return $course->get_course_structure();
	}

	return null;
}

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

	return home_url( '/' ) . trailingslashit( $main_slug ) . trailingslashit( $slug );
}

function coursepress_get_units_url( $course_id ) {
	$course_url = coursepress_get_course_url( $course_id );
	$units_slug = coursepress_get_setting( 'slugs/units', 'units' );

	return $course_url . trailingslashit( $units_slug );
}

function coursepress_breadcrumb() {
	global $CoursePress_Course;
}

/**
 * Get the course's submenu links.
 *
 * @return array of submenu items.
 */
function coursepress_get_submenus() {
	/**
	 * @var $CoursePress_Course CoursePress_Course
	 */
	global $CoursePress_Course;

	$course = $CoursePress_Course;
	$course_id = $course->__get( 'ID' );
	$course_url = $course->get_permalink();

	$menus = array(
		'units' => array(
			'label' => __( 'Units', 'cp' ),
			'url' => coursepress_get_units_url( $course_id ),
		),
	);

	if ( $course->__get( 'allow_discussion' ) ) {
		$menus['discussions'] = array(
			'label' => __( 'Forum', 'cp' ),
			'url' => $course->get_discussion_url(),
		);
	}

	if ( $course->__get( 'allow_workbook' ) ) {
		$menus['workbook'] = array(
			'label' => __( 'Workbook', 'cp' ),
			'url' => $course->get_workbook_url(),
		);
	}

	if ( $course->__get( 'allow_grades' ) ) {
		$menus['grades'] = array(
			'label' => __( 'Grades', 'cp' ),
			'url' => $course->get_grades_url(),
		);
	}

	// Add course details link at the last
	$menus['course-details'] = array(
		'label' => __( 'Course Details', 'cp' ),
		'url' => $course_url,
	);

	return $menus;
}
