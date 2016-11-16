<?php
class CoursePress_Helper_Upgrade {
	private static $settings = array();

	public static function update_course( $course_id ) {
		$course = get_post( $course_id );
		$found_error = 0;

		// Update course instructors
		if ( false == self::update_course_instructors( $course_id ) ) {
			$found_error += 1;
		}
		// Update course meta
		if ( false == self::update_course_meta( $course_id ) ) {
			$found_error += 1;
		}
		// Update course structure
		if ( false == self::update_course_structure( $course_id ) ) {
			$found_error += 1;
		}

		// Now update the course settings
		if ( false == self::update_course_settings( $course_id, self::$settings ) ) {
			$found_error += 1;
		}

		if ( false == self::update_course_units( $course_id ) ) {
			$found_error += 1;
		}

		// Update Student Progress data
		if ( false == self::update_course_students_progress( $course_id ) ) {
			$found_error += 1;
		}

		$result = ( 0 == $found_error );

		if ( $result ) {
			update_post_meta( $course_id, '_cp_updated_to_version_2', 1 );
		}

		return $result;
	}

	public static function strtotime( $timestamp ) {
		if ( ! is_numeric( $timestamp ) ) {
			$timestamp = strtotime( $timestamp . ' UTC' ); //@todo: Need hook to change timestamp
		}

		return $timestamp;
	}

	public static function fix_settings( $settings ) {
		if ( is_array( $settings ) ) {
			foreach ( $settings as $key => $value ) {
				if ( 'on' == $value ) {
					$value = 1;
				} elseif ( 'off' == $value ) {
					$value = '';
				} elseif ( is_array( $value ) ) {
					$value = self::fix_settings( $value );
				}
				$settings[ $key ] = $value;
			}
		}

		return $settings;
	}

