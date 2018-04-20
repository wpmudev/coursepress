<?php
/**
 * Front-end View.
 *
 * @package CoursePress
 */

/**
 * Handles the enrollment popup and ajax commands.
 */
class CoursePress_View_Front_EnrollmentPopup {

	/**
	 * Initialize the Registration popup.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_action(
			'after_setup_theme',
			array( __CLASS__, 'add_hooks' ),
			999
		);
	}

	/**
	 * Function runs after the theme was loaded.
	 * We check, if the theme supports CoursePress, and add different hooks
	 * depending on that condition.
	 *
	 * @since 2.0.0
	 */
	public static function add_hooks() {
		if ( get_theme_support( 'coursepress' ) ) {
			add_filter(
				'wp_footer',
				array( __CLASS__, 'add_backbone_registration_templates_footer' )
			);
		} else {
			add_filter(
				'coursepress_view_course',
				array( __CLASS__, 'add_backbone_registration_templates_vp' ),
				10, 3
			);
		}
	}

	/**
	 * Hook up the ajax handlers.
	 *
	 * @since  2.0.0
	 */
	public static function init_admin() {
		add_action(
			'wp_ajax_course_enrollment',
			array( __CLASS__, 'course_enrollment' )
		);

		add_action(
			'wp_ajax_nopriv_course_enrollment',
			array( __CLASS__, 'course_enrollment' )
		);
	}

	/**
	 * Adds the backbone code for registration popup to the page contents of a
	 * VirtualPage.
	 *
	 * @since  2.0.0
	 * @param  string $content Contents of the virtual page.
	 * @param  int    $course_id The course ID.
	 * @param  string $context Context.
	 * @return string The modified page contents.
	 */
	public static function add_backbone_registration_templates_vp( $content, $course_id, $context ) {
		if ( 'main' == $context ) {
			$scode = sprintf(
				'[coursepress_enrollment_templates course_id="%d"]',
				$course_id
			);
			$modal_content = do_shortcode( $scode );
			return $modal_content . $content;
		}

		return $content;
	}

	/**
	 * Adds the backbone code for registration popup to the page footer.
	 *
	 * @since 2.0.0
	 * @return string Modified page contents.
	 */
	public static function add_backbone_registration_templates_footer() {
		$scode = sprintf(
			'[coursepress_enrollment_templates course_id="%d"]',
			CoursePress_Helper_Utility::the_course( true )
		);

		echo do_shortcode( $scode );
	}

	/**
	 * Handle the Ajax requests.
	 *
	 * @since  2.0.0
	 */
	public static function course_enrollment() {
		$data = json_decode( file_get_contents( 'php://input' ) );
		$step_data = $data->data;
		$json_data = array();
		$success = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Enrolment: No action.', 'coursepress' );
			wp_send_json_error( $json_data );
		}

		$action = sanitize_text_field( $data->action );
		$json_data['action'] = $action;
		$json_data['last_step'] = (int) $step_data->step;

