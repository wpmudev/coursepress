<?php

class CoursePress_View_Front_EnrollmentPopup {

	public static function init() {

		// For modal registration
		add_filter( 'coursepress_view_course', array( __CLASS__, 'add_backbone_registration_templates' ), 10, 3 );

	}

	public static function init_ajax() {
		add_action( 'wp_ajax_course_enrollment', array( __CLASS__, 'course_enrollment' ) );
		add_action( 'wp_ajax_nopriv_course_enrollment', array( __CLASS__, 'course_enrollment' ) );
	}


	public static function add_backbone_registration_templates( $content, $course_id, $context ) {

		if( 'main' === $context ) {

			$nonce = wp_create_nonce( 'coursepress_enrollment_action' );
			$modal_steps = apply_filters( 'coursepress_registration_modal', array(

				'container' => '
					<script type="text/template" id="modal-template">
					    <div class="enrollment-modal-container" data-nonce="' . $nonce . '" data-course="' . $course_id . '"></div>
					</script>
				',
				'step_1' => do_shortcode('
					<script type="text/template" id="modal-view1-template" data-type="modal-step" data-modal-action="signup">
						<div class="bbm-modal-nonce signup" data-nonce="' . wp_create_nonce( 'coursepress_enrollment_action_signup' ) . '"></div>
						<div class="bbm-modal__topbar">
							<h3 class="bbm-modal__title">' . esc_html__( 'Create new account', CoursePress::TD ) . '</h3>
						</div>
						<div class="bbm-modal__section">
							<div class="modal-nav-link">
							[course_signup_form login_link_id="step2" show_submit="no" ]
							</div>
						</div>
						<div class="bbm-modal__bottombar">
						<input type="submit" class="bbm-button done signup" value="' . esc_attr__( 'Create an account', CoursePress::TD ) . '" />
						</div>
					</script>
				'),
				'step_2' => do_shortcode('
					<script type="text/template" id="modal-view2-template" data-type="modal-step" data-modal-action="login">
						<div class="bbm-modal-nonce login" data-nonce="' . wp_create_nonce( 'coursepress_enrollment_action_login' ) . '"></div>
						<div class="bbm-modal__topbar">
							<h3 class="bbm-modal__title">' . esc_html__( 'Login to your account', CoursePress::TD ) . '</h3>
						</div>
						<div class="bbm-modal__section">
							<div class="modal-nav-link">
							[course_signup_form signup_link_id="step1" show_submit="no" page="login"]
							</div>
						</div>
						<div class="bbm-modal__bottombar">
						<input type="submit" class="bbm-button done" value="' . esc_attr__( 'Log in', CoursePress::TD ) . '" />
						</div>
					</script>
				'),
				'step_3' => '
					<script type="text/template" id="modal-view3-template" data-type="modal-step" data-modal-action="enrolled">
						<div class="bbm-modal__topbar">
							<h3 class="bbm-modal__title">' . esc_html__( 'Successfully enrolled.', CoursePress::TD ) . '</h3>
						</div>
						<div class="bbm-modal__section">
							<p>CONGRATS TEMPLATE WILL GO HERE</p>
							<a href="' . get_permalink( CoursePress_Helper_Utility::the_course( true ) ) . '">Start Learning</a>
						</div>
						<div class="bbm-modal__bottombar">
						<a href="#" class="bbm-button previous inactive">Previous</a>
						<a href="#" class="bbm-button next">Next</a>
						</div>
					</script>
				',
				'step_4' => '
					<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="login">
						<div class="bbm-modal__topbar">
							<h3 class="bbm-modal__title">Wizard example - step 4</h3>
						</div>
						<div class="bbm-modal__section">
							<p>STEP 4</p>
						</div>
						<div class="bbm-modal__bottombar">
						<a href="#" class="bbm-button previous inactive">Previous</a>
						<a href="#" class="bbm-button done">Done</a>
						</div>
					</script>
				',

			), $course_id );

			$content = implode( '', $modal_steps ) . $content;

		}

		return $content;

	}


	public static function course_enrollment() {

		$data      = json_decode( file_get_contents( 'php://input' ) );
		$step_data = $data->data;
		$json_data = array();
		$success   = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Enrolment: No action.', CoursePress::TD );
			wp_send_json_error( $json_data );
		}

		$action              = sanitize_text_field( $data->action );
		$json_data['action'] = $action;
		$json_data['last_step'] = (int) $step_data->step;

		switch ( $action ) {

			// Update Course
			case 'update_course':

				if ( isset( $step_data->step ) && wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {

					$step = (int) $step_data->step;

					$course_id = CoursePress_Model_Course::update( $step_data->course_id, $step_data );
					$json_data['course_id'] = $course_id;

					$next_step              = (int) $data->next_step;
					$json_data['last_step'] = $step;
					$json_data['next_step'] = $next_step;
					$json_data['redirect'] = $data->data->is_finished;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}

				break;

			case 'toggle_course_status':

				$course_id = $data->data->course_id;

				if ( wp_verify_nonce( $data->data->nonce, 'publish-course' ) ) {

					wp_update_post( array(
						'ID'          => $course_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-course' );
					$success            = true;

				}

				$json_data['course_id'] = $course_id;
				$json_data['state']     = $data->data->state;

				break;

			// Delete Instructor
			case 'delete_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Model_Course::remove_instructor( $data->data->course_id, $data->data->instructor_id );
					$json_data['instructor_id'] = $data->data->instructor_id;
					$json_data['course_id']     = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}

				break;

			// Add Instructor
			case 'add_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Model_Course::add_instructor( $data->data->course_id, $data->data->instructor_id );
					$user = get_userdata( $data->data->instructor_id );
					$json_data['instructor_id']   = $data->data->instructor_id;
					$json_data['instructor_name'] = $user->display_name;
					$json_data['course_id']       = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}

				break;

			// Invite Instructor
			case 'invite_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$email_data               = CoursePress_Helper_Utility::object_to_array( $data->data );
					$response                 = CoursePress_Model_Instructor::send_invitation( $email_data );
					$json_data['message']     = $response['message'];
					$json_data['data']        = $data->data;
					$json_data['invite_code'] = $response['invite_code'];

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = $response['success'];
				}
				break;

			// Delete Invite
			case 'delete_instructor_invite':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Model_Instructor::delete_invitation( $data->data->course_id, $data->data->invite_code );
					$json_data['course_id']   = $data->data->course_id;
					$json_data['invite_code'] = $data->data->invite_code;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}
				break;

			//case 'enroll_student':
			//
			//	if ( wp_verify_nonce( $data->data->nonce, 'add_student' ) ) {
			//		CoursePress_Model_Course::enroll_student( $data->data->student_id, $data->data->course_id );
			//		$json_data['student_id'] = $data->data->student_id;
			//		$json_data['course_id']  = $data->data->course_id;
			//
			//		$json_data['nonce'] = wp_create_nonce( 'add_student' );
			//		$success            = true;
			//	}
			//	break;

			case 'withdraw_student':
				if ( wp_verify_nonce( $data->data->nonce, 'withdraw-single-student' ) ) {
					CoursePress_Model_Course::withdraw_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id']  = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw-single-student' );
					$success            = true;
				}
				break;

			case 'withdraw_all_students':

				if ( wp_verify_nonce( $data->data->nonce, 'withdraw_all_students' ) ) {
					CoursePress_Model_Course::withdraw_all_students( $data->data->course_id );
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw_all_students' );
					$success            = true;
				}
				break;

			case 'invite_student':

				if ( wp_verify_nonce( $data->data->nonce, 'invite_student' ) ) {
					$email_data = CoursePress_Helper_Utility::object_to_array( $data->data );
					$response   = CoursePress_Model_Course::send_invitation( $email_data );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'invite_student' );
					$success            = $response;
				}
				break;

