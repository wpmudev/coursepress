<?php

class CoursePress_Helper_Setting_Email {

	public static function get_defaults( $context = false ) {
		$defaults = apply_filters(
			'coursepress_default_email_settings',
			array(
				CoursePress_Helper_Email::BASIC_CERTIFICATE => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => CoursePress_View_Admin_Setting_BasicCertificate::default_email_subject(),
					'content' => CoursePress_View_Admin_Setting_BasicCertificate::default_email_content(),
					'auto_email' => true,
				),
				CoursePress_Helper_Email::REGISTRATION => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => __( 'Registration Status', 'CP_TD' ),
					'content' => self::_registration_email(),
				),
				CoursePress_Helper_Email::ENROLLMENT_CONFIRM => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => __( 'Enrollment Confirmation', 'CP_TD' ),
					'content' => self::_enrollment_confirmation_email(),
				),
				CoursePress_Helper_Email::COURSE_INVITATION => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => __( 'Invitation to a Course', 'CP_TD' ),
					'content' => self::_course_invitation_email(),
				),
				CoursePress_Helper_Email::COURSE_INVITATION_PASSWORD => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', 'CP_TD' ),
					'content' => self::_course_invitation_passcode_email(),
				),
				CoursePress_Helper_Email::INSTRUCTOR_INVITATION => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => sprintf( __( 'Invitation to be an instructor at %s', 'CP_TD' ), get_option( 'blogname' ) ),
					'content' => self::_instructor_invitation_email(),
				),
				CoursePress_Helper_Email::NEW_ORDER => array(
					'from_name' => get_option( 'blogname' ),
					'from_email' => get_option( 'admin_email' ),
					'subject' => __( 'Order Confirmation', 'CP_TD' ),
					'content' => self::_new_order_email(),
				),
			)
		);

		if ( $context && isset( $defaults[ $context ] ) ) {
			return $defaults[ $context ];
		} else {
			return $defaults;
		}
	}

	public static function get_settings_sections() {
		$basic_certificate_fields = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::BASIC_CERTIFICATE,
			array(
				'BLOG_NAME' => '',
				'LOGIN_ADDRESS' => '',
				'COURSES_ADDRESS' => '',
				'WEBSITE_ADDRESS' => '',
				'COURSE_ADDRESS' => '',
				'FIRST_NAME' => '',
				'LAST_NAME' => '',
				'COURSE_NAME' => '',
				'COMPLETION_DATE' => '',
				'CERTIFICATE_NUMBER' => '',
				'UNIT_LIST' => '',
			),
			null
		);
		$registration_fields = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::REGISTRATION,
			array(
				'STUDENT_FIRST_NAME' => '',
				'STUDENT_LAST_NAME' => '',
				'STUDENT_USERNAME' => '',
				'STUDENT_PASSWORD' => '',
				'BLOG_NAME' => '',
				'LOGIN_ADDRESS' => '',
				'COURSES_ADDRESS' => '',
				'WEBSITE_ADDRESS' => '',
			),
			null
		);
		$enrollment_confirm = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::ENROLLMENT_CONFIRM,
			array(
				'STUDENT_FIRST_NAME' => '',
				'STUDENT_LAST_NAME' => '',
				'BLOG_NAME' => '',
				'LOGIN_ADDRESS' => '',
				'COURSES_ADDRESS' => '',
				'WEBSITE_ADDRESS' => '',
				'COURSE_ADDRESS' => '',
			),
			null
		);
		$course_invitation_fields = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::COURSE_INVITATION,
			array(
				'STUDENT_FIRST_NAME' => '',
				'STUDENT_LAST_NAME' => '',
				'COURSE_NAME' => '',
				'COURSE_EXCERPT' => '',
				'COURSE_ADDRESS' => '',
				'WEBSITE_ADDRESS' => '',
				'PASSCODE' => '',
			),
			null
		);
		$instructor_invitation_fields = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::INSTRUCTOR_INVITATION,
			array(
				'INSTRUCTOR_FIRST_NAME' => '',
				'INSTRUCTOR_LAST_NAME' => '',
				'INSTRUCTOR_EMAIL' => '',
				'CONFIRMATION_LINK' => '',
				'COURSE_NAME' => '',
				'COURSE_EXCERPT' => '',
				'COURSE_ADDRESS' => '',
				'WEBSITE_ADDRESS' => '',
				'WEBSITE_NAME' => '',
			),
			null
		);
		$basic_certificate_fields = array_keys( $basic_certificate_fields );
		$registration_fields = array_keys( $registration_fields );
		$enrollment_confirm = array_keys( $enrollment_confirm );
		$course_invitation_fields = array_keys( $course_invitation_fields );
		$instructor_invitation_fields = array_keys( $instructor_invitation_fields );

		$defaults = apply_filters(
			'coursepress_default_email_settings_sections',
			array(
				CoursePress_Helper_Email::BASIC_CERTIFICATE => array(
					'title' => __( 'Basic Certificate E-mail', 'CP_TD' ),
					'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $basic_certificate_fields ),
					'order' => 7,
				),
				CoursePress_Helper_Email::REGISTRATION => array(
					'title' => __( 'User Registration E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail student get upon account registration.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $registration_fields ),
					'order' => 1,
				),
				CoursePress_Helper_Email::ENROLLMENT_CONFIRM => array(
					'title' => __( 'Course Enrollment Confirmation E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail student get upon enrollment.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $enrollment_confirm ),
					'order' => 2,
				),
				CoursePress_Helper_Email::COURSE_INVITATION => array(
					'title' => __( 'Student Invitation to a Course E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation to a course.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $course_invitation_fields ),
					'order' => 3,
				),
				CoursePress_Helper_Email::COURSE_INVITATION_PASSWORD => array(
					'title' => __( 'Student Invitation to a Course E-mail (with passcode)', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation (with passcode) to a course.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $course_invitation_fields ),
					'order' => 4,
				),
				CoursePress_Helper_Email::INSTRUCTOR_INVITATION => array(
					'title' => __( 'Instructor Invitation Email', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail an instructor will get upon receiving an invitation.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $instructor_invitation_fields ),
					'order' => 5,
				),
				CoursePress_Helper_Email::NEW_ORDER => array(
					'title' => __( 'New Order E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail student get upon placing an order.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL', 'CP_TD' ),
					'order' => 6,
				),
			)
		);

		return $defaults;
	}

	private static function _registration_email() {
		return CoursePress_Core::get_setting(
			'email/registration/content',
			sprintf(
				__( 'Hi %1$s %2$s,

Congratulations! We have set up your account at %3$s!
You can already access your account here %4$s

Get started by exploring our courses:
%5$s

best wishes,
%6$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'BLOG_NAME',
				'<a href="LOGIN_ADDRESS">LOGIN_ADDRESS</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private static function _enrollment_confirmation_email() {
		return CoursePress_Core::get_setting(
			'email/enrollment_confirm/content',
			sprintf(
				__( 'Hi %1$s %2$s,

Congratulations! You have enrolled in course "%3$s" successfully!

You may check all courses you are enrolled in here: %4$s.

Or you can explore other courses in your %5$s

best wishes,
%6$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'CP_TD' ) . '</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'BLOG_NAME'
			)
		);
	}

	private static function _course_invitation_email() {
		return CoursePress_Core::get_setting(
			'email/course_invitation/content',
			sprintf(
				__( 'Hi %1$s %2$s,

we would like to invite you to participate in the course: "%3$s"

What its all about:
%4$s

Check this page for more info on the course: %5$s

If you have any question feel free to contact us.

best wishes,
%6$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'COURSE_NAME',
				'COURSE_EXCERPT',
				'<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private static function _course_invitation_passcode_email() {
		return CoursePress_Core::get_setting(
			'email/course_invitation_password/content',
			sprintf(
				__( 'Hi %1$s %2$s,

we would like to invite you to participate in the course: "%3$s"

Since the course is only for selected ones, it is passcode protected. Here is the passcode for you: %7$s

What its all about:
%4$s

Check this page for more info on the course: %5$s

If you have any question feel free to contact us.

best wishes,
%6$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'COURSE_NAME',
				'COURSE_EXCERPT',
				'<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>',
				'PASSCODE'
			)
		);
	}

	private static function _instructor_invitation_email() {
		return CoursePress_Core::get_setting(
			'email/instructor_invitation/content',
			sprintf(
				__('Hi %1$s %2$s,

Congratulations! You have been invited to become an instructor for the course: %3$s

Click on the link below to confirm:

%4$s

If you haven\'t yet got a username you will need to create one.

%5$s
	', 'CP_TD' ),
				'INSTRUCTOR_FIRST_NAME',
				'INSTRUCTOR_LAST_NAME',
				'COURSE_NAME',
				'<a href="CONFIRMATION_LINK">CONFIRMATION_LINK</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private static function _new_order_email() {
		return CoursePress_Core::get_setting(
			'email/new_order/content',
			sprintf(
				__( 'Thank you for your order %1$s,

Your order for course "%2$s" has been received!

Please refer to your Order ID (ORDER_ID) whenever contacting us.

You can track the latest status of your order here: ORDER_STATUS_URL

best wishes,
%5$s Team', 'CP_TD' ),
				'CUSTOMER_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'CP_TD' ) . '</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'BLOG_NAME'
			)
		);
	}
}