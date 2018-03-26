<?php

class CoursePress_Data_Instructor {

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
		if ( is_multisite() && defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == get_current_blog_id() ) {
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

		global $wpdb, $CoursePress_Core;

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
				'post_type' => $CoursePress_Core->course_post_type,
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
		global $CoursePress_Core;
		// Filter the course IDs, make sure courses exists and are not deleted
		$args = array(
			'post_type' => $CoursePress_Core->course_post_type,
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
