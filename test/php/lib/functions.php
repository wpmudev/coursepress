<?php

function coursepress_helper_instructor() {
	$instructor = get_user_by( 'login', 'instructor' );
	if ( false === $instructor ) {
		$userdata = array(
			'user_login'  => 'instructor',
			'user_url'    => 'https://premium.wpmudev.org/',
			'user_pass'   => 'instructor',
			'first_name'  => 'Jon',
			'last_name'   => 'Snow',
			'nickname'    => 'bastard',
			'description' => 'Winter is comming.',
			'user_email'  => 'snow@winterfell.com',
		);
		$user_id = wp_insert_user( $userdata );
		$instructor = get_userdata( $user_id );
	}
	return $instructor;
}

function coursepress_helper_student() {
	$student = get_user_by( 'login', 'student' );
	if ( false === $student ) {
		$userdata = array(
			'user_login'  => 'student',
			'user_url'    => 'https://premium.wpmudev.org/',
			'user_pass'   => 'student',
			'first_name'  => 'Albert',
			'last_name'   => 'Einstein',
			'nickname'    => 'brain',
			'description' => 'E=mc^2',
			'user_email'  => 'einstein@example.com',
		);
		$user_id = wp_insert_user( $userdata );
		$student = get_userdata( $user_id );
	}
	return $student;
}

function coursepress_helper_course( $admin_id ) {
	$course = get_page_by_title( 'test course title', OBJECT, CoursePress_Data_Course::get_post_type_name() );
	if ( empty( $course ) ) {
		$course = (object) array(
			'post_author' => $admin_id,
			'post_status' => 'private',
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'course_excerpt' => 'test course excerpt',
			'course_description' => 'test course content',
			'course_name' => 'test course title',
		);
		$course_id = CoursePress_Data_Course::update( false, $course );
		$course = get_post( $course_id );
	}
	return $course;
}
