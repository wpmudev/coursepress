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
			'title' => __( 'CoursePress Import', 'cp' ),
			'menu_title' => __( 'Import', 'cp' ),
		);
	}

	/**
	 * Process the imported courses
	 *
	 * @since 2.0
	 **/
	public function process_form() {
		if ( $this->is_valid_page() ) {
			if ( ! isset( $_REQUEST['import_id'] ) ) {
				$file = $_FILES['import'];
				$is_replace = false;
				$with_students = false;

				if ( isset( $_REQUEST['coursepress'] ) ) {
					$options = $_REQUEST['coursepress'];
					$is_replace = ! empty( $options['replace'] );
					$with_students = ! empty( $options['students'] );
				}

				if ( empty( $file['error'] ) ) {
					$import = wp_import_handle_upload();
					$import_id = $import['id'];

					$filename = $import['file'];
					$courses = file_get_contents( $filename );

					if ( preg_match( '%.json%', $filename ) ) {
						// Import file is json format!
						$courses = json_decode( $courses );
					}

					self::course_importer( $courses, $import_id, $is_replace, $with_students );
				}
			} else {
				$user_id = get_current_user_id();
				$courses = get_option( 'coursepress_import_' . $user_id, array() );
				$is_replace = ! empty( $_REQUEST['replace'] );
				$with_students = ! empty( $_REQUEST['students'] );

				if ( ! empty( $courses ) ) {
					$courses = (object) $courses;
					self::course_importer( $courses, $_REQUEST['import_id'], $is_replace, $with_students );
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
	 * Print successful import notice
	 **/
	public static function import_completed() {
		printf( '<div class="notice notice-info is-dismissible"><p>%s</p></div>',
			__( 'Courses successfully imported!', 'cp' )
		);
	}

	/**
	 * Helper function to check memory limit
	 **/
	public static function check_memory() {
		$time_limit = (int) ini_get( 'max_execution_time' );
		$time_limit = $time_limit * 1000000;

		$time_now = microtime(true);
		$execution_limit = self::$start_time + $time_limit;

		// Less 6 seconds to avoid PHP warning error
		$execution_limit = $execution_limit - ( 1000000 * 6 );

		if ( $time_now >= $execution_limit ) {
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
	 **/
	public static function course_importer( $courses, $import_id, $replace, $with_students ) {
		self::$start_time = microtime(true);

		foreach ( $courses as $course_id => $course ) {

			// Break the loop when max-execution time reached
			if ( false === self::check_memory() ) { break; }

			// Import course and author
			if ( is_object( $course->course ) ) {
				$author_id = self::maybe_add_user( $course->author );
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

			// Import course students
			if ( $with_students && isset( $course->students ) && is_object( $course->students ) ) {
				foreach ( $course->students as $student_id => $student ) {
					if ( false === self::check_memory() ) { break; }

					if ( ! isset( $student->student_id ) ) {
						$student_data = $student;
						unset( $student_data->progress );
						$new_student_id = self::maybe_add_user( $student_data );
						$course->students->$student_id->student_id = $new_student_id;
					} else {
						$new_student_id = $student->student_id;
					}

					if ( false === self::check_memory() ) { break; }

					// Enroll student
					CoursePress_Data_Course::enroll_student( $new_student_id, $new_course_id );

					if ( false === self::check_memory() ) { break; }

					if ( isset( $student->progress ) ) {
						$student_progress = get_object_vars( $student->student_progress );
						CoursePress_Data_Student::update_completion_data( $new_student_id, $new_course_id, maybe_unserialize( $student_progress ) );
						unset( $course->students->$student_id->progress );
					}

					unset( $courses->students->$student_id );
				}

				unset( $courses->students );
			}

			if ( false === self::check_memory() ) { break; }

			$visible_units = CoursePress_Data_Course::get_setting( $new_course_id, 'structure_visible_units', array() );
			$preview_units = CoursePress_Data_Course::get_setting( $new_course_id, 'structure_preview_units', array() );
			$visible_pages = CoursePress_Data_Course::get_setting( $new_course_id, 'structure_visible_pages', array() );
			$preview_pages = CoursePress_Data_Course::get_setting( $new_course_id, 'structure_preview_pages', array() );
			$visible_modules = CoursePress_Data_Course::get_setting( $new_course_id, 'structure_visible_modules', array() );
			$preview_modules = CoursePress_Data_Course::get_setting( $new_course_id, 'structure_preview_modules', array() );

			// Import units
			if ( isset( $course->units ) ) {
				foreach ( $course->units as $unit_id => $unit ) {
					// Check memory
					if ( false === self::check_memory() ) { break; }

					if ( ! isset( $unit->unit_id ) ) {
						$the_unit = $unit->unit;
						$the_unit->post_parent = $new_course_id;
						$new_unit_id = self::_insert_post( $the_unit, CoursePress_Data_Unit::get_post_type_name() );
						$course->units->$unit_id->unit_id = $new_unit_id;
					} else {
						$new_unit_id = $unit->unit_id;
					}

					// Update visible units
					$visible_units[$new_unit_id] = $visible_units[$unit_id];
					$preview_units[$new_unit_id] = $preview_units[$unit_id];
					unset( $visible_units[$unit_id], $preview_units[$unit_id] );

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
							$visible_pages[$new_page_key] = $visible_pages[$old_page_key];
							$preview_pages[$new_page_key] = $preview_pages[$old_page_key];
							unset( $visible_pages[$old_page_key], $preview_pages[$old_page_key] );

							if ( isset( $page->modules ) ) {

								foreach ( $page->modules as $module_id => $module ) {
									// Check memory
									if ( false === self::check_memory() ) { break; }

									if ( ! isset( $module->_module_id ) ) {
										$module_data = $module;
										$module_data->post_parent = $new_unit_id;
										$new_module_id = self::_insert_post( $module_data, CoursePress_Data_Module::get_post_type_name() );
										$module->_module_id = $new_module_id;
										$page->modules->$module_id = $module;
									} else {
										$new_module_id = $module->_module_id;
									}

									// Update visible module
									$old_module_key = $unit_id . '_' . $page_number . '_' . $module_id;
									$new_module_key = $new_unit_id . '_' . $page_number . '_' . $new_module_id;

									$visible_modules[$new_module_key] = isset( $visible_modules[$old_module_key] ) ? $visible_modules[$old_module_key] : '';
									$preview_modules[$new_module_key] = isset( $preview_modules[$old_module_key] ) ? $preview_modules[$old_module_key] : '';

									if ( ! empty( $visible_modules[$old_module_key] ) ) {
										unset( $visible_modules[$old_module_key] );
									}

									if ( ! empty( $preview_modules[$old_module_key] ) ) {
										unset( $preview_modules[$old_module_key] );
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
			CoursePress_Data_Course::update_setting( $new_course_id, 'structure_visible_units', $visible_units );
			CoursePress_Data_Course::update_setting( $new_course_id, 'structure_preview_units', $preview_units );
			CoursePress_Data_Course::update_setting( $new_course_id, 'structure_visible_pages', $visible_pages );
			CoursePress_Data_Course::update_setting( $new_course_id, 'structure_preview_pages', $preview_pages );
			CoursePress_Data_Course::update_setting( $new_course_id, 'structure_visible_modules', $visible_modules );
			CoursePress_Data_Course::update_setting( $new_course_id, 'structure_preview_modules', $preview_modules );

			// If it reached this far, remove the course
			unset( $courses->$course_id );
		}

		// Save the remaining courses to db
		$courses = get_object_vars( $courses );
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
			$new_post_id = max(0, (int) $new_post_id );
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
		$user = get_user_by( 'email', $user_data->user_email );

		if ( is_wp_error( $user ) ) {
			// User doesn't exist, insert
			unset( $user_data->ID );
			$user_id = wp_insert_user( get_object_vars( $user_data ) );
		} else {
			$user_id = $user->ID;
		}

		return $user_id;
	}
}