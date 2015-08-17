<?php

class CoursePress_Model_Shortcodes {

	public static function init() {

		add_shortcode( 'course_instructors', array( __CLASS__, 'course_instructors' ) );
		add_shortcode( 'coursecourse_media_instructor_avatar', array( __CLASS__, 'course_instructor_avatar' ) );
		add_shortcode( 'course_instructor_avatar', array( __CLASS__, 'course_instructor_avatar' ) );
		add_shortcode( 'instructor_profile_url', array( __CLASS__, 'instructor_profile_url' ) );
		add_shortcode( 'course_details', array( __CLASS__, 'course_details' ) );
		add_shortcode( 'courses_student_dashboard', array( __CLASS__, 'courses_student_dashboard' ) );
		add_shortcode( 'courses_student_settings', array( __CLASS__, 'courses_student_settings' ) );
		add_shortcode( 'student_registration_form', array( __CLASS__, 'student_registration_form' ) );
		//add_shortcode( 'courses_urls', array( __CLASS__, 'courses_urls' ) );
		//add_shortcode( 'course_units', array( __CLASS__, 'course_units' ) );
		//add_shortcode( 'course_units_loop', array( __CLASS__, 'course_units_loop' ) );
		//add_shortcode( 'course_notifications_loop', array( __CLASS__, 'course_notifications_loop' ) );
		//add_shortcode( 'courses_loop', array( __CLASS__, 'courses_loop' ) );
		//add_shortcode( 'course_discussion_loop', array( __CLASS__, 'course_discussion_loop' ) );
		//add_shortcode( 'course_unit_single', array( __CLASS__, 'course_unit_single' ) );
		add_shortcode( 'course_unit_details', array( __CLASS__, 'course_unit_details' ) );
		add_shortcode( 'course_unit_archive_submenu', array( __CLASS__, 'course_unit_archive_submenu' ) );
		//add_shortcode( 'course_breadcrumbs', array( __CLASS__, 'course_breadcrumbs' ) );
		//add_shortcode( 'course_discussion', array( __CLASS__, 'course_discussion' ) );
		//add_shortcode( 'get_parent_course_id', array( __CLASS__, 'get_parent_course_id' ) );
		//add_shortcode( 'units_dropdown', array( __CLASS__, 'units_dropdown' ) );
		add_shortcode( 'course_list', array( __CLASS__, 'course_list' ) );
		//add_shortcode( 'course_calendar', array( __CLASS__, 'course_calendar' ) );
		add_shortcode( 'course_featured', array( __CLASS__, 'course_featured' ) );
		//add_shortcode( 'course_structure', array( __CLASS__, 'course_structure' ) );
		add_shortcode( 'module_status', array( __CLASS__, 'module_status' ) );
		//add_shortcode( 'student_workbook_table', array( __CLASS__, 'student_workbook_table' ) );
		add_shortcode( 'course', array( __CLASS__, 'course' ) );
		//// Sub-shortcodes
		add_shortcode( 'course_title', array( __CLASS__, 'course_title' ) );
		add_shortcode( 'course_link', array( __CLASS__, 'course_link' ) );
		add_shortcode( 'course_summary', array( __CLASS__, 'course_summary' ) );
		add_shortcode( 'course_description', array( __CLASS__, 'course_description' ) );
		add_shortcode( 'course_start', array( __CLASS__, 'course_start' ) );
		add_shortcode( 'course_end', array( __CLASS__, 'course_end' ) );
		add_shortcode( 'course_dates', array( __CLASS__, 'course_dates' ) );
		add_shortcode( 'course_enrollment_start', array( __CLASS__, 'course_enrollment_start' ) );
		add_shortcode( 'course_enrollment_end', array( __CLASS__, 'course_enrollment_end' ) );
		add_shortcode( 'course_enrollment_dates', array( __CLASS__, 'course_enrollment_dates' ) );
		add_shortcode( 'course_enrollment_type', array( __CLASS__, 'course_enrollment_type' ) );
		add_shortcode( 'course_class_size', array( __CLASS__, 'course_class_size' ) );
		add_shortcode( 'course_cost', array( __CLASS__, 'course_cost' ) );
		add_shortcode( 'course_language', array( __CLASS__, 'course_language' ) );
		add_shortcode( 'course_category', array( __CLASS__, 'course_category' ) );
		add_shortcode( 'course_list_image', array( __CLASS__, 'course_list_image' ) );
		add_shortcode( 'course_featured_video', array( __CLASS__, 'course_featured_video' ) );
		add_shortcode( 'course_join_button', array( __CLASS__, 'course_join_button' ) );
		add_shortcode( 'course_thumbnail', array( __CLASS__, 'course_thumbnail' ) );
		add_shortcode( 'course_media', array( __CLASS__, 'course_media' ) );
		add_shortcode( 'course_action_links', array( __CLASS__, 'course_action_links' ) );
		add_shortcode( 'course_random', array( __CLASS__, 'course_random' ) );
		add_shortcode( 'course_time_estimation', array( __CLASS__, 'course_time_estimation' ) );
		//// Course-progress
		//add_shortcode( 'course_progress', array( __CLASS__, 'course_progress' ) );
		//add_shortcode( 'course_unit_progress', array( __CLASS__, 'course_unit_progress' ) );
		//add_shortcode( 'course_mandatory_message', array( __CLASS__, 'course_mandatory_message' ) );
		//add_shortcode( 'course_unit_percent', array( __CLASS__, 'course_unit_percent' ) );
		//// Other shortcodes
		////add_shortcode( 'unit_discussion', array( __CLASS__, 'unit_discussion' ) );
		//// Page Shortcodes
		add_shortcode( 'course_signup', array( __CLASS__, 'course_signup' ) );
		//add_shortcode( 'cp_pages', array( __CLASS__, 'cp_pages' ) );

		add_shortcode( 'unit_archive_list', array( __CLASS__, 'unit_archive_list' ) );

		//
		//$GLOBALS[ 'units_breadcrumbs' ] = '';
		//
		////Messaging shortcodes
		//add_shortcode( 'messaging_submenu', array( __CLASS__, 'messaging_submenu' ) );


	}


	/**
	 *
	 * COURSE DETAILS SHORTCODES
	 * =========================
	 *
	 */

