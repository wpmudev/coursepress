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
	 * @param (int) $course_id
	 * @param (int) $unit_id
	 * @param (int) $page_number
	 * @param (int) $module_id
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
				} elseif ( 'completion_page' == $last_visited['page'] ) {
					$link = $course_url . 'course-completion';
				}
			}
		}

		return $link;
	}
}
