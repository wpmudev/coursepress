<?php
/**
 * Facilitator Class
 *
 * Controls course facilitator
 *
 * @since 2.0
 **/
class CoursePress_Data_Facilitator {
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
				$facilitators[$user_id] = get_userdata( $user_id );
				unset( $facilitators[$pos] );
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
	public function is_course_facilitator( $course_id, $user_id = 0 ) {
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
			$can_update_course = CoursePress_Data_Capabilities::is_course_instructor( $course_id, $user_id );

			if ( $can_update_course ) {
				// Because both facilitator and instructor share the same capabilities, only remove the role
				$global_option = ! is_multisite();
				delete_user_option( $user_id, 'cp_role', $global_option );
			} else {
				CoursePress_Data_Capabilities::drop_facilitator_capabilities( $user_id );
			}
		}

		do_action( 'coursepress_facilitator_removed', $course_id, $user_id );
	}

	public static function get_facilitated_courses( $user_id = 0, $status = array( 'pubish' ), $ids_only = false, $page = 0, $per_page = 20 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $status,
			'meta_key' => 'course_facilitator',
			'meta_value' => $user_id,
			'meta_compare' => 'IN',
			'paged' => $page,
			'posts_per_page' => $per_page,
			'suppress_filters' => true,
		);

		if ( $ids_only ) {
			$args['fields'] = 'ids';
		}

		$courses = get_posts( $args );

		return $courses;
	}

}