			case 'bulk_actions':

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					$courses = $data->data->courses;
					$action = $data->data->the_action;

					foreach( $courses as $course_id ) {
						switch ( $action ) {

							case 'publish':
								wp_update_post( array(
									'ID'          => $course_id,
									'post_status' => 'publish',
								) );
								break;
							case 'unpublish':
								wp_update_post( array(
									'ID'          => $course_id,
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
					$success            = true;
				}
				break;

			case 'delete_course':

				if ( wp_verify_nonce( $data->data->nonce, 'delete_course' ) ) {

					$course_id = (int) $data->data->course_id;
					wp_delete_post( $course_id );
					do_action( 'coursepress_course_deleted', $course_id );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'delete_course' );
					$success            = true;
				}

				break;

			case 'duplicate_course':

				if ( wp_verify_nonce( $data->data->nonce, 'duplicate_course' ) ) {

					$course_id = (int) $data->data->course_id;

					$the_course = get_post( $course_id );

					if( ! empty( $the_course ) ) {

						$the_course = CoursePress_Helper_Utility::object_to_array( $the_course );
						$the_course['post_author'] = get_current_user_id();
						$the_course['comment_count'] = 0;
						$the_course['post_title'] = $the_course['post_title'] . ' ' . __( 'Copy', CoursePress::TD );
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
						foreach( $course_meta as $key => $value ) {
							if( ! preg_match( '/^_/', $key ) ) {
								update_post_meta( $new_course_id, $key, maybe_unserialize( $value[0] ) );
							}
						}

						$course_data = CoursePress_Helper_Utility::object_to_array( CoursePress_Model_Course::get_units_with_modules( $course_id, array(
							'publish',
							'draft'
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
							foreach( $unit_meta as $key => $value ) {
								if( ! preg_match( '/^_/', $key ) ) {
									update_post_meta( $new_unit_id, $key, maybe_unserialize( $value[0] ) );
								}
							}

							$pages = isset( $unit_schema['pages'] ) ? $unit_schema['pages'] : array();
							foreach( $pages as $page ) {

								$modules = $page['modules'];
								foreach( $modules as $module_id => $module ) {


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
									foreach( $module_meta as $key => $value ) {
										if( ! preg_match( '/^_/', $key ) ) {
											update_post_meta( $new_module_id, $key, maybe_unserialize( $value[0] ) );
										}
									}

								}

							}

						}

						$json_data['course_id'] = $new_course_id;

						do_action( 'coursepress_course_duplicated', $new_course_id, $course_id );

						$json_data['data'] = $data->data;

						$json_data['nonce'] = wp_create_nonce( 'duplicate_course' );
						$success            = true;
					}
				}

				break;

			case 'signup':

				if( wp_verify_nonce( $data->data->nonce, 'coursepress_enrollment_action_signup' ) ) {
					$nonce = wp_create_nonce( 'coursepress_enrollment_action' );
				} else {
					$json_data['message'] = __( 'Enrolment: Invalid request. Please try reloading the page.', CoursePress::TD );
					wp_send_json_error( $json_data );
					return;
				}
				$json_data['nonce'] = $nonce;

				$username = sanitize_text_field( $data->data->username );
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
					'logged_in' => false
				);

				$user_id = username_exists( $username );
				if( ! empty( $user_id ) ) {
					$signup_errors[] = __( 'Username already taken.', CoursePress::TD );
				}
				$email_exists = email_exists( $email );
				if( $email_exists ) {
					$signup_errors[] = __( 'E-mail address already used.', CoursePress::TD );
				}

				if ( ! $user_id && ! $email_exists ) {
					$user_id = wp_create_user( $username, $password, $email );

					if( ! empty( $user_id ) ) {
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
					$enrolled = CoursePress_Model_Course::student_enrolled( $user->ID, $course_id );
					$json_data['user_data'] = array( 'ID' => $user->ID );
					$json_data['already_enrolled'] = ! empty( $enrolled );
				} else {
					$json_data['logged_in'] = false;
				}

				//handle_signup_return
				$json_data['callback'] = 'handle_login_return';
				$json_data['success'] = true;
				$success = isset( $json_data['success'] ) ? $json_data['success'] : false;
				break;

			case 'enroll_student':

				$student_id = (int) $data->data->student_id;
				$course_id = (int) $data->data->course_id;

				if( ! empty( $student_id ) && ! empty( $course_id ) && true === CoursePress_Model_Course::enroll_student( $student_id, $course_id ) ) {
					$json_data['student_id'] = $student_id;
					$json_data['course_id'] = $course_id;
					$json_data['success'] = true;
				} else {
					$json_data['error_message'] = __( 'Could not enroll at this time.', CoursePress::TD );
					$json_data['success'] = false;
				}

				$json_data['callback'] = 'handle_enroll_student_return';

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