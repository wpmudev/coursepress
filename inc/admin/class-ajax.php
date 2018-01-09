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
		add_action( 'wp_ajax_course_enroll_passcode', array( $this, 'enroll_with_passcode' ) );

		// Register user
		add_action( 'wp_ajax_nopriv_coursepress_register', array( $this, 'register_user' ) );
		// Update profile
		add_action( 'wp_ajax_coursepress_update_profile', array( $this, 'update_profile' ) );
		// Submit module
		add_action( 'wp_ajax_coursepress_submit', array( $this, 'validate_submission' ) );
		add_action( 'wp_ajax_nopriv_coursepress_submit', array( $this, 'validate_submission' ) );
		/**
		 * Search course
		 */
		add_action( 'wp_ajax_coursepress_courses_search', array( $this, 'search_course' ) );
	}

	/**
	 * Callback method to process ajax request.
	 * There's only 1 ajax request, each request differs and process base on the `action` param set.
	 * So if the request is `update_course` it's corresponding method will be `update_course`.
	 */
	function process_ajax_request() {
		$request = json_decode( file_get_contents( 'php://input' ) );
		$error = array( 'code' => 'cannot_process', 'message' => __( 'Something went wrong. Please try again.', 'cp' ) );
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
	 * Get the course units for editing
	 */
	function get_course_units() {
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
		$with_modules = filter_input( INPUT_GET, 'with_modules', FILTER_VALIDATE_INT );
		$wpnonce = filter_input( INPUT_GET, '_wpnonce' );
		$error = array( 'error_code' => 'cannot_get_units', 'message' => __( 'Something went wrong. Please try again.', 'cp' ) );

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

				$units[ $pos ] = $unit;
			}
		}

		wp_send_json_success( $units );
	}

	function update_course( $request ) {
		global $CoursePress_Core;

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

		if ( 'auto-draft' == $course_object['post_status'] ) {
			$course_object['post_status'] = 'draft';
		}

		if ( (int) $course_object['ID'] > 0 ) {
			$course_id = wp_update_post( $course_object );
		} else {
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

		return array( 'success' => true, 'ID' => $course_id, 'course' => $course );
	}

	function update_units( $request ) {
		if ( $request->units ) {
			$course_id = (int) $request->course_id;
			$units = $request->units;
			$menu_order = 0;
			$unit_ids = array();

			foreach ( $units as $cid => $unit ) {
				$unit->menu_order = $menu_order;

				// Get post object
				if ( ! empty( $unit->deleted ) ) {
					// Delete unit here
				    if ( ! empty( $unit->ID ) ) {
				    	coursepress_delete_unit( $unit->ID );
				    }
				    // Don't return the unit object
				    unset( $units->{$cid} );

				    continue;
			    }

				// Get post object
			    $unit_array = array(
			    	'ID' => $unit->ID,
				    'post_title' => $unit->post_title,
				    'post_content' => $unit->post_content,
				    'menu_order' => $menu_order,
				    'post_parent' => $course_id,
				    'post_status' => 'pending',
				    'post_type' => 'unit',
			    );

			    if ( ! empty( $unit->post_status ) ) {
			    	$unit_array['post_status'] = 'publish';
			    }

			    $metas = array();

			    foreach ( $unit as $key => $value ) {
			    	if ( preg_match( '%meta_%', $key ) ) {
					    $_key           = str_replace( 'meta_', '', $key );
					    $metas[ $_key ] = $value;
				    }
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
					    		foreach ( $step as $step_key => $step_value ) {
									if ( preg_match( '/^meta_((.+)\[view\d+\])$/', $step_key, $matches ) ) {
										unset( $step_metas[ $matches[1] ] );
										$step_metas[ $matches[2] ] = $step_value;
									}
								}
							    $stepId = coursepress_create_step( $step_array, $step_metas );
					    		$step_object = coursepress_get_course_step( $stepId );
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

						    $stepId = coursepress_create_step( $step_array, $step_metas );
						    $step_object = coursepress_get_course_step( $stepId );
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

		    wp_send_json_success( array( 'success' => true, 'units' => $units ) );
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
	function change_post( $request ) {
		if ( empty( $request->id ) || empty( $request->type ) || empty( $request->cp_action )
				|| !coursepress_is_type( $request->id, $request->type ) ) {
			return;
		}
		$result = coursepress_change_post( $request->id, $request->cp_action, $request->type );
		if ( $result ) {
			return array( 'success' => true );
		}
		return;
	}

	/**
	 * Update global settings.
	 *
	 * @param $request
	 * @return array
	 */
	function update_settings( $request ) {
		if ( $request ) {
			$request = get_object_vars( $request );
			$request = array_map( array( $this, 'to_array' ), $request );
		}

		coursepress_update_setting( true, $request );

		return array( 'success' => true );
	}

	function activate_marketpress() {
		global $CoursePress_Extension;

		if ( ! $CoursePress_Extension->is_plugin_installed( 'marketpress' ) ) {
			// Install MP then activate
		} elseif ( ! $CoursePress_Extension->is_plugin_active( 'marketpress/marketpress.php' ) ) {
			// Activate plugin
		}
	}

	/**
	 * Generate certificate for PREVIEW.
	 *
	 * @param $request
	 * @return array
	 */
	function preview_certificate( $request ) {
		global $CoursePress;

		$course_id = '';
		$pdf = $CoursePress->get_class( 'CoursePress_PDF' );

		if ( isset( $request->ID ) ) {
				$course_id = $request->ID;
			$content = $request->meta_basic_certificate_layout;
			$background = $request->meta_certificate_background;
			$margins = isset( $request->meta_cert_margin ) ? get_object_vars( $request->meta_cert_margin ) : array();
			$orientation = $request->meta_page_orientation;
			$text_color = $request->meta_cert_text_color;
			$logo_image = $request->meta_certificate_logo;
			$logo_positions = isset( $request->meta_certificate_logo_position ) ? get_object_vars( $request->meta_certificate_logo_position ) : array();
		} else {
			$content = $request->content;
			$background = $request->background_image;
			$margins = get_object_vars( $request->margin );
			$text_color = $request->cert_text_color;
			$orientation = $request->orientation;
			$logo_image = $request->certificate_logo;
			$logo_positions = get_object_vars( $request->certificate_logo_position );
		}

		$logo = array_merge(
			array( 'file' => $logo_image ),
			$logo_positions
		);
		$filename = 'cert-preview-' . $course_id . '.pdf';
		$date_format = apply_filters( 'coursepress_basic_certificate_date_format', get_option( 'date_format' ) );
		$content = apply_filters( 'coursepress_basic_certificate_html', $content, $course_id, get_current_user_id() );

		$vars = array(
			'FIRST_NAME' => __( 'Jon', 'CP_TD' ),
			'LAST_NAME' => __( 'Snow', 'CP_TD' ),
			'COURSE_NAME' => __( 'Example Course Title', 'CP_TD' ),
			'COMPLETION_DATE' => date_i18n( $date_format, $this->date_time_now() ),
			'CERTIFICATE_NUMBER' => uniqid( rand(), true ),
		);
		$content = $this->replace_vars( $content, $vars );
		$text_color = $this->convert_hex_color_to_rgb( $text_color, '#000000' );

		// Set PDF args
		$args = array(
			'title' => __( 'Course Completion Certificate', 'CP_TD' ),
			'orientation' => $orientation,
			'image' => $background,
			'filename' => $filename,
			'format' => 'F',
			'uid' => '12345',
			'margins' => apply_filters( 'coursepress_basic_certificate_margins', $margins ),
			'logo' => apply_filters( 'coursepress_basic_certificate_logo', $logo ),
			'text_color' => apply_filters( 'coursepress_basic_certificate_text_color', $text_color ),
		);

		$pdf->make_pdf( $content, $args );

		return array(
			'success' => true,
			'pdf' => $pdf->cache_url() . $filename,
		);
	}

	function upload_file() {
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

	function import_file( $files, $request ) {
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
				$courses = get_object_vars( $courses );
				$data['import_id'] = $import_id;
				$data['total_courses'] = count( $courses );
				foreach ( $courses as $course ) {
					$importClass = new CoursePress_Import( $course, $options );
				}
				wp_delete_attachment( $import_id );
				wp_send_json_success( $data );
			}
			wp_delete_attachment( $import_id );
		}
		wp_send_json_error();
	}

	function import_course( $request ) {

		$import_id = $request->import_id;
		$total_course = $request->total_courses;

		// Let's import the course one at a time to avoid over caps
		$courses = coursepress_get_option( $import_id );
		$courses = maybe_unserialize( $courses );
		$the_course = array_shift( $courses );

		$importClass = new CoursePress_Import( $the_course, $request );
	}

	/**
	 * Toggle course status.
	 *
	 * @param $request Request data.
	 */
	function course_status_toggle( $request ) {
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
			$error = array( 'error_code' => 'cannot_change_status', 'message' => __( 'Could not update course status.', 'cp' ) );
			wp_send_json_error( $error );
		}
	}

	/**
	 * Create new course category from text.
	 *
	 * @param object $request Request data.
	 */
	function create_course_category( $request ) {

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
	function send_email_invite( $request ) {

		// Do not continue if empty.
		if ( empty( $request->email ) || empty( $request->type ) || empty( $request->course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not send email invitation.', 'cp' ) ) );
		}

		$args = array(
			'email' => $request->email,
			'course_id' => $request->course_id,
			'first_name' => empty( $request->first_name ) ? '' : $request->first_name,
			'last_name' => empty( $request->last_name ) ? '' : $request->last_name,
		);

		// Send email invitation.
		if ( coursepress_send_email_invite( $args, $request->type ) ) {
			wp_send_json_success( array( 'message' => __( 'Invitation email has been sent.', 'cp' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Could not send email invitation.', 'cp' ) ) );
	}

	/**
	 * Assign instructor/facilitator to a course.
	 *
	 * @param object $request Request data.
	 */
	function assign_to_course( $request ) {

		// Do not continue if required values are empty.
		if ( empty( $request->course_id ) || empty( $request->user ) || empty( $request->type ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not assign selected user.', 'cp' ) ) );
		}

		switch ( $request->type ) {
			case 'instructor':
				$success = coursepress_add_course_instructor( $request->user, $request->course_id );
				break;

			case 'facilitator':
				$success = coursepress_add_course_facilitator( $request->user, $request->course_id );
				break;

			default:
				$success = false;
				break;
		}

		// If sent, send success response back.
		if ( $success ) {
			$user = $name = coursepress_get_user( $request->user );
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
	function remove_from_course( $request ) {

		// Do not continue if required values are empty.
		if ( empty( $request->course_id ) || empty( $request->user ) || empty( $request->type ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not remove the user.', 'cp' ) ) );
		}

		switch ( $request->type ) {
			case 'instructor':
				$success = coursepress_delete_course_instructor( $request->user, $request->course_id );
				break;

			case 'facilitator':
				$success = coursepress_remove_course_facilitator( $request->user, $request->course_id );
				break;

			default:
				$success = false;
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
	function get_course_users() {

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

	function import_sample_course( $request ) {
		global $CoursePress;

		$file = $request->meta_sample_course;
		$option_id = 'sample_' . $file;
		$data = array();
		$data['import_id'] = $option_id;

		// Let's check if the sample had previously use
		$courses = coursepress_get_option( $option_id );

		if ( empty( $courses ) ) {
			$filename = $CoursePress->plugin_path . 'assets/external/sample-courses/' . $file;
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

	function enroll() {
		$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
		$wpnonce = filter_input( INPUT_GET, '_wpnonce' );

		if ( ! $course_id || ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
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

	function enroll_with_passcode() {
		$course_id = filter_input( INPUT_POST, 'course_id', FILTER_VALIDATE_INT );
		$wpnonce = filter_input( INPUT_POST, '_wpnonce' );
		$passcode = filter_input( INPUT_POST, 'course_passcode' );

		if ( ! $course_id || ! wp_verify_nonce( $wpnonce, 'coursepress_nonce' ) ) {
			wp_send_json_error();
		}

		$course = coursepress_get_course( $course_id );

		if ( ! is_wp_error( $course ) ) {
			$course_passcode = $course->__get( 'enrollment_passcode' );

			if ( $course_passcode == trim( $passcode ) && coursepress_add_student( get_current_user_id(), $course_id ) ) {
				$redirect = $course->get_units_url();

				wp_safe_redirect( $redirect );
				exit;
			} else {
				coursepress_set_cookie( 'cp_incorrect_passcode', true, time() + HOUR_IN_SECONDS );
				$redirect = $course->get_permalink();

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
		coursepress_delete_student( $student_id, $course_id );
		$result = array( 'student_id' => $student_id );
		wp_send_json_success( $result );
	}

	function register_user() {}

	function update_profile() {
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

	function validate_submission() {
		$course_id = filter_input( INPUT_POST, 'course_id', FILTER_VALIDATE_INT );
		$unit_id = filter_input( INPUT_POST, 'unit_id', FILTER_VALIDATE_INT );
		$module_id = filter_input( INPUT_POST, 'module_id', FILTER_VALIDATE_INT );
		$step_id = filter_input( INPUT_POST, 'step_id', FILTER_VALIDATE_INT );
		$type = filter_input( INPUT_POST, 'type' );
		$user_id = get_current_user_id();
		$referer = filter_input( INPUT_POST, 'referer_url' );
		$redirect_url = filter_input( INPUT_POST, 'redirect_url' );
		$response = isset( $_POST['module'] ) ? $_POST['module'] : array();
		$user = coursepress_get_user( $user_id );
		if ( ! $user->is_enrolled_at( $course_id ) ) {
			// If user is not enrolled, don't validate
			wp_safe_redirect( $referer );
			exit;
		}
		$progress = $user->get_completion_data( $course_id );
		if ( (int) $step_id > 0 ) {
			$step = coursepress_get_course_step( $step_id );
			if ( ! empty( $response ) || 'fileupload' === $step->type || 'discussion' === $step->type ) {
				$progress = $step->validate_response( $response );
			}
		}
		$progress = $user->validate_completion_data( $course_id, $progress );
		//error_log(print_r($progress,true));
		$user->add_student_progress( $course_id, $progress );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Get the list of units and students based on selected course.
	 *
	 * @param object $request
	 */
	function get_notification_units_students( $request ) {
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
						'text' => $units->get_the_title(),
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
	function get_notification_students( $request ) {
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
	function send_notification_email( $request ) {
		global $CoursePress;

		// Check if required values are set.
		if ( empty( $request->content ) || empty( $request->title ) || empty( $request->students ) ) {
			wp_send_json_error();
		}
		$email = $CoursePress->get_class( 'CoursePress_Email' );

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
	function discussion_status_toggle( $request ) {
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
			$error = array( 'error_code' => 'cannot_change_status', 'message' => __( 'Could not update discussion status.', 'cp' ) );
			wp_send_json_error( $error );
		}
	}

	/**
	 * Toggle alert status.
	 *
	 * @param $request Request data.
	 */
	function alert_status_toggle( $request ) {
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
			$error = array( 'error_code' => 'cannot_change_status', 'message' => __( 'Could not update alert status.', 'cp' ) );
			wp_send_json_error( $error );
		}
	}

	/**
	 * Create new course alert.
	 *
	 * @param $request Request data.
	 */
	function create_course_alert( $request ) {

		$created = false;

		// If required values are set, create new alert.
		if ( ! empty( $request->course_id ) && ! empty( $request->title ) && ! empty( $request->content ) ) {
			$created = coursepress_create_course_alert( $request->course_id, $request->title, $request->content );
		}

		// If alert created return success response, else fail.
		if ( $created ) {
			$success = array( 'message' => __( 'New course alert created successfully.', 'cp' ) );
			wp_send_json_success( $success );
		} else {
			$error = array( 'message' => __( 'Could not create new course alert.', 'cp' ) );
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
				'button_text' => esc_html( $status? __( 'Approve', 'cp' ):__( 'Unapprove', 'cp' ) ),
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
	 * Get PDF report
	 */
	public function get_report_pdf( $request ) {
		global $CoursePress;
		$data = $CoursePress->get_class( 'CoursePress_Admin_Reports' );
		$content = $data->get_pdf_content( $request );
		if ( empty( $content ) ) {
			wp_send_json_error();
		}
		$pdf = $CoursePress->get_class( 'CoursePress_PDF' );
		$pdf->make_pdf( $content['content'], $content['args'] );
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
		$args = array(
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
			'email' => $request->email,
		);
		$send = coursepress_invite_student( $course_id, $args );
		if ( $send ) {
			wp_send_json_success( $send );
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
		if ( ! empty( $request->course_id && is_email( $request->email ) ) ) {
			$success = coursepress_remove_student_invite( $request->course_id, $request->email );
		}

		// Success resoponse with email.
		if ( $success ) {
			wp_send_json_success( $success );
		}

		wp_send_json_error( true );
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
			coursepress_add_student( $request->student_id, $request->course_id );
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
		wp_send_json_error( array( 'message' => __( 'Could not assign add student.', 'cp' ) ) );
	}

	public function courses_bulk_action( $request ) {
		if ( isset( $request->courses ) && is_array( $request->courses ) && isset( $request->which ) ) {
			foreach ( $request->courses as $course_id ) {
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
		foreach ( $request->students as $student_id ) {
			coursepress_delete_student( $student_id, $request->course_id );
		}
		return array( 'success' => true );
	}

	/**
	 * Duplicate single course and units.
	 *
	 * @param object $request Request.
	 */
	public function duplicate_course( $request ) {

		// We need course id.
		if ( ! isset( $request->course_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Oops! Could not duplicate the course.', 'cp' ) ) );;
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
}
