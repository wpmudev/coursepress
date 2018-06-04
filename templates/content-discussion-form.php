<form method="post" action="<?php echo esc_url( $course->get_discussion_url() ); ?>" class="course-discussion">
<?php wp_nonce_field( 'add-new-discussion' ); ?>
    <input type="hidden" name="action" value="add_new_discussion" />
    <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
    <input type="hidden" name="course_id" value="<?php echo esc_attr( $course->ID ); ?>" />
	<?php
	// Course Area
	$options_unit = array(
		'name'		=> 'unit_id',
		'id'		=> 'unitID',
		'class'		=> 'units_dropdown',
		'value'		=> $section,
		'options'	=> array(),
	);
	$options_unit['options'][] = array(
		'label' => sprintf( '%s: %s', __( 'Course', 'cp' ), get_post_field( 'post_title', $course->ID ) ),
		'value' => 'course',
	);
	$units = $course->get_units();
	foreach ( $units as $unit ) :
		$options_unit['options'][] = array(
			'value' => $unit->ID,
			'label' => $unit->post_title,
		);
	endforeach;
	?>
	<div class="discussion-section">
        <label><?php esc_html_e( 'This discussion is about?', 'cp' ); ?></label>
        <p><?php coursepress_html_select( $options_unit, true ); ?></p>
	</div>
	<?php
		$button      = $id ? esc_html__( 'Update discussion', 'cp' ) : esc_html__( 'Add discussion', 'cp' );
		$cancel_link = $course->get_discussion_url();
	?>
    <div class="new_question">
        <label><?php esc_html_e( 'Title', 'coursepress' ); ?></label>
		<input class="discussion-title" name="title" type="text" placeholder="<?php esc_attr_e( 'Title of the discussion', 'cp' ); ?>" value=""/>
        <label><?php esc_html_e( 'Discussion', 'coursepress' ); ?></label>
		<textarea class="discussion-content" name="content" placeholder="<?php esc_attr_e( 'Type your discussion or question here…', 'cp' ); ?>"></textarea>
		<div class="button-links">
			<a href="<?php echo esc_url( $cancel_link ); ?>" class="button_cancel"><?php esc_html_e( 'Cancel', 'cp' ); ?></a>
			<input type="submit" class="button_submit" name="new_question_submit" value="<?php esc_html_e( 'Ask this Question', 'cp' ); ?>"/>
		</div>
	</div>
</form>
