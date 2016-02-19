<?php

class CoursePress_Data_Shortcodes {

	/**
	 * @todo: Activate the commented shortcodes below and make sure that they work!
	 */
	public static function init() {
		// add_shortcode( 'courses_urls', array( __CLASS__, 'courses_urls' ) );
		// add_shortcode( 'course_units', array( __CLASS__, 'course_units' ) );
		// add_shortcode( 'course_units_loop', array( __CLASS__, 'course_units_loop' ) );
		// add_shortcode( 'course_notifications_loop', array( __CLASS__, 'course_notifications_loop' ) );
		// add_shortcode( 'courses_loop', array( __CLASS__, 'courses_loop' ) );
		// add_shortcode( 'course_discussion_loop', array( __CLASS__, 'course_discussion_loop' ) );
		// add_shortcode( 'course_unit_single', array( __CLASS__, 'course_unit_single' ) );
		add_shortcode( 'course_unit_details', array( __CLASS__, 'course_unit_details' ) );
		add_shortcode( 'course_unit_archive_submenu', array( __CLASS__, 'course_unit_archive_submenu' ) );
		add_shortcode( 'course_unit_submenu', array( __CLASS__, 'course_unit_submenu' ) );
		// add_shortcode( 'course_breadcrumbs', array( __CLASS__, 'course_breadcrumbs' ) );
		add_shortcode( 'course_discussion', array( __CLASS__, 'course_discussion' ) );
		// add_shortcode( 'get_parent_course_id', array( __CLASS__, 'get_parent_course_id' ) );
		// add_shortcode( 'units_dropdown', array( __CLASS__, 'units_dropdown' ) );
		add_shortcode( 'course_list', array( __CLASS__, 'course_list' ) );
		// add_shortcode( 'course_calendar', array( __CLASS__, 'course_calendar' ) );
		add_shortcode( 'course_featured', array( __CLASS__, 'course_featured' ) );
		add_shortcode( 'course_structure', array( __CLASS__, 'course_structure' ) );
		add_shortcode( 'module_status', array( __CLASS__, 'module_status' ) );

		//-- Sub-shortcodes.
		add_shortcode( 'course_join_button', array( __CLASS__, 'course_join_button' ) );
		add_shortcode( 'course_random', array( __CLASS__, 'course_random' ) );

		// -- Other shortcodes.
		// add_shortcode( 'unit_discussion', array( __CLASS__, 'unit_discussion' ) );

		// -- Page Shortcodes.
		if ( ! CoursePress_Data_Capabilities::is_wpmudev()
			&& ! CoursePress_Data_Capabilities::is_campus()
			&& ! apply_filters( 'coursepress_custom_signup_ignore', false )
		) {
			add_shortcode( 'course_signup', array( __CLASS__, 'course_signup' ) );
		}
		add_shortcode( 'course_signup_form', array( __CLASS__, 'course_signup_form' ) );
		// add_shortcode( 'cp_pages', array( __CLASS__, 'cp_pages' ) );

		add_shortcode( 'unit_archive_list', array( __CLASS__, 'unit_archive_list' ) );
		add_shortcode( 'course_social_links', array( __CLASS__, 'course_social_links' ) );

		// -- Messaging shortcodes.
		// add_shortcode( 'messaging_submenu', array( __CLASS__, 'messaging_submenu' ) );


		CoursePress_Data_Shortcodes_Course::init();
		CoursePress_Data_Shortcodes_Instructor::init();
		CoursePress_Data_Shortcodes_Student::init();
		CoursePress_Data_Shortcodes_Template::init();
	}


	/**
	 *
	 * COURSE DETAILS SHORTCODES
	 * =========================
	 *
	 */

