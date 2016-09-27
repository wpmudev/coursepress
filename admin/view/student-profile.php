<?php
$student_id = (int) $_GET['student_id'];
$student = get_userdata( $student_id );
$avatar = get_avatar( $student->user_email, 92 );
$enrolled_courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
$date_format = get_option( 'date_format' );
$time_format = get_option( 'time_format' );
?>
<div class="wrap coursepress_wrapper course-student-profile">
	<h2><?php esc_html_e( 'Profile', 'cp' ); ?></h2>
	<hr />

	<table class="widefat striped">
		<tr>
			<td rowspan="3" width="5%"><?php echo $avatar; ?></td>
			<td width="15%"><?php esc_html_e( 'Student ID', 'cp' ); ?></td>
			<td><?php echo $student_id; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'First Name', 'cp' ); ?></td>
			<td><?php echo $student->first_name; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Last Name', 'cp' ); ?></td>
			<td><?php echo $student->last_name; ?></td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Enrolled Courses', 'cp' ); ?></h2>
	<hr />
	<?php if ( count( $enrolled_courses ) > 0 ) : ?>
	<table class="wp-list-table widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Course', 'cp' ); ?></th>
				<th><?php esc_html_e( 'Date Enrolled', 'cp' ); ?></th>
				<th><?php esc_html_e( 'Average', 'cp' ); ?></th>
				<th><?php esc_html_e( 'Certificate', 'cp' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $enrolled_courses as $course_id ) :
				$course = CoursePress_Data_Course::get_course( $course_id );
				$date_enrolled = get_user_meta( $student_id, 'enrolled_course_date_' . $course->ID );
				if ( is_array( $date_enrolled ) ) {
					$date_enrolled = array_pop( $date_enrolled );
				}
				$date_enrolled = date_i18n( $date_format . ' ' . $time_format, CoursePress_Data_Course::strtotime( $date_enrolled ) );
				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course->ID );
				$average = CoursePress_Data_Student::average_course_responses( $student_id, $course->ID, $student_progress );
				$workbook_url = add_query_arg(
					array(
						'page' => 'coursepress_assessments',
						'student_id' => $student_id,
						'course_id' => $course_id,
						'view_answer' => 1,
						'display' => 'all_answered',
					)
				);
				$completed = CoursePress_Data_Student::is_course_complete( $student_id, $course->ID, $student_progress );
				$download_certificate = __( 'Not available', 'cp' );

				if ( $completed ) {
					$certificate_link = CoursePress_Data_Certificate::get_encoded_url( $course->ID, $student_id );
					$download_certificate = sprintf( '<a href="%s" class="button-primary">%s</a>', $certificate_link, __( 'Download', 'cp' ) );
				}
			?>
			<tr>
				<td>
					<strong><?php echo $course->post_title; ?></strong>
					<div class="row-actions cp-row-actions">
						<a href="<?php echo esc_url( $course->permalink ); ?>" target="_blank"><?php esc_html_e( 'View Course', 'cp' ); ?></a> |
						<a href="<?php echo $workbook_url; ?>"><?php esc_html_e( 'View Workbook', 'cp' ); ?></a> |
					</div>
				</td>
				<td><?php echo $date_enrolled; ?></td>
				<td><?php echo $average; ?>%</td>
				<td><?php echo $download_certificate; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>