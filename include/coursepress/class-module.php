<?php
/**
 * The class that handles student submissions.
 *
 * @since 2.0.0
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Module {
	public static $error_message = '';

	/**
	 * Check if it is a valid submission.
	 **/
	public static function is_valid() {
		return ( ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress_submit_modules' ) );
	}

	/**
	 * First level validation.
	 **/
	public static function validate_course( $input ) {
		$has_error = false;

		if ( empty( $input['course_id'] ) ) {
			$has_error = true;
			self::$error_message = __( 'Invalid course ID!', 'coursepress' );
		} elseif ( false === CoursePress_Data_Course::student_enrolled( $input['student_id'], $input['course_id'] ) ) {
			$has_error = true;
			self::$error_message = __( 'You are currently not enrolled to this course!', 'coursepress' );
		} elseif ( 'closed' == ( $course_status = CoursePress_Data_Course::get_course_status( $input['course_id'] ) ) ) {
			$has_error = true;
			self::$error_message = __( 'This course is completed, you can not submit answers anymore.', 'coursepress' );
		} elseif ( empty( $input['unit_id'] ) ) {
			$has_error = true;
			self::$error_message = __( 'Invalid unit!', 'coursepress' );
		}

		return $has_error;
	}

	/**
	 * Validate per module.
	 *
	 * @since 2.0
	 *
	 * @param (int) $module_id
	 * @param (mixed) $submitted response
	 * @return (bool) Returns true if an error found, otherwise false.
	 **/
	public static function validate_module( $module_id, $response = '', $student_id = 0 ) {
		$unit_id = get_post_field( 'post_parent', $module_id );
		$course_id = get_post_field( 'post_parent', $unit_id );
		$attributes = CoursePress_Data_Module::attributes( $module_id );
		$module_type = $attributes['module_type'];
		$mandatory = ! empty( $attributes['mandatory'] );
		$is_assessable = ! empty( $attributes['assessable'] );
		$has_error = false;
		$course_mode = get_post_meta( $course_id, 'cp_course_view', true );
		$is_focus = 'focus' == $course_mode;

		if ( true === $mandatory ) {
			if ( '' == $response ) {
				$has_error = true;
				self::$error_message = $is_focus ? __( 'You need to complete this module!', 'coursepress' ) : __( 'You need to complete the required modules!', 'coursepress' );
			} else {
				// Attempt to record the submission
				CoursePress_Data_Student::module_response( $student_id, $course_id, $unit_id, $module_id, $response );

				$excluded_modules = array(
					'input-textarea',
					'input-text',
				);

				if ( true === $is_assessable && ! in_array( $module_type, $excluded_modules ) ) {
					$minimum_grade = $attributes['minimum_grade'];
					$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id );
					$grade = CoursePress_Helper_Utility::get_array_val( $grades, 'grade' );
					$pass = (int) $grade >= (int) $minimum_grade;

					if ( false === $pass ) {
						$has_error = true;
						self::$error_message = __( 'You did not pass the required minimum grade!', 'coursepress' );
					}
				}
			}
		}

		return $has_error;
	}

	/**
	 * Add single comment in discussion module.
	 **/
	public static function add_comment( $comments, $student_id ) {
		if ( empty( $student_id ) ) {
			// Assume current user ID
			$student_id = get_current_user_id();
		}

		$module_id = $comments['comment_post_ID'];
		$is_module = CoursePress_Data_Module::is_module( $module_id );
		if ( ! $is_module ) {
			return new WP_Error( 'error', __( 'Module do not exists.', 'coursepress' ) );
		}

		$unit_id = get_post_field( 'post_parent', $module_id );
		$course_id = get_post_field( 'post_parent', $unit_id );

		/**
		 * Trigger CP action hooks before inserting a new comment
		 *
		 * @since 2.0
		 **/
		do_action( 'coursepress_before_add_comment', $module_id, $course_id );

		$comments = wp_parse_args(
			$comments,
			array(
				'comment_author' => '',
				'comment_author_url' => '',
				'comment_author_email' => '',
				'comment_type' => '',
				'user_id' => $student_id,
			)
		);
		$comment_id = wp_new_comment( $comments );
		/**
		 * Trigger CP action hooks after inserting comment to DB
		 **/
		do_action( 'coursepress_after_add_comment', $comment_id, $student_id, $module_id, $course_id );

		return $comment_id;
	}

	public static function add_single_comment( $input ) {
		global $post;

		$module_id = (int) $input['comment_post_ID'];
		$is_module = CoursePress_Data_Module::is_module( $module_id );
		if ( ! $is_module ) {
			$json_data = array(
				'success' => false,
			);
			wp_send_json_error( $json_data );
			return;
		}

		$unit_id = (int) $input['unit_id'];
		$course_id = (int) $input['course_id'];
		$student_id = (int) $input['student_id'];

		$comments = array(
			'comment_content' => $input['comment'],
			'comment_parent' => $input['comment_parent'],
			'comment_post_ID' => $input['comment_post_ID'],
			'user_id' => $student_id,
		);

		// Add new comment
		$comment_id = self::add_comment( $comments, $student_id );

		if ( is_a( $comment_id, 'WP_Error' ) ) {
			$json_data = array(
				'success' => false,
			);
			wp_send_json_error( $json_data );
			return;
		}

		if ( 0 < $comment_id ) {
			// Update subscribers list
			$value = $input['subscribe'];
			$post = get_post( $module_id );

			// Update student progres
			CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );

			setup_postdata( $post );

			if ( empty( $value ) ) {
				$value = CoursePress_Helper_Discussion::get_default_key();
			}
			CoursePress_Data_Discussion::update_user_subscription( $student_id, $module_id, $value );

			// Add comment filters
			add_filter( 'comment_reply_link', array( 'CoursePress_Template_Module', 'comment_reply_link' ), 10, 4 );

			$json_data = array(
				'success' => true,
				'html' => CoursePress_Template_Discussion::get_single_comment( $comment_id ),
				'comment_parent' => $comments['comment_parent'],
				'comment_id' => $comment_id,
			);

			// Remove comment filters, etc
			remove_filter( 'comment_reply_link', array( 'CoursePress_Template_Module', 'comment_reply_link' ), 10, 4 );

			// Print result
			wp_send_json_success( $json_data );

			return;
		}

		wp_send_json_error( array( 'success' => false ) );
	}

	/**
	 * Validate modules submission.
	 **/
	public static function process_submission() {

		if ( self::is_valid() ) {
			self::submit( $_REQUEST );
		}
	}

	public static function submit( $input ) {
		if ( ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		$course_id = (int) $input['course_id'];
		$unit_id = (int) $input['unit_id'];
		$module = isset( $input['module'] ) ? (array) $input['module'] : false;
		$module_id = 0;
		$page = ! empty( $input['page'] ) ? (int) $input['page'] : 1;
		$student_id = (int) $input['student_id'];
		$has_error = false;
		$course_mode = get_post_meta( $course_id, 'cp_course_view', true );
		$is_focus = 'focus' == $course_mode;
		$excluded_modules = array(
			'input-textarea',
			'input-text',
			'input-upload',
			'input-form',
		);

		$validate = isset( $input['save_progress_and_exit'] )? false : true;

		// Validate the course
		$error = CoursePress_Data_Course::can_access( $course_id, $unit_id );
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $student_id );

		if ( ! empty( $error ) ) {
			$has_error = true;
			self::$error_message = $error;
		} elseif ( true === self::validate_course( $input ) ) {
			$has_error = true;
		} else {
			if ( ! empty( $input['module_id'] ) ) {
				$module_ids = (array) $input['module_id'];

				foreach ( $module_ids as $module_id ) {
					$attributes = CoursePress_Data_Module::attributes( $module_id );
					$module_type = $attributes['module_type'];
					$is_mandatory = ! empty( $attributes['mandatory'] );
					$is_assessable = ! empty( $attributes['assessable'] ) || ! empty( $attributes['require_instructor_assessment'] );
					$is_answerable = preg_match( '%input-%', $module_type );

					if ( 'input-upload' == $module_type ) {
						if ( false == $can_update_course && empty( $_FILES ) ) {
							if ( $validate ) {
								self::$error_message = __( 'You need to complete the required module!', 'coursepress' );
								$has_error = true;
							}
						}
						continue; // Upload validation is at the bottom
					}

					if ( 'discussion' == $module_type ) {
						if ( empty( $input['comment'] ) ) {
							if ( $is_mandatory && false == $can_update_course ) {
								// Check if current student previously commented.
								if ( CoursePress_Data_Discussion::have_comments( $student_id, $module_id ) ) {
									continue;
								} else {
									$has_error = true;
									self::$error_message = __( 'Your participation to the discussion is required!', 'coursepress' );
									continue;
								}
							}
						} else {
							$comments = array(
								'comment_content' => $input['comment'],
								'comment_post_ID' => $module_id,
								'user_ID' => $student_id,
							);

							// Check for parent comment
							if ( ! empty( $input['comment_parent'] ) ) {
								$comments['comment_parent'] = $input['comment_parent'];
							}
							self::add_comment( $comments, $student_id );

							// Update subscribers list
							$field_name = CoursePress_Helper_Discussion::get_field_name();
							$value = isset( $input[ $field_name ] ) ? $input[ $field_name ] : CoursePress_Helper_Discussion::get_default_key();
							CoursePress_Data_Discussion::update_user_subscription( $student_id, $module_id, $value );

							continue;
						}
					}

					if ( $is_answerable ) {
						if ( ! isset( $module[ $module_id ] ) || '' === ( $module[ $module_id ] ) ) {
							// Check if module is mandatory
							if ( $is_mandatory && false == $can_update_course ) {
								if ( $validate ) {
									self::$error_message = __( 'You need to complete the required module!', 'coursepress' );
									$has_error = true;
								}
							}
							continue;
						} else {
							$response = $module[ $module_id ];

							if ( 'input-quiz' == $module_type ) {

								foreach ( $attributes['questions'] as $qi => $question ) {
									$answers = array_keys( $question['options']['answers'] );

									if ( isset( $response[ $qi ] ) && '' != $response[ $qi ] ) {
										$qi_response = $response[ $qi ];
										$values = array();

										foreach ( $answers as $a_key ) {
											$values[ $a_key ] = '';

											if ( 'multiple' == $question['type'] ) {
												$values[ $a_key ] = in_array( $a_key, $qi_response );
											} else {
												$values[ $a_key ] = $qi_response == $a_key;
											}
										}

										$response[ $qi ] = $values;
									}
								}
							}

							// Record submission only if student actually submitted the form.
							if ( empty( $input['is_module_hidden'][ $module_id ] ) ) {
								// Attempt to record the submission.
								CoursePress_Data_Student::module_response( $student_id, $course_id, $unit_id, $module_id, $response );
							}


							// override $is_assessable if module type 'input-form', regardless if enabled in admin dashboard or not
							// logic from CoursePress_Data_Module::get_form_results() is that Form will have a grade of 100 if not required, otherwise check if empty for all submodules
							if ( $module_type == 'input-form' ) {
								$is_assessable = true;
							}

							// Check if the grade acquired pass
							if ( true === $is_assessable && ! in_array( $module_type, $excluded_modules ) ) {
								$minimum_grade = $attributes['minimum_grade'];
								$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id );
								$grade = CoursePress_Helper_Utility::get_array_val( $grades, 'grade' );
								$pass = (int) $grade >= (int) $minimum_grade;

								if ( false === $pass && false == $can_update_course ) {
									$has_error = true;
									self::$error_message = ( $module_type == 'input-form' ) ?
										__( 'You did not complete the form!', 'coursepress' )
										: __( 'You did not pass the required minimum grade!', 'coursepress' );
								}
							}
						}
					}
				}
			}

			// Check for upload submission
			if ( ! empty( $_FILES['module'] ) ) {
				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				$upload_overrides = array(
					'test_form' => false,
					'mimes' => CoursePress_Helper_Utility::allowed_student_mimes(),
				);
				$files = $_FILES['module'];

				foreach ( $files['name'] as $_module_id => $filename ) {
					$attributes = CoursePress_Data_Module::attributes( $_module_id );
					$response = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $_module_id );
					$required = ! empty( $attributes['mandatory'] );

					if ( true === $required && false == $can_update_course ) {
						if ( empty( $filename ) ) {
							if ( empty( $response ) ) {
								if ( $validate ) {
									self::$error_message = __( 'You need to complete the required module!', 'coursepress' );
									$has_error = true;
								}
								continue;
							} else {
								// There's an old submission, exclude!
								continue;
							}
						}
					} else {
						// If it is not required and no submission, break
						if ( empty( $filename ) ) {
							continue;
						}
					}

					$file = array(
						'name' => $filename,
						'size' => $files['size'][ $_module_id ],
						'error' => $files['error'][ $_module_id ],
						'type' => $files['type'][ $_module_id ],
						'tmp_name' => $files['tmp_name'][ $_module_id ],
					);
					$response = wp_handle_upload( $file, $upload_overrides );
					$response['size'] = $file['size'];

					if ( ! empty( $response['error'] ) ) {
						$has_error = true;
						self::$error_message = $response['error'];
					} else {
						CoursePress_Data_Student::module_response( $student_id, $course_id, $unit_id, $_module_id, $response );
					}
				}
			}
		}

		$via_ajax = ! empty( $input['is_cp_ajax'] );

		if ( $has_error ) {
			if ( $via_ajax ) {
				if ( $is_focus ) {
					$html = '[coursepress_focus_item course="%s" unit="%s" type="%s" item_id="%s"]';
					$html = do_shortcode( sprintf( $html, $course_id, $unit_id, 'module', $module_id ) );
				} else {
					$html = CoursePress_Template_Unit::unit_with_modules( $course_id, $unit_id, $page, $student_id );
				}

				$json_data = array(
					'success' => false,
					'data'=> array(
						'error' => true,
						'error_message' => self::$error_message,
						'html' => $html,
						'is_reload' => false,
					)
				);
				header('Content-type: text/plain');
				echo json_encode( $json_data );
				die();
			} else {
				add_action( 'coursepress_before_unit_modules', array( __CLASS__, 'show_error_message' ) );
			}
		} else {

			if ( isset( $input['page'] ) ) {
				$page = intval( $input['page'] );
			}

			// Update student progress
			//CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
			$next = CoursePress_Data_Course::get_next_accessible_module( $course_id, $unit_id, $page, $module_id );

			if ( $via_ajax ) {

				$item_id = $next['id'];
				$reload = false;

				if ( 'section' == $next['type'] ) {
					$reload = $unit_id != $next['unit'];
					$unit_id = $next['unit'];
					$item_id = $next['id'];
				} else {
					$item_id = $next['id'];
				}

				if ( $is_focus ) {
					$html = '[coursepress_focus_item course="%s" unit="%s" type="%s" item_id="%s"]';
					$html = do_shortcode( sprintf( $html, $course_id, $unit_id, $next['type'], $item_id ) );
				} else {
					$html = '';

					if ( 'completion_page' != $next['id'] ) {
						$html = CoursePress_Template_Unit::unit_with_modules( $course_id, $unit_id, $item_id, $student_id );
					}
				}
				$type = 'completion_page' == $next['id'] ? 'completion' : $next['type'];

				/**
				 * Save last seen unit and page.
				 */
				if ( isset( $input['save_progress_and_exit'] ) ) {
					$meta_key = CoursePress_Data_Course::get_last_seen_unit_meta_key( $course_id );
					$meta_value = array(
						'unit_id' => isset( $input['unit_id'] )? $input['unit_id'] : false,
						'page' => isset( $input['page'] )? $input['page'] : 1,
					);
					update_user_meta( $student_id, $meta_key, $meta_value );
					$next['url'] = CoursePress_Data_Course::get_course_url( $course_id );
				}

				$json_data = array(
					'success' => true,
					'html' => $html,
					'url' => ! empty( $next['url'] ) ? $next['url'] : false,
					'type' => $type,
					'is_reload' => $reload,
				);
				$json_data = array( 'success' => true, 'data' => $json_data );

				header('Content-type: text/plain');
				echo json_encode( $json_data );
				die();

				//wp_send_json_success( $json_data );
			} else {
				$next_url = $next['url'];
				wp_safe_redirect( $next_url ); exit;
			}
		}
	}

	public static function show_error_message() {
		if ( ! empty( self::$error_message ) ) {
			$format = '<p>%s</p>';
			return sprintf( $format, self::$error_message );
		}
	}

	public static function record_expired_answer( $request ) {
		$module_id = (int) $request['module_id'];
		$course_id = (int) $request['course_id'];
		$unit_id = (int) $request['unit_id'];
		$student_id = (int) $request['student_id'];
		$keys = array( $course_id, $unit_id, $module_id, $student_id );
		$key = 'response_' . implode( '_', $keys );
		$count = (int) get_user_meta( $student_id, $key, true );
		$count += 1;
		update_user_meta( $student_id, $key, $count );
		wp_send_json_success( array( 'true' => true ) );
	}
}
