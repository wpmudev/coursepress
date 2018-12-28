<?php
/**
 * Class CoursePress_Admin_Ajax
 *
 * Handles ajax requests both front and backend.
 *
 * @since 3.0
 * @package CoursePress
 */

class CoursePress_Admin_Ajax extends CoursePress_Utility {
	public function __construct() {
		// Hook to `wp_ajax` action hook to process common ajax request
		add_action( 'wp_ajax_coursepress_request', array( $this, 'process_ajax_request' ) );
		// Hook to get course units for editing
		add_action( 'wp_ajax_coursepress_get_course_units', array( $this, 'get_course_units' ) );
		// Hook to handle file uploads
		add_action( 'wp_ajax_coursepress_upload', array( $this, 'upload_file' ) );
		// Hook to search for select2 data.
		add_action( 'wp_ajax_coursepress_get_users', array( $this, 'get_course_users' ) );
		add_action( 'wp_ajax_coursepress_search_students', array( $this, 'search_students' ) );
		// Hook to enrollment request
		add_action( 'wp_ajax_coursepress_enroll', array( $this, 'enroll' ) );
		add_action( 'wp_ajax_coursepress_unenroll', array( $this, 'unenroll' ) );
		add_action( 'wp_ajax_course_enroll_passcode', array( $this, 'enroll_with_passcode' ) );
		// Update profile
		add_action( 'wp_ajax_coursepress_update_profile', array( $this, 'update_profile' ) );
		// Submit module
		add_action( 'wp_ajax_coursepress_submit', array( $this, 'validate_submission' ) );
		add_action( 'wp_ajax_nopriv_coursepress_submit', array( $this, 'validate_submission' ) );
		/**
		 * Search course
		 */
		add_action( 'wp_ajax_coursepress_courses_search', array( $this, 'search_course' ) );
		add_action( 'wp_ajax_coursepress_discussion_courses_search', array( $this, 'search_discussion_course' ) );
	}

	/**
	 * Callback method to process ajax request.
	 * There's only 1 ajax request, each request differs and process base on the `action` param set.
	 * So if the request is `update_course` it's corresponding method will be `update_course`.
	 */
	public function process_ajax_request() {
		$request = json_decode( file_get_contents( 'php://input' ) );
		$error = array(
			'code' => 'cannot_process',
			'message' => __( 'Something went wrong. Please try again.', 'cp' ),
			);
		if ( isset( $request->_wpnonce ) && wp_verify_nonce( $request->_wpnonce, 'coursepress_nonce' ) ) {
			$action = $request->action;
			// Remove commonly used params
			unset( $request->action, $request->_wpnonce );
			if ( method_exists( $this, $action ) ) {
				$response = call_user_func( array( $this, $action ), $request );
				if ( ! empty( $response['success'] ) ) {
					unset( $response['success'] );
					wp_send_json_success( $response );
				} else {
					$error = wp_parse_args( $response, $error ); }
			}
		}
		wp_send_json_error( $error );
	}

	/**
	 * Update assessment grade and feedback.
	 *
	 * @param  array $request Request data.
	 * @return array          Response.
	 */
	public function update_assessments_grade( $request ) {
		$course_id     = $request->course_id;
		$unit_id       = $request->unit_id;
		$step_id       = $request->step_id;
		$student_id    = $request->student_id;
		$grade         = (int) $request->student_grade;
		$with_feedback = ! empty( $request->with_feedback );
		$feedback_text = ! empty( $request->feedback_content ) ? self::filter_content( $request->feedback_content ) : '';

		$student          = new CoursePress_User( $student_id );
		$student_progress = $student->get_completion_data( $course_id );
		$feedback         = $student->get_instructor_feedback( $student_id, $course_id, $unit_id, $step_id, false, $student_progress );
		$old_feedback     = ! empty( $feedback['feedback'] ) ? $feedback['feedback'] : '';
		$draft_feedback   = ! empty( $feedback['draft'] );
		$current_user_id  = get_current_user_id();
		$date             = current_time( 'mysql', 1 );

		$response          = coursepress_get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $step_id );
		$response['grade'] = $grade;

		$is_feedback_new = false;

		if ( $with_feedback ) {
			$is_feedback_new = empty( $old_feedback );

			if ( ! empty( $old_feedback ) ) {
				$is_feedback_new = $draft_feedback || trim( $feedback_text ) !== trim( $old_feedback );
			}

			if ( $is_feedback_new ) {
				$response['feedback'][] = array(
					'feedback_by' => $current_user_id,
					'feedback'    => $feedback_text,
					'date'        => $date,
					'draft'       => $draft_feedback,
				);

				$student    = new CoursePress_User( $student_id );
				$email_args = array(
					'email'               => $student->user_email,
					'student_id'          => $student_id,
					'course_id'           => $course_id,
					'unit_id'             => $unit_id,
					'module_id'           => $step_id,
					'instructor_feedback' => $feedback_text,
				);

				// New feedback, send email.
				$sent = CoursePress_Data_Email::send_email(
					CoursePress_Data_Email::INSTRUCTOR_MODULE_FEEDBACK_NOTIFICATION,
					$email_args
				);
			}
		}

		// Record new grade and get the progress back.
		$progress = $student->record_response( $course_id, $unit_id, $step_id, $response, $current_user_id );

