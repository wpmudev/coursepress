<?php
/**
 * CoursePress Student Data Class
 *
 * Use to manage the student's course information/data.
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Template_Course {

	/**
	 * Get template contents for course list.
	 *
	 * @param array $courses Courses.
	 *
	 * @return string
	 */
	public static function course_list_table( $courses = array() ) {

		global $CoursePress, $CoursePress_Core;

		if ( ! is_array( $courses ) || empty( $courses ) ) {
			return '';
		}

		$content = '';
		$student_id = get_current_user_id();
		$student = coursepress_get_user( $student_id );
		$courses = array_filter( $courses );

		if ( ! empty( $courses ) ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			$table_header = '';
			$table_body = '';
			$certificated = coursepress_is_true( coursepress_get_setting( 'basic_certificate/enabled', true ) );

			$table_columns = array(
				'name' => __( 'Course', 'cp' ),
				'date_enrolled' => __( 'Date Enrolled', 'cp' ),
				'average' => __( 'Average', 'cp' ),
				'status' => __( 'Status', 'cp' ),
			);

			if ( $certificated ) {
				$table_columns['certificate'] = __( 'Certificate', 'cp' );
			}

			foreach ( $table_columns as $column => $column_label ) {
				$table_header .= sprintf( '<th class="column-%s">%s</th>', $column, $column_label );
			}
			$table_header .= '<th>&nbsp;</th>';

			$column_keys = array_keys( $table_columns );

			foreach ( $courses as $course ) {
				$course_url = $course->get_permalink();
				$completion_status = CoursePress_Data_Student::get_course_status( $course->ID, $student_id );
				$course_completed = $student->is_course_completed();

				$table_body .= '<tr>';

				foreach ( $column_keys as $column_key ) {
					switch ( $column_key ) {
						case 'name':
							$table_body .= sprintf( '<td><a href="%s">%s</a></td>', esc_url( $course_url ), $course->post_title );
							break;

						case 'date_enrolled':
							$date_enrolled = get_user_meta( $student_id, 'enrolled_course_date_' . $course->ID );

							if ( is_array( $date_enrolled ) ) {
								$date_enrolled = array_pop( $date_enrolled );
							}
							if ( empty( $date_enrolled ) ) {
								$date_enrolled = sprintf(
									'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
									__( 'Unknown enrolled date.', 'cp' )
								);
							} else {
								$date_enrolled = date_i18n( $date_format, $CoursePress_Core->strtotime( $date_enrolled ) );
							}
							$table_body .= sprintf( '<td>%s</td>', $date_enrolled );
							break;

						case 'average':
							$statuses = array( 'Ongoing', 'Awaiting Review' );

							if ( in_array( $completion_status, $statuses ) ) {
								$average = '&#8212;';
							} else {
								$average = CoursePress_Data_Student::average_course_responses( $student_id, $course->ID );
								$average .= '%';
							}
							$table_body .= sprintf( '<td>%s</td>', $average );
							break;

						case 'status':

							$table_body .= sprintf( '<td class="column-status">%s</td>', $completion_status );

							break;

						case 'certificate':
							$download_certificate = __( 'Not available', 'cp' );

							if ( $course_completed ) {
								$certificate = $CoursePress->get_class( 'CoursePress_Certificate' );
								$certificate_link = $certificate->get_encoded_url( $course->ID, $student_id );
								$download_certificate = sprintf( '<a href="%s" class="button-primary">%s</a>', $certificate_link, __( 'Download', 'cp' ) );
							}

							$table_body .= sprintf( '<td>%s</td>', $download_certificate );
							break;
					}
				}

				// Row actions
				$row_actions = array();

				$allow_workbook = coursepress_course_get_setting( $course->ID, 'allow_workbook' );

				if ( $allow_workbook ) {
					$workbook_url = $course->get_workbook_url( $course->ID );
					$workbook_link = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $workbook_url ), __( 'Workbook', 'cp' ) );

					$row_actions['workbook'] = $workbook_link;
				}

				$withdraw_link = add_query_arg( array(
					'_wpnonce' => wp_create_nonce( 'coursepress_student_withdraw' ),
					'course_id' => $course->ID,
					'student_id' => $student_id,
				) );
				$withdraw_link = sprintf( '<a href="%s" class="cp-withdraw-student">%s</a>', esc_url( $withdraw_link ), __( 'Withdraw', 'cp' ) );
				$row_actions['withdraw'] = $withdraw_link;

				$table_body .= sprintf( '<td class="row-actions">%s</td>', implode( ' | ', $row_actions ) );
				$table_body .= '</tr>';
			}

			$table_format = '<table class="cp-dashboard-table"><thead><tr>%s</tr></thead><tbody>%s</tbody></table>';

			$content .= sprintf( $table_format, $table_header, $table_body );
		}

		return $content;
	}
}
