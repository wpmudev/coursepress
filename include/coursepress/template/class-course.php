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
}
