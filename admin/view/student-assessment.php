<?php
$course_id = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0;
$can_update = CoursePress_Data_Capabilities::can_update_course( $course_id );
if ( ! $can_update ) {
	wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
}
$course = get_post( $course_id );
$unit_id = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : 0;
$module_id = isset( $_REQUEST['module_id'] ) ? (int) $_REQUEST['module_id'] : 0;
$student_id = isset( $_REQUEST['student_id'] ) ? (int) $_REQUEST['student_id'] : 0;
$userdata = get_userdata( $student_id );
CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
$course_grade = (int) CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/average' );
$display_type = isset( $_REQUEST['display'] ) && ! empty( $_REQUEST['display'] ) ? $_REQUEST['display'] : 'all';
$assess = 'all_assessable' == $display_type;
$is_completed = CoursePress_Helper_Utility::get_array_val(
	$student_progress,
	'completion/completed'
);
$is_completed = cp_is_true( $is_completed );
	// Hide certified if it is not completed
$certified = $is_completed ? '' : 'style="display:none;"';

wp_nonce_field( 'student-grade-feedback' );
?>
<div class="wrap coursepress_wrapper coursepress-assessment">
	<h2><?php esc_html_e( 'Student Workbook', 'coursepress' ); ?></h2><hr />

	<input type="hidden" id="cp_student_id" value="<?php echo $student_id; ?>" />
	<div class="cp-actions">
		<button style="display: none;" type="button" title="<?php esc_attr_e( 'Revalidate user submission', 'coursepress' ); ?>" class="button cp-right cp-refresh-progress" data-course="<?php echo $course_id; ?>" data-student="<?php echo $student_id; ?>">
			<span class="fa fa-refresh"></span> <?php esc_html_e( 'Refresh', 'coursepress' ); ?>
		</button>

		<div class="cp-box">
			<label><?php esc_html_e( 'Select course', 'coursepress' ); ?></label>
			<?php
			$enrolled_courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
			$enrolled_courses = array_map( 'get_post', $enrolled_courses );
			$options = array(
				'value' => $course_id,
				'class' => 'medium dropdown course-reload',
			);
			$courses = CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'course_id', $enrolled_courses, $options );
			echo $courses;
			?>
		</div>
		<div class="cp-box">
			<label><?php esc_html_e( 'Select Display', 'coursepress' ); ?></label>
			<select id="grade-type" class="medium dropdown">
				<option value="all" <?php selected( 'all', $display_type ); ?>><?php esc_html_e( 'Show all modules', 'coursepress' ); ?></option>
				<option value="all_assessable" <?php selected( 'all_assessable', $display_type ); ?>><?php esc_html_e( 'Show all assessable modules', 'coursepress' ); ?></option>
			</select>
		</div>
		<?php if ( $is_completed ) : ?>
		<div class="cp-box" style="float:right;">
			<?php
			$certificate_url = CoursePress_Data_Certificate::get_encoded_url( $course_id, $student_id );
			?>
			<a href="<?php echo esc_url( $certificate_url ); ?>" target="_blank" class="button"><?php _e( 'View Certificate', 'coursepress' ); ?></a>
		</div>
		<?php endif; ?>
	</div>
	<div class="cp-content modules-answer-wrapper" data-student="<?php echo $student_id; ?>">
		<table>
			<thead>
				<tr>
					<td class="student-data">
						<?php echo get_avatar( $userdata->user_email, 52 ); ?>
						<h3><?php echo $userdata->first_name . ' '. $userdata->last_name; ?><br />(<?php echo $userdata->display_name; ?>)</h3>
					</td>
					<td>
						<h3><?php echo $course->post_title; ?></h3>
					</td>
					<td align="right">
						<span class="cp-course-grade final-grade" data-student="<?php echo $student_id; ?>"><?php echo $course_grade; ?>%</span>
						<span class="cp-certified" <?php echo $certified; ?>>
							<?php esc_html_e( 'Certified', 'coursepress' ); ?>
						</span>
					</td>
				</tr>
			</thead>
		</table>
		<div class="cp-responses">
			<?php echo CoursePress_Admin_Assessment::student_assessment( $student_id, $course_id, $student_progress, false, $assess, $display_type ); ?>
		</div>
	</div>
</div>
