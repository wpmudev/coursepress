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
class CoursePress_Data_Shortcode_CourseTemplate extends CoursePress_Utility {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public function init() {

		add_shortcode( 'course_join_button', array( $this, 'course_join_button' ) );
		add_shortcode( 'unit_archive_list', array( $this, 'unit_archive_list' ) );
		add_shortcode( 'course_featured', array( $this, 'course_featured' ) );
		add_shortcode( 'course_structure', array( $this, 'course_structure' ) );
		add_shortcode( 'course_list', array( $this, 'course_list' ) );
		add_shortcode( 'course_calendar', array( $this, 'course_calendar' ) );
		add_shortcode( 'course_social_links', array( $this, 'course_social_links' ) );
		add_shortcode( 'course_discussion', array( $this, 'course_discussion' ) );
		add_shortcode( 'units_dropdown', array( $this, 'units_dropdown' ) );
		add_shortcode( 'course_units', array( $this, 'course_units' ) );
		add_shortcode( 'course_breadcrumbs', array( $this, 'course_breadcrumbs' ) );
		// Callback to use when course structure block requires reloading
		add_action( 'init', array( $this, 'maybe_reload_course_structure' ) );
	}

	/**
	 * Shows the course join button.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function course_join_button( $atts ) {

		global $enrollment_process_url, $signup_url;

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'access_text' => __( 'Start Learning', 'cp' ),
			'class' => '',
			'continue_learning_text' => __( 'Continue Learning', 'cp' ),
			'course_expired_text' => __( 'Not available', 'cp' ),
			'course_full_text' => __( 'Course Full', 'cp' ),
			'details_text' => __( 'Details', 'cp' ),
			'enrollment_closed_text' => __( 'Enrollments Closed', 'cp' ),
			'enrollment_finished_text' => __( 'Enrollments Finished', 'cp' ),
			'enroll_text' => __( 'Enroll Now!', 'cp' ),
			'instructor_text' => __( 'Access Course', 'cp' ),
			'list_page' => false,
			'not_started_text' => __( 'Not Available', 'cp' ),
			'passcode_text' => __( 'Passcode Required', 'cp' ),
			'prerequisite_text' => __( 'Pre-requisite Required', 'cp' ),
			'signup_text' => __( 'Enroll Now!', 'cp' ),
		), $atts, 'course_join_button' );

		// Check course ID.
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}

		$course = coursepress_get_course( $course_id );
		if ( is_wp_error( $course ) ) {
			return '';
		}

		$list_page = sanitize_text_field( $atts['list_page'] );
		$list_page = coursepress_is_true( $atts['list_page'] );
		$class = sanitize_html_class( $atts['class'] );

		$course_url = $course->get_permalink();
		// @todo Use permission check.
		$can_update_course = true;

		if ( $can_update_course ) {
			$enroll_text = __( 'Enroll', 'cp' );
		}

		$now = $this->date_time_now();
		$general_settings = coursepress_get_setting( 'general' );

		$course->enroll_type = coursepress_course_get_setting( $course_id, 'enrollment_type' );
		$course->course_start_date = coursepress_course_get_setting( $course_id, 'course_start_date' );
		$course->course_start_date = $this->strtotime( $course->course_start_date );
		$course->course_end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$course->enrollment_start_date = coursepress_course_get_setting( $course_id, 'enrollment_start_date' );
		$course->enrollment_end_date = coursepress_course_get_setting( $course_id, 'enrollment_end_date' );
		$course->open_ended_course = coursepress_is_true( coursepress_course_get_setting( $course_id, 'course_open_ended' ) );
		$course->open_ended_enrollment = coursepress_is_true( coursepress_course_get_setting( $course_id, 'enrollment_open_ended' ) );
		$course->prerequisite = coursepress_get_enrollment_prerequisite( $course_id );
		$course->is_paid = coursepress_is_true( coursepress_course_get_setting( $course_id, 'payment_paid_course' ) );
		$course->course_started = ! $course->open_ended_course && ! empty( $course->course_end_date ) && $this->strtotime( $course->course_start_date ) <= $now ? true : false;
		$course->enrollment_started = $this->strtotime( $course->enrollment_start_date ) <= $now ? true : false;
		$course->course_expired = ! $course->open_ended_course && ! empty( $course->course_end_date ) && $this->strtotime( $course->course_end_date ) <= $now ? true : false;
		$course->enrollment_expired = ! empty( $course->enrollment_end_date ) && $this->strtotime( $course->enrollment_end_date ) <= $now ? true : false;
		$course->full = $course->is_students_full();

		$button = '';
		$button_option = '';
		$button_url = $enrollment_process_url;
		$is_form = false;

		$student_enrolled = false;
		$student_id = false;
		$is_instructor = false;
		$is_custom_login = coursepress_is_true( $general_settings['use_custom_login'] );
		$course_link = esc_url( trailingslashit( get_permalink( $course_id ) ) . trailingslashit( coursepress_get_setting( 'slugs/units', 'units' ) ) );
		$continue_learning_link = null;

		if ( is_user_logged_in() ) {
			$student_id = get_current_user_id();
			$student = coursepress_get_user();
			$student_enrolled = $student->is_enrolled_at( $course_id );
			$is_instructor = $student->is_instructor_at( $course_id );
			$course_progress = $student->get_course_progress( $course_id );
			if ( 100 === $course_progress ) {
				$continue_learning_text = __( 'Completed', 'cp' );
				$class .= ' course-completed-button';
			} else {
				$meta_key = CoursePress_Data_Course::get_last_seen_unit_meta_key( $course_id );
				$last_seen_unit = get_user_meta( $student_id, $meta_key, true );
				if ( is_array( $last_seen_unit ) && isset( $last_seen_unit['unit_id'] ) && isset( $last_seen_unit['page'] ) ) {
					$unit = coursepress_get_unit( $last_seen_unit['unit_id'] );
					if ( ! is_wp_error( $unit ) ) {
						$continue_learning_link = $course_link = $unit->get_permalink();
					}
				}
			}
		} else {
			$course_url = add_query_arg(
				array(
					'action' => 'enroll_student',
					'_wpnonce' => wp_create_nonce( 'enroll_student' ),
				),
				$course_url
			);
			if ( false === $is_custom_login ) {
				$signup_url = wp_login_url( $course_url );
			} else {
				$signup_url = coursepress_get_student_login_url();
				$signup_url = add_query_arg(
					array(
						'redirect_to' => urlencode( $course_url ),
						'_wpnonce' => wp_create_nonce( 'redirect_to' ),
					),
					$signup_url
				);
			}
		}

		$is_single = CoursePress_Helper_Utility::$is_singular;
		$buttons = apply_filters(
			'coursepress_course_enrollment_button_options',
			array(
				'full' => array(
					'label' => sanitize_text_field( $atts['course_full_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-full ' . $class,
					),
					'type' => 'label',
				),
				'expired' => array(
					'label' => sanitize_text_field( $atts['course_expired_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-finished ' . $class,
					),
					'type' => 'label',
				),
				'enrollment_finished' => array(
					'label' => sanitize_text_field( $atts['enrollment_finished_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-enrollment-finished ' . $class,
					),
					'type' => 'label',
				),
				'enrollment_closed' => array(
					'label' => sanitize_text_field( $atts['enrollment_closed_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-enrollment-closed ' . $class,
					),
					'type' => 'label',
				),
				'enroll' => array(
					'label' => sanitize_text_field( $enroll_text ),
					'attr' => array(
						'class' => $can_update_course ? 'apply-button' : 'apply-button enroll ' . $class,
						'data-link' => esc_url( $signup_url . '?course_id=' . $course_id ),
						'data-course-id' => $course_id,
					),
					'type' => 'form_button',
				),
				'signup' => array(
					'label' => sanitize_text_field( $atts['signup_text'] ),
					'attr' => array(
						'class' => 'apply-button signup ' . ( $is_custom_login ? 'cp-custom-login ' : '' ) . $class,
						'data-link-old' => $signup_url,//esc_url( $signup_url . '?course_id=' . $course_id ),
						'data-course-id' => $course_id,
						'data-link' => $signup_url,
					),
					'type' => 'link',
				),
				'details' => array(
					'label' => sanitize_text_field( $atts['details_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-details ' . $class,
						'data-link' => esc_url( $course_url ),
					),
					'type' => 'button',
				),
				'prerequisite' => array(
					'label' => sanitize_text_field( $atts['prerequisite_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-prerequisite ' . $class,
					),
					'type' => 'label',
				),
				'passcode' => array(
					'label' => sanitize_text_field( $atts['passcode_text'] ),
					'button_pre' => '<div class="passcode-box"><label>' . esc_html( $atts['passcode_text'] ) . ' <input type="password" name="passcode" /></label></div>',
					'attr' => array(
						'class' => 'apply-button apply-button-passcode ' . $class,
					),
					'type' => 'form_submit',
				),
				'not_started' => array(
					'label' => sanitize_text_field( $atts['not_started_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-not-started  ' . $class,
					),
					'type' => 'label',
				),
				'access' => array(
					'label' => ! $is_instructor ? sanitize_text_field( $atts['access_text'] ) : sanitize_text_field( $atts['instructor_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-enrolled apply-button-first-time ' . $class,
						'data-link' => $course_link,
					),
					'type' => 'link',
				),
				'continue' => array(
					'label' => ! $is_instructor ? sanitize_text_field( $continue_learning_text ) : sanitize_text_field( $atts['instructor_text'] ),
					'attr' => array(
						'class' => 'apply-button apply-button-enrolled ' . $class,
						'data-link' => empty( $continue_learning_link )? CoursePress_Data_Student::get_last_visited_url( $course_id ) : $continue_learning_link,
					),
					'type' => 'link',
				),
			),
			$course_id
		);

		$buttons = apply_filters( 'coursepress_coursetemplate_join_button', $buttons );

		// Determine the button option.
		if ( ! $student_enrolled && ! $is_instructor ) {
			// For vistors and non-enrolled students.
			$enrollment_start_date = $this->strtotime( $course->enrollment_start_date );

			if ( $enrollment_start_date > $now && false === $course->open_ended_enrollment ) {
				return ''; // Bail do not show the button
			}

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
					$prerequisites = is_array( $prerequisites ) ? $prerequisites : array();

					$completed = 0;
					$all_complete = false;

					foreach ( $prerequisites as $prerequisite ) {
						if ( empty( $student ) ) {
							$student = coursepress_get_user( $student_id );
						}
						if ( $student->is_enrolled_at( $prerequisite ) && $student->is_course_completed( $prerequisite ) ) {
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

			if ( empty( $button_option ) || 'enroll' == $button_option ) {

				$user_can_register = $this->users_can_register();

				// Even if user is signed-in, you might wan't to restrict and force an upgrade.
				// Make sure you know what you're doing and that you don't block everyone from enrolling.
				$force_signup = apply_filters( 'coursepress_course_enrollment_force_registration', false );

				if ( ( empty( $student_id ) && $user_can_register && empty( $button_option ) ) || $force_signup ) {
					// If the user is allowed to signup, let them sign up
					$button_option = 'signup';
				} elseif ( ! empty( $student_id ) && empty( $button_option ) ) {

					// If the user is not enrolled, then see if they can enroll.
					switch ( $course->enroll_type ) {
						default:
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
								if ( empty( $student ) ) {
									$student = coursepress_get_user( $student_id );
								}
								if ( $student->is_enrolled_at( $prerequisite ) && $student->is_course_completed( $course_id ) ) {
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
			}
		} else {
			// For already enrolled students.
			if ( empty( $student ) ) {
				$student = coursepress_get_user( $student_id );
			}
			$progress = $student->get_course_progress( $course_id );

			if ( $course->course_expired && ! $course->open_ended_course ) {
				// COURSE EXPIRED
				$button_option = 'expired';
			} elseif ( $course->course_start_date > $now ) {
				// COURSE HASN'T STARTED
				$button_option = 'not_started';
			} elseif ( ! is_single() && false === strpos( $_SERVER['REQUEST_URI'], coursepress_get_setting( 'slugs/student_dashboard', 'courses-dashboard' ) ) ) {
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
		if ( ( ! is_single() && ! is_page() ) || $list_page ) {
			$button_url = get_permalink( $course_id );
			global $post;
			if ( ! is_wp_error( coursepress_get_course( $post ) ) ) {
				$button = '<button data-link="' . esc_url( $button_url ) . '" class="apply-button apply-button-details ' . esc_attr( $class ) . '">' . esc_html( $atts['details_text'] ) . '</button>';
			} else {
				$button = '<a href="' . esc_url( $button_url ) . '" class="apply-button apply-button-details ' . esc_attr( $class ) . '">' . esc_html( $atts['details_text'] ) . '</a>';
			}
		} else {
			if ( empty( $button_option ) || ( 'manually' == $course->enroll_type && ! ( 'access' == $button_option || 'continue' == $button_option ) ) ) {
				return apply_filters( 'coursepress_enroll_button', $button, $course_id, $student_id, $button_option );
			}

			$button_pre = isset( $buttons[ $button_option ]['button_pre'] ) ? $buttons[ $button_option ]['button_pre'] : '';
			$button_post = isset( $buttons[ $button_option ]['button_post'] ) ? $buttons[ $button_option ]['button_post'] : '';

			// If there is no script, made a regular link instead of button.
			$is_wp_script = wp_script_is( 'coursepress-front-js' );
			if ( empty( $is_wp_script ) ) {
				// Fix button on shortcode.
				if ( 'enroll' == $button_option ) {
					$button_option = 'details';
				}
				$buttons[ $button_option ]['type'] = 'link';
			}

			$tag_content = esc_html( $buttons[ $button_option ]['label'] );

			switch ( $buttons[ $button_option ]['type'] ) {
				case 'label':
					$button = $this->create_html( 'span', $buttons[ $button_option ]['attr'], $tag_content );
					break;

				case 'form_button':
					$button = $this->create_html( 'button', $buttons[ $button_option ]['attr'], $tag_content );
					$is_form = true;
					break;

				case 'form_submit':
					$buttons[ $button_option ]['attr']['type'] = 'submit';
					$button = $this->create_html( 'input', $buttons[ $button_option ]['attr'], $tag_content );
					$is_form = true;
					break;

				case 'button':
					$button = $this->create_html( 'button', $buttons[ $button_option ]['attr'], $tag_content );
					break;
				case 'link':
					$url = $buttons[ $button_option ]['attr']['data-link'];
					$buttons[ $button_option ]['attr']['href'] = $url;
					$button = $this->create_html( 'a', $buttons[ $button_option ]['attr'], $tag_content );
					break;
			}

			$button = $button_pre . $button . $button_post;
		}

		// Remove enrol button for instructors.
		if ( is_user_logged_in() ) {
			if ( empty( $student ) ) {
				$student = coursepress_get_user();
			}
			if ( $student->is_instructor_at( $course_id ) ) {
				return '';
			}
		}

		// Wrap button in form if needed.
		if ( $is_form ) {
			$button = '<form name="enrollment-process" method="post" data-type="'. $button_option . '" action="' . $button_url . '">' . $button;
			$button .= sprintf( '<input type="hidden" name="student_id" value="%s" />', get_current_user_id() );

			if ( 'enroll' == $button_option ) {
				$button .= wp_nonce_field( 'enrollment_process', '_wpnonce', true, false );
			}

			$button .= '<input type="hidden" name="course_id" value="' . $course_id . '" />';
			$button .= '</form>';
		}

		return apply_filters( 'coursepress_enroll_button', $button, $course_id, $student_id, $button_option );
	}

	/**
	 * Gets the Unit archive as a list
	 *
	 * @since 2.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function unit_archive_list( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'with_modules' => false,
			'description' => false,
			'knob_data_width' => '40',
			'knob_data_height' => '40',
			'knob_fg_color' => '#24bde6',
			'knob_bg_color' => '#e0e6eb',
			'knob_data_thickness' => '0.18',
		), $atts, 'unit_archive_list' );

		coursepress_log_student_activity( 'course_unit_seen' );

		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}

		$course = coursepress_get_course( $course_id );
		if ( is_wp_error( $course ) ) {
			return '';
		}

		$with_modules = coursepress_is_true( $atts['with_modules'] );
		$course_base_url = coursepress_get_course_permalink( $course_id );
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		if ( ! $with_modules ) {
			$unit_mode = coursepress_course_get_setting( $course_id, 'structure_level', 'unit' );
			$with_modules = 'section' == $unit_mode;
		}

		$view_mode = coursepress_course_get_setting( $course_id, 'course_view', 'normal' );

		$knob_fg_color = sanitize_text_field( $atts['knob_fg_color'] );
		$knob_bg_color = sanitize_text_field( $atts['knob_bg_color'] );
		$knob_data_thickness = sanitize_text_field( $atts['knob_data_thickness'] );
		$knob_data_width = (int) $atts['knob_data_width'];
		$knob_data_height = (int) $atts['knob_data_height'];

		$student_id = get_current_user_id();
		$student = coursepress_get_user( $student_id );
		$instructors = $course->get_instructors();
		$is_instructor = is_array( $instructors ) && in_array( $student_id, array_keys( $instructors ) );

		// Show empty units?
		$show_empty_units = coursepress_is_true( coursepress_course_get_setting( $course_id, 'structure_show_empty_units' ) );

		$content = '';

		$unit_status = array( 'publish' );
		if ( current_user_can( 'manage_options' ) || $is_instructor ) {
			$unit_status[] = 'draft';
		}

		if ( $with_modules ) {
			$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $unit_status );
		} else {
			$units = $course->get_units( $course_id, $unit_status );
		}

		$units = $this->sort_on_key( $units, 'order' );

		$content .= sprintf( '<div class="unit-archive-list-wrapper" data-view-mode="%s">', esc_attr( $view_mode ) );

		$enrolled = false;
		$student_progress = false;
		if ( ! empty( $student ) ) {
			$enrolled = $student->is_enrolled_at( $course_id );
		}

		if ( $enrolled ) {
			$student_progress = $student->get_completion_data( $course_id );
		}

		$is_course_available = $course->is_available();
		$clickable = true;
		$previous_unit_id = false;
		$last_module_id = false;
		$current_last_module_id = false;

		// Units.
		$content_units = '';
		foreach ( $units as $unit ) {
			$the_unit = $with_modules ? $unit['unit'] : $unit;
			$unit_id = $the_unit->ID;

			if ( ! empty( $current_last_module_id ) ) {
				$last_module_id = $current_last_module_id;
			}
			if ( ! empty( $units_with_modules[ $unit_id ]['pages'] ) && is_array( $units_with_modules[ $unit_id ]['pages'] ) ) {
				$last_page = end( $units_with_modules[ $unit_id ]['pages'] );
				if ( ! empty( $last_page['modules'] ) && is_array( $last_page['modules'] ) ) {
					end( $last_page['modules'] );
					$current_last_module_id	= key( $last_page['modules'] );
				}
			}

			// Hide hidden unit
			$is_unit_structure_visible = CoursePress_Data_Unit::is_unit_structure_visible( $course_id, $unit_id, $student_id );
			if ( ! $is_unit_structure_visible ) {
				continue;
			}

			$unit_original = $unit;
			if ( is_object( $unit ) ) {
				$unit = get_object_vars( $unit );
			}

			$can_view = CoursePress_Data_Course::can_view_unit( $course_id, $unit_id );

			$is_unit_available = $is_course_available ? $unit_original->is_available() : $is_course_available;
			$previous_unit_id = $unit_id;

			if ( ! $can_view ) {
				continue;
			}

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

			if ( ! $can_update_course && $last_module_id > 0 && $clickable ) {
				// Check if the last module is already answered.
				$is_last_module_done = CoursePress_Data_Module::is_module_done_by_student( $last_module_id, $student_id );
				if ( ! $is_last_module_done ) {
					$clickable = false;
				}
			}

			if ( ( ! $can_update_course && $enrolled && ! $is_unit_available ) || ! $clickable ) {
				$additional_class = 'locked-unit';
				$additional_li_class = 'li-locked-unit';
			}

			if ( $enrolled ) {
				// Check if unit is completed
				$is_unit_completed = $student->is_unit_completed( $course_id, $unit_id );
				if ( $is_unit_completed ) {
					$additional_li_class .= ' unit-li-completed';
				} else {
					// Check if the first section/page is seen
					$is_first_page_seen = CoursePress_Data_Student::is_section_seen( $course_id, $unit_id, 1, $student_id );
					if ( $clickable && $is_first_page_seen ) {
						$additional_li_class .= ' unit-seen';
					}
				}
			}

			if ( ! $enrolled ) {
				$unit_progress = '';
				if ( ! $is_unit_available && ! $can_view ) {
					continue;
				}
			}

			$unit_feature_image = get_post_meta( $unit_id, 'unit_feature_image', true );
			$unit_image = $unit_feature_image ? '<div class="circle-thumbnail"><div class="unit-thumbnail"><img src="' . $unit_feature_image . '"" alt="' . $the_unit->post_title . '" /></div></div>' : '';

			/**
			 * unit content
			 */
			$unit_content = '';
			if ( ! empty( $the_unit->post_content ) ) {
				$unit_content = sprintf(
					'<div class="unit-content">%s</div>',
					wpautop( htmlspecialchars_decode( $the_unit->post_content ) )
				);
			}

