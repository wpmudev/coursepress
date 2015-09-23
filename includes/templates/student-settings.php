<?php if ( is_user_logged_in() ) { ?>
	<?php
	$form_message_class = '';
	$form_message       = '';

	if ( isset( $_POST['student-settings-submit'] ) ) {

		if ( ! isset( $_POST['student_settings_nonce'] ) || ! wp_verify_nonce( $_POST['student_settings_nonce'], 'student_settings_save' )
		) {
			_e( "Changed can't be saved because nonce didn't verify.", 'cp' );
		} else {
			$student_data       = array();
			$student_data['ID'] = get_current_user_id();
			$form_errors        = 0;

			do_action( 'coursepress_before_settings_validation' );

			if ( $_POST['password'] != '' ) {
				if ( $_POST['password'] == $_POST['password_confirmation'] ) {
					$student_data['user_pass'] = $_POST['password'];
				} else {
					$form_message       = __( "Passwords don't match", 'cp' );
					$form_message_class = 'red';
					$form_errors ++;
				}
			}

			$student_data['user_email'] = $_POST['email'];
			$student_data['first_name'] = $_POST['first_name'];
			$student_data['last_name']  = $_POST['last_name'];

			if ( ! is_email( $_POST['email'] ) ) {
				$form_message       = __( 'E-mail address is not valid.', 'cp' );
				$form_message_class = 'red';
				$form_errors ++;
			}

			if ( $form_errors == 0 ) {
				$student = new Student( get_current_user_id() );
				if ( $student->update_student_data( $student_data ) ) {
					$form_message       = __( 'Profile has been updated successfully.', 'cp' );
					$form_message_class = 'regular';
				} else {
					$form_message       = __( 'An error occured while updating. Please check the form and try again.', 'cp' );
					$form_message_class = 'red';
				}
			}
		}
	}
	$student = new Student( get_current_user_id() );
	?>
	<p class="<?php echo esc_attr( 'form-info-' . $form_message_class ); ?>"><?php echo esc_html( $form_message ); ?></p>
	<?php do_action( 'coursepress_before_settings_form' ); ?>
	<form id="student-settings" name="student-settings" method="post" class="student-settings">
	<?php wp_nonce_field( 'student_settings_save', 'student_settings_nonce' ); ?>
	<label>
		<?php _e( 'First Name', 'cp' ); ?>:
		<input type="text" name="first_name" value="<?php esc_attr_e( $student->user_firstname ); ?>"/>
	</label>

	<?php do_action( 'coursepress_after_settings_first_name' ); ?>

	<label>
		<?php _e( 'Last Name', 'cp' ); ?>:
		<input type="text" name="last_name" value="<?php esc_attr_e( $student->user_lastname ); ?>"/>
	</label>

	<?php do_action( 'coursepress_after_settings_last_name' ); ?>

	<label>
		<?php _e( 'E-mail', 'cp' ); ?>:
		<input type="text" name="email" value="<?php esc_attr_e( $student->user_email ); ?>"/>
	</label>

	<?php do_action( 'coursepress_after_settings_email' ); ?>

	<label>
		<?php _e( 'Username', 'cp' ); ?>:
		<input type="text" name="username" value="<?php esc_attr_e( $student->user_login ); ?>" disabled="disabled"/>
	</label>

	<?php do_action( 'coursepress_after_settings_username' ); ?>

	<label>
		<?php _e( 'Password', 'cp' ); ?>:
		<input type="password" name="password" value="" placeholder="<?php _e( "Won't change if empty.", 'cp' ); ?>"/>
	</label>

	<?php do_action( 'coursepress_after_settings_passwordon' ); ?>

	<label>
		<?php _e( 'Confirm Password', 'cp' ); ?>:
		<input type="password" name="password_confirmation" value=""/>
	</label>

	<?php do_action( 'coursepress_after_settings_pasword' ); ?>

	<label class="full">
		<input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php _e( 'Save Changes', 'cp' ); ?>"/>
	</label>
	</form><?php do_action( 'coursepress_after_settings_form' ); ?>
<?php
} else {
	// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
	wp_redirect( get_option( 'use_custom_login_form', 1 ) ? CoursePress::instance()->get_signup_slug( true ) : wp_login_url() );
	exit;
}
?>