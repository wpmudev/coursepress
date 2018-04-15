<?php
/**
 * CoursePress Student Data Class
 *
 * Use to manage the student's course information/data.
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Data_Student {

	/**
	 * Record the last time the student visited the course
	 *
	 * @since 2.0.5
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $page_number
	 * @param int $module_id
	 **/
	public static function log_visited_course( $course_id, $unit_id = 0, $page_number = 1, $module_id = 0 ) {
		/**
		 * Do nothing if there is no user.
		 */
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( empty( $course_id ) ) {
			return;
		}

		$key = 'coursepress_last_visited_' . $course_id;
		$value = array(
			'unit' => $unit_id,
			'page' => $page_number,
			'module' => $module_id,
		);

		update_user_meta( get_current_user_id(), $key, $value );
	}

	/**
	 * Returns the permalink of the last visited page of the course.
	 *
	 * @since 2.0.5
	 *
	 * @param (int) $course_id
	 *
	 * @return Returns permalink of the last visited page otherwise the units overview page.
	 **/
	public static function get_last_visited_url( $course_id ) {

		$course_url = coursepress_get_main_courses_url();
		$course = coursepress_get_course( $course_id );

		if ( is_wp_error( $course ) ) {
			return $course_url;
		} else {
			$course_url = $course->get_permalink();
		}

		// If there is no user, return course URL.
		if ( ! is_user_logged_in() ) {
			return $course->get_permalink();
		}

		$key = 'coursepress_last_visited_' . $course_id;
		$link = $course->get_permalink() . coursepress_get_setting( 'units/', 'units/' );

		$last_visited = get_user_meta( get_current_user_id(), $key, true );
		$last_visited = is_array( $last_visited ) ? array_filter( $last_visited ) : array();

		if ( ! empty( $last_visited ) ) {
			// Get unit url
			if ( ! empty( $last_visited['unit'] ) ) {
				$unit = coursepress_get_unit( $last_visited['unit'] );
				$link = $unit->get_permalink();

				// Add page number
				if ( ! empty( $last_visited['page'] ) && (int) $last_visited['page'] > 0 ) {
					$page = max( 1, (int) $last_visited['page'] );
					$link .= 'page/' . $page . '/';

					// Add module ID
					if ( ! empty( $last_visited['module'] ) ) {
						$link .= 'module_id/' . (int) $last_visited['module'];
					}
				} elseif ( 'completion_page' === $last_visited['page'] ) {
					$link = $course_url . 'course-completion';
				}
			}
		}

		return $link;
	}

	/**
	 * Check if a section is seen.
	 *
	 * @since 2.0
	 *
	 * @param int $course_id
	 * @param int $unit_id
	 * @param int $page The page/section number
	 * @param int $student_id Optional. Will use current user ID if empty.
	 *
	 * @return bool Returns true if all modules are seen, answered and completed otherwise false.
	 **/
	public static function is_section_seen( $course_id, $unit_id, $page, $student_id = 0 ) {

		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );

		$page = 0 === (int) $page ? 1 : $page;

		// Check if student is enrolled
		$is_enrolled = is_wp_error( $student ) ? false : $student->is_enrolled_at( $course_id );

		if ( ! $is_enrolled ) {
			return false;
		}

		$student_progress = $student->get_completion_data( $course_id );
		$is_unit_visited = coursepress_get_array_val( $student_progress, 'units/' . $unit_id . '/visited_pages/' . $page );
		$completed = ! empty( $is_unit_visited );

		if ( ! $completed ) {
			// Check if one of the modules was visited.
			$modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => $page ) );

			if ( count( $modules ) > 0 ) {
				foreach ( $modules as $module_id ) {
					$is_module_seen = coursepress_get_array_val( $student_progress, 'completion/' . $unit_id . '/modules_seen/' . $module_id );
					if ( ! empty( $is_module_seen ) ) {
						return true;
					}
				}
			}
		}

		return $completed;
	}

	/**
	 * Get the IDs of enrolled courses.
	 *
	 * @param  int $student_id WP User ID.
	 *
	 * @uses Student::get_course_enrollment_meta()
	 *
	 * @return array Contains enrolled course IDs.
	 */
	public static function get_enrolled_courses_ids( $student_id ) {

		$student = coursepress_get_user( $student_id );

		if ( is_wp_error( $student ) ) {
			return array();
		}

		return $student->get_enrolled_courses_ids();
	}

	/**
	 * Return course stats.
	 *
	 * @param boolean $return_human_readable_label @since 2.0.3 return label instad "row" status - default true.
	 *
	 * @return string
	 */
	public static function get_course_status( $course_id, $student_id = 0, $return_human_readable_label = true ) {

		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );

		$student_progress = $student->get_completion_data( $course_id );

		$completed = coursepress_get_array_val( $student_progress, 'completion/completed' );
		$is_completed = ! empty( $completed );

		$labels = array(
			'certified' => __( 'Certified', 'cp' ),
			'failed' => __( 'Failed', 'cp' ),
			'awaiting-review' => __( 'Awaiting Review', 'cp' ),
			'ongoing' => __( 'Ongoing', 'cp' ),
			'incomplete' => __( 'Incomplete', 'cp' ),
		);

		if ( $is_completed ) {
			$return = 'certified';
		} else {
			$course_status = CoursePress_Data_Course::get_course_status( $course_id );
			$course_progress = self::get_course_progress( $student_id, $course_id, $student_progress );

			if ( 100 == $course_progress ) {
				$failed = coursepress_get_array_val( $student_progress, 'completion/failed' );

				if ( ! empty( $failed ) ) {
					$return = 'failed';
				} else {
					$return = 'awaiting-review';
				}
			} else {
				if ( 'open' === $course_status ) {
					$return = 'ongoing';
				} else {
					$return = 'incomplete';
				}
			}
		}

		if ( $return_human_readable_label ) {
			return $labels[ $return ];
		}

		return $return;
	}

	/**
	 * Get courses of a student.
	 *
	 * @param int $student_id
	 * @param array $courses
	 *
	 * @return array|void
	 */
	public static function my_courses( $student_id = 0, $courses = array() ) {

		global $coursepress_core;

		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$student = coursepress_get_user( $student_id );

		if ( empty( $courses ) ) {
			$course_ids = self::get_enrolled_courses_ids( $student_id );
			$courses = array_map( 'get_post', $course_ids );
		}

		$courses = array_filter( $courses );

		if ( empty( $courses ) ) {
			return;
		}

		$found_courses = array(
			'current' => array(),
			'completed' => array(),
			'incomplete' => array(),
			'future' => array(),
			'past' => array(),
		);

		$now = $coursepress_core->date_time_now();

		foreach ( $courses as $course ) {
			$course_id = $course->ID;
			$start_date = coursepress_course_get_setting( $course_id, 'course_start_date', 0 );
			$start_date = empty( $start_date ) ? $start_date : $coursepress_core->strtotime( $start_date );
			$end_date = coursepress_course_get_setting( $course_id, 'course_start_date', 0 );
			$end_date = empty( $end_date ) ? $end_date : $coursepress_core->strtotime( $end_date );
			$open_date = coursepress_course_get_setting( $course_id, 'course_open_ended', 0 );
			$is_open_ended = ! empty( $open_date );

			$completed = $student->is_course_completed( $course_id );

			if ( $completed ) {
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

	/**
	 * Returns the user response.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $step_id
	 * @param bool $progress
	 *
	 * @return array|mixed|null|string
	 */
	public static function average_course_responses( $student_id, $course_id, $data = false ) {

		if ( false === $data ) {
			$student = coursepress_get_user( $student_id );
			$data = $student->get_completion_data( $course_id );
		}

		$average = coursepress_get_array_val( $data, 'completion/average' );

		return (int) $average;
	}

	/**
	 * Get mandatory completion details.
	 *
	 * @param int $student_id Student ID.
	 * @param int $course_id Course ID.
	 * @param int $unit_id Unit ID.
	 * @param bool|array $data
	 *
	 * @return array
	 */
	public static function get_mandatory_completion( $student_id, $course_id, $unit_id, &$data = false ) {

		if ( false === $data ) {
			$student = coursepress_get_user( $student_id );
			$data = $student->get_completion_data( $course_id );
		}

		$completed = '';
		// Sanitize $unit_id.
		if ( ! empty( $unit_id ) && is_numeric( $unit_id ) ) {
			$completed = coursepress_get_array_val( $data, 'completion/' . $unit_id . '/completed_mandatory' );
		}

		return array(
			'required' => CoursePress_Data_Unit::get_number_of_mandatory( $unit_id ),
			'completed' => $completed,
		);
	}

	/**
	 * Check unit for mantadory.
	 *
	 * @param int $student_id Student ID.
	 * @param int $course_id Course ID.
	 * @param int $unit_id Unit ID.
	 * @param bool|array $data
	 *
	 * @return array
	 */
	public static function is_mandatory_done( $student_id, $course_id, $unit_id, &$data = false ) {

		if ( false === $data ) {
			$student = coursepress_get_user( $student_id );
			$data = $student->get_completion_data( $course_id );
		}

		// Sanitize $unit_id.
		if ( ! empty( $unit_id ) && is_numeric( $unit_id ) ) {

			$all_mandatory = coursepress_get_array_val( $data, 'completion/' . $unit_id . '/all_mandatory' );
			if ( $all_mandatory ) {
				$required_steps = coursepress_get_array_val( $data, 'completion/' . $unit_id . '/required_steps' );
				$completed = coursepress_get_array_val( $data, 'completion/' . $unit_id . '/completed_mandatory' );

				return (int) $completed === (int) $required_steps;
			}
		}

		return false;
	}

	/**
	 * Send email about successful account creation.
	 * The email contains several links but no login name or password.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $student_id The newly created WP User ID.
	 *
	 * @return bool True on success.
	 */
	public static function send_registration( $student_id, $user_data = array() ) {

		$student_data = get_userdata( $student_id );

		$email_args = array();
		$email_args['email'] = $student_data->user_email;
		$email_args['first_name'] = empty( $student_data->first_name ) && empty( $student_data->last_name ) ? $student_data->display_name : $student_data->first_name;
		$email_args['last_name'] = $student_data->last_name;
		$email_args['fields'] = array();
		$email_args['fields']['student_id'] = $student_id;
		$email_args['fields']['student_username'] = $student_data->user_login;
		$email_args['fields']['student_password'] = $student_data->user_pass;
		$email_args['fields']['password'] = ! empty( $user_data['password_txt'] ) ? $user_data['password_txt'] : '';

		$sent = CoursePress_Data_Email::send_email( CoursePress_Data_Email::REGISTRATION, $email_args );

		return $sent;
	}
}
