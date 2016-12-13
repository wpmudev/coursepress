<?php

class CoursePress_Helper_TemplateTag {
	public static function init() {
	}
}

if ( ! function_exists( 'cp_student_login_address' ) ) {

	/**
	 * Returns the URL to the student login page.
	 *
	 * @since  1.0.0
	 * @return string The URL.
	 */
	function cp_student_login_address() {
		if ( get_option( 'use_custom_login_form', 1 ) ) {
			$slug = CoursePress_Core::get_setting( 'slugs/login', 'student-login' );
			$login_url = home_url( '/' . $slug . '/' );
		} else {
			$login_url = wp_login_url();
		}

		return esc_url_raw( $login_url );
	}
}

if ( ! function_exists( 'cp_write_log' ) ) {

	/**
	 * Debugging function.
	 * Output the message/object to the default php error log
	 *
	 * @since  1.0.0
	 * @param  mixed $message Any variable, even object or array is possible.
	 */
	function cp_write_log( $message ) {
		if ( defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ) {
			$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		} else {
			$trace = debug_backtrace( false );
		}
		$exception = new Exception();
		$debug = array_shift( $trace );
		$caller = array_shift( $trace );
		$exception = $exception->getTrace();
		$callee = array_shift( $exception );

		if ( true === WP_DEBUG ) {
			$class = isset( $caller['class'] ) ? $caller['class'] . '[' . $callee['line'] . '] ' : '';
			if ( ! is_scalar( $message ) ) {
				$message = print_r( $message, true );
			}
			error_log( $class . $message . ' In ' . $callee['file'] . ' on line ' . $callee['line'] );
		}
	}
}

if ( ! function_exists( 'cp_flush_rewrite_rules' ) ) {

	/**
	 * flush_rewrite_rules() wrapper for CoursePress.
	 *
	 * Used to wrap flush_rewrite_rules() so that rewrite flushes can
	 * be prevented in given environments.
	 *
	 * E.g. If we've got CampusPress/Edublogs then this method will have
	 * an early exit.
	 *
	 * @since 1.2.1
	 */
	function cp_flush_rewrite_rules() {
		if ( CP_IS_CAPUS ) { return; }

		flush_rewrite_rules();
	}
}

if ( ! function_exists( 'cp_is_true' ) ) {

	/**
	 * Evaluate if the specified value translates to boolean TRUE.
	 *
	 * True:
	 * - Boolean true
	 * - Number other than 0
	 * - Strings 'yes', 'on', 'true'
	 *
	 * @since  2.0.0
	 * @param  mixed $value Value to evaluate.
	 * @return bool
	 */
	function cp_is_true( $value ) {
		if ( ! $value ) {
			// Handles: null, 0, '0', false, ''.
			return false;
		}

		if ( true === $value ) {
			return true;
		}

		if ( ! is_scalar( $value ) ) {
			// Arrays, objects, etc. always evaluate to false.
			return false;
		}

		if ( is_numeric( $value ) ) {
			// A number other than 0 is true.
			return true;
		}

		$value = strtolower( (string) $value );

		if ( 'on' == $value || 'yes' == $value || 'true' == $value ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'cp_can_access_course' ) ) {
	/**
	 * Check user access to course.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID
	 * @return boolean User can or can not.
	 */
	function cp_can_access_course( $course_id ) {
		if ( empty( $course_id ) ) {
			$course_id = CoursePress_Helper_Utility::the_course( true );

			if ( empty( $course_id ) ) {
				return; // Simply return to avoid fatal error
			}
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( get_permalink( $course_id ) );
			exit;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		/**
		 * check student
		 */
		if ( CoursePress_Data_Student::is_enrolled_in_course( get_current_user_id(), $course_id ) ) {
			return true;
		}

		/**
		 * check instructor
		 */
		if ( CoursePress_Data_Instructor::is_assigned_to_course( get_current_user_id(), $course_id ) ) {
			return true;
		}

		wp_safe_redirect( get_permalink( $course_id ) );
		exit;

	}
}

/**
 * Template Tag is academy page
 */
if ( !function_exists( 'cp_is_academy_page' ) ) {
	function cp_is_academy_page() {
		$post_types = array(
			'course',
			'course_archive',
			'course_discussion',
			'course_discussion_archive',
			'course_notifications_archive',
			'coursepress_instructor',
			'coursepress_student_dashboard',
			'course_student_dashboard',
			'course_workbook',
			'discussions',
			'module',
			'notifications',
			'unit',
			'unit_archive',
		);
		if ( is_singular( $post_types ) ) {
			return true;
		}
		/**
		 * custom pages from CoursePress
		 */
		if( is_page() ) {
			$pages = CoursePress_Core::get_setting('pages');
			foreach( $pages as $key => $id ) {
				if ( 0 == $id ) {
					continue;
				}
				return true;
			}
		}
		return false;
	}

}

