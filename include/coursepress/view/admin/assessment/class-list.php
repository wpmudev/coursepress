<?php

class CoursePress_View_Admin_Assessment_List {

	public static $slug = 'coursepress_assessments';
	private static $title = '';
	private static $menu_title = '';
	private static $action = 'new';
	private static $allowed_actions = array(
		'new',
		'edit',
	);
	private static $tabs = array();
	private static $current_course = false;

	// Change flow
	private static $force_grid = false;
	private static $feedback_email = false;


	public static function init() {

		self::$action = isset( $_GET['action'] ) && in_array( $_GET['action'], self::$allowed_actions ) ? sanitize_text_field( $_GET['action'] ) : 'new';

		self::$title = __( 'Assessments/CoursePress', 'CP_TD' );
		self::$menu_title = __( 'Assessments', 'CP_TD' );
		self::$feedback_email = new CoursePress_View_Admin_Assessment_FeedbackEmail;

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );

		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );

		add_action( 'wp_ajax_update_assessment', array( __CLASS__, 'update_assessment' ) );
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			'cap' => apply_filters( 'coursepress_capabilities', 'coursepress_assessment_cap' ),
		);

		return $pages;
	}

	public static function update_assessment() {
		$data = json_decode( file_get_contents( 'php://input' ) );
		$json_data = array(
			'action' => $data->action,
		);
		$success = false;

		switch ( $data->action ) {
			case 'update':
				$course_id = $data->course_id;
				$unit_id = $data->unit_id;
				$module_id = $data->module_id;
				$student_id = $data->student_id;
				$grade = (int) $data->student_grade;
				$feedback_text = CoursePress_Helper_Utility::filter_content( $data->feedback_content );

				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

				$old_grade = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
				if ( ! empty( $old_grade['grade'] ) ) { $old_grade = $old_grade['grade']; }

				$old_feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
				if ( ! empty( $old_feedback['feedback'] ) ) {  $old_feedback = $old_feedback['feedback']; }

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

					if ( $feedback_text && trim( $feedback_text ) != trim( $old_feedback ) ) {
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

					$json_data['success'] = $success = true;
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
				$student_progress = CoursePress_Helper_Utility::unset_array_value(
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

				$json_data['html'] = self::get_students_table( $course_id, $unit_id, $type, $paged );
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

	public static function render_page() {
		// Check if we are in the right page!
		if ( isset( $_REQUEST['page'] ) && self::$slug != $_REQUEST['page'] ) {
			return '';
		}

		$content = '<div class="coursepress_settings_wrapper assessment wrap">';

		if ( isset( $_REQUEST['view_answer'] ) ) {
			$back_url = remove_query_arg(
				array( 'student_id', 'view_answer' )
			);
			$content .= '<span class="cp-right cp-back">'
				. '<a href="'. esc_url( $back_url ) . '"><span class="dashicons dashicons-arrow-left-alt2"></span> '. __( 'Back', 'CP_TD' ) . '</a>'
				. '</span>';
		}

		$content .= CoursePress_Helper_UI::get_admin_page_title( self::$menu_title );
		if ( isset( $_REQUEST['view_answer'] ) && ! self::$force_grid ) {
			$content .= self::view_grade_answer();
		} else {
			self::$force_grid = false;
			$content .= self::render_assessment();
		}

		$content .= '</div>';

		echo $content;
	}

	public static function view_grade_answer() {
		ob_start();

		require_once( dirname( __FILE__ ) . '/assessment-modules.php' );

		$content = ob_get_clean();
		return $content;
	}

	public static function render_assessment() {
		$user_id = get_current_user_id();
		$courses = CoursePress_Data_Instructor::get_accessable_courses( $user_id, true );
		$selected_course = isset( $_GET['course_id'] ) ? (int) $_GET['course_id'] : $courses[0]->ID;

		$current_unit = isset( $_REQUEST['unit'] ) ? $_REQUEST['unit'] : 'all';
		$type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'all';
		$units = CoursePress_Data_Course::get_units( $selected_course );
		$nonce = wp_create_nonce( 'cp_get_units' );
		$base_location = remove_query_arg( array( 'unit', 'type', 'paged' ) );

		$content = '<input type="hidden" id="base_location" value="' . esc_url( $base_location ) . '" />
			<div class="cp-assessment-page" data-nonce="' . esc_attr( $nonce ) . '">';

		if ( empty( $courses ) ) {
			$content .= sprintf( '<p class="description">%s</p>', __( 'You currently have no courses assigned.', 'CP_TD' ) );
		} else {
			$content .= '<div class="cp-course-selector"><br />
				<div class="cp-box">
					<label>' . esc_html__( 'Select Course', 'CP_TD' ) . '</label>
					' . CoursePress_Helper_UI::get_course_dropdown( 'course-list', 'course-list', $courses, array( 'class' => 'medium', 'value' => $selected_course ) )
					.
				'</div>
				<div class="cp-box">
					<select id="unit-list">
						<option value="all">' . esc_html__( 'Show all', 'CP_TD' ) . '</option>
						<option value="all_submitted"' . selected( 'all_submitted', $current_unit, false ) . '>' . esc_html__( 'Show all assessable students', 'CP_TD' ) . '</option>';

			foreach ( $units as $unit ) {
				$content .= '<option value="'. $unit->ID . '" ' . selected( $current_unit, $unit->ID, false ) . '>';
				$content .= esc_html__( sprintf( 'Show all students assessable for %s', $unit->post_title ) );
				$content .= '</option>';
			}
					$content .= '</select>
				</div>
				<div class="cp-box">
					<select id="ungraded-list">
						<option value="all">' . esc_html__( 'Show graded and ungraded students', 'CP_TD' ) . '</option>
						<option value="ungraded" ' . selected( 'ungraded', $type, false ) . '>' . esc_html__( 'Show ungraded students only', 'CP_TD' ) . '</option>
						<option value="graded" ' . selected( 'graded', $type, false ) . '>' . esc_html__( 'Show graded students only', 'CP_TD' ) . '</option>
					</select>
				</div>
			</div>';

			$content .= '<div id="assessment-table-container"></div>';
			$content .= sprintf( '<div class="cp-loader-info" style="display:none;"><span class="fa fa-spinner fa-spin"></span> %s</div>',
				__( 'Fetching students...', 'CP_TD' )
			);
		}

		$content .= '</div>';

		return $content;
	}

	public static function get_students_table( $course_id, $the_unit = 'all', $type = 'all', $paged = 1 ) {
		$per_page = 20;
		$offset = ($paged - 1) * $per_page;

		$results = CoursePress_View_Admin_Assessment_List::filter_students( $course_id, $the_unit, $type );
		$students = $results['students'];
		$total = count( $students );

		$students = array_slice( $students, $offset, $per_page );
		$date_format = get_option( 'date_format' );
		$content = '';

		if ( empty( $total ) ) {
			return sprintf( '<br><br><p class="description">%s</p>', __( 'There are no students found.', 'CP_TD' ) );
		}

		$table = '
			<table>
			<tr>
				<td>' . __( 'Students Found:', 'CP_TD' ) . ' ' . $total . '</td>
				<td>' . __( 'Modules:', 'CP_TD' ) . ' <span class="cp-total-assessable">' . $results['assessable'] . '</span></td>
				<td>' . __( 'Passing Grade: ', 'CP_TD' ) . ' <span class="cp-pasing-grade">' . $results['passing_grade'] . '%</span></td>
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

			if ( ! empty( $student_progress['units'] ) ) {
				$units = (array) $student_progress['units'];

				foreach ( $units as $unit_id => $unit ) {
					if ( ! empty( $units[ $unit_id ]['responses'] ) ) {
						$responses = $units[ $unit_id ]['responses'];

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
						<td class="final-grade" data-student="' . $student_id . '"></td>
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
								' . CoursePress_View_Admin_Assessment_List::student_assessment( $student_id, $course_id, $student_progress, $the_unit, ( $the_unit != 'all' ) ) . '
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

		$table .= CoursePress_Helper_UI::admin_paginate( $paged, $total, $per_page, $url );

		return $table;
	}

	public static function filter_students( $course_id, $unit_id = 0, $type = false ) {
		if ( empty( $student_ids ) ) {
			$student_ids = CoursePress_Data_Course::get_student_ids( $course_id );
		}

		$meta_query = array(
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'assessable',
					'value' => 1,
				),
				array(
					'key' => 'instructor_assessable',
					'value' => 1,
				)
			),
		);

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
							$assessable[ $module_id ] = $module_id;
						}

						$module_count[ $module_id ] = $module_id;

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
					$found_students[ $student_id ] = $student_id;
				} elseif ( 'ungraded' === $type && $student_grade < $minimum_grade ) {
					$found_students[ $student_id ] = $student_id;
				} elseif ( 'graded' === $type && true === $passed ) {
					$found_students[ $student_id ] = $student_id;
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

	public static function get_ids( $parent_id, $type = 'unit', $args = array() ) {
		$parent_id = ! is_array( $parent_id ) ? array( $parent_id ) : $parent_id;

		$args = wp_parse_args(
			$args,
			array(
				'post_status' => 'publish',
				'post_type' => $type,
				'post_parent__in' => $parent_id,
				'fields' => 'ids',
				'posts_per_page' => -1,
				'suppress_filters' => true,
			)
		);

		$results = get_posts( $args );

		return $results;
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

					if ( ! cp_is_true( $is_answerable ) ) {
						continue;
					}

					$no_anwer = 0 === count( $response );

					if ( $assess && false === $is_assessable ) {
						continue;
					}

					$unit_module_found += 1;

					if ( false === $assess && 0 === count( $response ) && $filter ) {
						continue;
					}
					$found_module += 1;

					$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
					$has_feedback = ! empty( $feedback );
					$feedback_class = $has_feedback ? ' cp-active' : '';
					$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
					$grade = empty( $grades['grade'] ) ? 0 : (int) $grades['grade'];

					if ( $require_instructor_assessment ) {
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

						$unit_grade += $grade;
						$pass = CoursePress_Helper_Utility::get_array_val(
							$student_progress,
							'completion/' . $unit_id . '/passed/' . $module_id
						);
						$is_pass = cp_is_true( $pass );
						$min_grade = empty( $attributes['minimum_grade'] ) ? 0 : (int) $attributes['minimum_grade'];
						$pass_class = $is_pass || $grade >= $min_grade ? ' green' : ' red';
						$no_anwer_class = 0 == count( $response ) ? ' cp-no-answer' : '';

						if ( $is_assessable || $require_instructor_assessment ) {
							$no_anwer_class .= ' module-assessable';
						}

						$page_content .= '<div class="cp-module '. $no_anwer_class . '">';

						if ( false === $no_anwer ) {

							$page_content .= '
									<div class="cp-right cp-assessment-div">
										<span class="cp-check cp-right ' . $pass_class . '">
											' . ( 'green' === trim( $pass_class ) ? __( 'Pass', 'CP_TD' ) : __( 'Fail', 'CP_TD' ) ) . '
										</span>
										<label class="cp-assess-label"> ' . __( 'Assessment Result', 'CP_TD' ) . '</label><br />
										<p class="coursepress-tooltip description cp-min-tooltip">
											' . __( 'The minimum grade required is: ', 'CP_TD' ) .'
											' . $min_grade . '
										</p>
										<p class="coursepress-tooltip cp-grade-tooltip">
											<input type="text" data-courseid="' . $course_id . '" data-unit="' . $unit_id . '" data-module="' . $module_id . '" data-minimum="' . esc_attr( $min_grade ) . '" data-student="'. $student_id . '" class="module-grade" name="module-grade" value="' . esc_attr( $grade ) . '" /> <span class="cp-percent">%</span>
											<button type="button" class="button-primary module-submit disabled">
												' . __( 'Submit Grade', 'CP_TD' ) . '
											</button>
										</p>
									</div>';
							if ( $is_required ) {
								$page_content .= '<span class="cp-required cp-right">' . __( 'Required', 'CP_TD' ) . '</span>';
							}
						} else {
							$page_content .= '
									<span class="cp-check cp-right ' . $pass_class . '">
										' . ( 'green' === trim( $pass_class ) ? __( 'Pass', 'CP_TD' ) : __( 'Fail', 'CP_TD' ) ) . '
									</span>';
							$page_content .= '<input type="hidden" data-courseid="' . $course_id . '" data-unit="' . $unit_id . '" data-module="' . $module_id . '" data-minimum="' . esc_attr( $min_grade ) . '" data-student="'. $student_id . '" class="module-grade" name="module-grade" value="0" />';
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
											$student_response = $response[ $q_index ];

											$page_content .= '<div class="cp-q"><hr />
														<p class="description cp-question">' . esc_html( $question['question'] ) . '</p>
														<ul>';

											foreach ( $options['answers'] as $p_index => $answer ) {
												$the_answer = isset( $checked[ $p_index ] ) ? $checked[ $p_index ] : false;
												$student_answer = '';

												if ( isset( $student_response[ $p_index ] ) && $student_response[ $p_index ] ) {
													$student_answer = $student_response[ $p_index ];

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
							}

								$page_content .= '</div>';
						}

						if ( 0 === count( $response ) ) {
							$page_content .= sprintf( '<div class="cp-answer-box"><span class="dashicons dashicons-no"></span> %s</div>', __( 'No answer!', 'CP_TD' ) );
						} else {
							// Will only allow feedback for 'Short', 'Long', and 'Upload' modules.
							$allowed_for_feedback = array( 'input-text', 'input-textarea', 'input-upload' );

							if ( in_array( $module_type, $allowed_for_feedback ) ) {

								$feedback_text = ! empty( $feedback['feedback'] ) ? $feedback['feedback'] : '';
								$feedback_by = ! empty( $feedback['feedback'] ) ? '- ' . CoursePress_Helper_Utility::get_user_name( $feedback['feedback_by'] ) : '';
								$add_label = $has_feedback ? __( 'Edit Feedback', 'CP_TD' ) : __( 'Add Feedback', 'CP_TD' );

								$page_content .= '<div class="cp-instructor-feedback">
										<h4>' . __( 'Instructor Feedback', 'CP_TD' ) . '</h4>
										<div class="cp-right cp-feedback-buttons">
											<button type="button" class="button-primary edit-feedback">' . $add_label . '</button>
											<button type="button" class="button-primary delete-feedback'. ( $has_feedback ? '' : ' disabled' ) . '">' . __( 'Delete Feedback', 'CP_TD' ) . '</button>
											<button type="button" class="button-primary send-feedback disabled">' . __( 'Send Feedback', 'CP_TD' ) . '</button>
										</div>
									';

								$page_content .= sprintf( '<div class="cp-feedback-details%s">%s</div><cite>%s</cite>', empty( $feedback_text ) ? ' empty' : '', $feedback_text, $feedback_by );
								$page_content .= sprintf( '<p class="description" %s>%s</p>', empty( $feedback_text ) ? '' : $hide, __( 'Write your feedback!', 'CP_TD' ) );

								$page_content .= '<textarea '. $hide . ' class="cp-temp-container"></textarea><textarea '. $hide . '" id="cp-editor-'. $course_id . '-'. $unit_id . '-'. $module_id . '" class="cp-feedback-content">'. esc_textarea( $feedback['feedback'] ). '</textarea>';
								$page_content .= '<div class="cp-feedback-text"></div>';
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
				$hidden_fields[ $unit_id ] = sprintf( '<input type="hidden" class="cp-total-unit-modules" data-unit="%s" value="%s" />', $unit_id, $unit_module_found );
			}
		}

		if ( empty( $content ) ) {
			$content .= sprintf( '<p class="div-info description">%s</p>', __( 'There are no assessable items!', 'CP_TD' ) );
		}

		$content .= implode( ' ', $hidden_fields );
		return $content;
	}
}
