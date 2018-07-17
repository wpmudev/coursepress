<?php
/**
 * The class use to process user registration and login.
 *
 * @class CoursePress_UserLogin
 * @version 2.0.5
 **/
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'CoursePress_UserLogin' ) ) :

	class CoursePress_UserLogin extends CoursePress_Utility {

		/**
		 * Warning or error message to display on registration form.
		 *
		 * @var (string)
		 **/
		var $form_message = '';

		/**
		 * Form class to render on registration form.
		 *
		 * @var (string)
		 **/
		var $form_message_class = '';

		/**
		 * Process user registration submission.
		 **/
		public function process_registration_form() {
			if ( isset( $_POST['student-settings-submit'] ) && isset( $_POST['_wpnonce'] )
				&& wp_verify_nonce( $_POST['_wpnonce'], 'coursepress_nonce' ) ) {

				check_admin_referer( 'coursepress_nonce' );

				/**
				 * Trigger before validating registration form
				 **/
				do_action( 'coursepress_before_signup_validation' );

				$min_password_length = $this->get_minimum_password_length();
				$username = $_POST['username'];
				$firstname = $_POST['first_name'];
				$lastname = $_POST['last_name'];
				$email = $_POST['email'];
				$passwd = $_POST['password'];
				$passwd2 = $_POST['password_confirmation'];
				$redirect_url = $_POST['redirect_url'];
				$found_errors = 0;

				if ( $username && $firstname && $lastname && $email && $passwd && $passwd2 ) {
					if ( username_exists( $username ) ) {
						$this->form_message = __( 'Username already exists. Please choose another one.', 'cp' );
						$found_errors++;
					} elseif ( ! validate_username( $username ) ) {
						$this->form_message = __( 'Invalid username!', 'cp' );
						$found_errors++;
					} elseif ( ! is_email( $email ) ) {
						$this->form_message = __( 'E-mail address is not valid.', 'cp' );
						$found_errors++;
					} elseif ( email_exists( $email ) ) {
						$this->form_message = __( 'Sorry, that email address is already used!', 'cp' );
						$found_errors++;
					} elseif ( $passwd != $passwd2 ) {
						$this->form_message = __( 'Passwords don\'t match', 'cp' );
						$found_errors++;
					} elseif ( ! $this->is_password_strong() ) {
						if ( $this->is_password_strength_meter_enabled() ) {
							$this->form_message = __( 'Your password is too weak.', 'cp' );
						} else {
							$this->form_message = sprintf( __( 'Your password must be at least %d characters long and have at least one letter and one number in it.', 'cp' ), $min_password_length );
						}
						$found_errors++;
					} elseif ( isset( $_POST['tos_agree'] ) && ! coursepress_is_true( $_POST['tos_agree'] ) ) {
						$this->form_message = __( 'You must agree to the Terms of Service in order to signup.', 'cp' );
						$found_errors++;
					}
				} else {
					$this->form_message = __( 'All fields are required.', 'cp' );
					$found_errors++;
				}

				if ( $found_errors > 0 ) {
					$this->form_message_class = 'red';
				} else {
					// Register new user
					$student_data = array(
						'default_role' => get_option( 'default_role', 'subscriber' ),
						'user_login' => $username,
						'user_email' => $email,
						'first_name' => $firstname,
						'last_name' => $lastname,
						'user_pass' => $passwd,
						'password_txt' => $passwd,
					);

					$student_data = $this->sanitize_recursive( $student_data );
					$student_id = wp_insert_user( $student_data );

					if ( ! empty( $student_id ) ) {
						// Send registration email
						CoursePress_Data_Student::send_registration( $student_id, $student_data );

						$creds = array(
							'user_login' => $username,
							'user_password' => $passwd,
							'remember' => true,
						);
						$user = wp_signon( $creds, false );

						if ( is_wp_error( $user ) ) {
							$this->form_message = $user->get_error_message();
							$this->form_message_class = 'red';
						}

						if ( ! empty( $_POST['course_id'] ) ) {
							$url = get_permalink( (int) $_POST['course_id'] );
							wp_safe_redirect( $url );
						} else {
							if ( ! empty( $redirect_url ) ) {
								wp_safe_redirect( esc_url_raw( apply_filters( 'coursepress_redirect_after_signup_redirect_url', $redirect_url ) ) );
							} else {
								wp_safe_redirect( esc_url_raw( apply_filters( 'coursepress_redirect_after_signup_url', CoursePress_Core::get_slug( 'student_dashboard', true ) ) ) );
							}
						}
						exit;
					} else {
						$this->form_message = __( 'An error occurred while creating the account. Please check the form and try again.', 'cp' );
						$this->form_message_class = 'red';
					}
				}
			}
		}
	}
endif;
