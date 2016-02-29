<?php
/**
 * Shortcode handlers.
 *
 * @package CoursePress
 */

/**
 * Course-template shortcodes.
 * These shortcodes display advanced course templates, like lists or calendar
 * views.
 */
class CoursePress_Data_Shortcode_CourseTemplate {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_shortcode(
			'course_join_button',
			array( __CLASS__, 'course_join_button' )
		);
		add_shortcode(
			'unit_archive_list',
			array( __CLASS__, 'unit_archive_list' )
		);
		add_shortcode(
			'course_featured',
			array( __CLASS__, 'course_featured' )
		);
		add_shortcode(
			'course_structure',
			array( __CLASS__, 'course_structure' )
		);
		add_shortcode(
			'course_list',
			array( __CLASS__, 'course_list' )
		);
		add_shortcode(
			'course_calendar',
			array( __CLASS__, 'course_calendar' )
		);
		add_shortcode(
			'course_social_links',
			array( __CLASS__, 'course_social_links' )
		);
		add_shortcode(
			'course_discussion',
			array( __CLASS__, 'course_discussion' )
		);
		add_shortcode(
			'units_dropdown',
			array( __CLASS__, 'units_dropdown' )
		);
		add_shortcode(
			'course_units',
			array( __CLASS__, 'course_units' )
		);
		add_shortcode(
			'course_breadcrumbs',
			array( __CLASS__, 'course_breadcrumbs' )
		);
	}

	/**
	 * Shows the course join button.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_join_button( $atts ) {
		global $coursepress, $enrollment_process_url, $signup_url, $wp_query;

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
		if ( empty( $course_id ) ) { return ''; }

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

		$buttons = apply_filters(
			'coursepress_course_enrollment_button_options',
			array(
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
			),
			$course_id
		);

		if ( CP_IS_WPMUDEV ) {
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

					if ( count( $prerequisites ) == $completed ) {
						$all_complete = true;
					}

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

						if ( count( $prerequisites ) == $completed ) {
							$all_complete = true;
						}

						if ( $all_complete ) {
							$button_option = 'enroll';
						} else {
							$button_option = 'prerequisite';
						}
						break;
				}
			}
		} else {
			// For already enrolled students.

			$progress = CoursePress_Data_Student::get_course_progress( get_current_user_id(), $course_id );

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

		return apply_filters(
			'coursepress_enroll_button',
			$button,
			$course_id,
			$student_id
		);
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
		if ( empty( $course_id ) ) { return ''; }

		$structure_visible = cp_is_true(
			CoursePress_Data_Course::get_setting( $course_id, 'structure_visible' )
		);

		if ( ! $structure_visible ) { return ''; }

		$time_estimates = cp_is_true(
			CoursePress_Data_Course::get_setting( $course_id, 'structure_show_duration' )
		);

		$preview = CoursePress_Data_Course::previewability( $course_id );
		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );

		if ( ! $visibility['has_visible'] ) { return ''; }

		$student_id = is_user_logged_in() ? get_current_user_id() : 0;
		$enrolled = false;
		$student_progress = false;

		if ( ! empty( $student_id ) ) {
			$enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
		}
		if ( $enrolled ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		}

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

				if ( ! $show_link ) { continue; }

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

				// Add Module Level.
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
							$completed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
							$completed_class = empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';

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
			'knob_data_thickness' => '0.18',
		), $atts, 'unit_archive_list' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

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

		$unit_status = array( 'publish' );
		if ( current_user_can( 'manage_options' ) || $is_instructor ) {
			$unit_status[] = 'draft';
		}

		if ( ! $with_modules ) {
			$units = CoursePress_Data_Course::get_units(
				CoursePress_Helper_Utility::the_course( true ),
				$unit_status
			);
		} else {
			$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $unit_status );
			$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );
		}

		$content .= '<div class="unit-archive-list-wrapper">';
		$content .= count( $units ) > 0 ? '<ul class="units-archive-list">' : '';
		$counter = 0;

		$enrolled = false;
		$student_progress = false;
		if ( ! empty( $student_id ) ) {
			$enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
		}
		if ( $enrolled ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		}

		foreach ( $units as $unit ) {
			$the_unit = $with_modules ? $unit['unit'] : $unit;
			$unit_id = $the_unit->ID;

			$can_view = CoursePress_Data_Course::can_view_unit( $course_id, $unit_id );

			$previous_unit_id = false;
			if ( ! $counter ) {
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

			if ( ! $can_view ) { continue; }

			$scode = sprintf(
				'[course_unit_percent course_id="%s" unit_id="%s" format="true" style="extended" knob_fg_color="%s" knob_bg_color="%s" knob_data_thickness="%s" knob_data_width="%s" knob_data_height="%s"]',
				$course_id,
				$unit_id,
				$knob_fg_color,
				$knob_bg_color,
				$knob_data_thickness,
				$knob_data_width,
				$knob_data_height
			);
			$unit_progress = do_shortcode( $scode );

			$additional_class = '';
			$additional_li_class = '';

			$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $the_unit, $previous_unit );

			if ( $enrolled && ! $is_unit_available ) {
				$additional_class = 'locked-unit';
				$additional_li_class = 'li-locked-unit';
			}

			if ( ! $enrolled ) {
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
						<a class="unit-archive-single-title" href="' . esc_url_raw( get_permalink( CoursePress_Helper_Utility::the_course( true ) ) . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . $post_name ) . '" rel="bookmark">' . $the_unit->post_title . ' ' . ( 'publish' != $the_unit->post_status && current_user_can( 'manage_options' ) ? esc_html__( ' [DRAFT]', 'CP_TD' ) : '' ) . '</a>';

			if ( $enrolled ) {
				$content .= do_shortcode(
					'[module_status format="true" unit_id="' . $unit_id . '" previous_unit="' . $previous_unit_id . '"]'
				);
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
						$completed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
						$completed_class = empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';

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

		if ( empty( $units ) ) {
			$content .= '<h3 class="zero-course-units">' . esc_html__( 'No units in the course currently. Please check back later.' ) . '</h3>';
		}
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
		extract( shortcode_atts(
			array(
				'course_id' => '',
				'featured_title' => __( 'Featured Course', 'CP_TD' ),
				'button_title' => __( 'Find out more.', 'CP_TD' ),
				'media_type' => '', // Video, image, thumbnail.
				'media_priority' => 'video', // Video, image.
				'class' => '',
			),
			$atts,
			'course_featured'
		) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

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
			'pre' => __( '&laquo; Previous', 'CP_TD' ),
			'next' => __( 'Next &raquo;', 'CP_TD' ),
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

		return CoursePress_Data_Calendar::get_calendar( $args, $pre, $next );

		//      $cal = new Course_Calendar( $args ); // @check

		//      return $cal->create_calendar( $pre, $next );
	}

	/**
	 * Display course list.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_list( $atts ) {
		$atts = CoursePress_Helper_Utility::sanitize_recursive(
			shortcode_atts(
				array(
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
					'show_labels' => false,
				),
				$atts,
				'course_page'
			)
		);

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
			'posts_per_page' => (int) $atts['limit'],
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

	public static function course_social_links( $atts ) {
		$atts = shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
				'services' => 'facebook,twitter,google,email',
				'share_title' => __( 'Share', 'CP_TD' ),
				'echo' => false,
			),
			$atts,
			'course_page'
		);

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

	public static function course_discussion( $atts ) {
		$course_id = CoursePress_Data_Course::get_current_course_id();

		$allow_discussion = CoursePress_Data_Course::get_setting( $course_id, 'allow_discussion', false );

		if ( ! cp_is_true( $allow_discussion ) ) { return false; }

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

		$units = CoursePress_Data_Course::get_units( $course_id );

		$dropdown = '<div class="units_dropdown_holder"><select name="units_dropdown" class="units_dropdown">';
		if ( $include_general ) {
			if ( ! $general_title ) {
				$general_title = __( '-- General --', 'CP_TD' );
			}

			$dropdown .= '<option value="">' . esc_html( $general_title ) . '</option>';
		}
		foreach ( $units as $unit ) {
			$dropdown .= sprintf(
				'<option value="%s">%s</option>',
				esc_attr( $unit->ID ),
				esc_html( $unit->post_title )
			);
		}
		$dropdown .= '</select></div>';

		return $dropdown;
	}

	public static function course_units( $atts ) {
		global $coursepress;

		$content = '';

		extract(
			shortcode_atts( array( 'course_id' => 0 ), $atts )
		);

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) {
			$course_id = CoursePress_Data_Course::get_current_course_id();
		}

		$units = CoursePress_Data_Course::get_units( $course_id, 'publish' );

		$user_id = get_current_user_id();

		// Redirect to the parent course page if not enrolled.
		if ( ! current_user_can( 'manage_options' ) ) {

			// If current user is not admin, check if he can access to the units.
			if ( get_current_user_id() != $course->details->post_author ) {

				// Check if user is an author of a course (probably instructor).
				if ( ! current_user_can( 'coursepress_view_all_units_cap' ) ) {

					/*
					 * Check if the instructor, even if it's not the author of
					 * the course, maybe has a capability given by the admin.
					 * If it's not an instructor who made the course, check if
					 * he is enrolled to course.
					 */
					if ( ! CoursePress_Data_Student::is_enrolled_in_course( $user_id, $course_id ) ) {
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
			$unit_url = CoursePress_Data_Unit::get_url( $unit->ID );
			$content .= sprintf(
				'<li><a href="%s">%s</a></li>',
				esc_url( $unit_url ),
				esc_html( $unit->post_title )
			);
			$last_unit_url = $unit_url;
		}

		$content .= '</ol>';

		if ( count( $units ) >= 1 ) {
			$content .= do_shortcode( '[course_discussion]' );
		}

		if ( ! count( $units ) ) {
			$content = __( '0 course units prepared yet. Please check back later.', 'CP_TD' );
		}

		if ( 1 == count( $units ) ) {
			wp_safe_redirect( $last_unit_url );
			exit;
		}

		return $content;
	}

	public static function course_breadcrumbs( $atts ) {
		// Also check why we modify global $units_breadcrumbs here??
		global $course_slug, $units_slug, $units_breadcrumbs; // @check

		extract(
			shortcode_atts(
				array(
					'type' => 'unit_archive',
					'course_id' => 0,
				),
				$atts
			)
		);

		$course_id = (int) $course_id;
		$type = sanitize_html_class( $type );

		if ( empty( $course_id ) ) {
			$course_id = CoursePress_Data_Course::get_current_course_id();
		}

		$post = get_post( $course_id );
		$course_name = $post->post_title;
		$course_url = get_permalink( $course_id );

		switch ( $type ) {
			case 'unit_archive':
				$units_breadcrumbs = sprintf(
					'<div class="units-breadcrumbs"><a href="%s">%s</a> » <a href="%s">%s</a></div>',
					esc_url( home_url( $course_slug . '/' ) ),
					esc_html__( 'Courses', 'CP_TD' ),
					esc_url( $course_url ),
					esc_html( $course_name )
				);
				break;

			case 'unit_single':
				$units_breadcrumbs = sprintf(
					'<div class="units-breadcrumbs"><a href="%s">%s</a> » <a href="%s">%s</a> » <a href="%s">%s</a></div>',
					esc_url( home_url( $course_slug . '/' ) ),
					esc_html__( 'Courses', 'CP_TD' ),
					esc_url( $course_url ),
					esc_html( $course_name ),
					esc_url( $course_url . $units_slug ),
					esc_html__( 'Units', 'CP_TD' )
				);
				break;
		}

		return $units_breadcrumbs;
	}
}
