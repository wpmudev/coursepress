<div class="cp_popup_inner">
	<div class="cp_popup_title cp_popup_congratulations_title"><?php _e( 'Congratulations', '<%= wpmudev.plugin.textdomain %>' ); ?></div>
	<?php
	global $coursepress;
	$course_id      = (int) $args['course_id'];
	$course         = new Course( $course_id );
	$dashboard_link = '<a href="' . esc_url( $coursepress->get_student_dashboard_slug( true ) ) . '">' . __( 'Dashboard', '<%= wpmudev.plugin.textdomain %>' ) . '</a>';
	$course_link    = '<a href="' . esc_url( get_permalink( $course_id ) ) . '">' . $course->details->post_title . '</a>';
	?>
	<div class="cp_popup_success_message">
		<div class="congratulations-image">
			<img src="<?php echo esc_url( CoursePress::instance()->plugin_url . 'images/congrats-tick.png' ); ?>" alt="<?php esc_attr_e( 'Congratulations image', '<%= wpmudev.plugin.textdomain %>' ); ?>">
		</div
		<p><?php echo sprintf( __( 'You have successfully enrolled in %s', '<%= wpmudev.plugin.textdomain %>' ), $course_link ); ?><br/>
			<?php
			_e( 'You will receive an e-mail confirmation shortly.', '<%= wpmudev.plugin.textdomain %>' );
			?></p>

		<p><?php echo sprintf( __( 'You course will be available at any time in your %s', '<%= wpmudev.plugin.textdomain %>' ), $dashboard_link ); ?></p>
	</div>

	<?php
	if ( ( $course->details->course_start_date !== '' && $course->details->course_end_date !== '' ) || $course->details->open_ended_course == 'on' ) {//Course is currently active
		if ( ( strtotime( $course->details->course_start_date ) <= time() && strtotime( $course->details->course_end_date ) >= time() ) || $course->details->open_ended_course == 'on' ) {//Course is currently active
			?>
			<div class="cp_popup_button_container">
				<button class="apply-button enroll-success" data-link="<?php echo esc_url( trailingslashit( get_permalink( $course_id ) ) ) . trailingslashit( $coursepress->get_units_slug() ); ?>"><?php _e( 'Start Learning Now', '<%= wpmudev.plugin.textdomain %>' ); ?></button>
			</div>
		<?php
		}
	} ?>
</div>
<script type="text/javascript">
	coursepress_apply_data_link_click();
</script>