		switch ( $action ) {
			/**
			 * Get a new wp_nonce instance.
			 **/
			case 'get_nonce':
				$json_data['nonce'] = wp_create_nonce( $data->data->nonce );
				$json_data['success'] = true;
				$success = true;
				break;

			// Update Course
			case 'update_course':
				if ( isset( $step_data->step ) && wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {

					$step = (int) $step_data->step;

					$course_id = CoursePress_Data_Course::update( $step_data->course_id, $step_data );
					$json_data['course_id'] = $course_id;

					$next_step = (int) $data->next_step;
					$json_data['last_step'] = $step;
					$json_data['next_step'] = $next_step;
					$json_data['redirect'] = $data->data->is_finished;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			case 'toggle_course_status':
				$course_id = $data->data->course_id;

				if ( wp_verify_nonce( $data->data->nonce, 'publish-course' ) ) {
					wp_update_post( array(
						'ID' => $course_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-course' );
					$success = true;

				}

				$json_data['course_id'] = $course_id;
				$json_data['state'] = $data->data->state;
				break;

			// Delete Instructor
			case 'delete_instructor':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Course::remove_instructor( $data->data->course_id, $data->data->instructor_id );
					$json_data['instructor_id'] = $data->data->instructor_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			// Add Instructor
			case 'add_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'coursepress_add_instructor' ) ) {
					CoursePress_Data_Course::add_instructor( $data->data->course_id, $data->data->instructor_id );
					$user = get_userdata( $data->data->instructor_id );
					$json_data['instructor_id'] = $data->data->instructor_id;
					$json_data['instructor_name'] = $user->display_name;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;

					// Remove instructor invitation from the list
					if ( ! empty( $data->data->invite_code ) ) {
						$success = $json_data['success'] = CoursePress_Data_Instructor::add_from_invitation( $data->data->course_id, $data->data->instructor_id, $data->data->invite_code );
					}
				}
				break;

			// Invite Instructor
			case 'invite_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$response = CoursePress_Data_Instructor::send_invitation(
						(int) $data->data->course_id,
						$data->data->email,
						$data->data->first_name,
						$data->data->last_name
					);
					$json_data['message'] = $response['message'];
					$json_data['data'] = $data->data;
					$json_data['invite_code'] = $response['invite_code'];

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = $response['success'];
				}
				break;

			// Delete Invite
			case 'delete_instructor_invite':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Instructor::delete_invitation( $data->data->course_id, $data->data->invite_code );
					$json_data['course_id'] = $data->data->course_id;
					$json_data['invite_code'] = $data->data->invite_code;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			case 'withdraw_student':
				if ( wp_verify_nonce( $data->data->nonce, 'withdraw-single-student' ) ) {
					CoursePress_Data_Course::withdraw_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw-single-student' );
					$success = true;
				}
				break;

			case 'withdraw_all_students':
				if ( wp_verify_nonce( $data->data->nonce, 'withdraw_all_students' ) ) {
					CoursePress_Data_Course::withdraw_all_students( $data->data->course_id );
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw_all_students' );
					$success = true;
				}
				break;

			case 'invite_student':
				if ( wp_verify_nonce( $data->data->nonce, 'invite_student' ) ) {
					$email_data = CoursePress_Helper_Utility::object_to_array( $data->data );
					$response = CoursePress_Data_Course::send_invitation( $email_data );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'invite_student' );
					$success = $response;
				}
				break;

			case 'bulk_actions':
				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {
					$courses = $data->data->courses;
					$action = $data->data->the_action;

					foreach ( $courses as $course_id ) {

						switch ( $action ) {
							case 'publish':
								wp_update_post( array(
									'ID' => $course_id,
									'post_status' => 'publish',
								) );
								break;

							case 'unpublish':
								wp_update_post( array(
									'ID' => $course_id,
									'post_status' => 'draft',
								) );
								break;

							case 'delete':
								wp_delete_post( $course_id );
								do_action( 'coursepress_course_deleted', $course_id );
								break;
						}
					}

					$json_data['data'] = $data->data;
					$json_data['nonce'] = wp_create_nonce( 'bulk_action_nonce' );
					$success = true;
				}
				break;

			case 'delete_course':
				if ( wp_verify_nonce( $data->data->nonce, 'delete_course' ) ) {
					$course_id = (int) $data->data->course_id;
					wp_delete_post( $course_id );
					do_action( 'coursepress_course_deleted', $course_id );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'delete_course' );
					$success = true;
				}
				break;

