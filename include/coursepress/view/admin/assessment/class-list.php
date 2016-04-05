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


	public static function init() {

		self::$action = isset( $_GET['action'] ) && in_array( $_GET['action'], self::$allowed_actions ) ? sanitize_text_field( $_GET['action'] ) : 'new';

		self::$title = __( 'Assessments/CoursePress', 'CP_TD' );
		self::$menu_title = __( 'Assessments', 'CP_TD' );

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'process_form' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );

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

	public static function process_form() {

		// Result / Feedback committed
		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'student-grade-feedback' ) ) {

			$new_feedback = isset( $_POST['feedback-content'] ) ? CoursePress_Helper_Utility::filter_content( $_POST['feedback-content'] ) : '';
			$new_grade = isset( $_POST['student-grade'] ) ? (int) $_POST['student-grade'] : false;

			$course_id = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0;
			$unit_id = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : 0;
			$module_id = isset( $_REQUEST['module_id'] ) ? (int) $_REQUEST['module_id'] : 0;
			$student_id = isset( $_REQUEST['student_id'] ) ? (int) $_REQUEST['student_id'] : 0;

			if ( empty( $course_id ) || empty( $unit_id ) || empty( $module_id ) || empty( $student_id ) ) {
				return ;
			}

			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			$old_grade = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
			$old_grade = $old_grade['grade'];
			$old_feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
			$old_feedback = $old_feedback['feedback'];

			if ( $new_grade && $new_grade != $old_grade ) {
				// Record new grade and get the progress back
				$student_progress = CoursePress_Data_Student::record_grade(
					$student_id,
					$course_id,
					$unit_id,
					$module_id,
					$new_grade,
					false,
					$student_progress
				);
			}

			if ( $new_feedback && trim( $new_feedback ) != trim( $old_feedback ) ) {
				// Record new feedback
				$student_progress = CoursePress_Data_Student::record_feedback(
					$student_id,
					$course_id,
					$unit_id,
					$module_id,
					$new_feedback,
					false,
					$student_progress
				);
			}
		}
	}

	public static function render_page() {
		$content = '<div class="coursepress_settings_wrapper assessment wrap">';
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
		$course_id = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0;
		$unit_id = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : 0;
		$module_id = isset( $_REQUEST['module_id'] ) ? (int) $_REQUEST['module_id'] : 0;
		$student_id = isset( $_REQUEST['student_id'] ) ? (int) $_REQUEST['student_id'] : 0;

		if ( empty( $course_id ) || empty( $unit_id ) || empty( $module_id ) || empty( $student_id ) ) {
			// Fall back...
			return self::render_assessment();
		}

		$student = get_userdata( $student_id );
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		$attributes = CoursePress_Data_Module::attributes( $module_id );
		$module = get_post( $module_id );
		$course = get_post( $course_id );
		$unit = get_post( $unit_id );

		$title = $module->post_title;
		$description = $module->post_content;
		$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $student_progress );
		$grade = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
		$feedback = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );

		$first_last = CoursePress_Helper_Utility::get_user_name( $student_id );

		$url = admin_url( 'admin.php?page=coursepress_assessments' );
		$url_course = admin_url( 'admin.php?page=coursepress_assessments&course_id=' . $course_id );
		$url_unit = admin_url( 'admin.php?page=coursepress_assessments&course_id=' . $course_id . '&unit_id=' . $unit_id );

		$content = '<div class="module-answer-wrapper"><form method="POST" action="' . esc_url_raw( $url_unit ) . '">';

		$content .= '
					<input type="hidden" name="course_id" value="' . $course_id . '" />
					<input type="hidden" name="unit_id" value="' . $unit_id . '" />
					<input type="hidden" name="module_id" value="' . $module_id . '" />
					<input type="hidden" name="student_id" value="' . $student_id . '" />
		';

		$content .= '<h3 class="student-name">' . $first_last . '</h3>' .
					'<p class="course-name"><strong>' . esc_html__( 'Course', 'CP_TD' ) . '</strong> : <a href="' . esc_url_raw( $url_course ) . '">' . $course->post_title . '</a></p>' .
					'<p class="unit-name"><strong>' . esc_html__( 'Unit', 'CP_TD' ) . '</strong> : <a href="' . esc_url_raw( $url_unit ) . '">' . $unit->post_title . '</a></p>' .
					'<hr>';

		$content .= '<h3 class="module-title">' . esc_html__( 'Activity: ', 'CP_TD' ) . '<span class="module-name">' . esc_html( $module->post_title ) . '</span></h3>' .
					'<div class="activity-wrapper">' .
					'<p class="description">' . $module->post_content . '</p>';

		if ( 'input-quiz' != $attributes['module_type'] ) {
			$content .= '<p><strong>' . esc_html__( 'Student Response', 'CP_TD' ) . '</strong></p>' .
					'<div class="response">';
		}

		$response_display = $response['response'];
		switch ( $attributes['module_type'] ) {

			case 'input-checkbox':

				$response_display = '<ul>';
				foreach ( $attributes['answers'] as $key => $answer ) {
					$the_answer = in_array( $key, $attributes['answers_selected'] );
					$student_answer = in_array( $key, $response['response'] );

					$class = '';
					if ( $student_answer && $the_answer ) {
						$class = 'chosen-answer correct';
					} elseif ( $student_answer && ! $the_answer ) {
						$class = 'chosen-answer incorrect';
					} elseif ( ! $student_answer && $the_answer ) {
						// $class = 'incorrect';
					}

					$response_display .= '<li class="' . $class . '">' . $answer . '</li>';

				}
				$response_display .= '</ul>';

				break;

			case 'input-radio':
			case 'input-select':
				$response_display = '';
				if ( isset( $response['response'] ) ) {
					$response_display = '<p class="answer">' . $attributes['answers'][ (int) $response['response'] ] . '</p>';
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

					$response_display = '<a href="' . esc_url( $url ) . '">' . esc_html( $file_name ) . ' ' . CoursePress_Helper_Utility::filter_content( $file_size ) . '</a>';
				} else {
					$response_display = '';
				}

				break;
			case 'input-quiz':
				$display = '';

				if ( $response_display ) {

					foreach ( $response_display as $q_index => $answers ) {
						$question = CoursePress_Helper_Utility::get_array_val(
							$attributes,
							'questions/' . $q_index . '/question'
						);
						$content .= sprintf('<p><strong>%s</strong></p>', $question );
						$content .= sprintf('<p><strong>%s</strong></p>', __( 'Student Response', 'CP_TD' ) );
						$content .= '<div class="response">';
						$q_answers = CoursePress_Helper_Utility::get_array_val(
							$attributes,
							'questions/' . $q_index . '/options/answers'
						);
						

						$content .= '<ul>';
						foreach ( $q_answers as $a_index => $answer ) {
							$checked = CoursePress_Helper_Utility::get_array_val(
								$attributes,
								'questions/' . $q_index . '/options/checked/' . $a_index
							);
							$class = '';
							if ( ! empty( $answers[$a_index ] ) ) {
								$class = 'chosen-answer ' . ( cp_is_true( $checked ) ? 'correct' : 'incorrect' );
							}
							$content .= sprintf( '<li class="%s">%s</li>', $class, $answer );
						}

						$content .= '</ul>';
						$content .= '</div>';
					}
				}
				$response_display = $display;
				break;
		}

		if ( 'input-quiz' != $attributes['module_type'] ) {
			$content .= $response_display;
			$content .= '</div>'; // .response
		}

		$response_date = ! isset( $response['date'] ) ? '' : date_i18n( get_option( 'date_format' ), strtotime( $response['date'] ) );
		$content .= '<div><em>' . sprintf( __( 'Submitted on: %s', 'CP_TD' ), $response_date ) . '</em></div>';

		$content .= '<p class="instructor-feedback-label"><strong>' . esc_html__( 'Instructor Feedback', 'CP_TD' ) . '</strong></p>' .
					'<div class="feedback">';

		$editor_name = 'feedback-content';
		$editor_id = 'feedbackContent';
		$editor_content = $feedback['feedback'];

		$args = array(
			'textarea_name' => $editor_name,
			'media_buttons' => false,
			'textarea_rows' => 3,
			'editor_class' => 'instructor-feedback',
		);

		ob_start();
		wp_editor( $editor_content, $editor_id, $args );
		$content .= ob_get_clean();

		$feedback_display = CoursePress_Helper_Utility::get_user_name( (int) $feedback['feedback_by'] );

		$content .= '<div><em>' . sprintf( __( 'Last feedback by: %s', 'CP_TD' ), $feedback_display ) . '</em></div>';
		$content .= '</div>'; // .feedback

		if ( ! empty( $grade['graded_by'] ) ) {
			if ( 'auto' != $grade['graded_by'] ) {
				$first_last = CoursePress_Helper_Utility::get_user_name( (int) $feedback['graded_by'] );
			} else {
				$first_last = __( 'Calculated', 'CP_TD' );
			}
		} else {
			$first_last = '';
		}

		$grade_date = ! isset( $grade['date'] ) ? '' : date_i18n( get_option( 'date_format' ), strtotime( $grade['date'] ) );
		$grade_date_display = empty( $grade_date ) ? '' : sprintf( __( 'on %s', 'CP_TD' ), $grade_date );
		$graded_by = 'auto' != $grade['graded_by'] ? sprintf( __( 'Graded by %s', 'CP_TD' ), $first_last ) : $first_last;

		$content .= '<p class="grading-label"><strong>' . esc_html__( 'Grade', 'CP_TD' ) . '</strong></p>' .
					'<div class="grading">' .
					'<div class="grading-by">' .
					$graded_by . ' ' .
					$grade_date_display .
					'</div>' .
					'<div class="grading-change">';

		$content .= '<select name="student-grade">';
		$content .= '<option value="-1" ' . selected( (int) $grade['grade'], -1, false ) . '>' . esc_html__( 'Ungraded', 'CP_TD' ) . '</option>';
		for ( $i = 0; $i <= 100; $i++ ) {
			$content .= '<option value="' . $i . '" ' . selected( (int) $grade['grade'], $i, false ) . '>' . $i . '%</option>';
		}
		$content .= '</select>';

		$content .= '</div>'; // .grading-change

		$content .= '</div>'; // .grading

		// Save
		$content .= '<div class="save-button">' .
					'<input type="submit" class="button button-primary" value="' . esc_attr__( 'Save Changes', 'CP_TD' ) . '" .>' .
					'</div>';

		$content .= '</div>'; // .activity-wrapper

		// ADD PERMISSION
		$content .= wp_nonce_field( 'student-grade-feedback', '_wpnonce', true, false );

		$content .= '</form></div>'; // .module-answer-wrapper

		return $content;

	}


	public static function render_assessment() {
		global $wp;

		$content = '';
		$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );

		if ( empty( $courses ) ) {
			return esc_html__( 'You do not currently have any courses assigned.', 'CP_TD' );
		}

		$selected_course = isset( $_GET['course_id'] ) ? (int) $_GET['course_id'] : $courses[0]->ID;

		$content .= '<div><strong>' . esc_html__( 'Select Course', 'CP_TD' ) . '</strong><br />';
		$content .= CoursePress_Helper_UI::get_course_dropdown( 'course-list', 'course-list', $courses, array( 'class' => 'medium', 'value' => $selected_course ) );
		$content .= ' <label class="ungraded-elements"><input type="checkbox" value="0" /><span>' . esc_html__( 'Ungraded elements only.', 'CP_TD' ) . '</span></label>';
		$content .= ' <label class="submitted-elements"><input type="checkbox" value="0" /><span>' . esc_html__( 'Submitted elements only.', 'CP_TD' ) . '</span></label>';
		$content .= ' <label class="expand-all-students"><a>' . esc_html__( 'Expand List', 'CP_TD' ) . '</a></label>';
		$content .= ' <label class="collapse-all-students"><a>' . esc_html__( 'Collapse List', 'CP_TD' ) . '</a></label>';
		$content .= '</div>';

		$units = CoursePress_Data_Course::get_units_with_modules( $selected_course, array( 'publish', 'draft' ) );
		$keys = array();
		if ( ! empty( $units ) ) {
			$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );
			$keys = array_keys( $units );

			$selected_unit = isset( $_GET['unit_id'] ) ? (int) $_GET['unit_id'] : $units[ $keys[0] ]['unit']->ID;

			// Get the tab array
			$tabs = array();
			foreach ( $units as $unit_id => $unit ) {
				$tabs[] = array(
					'unit_title' => $unit['unit']->post_title,
					'class' => $unit['unit']->post_status,
					'unit_id' => $unit_id,
				);
			}

			$url = admin_url( 'admin.php?page=coursepress_assessments' );
			$tab_string = '';

			foreach ( $tabs as $key => $tab ) {
				if ( $selected_unit == $tab['unit_id'] ) {
					$tab['class'] .= ' active';
				}
				$tab_url = add_query_arg(
					array(
						'course_id' => $selected_course,
						'unit_id' => $tab['unit_id'],
					)
				);
				$tab_string .= '<a href="' . $tab_url . '" class="unit-tab ' . $tab['class'] . '" data-unit="' . (int) $tab['unit_id'] . '" data-title="' . esc_attr( $tab['unit_title'] ) . '">' . ( $key + 1 ) . '</a>';
			}

			$content .= '<div class="unit-tabs-container"><span>' . esc_html__( 'Select Unit:', 'CP_TD' ) . '</span><div class="unit-tabs">' . $tab_string . '</div></div>';

			$content .= '<hr />';

			$content .= '<h3 class="unit-title">' . esc_html( $units[ $selected_unit ]['unit']->post_title ) . '</h3>';

			$students = CoursePress_Data_Course::get_students( $selected_course );

			/**
			 * Note: We're looping through each student getting the completion meta.
			 * This can potentially get slow depending on volume.
			 * When WP Core introduces similar filters for WP_User_Query than exist for WP_Query
			 * we can speed things up a bit by loading required meta when loading the students.
			 */

			$content .= '
			<table cellspacing="0">
				<thead>
					<tr>
						<th class="student">' . esc_html__( 'Student', 'CP_TD' ) . '</th>
						<th class="activity">' . esc_html__( 'Activity', 'CP_TD' ) . '</th>
						<th class="submission">' . esc_html__( 'Submission', 'CP_TD' ) . '</th>
						<th class="response">' . esc_html__( 'Response', 'CP_TD' ) . '</th>
						<th class="grade">' . esc_html__( 'Grade', 'CP_TD' ) . '</th>
						<th class="feedback">' . esc_html__( 'Feedback', 'CP_TD' ) . '</th>
					</tr>
				</thead>
			';

			$odd = '';
			$alt = 'alt';

			$count = 0;
			$hierarchy = 0;

			foreach ( $students as $student ) {
				$hierarchy += 1;
				$course_id = $selected_course;
				$unit_id = $selected_unit;
				$student_id = $student->ID;

				$student_label = CoursePress_Helper_Utility::get_user_name(
					$student_id,
					true
				);

				$student_progress = CoursePress_Data_Student::get_completion_data(
					$student_id,
					$course_id
				);

				$odd = 'odd' === $odd ? 'even' : 'odd';
				$alt = ! empty( $alt ) ? '' : 'alt';

				$content .= '<tbody id="' . $student_id . '" class="' . $odd . ' ' . $alt . '">';
				$content .= '<tr class="student-name treegrid-' . $hierarchy . '">' .
					'<td colspan="6">' . $student_label . '</td>' .
					'</tr>';

				$hierarchy_parent = $hierarchy;

				foreach ( $units[ $unit_id ]['pages'] as $page ) {
					$modules = $page['modules'];

					foreach ( $modules as $module_id => $module ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );

						if ( 'output' == $attributes['mode'] ) { continue; }

						$count += 1;

						$title = empty( $module->post_title ) ? $module->post_content : $module->post_title;
						$response = CoursePress_Data_Student::get_response(
							$student_id,
							$course_id,
							$unit_id,
							$module_id,
							false,
							$student_progress
						);
						$grade = CoursePress_Data_Student::get_grade(
							$student_id,
							$course_id,
							$unit_id,
							$module_id,
							false,
							false,
							$student_progress
						);
						$feedback = CoursePress_Data_Student::get_feedback(
							$student_id,
							$course_id,
							$unit_id,
							$module_id,
							false,
							false,
							$student_progress
						);

						$response_display = '';

						if ( $response ) {
							$qv = 'course_id=' . $course_id . '&unit_id=' . $unit_id . '&module_id=' . $module_id . '&student_id=' . $student_id . '&view_answer';
							$url = admin_url( 'admin.php?page=coursepress_assessments' . '&' . $qv );
							$response_display = '<a href="' . esc_url_raw( $url ) . '">' . esc_html__( 'View', 'CP_TD' ) . '</a>';
						}

						$response_date = ! isset( $response['date'] ) ? '' : date_i18n( get_option( 'date_format' ), strtotime( $response['date'] ) );
						$grade_display = (-1 == $grade['grade'] ? __( '--', 'CP_TD' ) : $grade['grade'] );

						$class = empty( $response_date ) ? 'not-submitted' : 'submitted';
						$class = (-1 == $grade['grade'] ) ? $class . ' ungraded' : $class . ' graded';

						$first_last = CoursePress_Helper_Utility::get_user_name( (int) $feedback['feedback_by'] );

						$feedback_display = ! empty( $feedback['feedback'] ) ? '<div class="feedback"><div class="comment">' . $feedback['feedback'] . '</div><div class="instructor"> – <em>' . esc_html( $first_last ) . '</em></div></div>' : '';

						$hierarchy += 1;
						$student_label = '';
						$content .= '<tr class="' . $class . ' treegrid-' . $hierarchy . ' treegrid-parent-' . $hierarchy_parent . '">' .
							'<td class="student-name">' . $student_label . '</td>' .
							'<td class="student-activity">' . $title . '</td>' .
							'<td class="student-submission">' . $response_date . '</td>' .
							'<td class="student-answer">' . $response_display . '</td>' .
							'<td class="student-grade">' . $grade_display . '</td>' .
							'<td class="instructor-feedback">' . $feedback_display . '</td>' .
							'</tr>';
					}
				}

				$content .= '</tbody>';

			}

			if ( empty( $count ) ) {
				$content .= '<tbody class="empty"><tr><td colspan="6">' . esc_html__( 'No activities found for this unit.', 'CP_TD' ) . '</td></tr></tbody>';
			}

			$content .= '
				<tfoot>
					<tr><td colspan="6">' . sprintf( __( '%d students', 'CP_TD' ), count( $students ) ) . '</td></tr>
				</tfoot>
			</table>
			';
		} else {
			$content .= '<div class="no-units">' . esc_html__( 'No units found for this course.', 'CP_TD' ) . '</div>';
		}

		return $content;

	}
}
