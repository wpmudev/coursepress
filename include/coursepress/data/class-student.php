<?php

class CoursePress_Data_Student {

	/**
	 * Filters through student meta to return only the course IDs.
	 *
	 * @uses Student::filter_course_meta_array() to filter the meta array
	 *
	 * @param $user_id
	 *
	 * @return array|mixed
	 */
	public static function get_course_enrollment_meta( $user_id ) {
		$course_ids = array();
		$meta = get_user_meta( $user_id );

		if ( $meta ) {

			// We only want to parse/return the meta-key; we ignore values.
			$meta_keys = array_filter(
				array_keys( $meta ),
				array( __CLASS__, 'filter_course_meta_array' )
			);

			// Convert the meta-key to a numeric course_id.
			$course_ids = array_map(
				array( __CLASS__, 'course_id_from_meta' ),
				$meta_keys
			);
		}

		return $course_ids;
	}

	/**
	 * Filters through student meta.
	 *
	 * @uses Student::course_id_from_meta()
	 *
	 * @return mixed
	 */
	public static function filter_course_meta_array( $var ) {
		$course_id_from_meta = self::course_id_from_meta( $var );
		if ( ! empty( $course_id_from_meta ) ) {
			return $var;
		}

		return false;
	}

	/**
	 * Extracts the correct Course ID from the meta.
	 *
	 * Makes sure that the correct ID gets returned from the correct blog
	 * regardless of single- or multisite.
	 *
	 * @param $meta_value
	 *
	 * @return bool|mixed
	 */
	public static function course_id_from_meta( $meta_value ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$base_prefix = $wpdb->base_prefix;
		$current_blog = str_replace( '_', '', str_replace( $base_prefix, '', $prefix ) );
		if ( is_multisite() && empty( $current_blog ) && defined( 'BLOG_ID_CURRENT_SITE' ) ) {
			$current_blog = BLOG_ID_CURRENT_SITE;
		}

		if ( preg_match( '/enrolled\_course\_date\_/', $meta_value ) ) {

			if ( preg_match( '/^' . $base_prefix . '/', $meta_value ) ) {

				// Get the blog ID that this meta key belongs to
				$blog_id = '';
				preg_match( '/(?<=' . $base_prefix . ')\d*/', $meta_value, $blog_id );
				$blog_id = $blog_id[0];

				// First site...
				if ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == $current_blog ) {
					$blog_id = $current_blog;
					$course_id = str_replace( $base_prefix . 'enrolled_course_date_', '', $meta_value );
				} else {
					$course_id = str_replace( $base_prefix . $blog_id . '_enrolled_course_date_', '', $meta_value );
				}

				// Only for current site...
				if ( $current_blog != $blog_id ) {
					return false;
				}
			} else {
				// old style, but should support it at least in the listings
				$course_id = str_replace( 'enrolled_course_date_', '', $meta_value );
			}

			if ( ! empty( $course_id ) ) {
				return $course_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public static function count_enrolled_courses_ids( $student_id, $refresh = false ) {
		$count = get_user_meta( $student_id, 'cp_course_count', true );

		if ( ! $count || $refresh ) {
			global $wpdb;

			$meta_keys = $wpdb->get_results(
				$wpdb->prepare( "SELECT `meta_key` FROM $wpdb->usermeta WHERE `meta_key` LIKE 'enrolled_course_class%%' AND `user_id`=%d", $student_id )
			, ARRAY_A);

			if ( $meta_keys ) {
				$meta_keys = array_map(
					array( __CLASS__, 'meta_key' ),
					$meta_keys
				);
				$meta_keys = array_unique( $meta_keys );

				$count = count( $meta_keys );

				// Save counted courses.
				update_user_meta( $student_id, 'cp_course_count', $count );
			}
		}

		return $count;
	}

	public static function meta_key( $key ) {
		return $key['meta_key'];
	}

	/**
	 * Get the IDs of enrolled courses.
	 *
	 * @uses Student::get_course_enrollment_meta()
	 * @param  int $student_id WP User ID.
	 * @return array Contains enrolled course IDs.
	 */
	public static function get_enrolled_courses_ids( $student_id ) {
		return self::get_course_enrollment_meta( $student_id );
	}

	/**
	 * Get the IDs of enrolled courses.
	 *
	 * @uses Student::get_course_enrollment_meta()
	 * @param  int $student_id WP User ID.
	 * @param  int $course_id The course ID to check.
	 * @return bool
	 */
	public static function is_enrolled_in_course( $student_id, $course_id ) {
		$enrolled = self::get_enrolled_courses_ids( $student_id );
		return in_array( $course_id, $enrolled );
	}

	/**
	 * Updates a student's data.
	 *
	 * @param $student_data
	 *
	 * @return bool
	 */
	public static function update_student_data( $student_id, $student_data ) {
		if ( ! isset( $student_data['ID'] ) ) {
			$student_data['ID'] = $student_id;
		}
		$student_data = apply_filters( 'coursepress_student_update_data', $student_data );
		if ( wp_update_user( $student_data ) ) {

			/**
			 * Perform action after a Student object is updated.
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_student_updated', $student_id );

			return true;
		} else {
			return false;
		}
	}

	public static function init_completion_data( $student_id, $course_id ) {
		$data = array();
		CoursePress_Helper_Utility::set_array_val( $data, 'version', '2.0' );

		self::update_completion_data( $student_id, $course_id, $data );

		return $data;
	}

	public static function get_completion_data( $student_id, $course_id ) {

		if ( ! function_exists( 'get_userdata' ) ) {
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}

		$data = get_user_option( 'course_' . $course_id . '_progress', $student_id );

		if ( empty( $data ) ) {
			$data = self::init_completion_data( $student_id, $course_id );
		}

		return $data;
	}

	public static function update_completion_data( $student_id, $course_id, $data ) {

		$global_setting = ! is_multisite();
		update_user_option( $student_id, 'course_' . $course_id . '_progress', $data, $global_setting );

	}

	public static function visited_page( $student_id, $course_id, $unit_id, $page, &$data = false ) {

		if ( empty( $data ) ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/visited_pages/' . $page, $page );
		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/last_visited_page', $page );
		self::update_completion_data( $student_id, $course_id, $data );

		return $data;

	}

	public static function visited_module( $student_id, $course_id, $unit_id, $module_id, &$data = false ) {

		if ( empty( $data ) ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		CoursePress_Helper_Utility::set_array_val( $data, 'completion/' . $unit_id . '/modules_seen/' . $module_id, true );
		self::update_completion_data( $student_id, $course_id, $data );

		return $data;

	}

	public static function module_response( $student_id, $course_id, $unit_id, $module_id, $response, &$data = false ) {

		$attributes = CoursePress_Data_Module::attributes( $module_id );

		if ( empty( $attributes ) || 'output' === $attributes['mode'] ) {
			return;
		}

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$grade = - 1;

		// Auto-grade the easy ones
		switch ( $attributes['module_type'] ) {
			case 'input-checkbox':
				$total = count( $attributes['answers_selected'] );
				$correct = 0;
				if ( is_array( $response ) ) {
					foreach ( $response as $answer ) {
						if ( in_array( $answer, $attributes['answers_selected'] ) ) {
							$correct += 1;
						}
					}
				}

				$grade = (int) ( $correct / $total * 100 );
				break;

			case 'input-select':
			case 'input-radio':
				if ( $response == $attributes['answers_selected'] ) {
					$grade = 100;
				} else {
					$grade = 0;
				}
				break;

			case 'input-quiz':
				$result = CoursePress_Data_Module::get_quiz_results(
					$student_id,
					$course_id,
					$unit_id,
					$module_id,
					$response,
					$data
				);
				$grade = $result['grade'];
				break;
			case 'input-upload':
				if ( ! empty( $response['file'] ) ) {
					$grade = $attributes['minimum_grade'];
				}
				break;
		}

		$grade = apply_filters(
			'coursepress_autograde_module_response',
			$grade,
			$module_id,
			$student_id
		);

		$grade_data = array(
			'graded_by' => (-1 == $grade ? '' : 'auto'),
			'grade' => $grade,
			'date' => (-1 == $grade ? '' : current_time( 'mysql' ) ),
		);

		$response_data = array(
			'response' => $response,
			'date' => current_time( 'mysql' ),
			'grades' => (-1 == $grade ? array() : array( $grade_data ) ),
			'feedback' => array(),
		);

		if ( isset( $attributes['mandatory'] ) && $attributes['mandatory'] ) {
			$key = 'completion/' . $unit_id . '/completed_mandatory';
			$mandatory = (int) CoursePress_Helper_Utility::get_array_val( $data, $key );
			CoursePress_Helper_Utility::set_array_val( $data, $key, $mandatory + 1 );
		}

		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/responses/' . $module_id . '/', $response_data );
		self::get_calculated_completion_data( $student_id, $course_id, $data );
		self::update_completion_data( $student_id, $course_id, $data );

		// Might as well do it on an AJAX call to make the experience a bit better.
		//self::calculate_completion( $student_id, $course_id );

		return $data;

	}

	public static function get_responses( $student_id, $course_id, $unit_id, $module_id, $response_only = false, &$data = false ) {

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$responses = CoursePress_Helper_Utility::get_array_val( $data, 'units/' . $unit_id . '/responses/' . $module_id );

		// Don't return the dates
		if ( $response_only ) {

			$result = array();
			if ( ! empty( $responses ) ) {
				foreach ( $responses as $key => $r ) {
					$result[ $key ] = $r['response'];
				}
			}

			return $result;

		}

		return empty( $responses ) ? array() : $responses;

	}

	public static function get_grade(
		$student_id, $course_id, $unit_id, $module_id, $response_index = false, $grade_index = false, &$data = false
	) {
		$grade = false;

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$response = self::get_response(
			$student_id,
			$course_id,
			$unit_id,
			$module_id,
			$response_index,
			$data
		);
		if ( empty( $response ) ) {
			$response = array();
		}

		if ( ! isset( $response['grades'] ) ) {
			$response['grades'] = array();
		}

		// Get last grade.
		$last_grade = ( count( $response['grades'] ) - 1 );

		if ( ! $grade_index || $grade_index > $last_grade ) {
			$grade_index = $last_grade;
		}

		if ( isset( $response['grades'][ $grade_index ] ) ) {
			$grade = $response['grades'][ $grade_index ];

			if ( empty( $grade['grade'] ) && 0 != $grade['grade'] ) {
				$grade['grade'] = -1;
			}
			$grade['grade'] = (int) $grade['grade'];
		}

		return $grade;
	}

	public static function record_grade(
		$student_id, $course_id, $unit_id, $module_id, $grade, $response_index = false, &$data = false
	) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$responses = CoursePress_Helper_Utility::get_array_val(
			$data,
			'units/' . $unit_id . '/responses/' . $module_id
		);

		if ( empty( $responses ) ) {
			$responses = array();
			CoursePress_Helper_Utility::set_array_val(
				$data,
				'units/' . $unit_id . '/responses/' . $module_id,
				$responses
			);

		}

		// Get last grade
		if ( ! $response_index ) {
			$response_index = ( count( $responses ) - 1 );

			if ( $response_index < 0 ) $response_index = 0;
		}

		$grade_data = array(
			'graded_by' => get_current_user_id(),
			'grade' => (int) $grade,
			'date' => current_time( 'mysql' ),
		);

		CoursePress_Helper_Utility::set_array_val(
			$data,
			'units/' . $unit_id . '/responses/' . $module_id . '/' . $response_index . '/grades/',
			$grade_data
		);

		self::get_calculated_completion_data( $student_id, $course_id, $data );
//		self::update_completion_data( $student_id, $course_id, $data );
		return $data;
	}

	public static function get_response(
		$student_id, $course_id, $unit_id, $module_id, $response_index = false, &$data = false
	) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$responses = CoursePress_Helper_Utility::get_array_val(
			$data,
			'units/' . $unit_id . '/responses/' . $module_id
		);

		// Get last grade
		if ( ! $response_index ) {
			$response_index = ( count( $responses ) - 1 );
		}

		return ! empty( $responses ) && isset( $responses[ $response_index ] ) ? $responses[ $response_index ] : false;
	}

	public static function get_feedback(
		$student_id, $course_id, $unit_id, $module_id, $response_index = false, $feedback_index = false, &$data = false
	) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$response = self::get_response(
			$student_id,
			$course_id,
			$unit_id,
			$module_id,
			$response_index,
			$data
		);
		$feedback = isset( $response['feedback'] ) ? $response['feedback'] : array();

		// Get last grade
		if ( ! $feedback_index ) {
			$feedback_index = ( count( $feedback ) - 1 );
		}

		return ! empty( $feedback ) && isset( $feedback[ $feedback_index ] ) ? $feedback[ $feedback_index ] : false;
	}

	public static function record_feedback(
		$student_id, $course_id, $unit_id, $module_id, $feedback_new, $response_index = false, &$data = false, $is_draft = false
	) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$responses = CoursePress_Helper_Utility::get_array_val(
			$data,
			'units/' . $unit_id . '/responses/' . $module_id
		);

		// Get last grade
		if ( ! $response_index ) {
			$response_index = ( count( $responses ) - 1 );
		}

		$feedback_data = array(
			'feedback_by' => get_current_user_id(),
			'feedback' => CoursePress_Helper_Utility::filter_content( $feedback_new ),
			'date' => current_time( 'mysql' ),
			'draft' => $is_draft,
		);

		CoursePress_Helper_Utility::set_array_val(
			$data,
			'units/' . $unit_id . '/responses/' . $module_id . '/' . $response_index . '/feedback/',
			$feedback_data
		);

		self::update_completion_data( $student_id, $course_id, $data );

		return $data;
	}

	public static function get_calculated_completion_data( $student_id, $course_id, &$student_progress = false ) {

		if ( ! $student_progress ) {
			$student_progress = self::get_completion_data( $student_id, $course_id );
		}

		$student_units = ! empty( $student_progress['units'] ) ? array_keys( $student_progress['units'] ) : array();

		if ( empty( $student_units ) ) {
			return $student_progress;
		}

		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );

		$is_done = CoursePress_Helper_Utility::get_array_val(
			$student_progress,
			'completion/completed'
		);

		$previous_unit_id = false;
		$unit_count = 0;
		$unit_completed = 0;
		$course_progress = 0;
		$total_course_grade = 0;
		$course_gradable_modules = 0;
		$course_grade = 0;
		$valid = true;
		$course_mandatory_steps = 0;
		$course_completed_mandatory_steps = 0;

		foreach ( $units as $unit_id => $unit ) {
			$unit_count += 1;
			$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $unit_id, $previous_unit_id );
			$force_current_unit_successful_completion = get_post_meta( $unit_id, 'force_current_unit_successful_completion', true );
			$previous_unit_id = $unit_id;

			$unit_total_modules = 0;
			$unit_required_modules = 0;
			$unit_assessable_modules = 0;
			$unit_completed_modules = 0;
			$unit_completed_required_modules = 0;
			$unit_completed_assessable_modules = 0;
			$total_valid_items = 0;
			$valid_items = 0;
			$unseen_modules = array();
			$last_seen_index = 0;
			$index = 0;
			$unit_grade = 0;
			$unit_gradable_modules = 0;
			$unit_passing_grade = 0;

			if ( false === $is_unit_available ) {
				// Let's not check unavailable unit
				continue;
			}

			if ( ! empty( $unit['pages'] ) ) {
				foreach ( $unit['pages'] as $page_number => $modules ) {
					$seen_modules = 0;

					// Include pages only that is set to be visible to avoid progress rate confusion
					$is_page_structure_visible = CoursePress_Data_Unit::is_page_structure_visible( $course_id, $unit_id, $page_number, $student_id );

					if ( $is_page_structure_visible ) {
						//$total_valid_items += 1;
					}

					if ( ! empty( $modules['modules'] ) ) {
						foreach ( $modules['modules'] as $module_id => $module ) {
							$attributes = CoursePress_Data_Module::attributes( $module_id );
							$is_mandatory = cp_is_true( $attributes['mandatory'] );
							$is_assessable = cp_is_true( $attributes['assessable'] );
							$module_type = $attributes['module_type'];
							$is_answerable = preg_match( '%input-%', $attributes['module_type'] );
							$require_instructor_assessment = ! empty( $attributes['instructor_assessable'] ) && cp_is_true( $attributes['instructor_assessable'] );
							$is_module_structure_visible = CoursePress_Data_Unit::is_module_structure_visible( $course_id, $unit_id, $module_id, $student_id );
							$minimum_grade = isset( $attributes['minimum_grade'] ) ? (int) $attributes['minimum_grade'] : 0;
							$gradable = false;

							$unit_total_modules += 1;
							$index += 1;

							// Count only modules that are set to be visible to avoid progress rating confusion
							if ( $is_module_structure_visible ) {
								$total_valid_items += 1;
							}

							if ( $is_mandatory ) {
								// Count mandatory modules
								$unit_required_modules += 1;
							}

							if ( $is_assessable || $require_instructor_assessment ) {
								// Count assessable modules
								$unit_assessable_modules += 1;
							}

							// Treat discussion as answerable if required
							if ( 'discussion' == $module_type && $is_mandatory ) {
								$is_answerable = true;
								// Don't treat discussion as assessable
								$is_assessable = false;
							}

							// Check if the student have seen the module
							$module_seen = CoursePress_Helper_Utility::get_array_val(
								$student_progress,
								'completion/' . $unit_id . '/modules_seen/' . $module_id
							);
							$module_seen = cp_is_true( $module_seen );

							if ( $module_seen ) {
								$seen_modules += 1;
							}

							if ( $is_answerable && 'discussion' != $module_type ) {
								$unit_gradable_modules += 1;
								$gradable = true;
								$unit_passing_grade += $minimum_grade;
							}

							// Begin checking answerable modules
							if ( $is_answerable ) {
								if ( false === $valid ) {
									continue;
								}

								$previous_module_done = self::is_module_completed( $course_id, $unit_id, $module_id, $student_id );

								if ( false === $previous_module_done ) {
									$valid = false;
								}

								$had_passed = CoursePress_Helper_Utility::get_array_val(
									$student_progress,
									'completion/' . $unit_id . '/passed/' . $module_id
								);
								$had_answered = CoursePress_Helper_Utility::get_array_val(
									$student_progress, 'completion/' . $unit_id . '/answered/' . $module_id
								);

								$responses = CoursePress_Helper_Utility::get_array_val(
									$student_progress,
									'units/' . $unit_id . '/responses/' . $module_id
								);
								if ( isset( $responses['response'] ) ) {
									$responses = $responses['response'];
								}

								// Only validate the last submitted response
								$last_answer = is_array( $responses ) ? array_pop( $responses ) : array();

								if ( $module_seen && 'discussion' == $module_type ) {
									$args = array(
										'post_id' => $module_id,
										'user_id' => $student_id,
										'order' => 'ASC',
										'number' => 1, // We only need one to verify if current user posted a comment.
										'fields' => 'ids',
									);
									$comments = get_comments( $args );
									$last_answer = count( $comments ) > 0;
								}

								if ( ! empty( $last_answer ) ) {
									// Trigger student action
									if ( ! cp_is_true( $had_answered ) ) {
										do_action( 'coursepress_student_module_attempted', $student_id, $module_id, get_post_field( 'post_tile', $module_id ), $unit_id, $course_id );
									}
									CoursePress_Helper_Utility::set_array_val(
										$student_progress, 'completion/' . $unit_id . '/answered/' . $module_id,
										true
									);

									if ( $module_seen && 'discussion' == $module_type ) {
										$unit_completed_modules += 1;
										$unit_completed_required_modules += 1;

										if ( $is_module_structure_visible ) { $valid_items += 1; }
										continue; 
									}

									// Get the last grade and see if the student pass
									$grades = self::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
									$grade = CoursePress_Helper_Utility::get_array_val(
										$grades,
										'grade'
									);

									// Set grade for input-textarea, input-text
									$excluded_modules = array(
										'input-textarea',
										'input-text'
									);

									if ( in_array( $module_type, $excluded_modules ) && 0 == $grade ) {
										$grade = $minimum_grade;
									}

									$total_course_grade += $grade;

									if ( $require_instructor_assessment || in_array( $module_type, $excluded_modules ) ) {
										// Check if the grade came from an instructor
										$graded_by = CoursePress_Helper_Utility::get_array_val(
											$grades,
											'graded_by'
										);

										if ( 'auto' === $graded_by ) {
											// Set 0 as grade if it is auto-graded
											$grade = 0; 
										}
									}

									$pass = (int) $grade >= (int) $minimum_grade;

									if ( $gradable ) {
										$unit_grade += (int) $grade;
									}

									if ( $is_mandatory ) {
										$unit_completed_required_modules += 1;

										if ( $is_assessable || $require_instructor_assessment ) {
											// We'll only validate a passing grade if it is assessable
											if ( $pass ) {
												$unit_completed_modules += 1;
												$unit_completed_assessable_modules += 1;
												if ( $is_module_structure_visible ) { $valid_items += 1; }

												// Trigger passed hook
												if ( ! cp_is_true( $had_passed ) ) {
													do_action( 'coursepress_student_module_passed', $student_id, $module_id, get_post_field( 'post_tile', $module_id ), $unit_id, $course_id );
												}

												CoursePress_Helper_Utility::set_array_val(
													$student_progress,
													'completion/' . $unit_id . '/passed/' . $module_id,
													true
												);
											} else {
												if ( $require_instructor_assessment ) {
													if ( $is_module_structure_visible ) { $valid_items += 1; }
												}
											}
										} else {
											$unit_completed_modules += 1;
											if ( $is_module_structure_visible ) { $valid_items += 1; }
										}
									} else {
										$unit_completed_modules += 1;
										if ( $is_module_structure_visible ) { $valid_items += 1; }
									}
								} else {
									if ( $module_seen ) {
										if ( false === $is_mandatory && false === $is_assessable && false === $require_instructor_assessment ) {
											$unit_completed_modules += 1;
											if ( $is_module_structure_visible ) { $valid_items += 1; }
										}
									}
								}
							} else {
								if ( $module_seen ) {
									$unit_completed_modules += 1;
									$last_seen_index = $index;
									if ( $is_module_structure_visible ) { $valid_items += 1; }
								} else {
									$unseen_modules[$module_id] = $module_id;
								}
							}
						}
					}

					// Check if the page have seen
					$pages_seen = CoursePress_Helper_Utility::get_array_val(
						$student_progress,
						'units/' . $unit_id . '/visited_pages'
					);

					if ( $is_page_structure_visible && ( (is_array( $pages_seen ) && isset( $pages_seen[$page_number] ) )
						|| ( $seen_modules > 0 ) )
						) {
					//	$valid_items += 1;
					}
				}

			}

			// Validate unseen modules if it is not required and assessable if the preceding modules are seen
			if ( count( $unseen_modules ) > 0 ) {
				$unseen_modules = array_slice( $unseen_modules, 0, $last_seen_index );

				if ( count( $unseen_modules ) > 0 ) {
					$unit_completed_modules += count( $unseen_modules );
				}
			}

			// Set # of required steps
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/required_steps',
				$unit_required_modules
			);
			$course_mandatory_steps += $unit_required_modules;

			// Set total # of answered mandatory modules
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/completed_mandatory',
				$unit_completed_required_modules
			);
			$course_completed_mandatory_steps += $unit_completed_required_modules;

			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/all_mandatory',
				$unit_required_modules == $unit_completed_required_modules
			);

			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/all_required_assessable',
				$unit_assessable_modules == $unit_completed_assessable_modules
			);

			// Calculate unit progress
			$unit_progress = $valid_items * 100;
			if ( $unit_progress > 0 ) {
				$unit_progress = ceil( $unit_progress / $total_valid_items );
			}

			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/progress',
				$unit_progress
			);

			$course_progress += $unit_progress;
			$was_completed = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/' . $unit_id . '/completed'
			);

			// Marked unit completion status
			$is_unit_completed = $unit_total_modules > 0 && $unit_completed_modules >= $unit_total_modules;
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/completed',
				$is_unit_completed
			);

			$course_gradable_modules += $unit_gradable_modules;
			$course_grade += $unit_grade;
			$unit_grade = $unit_grade > 0 && $unit_gradable_modules > 0 ? ceil( $unit_grade / $unit_gradable_modules ) : 0;
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/average',
				$unit_grade
			);

			if ( $is_unit_completed ) {
				$unit_completed += 1;

				// Trigger unit completion hook
				if ( ! cp_is_true( $was_unit_completed ) ) {
					do_action( 'coursepress_student_unit_completed', $student_id, $unit_id, $unit['unit']->post_title, $course_id );
				}
			}
		}

		CoursePress_Helper_Utility::set_array_val(
			$student_progress,
			'completion/required_steps',
			$course_mandatory_steps
		);
		CoursePress_Helper_Utility::set_array_val(
			$student_progress,
			'completion/completed_steps',
			$course_completed_mandatory_steps
		);

		if ( $course_progress > 0 && $unit_count > 0 ) {
			$course_progress = ceil( $course_progress / $unit_count );
		}

		CoursePress_Helper_Utility::set_array_val(
			$student_progress,
			'completion/progress',
			$course_progress
		);

		// Compute course average
		$completion_average = 0;
		if ( $course_gradable_modules > 0 && $course_grade > 0 ) {
			$completion_average = ceil( $course_grade / $course_gradable_modules );
		}

		CoursePress_Helper_Utility::set_array_val(
			$student_progress,
			'completion/average',
			$completion_average
		);

		$is_completed = $unit_count > 0 && $unit_count == $unit_completed;
		$minimum_grade_required = (int) CoursePress_Data_Course::get_setting( $course_id, 'minimum_grade_required', 100 );

		// Compute actual grade acquired
		if ( $course_gradable_modules > 0 && $total_course_grade > 0 ) {
			$total_course_grade = ceil( $total_course_grade / $course_gradable_modules );

			if ( $total_course_grade < $minimum_grade_required ) {
				$is_completed = false;

				CoursePress_Helper_Utility::set_array_val(
					$student_progress,
					'completion/failed',
					true
				);
			} else {
				CoursePress_Helper_Utility::unset_array_val(
					$student_progress,
					'completion/failed'
				);
			}
		} else {
			CoursePress_Helper_Utility::unset_array_val(
				$student_progress,
				'completion/failed'
			);
		}

		CoursePress_Helper_Utility::set_array_val(
			$student_progress,
			'completion/completed',
			$is_completed
		);

		if ( ! $is_done && $is_completed ) {
			// Notify other modules about the lucky student!
			do_action(
				'coursepress_student_course_completed',
				$student_id,
				$course_id,
				get_post_field( 'post_title', $course_id )
			);

			// Generate the certificate and send email to the student.
			CoursePress_Data_Certificate::generate_certificate(
				$student_id,
				$course_id
			);
		}

		self::update_completion_data(
			$student_id,
			$course_id,
			$student_progress
		);

		return $student_progress;
	}

	public static function calculate_completion( $student_id, $course_id ) {
		if ( empty( $student_id ) ) {
			return;
		}

		$student_progress = self::get_completion_data( $student_id, $course_id );
		$student_units = isset( $student_progress['units'] ) ? array_keys( $student_progress['units'] ) : array();
		$units = CoursePress_Data_Course::get_units_with_modules( $course_id );

		$course_required_steps = 0;
		$course_completed_steps = 0;

		$total_units = count( $units );
		$total_completion = 0;

		foreach ( $units as $unit_id => $unit ) {
			// Don't bother calculating completion if the student hasn't even started the unit.
			if ( ! in_array( $unit_id, $student_units ) ) {
				continue;
			}

			$required_steps = 0;
			$completed_steps = 0;

			// PAGES.
			$total_pages = count( $unit['pages'] );
			$required_steps += $total_pages;
			$visited_pages = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'units/' . $unit_id . '/visited_pages'
			);
			$total_visited_pages = count( $visited_pages );
			$completed_steps += $total_visited_pages;

			if ( $total_pages === $total_visited_pages ) {
				CoursePress_Helper_Utility::set_array_val(
					$student_progress,
					'completion/' . $unit_id . '/all_pages',
					true
				);
			}

			// First milestone
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/required_steps',
				$required_steps
			);
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/completed_steps',
				$completed_steps
			);

			// MODULES
			$assessable_mandatory = 0;
			$mandatory = 0;
			$student_assessable_mandatory = 0;
			$student_mandatory = 0;
			foreach ( $unit['pages'] as $page ) {

				foreach ( $page['modules'] as $module_id => $module ) {

					$attributes = CoursePress_Data_Module::attributes( $module_id );

					if ( 'output' === $attributes['mode'] ) {
						continue;
					}

					// Only worry about assessable units if they are required
					if ( $attributes['assessable'] && $attributes['mandatory'] ) {

						// Only worry about assessable units if they are required
						$required_steps += 1;
						$assessable_mandatory += 1;

						// Get the last grade and see if we pass
						$grade = self::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );

						$pass = (int) $grade >= (int) $attributes['minimum_grade'];

						if ( $pass ) {

							$completed_steps += 1;
							$student_assessable_mandatory += 1;

							$check = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $module_id );
							if ( isset( $check ) && empty( $check ) ) {
								do_action( 'coursepress_student_module_passed', $student_id, $module_id, get_post_field( 'post_tile' ), $unit_id, $course_id );
							}

							CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $module_id, true );

							$check = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $module_id );
							if ( isset( $check ) && empty( $check ) ) {
								do_action( 'coursepress_student_module_attempted', $student_id, $module_id, get_post_field( 'post_tile' ), $unit_id, $course_id );
							}

							CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $module_id, true );

						}
					} elseif ( $attributes['mandatory'] ) {

						// Required questions must at least have an answer, even if its not assessable
						$required_steps += 1;
						$mandatory += 1;

						// Is there a response?
						$responses = CoursePress_Helper_Utility::get_array_val( $student_progress, 'units/' . $unit_id . '/responses/' . $module_id );
						$response_count = ! empty( $responses ) ? count( $responses ) : 0;

						if ( ! empty( $response_count ) ) {

							$completed_steps += 1;
							$student_mandatory += 1;

							CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $module_id, true );

							$check = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $module_id );
							if ( isset( $check ) && empty( $check ) ) {
								do_action( 'coursepress_student_module_attempted', $student_id, $module_id, get_post_field( 'post_tile' ), $unit_id, $course_id );
							}
						}
					}  // Required Assessable or just required

				} // Module

			} // Page

			if ( $assessable_mandatory === $student_assessable_mandatory ) {
				CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/all_required_assessable', true );
			}

			$total_mandatory = $mandatory + $assessable_mandatory;
			$total_student_mandatory = $student_mandatory + $student_assessable_mandatory;

			if ( $total_mandatory === $total_student_mandatory ) {
				CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/all_mandatory', true );
			}

			// Next milestone
			CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/required_steps', $required_steps );
			CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/completed_steps', $completed_steps );

			// Is unit complete?
			if ( $required_steps === $completed_steps ) {
				$check = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/completed' );
				if ( isset( $check ) && empty( $check ) ) {
					do_action( 'coursepress_student_unit_completed', $student_id, $unit_id, $unit['unit']->title, $course_id );
				}
				CoursePress_Helper_Utility::set_array_val( $student_progress, 'completion/' . $unit_id . '/completed', true );
			}

			$progress = (int) ($completed_steps / $required_steps * 100);
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/' . $unit_id . '/progress',
				$progress
			);
			$total_completion += $progress;

			// Update Course Steps
			$course_required_steps += $required_steps;
			$course_completed_steps += $completed_steps;
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/required_steps',
				$course_required_steps
			);
			CoursePress_Helper_Utility::set_array_val(
				$student_progress,
				'completion/completed_steps',
				$course_completed_steps
			);

		} // End of foreach ( $units ) ...

		// Record course progress.
		$progress = 0;
		if ( $total_units > 0 ) {
			$progress = (int) ($total_completion / $total_units * 100);
		}

		CoursePress_Helper_Utility::set_array_val(
			$student_progress,
			'completion/progress',
			$progress
		);

		// Check if course is completed.
		if ( $course_required_steps === $course_completed_steps && ! empty( $student_units ) ) {
			$check = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/completed'
			);

			// Only process if not completed yet.
			if ( empty( $check ) ) {
				// Notify other modules about the lucky student!
				do_action(
					'coursepress_student_course_completed',
					$student_id,
					$course_id,
					get_post_field( 'post_title', $course_id )
				);

				// Generate the certificate and send email to the student.
				CoursePress_Data_Certificate::generate_certificate(
					$student_id,
					$course_id
				);

				// Mark course as completed.
				CoursePress_Helper_Utility::set_array_val(
					$student_progress,
					'completion/completed',
					true
				);
			}
		}

		self::update_completion_data(
			$student_id,
			$course_id,
			$student_progress
		);

		return $student_progress;
	}

	public static function get_mandatory_completion( $student_id, $course_id, $unit_id, &$data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		return array(
			'required' => CoursePress_Data_Unit::get_number_of_mandatory( $unit_id ),
			'completed' => CoursePress_Helper_Utility::get_array_val(
				$data,
				'completion/' . $unit_id . '/completed_mandatory'
			),
		);
	}

	public static function get_unit_progress( $student_id, $course_id, $unit_id, &$data = false ) {
		//return self::get_all_unit_progress( $student_id, $course_id, $unit_id, $data );
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		return (int) CoursePress_Helper_Utility::get_array_val(
			$data,
			'completion/' . $unit_id . '/progress'
		);
	}

	public static function get_course_progress( $student_id, $course_id, &$data = false ) {
		if ( empty( $data ) ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		return (int) CoursePress_Helper_Utility::get_array_val(
			$data,
			'completion/progress'
		);
	}

	public static function is_mandatory_done( $student_id, $course_id, $unit_id, &$data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$mandatory = CoursePress_Helper_Utility::get_array_val(
			$data,
			'completion/' . $unit_id . '/all_mandatory'
		);

		return cp_is_true( $mandatory );
	}

	public static function is_unit_complete( $student_id, $course_id, $unit_id, &$data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$completed = CoursePress_Helper_Utility::get_array_val(
			$data,
			'completion/' . $unit_id . '/completed'
		);

		return cp_is_true( $completed );
	}

	public static function is_course_complete( $student_id, $course_id, &$data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$progress = CoursePress_Helper_Utility::get_array_val(
			$data,
			'completion/completed'
		);

		return cp_is_true( $progress );
	}

	public static function count_course_responses( $student_id, $course_id, $data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$units = isset( $data['units'] ) ? $data['units'] : array();

		$response_count = 0;
		foreach ( $units as $key => $unit ) {
			$modules = CoursePress_Helper_Utility::get_array_val(
				$data,
				'units/' . $key . '/responses'
			);

			if ( ! empty( $modules ) ) {
				$response_count += count( $modules );
			}
		}

		return $response_count;
	}

	public static function average_course_responses( $student_id, $course_id, $data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}
		$average = CoursePress_Helper_Utility::get_array_val(
			$data,
			'completion/average'
		);
		return (int) $average;

		/*
		TODO: Remove this in 2.1 or so, but keep in 2.0 for reference:

		$units = isset( $data['units'] ) ? $data['units'] : array();
		$total_response = 0;
		$total_grade = 0;

		foreach ( $units as $key => $unit ) {
			$modules = CoursePress_Helper_Utility::get_array_val(
				$data,
				'units/' . $key . '/responses'
			);

			$total_response += count( $modules );

			foreach ( $modules as $mod_key => $module ) {
				$attributes = CoursePress_Data_Module::attributes( $mod_key );
				if ( 'output' === $attributes['mode'] || ! $attributes['assessable'] ) {
					unset( $modules[ $mod_key ] );
					continue;
				}

				$responses = CoursePress_Helper_Utility::get_array_val(
					$data,
					'units/' . $key . '/responses/' . $mod_key
				);

				if ( ! is_array( $responses ) ) { continue; }
				if ( ! count( $responses ) ) { continue; }

				$last_response = array_pop( $responses );

				if ( ! isset( $last_response['grades'] ) ) { continue; }
				if ( ! is_array( $last_response['grades'] ) ) { continue; }

				$grade = array_pop( $last_response['grades'] );
				$total_grade += (int) $grade['grade'];
			}
		}

		if ( $total_response > 0 ) {
			return (int) ($total_grade / $total_response);
		}
		return 0;
		*/
	}

	/**
	 * Send email about successful account creation.
	 * The email contains several links but no login name or password.
	 *
	 * @since  1.0.0
	 * @param  int $student_id The newly created WP User ID.
	 * @return bool True on success.
	 */
	public static function send_registration( $student_id ) {
		$student_data = get_userdata( $student_id );

		$email_args = array();
		$email_args['email'] = $student_data['user_email'];
		$email_args['first_name'] = $student_data['first_name'];
		$email_args['last_name'] = $student_data['last_name'];
		$email_args['fields'] = array();
		$email_args['fields']['student_id'] = $student_id;
		$email_args['fields']['student_username'] = $student_data['user_login'];
		$email_args['fields']['student_password'] = $student_data['user_pass'];

		$sent = CoursePress_Helper_Email::send_email(
			CoursePress_Helper_Email::REGISTRATION,
			$email_args
		);

		return $sent;
	}

	public static function get_admin_workbook_link( $student_id, $course_id ) {
		$workbook_link = add_query_arg(
			array(
				'page' => CoursePress_View_Admin_Student::get_slug(),
				'view' => 'workbook',
				'course_id' => $course_id,
				'student_id' => $student_id,
			),
			admin_url( 'admin.php' )
		);

		return $workbook_link;
	}

	/**
	 * Get all unit progress, even only unit to see
	 *
	 * @since 2.0.0
	 *
	 * @param integer $student_id Student Id.
	 * @param integer $course_id Course ID.
	 * @param integer $unit_id unit ID.
	 * @param array $data completion data.
	 *
	 * return float Percent of done.
	 */
	public static function get_all_unit_progress( $student_id, $course_id, $unit_id, &$data = false ) {
		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}
		/**
		 * Filter allow to count mandatory modules twice: first time when we
		 * seen this module, second time, when it is completed.
		 *
		 * @since 2.0.0
		 *
		 * @param boolean Count mandatory modules twice?
		 */
		$count_mandatory_seen_as_step = apply_filters( 'coursepress_count_mandatory_seen_as_step', false );
		$modules_ids = CoursePress_Data_Module::get_modules_ids_by_unit( $unit_id );
		$mandatory = self::get_mandatory_completion( $student_id, $course_id, $unit_id, $data );
		$all = count( $modules_ids );
		$done = 0;
		if ( $count_mandatory_seen_as_step ) {
			$all += $mandatory['required'];
			$done = $mandatory['completed'];
		}

		foreach ( $modules_ids as $module_id ) {
			if (
				isset( $data['completion'] )
				&& isset( $data['completion'][ $unit_id ] )
				&& isset( $data['completion'][ $unit_id ]['modules_seen'] )
				&& isset( $data['completion'][ $unit_id ]['modules_seen'][ $module_id ] )
				&& $data['completion'][ $unit_id ]['modules_seen'][ $module_id ]
			) {
				if ( $count_mandatory_seen_as_step ) {
					$done++;
				} else {
					$attributes = CoursePress_Data_Module::attributes( $module_id );
					if ( $attributes['mandatory'] ) {
						$is_module_completed = CoursePress_Data_Student::is_module_completed( $course_id, $unit_id, $module_id, $student_id );
						if ( $is_module_completed  ) {
							$done++;
						}
					} else {
						$done++;
					}
				}
			}
		}
		if ( 0 < $all ) {
			return ( $done * 100 ) / $all;
		}
		return 100;
	}

	/**
	 * Check if a section is seen.
	 *
	 * @since 2.0
	 * @param (int) $course_id
	 * @param (int) $unit_id
	 * @param (int) $page			The page/section number
	 * @param (int) $student_id		Optional. Will use current user ID if empty.
	 * @return (boolean)			Returns true if all modules are seen, answered and completed otherwise false.
	 **/
	public static function is_section_seen( $course_id, $unit_id, $page, $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$completed = false;
		$page = 0 == (int) $page ? 1 : $page;

		// Check if student is enrolled
		$is_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );

		if ( ! $is_enrolled ) { return false; }

		$student_progress = self::get_completion_data( $student_id, $course_id );
		$is_unit_visited = CoursePress_Helper_Utility::get_array_val(
			$student_progress,
			'units/' . $unit_id . '/visited_pages/' . $page
		);
		$completed = ! empty( $is_unit_visited );

		if ( ! $completed ) {
			// Check if one of the modules was visited.
			$modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => $page ) );

			if ( count( $modules ) > 0 ) {
				$count = 0;
				foreach ( $modules as $module_id ) {
					$is_module_seen = CoursePress_Helper_Utility::get_array_val(
						$student_progress,
						'completion/' . $unit_id . '/modules_seen/' . $module_id
					);
					if ( ! empty( $is_module_seen ) ) {
						return true;
					}
				}
			}
		}
		return $completed;
	}

	/**
	 * Check if a student completed a module.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id
	 * @param (int) $unit_id
	 * @param (int) $module_id
	 * @param (int) $student_id		Optional. Will use current user ID if empty.
	 * @return (boolean)			Returns true if per criteria is met otherwise false.
	 **/
	public static function is_module_completed( $course_id, $unit_id, $module_id, $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$completed = false;
		$student_progress = $student_progress = self::get_completion_data( $student_id, $course_id );
		$attributes = CoursePress_Data_Module::attributes( $module_id );
		$module_type = $attributes['module_type'];
		$is_required = cp_is_true( $attributes['mandatory'] );
		$is_assessable = cp_is_true( $attributes['assessable'] );
		$is_answerable = preg_match( '%input-%', $attributes['module_type'] ) || 'discussion' == $attributes['module_type'];
		$responses = CoursePress_Helper_Utility::get_array_val(
			$student_progress,
			'units/' . $unit_id . '/responses/' . $module_id
		);
		$is_seen = CoursePress_Helper_Utility::get_array_val(
			$student_progress,
			'completion/' . $unit_id . '/modules_seen/' . $module_id
		);

		if ( $is_answerable ) {
			if ( 'discussion' == $attributes['module_type'] ) {
				// Check if the student already commented at least once.
				$args = array(
					'post_id' => $module_id,
					'user_id' => $student_id,
					'order' => 'ASC',
					'number' => 1, // We only need one to verify if current user posted a comment.
					'fields' => 'ids',
					);
				$comments = get_comments( $args );
				$completed = count( $comments ) > 0;
			} else {
				$last_answer = is_array( $responses ) ? array_pop( $responses ) : array();
				$last_answer = array_filter( $last_answer );

				$excluded_modules = array(
					'input-textarea',
					'input-text'
				);

				if ( ! empty( $last_answer ) ) {
					if ( $is_required ) {
						if ( $is_assessable && ! in_array( $module_type, $excluded_modules ) ) {
							// Check grade if it pass
							$grades = self::get_grade( $student_id, $course_id, $unit_id, $module_id, false, false, $student_progress );
							$grade = CoursePress_Helper_Utility::get_array_val(
								$grades,
								'grade'
							);
							$minimum_grade = $attributes['minimum_grade'];
							$completed = (int) $grade >= (int) $minimum_grade;
						} else {
							$completed = ! empty( $last_answer );
						}
					} else {
						$completed = ! empty( $last_answer );
					}
				}
			}
		} else {
			// If module is not answerable but already seen marked completed.
			$completed = cp_is_true( $is_seen );
		}

		return $completed;
	}

	/**
	 * Get student data and create substitutions array.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $student_id Student ID.
	 * @return array Array of substitutions.
	 */
	public static function get_vars( $student_id ) {
		$user = get_userdata( $student_id );
		$vars = array(
			'FIRST_NAME' => get_user_meta( $student_id, 'first_name', true ),
			'LAST_NAME' => get_user_meta( $student_id, 'last_name', true ),
		);
		return $vars;
	}

	public static function my_courses( $student_id = 0, $courses = array() ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		if ( empty( $courses ) ) {
			$course_ids = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
			$courses = array_map( 'get_post', $course_ids );
		}

		$found_courses = array(
			'current' => array(),
			'completed' => array(),
			'incomplete' => array(),
			'future' => array(),
			'past' => array(),
		);

		$now = CoursePress_Data_Course::time_now();

		foreach ( $courses as $course ) {
			$course_id = $course->ID;
			$course_setting = CoursePress_Data_Course::get_setting( $course_id );
			$start_date = CoursePress_Data_Course::strtotime( $course_setting['course_start_date'] );
			$end_date = ! empty( $course_setting['course_end_date'] ) ? CoursePress_Data_Course::strtotime( $course_setting['course_end_date'] ) : 0;
			$is_open_ended = ! empty( $course_setting['course_open_ended'] );

			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			$completed = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/completed'
			);

			if ( cp_is_true( $completed ) ) {
				$found_courses['completed'][] = $course;
				$found_courses['past'][] = $course;
			} else {
				if ( $start_date <= $now ) {
					$ended = empty( $is_open_ended ) && $end_date <= $now;

					if ( $ended ) {
						// For ended courses, marked incomplete
						$found_courses['incomplete'][] = $course;
						$found_courses['past'][] = $course;
					} else {
						$found_courses['current'][] = $course;
					}
				} else {
					// Future courses
					$found_courses['future'][] = $course;
				}
			}

		}

		return $found_courses;
	}
}
