<?php
/**
 * Admin course
 **/
class CoursePress_Admin_Controller_Course {
	/**
	 * Delete a course and it's units and modules
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param (int) $course_id		The course ID to delete.
	 **/
	public static function delete_course( $course_id ) {
		global $wpdb;
		/**
		 * check is course
		 */
		$is_course = CoursePress_Data_Course::is_course( $course_id );
		if ( ! $is_course ) {
			return;
		}
		// Get units
		$status = array( 'publish', 'draft', 'private' );
		$units_ids = CoursePress_Data_Course::get_units( $course_id, $status, true );
		if ( is_array( $units_ids ) && ! empty( $units_ids ) ) {
			// Units found, delete them as well
			foreach ( $units_ids as $unit_id ) {
				wp_delete_post( $unit_id, false );
				/**
				 * Notify others that a unit is deleted
				 **/
				do_action( 'coursepress_unit_deleted', $unit_id );
				$modules_ids = CoursePress_Data_Course::get_unit_modules( $unit_id, $status, true );
				if ( is_array( $modules_ids ) && count( $modules_ids ) > 0 ) {
					// Modules found, delete them
					foreach ( $modules_ids as $module_id ) {
						wp_delete_post( $module_id, true );
						/**
						 * Notify others that a module is deleted
						 **/
						do_action( 'coursepress_module_deleted', $module_id );
					}
				}
			}
		}
		/**
		 * delete counters
		 */
		$query = $wpdb->prepare( "delete from {$wpdb->usermeta} where meta_key = %s", 'cp_instructor_course_count' );
		$wpdb->query( $query );
		/**
		 * delete user meta, most of them are students meta
		 */
		$keys_to_delete = array(
			'course_%d', // delete instructor user_meta
			'course_%d_progress',
			'cp_notice-course_%d',
			'enrolled_course_class_%d',
			'enrolled_course_date_%d',
			'enrolled_course_group_%d',
			'withdrawn_course_date_%d',
		);
		foreach ( $keys_to_delete as $template_of_meta_key ) {
			$meta_key = sprintf( $template_of_meta_key, $course_id );
			$query = $wpdb->prepare( "delete from {$wpdb->usermeta} where meta_key = %s", $meta_key );
			$wpdb->query( $query );
		}
		/**
		 * Notify others that a course is deleted
		 **/
		do_action( 'coursepress_course_deleted', $course_id );
		return true;
	}

	public static function update_course() {
		$data = json_decode( file_get_contents( 'php://input' ) );
		$step_data = $data->data;
		$json_data = array();
		$success = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Course Update: No action.', 'CP_TD' );
			wp_send_json_error( $json_data );
		}

