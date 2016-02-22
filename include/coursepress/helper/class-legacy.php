<?php
/**
 * LEGACY, still needed for now.
 *
 * @todo: needs to be replaced and removed soon
 */

class CoursePress_Helper_Legacy {
	public static function init() {
	}
}

if ( ! function_exists( 'cp_messaging_get_unread_messages_count' ) ) {
	function cp_messaging_get_unread_messages_count() {
		global $wpdb;

		$sql = '
		SELECT COUNT(1)
		FROM ' . $wpdb->base_prefix . 'messages
		WHERE message_to_user_ID = %d AND message_status = %s
		';

		$tmp_unread_message_count = $wpdb->get_var(
			$wpdb->prepare(
				$sql,
				get_current_user_id(),
				'unread'
			)
		);

		return $tmp_unread_message_count;
	}
}

if ( ! function_exists( 'cp_filter_content' ) ) {
	// Function moved.
	function cp_filter_content( $content, $none_allowed = false ) {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::filter_content() instead!' );
	}
}

if ( ! function_exists( 'cp_user_can_register' ) ) {
	// Function moved.
	function cp_user_can_register() {
		throw new Exception( 'Deprecated: Use CoursePress_Helper_Utility::user_can_register() instead!' );
	}
}

if ( ! function_exists( 'cp_get_user_option' ) ) {
	function cp_get_user_option( $option, $user_id = false ) {
		throw new Exception( 'Deprecated: Use WP core function get_user_option() instead!' );
	}
}

if ( ! function_exists( 'cp_can_see_unit_draft' ) ) {
	/**
	 * Check if the current user can see Course Drafts. By default only admin
	 * users can see drafts.
	 *
	 * @todo Move this function into the Capability class and deprecate this function!
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	function cp_can_see_unit_draft() {
		if ( ! is_user_logged_in() ) { return false; }
		if ( current_user_can( 'manage_options' ) ) { return true; }
		if ( current_user_can( 'coursepress_create_course_unit_cap' ) ) { return true; }

		return false;
	}
}

if ( ! function_exists( 'cp_set_last_visited_unit_page' ) ) {

	/**
	 * Save the given page-ID as "last visited unit-page" of the spcified user.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int $unit_id Unit ID.
	 * @param  int $page_id The page ID.
	 * @param  int $student_id WP User ID.
	 */
	function cp_set_last_visited_unit_page( $unit_id, $page_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$global_option = ! is_multisite();
		update_user_option(
			$student_id,
			'last_visited_unit_' . $unit_id . '_page',
			$page_id,
			$global_option
		);
	}
}

if ( ! function_exists( 'cp_get_last_visited_unit_page' ) ) {
	/**
	 * Return page ID of the last unit page that was viewed by given student.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int  $unit_id Unit ID.
	 * @param  bool $student_id WP User ID.
	 * @return int Unit page ID.
	 */
	function cp_get_last_visited_unit_page( $unit_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$global_option = ! is_multisite();
		$last_visited_unit_page = get_user_option(
			'last_visited_unit_' . $unit_id . '_page',
			$student_id
		);

		if ( $last_visited_unit_page ) {
			return (int) $last_visited_unit_page;
		} else {
			return 1;
		}
	}
}

if ( ! function_exists( 'cp_get_number_of_unit_pages_visited' ) ) {

	/**
	 * Return page ID of the last unit page that was viewed by given student.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int  $unit_id unknown.
	 * @param  bool $student_id Student ID.
	 * @return int Number of visited unit-pages.
	 */
	function cp_get_number_of_unit_pages_visited( $unit_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$visited_pages = get_user_option(
			'visited_unit_pages_' . $unit_id . '_page',
			$student_id
		);

		if ( $visited_pages ) {
			return count( explode( ',', $visited_pages ) ) - 1;
		} else {
			return 0;
		}
	}
}

if ( ! function_exists( 'cp_set_visited_course' ) ) {

	/**
	 * Mark the given course as "visited" for the specieid user.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int $unit_id A unit-ID of the course.
	 * @param  int $student_id WP User ID.
	 */
	function cp_set_visited_course( $unit_id, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) {  $student_id = get_current_user_ID(); }

		$course_id = wp_get_post_parent_id( (int) $unit_id );
		$visited_courses = get_user_option(
			'visited_course_units_' . $course_id,
			$student_id
		);

		if ( ! is_string( $visited_courses ) ) {
			$visited_courses = '';
		}

		$visited_courses = explode( ',', $visited_courses );

		if ( ! in_array( $course_id, $visited_courses ) ) {
			$visited_courses[] = $course_id;
			$visited_courses = implode( ',', $visited_courses );

			$global_option = ! is_multisite();
			update_user_option(
				$student_id,
				'visited_course_units_' . $course_id,
				$visited_courses,
				$global_option
			);
		}
	}
}

if ( ! function_exists( 'cp_set_visited_unit_page' ) ) {

	/**
	 * Mark a single unit-page as visited by the specified user.
	 *
	 * @todo  Migrate and use deprecated class.student.completion.php!
	 *        This function/logic is already in that file...
	 *
	 * @since  1.0.0
	 * @param  int $unit_id Unit ID.
	 * @param  int $page_num The page ID.
	 * @param  int $student_id WP User ID.
	 */
	function cp_set_visited_unit_page( $unit_id, $page_num, $student_id = 0 ) {
		$unit_id = (int) $unit_id;
		if ( ! $unit_id ) { return false; }

		if ( ! $student_id ) { $student_id = get_current_user_ID(); }

		$course_id = wp_get_post_parent_id( (int) $unit_id );
		// This is not migrated!
		// Student_Completion::record_visited_page( $student_id, $course_id, $unit_id, $page_num );

		// Legacy but still needed.

		$visited_pages = get_user_option(
			'visited_unit_pages_' . $unit_id . '_page',
			$student_id
		);

		if ( ! is_string( $visited_pages ) ) {
			$visited_pages = '';
		}

		$visited_pages = explode( ',', $visited_pages );

		if ( ! in_array( $page_num, $visited_pages ) ) {
			$visited_pages[] = $page_num;
			$visited_pages = implode( ',', $visited_pages );

			$global_option = ! is_multisite();
			update_user_option(
				$student_id,
				'visited_unit_pages_' . $unit_id . '_page',
				$visited_pages,
				$global_option
			);
			cp_set_visited_course(
				$unit_id,
				$student_id
			);
			cp_set_last_visited_unit_page(
				$unit_id,
				$page_num,
				$student_id
			);
		}
	}
}