	public static function update_course_settings( $course_id, $settings ) {
		$settings = array_filter( $settings );

		// Fix settings
		$settings = self::fix_settings( $settings );

		update_post_meta( $course_id, 'course_settings', $settings );

		$date_types = array(
			'course_start_date',
			'course_end_date',
			'enrollment_start_date',
			'enrollment_end_date',
		);

		$course_open_ended = ! empty( $settings['course_open_ended'] );
		$enrollment_open_ended = ! empty( $settings['enrollment_open_ended'] );

		foreach ( $settings as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, $date_types ) ) {
				$meta_value = trim( $meta_value );
				$meta_value = ! empty( $meta_value ) ? self::strtotime( $meta_value ) : 0;
				$meta_value = (int) $meta_value;

				if ( ( true === $course_open_ended && 'course_end_date' == $meta_key )
					|| ( true === $enrollment_open_ended && 'enrollment_end_date' == $meta_key )
				   ) {
					$meta_value = 0;
				}
				update_post_meta( $course_id, "cp_{$meta_key}", $meta_value );
			}
		}

		return true;
	}

	private static function update_course_instructors( $course_id ) {
		$instructors = (array) get_post_meta( $course_id, 'instructors', true );
		$instructors = array_filter( $instructors );
		self::$settings['instructors'] = $instructors;

		return true;
	}

	private static function update_course_meta( $course_id ) {
		$course_metas = array(
			'course_view' => 'normal',
			'minimum_grade_required' => 100,
			'pre_completion_title' => __( 'Almost There', 'cp' ),
			'pre_completion_content' => '',
			'course_completion_title' => __( 'Congratulations, you passed!', 'cp' ),
			'course_completion_content' => '',
			'course_failed_title' => __( 'Sorry, you did not pass this course!', 'cp' ),
			'course_failed_content' => '',
			'setup_step_1' => 'saved',
			'setup_step_2' => 'saved',
			'setup_step_3' => 'saved',
			'setup_step_4' => 'saved',
			'setup_step_5' => 'saved',
			'setup_step_6' => 'saved',
			'setup_step_7' => 'saved',
		);
		$meta_keys = array(
			'featured_url' => 'listing_image',
			'course_video_url' => 'featured_video',
			'course_structure_options' => 'structure_visible',
			'course_structure_time_display' => 'structure_show_duration',
			'course_language' => 'course_language',
			/** Course Dates **/
			'open_ended_course' => 'course_open_ended',
			'course_start_date' => 'course_start_date',
			'course_end_date' => 'course_end_date',
			'open_ended_enrollment' => 'enrollment_open_ended',
			'enrollment_start_date' => 'enrollment_start_date',
			'enrollment_end_date' => 'enrollment_end_date',
			/** Classes, Discussions **/
			'limit_class_size' => 'class_limited',
			'class_size' => 'class_size',
			'allow_course_discussion' => 'allow_discussion',
			'allow_workbook_page' => 'allow_workbook',
			/** Enrollment & Cost **/
			'enroll_type' => 'enrollment_type',
			'paid_course' => 'payment_paid_course',
		);

		foreach ( $meta_keys as $old_meta => $new_meta ) {
			$meta_value = get_post_meta( $course_id, $old_meta, true );
			$course_metas[ $new_meta ] = $meta_value;
		}

		self::$settings = wp_parse_args( $course_metas, self::$settings );

		return true;
	}

	public static function update_course_structure( $course_id ) {
		self::$settings['structure_visible_units'] = get_post_meta( $course_id, 'show_unit_boxes', true );
		self::$settings['structure_preview_units'] = get_post_meta( $course_id, 'preview_unit_boxes', true );
		$cp1_visible_pages = (array) get_post_meta( $course_id, 'show_page_boxes', true );
		$cp1_preview_pages = (array) get_post_meta( $course_id, 'preview_page_boxes', true );
		$structure_visible_modules = array();
		$structure_preview_modules = array();

		$units_args = array(
			'post_type' => 'unit',
			'post_parent' => $course_id,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
			'fields' => 'ids',
		);
		$units = get_posts( $units_args );

		$module_args = array(
			'post_type' => 'module',
			'post_status' => 'any',
			'posts_per_page' => -1,
			'suppress_filters' => true,
			'fields' => 'ids',
		);

		foreach ( $units as $unit_id ) {
			$unit_pages = get_post_meta( $unit_id, 'page_title', true );

			if ( empty( $unit_pages ) ) {
				continue;
			}

			foreach ( $unit_pages as $key => $page ) {
				$key = str_replace( 'page_', '', $key );
				$page_key = $unit_id . '_' . (int) $key;

				// Visible Modules
				if ( in_array( $page_key, array_keys( $cp1_visible_pages ) ) ) {
					$module_args['post_parent'] = $unit_id;
					$modules = get_posts( $module_args );

					foreach ( $modules as $module ) {
						$mod_key = $page_key . '_' . (int) $module;
						$structure_visible_modules[ $mod_key ] = true;
					}
				}

				// Preview Modules
				if ( in_array( $page_key, array_keys( $cp1_preview_pages ) ) ) {
					$module_args['post_parent'] = $unit_id;
					$modules = get_posts( $module_args );

					foreach ( $modules as $module ) {
						$mod_key = $page_key . '_' . (int) $module;
						$structure_preview_modules[ $mod_key ] = true;
					}
				}
			}
		}

		self::$settings['structure_visible_pages'] = $cp1_visible_pages;
		self::$settings['structure_preview_pages'] = $cp1_preview_pages;
		self::$settings['structure_visible_modules'] = $structure_visible_modules;
		self::$settings['structure_preview_modules'] = $structure_preview_modules;

		return true;
	}

	public static function update_course_students_progress( $course_id ) {
		global $wpdb;

		// get all enrolled students
		if ( is_multisite() ) {
			$class_meta_query_key = $wpdb->prefix . 'enrolled_course_class_' . $course_id;
		} else {
			$class_meta_query_key = 'enrolled_course_class_' . $course_id;
		}
		$args = array(
			'meta_query' => array(
				array(
					'key'   => $class_meta_query_key,
					'value' => '',
				),
			),
		);

		$wp_user_search = new WP_User_Query( $args );
		foreach ( $wp_user_search->get_results() as $user ) {
			// get (1.x) student progress data
			$current_student_course_progress = get_user_option( '_course_' . $course_id . '_progress', $user->ID );
			if ( $current_student_course_progress ) {
				// transform into (2.0) data
				$new_student_progress = array(
					'version' => '2.0',
				);

				// completion
				$completion = array();
				$course_total_grade = 0;
				$course_module_gradable_count = 0;

				// units
				$units = array();
				$old_unit_data = ( isset( $current_student_course_progress['unit'] ) ) ? $current_student_course_progress['unit'] : false;
				if ( $old_unit_data ) {
					foreach ( $old_unit_data as $key => $unit_data ) {
						$new_unit_data = array();

						// visited pages
						if ( isset( $unit_data['visited_pages'] ) ) {
							$visited_pages = array();
							foreach ( $visited_pages as $page ) {
								$visited_pages[ $page ] = $page;
							}
							$new_unit_data['visited_pages'] = $visited_pages;
						}

						// last visited page
						if ( isset( $unit_data['last_visited_page'] ) ) {
							$new_unit_data['last_visited_page'] = $unit_data['last_visited_page'];
						}

						// responses
						$new_responses_data = array();
						if ( isset( $unit_data['mandatory_answered'] ) && is_array( $unit_data['mandatory_answered'] ) ) {

							$completion[ $key ] = array(
								'modules_seen' => array(),
								'answered' => array(),
								'progress' => isset( $unit_data['unit_progress'] ) ? $unit_data['unit_progress'] : 0,
							);
							
							$unit_total_grade = 0;
							$module_gradable_count = 0;

							foreach ( $unit_data['mandatory_answered'] as $mandatory_key => $val ) {

								// module seen
								$completion[ $key ]['modules_seen'][ $mandatory_key ] = true;
								// answered
								$completion[ $key ]['answered'][ $mandatory_key ] = true;
								
								// module meta
								$module_type = get_post_meta( $mandatory_key, 'module_type', true );
								$is_gradable = get_post_meta( $mandatory_key, 'gradable_answer', true );
								
								if ( $is_gradable == 'yes' ) {
									$module_gradable_count++;
									$course_module_gradable_count++;
								}
								
								$new_module_response = array();
								$response_args = array(
									'post_type' => 'module_response',
									'post_status' => array( 'publish', 'private' ),
									'nopaging' => true,
									'ignore_sticky_posts' => true,
									'post_parent' => $mandatory_key,
									'orderby' => 'date',
									'order'   => 'ASC',
								);
								$response_query = new WP_Query( $response_args );
								$module_responses = $response_query->posts;
								if ( $module_responses && ! empty( $module_responses ) ) {
									foreach ( $module_responses as $post_response ) {
										$new_response_data = array();
										$meta_response = get_post_meta( $post_response->ID );
										// date, response, feedback
										$new_response_data['date'] = $post_response->post_date;
										switch ( $module_type ) {
											case 'checkbox_input_module':
												if ( isset( $meta_response['student_checked_answers'] ) && is_array( $meta_response['student_checked_answers'] ) ) {
													foreach ( $meta_response['student_checked_answers'] as $response_student_checked_answer ) {
														$new_response_data['response'] = maybe_unserialize( $response_student_checked_answer );
													}
												}
												$new_response_data['feedback'] = array();
												break;
											case 'radio_input_module':
												$new_response_data['response'] = $post_response->post_content;
												$new_response_data['feedback'] = array();
												break;
											case 'text_input_module':
												$new_response_data['response'] = $post_response->post_content;
												break;
										}
										// grade
										if ( isset( $meta_response['response_grade'] ) && is_array( $meta_response['response_grade'] ) ) {
											foreach ( $meta_response['response_grade'] as $grade ) {
												$grade = maybe_unserialize( $grade );
												$new_response_data['grades'][] = array(
													'graded_by' => ( $user->ID == $grade['instructor'] ) ? 'auto' : $grade['instructor'],
													'grade' => $grade['grade'],
													'date' => date( 'Y-m-d H:i:s', $grade['time'] ),
												);
												
												// for total grade
												if ( $is_gradable == 'yes' ) {
													$unit_total_grade += (int) $grade['grade'];
													$course_total_grade += (int) $grade['grade'];
												}
											}
										} elseif ( preg_match( '/^input/', $module_type ) ) {
											$new_response_data['grades'] = array();
										}
										// comment feedback
										if ( isset( $meta_response['response_comment'] ) && is_array( $meta_response['response_comment'] ) ) {
											foreach ( $meta_response['response_comment'] as $comment ) {
												$new_response_data['feedback'] = $comment;
											}
										}

										$new_module_response[] = $new_response_data;
									}
								}
								$new_responses_data[ $mandatory_key ] = $new_module_response;
							}
							
							// unit average grade
							$completion[$key]['average'] = $unit_total_grade / $module_gradable_count;
						}

						// input file
						$modules_args = array(
							'post_type' => 'module',
							'post_parent' => $key,
							'post_status' => array( 'any' ),
							'fields' => 'ids',
						);
						$modules_query = new WP_Query( $modules_args );
						$modules_ids = $modules_query->posts;
						if ( $modules_ids && ! empty( $modules_ids ) ) {
							$attachment_args = array(
								'post_type' => 'attachment',
								'nopaging' => true,
								'ignore_sticky_posts' => true,
								'post_parent__in' => $modules_ids,
								'post_status' => 'inherit',
							);
							$attachment_query = new WP_Query( $attachment_args );
							$attachments = $attachment_query->posts;
							foreach ( $attachments as $attachment ) {
								$new_responses_data[ $attachment->post_parent ] = array(
									array(
										'feedback' => array(),
										'date' => $attachment->post_date,
										'response' => array(
											'file' => '',
											'url' => wp_get_attachment_url( $attachment->ID ),
											'type' => $attachment->post_mime_type,
											'size' => '',
										),
										'grades' => array(
											array(
												'graded_by' => 'auto',
												'grade' => 100,
												'date' => $attachment->post_date,
											),
										),
									),
								);
							}
						}

						// newly structured responses
						$new_unit_data['responses'] = $new_responses_data;
						$units[ $key ] = $new_unit_data;
					}
				}
				$new_student_progress['units'] = $units;

				// completion
				$completion['progress'] = $current_student_course_progress['course_progress'];
				$completion['average'] = $course_total_grade / $course_module_gradable_count;
				$new_student_progress['completion'] = $completion;

				// save the new data structure
				$global_setting = ! is_multisite();
				update_user_option( $user->ID, 'course_' . $course_id . '_progress', $new_student_progress, $global_setting );
			}
		}

		return true;
	}

	public static function update_course_units( $course_id ) {
		$units_args = array(
			'post_type' => 'unit',
			'post_status' => array( 'publish', 'pending', 'draft', 'private' ),
			'fields' => 'ids',
			'suppress_filters' => true,
			'posts_per_page' => -1,
			'post_parent' => $course_id,
		);

		$units = get_posts( $units_args );

		if ( ! empty( $units ) ) {
			foreach ( $units as $unit_id ) {
				$unit_availability = get_post_meta( $unit_id, 'unit_availability', true );

				if ( ! empty( $unit_availability ) ) {
					update_post_meta( $unit_id, 'unit_availability', 'on_date' );
					update_post_meta( $unit_id, 'unit_date_availability', $unit_availability );
				}
			}
		}

		return true;
	}
}
