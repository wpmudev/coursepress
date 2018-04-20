<div class="step-title step-6">
	<?php printf( __( 'Step 6 &ndash; Enrollment %s', 'coursepress' ), $title2 ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="cp-box-content step-content step-6">
	<input type="hidden" name="meta_setup_step_6" value="saved" />

	<div class="wide">
		<label><?php _e( 'Enrollment Restrictions', 'coursepress' ); ?></label>
		<p class="description"><?php _e( 'Select the limitations on accessing and enrolling in this course.', 'coursepress' ); ?></p>
		<?php echo CoursePress_Helper_UI::select( 'meta_enrollment_type', $enrollment_types, $enrollment_type, 'chosen-select medium' ); ?>
	</div>

	<div class="wide enrollment-type-options prerequisite<?php echo $prerequisite_class; ?>">
		<label><?php _e( 'Prerequisite Courses', 'coursepress' ); ?></label>
		<p class="description"><?php _e( 'Select the courses a student needs to complete before enrolling in this course', 'coursepress' ); ?></p>
		<select name="meta_enrollment_prerequisite" class="medium chosen-select chosen-select-course <?php echo $class_extra; ?>" multiple="true" data-placeholder=" ">

			<?php if ( ! empty( $courses ) ) : foreach ( $courses as $course ) : ?>
				<option value="<?php echo $course->ID; ?>" <?php selected( true, in_array( $course->ID, $saved_settings ) ); ?>><?php echo $course->post_title; ?></option>
			<?php endforeach; endif; ?>

		</select>
	</div>

	<div class="wide enrollment-type-options passcode <?php echo $passcode_class; ?>">
		<label><?php _e( 'Course Passcode', 'coursepress' ); ?></label>
		<p class="description"><?php _e( 'Enter the passcode required to access this course', 'coursepress' ); ?></p>
		<input type="text" name="meta_enrollment_passcode" value="<?php echo esc_attr( $enrollment_passcode ); ?>" />
	</div>

	<?php if ( false === $disable_payment ) :
		$one = array(
				'meta_key' => 'payment_paid_course',
				'title' => __( 'Course Payment', 'coursepress' ),
				'description' => __( 'Payment options for your course. Additional plugins are required and settings vary depending on the plugin.', 'coursepress' ),
				'label' => __( 'This is a paid course', 'coursepress' ),
				'default' => false,
			);
		echo '<hr class="separator" />';
		echo CoursePress_Helper_UI::course_edit_checkbox( $one, $course_id );
	endif;
	?>

	<?php
	// Show install|payment messages when applicable
	if ( false === $payment_supported && false === $disable_payment ) :
		echo $payment_message;
	endif;
	?>
	<div class="is_paid_toggle <?php echo $payment_paid_course ? '' : 'hidden'; ?>">
		<?php
		/**
		 * Add additional fields if 'This is a paid course' is selected.
		 *
		 * Field names must begin with meta_ to allow it to be automatically added to the course settings
		 *
		 * * This is the ideal filter to use for integrating payment plugins
		 */
		echo apply_filters( 'coursepress_course_setup_step_6_paid', '', $course_id );
		?>
	</div>

	<?php
	/**
	 * Trigger to add additional fields in step 6.
	 **/
	echo apply_filters( 'coursepress_course_setup_step_6', '', $course_id );

	// Show buttons
	echo CoursePress_View_Admin_Course_Edit::get_buttons( $course_id, 6 );
	?>
</div>