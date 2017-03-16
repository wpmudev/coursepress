<?php
/**
 * Course Edit Step - 6
 **/
$class = 'prerequisite' === $enrollment_type ? '' : ' hidden';
?>
<div class="step-title step-6">
	<?php printf( __( 'Step 6 &ndash; Enrollment %s', 'CP_TD' ), $title2 ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="step-content step-6">
	<input type="hidden" name="meta_setup_step_6" value="saved" />

	<div class="wide">
		<label><?php _e( 'Enrollment Restrictions', 'CP_TD' ); ?></label>
		<p class="description"><?php _e( 'Select the limitations on accessing and enrolling in this course.', 'CP_TD' ); ?></p>
		<?php echo CoursePress_Helper_UI::select( 'meta_enrollment_type', $enrollment_types, $enrollment_type, 'chosen-select medium' ); ?>
	</div>

	<div class="wide enrollment-type-options prerequisite<?php echo $class; ?>">
		<label><?php _e( 'Prerequisite Courses', 'CP_TD' ); ?></label>
		<p class="description"><?php _e( 'Select the courses a student needs to complete before enrolling in this course', 'CP_TD' ); ?></p>
		<select name="meta_enrollment_prerequisite" class="medium chosen-select chosen-select-course <?php echo $class_extra; ?>" multiple="true" data-placeholder=" ">

			<?php if ( ! empty( $courses ) ) : foreach ( $courses as $course ) : ?>
				<option value="<?php echo $course->ID; ?>" <?php selected( true, in_array( $course->ID, $saved_settings ) ); ?>><?php echo $course->post_title; ?></option>
			<?php endforeach; endif; ?>

		</select>
	</div>
</div>