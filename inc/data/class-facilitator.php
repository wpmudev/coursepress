<?php

class CoursePress_Data_Facilitator {

	public static $messages = array();


	public static function init() {
		/**
		 * Intercept virtual page when dealing with invitation code.
		 **/
		self::facilitator_verification();

	}

	/**
	 * Check to see if the current page is the link sent from invitation email.
	 *
	 * @since 3.0.0
	 *
	 * @return (mixed) Returns either an array on success or false for error.
	 **/
	public static function is_course_invite() {
		$action = filter_input( INPUT_GET, 'action' );
		if ( 'course_invite_facilitator' === $action ) {
			$course_id = filter_input( INPUT_GET, 'course_id', FILTER_VALIDATE_INT );
			$code = filter_input( INPUT_GET, 'c' );
			$hash = filter_input( INPUT_GET, 'h' );

			return array(
				'course_id' => $course_id,
				'code' => $code,
				'hash' => $hash,
			);
		}

		return false;
	}

	public static function delete_invitation( $course_id, $invite_code ) {
		$facilitator_invites = get_post_meta(
			$course_id,
			'facilitator_invites',
			true
		);

		if ( $facilitator_invites ) {
			$keys = array_keys( $facilitator_invites );
			if ( in_array( $invite_code, $keys ) ) {
				unset( $facilitator_invites[ $invite_code ] );
			}
		}

		update_post_meta(
			$course_id,
			'facilitator_invites',
			$facilitator_invites
		);
	}

	/**
	 * Verify if it is a valid invitation code.
	 *
	 * @since 3.0.0
	 *
	 * @param (int) $course_id	The course ID.
	 * @param (string) $code	The code that was attached by the verification link.
	 *
	 * @return (mixed) Returns either an array on success or false for error.
	 **/
	public static function verify_invitation_code( $course_id, $code ) {
		$invitation_data = (array) get_post_meta( $course_id, 'facilitator_invites', true );

		return !empty( $invitation_data[ $code ] ) ? $invitation_data[ $code ] : false;
	}

	public static function add_from_invitation( $course_id, $facilitator_id, $facilitator_code ) {
		$invite_data = self::verify_invitation_code( $course_id, $facilitator_code );
		$userdata = get_userdata( $facilitator_id );

		if ( ! empty( $invite_data['email'] ) && $userdata && $invite_data['email'] === $userdata->user_email ) {
			coursepress_add_course_facilitator( $facilitator_id, $course_id );
			self::delete_invitation( $course_id, $invite_data['code'] );

			/**
			 * Facilitator invite confirmed.
			 *
			 * @since 3.0.0
			 *
			 * @param int $course_id The course facilitator was added to.
			 * @param int $facilitator_id The user ID of facilitator assigned.
			 *
			 */
			do_action( 'coursepress_facilitator_invite_confirmed', $course_id, $facilitator_id );

			return true;
		}

		/**
		 * Facilitator confirmation failed.
		 *
		 * Usually when the email sent to and the one trying to register don't match.
		 *
		 * @since 3.0.0
		 *
		 * @param int $course_id The course facilitator was added to.
		 * @param int $facilitator_id The user ID of facilitator assigned.
		 *
		 */
		do_action( 'coursepress_facilitator_invite_confirm_fail', $course_id, $facilitator_id );

		return false;
	}

	/**
	 * Check facilitator verification.
	 *
	 * @since 3.0.0
	 **/
	public static function facilitator_verification() {
		$course_invite = self::is_course_invite();
		if ( !$course_invite ) {
			return;
		}

		$messages = array();
		$is_verified = self::verify_invitation_code( $course_invite['course_id'], $course_invite['code'] );

		if ( $is_verified ) {

			/**
			 * redirect to registration form
			 */
			if ( ! is_user_logged_in() ) {
				$redirect = lib3()->net->current_url();
				$query_args = array(
					'redirect_to' => urlencode( $redirect ),
					'_wpnonce' => wp_create_nonce( 'redirect_to' ),
				);
				$url = coursepress_get_student_login_url( $redirect, $query_args );
				$messages[] = apply_filters( 'coursepress_facilitator_invitation_message_login',
					sprintf( '<a href="%s">%s</a> %s', esc_url( $url ), __( 'Login', 'cp' ), __( 'to continue.', 'cp' ) )
				);
			} else {
				$user = get_user_by( 'email', $is_verified['email'] );
				$user_id = !empty( $user->ID) ? $user->ID : '';

				$is_added = self::add_from_invitation( $course_invite['course_id'], $user_id, $course_invite['code'] );

				if ( $is_added ) {
					$messages[] = apply_filters( 'coursepress_facilitator_invitation_message_congratulations',
						esc_html__( 'Congratulations. You are now a facilitator of this course. ', 'cp' )
					);
				} else {
					$messages = apply_filters( 'coursepress_facilitator_invitation_message_wrong_email',
						array( esc_html__( 'This invitation link is not associated with your email address.', 'cp' ) ,
						esc_html__( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'cp' ) )
					);
				}
			}
		}

		if ( empty( $messages ) ) {
			$messages = apply_filters( 'coursepress_facilitator_invitation_message_error',
				array( esc_html__( 'This invitation could not be found or is no longer available.', 'cp' ),
				esc_html__( 'Please contact us if you believe this to be an error.', 'cp' ) )
			);
		}


		self::$messages = $messages;
		add_filter( 'coursepress_overview_messages', array( __CLASS__, 'add_messages' ) );
	}

	public static function add_messages( $messages ) {
		$messages = array_merge( $messages, self::$messages );
		return $messages;
	}


	/**
	 * Get courses where user is a facilitator.
	 *
	 * @param int $user_id
	 * @param array $status
	 * @param bool $ids_only
	 * @param int $page
	 * @param int $per_page
	 *
	 * @return array
	 */
	public static function get_facilitated_courses( $user_id = 0, $status = array( 'publish' ), $ids_only = false, $page = 0, $per_page = 20 ) {

		if ( empty( $user_id ) ) {
			$user_id = coursepress_get_user_id();
		}

		$args = array(
			'post_type' => 'course',
			'post_status' => $status,
			'meta_key' => 'facilitator',
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
}
