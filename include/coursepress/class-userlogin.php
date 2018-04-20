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
		static $form_message = '';

		/**
		 * Form class to render on registration form.
		 *
		 * @var (string)
		 **/
		static $form_message_class = '';

		/**
		 * Process user registration submission.
		 **/
		public static function process_registration_form() {
			if ( isset( $_POST['student-settings-submit'] ) && isset( $_POST['_wpnonce'] )
				&& wp_verify_nonce( $_POST['_wpnonce'], 'student_signup' ) ) {

				check_admin_referer( 'student_signup' );

				/**
				 * Trigger before validating registration form
				 **/
				do_action( 'coursepress_before_signup_validation' );

				$min_password_length = CoursePress_Helper_Utility::get_minimum_password_length();
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
						self::$form_message = __( 'Username already exists. Please choose another one.', 'coursepress' );
						$found_errors++;
					} elseif ( ! validate_username( $username ) ) {
						self::$form_message = __( 'Invalid username!', 'coursepress' );
						$found_errors++;
					} elseif ( ! is_email( $email ) ) {
						self::$form_message = __( 'E-mail address is not valid.', 'coursepress' );
						$found_errors++;
					} elseif ( email_exists( $email ) ) {
						self::$form_message = __( 'Sorry, that email address is already used!', 'coursepress' );
						$found_errors++;
					} elseif ( $passwd != $passwd2 ) {
						self::$form_message = __( 'Passwords don\'t match', 'coursepress' );
						$found_errors++;
					} elseif ( ! CoursePress_Helper_Utility::is_password_strong() ) {
						if ( CoursePress_Helper_Utility::is_password_strength_meter_enabled() ) {
							self::$form_message = __( 'Your password is too weak.', 'coursepress' );
						} else {
							self::$form_message = sprintf( __( 'Your password must be at least %d characters long and have at least one letter and one number in it.', 'coursepress' ), $min_password_length );
						}
						$found_errors++;
					} elseif ( isset( $_POST['tos_agree'] ) && ! cp_is_true( $_POST['tos_agree'] ) ) {
						self::$form_message = __( 'You must agree to the Terms of Service in order to signup.', 'coursepress' );
						$found_errors++;
					}
				} else {
					self::$form_message = __( 'All fields are required.', 'coursepress' );
					$found_errors++;
				}

				if ( $found_errors > 0 ) {
					self::$form_message_class = 'red';
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

					$student_data = CoursePress_Helper_Utility::sanitize_recursive( $student_data );
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
							self::$form_message = $user->get_error_message();
							self::$form_message_class = 'red';
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
						self::$form_message = __( 'An error occurred while creating the account. Please check the form and try again.', 'coursepress' );
						self::$form_message_class = 'red';
					}
				}
			}
		}

		/**
		 * Render registration form if current user is not logged-in.
		 *
		 * @param (string) $redirect_url
		 * @param (string) $login_url
		 * @param (string) $signup_title
		 * @param (string) $signup_tag
		 *
		 * @return Returns registration form or null.
		 **/
		public static function get_registration_form( $redirect_url = '', $login_url = '', $signup_title = '', $signup_tag = '' ) {
			if ( is_user_logged_in() ) {
				return '';
			}

			ob_start();

			/**
			 * Allow $form_message_class to be filtered before applying.
			 *
			 * @param (string) $form_message_class
			 **/
			self::$form_message_class = apply_filters( 'signup_form_message_class', self::$form_message_class );

			/**
			 * Allow form message to be filtered before rendering.
			 *
			 * @param (string) $form_message
			 **/
			self::$form_message = apply_filters( 'signup_form_message', self::$form_message );

			$args = array(
				'signup_title' => $signup_title,
				'signup_tag' => $signup_tag,
				'form_message' => self::$form_message,
				'form_message_class' => self::$form_message_class,
				'course_id' => isset( $_GET['course_id'] ) ? (int) $_GET['course_id'] : 0,
				'redirect_url' => $redirect_url,
				'login_url' => $login_url,
				'first_name' => isset( $_POST['first_name'] ) ? $_POST['first_name'] : '',
				'last_name' => isset( $_POST['last_name'] ) ? $_POST['last_name'] : '',
				'username' => isset( $_POST['username'] ) ? $_POST['username'] : '',
				'email' => isset( $_POST['email'] ) ? $_POST['email'] : '',
			);

			self::render( 'include/coursepress/view/registration-form', $args );

			$content = ob_get_clean();
			$content = preg_replace( '%\\r\\n|\\n%', '', $content );

			return $content;
		}
	}
endif;
