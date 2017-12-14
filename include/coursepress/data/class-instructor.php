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
		$user_id = self::_get_id( $user );

		if ( 0 < $user_id ) {
			$meta = get_user_meta( self::_get_id( $user ) );
			$meta = array_filter( array_keys( $meta ), array( __CLASS__, 'filter_course_meta_array' ) );

			return $meta;
		}

		return array();
	}

	/**
	 * Callback for array_filter() that will return the meta-key if it
	 * indicates an instructor-course-link.
	 *
	 * So this function only returns values if the associated user is an
	 * instructor.
	 *
	 * @since  2.0.0
	 */
	public static function filter_course_meta_array( $meta_key ) {
		global $wpdb;

		$regex = array();
		$regex[] = 'course_\d+';
		$regex[] = $wpdb->prefix . 'course_\d+';
		if ( is_multisite() && defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == get_current_blog_id() ) {
			$regex[] = $wpdb->base_prefix . 'course_\d+';
		}

		$pattern = sprintf( '/^(%s)$/', implode( '|', $regex ) );

		if ( preg_match( $pattern, $meta_key ) ) {
			return $meta_key;
		}
		return false;
	}

	public static function filter_by_where( $where ) {
		global $wpdb;

		$user_id = get_current_user_id();
		$post_type = CoursePress_Data_Course::get_post_type_name();

		$where .= $wpdb->prepare( " OR ({$wpdb->posts}.post_type='%s' AND {$wpdb->posts}.post_author=%d AND {$wpdb->posts}.post_status=%s)", $post_type, $user_id, 'publish' );

		// Let's remove the filter right away
		remove_filter( 'posts_where', array( __CLASS__, 'filter_by_where' ) );

		return $where;
	}

	public static function filter_by_whereall( $where ) {
		global $wpdb;

		$user_id = get_current_user_id();
		$post_type = CoursePress_Data_Course::get_post_type_name();

		$where .= $wpdb->prepare( " OR ({$wpdb->posts}.post_type='%s' AND {$wpdb->posts}.post_author=%d)", $post_type, $user_id );

		// Let's remove the filter right away
		remove_filter( 'posts_where', array( __CLASS__, 'filter_by_whereall' ) );

		return $where;
	}

	/**
	 * Return a list of courses of which the specified user is an instructor.
	 *
	 * @since  2.0.0
	 * @param  int|WP_User $user The instructor/user to check.
	 * @param  string      $status all|publish|draft.
	 * @return array List of course IDs.
	 */
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

		$course_ids = array();

		if ( ! empty( $assigned_courses ) ) {
			// Filter the course IDs, make sure courses exists and are not deleted
			$args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
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

	public static function get_accessable_courses( $user_id = '', $post_status = 'publish' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		} elseif ( is_object( $user_id ) ) {
			$user_id = $user_id->ID;
		}

		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $post_status,
			'posts_per_page' => -1,
		);

		if ( ! user_can( $user_id, 'manage_options' ) ) {
			$can_search = false;
			if ( user_can( $user_id, 'coursepress_update_my_course_cap' ) ) {
				$args['author'] = $user_id;
				$can_search = true;
			}
			if ( user_can( $user_id, 'coursepress_update_course_cap' ) ) {
				$assigned_courses = self::get_assigned_courses_ids( $user_id );
				$args['include'] = $assigned_courses;

				if ( $can_search ) {
					// Let's add the author param via filter hooked.
					unset( $args['author'] );
					add_filter( 'posts_where', array( __CLASS__, 'filter_by_where' ) );
				}
				$can_search = true;
			}

			if ( ! $can_search ) {
				// Bail early
				return array();
			}
		}

		$posts = get_posts( $args );

		// Filter posts
		if ( count( $posts ) > 0 ) {
			foreach ( $posts as $index => $post ) {
				$can_update = CoursePress_Data_Capabilities::can_update_course( $post->ID );

				if ( false === $can_update ) {
					unset( $posts[ $index ] );
				}
			}
		}

		return $posts;
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

	/**
	 * @todo This function is not used in CP 2.0 and is not finished
	 *       Need to remove instructor from all courses before removing the
	 *       role_ins meta value!
	 */
	public static function remove_instructor_status( $user ) {
		$user_id = self::_get_id( $user );
		$role_name = CoursePress_Data_Capabilities::get_role_instructor_name();
		delete_user_option( $user_id, $role_name, 'instructor' );
		// Legacy
		delete_user_meta( $user_id, $role_name, 'instructor' );
		self::unassign_from_all_courses( $user_id );
		CoursePress_Data_Capabilities::drop_instructor_capabilities( $user_id );
	}
	// */

	/**
	 * @todo This function is not used in CP 2.0 and does not look finished
	 *       $delete_user param is not used
	 */
	public static function delete_instructor( $user, $delete_user = true ) {
		self::remove_instructor_status( $user );
	}
	// */

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
		if ( empty( $user ) ) {
			return;
		}
		$hash = md5( $user->user_login );
		$global_option = ! is_multisite();
		/*
		 * Just in case someone is actually using this hash for something,
		 * we'll populate it with current value. Will be an empty array if
		 * nothing exists. We're only interested in the key anyway.
		 */
		update_user_option( $user_id, $hash, time(), $global_option );
		// Put it in cache
		wp_cache_add( $hash, $user_id, 'coursepress_userhash' );
	}

	public static function get_hash( $user ) {
		$user_id = self::_get_id( $user );
		$user = get_userdata( $user_id );
		$hash = md5( $user->user_login );
		$global_option = ! is_multisite();
		$option = get_user_option( $hash, $user_id );
		if ( empty( $option ) ) {
			self::create_hash( $user );
		}
		return null !== $option ? $hash : false;
	}

	public static function count_courses( $instructor_id, $refresh = false ) {
		$count = get_user_meta( $instructor_id, 'cp_instructor_course_count', true );

		if ( ! $count || $refresh ) {
			global $wpdb;
			/**
			 * multisite
			 */
			$prefix = is_multisite()? $wpdb->prefix:'';
			$query = $wpdb->prepare( "
					SELECT `meta_key`
					FROM $wpdb->usermeta
					WHERE `meta_key` LIKE '{$prefix}course_%%' AND `user_id`=%d",
				$instructor_id
			);
			$meta_keys = $wpdb->get_results( $query, ARRAY_A );
			if ( $meta_keys ) {
				$meta_keys = array_map(
					array( __CLASS__, 'meta_key' ),
					$meta_keys
				);

				$course_ids = array_map(
					array( 'CoursePress_Data_Instructor', 'filter_course_meta_array' ),
					$meta_keys
				);
				$course_ids = array_filter( $course_ids );
				$count = count( $course_ids );

				// Save counted courses.
				update_user_meta( $instructor_id, 'cp_instructor_course_count', $count );
			}
		}

		return $count;
	}

	public static function meta_key( $key ) {
		return $key['meta_key'];
	}

	public static function instructor_key( $meta_key ) {
		return ! preg_match( '/_progress$/', $meta_key );
	}

	public static function added_to_course( $instructor_id, $course_id ) {

		$instructor = get_userdata( $instructor_id );
		$assigned_courses_ids = self::get_assigned_courses_ids( $instructor );
		$assigned_courses_ids = array_filter( $assigned_courses_ids );

		if ( empty( $assigned_courses_ids ) ) {
			CoursePress_Data_Capabilities::assign_instructor_capabilities( $instructor );
		}

		$global_option = ! is_multisite();
		update_user_option( $instructor_id, 'course_' . $course_id, $course_id, $global_option );
		update_user_meta( $instructor_id, 'role_ins', 'instructor' );

		// Update course count
		self::count_courses( $instructor_id, true );
	}

	/**
	 * Remove Instructor from all courses
	 *
	 * @since 2.0.0
	 *
	 * @param integer $instructor_id Instructor ID
	 */
	public static function remove_from_all_courses( $instructor_id ) {
		$assigned_courses_ids = self::get_assigned_courses_ids( $instructor_id );
		if ( empty( $assigned_courses_ids ) ) {
			self::remove_instructor_status( $instructor_id );
			return;
		}
		foreach ( $assigned_courses_ids as $course_id ) {
			self::removed_from_course( $instructor_id, $course_id );
		}
	}

	public static function removed_from_course( $instructor_id, $course_id ) {

		$global_option = ! is_multisite();
		// CoursePress_Helper_Utility::delete_user_meta_by_key( 'course_' . $course_id );
		delete_user_option( $instructor_id, 'course_' . $course_id, $global_option );

		// Other associated actions
		// Unroll user from course only if he is not a student
		if ( ! CoursePress_Data_Student::is_enrolled_in_course( $instructor_id, $course_id ) ) {
			self::unassign_from_course( $instructor_id, $course_id );
		}

		$instructor = get_userdata( $instructor_id );
		$assigned_courses_ids = self::get_assigned_courses_ids( $instructor );
		$assigned_courses_ids = array_filter( $assigned_courses_ids );

		// Update course count
		self::count_courses( $instructor_id, true );

		/**
		 * Drop capabilities if no assigned courses found.
		 **/
		if ( empty( $assigned_courses_ids ) ) {
			CoursePress_Data_Capabilities::drop_instructor_capabilities( $instructor );
			delete_user_meta( $instructor_id, 'cp_instructor_course_count' );
		}

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

	public static function send_invitation( $course_id, $email, $first_name, $last_name ) {
		// So that we can use it later.
		CoursePress_Data_Course::set_last_course_id( $course_id );

		// Return data: Can be used by caller to get extra information
		$return_data = array();

		$email_args['course_id'] = $course_id;
		$email_args['email'] = $email;
		$email_args['first_name'] = $first_name;
		$email_args['last_name'] = $last_name;

		$invite_data = self::create_invite_code_hash( $email_args );
		$email_args['invite_code'] = $invite_data['code'];
		$email_args['invite_hash'] = $invite_data['hash'];

		// Get invites
		$instructor_invites = get_post_meta(
			$course_id,
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
					$course_id,
					'instructor_invites',
					$instructor_invites
				);

				// Invite sent and added.
				$return_data['success'] = true;
				$return_data['invite_code'] = $email_args['invite_code'];
				$return_data = CoursePress_Helper_Utility::set_array_value(
					$return_data,
					'message/sent',
					__( 'Invitation successfully sent.', 'CP_TD' )
				);

			} else {
				// Invite already exists.
				$return_data['success'] = true;
				$return_data['invite_code'] = $email_args['invite_code'];
				$return_data = CoursePress_Helper_Utility::set_array_value(
					$return_data,
					'message/exists',
					__( 'Invitation already exists. Invitation was re-sent.', 'CP_TD' )
				);
			}
		} else {
			// Email not sent.
			$return_data['success'] = false;
			$return_data = CoursePress_Helper_Utility::set_array_value(
				$return_data,
				'message/send_error',
				__( 'Email failed to send.', 'CP_TD' )
			);
		};

		return $return_data;
	}

	public static function create_invite_code_hash( $args ) {
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

	/**
	 * Check to see if the current page is the link sent from invitation email.
	 *
	 * @since 2.0
	 *
	 * @return (mixed)	 Returns an (object) on success and false if for error.
	 **/
	public static function is_course_invite() {
		if ( isset( $_GET['action'] ) && 'course_invite' == $_GET['action'] ) {
			$course_id = (int) $_GET['course_id'];
			$code = $_GET['c'];
			$hash = $_GET['h'];
			$invitation_data = (array) get_post_meta( $course_id, 'instructor_invites', true );

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
	 * Add invitation data object to $localize_array.
	 *
	 * @since 2.0
	 *
	 * @param (array)	 The previously set localize array.
	 **/
	public static function invitation_data( $localize_array ) {

		if ( $invitation_data = self::is_course_invite() ) {
			$invitation_data->invitation_data = $invitation_data->invitation_data[ $invitation_data->code ];
			$invitation_data->nonce = wp_create_nonce( 'coursepress_add_instructor' );
			$localize_array['invitation_data'] = $invitation_data;
		}

		return $localize_array;
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
		$invitation_data = ! $invitation_data ? (array) get_post_meta( $course_id, 'instructor_invites', true ) : (array) $invitation_data;
		$is_valid = in_array( $code, array_keys( $invitation_data ) );

		return $is_valid ? $invitation_data[ $code ] : false;
	}

	public static function add_from_invitation( $course_id, $instructor_id, $invitation_code ) {
		$invite_data = self::verify_invitation_code( $course_id, $invitation_code );
		$userdata = get_userdata( $instructor_id );

		if ( ! empty( $invite_data['email'] ) && $invite_data['email'] == $userdata->user_email ) {
			CoursePress_Data_Course::add_instructor( $course_id, $instructor_id );
			CoursePress_Data_Capabilities::assign_instructor_capabilities( $userdata );
			CoursePress_Data_Instructor::delete_invitation( $course_id, $invite_data['code'] );

			/**
			 * Instructor invite confirmed.
			 *
			 * @since 1.2.1
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
		 * @since 1.2.1
		 *
		 * @param int course_id The course instructor was added to.
		 * @param int instructor_id The user ID of instructor assigned.
		 *
		 */
		do_action( 'coursepress_instructor_invite_confirm_fail', $course_id, $instructor_id );

		return false;
	}

	/**
	 * Get total number of students of a given instructor ID.
	 *
	 * @since 2.0
	 *
	 * @param (int) $instructor_id	WP_User ID.
	 * @return (int) $count			Total number of students.
	 **/
	public static function get_students_count( $instructor_id, $refresh = false ) {
		if ( empty( $instructor_id ) ) {
			return 0; // Bail if no ID
		}
		$count = get_user_meta( $instructor_id, 'coursepress_followers_count', true );
		if ( false === $count || $refresh ) {
			// Not set yet, let's get the total student count
			$count = self::_get_students_count( $instructor_id );
			// Update usermeta setting
			update_user_meta( $instructor_id, 'coursepress_followers_count', $count );
		}
		if ( empty( $count ) ) {
			return 0;
		}
		return $count;
	}

	public static function _get_students_count( $instructor_id ) {
		global $wpdb;

		$course_ids = self::get_assigned_courses_ids( (int) $instructor_id, 'publish' );
		$keys = array();
		$students = array();

		foreach ( $course_ids as $course_id ) {
			$key = 'enrolled_course_date_' . $course_id;

			if ( is_multisite() ) {
				$key = $wpdb->prefix . $key;
			}
			$keys[] = "'{$key}'";
		}

		// We use custom SQL to avoid over capacity.
		$custom_sql = $wpdb->prepare( "SELECT DISTINCT(user_id) FROM $wpdb->usermeta as m WHERE m.meta_key IN (%s)", implode( ',', $keys ) );
		$results = $wpdb->get_results( $custom_sql );

		return count( $results );
	}

	/**
	 * Reset instructors students count.
	 **/
	public static function reset_students_count( $instructors ) {
		if ( is_array( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				$instructor_id = is_object( $instructor ) ? $instructor->ID : $instructor;
				self::get_students_count( $instructor_id, true );
			}
		}
	}

	/**
	 * Build nonce action string
	 *
	 * @since 2.0.0
	 *
	 * @param string $action Nonce action.
	 * @param integer $instructor_id Instructor ID - default 0;
	 * @return string Nonce action.
	 */
	public static function get_nonce_action( $action, $instructor_id = 0 ) {
		$user_id = get_current_user_id();
		return sprintf( '%s_%s_%d_%d', __CLASS__, $action, $user_id, $instructor_id );
	}

	/**
	 * Check is user instructor?
	 *
	 * @since 2.1.1
	 *
	 * @param integer instructor_id The user ID to check
	 * @param integer course_id The course ID.
	 */
	public static function is_course_instructor( $instructor_id, $course_id ) {
		$instructors = CoursePress_Data_Course::get_setting( $course_id, 'instructors', array() );
		return in_array( $instructor_id, $instructors );
	}
}
