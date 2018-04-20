<?php
/**
 * CoursePress Import
 *
 * This import only works with CP export.
 *
 * @since 2.0
 **/
class CoursePress_Admin_Import extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_import';
	private static $start_time = 0;
	private static $current_time = 0;
	private static $time_limit_reached = false;
	protected $cap = 'coursepress_settings_cap';

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Import', 'coursepress' ),
			'menu_title' => __( 'Import', 'coursepress' ),
		);
	}

	/**
	 * Process the imported courses
	 *
	 * @since 2.0
	 **/
	public function process_form() {
		if ( $this->is_valid_page() ) {
			if ( empty( $_REQUEST['import_id'] ) ) {
				$file = $_FILES['import'];
				$is_replace = false;
				$with_students = false;
				$with_comments = false;

				if ( isset( $_REQUEST['coursepress'] ) ) {
					$options = $_REQUEST['coursepress'];
					$is_replace = ! empty( $options['replace'] );
					$with_students = ! empty( $options['students'] );
					$with_comments = ! empty( $options['comments'] ) && $with_students;
				}

				if ( empty( $file['error'] ) ) {
					$import = wp_import_handle_upload();
					$import_id = $import['id'];

					$filename = $import['file'];
					$file_content = file_get_contents( $filename );

					$courses = array();
					if ( preg_match( '%.json%', $filename ) ) {
						// Import file is json format!
						$courses = json_decode( $file_content );
					}
					/**
					 * Check $courses
					 */
					if ( empty( $courses ) ) {
						add_action( 'admin_notices', array( __CLASS__, 'import_failed_wrong_or_empty_file' ) );
						return;
					}
					self::course_importer( $courses, $import_id, $is_replace, $with_students, $with_comments );
				}
			} else {
				$user_id = get_current_user_id();
				$courses = get_option( 'coursepress_import_' . $user_id, array() );
				$is_replace = ! empty( $_REQUEST['replace'] );
				$with_students = ! empty( $_REQUEST['students'] );
				$with_comments = ! empty( $_REQUEST['comments'] ) && $with_students;

				if ( ! empty( $courses ) ) {
				    if ( is_array( $courses ) ) {
				        $courses = json_encode( $courses );
                    }
					$courses = json_decode( $courses );
					self::course_importer( $courses, $_REQUEST['import_id'], $is_replace, $with_students, $with_comments );
				} else {
					self::clear_courses();
				}
			}
		}
	}

	public static function clear_courses() {
		$user_id = get_current_user_id();

		// Delete the imported courses
		delete_option( 'coursepress_import_' . $user_id );

		// Notify user that import has completed
		add_action( 'admin_notices', array( __CLASS__, 'import_completed' ) );
	}

	/**
	 * Print fail import notice
	 **/
	public static function import_failed_wrong_or_empty_file() {
		printf( '<div class="notice notice-error"><p>%s</p></div>',
			__( 'Courses import fail. Wrong or empty file.', 'coursepress' )
		);
	}

	/**
	 * Print successful import notice
	 **/
	public static function import_completed() {
		printf( '<div class="notice notice-info is-dismissible"><p>%s</p></div>',
			__( 'Courses successfully imported!', 'coursepress' )
		);
	}

	/**
	 * Helper function to check memory limit
	 **/
	public static function check_memory() {
	    if ( is_multisite() ) {
	        return true;
        }

		$time_limit = (int) ini_get( 'max_execution_time' );
		$time_limit = $time_limit * 1000000;

		$time_now = microtime( true );
		$execution_limit = self::$start_time + $time_limit;

		// Less 6 seconds to avoid PHP warning error
		$execution_limit = $execution_limit - ( 1000000 * 6 );

		if ( $time_now >= $execution_limit ) {
			usleep( 2000 );
			return false;
		}

		return true;
	}

	/**
	 * Import courses
	 *
	 * @param (object)	$courses			The list of courses to import
	 * @param (int) $import_id				An import ID assigned to the uploaded file
	 * @param (bool) $replace				Whether to replace existing course or not.
	 * @param (bool) $with_students			Whether to import students of the course
	 * @param (bool) $with_comments         Whether to import comments of the course
	 **/
	public static function course_importer( $courses, $import_id, $replace, $with_students, $with_comments ) {
		if ( empty( $courses ) || ! is_object( $courses ) ) {
			return;
		}

		self::$start_time = microtime( true );
		$actions = array(
			'pre_post_update',
			'edit_post',
			'post_updated',
			'save_post',
			'wp_insert_post',
			'update_post_meta',
			'updated_post_meta',
			'comment_post',
			'wp_insert_comment',
		);
		$filters = array(
			'pre_user_login',
			'insert_user_meta',
			'profile_update',
			'user_register',
			'preprocess_comment',
		);

		// Remove all hooks
		array_map( 'remove_all_actions', $actions );
		array_map( 'remove_all_filters', $filters );

		foreach ( $courses as $course_id => $course ) {

			// Break the loop when max-execution time reached
			if ( false === self::check_memory() ) { break; }

			// Import course and author
			if ( is_object( $course->course ) ) {
				/**
				 * sanitize_course author
				 */
				$author_id = get_current_user_id();
				if ( isset( $course->author ) ) {
					$author_id = self::maybe_add_user( $course->author );
				}
				$course->course->post_author = $author_id;
				$new_course_id = self::_insert_post( $course->course, CoursePress_Data_Course::get_post_type_name(), $replace );
				$course->course = $new_course_id;
			} else {
				$new_course_id = $course->course;
			}

			if ( false === self::check_memory() ) { break; }

			// Import course meta
			if ( isset( $course->meta ) ) {
				self::insert_meta( $new_course_id, $course->meta );
				unset( $course->meta );
			}

			if ( false === self::check_memory() ) { break; }

			// Import course instructors
			if ( isset( $course->instructors ) ) {
				foreach ( $course->instructors as $instructor_id => $instructor ) {
					if ( false === self::check_memory() ) { break; }

					$user_id = self::maybe_add_user( $instructor );
					CoursePress_Data_Course::add_instructor( $new_course_id, $user_id );
					unset( $course->instructors->$instructor_id );
				}

				// If reached this far, remove intstructors
				unset( $course->instructors );
			}

			if ( false === self::check_memory() ) { break; }

			// Import course facilitators
			if ( isset( $course->facilitators ) ) {
				foreach ( $course->facilitators as $facilitator_id => $facilitator ) {
					if ( false === self::check_memory() ) { break; }

					$user_id = self::maybe_add_user( $facilitator );
					CoursePress_Data_Facilitator::add_course_facilitator( $new_course_id, $user_id );
					unset( $course->facilitators->$facilitator_id );
				}

				// If it reached this far, removed facilitators
				unset( $course->facilitators );
			}

			if ( false === self::check_memory() ) { break; }

			$course_settings = CoursePress_Data_Course::get_setting( $new_course_id );
			$visible_units = $preview_units = $visible_pages = $preview_pages = $visible_modules = $preview_modules = array();
			$setting_keys = array(
				'structure_visible_units',
				'structure_preview_units',
				'structure_visible_pages',
				'structure_preview_pages',
				'structure_visible_modules',
				'structure_preview_modules',
			);
			// Get structure values
			foreach ( $course_settings as $setting_key => $setting_value ) {
				if ( in_array( $setting_key, $setting_keys ) && ! empty( $setting_value ) ) {
					$key = str_replace( 'structure_', '', $setting_key );
					$$key = $setting_value;
				}
			}

			// Import units
			$new_units = array();
			if ( isset( $course->units ) ) {
				foreach ( $course->units as $unit_id => $unit ) {
					// Check memory
					if ( false === self::check_memory() ) { break; }

					if ( ! isset( $unit->unit_id ) ) {
						$the_unit = $unit->unit;
						$the_unit->post_parent = $new_course_id;
						$new_unit_id = self::_insert_post( $the_unit, CoursePress_Data_Unit::get_post_type_name(), $replace );
						$course->units->$unit_id->unit_id = $new_unit_id;
					} else {
						$new_unit_id = $unit->unit_id;
					}

					// Set new unit id
					$new_units[ $unit_id ] = array( 'new_unit_id' => $new_unit_id, 'modules' => array() );

					// Update visible units
					if ( isset( $visible_units[ $unit_id ] ) ) { $visible_units[ $new_unit_id ] = $visible_units[ $unit_id ]; }
					if ( isset( $preview_units[ $unit_id ] ) ) { $preview_units[ $new_unit_id ] = $preview_units[ $unit_id ]; }
					unset( $visible_units[ $unit_id ], $preview_units[ $unit_id ] );

					if ( false === self::check_memory() ) {	break; }

					if ( isset( $unit->meta ) ) {
						self::insert_meta( $new_unit_id, $unit->meta );
						unset( $course->units->$unit_id->meta );
					}

					if ( false === self::check_memory() ) { break; }

					if ( isset( $unit->pages ) ) {
						foreach ( $unit->pages as $page_number => $page ) {
							if ( false === self::check_memory() ) { break; }

							// Update visible pages
							$old_page_key = $unit_id . '_' . $page_number;
							$new_page_key = $new_unit_id . '_' . $page_number;
							if ( isset( $visible_pages[ $old_page_key ] ) ) { $visible_pages[ $new_page_key ] = $visible_pages[ $old_page_key ]; }
							if ( isset( $preview_pages[ $old_page_key ] ) ) { $preview_pages[ $new_page_key ] = $preview_pages[ $old_page_key ]; }
							unset( $visible_pages[ $old_page_key ], $preview_pages[ $old_page_key ] );

							if ( isset( $page->modules ) ) {

								foreach ( $page->modules as $module_id => $module ) {
									// Check memory
									if ( false === self::check_memory() ) { break; }

									if ( ! isset( $module->_module_id ) ) {
										$module_data = $module;
										$module_data->post_parent = $new_unit_id;
										$new_module_id = self::_insert_post( $module_data, CoursePress_Data_Module::get_post_type_name(), $replace );
										$module->_module_id = $new_module_id;
										$page->modules->$module_id = $module;
									} else {
										$new_module_id = $module->_module_id;
									}

									// Cache modules
									$new_units[ $unit_id ]['modules'][ $module_id ] = $new_module_id;

									// Update visible module
									$old_module_key = $unit_id . '_' . $page_number . '_' . $module_id;
									$new_module_key = $new_unit_id . '_' . $page_number . '_' . $new_module_id;

									$visible_modules[ $new_module_key ] = isset( $visible_modules[ $old_module_key ] ) ? $visible_modules[ $old_module_key ] : '';
									$preview_modules[ $new_module_key ] = isset( $preview_modules[ $old_module_key ] ) ? $preview_modules[ $old_module_key ] : '';

									if ( ! empty( $visible_modules[ $old_module_key ] ) ) {
										unset( $visible_modules[ $old_module_key ] );
									}

									if ( ! empty( $preview_modules[ $old_module_key ] ) ) {
										unset( $preview_modules[ $old_module_key ] );
									}

									if ( false === self::check_memory() ) { break; }

									if ( isset( $module->meta ) ) {
										self::insert_meta( $new_module_id, $module->meta );
										unset( $module->meta );
									}

									// If it reached this far, unset module
									unset( $page->modules->$module_id );
								}
							}

							// If it reached this far, remove the page
							unset( $unit->pages->$page_number );
						}
					}

					// If it reached this far, remove unit
					unset( $course->units->$unit_id );
				}
			}

			// Update course meta
			$course_settings = CoursePress_Helper_Utility::set_array_value( $course_settings, 'structure_visible_units', $visible_units );
			$course_settings = CoursePress_Helper_Utility::set_array_value( $course_settings, 'structure_preview_units', $preview_units );
			$course_settings = CoursePress_Helper_Utility::set_array_value( $course_settings, 'structure_visible_pages', $visible_pages );
			$course_settings = CoursePress_Helper_Utility::set_array_value( $course_settings, 'structure_preview_pages', $preview_pages );
			$course_settings = CoursePress_Helper_Utility::set_array_value( $course_settings, 'structure_visible_modules', $visible_modules );
			$course_settings = CoursePress_Helper_Utility::set_array_value( $course_settings, 'structure_preview_modules', $preview_modules );
			CoursePress_Data_Course::update_setting( $new_course_id, true, $course_settings );

			if ( false === self::check_memory() ) { break; }
			// Import course students
			if ( $with_students && isset( $course->students ) && is_object( $course->students ) ) {
				// Tell coursepress not to send enrollment notification
				add_filter( 'coursepress_notify_student', '__return_false' );

				foreach ( $course->students as $student_id => $student ) {
					if ( false === self::check_memory() ) { break; }

					$student_progress = array();

					if ( ! isset( $student->student_id ) ) {
						$student_data = $student;
						$student_progress = $student_data->progress;
						unset( $student_data->progress );
						$new_student_id = self::maybe_add_user( $student_data );
						$course->students->$student_id = array( 'student_id' => $new_student_id, 'progress' => $student_progress );
					} else {
						$new_student_id = $student->student_id;
					}

					if ( false === self::check_memory() ) { break; }

					// Remove enrollment restrictions
					remove_all_filters( 'coursepress_enroll_student' );

					// Enroll student
					CoursePress_Data_Course::enroll_student( $new_student_id, $new_course_id );

					if ( false === self::check_memory() ) { break; }

					if ( ! empty( $student_progress ) && $new_student_id > 0 ) {
						$student_progress = CoursePress_Helper_Utility::object_to_array( $student_progress );
						$student_progress = self::replace_student_progress( $student_progress, $new_units );
						if ( false === self::check_memory() ) { break; }
						CoursePress_Data_Student::update_completion_data( $new_student_id, $new_course_id, $student_progress );
						unset( $course->students->$student_id->progress );
					}
					unset( $courses->students->$student_id );

					if ( false === self::check_memory() ) { break; }
				}

				unset( $courses->students );
			}

			// Import course comments
			if ( $with_comments && isset( $course->comments ) && is_object( $course->comments ) && isset( $course->comments->course ) && is_array( $course->comments->course ) ) {

				$new_comments = array();

				foreach ( array_reverse( $course->comments->course ) as $comment_id => $comment ) {

					if ( false === self::check_memory() ) { break; }

					// Create user if needed and replace maybe-new user id
					$user_id = self::maybe_add_user( $comment->user );
					$comment->user_id = $user_id;

					// Replace comment post id with new course id
					$comment->comment_post_ID = $new_course_id;

					// Replace comment parent id with new id
					if ( $comment->comment_parent !== '0' && isset( $new_comments[ $comment->comment_parent ] ) ) {
						$comment->comment_parent = $new_comments[ $comment->comment_parent ];
					}

					// Insert comment
					$new_comments[ $comment->comment_ID ] = wp_insert_comment( CoursePress_Helper_Utility::object_to_array( $comment ) );

					unset( $course->comments->course->$comment_id );

					if ( false === self::check_memory() ) { break; }
				}
				unset( $course->comments->course );
			}

			// Import module comments
			if ( $with_comments && isset( $course->comments ) && is_object( $course->comments ) && isset( $course->comments->modules ) && is_object( $course->comments->modules ) && isset( $course->comments->modules ) ) {

				$new_comments = array();

				foreach ( $course->comments->modules as $module_id => $modules ) {

					if ( false === self::check_memory() ) { break; }

					foreach ( array_reverse( $modules ) as $comment_id => $comment ) {

						// Replace comment post id with new module id
						if ( isset( $new_units[ $comment->unit_id ] )
							&& is_array( $new_units[ $comment->unit_id ]['modules'] )
							&& isset( $new_units[ $comment->unit_id ]['modules'][ $comment->module_id ] ) ) {
							 $comment->comment_post_ID = $new_units[ $comment->unit_id ]['modules'][ $comment->module_id ];
						}

						// Replace comment parent id with new id
						if ( $comment->comment_parent !== '0' && isset( $new_comments[ $comment->comment_parent ] ) ) {
							$comment->comment_parent = $new_comments[ $comment->comment_parent ];
						}

						if ( false === self::check_memory() ) { break; }

						// Create user if needed and replace maybe-new user id
						$user_id = self::maybe_add_user( $comment->user );
						$comment->user_id = $user_id;

						// Insert comment
						$new_comments[ $comment->comment_ID ] = wp_insert_comment( CoursePress_Helper_Utility::object_to_array( $comment ) );

						if ( false === self::check_memory() ) { break; }
					}
				}
				unset( $new_comments );
			}

			unset( $course->comments->modules );

			// If it reached this far, remove the course

			unset( $courses->{$course_id} );
		}

		// Save the remaining courses to db
		$courses = CoursePress_Helper_Utility::object_to_array( $courses );
		$courses = array_filter( $courses );

		if ( ! empty( $courses ) ) {
			$user_id = get_current_user_id();
			update_option( 'coursepress_import_' . $user_id, $courses );

			// Reload the page
			$url_args = array(
				'coursepress_import' => wp_create_nonce( 'coursepress_import' ),
				'reload' => true,
				'import_id' => $import_id,
			);

			if ( $replace ) {
				$url_args['replace'] = true;
			}

			if ( $with_students ) {
				$url_args['students'] = true;
			}

			$reload_url = add_query_arg( $url_args );
			wp_safe_redirect( $reload_url );
		} else {
			self::clear_courses();
		}
	}

	/**
	 * Helper function to insert courses, units, and/or modules
	 *
	 * @param (array|object) $post				The post to insert in DB
	 * @param (string) $post_type				The type of post to insert to ie. course, unit, module
	 * @param (boolean)	$replace				Whethere to replace the post if a match is found.
	 **/
	public static function _insert_post( $post, $post_type, $replace = false ) {
		global $wpdb;

		$new_post_id = 0;

		if ( $replace ) {
			$post_title = $post->post_title;

			// We'll use custom SQL to get existing post
			$sql = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE `post_title`='%s' AND post_type='%s' LIMIT 0, 1", $post_title, $post_type );
			$new_post_id = $wpdb->get_var( $sql );
			$new_post_id = max( 0, (int) $new_post_id );
		}

		$post->ID = $new_post_id;
		unset( $post->guid );
		$post = get_object_vars( $post );

		$new_post_id = $new_post_id > 0 ? wp_update_post( $post ) : wp_insert_post( $post );

		return $new_post_id;
	}

	/**
	 * Helper function to insert post_meta
	 *
	 * @param (int) $post_id				The post ID to insert the metas.
	 * @param (array|object) $metas			The metadata to insert.
	 **/
	public static function insert_meta( $post_id, $metas = array() ) {
		$metas = CoursePress_Helper_Utility::object_to_array( $metas );
		foreach ( $metas as  $key => $values ) {
			$values = array_map( 'maybe_unserialize', $values );
			if ( is_array( $values ) ) {
				foreach ( $values as $value ) {
					$value = maybe_unserialize( $value );
					add_post_meta( $post_id, $key, $value );
				}
			} else {
				add_post_meta( $post_id, $key, $values );
			}
		}
	}

	/**
	 * Helper function to get or insert new user
	 *
	 * @param (object)	$user_data
	 **/
	public static function maybe_add_user( $user_data ) {
		$add = true;

		if ( ! empty( $user_data->user_email ) && email_exists( $user_data->user_email ) ) {
			$add = false;
			$user = get_user_by( 'email', $user_data->user_email );
		}
		if ( ! empty( $user_data->user_login ) && username_exists( $user_data->user_login ) ) {
			$add = false;
			$user = get_user_by( 'login', $user_data->user_login );
		}

		if ( $add || empty( $user ) ) {
			if ( empty( $user_data ) ) {
				return get_current_user_id();
			}
			// User doesn't exist, insert
			unset( $user_data->ID );
			$user_id = wp_insert_user( get_object_vars( $user_data ) );
			if ( ! is_wp_error( $user_id ) ) {
				return $user_id;
			}
		} else {
			return $user->ID;
		}

		return 0;
	}

	/**
	 * Helper function to replace unit and module IDs to newly created unit and modules ID.
	 **/
	public static function replace_student_progress( $student_progress, $new_units ) {
		foreach ( $new_units as $unit_id => $unit ) {
			$new_unit_id = $unit['new_unit_id'];
			$unit_data = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'units/' . $unit_id
			);
			$unit_completion = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/' . $unit_id
			);

			if ( ! empty( $unit['modules'] ) ) {
				foreach ( $unit['modules'] as $module_id => $new_module_id ) {
					$module_progress = CoursePress_Helper_Utility::get_array_val(
						$unit_data,
						'responses/' . $module_id
					);

					if ( ! empty( $module_progress ) ) {
						$unit_data = CoursePress_Helper_Utility::set_array_value(
							$unit_data,
							'responses/' . $new_module_id,
							$module_progress
						);
					}

					$module_seen = CoursePress_Helper_Utility::get_array_val(
						$unit_completion,
						'modules_seen/' . $module_id
					);
					if ( ! empty( $module_seen ) ) {
						$unit_completion = CoursePress_Helper_Utility::set_array_value(
							$unit_completion,
							'modules_seen/' . $new_module_id,
							1
						);
						$unit_completion = CoursePress_Helper_Utility::unset_array_value(
							$unit_completion,
							'modules_seen/' . $module_id
						);
					}
					$module_answered = CoursePress_Helper_Utility::get_array_val(
						$unit_completion,
						'answered/' . $module_id
					);
					if ( ! empty( $module_answered ) ) {
						$unit_completion = CoursePress_Helper_Utility::set_array_value(
							$unit_completion,
							'answered/' . $new_module_id,
							1
						);
						$unit_completion = CoursePress_Helper_Utility::unset_array_value(
							$unit_completion,
							'answered/' . $module_id
						);
					}
				}
			}

			if ( ! empty( $unit_data ) ) {
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'units/' . $new_unit_id,
					$unit_data
				);
				$student_progress = CoursePress_Helper_Utility::unset_array_value(
					$student_progress,
					'units/' . $unit_id
				);
			}
			if ( ! empty( $unit_completion ) ) {
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'completion/' . $new_unit_id,
					$unit_completion
				);
				$student_progress = CoursePress_Helper_Utility::unset_array_value(
					$student_progress,
					'completion/' . $unit_id
				);
			}
		}

		return $student_progress;
	}
}
