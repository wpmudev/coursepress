<?php
/**
 * Course Edit Step - 5
 **/
?>
<div class="step-title step-5">
	<?php _e( 'Step 5 &ndash; Classes, Discussion & Workbook', 'CP_TD' ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="cp-box-content step-content step-5">
	<input type="hidden" name="meta_setup_step_5" value="saved" />

	<div class="wide class-size">
		<label><?php _e( 'Class Size', 'CP_TD' ); ?></label>
		<p class="description"><?php _e( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size(s).', 'CP_TD' ); ?></p>
		<label class="narrow col">
			<input type="checkbox" name="meta_class_limited" <?php checked( true, $class_limited ); ?> />
			<span><?php _e( 'Limit class size', 'CP_TD' ); ?></span>
		</label>

		<label class="num-students narrow col <?php echo ( $class_limited ? '' : 'disabled' ); ?>">
			<?php _e( 'Number of students', 'CP_TD' ); ?>
			<input type="text" class="spinners" name="meta_class_size" value="<?php echo $class_size; ?>" <?php echo ( $class_limited ? '' : 'disabled="disabled"' ); ?> />
		</label>
	</div>

	<?php
	$checkboxes = array(
		array(
			'meta_key' => 'allow_discussion',
			'title' => __( 'Course Discussion', 'CP_TD' ),
			'description' => __( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', 'CP_TD' ),
			'label' => __( 'Allow course discussion', 'CP_TD' ),
			'default' => false,
		),
		array(
			'meta_key' => 'allow_workbook',
			'title' => __( 'Student Workbook', 'CP_TD' ),
			'description' => __( 'If checked, students can see their progress and grades.', 'CP_TD' ),
			'label' => __( 'Show student workbook', 'CP_TD' ),
			'default' => false,
		),
		array(
			'meta_key' => 'allow_grades',
			'title' => __( 'Student grades', 'CP_TD' ),
			'description' => __( 'If checked, students can see their grades.', 'CP_TD' ),
			'label' => __( 'Show student grades', 'CP_TD' ),
			'default' => false,
		),
	);

	foreach ( $checkboxes as $one ) {
		echo CoursePress_Helper_UI::course_edit_checkbox( $one, $course_id );
	}

	/**
	 * Trigger after printing fields at step 5.
	 *
	 * The dynamic portion of this hook is to allow additional course meta fields.
	 **/
	echo apply_filters( 'coursepress_course_setup_step_5', '', $course_id );

	/**
	 * Print button **/
	echo CoursePress_View_Admin_Course_Edit::get_buttons( $course_id, 5 );
	?>
</div>