<?php

class CoursePress_Data_Instructor {

	private static function _get_id( $user ) {
		if ( ! is_object( $user ) ) {
			return $user;
		} else {
			return $user->ID;
		}
	}

	public static function get_first_name( $user ) {
		return get_user_meta( self::_get_id( $user ), 'first_name', true );
	}

	public static function get_last_name( $user ) {
		return get_user_meta( self::_get_id( $user ), 'last_name', true );
	}

	public static function get_course_count( $user ) {
		return self::get_courses_number( self::_get_id( $user ) );
	}


	public static function get_course_meta_keys( $user ) {
		$meta = get_user_meta( self::_get_id( $user ) );
		$meta = array_filter( array_keys( $meta ), array( __CLASS__, 'filter_course_meta_array' ) );

		return $meta;
	}

	public static function filter_course_meta_array( $var ) {
		global $wpdb;
		if ( preg_match( '/^course\_/', $var ) || preg_match( '/^' . $wpdb->prefix . 'course\_/', $var ) ||
			( is_multisite() && ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == get_current_blog_id() ) && preg_match( '/^' . $wpdb->base_prefix . 'course\_/', $var ) )
		) {
			return $var;
		}
	}

	public static function get_assigned_courses_ids( $user, $status = 'all' ) {
		global $wpdb;

		$assigned_courses = array();

		$courses = self::get_course_meta_keys( self::_get_id( $user ) );

		foreach ( $courses as $course ) {
			$course_id = $course;

			// Careful that we don't pick up students
			if ( preg_match( '/_progress$/', $course_id ) ) {
				continue;
			}

			// Dealing with multisite nuances
			if ( is_multisite() ) {
				// Primary blog?
				if ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == get_current_blog_id() ) {
					$course_id = str_replace( $wpdb->base_prefix, '', $course_id );
				} else {
					$course_id = str_replace( $wpdb->prefix, '', $course_id );
				}
			}

			$course_id = (int) str_replace( 'course_', '', $course_id );

			if ( ! empty( $course_id ) ) {
				if ( 'all' != $status ) {
					if ( get_post_status( $course_id ) == $status ) {
						$assigned_courses[] = $course_id;
					}
				} else {
					$assigned_courses[] = $course_id;
				}
			}
		}

		return $assigned_courses;
	}

	public static function get_accessable_courses( $user, $include_posts = false ) {

		$user_id = self::_get_id( $user );
		$courses = self::get_assigned_courses_ids( $user_id );
		$course_array = array();

		foreach ( $courses as $course ) {

			// @todo ADD CAPABILITIES CLASS
			// $can_update = CoursePress_Capabilities::can_update_course( $course, $user_id );
			// $can_delete = CoursePress_Capabilities::can_delete_course( $course, $user_id );
			// $can_publish = CoursePress_Capabilities::can_change_course_status( $course, $user_id );
			// $can_view_unit = CoursePress_Capabilities::can_view_course_units( $course, $user_id );
			// $my_course = CoursePress_Capabilities::is_course_instructor( $course, $user_id );
			// $creator = CoursePress_Capabilities::is_course_creator( $course, $user_id );
			$my_course = true;

			if ( ! $my_course && ! $creator && ! $can_update && ! $can_delete && ! $can_publish && ! $can_view_unit ) {
				continue;
			} else {
				$course_array[] = $course;
			}
		}

		if ( ! $include_posts ) {
			return $course_array;
		} else {
			$post_type = CoursePress_Data_Course::get_post_type_name( true );
			$query = new WP_Query( array( 'post__in' => $course_array, 'post_type' => $post_type, 'posts_per_page' => -1 ) );
			return $query->posts;
		}

	}

	public static function unassign_from_course( $user, $course_id = 0 ) {
		$user_id = self::_get_id( $user );
		$global_option = ! is_multisite();
		delete_user_option( $user_id, 'course_' . $course_id, $global_option );
		delete_user_option( $user_id, 'enrolled_course_date_' . $course_id, $global_option );
		delete_user_option( $user_id, 'enrolled_course_class_' . $course_id, $global_option );
		delete_user_option( $user_id, 'enrolled_course_group_' . $course_id, $global_option );

		// Legacy
		delete_user_meta( $user_id, 'course_' . $course_id );
		delete_user_meta( $user_id, 'enrolled_course_date_' . $course_id );
		delete_user_meta( $user_id, 'enrolled_course_class_' . $course_id );
		delete_user_meta( $user_id, 'enrolled_course_group_' . $course_id );
	}

	public static function unassign_from_all_courses( $user ) {
		$user_id = self::_get_id( $user );
		$courses = self::get_assigned_courses_ids( $user_id );
		foreach ( $courses as $course_id ) {
			self::unassign_from_course( $user_id, $course_id );
		}
	}

	// Get number of instructor's assigned courses
	public static function get_courses_number( $user ) {
		return count( self::get_course_meta_keys( $user ) );
	}

	public static function is_assigned_to_course( $instructor_id, $course_id ) {
		$instructor_course_id = get_user_option( 'course_' . $course_id, $instructor_id );
		if ( ! empty( $instructor_course_id ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function remove_instructor_status( $user ) {
		$user_id = self::_get_id( $user );
		$global_option = ! is_multisite();
		delete_user_option( $user_id, 'role_ins', 'instructor', $global_option );

		// Legacy
		delete_user_meta( $user_id, 'role_ins', 'instructor' );
		self::unassign_from_all_courses( $user_id );
		// CoursePress::instance()->drop_instructor_capabilities( $user_id );
	}

	public static function delete_instructor( $user, $delete_user = true ) {
		self::remove_instructor_status( $user );
	}

	public static function instructor_by_hash( $hash ) {
		global $wpdb;

		// Check cache first!
		$user_id = wp_cache_get( $hash, 'coursepress_userhash' );

		if ( is_multisite() ) {
			$hash = $wpdb->prefix . $hash;
		}

		// Not in cache, so retrieve
		if ( empty( $user_id ) ) {
			$sql = $wpdb->prepare( 'SELECT user_id FROM ' . $wpdb->prefix . 'usermeta WHERE meta_key = %s', $hash );
			$user_id = $wpdb->get_var( $sql );
			wp_cache_add( $hash, $user_id, 'coursepress_userhash' );
		}

		if ( ! empty( $user_id ) ) {
			return get_userdata( $user_id );
		} else {
			return false;
		}
	}

	public static function instructor_by_login( $login ) {
		$user = get_user_by( 'login', $login );
		if ( ! empty( $user ) ) {
			return $user;
		} else {
			return false;
		}
	}

	public static function create_hash( $user ) {
		$user_id = self::_get_id( $user );
		$user = get_userdata( $user_id );
		$hash = md5( $user->user_login );
		$global_option = ! is_multisite();
		/*
		 * Just in case someone is actually using this hash for something,
		 * we'll populate it with current value. Will be an empty array if
		 * nothing exists. We're only interested in the key anyway.
		 */
		update_user_option( $user_id, $hash, get_user_option( $hash, $user_id ), $global_option );

		// Put it in cache
		wp_cache_add( $hash, $user_id, 'coursepress_userhash' );
	}

	public static function get_hash( $user ) {
		$user_id = self::_get_id( $user );
		$user = get_userdata( $user_id );
		$hash = md5( $user->user_login );
		$global_option = ! is_multisite();

		$option = get_user_option( $hash, $user_id );

		return null !== $option ? $hash : false;
	}


	public static function added_to_course( $instructor_id, $course_id ) {

		$global_option = ! is_multisite();
		update_user_option( $instructor_id, 'course_' . $course_id, $course_id, $global_option );

	}

	public static function removed_from_course( $instructor_id, $course_id ) {

		$global_option = ! is_multisite();
		// CoursePress_Helper_Utility::delete_user_meta_by_key( 'course_' . $course_id );
		delete_user_option( $instructor_id, 'course_' . $course_id, $global_option );

		// Other associated actions
		self::unassign_from_course( $instructor_id, $course_id );

	}

	public static function delete_invitation( $course_id, $invite_code ) {
		$instructor_invites = get_post_meta(
			$course_id,
			'instructor_invites',
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
			'instructor_invites',
			$instructor_invites
		);
	}

	public static function send_invitation( $email_data ) {
		$email_data['course_id'] = (int) $email_data['course_id'];

		// So that we can use it later.
		CoursePress_Data_Course::set_last_course_id( $email_data['course_id'] );

		// We need to hook the email fields for the Utility method.
		self::_add_email_hooks();

		// Return data: Can be used by caller to get extra information
		$return_data = array();

		$email_args['course_id'] = $email_data['course_id'];
		$email_args['email'] = sanitize_email( $email_data['email'] );

		$user = get_user_by( 'email', $email_args['email'] );
		if ( $user ) {
			$email_data['user'] = $user;
		}

		$email_args['first_name'] = sanitize_text_field( $email_data['first_name'] );
		$email_args['last_name'] = sanitize_text_field( $email_data['last_name'] );

		$invite_data = self::_create_invite_code_hash( $email_args );
		$email_args['invite_code'] = $invite_data['code'];
		$email_args['invite_hash'] = $invite_data['hash'];

		// Get invites
		$instructor_invites = get_post_meta(
			$email_data['course_id'],
			'instructor_invites',
			true
		);

		// Create Course invites if they don't exist, and check to see if this invite is already there.
		$invite_exists = false;
		$invite_code = '';
		if ( $instructor_invites ) {
			foreach ( $instructor_invites as $key => $i ) {
				$invite_exists = array_search( $email_args['email'], $i );
				if ( $invite_exists ) {
					// Update code and hash for re-send.
					$email_args['invite_code'] = $i['code'];
					$email_args['invite_hash'] = $i['hash'];
				}
			}
		} else {
			$instructor_invites = array();
		}

		// Fire off the email, data altered in the hooks below.
		$sent = CoursePress_Helper_Email::send_email(
			CoursePress_Helper_Email::INSTRUCTOR_INVITATION,
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

				$instructor_invites[ $email_args['invite_code'] ] = $invite;

				update_post_meta(
					$email_data['course_id'],
					'instructor_invites',
					$instructor_invites
				);

				// Invite sent and added.
				$return_data['success'] = true;
				$return_data['invite_code'] = $email_args['invite_code'];
				CoursePress_Helper_Utility::set_array_val(
					$return_data,
					'message/sent',
					__( 'Invitation successfully sent.', 'CP_TD' )
				);

			} else {
				// Invite already exists.
				$return_data['success'] = true;
				$return_data['invite_code'] = $email_args['invite_code'];
				CoursePress_Helper_Utility::set_array_val(
					$return_data,
					'message/exists',
					__( 'Invitation already exists. Invitation was re-sent.', 'CP_TD' )
				);
			}
		} else {
			// Email not sent.
			$return_data['success'] = false;
			CoursePress_Helper_Utility::set_array_val(
				$return_data,
				'message/send_error',
				__( 'Email failed to send.', 'CP_TD' )
			);
		};

		return $return_data;
	}

	private static function _create_invite_code_hash( $args ) {
		// Generate invite code.
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$invite_code = '';
		for ( $i = 0; $i < 20; $i ++ ) {
			$invite_code .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return array(
			'code' => $invite_code,
			'hash' => sha1( sanitize_email( $args['email'] ) . $invite_code ),
		);
	}

	private static function _add_email_hooks() {
		add_filter( 'coursepress_email_fields', array( __CLASS__, 'email_fields' ), 10, 2 );
		add_filter( 'wp_mail_from', array( __CLASS__, 'email_from' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'email_from_name' ) );
	}

	public static function email_fields( $fields, $args ) {
		$email_settings = CoursePress_Helper_Email::get_email_fields(
			CoursePress_Helper_Email::INSTRUCTOR_INVITATION
		);

		$course_id = (int) $args['course_id'];

		// To Email Address
		$fields['email'] = sanitize_email( $args['email'] );

		// Email Subject
		$fields['subject'] = $email_settings['subject'];

		// For unpublished courses.
		$post = get_post( $course_id );

		$course_name = $post->post_title;
		$course_summary = $post->post_excerpt;

		$permalink = '';
		if ( in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) ) ) {
			$permalink = CoursePress_Core::get_slug( 'course/', true ) . $post->post_name . '/';
		} else {
			$permalink = get_permalink( $course_id );
		}
		$course_address = esc_url( $permalink );
		$confirm_link = esc_url( $course_address . '?action=course_invite&course_id=' . $course_id . '&c=' . $args['invite_code'] . '&h=' . $args['invite_hash'] );

		// Email Content
		$tags = array(
			'INSTRUCTOR_FIRST_NAME',
			'INSTRUCTOR_LAST_NAME',
			'INSTRUCTOR_EMAIL',
			'CONFIRMATION_LINK',
			'COURSE_NAME',
			'COURSE_EXCERPT',
			'COURSE_ADDRESS',
			'WEBSITE_ADDRESS',
			'WEBSITE_NAME',
		);

		$tags_replaces = array(
			sanitize_text_field( $args['first_name'] ),
			sanitize_text_field( $args['last_name'] ),
			$fields['email'],
			$confirm_link,
			$course_name,
			$course_summary,
			$course_address,
			home_url(),
			get_bloginfo(),
		);

		$fields['message'] = str_replace(
			$tags,
			$tags_replaces,
			$email_settings['content']
		);

		return $fields;
	}

	public static function email_from( $from ) {
		$email_settings = CoursePress_Helper_Email::get_email_fields(
			CoursePress_Helper_Email::INSTRUCTOR_INVITATION
		);

		$from = $email_settings['email'];

		return $from;
	}

	public static function email_from_name( $from_name ) {
		$email_settings = CoursePress_Helper_Email::get_email_fields(
			CoursePress_Helper_Email::INSTRUCTOR_INVITATION
		);

		$from = $email_settings['name'];

		return $from;
	}

	public static function verify_invitation_code( $course_id, $code, $invitation_data ) {
		// Not done yet.
	}


	public static function add_from_invitation( $course_id, $instructor_data ) {
		// Not done yet.
	}
}
