<?php

class CoursePress_Model_Instructor {

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
		$meta = array_filter( array_keys( $meta ), array( get_class(), 'filter_course_meta_array' ) );

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
				if ( $status !== 'all' ) {
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

	public static function get_accessable_courses( $user ) {

		$user_id          = self::_get_id( $user );
		$courses          = self::get_assigned_courses_ids( $user_id );
		$new_course_array = array();

		foreach ( $courses as $course ) {

			$can_update    = CoursePress_Capabilities::can_update_course( $course, $user_id );
			$can_delete    = CoursePress_Capabilities::can_delete_course( $course, $user_id );
			$can_publish   = CoursePress_Capabilities::can_change_course_status( $course, $user_id );
			$can_view_unit = CoursePress_Capabilities::can_view_course_units( $course, $user_id );
			$my_course     = CoursePress_Capabilities::is_course_instructor( $course, $user_id );
			$creator       = CoursePress_Capabilities::is_course_creator( $course, $user_id );

			if ( ! $my_course && ! $creator && ! $can_update && ! $can_delete && ! $can_publish && ! $can_view_unit ) {
				continue;
			} else {
				$new_course_array[] = $course;
			}
		}

		return $new_course_array;
	}

	public static function unassign_from_course( $user, $course_id = 0 ) {
		$user_id       = self::_get_id( $user );
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

	//Get number of instructor's assigned courses
	public static function get_courses_number( $user ) {
		return count( self::get_course_meta_keys( $user ) );
	}

	public static function is_assigned_to_course( $course_id, $instructor_id ) {
		$instructor_course_id = get_user_option( 'course_' . $course_id, $instructor_id );
		if ( ! empty( $instructor_course_id ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function remove_instructor_status( $user ) {
		$user_id       = self::_get_id( $user );
		$global_option = ! is_multisite();
		delete_user_option( $user_id, 'role_ins', 'instructor', $global_option );

		// Legacy
		delete_user_meta( $user_id, 'role_ins', 'instructor' );
		self::unassign_from_all_courses( $user_id );
		//CoursePress::instance()->drop_instructor_capabilities( $user_id );
	}

	public static function delete_instructor( $user, $delete_user = true ) {
		self::remove_instructor_status( $user );
	}

	public static function instructor_by_hash( $hash ) {
		global $wpdb;

		// Check cache first!
		$user_id = wp_cache_get( $hash, 'coursepress_userhash' );

		// Not in cache, so retrieve
		if( empty( $user_id ) ) {
			$sql     = $wpdb->prepare( "SELECT user_id FROM " . $wpdb->prefix . "usermeta WHERE meta_key = %s", $hash );
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
		$user_id       = self::_get_id( $user );
		$user          = get_userdata( $user_id );
		$hash          = md5( $user->user_login );
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


}
