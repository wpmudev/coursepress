<?php
/**
 * @var int $course
 * @var int $course_id
 */

if ( ! empty( $course_id ) && ! empty( $course ) ) : ?>

	<script type="text/template" id="modal-template">
		<div class="enrollment-modal-container"
		     data-nonce="<?php echo esc_attr( $nonce ); ?>"
		     data-course="<?php echo esc_attr( $course_id ); ?>"
		     data-course-is-paid="<?php esc_html_e( intval( $course->is_paid_course() ) ); ?>"
		></div>
	</script>

	<?php if ( apply_filters( 'coursepress_registration_form_step-1', true ) ) : ?>
		<script type="text/template" id="modal-view1-template" data-type="modal-step" data-modal-action="signup">
			<div class="bbm-modal-nonce signup" data-nonce="<?php echo esc_attr( wp_create_nonce( 'coursepress_enrollment_action_signup' ) ); ?>"></div>
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Create new account', 'cp' ); ?>
				</h3>
				<span id="error-messages"></span>
			</div>
			<div class="bbm-modal__section">
				<div class="modal-nav-link">
					<?php echo do_shortcode( $scode_1 ); ?>
				</div>
			</div>
			<div class="bbm-modal__bottombar">
				<input type="hidden" name="course_id" value="<?php echo  esc_attr( $course_id ); ?>" />
				<input type="submit" class="bbm-button done signup button cta-button" value="<?php esc_attr_e( 'Create an account', 'cp' ); ?>" />
				<a href="#" class="cancel-link">
					<?php esc_html_e( 'Cancel', 'cp' ); ?>
				</a>
			</div>
		</script>
	<?php endif; ?>

	<?php if ( apply_filters( 'coursepress_registration_form_step-2', true ) ) : ?>
		<script type="text/template" id="modal-view2-template" data-type="modal-step" data-modal-action="login">
			<div class="bbm-modal-nonce login" data-nonce="<?php echo esc_attr( wp_create_nonce( 'coursepress_enrollment_action_login' ) ); ?>"></div>
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Login to your account', 'cp' ); ?>
				</h3>
				<span id="error-messages"></span>
			</div>
			<div class="bbm-modal__section">
				<div class="modal-nav-link">
					<?php echo do_shortcode( $scode_2 ); ?>
				</div>
			</div>
			<div class="bbm-modal__bottombar">
				<input type="submit" class="bbm-button done login button cta-button" value="<?php esc_attr_e( 'Log in', 'cp' ); ?>" />
				<a href="#" class="cancel-link"><?php esc_html_e( 'Cancel', 'cp' ); ?></a>
			</div>
		</script>
	<?php endif; ?>

	<?php if ( apply_filters( 'coursepress_registration_form_step-3', true ) ) : ?>
		<script type="text/template" id="modal-view3-template" data-type="modal-step" data-modal-action="enrolled">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title">
					<?php esc_html_e( 'Successfully enrolled.', 'cp' ); ?>
				</h3>
			</div>
			<div class="bbm-modal__section">
				<p>
					<?php esc_html_e( 'Congratulations! You have successfully enrolled. Click below to get started.', 'cp' ); ?>
				</p>
				<a href="<?php echo esc_url( $course->get_units_url() ); ?>"><?php esc_html_e( 'Start Learning', 'cp' ); ?></a>
			</div>
			<div class="bbm-modal__bottombar">
			</div>
		</script>
	<?php endif; ?>

	<?php if ( apply_filters( 'coursepress_registration_form_step-4', true ) ) : ?>
		<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="passcode">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Could not enroll at this time.', 'cp' ); ?>
				</h3>
			</div>
			<div class="bbm-modal__section"><?php
				printf( '<p>%s</p>', esc_html__( 'A passcode is required to enroll. Click below to back to course.', 'cp' ) );
				?>
				<a href="<?php echo esc_url( $course->get_units_url() ); ?>"><?php esc_html_e( 'Go back to course!', 'cp' ); ?></a>
			</div>
			<div class="bbm-modal__bottombar">
			</div>
		</script>
	<?php endif; ?>

<?php endif; ?>
