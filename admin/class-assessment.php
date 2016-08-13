<?php
/**
 * Assessments
 **/
class CoursePress_Admin_Assessment extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_assessments';
	var $with_editor = true;
	static $feedback_email = false;
	protected $cap = 'coursepress_settings_cap';

	public function __construct() {
		parent::__construct();
		self::$feedback_email = new CoursePress_Admin_FeedbackEmail;
	}

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Assessments', 'CP_TD' ),
			'menu_title' => __( 'Assessments', 'CP_TD' ),
		);
	}

	/**
	 * Set scripts and CSS needed for assessment page.
	 **/
	public function get_assets() {
		$this->scripts = array(
			'jquery-select2' => true,
			'admin-ui' => true,
			'core' => true,
			'assessment' => CoursePress::$url . 'asset/js/coursepress-assessment.js',
		);
		$this->css = array(
			'select2' => true,
			'admin-ui' => true,
			'assessment' => CoursePress::$url . 'asset/css/assessment.css',
		);

		// Set localize array for assessment only
		$this->localize_array['courseinstructor_id'] = get_current_user_id();
		$this->localize_array['instructor_name'] = CoursePress_Helper_Utility::get_user_name( get_current_user_id() );
		$this->localize_array['assessment_labels'] = array(
			'pass' => __( 'Pass', 'CP_TD' ),
			'fail' => __( 'Fail', 'CP_TD' ),
			'add_feedback' => __( 'Add Feedback', 'CP_TD' ),
			'edit_feedback' => __( 'Edit Feedback', 'CP_TD' ),
			'cancel_feedback' => __( 'Cancel', 'CP_TD' ),
			'success' => __( 'Success', 'CP_TD' ),
			'error' => __( 'Unable to save feedback!', 'CP_TD' ),
			'help_tooltip' => __( 'If the submission of this grade makes a student completes the course, an email with certificate will be automatically sent.', 'CP_TD' ),
			'minimum_help' => __( 'You may change this minimum grade from course setting.', 'CP_TD' ),
			'submit_with_feedback' => __( 'Submit grade with feedback', 'CP_TD' ),
			'submit_no_feedback' => __( 'Submit grade without feedback', 'CP_TD' ),
			'edit_with_feedback' => __( 'Edit grade with feedback', 'CP_TD' ),
			'edit_no_feedback' => __( 'Edit grade without feedback', 'CP_TD' ),
		);

		// We will not need media buttons and we only need teeny editor for our feedback
		$this->wp_editor_settings['media_buttons'] = false;
		$this->wp_editor_settings['teeny'] = true;
	}

	public function render_page() {
		$view_id = str_replace( 'coursepress_', '', $this->slug );
		$admin_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
		$view_file = $admin_path . $view_id . '.php';

		// Load per student assessment if student_id is present.
		if ( isset( $_REQUEST['student_id'] ) && ! empty( $_REQUEST['student_id'] ) ) {
			$view_file = $admin_path . 'student-assessment.php';
		}

		if ( is_readable( $view_file ) ) {
			require_once $view_file;
		}
	}

	public function process_form() {
		if ( isset( $_REQUEST['course_action'] ) && 'upload-file' === $_REQUEST['course_action'] ) {
			$_REQUEST['in_admin'] = true;
			$json = CoursePress_View_Front_Course::handle_module_uploads( true );

			if ( ! empty( $json['success'] ) ) {
				// Reload the page
				$return_url = remove_query_arg( array( 'course_action', 'ajax' ) );
				wp_safe_redirect( $return_url );
			} else {
				// Print Error
			}
			exit;
		}
	}

	public function ajax_request() {
		$data = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array(
			'action' => $data->action
		);
		$success = false;

		switch( $data->action ) {
			case 'update':
				$course_id = $data->course_id;
				$unit_id = $data->unit_id;
				$module_id = $data->module_id;
				$student_id = $data->student_id;
				$grade = (int) $data->student_grade;
				$with_feedback = ! empty( $data->with_feedback );

				$feedback_text = CoursePress_Helper_Utility::filter_content( $data->feedback_content );
				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

				$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
				$old_feedback = '';
				$draft_feedback = ! empty( $feedback['draft'] );
				if ( ! empty( $feedback['feedback'] ) ){ $old_feedback = $feedback['feedback']; }

				// Record new grade and get the progress back
				$student_progress = CoursePress_Data_Student::record_grade(
					$student_id,
					$course_id,
					$unit_id,
					$module_id,
					$grade,
					false,
					$student_progress
				);

				$is_feedback_new = false;

				if ( $with_feedback ) {
					$is_feedback_new = empty( $old_feedback );

					if ( ! empty( $old_feedback ) ) {
						$is_feedback_new = $draft_feedback || trim( $feedback_text ) != trim( $old_feedback );
					}

					if ( $is_feedback_new ) {
						// Record new feedback
						$student_progress = CoursePress_Data_Student::record_feedback(
							$student_id,
							$course_id,
							$unit_id,
							$module_id,
							$feedback_text,
							false,
							$student_progress
						);

						// New feedback, send email
						self::$feedback_email->send_feedback( $course_id, $unit_id, $module_id, $student_id, $feedback_text );
					}
				}

				CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
				$is_completed = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'completion/completed'
				);
				$json_data['completed'] = cp_is_true( $is_completed );
				$json_data['success'] = $success = true;
				break;

			case 'save_draft_feedback':
				$course_id = $data->course_id;
				$unit_id = $data->unit_id;
				$module_id = $data->module_id;
				$student_id = $data->student_id;
				$feedback_text = CoursePress_Helper_Utility::filter_content( $data->feedback_content );
				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

				CoursePress_Data_Student::record_feedback(
					$student_id,
					$course_id,
					$unit_id,
					$module_id,
					$feedback_text,
					false,
					$student_progress,
					true
				);

				$json['success'] = $success = true;
				break;
			case 'delete_feedback':
				$course_id = $data->course_id;
				$unit_id = $data->unit_id;
				$module_id = $data->module_id;
				$student_id = $data->student_id;
				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
				$responses = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'units/' . $unit_id . '/responses/' . $module_id
				);

				// Get last response
				$response_index = ( count( $responses ) - 1 );
				CoursePress_Helper_Utility::unset_array_val(
					$student_progress,
					'units/' . $unit_id . '/responses/' . $module_id . '/' . $response_index . '/feedback',
					$feedback_data
				);

				CoursePress_Data_Student::update_completion_data( $student_id, $course_id, $student_progress );
				$json_data['success'] = $success = true;

				break;

			case 'refresh':
				$course_id = $data->course_id;
				$student_id = $data->student_id;
				$display_type = $data->display_type;
				$assess = 'all_assessable' == $display_type;
				$progress = CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
				$json_data['success'] = $success = true;
				$json_data['html'] = self::student_assessment( $student_id, $course_id, $progress, $assess, $display_type );
				break;
			case 'table':
				$course_id = $data->course_id;
				$unit_id = $data->unit_id;
				$type = $data->student_type;
				$paged = $data->paged;
				$search = $data->search;

				$json_data['html'] = self::get_students_table( $course_id, $unit_id, $type, $paged, $search );
				$json_data['success'] = $success = true;
				break;
		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}
		exit;
	}

	/**
	 * Get all assessable courses by user.
	 *
	 * @param (int) $user_id			An user ID to base the courses from.
	 * @return (array) $courses			An array of courses the user allowed to assess.
	 **/
	public static function get_assessable_courses( $user_id = 0 ) {
		$now = CoursePress_Data_Course::time_now();

		if ( current_user_can( 'manage_options' ) ) {
			// An admin, get all published courses but have already started
			$post_args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_key' => 'cp_course_start_date',
				'meta_value' => $now,
				'meta_compare' => '<=',
			);
			$courses = new WP_Query( $post_args );
		} else {
			$user_id = get_current_user_id();
			$courses = array();

			if ( CoursePress_Data_Capabilities::is_facilitator( $user_id ) ) {
				$courses = CoursePress_Data_Facilitator::get_facilitated_courses( $user_id, 'publish' );
			}
			if ( CoursePress_Data_Capabilities::is_instructor( $user_id ) ) {
				$courses2 = CoursePress_Data_Instructor::get_accessable_courses( $user_id, 'publish' );

				// Combine courses instructed and facilitated
				if ( ! empty( $courses ) ) {
					foreach ( $courses as $course ) {
						foreach ( $courses2 as $course2 ) {
							if ( $course2->ID != $course->ID ) {
								$courses[] = $course2;
							}
						}
					}
				}
			}

			return $courses;
		}

		return $courses->posts;
	}

	/**
	 * Get course students according to course, unit and type.
	 *
	 * @param (int) $course_id				The course ID the user enrollled at.
	 * @param (mixed) $unit_id				The unit_id, all (with or without submission), all_submitted type the user have submissions at.
	 * @param (string) $type				Whether to return only graded|ungraded students. Default 'all', returns both graded and ungraded students.
	 * @param (array) $student_ids			Pre list of student IDs to filter to.
	 * @return (array) $found_students		An array of student IDs that pass all the applied filters.
	 **/
	public static function filter_students( $course_id, $unit_id = 0, $type = false, $student_ids = array() ) {
		if ( empty( $student_ids ) ) {
			remove_all_filters( 'pre_user_query' );
			$student_ids = CoursePress_Data_Course::get_student_ids( $course_id );
		}

		$found_students = array();
		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$assessable = array();
		$passing_grade = 100;
		$module_count = array();

		foreach ( $student_ids as $student_id ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			$minimum_grade = 0;
			$student_grade = 0;
			$unit_found = 0;
			$found_valid = 0;
			$have_submissions = is_array( $student_progress ) && count( $student_progress ) > 0;

			if ( false === $have_submissions ) {
				continue;
			}

			foreach ( $units as $_unit_id => $unit ) {

				if ( ! is_array( $unit['pages'] ) || ( $unit_id > 0 && $unit_id != $_unit_id ) ) {
					continue;
				}

				$module_found = 0;

				foreach ( $unit['pages'] as $page_number => $page ) {
					if ( ! is_array( $page['modules'] ) ) { continue; }

					foreach ( $page['modules'] as $module_id => $module ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );
						$module_type = $attributes['module_type'];
						$is_answerable = preg_match( '%input%', $module_type );
						$is_required = cp_is_true( $attributes['mandatory'] );
						$is_assessable = ! empty( $attributes['assessable'] ) && cp_is_true( $attributes['assessable'] );
						$require_instructor_assessment = ! empty( $attributes['instructor_assessable'] ) && cp_is_true( $attributes['instructor_assessable'] );
						$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $_unit_id, $module_id, false, $student_progress );
						$response = $response['response'];
						$is_assessable = $is_assessable || $require_instructor_assessment;

						if ( ! $is_answerable ) {
							continue;
						}

						$now_answer = 0 == count( $response );

						if ( $is_assessable ) {
							$assessable[$module_id] = $module_id;
						}

						$module_count[$module_id] = $module_id;

						if ( ( 'all_submitted' == $unit_id || $unit_id == $_unit_id ) && false === $is_assessable ) {
							continue;
						}

						$minimum = ! empty( $attributes['minimum_grade'] ) ? (int) $attributes['minimum_grade'] : 0;
						$minimum = max( 0, $minimum );
						$minimum_grade += $minimum;
						$module_found += 1;

						if ( 0 == count( $response ) ) {
							continue;
						}

						$found_valid += 1;
						$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $_unit_id, $module_id, false, false, $student_progress );
						$grade = empty( $grades['grade'] ) ? 0 : (int) $grades['grade'];

						if ( 'input-upload' === $module_type && ! empty( $require_instructor ) && cp_is_true( $require_instructor ) ) {
							// Check if the grade came from an instructor
							$graded_by = CoursePress_Helper_Utility::get_array_val(
								$grades,
								'graded_by'
								);
							if ( 'auto' === $graded_by ) {
								// Set 0 as grade if it is auto-graded
								$grade = 0;
							}
						}
						if ( $now_answer ) {
							$grade = 0;
						}

						$grade = max( 0, $grade );
						$student_grade += $grade;
					}
				}
				$unit_found += $module_found;
			}

			// Validate users
			if ( $found_valid > 0 ) {
				$length = 'all' === $unit_id ? $unit_found : count( $assessable );
				$student_grade = $length > 0 && $student_grade > 0 ? ceil( $student_grade / $length ) : 0;
				$minimum_grade = $length > 0 && $minimum_grade > 0 ? ceil( $minimum_grade / $length ) : 0;
				$passing_grade = $minimum_grade;

				$passed = $student_grade > 0 && $minimum_grade > 0 && $student_grade >= $minimum_grade;

				if ( 'all' === $type ) {
					$found_students[$student_id] = $student_id;
				} elseif ( 'ungraded' === $type && $student_grade < $minimum_grade ) {
					$found_students[$student_id] = $student_id;
				} elseif ( 'graded' === $type && true === $passed ) {
					$found_students[$student_id] = $student_id;
				}
			}
		}

		$student_ids = array_filter( $found_students );

		return array(
			'students' => $student_ids,
			'assessable' => 'all' === $unit_id ? count( $module_count ) : count( $assessable ),
			'passing_grade' => $passing_grade,
		);

	}

	/**
	 * Search students of the given course ID
	 **/
	public static function search_students( $course_id, $search_key) {
		global $wpdb;

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $course_id;
		}

		remove_all_filters( 'pre_user_query' );

		$search_key = trim( $search_key );
		$q = explode( ' ', $search_key );
		$q = array_filter( $q );

		$user_args = array(
			'fields' => 'ID',
			//'include' => $student_ids,
		);
		$results = array();

		if ( count( $q ) > 0 ) {
			if ( count( $q ) > 1 ) {
				// Compare first_name, last_name first
				$user_args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key' => $course_meta_key,
						'compare' => 'EXISTS',
					),
					array(
						'key' => 'first_name',
						'value' => $q[0],
						'compare' => 'LIKE',
					),
					array(
						'key' => 'last_name',
						'value' => $q[1],
						'compare' => 'LIKE',
					)
				);

				$query = new WP_User_Query( $user_args );

				if ( ! empty( $query->results ) ) {
					$results += $query->results;
				}
				unset( $user_args['meta_query'] );
			}

			// Let's compare to first_name
			$user_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key' => $course_meta_key,
					'compare' => 'EXISTS',
				),
				array(
					'key' => 'first_name',
					'value' => $search_key,
					'compare' => 'LIKE',
				),
			);

			$query = new WP_User_Query( $user_args );

			if ( ! empty( $query->results ) ) {
				$results += $query->results;
			}
			unset( $user_args['meta_query'] );

			// Compare to last name
			$user_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key' => $course_meta_key,
					'compare' => 'EXISTS',
				),
				array(
					'key' => 'last_name',
					'value' => $search_key,
					'compare' => 'LIKE',
				)
			);
			$query = new WP_User_Query( $user_args );
			if ( ! empty( $query->results ) ) {
				$results += $query->results;
			}
			unset( $user_args['meta_query'] );

			// Finally, compare to login, nicename
			$user_args['meta_key'] = $course_meta_key;
			$user_args['meta_compare'] = 'EXISTS';
			$user_args['search'] = $search_key . '*';
			$user_args['search_columns'] = array(
				'user_login',
				'user_nicename',
				'user_email'
			);
			$query = new WP_User_Query( $user_args );

			if ( ! empty( $query->results ) ) {
				$results += $query->results;
			}
		}

		return $results;
	}

	/**
	 * Prints student table
	 **/
	public static function get_students_table( $course_id, $the_unit = 'all', $type = 'all', $paged = 1, $search = false ) {
		$per_page = 20;
		$offset = ($paged - 1) * $per_page;

		$student_ids = array();
		$results = array( 'students' => array() );

		if ( ! empty( $search ) ) {
			$student_ids = self::search_students( $course_id, $search );

			if ( ! empty( $student_ids ) ) {
				$results = self::filter_students( $course_id, $the_unit, $type, $student_ids );
			}
		} else {
			$results = self::filter_students( $course_id, $the_unit, $type );
		}

		$students = $results['students'];
		$total = count( $students );

		$students = array_slice( $students, $offset, $per_page );
		$date_format = get_option( 'date_format' );
		$content = '';

		if ( empty( $total ) ) {
			return sprintf( '<br><br><p class="description">%s</p>', __( 'There are no students found..', 'CP_TD' ) );
		}

		$grading_system = __( 'total acquired grade % total number of gradable modules', 'CP_TD' );

		if ( 'all' != $the_unit ) {
			$grading_system = __( 'total acquired assessable grade % total number of assessable modules', 'CP_TD' );
		}

		$grading_system = '<em>' . $grading_system . '</em>';
		$table = '
			<table class="cp-result-details">
			<tr>
				<td>' . __( 'Students Found:', 'CP_TD' ) . ' ' . $total . '</td>
				<td>' . __( 'Modules:', 'CP_TD' ) . ' <span class="cp-total-assessable">' . $results['assessable'] . '</span></td>
				<td>' . __( 'Passing Grade: ', 'CP_TD' ) . ' <span class="cp-pasing-grade">' . $results['passing_grade'] . '%</span></td>
				<td>'. __( 'Grade System: ', 'CP_TD' ) . $grading_system . '</td>
			</tr>
			</table>
		';

		$table .= '<table class="wp-list-table widefat fixed striped cp-table">
			<thead>
				<th>' . esc_html__( 'Student', 'CP_TD' ) . '</th>
				<th>' . esc_html__( 'Last Active', 'CP_TD' ) . '</th>
				<th class="unit-grade">' . esc_html__( 'Grade', 'CP_TD' ) . '</th>
				<th width="5%">' . esc_html__( 'Modules', 'CP_TD' ) . '</th>
				<th width="5%">' . esc_html__( 'View All', 'CP_TD' ) . '</th>
			</thead>
			<tbody>
		';

		$students = array_map( 'get_userdata', $students );

		foreach ( $students as $student ) {
			$student_id = $student->ID;
			$avatar = get_avatar( $student->user_email, 32 );
			$view_link = add_query_arg(
				array(
					'page' => 'coursepress_assessments',
					'student_id' => $student_id,
					'course_id' => $course_id,
				),
				remove_query_arg( 'view_answer', admin_url( 'admin.php' ) )
			);
			$view_link .= '&view_answer&display=all_answered';
			$student_label = CoursePress_Helper_Utility::get_user_name( $student_id, true );
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			$last_active = '';
			$is_completed = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/completed'
			);
			$is_completed = cp_is_true( $is_completed );
			// Hide certified if it is not completed
			$certified = $is_completed ? '' : 'style="display:none;"';

			if ( ! empty( $student_progress['units'] ) ) {
				$units = (array) $student_progress['units'];

				foreach ( $units as $unit_id => $unit ) {
					if ( ! empty( $units[$unit_id]['responses'] ) ) {
						$responses = $units[$unit_id]['responses'];

						foreach ( $responses as $module_id => $response ) {
							$last = array_pop( $response );

							if ( ! empty( $last['date'] ) ) {
								$date = CoursePress_Data_Course::strtotime( $last['date'] );
								$last_active = max( (int) $last_active, $date );
							}
						}
					}
				}

				if ( $last_active > 0 ) {
					$last_active = date_i18n( $date_format, $last_active );
				}
			}

			$table .= '<tr class="student-row student-row-' . $student_id . '" data-student="'. $student_id . '">
						<td>' . $avatar . $student_label . '</td>
						<td class="unit-last-active">' . $last_active . '</td>
						<td data-student="' . $student_id . '">
							<span class="final-grade"></span>
							<span class="cp-certified" ' . $certified . '>'. esc_html__( 'Certified', 'CP_TD' ) . '</span>
						</td>
						<td class="cp-actions">
							<span class="cp-edit-grade" data-student="' . $student_id . '">
								<i class="dashicons dashicons-list-view"></i>
							</span>
						</td><td class="cp-actions">
							<a href="' . esc_url( $view_link ) . '" target="_blank" class="cp-popup">
								<span class="dashicons dashicons-external"></span>
							</a>
						</td>
					</tr>
					<tr class="cp-content" data-student="' . $student_id . '" style="display: none;">
						<td class="cp-responses cp-inline-responses" colspan="5">
							<script type="text/template" id="student-grade-' . $student_id . '">
								' . CoursePress_Admin_Assessment::student_assessment( $student_id, $course_id, $student_progress, $the_unit, ( $the_unit != 'all' ) ) . '
							</script>
						</td>
					</tr>';
		}

		$table .= '</tbody></table>';

		$table .= '<br><br><div class="no-student-info" style="display: none;">
			<p class="description">' . esc_html__( '0 students found under this unit', 'CP_TD' ) . '</p>
		</div>
		<div class="no-assessable-info" style="display: none;">
			<p class="description">' . esc_html__( 'There are no assessable students found!', 'CP_TD' ) . '</p>
		</div>';

		$url = add_query_arg(
			array(
				'course_id' => $course_id,
				'unit_id' => $unit_id,
				'type' => $type,
			)
		);

		if ( $total > $per_page ) {
			$table .= CoursePress_Helper_UI::admin_paginate( $paged, $total, $per_page, $url, __( 'student', 'CP_TD' ) );
		}

		return $table;
	}

	public static function student_assessment( $student_id, $course_id, $student_progress = false, $activeUnit = 'all', $assess = false, $display = false ) {
		if ( false === $student_progress ) {
			CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		}
		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );

		$content = '';
		$hide = ' style="display:none;"';
		$first_unit = true;
		$filter = true;
		$hidden_fields = array();

		if ( $display ) {
			$filter = false;

			if ( 'all' != $display ) {
				$filter = true;
			}
		}

		// Change wp-editor settings

		foreach ( $units as $unit_id => $unit ) {
			$the_unit = $unit['unit'];
			$unit_progress = CoursePress_Data_Student::get_all_unit_progress( $student_id, $course_id, $unit_id, $student_progress );

			$unit_wrapper = sprintf( '<div class="cp-unit-div" data-unit="%s" data-student="%s" data-progress="%s">',
				$unit_id,
				$student_id,
				$unit_progress
			);

			if ( $activeUnit > 0 && $activeUnit != $unit_id ) {
				continue;
			}

			$unit_title = '<h3 class="cp-toggle">
				<span class="cp-right unit-data cp-unit-toggle">
					<em class="unit-grade" data-unit="'. $unit_id . '" data-student="'. $student_id . '"></em>
					<i class="dashicons dashicons-arrow-' .( $hide ? 'down' : 'up' ) . '"></i>
				</span>
				'. $the_unit->post_title . '
				</h3>';

			$unit_content = '';

			// Page titles
			$page_titles = '<div class="cp-page-titles">';

			$first_unit = false;
			$first_page = true;
			$unit_grade = 0;
			$unit_module_found = 0;

				foreach ( $unit['pages'] as $page_number => $page ) {
					$short_title = wp_trim_words( $page['title'], 4, '...' );
					$page_title = sprintf( '<h4 class="cp-page-title">%s</h4>', $page['title'] );

					$inner_page = sprintf( '<div class="cp-page-modules page-number-%s">', $page_number );
					$page_content = '';
					$first_page = false; // Hide the rest of the pages
					$found_module = 0;

						foreach ( $page['modules'] as $module_id => $module ) {
							$attributes = CoursePress_Data_Module::attributes( $module_id );
							$module_type = $attributes['module_type'];
							$is_answerable = preg_match( '%input%', $module_type );
							$is_required = cp_is_true( $attributes['mandatory'] );
							$is_assessable = ! empty( $attributes['assessable'] ) && cp_is_true( $attributes['assessable'] );
							$require_instructor_assessment = ! empty( $attributes['instructor_assessable'] ) && cp_is_true( $attributes['instructor_assessable'] );
							$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $student_progress );
							$response = $response['response'];
							$is_assessable = $is_assessable || $require_instructor_assessment;
							$min_grade = empty( $attributes['minimum_grade'] ) ? 0 : (int) $attributes['minimum_grade'];

							if ( ! cp_is_true( $is_answerable ) ) {
								continue;
							}

							$no_anwer = 0 === count( $response );

							if ( $assess && false === $is_assessable ) {
								continue;
							}

							$unit_module_found += 1;

							if ( false === $assess && 0 === count( $response ) && $filter ) {
								//continue;
							}

							$found_module += 1;

							$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
							$has_feedback = ! empty( $feedback['feedback'] );
							$feedback_class = $has_feedback ? ' cp-active' : '';
							$feedback_text = $has_feedback ? $feedback['feedback'] : '';
							$feedback_by = $has_feedback ? '- ' . CoursePress_Helper_Utility::get_user_name( $feedback['feedback_by'] ) : '';

							$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
							$grade = empty( $grades['grade'] ) ? 0 : (int) $grades['grade'];

							$excluded_modules = array( 'input-textarea', 'input-text' );

							// Check if the grade came from an instructor
							$graded_by = CoursePress_Helper_Utility::get_array_val(
								$grades,
								'graded_by'
							);

							if ( $require_instructor_assessment || in_array( $module_type, $excluded_modules ) ) {
								if ( 'auto' === $graded_by ) {
									// Set 0 as grade if it is auto-graded
									$grade = 0;
								}
							}

							$unit_grade += $grade;
							$is_pass = $grade > 0 && $grade >= $min_grade;
							$pass_class = $is_pass ? ' green' : ' red';
							$no_anwer_class = 0 == count( $response ) ? ' cp-no-answer' : '';

							if ( $is_assessable || $require_instructor_assessment ) {
								$no_anwer_class .= ' module-assessable';
							}

							$page_content .= '<div class="cp-module '. $no_anwer_class . '" id="unit-' . $unit_id . '-module-' . $module_id . '">';

							// Will only allow feedback for 'Short', 'Long', and 'Upload' modules.
							$allowed_for_feedback = array( 'input-text', 'input-textarea', 'input-upload' );

							if ( false === $no_anwer && ( $is_assessable || $require_instructor_assessment ) && in_array( $module_type, $allowed_for_feedback ) ) {
								$no_feedback_button_label = __( 'Submit Grade without Feedback', 'CP_TD' );
								$with_feedback_button_label = __( 'Submit Grade with Feedback', 'CP_TD' );
								$pass_label = sprintf( __( 'The minimum grade to pass: %s', 'CP_TD' ), $min_grade );
								$pass_label .= '<br />';
								$pass_label .= __( 'You can change this minimum score from course settings.', 'CP_TD' );
								$module_status = $is_pass ? __( 'Pass', 'CP_TD' ) : __( 'Fail', 'CP_TD' );

								if ( false === $is_pass && ( empty( $graded_by ) || 'auto' === $graded_by ) ) {
									$module_status = __( 'Pending', 'CP_TD' );
								}

								if ( ! empty( $graded_by ) && 'auto' != $graded_by ) {
									$no_feedback_button_label = __( 'Edit Grade without Feedback', 'CP_TD' );
									$with_feedback_button_label = __( 'Edit Grade with Feedback', 'CP_TD' );
								}

								$page_content .= '<div class="cp-grade-editor">
									<div class="cp-right cp-assessment-div">
										<div>
											<div class="cp-module-grade-info">
												<label class="cp-assess-label">' . __( 'Assessment Result: ', 'CP_TD' ) . '</label>
												<span class="cp-current-grade">'. $grade . '%</span>
												<span class="cp-check ' . $pass_class . '">' . $module_status . '</span>
											</div>
											<button type="button" class="button-primary edit-no-feedback">' . $no_feedback_button_label . '</button>
											<button type="button" class="button-primary edit-with-feedback">' . $with_feedback_button_label . '</button>
										</div>
									</div>
									<textarea class="cp_feedback_content" style="display:none;">'. esc_textarea( $feedback_text ) . '</textarea>
									<div class="cp-grade-editor-box" style="display:none;">
										<div class="coursepress-tooltip cp-right cp-edit-grade-box">
											<label class="cp-assess-label">'. __( 'Grade', 'CP_TD' ) . '</label>
											<input type="text" name="module-grade" data-courseid="' . $course_id . '" data-unit="' . $unit_id . '" data-module="' . $module_id . '" data-minimum="' . esc_attr( $min_grade ) . '" data-student="' . $student_id . '" class="module-grade" data-grade="'. esc_attr( $grade ) . '" value="' . esc_attr( $grade ) . '" />
											<button type="button" class="button-primary cp-right cp-save-as-draft disabled">'. __( 'Save Feeback as Draft', 'CP_TD' ) . '</button>
											<button type="button" class="button-primary cp-submit-grade disabled">' . __( 'Submit Grade', 'CP_TD' ) . '</button>
											<button type="button" class="button cp-cancel">' . __( 'Cancel', 'CP_TD' ) . '</button>
											<p class="description">' . $pass_label . '</p>
										</div>
										<div class="cp-feedback-editor">
											<label class="cp-feedback-title">' . __( 'Feedback', 'CP_TD' ) . '</label>
											<p class="description">'. __( 'Your feedback will be emailed to the student after submission.', 'CP_TD' ) . '</p>
										</div>
									</div>
								</div>
								';

							} else {
								$page_content .= '<input type="hidden" data-courseid="' . $course_id . '" data-unit="' . $unit_id . '" data-module="' . $module_id . '" data-minimum="' . esc_attr( $min_grade ) . '" data-student="'. $student_id . '" class="module-grade" name="module-grade" value="'. esc_attr( $grade ) . '" />';

								if ( ( $is_assessable || $require_instructor_assessment ) && in_array( $module_type, $allowed_for_feedback ) ) {
									// Allow instructors to add answer
									$page_content .= '<div class="cp-right cp-instructor-edit">';

									if ( 'input-upload' === $module_type ) {
										$action_url = add_query_arg(
											array(
												'page' => 'coursepress_assessments',
												'course_id' => $course_id,
												'unit' => $activeUnit,
												'type' => ! empty( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'all',
												'student_id' => $student_id,
												'course_action' => 'upload-file',
												'src' => 'ajax',
											),
											admin_url( 'admin.php' )
										);
										$action_url .= '&view_answer#unit-' . $unit_id . '-module-' . $module_id;
										$page_content .= '<form method="post" action="' . $action_url . '" enctype="multipart/form-data" class="has-disabled">';
										$page_content .= sprintf( '<label class="cp-assess-label">%s</label>', __( 'Upload File', 'CP_TD' ) );
										$page_content .= '<input type="file" name="module-' . $module_id .'" class="input-key" />';
										$page_content .= '<input type="hidden" name="module_id" value="' . $module_id . '" />';
										$page_content .= '<input type="hidden" name="course_id" value="' . $course_id . '" />';
										$page_content .= '<input type="hidden" name="student_id" value="' . $student_id . '" />';
										$page_content .= '<input type="hidden" name="unit_id" value="' . $unit_id . '" />';
										$page_content .= '<input type="submit" class="button-primary disabled" value="' . __( 'Submit', 'CP_TD' ) . '" />';
										$page_content .= '</form>';
									}

									$page_content .= '</div>';
								} else {
									$page_content .= '<div class="cp-right cp-assessment-div">
											<div>
												<div class="cp-module-grade-info">
													<label class="cp-assess-label">' . __( 'Module Grade: ', 'CP_TD' ) . '</label>
													<span class="cp-current-grade">'. $grade . '%</span>
													<span class="cp-check ' . $pass_class . '">' . ( 'green' === trim( $pass_class ) ? __( 'Pass', 'CP_TD' ) : __( 'Fail', 'CP_TD' ) ) . '</span>
												</div>
											</div>
										</div>
									';
								}
							}

							$page_content .= sprintf( '<h4>%s</h4>', $module->post_title );

							if ( false === $no_anwer ) {

								$page_content .= '<div class="cp-response">';

									switch ( $module_type ) {
										case 'input-checkbox': case 'input-select': case 'input-radio':
											$answers = $attributes['answers'];
											$selected = (array) $attributes['answers_selected'];

											$page_content .= '<ul class="cp-answers">';

											foreach ( $answers as $key => $answer ) {
												$the_answer = in_array( $key, $selected );
												$student_answer = is_array( $response ) ? in_array( $key, $response ) : $response == $key;
	
												if ( 'input-radio' === $module_type ) {
													$student_answer = $response == $key;
												}

												if ( $student_answer ) {
													if ( $the_answer ) {
														$answer = '<span class="chosen-answer correct"></span>' . $answer;
													} else {
														$answer = '<span class="chosen-answer incorrect"></span>' . $answer;
													}
													$page_content .= sprintf( '<li>%s</li>', $answer );
												}

											}
											$page_content .= '</ul>';
	
											break;

										case 'input-textarea': case 'input-text':
											if ( ! empty( $response ) ) {
												$page_content .= sprintf( '<div class="cp-answer-box">%s</div>', $response );
											}
											break;
										case 'input-upload':
											if ( ! empty( $response['url'] ) ) {
												$url = $response['url'];
												$filename = basename( $url );
												$url = CoursePress_Helper_Utility::encode( $url );
												$url = trailingslashit( home_url() ) . '?fdcpf=' . $url;

												$page_content .= sprintf( '<a href="%s" class="button-primary cp-download">%s</a>', esc_url( $url ), $filename );
											}
											break;
										case 'input-quiz':
											if ( ! empty( $attributes['questions'] ) ) {
												$questions = $attributes['questions'];

												foreach ( $questions as $q_index => $question ) {
													$options = (array) $question['options'];
													$checked = (array) $options['checked'];
													$checked = array_filter( $checked );
													$student_response = $response[$q_index];

													$page_content .= '<div class="cp-q"><hr />
														<p class="description cp-question">' . esc_html( $question['question']  ) . '</p>
														<ul>';

															foreach ( $options['answers'] as $p_index => $answer ) {
																$the_answer = isset( $checked[$p_index] ) ? $checked[$p_index] : false;
																$student_answer = '';

																if ( isset( $student_response[$p_index] ) && $student_response[$p_index] ) {
																	$student_answer = $student_response[$p_index];

																	if ( $the_answer ) {
																		$student_answer = '<span class="chosen-answer correct"></span>';
																	} else {
																		$student_answer = '<span class="chosen-answer incorrect"></span>';
																	}
																	$page_content .= '<li>' . $student_answer . esc_html( $answer ) . '</li>';
																}

															}

													$page_content .= '</ul></div>';

												}
											}
											break;
										case 'input-form':
											if ( ! empty( $attributes['questions'] ) ) {
												$questions = $attributes['questions'];

												foreach ( $questions as $q_index => $question ) {
													$student_response = $response[$q_index];

													$page_content .= '<div class="cp-q"><hr />
														<p class="description cp-question">' . esc_html( $question['question']  ) . '</p>
														<ul>';
															foreach ( $response[$q_index] as $p_index => $answer ) {
																	$page_content .= '<li>' . esc_html( $answer ) . '</li>';
																}
													$page_content .= '</ul></div>';

												}
											}
											break;
											
									}

								$page_content .= '</div>';
							}

							if ( 0 === count( $response ) ) {
								$page_content .= sprintf( '<div class="cp-answer-box"><span class="dashicons dashicons-no"></span> %s</div>', __( 'No answer!', 'CP_TD' ) );
							} else {
								if ( false === $no_anwer && ( $is_assessable || $require_instructor_assessment ) && in_array( $module_type, $allowed_for_feedback ) ) {

									$is_draft = $has_feedback && ! empty( $feedback['draft'] );

									$page_content .= '<div class="cp-instructor-feedback" style="display: '. ( ! empty( $feedback ) ? 'block' : 'none' ) . '">
										<h4>' . __( 'Instructor Feedback', 'CP_TD' ) . ' <span class="cp-draft-icon" style="display: '. ( $is_draft ? 'inline-block' : 'none' ) . ';">['. __( 'Draft', 'CP_TD' ) . ']</span></h4>
									';
									$page_content .= sprintf( '<div class="cp-feedback-details%s">%s</div><cite>%s</cite>', empty( $feedback_text ) ? ' empty' : '', $feedback_text, $feedback_by );
									$page_content .= sprintf( '<p class="description" %s>%s</p>', empty( $feedback_text ) ? '' : $hide, __( 'Write your feedback!', 'CP_TD' ) );
									$page_content .= '</div>';
								}

							}

							$page_content .= '</div>';
						}

						if ( $found_module > 0 ) {
							$unit_content .= $page_title . $inner_page . $page_content . '</div>';
						}
				}

				if ( '' != $unit_content ) {
					$content .= $unit_wrapper . $unit_title;
					$content .= sprintf( '<div class="cp-modules">%s</div>', $unit_content );
					$content .= '</div>';
				}

				if ( $unit_module_found > 0 ) {
					$hidden_fields[$unit_id] = sprintf( '<input type="hidden" class="cp-total-unit-modules" data-unit="%s" value="%s" />', $unit_id, $unit_module_found );
				}
		}

		if ( empty( $content ) ) {
			$content .= sprintf( '<p class="div-info description">%s</p>', __( 'There are no assessable items!', 'CP_TD' ) );
		}

		$content .= implode( ' ', $hidden_fields );
		return $content;
	}
}
