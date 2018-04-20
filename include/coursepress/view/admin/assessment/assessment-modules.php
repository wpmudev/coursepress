<?php
	$course_id = isset( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : 0;
	$course = get_post( $course_id );
	$unit_id = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : 0;
	$module_id = isset( $_REQUEST['module_id'] ) ? (int) $_REQUEST['module_id'] : 0;
	$student_id = isset( $_REQUEST['student_id'] ) ? (int) $_REQUEST['student_id'] : 0;
	$userdata = get_userdata( $student_id );
	$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
	$course_progress = (int) CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/average' );
	$display_type = isset( $_REQUEST['display'] ) && ! empty( $_REQUEST['display'] ) ? $_REQUEST['display'] : 'all';
	$assess = 'all_assessable' == $display_type;

	wp_nonce_field( 'student-grade-feedback' );
?>
<input type="hidden" id="cp_student_id" value="<?php echo $student_id; ?>" />
<div class="cp-actions">
	<button style="display: none;" type="button" title="<?php esc_attr_e( 'Revalidate user submission', 'coursepress' ); ?>" class="button cp-right cp-refresh-progress" data-course="<?php echo $course_id; ?>" data-student="<?php echo $student_id; ?>">
		<span class="fa fa-refresh"></span> <?php esc_html_e( 'Refresh', 'coursepress' ); ?>
	</button>
	<div class="cp-box">
		<label><?php esc_html_e( 'Select Display', 'coursepress' ); ?></label>
		<select id="grade-type" class="medium">
			<option value="all" <?php selected( 'all', $display_type ); ?>><?php esc_html_e( 'Show all modules', 'coursepress' ); ?></option>
			<option value="all_answered" <?php selected( 'all_answered', $display_type ); ?>><?php esc_html_e( 'Show all answered modules', 'coursepress' ); ?></option>
			<option value="all_assessable" <?php selected( 'all_assessable', $display_type ); ?>><?php esc_html_e( 'Show all assessable modules', 'coursepress' ); ?></option>
		</select>
	</div>
</div>
<div class="cp-content modules-answer-wrapper">
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
				<td width="5%">
					<span class="cp-course-grade final-grade" data-student="<?php echo $student_id; ?>"><?php echo $course_progress; ?>%</span>
				</td>
			</tr>
		</thead>
	</table>
	<div class="cp-responses">
		<?php echo CoursePress_View_Admin_Assessment_List::student_assessment( $student_id, $course_id, $student_progress, false, $assess, $display_type ); ?>
	</div>
</div>