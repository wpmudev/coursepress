<?php

class CoursePress_Helper_Setting_Email {

	public static function get_defaults( $context = false ) {
		add_filter( 'coursepress_admin_setting_before_top_save', array( __CLASS__, 'add_buttons' ), 10, 2 );
		$defaults = apply_filters(
			'coursepress_default_email_settings',
			array(
				CoursePress_Helper_Email::BASIC_CERTIFICATE => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => CoursePress_View_Admin_Setting_BasicCertificate::default_email_subject(),
					'content' => CoursePress_View_Admin_Setting_BasicCertificate::default_email_content(),
					'auto_email' => true,
				),
				CoursePress_Helper_Email::REGISTRATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Registration Status', 'CP_TD' ),
					'content' => self::_registration_email(),
				),
				CoursePress_Helper_Email::ENROLLMENT_CONFIRM => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Enrollment Confirmation', 'CP_TD' ),
					'content' => self::_enrollment_confirmation_email(),
				),
				CoursePress_Helper_Email::INSTRUCTOR_ENROLLMENT_NOTIFICATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'New Enrollment In Your Course', 'CP_TD' ),
					'content' => self::_instructor_enrollment_notification_email(),
				),
				CoursePress_Helper_Email::COURSE_INVITATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Invitation to a Course', 'CP_TD' ),
					'content' => self::_course_invitation_email(),
				),
				CoursePress_Helper_Email::COURSE_INVITATION_PASSWORD => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', 'CP_TD' ),
					'content' => self::_course_invitation_passcode_email(),
				),
				CoursePress_Helper_Email::INSTRUCTOR_INVITATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => sprintf( __( 'Invitation to be an instructor at %s', 'CP_TD' ), get_option( 'blogname' ) ),
					'content' => self::_instructor_invitation_email(),
				),
				CoursePress_Helper_Email::FACILITATOR_INVITATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => sprintf( __( 'Invitation to be a facilitator at %s', 'CP_TD' ), get_option( 'blogname' ) ),
					'content' => self::_facilitator_invitation_email(),
				),
				CoursePress_Helper_Email::NEW_ORDER => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Order Confirmation', 'CP_TD' ),
					'content' => self::_new_order_email(),
				),
				CoursePress_Helper_Email::COURSE_START_NOTIFICATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Course Start Notfication', 'CP_TD' ),
					'content' => self::course_start_defaults(),
				),
				CoursePress_Helper_Email::DISCUSSION_NOTIFICATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Discussion Notfication', 'CP_TD' ),
					'content' => self::discussion_defaults(),
				),
				CoursePress_Helper_Email::UNIT_STARTED_NOTIFICATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( '[UNIT_TITLE] is now available', 'CP_TD' ),
					'content' => self::unit_started_defaults(),
				),
				CoursePress_Helper_Email::INSTRUCTOR_MODULE_FEEDBACK_NOTIFICATION => array(
					'enabled' => '1',
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'New Feedback', 'CP_TD' ),
					'content' => self::instructor_feedback_module_defaults(),
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
				'CERTIFICATE_URL' => '',
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
		$instructor_enrollment_notification = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::INSTRUCTOR_ENROLLMENT_NOTIFICATION,
			array(
				'STUDENT_FIRST_NAME' => '',
				'STUDENT_LAST_NAME' => '',
				'INSTRUCTOR_FIRST_NAME' => '',
				'INSTRUCTOR_LAST_NAME' => '',
				'COURSE_TITLE' => '',
				'COURSE_ADDRESS' => '',
				'COURSE_ADMIN_ADDRESS' => '',
				'COURSE_STUDENTS_ADMIN_ADDRESS' => '',
				'WEBSITE_NAME' => '',
				'WEBSITE_ADDRESS' => ''
			)
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
		$course_start_fields = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::COURSE_START_NOTIFICATION,
			array(
				'COURSE_NAME' => '',
				'COURSE_ADDRESS' => '',
				'COURSE_OVERVIEW' => '',
				'BLOG_NAME' => '',
				'WEBSITE_ADDRESS' => '',
				'UNSUBSCRIBE_LINK' => '',
			)
		);
		$discussion_fields = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::DISCUSSION_NOTIFICATION,
			array(
				'COURSE_NAME' => '',
				'COURSE_ADDRESS' => '',
				'COURSE_OVERVIEW' => '',
				'BLOG_NAME' => '',
				'WEBSITE_ADDRESS' => '',
				'COMMENT_MESSAGE' => '',
				'COURSE_DISCUSSION_ADDRESS' => '',
				'UNSUBSCRIBE_LINK' => '',
				'COMMENT_AUTHOR' => '',
			)
		);
		$units_started = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::UNIT_STARTED_NOTIFICATION,
			array(
				'COURSE_NAME' => '',
				'COURSE_ADDRESS' => '',
				'UNIT_TITLE' => '',
				'UNIT_OVERVIEW' => '',
				'UNIT_ADDRESS' => '',
				'STUDENT_FIRST_NAME' => '',
				'STUDENT_LAST_NAME' => '',
				'UNSUBSCRIBE_LINK' => '',
			)
		);
		$instructor_module_feedback = apply_filters( 'coursepress_fields_' . CoursePress_Helper_Email::INSTRUCTOR_MODULE_FEEDBACK_NOTIFICATION,
			array(
				'COURSE_NAME' => '',
				'COURSE_ADDRESS' => '',
				'CURRENT_UNIT' => '',
				'CURRENT_MODULE' => '',
				'STUDENT_FIRST_NAME' => '',
				'STUDENT_LAST_NAME' => '',
				'INSTRUCTOR_FIRST_NAME' => '',
				'INSTRUCTOR_LAST_NAME' => '',
				'INSTRUCTOR_FEEDBACK' => '',
				'COURSE_GRADE' => '',
			)
		);
		$basic_certificate_fields = array_keys( $basic_certificate_fields );
		$registration_fields = array_keys( $registration_fields );
		$enrollment_confirm = array_keys( $enrollment_confirm );
		$instructor_enrollment_notification = array_keys( $instructor_enrollment_notification );
		$course_invitation_fields = array_keys( $course_invitation_fields );
		$instructor_invitation_fields = array_keys( $instructor_invitation_fields );
		$course_start_fields = array_keys( $course_start_fields );
		$discussion_fields = array_keys( $discussion_fields );
		$units_started = array_keys( $units_started );
		$instructor_module_feedback = array_keys( $instructor_module_feedback );

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
				CoursePress_Helper_Email::INSTRUCTOR_ENROLLMENT_NOTIFICATION => array(
					'title' => __( 'Enrollment Notification for Instructor E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail instructor gets when a new student enrolls.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $instructor_enrollment_notification ),
					'order' => 3,
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
				CoursePress_Helper_Email::FACILITATOR_INVITATION => array(
					'title' => __( 'Facilitator Invitation Email', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail a facilitator will get upon receiving an invitation.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $instructor_invitation_fields ),
					'order' => 5,
				),
				CoursePress_Helper_Email::NEW_ORDER => array(
					'title' => __( 'New Order E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail student get upon placing an order.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL', 'CP_TD' ),
					'order' => 6,
				),
				CoursePress_Helper_Email::COURSE_START_NOTIFICATION => array(
					'title' => __( 'Course Notfication E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail to send to students when a course started.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be relaced with actual data: ', 'CP_TD' ) . implode( ', ', $course_start_fields ),
					'order' => 7,
				),
				CoursePress_Helper_Email::DISCUSSION_NOTIFICATION => array(
					'title' => __( 'Discussion Notfication E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail to send to students and instructors.', 'CP_TD' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $discussion_fields ),
					'order' => 7,
				),
				CoursePress_Helper_Email::UNIT_STARTED_NOTIFICATION => array(
					'title' => __( 'Course Unit Started E-mail', 'CP_TD' ),
					'description' => __( 'Settings for an e-mail to send to students whenever a unit have started.', 'CP_TD' ),
					'content_help_text' => sprintf( __( '* You may use %s mail token to your subject line. ', 'CP_TD' ), 'UNIT_TITLE' ) .
						__( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $units_started ),
					'order' => 8,
				),
				CoursePress_Helper_Email::INSTRUCTOR_MODULE_FEEDBACK_NOTIFICATION => array(
					'title' => __( 'Instructor Feedback', 'CP_TD' ),
					'description' => __( 'Template for sending instructor feedback to students.', 'CP_TD' ),
					'content_help_text' => sprintf( __( 'These codes will be replaced with actual data: ', 'CP_TD' ) . implode( ', ', $instructor_module_feedback ) ),
					'order' => 9,
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

Welcome to %3$s!

You can access your profile here: %4$s

And get started exploring our courses here: %5$s

Best wishes,
The %6$s Team', 'CP_TD' ),
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

Congratulations! You have enrolled in "%3$s”.

You can review the courses you’re enrolled in here: %4$s.

And explore other courses available to you here: %5$s

Best wishes,
The %6$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'CP_TD' ) . '</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'WEBSITE_ADDRESS'
			)
		);
	}

	private static function _instructor_enrollment_notification_email() {
		return CoursePress_Core::get_setting(
			'email/instructor_enrollment_notification/content',
			sprintf(
				__( 'Hi %1$s %2$s,

A new student "%3$s %4$s" has enrolled in your course "%5$s".

You can manage all the students enrolled in this course here: %6$s

Best wishes,
The %7$s Team', 'CP_TD' ),
				'INSTRUCTOR_FIRST_NAME',
				'INSTRUCTOR_LAST_NAME',
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'<a href="COURSE_ADMIN_ADDRESS">COURSE_TITLE</a>',
				'<a href="COURSE_STUDENTS_ADMIN_ADDRESS">COURSE_STUDENTS_ADMIN_ADDRESS</a>',
				'WEBSITE_ADDRESS'
			)
		);
	}

	private static function _course_invitation_email() {
		return CoursePress_Core::get_setting(
			'email/course_invitation/content',
			sprintf(
				__( 'Hi %1$s %2$s,

You’ve invited to enroll in: "%3$s"

Here’s some more information about the course:

%4$s

Check out the course page for a detailed overview: %5$s

If you have any questions feel free to get in touch!

Best wishes,
The %6$s Team', 'CP_TD' ),
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

You’ve invited to enroll in: "%3$s"

This course is only available to select participants who have a passcode.

Your passcode is: %7$s

Here’s some more information about the course:

%4$s

Check out the course page for a detailed overview: %5$s

If you have any questions feel free to get in touch!

Best wishes,
The %6$s Team', 'CP_TD' ),
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

Best wishes,
The %5$s Team', 'CP_TD' ),
				'INSTRUCTOR_FIRST_NAME',
				'INSTRUCTOR_LAST_NAME',
				'COURSE_NAME',
				'<a href="CONFIRMATION_LINK">CONFIRMATION_LINK</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private static function _facilitator_invitation_email() {
		return CoursePress_Core::get_setting(
			'email/facilitator_invitation/content',
			sprintf(
				__('Hi %1$s %2$s,

Congratulations! You have been invited to join %3$s as a facilitator.

Click on the link below to confirm:

%4$s

If you haven\'t yet received a username you will need to create one. You can do that here:

%5$s

Best wishes,
The %5$s Team', 'CP_TD' ),
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

	public static function course_start_defaults() {
		return CoursePress_Core::get_setting(
			'email/course_start/content',
			sprintf(
				__( 'Hi %1$s,

Your course %2$s has begun!

You can review course material here::

%3$s

Best wishes,
The %4$s Team', 'CP_TD' ),
				'FIRST_NAME',
				'COURSE_NAME',
				'<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>',
				'WEBSITE_ADDRESS'
			)
		);
	}

	public static function discussion_defaults() {
		return CoursePress_Core::get_setting(
			'email/discussion_notification/content',
			sprintf(
				__( 'A new comment has been added to %1$s:

%2$s

Best wishes,
The %3$s Team', 'CP_TD' ),
				'COURSE_NAME',
				'COMMENT_MESSAGE',
				'WEBSITE_ADDRESS'
			)
		);
	}

	public static function unit_started_defaults() {
		return CoursePress_Core::get_setting(
			'email/unit_started/content',
			sprintf(
				__( 'Howdy %1$s,

%2$s of %3$s is now available.

You can continue your learning by clicking the link below:
%4$s

Best wishes,
The %5$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'UNIT_TITLE',
				'COURSE_NAME',
				'UNIT_ADDRESS',
				'WEBSITE_ADDRESS'
			)
		);
	}

	public static function instructor_feedback_module_defaults() {
		return CoursePress_Core::get_setting(
			'email/instructor_feedback_module/content',
			sprintf( __(
				'Hi %1$s %2$s,

A new feedback is given by your instructor at %3$s in %4$s at %5$s

%6$s says
%7$s

Best wishes,
The %8$s Team', 'CP_TD' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'COURSE_NAME',
				'CURRENT_UNIT',
				'CURRENT_MODULE',
				'INSTRUCTOR_FIRST_NAME',
				'INSTRUCTOR_LAST_NAME',
				'INSTRUCTOR_FEEDBACK',
				'WEBSITE_ADDRESS'
			)
		);
	}

	/**
	 * Add buttons: fold and unfold.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Current content to filter.
	 * @param string $active Current tab key.
	 * @return string Content after filter.
	 */
	public static function add_buttons( $content, $active ) {
		if ( 'email' != $active ) {
			return $content;
		}
		$content .= sprintf(
			'<input type="button" class="button %s disabled" value="%s" /> ',
			'hndle-items-fold',
			esc_attr__( 'Fold all', 'CP_TD' )
		);
		$content .= sprintf(
			'<input type="button" class="button %s" value="%s" /> ',
			'hndle-items-unfold',
			esc_attr__( 'Unfold all', 'CP_TD' )
		);
		return $content;
	}
}