		$is_completed              = coursepress_get_array_val( $student_progress, 'completion/completed' );
		$unit_grade                = $student->get_unit_grade( $course_id, $unit_id );
		$json_data['completed']    = coursepress_is_true( $is_completed );
		$json_data['success']      = true;
		$json_data['unit_grade']   = (int) $unit_grade;
		$json_data['course_grade'] = $student->get_course_grade( $course_id );
		$json_data['has_pass_course_unit'] = $student->has_pass_course_unit( $course_id, $unit_id );
		return $json_data;
	}

	/**
	 * Save feedback to draft.
	 *
	 * @param  array $request Request data.
	 * @return array          Response.
	 */
	public function save_draft_feedback( $request ) {
		$course_id  = $request->course_id;
		$unit_id    = $request->unit_id;
		$step_id    = $request->step_id;
		$student_id = $request->student_id;

		$feedback_text    = self::filter_content( $request->feedback_content );
		$student          = new CoursePress_User( $student_id );
		$student_progress = $student->get_completion_data( $course_id );
		$response         = coursepress_get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $step_id );
		$current_user_id  = get_current_user_id();
		$date             = current_time( 'mysql', 1 );

		$response['feedback'][] = array(
			'feedback_by' => $current_user_id,
			'feedback'    => $feedback_text,
			'date'        => $date,
			'draft'       => true,
		);

		$progress = $student->record_response( $course_id, $unit_id, $step_id, $response, $current_user_id );

		$json_data['success'] = true;
		return $json_data;
	}

	/**
	 * Get the course units for editing
	 */
	public function get_course_units() {
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
		$with_modules = filter_input( INPUT_GET, 'with_modules', FILTER_VALIDATE_INT );
		$wpnonce = filter_input( INPUT_GET, '_wpnonce' );
		$error = array(
			'error_code' => 'cannot_get_units',
			'message' => __( 'Something went wrong. Please try again.', 'cp' ),
			);
		if ( ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
			wp_send_json_error( $error );
		}
		$course = new CoursePress_Course( $course_id );
		$units = $course->get_units( false );
		if ( ! empty( $units ) ) {
			foreach ( $units as $pos => $unit ) {
				if ( $with_modules ) {
					$modules = $unit->get_modules_with_steps( false );
					if ( $modules ) {
						foreach ( $modules as $mpos => $module ) {
							$mini_desc = '';
							if ( ! empty( $module['description'] ) ) {
								$mini_desc = wp_strip_all_tags( $module['description'] );
								$mini_desc = substr( $mini_desc, 0, 50 );
							}
							$modules[ $mpos ]['mini_desc'] = $mini_desc;
						}
					} else {
						// Set empty module
						$modules = array(
							1 => array(
								'id' => 1,
								'title' => __( 'Untitled', 'cp' ),
								'description' => '',
								'show_description' => true,
								'steps' => array(),
							),
						);
					}
					$unit->__set( 'modules', $modules );
				} else {
					$steps = $unit->get_steps( false );
					$unit->__set( 'steps', $steps );
				}
				// Set permalink to the unit.
				$unit->unit_permalink = $unit->get_permalink();
				// Get unit permissions.
				$unit->can_update_unit = CoursePress_Data_Capabilities::can_update_unit( $unit->ID );
				$unit->can_delete_unit = CoursePress_Data_Capabilities::can_delete_unit( $unit->ID );
				$unit->can_change_unit_status = CoursePress_Data_Capabilities::can_change_unit_status( $unit->ID );
				$units[ $pos ] = $unit;
			}
		}
		wp_send_json_success( $units );
	}

	/**
	 * Update course data.
	 */
	public function update_course( $request ) {
		$course_object = array(
			'post_type' => 'course',
			'post_status' => 'pending',
			'post_title' => __( 'Untitled', 'cp' ),
			'post_excerpt' => '',
			'post_name' => '',
			'post_content' => '',
			'ID' => 0,
			'menu_order' => 0,
			'comment_status' => 'closed', // Alway closed comment status
		);
		// Fill course object
		foreach ( $course_object as $key => $value ) {
			if ( isset( $request->{$key} ) ) {
				$course_object[ $key ] = $request->{$key};
			}
		}
		$course_object['post_name'] = wp_unique_post_slug( $course_object['post_name'], $course_object['ID'], 'publish', 'course', 0 );
		$is_auto_draft              = false;
		if ( 'auto-draft' === $course_object['post_status'] ) {
			$is_auto_draft                = true;
			$course_object['post_status'] = 'draft';
		}
		if ( (int) $course_object['ID'] > 0 && ! $is_auto_draft ) {
			// Check course update capability.
			if ( ! CoursePress_Data_Capabilities::can_update_course( (int) $course_object['ID'] ) ) {
				return array( 'success' => false );
			}
			$course_id = wp_update_post( $course_object );
		} else {
			// Check course creation capability.
			if ( ! CoursePress_Data_Capabilities::can_create_course() ) {
				return array( 'success' => false );
			}
			$course_id = wp_insert_post( $course_object );
		}
		$course_meta = array();
		foreach ( $request as $key => $value ) {
			$_key = str_replace( 'meta_', '', $key );
			if ( preg_match( '%meta_%', $key ) ) {
				$course_meta[ $_key ] = $value;
			}
		}
		$course = coursepress_get_course( $course_id );
		$course->update_setting( true, $course_meta );
		$course->save_course_number( $course_id, $course_object['post_title'] );
		// Retrieve the course object back
		$course = coursepress_get_course( $course_id, false );
		return array(
			'success' => true,
			'ID' => $course_id,
			'course' => $course,
			);
	}

	public function update_units( $request ) {
		if ( $request->units ) {
			$course_id = (int) $request->course_id;
			$units = $request->units;
			$menu_order = empty( $request->menu_order ) ? 0 : (int) $request->menu_order;
			$unit_ids = array();
			foreach ( $units as $cid => $unit ) {
				// Get post object
				if ( ! empty( $unit->deleted ) ) {
					// Do not continue if no permission.
					if ( ! CoursePress_Data_Capabilities::can_delete_unit( $unit->ID ) ) {
						continue;
					}
					// Delete unit here
					if ( ! empty( $unit->ID ) ) {
						coursepress_delete_unit( $unit->ID );
					}
					// Don't return the unit object
					unset( $units->{$cid} );
					continue;
				}
				// Check if required capability is set.
				$can_proceed = empty( $unit->ID ) ?
					CoursePress_Data_Capabilities::can_create_unit( $course_id ) :
					CoursePress_Data_Capabilities::can_update_unit( $unit->ID );
				// Do not continue if no permission.
				if ( ! $can_proceed ) {
					continue;
				}
				// Get post object
				$unit_array = array(
					'ID' => $unit->ID,
					'post_title' => $unit->post_title,
					'post_content' => $unit->post_content,
					'menu_order' => $unit->menu_order,
					'post_parent' => $course_id,
					'post_status' => 'pending',
					'post_type' => 'unit',
				);
				if ( ! empty( $unit->post_status ) ) {
					$unit_array['post_status'] = $unit->post_status;
				}
				$metas = array();
				foreach ( $unit as $key => $value ) {
					if ( preg_match( '%meta_%', $key ) ) {
						$_key           = str_replace( 'meta_', '', $key );
						$metas[ $_key ] = $value;
					}
				}
				/**
				 * add unit_availability_date_timestamp
				 */
				if ( isset( $metas['unit_availability_date'] ) ) {
					$metas['unit_availability_date_timestamp'] = strtotime( $metas['unit_availability_date'] );
				}
				$unit_id = coursepress_create_unit( $unit_array, $metas );
				$unit_object = coursepress_get_unit( $unit_id );
				if ( ! empty( $unit->modules ) ) {
					$module_array = array();
					foreach ( $unit->modules as $module_id => $module ) {
						$module_deleted = false;
						if ( isset( $module->deleted ) ) {
							$module_deleted = true;
							unset( $unit->modules->{$module_id} );
						}
						$module_array[ $module_id ] = array(
							'title' => sanitize_text_field( $module->title ),
							'preview' => isset( $module->preview ) ? $module->preview : true, // Default is true,
							'show_description' => isset( $module->show_description ) && $module->show_description ? true : false,
							'description' => isset( $module->description ) ? $module->description : '',
						);
						if ( $module_deleted ) {
							unset( $module_array[ $module_id ] );
						}
						if ( ! empty( $module->steps ) ) {
							$new_steps = array();
							foreach ( $module->steps as $step_cid => $step ) {
								if ( $module_deleted ) {
									$step->deleted = true;
								}
								if ( ! empty( $step->deleted ) && $step->deleted ) {
									// This step was deleted, let's delete the data
									if ( isset( $step->ID ) && ! empty( $step->ID ) ) {
										coursepress_delete_step( $step->ID );
									}
									unset( $module->steps->{$step_cid} );
									continue;
								}
								$step_array = array(
									'ID' => isset( $step->ID ) ? (int) $step->ID : 0,
									'post_type' => 'module',
									'post_title' => isset( $step->post_title )? $step->post_title : '',
									'post_content' => isset( $step->post_content )? $step->post_content : '',
									'post_status' => 'publish',
									'post_parent' => $unit_id,
									'menu_order' => isset( $step->menu_order ) ? (int) $step->menu_order : 0,
								);
								/**
								 * Work on step meta
								 */
								$step_metas = array();
								foreach ( $step as $step_key => $step_value ) {
									if ( preg_match( '%meta_%', $step_key ) ) {
										$_step_key = str_replace( 'meta_', '', $step_key );
										if ( is_object( $step_value ) ) {
											$step_value = $this->to_array( $step_value );
										}
										$step_metas[ $_step_key ] = $step_value;
									}
								}
								foreach ( $step as $step_key => $step_value ) {
									if ( preg_match( '/^meta_((.+)\[view\d+\])$/', $step_key, $matches ) ) {
										unset( $step_metas[ $matches[1] ] );
										$step_metas[ $matches[2] ] = $step_value;
									}
								}
								// Let's keep course id too.
								$step_metas['course_id'] = $course_id;
								$step_id = coursepress_create_step( $step_array, $step_metas );
								$step_object = coursepress_get_course_step( $step_id );
								$new_steps[ $step_cid ] = $step_object;
							}
							$module->steps = $new_steps;
							$unit->modules->{$module_id} = $module;
						}
					}
					$unit_object->update_settings( 'course_modules', $module_array );
				} else {
					if ( ! empty( $unit->steps ) ) {
						foreach ( $unit->steps as $step_cid => $step ) {
							if ( ! empty( $step->deleted ) && $step->deleted ) {
								// This step was deleted, let's delete the data
								if ( isset( $step->ID ) && ! empty( $step->ID ) ) {
									coursepress_delete_step( $step->ID );
								}
								unset( $unit->steps->{$step_cid} );
								continue;
							}
							$step_array = array(
								'ID' => isset( $step->ID ) ? (int) $step->ID : 0,
								'post_type' => 'module',
								'post_title' => $step->post_title,
								'post_content' => $step->post_content,
								'post_status' => 'publish',
								'post_parent' => $unit_id,
								'menu_order' => isset( $step->menu_order ) ? (int) $step->menu_order : 0,
							);
							$step_metas = array();
							foreach ( $step as $step_key => $step_value ) {
								if ( preg_match( '%meta_%', $step_key ) ) {
									$_step_key = str_replace( 'meta_', '', $step_key );
									if ( is_object( $step_value ) ) {
										$step_value = $this->to_array( $step_value );
									}
									$step_metas[ $_step_key ] = $step_value;
								}
							}
							// Let's keep course id too.
							$step_metas['course_id'] = $course_id;
							$step_id = coursepress_create_step( $step_array, $step_metas );
							$step_object = coursepress_get_course_step( $step_id );
							$unit->steps->{$step_cid} = $step_object;
						}
					}
				}
				// Set back new vars
				$unit->ID = $unit_id;
				$unit->menu_order = $menu_order;
				$units->{$cid} = $unit;
				$menu_order++;
			}
			wp_send_json_success( array(
				'success' => true,
				'units' => $units,
			) );
		}
		wp_send_json_error( true );
	}

	/**
	 * Delete/Trash/Restore/Draft/Puplish cp post.
	 *
	 * @since 3.0.0
	 *
	 * @param $request
	 */
	public function change_post( $request ) {
		if ( empty( $request->id ) || empty( $request->type ) || empty( $request->cp_action )
				|| ! coursepress_is_type( $request->id, $request->type ) ) {
			return;
		}
		$result = coursepress_change_post( $request->id, $request->cp_action, $request->type );
		if ( $result ) {
			return array( 'success' => true );
		}
	}

	/**
	 * Update global settings.
	 *
	 * @param $request
	 * @return array
	 */
	public function update_settings( $request ) {
		if ( $request ) {
			$request = get_object_vars( $request );
			$request = array_map( array( $this, 'to_array' ), $request );
		}
		/**
		 * remove some keys
		 */
		$keys_to_remove = array( 'extensions_available' );
		foreach ( $keys_to_remove as $key ) {
			if ( isset( $request[ $key ] ) ) {
				unset( $request[ $key ] );
			}
		}
		/**
		 * check extensions settings
		 */
		global $coursepress_extension;
		$coursepress_extension->active_extensions();
		coursepress_update_setting( true, $request );
		return array( 'success' => true );
	}

	/**
	 * Generate certificate for PREVIEW.
	 *
	 * @param $request
	 * @return array
	 */
	public function preview_certificate( $request ) {
		global $cp_coursepress;
		$course_id = '';
		$pdf = $cp_coursepress->get_class( 'CoursePress_PDF' );
		if ( isset( $request->ID ) ) {
			$course_id = $request->ID;
			$content = $request->meta_basic_certificate_layout;
			$background = $request->meta_certificate_background;
			$margins = isset( $request->meta_cert_margin ) ? get_object_vars( $request->meta_cert_margin ) : array();
			$orientation = $request->meta_page_orientation;
			$text_color = $request->meta_cert_text_color;
			$logo_image = $request->meta_certificate_logo;
			$logo_positions = ! empty( $request->meta_certificate_logo_position ) ? get_object_vars( $request->meta_certificate_logo_position ) : array();
		} else {
			$content = $request->content;
			$background = isset( $request->background_image )? $request->background_image : false;
			$margins = get_object_vars( $request->margin );
			$text_color = $request->cert_text_color;
			$orientation = $request->orientation;
			$logo_image = isset( $request->certificate_logo )?  $request->certificate_logo : false;
			$logo_positions = ! empty( $request->certificate_logo_position ) ? get_object_vars( $request->certificate_logo_position ) : array();
		}
		$logo = array_merge(
			array( 'file' => $logo_image ),
			$logo_positions
		);
		$date_format = apply_filters( 'coursepress_basic_certificate_date_format', get_option( 'date_format' ) );
		$content = apply_filters( 'coursepress_basic_certificate_html', $content, $course_id, get_current_user_id() );
		$vars = array(
			'FIRST_NAME' => __( 'Jon', 'cp' ),
			'LAST_NAME' => __( 'Snow', 'cp' ),
			'COURSE_NAME' => __( 'Example Course Title', 'cp' ),
			'COMPLETION_DATE' => date_i18n( $date_format, $this->date_time_now() ),
			'CERTIFICATE_NUMBER' => uniqid( wp_rand(), true ),
		);
		$content = $this->replace_vars( $content, $vars );
		$text_color = $this->convert_hex_color_to_rgb( $text_color, '#000000' );
		// Set PDF args
		$args = array(
			'title' => __( 'Course Completion Certificate', 'cp' ),
			'orientation' => $orientation,
			'image' => $background,
			'pdf_content' => $content,
			'format' => 'F',
			'uid' => '12345',
			'margins' => apply_filters( 'coursepress_basic_certificate_margins', $margins ),
			'logo' => apply_filters( 'coursepress_basic_certificate_logo', $logo ),
			'text_color' => apply_filters( 'coursepress_basic_certificate_text_color', $text_color ),
		);
		$cache_buster = md5( serialize( $args ) );
		$filename = sprintf( 'cert-preview-%s-%s.pdf', $course_id, $cache_buster );
		$args['filename'] = $filename;
		$error = '';
		try {
			$pdf->make_pdf( $content, $args );
		} catch (Exception $exception) {
			$error = $exception->getMessage();
		}
		if ( $error ) {
			wp_send_json_error( array( 'message' => $error ) );
		} else {
			wp_send_json_success(array(
				'pdf' => $pdf->cache_url() . $filename,
			));
		}
	}

	public function upload_file() {
		$request = $_POST;
		if ( ! empty( $_FILES ) && ! empty( $request['_wpnonce'] )
			&& wp_verify_nonce( $request['_wpnonce'], 'coursepress_nonce' ) ) {
			$type = $request['type'];
			if ( method_exists( $this, $type ) ) {
				call_user_func( array( $this, $type ), $_FILES, $request );
			}
		}
		wp_send_json_error( true );
	}

	public function import_file( $files, $request ) {
		// Check permission before importing.
		if ( ! CoursePress_Data_Capabilities::can_create_course() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to import courses.', 'cp' ) ) );
		}
		$import = wp_import_handle_upload();
		if ( ! empty( $import['id'] ) ) {
			$import_id = $import['id'];
			$filename = $import['file'];
			$courses = file_get_contents( $filename );
			$data = array();
			/**
			 * sanitize option
			 */
			$options = array(
				'replace' => false,
				'with_students' => false,
				'with_comments' => false,
			);
			foreach ( $options as $key => $value ) {
				$options[ $key ] = isset( $request[ $key ] ) && is_string( $request[ $key ] ) && ( '1' == $request[ $key ] || 'true' == $request[ $key ] );
			}
			/**
			 *  Import file is json format!
			 */
			if ( preg_match( '%.json%', $filename ) ) {
				$courses = json_decode( $courses );
				if ( empty( $courses ) ) {
					wp_send_json_error();
				}
				$courses = get_object_vars( $courses );
				$data['import_id'] = $import_id;
				$data['total_courses'] = count( $courses );
				$data['courses'] = array();
				foreach ( $courses as $course ) {
					$import_class = new CoursePress_Import( $course, $options );
				}
				wp_delete_attachment( $import_id );
				wp_send_json_success( $data );
			}
			wp_delete_attachment( $import_id );
		}
		wp_send_json_error();
	}

	public function import_course( $request ) {
		// Check permission first.
		if ( ! CoursePress_Data_Capabilities::can_create_course() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to import courses.', 'cp' ) ) );
		}
		$import_id = $request->import_id;
		// Let's import the course one at a time to avoid over caps
		$courses = coursepress_get_option( $import_id );
		$courses = maybe_unserialize( $courses );
		if ( is_array( $courses ) ) {
			$the_course = array_shift( $courses );
			$import_options = wp_parse_args(
				(array) $request,
				array(
					'author' => wp_get_current_user(),
				)
			);
			$import_class = new CoursePress_Import( $the_course, $import_options );
			coursepress_delete_course( $request->old_course_id );
			wp_send_json_success(array(
				'course' => $import_class->get_course(),
			));
		}
	}

	/**
	 * Toggle course status.
	 *
	 * @param $request Request data.
	 */
	public function course_status_toggle( $request ) {
		$toggled = false;
		// If course id and status is not empty, attempt to change status.
		if ( ! empty( $request->course_id ) && ! empty( $request->status ) ) {
			$toggled = coursepress_change_post( $request->course_id, $request->status, 'course' );
		}
		// If status changed, return success response, else fail.
		if ( $toggled ) {
			$success = array( 'message' => __( 'Course status updated successfully.', 'cp' ) );
			wp_send_json_success( $success );
		} else {
			$error = array(
				'error_code' => 'cannot_change_status',
				'message' => __( 'Could not update course status.', 'cp' ),
				);
			wp_send_json_error( $error );
		}
	}

	/**
	 * Create new course category from text.
	 *
	 * @param object $request Request data.
	 */
	public function create_course_category( $request ) {
		// Do not continue if empty.
		if ( empty( $request->name ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not create new category.', 'cp' ) ) );
		}
		// Check if term already exist. We may have created it through select2 and removed.
		$term = get_term_by( 'name', $request->name, 'course_category' );
		// If term not exist, create new one.
		if ( ! $term ) {
			$term = coursepress_create_course_category( $request->name );
		}
		// If category created/exist, send the category name as response.
		if ( $term ) {
			wp_send_json_success( $term->name );
		}
		wp_send_json_error( array( 'message' => __( 'Could not create new category.', 'cp' ) ) );
	}

	/**
	 * Send email invitations to the users.
	 *
	 * @param object $request Request data.
	 */
	public function send_email_invite( $request ) {
		$proceed = true;
		// Do not continue if empty.
		if ( empty( $request->email ) || empty( $request->type ) || empty( $request->course_id ) ) {
			$proceed = false;
		} elseif ( 'instructor' === $request->type && ! CoursePress_Data_Capabilities::can_assign_course_instructor( $request->course_id ) ) {
			$proceed = false;
		} elseif ( 'facilitator' === $request->type && ! CoursePress_Data_Capabilities::can_assign_facilitator( $request->course_id ) ) {
			$proceed = false;
		}
		// If we can not continue.
		if ( ! $proceed ) {
			wp_send_json_error( array( 'message' => __( 'Could not send email invitation.', 'cp' ) ) );
		}
		$args = array(
			'email' => $request->email,
			'course_id' => $request->course_id,
			'first_name' => empty( $request->first_name ) ? '' : $request->first_name,
			'last_name' => empty( $request->last_name ) ? '' : $request->last_name,
		);
		$result = coursepress_send_email_invite( $args, $request->type );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		// Send email invitation.
		if ( $result ) {
			$data = (array) $request;
			$data['code'] = $result;
			$data['message'] = __( 'Invitation email has been sent.', 'cp' );
			wp_send_json_success( $data );
		}
		wp_send_json_error( array( 'message' => __( 'Could not send email invitation.', 'cp' ) ) );
	}

	/**
	 * Assign instructor/facilitator to a course.
	 *
	 * @param object $request Request data.
	 */
	public function assign_to_course( $request ) {
		// Do not continue if required values are empty.
		if ( empty( $request->course_id ) || empty( $request->user ) || empty( $request->type ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not assign selected user.', 'cp' ) ) );
		}
		$success = false;
		switch ( $request->type ) {
			case 'instructor':
				// Make sure assign capability is there, to remove.
				if ( CoursePress_Data_Capabilities::can_assign_course_instructor( $request->course_id ) ) {
					$success = coursepress_add_course_instructor( $request->user, $request->course_id );
				}
				break;
			case 'facilitator':
				// Make sure assign capability is there, to remove.
				if ( CoursePress_Data_Capabilities::can_assign_facilitator( $request->course_id ) ) {
					$success = coursepress_add_course_facilitator( $request->user, $request->course_id );
				}
				break;
		}
		// If sent, send success response back.
		if ( $success ) {
			$user = coursepress_get_user( $request->user );
			$name = $user->get_name();
			wp_send_json_success(
				array(
					'message' => sprintf( __( 'Selected user is assigned as %s.', 'cp' ), $request->type ),
					'name' => $name,
					'id' => $request->user,
				)
			);
		}
		wp_send_json_error( array( 'message' => __( 'Could not assign selected user.', 'cp' ) ) );
	}

	/**
	 * Remove instructor/facilitator from a course.
	 *
	 * @param object $request Request data.
	 */
	public function remove_from_course( $request ) {
		// Do not continue if required values are empty.
		if ( empty( $request->course_id ) || empty( $request->user ) || empty( $request->type ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not remove the user.', 'cp' ) ) );
		}
		$success = false;
		switch ( $request->type ) {
			case 'instructor':
				// Make sure assign capability is there, to remove.
				if ( CoursePress_Data_Capabilities::can_assign_course_instructor( $request->course_id ) ) {
					$success = coursepress_delete_course_instructor( $request->user, $request->course_id );
				}
				break;
			case 'facilitator':
				// Make sure assign capability is there, to remove.
				if ( CoursePress_Data_Capabilities::can_assign_facilitator( $request->course_id ) ) {
					$success = coursepress_remove_course_facilitator( $request->user, $request->course_id );
				}
				break;
		}
		// If sent, send success response back.
		if ( $success ) {
			wp_send_json_success(
				array(
					'message' => __( 'Selected user is removed from the course.', 'cp' ),
					'id' => $request->user,
				)
			);
		}
		wp_send_json_error( array( 'message' => __( 'Could not remove the user.', 'cp' ) ) );
	}

	/**
	 * Get users to assign as instructors and facilitators.
	 *
	 * @param object $request Request data.
	 */
	public function get_course_users() {
		$users = array();
		// Request data.
		$request = $_REQUEST;
		// Do some security checks.
		if ( isset( $request['_wpnonce'] ) && wp_verify_nonce( $request['_wpnonce'], 'coursepress_nonce' ) ) {
			$search = empty( $request['search'] ) ? '' : $request['search'];
			// Do not continue if required values are empty.
			if ( ! empty( $request['course_id'] ) && ! empty( $request['type'] ) ) {
				$users = coursepress_get_available_users( $request['course_id'], $request['type'], $search );
			}
		}
		wp_send_json( $users );
	}

	public function import_sample_course( $request ) {
		global $cp_coursepress;
		$file = $request->meta_sample_course;
		$option_id = 'sample_' . $file;
		$data = array();
		$data['import_id'] = $option_id;
		// Let's check if the sample had previously use
		$courses = coursepress_get_option( $option_id );
		if ( empty( $courses ) ) {
			$filename = $cp_coursepress->plugin_path . 'assets/external/sample-courses/' . $file;
			$courses = file_get_contents( $filename );
			$courses = json_decode( $courses );
			$courses = get_object_vars( $courses );
			coursepress_update_option( $option_id, $courses );
		}
		if ( $courses ) {
			wp_send_json_success( $data );
		}
		wp_send_json_error( true );
	}

	/**
	 * Enroll user to course
	 */
	public function enroll() {
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
		$wpnonce   = filter_input( INPUT_GET, '_wpnonce' );
		// Note: nonce verification is removed for now. As nonce value is regenerated after login and lead to nonce verification fail. Need to find way to add back.
		if ( ! $course_id ) {
			wp_send_json_error( true );
		}
		if ( coursepress_add_student( get_current_user_id(), $course_id ) ) {
			$course = coursepress_get_course( $course_id );
			$redirect = $course->get_units_url();
			wp_safe_redirect( $redirect );
			exit;
		}
		wp_send_json_error( true );
	}

	/**
	 * Withdraw from course, but as selfaction
	 */
	public function unenroll() {
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
		$wpnonce = filter_input( INPUT_GET, '_wpnonce' );
		if ( ! $course_id || ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
			die( __( 'Cheatin&#8217; uh?', 'cp' ) );
		}
		$student_id = get_current_user_id();
		$user = new CoursePress_User( $student_id );
		if ( is_wp_error( $user ) || $user->is_error() ) {
			die( __( 'Cheatin&#8217; uh?', 'cp' ) );
		}
		coursepress_delete_student( $student_id, $course_id );
		if ( isset( $_REQUEST['redirect'] ) ) {
			wp_safe_redirect( $_REQUEST['redirect'] );
		}
	}

	/**
	 * Enroll user with password to course
	 */
	public function enroll_with_passcode() {
		$course_id = filter_input( INPUT_POST, 'course_id', FILTER_VALIDATE_INT );
		$wpnonce = filter_input( INPUT_POST, '_wpnonce' );
		if ( ! $course_id || ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
			wp_send_json_error();
		}
		$course = coursepress_get_course( $course_id );
		if ( ! is_wp_error( $course ) ) {
			if ( coursepress_add_student( get_current_user_id(), $course_id ) ) {
				$redirect = $course->get_units_url();
				wp_safe_redirect( $redirect );
				exit;
			}
		}
		wp_send_json_error( true );
	}

	/**
	 * Withdraw student rfom a course.
	 */
	private function withdraw_student( $request ) {
		$course_id = filter_var( $request->course_id, FILTER_VALIDATE_INT );
		$student_id = filter_var( $request->student_id, FILTER_VALIDATE_INT );
		$result = coursepress_delete_student( $student_id, $course_id );
		if ( is_wp_error( $result ) ) {
			$data = array(
				'message' => $result->get_error_message(),
			);
			wp_send_json_error( $data );
		}
		$data = array( 'student_id' => $student_id );
		wp_send_json_success( $data );
	}

	public function update_profile() {
		$request = $_POST;
		$wpnonce = $request['_wpnonce'];
		if ( ! $wpnonce || ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
			wp_send_json_error( true );
		}
		$user = get_userdata( get_current_user_id() );
		$redirect = coursepress_get_student_settings_url();
		if ( ! empty( $request['password'] ) ) {
			$password = sanitize_text_field( $request['password'] );
			$confirm = sanitize_text_field( $request['password_confirmation'] );
			if ( $password !== $confirm ) {
				coursepress_set_cookie( 'cp_mismatch_password', true, time() + 120 );
				wp_safe_redirect( $redirect );
				exit;
			} else {
				$user->user_pass = $password;
			}
		}
		if ( ! empty( $request['first_name'] ) ) {
			$user->first_name = sanitize_text_field( $request['first_name'] );
		}
		if ( ! empty( $request['last_name'] ) ) {
			$user->last_name = sanitize_text_field( $request['last_name'] );
		}
		if ( ! empty( $request['email'] ) ) {
			$user->user_email = sanitize_email( $request['email'] );
		}
		wp_update_user( $user );
		coursepress_set_cookie( 'cp_profile_updated', true, time() + 120 );
		wp_safe_redirect( $redirect );
		exit;
	}

	public function record_media_response( $request ) {
		$user_id = get_current_user_id();
		$user = coursepress_get_user( $user_id );
		$user->record_response( $request->course_id, $request->unit_id, $request->step_id, array() );
		return array( 'success' => true );
	}

	/**
	 * Handle user answers
	 */
	public function validate_submission() {
		$course_id = filter_input( INPUT_POST, 'course_id', FILTER_VALIDATE_INT );
		$unit_id = filter_input( INPUT_POST, 'unit_id', FILTER_VALIDATE_INT );
		$module_id = filter_input( INPUT_POST, 'module_id', FILTER_VALIDATE_INT );
		$step_id = filter_input( INPUT_POST, 'step_id', FILTER_VALIDATE_INT );
		$type = filter_input( INPUT_POST, 'type' );
		$user_id = get_current_user_id();
		$referer = filter_input( INPUT_POST, 'referer_url' );
		$redirect_url = filter_input( INPUT_POST, 'redirect_url' );
		$response = isset( $_POST['module'] ) && is_array( $_POST['module'] )? $_POST['module']:null;
		$user = coursepress_get_user( $user_id );
		if ( ! $user->is_enrolled_at( $course_id ) ) {
			// If user is not enrolled, don't validate
			wp_safe_redirect( $referer );
			exit;
		}
		$progress = $user->get_completion_data( $course_id );
		if ( (int) $step_id > 0 ) {
			$step = coursepress_get_course_step( $step_id );
			if ( ! empty( $response ) || 'fileupload' === $step->type || 'discussion' === $step->type || 'quiz' === $step->type ) {
				$progress = $step->validate_response( $response );
			}
		}
		$progress = $user->validate_completion_data( $course_id, $progress );
		//error_log(print_r($progress,true));
		if ( empty( $redirect_url ) ) {
			$course = coursepress_get_course( $course_id );
			$redirect_url = ! empty( $referer ) ? $referer : $course->get_units_url();
		}
		$user->add_student_progress( $course_id, $progress );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Get the list of units and students based on selected course.
	 *
	 * @param object $request
	 */
	public function get_notification_units_students( $request ) {
		$result = array();
		if ( ! isset( $request->course_id ) ) {
			wp_send_json_success( $result );
		}
		// Get students based on the course id.
		$student_ids = coursepress_get_students_ids( $request->course_id );
		if ( ! empty( $student_ids ) ) {
			foreach ( $student_ids as $id => $student_id ) {
				$student = coursepress_get_user( $student_id );
				if ( ! is_wp_error( $student ) ) {
					$result['students'][] = array(
						'id' => $student_id,
						'text' => $student->get_name(),
					);
				}
			}
		}
		if ( ! empty( $request->course_id ) ) {
			// Get units based on the course id.
			$units = coursepress_get_course_units( $request->course_id );
			if ( ! empty( $units ) ) {
				foreach ( $units as $id => $unit ) {
					$result['units'][] = array(
						'id'   => $id,
						'text' => $unit->get_the_title(),
					);
				}
			}
		}
		wp_send_json_success( $result );
	}

	/**
	 * Get the list of units and students based on selected course.
	 *
	 * @param object $request
	 */
	public function get_notification_students( $request ) {
		$result = array();
		// Make sure required values are set.
		if ( empty( $request->course_id ) || empty( $request->unit_id ) ) {
			wp_send_json_error( $result );
		}
		// Get students based on the completed units.
		$students = coursepress_get_students_by_completed_unit( $request->course_id, $request->unit_id );
		if ( ! empty( $students ) ) {
			foreach ( $students as $id => $student ) {
				$result['students'][] = array(
					'id' => $id,
					'text' => $student->get_name(),
				);
			}
		}
		wp_send_json_success( $result );
	}

	/**
	 * Send notification emails to students.
	 *
	 * @param $request Request data.
	 */
	public function send_notification_email( $request ) {
		global $cp_coursepress;
		// Check if required values are set.
		if ( empty( $request->content ) || empty( $request->title ) || empty( $request->students ) ) {
			wp_send_json_error();
		}
		$email = $cp_coursepress->get_class( 'CoursePress_Email' );
		// Send email notifications.
		if ( $email->notification_alert_email( $request->students, $request->title, $request->content ) ) {
			wp_send_json_success( array( 'message' => __( 'Notification emails sent successfully.', 'cp' ) ) );
		}
		wp_send_json_error( array( 'message' => __( 'Could not send email notifications.', 'cp' ) ) );
	}

	/**
	 * Toggle discussion status.
	 *
	 * @param $request Request data.
	 */
	public function discussion_status_toggle( $request ) {
		$toggled = false;
		// If discussion id and status is not empty, attempt to change status.
		if ( ! empty( $request->discussion_id ) && ! empty( $request->status ) ) {
			$toggled = coursepress_change_post( $request->discussion_id, $request->status, 'discussion' );
		}
		// If status changed, return success response, else fail.
		if ( $toggled ) {
			$success = array( 'message' => __( 'Discussion status updated successfully.', 'cp' ) );
			wp_send_json_success( $success );
		} else {
			$error = array(
				'error_code' => 'cannot_change_status',
				'message' => __( 'Could not update discussion status.', 'cp' ),
				);
			wp_send_json_error( $error );
		}
	}

	/**
	 * Toggle alert status.
	 *
	 * @param $request Request data.
	 */
	public function alert_status_toggle( $request ) {
		$toggled = false;
		// If alert id and status is not empty, attempt to change status.
		if ( ! empty( $request->alert_id ) && ! empty( $request->status ) ) {
			$toggled = coursepress_change_post( $request->alert_id, $request->status, 'notification' );
		}
		// If status changed, return success response, else fail.
		if ( $toggled ) {
			$success = array( 'message' => __( 'Alert status updated successfully.', 'cp' ) );
			wp_send_json_success( $success );
		} else {
			$error = array(
				'error_code' => 'cannot_change_status',
				'message' => __( 'Could not update alert status.', 'cp' ),
				);
			wp_send_json_error( $error );
		}
	}

	public function get_course_alert( $request ) {
		$data = ! empty( $request->alert_id ) ? coursepress_get_notification_alert( $request->alert_id ) : array();
		if ( $data ) {
			wp_send_json_success( $data );
		}
		wp_send_json_error( true );
	}

	/**
	 * Create or Update course alert.
	 *
	 * @param $request Request data.
	 */
	public function update_course_alert( $request ) {
		$created = false;
		// Check if required values are set.
		if ( ! empty( $request->course_id ) && ! empty( $request->title ) && ! empty( $request->content ) ) {
			$alert_id = ! empty( $request->alert_id ) ? $request->alert_id : '' ;
			$receivers = ! empty( $request->receivers ) ? $request->receivers : '' ;
			// Check for capabilities.
			if ( empty( $alert_id ) ) {
				if ( ! CoursePress_Data_Capabilities::can_add_notification( $request->course_id ) ) {
					wp_send_json_error( array( 'message' => __( 'You do not have permission to create alert for this course.', 'cp' ) ) );
				}
			} elseif ( ! CoursePress_Data_Capabilities::can_update_notification( $alert_id ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to update this alert.', 'cp' ) ) );
			}
			$created = coursepress_update_course_alert( $request->course_id, $request->title, $request->content, $receivers, $alert_id );
		}
		// If alert inserted return success response, else fail.
		if ( $created ) {
			if ( ! empty( $alert_id ) ) {
				$message = __( 'Course alert updated successfully.', 'cp' );
			} else {
				$message = __( 'New course alert created successfully.', 'cp' );
			}
			$success = array( 'message' => $message );
			wp_send_json_success( $success );
		} else {
			if ( ! empty( $alert_id ) ) {
				$message = __( 'Could not update course alert.', 'cp' );
			} else {
				$message = __( 'Could not create new course alert.', 'cp' );
			}
			$error = array( 'message' => $message );
			wp_send_json_error( $error );
		}
	}

	private function comment_status_toggle( $request ) {
		if ( ! isset( $request->id ) || ! isset( $request->nonce ) ) {
			return;
		}
		$nonce_action = 'coursepress_comment_status_'.$request->id;
		if ( ! wp_verify_nonce( $request->nonce, $nonce_action ) ) {
			return;
		}
		$comment = get_comment( $request->id );
		if ( ! is_a( $comment, 'WP_Comment' ) ) {
			return;
		}
		$status = wp_get_comment_status( $request->id );
		$commentarr = array();
		$commentarr['comment_ID'] = $request->id;
		switch ( $status ) {
			case 'unapproved':
				$commentarr['comment_approved'] = 1;
				break;
			case 'approved':
				$commentarr['comment_approved'] = 0;
				break;
		}
		if ( ! isset( $commentarr['comment_approved'] ) ) {
			return;
		}
		$result = wp_update_comment( $commentarr );
		if ( $result ) {
			$status = wp_get_comment_status( $request->id );
			$response = array(
				'id' => $request->id,
				'status' => $status,
				'success' => true,
				'button_text' => esc_html( 'unapproved' === $status ? __( 'Approve', 'cp' ) : __( 'Unapprove', 'cp' ) ),
			);
			return $response;
		}
	}

	/**
	 * Search course
	 */
	public function search_course() {
		if (
			! isset( $_REQUEST['_wpnonce'] )
			|| ! isset( $_REQUEST['q'] )
			|| ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress-course-search-nonce' )
		) {
			wp_send_json_error();
		}
		$data = array(
			'items' => array(),
			'total_count' => 0,
		);
		$args = array(
			'post_type' => 'course',
			's' => $_REQUEST['q'],
		);
		$posts = new WP_Query( $args );
		$data['total_count'] = $posts->post_count;
		$posts = $posts->posts;
		foreach ( $posts as $post ) {
			$one['id'] = $post->ID;
			$one['post_title'] = $post->post_title;
			$data['items'][] = $one;
		}
		wp_send_json( $data );
	}

	/**
	 * Search discussion courses.
	 */
	public function search_discussion_course() {
		if (
			! isset( $_REQUEST['_wpnonce'] )
			|| ! isset( $_REQUEST['q'] )
			|| ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'coursepress-course-search-nonce' )
		) {
			wp_send_json_error();
		}
		$data = array(
			'items'       => array(),
			'total_count' => 0,
		);
		$user = coursepress_get_user();
		if ( is_wp_error( $user ) ) {
			wp_send_json( $data );
		}

		$args = array(
			'post_type' => 'course',
			's'         => $_REQUEST['q'],
		);
		$posts               = new WP_Query( $args );
		$data['total_count'] = $posts->post_count;
		$posts               = $posts->posts;
		foreach ( $posts as $post ) {
			if ( CoursePress_Data_Capabilities::can_add_discussion( $post->ID ) ) {
				$one['id']         = $post->ID;
				$one['post_title'] = $post->post_title;
				$data['items'][]   = $one;
			}
		}
		wp_send_json( $data );
	}

	/**
	 * Get PDF report
	 */
	public function get_report_pdf( $request ) {
		global $cp_coursepress;
		$data = $cp_coursepress->get_class( 'CoursePress_Admin_Reports' );
		$content = $data->get_pdf_content( $request );
		if ( is_wp_error( $content ) ) {
			wp_send_json_error( array( 'message' => $content->get_error_message() ) );
		}
		if ( empty( $content ) || ! isset( $content['pdf_content'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Oops! Some error occurred while generating the PDF file.', 'cp' ) ) );
		}
		$pdf = $cp_coursepress->get_class( 'CoursePress_PDF' );
		$pdf->make_pdf( $content['pdf_content'], $content['args'] );
		$data = array(
			'pdf' => $pdf->cache_url() . $content['filename'],
		);
		wp_send_json_success( $data );
	}

	/**
	 * Send student course invitation.
	 */
	public function send_student_invite( $request ) {
		$course_id = $request->course_id;
		// Do not continue if not capable.
		if ( ! CoursePress_Data_Capabilities::can_invite_students( $course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to invite students.', 'cp' ) ) );
		}
		$args = array(
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
			'email' => $request->email,
		);
		$result = coursepress_invite_student( $course_id, $args );
		if ( is_wp_error( $result ) ) {
			$data = array(
				'message' => $result->get_error_message(),
			);
			wp_send_json_error( $data );
		}
		if ( $result ) {
			wp_send_json_success( $result );
		}
		wp_send_json_error( true );
	}

	/**
	 * Remove student course invitation.
	 *
	 * @param object $request Request data.
	 */
	public function remove_student_invite( $request ) {

		$success = false;
		// We need course id and valid email.
		if ( $request->course_id && is_email( $request->email ) ) {
			// Continue only if user can withdraw student.
			if ( ! CoursePress_Data_Capabilities::can_withdraw_course_student( $request->course_id ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to remove student invitation.', 'cp' ) ) );
			}
			$success = coursepress_remove_student_invite( $request->course_id, $request->email );
		}
		// Success resoponse with email.
		if ( $success ) {
			wp_send_json_success( $success );
		}
		wp_send_json_error( true );
	}

	/**
	 * Remove instructor course invitation.
	 *
	 * @param object $request Request data.
	 */
	public function remove_instructor_invite( $request ) {

		$success = false;
		if ( ! empty( $request->course_id ) && ! empty( $request->code ) ) {
			// Continue only if user can withdraw student.
			if ( ! CoursePress_Data_Capabilities::can_assign_course_instructor( $request->course_id ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to remove instructor invitation.', 'cp' ) ) );
			}
			$success = CoursePress_Data_Instructor::delete_invitation( $request->course_id, $request->code );
		}
		// Success resoponse with email.
		if ( $success ) {
			wp_send_json_success( array( 'code' => $request->code ) );
		}
		wp_send_json_error( array( 'message' => __( 'Something went wrong.', 'cp' ) ) );
	}

	/**
	 * Remove facilitator course invitation.
	 *
	 * @param object $request Request data.
	 */
	public function remove_facilitator_invite( $request ) {

		$success = false;
		if ( ! empty( $request->course_id ) && ! empty( $request->code ) ) {
			// Continue only if user can withdraw student.
			if ( ! CoursePress_Data_Capabilities::can_assign_course_instructor( $request->course_id ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to remove facilitator invitation.', 'cp' ) ) );
			}
			$success = CoursePress_Data_Facilitator::delete_invitation( $request->course_id, $request->code );
		}
		// Success resoponse with email.
		if ( $success ) {
			wp_send_json_success( array( 'code' => $request->code ) );
		}
		wp_send_json_error( array( 'message' => __( 'Something went wrong.', 'cp' ) ) );
	}

	public function search_students( $request ) {
		$users = array();
		// Request data.
		$request = $_REQUEST;
		// Do some security checks.
		if ( ! isset( $request['_wpnonce'] ) || ! wp_verify_nonce( $request['_wpnonce'], 'coursepress_nonce' ) ) {
			wp_send_json_error( $users );
		}
		if ( ! isset( $request['search'] ) || empty( $request['search'] ) ) {
			wp_send_json_error( $users );
		}
		// Do not continue if required values are empty.
		if ( ! empty( $request['course_id'] ) ) {
			$args = array(
				'search' => sprintf( '*%s*', $request['search'] ),
			);
			$exclude_users = coursepress_get_students_ids( $request['course_id'] );
			if ( ! empty( $exclude_users ) ) {
				$args['exclude'] = $exclude_users;
			}
			$u = get_users( $args );
			if ( ! empty( $u ) ) {
				foreach ( $u as $user ) {
					$user = array(
						'ID' => $user->ID,
						'display_name' => $user->data->display_name,
						'gravatar' => get_avatar( $user, 30 ),
						'user_email' => $user->data->user_email,
						'user_nicename' => $user->data->user_nicename,
						'user_login' => $user->data->user_login,
					);
					$users[] = $user;
				}
			}
		}
		wp_send_json_success( $users );
	}

	public function add_student_to_course( $request ) {
		if ( isset( $request->student_id ) && isset( $request->course_id ) ) {
			// Do not continue if not capable.
			if ( ! CoursePress_Data_Capabilities::can_add_course_student( $request->course_id ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to add students.', 'cp' ) ) );
			}
			$result = coursepress_add_student( $request->student_id, $request->course_id );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			} else {
				$user = coursepress_get_user( $request->student_id );
				$data = array(
					'ID' => $user->ID,
					'display_name' => $user->display_name,
					'gravatar_url' => get_avatar_url( $user->ID, array( 'size' => 30 ) ),
					'user_email' => $user->user_email,
					'user_nicename' => $user->user_nicename,
					'user_login' => $user->user_login,
				);
				wp_send_json_success( $data );
			}
		}
		wp_send_json_error( array( 'message' => __( 'Something went wrong. Could not add student.', 'cp' ) ) );
	}

	public function courses_bulk_action( $request ) {
		if ( isset( $request->courses ) && is_array( $request->courses ) && isset( $request->which ) ) {
			foreach ( $request->courses as $course_id ) {

				// Make sure that the user is capable.
				if ( in_array( $request->which, array( 'trash', 'delete' ), true ) && ! CoursePress_Data_Capabilities::can_delete_course( $course_id ) ) {
					continue;
				} elseif ( ! CoursePress_Data_Capabilities::can_change_course_status( $course_id ) ) {
					continue;
				}

				coursepress_change_post( $course_id, $request->which, 'course' );
			}
			wp_send_json_success();
		}
		wp_send_json_error( array( 'message' => __( 'Could not apply courses action.', 'cp' ) ) );
	}

	/**
	 * Withdraw students from course!
	 */
	public function withdraw_students( $request ) {
		if (
			! isset( $request->students )
			|| ! isset( $request->course_id )
			|| ! is_array( $request->students )
		) {
			return;
		}
		// Do not continue if this user is not capable.
		if ( ! CoursePress_Data_Capabilities::can_withdraw_course_student( $request->course_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'You do not have permission to withdraw students.', 'cp' ),
				);
		}
		foreach ( $request->students as $student_id ) {
			coursepress_delete_student( $student_id, $request->course_id );
		}
		return array( 'success' => true );
	}

	/**
	 * Withdraw students from all their courses.
	 *
	 * @param object $request Request data.
	 *
	 * @return json
	 */
	public function withdraw_students_from_all( $request ) {

		// Don't continue if students id(s) not given.
		if ( empty( $request->students ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not withdraw as students id is empty.', 'cp' ) ) );
		}
		// Make sure it is array.
		$student_ids = (array) $request->students;
		// Loop through each students and process.
		foreach ( $student_ids as $student_id ) {
			// Get students enrolled course ids.
			$course_ids = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
			if ( empty( $course_ids ) ) {
				continue;
			}
			// Loop through each enrolled course.
			foreach ( $course_ids as $course_id ) {
				// Do not continue if instructor do not have permission.
				if ( ! CoursePress_Data_Capabilities::can_withdraw_course_student( $course_id ) ) {
					continue;
				}
				// Finally remove student from course.
				coursepress_delete_student( $student_id, $course_id );
			}
		}
		return wp_send_json_success();
	}

	/**
	 * Change selected notifications statuses.
	 *
	 * @param object $request Request data.
	 *
	 * @return json
	 */
	public function change_notifications_status( $request ) {

		// Don't continue if notification id(s) not given.
		if ( empty( $request->items ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not continue as notification id is empty.', 'cp' ) ) );
		}
		// Make sure it is array.
		$items = (array) $request->items;
		// Loop through each notifications and process.
		foreach ( $items as $item_id ) {
			// Finally change the notification status.
			// Capability check will be handled here.
			coursepress_change_post( $item_id, $request->sub_action, 'notification' );
		}
		return wp_send_json_success();
	}

	/**
	 * Change selected forums statuses.
	 *
	 * @param object $request Request data.
	 *
	 * @return json
	 */
	public function change_forums_status( $request ) {

		// Don't continue if forum id(s) not given.
		if ( empty( $request->forums ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not continue as forum id is empty.', 'cp' ) ) );
		}
		// Make sure it is array.
		$forum_ids = (array) $request->forums;
		// Loop through each forums and process.
		foreach ( $forum_ids as $forum_id ) {
			// Finally change the forum status.
			// Capability check will be handled here.
			coursepress_change_post( $forum_id, $request->cp_action, 'discussion' );
		}
		return wp_send_json_success();
	}

	/**
	 * Duplicate single course and units.
	 *
	 * @param object $request Request.
	 */
	public function duplicate_course( $request ) {
		// We need course id.
		if ( ! isset( $request->course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Oops! Could not duplicate the course.', 'cp' ) ) );
		}
		// Continue only if valid course.
		$course = coursepress_get_course( $request->course_id );
		if ( ! is_wp_error( $course ) ) {
			if ( $course->duplicate_course() ) {
				// Send success response back.
				wp_send_json_success();
			}
		}
		// Send error if failed.
		wp_send_json_error( array( 'message' => __( 'Oops! Could not duplicate the course.', 'cp' ) ) );
	}

	/**
	 * Activate plugin.
	 *
	 * @since 3.0.0
	 *
	 * @param object $request Request.
	 */
	public function activate_plugin( $request ) {
		if ( isset( $request->extension ) && isset( $request->nonce ) ) {
			$extensions = new CoursePress_Extension();
			$result = $extensions->activate( $request->extension, $request->nonce );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			} else {
				wp_send_json_success( $result );
			}
		}
		wp_send_json_error( array( 'message' => __( 'Oops! Could not activate plugin.', 'cp' ) ) );
	}

	/**
	 * Deactivate plugin.
	 *
	 * @since 3.0.0
	 *
	 * @param object $request Request.
	 */
	public function deactivate_plugin( $request ) {
		if ( isset( $request->extension ) && isset( $request->nonce ) ) {
			$extensions = new CoursePress_Extension();
			$result = $extensions->deactivate( $request->extension, $request->nonce );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			} else {
				wp_send_json_success( $result );
			}
		}
		wp_send_json_error( array( 'message' => __( 'Oops! Could not deativate plugin.', 'cp' ) ) );
	}
	public function dismiss_unit_help() {
		update_user_meta( get_current_user_id(), 'unit_help_dismissed', true );
		wp_send_json_success();
	}

	/**
	 * Upgrade course.
	 *
	 * @since 3.0.0
	 *
	 * @param object $request Request.
	 */
	public function upgrade_course( $request ) {
		global $cp_coursepress;
		if ( isset( $request->course_id ) ) {
			$upggrade = new CoursePress_Admin_Upgrade( $cp_coursepress );
			$result = $upggrade->upgrade_course_by_id( $request->course_id );
			if ( is_array( $result ) ) {
				wp_send_json_success( $result );
			}
		}
		wp_send_json_error( array( 'message' => __( 'Oops! Could not upgrade any course.', 'cp' ) ) );
	}
}
