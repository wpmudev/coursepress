<?php
/**
 * The template use for CoursePress custom registration form.
 *
 * @since 2.0.5
 **/
?>
<div class="coursepress-form coursepress-form-signup">
	<?php if ( ! empty( $signup_title ) ) : ?>
		<?php printf( '<%1$s>%2$s</%1$s>', $signup_tag, $signup_title ); ?>
	<?php endif; ?>
	<p class="form-info-<?php echo $form_message_class; ?>"><?php echo $form_message; ?></p>

	<form id="student-settings" name="student-settings" method="post" class="student-settings signup-form">
		<?php
		/**
		 * Trigger before the signup form.
		 **/
		do_action( 'coursepress_before_signup_form' );

		/**
		 * Trigger before signup fields are printed.
		 **/
		do_action( 'coursepress_before_all_signup_fields' );
		?>
		<input type="hidden" name="course_id" value="<?php echo $course_id; ?>"/>
		<input type="hidden" name="redirect_url" value="<?php echo esc_url( $redirect_url ); ?>"/>
		<label class="firstname">
			<span><?php _e( 'First Name', 'coursepress' ); ?>:</span>
			<input type="text" name="first_name" value="<?php echo esc_attr( $first_name ); ?>"/>
		</label>
		<?php
		/**
		 * Trigger after first_name field.
		 **/
		do_action( 'coursepress_after_signup_first_name' );
		?>

		<label class="lastname">
			<span><?php _e( 'Last Name', 'coursepress' ); ?>:</span>
			<input type="text" name="last_name" value="<?php echo esc_attr( $last_name ); ?>"/>
		</label>
		<?php
		/**
		 * Trigger after last_name field.
		 **/
		do_action( 'coursepress_after_signup_last_name' );
		?>

		<label class="username">
			<span><?php _e( 'Username', 'coursepress' ); ?>:</span>
			<input type="text" name="username" value="<?php echo esc_attr( $username ); ?>" />
		</label>
		<?php
		/**
		 * Trigger after printing username.
		 **/
		do_action( 'coursepress_after_signup_username' );
		?>

		<label class="email">
			<span><?php _e( 'E-mail', 'coursepress' ); ?>:</span>
			<input type="text" name="email" value="<?php echo esc_attr( $email ); ?>" />
		</label>
		<?php
		/**
		 * Trigger after email field.
		 **/
		do_action( 'coursepress_after_signup_email' );
		?>

		<label class="password">
			<span><?php _e( 'Password', 'coursepress' ); ?>:</span>
			<input type="password" name="password" value=""/>
		</label>
		<?php
		/**
		 * Trigger after password field.
		 **/
		do_action( 'coursepress_after_signup_password' );
		?>

		<label class="password-confirm right">
			<span><?php _e( 'Confirm Password', 'coursepress' ); ?>:</span>
			<input type="password" name="password_confirmation" value=""/>
		</label>
		<label class="weak-password-confirm">
			<input type="checkbox" name="confirm_weak_password" value="1" />
			<span><?php _e( 'Confirm use of weak password', 'coursepress' ); ?></span>
		</label>

		<?php if ( shortcode_exists( 'signup-tos' ) && '1' == get_option( 'show_tos', 0 ) ) : ?>
			<label class="tos full">
				<?php echo do_shortcode( '[signup-tos]' ); ?>
			</label>
		<?php endif; ?>

		<?php
		/**
		 * Trigger after all signup fields are rendered.
		 **/
		do_action( 'coursepress_after_all_signup_fields' );
		?>

		<label class="existing-link full">
			<?php printf( __( 'Already have an account? %s%s%s!', 'coursepress' ), '<a href="' . esc_url( $login_url ) . '">', __( 'Login to your account', 'coursepress' ), '</a>' ); ?>
		</label>
		<label class="submit-link full-right">
			<input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="<?php esc_attr_e( 'Create an Account', 'coursepress' ); ?>" />
		</label>

		<?php
		/**
		 * Trigger when registration form submitted.
		 **/
		do_action( 'coursepress_after_submit' );

		wp_nonce_field( 'student_signup', '_wpnonce', true );

		/**
		 * Trigger after all signform fields are printed.
		 **/
		do_action( 'coursepress_after_signup_form' );
		?>
	</form>
</div>
