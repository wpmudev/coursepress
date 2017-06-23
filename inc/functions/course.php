<?php
/**
 * Course functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */

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
	/** @var $CoursePress_Core CoursePress_Core */
	global $CoursePress_Core;

	$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );

	$args = wp_parse_args( array(
		'post_type' => $CoursePress_Core->__get( 'course_post_type' ),
		'posts_per_page' => $posts_per_page,
		'suppress_filters' => true,
		'fields' => 'ids',
	), $args );

	//$order_by = coursepress_get_setting( 'course/order_by', 'post_date' );
	//$order = coursepress_get_setting( 'course/order_by_direction', 'ASC' );

	// @todo: Apply orderby setting

	/**
	 * Filter course results.
	 *
	 * @since 3.0
	 * @param array $args
	 */
	$args = apply_filters( 'coursepress_pre_get_courses', $args );

	$results = get_posts( $args );
	$courses = array();

	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			$courses[ $result ] = coursepress_get_course( $result );
		}
	}

	return $courses;
}

/**
 * Get the course title.
 *
 * @param int $course_id Optional. If null will return course title of the current serve course.
 *
 * @return CoursePress_Course|null
 */
function coursepress_get_course_title( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->__get( 'post_title' );

	return null;
}

/**
 * Get the course summary of the given course ID.
 *
 * @param int $course_id    Optional. If omitted, will return summary of the current serve course.
 * @param int $length       Optional. The character length of the summary to return.
 *
 * @return null|string
 */
function coursepress_get_course_summary( $course_id = 0, $length = 140 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	return $course->get_summary( $length );
}

/**
 * Helper function to get course description.
 *
 * @param int $course_id
 *
 * @return null|string
 */
function coursepress_get_course_description( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	return $course->get_description();
}

/**
 * Return's course media base on set settings.
 *
 * @param int $course_id
 * @param int $width
 * @param int $height
 *
 * @return null|string
 */
function coursepress_get_course_media( $course_id = 0, $width = 235, $height = 235 ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) )
		return $course->get_media( $width, $height );

	return null;
}

/**
 * Returns course start and end date, separated by set separator.
 *
 * @param int $course_id
 * @param string $separator
 *
 * @return string|null
 */
function coursepress_get_course_availability_dates( $course_id = 0, $separator = ' - ' ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	return $course->get_course_dates( $separator );
}

/**
 * Returns course enrollment start and end date, separated by set separator.
 *
 * @param int $course_id
 * @param string $separator
 *
 * @return string|null
 */
function coursepress_get_course_enrollment_dates( $course_id = 0, $separator = ' - ' ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	return $course->get_enrollment_dates( $separator );
}

/**
 * Returns course enrollment button.
 *
 * @param int $course_id
 *
 * @return string
 */
function coursepress_get_course_enrollment_button( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	// @todo: Do

	return '';
}

/**
 * Returns course instructor links, separated by set separator.
 *
 * @param int $course_id
 * @param string $sepatator
 *
 * @return string|null
 */
function course_get_course_instructor_links( $course_id = 0, $sepatator = ' ' ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	$instructors = $course->get_instructors_link();

	if ( ! empty( $instructors ) )
		return implode( $sepatator, $instructors );

	return null;
}

/**
 * Returns course structure filter base on current user.
 *
 * @param int $course_id
 * @param bool $show_details
 *
 * @return null|string
 */
function coursepress_get_course_structure( $course_id = 0, $show_details = false ) {
	$course = coursepress_get_course( $course_id );

	if ( ! is_wp_error( $course ) ) {
		return $course->get_course_structure( $show_details );
	}

	return null;
}

/**
 * Returns CoursePress courses url.
 *
 * @return string
 */
function coursepress_get_main_courses_url() {
	$main_slug = coursepress_get_setting( 'slugs/course', 'courses' );

	return home_url( '/' ) . trailingslashit( $main_slug );
}

/**
 * Returns the course's URL structure.
 *
 * @param $course_id
 *
 * @return string
 */
function coursepress_get_course_permalink( $course_id = 0 ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	return $course->get_permalink();
}

