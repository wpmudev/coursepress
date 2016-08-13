<?php
	$course_id = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0;
	$course = get_post( $course_id );
	$unit_id = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : 0;
	$module_id = isset( $_REQUEST['module_id'] ) ? (int) $_REQUEST['module_id'] : 0;
	$student_id = isset( $_REQUEST['student_id'] ) ? (int) $_REQUEST['student_id'] : 0;
	$userdata = get_userdata( $student_id );
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
	<h2><?php esc_html_e( 'Assessments', 'CP_TD' ); ?></h2>

	<input type="hidden" id="cp_student_id" value="<?php echo $student_id; ?>" />
	<div class="cp-actions">
		<button style="display: none;" type="button" title="<?php esc_attr_e( 'Revalidate user submission', 'CP_TD' ); ?>" class="button cp-right cp-refresh-progress" data-course="<?php echo $course_id; ?>" data-student="<?php echo $student_id; ?>">
			<span class="fa fa-refresh"></span> <?php esc_html_e( 'Refresh', 'CP_TD' ); ?>
		</button>
		<div class="cp-box">
			<label><?php esc_html_e( 'Select Display', 'CP_TD' ); ?></label>
			<select id="grade-type" class="medium dropdown">
				<option value="all" <?php selected( 'all', $display_type ); ?>><?php esc_html_e( 'Show all modules', 'CP_TD' ); ?></option>
				<option value="all_assessable" <?php selected( 'all_assessable', $display_type ); ?>><?php esc_html_e( 'Show all assessable modules', 'CP_TD' ); ?></option>
			</select>
		</div>
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
							<?php esc_html_e( 'Certified', 'CP_TD' ); ?>
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