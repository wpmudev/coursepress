<?php
/**
 * Shortcode handlers.
 *
 * @package CoursePress
 */

/**
 * Unit and module-related shortcodes.
 */
class CoursePress_Data_Shortcode_Unit {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_shortcode(
			'course_unit_details',
			array( __CLASS__, 'course_unit_details' )
		);
		add_shortcode(
			'course_unit_archive_submenu',
			array( __CLASS__, 'course_unit_archive_submenu' )
		);
		add_shortcode(
			'course_unit_submenu',
			array( __CLASS__, 'course_unit_submenu' )
		);
		add_shortcode(
			'module_status',
			array( __CLASS__, 'module_status' )
		);
		add_shortcode(
			'unit_discussion',
			array( __CLASS__, 'unit_discussion' )
		);
		add_shortcode(
			'course_unit_title',
			array( __CLASS__, 'course_unit_title' )
		);
	}

	public static function course_unit_details( $atts ) {
		global $post_id, $wp, $coursepress;

		extract( shortcode_atts(
			apply_filters( 'shortcode_atts_course_unit_details',
				array(
					'unit_id' => 0,
					'field' => 'post_title',
					'format' => 'true',
					'additional' => '2',
					'style' => 'flat',
					'class' => 'course-name-content',
					'tooltip_alt' => __( 'Percent of the unit completion', 'cp' ),
					'knob_fg_color' => '#24bde6',
					'knob_bg_color' => '#e0e6eb',
					'knob_data_thickness' => '.35',
					'knob_data_width' => '70',
					'knob_data_height' => '70',
					'unit_title' => '',
					'unit_page_title_tag' => 'h3',
					'unit_page_title_tag_class' => '',
					'last_visited' => 'false',
					'parent_course_preceding_content' => __( 'Course: ', 'cp' ),
					'student_id' => get_current_user_id(),
				)
			),
			$atts
		) );

		$unit_id = (int) $unit_id;
		$field = sanitize_html_class( $field );
		$format = cp_is_true( $format );
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
		$last_visited = cp_is_true( $last_visited );
		$class = sanitize_html_class( $class );

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$content = '';
		if ( 'permalink' == $field ) {
			// COMPLETION_LOGIC.
			// if ( $last_visited ) {
			//  $last_visited_page = cp_get_last_visited_unit_page( $unit_id );
			//  $unit->details->$field = CoursePress_Data_Unit::get_url( $unit_id, $last_visited_page );
			// } else {
			$unit = get_post( $unit_id );
			$content = get_permalink( $course_id ) . CoursePress_Core::get_slug( 'unit/' ) . $unit->post_name;
			// $unit->details->$field = CoursePress_Data_Unit::get_url( $unit_id );
			// }
		}
		return $content;

		/**
		 * @todo : THIS CODE IS UNREACHABLE...?!?!?!?!
		 */

		// COMPLETION LOGIC

		if ( ! $unit_id ) {
			$unit_id = get_the_ID();
		}

		$unit = new Unit( $unit_id ); // @check
		$class = sanitize_html_class( $class );

		if ( 'is_unit_available' == $field ) {
			$unit->details->$field = Unit::is_unit_available( $unit_id ); // @check
		}

		if ( 'unit_page_title' == $field ) {
			$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
			$page_name = $unit->get_unit_page_name( $paged );
			if ( $unit_title ) {
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
				$unit->details->$field = '<' . $unit_page_title_tag . '' . ( $unit_page_title_tag_class ? ' class="' . $unit_page_title_tag_class . '"' : '' ) . '>' . $page_title_prepend . $unit->get_unit_page_name( $paged ) . '</' . $unit_page_title_tag . '>';
			} else {
				$unit->details->$field = '';
			}
		}

		if ( 'parent_course' == $field ) {
			$course = get_post( $unit->course_id );
			$course_url = get_permalink( $unit->course_id );
			$course_name = $course->post_title;

			$unit->details->$field = $parent_course_preceding_content . '<a href="' . esc_url( $course_url ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $course_name ) . '</a>';
		}

		/* ------------ */

		$front_save_count = 0;

		$modules = Unit_Module::get_modules( $unit_id );
		$mandatory_answers = 0;
		$mandatory = 'no';

		foreach ( $modules as $mod ) {
			$mandatory = get_post_meta( $mod->ID, 'mandatory_answer', true );

			if ( cp_is_true( $mandatory ) ) {
				$mandatory_answers++;
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
				$responses_count++;
			}
		}
		$student_modules_responses_count = $responses_count;

		if ( $student_modules_responses_count > 0 ) {
			$percent_value = $mandatory_answers > 0 ? ( round( ( 100 / $mandatory_answers ) * $student_modules_responses_count, 0 ) ) : 0;
			// In case that student gave answers on all required plus optional questions.
			$percent_value = ( $percent_value > 100 ? 100 : $percent_value );
		} else {
			$percent_value = 0;
		}

		if ( ! $input_modules_count ) {

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

				$percent_value = max( 0, round( ( $grade / $assessable_answers ), 0 ) );

				if ( $format ) {
					if ( $responses == $graded && $responses == $front_save_count ) {
						$format_class = 'grade-active';
					} else {
						$format_class = 'grade-inactive';
					}
					$percent_value = '<span class="' . $format_class . '">' . $percent_value . '</span>';
				}
			} else {
				$student = new Student( $student_id ); // @check

				if ( $student->is_unit_visited( $unit_id, $student_id ) ) {
					$percent_value = 100;
					$format_class = 'grade-active';
				} else {
					$percent_value = 0;
					$format_class = 'grade-inactive';
				}
				if ( $format ) {
					$percent_value = '<span class="' . $format_class . '">' . $percent_value . '</span>';
				}
			}
		}

		// Redirect to the parent course page if not enrolled.
		if ( ! current_user_can( 'manage_options' ) ) {
			if ( ! $coursepress->check_access( $unit->course_id, $unit_id ) ) {
				wp_redirect( get_permalink( $unit->course_id ) );
				exit;
			}
		}

		if ( 'percent' == $field ) {
			$percent_value = CoursePress_Data_Student::get_unit_progress(
				$student_id,
				$unit->course_id,
				$unit_id
			);

			$assessable_input_modules_count = do_shortcode(
				'[course_unit_details field="assessable_input_modules_count"]'
			);

			if ( 'flat' == $style ) {
				$unit->details->$field = '<span class="percentage">' . ( $format ? $percent_value . '%' : $percent_value ) . '</span>';
			} elseif ( 'none' == $style ) {
				$unit->details->$field = $percent_value;
			} else {
				$unit->details->$field = '<a class="tooltip" alt="' . $tooltip_alt . '"><input class="knob" data-fgColor="' . $knob_fg_color . '" data-bgColor="' . $knob_bg_color . '" data-thickness="' . $knob_data_thickness . '" data-width="' . $knob_data_width . '" data-height="' . $knob_data_height . '" data-readOnly=true value="' . $percent_value . '"></a>';
			}
		}

		if ( 'permalink' == $field ) {
			if ( $last_visited ) {
				$last_visited_page = cp_get_last_visited_unit_page( $unit_id );
				$unit->details->$field = CoursePress_Data_Unit::get_url( $unit_id, $last_visited_page );
			} else {
				$unit->details->$field = CoursePress_Data_Unit::get_url( $unit_id );
			}
		}

		if ( 'input_modules_count' == $field ) {
			$front_save_count = 0;
			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {
					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						$front_save_count++;
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
						if ( cp_is_true( $mandatory_answer ) ) {
							$mandatory_answers++;
						}
						// $front_save_count++;
					}
				}
			}

			$unit->details->$field = $mandatory_answers;
		}

		if ( 'assessable_input_modules_count' == $field ) {
			$front_save_count = 0;
			$assessable_answers = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );

				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {
					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
						if ( cp_is_true( $assessable ) ) {
							$assessable_answers++;
						}
						// $front_save_count++;
					}
				}
			}

			if ( isset( $unit->details->$field ) ) {
				$unit->details->$field = $assessable_answers;
			}
		}

		if ( 'student_module_responses' == $field ) {
			$responses_count = 0;
			$mandatory_answers = 0;
			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $module ) {
				$mandatory = get_post_meta( $module->ID, 'mandatory_answer', true );

				if ( cp_is_true( $mandatory ) ) {
					$mandatory_answers++;
				}

				if ( Unit_Module::did_student_respond( $module->ID, $student_id ) ) {
					$responses_count++;
				}
			}

			if ( 'mandatory' == $additional ) {
				if ( $responses_count > $mandatory_answers ) {
					$unit->details->$field = $mandatory_answers;
				} else {
					$unit->details->$field = $responses_count;
				}
				//so we won't have 7 of 6 required answered but required number as a max number
			} else {
				$unit->details->$field = $responses_count;
			}
		}

		if ( 'student_unit_grade' == $field ) {
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
							$front_save_count++;
							$response = call_user_func( $class_name . '::get_response', $student_id, $mod->ID );
							$assessable = cp_is_true(
								get_post_meta( $mod->ID, 'gradable_answer', true )
							);
							$mandatory = cp_is_true(
								get_post_meta( $mod->ID, 'mandatory_answer', true )
							);

							if ( $assessable ) {
								$assessable_answers++;
							}

							if ( isset( $response->ID ) ) {
								if ( $assessable ) {
									$grade_data = Unit_Module::get_response_grade( $response->ID );
									$grade = $grade + $grade_data['grade'];

									if ( get_post_meta( $response->ID, 'response_grade' ) ) {
										$graded++;
									}

									$responses++;
								}
							}
						} else {
							// Read only module.
						}
					}
				}

				$unit->details->$field = ( $format ? ( $responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">' ) . ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) . '%</span>' : ( $grade > 0 ? round( ( $grade / $assessable_answers ), 0 ) : 0 ) );
			} else {
				$student = new Student( $student_id ); // @check
				if ( $student->is_unit_visited( $unit_id, $student_id ) ) {
					$grade = 100;
					$unit->details->$field = ( $format ? '<span class="grade-active">' . $grade . '%</span>' : $grade );
				} else {
					$grade = 0;
					$unit->details->$field = ( $format ? '<span class="grade-inactive">' . $grade . '%</span>' : $grade );
				}
			}
		}

		if ( 'student_unit_modules_graded' == $field ) {
			$grade = 0;
			$front_save_count = 0;
			$responses = 0;
			$graded = 0;

			$modules = Unit_Module::get_modules( $unit_id );

			foreach ( $modules as $mod ) {
				$class_name = $mod->module_type;

				if ( class_exists( $class_name ) ) {

					if ( constant( $class_name . '::FRONT_SAVE' ) ) {
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

			$unit->details->$field = $graded;
		}

		if ( isset( $unit->details->$field ) ) {
			return $unit->details->$field;
		}
	}

	// Alias
	public static function course_unit_submenu( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
		), $atts, 'course_unit_archive_submenu' ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) { return ''; }

		return do_shortcode( '[course_unit_archive_submenu course_id="' . $course_id . '"]' );
	}

	public static function course_unit_archive_submenu( $atts ) {
		extract( shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
			),
			$atts,
			'course_unit_archive_submenu'
		) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) { return ''; }

		$subpage = CoursePress_Helper_Utility::the_course_subpage();
		$course_status = get_post_status( $course_id );
		$course_base_url = CoursePress_Data_Course::get_course_url( $course_id );

		$content = '
		<div class="submenu-main-container">
			<ul id="submenu-main" class="submenu nav-submenu">
				<li class="submenu-item submenu-units ' . ( 'units' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'unit/' ) ) . '" class="course-units-link">' . esc_html__( 'Units', 'cp' ) . '</a></li>
		';

		$student_id = is_user_logged_in() ? get_current_user_id() : false;
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = CoursePress_Data_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		if ( $enrolled || $is_instructor ) {
			$content .= '
				<li class="submenu-item submenu-notifications ' . ( 'notifications' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'notification' ) ) . '">' . esc_html__( 'Notifications', 'cp' ) . '</a></li>
			';
		}

		$pages = CoursePress_Data_Course::allow_pages( $course_id );

		if ( $pages['course_discussion'] && ( $enrolled || $is_instructor ) ) {
			$content .= '<li class="submenu-item submenu-discussions ' . ( 'discussions' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'discussion' ) ) . '">' . esc_html__( 'Discussions', 'cp' ) . '</a></li>';
		}

		if ( $pages['workbook'] && $enrolled ) {
			$content .= '<li class="submenu-item submenu-workbook ' . ( 'workbook' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'workbook' ) ) . '">' . esc_html__( 'Workbook', 'cp' ) . '</a></li>';
		}

		$content .= '<li class="submenu-item submenu-info"><a href="' . esc_url_raw( $course_base_url ) . '">' . esc_html__( 'Course Details', 'cp' ) . '</a></li>';

		$show_link = false;

		if ( CP_IS_PREMIUM ) {
			// CERTIFICATE CLASS.
			$show_link = CoursePress_Data_Certificate::is_enabled() && CoursePress_Data_Student::is_enrolled_in_course( $student_id, $course_id );
		}

		if ( is_user_logged_in() && $show_link ) {
			// COMPLETION LOGIC.
			if ( CoursePress_Data_Student::is_course_complete( get_current_user_id(), $course_id ) ) {
				$certificate = CoursePress_Data_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', 'cp' ) );

				$content .= '<li class="submenu-item submenu-certificate ' . ( 'certificate' == $subpage ? 'submenu-active' : '') . '">' . $certificate . '</li>';
			}
		}

		$content .= '
			</ul>
		</div>
		';

		return $content;
	}

	public static function module_status( $atts ) {
		ob_start();
		extract( shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
				'unit_id' => CoursePress_Helper_Utility::the_post( true ),
				'previous_unit' => false,
				'message' => __( '%d of %d required elements completed.', 'cp' ),
				'format' => 'true',
			),
			$atts,
			'module_status'
		) );

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
				$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'cp' );
				$content .= CoursePress_Helper_UI::get_message_required_modules( $first_line );
			} elseif ( $unit_status['completion_required']['enabled'] && ! $unit_status['completion_required']['result'] ) {
				$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'cp' );
				$content .= CoursePress_Helper_UI::get_message_required_modules( $first_line );
			}
			if ( ! empty( $unit_status['date_restriction'] ) && ! $unit_status['date_restriction']['result'] ) {
				//$unit_availability = get_post_meta( $unit_id, 'unit_availability', true );
				$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id );

				if ( ! empty( $unit_availability_date ) ) {
					$available_on = date_i18n( get_option( 'date_format' ), CoursePress_Data_Course::strtotime( $unit_availability_date ) );
					$content .= esc_html__( 'This unit will be available on ', 'cp' ) . ' ' . $available_on;
				}

				/*
				if ( 'on_date' == $unit_availability ) {
					$date = get_post_meta( $unit_id, 'unit_date_availability', true );
					$content .= esc_html__( 'Available on ', 'cp' ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( current_time( $date ) ) );
				} elseif ( 'after_delay' == $unit_availability ) {
					$student_id = get_current_user_id();

					if ( $student_id > 0 ) {
						$now = CoursePress_Data_Course::time_now(); //strtotime( 'now', current_time( 'timestamp' ) );
						$delay_days = get_post_meta( $unit_id, 'unit_delay_days', true );
						$date_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );

						if ( (int) $delay_days > 0 ) {
							$date_enrolled = //strtotime( $date_enrolled, current_time( 'timestamp' ) );
							$delay_date = $date_enrolled + ( (int) $delay_days * 86400 );
							$since_published = $delay_date;

							$available = $since_published <= 0;

							if ( ! $available ) {
								/**
								 * Include the time_format to avoid confusion where the unit's availability
								 * is the current date.
								 *
								$content .= sprintf( esc_html__( 'Available on %s @ %s', 'cp' ),
									date_i18n( get_option( 'date_format' ), $delay_date ),
									date_i18n( get_option( 'time_format' ), $delay_date )
								);
							}
						}
					}
				}
				*/
			}
		}

		$content .= '</span>';

		return $content;
	}

	public static function unit_discussion( $atts ) {
		global $wp;

		if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
			$unit_id = CoursePress_Data_Unit::by_name( $wp->query_vars['unitname'] );
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

	/**
	 * Shows the course title.
	 *
	 * @since 1.0.0
	 */
	public static function course_unit_title( $atts ) {
		extract( shortcode_atts( array(
			'unit_id'   => in_the_loop() ? get_the_ID() : '',
			'title_tag' => '',
			'link'      => 'no',
			'class'     => '',
			'last_page' => 'no',
		), $atts, 'course_unit_title' ) );

		$unit_id   = (int) $unit_id;
		$course_id = (int) get_post_field( 'post_parent', $unit_id );
		$title_tag = sanitize_html_class( $title_tag );
		$link      = sanitize_html_class( $link );
		$last_page = sanitize_html_class( $last_page );
		$class     = sanitize_html_class( $class );

		$title = get_the_title( $unit_id );

		$draft      = get_post_status( $unit_id ) !== 'publish';
		$show_draft = $draft && cp_can_see_unit_draft();

		$the_permalink = CoursePress_Data_Unit::get_url( $unit_id );

		$content = '';
		if ( ! $draft || ( $draft && $show_draft ) ) {
			$content = ! empty( $title_tag ) ? '<' . $title_tag . ' class="course-unit-title course-unit-title-' . $unit_id . ' ' . $class . '">' : '';
			$content .= 'yes' == $link ? '<a href="' . esc_url( $the_permalink ) . '" title="' . esc_attr( $title ) . '" class="unit-archive-single-title">' : '';
			$content .= $title;
			$content .= 'yes' == $link ? '</a>' : '';
			$content .= ! empty( $title_tag ) ? '</' . $title_tag . '>' : '';
		}

		// Return the html in the buffer.
		return $content;
	}
}
