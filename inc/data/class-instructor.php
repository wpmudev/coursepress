<?php

class CoursePress_Data_Instructor {

	public static $messages = array();


	public static function init() {
		/**
		 * Intercept virtual page when dealing with invitation code.
		 **/
		self::instructor_verification();

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
		if ( 'course_invite' === $action ) {
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
		$instructor_invites = get_post_meta(
			$course_id,
			'instructor_invites',
			true
		);

		$return = false;
		if ( $instructor_invites ) {
			$keys = array_keys( $instructor_invites );
			if ( in_array( $invite_code, $keys ) ) {
				unset( $instructor_invites[ $invite_code ] );
				update_post_meta(
					$course_id,
					'instructor_invites',
					$instructor_invites
				);
				$return = true;
			}
		}

		return $return;
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
		$invitation_data = (array) get_post_meta( $course_id, 'instructor_invites', true );

		return !empty( $invitation_data[ $code ] ) ? $invitation_data[ $code ] : false;
	}

	public static function add_from_invitation( $course_id, $instructor_id, $invitation_code ) {
		$invite_data = self::verify_invitation_code( $course_id, $invitation_code );
		$userdata = get_userdata( $instructor_id );

		if ( ! empty( $invite_data['email'] ) && $userdata && $invite_data['email'] === $userdata->user_email ) {
			coursepress_add_course_instructor( $instructor_id, $course_id );
			self::delete_invitation( $course_id, $invite_data['code'] );

			/**
			 * Instructor invite confirmed.
			 *
			 * @since 3.0.0
			 *
			 * @param int course_id The course instructor was added to.
			 * @param int instructor_id The user ID of instructor assigned.
			 *
			 */
			do_action( 'coursepress_instructor_invite_confirmed', $course_id, $instructor_id );

			return true;
		}

		/**
		 * Instructor confirmation failed.
		 *
		 * Usually when the email sent to and the one trying to register don't match.
		 *
		 * @since 3.0.0
		 *
		 * @param int course_id The course instructor was added to.
		 * @param int instructor_id The user ID of instructor assigned.
		 *
		 */
		do_action( 'coursepress_instructor_invite_confirm_fail', $course_id, $instructor_id );

		return false;
	}

	/**
	 * Check instructor verification.
	 *
	 * @since 3.0.0
	 **/
	public static function instructor_verification() {
		$course_invite = self::is_course_invite();
		if ( !$course_invite ) {
			return;
		}

		$messages = array();
		$is_verified = self::verify_invitation_code( $course_invite['course_id'], $course_invite['code'] );

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
			$messages[] = apply_filters( 'coursepress_instructor_invitation_message_login',
				sprintf( '<a href="%s">%s</a> %s', esc_url( $url ), __( 'Login', 'cp' ), __( 'to continue.', 'cp' ) )
			);
		} elseif ( $is_verified ) {
			$user = get_user_by( 'email', $is_verified['email'] );
			$user_id = !empty( $user->ID) ? $user->ID : '';

			$is_added = self::add_from_invitation( $course_invite['course_id'], $user_id, $course_invite['code'] );

			if ( $is_added ) {
				$messages[] = apply_filters( 'coursepress_instructor_invitation_message_congratulations',
					esc_html__( 'Congratulations. You are now an instructor of this course. ', 'cp' )
				);
			} else {
				$messages = apply_filters( 'coursepress_instructor_invitation_message_wrong_email',
					array(
						esc_html__( 'This invitation link is not associated with your email address.', 'cp' ) ,
						esc_html__( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'cp' ),
					)
				);
			}
		}

		if ( empty( $messages ) ) {
			$messages = apply_filters( 'coursepress_instructor_invitation_message_error',
				array(
					esc_html__( 'This invitation could not be found or is no longer available.', 'cp' ),
					esc_html__( 'Please contact us if you believe this to be an error.', 'cp' ),
				)
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
	 * Callback for array_filter() that will return the meta-key if it
	 * indicates an instructor-course-link.
	 *
	 * So this function only returns values if the associated user is an
	 * instructor.
	 *
	 * @since  2.0.0
	 *
	 * @param string $meta_key
	 *
	 * @return mixed
	 */
	public static function filter_course_meta_array( $meta_key ) {

		global $wpdb;

		$regex = array();
		$regex[] = 'course_\d+';
		$regex[] = $wpdb->prefix . 'course_\d+';
		if ( is_multisite() && defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE === get_current_blog_id() ) {
			$regex[] = $wpdb->base_prefix . 'course_\d+';
		}

		$pattern = sprintf( '/^(%s)$/', implode( '|', $regex ) );

		if ( preg_match( $pattern, $meta_key ) ) {
			return $meta_key;
		}

		return false;
	}

	/**
	 * Get course meta keys.
	 *
	 * @param int|object $user
	 *
	 * @return array|mixed
	 */
	public static function get_course_meta_keys( $user ) {

		$user_id = coursepress_get_user_id( $user );

		if ( 0 < $user_id ) {
			$meta = get_user_meta( $user_id );
			$meta = array_filter( array_keys( $meta ), array( __CLASS__, 'filter_course_meta_array' ) );

			return $meta;
		}

		return array();
	}

	/**
	 * Return a list of courses of which the specified user is an instructor.
	 *
	 * @since  2.0.0
	 *
	 * @param  int|WP_User $user The instructor/user to check.
	 * @param  string      $status all|publish|draft.
	 *
	 * @return array List of course IDs.
	 */
	public static function get_assigned_courses_ids( $user, $status = 'all' ) {

		global $wpdb, $coursepress_core;

		$assigned_courses = array();

		$courses = self::get_course_meta_keys( coursepress_get_user_id( $user ) );

		foreach ( $courses as $course ) {
			$course_id = $course;

			// Careful that we don't pick up students
			if ( preg_match( '/_progress$/', $course_id ) ) {
				continue;
			}

			// Dealing with multisite nuances
			if ( is_multisite() ) {
				// Primary blog?
				if ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE === get_current_blog_id() ) {
					$course_id = str_replace( $wpdb->base_prefix, '', $course_id );
				} else {
					$course_id = str_replace( $wpdb->prefix, '', $course_id );
				}
			}

			$course_id = (int) str_replace( 'course_', '', $course_id );

			if ( ! empty( $course_id ) ) {
				if ( 'all' !== $status ) {
					if ( get_post_status( $course_id ) === $status ) {
						$assigned_courses[] = $course_id;
					}
				} else {
					$assigned_courses[] = $course_id;
				}
			}
		}

		$course_ids = array();

		if ( ! empty( $assigned_courses ) ) {
			// Filter the course IDs, make sure courses exists and are not deleted
			$args = array(
				'post_type' => $coursepress_core->course_post_type,
				'post_status' => 'any',
				'suppress_filters' => true,
				'fields' => 'ids',
				'post__in' => $assigned_courses,
				'posts_per_page' => -1,
			);
			$course_ids = get_posts( $args );
		}

		return $course_ids;
	}

	/**
	 * Return a list of courses of which the specified user is an creater.
	 *
	 * @since  3.0.0
	 *
	 * @param  int|WP_User $user The instructor/user to check.
	 * @param  string      $status all|publish|draft.
	 *
	 * @return array List of course IDs.
	 */
	public static function get_created_courses_ids( $user, $status = 'any' ) {
		global $coursepress_core;
		// Filter the course IDs, make sure courses exists and are not deleted
		$args = array(
			'post_type' => $coursepress_core->course_post_type,
			'post_status' => $status,
			'suppress_filters' => true,
			'fields' => 'ids',
			'author' => $user,
			'posts_per_page' => -1,
		);
		$course_ids = get_posts( $args );
		return $course_ids;
	}

	/**
	 * Get assigned courses count for the user.
	 *
	 * @param object $user WP_User object.
	 *
	 * @return int
	 */
	public static function get_course_count( $user ) {

		return self::get_courses_number( coursepress_get_user_id( $user ) );
	}

	/**
	 * Get number of instructor's assigned courses.
	 *
	 * @param object $user WP_User object.
	 *
	 * @return int
	 */
	public static function get_courses_number( $user ) {

		return count( self::get_course_meta_keys( $user ) );
	}

	/**
	 * Is the user assigned to course?
	 *
	 * @param int $instructor_id Instructor ID.
	 * @param int $course_id Course ID.
	 *
	 * @return bool
	 */
	public static function is_assigned_to_course( $instructor_id, $course_id ) {

		$instructor_course_id = get_user_option( 'course_' . $course_id, $instructor_id );
		if ( ! empty( $instructor_course_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get instructor by md5 hash.
	 *
	 * @param string $hash MD% hash.
	 *
	 * @return bool|false|WP_User
	 */
	public static function instructor_by_hash( $hash ) {

		global $wpdb;
		// Check cache first!
		$user_id = wp_cache_get( $hash, 'coursepress_userhash' );
		if ( is_multisite() ) {
			$hash = $wpdb->prefix . $hash;
		}
		// Not in cache, so retrieve.
		if ( empty( $user_id ) ) {
			$sql = $wpdb->prepare( 'SELECT user_id FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key = %s', $hash );
			$user_id = $wpdb->get_var( $sql );
			wp_cache_add( $hash, $user_id, 'coursepress_userhash' );
		}

		return empty( $user_id ) ? false : get_userdata( $user_id );
	}

	/**
	 * Create md5 hash for the user.
	 *
	 * @param int|WP_User $user
	 */
	public static function create_hash( $user ) {

		$user_id = coursepress_get_user_id( $user );
		$user = get_userdata( $user_id );
		if ( empty( $user ) ) {
			return;
		}
		$hash = md5( $user->user_login );
		$global_option = ! is_multisite();
		/**
		 * Just in case someone is actually using this hash for something,
		 * we'll populate it with current value. Will be an empty array if
		 * nothing exists. We're only interested in the key anyway.
		 */
		update_user_option( $user_id, $hash, time(), $global_option );
		// Put it in cache.
		wp_cache_add( $hash, $user_id, 'coursepress_userhash' );
	}

	/**
	 * Get md5 hash for the user.
	 *
	 * @param int|WP_User $user
	 *
	 * @return bool|string
	 */
	public static function get_hash( $user ) {

		$user_id = coursepress_get_user_id( $user );
		$user = get_userdata( $user_id );
		$hash = md5( $user->user_login );
		$option = get_user_option( $hash, $user_id );
		if ( empty( $option ) ) {
			self::create_hash( $user_id );
		}

		return null === $option ? false : $hash;
	}
}