	/**
	 * Shows the course join button.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_join_button( $atts ) {
		global $coursepress, $enrollment_process_url, $signup_url;

		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'course_full_text' => __( 'Course Full', 'CP_TD' ),
			'course_expired_text' => __( 'Not available', 'CP_TD' ),
			'enrollment_finished_text' => __( 'Enrollments Finished', 'CP_TD' ),
			'enrollment_closed_text' => __( 'Enrollments Closed', 'CP_TD' ),
			'enroll_text' => __( 'Enroll now', 'CP_TD' ),
			'signup_text' => __( 'Signup!', 'CP_TD' ),
			'details_text' => __( 'Details', 'CP_TD' ),
			'prerequisite_text' => __( 'Pre-requisite Required', 'CP_TD' ),
			'passcode_text' => __( 'Passcode Required', 'CP_TD' ),
			'not_started_text' => __( 'Not Available', 'CP_TD' ),
			'access_text' => __( 'Start Learning', 'CP_TD' ),
			'continue_learning_text' => __( 'Continue Learning', 'CP_TD' ),
			'instructor_text' => __( 'Access Course', 'CP_TD' ),
			'list_page' => false,
			'class' => '',
		), $atts, 'course_join_button' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}

		$list_page = sanitize_text_field( $list_page );
		$list_page = cp_is_true( $list_page );
		$class = sanitize_html_class( $class );

		$course = get_post( $course_id );

		$course->enroll_type = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type' );
		$course->course_start_date = CoursePress_Data_Course::get_setting( $course_id, 'course_start_date' );
		$course->course_end_date = CoursePress_Data_Course::get_setting( $course_id, 'course_end_date' );
		$course->enrollment_start_date = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_start_date' );
		$course->enrollment_end_date = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_end_date' );
		$course->open_ended_course = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended' ) );
		$course->open_ended_enrollment = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'enrollment_open_ended' ) );
		$course->prerequisite = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_prerequisite' );
		$course->is_paid = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'payment_paid_course' ) );
		$course->course_started = strtotime( $course->course_start_date ) <= current_time( 'timestamp', 0 ) ? true : false;
		$course->enrollment_started = strtotime( $course->enrollment_start_date ) <= current_time( 'timestamp', 0 ) ? true : false;
		$course->course_expired = strtotime( $course->course_end_date ) < current_time( 'timestamp', 0 ) ? true : false;
		$course->enrollment_expired = strtotime( $course->enrollment_end_date ) < current_time( 'timestamp', 0 ) ? true : false;
		$course->full = CoursePress_Data_Course::is_full( $course_id );

		$button = '';
		$button_option = '';
		$button_url = $enrollment_process_url;
		$is_form = false;

		$student_enrolled = false;
		$student_id = false;
		$is_instructor = false;
		if ( is_user_logged_in() ) {
			$student_id = get_current_user_id();
			$student_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
			$is_instructor = CoursePress_Data_Instructor::is_assigned_to_course( $course_id, $student_id );
		}

		$is_single = CoursePress_Helper_Utility::$is_singular;

		$buttons = apply_filters( 'coursepress_course_enrollment_button_options', array(
			'full' => array(
				'label' => sanitize_text_field( $course_full_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-full ' . $class,
				),
				'type' => 'label',
			),
			'expired' => array(
				'label' => sanitize_text_field( $course_expired_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-finished ' . $class,
				),
				'type' => 'label',
			),
			'enrollment_finished' => array(
				'label' => sanitize_text_field( $enrollment_finished_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-enrollment-finished ' . $class,
				),
				'type' => 'label',
			),
			'enrollment_closed' => array(
				'label' => sanitize_text_field( $enrollment_closed_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-enrollment-closed ' . $class,
				),
				'type' => 'label',
			),
			'enroll' => array(
				'label' => sanitize_text_field( $enroll_text ),
				'attr' => array(
					'class' => 'apply-button enroll ' . $class,
					'data-link' => esc_url( $signup_url . '?course_id=' . $course_id ),
					'data-course-id' => $course_id,
				),
				'type' => 'form_button',
			),
			'signup' => array(
				'label' => sanitize_text_field( $signup_text ),
				'attr' => array(
					'class' => 'apply-button signup ' . $class,
					'data-link-old' => esc_url( $signup_url . '?course_id=' . $course_id ),
					'data-course-id' => $course_id,
				),
				'type' => 'form_button',
			),
			'details' => array(
				'label' => sanitize_text_field( $details_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-details ' . $class,
					'data-link' => esc_url( get_permalink( $course_id ) ),
				),
				'type' => 'button',
			),
			'prerequisite' => array(
				'label' => sanitize_text_field( $prerequisite_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-prerequisite ' . $class,
				),
				'type' => 'label',
			),
			'passcode' => array(
				'label' => sanitize_text_field( $passcode_text ),
				'button_pre' => '<div class="passcode-box"><label>' . esc_html( $passcode_text ) . ' <input type="password" name="passcode" /></label></div>',
				'attr' => array(
					'class' => 'apply-button apply-button-passcode ' . $class,
				),
				'type' => 'form_submit',
			),
			'not_started' => array(
				'label' => sanitize_text_field( $not_started_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-not-started  ' . $class,
				),
				'type' => 'label',
			),
			'access' => array(
				'label' => ! $is_instructor ? sanitize_text_field( $access_text ) : sanitize_text_field( $instructor_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-enrolled apply-button-first-time ' . $class,
					'data-link' => esc_url( trailingslashit( get_permalink( $course_id ) ) . trailingslashit( CoursePress_Core::get_setting( 'slugs/units', 'units' ) ) ),
				),
				'type' => 'button',
			),
			'continue' => array(
				'label' => ! $is_instructor ? sanitize_text_field( $continue_learning_text ) : sanitize_text_field( $instructor_text ),
				'attr' => array(
					'class' => 'apply-button apply-button-enrolled ' . $class,
					'data-link' => esc_url( trailingslashit( get_permalink( $course_id ) ) . trailingslashit( CoursePress_Core::get_setting( 'slugs/units', 'units' ) ) ),
				),
				'type' => 'button',
			),
		), $course_id );

		if ( CoursePress_Data_Capabilities::is_wpmudev() ) {
			unset( $buttons['signup']['attr']['data-type'] );
		}

		// Determine the button option.
		if ( ! $student_enrolled && ! $is_instructor ) {
			// For vistors and non-enrolled students.
			if ( $course->full ) {
				// COURSE FULL.
				$button_option = 'full';
			} elseif ( $course->course_expired && ! $course->open_ended_course ) {
				// COURSE EXPIRED.
				$button_option = 'expired';
			} elseif ( ! $course->enrollment_started && ! $course->open_ended_enrollment && ! $course->enrollment_expired ) {
				// ENROLMENTS NOT STARTED (CLOSED).
				$button_option = 'enrollment_closed';
			} elseif ( $course->enrollment_expired && ! $course->open_ended_enrollment ) {
				// ENROLMENTS FINISHED.
				$button_option = 'enrollment_finished';
			} elseif ( 'prerequisite' == $course->enroll_type ) {
				// PREREQUISITE REQUIRED.
				if ( ! empty( $student_id ) ) {
					$pre_course = ! empty( $course->prerequisite ) ? $course->prerequisite : false;
					$enrolled_pre = false;

					$prerequisites = maybe_unserialize( $pre_course );
					$prerequisites = empty( $prerequisites ) ? array() : $prerequisites;

					$completed = 0;
					$all_complete = false;

					foreach ( $prerequisites as $prerequisite ) {
						if ( CoursePress_Data_Course::student_enrolled( $student_id, $prerequisite ) && CoursePress_Data_Course::student_completed( $student_id, $course_id ) ) {
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

			// Even if user is signed-in, you might wan't to restrict and force an upgrade.
			// Make sure you know what you're doing and that you don't block everyone from enrolling.
			$force_signup = apply_filters( 'coursepress_course_enrollment_force_registration', false );

			if ( ( empty( $student_id ) && $user_can_register && empty( $button_option ) ) || $force_signup ) {
				// If the user is allowed to signup, let them sign up
				$button_option = 'signup';
			} elseif ( ! empty( $student_id ) && empty( $button_option ) ) {

				// If the user is not enrolled, then see if they can enroll.
				switch ( $course->enroll_type ) {
					case 'anyone':
					case 'registered':
						$button_option = 'enroll';
						break;

					case 'passcode':
						$button_option = 'passcode';
						break;

					case 'prerequisite':
						$pre_course = ! empty( $course->prerequisite ) ? $course->prerequisite : false;
						$enrolled_pre = false;

						$prerequisites = maybe_unserialize( $pre_course );
						$prerequisites = empty( $prerequisites ) ? array() : $prerequisites;

						$completed = 0;
						$all_complete = false;

						foreach ( $prerequisites as $prerequisite ) {
							if ( CoursePress_Data_Course::student_enrolled( $student_id, $prerequisite ) && CoursePress_Data_Course::student_completed( $student_id, $course_id ) ) {
								$completed += 1;
							}
						}
						$all_complete = $completed === count( $prerequisites );

						if ( $all_complete ) {
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

			// COMPLETION LOGIX.
			//$progress = Student_Completion::calculate_course_completion( get_current_user_id(), $course_id, false );
			$progress = 0;

			if ( $course->course_expired && ! $course->open_ended_course ) {
				// COURSE EXPIRED
				$button_option = 'expired';
			} elseif ( ! $course->course_started && ! $course->open_ended_course ) {
				// COURSE HASN'T STARTED
				$button_option = 'not_started';
			} elseif ( ! $is_single && false === strpos( $_SERVER['REQUEST_URI'], CoursePress_Core::get_setting( 'slugs/student_dashboard', 'courses-dashboard' ) ) ) {
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

		// Make the option extendable.
		$button_option = apply_filters( 'coursepress_course_enrollment_button_option', $button_option );

		// Prepare the button.
		if ( ( ! $is_single && ! is_page() ) || $list_page ) {
			$button_url = get_permalink( $course_id );
			$button = '<button data-link="' . esc_url( $button_url ) . '" class="apply-button apply-button-details ' . esc_attr( $class ) . '">' . esc_html( $details_text ) . '</button>';
		} else {
			//$button = apply_filters( 'coursepress_enroll_button_content', '', $course );
			if ( empty( $button_option ) || ( 'manually' == $course->enroll_type && ! ( 'access' == $button_option || 'continue' == $button_option ) ) ) {
				return apply_filters( 'coursepress_enroll_button', $button, $course_id, $student_id );
			}

			$button_attributes = '';
			foreach ( $buttons[ $button_option ]['attr'] as $key => $value ) {
				$button_attributes .= $key . '="' . esc_attr( $value ) . '" ';
			}
			$button_pre = isset( $buttons[ $button_option ]['button_pre'] ) ? $buttons[ $button_option ]['button_pre'] : '';
			$button_post = isset( $buttons[ $button_option ]['button_post'] ) ? $buttons[ $button_option ]['button_post'] : '';

			switch ( $buttons[ $button_option ]['type'] ) {
				case 'label':
					$button = '<span ' . $button_attributes . '>' . esc_html( $buttons[ $button_option ]['label'] ) . '</span>';
					break;

				case 'form_button':
					$button = '<button ' . $button_attributes . '>' . esc_html( $buttons[ $button_option ]['label'] ) . '</button>';
					$is_form = true;
					break;

				case 'form_submit':
					$button = '<input type="submit" ' . $button_attributes . ' value="' . esc_attr( $buttons[ $button_option ]['label'] ) . '" />';
					$is_form = true;
					break;

				case 'button':
					$button = '<button ' . $button_attributes . '>' . esc_html( $buttons[ $button_option ]['label'] ) . '</button>';
					break;
			}

			$button = $button_pre . $button . $button_post;
		}

		// Wrap button in form if needed.
		if ( $is_form ) {
			$button = '<form name="enrollment-process" method="post" action="' . $button_url . '">' . $button;
			$button .= wp_nonce_field( 'enrollment_process' );
			$button .= '<input type="hidden" name="course_id" value="' . $course_id . '" />';
			$button .= '</form>';
		}

		return apply_filters( 'coursepress_enroll_button', $button, $course_id, $student_id );
	}

	/**
	 * Display course structure.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_structure( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'free_text' => __( 'Preview', 'CP_TD' ),
			'free_show' => 'true',
			'free_class' => 'free',
			'show_title' => 'no',
			'show_label' => 'no',
			'label_delimeter' => ': ',
			'label_tag' => 'h2',
			'show_divider' => 'yes',
			'show_estimates' => 'no',
			'label' => __( 'Course Structure', 'CP_TD' ),
			'class' => '',
			'deep' => false,
		), $atts, 'course_structure' ) );

		$course_id = (int) $course_id;
		$free_text = sanitize_text_field( $free_text );
		$show_title = cp_is_true( sanitize_text_field( $show_title ) );
		$show_label = cp_is_true( sanitize_text_field( $show_label ) );
		$free_show = cp_is_true( sanitize_text_field( $free_show ) );
		$show_estimates = cp_is_true( sanitize_text_field( $show_estimates ) );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$label_tag = sanitize_html_class( $label_tag );
		$show_divider = cp_is_true( sanitize_text_field( $show_divider ) );
		$label = sanitize_text_field( $label );
		$title = ! empty( $label ) ? '<h3 class="section-title">' . esc_html( $label ) . '</h3>' : $label;
		$class = sanitize_html_class( $class );
		$deep = cp_is_true( sanitize_text_field( $deep ) );

		$content = '';
		if ( empty( $course_id ) ) {
			return $content;
		}

		$structure_visible = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'structure_visible' ) );
		if ( ! $structure_visible ) {
			return $content;
		}

		$time_estimates = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'structure_show_duration' ) );

		$preview = CoursePress_Data_Course::previewability( $course_id );
		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );

		if ( ! $visibility['has_visible'] ) {
			return $content;
		}

		$student_id = is_user_logged_in() ? get_current_user_id() : 0;
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$student_progress = $enrolled ? CoursePress_Data_Student::get_completion_data( $student_id, $course_id ) : false;

		$units = CoursePress_Data_Course::get_units_with_modules( $course_id, array( 'publish' ) );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		$enrolled_class = $enrolled ? 'enrolled' : '';
		$content .= '<div class="course-structure-block course-structure-block-' . $course_id . ' ' . $enrolled_class . '">';

		$content .= $title;

		$course_slug = get_post_field( 'post_name', $course_id );

		$content .= '<ul class="tree">';
		$last_unit = 0;

		foreach ( $units as $unit_id => $unit ) {
			if ( ! isset( $visibility['structure'][ $unit_id ] ) || empty( $visibility['structure'][ $unit_id ] ) ) {
				continue;
			}

			$unit_link = trailingslashit( CoursePress_Core::get_slug( 'courses', true ) ) . $course_slug . '/' . CoursePress_Core::get_slug( 'unit' ) . '/' . $unit['unit']->post_name;

			$estimation = CoursePress_Data_Unit::get_time_estimation( $unit_id, $units );

			$unit_title = $enrolled ? '<a href="' . esc_url( $unit_link ) . '">' . esc_html( $unit['unit']->post_title ) . '</a>' : esc_html( $unit['unit']->post_title );

			$content .= '<li class="unit">';

			$content .= '<div class="unit-title-wrapper">';
			$content .= '<div class="unit-title">' . $unit_title . '</div>';

			if ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && ! is_array( $preview['structure'][ $unit_id ] ) ) {
				if ( empty( $last_unit ) ) {
					$unit_available = true;
				} else {
					$unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $unit_id, $last_unit );
				}
				if ( $unit_available ) {
					$content .= '<div class="unit-link"><a href="' . esc_url( $unit_link ) . '">' . $free_text . '</a></div>';
				}
			}
			$content .= '</div>';

			if ( ! isset( $unit['pages'] ) ) {
				$unit['pages'] = array();
			}

			$content .= '<ul>';
			$count = 0;
			ksort( $unit['pages'] );

			foreach ( $unit['pages'] as $key => $page ) {

				$show_link = isset( $visibility['structure'][ $unit_id ] ) && ! empty( $visibility['structure'][ $unit_id ] ) && ! is_array( $visibility['structure'][ $unit_id ] );
				$show_link = $show_link ? $show_link : isset( $visibility['structure'][ $unit_id ][ $key ] );
				if ( ! $show_link ) {
					continue;
				}

				$count += 1;

				$page_link = trailingslashit( $unit_link ) . 'page/' . $key;
				$page_title = empty( $page['title'] ) ? sprintf( __( 'Untitled Page %s', 'CP_TD' ), $count ) : $page['title'];
				$page_title = $enrolled ? '<a href="' . esc_url( $page_link ) . '">' . esc_html( $page_title ) . '</a>' : esc_html( $page_title );

				$content .= '<li class="unit-page">';

				$preview_class = ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && is_array( $preview['structure'][ $unit_id ] ) ) ? $free_class : '';
				$content .= '<div class="unit-page-title-wrapper ' . $preview_class . '">';
				$content .= '<div class="unit-page-title">' . $page_title . '</div>';
				if ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && is_array( $preview['structure'][ $unit_id ] ) ) {
					$content .= '<div class="unit-page-link"><a href="' . esc_url( $page_link ) . '">' . $free_text . '</a></div>';
				}

				if ( $time_estimates ) {
					$page_estimate = ! empty( $estimation['pages'][ $key ]['components']['hours'] ) ? str_pad( $estimation['pages'][ $key ]['components']['hours'], 2, '0', STR_PAD_LEFT ) . ':' : '';
					$page_estimate = isset( $estimation['pages'][ $key ]['components']['minutes'] ) ? $page_estimate . str_pad( $estimation['pages'][ $key ]['components']['minutes'], 2, '0', STR_PAD_LEFT ) . ':' : $page_estimate;
					$page_estimate = isset( $estimation['pages'][ $key ]['components']['seconds'] ) ? $page_estimate . str_pad( $estimation['pages'][ $key ]['components']['seconds'], 2, '0', STR_PAD_LEFT ) : '';
					$page_estimate = apply_filters( 'coursepress_page_estimation', $page_estimate, $estimation['pages'][ $key ] );
					$content .= '<div class="unit-page-estimate">' . esc_html( $page_estimate ) . '</div>';
				}

				$content .= '</div>';

				// Add Module Level
				$structure_level = CoursePress_Data_Course::get_setting( $course_id, 'structure_level', 'unit' );
				if ( $deep || 'section' === $structure_level || 'unit' === $structure_level ) {
					$visibility_count = 0;
					$list_content = '<ul class="page-modules">';

					foreach ( $page['modules'] as $m_key => $module ) {
						if ( ! empty( $visibility['structure'][ $unit_id ][ $key ][ $m_key ] ) ) {
							$list_content .= '
						<li>';

							$preview_class = ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ][ $m_key ] ) ) ? $free_class : '';
							$type_class = get_post_meta( $m_key, 'module_type', true );

							$attributes = CoursePress_Data_Module::attributes( $m_key );
							$attributes['course_id'] = $course_id;

							// Get completion states
							$module_seen = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/modules_seen/' . $m_key );
							$module_passed = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $m_key );
							$module_answered = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $m_key );

							$seen_class = isset( $module_seen ) && ! empty( $module_seen ) ? 'module-seen' : '';
							$passed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] ? 'module-passed' : '';
							$answered_class = isset( $module_answered ) && ! empty( $module_answered ) && $attributes['mandatory'] ? 'not-assesable module-answered' : '';
							$completed_class =  isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
							$completed_class =  empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';

							$list_content .= '
							<div class="unit-page-module-wrapper ' . $preview_class . ' ' . $type_class . ' ' . $passed_class . ' ' . $answered_class . ' ' . $completed_class . ' ' . $seen_class . '">
							';
							$module_link = trailingslashit( $unit_link ) . 'page/' . $key . '#module-' . $m_key;
							$module_title = $module->post_title;
							$module_title = $enrolled ? '<a href="' . esc_url( $module_link ) . '">' . esc_html( $module_title ) . '</a>' : esc_html( $module_title );
							if ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ][ $m_key ] ) ) {
								$list_content .= '<div class="unit-module-preview-link"><a href="' . esc_url( $module_link ) . '">' . $free_text . '</a></div>';
							}

							$visibility_count += 1;
							$list_content .= '
								<div class="module-title">' . $module_title . '</div>
							</div>
						';

							$list_content .= '</li>';
						}
					}
					$list_content .= '</ul>'; // Modules

					if ( ! empty( $visibility_count ) ) {
						$content .= $list_content;
					}
				}

				$content .= '</li>'; // Page Title
			}
			$content .= '</ul>';

			$content .= '</li>'; // Unit

			$last_unit = $unit_id;
		}

		$content .= '</ul>';
		$content .= '</div>';

		return $content;
	}

	/**
	 * Gets the Unit archive as a list
	 *
	 * @since 2.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function unit_archive_list( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'with_modules' => 'true',
			'description' => false,
			'knob_data_width' => '60',
			'knob_data_height' => '60',
			'knob_fg_color' => '#24bde6',
			'knob_bg_color' => '#e0e6eb',
			'knob_data_thickness' => '0.18'
		), $atts, 'unit_archive_list' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}

		$with_modules = cp_is_true( $with_modules );

		$view_mode = CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'normal' );
		$base_link = get_permalink( $course_id );

		$knob_fg_color = sanitize_text_field( $knob_fg_color );
		$knob_bg_color = sanitize_text_field( $knob_bg_color );
		$knob_data_thickness = sanitize_text_field( $knob_data_thickness );
		$knob_data_width = (int) $knob_data_width;
		$knob_data_height = (int) $knob_data_height;

		$student_id = get_current_user_id();
		$instructors = CoursePress_Data_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		$content = '';

		$unit_status = current_user_can( 'manage_options' ) || $is_instructor ? array(
			'publish',
			'draft'
		) : array( 'publish' );
		if ( ! $with_modules ) {
			$units = CoursePress_Data_Course::get_units( CoursePress_Helper_Utility::the_course( true ), $unit_status );
		} else {
			$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $unit_status );
			$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );
		}

		$content .= '<div class="unit-archive-list-wrapper">';
		$content .= count( $units ) > 0 ? '<ul class="units-archive-list">' : '';
		$counter = 0;

		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$student_progress = $enrolled ? CoursePress_Data_Student::get_completion_data( $student_id, $course_id ) : false;

		foreach ( $units as $unit ) {
			$the_unit = $with_modules ? $unit['unit'] : $unit;
			$unit_id = $the_unit->ID;

			$can_view = CoursePress_Data_Course::can_view_unit( $course_id, $unit_id );

			$previous_unit_id = false;
			if ( $counter == 0 ) {
				$previous_unit = false;
			} else {
				if ( $with_modules ) {
					$keys = array_keys( $units );
					$index = $keys[ $counter - 1 ];
				} else {
					$index = $counter - 1;
				}

				$previous_unit = $with_modules ? $units[ $index ]['unit'] : $units[ $index ];
				$previous_unit_id = $previous_unit->ID;
			}
			$counter += 1;

			if ( ! $can_view ) {
				continue;
			}

			$unit_progress = do_shortcode( '[course_unit_percent course_id="' . $course_id . '" unit_id="' . $unit_id . '" format="true" style="extended" knob_fg_color="' . $knob_fg_color . '" knob_bg_color="' . $knob_bg_color . '" knob_data_thickness="' . $knob_data_thickness . '" knob_data_width="' . $knob_data_width . '" knob_data_height="' . $knob_data_height . '"]' );

			$additional_class = '';
			$additional_li_class = '';

			$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $the_unit, $previous_unit );

			if ( $enrolled && ! $is_unit_available ) {
				$additional_class = 'locked-unit';
				$additional_li_class = 'li-locked-unit';
			}

			if ( ! $enrolled ) {
				// $unit_progress = sprintf( '<div class="course-preview-container">%s</div>', __( 'Preview Only', 'CP_TD' ) );
				$unit_progress = '';
				if ( ! $is_unit_available && ! $can_view ) {
					continue;
				}
			}

			$unit_feature_image = get_post_meta( $unit_id, 'unit_feature_image', true );
			$unit_image = ($unit_feature_image) ? '<div class="circle-thumbnail"><div class="unit-thumbnail"><img src="' . $unit_feature_image . '"" alt="' . $the_unit->post_title . '" /></div></div>' : '';

			$post_name = empty( $the_unit->post_name ) ? $the_unit->ID : $the_unit->post_name;
			$content .= '
				<li class="' . esc_attr( $additional_li_class ) . '">
					<div class="unit-archive-single">
						' . $unit_progress . '
						' . $unit_image . '
						<a class="unit-archive-single-title" href="' . esc_url_raw( get_permalink( CoursePress_Helper_Utility::the_course( true ) ) . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . $post_name ) . '" rel="bookmark">' . $the_unit->post_title . ' ' . ( $the_unit->post_status !== 'publish' && current_user_can( 'manage_options' ) ? esc_html__( ' [DRAFT]', 'CP_TD' ) : '' ) . '</a>';

			if ( $enrolled ) {
				$content .= do_shortcode( '[module_status format="true" unit_id="' . $unit_id . '" previous_unit="' . $previous_unit_id . '"]' );
			}

			if ( $description ) {
				$content .= $the_unit->post_content;
			}

			if ( $with_modules ) {
				$structure_level = CoursePress_Data_Course::get_setting( $course_id, 'structure_level', 'unit' );
				$module_table = '<ul class="unit-archive-module-wrapper">';
				$unit['pages'] = isset( $unit['pages'] ) ? $unit['pages'] : array();

				foreach ( $unit['pages'] as $page_number => $page ) {
					if ( ! CoursePress_Data_Course::can_view_page( $course_id, $unit_id, $page_number ) ) {
						continue;
					}

					$heading_visible = isset( $page['visible'] ) && $page['visible'];
					$module_table .= '<li>';

					if ( $heading_visible ) {
						if ( 'normal' == $view_mode ) {
							$module_table .= '<div class="section-title" data-id="' . $page_number . '">' . ( ! empty( $page['title'] ) ? esc_html( $page['title'] ) : esc_html__( 'Untitled', 'CP_TD' ) ) . '</div>';
						} else {
							$section_link = trailingslashit( $base_link . CoursePress_Core::get_slug( 'units' ) );
							$section_link .= '#section-' . $page_number;
							$module_table .= '<div class="section-title" data-id="' . $page_number . '"><a href="' . $section_link . '">' . ( ! empty( $page['title'] ) ? esc_html( $page['title'] ) : esc_html__( 'Untitled', 'CP_TD' ) ) . '</a></div>';
						}
					}

					$module_table .= '<ul class="module-list">';

					foreach ( $page['modules'] as $module ) {
						$attributes = CoursePress_Data_Module::attributes( $module->ID );
						if ( 'normal' != $view_mode && 'input' == $attributes['mode'] ) {
							continue;
						}

						if ( ! CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $module->ID, $page_number ) ) {
							continue;
						}

						// Get completion states.
						$module_seen = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/modules_seen/' . $module->ID );
						$module_passed = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $module->ID );
						$module_answered = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $module->ID );

						$seen_class = isset( $module_seen ) && ! empty( $module_seen ) ? 'module-seen' : '';
						$passed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] ? 'module-passed' : '';
						$answered_class = isset( $module_answered ) && ! empty( $module_answered ) && $attributes['mandatory'] ? 'not-assesable module-answered' : '';
						$completed_class =  isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
						$completed_class =  empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';

						$type_class = get_post_meta( $module->ID, 'module_type', true );
						$module_table .= '<li class="module ' . $type_class . ' ' . $passed_class . ' ' . $answered_class . ' ' . $completed_class . ' ' . $seen_class . '">';

						$title = ! empty( $module->post_title ) ? esc_html( $module->post_title ) : esc_html__( 'Mod', 'CP_TD' ) . '<br />';

						if ( 'normal' == $view_mode ) {
							$module_table .= '<div class="module-title" data-id="' . $module->ID . '">' . $title . '</div>';
						} else {
							$module_link = trailingslashit( $base_link . CoursePress_Core::get_slug( 'units' ) );
							$module_link .= '#module-' . $module->ID;
							$module_table .= '<div class="module-title" data-id="' . $module->ID . '"><a href="' . $module_link . '">' . $title . '</a></div>';
						}

						$module_table .= '</li>';
					}

					$module_table .= '</ul>';
					$module_table .= '</li>';
				}

				$module_table .= '</ul>';
				$content .= $module_table;
			}

			$content .= '
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
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_featured( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => '',
			'featured_title' => __( 'Featured Course', 'CP_TD' ),
			'button_title' => __( 'Find out more.', 'CP_TD' ),
			'media_type' => '', // Video, image, thumbnail.
			'media_priority' => 'video', // Video, image.
			'class' => '',
		), $atts, 'course_featured' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			return '';
		}

		$featured_title = sanitize_text_field( $featured_title );
		$button_title = sanitize_text_field( $button_title );
		$media_type = sanitize_text_field( $media_type );
		$media_priority = sanitize_text_field( $media_priority );
		$class = sanitize_html_class( $class );

		$course = get_post( $course_id );
		$class = sanitize_html_class( $class );

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
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_calendar( $atts ) {
		global $post;

		extract( shortcode_atts( array(
			'course_id' => in_the_loop() ? get_the_ID() : false,
			'month' => false,
			'year' => false,
			'pre' => __( 'Â« Previous', 'CP_TD' ),
			'next' => __( 'Next Â»', 'CP_TD' ),
			'date_indicator' => 'indicator_light_block',
		), $atts, 'course_calendar' ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}

		$month = sanitize_text_field( $month );
		$month = 'true' == $month ? true : false;
		$year = sanitize_text_field( $year );
		$year = 'true' == $year ? true : false;
		$pre = sanitize_text_field( $pre );
		$next = sanitize_text_field( $next );
		$date_indicator = sanitize_text_field( $date_indicator );

		if ( empty( $course_id ) ) {
			if ( $post && CoursePress_Data_Course::get_post_type_name() == $post->post_type ) {
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
	 * Display course list.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_list( $atts ) {

		$atts = CoursePress_Helper_Utility::sanitize_recursive( shortcode_atts( array(
			'status' => 'publish',
			'instructor' => '', // Note, one or the other
			'instructor_msg' => __( 'The Instructor does not have any courses assigned yet.', 'CP_TD' ),
			'student' => '', // If both student and instructor is specified only student will be used
			'student_msg' => __( 'You are not enrolled in any courses. <a href="%s">See available courses.</a>', 'CP_TD' ),
			'dashboard' => false,
			'context' => '', // <blank>, enrolled, completed
			'limit' => - 1,
			'order' => 'ASC',
			'manage_label' => __( 'Courses you manage', 'CP_TD' ),
			'current_label' => __( 'Current courses', 'CP_TD' ),
			'completed_label' => __( 'Completed courses', 'CP_TD' ),
			'suggested_label' => __( 'Suggested courses', 'CP_TD' ),
			'suggested_msg' => __( 'You are not enrolled in any courses.<br />Here are a few you might like, or <a href="%s">see all available courses.</a>', 'CP_TD' ),
			'show_labels' => false
		), $atts, 'course_page' ) );

		$instructor_list = false;
		$student_list = false;
		$atts['dashboard'] = cp_is_true( $atts['dashboard'] );
		$courses = array();
		$content = '';
		$student = 0;

		if ( ! empty( $atts['instructor'] ) ) {
			$include_ids = array();
			$instructors = explode( ',', $atts['instructor'] );
			if ( ! empty( $instructors ) ) {
				foreach ( $instructors as $ins ) {
					$ins = (int) $ins;
					if ( $ins ) {
						$course_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $ins, $atts['status'] );
						if ( $course_ids ) {
							$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
						}
					}
				}
			} else {
				$instructor = (int) $atts['instructor'];
				if ( $instructor ) {
					$course_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $instructor, $atts['status'] );
					if ( $course_ids ) {
						$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
					}
				}
			}
			$instructor_list = true;
		}

		if ( ! empty( $atts['student'] ) ) {
			$include_ids = array();

			$students = explode( ',', $atts['student'] );
			if ( ! empty( $students ) ) {
				foreach ( $students as $student ) {
					$student = (int) $student;
					if ( $student ) {
						$course_ids = CoursePress_Data_Student::get_enrolled_courses_ids( $student );
						if ( $course_ids ) {
							$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
						}
					}
				}
			} else {
				$student = (int) $atts['student'];
				if ( $student ) {
					$course_ids = CoursePress_Data_Student::get_enrolled_courses_ids( $student );
					if ( $course_ids ) {
						$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
					}
				}
			}

			$student_list = true;
		}

		$post_args = array(
			'order' => $atts['order'],
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $atts['status'],
			'posts_per_page' => (int) $atts['limit']
		);

		if ( ! empty( $include_ids ) ) {
			$post_args = wp_parse_args( array( 'post__in' => $include_ids ), $post_args );
		}

		if ( ( ( $student_list || $instructor_list ) && ! empty( $include_ids ) ) || ( ! $student_list && ! $instructor_list ) ) {
			$courses = get_posts( $post_args );
		}

		$counter = 0;
		foreach ( $courses as $course ) {
			if ( ! $atts['dashboard'] ) {
				$content .= do_shortcode( '[course_list_box course_id="' . $course->ID . '"]' );
				$counter += 1;
			} else {
				if ( $student_list ) {
					$course_url = get_permalink( $course->ID );
					$completed = CoursePress_Data_Student::is_course_complete( $student, $course->ID );

					switch ( $atts['context'] ) {

						case 'enrolled':
							if ( ! $completed ) {
								$content .= do_shortcode( '[course_list_box course_id="' . $course->ID . '" override_button_text="' . esc_attr__( 'Go to Course', 'CP_TD' ) . '" override_button_link="' . esc_url( $course_url ) . '"]' );
								$counter += 1;
							}
							break;

						case 'completed':
							if ( $completed ) {
								$content .= do_shortcode( '[course_list_box course_id="' . $course->ID . '" override_button_text="' . esc_attr__( 'Go to Course', 'CP_TD' ) . '" override_button_link="' . esc_url( $course_url ) . '"]' );
								$counter += 1;
							}
							break;
					}
				} else {
					$edit_page = CoursePress_View_Admin_Course_Edit::$slug;
					$query = sprintf( '?page=%s&action=%s&id=%s', esc_attr( $edit_page ), 'edit', absint( $course->ID ) );
					$course_url = admin_url( 'admin.php' . $query );
					$content .= do_shortcode( '[course_list_box course_id="' . $course->ID . '" override_button_text="' . esc_attr__( 'Manage Course', 'CP_TD' ) . '" override_button_link="' . esc_url( $course_url ) . '"]' );
					$counter += 1;
				}
			}
		}

		$context = $atts['dashboard'] && $instructor_list ? 'manage' : $atts['context'];

		if ( $atts['dashboard'] && ! empty( $counter ) ) {
			$label = '';

			switch ( $context ) {
				case 'enrolled':
					$label = $atts['current_label'];
					break;

				case 'completed':
					$label = $atts['completed_label'];
					break;

				case 'manage':
					$label = $atts['manage_label'];
					break;
			}

			$content = '<div class="dashboard-course-list ' . esc_attr( $context ) . '">' .
						'<h3 class="section-title">' . esc_html( $label ) . '</h3>' .
						$content .
						'</div>';

		} elseif ( $atts['dashboard'] && 'enrolled' === $context ) {

			$label = $atts['suggested_label'];
			$message = sprintf( $atts['suggested_msg'], esc_url( CoursePress_Core::get_slug( 'courses', true ) ) );

			$content = '<div class="dashboard-course-list suggested">' .
						'<h3 class="section-title">' . esc_html( $label ) . '</h3>' .
						'<p>' . $message . '</p>' .
						do_shortcode( '[course_random featured_title="" media_type="image" media_priority="image"]' ) .
						'</div>';

		}

		return $content;
	}

	/**
	 * Shows the course list.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_list_old( $atts ) {
		extract( shortcode_atts( array(
			'status' => 'publish',
			'instructor' => '', // Note, one or the other.
			'instructor_msg' => __( 'The Instructor does not have any courses assigned yet.', 'CP_TD' ),
			'student' => '', // If both student and instructor is specified only student is used.
			'student_msg' => __( 'You have not yet enrolled in a course. Browse courses %s', 'CP_TD' ),
			'two_column' => 'yes',
			'title_column' => 'none',
			'left_class' => '',
			'right_class' => '',
			'course_class' => '',
			'title_link' => 'yes',
			'title_class' => 'course-title',
			'title_tag' => 'h3',
			'course_status' => 'all',
			'list_wrapper_before' => 'div',
			'list_wrapper_before_class' => 'course-list %s',
			'list_wrapper_after' => 'div',
			'show' => 'dates,enrollment_dates,class_size,cost',
			'show_button' => 'yes',
			'show_divider' => 'yes',
			'show_media' => 'false',
			'show_title' => 'yes',
			'media_type' => get_option( 'listings_media_type', 'image' ), // Default, image, video.
			'media_priority' => get_option( 'listings_media_priority', 'image' ), // Image, video.
			'admin_links' => 'false',
			'manage_link_title' => __( 'Manage Course', 'CP_TD' ),
			'finished_link_title' => __( 'View Course', 'CP_TD' ),
			'limit' => - 1,
			'order' => 'ASC',
			'class' => '',
		), $atts, 'course_list' ) );

		$status = sanitize_html_class( $status );
		$instructor = sanitize_text_field( $instructor );
		$instructor_msg = sanitize_text_field( $instructor_msg );
		$student = sanitize_text_field( $student );
		$student_msg = sanitize_text_field( $student_msg );
		$two_column = cp_is_true( sanitize_html_class( $two_column ) );
		$title_column = sanitize_text_field( $title_column );
		$left_class = sanitize_html_class( $left_class );
		$right_class = sanitize_html_class( $right_class );
		$course_class = sanitize_html_class( $course_class );
		$title_link = cp_is_true( sanitize_html_class( $title_link ) );
		$title_class = sanitize_html_class( $title_class );
		$title_tag = sanitize_html_class( $title_tag );
		$course_status = sanitize_text_field( $course_status );
		$list_wrapper_before = sanitize_html_class( $list_wrapper_before );
		$list_wrapper_after = sanitize_html_class( $list_wrapper_after );
		$list_wrapper_before_class = sanitize_html_class( $list_wrapper_before_class );
		$show = sanitize_text_field( $show );
		$show_button = cp_is_true( sanitize_html_class( $show_button ) );
		$show_divider = cp_is_true( sanitize_html_class( $show_divider ) );
		$show_title = cp_is_true( sanitize_html_class( $show_title ) );
		$show_media = cp_is_true( sanitize_html_class( $show_media ) );
		$media_type = ! empty( $media_type ) ? sanitize_text_field( $media_type ) : 'image';
		$media_priority = ! empty( $media_priority ) ? sanitize_text_field( $media_priority ) : 'image';
		$admin_links = sanitize_text_field( $admin_links );
		$admin_links = cp_is_true( sanitize_html_class( $admin_links ) );
		$manage_link_title = sanitize_text_field( $manage_link_title );
		$finished_link_title = sanitize_text_field( $finished_link_title );
		$limit = (int) $limit;
		$order = sanitize_html_class( $order );
		$class = sanitize_html_class( $class );

		$status = 'published' == $status ? 'publish' : $status;

		// Student or instructor ids provided.
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
						$course_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $ins, $status );
						if ( $course_ids ) {
							$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
						}
					}
				}
			} else {
				$instructor = (int) $instructor;
				if ( $instructor ) {
					$course_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $instructor, $status );
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
						$course_ids = CoursePress_Data_Student::get_enrolled_courses_ids( $stud );
						if ( $course_ids ) {
							$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
						}
					}
				}
			} else {
				$student = (int) $student;
				if ( $student ) {
					$student = new Student( $student );
					$course_ids = CoursePress_Data_Student::get_enrolled_courses_ids( $student );
					if ( $course_ids ) {
						$include_ids = array_unique( array_merge( $include_ids, $course_ids ) );
					}
				}
			}
		}

		$post_args = array(
			'order' => $order,
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'meta_key' => 'enroll_type',
			'post_status' => $status,
			'posts_per_page' => $limit,
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

				// COMPLETION LOGIC.
				// $course->completed = Student_Completion::is_course_complete( $student, $course->ID );
				$course->completed = false;
				// Skip if we wanted a completed course but got an incomplete.
				if ( 'completed' == strtolower( $course_status ) && ! $course->completed ) {
					continue;
				}
				// Skip if we wanted an incompleted course but got a completed.
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

			// Add action links if student.
			if ( ! empty( $student ) ) {
				$content .= do_shortcode( '[course_action_links course_id="' . $course->ID . '"]' );
			}

			if ( $two_column ) {
				$content .= '</div>';
			}

			if ( $show_divider ) {
				$content .= '<div class="divider" ></div>';
			}

			$content .= '</div>';  // Course-list-item.
		} // Foreach.

		if ( ( ! $courses || 0 == count( $courses ) ) && ! empty( $instructor ) ) {
			$content .= $instructor_msg;
		}

		if ( ( ! $courses || 0 == count( $courses ) ) && ! empty( $student ) ) {
			$content .= sprintf( $student_msg, '<a href="' . trailingslashit( home_url() . '/' . CoursePress_Core::get_setting( 'slugs/course', 'courses' ) ) . '">' . __( 'here', 'CP_TD' ) . '</a>' );
		}

		// </div> course-list
		$content .= 0 < count( $courses ) && ! empty( $list_wrapper_before ) ? '</' . $list_wrapper_after . '>' : '';

		return $content;
	}


	/**
	 * MESSAGING PLUGIN SUBMENU SHORTCODE
	 * =========================
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
				<li class="submenu-item submenu-inbox <?php echo( isset( $subpage ) && 'inbox' == $subpage ? 'submenu-active' : '' ); ?>">
					<a href="<?php echo $coursepress->get_inbox_slug( true ); ?>"><?php
						_e( 'Inbox', 'CP_TD' );
						echo $unread_count;
						?></a></li>
				<li class="submenu-item submenu-sent-messages <?php echo( isset( $subpage ) && $subpage == 'sent_messages' ? 'submenu-active' : '' ); ?>">
					<a href="<?php echo $coursepress->get_sent_messages_slug( true ); ?>"><?php _e( 'Sent', 'CP_TD' ); ?></a>
				</li>
				<li class="submenu-item submenu-new-message <?php echo( isset( $subpage ) && $subpage == 'new_message' ? 'submenu-active' : '' ); ?>">
					<a href="<?php echo $coursepress->get_new_message_slug( true ); ?>"><?php _e( 'New Message', 'CP_TD' ); ?></a>
				</li>
			</ul>
			<!--submenu-main-->
		</div><!--submenu-main-container-->
		<br clear="all"/>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * UNIT DETAILS SHORTCODES
	 * =========================
	 */

