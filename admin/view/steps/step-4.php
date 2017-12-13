<?php
/**
 * Course Edit Step - 4
 **/
?>
<div class="step-title step-4">
	<?php _e( 'Step 4 &ndash; Course Dates', 'CP_TD' ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="cp-box-content step-content step-4">
	<input type="hidden" name="meta_setup_step_4" value="saved" />

	<div class="wide course-dates">
		<label><?php _e( 'Course Availability', 'CP_TD' ); ?></label>
		<p class="description"><?php _e( 'These are the dates that the course will be available to students', 'CP_TD' ); ?></p>
		<label class="checkbox medium">
			<input type="checkbox" name="meta_course_open_ended" <?php checked( true, $open_ended_course ); ?> />
			<span><?php _e( 'This course has no end date', 'CP_TD' ); ?></span>
		</label>
		<div class="date-range">
			<div class="start-date">
				<label for="meta_course_start_date" class="start-date-label required"><?php _e( 'Start Date', 'CP_TD' ); ?></label>

				<div class="date">
					<input type="text" class="dateinput timeinput" name="meta_course_start_date" value="<?php echo $course_start_date; ?>" /><i class="calendar"></i>
				</div>
			</div>
			<div class="end-date <?php echo ( $open_ended_course ? 'disabled' : '' ); ?>">
				<label for="meta_course_end_date" class="end-date-label required"><?php _e( 'End Date', 'CP_TD' ); ?></label>
				<div class="date">
					<input type="text" class="dateinput" name="meta_course_end_date" value="<?php echo $course_end_date; ?>" <?php echo ( $open_ended_course ? 'disabled="disabled"' : '' ); ?> />
				</div>
			</div>
		</div>
	</div>

	<div class="wide enrollment-dates">
		<label><?php _e( 'Course Enrollment Dates', 'CP_TD' ); ?></label>
		<p class="description"><?php _e( 'These are the dates that students will be able to enroll in a course.', 'CP_TD' ); ?></p>
		<label class="checkbox medium">
			<input type="checkbox" name="meta_enrollment_open_ended" <?php checked( true, $enrollment_open_ended ); ?> />
			<span><?php _e( 'Students can enroll at any time', 'CP_TD' ); ?></span>
		</label>
		<div class="date-range enrollment">
			<div class="start-date <?php echo ( $enrollment_open_ended ? 'disabled' : '' ); ?>">
				<label for="meta_enrollment_start_date" class="start-date-label required"><?php _e( 'Start Date', 'CP_TD' ); ?></label>

				<div class="date">
					<input type="text" class="dateinput" name="meta_enrollment_start_date" value="<?php echo esc_attr( $enrollment_start_date ); ?>" /><i class="calendar"></i>
				</div>
			</div>
			<div class="end-date <?php echo ( $enrollment_open_ended ? 'disabled' : '' ); ?>">
				<label for="meta_enrollment_end_date" class="end-date-label required"><?php _e( 'End Date', 'CP_TD' ); ?></label>
				<div class="date">
					<input type="text" class="dateinput" name="meta_enrollment_end_date" value="<?php echo esc_attr( $enrollment_end_date ); ?>" <?php echo ( $enrollment_open_ended ? 'disabled="disabled"' : '' ); ?> />
				</div>
			</div>
		</div>
	</div>

	<?php
	/**
	 * Trigger after printing step 4 fields.
	 **/
	echo apply_filters( 'coursepress_course_setup_step_4', '', $course_id );

	// Print buttons
	echo CoursePress_View_Admin_Course_Edit::get_buttons( $course_id, 4 );
	?>
	<br />
</div>