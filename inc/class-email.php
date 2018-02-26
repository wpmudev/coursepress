<?php
/**
 * Class CoursePress_Email
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Email extends CoursePress_Utility {

	public function get_email_data( $context = false ) {
		$defaults = $this->get_defaults( $context );
		$key = ! $context ? 'email' : 'email/' . $context;
		$data = coursepress_get_setting( $key, $defaults );
		return $data;
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
					'subject' => sprintf( __( '[%s] Congratulations. You passed your course.', 'coursepress' ), get_option( 'blogname' ) ),
					'content' => $this->_basic_certificate_email(),
					'auto_email' => true,
				),
				'registration' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Registration Status', 'coursepress' ),
					'content' => $this->_registration_email(),
				),
				'enrollment_confirm' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Enrollment Confirmation', 'coursepress' ),
					'content' => $this->_enrollment_confirmation_email(),
				),
				'instructor_enrollment_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'New Enrollment In Your Course', 'coursepress' ),
					'content' => $this->_instructor_enrollment_notification_email(),
				),
				'course_invitation' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Invitation to a Course', 'coursepress' ),
					'content' => $this->_course_invitation_email(),
				),
				'course_invitation_password' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Invitation to a Course ( Psss...for selected ones only )', 'coursepress' ),
					'content' => $this->_course_invitation_passcode_email(),
				),
				'instructor_invitation' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => sprintf( __( 'Invitation to be an instructor at %s', 'coursepress' ), get_option( 'blogname' ) ),
					'content' => $this->_instructor_invitation_email(),
				),
				'facilitator_invitation' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => sprintf( __( 'Invitation to be a facilitator at %s', 'coursepress' ), get_option( 'blogname' ) ),
					'content' => $this->_facilitator_invitation_email(),
				),
				'new_order' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Order Confirmation', 'coursepress' ),
					'content' => $this->_new_order_email(),
				),
				'course_start_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Course Start Notification', 'coursepress' ),
					'content' => $this->course_start_defaults(),
				),
				'discussion_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( 'Discussion Notification', 'coursepress' ),
					'content' => $this->discussion_defaults(),
				),
				'unit_started_notification' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( '[UNIT_TITLE] is now available', 'coursepress' ),
					'content' => $this->unit_started_defaults(),
				),
				'instructor_feedback' => array(
					'enabled' => '1',
					'from' => $blog_name,
					'email' => $blog_email,
					'subject' => __( '[COURSE_NAME/UNIT_TITLE] New Feedback', 'coursepress' ),
					'content' => $this->instructor_feedback_defaults(),
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
		$basic_certificate_fields = apply_filters( 'coursepress_fields_basic_certificate',
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
		$registration_fields = apply_filters( 'coursepress_fields_registration',
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
		$enrollment_confirm = apply_filters( 'coursepress_fields_enrollment_confirm',
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
		$instructor_enrollment_notification = apply_filters( 'coursepress_fields_instructor_enrollment_notification',
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
		$instructor_feedback = apply_filters( 'coursepress_fields_instructor_feedback',
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
		$course_invitation_fields = apply_filters( 'coursepress_fields_course_invitation',
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
		$instructor_invitation_fields = apply_filters( 'coursepress_fields_instructor_invitation',
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
		$course_start_fields = apply_filters( 'coursepress_fields_course_start_notification',
			array(
				'COURSE_NAME' => '',
				'COURSE_ADDRESS' => '',
				'COURSE_OVERVIEW' => '',
				'BLOG_NAME' => '',
				'WEBSITE_ADDRESS' => '',
				'UNSUBSCRIBE_LINK' => '',
			)
		);
		$discussion_fields = apply_filters( 'coursepress_fields_discussion_notification',
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
		$units_started = apply_filters( 'coursepress_fields_unit_started_notification',
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
		$course_invitation_fields = array_keys( $course_invitation_fields );
		$course_start_fields = array_keys( $course_start_fields );
		$discussion_fields = array_keys( $discussion_fields );
		$enrollment_confirm = array_keys( $enrollment_confirm );
		$instructor_enrollment_notification = array_keys( $instructor_enrollment_notification );
		$instructor_invitation_fields = array_keys( $instructor_invitation_fields );
		$registration_fields = array_keys( $registration_fields );
		$units_started = array_keys( $units_started );
		$instructor_feedback = array_keys( $instructor_feedback );

		$_codes_text = sprintf( '<p>%1$s</p> <p>%2$s</p>', __( 'These codes will be replaced with actual data:', 'cp' ), '<b>%s</b>' );
		$defaults = apply_filters(
			'coursepress_default_email_settings_sections',
			array(
				'basic_certificate' => array(
					'title' => __( 'Basic Certificate E-mail', 'coursepress' ),
					'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $basic_certificate_fields ) ),
				),
				'registration' => array(
					'title' => __( 'User Registration E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail student get upon account registration.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $registration_fields ) ),
				),
				'enrollment_confirm' => array(
					'title' => __( 'Course Enrollment Confirmation E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail student get upon enrollment.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $enrollment_confirm ) ),
				),
				'instructor_enrollment_notification' => array(
					'title' => __( 'Enrollment Notification for Instructor E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail instructor gets when a new student enrolls.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_enrollment_notification ) ),
				),
				'course_invitation' => array(
					'title' => __( 'Student Invitation to a Course E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation to a course.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $course_invitation_fields ) ),
				),
				'course_invitation_password' => array(
					'title' => __( 'Student Invitation to a Course E-mail (with passcode)', 'coursepress' ),
					'description' => __( 'Settings for an e-mail student get upon receiving an invitation (with passcode) to a course.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $course_invitation_fields ) ),
				),
				'instructor_invitation' => array(
					'title' => __( 'Instructor Invitation Email', 'coursepress' ),
					'description' => __( 'Settings for an e-mail an instructor will get upon receiving an invitation.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_invitation_fields ) ),
				),
				'facilitator_invitation' => array(
					'title' => __( 'Facilitator Invitation Email', 'coursepress' ),
					'description' => __( 'Settings for an e-mail a facilitator will get upon receiving an invitation.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_invitation_fields ) ),
				),
				'new_order' => array(
					'title' => __( 'New Order E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail student get upon placing an order.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, 'CUSTOMER_NAME, BLOG_NAME, LOGIN_ADDRESS, COURSES_ADDRESS, WEBSITE_ADDRESS, COURSE_ADDRESS, ORDER_ID, ORDER_STATUS_URL' ),
				),
				'course_start_notification' => array(
					'title' => __( 'Course Notification E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail to send to students when a course started.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $course_start_fields ) ),
				),
				'discussion_notification' => array(
					'title' => __( 'Discussion Notification E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail to send to students and instructors.', 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $discussion_fields ) ),
				),
				'unit_started_notification' => array(
					'title' => __( 'Course Unit Started E-mail', 'coursepress' ),
					'description' => __( 'Settings for an e-mail to send to students whenever a unit have started.', 'coursepress' ),
					'content_help_text' => sprintf( __( '* You may use %s mail token to your subject line. ', 'coursepress' ), 'UNIT_TITLE' ) .
					sprintf( $_codes_text, implode( ', ', $units_started ) ),
				),
				'instructor_feedback' => array(
					'title' => __( 'Instructor Feedback', 'coursepress' ),
					'description' => __( 'Settings for emails when using basic certificate functionality (when course completed).' , 'coursepress' ),
					'content_help_text' => sprintf( $_codes_text, implode( ', ', $instructor_feedback ) ),
				),
			)
		);
		/**
		 * sort by title
		 */
		foreach ( $defaults as $key => $value ) {
			$defaults[ $key ]['id'] = $key;
		}
		uasort( $defaults, array( $this, 'sort_defaults_by_title' ) );
		$result = array();
		foreach ( $defaults as $one ) {
			$result[ $one['id'] ] = $one;
		}
		return $result;
	}

	/**
	 * Sort emails by title
	 */
	private function sort_defaults_by_title( $a, $b ) {
		return strcmp( $a['title'], $b['title'] );
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
The %6$s Team', 'coursepress' ),
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
The %6$s Team', 'coursepress' ),
				'STUDENT_FIRST_NAME',
				'STUDENT_LAST_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'coursepress' ) . '</a>',
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
The %7$s Team', 'coursepress' ),
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
The %6$s Team', 'coursepress' ),
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
The %6$s Team', 'coursepress' ),
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
The %5$s Team', 'coursepress' ),
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
The %5$s Team', 'coursepress' ),
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
%5$s Team', 'coursepress' ),
				'CUSTOMER_NAME',
				'<a href="COURSE_ADDRESS">COURSE_TITLE</a>',
				'<a href="STUDENT_DASHBOARD">' . __( 'Dashboard', 'coursepress' ) . '</a>',
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
The %4$s Team', 'coursepress' ),
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
The %3$s Team', 'coursepress' ),
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
The %5$s Team', 'coursepress' ),
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

	public function instructor_feedback_defaults() {
		$msg = __( 'Hi %1$s,

A new feedback is given by your instructor at %2$s in %3$s at %4$s

%5$s says
%6$s

Best wishes,
The %7$s Team', 'coursepress' );
		return coursepress_get_setting(
			'email/instructor_feedback/content',
			sprintf(
				$msg,
				'STUDENT_FIRST_NAME',
				'COURSE_NAME',
				'CURRENT_UNIT',
				'CURRENT_MODULE',
				'INSTRUCTOR_LAST_NAME',
				'INSTRUCTOR_FEEDBACK',
				'WEBSITE_NAME'
			)
		);
	}

	/**
	 * Send email from notification form.
	 *
	 * @param array $students Student User IDs.
	 * @param string $title Email title.
	 * @param string $content Email content.
	 *
	 * @return bool
	 */
	public function notification_alert_email( $students, $title, $content ) {
		// Make sure students are there.
		if ( empty( $students ) ) {
			return false;
		}
		// If all students option selected, get all students.
		if ( in_array( 0, $students ) ) {
			$students = coursepress_get_students_ids();
		}
		return $this->_send_students_notification_email( $students, $title, $content );
	}

	/**
	 * Send email notification to students.
	 *
	 * @param array $students Student User IDs.
	 * @param string $title Email title.
	 * @param string $content Email content.
	 *
	 * @return bool
	 */
	private function _send_students_notification_email( $students, $title, $content ) {
		// Make sure students are there.
		if ( empty( $students ) ) {
			return false;
		}
		// Set html email.
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		// Send separate email for each students.
		foreach ( $students as $student ) {
			$student = coursepress_get_user( $student );
			if ( is_wp_error( $student ) ) {
				continue;
			}
			// @todo Add more tokens.
			$tokens = array(
				'STUDENT_FIRST_NAME' => $student->first_name,
				'STUDENT_LAST_NAME' => $student->last_name,
				'STUDENT_USERNAME' => $student->user_login,
				'BLOG_NAME' => get_bloginfo( 'name' ),
				'LOGIN_ADDRESS' => wp_login_url(),
				'WEBSITE_ADDRESS' => site_url(),
			);
			// Replacing tokens by actual content.
			$content = $this->replace_vars( $content, $tokens );
			wp_mail( $student->user_email, $title, $content, $headers );
		}
		return true;
	}

	/**
	 * Send an email.
	 *
	 * @param string $context
	 *               if specifying the 'subject' and 'message'.
	 * @param array  $args Variables and email content.
	 *               email .. recipient.
	 *               message .. optional if specifying type.
	 *               subject .. optional if specifying type.
	 *               first_name
	 *               last_name
	 *               fields .. content variables, array of key-value pairs.
	 * @return bool True if email was accepted by wp_mail.
	 **/
	public function sendEmail( $context, $args ) {
		// Prepare email content.
		$email = array(
			'to' => apply_filters(
				'coursepress_email_to_address',
				sanitize_email( $args['to'] ),
				$args
			),
			'subject' => apply_filters(
				'coursepress_email_subject',
				sanitize_text_field( $args['subject'] ) ,
				$args
			),
			'message' => apply_filters(
				'coursepress_email_message',
				$args['message'],
				$args
			),
			'headers' => apply_filters(
				'coursepress_email_headers',
				array(
					'Content-Type' => 'text/html',
				)
			),
			'attachments' => apply_filters(
				'coursepress_email_attachments',
				isset( $args['attachments'] ) ? $args['attachments'] : array()
			),
		);
		$email = apply_filters(
			'coursepress_email_fields',
			$email,
			$args,
			$context
		);
		$email = apply_filters(
			'coursepress_email_fields-' . $context,
			$email,
			$args
		);
		// Good one to hook if you want to hook WP specific filters (e.g. changing from address)
		do_action( 'coursepress_email_pre_send', $args, $context );
		do_action( 'coursepress_email_pre_send-' . $context, $args );
		if ( apply_filters( 'coursepress_email_strip_slashed', true, $args, $context ) ) {
			$email['subject'] = stripslashes( $email['subject'] );
			$email['message'] = stripslashes( nl2br( $email['message'] ) );
		}
		$header_string = '';
		if ( isset( $args['bcc'] ) ) {
			if ( is_array( $args['bcc'] ) ) {
				$bcc = implode( ',', $args['bcc'] );
			} else {
				$bcc = $args['bcc'];
			}
			if ( ! empty( $bcc ) ) {
				$header_string .= 'Bcc: ' . $bcc . ';';
			}
		}
		foreach ( $email['headers'] as $key => $value ) {
			$header_string .= $key . ': ' . $value . "\r\n";
		}
		$email['headers'] = $header_string;
		/**
		 * Action offers other plugins to implement custom email sending code,
		 * for example to use a custom built HTML template or similar.
		 *
		 * @var bool  $result Output parameter, this should be set to true/false
		 *            if the email was processed by the custom action handler.
		 * @var array $email The email options for wp_mail.
		 * @var array $args Email parameters passed to the CoursePress function.
		 */
		$result = apply_filters(
			'coursepress_send_email',
			null,
			$email,
			$args,
			$context
		);
		// If custom send-option failed or was not used then send via wp_mail.
		if ( is_null( $result ) || ! $result ) {
			try {
				$result = wp_mail(
					$email['to'],
					$email['subject'],
					$email['message'],
					$email['headers'],
					$email['attachments']
				);
			} catch (phpmailerException $e) {
				// print_r($e->getMessage()); // for debugging purposes
				$result = false;
			}
		}
		do_action( 'coursepress_email_sent', $args, $context, $result );
		do_action( 'coursepress_email_sent-' . $context, $args, $result );
		return $result;
	}
}
