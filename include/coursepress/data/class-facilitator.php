<?php
/**
 * Facilitator Class
 *
 * Controls course facilitator
 *
 * @since 2.0
 **/
class CoursePress_Data_Facilitator {

	private static $facilitators_post_meta_name = 'facilitator_invites';

	/**
	 * Add course facilitator.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id
	 * @param (int) $user_id.
	 **/
	public static function add_course_facilitator( $course_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			return false; // Bail if no user ID specified!
		}

		$course_facilitators = self::get_course_facilitators( $course_id );

		// Check if current ID already exist
		if ( in_array( $user_id, $course_facilitators ) ) {
			return; // Bail!
		}

		// Add to course facilitator list
		add_post_meta( $course_id, 'course_facilitator', $user_id );

		// Set capabilities
		CoursePress_Data_Capabilities::assign_facilitator_capabilities( $user_id );

		do_action( 'coursepress_facilitator_added', $course_id, $user_id );

	}

	/**
	 * Get the facilitators of a course.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id
	 * @param (bool) $ids_only 	Wether to return an array of user IDs or user object.
	 * @return (mixed) array of user_id or WP_User object.
	 **/
	public static function get_course_facilitators( $course_id, $ids_only = true ) {
		$facilitators = (array) get_post_meta( $course_id, 'course_facilitator' );
		$facilitators = array_unique( array_filter( $facilitators ) );

		if ( ! $ids_only ) {
			foreach ( $facilitators as $pos => $user_id ) {
				$facilitators[ $user_id ] = get_userdata( $user_id );
				unset( $facilitators[ $pos ] );
			}
			$facilitators = array_filter( $facilitators );
		}

		return $facilitators;
	}

	/**
	 * Check if user is facilitator to a course.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id
	 * @param (int) $user_id
	 *
	 * @return (bool) 	Return true if facilitator otherwise false.
	 **/
	public static function is_course_facilitator( $course_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$facilitators = self::get_course_facilitators( $course_id );

		return in_array( $user_id, $facilitators );
	}

	/**
	 * Remove course facilitator.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id
	 * @param (int) $user_id
	 **/
	public static function remove_course_facilitator( $course_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		delete_post_meta( $course_id, 'course_facilitator', $user_id );

		// Check if current user has courses left to facilitate
		$courses = self::get_facilitated_courses( $user_id, array( 'publish', 'draft', 'private', 'pending' ), true, 1, 1 );

		if ( empty( $courses ) ) {
			// Check if user is also an instructor
			$can_update_course = self::is_course_facilitator( $course_id, $user_id );

			if ( $can_update_course ) {
				// Because both facilitator and instructor share the same capabilities, only remove the role
				$global_option = ! is_multisite();
				delete_user_option( $user_id, 'cp_role', $global_option );
			} else {
				CoursePress_Data_Capabilities::drop_instructor_capabilities( $user_id );
			}
		}

		do_action( 'coursepress_facilitator_removed', $course_id, $user_id );
	}

	public static function get_facilitated_courses( $user_id = 0, $status = array( 'publish' ), $ids_only = false, $page = 0, $per_page = 20 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $status,
			'meta_key' => 'course_facilitator',
			'meta_value' => $user_id,
			'meta_compare' => 'IN',
			'suppress_filters' => true,
		);
		if ( 0 < $per_page ) {
			$args['paged'] = $page;
			$args['posts_per_page'] = $per_page;
		} else {
			$args['nopaging'] = true;
		}

		if ( $ids_only ) {
			$args['fields'] = 'ids';
		}

		$courses = get_posts( $args );

		return $courses;
	}

	/**
	 * Send invitation to new Facilitator.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param string $email Email of new facilitator.
	 * @param string $first_name First name of new facilitator.
	 * @param string $last_name Last name of new facilitator.
	 * @return array Status of invitation.
	 */
	public static function send_invitation( $course_id, $email, $first_name, $last_name ) {
		// So that we can use it later.
		CoursePress_Data_Course::set_last_course_id( $course_id );

		// Return data: Can be used by caller to get extra information
		$return_data = array();

		$email_args['course_id'] = $course_id;
		$email_args['email'] = $email;
		$email_args['first_name'] = $first_name;
		$email_args['last_name'] = $last_name;

		$invite_data = CoursePress_Data_Instructor::create_invite_code_hash( $email_args );
		$email_args['invite_code'] = $invite_data['code'];
		$email_args['invite_hash'] = $invite_data['hash'];

		// Get invites
		$facilitator_invites = self::get_invitations_by_course_id( $course_id );

		// Create Course invites if they don't exist, and check to see if this invite is already there.
		$invite_exists = false;
		$invite_code = '';
		if ( $facilitator_invites ) {
			foreach ( $facilitator_invites as $key => $i ) {
				$invite_exists = array_search( $email_args['email'], $i );
				if ( $invite_exists ) {
					// Update code and hash for re-send.
					$email_args['invite_code'] = $i['code'];
					$email_args['invite_hash'] = $i['hash'];
				}
			}
		} else {
			$facilitator_invites = array();
		}

		// Fire off the email, data altered in the hooks below.
		$sent = CoursePress_Helper_Email::send_email(
			CoursePress_Helper_Email::FACILITATOR_INVITATION,
			$email_args
		);

		if ( $sent ) {
			if ( ! $invite_exists ) {
				// Add the new invite
				$invite = array(
					'first_name' => $email_args['first_name'],
					'last_name' => $email_args['last_name'],
					'email' => $email_args['email'],
					'code' => $email_args['invite_code'],
					'hash' => $email_args['invite_hash'],
				);

				$facilitator_invites[ $email_args['invite_code'] ] = $invite;

				update_post_meta(
					$course_id,
					self::$facilitators_post_meta_name,
					$facilitator_invites
				);

				// Invite sent and added.
				$return_data['success'] = true;
				$return_data['invite_code'] = $email_args['invite_code'];
				$return_data = CoursePress_Helper_Utility::set_array_value(
					$return_data,
					'message/sent',
					__( 'Invitation successfully sent.', 'coursepress' )
				);

			} else {
				// Invite already exists.
				$return_data['success'] = true;
				$return_data['invite_code'] = $email_args['invite_code'];
				$return_data = CoursePress_Helper_Utility::set_array_value(
					$return_data,
					'message/exists',
					__( 'Invitation already exists. Invitation was re-sent.', 'coursepress' )
				);
			}
		} else {
			// Email not sent.
			$return_data['success'] = false;
			$return_data = CoursePress_Helper_Utility::set_array_value(
				$return_data,
				'message/send_error',
				__( 'Email failed to send.', 'coursepress' )
			);
		};

		if ( ! isset( $return_data['message']['exists'] ) ) {
			$return_data['message']['exists'] = __( 'Invitation already exists.', 'coursepress' );
		}

		return $return_data;
	}

	/**
	 * Check to see if the current page is the link sent from invitation email.
	 *
	 * @since 2.0
	 *
	 * @return (mixed)	 Returns an (object) on success and false if for error.
	 **/
	public static function is_course_invite() {
		if ( isset( $_GET['action'] ) && 'course_invite_facilitator' == $_GET['action'] ) {
			$course_id = (int) $_GET['course_id'];
			$code = $_GET['c'];
			$hash = $_GET['h'];
			$invitation_data = (array) get_post_meta( $course_id, self::$facilitators_post_meta_name, true );

			return (object) array(
				'course_id' => $course_id,
				'code' => $code,
				'hash' => $hash,
				'invitation_data' => $invitation_data,
			);
		}

		return false;
	}

	/**
	 * Verify if it is a valid invitation code.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id 	The course ID.
	 * @param (string) $code 	 The code that was attached by the verification link.
	 * @param (array) $invitation_data	 The list of invitations sent.
	 *
	 * @return (bool)
	 **/
	public static function verify_invitation_code( $course_id, $code, $invitation_data = false ) {
		$invitation_data = ! $invitation_data ? (array) get_post_meta( $course_id, self::$facilitators_post_meta_name, true ) : (array) $invitation_data;
		$is_valid = in_array( $code, array_keys( $invitation_data ) );

		return $is_valid ? $invitation_data[ $code ] : false;
	}

	public static function add_from_invitation( $course_id, $instructor_id, $invitation_code ) {
		$invite_data = self::verify_invitation_code( $course_id, $invitation_code );
		$userdata = get_userdata( $instructor_id );

		if ( ! empty( $invite_data['email'] ) && $invite_data['email'] == $userdata->user_email ) {
			self::add_course_facilitator( $course_id, $instructor_id );
			self::delete_invitation( $course_id, $invite_data['code'] );

			/**
			 * Instructor invite confirmed.
			 *
			 * @since 1.2.1
			 *
			 * @param int course_id The course instructor was added to.
			 * @param int instructor_id The user ID of instructor assigned.
			 *
			 */
			do_action( 'coursepress_facilitator_invite_confirmed', $course_id, $instructor_id );

			return true;
		}

		/**
		 * Instructor confirmation failed.
		 *
		 * Usually when the email sent to and the one trying to register don't match.
		 *
		 * @since 1.2.1
		 *
		 * @param int course_id The course instructor was added to.
		 * @param int instructor_id The user ID of instructor assigned.
		 *
		 */
		do_action( 'coursepress_facilitator_invite_confirm_fail', $course_id, $instructor_id );

		return false;
	}

	public static function delete_invitation( $course_id, $invite_code ) {
		$instructor_invites = get_post_meta(
			$course_id,
			self::$facilitators_post_meta_name,
			true
		);

		if ( $instructor_invites ) {
			$keys = array_keys( $instructor_invites );
			if ( in_array( $invite_code, $keys ) ) {
				unset( $instructor_invites[ $invite_code ] );
			}
		}

		update_post_meta(
			$course_id,
			self::$facilitators_post_meta_name,
			$instructor_invites
		);
	}

	/**
	 * Get course invitations.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @return array Array of invitations.
	 */
	public static function get_invitations_by_course_id( $course_id ) {
		return get_post_meta(
			$course_id,
			self::$facilitators_post_meta_name,
			true
		);
	}
}
