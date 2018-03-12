<?php
function coursepress_visual_editor( $content, $id, $settings = array() ) {
    if ( empty( $settings['editor_height'] ) ) {
        $settings['editor_height'] = 300;
    }
    wp_editor( $content, $id, $settings );
}

//function coursepress_teeny_editor( $content, $id, $settings = array() ) {
//    $settings['teeny'] = true;
//    $settings['media_buttons'] = false;
//
//    coursepress_visual_editor( $content, $id, $settings );
//}

/**
 * Send email invitation to the instructor/facilitator.
 *
 * @param array $args Data.
 * @param string $type Type of user.
 *
 * @return bool Email sent?
 */
function coursepress_send_email_invite( $args, $type = 'instructor' ) {

	// Sanitize email address.
	$email = sanitize_email( $args['email'] );
	$course_id = intval( $args['course_id'] );

	// Create new invite code and hash.
	$invite_data = CoursePress_Data_Course::create_invite_code_hash( $email );
	$args['invite_code'] = $invite_data['code'];
	$args['invite_hash'] = $invite_data['hash'];

	// Get existing invites for the instructors.
	$invites = CoursePress_Data_Course::get_invitations_by_course_id( $course_id, $type );
	$invite_exists = false;

	// Check to see if this invite is already there.
	if ( $invites ) {
		foreach ( $invites as $invite => $data ) {
			$invite_exists = array_search( $email, $data );
			if ( $invite_exists ) {
				// Update code and hash for re-send.
				$args['invite_code'] = $data['code'];
				$args['invite_hash'] = $data['hash'];
			}
		}
	}

	// Fire off the email based on type.
	if ( $type === 'instructor' ) {
		$sent = CoursePress_Data_Email::send_email( CoursePress_Data_Email::INSTRUCTOR_INVITATION, $args );
	} elseif ( $type === 'facilitator'  ) {
		$sent = CoursePress_Data_Email::send_email( CoursePress_Data_Email::FACILITATOR_INVITATION, $args );
	} else {
		return false;
	}

	// Update post meta only if new invite and email sent.
	if ( $sent && ! $invite_exists ) {
		// Add the new invite
		$invite = array(
			'first_name' => $args['first_name'],
			'last_name' => $args['last_name'],
			'email' => $email,
			'code' => $args['invite_code'],
			'hash' => $args['invite_hash'],
		);

		$invites[ $args['invite_code'] ] = $invite;

		// Set meta name.
		$meta_name = $type === 'instructor' ? 'instructor_invites' : 'facilitator_invites';
		// Update meta with invite data.
		update_post_meta(
			$course_id,
			$meta_name,
			$invites
		);
	}

	return $sent;
}
