<?php
/**
 * Admin reports controller
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Reports extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_reports';
	var $with_editor = false;
	protected $cap = 'coursepress_reports_cap';
	protected $reports_table;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Reports', 'cp' ),
			'menu_title' => __( 'Reports', 'cp' ),
		);
	}

	public function process_form() {
		self::process_request();

		if ( empty( $_REQUEST['view'] ) ) {
			$this->reports_table = new CoursePress_Admin_Table_Reports;
			$this->reports_table->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20 ) );
		}
	}

	protected static function process_request() {
		if ( ! empty( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];

			// Check for download request
			if ( wp_verify_nonce( $nonce, 'coursepress_download_report' ) ) {
				$student_id = (int) $_REQUEST['student_id'];
				$course_id = (int) $_REQUEST['course_id'];
				self::report_content( array( $student_id ), $course_id );
				exit;
			}

			if ( ! empty( $_REQUEST['action'] ) ) {
				$action = strtolower( trim( $_REQUEST['action'] ) );

				if ( 'filter' == $action && wp_verify_nonce( $nonce, 'coursepress_report' )  ) {
					// Reload the page to apply filter
					$course_id = (int) $_REQUEST['course_id'];
					$url = add_query_arg( 'course_id', $course_id );
					wp_safe_redirect( $url ); exit;
				}
				if ( 'download' == $action ) {
					if ( ! empty( $_REQUEST['students'] ) ) {
						$students = (array) $_REQUEST['students'];
						$course_id = (int) $_REQUEST['course_id'];
						self::report_content( $students, $course_id );
						exit;
					} else {
						self::$warning_message = __( 'Select students to generate the report!', 'cp' );
					}
				}
			}
		}
	}

	public static function report_content( $students, $course_id, $unit_id = 'all' ) {
		$pdf_args = array(
			'format' => 'D',
			'force_download' => true, // Use force_download with
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
						$date_display = false !== $response && isset( $response['date'] ) ? $response['date'] : __( 'Not yet submitted', 'cp' );
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
								<td colspan="3"><em>' . esc_html__( 'No assessable items.', 'cp' ) . '</em></td>
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
			$average_display = ! $course_answered && ! $assessable_modules ? '' : sprintf( __( 'Average response grade: %d%%', 'cp' ), $average );
			$course_average = $assessable_modules > 0 ? (int) ( $course_total / $course_assessable_modules ) : 0;
			$course_average_display = ! $assessable_modules ? __( 'No assessable items in this course.', 'cp' ) : sprintf( __( 'Total Average: %d%%', 'cp' ), $course_average );

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
		$pdf_args['footer'] = __( 'Course Report', 'cp' );

		CoursePress_Helper_PDF::make_pdf( $html, $pdf_args );

	}
}