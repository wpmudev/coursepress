<?php
class CoursePressData {
	public static function course_data( $args = array() ) {
		$course = array(
			'post_type' => 'course',
			'post_status' => 'publish',
			'post_title' => 'Course',
		);
		$course = wp_parse_args( $args, $course );
	}
}