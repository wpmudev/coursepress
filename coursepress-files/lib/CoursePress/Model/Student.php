<?php

class CoursePress_Model_Student {

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
		$meta = get_user_meta( $user_id );
		if ( $meta ) {
			// Get only the enrolled courses
			$meta = array_filter( array_keys( $meta ), array( __CLASS__, 'filter_course_meta_array' ) );
			// Map only the course IDs back to the array
			$meta = array_map( array( __CLASS__, 'course_id_from_meta' ), $meta );
		}

		return $meta;
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
		$prefix       = $wpdb->prefix;
		$base_prefix  = $wpdb->base_prefix;
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
					$blog_id   = $current_blog;
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

	/**
	 * Get the IDs of enrolled courses.
	 *
	 * @uses Student::get_course_enrollment_meta()
	 * @return array Contains enrolled course IDs.
	 */
	public static function get_enrolled_courses_ids( $student_id ) {
		return self::get_course_enrollment_meta( $student_id );
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

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/visited_pages/' . $page, $page );
		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/last_visited_page', $page );
		self::update_completion_data( $student_id, $course_id, $data );

		return $data;

	}

	public static function module_response( $student_id, $course_id, $unit_id, $module_id, $response, &$data = false ) {

		$attributes = CoursePress_Model_Module::module_attributes( $module_id );

		if ( empty( $attributes ) || 'output' === $attributes['mode'] ) {
			return;
		}

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$grade = -1;

		// Auto-grade the easy ones
		switch( $attributes['module_type'] ) {
			case 'input-checkbox':
				$total = count( $attributes['answers_selected'] );
				$correct = 0;
				if( is_array( $response ) ) {
					foreach( $response as $answer ) {
						if( in_array( $answer, $attributes['answers_selected'] ) ) {
							$correct += 1;
						}
					}
				}

				$grade = (int) ( $correct / $total * 100 );

				break;
			case 'input-select':
			case 'input-radio':
				if ( (int) $response === (int) $attributes['answers_selected'] ) {
					$grade = 100;
				} else {
					$grade = 0;
				}
				break;

		}
		$grade = apply_filters( 'coursepress_autograde_module_response', $grade, $module_id, $student_id );

		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/responses/' . $module_id . '/', $response );
		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/grades/' . $module_id . '/', $grade );
		CoursePress_Helper_Utility::set_array_val( $data, 'units/' . $unit_id . '/feedback/' . $module_id . '/', '' );
		self::update_completion_data( $student_id, $course_id, $data );

		return $data;

	}

	public static function get_grade( $student_id, $course_id, $unit_id, $module_id, $response_index = false, &$data = false ) {

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$grades = CoursePress_Helper_Utility::get_array_val( $data, 'units/' . $unit_id . '/grades/' . $module_id );

		// Get last grade
		if( ! $response_index ) {
			$response_index = ( count( $grades ) - 1 );
		}

		return ! empty( $grades ) && isset( $grades[ $response_index ] ) ? $grades[ $response_index ] : false;

	}

	public static function get_feedback( $student_id, $course_id, $unit_id, $module_id, $response_index = false, &$data = false ) {

		if ( false === $data ) {
			$data = self::get_completion_data( $student_id, $course_id );
		}

		$feedback = CoursePress_Helper_Utility::get_array_val( $data, 'units/' . $unit_id . '/feedback/' . $module_id );

		// Get last grade
		if( ! $response_index ) {
			$response_index = ( count( $feedback ) - 1 );
		}

		return ! empty( $feedback ) && isset( $feedback[ $response_index ] ) ? $feedback[ $response_index ] : false;

	}


}