			case 'duplicate_course':
				if ( wp_verify_nonce( $data->data->nonce, 'duplicate_course' ) ) {
					$course_id = (int) $data->data->course_id;
					$the_course = get_post( $course_id );

					if ( ! empty( $the_course ) ) {
						$the_course = CoursePress_Helper_Utility::object_to_array( $the_course );
						$the_course['post_author'] = get_current_user_id();
						$the_course['comment_count'] = 0;
						$the_course['post_title'] = $the_course['post_title'] . ' ' . __( 'Copy', 'coursepress' );
						$the_course['post_status'] = 'draft';
						unset( $the_course['ID'] );
						unset( $the_course['post_date'] );
						unset( $the_course['post_date_gmt'] );
						unset( $the_course['post_name'] );
						unset( $the_course['post_modified'] );
						unset( $the_course['post_modified_gmt'] );
						unset( $the_course['guid'] );

						$new_course_id = wp_insert_post( $the_course );

						$course_meta = get_post_meta( $course_id );
						foreach ( $course_meta as $key => $value ) {
							if ( ! preg_match( '/^_/', $key ) ) {
								$success = add_post_meta( $new_course_id, $key, maybe_unserialize( $value[0] ), true );
								if ( ! $success ) {
									update_post_meta( $new_course_id, $key, maybe_unserialize( $value[0] ) );
								}
							}
						}

						$course_data = CoursePress_Helper_Utility::object_to_array( CoursePress_Data_Course::get_units_with_modules( $course_id, array(
							'publish',
							'draft',
						) ) );
						$course_data = CoursePress_Helper_Utility::sort_on_key( $course_data, 'order' );

						foreach ( $course_data as $unit_id => $unit_schema ) {

							$unit = $unit_schema['unit'];
							// Set Fields
							$unit['post_author'] = get_current_user_id();
							$unit['post_parent'] = $new_course_id;
							$unit['comment_count'] = 0;
							$unit['post_status'] = 'draft';
							unset( $unit['ID'] );
							unset( $unit['post_date'] );
							unset( $unit['post_date_gmt'] );
							unset( $unit['post_name'] );
							unset( $unit['post_modified'] );
							unset( $unit['post_modified_gmt'] );
							unset( $unit['guid'] );

							$new_unit_id = wp_insert_post( $unit );
							$unit_meta = get_post_meta( $unit_id );
							foreach ( $unit_meta as $key => $value ) {
								if ( ! preg_match( '/^_/', $key ) ) {
									$success = add_post_meta( $new_unit_id, $key, maybe_unserialize( $value[0] ), true );
									if ( ! $success ) {
										update_post_meta( $new_unit_id, $key, maybe_unserialize( $value[0] ) );
									}
								}
							}

							$pages = isset( $unit_schema['pages'] ) ? $unit_schema['pages'] : array();
							foreach ( $pages as $page ) {

								$modules = $page['modules'];
								foreach ( $modules as $module_id => $module ) {

									$module['post_author'] = get_current_user_id();
									$module['post_parent'] = $new_unit_id;
									$module['comment_count'] = 0;
									unset( $module['ID'] );
									unset( $module['post_date'] );
									unset( $module['post_date_gmt'] );
									unset( $module['post_name'] );
									unset( $module['post_modified'] );
									unset( $module['post_modified_gmt'] );
									unset( $module['guid'] );

									$new_module_id = wp_insert_post( $module );

									$module_meta = get_post_meta( $module_id );
									foreach ( $module_meta as $key => $value ) {
										if ( ! preg_match( '/^_/', $key ) ) {
											$success = add_post_meta( $new_module_id, $key, maybe_unserialize( $value[0] ), true );
											if ( ! $success ) {
												update_post_meta( $new_module_id, $key, maybe_unserialize( $value[0] ) );
											}
										}
									}
								}
							}
						}
						$json_data['course_id'] = $new_course_id;
						do_action( 'coursepress_course_duplicated', $new_course_id, $course_id );
						$json_data['data'] = $data->data;
						$json_data['nonce'] = wp_create_nonce( 'duplicate_course' );
						$success = true;
					}
				}
				break;

			case 'signup':
				if ( wp_verify_nonce( $data->data->nonce, 'coursepress_enrollment_action_signup' ) ) {
					$nonce = wp_create_nonce( 'coursepress_enrollment_action' );
				} else {
					$json_data['message'] = __( 'Enrolment: Invalid request. Please try reloading the page.', 'coursepress' );
					wp_send_json_error( $json_data );
					return;
				}
				$json_data['nonce'] = $nonce;

				$username = sanitize_user( $data->data->username );
				$first_name = sanitize_text_field( $data->data->first_name );
				$last_name = sanitize_text_field( $data->data->last_name );
				$email = sanitize_email( $data->data->email );
				$password = sanitize_text_field( $data->data->password );

				$signup_errors = array();
				$user_data = array(
					'username' => $username,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'password' => $password,
					'ID' => 0,
					'logged_in' => false,
				);

				$registration_data_are_valid = true;

				/**
				 * check user name
				 */
				if ( empty( $username ) ) {
					$signup_errors[] = __( 'Username can not be empty.', 'coursepress' );
					$registration_data_are_valid = false;
				} elseif ( ! validate_username( $username ) ) {
					$signup_errors[] = __( 'Invalid username. Please choose another one.', 'coursepress' );
					$registration_data_are_valid = false;
				} else {
					$user_id = username_exists( $username );
					if ( ! empty( $user_id ) ) {
						$signup_errors[] = __( 'Username already exists. Please choose another one.', 'coursepress' );
						$registration_data_are_valid = false;
					}
				}

