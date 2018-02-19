<?php

class CoursePress_Data_Unit {

	/**
	 * Get time to end tasks.
	 *
	 * @param int $unit_id Unit ID.
	 * @param array $data
	 * @param string $default
	 *
	 * @return array
	 */
	public static function get_time_estimation( $unit_id, $data, $default = '1:00' ) {

		global $CoursePress_Core;

		$estimations = array();
		if ( ! isset( $data[ $unit_id ]['pages'] ) ) {
			$data[ $unit_id ]['pages'] = array();
		}

		$unit_seconds = 0;

		foreach ( $data[ $unit_id ]['pages'] as $page_id => $page ) {
			$page_seconds = 0;
			foreach ( $page['modules'] as $module_id => $module ) {
				$duration = CoursePress_Data_Module::get_time_estimation( $module_id, $default );
				$parts = explode( ':', $duration );
				$seconds = (int) array_pop( $parts );
				$minutes = (int) array_pop( $parts );
				$hours = 0;
				if ( ! empty( $parts ) ) {
					$hours = (int) array_pop( $parts );
				}
				$time = $CoursePress_Core->get_time( $seconds, $minutes, $hours );
				// Increase page time.
				$page_seconds += $time['total_seconds'];
			}

			// Page time.
			$time = $CoursePress_Core->get_time( $page_seconds );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/estimation', $time['time'] );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $time['hours'] );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/components/minutes', $time['minutes'] );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/components/seconds', $time['seconds'] );
			// Increase unit time.
			$unit_seconds += $time['total_seconds'];
		}

		// Unit time.
		$time = $CoursePress_Core->get_time( $unit_seconds );
		$estimations = coursepress_set_array_val( $estimations, 'unit/estimation', $time['time'] );
		$estimations = coursepress_set_array_val( $estimations, 'unit/components/hours', $time['hours'] );
		$estimations = coursepress_set_array_val( $estimations, 'unit/components/minutes', $time['minutes'] );
		$estimations = coursepress_set_array_val( $estimations, 'unit/components/seconds', $time['seconds'] );

