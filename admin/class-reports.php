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
			'title' => __( 'CoursePress Reports', 'coursepress' ),
			'menu_title' => __( 'Reports', 'coursepress' ),
		);
	}

	public function process_form() {
		self::process_request();

		if ( empty( $_REQUEST['view'] ) ) {
			$options = array(
				'default' => 20,
				'option' => 'coursepress_reports_per_page',
				'course_id' => isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0,
			);
			add_screen_option( 'per_page', $options );
			$this->reports_table = new CoursePress_Admin_Table_Reports;
			$this->reports_table->prepare_items();
		}
	}

	protected static function process_request() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}
		$nonce = $_REQUEST['_wpnonce'];

		// Check for download request
		if ( wp_verify_nonce( $nonce, 'coursepress_download_report' ) ) {
			$student_id = (int) $_REQUEST['student_id'];
			$course_id = (int) $_REQUEST['course_id'];
			$mode = isset( $_REQUEST['mode'] ) ? $_REQUEST['mode'] : 'pdf';
			self::report_content( array( $student_id ), $course_id, $mode );
			exit;
		}

		$action = '';
		if ( ! empty( $_REQUEST['action'] ) ) {
			$action = strtolower( trim( $_REQUEST['action'] ) );
		}
		if ( '-1' == $action && ! empty( $_REQUEST['action2'] ) ) {
			$action = strtolower( trim( $_REQUEST['action2'] ) );
		}

		switch ( $action ) {
			case 'filter': case 'Filter':
					// Reload the page to apply filter
					$course_id = (int) $_REQUEST['course_id'];
					$url = add_query_arg( 'course_id', $course_id );
					wp_safe_redirect( $url ); exit;
			break;
			case 'download':
			case 'show':
				if ( ! empty( $_REQUEST['students'] ) ) {
					$students = (array) $_REQUEST['students'];
					$course_id = (int) $_REQUEST['course_id'];
					$mode = 'show' == $action ? 'html':'pdf';
					self::report_content( $students, $course_id, $mode );
					exit;
				} else {
					self::$warning_message = __( 'Select students to generate the report!', 'coursepress' );
				}
			break;
			case 'download_summary':
			case 'show_summary':
				if ( ! empty( $_REQUEST['students'] ) ) {
					$students = (array) $_REQUEST['students'];
					$course_id = (int) $_REQUEST['course_id'];
					$mode = 'show_summary' == $action ? 'html':'pdf';
					self::report_content_multi( $students, $course_id, $mode );
					exit;
				} else {
					self::$warning_message = __( 'Select students to generate the report!', 'coursepress' );
				}
			break;
		}
	}

	public static function report_content( $students, $course_id, $mode = 'pdf' ) {
		$pdf_args = array(
			'format' => 'D',
			'force_download' => true, // Use force_download with
			'orientation' => 'L',
		);

		$course_title = get_the_title( $course_id );
		$pdf_args['header'] = array(
			'title' => html_entity_decode( $course_title ),
		);

		$colors = self::get_colors();

		$html = '';

		// Get the units...
		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		if ( 1 < count( $students ) ) {
			$html .= '<br />';
			$html .= sprintf( '<h2>%s</h2>', __( 'Units list', 'coursepress' ) );
			$html .= CoursePress_Data_Course::get_units_html_list( $course_id );
			$html .= '<br />';
			$html .= sprintf( '<h2>%s</h2>', __( 'Student list', 'coursepress' ) );
			$html .= '<ul>';
			foreach ( $students as $student_id ) {
				$student_name = CoursePress_Helper_Utility::get_user_name( $student_id );
				$html .= sprintf( '<li>%s</li>', esc_html( $student_name ) );
			}
			$html .= '</ul>';
		}

		$last_student = false;

		foreach ( $students as $student_id ) {
			$student_name = CoursePress_Helper_Utility::get_user_name( $student_id );
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

			/**
			 * Add page break here.
			 */
			if ( 1 < count( $students ) ) {
				$html .= '<br pagebreak="true" />';
			}

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
			$pdf_args['filename'] = $course_title . '_' . $student_name;
		} elseif ( count( $students > 1 ) ) {
			$pdf_args['filename'] = $course_title . '_bulk';
		}

		$pdf_args['filename'] = sanitize_title( strtolower( str_replace( ' ', '-', $pdf_args['filename'] ) ) ).'.pdf';
		$pdf_args['footer'] = __( 'Course Report', 'coursepress' );

		if ( 'html' == $mode ) {
			CoursePress_Helper_HTML::make( $html, $pdf_args );
			return;
		}

		CoursePress_Helper_PDF::make_pdf( $html, $pdf_args );

	}

	public static function report_content_multi( $students, $course_id, $mode = 'pdf' ) {
		$pdf_args = array(
			'format' => 'D',
			'force_download' => true, // Use force_download with
			'orientation' => 'L',
		);

		$course_title = get_the_title( $course_id );
		$pdf_args['header'] = array(
			'title' => html_entity_decode( $course_title ),
		);
		$colors = self::get_colors();
		$html = '';
		// Get the units...
		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		$last_student = false;
		$html .= '<table>';

		/**
		 * Header
		 */
		$style = sprintf(
			'font-size: 4mm; background-color:%s;color:%s;',
			$colors['footer_bg'],
			$colors['footer']
		);
		$html .= '<tr>';
		$html .= sprintf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Student', 'coursepress' ) );
		$html .= sprintf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Responses', 'coursepress' ) );
		$html .= sprintf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Average response grade', 'coursepress' ) );
		$html .= sprintf( '<th style="%s">%s</th>', esc_attr( $style ), __( 'Total Average', 'coursepress' ) );
		$html .= '</tr>';

		$i = 0;
		foreach ( $students as $student_id ) {
			$student_name = CoursePress_Helper_Utility::get_user_name( $student_id );
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );

			$html .= sprintf( '<tr style="background-color: %s">', $i++ % 2 ? $colors['row_even_bg']:$colors['row_odd_bg'] );
			$html .= '<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';">' . esc_html( $student_name ) . '</td>'
				;
			$course_assessable_modules = 0;
			$course_answered = 0;
			$course_total = 0;

			foreach ( $units as $unit_id => $unit_obj ) {
				if ( ! isset( $unit_obj['pages'] ) ) {
					continue;
				}
				$unit = $unit_obj['unit'];
				$assessable_modules = 0;
				$answered = 0;
				$total = 0;
				foreach ( $unit_obj['pages'] as $page ) {
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
						$answered += false !== $response && isset( $response['date'] ) ? 1 : 0;

					}
				}

				$course_assessable_modules += $assessable_modules;
				$course_answered += $answered;
				$course_total += $total;
			}

			$average = $course_answered > 0 ? (int) ( $course_total / $course_answered ) : 0;
			$average_display = ! $course_answered && ! $assessable_modules ? '' : sprintf( '%d%%', $average );
			$course_average = $assessable_modules > 0 ? (int) ( $course_total / $course_assessable_modules ) : 0;
			$course_average_display = sprintf( '%d%%', $course_average );

			$html .= '<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';text-align:center;">' . esc_html( $course_answered ) . '</td>';
			$html .= '<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';text-align:center;">' . esc_html( $average_display ) . '</td>';
			$html .= '<td style="border-bottom: 0.5mm solid ' . esc_attr( $colors['item_line'] ) . ';text-align:center;">' . esc_html( $course_average_display ) . '</td>';
			$html .= '</tr>';
			$last_student = $student_id;
		}
		$html .= '</table>';

		$pdf_args['filename'] = $course_title . '_bulk';

		$pdf_args['filename'] = sanitize_title( strtolower( str_replace( ' ', '-', $pdf_args['filename'] ) ) ).'.pdf';
		$pdf_args['footer'] = __( 'Course Report', 'coursepress' );

		if ( 'html' == $mode ) {
			CoursePress_Helper_HTML::make( $html, $pdf_args );
			return;
		}

		CoursePress_Helper_PDF::make_pdf( $html, $pdf_args );

	}

	private static function get_colors() {
		$colors = apply_filters(
			'coursepress_report_colors',
			array(
				'title_bg' => '#0091cd',
				'title' => '#ffffff',
				'unit_bg' => '#f5f5f5',
				'unit' => '#000000',
				'no_items' => '#858585',
				'item_bg' => '#ffffff',
				'item' => '#000000',
				'item_line' => '#f5f5f5',
				'footer_bg' => '#0091cd',
				'footer' => '#ffffff',
				'row_even_bg' => '#fdfdf0',
				'row_odd_bg' => '#fff',
			)
		);
		return $colors;
	}
}
