<div class="cp_popup_inner">
	<div class="cp_popup_title cp_popup_congratulations_title"><?php _e( 'Congratulations', 'cp' ); ?></div>
	<?php
	global $coursepress;
	$course_id      = (int) $args['course_id'];
	$course         = new Course( $course_id );
	$dashboard_link = '<a href="' . esc_url( $coursepress->get_student_dashboard_slug( true ) ) . '">' . __( 'Dashboard', 'cp' ) . '</a>';
	$course_link    = '<a href="' . esc_url( get_permalink( $course_id ) ) . '">' . $course->details->post_title . '</a>';
	?>
	<div class="cp_popup_success_message">
		<div class="congratulations-image">
			<img src="<?php echo esc_url( CoursePress::instance()->plugin_url . 'images/congrats-tick.png' ); ?>" alt="<?php esc_attr_e( 'Congratulations image', 'cp' ); ?>">
		</div
		<p><?php echo sprintf( __( 'You have successfully enrolled in %s', 'cp' ), $course_link ); ?><br/>
			<?php
			_e( 'You will receive an e-mail confirmation shortly.', 'cp' );
			?></p>

		<p><?php echo sprintf( __( 'You course will be available at any time in your %s', 'cp' ), $dashboard_link ); ?></p>
	</div>

	<?php
	if ( ( $course->details->course_start_date !== '' && $course->details->course_end_date !== '' ) || $course->details->open_ended_course == 'on' ) {//Course is currently active
		if ( ( strtotime( $course->details->course_start_date ) <= time() && strtotime( $course->details->course_end_date ) >= time() ) || $course->details->open_ended_course == 'on' ) {//Course is currently active
			?>
			<div class="cp_popup_button_container">
				<button class="apply-button enroll-success" data-link="<?php echo esc_url( trailingslashit( get_permalink( $course_id ) ) ) . trailingslashit( $coursepress->get_units_slug() ); ?>"><?php _e( 'Start Learning Now', 'cp' ); ?></button>
			</div>
		<?php
		}
	} ?>
</div>
<script type="text/javascript">
	coursepress_apply_data_link_click();
</script>