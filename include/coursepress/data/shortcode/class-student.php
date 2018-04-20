<?php
/**
 * Shortcode handlers.
 *
 * @package  CoursePress
 */

/**
 * Student-related shortcodes.
 */
class CoursePress_Data_Shortcode_Student {
	private static $templates_was_already_loaded = false;

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_shortcode(
			'courses_student_dashboard',
			array( __CLASS__, 'courses_student_dashboard' )
		);

		add_shortcode(
			'courses_student_settings',
			array( __CLASS__, 'courses_student_settings' )
		);

		add_shortcode(
			'coursepress_enrollment_templates',
			array( __CLASS__, 'coursepress_enrollment_templates' )
		);

		if ( ! CP_IS_CAMPUS ) {
			add_shortcode(
				'student_registration_form',
				array( __CLASS__, 'student_registration_form' )
			);
		}

		add_shortcode(
			'student_workbook_table',
			array( __CLASS__, 'student_workbook_table' )
		);

		add_shortcode(
			'student_grades_table',
			array( __CLASS__, 'student_grades_table' )
		);

		// -- Course-progress.
		add_shortcode(
			'course_progress',
			array( __CLASS__, 'course_progress' )
		);
		add_shortcode(
			'course_unit_progress',
			array( __CLASS__, 'course_unit_progress' )
		);
		add_shortcode(
			'course_mandatory_message',
			array( __CLASS__, 'course_mandatory_message' )
		);
		add_shortcode(
			'course_unit_percent',
			array( __CLASS__, 'course_unit_percent' )
		);

		/**
		 * Log student visit to the course **/
		add_action( 'coursepress_focus_item_preload', array( __CLASS__, 'log_student_visit' ) );
		add_action( 'coursepress_normal_items_loaded', array( __CLASS__, 'log_normal_student_visit' ), 10, 3 );
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

