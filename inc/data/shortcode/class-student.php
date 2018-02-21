<?php
/**
 * Shortcode handlers.
 *
 * @package  CoursePress
 */

/**
 * Student-related shortcodes.
 */
class CoursePress_Data_Shortcode_Student extends CoursePress_Utility {

	private static $templates_was_already_loaded = false;

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public function init() {

		$shortcodes = array(
			'courses_student_dashboard',
			'courses_student_settings',
			'student_registration_form',
			'student_workbook_table',
			'coursepress_enrollment_templates',
			'course_progress',
			'course_unit_progress',
			'student_grades_table',
			'course_mandatory_message',
			'course_unit_percent',
		);

		foreach ( $shortcodes as $shortcode ) {
			$method = 'get_' . $shortcode;

			if ( method_exists( $this, $method ) ) {
				add_shortcode( $shortcode, array( $this, $method ) );
			}
		}

		// Log student visit to the course.
		add_action( 'coursepress_focus_item_preload', array( $this, 'log_student_visit' ) );
		add_action( 'coursepress_normal_items_loaded', array( $this, 'log_normal_student_visit' ), 10, 3 );
	}

	/**
	 * Display student dashboard.
	 *
	 * @since  1.0.0
	 *
	 * @return string Shortcode output.
	 */
	public function get_courses_student_dashboard() {

		ob_start();
		// My courses template
		coursepress_get_template( 'content', 'my-courses' );
		// Instructed courses
		coursepress_get_template( 'content', 'instructed-courses' );
		// Facilitated courses
		coursepress_get_template( 'content', 'facilitated-courses' );

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Display student settings.
	 *
	 * @since  1.0.0
	 *
	 * @return string Shortcode output.
	 */
	public function get_courses_student_settings() {

		ob_start();

		coursepress_get_template( 'registration', 'form' );

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Display student registration form.
	 *
	 * @since  1.0.0
	 *
	 * @return string Shortcode output.
	 */
	public function get_student_registration_form() {

		ob_start();

		coursepress_get_template( 'registration', 'form' );

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Display student workbook table.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_student_workbook_table( $args ) {

		$course_id = coursepress_get_course_id();

		$workbook_is_active = coursepress_is_true( coursepress_course_get_setting( $course_id, 'allow_workbook', false ) );

		if ( false == $workbook_is_active ) {
			$content = sprintf( '<p class="message">%s</p>', __( 'Workbook is not available for this course.', 'cp' ) );
			return $content;
		}

		$args = shortcode_atts( array(
			'course_id' => $course_id,
			'unit_id' => false,
			'user_id' => coursepress_get_user_id(),
			'comment_column_title' => __( 'Feedback', 'cp' ),
			'pending_grade_label' => __( 'Pending', 'cp' ),
			'table_class' => 'widefat shadow-table assessment-archive-table workbook-table',
			'show_page' => false,
			'show_course_progress' => true,
		), $args, 'student_workbook_table' );

		$content = coursepress_render( 'templates/content-workbook', $args, false );

		$course_id = (int) $args['course_id'];
		$unit_id = (int) $args['unit_id'];
		$student_id = get_current_user_id();
		$student = coursepress_get_user();

		if ( empty( $course_id ) || empty( $student_id ) ) {
			return '';
		}

		$pending_grade_label = sanitize_text_field( $args['pending_grade_label'] );
		$table_class = sanitize_text_field( $args['table_class'] );
		$show_page = coursepress_is_true( $args['show_page'] );
		$show_course_progress = coursepress_is_true( $args['show_course_progress'] );
		$comment_column_title = sanitize_text_field( $args['comment_column_title'] );

		$student_progress = $student->get_completion_data( $course_id );

		$unit_list = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$unit_list = $this->sort_on_key( $unit_list, 'order' );

		if ( ! empty( $unit_id ) && array_key_exists( $unit_id, $unit_list ) ) {
			$unit_list = array( $unit_list[ $unit_id ] );
		}

		if ( $show_course_progress && empty( $unit_id ) ) {
			$content .= '<h3 class="course-completion-progress">' . esc_html__( 'Course completion: ', 'cp' ) . '<small>' . $student->get_course_progress( $course_id ) . '%</small>' . '</h3>';
		}

		$content .= '<div class="workbook">';
		$content .= '<table class="workbook-table ' . $table_class . '">';

		$student_progress_units = ( ! empty( $student_progress['units'] ) ) ? $student_progress['units'] : array();
		foreach ( $unit_list as $unit_id => $unit ) {
			if ( ! array_key_exists( $unit_id, $student_progress_units ) ) {
				continue;
			}
			$progress = $student->get_unit_progress( $course_id, $unit_id );
			$format = '<tr class="row-unit"><th colspan="2" class="workbook-unit unit-%s">%s</th><th class="td-right">%s: %s</th></tr>';
			$content .= sprintf( $format, $unit_id, $unit['unit']->post_title, __( 'Progress', 'cp' ), $progress . '%' );

			$module_count = 0;
			if ( isset( $unit['pages'] ) ) {
				foreach ( $unit['pages'] as $page ) {
					if ( $show_page ) {
						$content .= '<tr class="page page-separator"><td colspan="5">' . $page['title'] . '</td></tr>';
					}

					foreach ( $page['modules'] as $module_id => $module ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );
						$is_assessable = ! empty( $attributes['assessable'] );
						$require_instructor_assessment = ! empty( $attributes['instructor_assessable'] );

						if ( 'output' === $attributes['mode'] ) {
							continue;
						}

						$module_count += 1;

						$title = empty( $module->post_title ) ? $module->post_content : $module->post_title;
						$response = $student->get_response( $course_id, $unit_id, $module_id, $student_progress );

						$feedback = $student->get_instructor_feedback( $course_id, $unit_id, $module_id, $student_progress );

						// Check if the grade came from an instructor
						$grade = $student->get_step_grade( $course_id, $unit_id, $module_id );

						$grade = empty( $grade ) ? 0 : (int) $grade;

						$response_display = $response['response'];

						switch ( $attributes['module_type'] ) {

							case 'input-checkbox': case 'input-radio': case 'input-select':
										$answers = $attributes['answers'];
										$selected = (array) $attributes['answers_selected'];
										$display = '';
										if ( empty( $response ) ) {
											$response_display = '&ndash;';
										} else {
											foreach ( $answers as $key => $answer ) {
												$the_answer = in_array( $key, $selected );
												$student_answer = is_array( $response_display ) ? in_array( $key, $response_display ) : $response_display == $key;
												if ( 'input-radio' === $attributes['module_type'] ) {
													$student_answer = $response_display == $key;
												}
												if ( $student_answer ) {
													$class = $the_answer ? 'chosen-correct' : 'chosen-incorrect';
													$display .= sprintf( '<p class="answer %s">%s</p>', $class, $answer );
												}
											}
											$response_display = $display;
										}
								break;

							case 'input-upload':
								if ( $response ) {
									$url = $response['response']['url'];

									$file_size = isset( $response['response']['size'] ) ? $response['response']['size'] : false;
									$file_size = $file_size ? $this->format_file_size( $file_size ) : '';
									$file_size = ! empty( $file_size ) ? '<small>(' . esc_html( $file_size ) . ')</small>' : '';

									$file_name = explode( '/', $url );
									$file_name = array_pop( $file_name );

									$url = $this->encode( $url );
									$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;

									$response_display = '<a href="' . esc_url( $url ) . '" class="button button-download">' . esc_html( $file_name ) . ' ' . $this->filter_content( $file_size ) . '</a>';
								} else {
									$response_display = '';
								}
								break;
							case 'input-quiz':
								$display = '';
								$questions = $attributes['questions'];

								foreach ( $questions as $q_index => $question ) {
									$options = (array) $question['options'];
									$checked = (array) $options['checked'];
									$checked = array_filter( $checked );

									$display .= sprintf( '<p class="question">%s</p>', $question['question'] );

									if ( ! empty( $response_display[ $q_index ] ) ) {

										$answers = $response_display[ $q_index ];

										foreach ( $answers as $a_index => $answer ) {
											if ( ! empty( $answer ) ) {
												$the_answer = coursepress_get_array_val( $attributes, 'questions/' . $q_index . '/options/answers/' . $a_index );
												$correct = ! empty( $checked[ $a_index ] );
												$class = $correct ? 'chosen-correct' : 'chosen-incorrect';

												$display .= sprintf( '<p class="answer %s">%s</p>', $class, $the_answer );
											}
										}
									}
								}

								$response_display = $display;
								break;
							case 'input-form':
								$display = '';
								$questions = $attributes['questions'];
								if ( $response_display ) {
									foreach ( $questions as $q_index => $question ) {
										$answer = $response_display[ $q_index ];
										if ( $question['type'] == 'selectable' ) {
											$selected = ( isset( $question['options'] ) && isset( $question['options']['answers'] ) && isset( $question['options']['answers'][ $answer ] ) )
												? $question['options']['answers'][ $answer ]
												: '';
											$display .= sprintf( '<p class="answerd">%s</p>', $selected );
										} else {
											$display .= sprintf( '<p class="answerd">%s</p>', $answer );
										}
									}
								}
								$response_display = $display;
								break;
							case 'input-text': case 'input-textarea':
									$response_display = empty( $response_display ) ? __( 'No answer!', 'cp' ) : $response_display;
								break;

							case 'input-form':
								$response = $response_display;
								$response_display = '';

								if ( ! empty( $attributes['questions'] ) ) {
									$questions = $attributes['questions'];

									foreach ( $questions as $q_index => $question ) {
										$student_response = ! empty( $response[ $q_index ] ) ? $response[ $q_index ] : '';
										$format = '<p class="question">%s</p>';
										$response_display .= sprintf( $format, esc_html( $question['question'] ) );

										if ( 'selectable' == $question['type'] ) {
											$options = $question['options']['answers'];
											$checked = $question['options']['checked'];

											foreach ( $options as $ai => $answer ) {
												if ( $student_response == $ai ) {
													$the_answer = ! empty( $checked[ $ai ] );

													if ( $the_answer === $student_response ) {
														$class = 'chosen-correct';
													} else {
														$class = 'chosen-incorrect';
													}
													$response_display .= sprintf( '<p class="answer %s">%s</p>', $class, $answer );
												}
											}
										} else {
											$response_display .= sprintf( '<p>%s</p>', esc_html( $student_response ) );
										}
									}
								}
								break;
						}

						$feedback_by = ( ! is_null( $feedback ) && isset( $feedback['feedback_by'] ) ) ? (int) $feedback['feedback_by'] : 0;
						$first_last = $this->get_user_name( $feedback_by );

						$feedback_display = ! empty( $feedback['feedback'] ) ? '<div class="feedback"><div class="comment">' . $feedback['feedback'] . '</div><div class="instructor"> – <em>' . esc_html( $first_last ) . '</em></div></div>' : '';

						$grade_display = 0 === $grade || 0 < (int) $grade ? $grade . '%' : $grade;

						if ( $require_instructor_assessment ) {
							$is_assessable = true;
						}

						if ( 'Pending' === $grade_display && false === $is_assessable ) {
							$grade_display = $pending_grade_label;
						}

						$content .= '<tr class="row-module">';
						$content .= sprintf( '<td class="column-title">%s</td>', $title );
						$content .= sprintf( '<td class="column-answer">%s</td>', $response_display );
						$content .= sprintf( '<td class="td-right">%s</td>', $grade_display );
						$content .= '</tr>';

						if ( '' !== $feedback_display ) {
							$content .= '<tr>';
							$content .= '<td class="column-title">' . $comment_column_title . ':</td>';
							$content .= sprintf( '<td colspan="2">%s</td>', $feedback_display );
							$content .= '</tr>';
						}
					}
				}
			}
			if ( 0 == $module_count ) {
				$content .= sprintf( '<tr><td colspan="3" class="non-gradable">%s</td></tr>', __( 'No gradable modules under this unit.', 'cp' ) );
			}
		}

		$content .= '</table></div>';

		return $content;
	}

	/**
	 * Display coursepress enrollment templates.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_coursepress_enrollment_templates( $atts ) {

		// Avoid to load templates twice...
		global $post;

		$course_id = 0;

		if ( ! is_object( $post ) ) {
			$course_id = coursepress_get_course_id();
			$post = get_post( $course_id );
			if ( ! is_a( $post, 'WP_Post' ) ) {
				return;
			}
		}

		if ( isset( $post->coursepress_enrollment_templates_was_already_loaded )
			&& $post->coursepress_enrollment_templates_was_already_loaded
		) {
			return;
		}
		$post->coursepress_enrollment_templates_was_already_loaded = true;

		self::$templates_was_already_loaded = true;

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id( $course_id ),
		), $atts, 'coursepress_enrollment_templates' );

		$course_id = (int) $atts['course_id'];

		if ( empty( $course_id ) ) {
			return '';
		}

		$data['course_id'] = $course_id;
		$data['course'] = coursepress_get_course( $course_id );
		$data['nonce'] = wp_create_nonce( 'coursepress_enrollment_action' );

		$data['scode_1'] = '[course_signup_form login_link_id="step2" show_submit="no"]';
		$data['scode_2'] = '[course_signup_form signup_link_id="step1" show_submit="no" page="login"]';

		// The filters below can be used to customize the output of the registration process.
		$content = coursepress_render( 'views/front/course-enrollment', $data, false );

		return $content;
	}

	/**
	 * Course Progress.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_progress( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'decimal_places' => '0',
		), $atts, 'course_progress' );

		if ( ! empty( $atts['course_id'] ) ) {
			$course_id = (int) $atts['course_id'];
		}

		$user = coursepress_get_user();

		$decimal_places = (int) $atts['decimal_places'];

		$progress = $user->get_course_progress( $course_id );

		return number_format_i18n( $progress, $decimal_places );
	}

	/**
	 * Course Unit Progress
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public static function get_course_unit_progress( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'unit_id' => false,
			'decimal_places' => '0',
		), $atts, 'course_unit_progress' );

		if ( ! empty( $atts['course_id'] ) ) {
			$course_id = (int) $atts['course_id'];
		}

		$user = coursepress_get_user();

		$unit_id = (int) $atts['unit_id'];

		$decimal_places = sanitize_text_field( $atts['decimal_places'] );

		$progress = $user->get_unit_progress( $course_id, $unit_id );

		return number_format_i18n( $progress, $decimal_places );
	}

	/**
	 * Course Grades table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_student_grades_table( $args ) {

		$course_id = coursepress_get_course_id();

		$workbook_is_active = coursepress_is_true( coursepress_course_get_setting( $course_id, 'allow_workbook', false ) );

		if ( false == $workbook_is_active ) {
			$content = sprintf( '<p class="message">%s</p>', __( 'Workbook is not available for this course.', 'cp' ) );
			return $content;
		}

		$args = shortcode_atts( array(
			'course_id' => $course_id,
		), $args, 'student_workbook_table' );

		$course_id = (int) $args['course_id'];
		$student_id = get_current_user_id();
		$user = coursepress_get_user();

		if ( empty( $course_id ) || empty( $student_id ) ) {
			return '';
		}

		$student_progress = $user->get_completion_data( $course_id );

		$content = '';
		$unit_list = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$unit_list = $this->sort_on_key( $unit_list, 'order' );

		$content .= '<div class="grades">';
		$content .= '<table class="grades-table">';

		foreach ( $unit_list as $unit_id => $unit ) {
			$progress = $user->get_unit_progress( $course_id, $unit_id, $student_progress );
			$content .= '<tbody>';
			$content .= '<tr class="row-unit">';
			$content .= sprintf( '<td class="workbook-unit unit-%s">%s</td>', esc_attr( $unit_id ), $unit['unit']->post_title );
			$content .= sprintf( '<td class="td-right">%s: %d%%</td>', __( 'Progress', 'cp' ), $progress );
			$content .= '</tr>';

			$module_count = 0;
			$module_done = 0;
			if ( isset( $unit['pages'] ) ) {
				foreach ( $unit['pages'] as $page ) {
					foreach ( $page['modules'] as $module_id => $module ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );
						$require_instructor_assessment = ! empty( $attributes['instructor_assessable'] );
						if ( 'output' === $attributes['mode'] ) {
							continue;
						}
						$module_count += 1;
						$module_type = $attributes['module_type'];

						$response = $user->get_response( $student_id, $course_id, $unit_id, $module_id, $student_progress );

						$excluded_modules = array( 'input-textarea', 'input-text', 'input-upload', 'input-form' );

						$auto_grade = true;
						if ( in_array( $module_type, $excluded_modules ) ) {
							$graded_by = coursepress_get_array_val( $response, 'graded_by' );
							if ( ( 'auto' == $graded_by || empty( $graded_by ) ) && ! empty( $response ) ) {
								$auto_grade = false;
							}
						}

						$response_display = $response['response'];

						switch ( $attributes['module_type'] ) {

							case 'input-checkbox': case 'input-radio': case 'input-select':
							$answers = $attributes['answers'];
							$selected = (array) $attributes['answers_selected'];
							if ( empty( $response ) ) {
								$response_display = '&ndash;';
							} else {
								$add = false;
								foreach ( $answers as $key => $answer ) {
									$the_answer = in_array( $key, $selected );
									$student_answer = is_array( $response_display ) ? in_array( $key, $response_display ) : $response_display == $key;
									if ( 'input-radio' === $attributes['module_type'] ) {
										$student_answer = $response_display == $key;
									}
									if ( $student_answer && $the_answer ) {
										$add = true;
									}
								}
								if ( $add && $auto_grade ) {
									$module_done++;
								}
							}
							break;

							case 'input-upload':
								if ( $response && $auto_grade ) {
									$module_done++;
								}
								break;
							case 'input-quiz':
								$questions = $attributes['questions'];
								$add = false;
								foreach ( $questions as $q_index => $question ) {
									$options = (array) $question['options'];
									if ( ! empty( $response_display[ $q_index ] ) ) {
										$answers = $response_display[ $q_index ];
										foreach ( $answers as $a_index => $answer ) {
											if ( ! empty( $answer ) ) {
												if ( $correct ) {
													$add = true;
												}
											}
										}
									}
								}
								if ( $add && $auto_grade ) {
									$module_done++;
								}
								break;

							case 'input-form':
								$questions = $attributes['questions'];
								if ( $response_display ) {
									$add = false;
									foreach ( $questions as $q_index => $question ) {
										$answer = $response_display[ $q_index ];
										if ( $answer ) {
											$add = true;
										}
									}
								}
								if ( $add && $auto_grade ) {
									$module_done++;
								}
								break;

							case 'input-text': case 'input-textarea':
							if ( ! empty( $response_display ) && $auto_grade ) {
								$module_done++;
							}
							break;

							case 'input-form':
								$response = $response_display;
								$response_display = '';

								if ( ! empty( $attributes['questions'] ) ) {
									$questions = $attributes['questions'];
									$add = false;
									foreach ( $questions as $q_index => $question ) {
										$student_response = ! empty( $response[ $q_index ] ) ? $response[ $q_index ] : '';
										if ( $student_response  ) {
											if ( 'selectable' == $question['type'] ) {
												$options = $question['options']['answers'];
												$checked = $question['options']['checked'];
												foreach ( $options as $ai => $answer ) {
													if ( $student_response == $ai ) {
														$the_answer = ! empty( $checked[ $ai ] );

														if ( $the_answer === $student_response ) {
															$add = true;
														}
													}
												}
											} else {
												$add = true;
											}
										}
									}
									if ( $add && $auto_grade ) {
										$module_done++;
									}
								}
								break;
						}
					}
				}
			}
			$grade_display = __( 'No gradable modules under this unit.', 'cp' );
			if ( 0 != $module_count ) {
				$grade_display = __( '%d of %d elements completed.', 'cp' );
				$grade_display = sprintf( $grade_display, $module_done, $module_count );
			}
			$content .= '<tr class="row-elements">';
			$content .= sprintf( '<td colspan="2" >%s</td>', $grade_display );
			$content .= '</tr>';
			$content .= '</tbody>';
		}

		$content .= '</table></div>';

		return $content;
	}

	/**
	 * Course Required Message.
	 *
	 * Text: "x of y required elements completed".
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_mandatory_message( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'unit_id' => coursepress_get_unit_id(),
			'message' => __( '%d of %d required elements completed.', 'cp' ),
		), $atts, 'course_mandatory_message' );

		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}

		$unit_id = (int) $atts['unit_id'];
		if ( empty( $unit_id ) ) {
			return '';
		}

		$student_id = get_current_user_id();
		if ( empty( $student_id ) ) {
			return '';
		}

		$mandatory = CoursePress_Data_Student::get_mandatory_completion( $student_id, $course_id, $unit_id );
		if ( empty( $mandatory['required'] ) ) {
			return '';
		}

		$message = sanitize_text_field( $atts['message'] );
		$mandatory_required = (int) $mandatory['required'];
		if ( empty( $mandatory_required ) ) {
			return '';
		}

		return sprintf( $message, (int) $mandatory['completed'], $mandatory_required );
	}

	/**
	 * Display percentage of unit completion.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_unit_percent( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'unit_id' => coursepress_get_unit_id(),
			'format' => false,
			'style' => 'flat',
			'tooltip_alt' => __( 'Percent of the unit completion', 'cp' ),
			'knob_animation' => '{ "duration": 1200 }',
			'knob_bg_color' => '#e0e6eb',
			'knob_data_height' => '60',
			'knob_data_thickness' => '0.18',
			'knob_data_width' => '60',
			'knob_empty_color' => 'rgba( 20, 20, 20, .1 )',
			'knob_fg_color' => '#24bde6',
			'knob_text_align' => 'center',
			'knob_text_color' => '#222',
			'knob_text_denominator' => 4.5,
			'knob_text_show' => true,
			'knob_text_prepend' => false,
		), $atts, 'course_unit_percent' );

		// Filter progress_wheel attributes.
		$atts = apply_filters( 'coursepress_unit_progress_wheel_atts', $atts );

		$course_id = (int) $atts['course_id'];
		$unit_id = (int) $atts['unit_id'];

		if ( empty( $course_id ) || empty( $unit_id ) ) {
			return 0;
		}

		$format = coursepress_is_true( $atts['format'] );
		$style = sanitize_text_field( $atts['style'] );
		$tooltip_alt = sanitize_text_field( $atts['tooltip_alt'] );
		$knob_fg_color = sanitize_text_field( $atts['knob_fg_color'] );
		$knob_bg_color = sanitize_text_field( $atts['knob_bg_color'] );
		$knob_text_color = sanitize_text_field( $atts['knob_text_color'] );
		$knob_data_thickness = sanitize_text_field( $atts['knob_data_thickness'] );
		$knob_data_width = (int) $atts['knob_data_width'];
		$knob_data_height = (int) $atts['knob_data_height'];

		if ( empty( $knob_data_width ) && ! empty( $knob_data_height ) ) {
			$knob_data_width = $knob_data_height;
		}

		$knob_data_thickness = $knob_data_width * $knob_data_thickness;

		$user = coursepress_get_user();
		$unit = coursepress_get_unit( $unit_id );

		$percent_value = $user->get_unit_progress( $course_id, $unit_id );

		$content = '';

		// Check is unit available?
		$is_unit_available = $unit->is_available();

		if ( $is_unit_available ) {
			if ( 'flat' == $style ) {
				$content = '<span class="percentage">' . ( $format ? $percent_value . '%' : $percent_value ) . '</span>';
			} elseif ( 'none' == $style ) {
				$content = $percent_value;
			} else {
				$data_value = $percent_value / 100;
				$content = '<div class="course-progress-disc-container"><a class="tooltip" alt="' . $tooltip_alt . '">';
				// Knob settings as date fields.
				$knob_settings = array();
				foreach ( $atts as $key => $value ) {
					if ( ! preg_match( '/^knob_/', $key ) ) {
						continue;
					}
					$knob_settings[] = sprintf(
						'data-%s="%s"',
						sanitize_text_field( preg_replace( '/_/', '-', $key ) ),
						esc_attr( $value )
					);
				}
				$content .= sprintf(
					'<div class="course-progress-disc" data-value="%s" data-start-angle="4.7" data-size="%s" data-thickness="%s" data-animation-start-value="1.0" data-fill="{ &quot;color&quot;: &quot;%s&quot; }" %s></div>',
					esc_attr( $data_value ),
					esc_attr( $knob_data_width ),
					esc_attr( $knob_data_thickness ),
					esc_attr( $knob_fg_color ),
					implode( ' ', $knob_settings )
				);
				$content .= '</a></div>';
			}
		} else {
			$content .= '<i class="fa fa-lock" aria-hidden="true"></i>';
		}

		/**
		 * Filter the progress wheel markup.
		 *
		 * @since 2.0
		 *
		 * @param (int) $markup
		 * @param (int) $course_id
		 * @param (int) $unit_id
		 * @param (int) $percent_value		The total percent value.
		 **/
		$content = apply_filters( 'coursepress_unit_progress_wheel', $content, $course_id, $unit_id, $percent_value );

		return $content;
	}

	/**
	 * Helper function to log student visit to the course in focus view mode.
	 *
	 * @param array $array
	 */
	public function log_student_visit( array $array ) {

		$course_id = $array['course'];
		$unit_id = $array['unit'];
		$page_number = 1;
		$module_id = 0;
		$type = $array['type'];

		if ( 'section' == $type ) {
			$page_number = $array['item_id'];
		} elseif ( 'module' == $type ) {
			$module_id = $array['item_id'];
			$page_number = ( new CoursePress_Data_Shortcode_Template() )->get_module_page( $course_id, $unit_id, $module_id );
		}

		CoursePress_Data_Student::log_visited_course( $course_id, $unit_id, $page_number, $module_id );
	}

	/**
	 * Helper function to log student visit to a course in normal mode.
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $page_number
	 */
	public function log_normal_student_visit( $course_id, $unit_id, $page_number ) {

		CoursePress_Data_Student::log_visited_course( $course_id, $unit_id, $page_number );
	}
}
