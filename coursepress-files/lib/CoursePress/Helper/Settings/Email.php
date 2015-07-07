<?php

class CoursePress_Helper_Settings_Email {


	public static function get_defaults( $context = false ) {

		$defaults = apply_filters( 'coursepress_default_email_settings', array(
			'basic_certificate' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Congratulations. You passed your course.', CoursePress::TD ),
				'content' => '',
				'auto_email' => true,
			),
			'registration' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Registration Status', CoursePress::TD ),
				'content' => '',
			),
			'enrollment_confirm' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Enrollment Confirmation', CoursePress::TD ),
				'content' => '',
			),
			'course_invitation' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Invitation to a Course', CoursePress::TD ),
				'content' => '',
			),
			'course_invitation_password' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', CoursePress::TD ),
				'content' => '',
			),
			'instructor_invitation' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Invitation to be an instructor at CoursePress Pro', CoursePress::TD ),
				'content' => '',
			),
			'new_order' => array(
				'from_name' => get_option( 'blogname' ),
				'from_email' => get_option( 'admin_email' ),
				'subject' => __( 'Order Confirmation', CoursePress::TD ),
				'content' => '',
			),

		) );


		if( $context && isset( $defaults[ $context ] ) ) {
			return $defaults[ $context ];
		} else {
			return $defaults;
		}

	}

	public static function get_settings_sections() {
		$defaults = apply_filters( 'coursepress_default_email_settings_sections', array(
			'basic_certificate' => array(
				'title' => __( 'Basic Certificate E-mail', CoursePress::TD ),
				'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, FIRST_NAME, LAST_NAME, COURSE_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER, UNIT_LIST', CoursePress::TD ),
				'order' => 7,
			),
			'registration' => array(
				'title' => __( 'User Registration E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon account registration.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, STUDENT_USERNAME, STUDENT_PASSWORD, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS', CoursePress::TD ),
				'order' => 1,
			),
			'enrollment_confirm' => array(
				'title' => __( 'Course Enrollment Confirmation E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon enrollment.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS', CoursePress::TD ),
				'order' => 2,
			),
			'course_invitation' => array(
				'title' => __( 'Student Invitation to a Course E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon receiving an invitation to a course.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS', CoursePress::TD ),
				'order' => 3,
			),
			'course_invitation_password' => array(
				'title' => __( 'Student Invitation to a Course E-mail (with passcode)', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon receiving an invitation (with passcode) to a course.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: STUDENT_FIRST_NAME, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, PASSCODE', CoursePress::TD ),
				'order' => 4,
			),
			'instructor_invitation' => array(
				'title' => __( 'Instructor Invitation Email', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail an instructor will get upon receiving an invitation.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: INSTRUCTOR_FIRST_NAME, INSTRUCTOR_LAST_NAME, INSTRUCTOR_EMAIL, CONFIRMATION_LINK, COURSE_NAME, COURSE_EXCERPT, COURSE_ADDRESS, WEBSITE_ADDRESS, WEBSITE_NAME', CoursePress::TD ),
				'order' => 5,
			),
			'new_order' => array(
				'title' => __( 'New Order E-mail', CoursePress::TD ),
				'description' => __( 'Settings for an e-mail student get upon placing an order.', CoursePress::TD ),
				'content_help_text' => __( 'These codes will be replaced with actual data: CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL', CoursePress::TD ),
				'order' => 6,
			),

		) );

		return $defaults;
	}




}