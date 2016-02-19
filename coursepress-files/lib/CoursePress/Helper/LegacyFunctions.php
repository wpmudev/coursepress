<?php

/**
 * LEGACY, still needed for now.
 *
 * @todo: needs to be replaced and removed soon
 */

class CoursePress_Helper_LegacyFunctions {
	public static function init() {
	}
}

if ( !function_exists( 'cp_set_last_visited_unit_page' ) ) {

	function cp_set_last_visited_unit_page( $unit_id = false, $page_num = false, $student_id = false ) {
		if ( !$unit_id ) {
			return false;
		}
		if ( !$student_id ) {
			$student_id = get_current_user_ID();
		}
		$global_option = !is_multisite();
		update_user_option( $student_id, 'last_visited_unit_' . $unit_id . '_page', $page_num, $global_option );
	}

}

if ( !function_exists( 'cp_set_visited_course' ) ) {

	function cp_set_visited_course( $unit_id, $student_id = false ) {

		if ( !$student_id ) {
			$student_id = get_current_user_ID();
		}

		$course_id = wp_get_post_parent_id( $unit_id );
		$visited_courses = get_user_option( 'visited_course_units_' . $course_id, $student_id );

		if ( $visited_courses === false ) {
			$visited_courses = $course_id;
		} else {
			$visited_courses = explode( ',', $visited_courses );
			if ( !in_array( $course_id, $visited_courses ) ) {
				$visited_courses[] = $course_id;
			}
			$visited_courses = implode( ',', $visited_courses );
		}
		$global_option = !is_multisite();
		update_user_option( $student_id, 'visited_course_units_' . $course_id, $visited_courses, $global_option );
	}

}

if ( !function_exists( 'cp_set_visited_unit_page' ) ) {

	function cp_set_visited_unit_page( $unit_id = false, $page_num = false, $student_id = false, $course_id = false ) {

		if ( !$unit_id ) {
			return false;
		}
		if ( !$student_id ) {
			$student_id = get_current_user_ID();
		}
		if ( !$course_id ) {
			$course_id = do_shortcode( '[get_parent_course_id' );
		}

		Student_Completion::record_visited_page( $student_id, $course_id, $unit_id, $page_num );

		// Legacy, needed still

		$visited_pages = get_user_option( 'visited_unit_pages_' . $unit_id . '_page', $student_id );

		if ( $visited_pages === false ) {
			$visited_pages = $page_num;
		} else {
			$visited_pages = explode( ',', $visited_pages );
			if ( !in_array( $page_num, $visited_pages ) ) {
				$visited_pages[] = $page_num;
			}
			$visited_pages = implode( ',', $visited_pages );
		}

		$global_option = !is_multisite();
		update_user_option( $student_id, 'visited_unit_pages_' . $unit_id . '_page', $visited_pages, $global_option );
		cp_set_visited_course( $unit_id, $student_id );
		cp_set_last_visited_unit_page( $unit_id, $page_num, $student_id );
	}

}

if ( !function_exists( 'cp_messaging_get_unread_messages_count' ) ) {

	function cp_messaging_get_unread_messages_count() {
		global $wpdb, $user_ID;
		$tmp_unread_message_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "messages WHERE message_to_user_ID = %d AND message_status = %s", $user_ID, 'unread' ) );

		return $tmp_unread_message_count;
	}

}