/**
 * Get the course's submenu links.
 *
 * @return array of submenu items.
 */
function coursepress_get_course_submenu() {
	$course = coursepress_get_course(); // Submenu only works on CoursePress pages

	if ( is_wp_error( $course ) )
		return null;

	$course_id = $course->__get( 'ID' );

	$menus = array(
		'units' => array(
			'label' => __( 'Units', 'cp' ),
			'url' => coursepress_get_course_units_archive_url( $course_id ),
		),
	);

	if ( $course->__get( 'allow_discussion' ) ) {
		$menus['discussions'] = array(
			'label' => __( 'Forum', 'cp' ),
			'url' => esc_url_raw( $course->get_discussion_url() ),
		);
	}

	if ( $course->__get( 'allow_workbook' ) ) {
		$menus['workbook'] = array(
			'label' => __( 'Workbook', 'cp' ),
			'url' => esc_url_raw( $course->get_workbook_url() ),
		);
	}

	if ( $course->__get( 'allow_grades' ) ) {
		$menus['grades'] = array(
			'label' => __( 'Grades', 'cp' ),
			'url' => esc_url_raw( $course->get_grades_url() ),
		);
	}

	// Add course details link at the last
	$menus['course-details'] = array(
		'label' => __( 'Course Details', 'cp' ),
		'url' => esc_url_raw( $course->get_permalink() ),
	);

	return $menus;
}

/**
 * Returns the course's units archive link.
 *
 * @param int $course_id
 *
 * @return string|null
 */
function coursepress_get_course_units_archive_url( $course_id = 0 ) {
	$course_url = coursepress_get_course_permalink( $course_id );

	if ( ! $course_url )
		return null;

	$units_slug = coursepress_get_setting( 'slugs/units', 'units' );

	return $course_url . trailingslashit( $units_slug );
}

/**
 * Gets the current serve course cycle.
 *
 * @since 3.0
 */
function coursepress_get_current_course_cycle() {
	/**
	 * @var array $_course_module An array of current module data.
	 * @var object $_course_step
	 */
	global $CoursePress_VirtualPage, $_course_module, $_course_step;

	$course = coursepress_get_course();

	if ( is_wp_error( $course ) )
		return null;

	$unit = coursepress_get_unit();

	if ( is_wp_error( $unit ) )
		return null;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
		return null;

	$vp = $CoursePress_VirtualPage;
	$vp_type = $vp->__get( 'type' );

	if ( ! in_array( $vp_type, array( 'unit', 'module', 'step' ) ) )
		return null;

	$view_mode = $course->get_view_mode();

	$template = '';

	if ( 'focus' == $view_mode ) {
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
	} else {
		if ( 'unit' == $vp_type ) {

		}
	}

	return $template;
}

/**
 * Returns the course previous cycle.
 *
 * @param string $label
 *
 * @return null|string
 */
function coursepress_get_previous_course_cycle_link( $label = '' ) {
	global $CoursePress_VirtualPage;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
		return null;

	$course = coursepress_get_course();

	if ( is_wp_error( $course ) )
		return null;

	if ( empty( $label ) )
		$label = __( 'Previous', 'cp' );

	$vp = $CoursePress_VirtualPage;

	// @todo: Add previous link here

	return $vp->create_html(
		'a',
		array(
			'href' => '',
			'class' => 'button previous-button coursepress-previous-cycle',
		),
		$label
	);
}

/**
 * Returns the course next cycle.
 *
 * @param string $label
 *
 * @return null|string
 */
function coursepress_get_next_course_cycle_link( $label = '' ) {
	global $CoursePress_VirtualPage;

	if ( ! $CoursePress_VirtualPage instanceof CoursePress_VirtualPage )
		return null;

	$course = coursepress_get_course();

	if ( is_wp_error( $course ) )
		return null;

	if ( empty( $label ) )
		$label = __( 'Next', 'cp' );

	$vp = $CoursePress_VirtualPage;

	// @todo: Add next link here

	return $vp->create_html(
		'a',
		array(
			'href' => '',
			'class' => 'button next-button coursepress-next-cycle',
		),
		$label
	);
}