<?php
function coursepress_visual_editor( $content, $id, $settings = array() ) {
    if ( empty( $settings['editor_height'] ) ) {
        $settings['editor_height'] = 300;
    }
    wp_editor( $content, $id, $settings );
}

function coursepress_teeny_editor( $content, $id, $settings = array() ) {
    $settings['teeny'] = true;
    $settings['media_buttons'] = false;

    coursepress_visual_editor( $content, $id, $settings );
}

/**
 * Send email invitation to the user.
 *
 * @param string $email Email address.
 * @param string $course_id Course ID.
 * @param string $type Type of user.
 *
 * @return bool Email sent?
 */
function coursepress_send_email_invite( $email, $course_id, $type = 'instructor' ) {

	// Do not continue if required values are empty.
	if ( empty( $course_id ) || ! is_email ( $email ) ) {
		return false;
	}

	// Do not continue if the course does not exist.
	if ( empty( $course = coursepress_get_course( $course_id ) ) ) {
		return false;
	}

	// Get the title of the course.
	$title = empty( $course->post_title ) ? ' a course' : $course->post_title;

	switch ( $type ) {
		case 'instructor':
			$subject = __( 'Invitation as an instructor', 'cp' );
			$message = sprintf( __( 'You have been invited as an instructor to %s. Please get in touch.', 'cp' ), $title );
			break;

		case 'facilitator':
			$subject = __( 'Invitation as a facilitator', 'cp' );
			$message = sprintf( __( 'You have been invited as a facilitator to %s. Please get in touch.', 'cp' ), $title );
			break;
	}

	// If required params are set, send email.
	if ( ! empty( $subject ) && $message ) {
		return wp_mail( $email, $subject, $message );
	}

	return false;
}
