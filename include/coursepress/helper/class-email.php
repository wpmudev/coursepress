<?php

class CoursePress_Helper_Email {

	/**
	 * Email type.
	 * Used by CoursePress_Data_Certificate::send_certificate().
	 */
	const BASIC_CERTIFICATE = 'basic_certificate';

	/**
	 * Email type.
	 * Used by CoursePress_Data_Student::send_registration().
	 */
	const REGISTRATION = 'registration';

	/**
	 * Email type.
	 * Used by CoursePress_Data_Course::enroll_student().
	 */
	const ENROLLMENT_CONFIRM = 'enrollment_confirm';

	/**
	 * Email type.
	 * Used by CoursePress_Data_Course::send_invitation().
	 */
	const COURSE_INVITATION = 'course_invitation';

	/**
	 * Email type.
	 * Used by CoursePress_Data_Course::send_invitation().
	 */
	const COURSE_INVITATION_PASSWORD = 'course_invitation_password';

	/**
	 * Email type.
	 * Used by CoursePress_Data_Instructor::send_invitation().
	 */
	const INSTRUCTOR_INVITATION = 'instructor_invitation';

	/**
	 * Email type.
	 * (not used anywhere yet)
	 */
	const NEW_ORDER = 'new_order';

	/**
	 * Stores the current email-template-type for usage in filter-callbacks.
	 *
	 * @var string
	 */
	protected static $current_type = '';

	/**
	 * Return default content for email, by email-type.
	 *
	 * @since  1.0.0
	 * @param  string $email_type Email-template-type.
	 * @return array Email specifications.
	 */
	public static function get_email_fields( $email_type ) {
		return apply_filters(
			'coursepress_get_email_fields-' . $email_type,
			array(
				'name' => self::from_name( $email_type ),
				'email' => self::from_email( $email_type ),
				'subject' => self::subject( $email_type ),
				'content' => self::content( $email_type ),
			)
		);
	}

	/**
	 * Send an email.
	 *
	 * @param string $type One of the constants defined in this class or empty
	 *               if specifying the 'subject' and 'message'.
	 * @param array  $args Variables and email content.
	 *               email .. recipient.
	 *               message .. optional if specifying type.
	 *               subject .. optional if specifying type.
	 *               first_name
	 *               last_name
	 *               fields .. content variables, array of key-value pairs.
	 * @return mixed
	 */
	public static function send_email( $type, $args ) {
		self::$current_type = $type;

		if ( ! empty( $type ) ) {
			add_filter( 'wp_mail_from', array( __CLASS__, 'wp_mail_from' ) );
			add_filter( 'wp_mail_from_name', array( __CLASS__, 'wp_mail_from_name' ) );

			$email_settings = self::get_email_fields( $type );

			$args['subject'] = $email_settings['subject'];

			switch ( $type ) {
				case self::BASIC_CERTIFICATE:
					$args['message'] = self::basic_certificate_message(
						$args,
						$email_settings['content']
					);
					break;

				case self::REGISTRATION:
					$args['message'] = self::registration_message(
						$args,
						$email_settings['content']
					);
					break;

				case self::ENROLLMENT_CONFIRM:
					$args['message'] = self::enrollment_confirm_message(
						$args,
						$email_settings['content']
					);
					break;

				case self::COURSE_INVITATION:
					$args['message'] = self::course_invitation_message(
						$args,
						$email_settings['content']
					);
					break;

				case self::COURSE_INVITATION_PASSWORD:
					$args['message'] = self::course_invitation_password_message(
						$args,
						$email_settings['content']
					);
					break;

				case self::INSTRUCTOR_INVITATION:
					$args['message'] = self::instructor_invitation_message(
						$args,
						$email_settings['content']
					);
					break;

				case self::NEW_ORDER:
					$args['message'] = self::new_order_message(
						$args,
						$email_settings['content']
					);
					break;
			}
		}

		return self::process_and_send( $type, $args );
	}