		$action = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		switch ( $action ) {

			// Update Course
			case 'update_course':

				if (
					isset( $step_data->step )
					&& wp_verify_nonce( $data->data->nonce, 'setup-course' )
				) {

					$step = (int) $step_data->step;

					$course_id = CoursePress_Data_Course::update( $step_data->course_id, $step_data );
					$json_data['course_id'] = $course_id;

					$next_step = (int) $data->next_step;
					$json_data['last_step'] = $step;
					$json_data['next_step'] = $next_step;
					$json_data['redirect'] = $data->data->is_finished;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
					$settings = CoursePress_Data_Course::get_setting( $course_id, true );

					/*
					 * save course start date as separate field, we need it to
					 * sort courses on courses list page. The post-meta field
					 * contains the numeric timestamp, not a formated string!
					 */
					$start_date = 0;
					if ( isset( $settings['course_start_date'] ) ) {
						$start_date = $settings['course_start_date'];
					}
					$start_date = strtotime( $start_date );
					update_post_meta( $course_id, 'course_start_date', $start_date );

					/**
					 * save enrollment_end_date
					 */
					$course_open_ended = isset( $settings['course_open_ended'] ) && cp_is_true( $settings['course_open_ended'] );
					if ( $course_open_ended ) {
						delete_post_meta( $course_id, 'course_enrollment_end_date' );
					} else {
						$enrollment_end_date = 0;
						if ( isset( $settings['enrollment_end_date'] ) ) {
							$enrollment_end_date = $settings['enrollment_end_date'];
						}
						$enrollment_end_date = strtotime( $enrollment_end_date );
						update_post_meta( $course_id, 'course_enrollment_end_date', $enrollment_end_date );
					}
				}

				break;

			case 'toggle_course_status':

				$course_id = $data->data->course_id;

				if (
					wp_verify_nonce( $data->data->nonce, 'publish-course' )
					&& CoursePress_Data_Capabilities::can_update_course( $data->data->course_id )
				) {

					wp_update_post( array(
						'ID' => $course_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-course' );
					$success = true;
					$settings = CoursePress_Data_Course::get_setting( $course_id, true );
					/** This action is documented in include/coursepress/data/class-course.php */
					do_action( 'coursepress_course_updated', $course_id, $settings );

				}

				$json_data['course_id'] = $course_id;
				$json_data['state'] = $data->data->state;

				break;

			// Delete Instructor
			case 'delete_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$json_data['who'] = 'instructor';
					if ( isset( $data->data->who ) && 'facilitator' === $data->data->who ) {
						CoursePress_Data_Facilitator::remove_course_facilitator(
							$data->data->course_id,
							$data->data->instructor_id
						);
						$json_data['who'] = 'facilitator';
					} else {
						CoursePress_Data_Course::remove_instructor(
							$data->data->course_id,
							$data->data->instructor_id
						);
					}
					$json_data['instructor_id'] = $data->data->instructor_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}

				break;

			// Add Instructor
			case 'add_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Course::add_instructor( $data->data->course_id, $data->data->instructor_id );
					$user = get_userdata( $data->data->instructor_id );
					$json_data['id'] = $data->data->instructor_id;
					$json_data['display_name'] = $user->display_name;
					$json_data['course_id'] = $data->data->course_id;
					$json_data['avatar'] = get_avatar( $data->data->instructor_id, 80 );
					$json_data['who'] = 'instructor';

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}

				break;

			// Invite Instructor
			case 'invite_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$response = '';
					if ( isset( $data->data->who ) && 'facilitator' === $data->data->who ) {
						$response = CoursePress_Data_Facilitator::send_invitation(
							(int) $data->data->course_id,
							$data->data->email,
							$data->data->first_name,
							$data->data->last_name
						);
					} else {
						$response = CoursePress_Data_Instructor::send_invitation(
							(int) $data->data->course_id,
							$data->data->email,
							$data->data->first_name,
							$data->data->last_name
						);
					}
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
					$json_data['who'] = 'instructor';
					if ( isset( $data->data->who ) && 'facilitator' === $data->data->who ) {
						CoursePress_Data_Facilitator::delete_invitation(
							$data->data->course_id,
							$data->data->invite_code
						);
						$json_data['who'] = 'facilitator';
					} else {
						CoursePress_Data_Instructor::delete_invitation(
							$data->data->course_id,
							$data->data->invite_code
						);
					}
					$json_data['course_id'] = $data->data->course_id;
					$json_data['invite_code'] = $data->data->invite_code;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			case 'enroll_student':
				if ( wp_verify_nonce( $data->data->nonce, 'add_student' ) ) {
					/**
					 * Turn off enroll_student check when we are in ajax admin action
					 */
					if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
						remove_all_filters( 'coursepress_enroll_student' );
					}
					CoursePress_Data_Course::enroll_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'add_student' );
					$success = true;
				}
				break;

			case 'withdraw_student':
				$nonce = sprintf( 'withdraw-single-student-%d', $data->data->student_id );
				if ( wp_verify_nonce( $data->data->nonce, $nonce ) ) {
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

					// Save invited student
					$email = sanitize_email( $email_data['email'] );
					$course_id = (int) $email_data['course_id'];
					$invited_students = CoursePress_Data_Course::get_setting( $course_id, 'invited_students', array() );
					$invite_data = array(
						'first_name' => $email_data['first_name'],
						'last_name' => $email_data['last_name'],
						'email' => $email_data['email'],
					);
					$invited_students[ $email ] = $invite_data;

					// Save invited data
					CoursePress_Data_Course::update_setting( $course_id, 'invited_students', $invited_students );

					$success = $response;
				}
				break;

			case 'remove_student_invitation':
				if ( wp_verify_nonce( $data->data->nonce, 'coursepress_remove_invite' ) ) {
					$course_id = (int) $data->data->course_id;
					$student_email = sanitize_email( $data->data->email );
					$invited_students = CoursePress_Data_Course::get_setting( $course_id, 'invited_students', array() );

					if ( ! empty( $invited_students[ $student_email ] ) ) {
						unset( $invited_students[ $student_email ] );
					}
					// Resaved invited students
					CoursePress_Data_Course::update_setting( $course_id, 'invited_students', $invited_students );
					$success = true;
				}
				break;

			// Add facilitator
			case 'add_facilitator':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Facilitator::add_course_facilitator( $data->data->course_id, $data->data->facilitator_id );
					$json_data['who'] = 'facilitator';
					$json_data['id'] = $data->data->facilitator_id;
					$json_data['display_name'] = get_user_option( 'display_name', $data->data->facilitator_id );
					$json_data['course_id'] = $data->data->course_id;

					$user = get_userdata( $data->data->facilitator_id );
					$json_data['avatar'] = get_avatar( $user->user_email, 80 );

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				} else {
					$json_data['facilitator_id'] = $data->data->facilitator_id;
					$json_data['message'] = __( 'Unable to add facilitator!', 'CP_TD' );
				}

				break;
			// Remove facilitator
			case 'remove_facilitator':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Facilitator::remove_course_facilitator( $data->data->course_id, $data->data->facilitator_id );
					$json_data['facilitator_id'] = $data->data->facilitator_id;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			case 'bulk_actions':

				if ( isset( $data->data->nonce ) && wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					$courses = $data->data->courses;
					$action = $data->data->the_action;

					foreach ( $courses as $course_id ) {
						switch ( $action ) {

							case 'publish':
								if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
									continue;
								}
								wp_update_post( array(
									'ID' => $course_id,
									'post_status' => 'publish',
								) );
							break;
							case 'unpublish':
								if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
									continue;
								}
								wp_update_post( array(
									'ID' => $course_id,
									'post_status' => 'draft',
								) );
							break;
							case 'delete':
								CoursePress_Admin_Controller_Course::delete_course( $course_id );
							break;

						}
					}

					$settings = CoursePress_Data_Course::get_setting( $course_id, true );
					/** This action is documented in include/coursepress/data/class-course.php */
					do_action( 'coursepress_course_updated', $course_id, $settings );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'bulk_action_nonce' );
					$success = true;
				}
				break;

			case 'delete_course':

				if ( wp_verify_nonce( $data->data->nonce, 'delete_course' ) ) {

					$course_id = (int) $data->data->course_id;
					CoursePress_Admin_Controller_Course::delete_course( $course_id );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'delete_course' );
					$success = true;
				}

				break;

			case 'duplicate_course':
				// Check wp nonce.
				if ( wp_verify_nonce( $data->data->nonce, 'duplicate_course' ) ) {
					$json_data = CoursePress_Data_Course::duplicate_course( $data );
					$success = (bool) $json_data['success'];
					if ( $success ) {
						// force removal of MP meta stuffs
						delete_post_meta( $json_data['course_id'], 'cp_mp_product_id' );
						delete_post_meta( $json_data['course_id'], 'cp_mp_sku' );
						delete_post_meta( $json_data['course_id'], 'cp_mp_auto_sku' );
					}
				}

				break;

			case 'send_email':
				if ( wp_verify_nonce( $data->data->nonce, 'send_email_to_enroled_students' ) ) {
					$course_id = $data->data->course_id;
					$students = CoursePress_Data_Course::get_students( $course_id );
					$error_message = __( 'No email sent!', 'CP_TD' );

					// Filter list of students to send email to
					if ( ! empty( $data->data->send_to ) && 'all' != $data->data->send_to ) {
						$send_to = $data->data->send_to;
						$filtered_students = array();

						foreach ( $students as $student ) {
							$student_progress = CoursePress_Data_Student::get_completion_data( $student->ID, $course_id );
							$units_progress = CoursePress_Helper_Utility::get_array_val(
								$student_progress,
								'completion/progress'
							);

							if ( 'all_with_submission' === $send_to && intval( $units_progress ) > 0 ) {
								$filtered_students[] = $student;
							} elseif ( intval( $send_to ) > 0 ) {
								$per_unit_progress = CoursePress_Helper_Utility::get_array_val(
									$student_progress,
									'completion/' . $send_to . '/progress'
								);

								if ( intval( $per_unit_progress ) > 0 ) {
									$filtered_students[] = $student;
								}
							}
						}

						if ( count( $filtered_students ) > 0 ) {
							$students = $filtered_students;
						} else {
							$error_message = __( 'No students found!', 'CP_TD' );
							$students = array();
						}
					}

					/**
					 * post body vars
					 */
					$post = get_post( $course_id );
					$course_name = $post->post_title;
					$course_summary = $post->post_excerpt;
					$valid_stati = array( 'draft', 'pending', 'auto-draft' );

					if ( in_array( $post->post_status, $valid_stati ) ) {
						$course_address = CoursePress_Core::get_slug( 'course/', true ) . $post->post_name . '/';
					} else {
						$course_address = get_permalink( $course_id );
					}

					if ( CoursePress_Core::get_setting( 'general/use_custom_login', true ) ) {
						$login_url = CoursePress_Core::get_slug( 'login', true );
					} else {
						$login_url = wp_login_url();
					}
					$json_data['message'] = array(
						'body' => $data->data->body,
						'subject' => $data->data->subject,
						'to' => array(),
					);

					// Email Content.
					$vars = array(
						'BLOG_NAME' => get_bloginfo( 'name' ),
						'COURSE_ADDRESS' => esc_url( $course_address ),
						'COURSE_EXCERPT' => $course_summary,
						'COURSE_NAME' => $course_name,
						'COURSE_OVERVIEW' => $course_summary,
						'COURSES_ADDRESS' => CoursePress_Core::get_slug( 'course', true ),
						'LOGIN_ADDRESS' => esc_url( $login_url ),
						'WEBSITE_ADDRESS' => home_url(),
						'WEBSITE_NAME' => get_bloginfo( 'name' ),
					);
					$count = 0;
					/**
					 * send mail to each student
					 */
					foreach ( $students as $student ) {
						$vars['STUDENT_FIRST_NAME'] = empty( $student->first_name ) && empty( $student->last_name ) ? $student->display_name : $student->first_name;
						$vars['STUDENT_LAST_NAME'] = $student->last_name;
						$vars['STUDENT_LOGIN'] = $student->data->user_login;
						$body = CoursePress_Helper_Utility::replace_vars( $data->data->body, $vars );
						$args = array(
							'subject' => $data->data->subject,
							'to' => $student->user_email,
							'message' => $body,
						);
						if ( CoursePress_Helper_Email::send_email( '', $args ) ) {
							$count++;
						}
					}
					/**
					 * add message
					 */
					if ( $count ) {
						$success = true;
						$json_data['message']['info'] = sprintf(
							_n(
								'%d email have been sent successfully.',
								'%d emails have been sent successfully.',
								$count,
								'CP_TD'
							),
							$count
						);
					} else {
						$success = false;
						$json_data['message']['info'] = $error_message;
					}
				} else {
					$json_data['message']['to'] = 0;
					$json_data['message']['info'] = __( 'Something went wrong.', 'CP_TD' );
				}
				break;

		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}
	}
}
