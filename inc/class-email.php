<?php
/**
 * Class CoursePress_Email
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Email extends CoursePress_Utility {

	/**
	 * CoursePress_Email constructor.
	 */
	public function __construct() {

    }

	public function get_defaults( $context = false ) {
        $blog_name = coursepress_get_option( 'blogname' );
        $blog_email = coursepress_get_option( 'admin_email' );

		$defaults = apply_filters(
			'coursepress_default_email_settings',
			array(
				'basic_certificate' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => sprintf( __( '[%s] Congratulations. You passed your course.', 'CoursePress' ), get_option( 'blogname' ) ),
					'content' => $this->_basic_certificate_email(),
					'auto_email' => true,
				),
				'registration' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Registration Status', 'CoursePress' ),
					'content' => $this->_registration_email(),
				),
				'enrollment_confirm' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Enrollment Confirmation', 'CoursePress' ),
					'content' => $this->_enrollment_confirmation_email(),
				),
				'instructor_enrollment_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'New Enrollment In Your Course', 'CoursePress' ),
					'content' => $this->_instructor_enrollment_notification_email(),
				),
				'course_invitation' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Invitation to a Course', 'CoursePress' ),
					'content' => $this->_course_invitation_email(),
				),
				'course_invitation_password' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', 'CoursePress' ),
					'content' => $this->_course_invitation_passcode_email(),
				),
				'instructor_invitation' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => sprintf( __( 'Invitation to be an instructor at %s', 'CoursePress' ), get_option( 'blogname' ) ),
					'content' => $this->_instructor_invitation_email(),
				),
				'facilitator_invitation' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => sprintf( __( 'Invitation to be a facilitator at %s', 'CoursePress' ), get_option( 'blogname' ) ),
					'content' => $this->_facilitator_invitation_email(),
				),
				'new_order' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Order Confirmation', 'CoursePress' ),
					'content' => $this->_new_order_email(),
				),
				'course_start_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Course Start Notfication', 'CoursePress' ),
					'content' => $this->course_start_defaults(),
				),
				'discussion_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Discussion Notfication', 'CoursePress' ),
					'content' => $this->discussion_defaults(),
				),
				'unit_started_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( '[UNIT_TITLE] is now available', 'CoursePress' ),
					'content' => $this->unit_started_defaults(),
				),
			)
		);

		if ( $context && isset( $defaults[ $context ] ) ) {
			return $defaults[ $context ];
		} else {
			return $defaults;
		}
	}

	public function get_settings_sections() {
		$basic_certificate_fields = apply_filters( 'coursepress_fields_' . 'basic_certificate',
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
		$registration_fields = apply_filters( 'coursepress_fields_' . 'registration',
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
		$enrollment_confirm = apply_filters( 'coursepress_fields_' . 'enrollment_confirm',
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
		$instructor_enrollment_notification = apply_filters( 'coursepress_fields_' . 'instructor_enrollment_notification',
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
				'WEBSITE_ADDRESS' => '',
			)
		);
		$course_invitation_fields = apply_filters( 'coursepress_fields_' . 'course_invitation',
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
		$instructor_invitation_fields = apply_filters( 'coursepress_fields_' . 'instructor_invitation',
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
		$course_start_fields = apply_filters( 'coursepress_fields_' . 'course_start_notification',
			array(
				'COURSE_NAME' => '',
				'COURSE_ADDRESS' => '',
				'COURSE_OVERVIEW' => '',
				'BLOG_NAME' => '',
				'WEBSITE_ADDRESS' => '',
				'UNSUBSCRIBE_LINK' => '',
			)
		);
		$discussion_fields = apply_filters( 'coursepress_fields_' . 'discussion_notification',
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
		$units_started = apply_filters( 'coursepress_fields_' . 'unit_started_notification',
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
		$instructor_enrollment_notification = array_keys( $instructor_enrollment_notification );
		$course_invitation_fields = array_keys( $course_invitation_fields );
		$instructor_invitation_fields = array_keys( $instructor_invitation_fields );
		$course_start_fields = array_keys( $course_start_fields );
		$discussion_fields = array_keys( $discussion_fields );
		$units_started = array_keys( $units_started );

		$_codes_text = sprintf( '<p>%1$s</p> <p>%2$s</p>', __( 'These codes will be replaced with actual data:', 'cp' ), '<b>%s</b>' );

		$defaults = apply_filters(
			'coursepress_default_email_settings_sections',
			array(
				'basic_certificate' => array(
					'title' => __( 'Basic Certificate E-mail', 'CoursePress' ),
					'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $basic_certificate_fields ) ),
				),
				'registration' => array(
					'title' => __( 'User Registration E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail student get upon account registration.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $registration_fields ) ),
				),
				'enrollment_confirm' => array(
					'title' => __( 'Course Enrollment Confirmation E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail student get upon enrollment.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $enrollment_confirm ) ),
				),
				'instructor_enrollment_notification' => array(
					'title' => __( 'Enrollment Notification for Instructor E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail instructor gets when a new student enrolls.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_enrollment_notification ) ),
				),
				'course_invitation' => array(
					'title' => __( 'Student Invitation to a Course E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation to a course.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $course_invitation_fields ) ),
				),
				'course_invitation_password' => array(
					'title' => __( 'Student Invitation to a Course E-mail (with passcode)', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation (with passcode) to a course.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $course_invitation_fields ) ),
				),
				'instructor_invitation' => array(
					'title' => __( 'Instructor Invitation Email', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail an instructor will get upon receiving an invitation.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_invitation_fields ) ),
				),
				'facilitator_invitation' => array(
					'title' => __( 'Facilitator Invitation Email', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail a facilitator will get upon receiving an invitation.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_invitation_fields ) ),
				),
				'new_order' => array(
					'title' => __( 'New Order E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail student get upon placing an order.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, 'CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL' ),
				),
				'course_start_notification' => array(
					'title' => __( 'Course Notfication E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail to send to students when a course started.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $course_start_fields ) ),
				),
				'discussion_notification' => array(
					'title' => __( 'Discussion Notfication E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail to send to students and instructors.', 'CoursePress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $discussion_fields ) ),
				),
				'unit_started_notification' => array(
					'title' => __( 'Course Unit Started E-mail', 'CoursePress' ),
					'description' => __( 'Settings for an e-mail to send to students whenever a unit have started.', 'CoursePress' ),
					'content_help_text' => sprintf( __( '* You may use %s mail token to your subject line. ', 'CoursePress' ), 'UNIT_TITLE' ) .
						sprintf( $_codes_text, implode( ', ', $units_started ) ),
				),
			)
		);

		return $defaults;
	}

	private function _registration_email() {
		return coursepress_get_setting(
			'email/registration/content',
			sprintf(
				__( 'Hi %1$s %2$s,

Welcome to %3$s!

You can access your profile here: %4$s

And get started exploring our courses here: %5$s

Best wishes,
The %6$s Team', 'CoursePress' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'BLOG_NAME',
				'<a href="LOGIN_ADDRESS">LOGIN_ADDRESS</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private function _enrollment_confirmation_email() {
		return coursepress_get_setting(
			'email/enrollment_confirm/content',
			sprintf(
				__( 'Hi %1$s %2$s,

Congratulations! You have enrolled in "%3$s”.

You can review the courses you’re enrolled in here: %4$s.

And explore other courses available to you here: %5$s

Best wishes,
The %6$s Team', 'CoursePress' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'CoursePress' ) . '</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'WEBSITE_ADDRESS'
			)
		);
	}

	private function _instructor_enrollment_notification_email() {
		return coursepress_get_setting(
			'email/instructor_enrollment_notification/content',
			sprintf(
				__( 'Hi %1$s %2$s,

A new student "%3$s %4$s" has enrolled in your course "%5$s".

You can manage all the students enrolled in this course here: %6$s

Best wishes,
The %7$s Team', 'CoursePress' ),
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

	private function _course_invitation_email() {
		return coursepress_get_setting(
			'email/course_invitation/content',
			sprintf(
				__( 'Hi %1$s %2$s,

You’ve invited to enroll in: "%3$s"

Here’s some more information about the course:

%4$s

Check out the course page for a detailed overview: %5$s

If you have any questions feel free to get in touch!

Best wishes,
The %6$s Team', 'CoursePress' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'COURSE_NAME',
				'COURSE_EXCERPT',
				'<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private function _course_invitation_passcode_email() {
		return coursepress_get_setting(
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
The %6$s Team', 'CoursePress' ),
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

	private function _instructor_invitation_email() {
		return coursepress_get_setting(
			'email/instructor_invitation/content',
			sprintf(
				__('Hi %1$s %2$s,

Congratulations! You have been invited to become an instructor for the course: %3$s

Click on the link below to confirm:

%4$s

If you haven\'t yet got a username you will need to create one.

%5$s

Best wishes,
The %5$s Team', 'CoursePress' ),
				'INSTRUCTOR_FIRST_NAME',
				'INSTRUCTOR_LAST_NAME',
				'COURSE_NAME',
				'<a href="CONFIRMATION_LINK">CONFIRMATION_LINK</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private function _facilitator_invitation_email() {
		return coursepress_get_setting(
			'email/facilitator_invitation/content',
			sprintf(
				__('Hi %1$s %2$s,

Congratulations! You have been invited to join %3$s as a facilitator.

Click on the link below to confirm:

%4$s

If you haven\'t yet received a username you will need to create one. You can do that here:

%5$s

Best wishes,
The %5$s Team', 'CoursePress' ),
				'INSTRUCTOR_FIRST_NAME',
				'INSTRUCTOR_LAST_NAME',
				'COURSE_NAME',
				'<a href="CONFIRMATION_LINK">CONFIRMATION_LINK</a>',
				'<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>'
			)
		);
	}

	private function _new_order_email() {
		return coursepress_get_setting(
			'email/new_order/content',
			sprintf(
				__( 'Thank you for your order %1$s,

Your order for course "%2$s" has been received!

Please refer to your Order ID (ORDER_ID) whenever contacting us.

You can track the latest status of your order here: ORDER_STATUS_URL

best wishes,
%5$s Team', 'CoursePress' ),
				'CUSTOMER_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'CoursePress' ) . '</a>',
				'<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>',
				'BLOG_NAME'
			)
		);
	}

	public function course_start_defaults() {
		return coursepress_get_setting(
			'email/course_start/content',
			sprintf(
				__( 'Hi %1$s,

Your course %2$s has begun!

You can review course material here::

%3$s

Best wishes,
The %4$s Team', 'CoursePress' ),
				'FIRST_NAME',
				'COURSE_NAME',
				'<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>',
				'WEBSITE_ADDRESS'
			)
		);
	}

	public function discussion_defaults() {
		return coursepress_get_setting(
			'email/discussion_notification/content',
			sprintf(
				__( 'A new comment has been added to %1$s:

%2$s

Best wishes,
The %3$s Team', 'CoursePress' ),
				'COURSE_NAME',
				'COMMENT_MESSAGE',
				'WEBSITE_ADDRESS'
			)
		);
	}

	public function unit_started_defaults() {
		return coursepress_get_setting(
			'email/unit_started/content',
			sprintf(
				__( 'Howdy %1$s,

%2$s of %3$s is now available.

You can continue your learning by clicking the link below:
%4$s

Best wishes,
The %5$s Team', 'CoursePress' ),
				'STUDENT_FIRST_NAME',
				'UNIT_TITLE',
				'COURSE_NAME',
				'UNIT_ADDRESS',
				'WEBSITE_ADDRESS'
			)
		);
	}

	private static function _basic_certificate_email() {
		$msg = __(
			'<h2>%1$s %2$s</h2>
			has successfully completed the course

			<h3>%3$s</h3>

			<h4>Date: %4$s</h4>
			<small>Certificate no.: %5$s</small>', 'CP_TD'
		);

		$default_certification_content = sprintf(
			$msg,
			'FIRST_NAME',
			'LAST_NAME',
			'COURSE_NAME',
			'COMPLETION_DATE',
			'CERTIFICATE_NUMBER',
			'UNIT_LIST'
		);

		return $default_certification_content;
	}
}