				/**
				 * check email
				 */
				if ( ! is_email( $email ) ) {
					$signup_errors[] = __( 'E-mail address is not valid.', 'coursepress' );
					$registration_data_are_valid = false;
				} else {
					$email_exists = email_exists( $email );
					if ( $email_exists ) {
						$signup_errors[] = __( 'E-mail address already used.', 'coursepress' );
						$registration_data_are_valid = false;
					}
				}

				if ( $registration_data_are_valid ) {
					$user_id = wp_create_user( $username, $password, $email );

					if ( ! empty( $user_id ) ) {
						update_user_meta( $user_id, 'first_name', $first_name );
						update_user_meta( $user_id, 'last_name', $last_name );
						$user_data['ID'] = $user_id;

						$creds = array();
						$creds['user_login'] = $username;
						$creds['user_password'] = $password;
						$creds['remember'] = true;
						$user = wp_signon( $creds, false );
						if ( ! is_wp_error( $user ) ) {
							$user_data['logged_in'] = true;
						}
					}
				}

				$json_data['user_data'] = $user_data;
				$json_data['signup_errors'] = $signup_errors;
				$json_data['callback'] = 'handle_signup_return';
				$json_data['success'] = true;
				$success = isset( $json_data['success'] ) ? $json_data['success'] : false;
				break;

			case 'login':
				$nonce = wp_create_nonce( 'coursepress_enrollment_action' );
				$json_data['nonce'] = $nonce;
				$json_data['signup_errors'] = array();

				$username = sanitize_text_field( $data->data->username );
				$password = sanitize_text_field( $data->data->password );
				$course_id = (int) $data->data->course_id;

				$creds = array();
				$creds['user_login'] = $username;
				$creds['user_password'] = $password;
				$creds['remember'] = true;
				$user = wp_signon( $creds, false );
				if ( ! is_wp_error( $user ) ) {
					$json_data['logged_in'] = true;
					$enrolled = CoursePress_Data_Course::student_enrolled( $user->ID, $course_id );
					$json_data['user_data'] = array( 'ID' => $user->ID );
					$json_data['already_enrolled'] = ! empty( $enrolled );
				} else {
					$json_data['logged_in'] = false;
					foreach ( $user->errors as $key => $message_array ) {
						$json_data['signup_errors'][] = implode( ' ', $message_array );
					}
				}

				// handle_signup_return
				$json_data['callback'] = 'handle_login_return';
				$json_data['success'] = true;
				$success = isset( $json_data['success'] ) ? $json_data['success'] : false;
				break;

			case 'enroll_student':
				$student_id = (int) $data->data->student_id;
				$course_id = (int) $data->data->course_id;
				$type = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type', 'manually' );

				if ( 'passcode' == $type ) {
					$json_data['success'] = false;
				} else if ( ! empty( $student_id ) && ! empty( $course_id ) && true === CoursePress_Data_Course::enroll_student( $student_id, $course_id ) ) {
					$json_data['student_id'] = $student_id;
					$json_data['course_id'] = $course_id;
					$json_data['success'] = true;
				} else {
					$json_data['error_message'] = __( 'Could not enroll at this time.', 'coursepress' );
					$json_data['success'] = false;
				}

				$json_data['callback'] = 'handle_enroll_student_return';

				$success = isset( $json_data['success'] ) ? $json_data['success'] : false;
				break;
			case 'enroll_with_passcode':
				$passcode = $data->data->passcode;
				$student_id = $data->data->student_id;
				$course_id = $data->data->course_id;
				// Verify passcode
				$course_passcode = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_passcode' );

				if ( $course_passcode != $passcode ) {
					$json_data['success'] = false;
					$json_data['message'] = __( 'Invalid PASSCODE!', 'coursepress' );
				} else {
					CoursePress_Data_Course::enroll_student( $student_id, $course_id );
					$json_data['success'] = true;
				}
				$success = isset( $json_data['success'] ) ? $json_data['success'] : false;
				break;

			default:
				// Custom actions may be handled (e.g. extending the registration process) but must return an array to send for return values.
				// Array must return a success key with a value of true or false.
				$json_data = array_merge( apply_filters( 'coursepress_popup_enrollment', array( 'success' => true ), $action, $step_data ), $json_data );
				$json_data['callback'] = 'awesome';
				$success = isset( $json_data['success'] ) ? $json_data['success'] : false;
				break;
		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}
	}
}