	// Alias
	public static function course_unit_submenu( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
		), $atts, 'course_unit_archive_submenu' ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			return '';
		}

		return do_shortcode( '[course_unit_archive_submenu course_id="' . $course_id . '"]' );
	}

	public static function course_unit_archive_submenu( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true )
		), $atts, 'course_unit_archive_submenu' ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			return '';
		}

		$subpage = CoursePress_Helper_Utility::the_course_subpage();

		$content = '
		<div class="submenu-main-container">
			<ul id="submenu-main" class="submenu nav-submenu">
				<li class="submenu-item submenu-units ' . ( $subpage == 'units' ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'unit' ) ) . '">' . esc_html__( 'Units', 'CP_TD' ) . '</a></li>
		';

		$student_id = is_user_logged_in() ? get_current_user_id() : false;
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = CoursePress_Data_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		if ( $enrolled || $is_instructor ) {
			$content .= '
				<li class="submenu-item submenu-notifications ' . ( $subpage == 'notifications' ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'notification' ) ) . '">' . esc_html__( 'Notifications', 'CP_TD' ) . '</a></li>
			';
		}

		$pages = CoursePress_Data_Course::allow_pages( $course_id );

		if ( $pages['course_discussion'] && ( $enrolled || $is_instructor ) ) {
			$content .= '<li class="submenu-item submenu-discussions ' . ( $subpage == 'discussions' ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'discussion' ) ) . '">' . esc_html__( 'Discussions', 'CP_TD' ) . '</a></li>';
		}

		if ( $pages['workbook'] && $enrolled ) {
			$content .= '<li class="submenu-item submenu-workbook ' . ( $subpage == 'workbook' ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'workbook' ) ) . '">' . esc_html__( 'Workbook', 'CP_TD' ) . '</a></li>';
		}

		$content .= '<li class="submenu-item submenu-info"><a href="' . esc_url_raw( get_permalink( $course_id ) ) . '">' . esc_html__( 'Course Details', 'CP_TD' ) . '</a></li>';


		$show_link = false;

		if ( CoursePress_Data_Capabilities::is_pro() ) {
			// CERTIFICATE CLASS.
			// $show_link = CP_Basic_Certificate::option( 'basic_certificate_enabled' );
			// $show_link = ! empty( $show_link ) ? true : false;

			// Debug code. Remove it!
			$show_link = false;
		}
		if ( is_user_logged_in() && $show_link ) {

			// COMPLETION LOGIC.
			// if ( Student_Completion::is_course_complete( get_current_user_id(), $course_id ) ) {
			// $certificate = CP_Basic_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', 'CP_TD' ) );

			// $content .= '<li class="submenu-item submenu-certificate ' . ( $subpage == 'certificate' ? 'submenu-active' : '') . '">' . $certificate . '</li>';
			// }
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
			'url' => '',
		), $atts ) );

		if ( 'enrollment-process' == $url ) {
			return $enrollment_process_url;
		}

		if ( 'signup' == $url ) {
			return $signup_url;
		}

		return '';
	}

	public static function units_dropdown( $atts ) {
		global $wp_query;
		extract( shortcode_atts( array(
			'course_id' => ( isset( $wp_query->post->ID ) ? $wp_query->post->ID : 0 ),
			'include_general' => 'false',
			'general_title' => '',
		), $atts ) );

		$course_id = (int) $course_id;
		$include_general = sanitize_text_field( $include_general );
		$include_general = 'true' == $include_general ? true : false;
		$general_title = sanitize_text_field( $general_title );

		$course_obj = new Course( $course_id );
		$units = $course_obj->get_units();

		$dropdown = '<div class="units_dropdown_holder"><select name="units_dropdown" class="units_dropdown">';
		if ( $include_general ) {
			if ( ! $general_title ) {
				$general_title = __( '-- General --', 'CP_TD' );
			}

			$dropdown .= '<option value="">' . esc_html( $general_title ) . '</option>';
		}
		foreach ( $units as $unit ) {
			$dropdown .= '<option value="' . esc_attr( $unit->ID ) . '">' . esc_html( $unit->post_title ) . '</option>';
		}
		$dropdown .= '</select></div>';

		return $dropdown;
	}

	public static function get_parent_course_id( $atts ) {
		global $wp;

		// if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
		if ( is_array( $wp->query_vars ) && array_key_exists( 'coursename', $wp->query_vars ) ) {
			$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
		} else {
			$course_id = 0;
		}

		return $course_id;
	}

	public static function course_unit_single( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'unit_id' => 0 ), $atts ) );

		$unit_id = (int) $unit_id;

		if ( empty( $unit_id ) ) {
			if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
				$unit = new Unit();
				$unit_id = $unit->get_unit_id_by_name( $wp->query_vars['unitname'] );
			} else {
				$unit_id = 0;
			}
		}

		$args = array(
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post__in' => array( $unit_id ),
			'post_status' => cp_can_see_unit_draft() ? 'any' : 'publish',
		);

		ob_start();
		query_posts( $args );
		ob_clean();
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
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
			'order' => 'ASC',
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_status' => ( cp_can_see_unit_draft() ? 'any' : 'publish' ),
			'meta_key' => 'unit_order',
			'orderby' => 'meta_value_num',
			'posts_per_page' => '-1',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
			),
		);

		query_posts( $args );
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
	public static function courses_loop( $atts ) {
		global $wp;

		if ( array_key_exists( 'course_category', $wp->query_vars ) ) {
			$page = ( isset( $wp->query_vars['paged'] ) ) ? $wp->query_vars['paged'] : 1;
			$query_args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'post_status' => 'publish',
				'paged' => $page,
				'tax_query' => array(
					array(
						'taxonomy' => 'course_category',
						'field' => 'slug',
						'terms' => array( $wp->query_vars['course_category'] ),
					),
				),
			);

			$selected_course_order_by_type = get_option( 'course_order_by_type', 'DESC' );
			$selected_course_order_by = get_option( 'course_order_by', 'post_date' );

			if (  'course_order' == $selected_course_order_by ) {
				$query_args['meta_key'] = 'course_order';
				$query_args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key' => 'course_order',
						'compare' => 'NOT EXISTS',
					),
				);
				$query_args['orderby'] = 'meta_value';
				$query_args['order'] = $selected_course_order_by_type;
			} else {
				$query_args['orderby'] = $selected_course_order_by;
				$query_args['order'] = $selected_course_order_by_type;
			}

			query_posts( $query_args );
		}
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
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
			'category' => '',
			'order' => 'ASC',
			'post_type' => 'notifications',
			'post_mime_type' => '',
			'post_parent' => '',
			'post_status' => 'publish',
			'orderby' => 'meta_value_num',
			'posts_per_page' => '-1',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
				array(
					'key' => 'course_id',
					'value' => '',
				),
			),
		);

		query_posts( $args );
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
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
			'order' => 'DESC',
			'post_type' => 'discussions',
			'post_mime_type' => '',
			'post_parent' => '',
			'post_status' => 'publish',
			'posts_per_page' => '-1',
			'meta_key' => 'course_id',
			'meta_value' => $course_id,
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
		$units = $course->get_units( $course_id, 'publish' );

		$user_id = get_current_user_id();
		$student = new Student( $user_id );

		// Redirect to the parent course page if not enrolled.
		if ( ! current_user_can( 'manage_options' ) ) {

			// If current user is not admin, check if he can access to the units.
			if ( $course->details->post_author != get_current_user_id() ) {

				// Check if user is an author of a course (probably instructor).
				if ( ! current_user_can( 'coursepress_view_all_units_cap' ) ) {

					/*
					 * Check if the instructor, even if it's not the author of
					 * the course, maybe has a capability given by the admin.
					 * If it's not an instructor who made the course, check if
					 * he is enrolled to course.
					 * Added 3rd parameter to deal with legacy meta data.
					 */
					if ( ! $student->user_enrolled_in_course( $course_id, $user_id, 'update_meta' ) ) {
						// If not, redirect him to the course page so he may
						// enroll it if the enrollment is available.
						wp_redirect( get_permalink( $course_id ) );
						exit;
					}
				}
			}
		}


		$content .= '<ol>';
		$last_unit_url = '';

		foreach ( $units as $unit ) {
			// $unit_details = new Unit( $unit->ID );
			$content .= '<li><a href="' . Unit::get_permalink( $unit->ID, $course_id ) . '">' . $unit->post_title . '</a></li>';
			$last_unit_url = Unit::get_permalink( $unit->ID, $course_id );
		}

		$content .= '</ol>';

		if ( count( $units ) >= 1 ) {
			$content .= do_shortcode( '[course_discussion]' );
		}

		if ( ! count( $units ) ) {
			$content = __( '0 course units prepared yet. Please check back later.', 'CP_TD' );
		}

		if ( 1 == count( $units ) ) {
			wp_redirect( $last_unit_url );
			exit;
		}

		return $content;
	}

	public static function course_unit_details( $atts ) {
		global $post_id, $wp, $coursepress;

		extract( shortcode_atts(
			apply_filters( 'shortcode_atts_course_unit_details', array(
				'unit_id' => 0,
				'field' => 'post_title',
				'format' => 'true',
				'additional' => '2',
				'style' => 'flat',
				'class' => 'course-name-content',
				'tooltip_alt' => __( 'Percent of the unit completion', 'CP_TD' ),
				'knob_fg_color' => '#24bde6',
				'knob_bg_color' => '#e0e6eb',
				'knob_data_thickness' => '.35',
				'knob_data_width' => '70',
				'knob_data_height' => '70',
				'unit_title' => '',
				'unit_page_title_tag' => 'h3',
				'unit_page_title_tag_class' => '',
				'last_visited' => 'false',
				'parent_course_preceding_content' => __( 'Course: ', 'CP_TD' ),
				'student_id' => get_current_user_ID(),
			) ), $atts ) );

		$unit_id = (int) $unit_id;
		$field = sanitize_html_class( $field );
		$format = sanitize_text_field( $format );
		$format = 'true' == $format ? true : false;
		$additional = sanitize_text_field( $additional );
		$style = sanitize_html_class( $style );
		$tooltip_alt = sanitize_text_field( $tooltip_alt );
		$knob_fg_color = sanitize_text_field( $knob_fg_color );
		$knob_bg_color = sanitize_text_field( $knob_bg_color );
		$knob_data_thickness = sanitize_text_field( $knob_data_thickness );
		$knob_data_width = (int) $knob_data_width;
		$knob_data_height = (int) $knob_data_height;
		$unit_title = sanitize_text_field( $unit_title );
		$unit_page_title_tag = sanitize_html_class( $unit_page_title_tag );
		$unit_page_title_tag_class = sanitize_html_class( $unit_page_title_tag_class );
		$parent_course_preceding_content = sanitize_text_field( $parent_course_preceding_content );
		$student_id = (int) $student_id;
		$last_visited = sanitize_text_field( $last_visited );
		$last_visited = 'true' == $last_visited ? true : false;
		$class = sanitize_html_class( $class );

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$content = '';
		if ( 'permalink' == $field ) {
			// COMPLETION_LOGIC.
			// if ( $last_visited ) {
			//  $last_visited_page = cp_get_last_visited_unit_page( $unit_id );
			//  $unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id ) . 'page/' . trailingslashit( $last_visited_page );
			// } else {
			$unit = get_post( $unit_id );
			$content = get_permalink( $course_id ) . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . $unit->post_name;
			// $unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id );
			// }
		}
		return $content;

		/**
		 * @todo : THIS CODE IS UNREACHABLE...?!?!?!?!
		 */

		// COMPLETION LOGIC

		if ( $unit_id == 0 ) {
			$unit_id = get_the_ID();
		}

		$unit = new Unit( $unit_id );

		$student = new Student( get_current_user_id() );
		$class = sanitize_html_class( $class );

		if ( $field == 'is_unit_available' ) {
			$unit->details->$field = Unit::is_unit_available( $unit_id );
		}

		if ( 'unit_page_title' == $field ) {
			$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
			$page_name = $unit->get_unit_page_name( $paged );
			if ( $unit_title !== '' ) {
				$page_title_prepend = $unit_title . ': ';
			} else {
				$page_title_prepend = '';
			}

			$show_title_array = get_post_meta( $unit_id, 'show_page_title', true );
			$show_title = false;
			if ( isset( $show_title_array[ $paged - 1 ] ) && 'yes' == $show_title_array[ $paged - 1 ] ) {
				$show_title = true;
			}

			if ( ! empty( $page_name ) && $show_title ) {
				$unit->details->$field = '<' . $unit_page_title_tag . '' . ( $unit_page_title_tag_class !== '' ? ' class="' . $unit_page_title_tag_class . '"' : '' ) . '>' . $page_title_prepend . $unit->get_unit_page_name( $paged ) . '</' . $unit_page_title_tag . '>';
			} else {
				$unit->details->$field = '';
			}
		}

		if ( 'parent_course' == $field ) {
			$course = new Course( $unit->course_id );
			$unit->details->$field = $parent_course_preceding_content . '<a href="' . $course->get_permalink() . '" class="' . $class . '">' . $course->details->post_title . '</a>';
		}

		/* ------------ */

		$front_save_count = 0;

		$modules = Unit_Module::get_modules( $unit_id );
		$mandatory_answers = 0;
		$mandatory = 'no';

		foreach ( $modules as $mod ) {
			$mandatory = get_post_meta( $mod->ID, 'mandatory_answer', true );

			if ( $mandatory == 'yes' ) {
				$mandatory_answers ++;
			}

			$class_name = $mod->module_type;

			if ( class_exists( $class_name ) ) {
				if ( constant( $class_name . '::FRONT_SAVE' ) ) {
					$front_save_count++;
				}
			}
		}

		$input_modules_count = $front_save_count;
		$responses_count = 0;

		$modules = Unit_Module::get_modules( $unit_id );
		foreach ( $modules as $module ) {
			if ( Unit_Module::did_student_respond( $module->ID, $student_id ) ) {
				$responses_count ++;
			}
		}
		$student_modules_responses_count = $responses_count;

		if ( $student_modules_responses_count > 0 ) {
			$percent_value = $mandatory_answers > 0 ? ( round( ( 100 / $mandatory_answers ) * $student_modules_responses_count, 0 ) ) : 0;
			// In case that student gave answers on all mandatory plus optional questions.
			$percent_value = ( $percent_value > 100 ? 100 : $percent_value );
		} else {
			$percent_value = 0;
		}

		if ( $input_modules_count == 0 ) {

			$grade = 0;
			$front_save_count = 0;
			$assessable_answers = 0;
			$responses = 0;
			$graded = 0;
			// $input_modules_count = do_shortcode( '[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]' );
			$modules = Unit_Module::get_modules( $unit_id );

			if ( $input_modules_count > 0 ) {
				foreach ( $modules as $mod ) {

					$class_name = $mod->module_type;
					$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );

					if ( class_exists( $class_name ) ) {

						if ( constant( $class_name . '::FRONT_SAVE' ) ) {
							if ( 'yes' == $assessable ) {
								$assessable_answers++;
							}

							$front_save_count++;
							$response = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );

							if ( isset( $response->ID ) ) {
								$grade_data = Unit_Module::get_response_grade( $response->ID );
								$grade = $grade + $grade_data['grade'];

								if ( get_post_meta( $response->ID, 'response_grade' ) ) {
									$graded++;
								}

								$responses++;
							}
						} else {
							// Read only module.
						}
					}
				}

				$percent_value = ( $format == true ? ( $responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">' ) . ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) . '</span>' : ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) );
			} else {
				$student = new Student( $student_id );
				if ( $student->is_unit_visited( $unit_id, $student_id ) ) {
					$grade = 100;
					$percent_value = ( $format == true ? '<span class="grade-active">' . $grade . '</span>' : $grade );
				} else {
					$grade = 0;
					$percent_value = ( $format == true ? '<span class="grade-inactive">' . $grade . '</span>' : $grade );
				}
			}

			// $percent_value = do_shortcode( '[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]' );
		}

		// Redirect to the parent course page if not enrolled.
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( ! $coursepress->check_access( $unit->course_id, $unit_id ) ) {
				wp_redirect( get_permalink( $unit->course_id ) );
				exit;
			}
		}

		if ( $field == 'percent' ) {

			// $completion = new Course_Completion( $unit->course_id );
			// $completion->init_student_status();
			// $percent_value = $completion->unit_progress( $unit_id );
			$percent_value = Student_Completion::calculate_unit_completion( $student_id, $unit->course_id, $unit_id );

			$assessable_input_modules_count = do_shortcode( '[course_unit_details field="assessable_input_modules_count"]' );

			if ( 'flat' == $style ) {
				$unit->details->$field = '<span class="percentage">' . ( $format == true ? $percent_value . '%' : $percent_value ) . '</span>';
			} elseif ( 'none' == $style ) {
				$unit->details->$field = $percent_value;
			} else {
				$unit->details->$field = '<a class="tooltip" alt="' . $tooltip_alt . '"><input class="knob" data-fgColor="' . $knob_fg_color . '" data-bgColor="' . $knob_bg_color . '" data-thickness="' . $knob_data_thickness . '" data-width="' . $knob_data_width . '" data-height="' . $knob_data_height . '" data-readOnly=true value="' . $percent_value . '"></a>';
			}
		}

		if ( 'permalink' == $field ) {
			if ( $last_visited ) {
				$last_visited_page = cp_get_last_visited_unit_page( $unit_id );
				$unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id ) . 'page/' . trailingslashit( $last_visited_page );
			} else {
				$unit->details->$field = Unit::get_permalink( $unit_id, $unit->course_id );
			}
		}

		if ( 'input_modules_count' == $field ) {
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

		if ( 'mandatory_input_modules_count' == $field ) {

			$front_save_count = 0;
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
						// $front_save_count++;
					}
				}
			}

			$unit->details->$field = $mandatory_answers;
		}

		if ( $field == 'assessable_input_modules_count' ) {
			$front_save_count = 0;
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
						// $front_save_count++;
					}
				}
			}

			if ( isset( $unit->details->$field ) ) {
				$unit->details->$field = $assessable_answers;
			}
		}

		if ( $field == 'student_module_responses' ) {
			$responses_count = 0;
			$mandatory_answers = 0;
			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $module ) {
				$mandatory = get_post_meta( $module->ID, 'mandatory_answer', true );

				if ( 'yes' == $mandatory ) {
					$mandatory_answers++;
				}

				if ( Unit_Module::did_student_respond( $module->ID, $student_id ) ) {
					$responses_count++;
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
			$grade = 0;
			$front_save_count = 0;
			$responses = 0;
			$graded = 0;
			$input_modules_count = do_shortcode( '[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]' );
			$modules = Unit_Module::get_modules( $unit_id );
			$mandatory_answers = 0;
			$assessable_answers = 0;

			if ( $input_modules_count > 0 ) {
				foreach ( $modules as $mod ) {

					$class_name = $mod->module_type;

					if ( class_exists( $class_name ) ) {

						if ( constant( $class_name . '::FRONT_SAVE' ) ) {
							$front_save_count ++;
							$response = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );
							$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );
							$mandatory = get_post_meta( $mod->ID, 'mandatory_answer', true );


							if ( $assessable == 'yes' ) {
								$assessable_answers ++;
							}

							if ( isset( $response->ID ) ) {

								if ( $assessable == 'yes' ) {

									$grade_data = Unit_Module::get_response_grade( $response->ID );
									$grade = $grade + $grade_data['grade'];

									if ( get_post_meta( $response->ID, 'response_grade' ) ) {
										$graded ++;
									}

									$responses ++;
								}
							}
						} else {
							// Read only module.
						}
					}
				}

				$unit->details->$field = ( $format == true ? ( $responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">' ) . ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) . '%</span>' : ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) );
			} else {
				$student = new Student( $student_id );
				if ( $student->is_unit_visited( $unit_id, $student_id ) ) {
					$grade = 100;
					$unit->details->$field = ( $format == true ? '<span class="grade-active">' . $grade . '%</span>' : $grade );
				} else {
					$grade = 0;
					$unit->details->$field = ( $format == true ? '<span class="grade-inactive">' . $grade . '%</span>' : $grade );
				}
			}
		}

		if ( $field == 'student_unit_modules_graded' ) {
			$grade = 0;
			$front_save_count = 0;
			$responses = 0;
			$graded = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {

				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {

					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						$front_save_count ++;
						$response = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );

						if ( isset( $response->ID ) ) {
							$grade_data = Unit_Module::get_response_grade( $response->ID );
							$grade = $grade + $grade_data['grade'];

							if ( get_post_meta( $response->ID, 'response_grade' ) ) {
								$graded ++;
							}

							$responses ++;
						}
					} else {
						// Read only module.
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
			'type' => 'unit_archive',
			'course_id' => 0,
			'position' => 'shortcode'
		), $atts ) );

		$course_id = (int) $course_id;
		$type = sanitize_html_class( $type );
		$position = sanitize_html_class( $position );

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$course = new Course( $course_id );

		if ( $type == 'unit_archive' ) {
			$units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit( get_option( 'home' ) ) . $course_slug . '/">' . __( 'Courses', 'CP_TD' ) . '</a> Â» <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a></div>';
		}

		if ( $type == 'unit_single' ) {
			$units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit( get_option( 'home' ) ) . $course_slug . '/">' . __( 'Courses', 'CP_TD' ) . '</a> Â» <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a> Â» <a href="' . $course->get_permalink() . $units_slug . '/">' . __( 'Units', 'CP_TD' ) . '</a></div>';
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

		if ( 'on' == $course->details->allow_course_discussion ) {

			$comments_args = array(
				// Change the title of send button.
				'label_submit' => __( 'Send', 'CP_TD' ),
				// Change the title of the reply section.
				'title_reply' => __( 'Write a Reply or Comment', 'CP_TD' ),
				// Remove "Text or HTML to be displayed after the set of comment fields".
				'comment_notes_after' => '',
				// Redefine your own textarea (the comment body).
				'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
			);

			$defaults = array(
				'author_email' => '',
				'ID' => '',
				'karma' => '',
				'number' => '',
				'offset' => '',
				'orderby' => '',
				'order' => 'DESC',
				'parent' => '',
				'post_id' => $course_id,
				'post_author' => '',
				'post_name' => '',
				'post_parent' => '',
				'post_status' => '',
				'post_type' => '',
				'status' => '',
				'type' => '',
				'user_id' => '',
				'search' => '',
				'count' => false,
				'meta_key' => '',
				'meta_value' => '',
				'meta_query' => '',
			);

			$wp_list_comments_args = array(
				'walker' => null,
				'max_depth' => '',
				'style' => 'ul',
				'callback' => null,
				'end-callback' => null,
				'type' => 'all',
				'reply_text' => __( 'Reply', 'CP_TD' ),
				'page' => '',
				'per_page' => '',
				'avatar_size' => 32,
				'reverse_top_level' => null,
				'reverse_children' => '',
				'format' => 'xhtml', // Or html5.
				'short_ping' => false,
			);

			comment_form( $comments_args = array(), $course_id );
			wp_list_comments( $wp_list_comments_args, get_comments( $defaults ) );
		}
	}

	public static function unit_discussion( $atts ) {
		global $wp;
		if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
			$unit = new Unit();
			$unit_id = $unit->get_unit_id_by_name( $wp->query_vars['unitname'] );
		} else {
			$unit_id = 0;
		}
		$comments_args = array(
			// Change the title of send button.
			'label_submit' => 'Send',
			// Change the title of the reply secpertion.
			'title_reply' => 'Write a Reply or Comment',
			// Remove "Text or HTML to be displayed after the set of comment fields".
			'comment_notes_after' => '',
			// Redefine your own textarea (the comment body).
			'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
		);
		ob_start();
		comment_form( $comments_args, $unit_id );
		$content = ob_get_clean();

		return $content;
	}


	/* =========== PAGES SHORTCODES =============== */


	/**
	 * @todo: Migrate those templates to 2.0 code!
	 */
	public static function cp_pages( $atts ) {
		ob_start();
		global $plugin_dir;
		extract( shortcode_atts( array(
			'page' => '',
		), $atts ) );

		switch ( $page ) {
			case 'enrollment_process':
				require( $plugin_dir . '_deprecated/templates/enrollment-process.php' );
				break;

			case 'student_login':
				require( $plugin_dir . '_deprecated/templates/student-login.php' );
				break;

			case 'student_signup':
				require( $plugin_dir . '_deprecated/templates/student-signup.php' );
				break;

			case 'student_dashboard':
				require( $plugin_dir . '_deprecated/templates/student-dashboard.php' );
				break;

			case 'student_settings':
				require( $plugin_dir . '_deprecated/templates/student-settings.php' );
				break;

			default:
				_e( 'Page cannot be found', 'CP_TD' );
		}

		$content = wpautop( ob_get_clean(), apply_filters( 'coursepress_pages_content_preserve_line_breaks', true ) );

		return $content;
	}

	public static function course_signup( $atts ) {
		$allowed = array( 'signup', 'login' );

		extract( shortcode_atts( array(
			'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
			'failed_login_text' => __( 'Invalid login.', 'CP_TD' ),
			'failed_login_class' => 'red',
			'logout_url' => '',
			'signup_tag' => 'h3',
			'signup_title' => __( 'Signup', 'CP_TD' ),
			'login_tag' => 'h3',
			'login_title' => __( 'Login', 'CP_TD' ),
			'signup_url' => '',
			'login_url' => '',
			'redirect_url' => '', // Redirect on successful login or signup.
		), $atts, 'course_signup' ) );

		$failed_login_text = sanitize_text_field( $failed_login_text );
		$failed_login_class = sanitize_html_class( $failed_login_class );
		$logout_url = esc_url_raw( $logout_url );
		$signup_tag = sanitize_html_class( $signup_tag );
		$signup_title = sanitize_text_field( $signup_title );
		$login_tag = sanitize_html_class( $login_tag );
		$login_title = sanitize_text_field( $login_title );
		$signup_url = esc_url_raw( $signup_url );
		$redirect_url = esc_url_raw( $redirect_url );

		$page = in_array( $page, $allowed ) ? $page : 'signup';

		$signup_prefix = empty( $signup_url ) ? '&' : '?';
		$login_prefix = empty( $login_url ) ? '&' : '?';

		$signup_url = empty( $signup_url ) ? CoursePress_Core::get_slug( 'signup', true ) : $signup_url;
		$login_url = empty( $login_url ) ? CoursePress_Core::get_slug( 'login', true ) : $login_url;

		if ( ! empty( $redirect_url ) ) {
			$signup_url = $signup_url . $signup_prefix . 'redirect_url=' . urlencode( $redirect_url );
			$login_url = $login_url . $login_prefix . 'redirect_url=' . urlencode( $redirect_url );
		}
		if ( ! empty( $_POST['redirect_url'] ) ) {
			$signup_url = $signup_url . '?redirect_url=' . $_POST['redirect_url'];
			$login_url = $login_url . '?redirect_url=' . $_POST['redirect_url'];
		}

		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
		};

		$form_message = '';
		$form_message_class = '';

		// Attempt a login if submitted.
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {

			$auth = wp_authenticate_username_password( null, $_POST['log'], $_POST['pwd'] );
			if ( ! is_wp_error( $auth ) ) {
				$user = get_user_by( 'login', $_POST['log'] );
				$user_id = $user->ID;
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );
				if ( ! empty( $redirect_url ) ) {
					wp_redirect( urldecode( esc_url_raw( $redirect_url ) ) );
				} else {
					wp_redirect( esc_url_raw( CoursePress_Core::get_slug( 'student_dashboard', true ) ) );
				}
				exit;
			} else {
				$form_message = $failed_login_text;
				$form_message_class = $failed_login_class;
			}
		}

		$content = '';
		switch ( $page ) {
			case 'signup':
				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Helper_Utility::users_can_register() ) {
						$form_message_class = '';
						$form_message = '';

						if ( isset( $_POST['student-settings-submit'] ) ) {

							check_admin_referer( 'student_signup' );
							$min_password_length = apply_filters( 'coursepress_min_password_length', 6 );

							$student_data = array();
							$form_errors = 0;

							do_action( 'coursepress_before_signup_validation' );

							if ( $_POST['username'] != '' && $_POST['first_name'] != '' && $_POST['last_name'] != '' && $_POST['email'] != '' && $_POST['password'] != '' && $_POST['password_confirmation'] != '' ) {

								if ( ! username_exists( $_POST['username'] ) ) {
									if ( ! email_exists( $_POST['email'] ) ) {
										if ( $_POST['password'] == $_POST['password_confirmation'] ) {
											if ( ! preg_match( "#[0-9]+#", $_POST['password'] ) || ! preg_match( "#[a-zA-Z]+#", $_POST['password'] ) || strlen( $_POST['password'] ) < $min_password_length ) {
												$form_message = sprintf( __( 'Your password must be at least %d characters long and have at least one letter and one number in it.', 'CP_TD' ), $min_password_length );
												$form_message_class = 'red';
												$form_errors ++;
											} else {

												if ( $_POST['password_confirmation'] ) {
													$student_data['user_pass'] = $_POST['password'];
												} else {
													$form_message = __( "Passwords don't match", 'CP_TD' );
													$form_message_class = 'red';
													$form_errors ++;
												}
											}
										} else {
											$form_message = __( 'Passwords don\'t match', 'CP_TD' );
											$form_message_class = 'red';
											$form_errors ++;
										}

										$student_data['role'] = get_option( 'default_role', 'subscriber' );
										$student_data['user_login'] = $_POST['username'];
										$student_data['user_email'] = $_POST['email'];
										$student_data['first_name'] = $_POST['first_name'];
										$student_data['last_name'] = $_POST['last_name'];

										if ( ! is_email( $_POST['email'] ) ) {
											$form_message = __( 'E-mail address is not valid.', 'CP_TD' );
											$form_message_class = 'red';
											$form_errors ++;
										}

										if ( isset( $_POST['tos_agree'] ) ) {
											if ( $_POST['tos_agree'] == '0' ) {
												$form_message = __( 'You must agree to the Terms of Service in order to signup.', 'CP_TD' );
												$form_message_class = 'red';
												$form_errors ++;
											}
										}

										if ( $form_errors == 0 ) {

											$student_data = CoursePress_Helper_Utility::sanitize_recursive( $student_data );
											$student_id = wp_insert_user( $student_data );
											if ( ! empty( $student_id ) ) {
												// $form_message = __( 'Account created successfully! You may now <a href="' . ( get_option( 'use_custom_login_form', 1 ) ? trailingslashit( site_url() . '/' . $this->get_login_slug() ) : wp_login_url() ) . '">log into your account</a>.', 'CP_TD' );
												// $form_message_class = 'regular';
												$email_args['email_type'] = CoursePress_Helper_Email::REGISTRATION;
												$email_args['email'] = $student_data['user_email'];
												$email_args['first_name'] = $student_data['first_name'];
												$email_args['last_name'] = $student_data['last_name'];
												$email_args['fields'] = array();
												$email_args['fields']['student_id'] = $student_id;
												$email_args['fields']['student_username'] = $student_data['user_login'];
												$email_args['fields']['student_password'] = $student_data['user_pass'];

												CoursePress_Helper_Email::send_email( $email_args );

												$creds = array();
												$creds['user_login'] = $student_data['user_login'];
												$creds['user_password'] = $student_data['user_pass'];
												$creds['remember'] = true;
												$user = wp_signon( $creds, false );

												if ( is_wp_error( $user ) ) {
													$form_message = $user->get_error_message();
													$form_message_class = 'red';
												}

												if ( isset( $_POST['course_id'] ) && is_numeric( $_POST['course_id'] ) ) {
													$course = new Course( $_POST['course_id'] );
													wp_redirect( $course->get_permalink() );
												} else {
													if ( ! empty( $redirect_url ) ) {
														wp_redirect( esc_url_raw( apply_filters( 'coursepress_redirect_after_signup_redirect_url', $redirect_url ) ) );
													} else {
														wp_redirect( esc_url_raw( apply_filters( 'coursepress_redirect_after_signup_url', CoursePress_Core::get_slug( 'student_dashboard', true ) ) ) );
													}
												}
												exit;
											} else {
												$form_message = __( 'An error occurred while creating the account. Please check the form and try again.', 'CP_TD' );
												$form_message_class = 'red';
											}
										}
									} else {
										$form_message = __( 'Sorry, that email address is already used!', 'CP_TD' );
										$form_message_class = 'error';
									}
								} else {
									$form_message = __( 'Username already exists. Please choose another one.', 'CP_TD' );
									$form_message_class = 'red';
								}
							} else {
								$form_message = __( 'All fields are required.', 'CP_TD' );
								$form_message_class = 'red';
							}
						} else {
							$form_message = __( 'All fields are required.', 'CP_TD' );
						}

						if ( ! empty( $signup_title ) ) {
							$content .= '<' . $signup_tag . '>' . $signup_title . '</' . $signup_tag . '>';
						}

						$content .= '
							<p class="form-info-' . esc_attr( apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ) ) . '">' . esc_html( apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ) ) . '</p>
						';

						ob_start();
						do_action( 'coursepress_before_signup_form' );
						$content .= ob_get_clean();

						$content .= '
							<form id="student-settings" name="student-settings" method="post" class="student-settings signup-form">
						';

						ob_start();
						do_action( 'coursepress_before_all_signup_fields' );
						$content .= ob_get_clean();

						// First name
						$content .= '
							<input type="hidden" name="course_id" value="' . esc_attr( isset( $_GET['course_id'] ) ? $_GET['course_id'] : ' ' ) . '"/>
							<input type="hidden" name="redirect_url" value="' . esc_url( $redirect_url ) . '"/>
							<label class="firstname">
								<span>' . esc_html__( 'First Name', 'CP_TD' ) . ':</span>
								<input type="text" name="first_name" value="' . ( isset( $_POST['first_name'] ) ? esc_html( $_POST['first_name'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_first_name' );
						$content .= ob_get_clean();

						// Last name
						$content .= '
							<label class="lastname">
								<span>' . esc_html__( 'Last Name', 'CP_TD' ) . ':</span>
								<input type="text" name="last_name" value="' . ( isset( $_POST['last_name'] ) ? esc_attr( $_POST['last_name'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_last_name' );
						$content .= ob_get_clean();

						// Username.
						$content .= '
							<label class="username">
								<span>' . esc_html__( 'Username', 'CP_TD' ) . ':</span>
								<input type="text" name="username" value="' . ( isset( $_POST['username'] ) ? esc_attr( $_POST['username'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_username' );
						$content .= ob_get_clean();

						// Email.
						$content .= '
							<label class="email">
								<span>' . esc_html__( 'E-mail', 'CP_TD' ) . ':</span>
								<input type="text" name="email" value="' . ( isset( $_POST['email'] ) ? esc_attr( $_POST['email'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_email' );
						$content .= ob_get_clean();

						// Password.
						$content .= '
							<label class="password">
								<span>' . esc_html__( 'Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password" value=""/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_password' );
						$content .= ob_get_clean();

						// Confirm.
						$content .= '
							<label class="password-confirm right">
								<span>' . esc_html__( 'Confirm Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password_confirmation" value=""/>
							</label>
						';

						if ( shortcode_exists( 'signup-tos' ) ) {
							if ( get_option( 'show_tos', 0 ) == '1' ) {
								$content .= '<label class="tos full">';
								ob_start();
								echo do_shortcode( '[signup-tos]' );
								$content .= ob_get_clean();
								$content .= '</label>';
							}
						}

						ob_start();
						do_action( 'coursepress_after_all_signup_fields' );
						$content .= ob_get_clean();

						$content .= '
							<label class="existing-link full">
								' . sprintf( __( 'Already have an account? %s%s%s!', 'CP_TD' ), '<a href="' . esc_url( $login_url ) . '">', __( 'Login to your account', 'CP_TD' ), '</a>' ) . '
							</label>
							<label class="submit-link full-right">
								<input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="' . esc_attr__( 'Create an Account', 'CP_TD' ) . '"/>
							</label>
						';

						ob_start();
						do_action( 'coursepress_after_submit' );
						$content .= ob_get_clean();

						$content .= wp_nonce_field( 'student_signup', '_wpnonce', true, false );
						$content .= '
							</form>
							<div class="clearfix" style="clear: both;"></div>
						';

						ob_start();
						do_action( 'coursepress_after_signup_form' );
						$content .= ob_get_clean();

					} else {
						$content .= __( 'Registrations are not allowed.', 'CP_TD' );
					}
				} else {

					if ( ! empty( $redirect_url ) ) {
						wp_redirect( esc_url_raw( urldecode( $redirect_url ) ) );
					} else {
						wp_redirect( esc_url_raw( CoursePress_Core::get_slug( 'student_dashboard', true ) ) );
					}
					exit;
				}
				break;

			case 'login':
				$content = '';

				if ( ! empty( $login_title ) ) {
					$content .= '<' . $login_tag . '>' . $login_title . '</' . $login_tag . '>';
				}

				$content .= '
					<p class="form-info-' . esc_attr( apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ) ) . '">' . esc_html( apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ) ) . '</p>
				';
				ob_start();
				do_action( 'coursepress_before_login_form' );
				$content .= ob_get_clean();
				$content .= '
					<form name="loginform" id="student-settings" class="student-settings login-form" method="post">
				';
				ob_start();
				do_action( 'coursepress_after_start_form_fields' );
				$content .= ob_get_clean();

				$content .= '
						<label class="username">
							<span>' . esc_html__( 'Username', 'CP_TD' ) . '</span>
							<input type="text" name="log" value="' . ( isset( $_POST['log'] ) ? esc_attr( $_POST['log'] ) : '' ) . '"/>
						</label>
						<label class="password">
							<span>' . esc_html__( 'Password', 'CP_TD' ) . '</span>
							<input type="password" name="pwd" value="' . ( isset( $_POST['pwd'] ) ? esc_attr( $_POST['pwd'] ) : '' ) . '"/>
						</label>

				';

				ob_start();
				do_action( 'coursepress_form_fields' );
				$content .= ob_get_clean();

				$content .= '
						<label class="signup-link full">
						' . ( CoursePress_Helper_Utility::users_can_register() ? sprintf( __( 'Don\'t have an account? %s%s%s now!', 'CP_TD' ), '<a href="' . $signup_url . '">', __( 'Create an Account', 'CP_TD' ), '</a>' ) : '' ) . '
						</label>
						<label class="forgot-link half-left">
							<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot Password?', 'CP_TD' ) . '</a>
						</label>
						<label class="submit-link half-right">
							<input type="submit" name="wp-submit" id="wp-submit" class="apply-button-enrolled" value="' . esc_attr__( 'Log In', 'CP_TD' ) . '"><br>
						</label>
						<input name="redirect_to" value="' . esc_url( CoursePress_Core::get_slug( 'student_dashboard', true ) ) . '" type="hidden">
						<input name="testcookie" value="1" type="hidden">
						<input name="course_signup_login" value="1" type="hidden">
				';

				ob_start();
				do_action( 'coursepress_before_end_form_fields' );
				$content .= ob_get_clean();

				$content .= '</form>';

				ob_start();
				do_action( 'coursepress_after_login_form' );
				$content .= ob_get_clean();

				break;
		}

		return $content;
	}

	public static function course_signup_form( $atts ) {
		$allowed = array( 'signup', 'login' );

		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
			'class' => '',
			'login_link_url' => '#',
			'login_link_id' => '',
			'login_link_class' => '',
			'login_link_label' => __( 'Already have an account? <a href="%s" class="%s" id="%s">Login to your account</a>!', 'CP_TD' ),
			'signup_link_url' => '#',
			'signup_link_id' => '',
			'signup_link_class' => '',
			'signup_link_label' => __( 'Don’t have an account? <a href="%s" class="%s" id="%s">Create an Account</a> now!', 'CP_TD' ),
			'forgot_password_label' => __( 'Forgot Password?', 'CP_TD' ),
			'submit_button_class' => '',
			'submit_button_attributes' => '',
			'submit_button_label' => '',
			'show_submit' => 'yes',
			'strength_meter_placeholder' => 'yes',
		), $atts, 'course_signup_form' ) );

		$course_id = (int) $course_id;
		$class = sanitize_text_field( $class );

		$login_link_id = sanitize_text_field( $login_link_id );
		$login_link_class = sanitize_text_field( $login_link_class );
		$login_link_url = esc_url_raw( $login_link_url );
		$login_link_url = ! empty( $login_link_url ) ? $login_link_url : '#' . $login_link_id;

		$login_link_label = sprintf( $login_link_label, $login_link_url, $login_link_class, $login_link_id );
		$signup_link_id = sanitize_text_field( $signup_link_id );
		$signup_link_class = sanitize_text_field( $signup_link_class );
		$signup_link_url = esc_url_raw( $signup_link_url );
		$signup_link_label = sprintf( $signup_link_label, $signup_link_url, $signup_link_class, $signup_link_id );
		$forgot_password_label = sanitize_text_field( $forgot_password_label );
		$submit_button_class = sanitize_text_field( $submit_button_class );
		$submit_button_attributes = sanitize_text_field( $submit_button_attributes );
		$submit_button_label = sanitize_text_field( $submit_button_label );

		$show_submit = cp_is_true( $show_submit );
		$strength_meter_placeholder = cp_is_true( $strength_meter_placeholder );

		$page = in_array( $page, $allowed ) ? $page : 'signup';

		$signup_prefix = empty( $signup_url ) ? '&' : '?';
		$login_prefix = empty( $login_url ) ? '&' : '?';

		$signup_url = CoursePress_Core::get_slug( 'signup', true );
		$login_url = CoursePress_Core::get_slug( 'login', true );
		$forgot_url = wp_lostpassword_url();

		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
		};

		$content = '';
		switch ( $page ) {
			case 'signup':
				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Helper_Utility::users_can_register() ) {
						$form_message_class = '';
						$form_message = '';

						ob_start();
						do_action( 'coursepress_before_signup_form' );
						$content .= ob_get_clean();

						$content .= '
							<form id="student-settings" name="student-settings" method="post" class="student-settings signup-form">
						';

						ob_start();
						do_action( 'coursepress_before_all_signup_fields' );
						$content .= ob_get_clean();

						if ( $strength_meter_placeholder ) {
							$content .= '<span id="error-messages"></span>';
						}

						// First name.
						$content .= '
							<input type="hidden" name="course_id" value="' . esc_attr( $course_id ) . '"/>
							<label class="firstname">
								<span>' . esc_html__( 'First Name', 'CP_TD' ) . ':</span>
								<input type="text" name="first_name" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_first_name' );
						$content .= ob_get_clean();

						// Last name.
						$content .= '
							<label class="lastname">
								<span>' . esc_html__( 'Last Name', 'CP_TD' ) . ':</span>
								<input type="text" name="last_name" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_last_name' );
						$content .= ob_get_clean();

						// Username.
						$content .= '
							<label class="username">
								<span>' . esc_html__( 'Username', 'CP_TD' ) . ':</span>
								<input type="text" name="username" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_username' );
						$content .= ob_get_clean();

						// Email.
						$content .= '
							<label class="email">
								<span>' . esc_html__( 'E-mail', 'CP_TD' ) . ':</span>
								<input type="text" name="email" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_email' );
						$content .= ob_get_clean();

						// Password.
						$content .= '
							<label class="password">
								<span>' . esc_html__( 'Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password" value=""/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_password' );
						$content .= ob_get_clean();

						// Confirm.
						$content .= '
							<label class="password-confirm right">
								<span>' . esc_html__( 'Confirm Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password_confirmation" value=""/>
							</label>
						';

						if ( $strength_meter_placeholder ) {
							$content .= '<span id="password-strength"></span>';
						}

						if ( shortcode_exists( 'signup-tos' ) ) {
							if ( get_option( 'show_tos', 0 ) == '1' ) {
								$content .= '<label class="tos full">';
								ob_start();
								echo do_shortcode( '[signup-tos]' );
								$content .= ob_get_clean();
								$content .= '</label>';
							}
						}

						ob_start();
						do_action( 'coursepress_after_all_signup_fields' );
						$content .= ob_get_clean();

						$content .= '
							<label class="existing-link full">
								' . $login_link_label . '
							</label>
						';

						if ( $show_submit ) {
							$content .= '
							<label class="submit-link full-right">
								<input type="submit" ' . esc_attr( $submit_button_attributes ) . ' class="' . esc_attr( $course_id ) . '" value="' . esc_attr( $submit_button_label ) . '"/>
							</label>
							';
						}

						ob_start();
						do_action( 'coursepress_after_submit' );
						$content .= ob_get_clean();

						$content .= wp_nonce_field( 'student_signup', '_wpnonce', true, false );
						$content .= '
							</form>
							<div class="clearfix" style="clear: both;"></div>
						';

						ob_start();
						do_action( 'coursepress_after_signup_form' );
						$content .= ob_get_clean();

					} else {
						$content .= __( 'Registrations are not allowed.', 'CP_TD' );
					}
				}
				break;

			case 'login':
				$content = '';

				ob_start();
				do_action( 'coursepress_before_login_form' );
				$content .= ob_get_clean();
				$content .= '
					<form name="loginform" id="student-settings" class="student-settings login-form" method="post">
				';
				ob_start();
				do_action( 'coursepress_after_start_form_fields' );
				$content .= ob_get_clean();

				$content .= '
						<label class="username">
							<span>' . esc_html__( 'Username', 'CP_TD' ) . '</span>
							<input type="text" name="log" />
						</label>
						<label class="password">
							<span>' . esc_html__( 'Password', 'CP_TD' ) . '</span>
							<input type="password" name="pwd" />
						</label>

				';

				ob_start();
				do_action( 'coursepress_form_fields' );
				$content .= ob_get_clean();

				if( apply_filters( 'coursepress_popup_allow_account', true ) ) {
					$content .= '
						<label class="existing-link full">
							' . $signup_link_label . '
						</label>
						<label class="forgot-link half-left">
							<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot Password?', 'CP_TD' ) . '</a>
						</label>
						';
				}

				if ( $show_submit ) {
					$content .= '
						<label class="submit-link full-right">
							<!--<input type="submit" ' . esc_attr( $submit_button_attributes ) . ' class="' . esc_attr( $course_id ) . '" value="' . esc_attr( $submit_button_label ) . '"/>-->
						</label>
							';
				}

				$content .= '
						<input name="testcookie" value="1" type="hidden">
						<input name="course_signup_login" value="1" type="hidden">
				';

				ob_start();
				do_action( 'coursepress_before_end_form_fields' );
				$content .= ob_get_clean();

				$content .= '</form>';

				ob_start();
				do_action( 'coursepress_after_login_form' );
				$content .= ob_get_clean();

				break;
		}

		return $content;
	}

	public static function module_status( $atts ) {
		ob_start();
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'unit_id' => CoursePress_Helper_Utility::the_post( true ),
			'previous_unit' => false,
			'message' => __( '%d of %d mandatory elements completed.', 'CP_TD' ),
			'format' => 'true',
		), $atts, 'module_status' ) );

		$message = sanitize_text_field( $message );
		$format = sanitize_text_field( $format );
		$format = 'true' == $format ? true : false;

		$course_id = (int) $course_id;
		$unit_id = (int) $unit_id;
		$previous_unit_id = empty( $previous_unit ) ? false : (int) $previous_unit;

		if ( empty( $unit_id ) || empty( $course_id ) ) {
			return '';
		}

		$unit_status = CoursePress_Data_Unit::get_unit_availability_status( $course_id, $unit_id, $previous_unit );
		$unit_available = $unit_status['available'];

		$content = '<span class="unit-archive-single-module-status">';

		if ( $unit_available ) {
			$content .= do_shortcode( '[course_mandatory_message course_id="' . $course_id . '" unit_id="' . $unit_id . '" message="' . $message . '"]' );
		} else {
			if ( $unit_status['mandatory_required']['enabled'] && ! $unit_status['mandatory_required']['result'] && ! $unit_status['completion_required']['enabled'] ) {
				$content .= esc_html__( 'All mandatory answers are required in previous unit.', 'CP_TD' );
			} elseif ( $unit_status['completion_required']['enabled'] && ! $unit_status['completion_required']['result'] ) {
				$content .= esc_html__( 'Previous unit must be completed successfully.', 'CP_TD' );
			}
			if ( ! $unit_status['date_restriction']['result'] ) {
				$date = get_post_meta( $unit_id, 'unit_availability', true );
				$content .= esc_html__( 'Available', 'CP_TD' ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( $date ) );
			}
		}

		$content .= '</span>';

		return $content;
	}

	public static function course_social_links( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'services' => 'facebook,twitter,google,email',
			'share_title' => __( 'Share', 'CP_TD' ),
			'echo' => false,
		), $atts, 'course_page' );

		$course_id = (int) $atts['course_id'];
		$echo = cp_is_true( $atts['echo'] );
		$services = explode( ',', sanitize_text_field( $atts['services'] ) );
		$share_title = sanitize_text_field( $atts['share_title'] );
		$share_title = ! empty( $share_title ) ? '<span class="share-title">' . $share_title . '</span>' : $share_title;
		$services_content = '';

		$course_title = get_post_field( 'post_title', $course_id );
		$course_url = get_permalink( $course_id );
		$course_summary = get_post_field( 'post_excerpt', $course_id );
		$course_image = CoursePress_Data_Course::get_setting( $course_id, 'listing_image' );

		foreach ( $services as $service ) {
			switch ( $service ) {
				case 'facebook':
					$service_title = '<span class="dashicons dashicons-facebook"></span>';
					$services_content .= '<a href="http://www.facebook.com/sharer/sharer.php?s=100&p[url]=' . $course_url . '&p[images][0]=' . $course_image . '&p[title]=' . $course_title . '&p[summary]=' . urlencode( strip_tags( $course_summary ) ) . '" class="facebook-share" target="_blank"><span class="service-title">' . $service_title . '</span></a>';
					$services_content .= ' ';
					break;

				case 'twitter':
					$service_title = '<span class="dashicons dashicons-twitter"></span>';
					$services_content .= '<a href="http://twitter.com/home?status=' . $course_title . ' (' . $course_url . ')" class="twitter-share" target="_blank"><span class="service-title">' . $service_title . '</span></a>';
					$services_content .= ' ';
					break;

				case 'google':
					$service_title = '<span class="dashicons dashicons-googleplus"></span>';
					$services_content .= '<a href="https://plus.google.com/share?url=' . $course_url . '" class="google-share" target="_blank"><span class="service-title">' . $service_title . '</span></a>';
					$services_content .= ' ';
					break;

				case 'email':
					$service_title = '<span class="dashicons dashicons-email-alt"></span>';
					$services_content .= '<a href="mailto:?subject=' . $course_title . '&body=' . strip_tags( $course_summary ) . ' ( ' . $course_url . ' )" target="_top" class="email-share"><span class="service-title">' . $service_title . '</span></a>';
					$services_content .= ' ';
					break;

				default:
					$services_content .= apply_filters( 'coursepress_social_link_' . $service, '', $course_id );
					$services_content .= ' ';
					break;
			}
		}

		$content = '';

		if ( ! empty( $services_content ) ) {
			$content .= '
				<div class="coursepress-course-share">
					' . $share_title . '
					' . $services_content . '
				</div>
			';
		}

		if ( $echo ) {
			echo $content;
		}

		return $content;
	}

}