	public static function student_workbook_table( $args ) {

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$workbook_is_active = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'allow_workbook', false ) );

		if ( false == $workbook_is_active ) {
			$content = sprintf( '<p class="message">%s</p>', __( 'Workbook is not available for this course.', 'coursepress' ) );
			return $content;
		}

		$args = shortcode_atts(
			array(
				'course_id' => $course_id,
				'unit_id' => false,
				'module_column_title' => __( 'Element', 'coursepress' ),
				'title_column_title' => __( 'Module', 'coursepress' ),
				'submission_date_column_title' => __( 'Submitted', 'coursepress' ),
				'response_column_title' => __( 'Answer', 'coursepress' ),
				'grade_column_title' => __( 'Grade', 'coursepress' ),
				'comment_column_title' => __( 'Feedback', 'coursepress' ),
				'module_response_description_label' => __( 'Description', 'coursepress' ),
				'comment_label' => __( 'Comment', 'coursepress' ),
				'view_link_label' => __( 'View', 'coursepress' ),
				'view_link_class' => 'assessment-view-response-link button button-units',
				'comment_link_class' => 'assessment-view-response-link button button-units',
				'pending_grade_label' => __( 'Pending', 'coursepress' ),
				'unit_unread_label' => __( 'Unit Unread', 'coursepress' ),
				'unit_read_label' => __( 'Unit Read', 'coursepress' ),
				'single_correct_label' => __( 'Correct', 'coursepress' ),
				'single_incorrect_label' => __( 'Incorrect', 'coursepress' ),
				'no_content_label' => __( 'This unit has no activities.', 'coursepress' ),
				'non_assessable_label' => __( '**' ),
				'table_class' => 'widefat shadow-table assessment-archive-table workbook-table',
				'table_labels_th_class' => 'manage-column',
				'show_page' => false,
				'show_course_progress' => true,
			)
			,
			$args,
			'student_workbook_table'
		);

		$course_id = (int) $args['course_id'];
		$unit_id = (int) $args['unit_id'];
		$student_id = get_current_user_id();

		if ( empty( $course_id ) || empty( $student_id ) ) {
			return '';
		}

		$module_column_title = sanitize_text_field( $args['module_column_title'] );
		$title_column_title = sanitize_text_field( $args['title_column_title'] );
		$submission_date_column_title = sanitize_text_field( $args['submission_date_column_title'] );
		$response_column_title = sanitize_text_field( $args['response_column_title'] );
		$grade_column_title = sanitize_text_field( $args['grade_column_title'] );
		$comment_column_title = sanitize_text_field( $args['comment_column_title'] );
		$module_response_description_label = sanitize_text_field( $args['module_response_description_label'] );
		$comment_label = sanitize_text_field( $args['comment_label'] );
		$view_link_label = sanitize_text_field( $args['view_link_label'] );
		$view_link_class = sanitize_text_field( $args['view_link_class'] );
		$comment_link_class = sanitize_text_field( $args['comment_link_class'] );
		$pending_grade_label = sanitize_text_field( $args['pending_grade_label'] );
		$unit_unread_label = sanitize_text_field( $args['unit_unread_label'] );
		$unit_read_label = sanitize_text_field( $args['unit_read_label'] );
		$non_assessable_label = sanitize_text_field( $args['non_assessable_label'] );
		$no_content_label = sanitize_text_field( $args['no_content_label'] );
		$table_class = sanitize_text_field( $args['table_class'] );
		$table_labels_th_class = sanitize_text_field( $args['table_labels_th_class'] );
		$single_correct_label = sanitize_text_field( $args['single_correct_label'] );
		$single_incorrect_label = sanitize_text_field( $args['single_incorrect_label'] );
		$show_page = cp_is_true( $args['show_page'] );
		$show_course_progress = cp_is_true( $args['show_course_progress'] );

		$columns = array(
			'title' => $title_column_title,
			'submission_date' => $submission_date_column_title,
			'response' => $response_column_title,
			'grade' => $grade_column_title,
			'comment' => $comment_column_title,
		);

		$col_sizes = array(
			'45',
			'15',
			'12',
			'13',
			'15',
		);

		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

		$content = '';
		$unit_list = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$unit_list = CoursePress_Helper_Utility::sort_on_key( $unit_list, 'order' );

		if ( ! empty( $unit_id ) && array_key_exists( $unit_id, $unit_list ) ) {
			$unit_list = array( $unit_list[ $unit_id ] );
		}

		if ( $show_course_progress && empty( $unit_id ) ) {
			$content .= '<h3 class="course-completion-progress">' . esc_html__( 'Course completion: ', 'coursepress' ) . '<small>' . CoursePress_Data_Student::get_course_progress( $student_id, $course_id, $student_progress ) . '%</small>' . '</h3>';
		}

		$content .= '<div class="workbook">';
		$content .= '<table class="workbook-table">';

		$student_progress_units = ( ! empty( $student_progress['units'] ) ) ? $student_progress['units'] : array();
		foreach ( $unit_list as $unit_id => $unit ) {
			if ( ! array_key_exists( $unit_id, $student_progress_units ) ) {
				continue;
			}
			$progress = CoursePress_Data_Student::get_unit_progress( $student_id, $course_id, $unit_id, $student_progress );
			$format = '<tr class="row-unit"><th colspan="2" class="workbook-unit unit-%s">%s</th><th class="td-right">%s: %s</th></tr>';
			$content .= sprintf( $format, $unit_id, $unit['unit']->post_title, __( 'Progress', 'coursepress' ), $progress . '%' );

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
						$module_type = $attributes['module_type'];

						$title = empty( $module->post_title ) ? $module->post_content : $module->post_title;
						$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $student_progress );

						$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );

						// Check if the grade came from an instructor
						$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
						$graded_by = CoursePress_Helper_Utility::get_array_val(
							$grades,
							'graded_by'
						);
						$grade = empty( $grades['grade'] ) ? 0 : (int) $grades['grade'];

						$excluded_modules = array( 'input-textarea', 'input-text', 'input-upload', 'input-form' );

						if ( in_array( $module_type, $excluded_modules ) ) {
							if ( ( 'auto' == $graded_by || empty( $graded_by ) ) && ! empty( $response ) ) {
								$grade = __( 'Pending', 'coursepress' );
							}
						}

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
									$file_size = $file_size ? CoursePress_Helper_Utility::format_file_size( $file_size ) : '';
									$file_size = ! empty( $file_size ) ? '<small>(' . esc_html( $file_size ) . ')</small>' : '';

									$file_name = explode( '/', $url );
									$file_name = array_pop( $file_name );

									$url = CoursePress_Helper_Utility::encode( $url );
									$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;

									$response_display = '<a href="' . esc_url( $url ) . '" class="button button-download">' . esc_html( $file_name ) . ' ' . CoursePress_Helper_Utility::filter_content( $file_size ) . '</a>';
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
									$student_response = $response_display[ $q_index ];

									$display .= sprintf( '<p class="question">%s</p>', $question['question'] );

									if ( ! empty( $response_display[ $q_index ] ) ) {

										$answers = $response_display[ $q_index ];

										foreach ( $answers as $a_index => $answer ) {
											if ( ! empty( $answer ) ) {
												$the_answer = CoursePress_Helper_Utility::get_array_val(
													$attributes,
													'questions/' . $q_index . '/options/answers/' . $a_index
												);
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
									$response_display = empty( $response_display ) ? __( 'No answer!', 'coursepress' ) : $response_display;
									$display = sprintf( '<p>%s</p>', $response_display );
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

						$response_date = ! isset( $response['date'] ) ? '' : date_i18n( get_option( 'date_format' ), CoursePress_Data_Course::strtotime( $response['date'] ) );
						$mandatory = cp_is_true( $attributes['mandatory'] ) ? '<span class="dashicons dashicons-flag mandatory"></span>' : '';
						$non_assessable = cp_is_true( $attributes['assessable'] ) ? '' : '<span class="dashicons dashicons-star-filled non-assessable"></span>';

						$extra = $mandatory . $non_assessable;

						$feedback_by = ( ! is_null( $feedback ) && isset( $feedback['feedback_by'] ) ) ? (int) $feedback['feedback_by'] : 0;
						$first_last = CoursePress_Helper_Utility::get_user_name( $feedback_by );

						$feedback_display = ! empty( $feedback['feedback'] ) ? '<div class="feedback"><div class="comment">' . $feedback['feedback'] . '</div><div class="instructor"> – <em>' . esc_html( $first_last ) . '</em></div></div>' : '';

						$grade_display = 0 === $grade || 0 < (int) $grade ? $grade . '%' : $grade;

						if ( $require_instructor_assessment ) {
							$is_assessable = true;
						}

						if ( 'Pending' === $grade_display && false === $is_assessable ) {
							$grade_display = __( 'Non-gradable', 'coursepress' );
						}

						$content .= '<tr class="row-module">';
						$content .= sprintf( '<td class="column-title">%s</td>', $title );
						$content .= sprintf( '<td class="column-answer">%s</td>', $response_display );
						$content .= sprintf( '<td class="td-right">%s</td>', $grade_display );
						$content .= '</tr>';

						if ( '' !== $feedback_display ) {
							$content .= '<tr>';
							$content .= '<td class="column-title">Feedback:</td>';
							$content .= sprintf( '<td colspan="2">%s</td>', $feedback_display );
							$content .= '</tr>';
						}
					}
				}
			}
			if ( 0 == $module_count ) {
				$content .= sprintf( '<tr><td colspan="3" class="non-gradable">%s</td></tr>', __( 'No gradable modules under this unit.', 'coursepress' ) );
			}
		}
		$content .= '</table></div>';

		return $content;
	}

	public static function coursepress_enrollment_templates( $atts ) {
		/**
		 * Avoid to load templates twice...
		 */
		global $post;

		if ( ! is_object( $post ) ) {
			$course_id = CoursePress_Helper_Utility::the_course( true );
			$post = get_post( $course_id );
			if ( ! is_a( $post, 'WP_Post' ) ) {
				return;
			}
		}
		if (
			isset( $post->coursepress_enrollment_templates_was_already_loaded )
			&& $post->coursepress_enrollment_templates_was_already_loaded
		) {
			return;
		}
		$post->coursepress_enrollment_templates_was_already_loaded = true;
		self::$templates_was_already_loaded = true;
		/**
		 * proceder shortcode
		 */
		$atts = shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
			),
			$atts,
			'course_page'
		);

		$course_id = (int) $atts['course_id'];

		if ( empty( $course_id ) ) {
			return '';
		}

		$nonce = wp_create_nonce( 'coursepress_enrollment_action' );

		$scode_1 = '[course_signup_form login_link_id="step2" show_submit="no"]';
		$scode_2 = '[course_signup_form signup_link_id="step1" show_submit="no" page="login"]';

		/**
		 * The filters below can be used to customize the output of the
		 * registration process.
		 */
		ob_start();
		?>
		<script type="text/template" id="modal-template">
			<div class="enrollment-modal-container"
				data-nonce="<?php echo esc_attr( $nonce ); ?>"
				data-course="<?php echo esc_attr( $course_id ); ?>"
				data-course-is-paid="<?php esc_html_e( intval( CoursePress_Data_Course::is_paid_course( $course_id ) ) ); ?>"
			></div>
		</script>

		<?php if ( apply_filters( 'coursepress_registration_form_step-1', true ) ) : ?>
		<script type="text/template" id="modal-view1-template" data-type="modal-step" data-modal-action="signup">
			<div class="bbm-modal-nonce signup" data-nonce="<?php echo wp_create_nonce( 'coursepress_enrollment_action_signup' ); ?>"></div>
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Create new account', 'coursepress' ); ?>
				</h3>
				<span id="error-messages"></span>
			</div>
			<div class="bbm-modal__section">
				<div class="modal-nav-link">
				<?php echo do_shortcode( $scode_1 ); ?>
				</div>
			</div>
			<div class="bbm-modal__bottombar">
			<input type="hidden" name="course_id" value="<?php esc_attr_e( $course_id ); ?>" />
			<input type="submit" class="bbm-button done signup button cta-button" value="<?php esc_attr_e( 'Create an account', 'coursepress' ); ?>" />
			<a href="#" class="cancel-link">
				<?php esc_html_e( 'Cancel', 'coursepress' ); ?>
			</a>
			</div>
		</script>
		<?php endif; ?>

		<?php if ( apply_filters( 'coursepress_registration_form_step-2', true ) ) : ?>
		<script type="text/template" id="modal-view2-template" data-type="modal-step" data-modal-action="login">
			<div class="bbm-modal-nonce login" data-nonce="<?php echo wp_create_nonce( 'coursepress_enrollment_action_login' ); ?>"></div>
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Login to your account', 'coursepress' ); ?>
				</h3>
				<span id="error-messages"></span>
			</div>
			<div class="bbm-modal__section">
				<div class="modal-nav-link">
				<?php echo do_shortcode( $scode_2 ); ?>
				</div>
			</div>
			<div class="bbm-modal__bottombar">
			<input type="submit" class="bbm-button done login button cta-button" value="<?php esc_attr_e( 'Log in', 'coursepress' ); ?>" />
			<a href="#" class="cancel-link"><?php esc_html_e( 'Cancel', 'coursepress' ); ?></a>
			</div>
		</script>
		<?php endif; ?>

		<?php if ( apply_filters( 'coursepress_registration_form_step-3', true ) ) : ?>
		<script type="text/template" id="modal-view3-template" data-type="modal-step" data-modal-action="enrolled">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Successfully enrolled.', 'coursepress' ); ?>
				</h3>
			</div>
			<div class="bbm-modal__section">
				<p>
					<?php esc_html_e( 'Congratulations! You have successfully enrolled. Click below to get started.', 'coursepress' ); ?>
				</p>
				<a href="<?php echo get_permalink( CoursePress_Helper_Utility::the_course( true ) ) . CoursePress_Core::get_slug( 'units' ); ?>"><?php _e( 'Start Learning', 'coursepress' ); ?></a>
			</div>
			<div class="bbm-modal__bottombar">
			</div>
		</script>
		<?php endif; ?>

		<?php if ( apply_filters( 'coursepress_registration_form_step-4', true ) ) : ?>
		<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="passcode">
			<div class="bbm-modal__topbar">
                <h3 class="bbm-modal__title"><?php esc_html_e( 'Could not enroll at this time.', 'coursepress' ); ?>
				</h3>
			</div>
            <div class="bbm-modal__section"><?php
			printf( '<p>%s</p>', esc_html__( 'A passcode is required to enroll. Click below to go back to the course.', 'coursepress' ) );
