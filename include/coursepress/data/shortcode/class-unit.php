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
			$content = get_permalink( $course_id ) . trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . $unit->post_name;
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
			// In case that student gave answers on all mandatory plus optional questions.
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
				//so we won't have 7 of 6 mandatory answered but mandatory number as a max number
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

		$content = '
		<div class="submenu-main-container">
			<ul id="submenu-main" class="submenu nav-submenu">
				<li class="submenu-item submenu-units ' . ( 'units' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'unit' ) ) . '">' . esc_html__( 'Units', 'CP_TD' ) . '</a></li>
		';

		$student_id = is_user_logged_in() ? get_current_user_id() : false;
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = CoursePress_Data_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		if ( $enrolled || $is_instructor ) {
			$content .= '
				<li class="submenu-item submenu-notifications ' . ( 'notifications' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'notification' ) ) . '">' . esc_html__( 'Notifications', 'CP_TD' ) . '</a></li>
			';
		}

		$pages = CoursePress_Data_Course::allow_pages( $course_id );

		if ( $pages['course_discussion'] && ( $enrolled || $is_instructor ) ) {
			$content .= '<li class="submenu-item submenu-discussions ' . ( 'discussions' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'discussion' ) ) . '">' . esc_html__( 'Discussions', 'CP_TD' ) . '</a></li>';
		}

		if ( $pages['workbook'] && $enrolled ) {
			$content .= '<li class="submenu-item submenu-workbook ' . ( 'workbook' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( get_permalink( $course_id ) . CoursePress_Core::get_slug( 'workbook' ) ) . '">' . esc_html__( 'Workbook', 'CP_TD' ) . '</a></li>';
		}

		$content .= '<li class="submenu-item submenu-info"><a href="' . esc_url_raw( get_permalink( $course_id ) ) . '">' . esc_html__( 'Course Details', 'CP_TD' ) . '</a></li>';

		$show_link = false;

		if ( CP_IS_PREMIUM ) {
			// CERTIFICATE CLASS.
			// $show_link = CP_Basic_Certificate::option( 'basic_certificate_enabled' );
			// $show_link = ! empty( $show_link ) ? true : false;

			// Debug code. Remove it!
			$show_link = false;
		}

		if ( is_user_logged_in() && $show_link ) {
			// COMPLETION LOGIC.
			if ( CoursePress_Data_Student::is_course_complete( get_current_user_id(), $course_id ) ) {
				// $certificate = CP_Basic_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', 'CP_TD' ) );

				// $content .= '<li class="submenu-item submenu-certificate ' . ( $subpage == 'certificate' ? 'submenu-active' : '') . '">' . $certificate . '</li>';
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
				'message' => __( '%d of %d mandatory elements completed.', 'CP_TD' ),
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
}