			$title_suffix = '';
			if ( 'publish' != $the_unit->post_status && $can_update_course ) {
				$title_suffix = esc_html__( ' [DRAFT]', 'cp' );
			}

			if ( ! $is_unit_available && $enrolled ) {
				$unit_status = do_shortcode(
					'[module_status format="true" unit_id="' . $unit_id . '" previous_unit="' . $previous_unit_id . '"]'
				);
				$unit_status = strip_tags( $unit_status );
			}
			if ( ! $clickable ) {
				$unit_status = __( 'This unit is available, but you need to complete all the REQUIRED modules before this unit.', 'cp' );
				$is_unit_available = false;
			}

			$add_open_date = false;
			if ( ! $is_unit_available && $enrolled ) {
				$add_open_date = true;
			}

			/**
			 * Filter allow to display open unit date.
			 *
			 * @since 2.0.4
			 *
			 * @param boolean $add_open_date Current state of display open * unit date.
			 * @param integer $unit_id Unit ID.
			 * @param integer $course_id Course ID.
			 */
			$add_open_date = apply_filters( 'coursepress_unit_add_open_date', $add_open_date, $unit_id, $course_id );

			if ( $add_open_date ) {
				/**
				 * return date with known format
				 */
				$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id, 'c' );
				$_unit_date = $this->strtotime( $unit_availability_date );
				$now = $this->date_time_now();

