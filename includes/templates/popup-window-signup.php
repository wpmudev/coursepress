<form id="popup_signup_form">
	<div class="cp_popup_title"><?php _e( 'Sign Up', 'coursepress_base_td' ); ?></div>
	<input type="hidden" name="signup-next-step" value="enrollment"/>

	<?php if ( cp_user_can_register() ) { ?>

		<p class="cp_popup_required"><?php _e( 'All fields are required', 'coursepress_base_td' ); ?></p>

		<label class="cp_popup_col_2">
			<input type="text" name="student_first_name" class="required" id="cp_popup_student_first_name" placeholder="<?php _e( 'First Name', 'coursepress_base_td' ); ?>"/>
		</label>

		<label class="cp_popup_col_2 second-child">
			<input type="text" name="student_last_name" class="required" id="cp_popup_student_last_name" placeholder="<?php _e( 'Last Name', 'coursepress_base_td' ); ?>"/>
		</label>

		<label class="cp_popup_col_1">
			<input type="text" name="username" class="required" id="cp_popup_username" value="" placeholder="<?php _e( 'Username', 'coursepress_base_td' ); ?>">
		</label>

		<label class="cp_popup_col_2">
			<input type="text" name="email" class="required" id="cp_popup_email" placeholder="<?php _e( 'E-mail', 'coursepress_base_td' ); ?>"/>
		</label>

		<label class="cp_popup_col_2 second-child">
			<input type="text" class="required" id="cp_popup_email_confirmation" placeholder="<?php _e( 'E-mail Confirmation', 'coursepress_base_td' ); ?>"/>
		</label>

		<label class="cp_popup_col_2">
			<input type="password" class="required" name="cp_popup_password" id="cp_popup_password" placeholder="<?php _e( 'Password', 'coursepress_base_td' ); ?>"/>
		</label>

		<label class="cp_popup_col_2 second-child">
			<input type="password" class="required" id="cp_popup_password_confirmation" placeholder="<?php _e( 'Password Confirmation', 'coursepress_base_td' ); ?>"/>
		</label>

		<?php
		if ( shortcode_exists( 'signup-tos' ) ) {
			if ( get_option( 'show_tos', 0 ) == '1' ) {
				echo do_shortcode( '[signup-tos]' );
			}
		}
		?>

		<?php
		$course_id = isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : ' ';
		$course    = new Course( $course_id );
		if ( $course->details->enroll_type == 'passcode' ) {
			?>
			<label class="cp_popup_col_1">
				<input type="text" class="required" name="passcode" id="cp_popup_passcode" placeholder="<?php _e( 'Course Passcode', 'coursepress_base_td' ); ?>"/>
			</label>
		<?php } ?>
		<input type="hidden" value="<?php esc_attr_e( isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : ' ' ); ?>" name="course_id"/>
	<?php
	} else {
		_e( 'Registrations are not allowed.', 'coursepress_base_td' );
	}
	?>
	<div class="cp_popup_buttons">
		<?php wp_nonce_field( 'popup_signup_nonce', 'submit_signup_data' ); ?>
		<div class="validation_errors"></div>

		<label class="cp_popup_col_2">
			<a href="" class="cp_login_step" data-course-id="<?php esc_attr_e( isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : ' ' ); ?>"><?php _e( 'Already have an Account?', 'coursepress_base_td' ); ?></a>
		</label>

		<label class="cp_popup_col_2 second-child">
			<?php
			if ( cp_user_can_register() ) {
				$prereq = get_post_meta( $_POST['course_id'], 'prerequisite', true );
				if ( $prereq == 'false' ) {
					?>
					<button class="apply-button signup-data" data-course-id="<?php esc_attr_e( isset( $_POST['course_id'] ) ? (int) $_POST['course_id'] : ' ' ); ?>"><?php _e( 'Create Account', 'coursepress_base_td' ); ?></button>
				<?php
				}
			}
			?>
		</label>
	</div>

</form>