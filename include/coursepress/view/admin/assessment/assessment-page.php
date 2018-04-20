<?php
	$user_id = get_current_user_id();
	$courses = CoursePress_Data_Instructor::get_accessable_courses( $user_id, true );
	$selected_course = isset( $_GET['course_id'] ) ? (int) $_GET['course_id'] : $courses[0]->ID;
	$paged = isset( $_REQUEST['paged'] ) ? (int) $_REQUEST['paged'] : 1;
	$per_page = 20;
	$offset = ($paged - 1) * $per_page;
	$current_unit = isset( $_REQUEST['unit'] ) ? $_REQUEST['unit'] : 'all';
	$type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : 'all';
	$students = CoursePress_View_Admin_Assessment_List::filter_students( $selected_course, $current_unit, $type );
	$total = count( $students );
	$students = CoursePress_Data_Course::get_students( $selected_course, $per_page, $offset );
	$date_format = get_option( 'date_format' );
	$units = CoursePress_Data_Course::get_units( $selected_course );
	$nonce = wp_create_nonce( 'cp_get_units' );
	$student_data = array();
	$base_location = remove_query_arg( array( 'unit', 'type' ) );
?>
<input type="hidden" id="base_location" value="<?php esc_attr_e( $base_location ); ?>" />
<div class="cp-assessment-page" data-nonce="<?php esc_attr_e( $nonce ); ?>">
	<?php if ( empty( $courses ) ): ?>
		<p class="description"><?php esc_html_e( 'You currently have no courses assigned.', 'coursepress' ); ?></p>
	<?php else: ?>
		<div class="cp-course-selector">
			<div class="cp-box">
				<label><?php esc_html_e( 'Select Course', 'coursepress' ); ?></label>
				<?php echo CoursePress_Helper_UI::get_course_dropdown( 'course-list', 'course-list', $courses, array( 'class' => 'medium', 'value' => $selected_course ) ); ?>
			</div>
			<div class="cp-box">
				<select id="unit-list">
					<option value="all"><?php esc_html_e( 'Show all', 'coursepress' ); ?></option>
					<option value="all_submitted" <?php selected( 'all_submitted', $current_unit ); ?>><?php esc_html_e('Show all assessable students', 'coursepress' ); ?></option>

					<?php foreach( $units as $unit ): ?>
						<option value="<?php echo $unit->ID; ?>" <?php selected( $current_unit, $unit->ID ); ?>>
							<?php esc_html_e( sprintf( 'Show all students assessable for %s', $unit->post_title ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="cp-box">
				<select id="ungraded-list">
					<option value="all"><?php esc_html_e( 'Show graded and ungraded students', 'coursepress' ); ?></option>
					<option value="ungraded" <?php selected( 'ungraded', $type ); ?>><?php esc_html_e( 'Show ungraded students only', 'coursepress' ); ?></option>
					<option value="graded" <?php selected( 'graded', $type ); ?>><?php esc_html_e( 'Show graded students only', 'coursepress' ); ?></option>
				</select>
			</div>
		</div>
		<table class="wp-list-table widefat fixed striped cp-table">
			<thead>
				<th><?php esc_html_e( 'Student', 'coursepress' ); ?></th>
				<th><?php esc_html_e( 'Last Active', 'coursepress' ); ?></th>
				<th class="unit-grade"><?php esc_html_e( 'Grade', 'coursepress' ); ?></th>
				<th width="10%"><?php esc_html_e( 'Submission', 'coursepress' ); ?></th>
			</thead>
			<tbody id="the-list">
				<?php
					$odd = '';

					foreach ( $students as $student ):
						$student_id = $student->ID;
						$avatar = get_avatar( $student->user_email, 32 );
						$view_link = add_query_arg(
							array(
								'student_id' => $student_id,
								'course_id' => $selected_course,
							),
							remove_query_arg( 'view_answer' )
						);
						$view_link .= '&view_answer';
						$student_label = CoursePress_Helper_Utility::get_user_name(
							$student_id,
							true
						);
						$student_label = sprintf( '<a href="%s">%s</a>', $view_link, $student_label );
						$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $selected_course );
						$student_data[$student_id] = $student_progress;

						$final_grade = (int) CoursePress_Helper_Utility::get_array_val(
							$student_progress,
							'completion/average'
						);
						
						$odd = 'odd' === $odd ? 'even' : 'odd';
						$last_active = '-';

						$course_completed = CoursePress_Helper_Utility::get_array_val(
							$student_progress,
							'completion/completed'
						);

						if ( cp_is_true( $course_completed ) ) {
							$odd .= ' course-completed';
						}

						if ( ! empty( $student_progress['units'] ) ) {
							$units = (array) $student_progress['units'];

							foreach ( $units as $unit_id => $unit ) {
								if ( ! empty( $units[$unit_id]['responses'] ) ) {
									$responses = $units[$unit_id]['responses'];

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
				?>

					<tr class="student-row student-row-<?php echo $student_id; ?> <?php echo $odd; ?>">
						<td><?php echo $avatar . $student_label; ?></td>
						<td class="unit-last-active"><?php echo $last_active; ?></td>
						<td class="unit-grade" data-student="<?php echo $student_id; ?>"></td>
						<td class="cp-actions">
							<span class="cp-edit-grade" data-student="<?php echo $student_id; ?>">
								<i class="dashicons dashicons-list-view"></i>
							</span>
							<a href="<?php echo esc_url( $view_link ); ?>" target="_blank" class="cp-popup">
								<span class="dashicons dashicons-external"></span>
							</a>
							<button type="button" data-course="<?php echo $selected_course; ?>" data-student="<?php echo $student_id; ?>" class="cp-refresh-progress">
								<span class="fa fa-refresh"></span>
							</button>
						</td>
					</tr>
					<tr class="cp-content" data-student="<?php echo $student_id; ?>" style="display: none;">
						<td class="cp-responses cp-inline-responses" colspan="4">
							<script type="text/template" id="student-grade-<?php echo $student_id; ?>">
								<?php echo CoursePress_View_Admin_Assessment_List::student_assessment( $student_id, $selected_course, $student_progress ); ?>
							</script>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<br />
		<div class="no-student-info" style="display: none;">
			<p class="description"><?php esc_html_e( '0 students found under this unit', 'coursepress' ); ?></p>
		</div>
		<div class="no-assessable-info" style="display: none;">
			<p class="description"><?php esc_html_e( 'There are no assessable students found!', 'coursepress' ); ?></p>
		</div>
		<?php
		$url = remove_query_arg( array( 'unit', 'type' ) );
		echo CoursePress_Helper_UI::admin_paginate( $paged, $total, $per_page, $url );
		?>
	<?php endif; ?>
</div>