		return $estimations;
	}

	/**
	 * Check entry - is this a unit?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post|integer|null $course Variable to check.
	 *
	 * @return boolean Answer is that course or not?
	 */
	public static function is_unit( $unit = null ) {

		if ( empty( $unit ) ) {

			$unit = get_the_ID();
		}

		$unit = coursepress_get_unit( $unit );

		return ! is_wp_error( $unit );
	}

	/**
	 * Check if unit structure is visible
	 *
	 * @since 2.0
	 *
	 * @param int $course_id Course ID.
	 * @param int $unit_id Unit ID.
	 * @param int $user_id Optional. Will use current user ID if empty.
	 *
	 * @return bool Returns true if unit is visible otherwise false.
	 **/
	public static function is_unit_structure_visible( $course_id, $unit_id, $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id );

		if (  $can_update_course ) {
			// Always make the unit visible to administrator, instructors and facilitators.
			return true;
		}

		$structure_visibility = CoursePress_Data_Course::structure_visibility( $course_id );

		return ! empty( $structure_visibility['structure'][ $unit_id ] );
	}

	/**
	 * Get the unit availability date in GMT timezone.
	 *
	 * @since 2.0
	 *
	 * @param int $unit_id
	 * @param int $course_id
	 * @param string $date_format Optional. Define the date format of return value.
	 *                NOTE: If a date format is defined then the function will not
	 *                return an empty value for units that are available today!
	 *
	 * @return string $date The unit becomes available or null. Date is in GMT timezone.
	 **/
	public static function get_unit_availability_date( $unit_id, $course_id, $date_format = null, $student_id = 0 ) {

		global $CoursePress_Core;

		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );

		$is_open_ended = coursepress_course_get_setting( $course_id, 'course_open_ended' );
		$course_start = coursepress_course_get_setting( $course_id, 'course_start_date' );
		$course_end = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$start_date = $CoursePress_Core->strtotime( $course_start ); // Converts date to UTC.
		$end_date = $CoursePress_Core->strtotime( $course_end ); // Converts date to UTC.
		$is_open_ended = coursepress_is_true( $is_open_ended );

		// Use common current timestamp for CP
		$always_return_date = false; // Return empty value if unit is available!
		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
		} else {
			$always_return_date = true; // Return formatted date, even when unit is available.
		}
		$now = $CoursePress_Core->date_time_now();
		$availability_date = '';
		$return_date = $start_date; // UTC value.

		// Check for course start/end dates.
		if ( $now < $start_date ) {
			// 1. Start date reached?
			$is_available = false;
			$availability_date = date_i18n( $date_format, $start_date );
		} elseif ( ! $is_open_ended && $now > $end_date ) {
			// 2. End date reached?
			// Check if student is currently enrolled
			$is_student = is_wp_error( $student ) ? false : $student->is_enrolled_at( $course_id );

			if ( $is_student ) {
			} else {
				$availability_date = 'expired';
			}
		}

		// Course is active today, so check for unit-specific limitations.
		$status_type = get_post_meta( $unit_id, 'unit_availability', true );

		if ( 'after_delay' == $status_type ) {
			$delay_val = get_post_meta( $unit_id, 'unit_delay_days', true );
			$delay_days = (int) $delay_val;

			if ( $delay_days > 0 ) {

				// Delay is added to the base-date. In future this could be
				// changed to enrollment date or completion of prev-unit, etc.
				$base_date = $CoursePress_Core->strtotime( $course_start ); // UTC value.
				$release_date = $base_date + ($delay_days * DAY_IN_SECONDS);
				$return_date = $release_date; // UTC value.

				if ( $now < $release_date ) {
					$availability_date = date_i18n( $date_format, $release_date );
				}
			}
		} elseif ( 'on_date' == $status_type ) {
			$due_on = get_post_meta( $unit_id, 'unit_availability_date', true );
			$due_date = $CoursePress_Core->strtotime( $due_on ); // UTC value.
			$return_date = $due_date; // UTC value.

			// Unit-Start date reached?
			if ( $now < $due_date ) {
				$availability_date = date_i18n( $date_format, $due_date );
			}
		}

		if ( $always_return_date ) {
			return $return_date;
		}

		return $availability_date;
	}

	/**
	 * Check if page is visible
	 *
	 * @since 2.0
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $page_number
	 * @param int $user_id Optional. Will use current user ID if empty.
	 *
	 * @return bool Returns true if page is set to be visible otherwise false.
	 **/
	public static function is_page_structure_visible( $course_id, $unit_id, $page_number, $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id );

		if (  $can_update_course ) {
			// Always make the unit visible to administrator, instructors and facilitators.
			return true;
		}

		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );
		$page_modules = coursepress_get_array_val( $visibility, 'structure/' . $unit_id . '/' . $page_number );

		return ! empty( $page_modules );
	}

	/**
	 * Check if module structure is visible.
	 *
	 * @since 2.0
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $module_id
	 * @param int $user_id Optional. Will use current user ID if empty.
	 *
	 * @return bool Returns true if the module check set to be visible otherwise false.
	 **/
	public static function is_module_structure_visible( $course_id, $unit_id, $module_id, $user_id = 0 ) {

		if ( ! empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id );

		if (  $can_update_course ) {
			// Always make the unit visible to administrator, instructors and facilitators.
			return true;
		}

		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );
		if ( ! empty( $visibility['structure'][ $unit_id ] ) && is_array( $visibility['structure'][ $unit_id ] ) ) {
			$unit_modules = $visibility['structure'][ $unit_id ];

			foreach ( $unit_modules as $page_number => $modules ) {
				if ( ! empty( $modules[ $module_id ] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get previous unit ID.
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 *
	 * @return bool
	 */
	public static function get_previous_unit_id( $course_id, $unit_id ) {

		$previous_unit = false;
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$status = array( 'publish' );

		if ( $can_update_course ) {
			$status[] = 'draft';
		}

		$units = CoursePress_Data_Course::get_units( $course_id, $status );
		$valid = true;

		if ( $units ) {
			foreach ( $units as $unit_index => $unit ) {
				if ( $unit->ID === $unit_id ) {
					$valid = false;
					continue;
				}

				if ( $valid ) {
					$previous_unit = $unit;
				}
			}
		}

		return $previous_unit ? $previous_unit->ID : false;
	}

	/**
	 * Number of required modules.
	 *
	 * Return number of required modules based on unit id.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit id.
	 *
	 * @return integer Number of required modules.
	 */
	public static function get_number_of_mandatory( $unit_id ) {

		$modules = CoursePress_Data_Course::get_unit_modules( $unit_id );
		$found = 0;

		if ( $modules ) {
			foreach ( $modules as $module ) {
				$module_id = $module->ID;
				$attributes = CoursePress_Data_Module::attributes( $module_id );

				if ( ! empty( $attributes['mandatory'] ) ) {
					$found++;
				}
			}
		}

		return $found;
	}

	/**
	 * @param $course
	 * @param $unit
	 * @param int $previous_unit
	 *
	 * @return array
	 */
	public static function get_unit_availability_status( $course, $unit, $previous_unit = 0 ) {

		$status = array(
			'mandatory_required' => array(
				'enabled' => false,
				'result' => false,
			),
			'completion_required' => array(
				'enabled' => false,
				'result' => false,
			),
			'passed_required' => array(
				'enabled' => false,
				'result' => false,
			),
		);

		if ( ! is_object( $unit ) ) {
			$unit = get_post( $unit );
		}

		// Check it... is a post at all?
		if ( ! is_a( $unit, 'WP_Post' ) ) {
			return $status;
		}

		$course_id = is_object( $course ) ? $course->ID : (int) $course;

		$unit_id = $unit->ID;
		$previous_unit_id = false;
		if ( $previous_unit ) {
			$previous_unit_id = is_object( $previous_unit ) ? $previous_unit->ID : (int) $previous_unit ;
		}

		$student = coursepress_get_user();
		$student_id = $student->__get( 'ID' );
		$is_student = $student->is_enrolled_at( $course_id );
		$due_date = self::get_unit_availability_date( $unit_id, $course_id );
		$is_available = empty( $due_date ) || ( 'expired' === $due_date && $is_student );

		// Check if previous has conditions.
		$force_current_unit_completion = false;
		$force_current_unit_successful_completion = false;

		if ( $previous_unit_id ) {
			$force_current_unit_completion = coursepress_is_true( get_post_meta( $previous_unit_id, 'force_current_unit_completion', true ) );
			$force_current_unit_successful_completion = coursepress_is_true( get_post_meta( $previous_unit_id, 'force_current_unit_successful_completion', true ) );
		}

		/**
		 * If there is NO MANDATORY modules, then this parameter can not be
		 * true!
		 */
		if ( $previous_unit_id && $force_current_unit_completion ) {
			$number_of_mandatory = self::get_number_of_mandatory( $previous_unit_id );

			if ( 0 == $number_of_mandatory ) {
				$force_current_unit_completion = false;
				$force_current_unit_successful_completion = false;
			}
		}

		if ( $previous_unit_id && $is_available ) {
			$student_progress = $student->get_completion_data( $course_id );
			$mandatory_done = CoursePress_Data_Student::is_mandatory_done( $student_id, $course_id, $previous_unit_id, $student_progress );

			$unit_completed = CoursePress_Data_Student::is_unit_complete(
				$student_id, $course_id, $previous_unit_id, $student_progress
			);

			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'mandatory_required/enabled', $force_current_unit_completion
			);
			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'mandatory_required/result', $mandatory_done
			);

			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'completion_required/enabled', $force_current_unit_successful_completion
			);
			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'completion_required/result', $unit_completed
			);

			if ( $status['completion_required']['enabled'] ) {
				$is_available = $status['completion_required']['result'];
			} elseif ( $status['mandatory_required']['enabled'] ) {
				$is_available = $status['mandatory_required']['result'];
			}

			/**
			 * User also needs to pass all required assessments
			 *
			 * @since 2.0.6
			 */
			if ( $is_available && $force_current_unit_successful_completion ) {
				$is_available = CoursePress_Data_Student::unit_answers_are_correct( $student_id, $course_id, $previous_unit );
				CoursePress_Helper_Utility::set_array_value( $status, 'passed_required/enabled', true );
				CoursePress_Helper_Utility::set_array_value( $status, 'passed_required/result', $is_available );
			}
		}

		/**
		 * Perform action if unit is available.
		 *
		 * @since 1.2.2
		 * */
		do_action( 'coursepress_unit_availble', $is_available, $unit_id );

		/**
		 * Return filtered value.
		 *
		 * Can be used by other plugins to filter unit availability.
		 *
		 * @since 1.2.2
		 * */
		$is_available = apply_filters(
			'coursepress_filter_unit_availability',
			$is_available,
			$unit_id
		);
		$status['available'] = $is_available;

		return $status;
	}

	/**
	 * Get unit id from name.
	 *
	 * @param string $slug
	 * @param int $id_only
	 * @param string $post_parent
	 *
	 * @return array|bool|int|null|WP_Post
	 */
	public static function by_name( $slug, $id_only, $post_parent = '' ) {

		$res = false;

		// First try to fetch the unit by the slug (name).
		$args = array(
			'name' => $slug,
			'post_type' => 'unit',
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		if ( $id_only ) {
			$args['fields'] = 'ids';
		}

		if ( $post_parent ) {
			$args['post_parent'] = (int) $post_parent;
		}

		$post = get_posts( $args );

		if ( $post ) {
			$res = $post[0];
		} else {
			// If we did not find a unit by name, try to fetch it via ID.
			$post = get_post( $slug );
			// Check it... is a post at all?
			if ( ! is_a( $post, 'WP_Post' ) ) {
				return $res;
			}
			if ( 'unit' == $post->post_type ) {
				if ( $id_only ) {
					$res = $post->ID;
				} else {
					$res = $post;
				}
			}
		}

		return $res;
	}

	/**
	 * Get course page meta.
	 *
	 * @param int $unit_id Unit ID.
	 * @param int $item_id Item ID.
	 *
	 * @return array
	 */
	public static function get_page_meta( $unit_id, $item_id ) {

		if ( empty( $item_id ) || empty( $unit_id ) ) {
			return array(
				'title' => '',
				'description' => '',
				'feature_image' => '',
				'visible' => false,
			);
		}

		$unit_id = is_object( $unit_id ) ? $unit_id->ID : (int) $unit_id;

		$meta = get_post_meta( $unit_id );
		$titles = isset( $meta['page_title'] ) && ! empty( $meta['page_title'] ) ? maybe_unserialize( $meta['page_title'][0] ) : array();
		$descriptions = isset( $meta['page_description'] ) && ! empty( $meta['page_description'] ) ? maybe_unserialize( $meta['page_description'][0] ) : array();
		$images = isset( $meta['page_feature_image'] ) && ! empty( $meta['page_feature_image'] ) ? maybe_unserialize( $meta['page_feature_image'][0] ) : array();
		$visibilities = isset( $meta['show_page_title'] ) && ! empty( $meta['show_page_title'] ) ? maybe_unserialize( $meta['show_page_title'][0] ) : array();

		$return = array(
			'title' => $titles[ 'page_' . $item_id ],
			'description' => isset( $descriptions[ 'page_' . $item_id ] ) ? $descriptions[ 'page_' . $item_id ] : '',
			'feature_image' => isset( $images[ 'page_' . $item_id ] ) ? $images[ 'page_' . $item_id ] : '',
		);

		if ( isset( $visibilities[ ( $item_id - 1 ) ] ) ) {
			$return['visible'] = $visibilities[ ( $item_id - 1 ) ];
		}

		return $return;
	}

	/**
	 * Get course ID by unit
	 *
	 * @since 2.0.0
	 *
	 * @param integer/WP_Post $unit unit ID or unit WP_Post object.
	 *
	 * @return integer Returns course id.
	 */
	public static function get_course_id_by_unit( $unit ) {

		if ( ! is_object( $unit ) && preg_match( '/^\d+$/', $unit ) ) {
			$unit = get_post( $unit );
		}

		// Check it... is a post at all?
		if ( ! is_a( $unit, 'WP_Post' ) ) {
			return 0;
		}

		if ( $unit->post_type == 'unit' ) {
			return $unit->post_parent;
		}

		return 0;
	}

	/**
	 * Check if
	 * @param $course
	 * @param $unit
	 * @param $previous_unit
	 * @param bool $status
	 *
	 * @return bool|mixed
	 */
	public static function is_unit_available( $course, $unit, $previous_unit, $status = false ) {

		if ( ! $status ) {
			$status = self::get_unit_availability_status( $course, $unit, $previous_unit );
		}

		return isset( $status['available'] )? $status['available'] : false;
	}
}
