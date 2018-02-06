<?php

class CoursePress_Data_Unit {


	public static function get_post_type_name() {

		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

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
				$time = CoursePress_Helper_Utility::get_time( $seconds, $minutes, $hours );
				// Increase page time.
				$page_seconds += $time['total_seconds'];
			}

			// Page time.
			$time = CoursePress_Helper_Utility::get_time( $page_seconds );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/estimation', $time['time'] );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $time['hours'] );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/components/minutes', $time['minutes'] );
			$estimations = coursepress_set_array_val( $estimations, 'pages/' . $page_id . '/components/seconds', $time['seconds'] );
			// Increase unit time.
			$unit_seconds += $time['total_seconds'];
		}

		// Unit time.
		$time = CoursePress_Helper_Utility::get_time( $unit_seconds );
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
}
