<?php

class CoursePress_View_Admin_Assessment_Report {

	public static $slug = 'coursepress_reports';
	private static $title = '';
	private static $menu_title = '';

	public static function init() {
		self::$title = __( 'Reports/CoursePress', 'coursepress' );
		self::$menu_title = __( 'Reports', 'coursepress' );

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_settings_page_pre_render_' . self::$slug, array( __CLASS__, 'process_form' ) );
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
			'cap' => apply_filters( 'coursepress_capabilities', 'coursepress_reports_cap' ),
		);

		return $pages;
	}

	public static function process_form() {
		if ( isset( $_REQUEST['action'] ) && 'coursepress_report' === $_REQUEST['action'] ) {
			$course_id = isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : false;
			$unit_id = isset( $_POST['bulk-report-unit'] ) ? (int) $_POST['bulk-report-unit'] : 'all';
			$students = array();

			if ( isset( $_POST['bulk-report-submit'] ) ) {
				if ( isset( $_POST['bulk-actions'] ) ) {
					$students = (array) $_POST['bulk-actions'];
				}
			} else {
				$students = isset( $_POST['students'] ) ? (array) $_POST['students'] : false;
			}

			if ( ! $course_id && empty( $students ) ) {
				return;
			}

			self::report_content( $students, $course_id, $unit_id );
		}

	}

	public static function report_content( $students, $course_id, $unit_id = 'all' ) {
		$pdf_args = array(
			'format' => 'FI',
			'force_download' => true, // Use force_download with
			'url' => true, // url to hide path to file
			'orientation' => 'L',
		);

		$course_title = get_the_title( $course_id );
		$pdf_args['header'] = array(
			'title' => html_entity_decode( $course_title ),
		);

		$colors = apply_filters(
			'coursepress_report_colors',
			array(
				'title_bg' => '#0091CD',
				'title' => '#ffffff',
				'unit_bg' => '#F5F5F5',
				'unit' => '#000000',
				'no_items' => '#858585',
				'item_bg' => '#ffffff',
				'item' => '#000000',
				'item_line' => '#f5f5f5',
				'footer_bg' => '#0091CD',
				'footer' => '#ffffff',
			)
		);

		$html = '';

		// Get the units...
		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		// Or unit...
		$unit_file_part = 'all_units';
		if ( 'all' != $unit_id ) {
			$units = array( $unit_id => $units[ (int) $unit_id ] );
			$unit_file_part = $units[ (int) $unit_id ]['unit']->post_name;
		}

		$last_student = false;

		foreach ( $students as $student_id ) {
			$student_name = CoursePress_Helper_Utility::get_user_name( $student_id );
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

			$html .= '
				<table style="padding: 1mm">
					<thead>
						<tr>
							<th colspan="3" style="font-size: 5mm; background-color:' . esc_attr( $colors['title_bg'] ) . ';color:' . esc_attr( $colors['title'] ) . ';">' . esc_html( $student_name ) . '</th>
						</tr>
					</thead>
			';

			$course_assessable_modules = 0;
			$course_answered = 0;
			$course_total = 0;

			foreach ( $units as $unit_id => $unit_obj ) {

				if ( ! isset( $unit_obj['pages'] ) ) {
					continue;
				}

				$unit = $unit_obj['unit'];

				$html .= '
					<tbody>
						<tr style="font-weight:bold; font-size: 4mm; background-color: ' . esc_attr( $colors['unit_bg'] ) . '; color: ' . esc_attr( $colors['unit'] ) . ';">
							<th colspan="3">' . esc_html( $unit->post_title ) . '</th>
						</tr>
				';

				$assessable_modules = 0;
				$answered = 0;
				$total = 0;
				foreach ( $unit_obj['pages'] as $page ) {

					// $html .= '
					// <tr style="font-style:oblique; font-size: 4mm; background-color: ' . esc_attr( $colors['unit_bg'] ) . '; color: ' . esc_attr( $colors['unit'] ) . ';">
					// <th colspan="3">' . esc_html( $page['title'] ) . '</th>
					// </tr>
					// ';
					foreach ( $page['modules'] as $module_id => $module ) {

						$attributes = CoursePress_Data_Module::attributes( $module_id );

						if ( false === $attributes || 'output' === $attributes['mode'] || ! $attributes['assessable'] ) {
							continue;
						}

						$assessable_modules += 1;

						$grade = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
						$total += false !== $grade && isset( $grade['grade'] ) ? (int) $grade['grade'] : 0;
						$grade_display = false !== $grade && isset( $grade['grade'] ) ? (int) $grade['grade'] . '%' : '--';
						$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, false, $student_progress );
						$date_display = false !== $response && isset( $response['date'] ) ? $response['date'] : __( 'Not yet submitted', 'coursepress' );
						$answered += false !== $response && isset( $response['date'] ) ? 1 : 0;

						$html .= '
							<tr style="font-size: 4mm; background-color: ' . esc_attr( $colors['item_bg'] ) . '; color: ' . esc_attr( $colors['item'] ) . ';">
								<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';">' . esc_html( $module->post_title ) . '</td>
								<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';">' . esc_html( $date_display ) . '</td>
								<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';">' . esc_html( $grade_display ) . '</td>
							</tr>
						';

					}
				}

				if ( empty( $assessable_modules ) ) {
					$html .= '
							<tr style="font-style:oblique; font-size: 4mm; background-color: ' . esc_attr( $colors['item_bg'] ) . '; color: ' . esc_attr( $colors['no_items'] ) . ';">
								<td colspan="3"><em>' . esc_html__( 'No assessable items.', 'coursepress' ) . '</em></td>
							</tr>
						';
				}

				$html .= '
					</tbody>
				';

				$course_assessable_modules += $assessable_modules;
				$course_answered += $answered;
				$course_total += $total;
			}

			$average = $course_answered > 0 ? (int) ( $course_total / $course_answered ) : 0;
			$average_display = ! $course_answered && ! $assessable_modules ? '' : sprintf( __( 'Average response grade: %d%%', 'coursepress' ), $average );
			$course_average = $assessable_modules > 0 ? (int) ( $course_total / $course_assessable_modules ) : 0;
			$course_average_display = ! $assessable_modules ? __( 'No assessable items in this course.', 'coursepress' ) : sprintf( __( 'Total Average: %d%%', 'coursepress' ), $course_average );

			$html .= '
					<tfoot>
						<tr>
							<td colspan="2" style="font-size: 4mm; background-color:' . esc_attr( $colors['footer_bg'] ) . ';color:' . esc_attr( $colors['footer'] ) . ';">' . esc_html( $average_display ) . '</td>
							<td style="text-align:right; font-size: 4mm; background-color:' . esc_attr( $colors['footer_bg'] ) . ';color:' . esc_attr( $colors['footer'] ) . ';">' . esc_html( $course_average_display ) . '</td>
						</tr>
					</tfoot>
				</table>
				<p class="font-size:0.1mm;"></p>
			';

			$last_student = $student_id;

		}

		if ( count( $students ) === 1 ) {
			$student_name = CoursePress_Helper_Utility::get_user_name( $last_student );
			$pdf_args['filename'] = $course_title . '_' . $unit_file_part . '_' . $student_name;

		} elseif ( count( $students > 1 ) ) {
			$pdf_args['filename'] = $course_title . '_' . $unit_file_part . '_bulk';
		}

		$pdf_args['filename'] = sanitize_title( strtolower( str_replace( ' ', '-', $pdf_args['filename'] ) ) ).'.pdf';
		$pdf_args['footer'] = __( 'Course Report', 'coursepress' );

		CoursePress_Helper_PDF::make_pdf( $html, $pdf_args );

	}

	public static function render_page() {
		$content = '<div class="coursepress_settings_wrapper reports wrap">';
		$content .= CoursePress_Helper_UI::get_admin_page_title( self::$menu_title );
		$content .= self::render_report_list();
		$content .= '</div>';

		echo $content;
	}

	public static function render_report_list() {
		$content = '';
		$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );

		if ( empty( $courses ) ) {
			return esc_html__( 'You do not currently have any courses assigned.', 'coursepress' );
		}

		$selected_course = isset( $_GET['course_id'] ) ? (int) $_GET['course_id'] : $courses[0]->ID;

		$content .= '<div><strong>' . esc_html__( 'Select Course', 'coursepress' ) . '</strong><br />';
		$content .= CoursePress_Helper_UI::get_course_dropdown( 'course-list', 'course-list', $courses, array(
			'class' => 'medium',
			'value' => $selected_course,
		) );
		$content .= '</div>';

		$content .= '
			<form method="POST">
				<input type="hidden" name="students" value="" />
				<input type="hidden" name="course_id" value="' . $selected_course . '" />
				<input type="hidden" name="action" value="coursepress_report" />
		';

		/**
		 * Student List
		 */
		$list_course = new CoursePress_Helper_Table_ReportStudent();

		$list_course->set_course( $selected_course );
		$list_course->prepare_items();

		// The list table output
		ob_start();
		$list_course->display();
		$content .= ob_get_clean();

		$tooltip = '<span class="help-tooltip">' . esc_html__( 'Select entire course for selected students, or just a unit for selected students.', 'coursepress' ) . '</span>';
		$content .= '<div><strong>' . esc_html__( 'Bulk Reporting', 'coursepress' ) . '</strong>' . $tooltip . '<br />';

		$units = CoursePress_Data_Course::get_units( $selected_course );

		$content .= '
			<select name="bulk-report-unit" class="narrow">
				<option value="all">' . esc_html__( 'All units', 'coursepress' ) . '</option>
		';

		foreach ( $units as $unit ) {

			$content .= '<option value="' . esc_attr( $unit->ID ) . '">' . esc_html( $unit->post_title ) . '</option>';

		}

		$content .= '
			</select>
			<input type="submit" class="button button-primary" value="' . esc_attr__( 'Generate Report', 'coursepress' ) . '" name="bulk-report-submit" />
		';

		$content .= '
			</form>
		';

		return $content;
	}
}