	/**
	 * Creates a [course] shortcode.
	 *
	 * This is just a wrapper shortcode for several other shortcodes.
	 *
	 * @since 1.0.0
	 */
	public static function course( $atts ) {

		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'show'            => 'summary',
			'date_format'     => get_option( 'date_format' ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'show_title'      => 'no',
		), $atts, 'course' ) );

		$course_id       = (int) $course_id;
		if( empty( $course_id ) ) {
			return '';
		}
		$show            = sanitize_text_field( $show );
		$date_format     = sanitize_text_field( $date_format );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$show_title      = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $show_title ) );

		$sections = explode( ',', $show );

		$content = '';

		foreach ( $sections as $section ) {
			$section = strtolower( $section );
			// [course_title]
			if ( 'title' == trim( $section ) && $show_title ) {
				$content .= do_shortcode( '[course_title title_tag="h3" course_id="' . $course_id . '" course_id="' . $course_id . '"]' );
			}

			// [course_summary]
			if ( 'summary' == trim( $section ) ) {
				$content .= do_shortcode( '[course_summary course_id="' . $course_id . '"]' );
			}

			// [course_description]
			if ( 'description' == trim( $section ) ) {
				$content .= do_shortcode( '[course_description course_id="' . $course_id . '"]' );
			}

			// [course_start]
			if ( 'start' == trim( $section ) ) {
				$content .= do_shortcode( '[course_start date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]' );
			}

			// [course_end]
			if ( 'end' == trim( $section ) ) {
				$content .= do_shortcode( '[course_end date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]' );
			}

			// [course_dates]
			if ( 'dates' == trim( $section ) ) {
				$content .= do_shortcode( '[course_dates date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]' );
			}

			// [course_enrollment_start]
			if ( 'enrollment_start' == trim( $section ) ) {
				$content .= do_shortcode( '[course_enrollment_start date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]' );
			}

			// [course_enrollment_end]
			if ( 'enrollment_end' == trim( $section ) ) {
				$content .= do_shortcode( '[course_enrollment_end date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]' );
			}

			// [course_enrollment_dates]
			if ( 'enrollment_dates' == trim( $section ) ) {
				$content .= do_shortcode( '[course_enrollment_dates date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]' );
			}

			// [course_summary]
			if ( 'class_size' == trim( $section ) ) {
				$content .= do_shortcode( '[course_class_size course_id="' . $course_id . '"]' );
			}

			// [course_cost]
			if ( 'cost' == trim( $section ) ) {
				$content .= do_shortcode( '[course_cost course_id="' . $course_id . '"]' );
			}

			// [course_language]
			if ( 'language' == trim( $section ) ) {
				$content .= do_shortcode( '[course_language course_id="' . $course_id . '"]' );
			}

			// [course_category]
			if ( 'category' == trim( $section ) ) {
				$content .= do_shortcode( '[course_category course_id="' . $course_id . '"]' );
			}

			// [course_enrollment_type]
			if ( 'enrollment_type' == trim( $section ) ) {
				$content .= do_shortcode( '[course_enrollment_type course_id="' . $course_id . '"]' );
			}

			// [course_instructors]
			if ( 'instructors' == trim( $section ) ) {
				$content .= do_shortcode( '[course_instructors course_id="' . $course_id . '"]' );
			}

			// [course_list_image]
			if ( 'image' == trim( $section ) ) {
				$content .= do_shortcode( '[course_list_image course_id="' . $course_id . '"]' );
			}

			// [course_featured_video]
			if ( 'video' == trim( $section ) ) {
				$content .= do_shortcode( '[course_featured_video course_id="' . $course_id . '"]' );
			}

			// [course_join_button]
			if ( 'button' == trim( $section ) ) {
				$content .= do_shortcode( '[course_join_button course_id="' . $course_id . '"]' );
			}

			// [course_thumbnail]
			if ( 'thumbnail' == trim( $section ) ) {
				$content .= do_shortcode( '[course_thumbnail course_id="' . $course_id . '"]' );
			}

			// [course_action_links]
			if ( 'action_links' == trim( $section ) ) {
				$content .= do_shortcode( '[course_action_links course_id="' . $course_id . '"]' );
			}

			// [course_media]
			if ( 'media' == trim( $section ) ) {
				$content .= do_shortcode( '[course_media course_id="' . $course_id . '"]' );
			}

			// [course_calendar]
			if ( 'calendar' == trim( $section ) ) {
				$content .= do_shortcode( '[course_calendar course_id="' . $course_id . '"]' );
			}
		}

		return $content;
	}

	/**
	 * Shows the course title.
	 *
	 * @since 1.0.0
	 */
	public static function course_title( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'title_tag' => 'h3',
			'link'      => 'no',
			'class'     => '',
		), $atts, 'course_title' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$title_tag = sanitize_html_class( $title_tag );
		$link      = sanitize_html_class( $link );
		$class     = sanitize_html_class( $class );

		$title = get_the_title( $course_id );

		$content = ! empty( $title_tag ) ? '<' . $title_tag . ' class="course-title course-title-' . $course_id . ' ' . $class . '">' : '';
		$content .= 'yes' == $link ? '<a href="' . get_permalink( $course_id ) . '" title="' . $title . '">' : '';
		$content .= $title;
		$content .= 'yes' == $link ? '</a>' : '';
		$content .= ! empty( $title_tag ) ? '</' . $title_tag . '>' : '';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course title with a link to the course.
	 *
	 * @since 1.0.0
	 */
	public static function course_link( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'element'   => 'span',
			'class'     => 'course-link',
		), $atts, 'course_link' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$element = sanitize_html_class( $element );
		$class   = sanitize_html_class( $class );

		$title = get_the_title( $course_id );

		$content = do_shortcode( '[course_title course_id="' . $course_id . '" title_tag="' . $element . '" link="yes" class="' . $class . '"]' );

		return $content;
	}

	/**
	 * Shows the course summary/excerpt.
	 *
	 * @since 1.0.0
	 */
	public static function course_summary( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class'     => '',
			'length'    => ''
		), $atts, 'course_summary' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$class  = sanitize_html_class( $class );
		$length = (int) $length;
		$length = empty( $length ) ? '' : $length;
		$course = get_post( $course_id );

		$content = '<div class="course-summary course-summary-' . $course_id . ' ' . $class . '">';

		if ( is_numeric( $length ) ) {
			$content .= CoursePress_Helper_Utility::truncateHtml( do_shortcode( $course->post_excerpt ), $length );
		} else {
			$content .= do_shortcode( $course->post_excerpt );
		}

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course description.
	 *
	 * @since 1.0.0
	 */
	public static function course_description( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class'     => '',
		), $atts, 'course_description' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$class  = sanitize_html_class( $class );
		$course = get_post( $course_id );

		$content = '<div class="course-description course-description-' . $course_id . ' ' . $class . '">';
		$content .= do_shortcode( $course->post_content );
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course start date.
	 *
	 * @since 1.0.0
	 */
	public static function course_start( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'date_format'     => get_option( 'date_format' ),
			'label'           => __( 'Course Start Date: ', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'class'           => '',
		), $atts, 'course_start' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format     = sanitize_text_field( $date_format );
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$class           = sanitize_html_class( $class );

		$start_date = CoursePress_Model_Course::get_setting( $course_id, 'course_start_date' );

		$content = '<div class="course-start-date course-start-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= str_replace( ' ', '&nbsp;', date_i18n( $date_format, strtotime( $start_date ) ) );
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course end date.
	 *
	 * If the course has no end date, the no_date_text will be displayed instead of the date.
	 *
	 * @since 1.0.0
	 */
	public static function course_end( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'date_format'     => get_option( 'date_format' ),
			'label'           => __( 'Course End Date: ', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'no_date_text'    => __( 'No End Date', CoursePress::TD ),
			'class'           => '',
		), $atts, 'course_end' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format     = sanitize_text_field( $date_format );
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text    = sanitize_text_field( $no_date_text );
		$class           = sanitize_html_class( $class );

		$end_date   = CoursePress_Model_Course::get_setting( $course_id, 'course_end_date' );
		$open_ended = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'course_open_ended', false ) );


		$content = '<div class="course-end-date course-end-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}
		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, strtotime( $end_date ) ) );
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course start and end date.
	 *
	 * If the course has no end date, the no_date_text will be displayed instead of the date.
	 *
	 * @since 1.0.0
	 */
	public static function course_dates( $atts ) {
		extract( shortcode_atts( array(
			'course_id'        => CoursePress_Helper_Utility::the_course( true ),
			'date_format'      => get_option( 'date_format' ),
			'label'            => __( 'Course Dates: ', CoursePress::TD ),
			'label_tag'        => 'strong',
			'label_delimeter'  => ':',
			'no_date_text'     => __( 'No End Date', CoursePress::TD ),
			'alt_display_text' => __( 'Open-ended', CoursePress::TD ),
			'show_alt_display' => 'no',
			'class'            => '',
		), $atts, 'course_dates' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format      = sanitize_text_field( $date_format );
		$label            = sanitize_text_field( $label );
		$label_tag        = sanitize_html_class( $label_tag );
		$label_delimeter  = sanitize_html_class( $label_delimeter );
		$no_date_text     = sanitize_text_field( $no_date_text );
		$alt_display_text = sanitize_text_field( $alt_display_text );
		$show_alt_display = sanitize_html_class( $show_alt_display );
		$class            = sanitize_html_class( $class );


		$start_date = CoursePress_Model_Course::get_setting( $course_id, 'course_start_date' );
		$end_date   = CoursePress_Model_Course::get_setting( $course_id, 'course_end_date' );
		$open_ended = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'course_open_ended', false ) );

		$end_output       = $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', ( date_i18n( $date_format, strtotime( $end_date ) ) ) );
		$show_alt_display = CoursePress_Helper_Utility::fix_bool( $show_alt_display );

		$content = '
			<div class="course-dates course-dates-' . esc_attr( $course_id ) . ' ' . esc_attr( $class ) . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '>';
		}
		$content .= ' ';
		if ( $show_alt_display && $open_ended ) {
			$content .= $alt_display_text;
		} else {
			$content .= str_replace( ' ', '&nbsp;', date_i18n( $date_format, strtotime( $start_date ) ) ) . ' - ' . $end_output;
		}

		$content .= '
			</div>
		';


		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the enrollment start date.
	 *
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 */
	public static function course_enrollment_start( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'date_format'     => get_option( 'date_format' ),
			'label'           => __( 'Enrollment Start Date: ', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'no_date_text'    => __( 'Enroll Anytime', CoursePress::TD ),
			'class'           => '',
		), $atts, 'course_enrollment_start' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format     = sanitize_text_field( $date_format );
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text    = sanitize_text_field( $no_date_text );
		$class           = sanitize_html_class( $class );

		$start_date = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_start_date' );
		$open_ended = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'enrollment_open_ended', false ) );

		$content = '<div class="enrollment-start-date enrollment-start-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, strtotime( $start_date ) ) );

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the enrollment end date.
	 *
	 * By default this will not show for open ended enrollments.
	 * Set show_all_dates="yes" to make it display.
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 */
	public static function course_enrollment_end( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'date_format'     => get_option( 'date_format' ),
			'label'           => __( 'Enrollment End Date: ', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'no_date_text'    => __( 'Enroll Anytime ', CoursePress::TD ),
			'show_all_dates'  => 'no',
			'class'           => '',
		), $atts, 'course_enrollment_end' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format     = sanitize_text_field( $date_format );
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text    = sanitize_text_field( $no_date_text );
		$show_all_dates  = sanitize_html_class( $show_all_dates );
		$class           = sanitize_html_class( $class );

		$end_date   = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_end_date' );
		$open_ended = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'enrollment_open_ended', false ) );

		$content = '<div class="enrollment-end-date enrollment-end-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, strtotime( $end_date ) ) );

		$content .= '</div>';

		if ( ! $open_ended || 'yes' == $show_all_dates ) {
			return $content;
		} else {
			return '';
		}

	}

	/**
	 * Shows the enrollment start and end date.
	 *
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 */
	public static function course_enrollment_dates( $atts ) {
		extract( shortcode_atts( array(
			'course_id'             => CoursePress_Helper_Utility::the_course( true ),
			'date_format'           => get_option( 'date_format' ),
			'label'                 => __( 'Enrollment Dates: ', CoursePress::TD ),
			'label_enrolled'        => __( 'You Enrolled on: ', CoursePress::TD ),
			'show_enrolled_display' => 'yes',
			'label_tag'             => 'strong',
			'label_delimeter'       => ':',
			'no_date_text'          => __( 'Enroll Anytime', CoursePress::TD ),
			'alt_display_text'      => __( 'Open-ended', CoursePress::TD ),
			'show_alt_display'      => 'no',
			'class'                 => '',
		), $atts, 'course_enrollment_dates' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format           = sanitize_text_field( $date_format );
		$label                 = sanitize_text_field( $label );
		$label_enrolled        = sanitize_text_field( $label_enrolled );
		$show_enrolled_display = sanitize_html_class( $show_enrolled_display );
		$label_tag             = sanitize_html_class( $label_tag );
		$label_delimeter       = sanitize_html_class( $label_delimeter );
		$no_date_text          = sanitize_text_field( $no_date_text );
		$alt_display_text      = sanitize_text_field( $alt_display_text );
		$show_alt_display      = sanitize_text_field( $show_alt_display );
		$class                 = sanitize_html_class( $class );

		$class = sanitize_html_class( $class );

		$start_date       = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_start_date' );
		$end_date         = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_end_date' );
		$open_ended       = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'enrollment_open_ended', false ) );
		$show_alt_display = CoursePress_Helper_Utility::fix_bool( $show_alt_display );

		$is_enrolled = false;

		if ( 'yes' == strtolower( $show_enrolled_display ) ) {
			$enrollment_date = CoursePress_Model_Course::student_enrolled( get_current_user_id(), $course_id );
			if ( ! empty( $enrollment_date ) ) {
				$enrollment_date = date_i18n( $date_format, strtotime( $enrollment_date ) );
				$label           = $label_enrolled;
			}
		}

		$content = '<div class="enrollment-dates enrollment-dates-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		if ( empty( $enrollment_date ) ) {
			if ( $show_alt_display && $open_ended ) {
				$content .= $alt_display_text;
			} else {
				$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, strtotime( $start_date ) ) ) . ' - ' . cp_sp2nbsp( date_i18n( $date_format, strtotime( $end_date ) ) );
			}
		} else {
			// User is enrolled
			echo $enrollment_date;
		}

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows a friendly course enrollment type message.
	 *
	 * @since 1.0.0
	 */
	public static function course_enrollment_type( $atts ) {
		extract( shortcode_atts( array(
			'course_id'         => CoursePress_Helper_Utility::the_course( true ),
			'label'             => __( 'Who can Enroll: ', CoursePress::TD ),
			'label_tag'         => 'strong',
			'label_delimeter'   => ':',
			'manual_text'       => __( 'Students are added by instructors.', CoursePress::TD ),
			'prerequisite_text' => __( 'Students need to complete "%s" first.', CoursePress::TD ),
			'passcode_text'     => __( 'A passcode is required to enroll.', CoursePress::TD ),
			'anyone_text'       => __( 'Anyone', CoursePress::TD ),
			'registered_text'   => __( 'Registered users', CoursePress::TD ),
			'class'             => '',
		), $atts, 'course_enrollment_type' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$label             = sanitize_text_field( $label );
		$label_tag         = sanitize_html_class( $label_tag );
		$label_delimeter   = sanitize_html_class( $label_delimeter );
		$manual_text       = sanitize_text_field( $manual_text );
		$prerequisite_text = sanitize_text_field( $prerequisite_text );
		$passcode_text     = sanitize_text_field( $passcode_text );
		$anyone_text       = sanitize_text_field( $anyone_text );
		$registered_text   = sanitize_text_field( $registered_text );
		$class             = sanitize_html_class( $class );


		$enrollment_type = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_type' );

		$enrollment_text = '';

		switch ( $enrollment_type ) {
			case 'anyone':
				$enrollment_text = $anyone_text;
				break;
			case 'registered':
				$enrollment_text = $registered_text;
				break;
			case 'passcode':
				$enrollment_text = $passcode_text;
				break;
			case 'prerequisite':
				$prereq          = get_post_meta( $course_id, 'prerequisite', true );
				$pretitle        = '<a href="' . get_permalink( $prereq ) . '">' . get_the_title( $prereq ) . '</a>';
				$enrollment_text = sprintf( $prerequisite_text, $pretitle );
				break;
			case 'manually':
				$enrollment_text = $manual_text;
				break;
		}

		// For non-standard enrolment types.
		$enrollment_text = apply_filters( 'coursepress_course_enrollment_type_text', $enrollment_text );

		$content = '<div class="course-enrollment-type course-enrollment-type-' . $course_id . ' ' . $class . '">';
		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $enrollment_text;

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course class size.
	 *
	 * If there is no limit set on the course nothing will be displayed.
	 * You can make the no_limit_text display by setting show_no_limit="yes".
	 *
	 * By default it will show the remaining places,
	 * turn this off by setting show_remaining="no".
	 *
	 * @since 1.0.0
	 */
	public static function course_class_size( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'show_no_limit'   => 'no',
			'show_remaining'  => 'yes',
			'label'           => __( 'Class Size: ', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'no_limit_text'   => __( 'Unlimited', CoursePress::TD ),
			'remaining_text'  => __( '(%d places left)', CoursePress::TD ),
			'class'           => '',
		), $atts, 'course_class_size' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$show_no_limit   = sanitize_html_class( $show_no_limit );
		$show_remaining  = sanitize_html_class( $show_remaining );
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_limit_text   = sanitize_text_field( $no_limit_text );
		$remaining_text  = sanitize_text_field( $remaining_text );
		$class           = sanitize_html_class( $class );

		$content = '';

		$is_limited     = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'class_limited', false ) );
		$class_size     = (int) CoursePress_Model_Course::get_setting( $course_id, 'class_size' );
		$show_no_limit  = CoursePress_Helper_Utility::fix_bool( $show_no_limit );
		$show_remaining = CoursePress_Helper_Utility::fix_bool( $show_remaining );

		if ( $is_limited ) {
			$content .= '<span class="total">' . $class_size . '</span>';

			if ( $show_remaining ) {
				$remaining = $class_size - (int) CoursePress_Model_Course::count_students( $course_id );
				$content .= ' <span class="remaining">' . sprintf( $remaining_text, $remaining ) . '</span>';
			}
		} else {
			if ( $show_no_limit ) {
				$content .= $no_limit_text;
			}
		}

		if ( ! empty( $content ) ) {
			$display_content = $content;

			$content = '<div class="course-class-size course-class-size-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
			}
			$content .= $display_content;
			$content .= '</div>';
		}

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course cost.
	 *
	 * @since 1.0.0
	 */
	public static function course_cost( $atts ) {
		global $coursepress;

		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'label'           => __( 'Price:&nbsp;', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ': ',
			'no_cost_text'    => __( 'FREE', CoursePress::TD ),
			'show_icon'       => 'false',
			'class'           => '',
		), $atts, 'course_cost' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_cost_text    = sanitize_text_field( $no_cost_text );
		$show_icon       = sanitize_text_field( $show_icon );
		$class           = sanitize_html_class( $class );

		$show_icon = CoursePress_Helper_Utility::fix_bool( $show_icon );
		$is_paid   = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'payment_paid_course', false ) );

		$content = '';

		if ( $is_paid ) {

			// ADD WOO INTEGRATION
			$content .= apply_filters( 'coursepress_shortcode_course_cost', '', $course_id );
		} else {
			if ( $show_icon ) {
				$content .= '<span class="product_price">' . $no_cost_text . '</span>';
			} else {
				$content .= $no_cost_text;
			}
		}


		//if ( cp_use_woo() ) {
		//	if ( $is_paid ) {
		//
		//		$woo_product = get_post_meta( $course_id, 'woo_product', true );
		//		$wc_product  = new WC_Product( $woo_product );
		//
		//		$content .= $wc_product->get_price_html();
		//	} else {
		//		if ( $show_icon ) {
		//			$content .= '<span class="mp_product_price">' . $no_cost_text . '</span>';
		//		} else {
		//			$content .= $no_cost_text;
		//		}
		//	}
		//} else {
		//	if ( $is_paid && CoursePress::instance()->marketpress_active ) {
		//
		//		$mp_product = get_post_meta( $course_id, 'marketpress_product', true );
		//
		//		$content .= do_shortcode( '[mp_product_price product_id="' . $mp_product . '" label=""]' );
		//	} else {
		//		if ( $show_icon ) {
		//			$content .= '<span class="mp_product_price">' . $no_cost_text . '</span>';
		//		} else {
		//			$content .= $no_cost_text;
		//		}
		//	}
		//}

		if ( ! empty( $content ) ) {
			$display_content = $content;

			$content = '<div class="course-cost course-cost-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
			}
			$content .= $display_content;
			$content .= '</div>';
		}

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course language.
	 *
	 * @since 1.0.0
	 */
	public static function course_language( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'label'           => __( 'Course Language: ', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ':',
			'class'           => '',
		), $atts, 'course_language' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$class           = sanitize_html_class( $class );

		$language = CoursePress_Model_Course::get_setting( $course_id, 'course_language', '' );


		if ( ! empty( $language ) ) {
			$content = '<div class="course-language course-language-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
			}

			$content .= $language;


			$content .= '</div>';

			return $content;
		} else {
			return '';
		}

	}

	/**
	 * Shows the course category.
	 *
	 * @since 1.0.0
	 */
	public static function course_category( $atts ) {
		extract( shortcode_atts( array(
			'course_id'        => CoursePress_Helper_Utility::the_course( true ),
			'label'            => __( 'Course Category: ', CoursePress::TD ),
			'label_tag'        => 'strong',
			'label_delimeter'  => ':',
			'no_category_test' => __( 'None', CoursePress::TD ),
			'class'            => '',
		), $atts, 'course_category' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$label            = sanitize_text_field( $label );
		$label_tag        = sanitize_html_class( $label_tag );
		$label_delimeter  = sanitize_html_class( $label_delimeter );
		$no_category_test = sanitize_text_field( $no_category_test );
		$class            = sanitize_html_class( $class );


		$content = '';


		$categories = CoursePress_Model_Course::get_course_categories( $course_id );
		$counter    = 0;
		foreach ( $categories as $key => $category ) {
			$counter += 1;
			$content .= $category;
			$content .= count( $categories ) > $counter ? ', ' : '';
		}

		if ( empty( $categories ) ) {
			$content .= $no_category_text;
		}

		$display_content = $content;

		$content = '<div class="course-category course-category-' . $course_id . ' ' . $class . '">';
		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $display_content;

		$content .= '</div>';

		return $content;
	}


	/**
	 * Shows the course list image.
	 *
	 * @since 1.0.0
	 */
	public static function course_list_image( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'width'     => 'default',
			'height'    => 'default',
			'class'     => '',
		), $atts, 'course_list_image' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$width  = sanitize_html_class( $width );
		$height = sanitize_html_class( $height );
		$class  = sanitize_html_class( $class );

		// Saves some overhead by not loading the post again if we don't need to.
		$course = get_post( $course_id );

		$image_src = CoursePress_Model_Course::get_setting( $course_id, 'listing_image', '' );

		if ( ! empty( $image_src ) ) {
			list( $img_w, $img_h ) = getimagesize( $image_src );

			// Note: by using both it usually reverts to the width
			$width  = 'default' == $width ? $img_w : $width;
			$height = 'default' == $height ? $img_h : $height;

			$content = '<div class="course-list-image course-list-image-' . $course_id . ' ' . $class . '">';

			$content .= '<img width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" src="' . esc_url( $image_src ) . '" alt="' . esc_attr( $course->post_title ) . '" title="' . esc_attr( $course->post_title ) . '"/>';

			$content .= '</div>';

			return $content;
		}

		return '';
	}

	/**
	 * Shows the course featured video.
	 *
	 * @since 1.0.0
	 */
	public static function course_featured_video( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'width'     => 'default',
			'height'    => 'default',
			'class'     => '',
		), $atts, 'course_featured_video' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$width  = sanitize_text_field( $width );
		$height = sanitize_html_class( $height );
		$class  = sanitize_html_class( $class );

		$video_src       = CoursePress_Model_Course::get_setting( $course_id, 'featured_video', '' );
		$video_extension = pathinfo( $video_src, PATHINFO_EXTENSION );

		$content = '';

		if ( ! empty( $video_extension ) ) { //it's file, most likely on the server
			$attr = array(
				'src' => $video_src,
			);

			if ( 'default' != $width ) {
				$attr['width'] = $width;
			}

			if ( 'default' != $height ) {
				$attr['height'] = $height;
			}

			$content .= wp_video_shortcode( $attr );
		} else {

			$embed_args = array();

			if ( 'default' != $width ) {
				$embed_args['width'] = $width;
			}

			if ( 'default' != $height ) {
				$embed_args['height'] = $height;
			}

			$post_type = get_post_type();
			$is_course = ! empty( $post_type ) && 'course' === $post_type;
			if ( $is_course && preg_match( '/(youtube.com|youtu.be)/', $video_src ) ) {

				if ( preg_match( '/youtube.com/', $video_src ) ) {
					$videoID = preg_replace( '/.*v\=/', '', $video_src );
				} else {
					$videoID = preg_replace( '/.*youtu.be\//', '', $video_src );
				}

				$video = wp_oembed_get( $video_src, $embed_args );
				if ( 'default' == $width ) {
					preg_match( '/width="(\d*)"/', $video, $match );
					$embed_args['width'] = $match[1];
				}

				if ( 'default' == $height ) {
					preg_match( '/height="(\d*)"/', $video, $match );
					$embed_args['height'] = $match[1];
				}

				$video = '<div data-video="' . $videoID . '" id="feature-video-div" data-width="' . $embed_args['width'] . '" data-height="' . $embed_args['height'] . '"></div>';

			} else {
				$video = wp_oembed_get( $video_src, $embed_args );
			}

			$content .= $video;
		}

		$video_content = $content;


		$content = '<div class="course-featured-video course-featured-video-' . $course_id . ' ' . $class . '">';
		$content .= $video_content;
		$content .= '</div>';

		return $content;
	}

	/**
	 * Shows the course join button.
	 *
	 * @since 1.0.0
	 */
	public static function course_join_button( $atts ) {
		global $coursepress;
		extract( shortcode_atts( array(
			'course_id'                => CoursePress_Helper_Utility::the_course( true ),
			'course_full_text'         => __( 'Course Full', CoursePress::TD ),
			'course_expired_text'      => __( 'Not available', CoursePress::TD ),
			'enrollment_finished_text' => __( 'Enrollments Finished', CoursePress::TD ),
			'enrollment_closed_text'   => __( 'Enrollments Closed', CoursePress::TD ),
			'enroll_text'              => __( 'Enroll now', CoursePress::TD ),
			'signup_text'              => __( 'Signup!', CoursePress::TD ),
			'details_text'             => __( 'Details', CoursePress::TD ),
			'prerequisite_text'        => __( 'Pre-requisite Required', CoursePress::TD ),
			'passcode_text'            => __( 'Passcode Required', CoursePress::TD ),
			'not_started_text'         => __( 'Not Available', CoursePress::TD ),
			'access_text'              => __( 'Start Learning', CoursePress::TD ),
			'continue_learning_text'   => __( 'Continue Learning', CoursePress::TD ),
			'instructor_text'          => __( 'Access Course', CoursePress::TD ),
			'list_page'                => false,
			'class'                    => '',
		), $atts, 'course_join_button' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$list_page = sanitize_text_field( $list_page );
		$list_page = CoursePress_Helper_Utility::fix_bool( $list_page );
		$class     = sanitize_html_class( $class );

		global $enrollment_process_url, $signup_url;


		$course = get_post( $course_id );

		$course->enroll_type           = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_type' );
		$course->course_start_date     = CoursePress_Model_Course::get_setting( $course_id, 'course_start_date' );
		$course->course_end_date       = CoursePress_Model_Course::get_setting( $course_id, 'course_end_date' );
		$course->enrollment_start_date = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_start_date' );
		$course->enrollment_end_date   = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_end_date' );
		$course->open_ended_course     = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'course_open_ended' ) );
		$course->open_ended_enrollment = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'enrollment_open_ended' ) );
		$course->prerequisite          = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_prerequisite' );
		$course->is_paid               = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'payment_paid_course' ) );
		$course->course_started        = strtotime( $course->course_start_date ) <= current_time( 'timestamp', 0 ) ? true : false;
		$course->enrollment_started    = strtotime( $course->enrollment_start_date ) <= current_time( 'timestamp', 0 ) ? true : false;
		$course->course_expired        = strtotime( $course->course_end_date ) < current_time( 'timestamp', 0 ) ? true : false;
		$course->enrollment_expired    = strtotime( $course->enrollment_end_date ) < current_time( 'timestamp', 0 ) ? true : false;
		$course->full                  = CoursePress_Model_Course::is_full( $course_id );


		$button        = '';
		$button_option = '';
		$button_url    = $enrollment_process_url;
		$is_form       = false;

		$student_enrolled = false;
		$student_id       = false;
		$is_instructor    = false;
		if ( is_user_logged_in() ) {
			$student_id       = get_current_user_id();
			$student_enrolled = CoursePress_Model_Course::student_enrolled( $student_id, $course_id );
			$is_instructor    = CoursePress_Model_Instructor::is_assigned_to_course( $course_id, $student_id );
		}

		$is_single = CoursePress_Helper_Utility::$is_singular;

		$buttons = apply_filters( 'coursepress_course_enrollment_button_options', array(
			'full'                => array(
				'label' => sanitize_text_field( $course_full_text ),
				'attr'  => array(
					'class' => 'apply-button apply-button-full ' . $class,
				),
				'type'  => 'label',
			),
			'expired'             => array(
				'label' => sanitize_text_field( $course_expired_text ),
				'attr'  => array(
					'class' => 'apply-button apply-button-finished ' . $class,
				),
				'type'  => 'label',
			),
			'enrollment_finished' => array(
				'label' => sanitize_text_field( $enrollment_finished_text ),
				'attr'  => array(
					'class' => 'apply-button apply-button-enrollment-finished ' . $class,
				),
				'type'  => 'label',
			),
			'enrollment_closed'   => array(
				'label' => sanitize_text_field( $enrollment_closed_text ),
				'attr'  => array(
					'class' => 'apply-button apply-button-enrollment-closed ' . $class,
				),
				'type'  => 'label',
			),
			'enroll'              => array(
				'label' => sanitize_text_field( $enroll_text ),
				'attr'  => array(
					'class'          => 'apply-button enroll ' . $class,
					'data-link-old'  => esc_url( $signup_url . '?course_id=' . $course_id ),
					'data-course-id' => $course_id,
				),
				'type'  => 'form_button',
			),
			'signup'              => array(
				'label' => sanitize_text_field( $signup_text ),
				'attr'  => array(
					'class'          => 'apply-button signup ' . $class,
					'data-link-old'  => esc_url( $signup_url . '?course_id=' . $course_id ),
					'data-course-id' => $course_id,
				),
				'type'  => 'form_button',
			),
			'details'             => array(
				'label' => sanitize_text_field( $details_text ),
				'attr'  => array(
					'class'     => 'apply-button apply-button-details ' . $class,
					'data-link' => esc_url( get_permalink( $course_id ) ),
				),
				'type'  => 'button',
			),
			'prerequisite'        => array(
				'label' => sanitize_text_field( $prerequisite_text ),
				'attr'  => array(
					'class' => 'apply-button apply-button-prerequisite ' . $class,
				),
				'type'  => 'label',
			),
			'passcode'            => array(
				'label'      => sanitize_text_field( $passcode_text ),
				'button_pre' => '<div class="passcode-box"><label>' . esc_html( $passcode_text ) . ' <input type="password" name="passcode" /></label></div>',
				'attr'       => array(
					'class' => 'apply-button apply-button-passcode ' . $class,
				),
				'type'       => 'form_submit',
			),
			'not_started'         => array(
				'label' => sanitize_text_field( $not_started_text ),
				'attr'  => array(
					'class' => 'apply-button apply-button-not-started  ' . $class,
				),
				'type'  => 'label',
			),
			'access'              => array(
				'label' => ! $is_instructor ? sanitize_text_field( $access_text ) : sanitize_text_field( $instructor_text ) ,
				'attr'  => array(
					'class'     => 'apply-button apply-button-enrolled apply-button-first-time ' . $class,
					'data-link' => esc_url( trailingslashit( get_permalink( $course_id ) ) . trailingslashit( CoursePress_Core::get_setting( 'slugs/units' ) ) ),
				),
				'type'  => 'button',
			),
			'continue'            => array(
				'label' => ! $is_instructor ? sanitize_text_field( $continue_learning_text ) : sanitize_text_field( $instructor_text ) ,
				'attr'  => array(
					'class'     => 'apply-button apply-button-enrolled ' . $class,
					'data-link' => esc_url( trailingslashit( get_permalink( $course_id ) ) . trailingslashit( CoursePress_Core::get_setting( 'slugs/units' ) ) ),
				),
				'type'  => 'button',
			),
		) );

		// Determine the button option
		if ( ! $student_enrolled && ! $is_instructor ) {

			// For vistors and non-enrolled students
			if ( $course->full ) {
				// COURSE FULL
				$button_option = 'full';
			} elseif ( $course->course_expired && ! $course->open_ended_course ) {
				// COURSE EXPIRED
				$button_option = 'expired';
			} elseif ( ! $course->enrollment_started && ! $course->open_ended_enrollment && ! $course->enrollment_expired ) {
				// ENROLMENTS NOT STARTED (CLOSED)
				$button_option = 'enrollment_closed';
			} elseif ( $course->enrollment_expired && ! $course->open_ended_enrollment ) {
				// ENROLMENTS FINISHED
				$button_option = 'enrollment_finished';
			} elseif ( 'prerequisite' == $course->enroll_type ) {
				// PREREQUISITE REQUIRED
				if ( ! empty( $student_id ) ) {
					$pre_course   = ! empty( $course->prerequisite ) ? $course->prerequisite : false;
					$enrolled_pre = false;

					$prerequisites = maybe_unserialize( $pre_course );
					$prerequisites = empty( $prerequisites ) ? array() : $prerequisites;

					$completed    = 0;
					$all_complete = false;

					foreach ( $prerequisites as $prerequisite ) {
						if ( CoursePress_Model_Course::student_enrolled( $student_id, $prerequisite ) && CoursePress_Model_Course::student_completed( $student_id, $course_id ) ) {
							$completed += 1;
						}
					}
					$all_complete = $completed === count( $prerequisites );

					if ( $all_complete ) {
						$button_option = 'enroll';
					} else {
						$button_option = 'prerequisite';
					}
				} else {
					$button_option = 'prerequisite';
				}
			}

			$user_can_register = CoursePress_Helper_Utility::users_can_register();

			if ( empty( $student_id ) && $user_can_register && empty( $button_option ) ) {

				// If the user is allowed to signup, let them sign up
				$button_option = 'signup';
			} elseif ( ! empty( $student_id ) && empty( $button_option ) ) {

				// If the user is not enrolled, then see if they can enroll
				switch ( $course->enroll_type ) {
					case 'anyone':
					case 'registered':
						$button_option = 'enroll';
						break;
					case 'passcode':
						$button_option = 'passcode';
						break;
					case 'prerequisite':
						$pre_course   = ! empty( $course->prerequisite ) ? $course->prerequisite : false;
						$enrolled_pre = false;

						$prerequisites = maybe_unserialize( $pre_course );
						$prerequisites = empty( $prerequisites ) ? array() : $prerequisites;

						$completed    = 0;
						$all_complete = false;

						foreach ( $prerequisites as $prerequisite ) {
							if ( CoursePress_Model_Course::student_enrolled( $student_id, $prerequisite ) && CoursePress_Model_Course::student_completed( $student_id, $course_id ) ) {
								$completed += 1;
							}
						}
						$all_complete = $completed === count( $prerequisites );

						if ( $all_complete ) {
							//							if ( !empty( $pre_course ) && $pre_course->is_course_complete() ) {
							$button_option = 'enroll';
						} else {
							$button_option = 'prerequisite';
						}

						break;
				}
			}
		} else {

			global $wp_query;

			// For already enrolled students.

			// COMPLETION LOGIX
			//$progress = Student_Completion::calculate_course_completion( get_current_user_id(), $course_id, false );
			$progress = 0;

			if ( $course->course_expired && ! $course->open_ended_course ) {
				// COURSE EXPIRED
				$button_option = 'expired';
			} elseif ( ! $course->course_started && ! $course->open_ended_course ) {
				// COURSE HASN'T STARTED
				$button_option = 'not_started';
			} elseif ( ! $is_single && false === strpos( $_SERVER['REQUEST_URI'], CoursePress_Core::get_setting( 'slugs/student_dashboard' ) ) ) {
				// SHOW DETAILS | Dashboard
				$button_option = 'details';
			} else {
				if ( 0 < $progress ) {
					$button_option = 'continue';
				} else {
					$button_option = 'access';
				}
			}
		}

		// Make the option extendable
		$button_option = apply_filters( 'coursepress_course_enrollment_button_option', $button_option );

		// Prepare the button
		if ( ( ! $is_single && ! is_page() ) || $list_page ) {
			$button_url = get_permalink( $course_id );
			$button     = '<button data-link="' . esc_url( $button_url ) . '" class="apply-button apply-button-details ' . esc_attr( $class ) . '">' . esc_html( $details_text ) . '</button>';
		} else {
			//$button = apply_filters( 'coursepress_enroll_button_content', '', $course );
			if ( empty( $button_option ) || ( 'manually' == $course->enroll_type && ! ( 'access' == $button_option || 'continue' == $button_option ) ) ) {
				return apply_filters( 'coursepress_enroll_button', $button, $course_id, $student_id );
			}

			$button_attributes = '';
			foreach ( $buttons[ $button_option ]['attr'] as $key => $value ) {
				$button_attributes .= $key . '="' . esc_attr( $value ) . '" ';
			}
			$button_pre  = isset( $buttons[ $button_option ]['button_pre'] ) ? $buttons[ $button_option ]['button_pre'] : '';
			$button_post = isset( $buttons[ $button_option ]['button_post'] ) ? $buttons[ $button_option ]['button_post'] : '';

			switch ( $buttons[ $button_option ]['type'] ) {
				case 'label':
					$button = '<span ' . $button_attributes . '>' . esc_html( $buttons[ $button_option ]['label'] ) . '</span>';
					break;
				case 'form_button':
					$button  = '<button ' . $button_attributes . '>' . esc_html( $buttons[ $button_option ]['label'] ) . '</button>';
					$is_form = true;
					break;
				case 'form_submit':
					$button  = '<input type="submit" ' . $button_attributes . ' value="' . esc_attr( $buttons[ $button_option ]['label'] ) . '" />';
					$is_form = true;
					break;
				case 'button':
					$button = '<button ' . $button_attributes . '>' . esc_html( $buttons[ $button_option ]['label'] ) . '</button>';
					break;
			}

			$button = $button_pre . $button . $button_post;
		}

		// Wrap button in form if needed
		if ( $is_form ) {
			$button = '<form name="enrollment-process" method="post" action="' . $button_url . '">' . $button;
			$button .= wp_nonce_field( 'enrollment_process' );
			$button .= '<input type="hidden" name="course_id" value="' . $course_id . '" />';
			$button .= '</form>';
		}

		return apply_filters( 'coursepress_enroll_button', $button, $course_id, $student_id );
	}

	/**
	 * Shows the course thumbnail.
	 *
	 * @since 1.0.0
	 */
	public static function course_thumbnail( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'wrapper'   => 'figure',
			'class'     => '',
		), $atts, 'course_thumbnail' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$wrapper = sanitize_html_class( $wrapper );
		$class   = sanitize_html_class( $class );

		return do_shortcode( '[course_media course_id="' . $course_id . '" wrapper="' . $wrapper . '" class="' . $class . '" type="thumbnail"]' );
	}


	/**
	 * Shows the course media (video or image).
	 *
	 * @since 1.0.0
	 */
	public static function course_media( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'type'      => '', // default, video, image
			'priority'  => '', // gives priority to video (or image)
			'list_page' => 'no',
			'class'     => '',
			'wrapper'   => '',
		), $atts, 'course_media' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$type      = sanitize_text_field( $type );
		$priority  = sanitize_text_field( $priority );
		$list_page = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $list_page ) );
		$class     = sanitize_html_class( $class );
		$wrapper   = sanitize_html_class( $wrapper );


		if ( ! $list_page ) {
			$type     = empty( $type ) ? CoursePress_Core::get_setting( 'course/details_media_type', 'default' ) : $type;
			$priority = empty( $priority ) ? CoursePress_Core::get_setting( 'course/details_media_priority', 'video' ) : $priority;
		} else {
			$type     = empty( $type ) ? CoursePress_Core::get_setting( 'course/listing_media_type', 'default' ) : $type;
			$priority = empty( $priority ) ? CoursePress_Core::get_setting( 'course/listing_media_priority', 'image' ) : $priority;
		}

		$priority = 'default' != $type ? false : $priority;

		// Saves some overhead by not loading the post again if we don't need to.
		$class = sanitize_html_class( $class );

		$course_video = CoursePress_Model_Course::get_setting( $course_id, 'featured_video' );
		$course_image = CoursePress_Model_Course::get_setting( $course_id, 'listing_image' );

		$content = '';

		if ( 'thumbnail' == $type ) {
			$type     = "image";
			$priority = "image";
		}


		if ( ( ( 'default' == $type && 'video' == $priority ) || 'video' == $type || ( 'default' == $type && 'image' == $priority && empty( $course_image ) ) ) && ! empty( $course_video ) ) {

			$content = '<div class="video_player course-featured-media course-featured-media-' . $course_id . ' ' . $class . '">';

			$content .= ! empty( $wrapper ) ? '<' . $wrapper . '>' : '';

			$video_extension = pathinfo( $course_video, PATHINFO_EXTENSION );

			if ( ! empty( $video_extension ) ) {
				$attr = array(
					'src' => $course_video,
				);
				$content .= wp_video_shortcode( $attr );
			} else {
				$embed_args = array();
				$content .= wp_oembed_get( $course_video, $embed_args );
			}

			$content .= ! empty( $wrapper ) ? '</' . $wrapper . '>' : '';

			$content .= '</div>';
		}

		if ( ( ( 'default' == $type && 'image' == $priority ) || 'image' == $type || ( 'default' == $type && 'video' == $priority && empty( $course_video ) ) ) && ! empty( $course_image ) ) {

			$content .= '<div class="course-thumbnail course-featured-media course-featured-media-' . $course_id . ' ' . $class . '">';
			$content .= ! empty( $wrapper ) ? '<' . $wrapper . '>' : '';

			$content .= '<img src="' . esc_url( $course_image ) . '" class="course-media-img"></img>';

			$content .= ! empty( $wrapper ) ? '</' . $wrapper . '>' : '';
			$content .= '</div>';
		}

		return $content;
	}


	/**
	 * Shows the course action links.
	 *
	 * @since 1.0.0
	 */
	public static function course_action_links( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class'     => '',
		), $atts, 'course_action_links' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$class = sanitize_html_class( $class );

		$course_start_date = CoursePress_Model_Course::get_setting( $course_id, 'course_start_date' );
		$course_end_date   = CoursePress_Model_Course::get_setting( $course_id, 'course_end_date' );
		$open_ended_course = CoursePress_Helper_Utility::fix_bool( CoursePress_Model_Course::get_setting( $course_id, 'course_open_ended' ) );

		$withdraw_link_visible = false;

		$content = '';

		$student_id = get_current_user_id();

		if ( ! empty( $student_id ) && CoursePress_Model_Course::student_enrolled( $student_id, $course_id ) ) {
			if ( ( ( strtotime( $course_start_date ) <= current_time( 'timestamp', 0 ) && strtotime( $course_end_date ) >= current_time( 'timestamp', 0 ) ) || ( strtotime( $course_end_date ) >= current_time( 'timestamp', 0 ) ) ) || $open_ended_course ) {
				//course is currently active or is not yet active ( will be active in the future )
				$withdraw_link_visible = true;
			}
		}

		$content = '<div class="apply-links course-action-links course-action-links-' . $course_id . ' ' . $class . '">';

		if ( $withdraw_link_visible === true ) {
			$content .= '<a href="' . wp_nonce_url( '?withdraw=' . $course_id, 'withdraw_from_course_' . $course_id, 'course_nonce' ) . '" onClick="return withdraw();">' . esc_html__( 'Withdraw', CoursePress::TD ) . '</a> | ';
		}
		$content .= '<a href="' . get_permalink( $course_id ) . '">' . esc_html__( 'Course Details', CoursePress::TD ) . '</a>';

		// Add certificate link
		if ( CoursePress_Model_Capabilities::is_pro() ) {

			// COMPLETION LOGIC
			//$content .= CP_Basic_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', CoursePress::TD ), ' | ' );
		}

		$content .= '</div>';

		return $content;
	}

	public static function course_random( $atts ) {

		extract( shortcode_atts( array(
			'number'         => 3,
			'featured_title' => 'default',
			'button_title'   => 'default',
			'media_type'     => 'default',
			'media_priority' => 'default',
			'course_class'   => 'default',
			'class'          => '',
		), $atts, 'course_random' ) );

		$number         = (int) $number;
		$featured_title = sanitize_text_field( $featured_title );
		$button_title   = sanitize_text_field( $button_title );
		$media_type     = sanitize_html_class( $media_type );
		$media_priority = sanitize_html_class( $media_priority );
		$course_class   = sanitize_html_class( $course_class );
		$class          = sanitize_html_class( $class );

		$args = array(
			'post_type'      => 'course',
			'posts_per_page' => $number,
			'orderby'        => 'rand',
			'fields'         => 'ids',
		);

		$courses = new WP_Query( $args );
		$courses = $courses->posts;
		$class   = sanitize_html_class( $class );

		$content = 0 < count( $courses ) ? '<div class="course-random ' . $class . '">' : '';

		$featured_atts = '';

		if ( 'default' != $featured_title ) {
			$featured_atts .= 'featured_title="' . $featured_title . '" ';
		}
		if ( 'default' != $button_title ) {
			$featured_atts .= 'button_title="' . $button_title . '" ';
		}
		if ( 'default' != $media_type ) {
			$featured_atts .= 'media_type="' . $media_type . '" ';
		}
		if ( 'default' != $media_priority ) {
			$featured_atts .= 'media_priority="' . $media_priority . '" ';
		}
		if ( 'default' != $course_class ) {
			$featured_atts .= 'class="' . $course_class . '" ';
		}

		foreach ( $courses as $course ) {
			$content .= '<div class="course-item course-item-' . $course . '">';
			$content .= do_shortcode( '[course_featured course_id="' . $course . '" ' . $featured_atts . ']' );
			$content .= '</div>';
		}

		$content .= 0 < count( $courses ) ? '</div>' : '';

		return $content;
	}

	/**
	 * Shows the estimated course time.
	 *
	 * @since 1.0.0
	 */
	public static function course_time_estimation( $atts ) {
		$content = '';

		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'label'           => __( 'Estimated Duration:&nbsp;', CoursePress::TD ),
			'label_tag'       => 'strong',
			'label_delimeter' => ': ',
			'wrapper'         => 'no',
			'class'           => '',
		), $atts, 'course_time_estimation' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}

		$label           = sanitize_text_field( $label );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$class           = sanitize_html_class( $class );
		$wrapper         = CoursePress_Helper_Utility::fix_bool( sanitize_text_field( $wrapper ) );

		if ( $wrapper ) {
			$content .= '<div class="course-time-estimate course-time-estimate-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . $label_tag . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . $label_tag . '>';
			}
		}

		$content .= CoursePress_Model_Course::get_time_estimation( $course_id );

		if ( $wrapper ) {
			$content .= '</div>';
		}


		return $content;
	}

	/**
	 * Shows the course structure.
	 *
	 * @since 1.0.0
	 */
	public static function course_structure( $atts ) {
		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'free_text'       => __( 'Free', CoursePress::TD ),
			'free_show'       => 'true',
			'show_title'      => 'no',
			'show_label'      => 'no',
			'label_delimeter' => ': ',
			'label_tag'       => 'h2',
			'show_divider'    => 'yes',
			'label'           => __( 'Course Structure', CoursePress::TD ),
			'class'           => '',
		), $atts, 'course_structure' ) );

		$course_id       = (int) $course_id;
		$free_text       = sanitize_text_field( $free_text );
		$free_show       = sanitize_text_field( $free_show );
		$free_show       = 'true' == $free_show ? true : false;
		$show_title      = sanitize_html_class( $show_title );
		$show_label      = sanitize_html_class( $show_label );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$label_tag       = sanitize_html_class( $label_tag );
		$show_divider    = sanitize_html_class( $show_divider );
		$label           = sanitize_text_field( $label );
		$class           = sanitize_html_class( $class );

		// Saves some overhead by not loading the post again if we don't need to.
		$course          = empty( $course ) ? new Course( $course_id ) : object_decode( $course, 'Course' );
		$class           = sanitize_html_class( $class );
		$label_tag       = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );

		if ( $course->details->course_structure_options == 'on' ) {
			$content = '';

			$student          = new Student( get_current_user_id() );
			$existing_student = $student->has_access_to_course( $course_id );

			$show_unit    = $course->details->show_unit_boxes;
			$preview_unit = $course->details->preview_unit_boxes;

			$show_page    = $course->details->show_page_boxes;
			$preview_page = $course->details->preview_page_boxes;

			$current_time      = date( 'Y-m-d', current_time( 'timestamp', 0 ) );
			$course_start_date = $course->details->course_start_date;

			$enable_links = false;
			if ( $current_time >= $course_start_date ) {
				$enable_links = true;
			}

			$units = $course->get_units();

			$content .= '<div class="course-structure-block course-structure-block-' . $course_id . '">';

			if ( ! empty( $label ) ) {
				$content .= '<' . $label_tag . ' class="label">' . $label . $label_delimeter . '</' . $label_tag . '>';
			}

			//$content .= 'yes' == $show_title ? '<label>' . $this->details->post_title . '</label>' : '';

			if ( $units ) {
				ob_start();
				?>
				<ul class="tree">
						<li>
							<ul>
								<?php
								foreach ( $units as $unit ) {
									$unit_class = new Unit( $unit->ID );

									$unit_pagination = cp_unit_uses_new_pagination( $unit->ID );

									if ( $unit_pagination ) {
										$unit_pages = coursepress_unit_pages( $unit->ID, $unit_pagination );
									} else {
										$unit_pages = coursepress_unit_pages( $unit->ID );
									}

									//$unit_pages	 = $unit_class->get_number_of_unit_pages();
									//									$modules = Unit_Module::get_modules( $unit->ID );
									$unit_permalink = Unit::get_permalink( $unit->ID );
									if ( isset( $show_unit[ $unit->ID ] ) && $show_unit[ $unit->ID ] == 'on' && $unit->post_status == 'publish' ) {
										?>
										<li>
											<label for="unit_<?php echo $unit->ID; ?>" class="course_structure_unit_label <?php echo $existing_student ? 'single_column' : ''; ?>">
												<?php
												$title = '';
												if ( $existing_student && $enable_links ) {
													$title = '<a href="' . $unit_permalink . '">' . $unit->post_title . '</a>';
												} else {
													$title = $unit->post_title;
												}
												?>
												<div class="tree-unit-left"><?php echo $title; ?></div>
												<div class="tree-unit-right">

													<?php if ( $course->details->course_structure_time_display == 'on' ) { ?>
														<span><?php echo $unit_class->get_unit_time_estimation( $unit->ID ); ?></span>
													<?php } ?>

													<?php
													if ( isset( $preview_unit[ $unit->ID ] ) && $preview_unit[ $unit->ID ] == 'on' && $unit_permalink && ! $existing_student ) {
														?>
														<a href="<?php echo $unit_permalink; ?>?try" class="preview_option"><?php echo $free_text; ?></a>
													<?php } ?>
												</div>
											</label>

											<ul>
												<?php
												for ( $i = 1; $i <= $unit_pages; $i ++ ) {
													if ( isset( $show_page[ $unit->ID . '_' . $i ] ) && $show_page[ $unit->ID . '_' . $i ] == 'on' ) {
														?>
														<li class="course_structure_page_li <?php echo $existing_student ? 'single_column' : ''; ?>">
															<?php
															$pages_num  = 1;
															$page_title = $unit_class->get_unit_page_name( $i );
															?>

															<label for="page_<?php echo $unit->ID . '_' . $i; ?>">
																<?php
																$title = '';
																if ( $existing_student && $enable_links ) {
																	$p_title = isset( $page_title ) && $page_title !== '' ? $page_title : __( 'Untitled Page', CoursePress::TD );
																	$title   = '<a href="' . trailingslashit( $unit_permalink ) . trailingslashit( 'page' ) . trailingslashit( $i ) . '">' . $p_title . '</a>';
																} else {
																	$title = isset( $page_title ) && $page_title !== '' ? $page_title : __( 'Untitled Page', CoursePress::TD );
																}
																?>

																<div class="tree-page-left">
																	<?php echo $title; ?>
																</div>
																<div class="tree-page-right">

																	<?php if ( $course->details->course_structure_time_display == 'on' ) { ?>
																		<span><?php echo $unit_class->get_unit_page_time_estimation( $unit->ID, $i ); ?></span>
																	<?php } ?>

																	<?php
																	if ( isset( $preview_page[ $unit->ID . '_' . $i ] ) && $preview_page[ $unit->ID . '_' . $i ] == 'on' && $unit_permalink && ! $existing_student ) {
																		?>
																		<a href="<?php echo $unit_permalink; ?>page/<?php echo $i; ?>?try" class="preview_option"><?php echo $free_text; ?></a>
																	<?php } ?>

																</div>
															</label>

															<?php ?>
														</li>
													<?php
													}
												}//page visible
												?>

											</ul>
										</li>
									<?php
									}//unit visible
								} // foreach
								?>
							</ul>
						</li>
					</ul>

				<?php if ( $show_divider == 'yes' ) { ?>
					<div class="divider"></div>
				<?php } ?>

				<?php
				$content .= trim( ob_get_clean() );
			} else {

			}

			$content .= '</div>';

			return $content;
		}
	}


	/**
	 * Gets the Unit archive as a list
	 *
	 * @since 2.0.0
	 */
	public static function unit_archive_list( $atts ) {

		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
		), $atts, 'unit_archive_list' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}

		$student_id = get_current_user_id();

		$content = '';

		$unit_status = current_user_can( 'manage_options' ) ? array( 'publish', 'draft' ) : array( 'publish' );
		$units = CoursePress_Model_Course::get_units( CoursePress_Helper_Utility::the_course( true ), $unit_status );

		$content .= '<div class="unit-archive-list-wrapper>';

		$content .= count( $units ) > 0 ? '<ul class="units-archive-list">' : '';

		$counter = 0;

		$student_progress = CoursePress_Model_Student::get_completion_data( $student_id, $course_id );

		foreach( $units as $unit ) {


			// COMPLETION LOGIC
			//$unit_progress = do_shortcode( '[course_unit_percent course_id="' . $course_id . '" unit_id="' . $unit_id . '" format="true" style="extended"]' );


			if( $counter == 0 ) {
				$previous_unit = false;
			} else {
				$previous_unit = $units[ $counter - 1 ];
			}
			$counter += 1;

			$additional_class	 = '';
			$additional_li_class = '';

			$unit_id = $unit->ID;
			$is_unit_available = CoursePress_Model_Unit::is_unit_available( $unit, $previous_unit );

			if ( !$is_unit_available ) {
				$additional_class	 = 'locked-unit';
				$additional_li_class = 'li-locked-unit';
			}




			$unit_progress = '';

			$post_name = empty( $unit->post_name ) ? $unit->ID : $unit->post_name;
			$content .= '
				<li class="' . esc_attr( $additional_li_class ) . '">
					<div class="' . esc_attr( $additional_class ) . '"></div>
					<div class="unit-archive-single">
						' . do_shortcode( '[course_unit_details field="percent" format="true" style="extended"]' ) . '
						' . $unit_progress . '
						<a class="unit-archive-single-title" href="' . esc_url_raw( get_permalink( CoursePress_Helper_Utility::the_course( true ) ) . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . $post_name ) . '" rel="bookmark">'. $unit->post_title . ' ' . ( $unit->post_status !== 'publish' && current_user_can( 'manage_options' ) ? esc_html__( ' [DRAFT]', CoursePress::TD ) : '') . '</a>
						' . do_shortcode( '[module_status format="true" unit_id="' . $unit_id . '"]' ) . '
					</div>
				</li>
			';

		}

		$content .= count( $units ) > 0 ? '</ul>' : '';

		$content .= empty( $units ) || count( $units ) === 0 ? '<h3 class="zero-course-units">' . esc_html__( "0 units in the course currently. Please check back later." ) . '</h3>' : '';

		$content .= '</div>';

		return $content;

	}


	/**
	 * Shows a featured course.
	 *
	 * @since 1.0.0
	 */
	public static function course_featured( $atts ) {
		extract( shortcode_atts( array(
			'course_id'      => '',
			'featured_title' => __( 'Featured Course', CoursePress::TD ),
			'button_title'   => __( 'Find out more.', CoursePress::TD ),
			'media_type'     => '', // video, image, thumbnail
			'media_priority' => 'video', // video, image
			'class'          => '',
		), $atts, 'course_featured' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$featured_title = sanitize_text_field( $featured_title );
		$button_title   = sanitize_text_field( $button_title );
		$media_type     = sanitize_text_field( $media_type );
		$media_priority = sanitize_text_field( $media_priority );
		$class          = sanitize_html_class( $class );

		$course = get_post( $course_id );
		$class  = sanitize_html_class( $class );

		$content = '<div class="featured-course featured-course-' . $course_id . '">';

		if ( ! empty( $featured_title ) ) {
			$content .= '<h2>' . $featured_title . '</h2>';
		}

		$content .= '<h3 class="featured-course-title">' . $course->post_title . '</h3>';
		$content .= do_shortcode( '[course_media type="' . $media_type . '" priority="' . $media_priority . '" course_id="' . $course_id . '"]' );

		$content .= '<div class="featured-course-summary">';
		$content .= do_shortcode( '[course_summary course_id="' . $course_id . '" length="30"]' );
		$content .= '</div>';

		$content .= '<div class="featured-course-link">';
		$content .= '<button data-link="' . esc_url( get_permalink( $course_id ) ) . '">' . esc_html( $button_title ) . '</button>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}


	/**
	 * Shows the course calendar.
	 *
	 * @since 1.0.0
	 */
	public static function course_calendar( $atts ) {
		global $post;

		extract( shortcode_atts( array(
			'course_id'      => in_the_loop() ? get_the_ID() : false,
			'month'          => false,
			'year'           => false,
			'pre'            => __( 'Â« Previous', CoursePress::TD ),
			'next'           => __( 'Next Â»', CoursePress::TD ),
			'date_indicator' => 'indicator_light_block',
		), $atts, 'course_calendar' ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}
		$month          = sanitize_text_field( $month );
		$month          = 'true' == $month ? true : false;
		$year           = sanitize_text_field( $year );
		$year           = 'true' == $year ? true : false;
		$pre            = sanitize_text_field( $pre );
		$next           = sanitize_text_field( $next );
		$date_indicator = sanitize_text_field( $date_indicator );

		if ( empty( $course_id ) ) {
			if ( $post && 'course' == $post->post_type ) {
				$course_id = $post->ID;
			} else {
				$parent_id = do_shortcode( '[get_parent_course_id]' );
				$course_id = 0 != $parent_id ? $parent_id : $course_id;
			}
		}

		$args = array();

		if ( ! empty( $month ) && ! empty( $year ) ) {
			$args = array( 'course_id' => $course_id, 'month' => $month, 'year' => $year );
		} else {
			$args = array( 'course_id' => $course_id );
		}

		$args['date_indicator'] = $date_indicator;

		$cal = new Course_Calendar( $args );

		return $cal->create_calendar( $pre, $next );
	}

	/**
	 * Shows the course list.
	 *
	 * @since 1.0.0
	 */
	public static function course_list( $atts ) {

		extract( shortcode_atts( array(
			'status'                    => 'publish',
			'instructor'                => '', // Note, one or the other
			'instructor_msg'            => __( 'The Instructor does not have any courses assigned yet.', CoursePress::TD ),
			'student'                   => '', // If both student and instructor is specified only student will be used
			'student_msg'               => __( 'You have not yet enrolled in a course. Browse courses %s', CoursePress::TD ),
			'two_column'                => 'yes',
			'title_column'              => 'none',
			'left_class'                => '',
			'right_class'               => '',
			'course_class'              => '',
			'title_link'                => 'yes',
			'title_class'               => 'course-title',
			'title_tag'                 => 'h3',
			'course_status'             => 'all',
			'list_wrapper_before'       => 'div',
			'list_wrapper_before_class' => 'course-list %s',
			'list_wrapper_after'        => 'div',
			'show'                      => 'dates,enrollment_dates,class_size,cost',
			'show_button'               => 'yes',
			'show_divider'              => 'yes',
			'show_media'                => 'false',
			'show_title'                => 'yes',
			'media_type'                => get_option( 'listings_media_type', 'image' ), // default, image, video
			'media_priority'            => get_option( 'listings_media_priority', 'image' ), // image, video
			'admin_links'               => 'false',
			'manage_link_title'         => __( 'Manage Course', CoursePress::TD ),
			'finished_link_title'       => __( 'View Course', CoursePress::TD ),
			'limit'                     => - 1,
			'order'                     => 'ASC',
			'class'                     => '',
		), $atts, 'course_list' ) );


		$status              = sanitize_html_class( $status );
		$instructor          = sanitize_text_field( $instructor );
		$instructor_msg      = sanitize_text_field( $instructor_msg );
		$student             = sanitize_text_field( $student );
		$student_msg         = sanitize_text_field( $student_msg );
		$two_column          = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $two_column ) );
		$title_column        = sanitize_text_field( $title_column );
		$left_class          = sanitize_html_class( $left_class );
		$right_class         = sanitize_html_class( $right_class );
		$course_class        = sanitize_html_class( $course_class );
		$title_link          = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $title_link ) );
		$title_class         = sanitize_html_class( $title_class );
		$title_tag           = sanitize_html_class( $title_tag );
		$course_status       = sanitize_text_field( $course_status );
		$list_wrapper_before = sanitize_html_class( $list_wrapper_before );
		$list_wrapper_after  = sanitize_html_class( $list_wrapper_after );
		$list_wrapper_before_class = sanitize_html_class( $list_wrapper_before_class );
		$show                = sanitize_text_field( $show );
		$show_button         = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $show_button ) );
		$show_divider        = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $show_divider ) );
		$show_title          = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $show_title ) );
		$show_media          = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $show_media ) );
		$media_type          = ! empty( $media_type ) ? sanitize_text_field( $media_type ) : 'image';
		$media_priority      = ! empty( $media_priority ) ? sanitize_text_field( $media_priority ) : 'image';
		$admin_links         = sanitize_text_field( $admin_links );
		$admin_links         = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $admin_links ) );
		$manage_link_title   = sanitize_text_field( $manage_link_title );
		$finished_link_title = sanitize_text_field( $finished_link_title );
		$limit               = (int) $limit;
		$order               = sanitize_html_class( $order );
		$class               = sanitize_html_class( $class );

		$status = 'published' == $status ? 'publish' : $status;

		// student or instructor ids provided
		$user_provided = false;
		$user_provided = empty( $student ) ? empty( $instructor ) ? false : true : true;

		$content = '';
		$courses = array();

		if ( ! empty( $instructor ) ) {
			$include_ids = array();
			$instructors = explode( ',', $instructor );
			if ( ! empty( $instructors ) ) {
				foreach ( $instructors as $ins ) {
					$ins = (int) $ins;
					if ( $ins ) {
						$course_ids = CoursePress_Model_Instructor::get_assigned_courses_ids( $ins, $status );
						if ( $course_ids ) {
							$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
						}
					}
				}
			} else {
				$instructor = (int) $instructor;
				if ( $instructor ) {
					$course_ids = CoursePress_Model_Instructor::get_assigned_courses_ids( $instructor, $status );
					if ( $course_ids ) {
						$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
					}
				}
			}
		}

		if ( ! empty( $student ) ) {
			$include_ids = array();

			$students = explode( ',', $student );
			if ( ! empty( $students ) ) {
				foreach ( $students as $stud ) {
					$stud = (int) $stud;
					if ( $stud ) {
						$course_ids = CoursePress_Model_Student::get_enrolled_courses_ids( $stud );
						if ( $course_ids ) {
							$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
						}
					}
				}
			} else {
				$student = (int) $student;
				if ( $student ) {
					$student    = new Student( $student );
					$course_ids = CoursePress_Model_Student::get_enrolled_courses_ids( $student );
					if ( $course_ids ) {
						$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
					}
				}
			}
		}

		$post_args = array(
			'order'          => $order,
			'post_type'      => 'course',
			'meta_key'       => 'enroll_type',
			'post_status'    => $status,
			'posts_per_page' => $limit
		);

		if ( ! empty( $include_ids ) ) {
			$post_args = wp_parse_args( array( 'include' => $include_ids ), $post_args );
		}


		if ( $user_provided && ! empty( $include_ids ) || ! $user_provided ) {
			$courses = get_posts( $post_args );
		}

		$content .= 0 < count( $courses ) && ! empty( $list_wrapper_before ) ? '<' . $list_wrapper_before . ' class=' . $list_wrapper_before_class . '>' : '';

		foreach ( $courses as $course ) {

			if ( ! empty( $student ) && 'all' != strtolower( $course_status ) && ! is_array( $student ) ) {

				// COMPLETION LOGIC
				//$course->completed = Student_Completion::is_course_complete( $student, $course->ID );
				$course->completed = false;
				// Skip if we wanted a completed course but got an incomplete
				if ( 'completed' == strtolower( $course_status ) && ! $course->completed ) {
					continue;
				}
				// Skip if we wanted an incompleted course but got a completed
				if ( 'incomplete' == strtolower( $course_status ) && $course->completed ) {
					continue;
				}
			}

			$content .= '<div class="course-list-item ' . $course_class . '">';
			if ( $show_media ) {
				$content .= do_shortcode( '[course_media course_id="' . $course->ID . '" type="' . $media_type . '" priority="' . $media_priority . '"]' );
			}

			if ( 'none' == $title_column ) {
				$content .= do_shortcode( '[course_title course_id="' . $course->ID . '" link="' . $title_link . '" class="' . $title_class . '" title_tag="' . $title_tag . '"]' );
			}

			if ( $two_column ) {
				$content .= '<div class="course-list-box-left ' . $left_class . '">';
			}


			if ( 'left' == $title_column ) {
				$content .= do_shortcode( '[course_title course_id="' . $course->ID . '" link="' . $title_link . '" class="' . $title_class . '" title_tag="' . $title_tag . '"]' );
			}
			// One liner..
			$content .= do_shortcode( '[course show="' . $show . '" show_title="yes" course_id="' . $course->ID . '"]' );

			if ( $two_column ) {
				$content .= '</div>';
				$content .= '<div class="course-list-box-right ' . $right_class . '">';
			}

			if ( 'right' == $title_column ) {
				$content .= do_shortcode( '[course_title course_id="' . $course->ID . '" link="' . $title_link . '" class="' . $title_class . '" title_tag="' . $title_tag . '"]' );
			}

			if ( $show_button ) {
				if ( ! empty( $course->completed ) ) {
					$content .= do_shortcode( '[course_join_button course_id="' . $course->ID . '" continue_learning_text="' . $finished_link_title . '"]' );
				} else {
					$content .= do_shortcode( '[course_join_button course_id="' . $course->ID . '"]' );
				}
			}

			if ( $admin_links ) {
				$content .= '<button class="manage-course" data-link="' . admin_url( 'admin.php?page=course_details&course_id=' . $course->ID ) . '">' . $manage_link_title . '</button>';
			}

			// Add action links if student
			if ( ! empty( $student ) ) {
				$content .= do_shortcode( '[course_action_links course_id="' . $course->ID . '"]' );
			}

			if ( $two_column ) {
				$content .= '</div>';
			}

			if ( $show_divider ) {
				$content .= '<div class="divider" ></div>';
			}

			$content .= '</div>';  //course-list-item
		} // foreach

		if ( ( ! $courses || 0 == count( $courses ) ) && ! empty( $instructor ) ) {
			$content .= $instructor_msg;
		}

		if ( ( ! $courses || 0 == count( $courses ) ) && ! empty( $student ) ) {
			$content .= sprintf( $student_msg, '<a href="' . trailingslashit( home_url() . '/' . CoursePress_Core::get_setting('slugs/course') ) . '">' . __( 'here', CoursePress::TD ) . '</a>' );
		}

		// </div> course-list
		$content .= 0 < count( $courses ) && ! empty( $list_wrapper_before ) ? '</' . $list_wrapper_after . '>' : '';

		return $content;
	}

	/**
	 * COURSE PROGRESS SHORTCODES
	 *
	 */

	/**
	 * Course Progress
	 *
	 * @since 1.0.0
	 */
	public static function course_progress( $atts ) {
		extract( shortcode_atts( array(
			'course_id'      => CoursePress_Helper_Utility::the_course( true ),
			'decimal_places' => '0',
		), $atts, 'course_progress' ) );
		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}

		$decimal_places = sanitize_text_field( $decimal_places );
		//			$completion = new Course_Completion( $course_id );
		//			$completion->init_student_status();
		//			return $completion->course_progress();
		return number_format_i18n( Student_Completion::calculate_course_completion( get_current_user_id(), $course_id ), $decimal_places );
	}

	/**
	 * Course Unit Progress
	 *
	 * @since 1.0.0
	 */
	public static function course_unit_progress( $atts ) {
		extract( shortcode_atts( array(
			'course_id'      => CoursePress_Helper_Utility::the_course( true ),
			'unit_id'        => false,
			'decimal_places' => '0',
		), $atts, 'course_unit_progress' ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}
		$unit_id = (int) $unit_id;

		$decimal_places = sanitize_text_field( $decimal_places );

		//			$completion = new Course_Completion( $course_id );
		//			$completion->init_student_status();
		//			return $completion->unit_progress( $unit_id );
		$progress = number_format_i18n( Student_Completion::calculate_unit_completion( get_current_user_id(), $course_id, $unit_id ), $decimal_places );

		return $progress;
	}

	/**
	 * Course Mandatory Message
	 *
	 * x of y mandatory elements completed
	 *
	 * @since 1.0.0
	 */
	public static function course_mandatory_message( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'unit_id'   => false,
			'message'   => __( '%d of %d mandatory elements completed.', CoursePress::TD ),
		), $atts, 'course_mandatory_message' ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}

		$unit_id = (int) $unit_id;
		$message = sanitize_text_field( $message );

		//			$completion = new Course_Completion( $course_id );
		//			$completion->init_student_status();

		$mandatory_required = Student_Completion::get_mandatory_steps_required( $unit_id );

		if ( 0 == $mandatory_required ) {
			return false;
		}

		$mandatory_completed = Student_Completion::get_mandatory_steps_completed( get_current_user_id(), $course_id, $unit_id );

		//			return sprintf( $message, $completion->unit_completed_mandatory_steps( $unit_id ), $completion->unit_mandatory_steps( $unit_id ) );
		return sprintf( $message, $mandatory_completed, $mandatory_required );
	}

	public static function course_unit_percent( $atts ) {

		extract( shortcode_atts( array(
			'course_id'           => false,
			'unit_id'             => false,
			'format'              => false,
			'style'               => 'flat',
			'decimal_places'      => '0',
			'tooltip_alt'         => __( 'Percent of the unit completion', CoursePress::TD ),
			'knob_fg_color'       => '#24bde6',
			'knob_bg_color'       => '#e0e6eb',
			'knob_data_thickness' => '.35',
			'knob_data_width'     => '70',
			'knob_data_height'    => '70',
		), $atts, 'course_unit_percent' ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}

		$unit_id             = (int) $unit_id;
		$format              = sanitize_text_field( $format );
		$decimal_places      = sanitize_text_field( $decimal_places );
		$style               = sanitize_text_field( $style );
		$tooltip_alt         = sanitize_text_field( $tooltip_alt );
		$knob_fg_color       = sanitize_text_field( $knob_fg_color );
		$knob_bg_color       = sanitize_text_field( $knob_bg_color );
		$knob_data_thickness = sanitize_text_field( $knob_data_thickness );
		$knob_data_width     = (int) $knob_data_width;
		$knob_data_height    = (int) $knob_data_height;

		if ( empty( $unit_id ) || empty( $course_id ) ) {
			$percent_value = 0;
		} else {
			$percent_value = number_format_i18n( Student_Completion::calculate_unit_completion( get_current_user_id(), $course_id, $unit_id ), $decimal_places );
		}

		$content = '';
		if ( $style == 'flat' ) {
			$content = '<span class="percentage">' . ( $format == 'true' ? $percent_value . '%' : $percent_value ) . '</span>';
		} elseif ( $style == 'none' ) {
			$content = $percent_value;
		} else {
			$content = '<a class="tooltip" alt="' . $tooltip_alt . '"><input class="knob" data-fgColor="' . $knob_fg_color . '" data-bgColor="' . $knob_bg_color . '" data-thickness="' . $knob_data_thickness . '" data-width="' . $knob_data_width . '" data-height="' . $knob_data_height . '" data-readOnly=true value="' . $percent_value . '"></a>';
		}

		return $content;
	}

	/**
	 *
	 * INSTRUCTOR DETAILS SHORTCODES
	 * =========================
	 *
	 */

	/**
	 * Shows all the instructors of the given course.
	 *
	 * Four styles are supported:
	 *
	 * * style="block" - List profile blocks including name, avatar, description (optional) and profile link. You can choose to make the entire block clickable ( link_all="yes" ) or only the profile link ( link_all="no", Default).
	 * * style="list"  - Lists instructor display names (separated by list_separator).
	 * * style="link"  - Same as 'list', but returns hyperlinks to instructor profiles.
	 * * style="count" - Outputs a simple integer value with the total of instructors for the course.
	 *
	 * @since 1.0.0
	 */
	public static function course_instructors( $atts ) {
		global $wp_query;

		$instructor_profile_slug = CoursePress_Core::get_setting( 'slugs/instructor_profile' );

		extract( shortcode_atts( array(
			'course_id'       => CoursePress_Helper_Utility::the_course( true ),
			'label'           => __( 'Instructor', CoursePress::TD ),
			'label_plural'    => __( 'Instructors', CoursePress::TD ),
			'label_delimeter' => ':&nbsp;',
			'label_tag'       => '',
			'count'           => false, // deprecated
			'list'            => false, // deprecated
			'link'            => 'false',
			'link_text'       => __( 'View Full Profile', CoursePress::TD ),
			'show_label'      => 'no', // yes, no
			'summary_length'  => 50,
			'style'           => 'block', //list, list-flat, block, count
			'list_separator'  => ', ',
			'avatar_size'     => 80,
			'default_avatar'  => '',
			'show_divider'    => 'yes',
			'link_all'        => 'no',
			'class'           => '',
		), $atts, 'course_instructors' ) );


		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}
		$label           = sanitize_text_field( $label );
		$label_plural    = sanitize_text_field( $label_plural );
		$label_delimeter = sanitize_text_field( $label_delimeter );
		$label_tag       = sanitize_html_class( $label_tag );
		$link            = CoursePress_Helper_Utility::fix_bool( sanitize_text_field( $link ) );
		$link_text       = sanitize_text_field( $link_text );
		$show_label      = CoursePress_Helper_Utility::fix_bool( sanitize_text_field( $show_label ) );
		$summary_length  = (int) $summary_length;
		$style           = sanitize_html_class( $style );
		$list_separator  = sanitize_text_field( $list_separator );
		$avatar_size     = (int) $avatar_size;
		$show_divider    = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $show_divider ) );
		$link_all        = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $link_all ) );
		$class           = sanitize_html_class( $class );

		// Support previous arguments
		$count = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $count ) );
		$list = CoursePress_Helper_Utility::fix_bool( sanitize_html_class( $list ) );
		$style = $count ? 'count' : $style;
		$style = $list ? 'list-flat' : $style;

		$show_label = 'list-flat' === $style && ! $show_label ? 'yes' : $show_label;


		$instructors = CoursePress_Model_Course::get_instructors( $course_id, true );

		$list        = array();
		$content     = '';

		if ( 0 < count( $instructors ) && $show_label ) {
			if ( ! empty( $label_tag ) ) {
				$content .= '<' . $label_tag . '>';
			}

			$content .= count( $instructors ) > 1 ? $label_plural . $label_delimeter : $label . $label_delimeter;

			if ( ! empty( $label_tag ) ) {
				$content .= '</' . $label_tag . '>';
			}
		}


		if ( 'count' != $style ) {
			if ( ! empty( $instructors ) ) {

				foreach ( $instructors as $instructor ) {

					$profile_href = trailingslashit( home_url() ) . trailingslashit( $instructor_profile_slug );
					$profile_href .= get_option( 'show_instructor_username', 1 ) == 1 ? trailingslashit( $instructor->user_login ) : trailingslashit( CoursePress_Helper_Utility::md5( $instructor->user_login ) );

					switch ( $style ) {

						case 'block':

							$content .= '<div class="instructor-profile ' . $class . '">';

							if( $link_all ) {
								$content .= '<a href="' . esc_url_raw( $profile_href ) . '">';
							}

							$content .= '<div class="profile-name">' . $instructor->display_name . '</div>';

							$content .= '<div class="profile-avatar">';
							$content .= get_avatar( $instructor->ID, $avatar_size, '', $instructor->display_name );
							$content .= '</div>';

							$content .= '<div class="profile-description">' . CoursePress_Helper_Utility::author_description_excerpt( $instructor, $summary_length ) . '</div>';

							$content .= '<div class="profile-link">';
							$content .= ! $link_all ? '<a href="' . esc_url_raw( $profile_href ) . '">' : '';
							$content .= $link_text;
							$content .= ! $link_all ? '</a>' : '';
							$content .= '</div>';

							if( $link_all ) {
								$content .= '</a>';
							}

							$content .= '</div>';
							break;

						case 'link':
						case 'list':
						case 'list-flat':
							$list[] = ( $link ? '<a href="' . esc_url_raw( $profile_href ) . '">' . esc_html( $instructor->display_name ) . '</a>' : esc_html( $instructor->display_name ) );
							break;
					}
				}
			}
		}

		switch ( $style ) {

			case 'block':
				$content = '<div class="instructor-block ' . $class . '">' . $content . '</div>';
				if ( $show_divider  && ( 0 < count( $instructors ) ) ) {
					$content .= '<div class="divider"></div>';
				}
				break;

			case 'list-flat':
				$content .= implode( $list_separator, $list );
				$content = '<div class="instructor-list instructor-list-flat ' . $class . '">' . $content . '</div>';
				break;

			case 'list':
				$content .= '<ul>';
				foreach ( $list as $instructor ) {
					$content .= '<li>' . $instructor . '</li>';
				}
				$content .= '</ul>';
				$content = '<div class="instructor-list ' . $class . '">' . $content . '</div>';
				break;

			case 'count':
				$content = count( $instructors );
				break;
		}

		return $content;
	}

	public static function course_instructor_avatar( $atts ) {
		global $wp_query;

		extract( shortcode_atts( array(
			'instructor_id' => 0,
			'thumb_size'    => 80,
			'class'         => 'small-circle-profile-image'
		), $atts ) );


		$instructor_id = (int) $instructor_id;
		if( empty( $instructor_id ) ) {
			return '';
		}

		$thumb_size = (int) $thumb_size;
		$class      = sanitize_html_class( $class );

		$content = '';

		$avatar = get_avatar( $instructor_id, $thumb_size );
		if (  ! empty( $avatar ) ) {

			preg_match( '/src=(\'|")(\S*)(\'|")/', $avatar, $match );
			$avatar_url = $match[2];

			$content .= '<div class="instructor-avatar">';
			$content .= '<div class="' . $class . '" style="background: url( ' . $avatar_url . ' ); width: ' . $thumb_size . 'px; height: ' . $thumb_size . 'px;"></div>';
			$content .= '</div>';
		}

		return $content;
	}

	public static function instructor_profile_url( $atts ) {
		$instructor_profile_slug = CoursePress_Core::get_setting( 'slugs/instructor_profile' );

		extract( shortcode_atts( array(
			'instructor_id' => 0
		), $atts ) );

		$instructor_id = (int) $instructor_id;
		if( empty( $instructor_id ) ) {
			return '';
		}
		$instructor = get_userdata( $instructor_id );

		if ( $instructor_id ) {
			if ( ( get_option( 'show_instructor_username', 1 ) == 1 ) ) {
				$username = trailingslashit( $instructor->user_login );
			} else {
				$username = trailingslashit( CoursePress_Helper_Utility::md5( $instructor->user_login ) );
			}

			return trailingslashit( home_url() ) . trailingslashit( $instructor_profile_slug ) . $username;
		}
	}

	/**
	 *
	 * MESSAGING PLUGIN SUBMENU SHORTCODE
	 * =========================
	 *
	 */
	public static function messaging_submenu( $atts ) {
		global $coursepress;

		extract( shortcode_atts( array(), $atts ) );

		if ( isset( $coursepress->inbox_subpage ) ) {
			$subpage = $coursepress->inbox_subpage;
		} else {
			$subpage = '';
		}

		$unread_count = '';

		if ( get_option( 'show_messaging', 0 ) == 1 ) {
			$unread_count = cp_messaging_get_unread_messages_count();
			if ( $unread_count > 0 ) {
				$unread_count = ' (' . $unread_count . ')';
			} else {
				$unread_count = '';
			}
		}

		ob_start();
		?>

		<div class="submenu-main-container submenu-messaging">
				<ul id="submenu-main" class="submenu nav-submenu">
					<li class="submenu-item submenu-inbox <?php echo( isset( $subpage ) && $subpage == 'inbox' ? 'submenu-active' : '' ); ?>"><a href="<?php echo $coursepress->get_inbox_slug( true ); ?>"><?php
							_e( 'Inbox', CoursePress::TD );
							echo $unread_count;
							?></a></li>
					<li class="submenu-item submenu-sent-messages <?php echo( isset( $subpage ) && $subpage == 'sent_messages' ? 'submenu-active' : '' ); ?>"><a href="<?php echo $coursepress->get_sent_messages_slug( true ); ?>"><?php _e( 'Sent', CoursePress::TD ); ?></a></li>
					<li class="submenu-item submenu-new-message <?php echo( isset( $subpage ) && $subpage == 'new_message' ? 'submenu-active' : '' ); ?>"><a href="<?php echo $coursepress->get_new_message_slug( true ); ?>"><?php _e( 'New Message', CoursePress::TD ); ?></a></li>
				</ul><!--submenu-main-->
			</div><!--submenu-main-container-->
		<br clear="all"/>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	/**
	 *
	 * UNIT DETAILS SHORTCODES
	 * =========================
	 *
	 */
	public static function course_unit_archive_submenu( $atts ) {
		global $coursepress;

		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true )
		), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			return '';
		}

		$subpage = CoursePress_Helper_Utility::the_course_subpage();

		$content = '
		<div class="submenu-main-container">
			<ul id="submenu-main" class="submenu nav-submenu">
				<li class="submenu-item submenu-units ' . ( $subpage == 'units' ? 'submenu-active' : '') . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'unit' ) ) .'">' . esc_html__( 'Units', CoursePress::TD ) . '</a></li>
				<li class="submenu-item submenu-notifications ' . ( $subpage == 'notifications' ? 'submenu-active' : '') . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'notification' ) ) . '">' . esc_html__( 'Notifications', CoursePress::TD ) . '</a></li>
		';

		$pages = CoursePress_Model_Course::allow_pages( $course_id );

		if ( $pages['course_discussion'] ) {
			$content .= '<li class="submenu-item submenu-discussions ' . ( $subpage == 'discussions' ? 'submenu-active' : '') . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'discussion' ) ) . '">' . esc_html__( 'Discussions', CoursePress::TD ) . '</a></li>';
		}

		if ( $pages['workbook'] ) {
			$content .= '<li class="submenu-item submenu-workbook ' . ( $subpage == 'workbook' ? 'submenu-active' : '') . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'workbook' ) ) . '">' .esc_html__( 'Workbook', CoursePress::TD ) . '</a></li>';
		}

		$content .= '<li class="submenu-item submenu-info"><a href="' . esc_url_raw( get_permalink( $course_id ) ) . '">' . esc_html__( 'Course Details', CoursePress::TD ) . '</a></li>';


		$show_link = false;

		if ( CoursePress_Model_Capabilities::is_pro() ) {
			// CERTIFICATE CLASS
			//$show_link = CP_Basic_Certificate::option( 'basic_certificate_enabled' );
			//$show_link = ! empty( $show_link ) ? true : false;

			//debug
			$show_link = false;
		}
		if ( is_user_logged_in() && $show_link ) {

			// COMPLETION LOGIC
			//if ( Student_Completion::is_course_complete( get_current_user_id(), $course_id ) ) {
			//	$certificate = CP_Basic_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', CoursePress::TD ) );

				//$content .= '<li class="submenu-item submenu-certificate ' . ( $subpage == 'certificate' ? 'submenu-active' : '') . '">' . $certificate . '</li>';
			//}
		}


		$content .= '
			</ul>
		</div>
		';

		return $content;

	}

	public static function courses_urls( $atts ) {
		global $enrollment_process_url, $signup_url;

		extract( shortcode_atts( array(
			'url' => ''
		), $atts ) );

		$url = esc_url_raw( $url );

		if ( $url == 'enrollment-process' ) {
			return $enrollment_process_url;
		}

		if ( $url == 'signup' ) {
			return $signup_url;
		}
	}

	public static function units_dropdown( $atts ) {
		global $wp_query;
		extract( shortcode_atts( array(
			'course_id'       => ( isset( $wp_query->post->ID ) ? $wp_query->post->ID : 0 ),
			'include_general' => 'false',
			'general_title'   => ''
		), $atts ) );

		$course_id       = (int) $course_id;
		$include_general = sanitize_text_field( $include_general );
		$include_general = 'true' == $include_general ? true : false;
		$general_title   = sanitize_text_field( $general_title );

		$course_obj = new Course( $course_id );
		$units      = $course_obj->get_units();

		$dropdown = '<div class="units_dropdown_holder"><select name="units_dropdown" class="units_dropdown">';
		if ( $include_general ) {
			if ( $general_title == '' ) {
				$general_title = __( '-- General --', CoursePress::TD );
			}

			$dropdown .= '<option value="">' . esc_html( $general_title ) . '</option>';
		}
		foreach ( $units as $unit ) {
			$dropdown .= '<option value="' . esc_attr( $unit->ID ) . '">' . esc_html( $unit->post_title ) . '</option>';
		}
		$dropdown .= '</select></div>';

		return $dropdown;
	}

	public static function course_details( $atts ) {
		global $wp_query, $signup_url, $coursepress;

		$student_id = get_current_user_id();

		extract( shortcode_atts( array(
			'course_id' => ( isset( $wp_query->post->ID ) ? $wp_query->post->ID : 0 ),
			'field'     => 'course_start_date'
		), $atts ) );

		$course_id = (int) $course_id;
		if( empty( $course_id ) ) {
			return '';
		}

		$field     = sanitize_html_class( $field );

		$map = array(
			'action_links' => 'action_links',
			'class_size' => 'class_size',
			'enroll_type' => 'enrollment_type',
			'course_start_date' => 'start',
			'course_end_date' => 'end',
			'enrollment_start_date' => 'enrollment_start',
			'enrollment_end_date' => 'enrollment_end',
			'price' => 'cost',
			'button' => 'button',
		);

		$action = in_array( $field, $map ) ? $map[ $field ] : $field;
		return do_shortcode( '[course course_id="' . $course_id . '" show="' . $action . '"]' );

	}

	public static function get_parent_course_id( $atts ) {
		global $wp;

		//if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
		if ( is_array( $wp->query_vars ) && array_key_exists( 'coursename', $wp->query_vars ) ) {
			$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
		} else {
			$course_id = 0;
		}

		return $course_id;
	}

	public static function courses_student_dashboard( $atts ) {

		$content = CoursePress_Template_Student::dashboard();

		return $content;
	}

	public static function courses_student_settings( $atts ) {

		$content = CoursePress_Template_Student::student_settings();

		return $content;
	}

	public static function student_registration_form() {

		$content = CoursePress_Template_Student::registration_form();

		return $content;
	}

	public static function course_unit_single( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'unit_id' => 0 ), $atts ) );

		$unit_id = (int) $unit_id;

		if ( empty( $unit_id ) ) {
			if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
				$unit    = new Unit();
				$unit_id = $unit->get_unit_id_by_name( $wp->query_vars['unitname'] );
			} else {
				$unit_id = 0;
			}
		}

		//echo $unit_id;

		$args = array(
			'post_type'   => 'unit',
			//'post_id'		 => $unit_id,
			'post__in'    => array( $unit_id ),
			'post_status' => cp_can_see_unit_draft() ? 'any' : 'publish',
		);

		ob_start();
		query_posts( $args );
		ob_clean();
	}

	public static function course_units_loop( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'course_id' => 0 ), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp', 0 ) );

		$args = array(
			'order'          => 'ASC',
			'post_type'      => 'unit',
			'post_status'    => ( cp_can_see_unit_draft() ? 'any' : 'publish' ),
			'meta_key'       => 'unit_order',
			'orderby'        => 'meta_value_num',
			'posts_per_page' => '-1',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => 'course_id',
					'value' => $course_id
				),
			)
		);

		query_posts( $args );
	}

	public static function courses_loop( $atts ) {
		global $wp;
		if ( array_key_exists( 'course_category', $wp->query_vars ) ) {
			$page       = ( isset( $wp->query_vars['paged'] ) ) ? $wp->query_vars['paged'] : 1;
			$query_args = array(
				'post_type'   => 'course',
				'post_status' => 'publish',
				'paged'       => $page,
				'tax_query'   => array(
					array(
						'taxonomy' => 'course_category',
						'field'    => 'slug',
						'terms'    => array( $wp->query_vars['course_category'] ),
					)
				)
			);

			$selected_course_order_by_type = get_option( 'course_order_by_type', 'DESC' );
			$selected_course_order_by      = get_option( 'course_order_by', 'post_date' );

			if ( $selected_course_order_by == 'course_order' ) {
				$query_args['meta_key']   = 'course_order';
				$query_args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key'     => 'course_order',
						'compare' => 'NOT EXISTS'
					),
				);
				$query_args['orderby']    = 'meta_value';
				$query_args['order']      = $selected_course_order_by_type;
			} else {
				$query_args['orderby'] = $selected_course_order_by;
				$query_args['order']   = $selected_course_order_by_type;
			}

			query_posts( $query_args );
		}
	}

	public static function course_notifications_loop( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'course_id' => 0 ), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$args = array(
			'category'       => '',
			'order'          => 'ASC',
			'post_type'      => 'notifications',
			'post_mime_type' => '',
			'post_parent'    => '',
			'post_status'    => 'publish',
			'orderby'        => 'meta_value_num',
			'posts_per_page' => '-1',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => 'course_id',
					'value' => $course_id
				),
				array(
					'key'   => 'course_id',
					'value' => ''
				),
			)
		);

		query_posts( $args );
	}

	public static function course_discussion_loop( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'course_id' => 0 ), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$args = array(
			'order'          => 'DESC',
			'post_type'      => 'discussions',
			'post_mime_type' => '',
			'post_parent'    => '',
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
			'meta_key'       => 'course_id',
			'meta_value'     => $course_id
		);

		query_posts( $args );
	}

	public static function course_units( $atts ) {
		global $wp, $coursepress;

		$content = '';

		extract( shortcode_atts( array( 'course_id' => $course_id ), $atts ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$course = new Course( $course_id );
		$units  = $course->get_units( $course_id, 'publish' );

		$user_id = get_current_user_id();
		$student = new Student( $user_id );
		//redirect to the parent course page if not enrolled
		if ( ! current_user_can( 'manage_options' ) ) {//If current user is not admin, check if he can access to the units
			if ( $course->details->post_author != get_current_user_id() ) {//check if user is an author of a course ( probably instructor )
				if ( ! current_user_can( 'coursepress_view_all_units_cap' ) ) {//check if the instructor, even if it's not the author of the course, maybe has a capability given by the admin
					//if it's not an instructor who made the course, check if he is enrolled to course
					// Added 3rd parameter to deal with legacy meta data
					if ( ! $student->user_enrolled_in_course( $course_id, $user_id, 'update_meta' ) ) {
						// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
						//ob_start();
						wp_redirect( get_permalink( $course_id ) ); //if not, redirect him to the course page so he may enroll it if the enrollment is available
						exit;
					}
				}
			}
		}


		$content .= '<ol>';
		$last_unit_url = '';

		foreach ( $units as $unit ) {
			//				$unit_details	 = new Unit( $unit->ID );
			$content .= '<li><a href="' . Unit::get_permalink( $unit->ID, $course_id ) . '">' . $unit->post_title . '</a></li>';
			$last_unit_url = Unit::get_permalink( $unit->ID, $course_id );
		}

		$content .= '</ol>';

		if ( count( $units ) >= 1 ) {
			$content .= do_shortcode( '[course_discussion]' );
		}

		if ( count( $units ) == 0 ) {
			$content = __( '0 course units prepared yet. Please check back later.', CoursePress::TD );
		}

		if ( count( $units ) == 1 ) {
			//ob_start();
			// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
			wp_redirect( $last_unit_url );
			exit;
		}

		return $content;
	}

	public static function course_unit_details( $atts ) {
		global $post_id, $wp, $coursepress;

		extract( shortcode_atts(
			apply_filters( 'shortcode_atts_course_unit_details', array(
				'unit_id'                         => 0,
				'field'                           => 'post_title',
				'format'                          => 'true',
				'additional'                      => '2',
				'style'                           => 'flat',
				'class'                           => 'course-name-content',
				'tooltip_alt'                     => __( 'Percent of the unit completion', CoursePress::TD ),
				'knob_fg_color'                   => '#24bde6',
				'knob_bg_color'                   => '#e0e6eb',
				'knob_data_thickness'             => '.35',
				'knob_data_width'                 => '70',
				'knob_data_height'                => '70',
				'unit_title'                      => '',
				'unit_page_title_tag'             => 'h3',
				'unit_page_title_tag_class'       => '',
				'last_visited'                    => 'false',
				'parent_course_preceding_content' => __( 'Course: ', CoursePress::TD ),
				'student_id'                      => get_current_user_ID(),
			) ), $atts ) );

		$unit_id                         = (int) $unit_id;
		$field                           = sanitize_html_class( $field );
		$format                          = sanitize_text_field( $format );
		$format                          = 'true' == $format ? true : false;
		$additional                      = sanitize_text_field( $additional );
		$style                           = sanitize_html_class( $style );
		$tooltip_alt                     = sanitize_text_field( $tooltip_alt );
		$knob_fg_color                   = sanitize_text_field( $knob_fg_color );
		$knob_bg_color                   = sanitize_text_field( $knob_bg_color );
		$knob_data_thickness             = sanitize_text_field( $knob_data_thickness );
		$knob_data_width                 = (int) $knob_data_width;
		$knob_data_height                = (int) $knob_data_height;
		$unit_title                      = sanitize_text_field( $unit_title );
		$unit_page_title_tag             = sanitize_html_class( $unit_page_title_tag );
		$unit_page_title_tag_class       = sanitize_html_class( $unit_page_title_tag_class );
		$parent_course_preceding_content = sanitize_text_field( $parent_course_preceding_content );
		$student_id                      = (int) $student_id;
		$last_visited                    = sanitize_text_field( $last_visited );
		$last_visited                    = 'true' == $last_visited ? true : false;
		$class                           = sanitize_html_class( $class );

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$content = '';
		if ( $field == 'permalink' ) {
			// COMPLETION_LOGIC
			//if ( $last_visited ) {
			//	$last_visited_page     = cp_get_last_visited_unit_page( $unit_id );
			//	$unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id ) . 'page/' . trailingslashit( $last_visited_page );
			//} else {
				$unit = get_post( $unit_id );
				$content = get_permalink( $course_id ) . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . $unit->post_name;
				//$unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id );
			//}
		}


		return $content;

		// COMPLETION LOGIC


		if ( $unit_id == 0 ) {
			$unit_id = get_the_ID();
		}

		$unit = new Unit( $unit_id );

		$student = new Student( get_current_user_id() );
		$class   = sanitize_html_class( $class );

		if ( $field == 'is_unit_available' ) {
			$unit->details->$field = Unit::is_unit_available( $unit_id );
		}

		if ( $field == 'unit_page_title' ) {
			$paged     = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
			$page_name = $unit->get_unit_page_name( $paged );
			if ( $unit_title !== '' ) {
				$page_title_prepend = $unit_title . ': ';
			} else {
				$page_title_prepend = '';
			}

			$show_title_array = get_post_meta( $unit_id, 'show_page_title', true );
			$show_title       = false;
			if ( isset( $show_title_array[ $paged - 1 ] ) && 'yes' == $show_title_array[ $paged - 1 ] ) {
				$show_title = true;
			}

			if ( ! empty( $page_name ) && $show_title ) {
				$unit->details->$field = '<' . $unit_page_title_tag . '' . ( $unit_page_title_tag_class !== '' ? ' class="' . $unit_page_title_tag_class . '"' : '' ) . '>' . $page_title_prepend . $unit->get_unit_page_name( $paged ) . '</' . $unit_page_title_tag . '>';
			} else {
				$unit->details->$field = '';
			}
		}

		if ( $field == 'parent_course' ) {
			$course                = new Course( $unit->course_id );
			$unit->details->$field = $parent_course_preceding_content . '<a href="' . $course->get_permalink() . '" class="' . $class . '">' . $course->details->post_title . '</a>';
		}

		/* ------------ */

		$front_save_count = 0;

		$modules           = Unit_Module::get_modules( $unit_id );
		$mandatory_answers = 0;
		$mandatory         = 'no';

		foreach ( $modules as $mod ) {

			$mandatory = get_post_meta( $mod->ID, 'mandatory_answer', true );

			if ( $mandatory == 'yes' ) {
				$mandatory_answers ++;
			}

			$class_name = $mod->module_type;

			if ( class_exists( $class_name ) ) {
				if ( constant( $class_name . '::FRONT_SAVE' ) ) {
					$front_save_count ++;
				}
			}
		}

		$input_modules_count = $front_save_count;
		/* ------------ */
		//$input_modules_count = do_shortcode( '[course_unit_details field="input_modules_count" unit_id="' . $unit_id . '"]' );

		$responses_count = 0;

		$modules = Unit_Module::get_modules( $unit_id );
		foreach ( $modules as $module ) {
			if ( Unit_Module::did_student_respond( $module->ID, $student_id ) ) {
				$responses_count ++;
			}
		}
		$student_modules_responses_count = $responses_count;

		//$student_modules_responses_count = do_shortcode( '[course_unit_details field="student_module_responses" unit_id="' . $unit_id . '"]' );

		if ( $student_modules_responses_count > 0 ) {
			$percent_value = $mandatory_answers > 0 ? ( round( ( 100 / $mandatory_answers ) * $student_modules_responses_count, 0 ) ) : 0;
			$percent_value = ( $percent_value > 100 ? 100 : $percent_value ); //in case that student gave answers on all mandatory plus optional questions
		} else {
			$percent_value = 0;
		}

		if ( $input_modules_count == 0 ) {

			$grade              = 0;
			$front_save_count   = 0;
			$assessable_answers = 0;
			$responses          = 0;
			$graded             = 0;
			//$input_modules_count = do_shortcode( '[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]' );
			$modules = Unit_Module::get_modules( $unit_id );

			if ( $input_modules_count > 0 ) {
				foreach ( $modules as $mod ) {

					$class_name = $mod->module_type;
					$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );

					if ( class_exists( $class_name ) ) {

						if ( constant( $class_name . '::FRONT_SAVE' ) ) {

							if ( $assessable == 'yes' ) {
								$assessable_answers ++;
							}

							$front_save_count ++;
							$response = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );

							if ( isset( $response->ID ) ) {
								$grade_data = Unit_Module::get_response_grade( $response->ID );
								$grade      = $grade + $grade_data['grade'];

								if ( get_post_meta( $response->ID, 'response_grade' ) ) {
									$graded ++;
								}

								$responses ++;
							}
						} else {
							//read only module
						}
					}
				}
				$percent_value = ( $format == true ? ( $responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">' ) . ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) . '</span>' : ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) );
			} else {
				$student = new Student( $student_id );
				if ( $student->is_unit_visited( $unit_id, $student_id ) ) {
					$grade         = 100;
					$percent_value = ( $format == true ? '<span class="grade-active">' . $grade . '</span>' : $grade );
				} else {
					$grade         = 0;
					$percent_value = ( $format == true ? '<span class="grade-inactive">' . $grade . '</span>' : $grade );
				}
			}

			//$percent_value = do_shortcode( '[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]' );
		}

		//redirect to the parent course page if not enrolled
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( ! $coursepress->check_access( $unit->course_id, $unit_id ) ) {
				// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
				//ob_start();
				wp_redirect( get_permalink( $unit->course_id ) );
				exit;
			}
		}

		if ( $field == 'percent' ) {

			//				$completion		 = new Course_Completion( $unit->course_id );
			//				$completion->init_student_status();
			//				$percent_value	 = $completion->unit_progress( $unit_id );
			$percent_value = Student_Completion::calculate_unit_completion( $student_id, $unit->course_id, $unit_id );

			$assessable_input_modules_count = do_shortcode( '[course_unit_details field="assessable_input_modules_count"]' );

			if ( $style == 'flat' ) {
				$unit->details->$field = '<span class="percentage">' . ( $format == true ? $percent_value . '%' : $percent_value ) . '</span>';
			} elseif ( $style == 'none' ) {
				$unit->details->$field = $percent_value;
			} else {
				$unit->details->$field = '<a class="tooltip" alt="' . $tooltip_alt . '"><input class="knob" data-fgColor="' . $knob_fg_color . '" data-bgColor="' . $knob_bg_color . '" data-thickness="' . $knob_data_thickness . '" data-width="' . $knob_data_width . '" data-height="' . $knob_data_height . '" data-readOnly=true value="' . $percent_value . '"></a>';
			}
		}

		if ( $field == 'permalink' ) {
			if ( $last_visited ) {
				$last_visited_page     = cp_get_last_visited_unit_page( $unit_id );
				$unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id ) . 'page/' . trailingslashit( $last_visited_page );
			} else {
				$unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id );
			}
		}

		if ( $field == 'input_modules_count' ) {
			$front_save_count = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {

				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {
					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						$front_save_count ++;
					}
				}
			}

			$unit->details->$field = $front_save_count;
		}

		if ( $field == 'mandatory_input_modules_count' ) {

			$front_save_count  = 0;
			$mandatory_answers = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				$mandatory_answer = get_post_meta( $mod->ID, 'mandatory_answer', true );

				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {
					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						if ( $mandatory_answer == 'yes' ) {
							$mandatory_answers ++;
						}
						//$front_save_count++;
					}
				}
			}

			$unit->details->$field = $mandatory_answers;
		}

		if ( $field == 'assessable_input_modules_count' ) {
			$front_save_count   = 0;
			$assessable_answers = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );

				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {
					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						if ( $assessable == 'yes' ) {
							$assessable_answers ++;
						}
						//$front_save_count++;
					}
				}
			}

			if ( isset( $unit->details->$field ) ) {
				$unit->details->$field = $assessable_answers;
			}
		}

		if ( $field == 'student_module_responses' ) {
			$responses_count   = 0;
			$mandatory_answers = 0;
			$modules           = Unit_Module::get_modules( $unit_id );
			foreach ( $modules as $module ) {

				$mandatory = get_post_meta( $module->ID, 'mandatory_answer', true );

				if ( $mandatory == 'yes' ) {
					$mandatory_answers ++;
				}

				if ( Unit_Module::did_student_respond( $module->ID, $student_id ) ) {
					$responses_count ++;
				}
			}

			if ( $additional == 'mandatory' ) {
				if ( $responses_count > $mandatory_answers ) {
					$unit->details->$field = $mandatory_answers;
				} else {
					$unit->details->$field = $responses_count;
				}
				//so we won't have 7 of 6 mandatory answered but mandatory number as a max number
			} else {
				$unit->details->$field = $responses_count;
			}
		}

		if ( $field == 'student_unit_grade' ) {
			$grade               = 0;
			$front_save_count    = 0;
			$responses           = 0;
			$graded              = 0;
			$input_modules_count = do_shortcode( '[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]' );
			$modules             = Unit_Module::get_modules( $unit_id );
			$mandatory_answers   = 0;
			$assessable_answers  = 0;

			if ( $input_modules_count > 0 ) {
				foreach ( $modules as $mod ) {

					$class_name = $mod->module_type;

					if ( class_exists( $class_name ) ) {

						if ( constant( $class_name . '::FRONT_SAVE' ) ) {
							$front_save_count ++;
							$response   = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );
							$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );
							$mandatory  = get_post_meta( $mod->ID, 'mandatory_answer', true );


							if ( $assessable == 'yes' ) {
								$assessable_answers ++;
							}

							if ( isset( $response->ID ) ) {

								if ( $assessable == 'yes' ) {

									$grade_data = Unit_Module::get_response_grade( $response->ID );
									$grade      = $grade + $grade_data['grade'];

									if ( get_post_meta( $response->ID, 'response_grade' ) ) {
										$graded ++;
									}

									$responses ++;
								}
							}
						} else {
							//read only module
						}
					}
				}

				$unit->details->$field = ( $format == true ? ( $responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">' ) . ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) . '%</span>' : ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) );
			} else {
				$student = new Student( $student_id );
				if ( $student->is_unit_visited( $unit_id, $student_id ) ) {
					$grade                 = 100;
					$unit->details->$field = ( $format == true ? '<span class="grade-active">' . $grade . '%</span>' : $grade );
				} else {
					$grade                 = 0;
					$unit->details->$field = ( $format == true ? '<span class="grade-inactive">' . $grade . '%</span>' : $grade );
				}
			}
		}

		if ( $field == 'student_unit_modules_graded' ) {
			$grade            = 0;
			$front_save_count = 0;
			$responses        = 0;
			$graded           = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {

				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {

					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						$front_save_count ++;
						$response = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );

						if ( isset( $response->ID ) ) {
							$grade_data = Unit_Module::get_response_grade( $response->ID );
							$grade      = $grade + $grade_data['grade'];

							if ( get_post_meta( $response->ID, 'response_grade' ) ) {
								$graded ++;
							}

							$responses ++;
						}
					} else {
						//read only module
					}
				}
			}

			$unit->details->$field = $graded;
		}

		if ( isset( $unit->details->$field ) ) {
			return $unit->details->$field;
		}
	}

	public static function course_breadcrumbs( $atts ) {
		global $course_slug, $units_slug, $units_breadcrumbs, $wp;

		extract( shortcode_atts( array(
			'type'      => 'unit_archive',
			'course_id' => 0,
			'position'  => 'shortcode'
		), $atts ) );

		$course_id = (int) $course_id;
		$type      = sanitize_html_class( $type );
		$position  = sanitize_html_class( $position );

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$course = new Course( $course_id );

		if ( $type == 'unit_archive' ) {
			$units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit( get_option( 'home' ) ) . $course_slug . '/">' . __( 'Courses', CoursePress::TD ) . '</a> Â» <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a></div>';
		}

		if ( $type == 'unit_single' ) {
			$units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit( get_option( 'home' ) ) . $course_slug . '/">' . __( 'Courses', CoursePress::TD ) . '</a> Â» <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a> Â» <a href="' . $course->get_permalink() . $units_slug . '/">' . __( 'Units', CoursePress::TD ) . '</a></div>';
		}

		if ( $position == 'shortcode' ) {
			return $units_breadcrumbs;
		}
	}

	public static function course_discussion( $atts ) {
		global $wp;

		if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
			$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
		} else {
			$course_id = 0;
		}

		$course = new Course( $course_id );

		if ( $course->details->allow_course_discussion == 'on' ) {

			$comments_args = array(
				// change the title of send button
				'label_submit'        => __( 'Send', CoursePress::TD ),
				// change the title of the reply section
				'title_reply'         => __( 'Write a Reply or Comment', CoursePress::TD ),
				// remove "Text or HTML to be displayed after the set of comment fields"
				'comment_notes_after' => '',
				// redefine your own textarea ( the comment body )
				'comment_field'       => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
			);

			$defaults = array(
				'author_email' => '',
				'ID'           => '',
				'karma'        => '',
				'number'       => '',
				'offset'       => '',
				'orderby'      => '',
				'order'        => 'DESC',
				'parent'       => '',
				'post_id'      => $course_id,
				'post_author'  => '',
				'post_name'    => '',
				'post_parent'  => '',
				'post_status'  => '',
				'post_type'    => '',
				'status'       => '',
				'type'         => '',
				'user_id'      => '',
				'search'       => '',
				'count'        => false,
				'meta_key'     => '',
				'meta_value'   => '',
				'meta_query'   => '',
			);

			$wp_list_comments_args = array(
				'walker'            => null,
				'max_depth'         => '',
				'style'             => 'ul',
				'callback'          => null,
				'end-callback'      => null,
				'type'              => 'all',
				'reply_text'        => __( 'Reply', CoursePress::TD ),
				'page'              => '',
				'per_page'          => '',
				'avatar_size'       => 32,
				'reverse_top_level' => null,
				'reverse_children'  => '',
				'format'            => 'xhtml', //or html5 @since 3.6
				'short_ping'        => false // @since 3.6
			);

			comment_form( $comments_args = array(), $course_id );
			wp_list_comments( $wp_list_comments_args, get_comments( $defaults ) );
			//comments_template()
		}
	}

	public static function unit_discussion( $atts ) {
		global $wp;
		if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
			$unit    = new Unit();
			$unit_id = $unit->get_unit_id_by_name( $wp->query_vars['unitname'] );
		} else {
			$unit_id = 0;
		}

		$comments_args = array(
			// change the title of send button
			'label_submit'        => 'Send',
			// change the title of the reply secpertion
			'title_reply'         => 'Write a Reply or Comment',
			// remove "Text or HTML to be displayed after the set of comment fields"
			'comment_notes_after' => '',
			// redefine your own textarea ( the comment body )
			'comment_field'       => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
		);
		ob_start();
		comment_form( $comments_args, $unit_id );
		$content = ob_get_clean();

		return $content;
	}





	/* =========== PAGES SHORTCODES =============== */

	public static function cp_pages( $atts ) {
		ob_start();
		global $plugin_dir;
		extract( shortcode_atts( array(
			'page' => '',
		), $atts ) );

		switch ( $page ) {
			case 'enrollment_process':
				require( $plugin_dir . 'includes/templates/enrollment-process.php' );
				break;

			case 'student_login':
				require( $plugin_dir . 'includes/templates/student-login.php' );
				break;

			case 'student_signup':
				require( $plugin_dir . 'includes/templates/student-signup.php' );
				break;

			case 'student_dashboard':
				require( $plugin_dir . 'includes/templates/student-dashboard.php' );
				break;

			case 'student_settings':
				require( $plugin_dir . 'includes/templates/student-settings.php' );
				break;

			default:
				_e( 'Page cannot be found', CoursePress::TD );
		}

		$content = wpautop( ob_get_clean(), apply_filters( 'coursepress_pages_content_preserve_line_breaks', true ) );

		return $content;
	}

	public static function course_signup( $atts ) {

		$allowed = array( 'signup', 'login' );

		extract( shortcode_atts( array(
			'page'               => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
			'failed_login_text'  => __( 'Invalid login.', CoursePress::TD ),
			'failed_login_class' => 'red',
			'logout_url'         => '',
			'signup_tag'         => 'h3',
			'signup_title'       => __( 'Signup', CoursePress::TD ),
			'login_tag'          => 'h3',
			'login_title'        => __( 'Login', CoursePress::TD ),
			'signup_url'         => '',
			'login_url'          => '',
			'redirect_url'       => '', // redirect on successful login or signup
		), $atts, 'course_signup' ) );

		$failed_login_text  = sanitize_text_field( $failed_login_text );
		$failed_login_class = sanitize_html_class( $failed_login_class );
		$logout_url         = esc_url_raw( $logout_url );
		$signup_tag         = sanitize_html_class( $signup_tag );
		$signup_title       = sanitize_text_field( $signup_title );
		$login_tag          = sanitize_html_class( $login_tag );
		$login_title        = sanitize_text_field( $login_title );
		$signup_url         = esc_url_raw( $signup_url );
		$redirect_url       = esc_url_raw( $redirect_url );


		$page = in_array( $page, $allowed ) ? $page : 'signup';

		$signup_prefix = empty( $signup_url ) ? '&' : '?';
		$login_prefix  = empty( $login_url ) ? '&' : '?';

		$signup_url    = empty( $signup_url ) ? CoursePress_Core::get_slug( 'signup', true ) : $signup_url;
		$login_url     = empty( $login_url ) ? CoursePress_Core::get_slug( 'signup', true ) : $login_url;

		if ( ! empty( $redirect_url ) ) {
			$signup_url = $signup_url . $signup_prefix . 'redirect_url=' . urlencode( $redirect_url );
			$login_url  = $login_url . $login_prefix . 'redirect_url=' . urlencode( $redirect_url );
		}
		if ( ! empty( $_POST['redirect_url'] ) ) {
			$signup_url = $signup_url . '?redirect_url=' . $_POST['redirect_url'];
			$login_url  = $login_url . '?redirect_url=' . $_POST['redirect_url'];
		}

		//Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
		};

		//Set a redirect for the logout form
		if ( ! empty( $logout_url ) ) {
			// Legacy, kept for other plugins that want this
			update_option( 'cp_custom_login_url', $logout_url );
		}

		$form_message       = '';
		$form_message_class = '';
		// Attempt a login if submitted
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {

			$auth = wp_authenticate_username_password( null, $_POST['log'], $_POST['pwd'] );
			if ( ! is_wp_error( $auth ) ) {
				// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
				$user    = get_user_by( 'login', $_POST['log'] );
				$user_id = $user->ID;
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );
				if ( ! empty( $redirect_url ) ) {
					wp_redirect( urldecode( $redirect_url ) );
				} else {
					wp_redirect( CoursePress_Core::get_slug( 'student_dashboard', true ) );
				}
				exit;
			} else {
				$form_message       = $failed_login_text;
				$form_message_class = $failed_login_class;
			}
		}

		switch ( $page ) {

			case 'signup':

				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Helper_Utility::users_can_register() ) {
						?>

						<?php
						$form_message_class = '';
						$form_message       = '';

						$student = new Student( 0 );

						if ( isset( $_POST['student-settings-submit'] ) ) {

							check_admin_referer( 'student_signup' );
							$min_password_length = apply_filters( 'coursepress_min_password_length', 6 );

							$student_data = array();
							$form_errors  = 0;

							do_action( 'coursepress_before_signup_validation' );

							if ( $_POST['username'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['email'] != '' && $_POST['password'] != '' && $_POST['password_confirmation'] != '' ) {

								if ( ! username_exists( $_POST['username'] ) ) {

									if ( ! email_exists( $_POST['email'] ) ) {

										if ( $_POST['password'] == $_POST['password_confirmation'] ) {

											if ( ! preg_match( "#[0-9]+#", $_POST['password'] ) || ! preg_match( "#[a-zA-Z]+#", $_POST['password'] ) || strlen( $_POST['password'] ) < $min_password_length ) {
												$form_message       = sprintf( __( 'Your password must be at least %d characters long and have at least one letter and one number in it.', CoursePress::TD ), $min_password_length );
												$form_message_class = 'red';
												$form_errors ++;
											} else {

												if ( $_POST['password_confirmation'] ) {
													$student_data['user_pass'] = $_POST['password'];
												} else {
													$form_message       = __( "Passwords don't match", CoursePress::TD );
													$form_message_class = 'red';
													$form_errors ++;
												}
											}
										} else {
											$form_message       = __( 'Passwords don\'t match', CoursePress::TD );
											$form_message_class = 'red';
											$form_errors ++;
										}

										$student_data['role']       = get_option( 'default_role', 'subscriber' );
										$student_data['user_login'] = $_POST['username'];
										$student_data['user_email'] = $_POST['email'];
										$student_data['first_name'] = $_POST['first_name'];
										$student_data['last_name']  = $_POST['last_name'];

										if ( ! is_email( $_POST['email'] ) ) {
											$form_message       = __( 'E-mail address is not valid.', CoursePress::TD );
											$form_message_class = 'red';
											$form_errors ++;
										}

										if ( isset( $_POST['tos_agree'] ) ) {
											if ( $_POST['tos_agree'] == '0' ) {
												$form_message       = __( 'You must agree to the Terms of Service in order to signup.', CoursePress::TD );
												$form_message_class = 'red';
												$form_errors ++;
											}
										}

										if ( $form_errors == 0 ) {
											$student_id = wp_insert_user( $student_data );
											if ( ! empty( $student_id ) ) {
												//$form_message = __( 'Account created successfully! You may now <a href="' . ( get_option( 'use_custom_login_form', 1 ) ? trailingslashit( site_url() . '/' . $this->get_login_slug() ) : wp_login_url() ) . '">log into your account</a>.', CoursePress::TD );
												//$form_message_class = 'regular';
												$email_args['email_type']                 = CoursePress_Helper_Email::REGISTRATION;
												$email_args['email']                      = $student_data['user_email'];
												$email_args['first_name']                 = $student_data['first_name'];
												$email_args['last_name']                  = $student_data['last_name'];
												$email_args['fields']                     = array();
												$email_args['fields']['student_id']       = $student_id;
												$email_args['fields']['student_username'] = $student_data['user_login'];
												$email_args['fields']['student_password'] = $student_data['user_pass'];

												CoursePress_Helper_Email::send_email( $email_args );

												$creds                  = array();
												$creds['user_login']    = $student_data['user_login'];
												$creds['user_password'] = $student_data['user_pass'];
												$creds['remember']      = true;
												$user                   = wp_signon( $creds, false );

												if ( is_wp_error( $user ) ) {
													$form_message       = $user->get_error_message();
													$form_message_class = 'red';
												}

												// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
												if ( isset( $_POST['course_id'] ) && is_numeric( $_POST['course_id'] ) ) {
													$course = new Course( $_POST['course_id'] );
													wp_redirect( $course->get_permalink() );
												} else {
													if ( ! empty( $redirect_url ) ) {
														wp_redirect( apply_filters( 'coursepress_redirect_after_signup_redirect_url', $redirect_url ) );
													} else {
														wp_redirect( apply_filters( 'coursepress_redirect_after_signup_url', CoursePress::instance()->get_student_dashboard_slug( true ) ) );
													}
												}
												exit;
											} else {
												$form_message       = __( 'An error occurred while creating the account. Please check the form and try again.', CoursePress::TD );
												$form_message_class = 'red';
											}
										}
									} else {
										$form_message       = __( 'Sorry, that email address is already used!', CoursePress::TD );
										$form_message_class = 'error';
									}
								} else {
									$form_message       = __( 'Username already exists. Please choose another one.', CoursePress::TD );
									$form_message_class = 'red';
								}
							} else {
								$form_message       = __( 'All fields are required.', CoursePress::TD );
								$form_message_class = 'red';
							}
						} else {
							$form_message = __( 'All fields are required.', CoursePress::TD );
						}
						?>
						<?php
						//ob_start();
						if ( ! empty( $signup_title ) ) {
							echo '<' . $signup_tag . '>' . $signup_title . '</' . $signup_tag . '>';
						}
						?>

						<p class="form-info-<?php echo apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ); ?>"><?php echo apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ); ?></p>

						<?php do_action( 'coursepress_before_signup_form' ); ?>

						<form id="student-settings" name="student-settings" method="post" class="student-settings">

								<?php do_action( 'coursepress_before_all_signup_fields' ); ?>

							<input type="hidden" name="course_id" value="<?php esc_attr_e( isset( $_GET['course_id'] ) ? $_GET['course_id'] : ' ' ); ?>"/>
								<input type="hidden" name="redirect_url" value="<?php echo $redirect_url; ?>"/>

								<label>
									<?php _e( 'First Name', CoursePress::TD ); ?>:
									<input type="text" name="first_name" value="<?php echo( isset( $_POST['first_name'] ) ? $_POST['first_name'] : '' ); ?>"/>
								</label>

							<?php do_action( 'coursepress_after_signup_first_name' ); ?>

							<label>
									<?php _e( 'Last Name', CoursePress::TD ); ?>:
									<input type="text" name="last_name" value="<?php echo( isset( $_POST['last_name'] ) ? $_POST['last_name'] : '' ); ?>"/>
								</label>

							<?php do_action( 'coursepress_after_signup_last_name' ); ?>

							<label>
									<?php _e( 'Username', CoursePress::TD ); ?>:
									<input type="text" name="username" value="<?php echo( isset( $_POST['username'] ) ? $_POST['username'] : '' ); ?>"/>
								</label>

							<?php do_action( 'coursepress_after_signup_username' ); ?>

							<label>
									<?php _e( 'E-mail', CoursePress::TD ); ?>:
									<input type="text" name="email" value="<?php echo( isset( $_POST['email'] ) ? $_POST['email'] : '' ); ?>"/>
								</label>

							<?php do_action( 'coursepress_after_signup_email' ); ?>

							<label>
									<?php _e( 'Password', CoursePress::TD ); ?>:
									<input type="password" name="password" value=""/>
								</label>

							<?php do_action( 'coursepress_after_signup_password' ); ?>

							<label class="right">
									<?php _e( 'Confirm Password', CoursePress::TD ); ?>:
									<input type="password" name="password_confirmation" value=""/>
								</label>
								<br clear="both"/><br/>

							<?php
							if ( shortcode_exists( 'signup-tos' ) ) {
								if ( get_option( 'show_tos', 0 ) == '1' ) {
									?>
									<label class="full"><?php echo do_shortcode( '[signup-tos]' ); ?></label>
								<?php
								}
							}
							?>

							<?php do_action( 'coursepress_after_all_signup_fields' ); ?>

							<label class="full">
									<?php printf( __( 'Already have an account? %s%s%s!', CoursePress::TD ), '<a href="' . $login_url . '">', __( 'Login to your account', CoursePress::TD ), '</a>' ); ?>
								</label>

								<label class="full-right">
									<input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php _e( 'Create an Account', CoursePress::TD ); ?>"/>
								</label>

							<?php do_action( 'coursepress_after_submit' ); ?>

							<?php wp_nonce_field( 'student_signup' ); ?>
							</form>
						<div class="clearfix" style="clear: both;"></div>

						<?php do_action( 'coursepress_after_signup_form' ); ?>
						<?php
						//$content = ob_get_clean();
						// Return the html in the buffer.
						//return $content;
					} else {
						_e( 'Registrations are not allowed.', CoursePress::TD );
					}
				} else {

					if ( ! empty( $redirect_url ) ) {
						wp_redirect( urldecode( $redirect_url ) );
					} else {
						wp_redirect( CoursePress::instance()->get_student_dashboard_slug( true ) );
					}
					exit;
				}

				break;

			case 'login':
				?>
				<?php
				if ( ! empty( $login_title ) ) {
					echo '<' . $login_tag . '>' . $login_title . '</' . $login_tag . '>';
				}
				?>
				<p class="form-info-<?php echo apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ); ?>"><?php echo apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ); ?></p>
				<?php do_action( 'coursepress_before_login_form' ); ?>
				<form name="loginform" id="student-settings" class="student-settings" method="post">
						<?php do_action( 'coursepress_after_start_form_fields' ); ?>

					<label>
							<?php _e( 'Username', CoursePress::TD ); ?>:
							<input type="text" name="log" value="<?php echo( isset( $_POST['log'] ) ? $_POST['log'] : '' ); ?>"/>
						</label>

						<label>
							<?php _e( 'Password', CoursePress::TD ); ?>:
							<input type="password" name="pwd" value="<?php echo( isset( $_POST['pwd'] ) ? $_POST['pwd'] : '' ); ?>"/>
						</label>

					<?php do_action( 'coursepress_form_fields' ); ?>

					<label class="full">
							<?php
							if ( cp_user_can_register() ) {
								printf( __( 'Don\'t have an account? %s%s%s now!', CoursePress::TD ), '<a href="' . $signup_url . '">', __( 'Create an Account', CoursePress::TD ), '</a>' );
							}
							?>
						</label>

						<label class="half-left">
							<a href="<?php echo wp_lostpassword_url(); ?>"><?php _e( 'Forgot Password?', CoursePress::TD ); ?></a>
						</label>
						<label class="half-right">
							<input type="submit" name="wp-submit" id="wp-submit" class="apply-button-enrolled" value="<?php _e( 'Log In', CoursePress::TD ); ?>"><br>
						</label>
						<br clear="all"/>

						<input name="redirect_to" value="<?php echo CoursePress::instance()->get_student_dashboard_slug( true ); ?>" type="hidden">
						<input name="testcookie" value="1" type="hidden">
						<input name="course_signup_login" value="1" type="hidden">
					<?php do_action( 'coursepress_before_end_form_fields' ); ?>
					</form>

				<?php do_action( 'coursepress_after_login_form' ); ?>
				<?php
				break;
		}


		// Return the html in the buffer.
		return $content;
	}

	public static function module_status( $atts ) {
		ob_start();
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'unit_id'   => false,
			'message'   => __( '%d of %d mandatory elements completed.', CoursePress::TD ),
			'format'    => 'true',
		), $atts, 'module_status' ) );

		return '';

		// COMPLETION LOGIC

		$message = sanitize_text_field( $message );
		$format  = sanitize_text_field( $format );
		$format  = 'true' == $format ? true : false;

		if ( $course_id ) {
			$course_id = (int) $course_id;
		}
		$unit_id = (int) $unit_id;

		if ( empty( $unit_id ) ) {
			return '';
		}

		$criteria = Unit::get_module_completion_data( $unit_id );

		$unit_status                    = Unit::get_unit_availability_status( $unit_id );
		$unit_available                 = Unit::is_unit_available( $unit_id, $unit_status );
		$input_modules_count            = count( $criteria['all_input_ids'] );
		$assessable_input_modules_count = count( $criteria['gradable_modules'] );
		$mandatory_input_elements       = count( $criteria['mandatory_modules'] );
		$mandatory_responses            = Student_Completion::get_mandatory_steps_completed( get_current_user_id(), $course_id, $unit_id );
		//			$all_responses					 = do_shortcode( '[course_unit_details field="student_module_responses"]' );
		// $is_unit_available				 = do_shortcode( '[course_unit_details field="is_unit_available"]' );
		//			$input_modules_count			 = do_shortcode( '[course_unit_details field="input_modules_count"]' );
		//			$assessable_input_modules_count	 = do_shortcode( '[course_unit_details field="assessable_input_modules_count"]' );
		//			$mandatory_input_elements		 = do_shortcode( '[course_unit_details field="mandatory_input_modules_count"]' );
		//			$mandatory_responses			 = do_shortcode( '[course_unit_details field="student_module_responses" additional="mandatory"]' );
		//			$all_responses					 = do_shortcode( '[course_unit_details field="student_module_responses"]' );

		$unit         = new Unit( $unit_id );
		$unit->status = $unit_status;

		if ( $input_modules_count > 0 ) {
			?>
			<span class="unit-archive-single-module-status"><?php
				if ( $unit_available ) {
					if ( $mandatory_input_elements > 0 ) {
						echo sprintf( $message, $mandatory_responses, $mandatory_input_elements );
					}
				} else {
					if ( isset( $unit->status ) && $unit->status['mandatory_required']['enabled'] && ! $unit->status['mandatory_required']['result'] && ! $unit->status['completion_required']['enabled'] ) {
						esc_html_e( 'All mandatory answers are required in previous unit.', CoursePress::TD );
					} elseif ( isset( $unit->status ) && $unit->status['completion_required']['enabled'] && ! $unit->status['completion_required']['result'] ) {
						esc_html_e( 'Previous unit must be completed successfully.', CoursePress::TD );
					}
					if ( isset( $unit->status ) && ! $unit->status['date_restriction']['result'] ) {
						echo __( 'Available', CoursePress::TD ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( do_shortcode( '[course_unit_details field="unit_availability"]' ) ) );
					}
				}
				?></span>
		<?php } else { ?>
			<span class="unit-archive-single-module-status"><?php
				if ( $unit_available ) {
					//						 _e('Read-only');
				} else {
					if ( isset( $unit->status ) && $unit->status['mandatory_required']['enabled'] && ! $unit->status['mandatory_required']['result'] && ! $unit->status['completion_required']['enabled'] ) {
						esc_html_e( 'All mandatory answers are required in previous unit.', CoursePress::TD );
					} elseif ( isset( $unit->status ) && $unit->status['completion_required']['enabled'] && ! $unit->status['completion_required']['result'] ) {
						esc_html_e( 'Previous unit must be completed successfully.', CoursePress::TD );
					}
					if ( isset( $unit->status ) && ! empty( $unit->status ) && ! $unit->status['date_restriction']['result'] ) {
						echo __( 'Available', CoursePress::TD ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( do_shortcode( '[course_unit_details field="unit_availability"]' ) ) );
					}
				}
				?></span>
		<?php
		}
		$content = ob_get_clean();

		return $content;
	}

	public static function student_workbook_table( $args ) {
		ob_start();
		extract( shortcode_atts(
			array(
				'module_column_title'               => __( 'Element', CoursePress::TD ),
				'title_column_title'                => __( 'Title', CoursePress::TD ),
				'submission_date_column_title'      => __( 'Submitted', CoursePress::TD ),
				'response_column_title'             => __( 'Answer', CoursePress::TD ),
				'grade_column_title'                => __( 'Grade', CoursePress::TD ),
				'comment_column_title'              => __( 'Comment', CoursePress::TD ),
				'module_response_description_label' => __( 'Description', CoursePress::TD ),
				'comment_label'                     => __( 'Comment', CoursePress::TD ),
				'view_link_label'                   => __( 'View', CoursePress::TD ),
				'view_link_class'                   => 'assessment-view-response-link button button-units',
				'comment_link_class'                => 'assessment-view-response-link button button-units',
				'pending_grade_label'               => __( 'Pending', CoursePress::TD ),
				'unit_unread_label'                 => __( 'Unit Unread', CoursePress::TD ),
				'unit_read_label'                   => __( 'Unit Read', CoursePress::TD ),
				'single_correct_label'              => __( 'Correct', CoursePress::TD ),
				'single_incorrect_label'            => __( 'Incorrect', CoursePress::TD ),
				'non_assessable_label'              => __( '**' ),
				'table_class'                       => 'widefat shadow-table assessment-archive-table',
				'table_labels_th_class'             => 'manage-column'
			)
			, $args ) );

		$module_column_title               = sanitize_text_field( $module_column_title );
		$title_column_title                = sanitize_text_field( $title_column_title );
		$submission_date_column_title      = sanitize_text_field( $submission_date_column_title );
		$response_column_title             = sanitize_text_field( $response_column_title );
		$grade_column_title                = sanitize_text_field( $grade_column_title );
		$comment_column_title              = sanitize_text_field( $comment_column_title );
		$module_response_description_label = sanitize_text_field( $module_response_description_label );
		$comment_label                     = sanitize_text_field( $comment_label );
		$view_link_label                   = sanitize_text_field( $view_link_label );
		$view_link_class                   = sanitize_html_class( $view_link_class );
		$comment_link_class                = sanitize_html_class( $comment_link_class );
		$pending_grade_label               = sanitize_text_field( $pending_grade_label );
		$unit_unread_label                 = sanitize_text_field( $unit_unread_label );
		$unit_read_label                   = sanitize_text_field( $unit_read_label );
		$non_assessable_label              = sanitize_text_field( $non_assessable_label );
		$table_class                       = sanitize_html_class( $table_class );
		$table_labels_th_class             = sanitize_html_class( $table_labels_th_class );
		$single_correct_label              = sanitize_text_field( $single_correct_label );
		$single_incorrect_label            = sanitize_text_field( $single_incorrect_label );

		$columns = array(
			// "module" => $module_column_title,
			"title"           => $title_column_title,
			"submission_date" => $submission_date_column_title,
			"response"        => $response_column_title,
			"grade"           => $grade_column_title,
			"comment"         => $comment_column_title,
		);


		$col_sizes = array(
			// '15',
			// 				'30',
			'45',
			'15',
			'10',
			'13',
			'5'
		);
		?>
		<table cellspacing="0" class="<?php echo $table_class; ?>">
				<thead>
					<tr>
						<?php
						$n = 0;
						foreach ( $columns as $key => $col ) {
							?>
							<th class="<?php echo $table_labels_th_class; ?> column-<?php echo $key; ?>" width="<?php echo $col_sizes[ $n ] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
							<?php
							$n ++;
						}
						?>
					</tr>
				</thead>

			<?php
			$user_object = new Student( get_current_user_ID() );

			$modules = Unit_Module::get_modules( get_the_ID() );

			$input_modules_count = 0;

			foreach ( $modules as $mod ) {
				$class_name = $mod->module_type;
				if ( class_exists( $class_name ) ) {
					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						$input_modules_count ++;
					}
				}
			}

			$current_row = 0;
			$style       = '';
			foreach ( $modules as $mod ) {
				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {

					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						$response         = call_user_func( $class_name . '::get_response', $user_object->ID, $mod->ID );
						$visibility_class = ( count( $response ) >= 1 ? '' : 'less_visible_row' );

						if ( count( $response ) >= 1 ) {
							$grade_data = Unit_Module::get_response_grade( $response->ID );
						}

						if ( isset( $_GET['ungraded'] ) && $_GET['ungraded'] == 'yes' ) {
							if ( count( $response ) >= 1 && ! $grade_data ) {
								$general_col_visibility = true;
							} else {
								$general_col_visibility = false;
							}
						} else {
							$general_col_visibility = true;
						}

						$style = ( isset( $style ) && 'alternate' == $style ) ? '' : ' alternate';
						?>
						<tr id='user-<?php echo $user_object->ID; ?>' class="<?php
						echo $style;
						echo 'row-' . $current_row;
						?>">

								<?php
								if ( $general_col_visibility ) {
									?>

									<td class="<?php echo $style . ' ' . $visibility_class; ?>">
										<?php echo $mod->post_title; ?>
									</td>

									<td class="<?php echo $style . ' ' . $visibility_class; ?>">
										<?php echo( count( $response ) >= 1 ? date_i18n( 'M d, Y', strtotime( $response->post_date ) ) : __( 'Not submitted', CoursePress::TD ) ); ?>
									</td>

									<td class="<?php echo $style . ' ' . $visibility_class; ?>">
										<?php
										if ( count( $response ) >= 1 ) {
											?>
											<div id="response_<?php echo $response->ID; ?>" style="display:none;">
												<?php if ( isset( $mod->post_content ) && $mod->post_content !== '' ) { ?>
													<div class="module_response_description">
														<label><?php echo $module_response_description_label; ?></label>
														<?php echo $mod->post_content; ?>
													</div>
												<?php } ?>
												<?php echo call_user_func( $class_name . '::get_response_form', get_current_user_ID(), $mod->ID ); ?>

												<?php
												if ( is_object( $response ) && ! empty( $response ) ) {

													$comment = Unit_Module::get_response_comment( $response->ID );
													if ( ! empty( $comment ) ) {
														?>
														<label class="comment_label"><?php echo $comment_label; ?></label>
														<div class="response_comment_front"><?php echo $comment; ?></div>
													<?php
													}
												}
												?>
											</div>

											<a class="<?php echo sanitize_html_class( $view_link_class ); ?> thickbox" href="#TB_inline?width=500&height=300&inlineId=response_<?php echo $response->ID; ?>"><?php echo sanitize_html_class( $view_link_label ); ?></a>

										<?php
										} else {
											echo '-';
										}
										?>
									</td>

									<td class="<?php echo $style . ' ' . $visibility_class; ?>">
										<?php
										if ( isset( $grade_data ) ) {
											$grade           = $grade_data['grade'];
											$instructor_id   = $grade_data['instructor'];
											$instructor_name = get_userdata( $instructor_id );
											$grade_time      = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $grade_data['time'] );
										}
										if ( count( $response ) >= 1 ) {

											if ( isset( $grade_data ) ) {
												if ( get_post_meta( $mod->ID, 'gradable_answer', true ) == 'no' ) {
													echo $non_assessable_label;
												} else {
													if ( 'radio_input_module' == $class_name ) {
														if ( 100 == $grade ) {
															echo $single_correct_label;
														} else {
															echo $single_incorrect_label;
														}
													} else {
														echo $grade . '%';
													}
												}
											} else {
												if ( get_post_meta( $mod->ID, 'gradable_answer', true ) == 'no' ) {
													echo $non_assessable_label;
												} else {
													echo $pending_grade_label;
												}
											}
										} else {
											echo '-';
										}
										?>
									</td>

									<td class="<?php echo $style . ' ' . $visibility_class; ?> td-center">
										<?php
										if ( ! empty( $response ) ) {

											$comment = Unit_Module::get_response_comment( $response->ID );
											if ( ! empty( $comment ) ) {
												?>
												<a alt="<?php echo strip_tags( $comment ); ?>" title="<?php echo strip_tags( $comment ); ?>" class="<?php echo $comment_link_class; ?> thickbox" href="#TB_inline?width=500&height=300&inlineId=response_<?php echo $response->ID; ?>"><i class="fa fa-comment"></i></a>
											<?php
											}
										} else {
											echo '<i class="fa fa-comment-o"></i>';
										}
										?>
									</td>
								<?php }//general col visibility                          ?>
							</tr>
						<?php
						$current_row ++;
					}
				}
			}


			if ( ! isset( $input_modules_count ) || isset( $input_modules_count ) && $input_modules_count == 0 ) {
				?>
				<tr>
						<td colspan="7">
							<?php
							$unit_grade = do_shortcode( '[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]' );
							_e( '0 input elements in the selected unit.', CoursePress::TD );
							?>
							<?php
							if ( $unit_grade == 0 ) {
								echo $unit_unread_label;
							} else {
								echo $unit_read_label;
							}
							?>
						</td>
					</tr>
			<?php
			}
			?>
			<?php if ( 0 < $current_row ) : ?>
				<tfoot><tr><td colspan="6">** <?php _e( 'Non-assessable elements.', CoursePress::TD ); ?></td></tr></tfoot>
			<?php endif; ?>
			</table>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	public static function instance( $instance = null ) {
		if ( ! $instance || 'CoursePress_Shortcodes' != get_class( $instance ) ) {
			if ( is_null( self::$instance ) ) {
				self::$instance = new CoursePress_Shortcodes();
			}
		} else {
			if ( is_null( self::$instance ) ) {
				self::$instance = $instance;
			}
		}

		return self::$instance;
	}

}

