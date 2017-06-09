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

/**
 * Get course data object.
 *
 * @param int|WP_Post $course_id
 *
 * @return WP_Error|CoursePress_Course
 */
function coursepress_get_course( $course_id = 0 ) {
	global $CoursePress_Course, $CoursePress_Core;

	if ( empty( $course_id ) ) {
		// Assume current course
		if ( $CoursePress_Course instanceof CoursePress_Course ) {
			return $CoursePress_Course;
		} else {
			// Try current post
			$course_id = get_the_ID();
		}
	}

	if ( $course_id instanceof WP_Post )
		$course_id = $course_id->ID;

	if ( $CoursePress_Course instanceof CoursePress_Course
	     && $course_id == $CoursePress_Course->__get( 'ID' ) )
		return $CoursePress_Course;

	if ( isset( $CoursePress_Core->courses[ $course_id ] ) )
		return $CoursePress_Core->courses[ $course_id ];

	$course = new CoursePress_Course( $course_id );

	if ( ! $course->__get( 'is_error' ) ) {
		$CoursePress_Core->courses[ $course_id ] = $course;

		return $course;
	}

	return $course->wp_error();
}

/**
 * Returns list courses.
 *
 * @param array $args
 *
 * @return array Returns an array of courses where each course is an instance of CoursePress_Course object.
 */
function coursepress_get_courses( $args = array() ) {
	global $CoursePress_Core;

	$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );

	$args = wp_parse_args( array(
		'post_type' => $CoursePress_Core->__get( 'course_post_type' ),
		'posts_per_page' => $posts_per_page,
		'suppress_filters' => true,
		'fields' => 'ids',
	), $args );

	$results = get_posts( $args );
	$courses = array();

	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			$courses[ $result ] = coursepress_get_course( $result );
		}
	}

	return $courses;
}

//@todo: check
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
 * Get the course title.
 *
 * @param int $course_id
 *
 * @return null
 */
function coursepress_get_the_title( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_title' );

	return null;
}

/**
 * Get the course summary.
 *
 * @param int $course_id
 * @param int $length
 *
 * @return bool|null|string
 */
function coursepress_get_course_summary( $course_id = 0, $length = 140 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_excerpt' );

	return $course->get_summary( $length );
}

/**
 * Helper function to get course description.
 *
 * @param int $course_id
 *
 * @return mixed|null|void
 */
function coursepress_get_description( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_content' );

	$description = $course->__get( 'post_content' );

	return apply_filters( 'the_content', $description );
}

// @todo:
function coursepress_get_enrollment_button( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;
}

function coursepress_get_instructors_links( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	return $course->get_instructors_link();
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

function coursepress_get_unit_structure( $course_id = 0, $unit_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	$unit = coursepress_get_unit( $unit_id );

	if ( is_wp_error( $unit ) )
		return null;

	$student = coursepress_get_student();

	if ( is_wp_error( $student ) )
		return null;

	return $unit->get_unit_structure();
}

function coursepress_get_the_content() {
	global $CoursePress_VirtualPage, $_course_module_id, $_course_module, $_course_step;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
		return null;

	$vp = $CoursePress_VirtualPage;
	$vp_type = $vp->__get( 'type' );

	if ( ! in_array( $vp_type, array( 'unit', 'module', 'step' ) ) )
		return null;

	$course = coursepress_get_course();
	$unit = coursepress_get_unit();
	$course_mode = $course->__get( 'course_view' );

	$template = '';

	if ( 'focus' == $course_mode ) {
		if ( 'unit' == $vp_type ) {
			$template .= $vp->create_html(
				'div',
				array( 'class' => 'unit-description' ),
				apply_filters( 'the_content', $unit->get_description() )
			);
		} elseif ( 'module' == $vp_type && $_course_module ) {
			$template .= $vp->create_html(
				'h3',
				array(),
				$_course_module['title']
			);
			$template .= $vp->create_html(
				'div',
				array( 'class' => 'module-description' ),
				apply_filters( 'the_content', $_course_module['description'] )
			);
		} elseif ( 'step' == $vp_type ) {
			$template = $_course_step->template();
		}
	}

	return $template;
}

function coursepress_get_next_item_link() {
	global $CoursePress_VirtualPage;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
		return null;

	$vp = $CoursePress_VirtualPage;
	$vp_type = $vp->__get( 'type' );
	$course = coursepress_get_course();
}

function coursepress_get_previous_item_link() {
	global $CoursePress_VirtualPage;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
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

function coursepress_get_unit( $unit_id = 0 ) {
	global $CoursePress_Unit;

	if ( empty( $unit_id ) ) {
		// Assume current unit
		if ( $CoursePress_Unit instanceof CoursePress_Unit )
			return $CoursePress_Unit;
	} else {
		$unit = new CoursePress_Unit( $unit_id );

		if ( is_wp_error( $unit ) )
			return null;

		return $unit;
	}
}

function coursepress_get_unit_title( $unit_id = 0 ) {
	$unit = coursepress_get_unit( $unit_id );

	if ( is_wp_error( $unit ) )
		return null;

	return $unit->get_the_title();
}

function coursepress_get_unit_description( $unit_id = 0 ) {
	$unit = coursepress_get_unit( $unit_id );

	if ( is_wp_error( $unit ) )
		return null;

	return $unit->get_the_description();
}

function coursepress_breadcrumb() {
	global $CoursePress_VirtualPage;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
		return null;

	$vp = $CoursePress_VirtualPage;
	$items = $CoursePress_VirtualPage->__get( 'breadcrumb' );

	if ( ! empty( $items ) ) {
		$breadcrumb = '';

		// Make the last item non-clickable
		$last_item = array_pop( $items );

		foreach ( $items as $item ) {
			$attr = array( 'class' => 'course-item' );
			$breadcrumb .= $vp->create_html( 'li', $attr, $item );
		}

		$breadcrumb .= $vp->create_html( 'li', array( 'class' => 'current' ), wp_strip_all_tags( $last_item ) );

		echo $vp->create_html( 'ul', array( 'class' => 'course-breadcrumb' ), $breadcrumb );
	}

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
