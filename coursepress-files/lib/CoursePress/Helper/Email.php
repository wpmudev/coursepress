<?php

class CoursePress_Helper_Email {

	const BASIC_CERTIFICATE = 'basic_certificate';
	const REGISTRATION = 'registration';
	const ENROLLMENT_CONFIRM = 'enrollment_confirm';
	const COURSE_INVITATION = 'course_invitation';
	const COURSE_INVITATION_PASSWORD = 'course_invitation_password';
	const INSTRUCTOR_INVITATION = 'instructor_invitation';
	const NEW_ORDER = 'new_order';

	private static $last_args;

	public static function from_name( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return CoursePress_Core::get_setting( 'email/' . $context . '/from_name', $fields['from_name'] );
	}

	public static function from_email( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return CoursePress_Core::get_setting( 'email/' . $context . '/from_email', $fields['from_email'] );
	}

	public static function subject( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return CoursePress_Core::get_setting( 'email/' . $context . '/subject', $fields['subject'] );
	}

	public static function content( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return CoursePress_Core::get_setting( 'email/' . $context . '/content', $fields['content'] );
	}

	public static function get_email_fields( $context ) {
		return apply_filters( 'coursepress_get_email_fields_' . $context, array(
			'name'    => self::from_name( $context ),
			'email'   => self::from_email( $context ),
			'subject' => self::subject( $context ),
			'content' => self::content( $context ),
		) );
	}


	/**
	 * Send an email
	 *
	 * @param $args array()
	 *
	 * 'email_type' => // One of the constants defined in this class or empty if specifying the 'subject' and 'message'
	 * 'email',
	 * 'first_name',
	 * 'last_name',
	 * 'subject', (optional if specifying type)
	 * 'message', (optional if specifying type)
	 * 'fields' => array() of key-value pairs to pass to the email for replacement
	 *
	 * @return mixed
	 */
	public static function send_email( $args ) {

		self::$last_args = $args;

		if( isset( $args['email_type'] ) && ! empty( $args['email_type'] ) ) {

			add_filter( 'wp_mail_from', array( __CLASS__, 'email_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'email_from_name' ) );

			$email_settings = self::get_email_fields( $args['email_type'] );

			$args['subject'] = $email_settings['subject'];

			switch( $args['email_type'] ) {

				case self::BASIC_CERTIFICATE:
					$args['message'] = self::basic_certificate_message( $args, $email_settings );
					break;
				case self::REGISTRATION:
					$args['message'] = self::registration_message( $args, $email_settings );
					break;
				case self::ENROLLMENT_CONFIRM:
					$args['message'] = self::enrollment_confirm_message( $args, $email_settings );
					break;
				case self::COURSE_INVITATION:
					$args['message'] = self::course_invitation_message( $args, $email_settings );
					break;
				case self::COURSE_INVITATION_PASSWORD:
					$args['message'] = self::course_invitation_password_message( $args, $email_settings );
					break;
				case self::INSTRUCTOR_INVITATION:
					$args['message'] = self::instructor_invitation_message( $args, $email_settings );
					break;
				case self::NEW_ORDER:
					$args['message'] = self::new_order_message( $args, $email_settings );
					break;

			}

		}

		return CoursePress_Helper_Utility::send_email( $args );

	}

	public static function basic_certificate_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();

		return '';
	}

	public static function registration_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();

		// Email Content
		$tags = array(
			'STUDENT_FIRST_NAME',
			'STUDENT_LAST_NAME',
			'BLOG_NAME',
			'LOGIN_ADDRESS',
			'COURSES_ADDRESS',
			'WEBSITE_ADDRESS'
		);

		$tags_replaces = array(
			sanitize_text_field( $args[ 'first_name' ] ),
			sanitize_text_field( $args[ 'last_name' ] ),
			get_bloginfo(),
			CoursePress_Core::get_setting( 'general/use_custom_login', true ) ? CoursePress_Core::get_slug( 'login', true ) : wp_login_url(),
			CoursePress_Core::get_slug( 'course', true ),
			home_url()
		);

		return str_replace( $tags, $tags_replaces, $email_settings['content'] );

	}

	public static function enrollment_confirm_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}

	public static function course_invitation_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}

	public static function course_invitation_password_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}

	public static function instructor_invitation_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}

	public static function new_order_message( $args, $email_settings ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}


	public static function email_from( $from ) {

		$email_settings = CoursePress_Helper_Email::get_email_fields( self::$last_args['email_type'] );

		$from = $email_settings['email'];

		return $from;
	}

	public static function email_from_name( $from_name ) {

		$email_settings = CoursePress_Helper_Email::get_email_fields( self::$last_args['email_type'] );

		$from = $email_settings['name'];

		return $from;
	}

}