				if ( ! empty( $unit_availability_date ) && $_unit_date > $now && 'expired' != $unit_availability_date ) {
					$status_type = get_post_meta( $unit_id, 'unit_availability', true );
					if ( 'instant' == $status_type ) {
						$unit_status = esc_attr__( 'You need to complete the REQUIRED unit before this unit.', 'cp' );
					} else {
						$unit_availability_date = $this->strtotime( $unit_availability_date );
						$year_now = date( 'Y', $this->date_time_now() );
						$unit_year = date( 'Y', $unit_availability_date );
						$format = $year_now !== $unit_year ? _x( 'M d, Y', 'Unit available date with year for future unit.', 'cp' ) : _x( 'M d', 'Unit available date without year for future unit.', 'cp' );
						// Requires custom hook to attached
						$when = date_i18n( $format, $unit_availability_date );

						$delay_date = sprintf(
							'<span class="unit-delay-date">%s</span>',
							sprintf( __( 'Opens %s', 'cp' ), $when )
						);
						$unit_status = __( 'This unit will be available on the scheduled start date.', 'cp' );
						/**
						 * Filter delay date markup.
						 *
						 * @since 2.0
						 *
						 * @param (string) $delay_date 	The HTML markup.
						 * @param (date) $unit_availability_date	The date the unit becomes available.
						 *
						 * @return $date or null
						 **/
						$delay_date = apply_filters( 'coursepress_unit_delay_markup', $delay_date, $unit_availability_date );
						$title_suffix .= $delay_date;
					}
				}
			}

			if ( $is_unit_available || $can_update_course ) {
				$unit_url = coursepress_get_unit_permalink( $unit_id );
			} else {
				$unit_url = remove_query_arg( 'dummy-query' );
			}

			$unit_link = sprintf(
				'<a class="unit-archive-single-title" href="%s" data-original-href="%s" rel="bookmark">%s %s</a>',
				esc_url( $unit_url ),
				esc_url( $unit_url ),
				$the_unit->post_title,
				$title_suffix
			);

			if ( ( ! $is_unit_available && ! $can_update_course ) || ( ! $clickable && ! $can_update_course ) ) {
				$unit_link = sprintf( '<span class="unit-archive-single-title">%s</span>', $the_unit->post_title . ' ' . $title_suffix );
			}

			if ( $with_modules ) {
				$has_pages = isset( $unit['pages'] ) && ! empty( $unit['pages'] );
			} else {
				$found = get_posts(
					array(
						'post_type' => 'module',
						'post_status' => $can_update_course ? 'any' : 'publish',
						'post_parent' => $the_unit->ID,
						'posts_per_page' => 1,
						'fields' => 'ids',
					)
				);

				$has_pages = count( $found ) > 0;
			}

			// Don't show units without modules/elements.
			if ( ! $show_empty_units && ! $has_pages && ! $can_update_course ) {
				continue;
			}

			$unit_data = '';
			if ( ! empty( $unit_status ) && ! $can_update_course && ! is_array( $unit_status ) ) {
				$unit_data = sprintf( ' data-title="%s"', esc_attr( $unit_status ) );
			}

			$module_table = '';
			if ( ( $is_unit_available && $with_modules ) || $can_update_course ) {
				$unit['pages'] = isset( $unit['pages'] ) ? $unit['pages'] : array();

				foreach ( $unit['pages'] as $page_number => $page ) {

					// Hide pages not set to be visible
					$is_page_structure_visible = CoursePress_Data_Unit::is_page_structure_visible( $course_id, $unit_id, $page_number, $student_id );
					if ( ! $is_page_structure_visible ) {
						continue;
					}

					if ( ! CoursePress_Data_Course::can_view_page( $course_id, $unit_id, $page_number ) ) {
						continue;
					}

					$heading_visible = isset( $page['visible'] ) && $page['visible'];
					$module_table .= '<li>';

					if ( $heading_visible ) {
						$section_class   = 'section-title';
						$is_section_seen = CoursePress_Data_Student::is_section_seen( $course_id, $unit_id, $page_number );
						$section_data    = '';

						if ( $is_section_seen ) {
							$section_class .= ' section-seen';
						}

						if ( ! $can_update_course && $last_module_id > 0 && $clickable ) {
							// Check if the last module is already answered.
							$is_last_module_done = CoursePress_Data_Module::is_module_done_by_student( $last_module_id, $student_id );

							if ( ! $is_last_module_done ) {
								$clickable = false;
							}
						}

						if ( ! $clickable ) {
							$section_class = 'section-title section-locked';
							$section_data  = sprintf( ' data-title="%s"', esc_attr__( 'You need to complete all the REQUIRED modules before this section.', 'cp' ) );
						}

						$section_link = sprintf( '%spage/%s', $unit_url, $page_number );
						$module_table .= '<div class="' . $section_class . '" data-id="' . $page_number . '"' . $section_data . '>';

						if ( $clickable || $can_update_course ) {
							$module_table .= '<a href="' . $section_link . '">' . ( ! empty( $page['title'] ) ? esc_html( $page['title'] ) : esc_html__( 'Untitled', 'cp' ) ) . '</a>';
						} else {
							$module_table .= sprintf( '<span>%s</span>', ! empty( $page['title'] ) ? esc_html( $page['title'] ) : esc_html__( 'Untitled', 'cp' ) );
						}

						$module_table .= '</div>';

						// Set featured image
						if ( ! empty( $page['feature_image'] ) ) {
							$page_featured_image = sprintf( '<img src="%s" alt="%s" />', esc_url( $page['feature_image'] ), esc_attr( basename( $page['feature_image'] ) ) );
							$module_table        .= '<div class="section-thumbnail">' . $page_featured_image . '</div>';
						}
					}

					// Hide modules of locked section
					if ( ! $clickable ) {
						continue;
					}

					$module_table .= '<ul class="module-list">';
					$prev_module_id = 0;

					$is_previous_module_done = true;
					foreach ( $page['modules'] as $module ) {
						// Hide hidden modules
						$is_module_structure_visible = CoursePress_Data_Unit::is_module_structure_visible( $course_id, $unit_id, $module->ID, $student_id );
						if ( ! $is_module_structure_visible ) { continue; }

						$attributes = CoursePress_Data_Module::attributes( $module->ID );
						$is_required = isset( $attributes['mandatory'] ) && coursepress_is_true( $attributes['mandatory'] );

						if ( ! CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $module->ID, $page_number ) ) {
							continue;
						}

						// Get completion states.
						$module_seen = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/modules_seen/' . $module->ID );
						$module_passed = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $module->ID );
						$module_answered = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $module->ID );

						if ( $prev_module_id > 0 ) {
							$is_done = CoursePress_Data_Module::is_module_done_by_student( $prev_module_id, $student_id );

							if ( ! $can_update_course && ! coursepress_is_true( $is_done ) ) {
								$clickable = false;
							} else {
								$last_module_id = $module->ID;
							}
						}
						$prev_module_id = $module->ID;

						$seen_class = isset( $module_seen ) && ! empty( $module_seen ) ? 'module-seen' : '';
						$passed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] ? 'module-passed' : '';

						$answered_class = isset( $module_answered ) && ! empty( $module_answered ) && $attributes['mandatory'] ? 'not-assesable module-answered' : '';
						//$completed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
						//$completed_class = empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
						$completed_class = '';
						$is_module_completed = $student->is_module_completed( $course_id, $unit_id, $module->ID );

						if ( $is_required ) {
							$completed_class .= 'module-required';
						}
						if ( $is_module_completed ) {
							$completed_class .= ' module-completed';
						}

						$info = __( 'You need to completed all the REQUIRED modules before this module.', 'cp' );
						$data_title = __( 'Preview', 'cp' );
						if ( ! $clickable ) {
							$seen_class = 'module-locked';
							$completed_class = '';
							$data_title = $info;
						}

						$type_class = get_post_meta( $module->ID, 'module_type', true );
						$module_table .= '<li class="module ' . $type_class .  ' ' . $seen_class . ' ' . $passed_class . ' ' . $answered_class . ' ' . $completed_class . '">';

						$title = ! empty( $module->post_title ) ? esc_html( $module->post_title ) : esc_html__( 'Mod', 'cp' ) . '<br />';

						if ( 'normal' == $view_mode ) {
							$module_table .= sprintf(
								'<div class="module-title" data-id="%s" data-title="%s">%s</div>',
								$module->ID,
								esc_attr( $data_title ),
								$title
							);
						} else {
							$module_link = $unit_url . 'page/'. $page_number . '/module_id/' . $module->ID;
							$module_link = sprintf( '<a href="%s">%s</a>', $module_link, $title );
							if ( ! $clickable && ! $can_update_course ) {
								$module_link = sprintf( '<span>%s</span>', $title );
							}
							$module_table .= sprintf(
								'<div class="module-title" data-id="%s" data-title="%s">%s</div>',
								$module->ID,
								esc_attr( $data_title ),
								$module_link
							);
						}

						$module_table .= '</li>';
					}

					$module_table .= '</ul>';
					$module_table .= '</li>';
				}

				if ( ! empty( $module_table ) ) {
					$module_table = sprintf(
						'<ul class="unit-archive-module-wrapper">%s</ul>',
						$module_table
					);
				}
			}

			if ( ! empty( $module_table ) && ( ( $is_unit_available && $with_modules ) || $can_update_course ) ) {
				$unit_link = '<span class="fold"></span> '.$unit_link;
				$additional_li_class .= ' unfolded';
			}

			$content_units .= '<li class="' . esc_attr( $additional_li_class ) . '"'. $unit_data . '>' .
			                  $unit_image .
			                  '<div class="unit-archive-single">' .
			                  $unit_progress .
			                  //$unit_image .
			                  $unit_link.
			                  $unit_content;

			$content_units .= $module_table;
			$content_units .= '</div></li>';
		}

		if ( empty( $content_units ) ) {
			$content .= sprintf(
				'<h3 class="zero-course-units">%s</h3>',
				esc_html__( 'No visible units in the course currently. Please check back later.', 'cp' )
			);
		} else {
			$content .= sprintf( '<ul class="units-archive-list">%s</ul>', $content_units );
		}

		if ( empty( $units ) ) {
			$content .= '<h3 class="zero-course-units">' . esc_html__( 'No units in the course currently. Please check back later.', 'cp' ) . '</h3>';
		}
		$content .= '</div>';

		return $content;
	}

	/**
	 * Shows a featured course.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function course_featured( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => '',
			'featured_title' => __( 'Featured Course', 'cp' ),
			'button_title' => __( 'Find out more.', 'cp' ),
			'media_type' => '', // Video, image, thumbnail.
			'media_priority' => 'video', // Video, image.
			'class' => '',
		), $atts, 'course_featured' );

		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}

		$featured_title = sanitize_text_field( $atts['featured_title'] );
		$button_title = sanitize_text_field( $atts['button_title'] );
		$media_type = sanitize_text_field( $atts['media_type'] );
		$media_priority = sanitize_text_field( $atts['media_priority'] );
		$class = sanitize_html_class( $atts['class'] );

		$course = get_post( $course_id );

		$content = '';
		$class = 'featured-course featured-course-' . $course_id . ' ' . $class;

		if ( ! empty( $featured_title ) ) {
			$content .= $this->create_html( 'h2', array(), $featured_title );
		}

		$content .= $this->create_html( 'h3', array( 'class' => 'featured-course-title' ), $course->post_title );
		$content .= do_shortcode( '[course_media type="' . $media_type . '" priority="' . $media_priority . '" course_id="' . $course_id . '"]' );

		$content .= $this->create_html( 'div', array( 'class' => 'featured-course-summary' ), do_shortcode( '[course_summary course_id="' . $course_id . '" length="30"]' ) );

		$btn_content = $this->create_html( 'button', array( 'data-link' => esc_url( get_permalink( $course_id ) ) ), esc_html( $button_title ) );
		$content .= $this->create_html( 'div', array( 'class' => 'featured-course-link' ), $btn_content );

		$content = $this->create_html( 'div', array( 'class' => $class ), $content );

		return $content;
	}

	/**
	 * Display course structure.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function course_structure( $atts ) {

		$orig_atts = $atts;

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'free_text' => __( 'Preview', 'cp' ),
			'free_show' => 'true',
			'free_class' => 'free',
			'show_title' => 'no',
			'show_label' => 'no',
			'label_delimeter' => ': ',
			'label_tag' => 'h2',
			'show_divider' => 'yes',
			'show_estimates' => 'no',
			'label' => __( 'Course Structure', 'cp' ),
			'class' => '',
			'deep' => false,
		), $atts, 'course_structure' );

		$course_id = (int) $atts['course_id'];
		$free_text = sanitize_text_field( $atts['free_text'] );
		$show_title = coursepress_is_true( sanitize_text_field( $atts['show_title'] ) );
		$show_label = coursepress_is_true( sanitize_text_field( $atts['show_label'] ) );
		$free_show = coursepress_is_true( sanitize_text_field( $atts['free_show'] ) );
		$show_estimates = coursepress_is_true( sanitize_text_field( $atts['show_estimates'] ) );
		$label_delimeter = sanitize_html_class( $atts['label_delimeter'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$show_divider = coursepress_is_true( sanitize_text_field( $atts['show_divider'] ) );
		$label = sanitize_text_field( $atts['label'] );
		$title = ! empty( $label ) ? '<h3 class="section-title">' . esc_html( $label ) . '</h3>' : $label;
		$class = sanitize_html_class( $atts['class'] );
		$deep = coursepress_is_true( sanitize_text_field( $atts['deep'] ) );
		$view_mode = coursepress_course_get_setting( $course_id, 'course_view', 'normal' );

		$content = '';
		if ( empty( $course_id ) ) {
			return '';
		}

		$structure_visible = coursepress_is_true( coursepress_course_get_setting( $course_id, 'structure_visible' ) );

		if ( ! $structure_visible ) {
			return '';
		}

		$time_estimates = coursepress_is_true( coursepress_course_get_setting( $course_id, 'structure_show_duration' ) );

		$preview = CoursePress_Data_Course::previewability( $course_id );
		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );
		$structure_level = coursepress_course_get_setting( $course_id, 'structure_level' );
		$is_unit_only = 'unit' === $structure_level;

		if ( ! $visibility['has_visible'] ) {
			return '';
		}

		$student_id = is_user_logged_in() ? get_current_user_id() : 0;
		$student = coursepress_get_user( $student_id );
		$course = coursepress_get_course( $course_id );
		$enrolled = false;
		$student_progress = false;

		if ( ! empty( $student_id ) ) {
			$enrolled = is_wp_error( $student ) ? false : $student->is_enrolled_at( $course_id );
		}
		if ( $enrolled ) {
			$student_progress = is_wp_error( $student ) ? false : $student->get_completion_data( $course_id );
		}

		$units = CoursePress_Data_Course::get_units_with_modules( $course_id, array( 'publish' ) );
		$units = $this->sort_on_key( $units, 'order' );

		if ( CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
			$enrolled = true;
		}

		$is_course_available = $course->is_available();
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$enrolled_class = $enrolled ? 'enrolled' : '';
		$o_atts = '';

		foreach ( $orig_atts as $k => $v ) {
			$o_atts .= 'data-' . $k . '="' . esc_attr( $v ) . '"';
		}

		$classes = array(
			'course-structure-block',
			sprintf( 'course-structure-block-%d', $course_id ),
			$enrolled_class,
		);
		$classes[] = $enrolled? 'student-is-enroled' : 'student-not-enroled';

		$content .= sprintf(
			'<div class="%s" data-nonce="%s" data-course="%s" %s>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( wp_create_nonce( 'course_structure_refresh' ) ),
			esc_attr( $course_id ),
			$o_atts
		);

		$content .= $title;

		$course_slug = get_post_field( 'post_name', $course_id );

		$content .= '<ul class="tree">';
		$last_unit = 0;
		$counter = 0;

		/**
		 * $unitname & $paged - needed for "current" class
		 */
		$unitname = get_query_var( 'unitname' );
		$paged = get_query_var( 'paged' );
		$clickable = true;
		$last_module_id = false;

		foreach ( $units as $unit_id => $unit ) {
			$is_unit_visible = CoursePress_Data_Unit::is_unit_structure_visible( $course_id, $unit_id );
			if ( ! $is_unit_visible ) {
				continue;
			}

			$the_unit = $unit['unit'];
			$previous_unit_id = CoursePress_Data_Unit::get_previous_unit_id( $course_id, $the_unit->ID );

			$is_unit_available = $is_course_available ? CoursePress_Data_Unit::is_unit_available( $course_id, $the_unit, $previous_unit_id ) : $is_course_available;

			$unit_link = coursepress_get_unit_permalink( $unit_id );

			$estimation = CoursePress_Data_Unit::get_time_estimation( $unit_id, $units );

			if ( $last_module_id > 0 && $clickable ) {
				// Check if the last module is already answered.
				$is_last_module_done = CoursePress_Data_Module::is_module_done_by_student( $last_module_id, $student_id );

				if ( ! $is_last_module_done ) {
					$clickable = false;
				}
			}

			$unit_title = ( $is_unit_available && $enrolled && $clickable ) || $can_update_course ? '<a href="' . esc_url( $unit_link ) . '">' . esc_html( $unit['unit']->post_title ) . '</a>' : '<span>' . esc_html( $unit['unit']->post_title ) . '</span>';

			$is_current_unit = false;
			$classes = array( 'unit' );
			if ( $unitname == $unit['unit']->post_name ) {
				$classes[] = 'current-unit';
				$is_current_unit = true;
			}

			$content .= sprintf( '<li class="%s">', implode( ' ', $classes ) );

			if ( $can_update_course ) {
				$content .= '<span class="fold"></span>';
			}

			/**
			 * add enroled information to wrapper
			 */
			$content .= sprintf(
				'<div class="unit-title-wrapper" data-student-is-enroled="%d">',
				esc_attr( $enrolled )
			);
			$content .= '<div class="unit-title">' . $unit_title . '</div>';

			$show_structure = false;

			if (
				$free_show
				&& isset( $preview['structure'][ $unit_id ] )
				&& is_array( $preview['structure'][ $unit_id ] )
				&& isset( $preview['structure'][ $unit_id ]['unit_has_previews'] )
				&& coursepress_is_true( $preview['structure'][ $unit_id ]['unit_has_previews'] )
			) {
				if ( empty( $last_unit ) ) {
					$unit_available = true;
				} else {
					$unit_available = $unit->is_available();
				}
				if ( $unit_available ) {
					$content .= '<div class="unit-link"><a href="' . esc_url( $unit_link ) . '">' . $free_text . '</a></div>';
					$show_structure = true;
				}
			}
			$content .= '</div>';

			if (
				! $show_structure
				&& (
					( ! $can_update_course && $is_unit_only )
					|| ( ! $is_unit_available && ! $can_update_course )
					|| ( ! $clickable && ! $can_update_course )
				)
			) {
				continue;
			}

			if ( ! isset( $unit['pages'] ) ) {
				$unit['pages'] = array();
			}

			if ( ! $show_structure && false === $enrolled && false === $can_update_course ) {
				continue;
			}

			$content .= '<ul class="unit-structure-modules">';
			$count = 0;
			ksort( $unit['pages'] );

			foreach ( $unit['pages'] as $key => $page ) {

				if ( false === $enrolled && false === $can_update_course ) {
					if (
						! isset( $preview['structure'][ $unit_id ] )
						|| ! is_array( $preview['structure'][ $unit_id ] )
						|| ! isset( $preview['structure'][ $unit_id ][ $key ] )
						|| ! is_array( $preview['structure'][ $unit_id ][ $key ] )
						|| ! isset( $preview['structure'][ $unit_id ][ $key ]['page_has_previews'] )
						|| ! coursepress_is_true( $preview['structure'][ $unit_id ][ $key ]['page_has_previews'] )
					) {
						continue;
					}
				}

				//	if ( empty( $show_page ) ) { continue; }

				$count += 1;

				$page_link = trailingslashit( $unit_link ) . 'page/' . $key;
				$page_title = empty( $page['title'] ) ? sprintf( __( 'Untitled Page %s', 'cp' ), $count ) : $page['title'];
				$page_title = $enrolled ? '<a href="' . esc_url( $page_link ) . '">' . esc_html( $page_title ) . '</a>' : esc_html( $page_title );

				$classes = array(
					'unit-page',
					sprintf( 'unit-page-%d', $count ),
				);
				if ( $is_current_unit && $paged == $count ) {
					$classes[] = 'current-unit-page';
				}

				if ( $last_module_id > 0 && $clickable ) {
					// Check if the last module is already answered.
					$is_last_module_done = CoursePress_Data_Module::is_module_done_by_student( $last_module_id, $student_id );

					if ( ! $is_last_module_done ) {
						$clickable = false;
					}
				}

				if ( ! $clickable && ! $can_update_course ) {
					$page_title = sprintf( '<span>%s</span>', strip_tags( $page_title ) );
				}

				$content .= sprintf( '<li class="%s">', implode( ' ', $classes ) );

				/**
				 * page is visible?
				 */
				$heading_visible = isset( $page['visible'] ) && $page['visible'];

				if ( $heading_visible && ! empty( $page['modules'] ) ) {
					$preview_class = ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && is_array( $preview['structure'][ $unit_id ] ) ) ? $atts['free_class'] : '';
					$content .= '<div class="unit-page-title-wrapper ' . esc_attr( $preview_class ) . '">';
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
				}

				if ( $enrolled && ! $clickable && ! $can_update_course ) {
					continue;
				}

				// Add Module Level.
				$structure_level = coursepress_course_get_setting( $course_id, 'structure_level', 'unit' );

				if ( $deep || 'section' === $structure_level || 'unit' === $structure_level ) {
					$visibility_count = 0;
					$list_content = '<ul class="page-modules">';
					$prev_module_id = 0;

					foreach ( $page['modules'] as $m_key => $module ) {
						// Hide module if not set as visible
						$is_module_visible = CoursePress_Data_Unit::is_module_structure_visible( $course_id, $unit_id, $m_key, $student_id );
						if ( ! $is_module_visible ) {
							continue;
						}

						$classes = array(
							'module',
							sprintf( 'module-%d', $module->ID ),
						);
						$list_content .= sprintf( '<li class="%s">', implode( ' ', $classes ) );

						$preview_class = ( $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ][ $m_key ] ) ) ? $atts['free_class'] : '';
						$type_class = get_post_meta( $m_key, 'module_type', true );

						$attributes = CoursePress_Data_Module::attributes( $m_key );

						/**
						 * do not show title
						 */
						$show_title = isset( $attributes['show_title'] ) && coursepress_is_true( $attributes['show_title'] );
						if ( ! $show_title ) {
							continue;
						}

						$attributes['course_id'] = $course_id;

						// Get completion states
						$module_seen = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/modules_seen/' . $m_key );
						$module_passed = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $m_key );
						$module_answered = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $m_key );

						$seen_class = isset( $module_seen ) && ! empty( $module_seen ) ? 'module-seen' : '';
						$passed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] ? 'module-passed' : '';
						$answered_class = isset( $module_answered ) && ! empty( $module_answered ) && $attributes['mandatory'] ? 'not-assesable module-answered' : '';
						$completed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
						$completed_class = empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';

						if ( $prev_module_id > 0 ) {
							$is_done = CoursePress_Data_Module::is_module_done_by_student( $prev_module_id, $student_id );
							if ( false === $is_done ) {
								$clickable = false;
							} else {
								$last_module_id = $m_key;
							}
						}
						$prev_module_id = $m_key;

						$list_content .= '
							<div class="unit-page-module-wrapper ' . $preview_class . ' ' . $type_class . ' ' . $passed_class . ' ' . $answered_class . ' ' . $completed_class . ' ' . $seen_class . '">
							';
						$module_link = trailingslashit( $unit_link ) . 'page/' . $key . '#module-' . $m_key;
						$module_title = $module->post_title;
						$module_title = $enrolled ? '<a href="' . esc_url( $module_link ) . '">' . esc_html( $module_title ) . '</a>' : esc_html( $module_title );

						if ( ! $clickable && ! $can_update_course ) {
							$module_title = sprintf( '<span>%s</span>', $module->post_title );
						}

						if ( 'focus' == $view_mode && $free_show && ! $enrolled && ! empty( $preview['structure'][ $unit_id ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ] ) && ! empty( $preview['structure'][ $unit_id ][ $key ][ $m_key ] ) ) {
							$module_link = preg_replace( '/#module-/', '/module_id/', $module_link );
							$list_content .= '<div class="unit-module-preview-link"><a href="' . esc_url( $module_link ) . '">' . $free_text . '</a></div>';
						}

						$visibility_count += 1;
						$list_content .= sprintf(
							'<div class="module-title" data-title="%s">%s</div>',
							esc_attr__( 'Preview', 'cp' ),
							$module_title
						);
						$list_content .= '</div>';
						$list_content .= '</li>';
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
	 * Display course list.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function course_list( $atts ) {

		$atts = shortcode_atts( array(
			'completed_label' => __( 'Completed courses', 'cp' ),
			'context' => 'all', // <blank>, enrolled, completed
			'current_label' => __( 'Current Courses', 'cp' ),
			'dashboard' => false,
			'facilitator_label' => __( 'Facilitated Courses', 'cp' ),
			'facilitator' => '',
			'future_label' => __( 'Starting soon', 'cp' ),
			'incomplete_label' => __( 'Incomplete courses', 'cp' ),
			'instructor_msg' => __( 'The Instructor does not have any courses assigned yet.', 'cp' ),
			'instructor' => '', // Note, one or the other
			'limit' => - 1,
			'manage_label' => __( 'Manage Courses', 'cp' ),
			'order' => 'ASC',
			'orderby' => 'meta', /// possible values: meta, title
			'past_label' => __( 'Past courses', 'cp' ),
			'show_labels' => false,
			'status' => 'publish',
			'student_msg' => sprintf( __( 'You are not enrolled in any courses. <a href="%s">See available courses.</a>', 'cp' ), coursepress_get_main_courses_url() ),
			'student' => '', // If both student and instructor is specified only student will be used
			'suggested_label' => __( 'Suggested courses', 'cp' ),
			'suggested_msg' => __( 'You are not enrolled in any courses.<br />Here are a few you might like, or <a href="%s">see all available courses.</a>', 'cp' ),
			'show_withdraw_link' => false,
			'categories' => '',
		), $atts, 'course_page' );

		$atts = $this->sanitize_recursive( $atts );

		$instructor_list = false;
		$student_list = false;
		$atts['dashboard'] = coursepress_is_true( $atts['dashboard'] );
		$courses = array();
		$content = '';
		$student = 0;
		$include_ids = array();

		// Sanitize show_withdraw_link.
		if ( empty( $atts['student'] ) || 'incomplete' != $atts['status'] ) {
			$atts['show_withdraw_link'] = false;
		}

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
			if ( empty( $include_ids ) ) { return ''; }
		}

		if ( ! empty( $atts['facilitator'] ) ) {
			$facilitator = $atts['facilitator'];
			$atts['context'] = 'facilitator';
			$include_ids = CoursePress_Data_Facilitator::get_facilitated_courses( $facilitator, 'publish', true );

			if ( empty( $include_ids ) ) {
				return '';
			}
		}

		if ( ! empty( $atts['student'] ) ) {
			$include_ids = array();
			$students = explode( ',', $atts['student'] );
			foreach ( $students as $student ) {
				$student = (int) $student;
				if ( $student ) {
					$courses_ids = array();
					$courses_to_add = CoursePress_Data_Student::get_enrolled_courses_ids( $student );
					if ( isset( $atts['status'] ) ) {
						foreach ( $courses_to_add as $course_id ) {
							$status = get_post_status( $course_id );
							if ( 'publish' != $status ) {
								continue;
							}
							$add = true;
							if ( 'publish' != $atts['status'] ) {
								$status = CoursePress_Data_Student::get_course_status( $course_id, $student, false );
								if ( 'completed' == $atts['status'] ) {
									$add = false;
									if ( 'certified' == $status ) {
										$add = true;
									}
								} else {
									if ( 'certified' == $status ) {
										$add = false;
									}
								}
							}
							if ( $add ) {
								$courses_ids[] = $course_id;
							}
						}
					} else {
						$courses_ids = $courses_to_add;
					}
					if ( $courses_ids ) {
						$include_ids = array_unique( array_merge( $include_ids, $courses_ids ) );
					}
				}
			}

			$student_list = true;
		}

		$post_args = array(
			'order' => $atts['order'],
			'post_type' => 'course',
			'post_status' => $atts['status'],
			'posts_per_page' => (int) $atts['limit'],
			'suppress_filters' => true,
			'meta_key' => 'cp_course_start_date',
			'orderby' => 'meta_value_num',
		);

		// Categories.
		if ( ! empty( $atts['categories'] ) ) {
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => 'course_category',
					'field' => 'slug',
					'terms' => preg_split( '/[, ]+/', $atts['categories'] ),
				),
			);
		}

		$test_empty_courses_ids = false;

		switch ( $atts['context'] ) {
			case 'enrolled':
				$test_empty_courses_ids = true;
				$include_ids = CoursePress_Data_Student::get_enrolled_courses_ids( get_current_user_id() );
				break;
			case 'incomplete':
				$test_empty_courses_ids = true;
				$user_id = get_current_user_id();
				$ids = CoursePress_Data_Student::get_enrolled_courses_ids( $user_id );
				foreach ( $ids as $course_id ) {
					$status = CoursePress_Data_Student::get_course_status( $course_id, $user_id, false );
					if ( 'certified' != $status ) {
						$include_ids[] = $course_id;
					}
				}
				break;
			case 'completed':
				$test_empty_courses_ids = true;
				$user_id = get_current_user_id();
				$ids = CoursePress_Data_Student::get_enrolled_courses_ids( $user_id );
				foreach ( $ids as $course_id ) {
					$status = CoursePress_Data_Student::get_course_status( $course_id, $user_id, false );
					if ( 'certified' == $status ) {
						$include_ids[] = $course_id;
					}
				}
				break;
			case 'future':
				unset( $post_args['meta_key'] );
				$post_args['meta_query'] = array(
					array(
						'key' => 'cp_course_start_date',
						'value' => time(),
						'type' => 'NUMERIC',
						'compare' => '>',
					),
				);
				break;
			case 'past':
				unset( $post_args['meta_key'] );
				$post_args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key' => 'cp_course_end_date',
						'compare' => 'EXISTS',
					),
					array(
						'key' => 'cp_course_end_date',
						'value' => 0,
						'type' => 'NUMERIC',
						'compare' => '>',
					),
					array(
						'key' => 'cp_course_end_date',
						'value' => time(),
						'type' => 'NUMERIC',
						'compare' => '<',
					),
				);
				break;
			case 'manage':
				$user_id = get_current_user_id();
				$test_empty_courses_ids = true;
				if ( CoursePress_Data_Capabilities::can_manage_courses( $user_id ) ) {
					$local_args = array(
						'post_type' => 'course',
						'nopaging' => true,
						'fields' => 'ids',
					);
					$include_ids = get_posts( $local_args );
				} else {
					$include_ids = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
					if ( empty( $include_ids ) ) {
						$include_ids = CoursePress_Data_Facilitator::get_facilitated_courses( $user_id, array( 'all' ), true, 0, -1 );
					}
				}
				break;
			case 'all':
				$atts['orderby'] = strtolower( $atts['orderby'] );
				switch ( $atts['orderby'] ) {
					case 'title':
					case 'post_title':
						$post_args['orderby'] = 'title';
						break;
					default:
						$post_args['orderby'] = 'meta_value_num';
						break;
				}
				break;
		}

		if ( $test_empty_courses_ids && empty( $include_ids ) ) {
			// Do nothing if we have empty list.
			$courses = array();
		} else if ( ( ( $student_list || $instructor_list ) && ! empty( $include_ids ) ) || ( ! $student_list && ! $instructor_list ) ) {
			if ( ! empty( $include_ids ) ) {
				$post_args = wp_parse_args( array( 'post__in' => $include_ids ), $post_args );
			}
			$courses = get_posts( $post_args );
		}

		$counter = 0;

		if ( ! $atts['dashboard'] ) {
			foreach ( $courses as $course ) {
				$shortcode_attributes  = array(
					'course_id' => $course->ID,
					'show_withdraw_link' => $atts['show_withdraw_link'],
				);
				$shortcode_attributes = $this->convert_array_to_params( $shortcode_attributes );
				$content .= do_shortcode( '[course_list_box ' . $shortcode_attributes . ']' );
				$counter += 1;
			}
		} else {
			if ( $student_list ) {
				$my_courses = CoursePress_Data_Student::my_courses( $student, $courses );
				$context = $atts['context'];

				if ( isset( $my_courses[ $context ] ) ) {
					$courses = $my_courses[ $context ];
				}
				$courses = array_filter( $courses );

				if ( empty( $courses ) ) {
					if ( $atts['dashboard'] ) {
						$content .= sprintf( '<p class="message">%s</p>', esc_html__( 'You are not enrolled to any course.', 'cp' ) );
					}
				} else {
					$counter += count( $courses );
					$content .= CoursePress_Template_Course::course_list_table( $courses );
				}
			} else {
				foreach ( $courses as $course ) {
					$course_url = get_edit_post_link( $course->ID );
					$content .= do_shortcode( '[course_list_box course_id="' . $course->ID . '" override_button_text="' . esc_attr__( 'Manage Course', 'cp' ) . '" override_button_link="' . esc_url( $course_url ) . '"]' );
					$counter += 1;
				}
			}
		}

		$context = $atts['dashboard'] && $instructor_list ? 'manage' : $atts['context'];

		if ( ( $atts['dashboard'] && ! empty( $counter ) ) || ! empty( $atts['show_labels'] ) ) {
			$label = '';
			$show_empty = false;

			switch ( $context ) {
				case 'enrolled':
				case 'current':
				case 'all':
					$label = $atts['current_label'];
					if ( 0 == $counter ) {
						$show_empty = true;
						$content = sprintf(
							'<p class="message">%s</p>',
							sprintf(
								$atts['student_msg'],
								esc_attr( '/' . coursepress_get_setting( 'slugs/course', 'courses' ) )
							)
						);
					}
					break;

				case 'future':
					$label = $atts['future_label'];
					break;

				case 'incomplete':
					$label = $atts['incomplete_label'];
					break;

				case 'completed':
					$label = $atts['completed_label'];
					break;

				case 'past':
					$label = $atts['past_label'];
					break;

				case 'manage':
					$label = $atts['manage_label'];
					break;

				case 'facilitator':
					$label = $atts['facilitator_label'];
					break;
			}

			if ( $counter || ( 0 === $counter && $show_empty ) ) {
				$content = '<div class="dashboard-course-list ' . esc_attr( $context ) . '">' .
				           '<h3 class="section-title">' . esc_html( $label ) . '</h3>' .
				           $content .
				           '</div>';
			}
		} elseif ( $atts['dashboard'] && 'enrolled' === $context ) {

			$label = $atts['suggested_label'];
			$message = sprintf( $atts['suggested_msg'], esc_url( coursepress_get_main_courses_url() ) );

			$content = '<div class="dashboard-course-list suggested">' .
			           '<h3 class="section-title">' . esc_html( $label ) . '</h3>' .
			           '<p>' . $message . '</p>' .
			           do_shortcode( '[course_random featured_title="" media_type="image" media_priority="image"]' ) .
			           '</div>';

		}

		return $content;
	}

	/**
	 * Shows the course calendar.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function course_calendar( $atts ) {

		global $post;

		$atts = shortcode_atts( array(
			'course_id' => in_the_loop() ? get_the_ID() : false,
			'date_indicator' => 'indicator_light_block',
			'month' => false,
			'next' => __( 'Next &raquo;', 'cp' ),
			'pre' => __( '&laquo; Previous', 'cp' ),
			'year' => false,
		), $atts, 'course_calendar' );

		if ( ! empty( $atts['course_id'] ) ) {
			$course_id = (int) $atts['course_id'];
		}

		$month = sanitize_text_field( $atts['month'] );
		$month = 'true' == $month ? true : false;
		$year = sanitize_text_field( $atts['year'] );
		$year = 'true' == $year ? true : false;
		$pre = sanitize_text_field( $atts['pre'] );
		$next = sanitize_text_field( $atts['next'] );
		$date_indicator = sanitize_text_field( $atts['date_indicator'] );

		if ( empty( $course_id ) ) {
			if ( $post && 'course' == $post->post_type ) {
				$course_id = $post->ID;
			} else {
				$parent_id = do_shortcode( '[get_parent_course_id]' );
				$course_id = 0 != $parent_id ? $parent_id : $course_id;
			}
		}

		if ( ! empty( $month ) && ! empty( $year ) ) {
			$args = array( 'course_id' => $course_id, 'month' => $month, 'year' => $year );
		} else {
			$args = array( 'course_id' => $course_id );
		}

		$args['date_indicator'] = $date_indicator;

		$course_calendar = new CoursePress_Template_Calendar( $args );

		return $course_calendar->create_calendar( $pre, $next );
	}

	public function course_social_links( $atts ) {
		$services = CoursePress_Helper_SocialMedia::get_social_sharing_keys();
		$atts = shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
				'services' => implode( ',', $services ),
				'share_title' => __( 'Share', 'cp' ),
				'echo' => false,
			),
			$atts,
			'course_page'
		);

		$course_id = (int) $atts['course_id'];
		$echo = coursepress_is_true( $atts['echo'] );
		$services = explode( ',', sanitize_text_field( $atts['services'] ) );
		$share_title = sanitize_text_field( $atts['share_title'] );
		$share_title = ! empty( $share_title ) ? '<span class="share-title">' . $share_title . '</span>' : $share_title;
		$services_content = '';

		$course_title = get_post_field( 'post_title', $course_id );
		$course_url = get_permalink( $course_id );
		$course_summary = get_post_field( 'post_excerpt', $course_id );
		$course_image = coursepress_course_get_setting( $course_id, 'listing_image' );

		foreach ( $services as $service ) {
			$is_on = coursepress_is_true( coursepress_get_setting( 'general/social_sharing/'.$service, 1 ) );
			if ( ! $is_on ) {
				continue;
			}
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

	public function course_discussion( $atts ) {
		$course_id = CoursePress_Data_Course::get_current_course_id();

		$allow_discussion = coursepress_course_get_setting( $course_id, 'allow_discussion', false );

		if ( ! coursepress_is_true( $allow_discussion ) ) { return false; }

		$comments_args = array(
			// Change the title of send button.
			'label_submit' => __( 'Send', 'cp' ),
			// Change the title of the reply section.
			'title_reply' => __( 'Write a Reply or Comment', 'cp' ),
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
			'reply_text' => __( 'Reply', 'cp' ),
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

	public function units_dropdown( $atts ) {
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
				$general_title = __( '-- General --', 'cp' );
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

	public function course_units( $atts ) {
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
			$content = __( '0 course units prepared yet. Please check back later.', 'cp' );
		}

		if ( 1 == count( $units ) ) {
			wp_safe_redirect( $last_unit_url );
			exit;
		}

		return $content;
	}

	public function course_breadcrumbs( $atts ) {
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
		$course_url = CoursePress_Data_Course::get_course_url( $course_id ); //get_permalink( $course_id );
		$course_home = CoursePress_Core::get_slug( 'course', true );
		$units_slug = CoursePress_Core::get_slug( 'unit/' );

		switch ( $type ) {
			case 'unit_archive':
				$units_breadcrumbs = sprintf(
					'<div class="units-breadcrumbs"><a href="%s">%s</a> » <a href="%s">%s</a></div>',
					esc_url( $course_home . '/' ),
					esc_html__( 'Courses', 'cp' ),
					esc_url( $course_url ),
					esc_html( $course_name )
				);
				break;

			case 'unit_single':
				$units_breadcrumbs = sprintf(
					'<div class="units-breadcrumbs"><a href="%s">%s</a> » <a href="%s">%s</a> » <a href="%s">%s</a></div>',
					esc_url( $course_home . '/' ),
					esc_html__( 'Courses', 'cp' ),
					esc_url( $course_url ),
					esc_html( $course_name ),
					esc_url( $course_url . $units_slug ),
					esc_html__( 'Units', 'cp' )
				);
				break;
		}

		return $units_breadcrumbs;
	}

	public function maybe_reload_course_structure() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'course_structure_refresh' ) ) {
			$data = $_REQUEST['data'];
			$course_id = (int) $_REQUEST['course_id'];
			$atts = array( 'course_id="' . $course_id . '"' );

			if ( is_array( $data ) && ! empty( $data ) ) {
				foreach ( $data as $k => $v ) {
					$atts[] = "{$k}=\"" . esc_attr( $v ) . '"';
				}
			}
			$shortcode = '[course_structure '. implode( ' ', $atts ) . ']';
			echo do_shortcode( $shortcode );
			exit;
		}
	}
}
