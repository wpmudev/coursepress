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

		global $wpdb;

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
				'post_type' => 'course',
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
}