	/**
	 * Send a CoursePress email template to a single user.
	 *
	 * @since  1.0.0
	 * @param  array $args Email args.
	 * @return bool True if the email was processed correctly.
	 */
	protected static function process_and_send( $type, $args ) {
		// Legacy support for args['email']. Remove this in future!
		if ( ! empty( $args['email'] ) && empty( $args['to'] ) ) {
			$args['to'] = $args['email'];
		}

		if ( empty( $args['to'] ) ) {
			throw new Exception( 'Error: No email recipient!' );
		}
		if ( empty( $args['message'] ) ) {
			throw new Exception( 'Error: Empty email body!' );
		}
		if ( empty( $args['subject'] ) ) {
			throw new Exception( 'Error: Empty email subject!' );
		}

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
					'Content-type' => 'text/html',
				)
			),
		);

		$email = apply_filters(
			'coursepress_email_fields',
			$email,
			$args,
			$type
		);
		$email = apply_filters(
			'coursepress_email_fields-' . $type,
			$email,
			$args
		);

		// Good one to hook if you want to hook WP specific filters (e.g. changing from address)
		do_action( 'coursepress_email_pre_send', $args, $type );
		do_action( 'coursepress_email_pre_send-' . $type, $args );

		if ( apply_filters( 'coursepress_email_strip_slashed', true, $args, $type ) ) {
			$email['subject'] = stripslashes( $email['subject'] );
			$email['message'] = stripslashes( nl2br( $email['message'] ) );
		}

		$header_string = '';
		foreach ( $email['headers'] as $key => $value ) {
			$header_string .= $key . ': ' . $value . "\r\n";
		}

		$result = wp_mail(
			$email['to'],
			$email['subject'],
			CoursePress_Helper_Utility::filter_content( $email['message'] ),
			$header_string
		);

		do_action( 'coursepress_email_sent', $args, $type, $result );
		do_action( 'coursepress_email_sent-' . $type, $args, $result );

		return $result;
	}

	/*
	 ***************************************************************************
	 * Fetch email settings from DB.
	 ***************************************************************************
	 */

	protected static function from_name( $email_type ) {
		$fields = CoursePress_Helper_Setting_Email::get_defaults( $email_type );

		return CoursePress_Core::get_setting(
			'email/' . $email_type . '/from_name',
			$fields['from_name']
		);
	}

	protected static function from_email( $email_type ) {
		$fields = CoursePress_Helper_Setting_Email::get_defaults( $email_type );

		return CoursePress_Core::get_setting(
			'email/' . $email_type . '/from_email',
			$fields['from_email']
		);
	}

	protected static function subject( $email_type ) {
		$fields = CoursePress_Helper_Setting_Email::get_defaults( $email_type );

		return CoursePress_Core::get_setting(
			'email/' . $email_type . '/subject',
			$fields['subject']
		);
	}

	protected static function content( $email_type ) {
		$fields = CoursePress_Helper_Setting_Email::get_defaults( $email_type );

		return CoursePress_Core::get_setting(
			'email/' . $email_type . '/content',
			$fields['content']
		);
	}

	/**
	 * Hooks into `wp_mail_from` to provide a custom sender email address.
	 *
	 * @since  2.0.0
	 * @param  string $from Default WP Sender address.
	 * @return string Custom sender address.
	 */
	public static function wp_mail_from( $from ) {
		return self::from_email( self::$current_type );
	}

	/**
	 * Hooks into `wp_mail_from_name` to provide a custom sender name.
	 *
	 * @since  2.0.0
	 * @param  string $from_name Default WP Sender name.
	 * @return string Custom sender name.
	 */
	public static function wp_mail_from_name( $from_name ) {
		return self::from_name( self::$current_type );
	}

	/*
	 ***************************************************************************
	 * Prepare default email contents.
	 ***************************************************************************
	 */

	protected static function basic_certificate_message( $args, $content ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// TODO: Finish this!
		return '';
	}

	protected static function registration_message( $args, $content ) {
		if ( CoursePress_Core::get_setting( 'general/use_custom_login', true ) ) {
			$login_url = CoursePress_Core::get_slug( 'login', true );
		} else {
			$login_url = wp_login_url();
		}

		$tags_replaces = array(
			sanitize_text_field( $args['first_name'] ),
			sanitize_text_field( $args['last_name'] ),
			get_bloginfo(),
			$login_url,
			CoursePress_Core::get_slug( 'course', true ),
			home_url(),
		);

		return str_replace( $tags, $tags_replaces, $email_settings['content'] );

	}

	protected static function enrollment_confirm_message( $args, $content ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}

	protected static function course_invitation_message( $args, $content ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}

	protected static function course_invitation_password_message( $args, $content ) {
		// Not clear yet, why this email has 2 different types.
		// @see CoursePress_Data_Course::send_invitation()
		return self::course_invitation_message( $args, $content );
	}

	protected static function instructor_invitation_message( $args, $content ) {
	}

	protected static function new_order_message( $args, $content ) {
		$fields = isset( $args['fields'] ) ? $args['fields'] : array();
		// Currently hooked elsewhere
		return '';
	}
}
