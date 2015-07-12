<?php

class CoursePress_Helper_Email {

	const BASIC_CERTIFICATE = 'basic_certificate';
	const REGISTRATION = 'registration';
	const ENROLLMENT_CONFIRM = 'enrollment_confirm';
	const COURSE_INVITATION = 'course_invitation';
	const COURSE_INVITATION_PASSWORD = 'course_invitation_password';
	const INSTRUCTOR_INVITATION = 'instructor_invitation';
	const NEW_ORDER = 'new_order';

	public static function from_name( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return $fields['from_name'];
	}

	public static function from_email( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return $fields['from_email'];
	}

	public static function subject( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return $fields['subject'];
	}

	public static function content( $context ) {
		$fields = CoursePress_Helper_Settings_Email::get_defaults( $context );

		return $fields['content'];
	}

	public static function get_email_fields( $context ) {
		return apply_filters( 'coursepress_get_email_fields_' . $context, array(
			'name'    => self::from_name( $context ),
			'email'   => self::from_email( $context ),
			'subject' => self::subject( $context ),
			'content' => self::content( $context ),
		) );
	}

}