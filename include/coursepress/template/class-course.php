<?php
/**
 * Course Template
 **/
class CoursePress_Template_Course {

	public static function course_instructors() {
		$content = '[COURSE INSTRUCTORS]';

		return $content;
	}

	public static function course_archive() {
		return do_shortcode( '[course_archive]' );
	}

	public static function course() {
		return do_shortcode( '[course_page]' );
	}

	public static function the_course( $course_id = 0 ) {
		if ( empty( $course_id ) ) {
			// Check if it is in course page
			$course = CoursePress_Helper_Utility::the_course();
			$course_id = $course->ID;
		} else {
			$course = get_post( $course_id );
		}

	}
}
