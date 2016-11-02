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

		$result = ( 0 == $found_error );

		if ( $result ) {
			update_post_meta($course_id, '_cp_updated_to_version_2', 1);
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

			if ( 'enroll_type' == $old_meta ) {
				// Fix enrollment type
				if ( 'registered' == $meta_value ) {
					$meta_value = 'anyone';
				}
			}

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

		/**
		 * get units
		 */
		$units = Course::get_units_with_modules( $course_id, true );
//		$units = CoursePress_Helper_Utility::sort_on_object_key( $units, 'order' );
error_log( print_r( $units, true ) );
		/**
		 * Update pages and try to update modules too.
		 */
		foreach ( $units as $unit ) {
			if ( ! isset( $unit['pages'] ) ) {
				continue;
			}
			foreach ( $unit['pages'] as $key => $page ) {
				$page_key = (int) $unit['unit']->ID . '_' . (int) $key;
				/**
				 * Visible
				 */
				if ( in_array( $page_key, $cp1_visible_pages ) ) {
					$structure_visible_pages[ $page_key ] = true;
					foreach ( $page['modules'] as $module ) {
						$mod_key = $page_key . '_' . (int) $module->ID;
						$structure_visible_modules[ $mod_key ] = true;
					}
				}
				/**
				 * Preview
				 */
				if ( in_array( $page_key, $cp1_preview_pages ) ) {
					$structure_preview_pages[ $page_key ] = true;
					foreach ( $page['modules'] as $module ) {
						$mod_key = $page_key . '_' . (int) $module->ID;
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
}