?>
                    <a href="<?php echo get_permalink( CoursePress_Helper_Utility::the_course( true ) ) . CoursePress_Core::get_slug( 'units' ); ?>"><?php _e( 'Go back to course!', 'coursepress' ); ?></a>
			</div>
			<div class="bbm-modal__bottombar">
			</div>
		</script>
		<?php endif; ?>

		<?php
		do_action( 'coursepress_registration_form_end', $atts );
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Course Progress.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_progress( $atts ) {
		extract(
			shortcode_atts(
				array(
					'course_id' => CoursePress_Helper_Utility::the_course( true ),
					'decimal_places' => '0',
				),
				$atts,
				'course_progress'
			)
		);

		if ( $course_id  ) { $course_id = (int) $course_id; }
		$decimal_places = (int) $decimal_places ;

		return number_format_i18n(
			CoursePress_Data_Student::get_course_progress(
				get_current_user_id(),
				$course_id
			),
			$decimal_places
		);
	}

	/**
	 * Course Unit Progress
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_unit_progress( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'unit_id' => false,
			'decimal_places' => '0',
		), $atts, 'course_unit_progress' ) );

		if ( ! empty( $course_id ) ) {
			$course_id = (int) $course_id;
		}
		$unit_id = (int) $unit_id;

		$decimal_places = sanitize_text_field( $decimal_places );

		$progress = number_format_i18n(
			CoursePress_Data_Student::get_unit_progress(
				get_current_user_id(),
				$course_id,
				$unit_id
			),
			$decimal_places
		);

		return $progress;
	}

	/**
	 * Course Required Message.
	 *
	 * Text: "x of y required elements completed".
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_mandatory_message( $atts ) {
		extract(
			shortcode_atts(
				array(
					'course_id' => CoursePress_Helper_Utility::the_course( true ),
					'unit_id' => CoursePress_Helper_Utility::the_post( true ),
					'message' => __( '%d of %d required elements completed.', 'coursepress' ),
				),
				$atts,
				'course_mandatory_message'
			)
		);

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$unit_id = (int) $unit_id;
		if ( empty( $unit_id ) ) { return ''; }

		$student_id = get_current_user_id();
		if ( empty( $student_id ) ) { return ''; }

		$mandatory = CoursePress_Data_Student::get_mandatory_completion(
			$student_id,
			$course_id,
			$unit_id
		);
		if ( empty( $mandatory['required'] ) ) { return ''; }

		$message = sanitize_text_field( $message );
		$mandatory_required = (int) $mandatory['required'];
		if ( empty( $mandatory_required ) ) {
			return '';
		}

		return sprintf(
			$message,
			(int) $mandatory['completed'],
			$mandatory_required
		);
	}

	/**
	 * Display percentage of unit completion.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_unit_percent( $atts ) {
		$shortcode_atts = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'unit_id' => CoursePress_Helper_Utility::the_post( true ),
			'format' => false,
			'style' => 'flat',
			'tooltip_alt' => __( 'Percent of the unit completion', 'coursepress' ),
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

		/**
		 * Filter progress_wheel attributes.
		 **/
		$shortcode_atts = apply_filters( 'coursepress_unit_progress_wheel_atts', $shortcode_atts );

		extract( $shortcode_atts );

		$course_id = (int) $course_id;
		$unit_id = (int) $unit_id;

		if ( empty( $course_id ) || empty( $unit_id ) ) {
			return 0;
		}

		$format = cp_is_true( $format );
		$style = sanitize_text_field( $style );
		$tooltip_alt = sanitize_text_field( $tooltip_alt );
		$knob_fg_color = sanitize_text_field( $knob_fg_color );
		$knob_bg_color = sanitize_text_field( $knob_bg_color );
		$knob_text_color = sanitize_text_field( $knob_text_color );
		$knob_data_thickness = sanitize_text_field( $knob_data_thickness );
		$knob_data_width = (int) $knob_data_width;
		$knob_data_height = (int) $knob_data_height;

		if ( empty( $knob_data_width ) && ! empty( $knob_data_height ) ) {
			$knob_data_width = $knob_data_height;
		}

		$knob_data_thickness = $knob_data_width * $knob_data_thickness;

		$percent_value = (int) CoursePress_Data_Student::get_unit_progress( get_current_user_id(), $course_id, $unit_id );

		$content = '';

		/**
		 * check is unit available?
		 */
		$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $unit_id, null );

		if ( $is_unit_available ) {
			if ( 'flat' == $style ) {
				$content = '<span class="percentage">' . ( $format ? $percent_value . '%' : $percent_value ) . '</span>';
			} elseif ( 'none' == $style ) {
				$content = $percent_value;
			} else {
				$data_value = $percent_value / 100;
				$content = '<div class="course-progress-disc-container"><a class="tooltip" alt="' . $tooltip_alt . '">';
				/**
				 * Knob settings as date fields
				 */
				$knob_settings = array();
				foreach ( $shortcode_atts as $key => $value ) {
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
	 * Course Grades
	 *
	 * @since 2.0.5
	 */
	public static function student_grades_table( $args ) {

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$workbook_is_active = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'allow_workbook', false ) );

		if ( false == $workbook_is_active ) {
			$content = sprintf( '<p class="message">%s</p>', __( 'Workbook is not available for this course.', 'coursepress' ) );
			return $content;
		}

		$args = shortcode_atts(
			array(
				'course_id' => $course_id,
			)
			,
			$args,
			'student_workbook_table'
		);

		$course_id = (int) $args['course_id'];
		$student_id = get_current_user_id();

		if ( empty( $course_id ) || empty( $student_id ) ) {
			return '';
		}
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

		$content = '';
		$unit_list = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$unit_list = CoursePress_Helper_Utility::sort_on_key( $unit_list, 'order' );

		$content .= '<div class="grades">';
		$content .= '<table class="grades-table">';

		$student_progress_units = ( ! empty( $student_progress['units'] ) ) ? $student_progress['units'] : array();
		foreach ( $unit_list as $unit_id => $unit ) {
			$progress = CoursePress_Data_Student::get_unit_progress( $student_id, $course_id, $unit_id, $student_progress );
			$content .= '<tbody>';
			$content .= '<tr class="row-unit">';
			$content .= sprintf( '<td class="workbook-unit unit-%s">%s</td>', esc_attr( $unit_id ), $unit['unit']->post_title );
			$content .= sprintf( '<td class="td-right">%s: %d%%</td>', __( 'Progress', 'coursepress' ), $progress );
			$content .= '</tr>';

			$module_count = 0;
			$module_done = 0;
			if ( isset( $unit['pages'] ) ) {
				foreach ( $unit['pages'] as $page ) {
					foreach ( $page['modules'] as $module_id => $module ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );
						$is_assessable = ! empty( $attributes['assessable'] );
						$require_instructor_assessment = ! empty( $attributes['instructor_assessable'] );
						if ( 'output' === $attributes['mode'] ) {
							continue;
						}
						$module_count += 1;
						$module_type = $attributes['module_type'];

						$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $student_progress );
						$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );

						// Check if the grade came from an instructor
						$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
						$graded_by = CoursePress_Helper_Utility::get_array_val(
							$grades,
							'graded_by'
						);
						$grade = empty( $grades['grade'] ) ? 0 : (int) $grades['grade'];

						$excluded_modules = array( 'input-textarea', 'input-text', 'input-upload', 'input-form' );

						$auto_grade = true;
						if ( in_array( $module_type, $excluded_modules ) ) {
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
									$checked = (array) $options['checked'];
									$checked = array_filter( $checked );
									$student_response = $response_display[ $q_index ];
									if ( ! empty( $response_display[ $q_index ] ) ) {
										$answers = $response_display[ $q_index ];
										foreach ( $answers as $a_index => $answer ) {
											if ( ! empty( $answer ) ) {
												$the_answer = CoursePress_Helper_Utility::get_array_val(
													$attributes,
													'questions/' . $q_index . '/options/answers/' . $a_index
												);
												$correct = ! empty( $checked[ $a_index ] );
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
						$grade_display = 0 === $grade || 0 < (int) $grade ? $grade . '%' : $grade;
						if ( $require_instructor_assessment ) {
							$is_assessable = true;
						}
					}
				}
			}
			$grade_display = __( 'No gradable modules under this unit.', 'coursepress' );
			if ( 0 != $module_count ) {
				$grade_display = __( '%d of %d elements completed.', 'coursepress' );
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
	 * Helper function to log student visit to the course in focus view mode.
	 *
	 * @param (array) $array
	 **/
	public static function log_student_visit( array $array ) {
		$course_id = $array['course'];
		$unit_id = $array['unit'];
		$page_number = 1;
		$module_id = 0;
		$type = $array['type'];

		if ( 'section' == $type ) {
			$page_number = $array['item_id'];
		} elseif ( 'module' == $type ) {
			$module_id = $array['item_id'];
			$page_number = CoursePress_Data_Shortcode_Template::get_module_page( $course_id, $unit_id, $module_id );
		}

		CoursePress_Data_Student::log_visited_course( $course_id, $unit_id, $page_number, $module_id );
	}

	/**
	 * Helper function to log student visit to a course in normal mode.
	 **/
	public static function log_normal_student_visit( $course_id, $unit_id, $page_number ) {
		CoursePress_Data_Student::log_visited_course( $course_id, $unit_id, $page_number );
	}
}
