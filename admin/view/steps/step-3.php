<?php
/**
 * Course Edit - Step 3
 **/
?>
<div class="step-title step-3">
	<?php _e( 'Step 3 &ndash; Instructors and Facilitators', 'CP_TD' ); ?>
	<div class="status <?php echo $setup_class; ?>"></div>
</div>

<div class="step-content step-3">
	<input type="hidden" name="meta_setup_step_3" value="saved" />

	<?php if ( $can_assign_instructor ) : ?>
		<div class="wide">
			<label><?php _e( 'Course Instructor(s)', 'CP_TD' ); ?>
				<p class="description"><?php _e( 'Select one or more instructor to facilitate this course', 'CP_TD' ); ?></p>
			</label>
			<select id="instructors" style="width:350px;" name="instructors" data-nonce-search="<?php echo $search_nonce; ?>" class="medium"></select>
			<input type="button" class="button button-primary instructor-assign disabled" value="<?php esc_attr_e( 'Assign', 'CP_TD' ); ?>" />
		</div>
	<?php endif; ?>

	<div class="instructors-info medium" id="instructors-info">
		<p><?php echo $can_assign_instructor ? __( 'Assigned Instructors:', 'CP_TD' ) : __( 'You do not have sufficient permission to add instructor!' ); ?></p>

		<?php if ( $instructors > 0 && $can_assign_instructor ) : ?>
			<div class="instructor-avatar-holder empty">
				<span class="instructor-name"><?php _e( 'Please Assign Instructor', 'CP_TD' ); ?></span>
			</div>
			<?php echo CoursePress_Helper_UI::course_pendings_instructors_avatars( $course_id ); ?>
		<?php else :
			echo CoursePress_Helper_UI::course_instructors_avatars( $course_id, array(), true );
		endif;
		?>
	</div>

	<?php if ( $can_assign_facilitator ) : ?>
		<div class="wide">
			<label><?php _e( 'Course Facilitator(s)', 'CP_TD' ); ?>
				<p class="description"><?php _e( 'Select one or more facilitator to facilitate this course', 'CP_TD' ); ?></p>
			</label>
			<select data-nonce-search="<?php echo $facilitator_search_nonce; ?>" name="facilitators" style="width:350px;" id="facilitators" class="user-dropdown medium"></select>
			<input type="button" class="button button-primary facilitator-assign disabled" value="<?php esc_attr_e( 'Assign', 'CP_TD' ); ?>" />
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $facilitators ) ) : ?>
		<div class="wide">
			<label><?php _e( 'Course Facilitators', 'CP_TD' ); ?></label>
		</div>
	<?php endif; ?>

	<div class="wide facilitator-info medium" id="facilitators-info">
		<?php echo CoursePress_Helper_UI::course_facilitator_avatars( $course_id, array(), true ); ?>
	</div>

	<?php if ( $can_assign_instructor || $can_assign_facilitator ) : ?>
	<?php endif; ?>
</div>