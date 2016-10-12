<?php

class CoursePress_Helper_Setting_Email {

	public static function get_defaults( $context = false ) {
		add_filter( 'coursepress_admin_setting_before_top_save', array( __CLASS__, 'add_buttons' ), 10, 2 );
		$defaults = apply_filters(
			'coursepress_default_email_settings',
			array(
				CoursePress_Helper_Email::BASIC_CERTIFICATE => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => CoursePress_View_Admin_Setting_BasicCertificate::default_email_subject(),
					'content' => CoursePress_View_Admin_Setting_BasicCertificate::default_email_content(),
					'auto_email' => true,
				),
				CoursePress_Helper_Email::REGISTRATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Registration Status', 'cp' ),
					'content' => self::_registration_email(),
				),
				CoursePress_Helper_Email::ENROLLMENT_CONFIRM => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Enrollment Confirmation', 'cp' ),
					'content' => self::_enrollment_confirmation_email(),
				),
				CoursePress_Helper_Email::COURSE_INVITATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Invitation to a Course', 'cp' ),
					'content' => self::_course_invitation_email(),
				),
				CoursePress_Helper_Email::COURSE_INVITATION_PASSWORD => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', 'cp' ),
					'content' => self::_course_invitation_passcode_email(),
				),
				CoursePress_Helper_Email::INSTRUCTOR_INVITATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => sprintf( __( 'Invitation to be an instructor at %s', 'cp' ), get_option( 'blogname' ) ),
					'content' => self::_instructor_invitation_email(),
				),
				CoursePress_Helper_Email::FACILITATOR_INVITATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => sprintf( __( 'Invitation to be a facilitator at %s', 'cp' ), get_option( 'blogname' ) ),
					'content' => self::_facilitator_invitation_email(),
				),
				CoursePress_Helper_Email::NEW_ORDER => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Order Confirmation', 'cp' ),
					'content' => self::_new_order_email(),
				),
				CoursePress_Helper_Email::COURSE_START_NOTIFICATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Course Start Notfication', 'cp' ),
					'content' => self::course_start_defaults(),
				),
				CoursePress_Helper_Email::DISCUSSION_NOTIFICATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( 'Discussion Notfication', 'cp' ),
					'content' => self::discussion_defaults(),
				),
				CoursePress_Helper_Email::UNIT_STARTED_NOTIFICATION => array(
					'from' => get_option( 'blogname' ),
					'email' => get_option( 'admin_email' ),
					'subject' => __( '[UNIT_TITLE] is now available', 'cp' ),
					'content' => self::unit_started_defaults(),
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
		$basic_certificate_fields = array_keys( $basic_certificate_fields );
		$registration_fields = array_keys( $registration_fields );
		$enrollment_confirm = array_keys( $enrollment_confirm );
		$course_invitation_fields = array_keys( $course_invitation_fields );
		$instructor_invitation_fields = array_keys( $instructor_invitation_fields );
		$course_start_fields = array_keys( $course_start_fields );
		$discussion_fields = array_keys( $discussion_fields );
		$units_started = array_keys( $units_started );

		$defaults = apply_filters(
			'coursepress_default_email_settings_sections',
			array(
				CoursePress_Helper_Email::BASIC_CERTIFICATE => array(
					'title' => __( 'Basic Certificate E-mail', 'cp' ),
					'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $basic_certificate_fields ),
					'order' => 7,
				),
				CoursePress_Helper_Email::REGISTRATION => array(
					'title' => __( 'User Registration E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail student get upon account registration.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $registration_fields ),
					'order' => 1,
				),
				CoursePress_Helper_Email::ENROLLMENT_CONFIRM => array(
					'title' => __( 'Course Enrollment Confirmation E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail student get upon enrollment.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $enrollment_confirm ),
					'order' => 2,
				),
				CoursePress_Helper_Email::COURSE_INVITATION => array(
					'title' => __( 'Student Invitation to a Course E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation to a course.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $course_invitation_fields ),
					'order' => 3,
				),
				CoursePress_Helper_Email::COURSE_INVITATION_PASSWORD => array(
					'title' => __( 'Student Invitation to a Course E-mail (with passcode)', 'cp' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation (with passcode) to a course.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $course_invitation_fields ),
					'order' => 4,
				),
				CoursePress_Helper_Email::INSTRUCTOR_INVITATION => array(
					'title' => __( 'Instructor Invitation Email', 'cp' ),
					'description' => __( 'Settings for an e-mail an instructor will get upon receiving an invitation.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $instructor_invitation_fields ),
					'order' => 5,
				),
				CoursePress_Helper_Email::FACILITATOR_INVITATION => array(
					'title' => __( 'Facilitator Invitation Email', 'cp' ),
					'description' => __( 'Settings for an e-mail a facilitator will get upon receiving an invitation.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $instructor_invitation_fields ),
					'order' => 5,
				),
				CoursePress_Helper_Email::NEW_ORDER => array(
					'title' => __( 'New Order E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail student get upon placing an order.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL', 'cp' ),
					'order' => 6,
				),
				CoursePress_Helper_Email::COURSE_START_NOTIFICATION => array(
					'title' => __( 'Course Notfication E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail to send to students when a course started.', 'cp' ),
					'content_help_text' => __( 'These codes will be relaced with actual data: ', 'cp' ) . implode( ', ', $course_start_fields ),
					'order' => 7,
				),
				CoursePress_Helper_Email::DISCUSSION_NOTIFICATION => array(
					'title' => __( 'Discussion Notfication E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail to send to students and instructors.', 'cp' ),
					'content_help_text' => __( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $discussion_fields ),
					'order' => 7,
				),
				CoursePress_Helper_Email::UNIT_STARTED_NOTIFICATION => array(
					'title' => __( 'Course Unit Started E-mail', 'cp' ),
					'description' => __( 'Settings for an e-mail to send to students whenever a unit have started.', 'cp' ),
					'content_help_text' => sprintf( __( '* You may use %s mail token to your subject line. ', 'cp' ), 'UNIT_TITLE' ) .
						__( 'These codes will be replaced with actual data: ', 'cp' ) . implode( ', ', $units_started ),
					'order' => 8,
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
%6$s Team', 'cp' ),
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
%6$s Team', 'cp' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'cp' ) . '</a>',
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
%6$s Team', 'cp' ),
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
%6$s Team', 'cp' ),
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
	', 'cp' ),
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

Congratulations! You have been invited to become a facilitator for the course: %3$s

Click on the link below to confirm:

%4$s

If you haven\'t yet got a username you will need to create one.

%5$s
	', 'cp' ),
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
%5$s Team', 'cp' ),
				'CUSTOMER_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'cp' ) . '</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'BLOG_NAME'
			)
		);
	}

	public static function course_start_defaults() {
		return CoursePress_Core::get_setting(
			'email/course_start/content',
			sprintf(
				__( 'Your %s have started.

You can start reading the material by clicking the link below.

%s

%s
					'
				),
				'COURSE_NAME',
				'<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>',
				'BLOG_NAME'
			)
		);
	}

	public static function discussion_defaults() {
		return CoursePress_Core::get_setting(
			'email/discussion_notification/content',
			sprintf(
				__( 'A new comment is added %s.

%s

%s
					'
				),
				'COURSE_NAME',
				'COMMENT_MESSAGE',
				'BLOG_NAME'
			)
		);
	}

	public static function unit_started_defaults() {
		return CoursePress_Core::get_setting(
			'email/unit_started/content',
			sprintf(
				__( 'Howdy %s,

<strong>%s</strong> of %s is now available.

You can continue your learning by clicking the link below:
<a href="%s">%s</a>
				'),
				'STUDENT_FIRST_NAME',
				'UNIT_TITLE',
				'COURSE_NAME',
				'UNIT_ADDRESS',
				'UNIT_ADDRESS'
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
			esc_attr__( 'Fold all', 'cp' )
		);
		$content .= sprintf(
			'<input type="button" class="button %s" value="%s" /> ',
			'hndle-items-unfold',
			esc_attr__( 'Unfold all', 'cp' )
		);
		return $content;
	}
}
