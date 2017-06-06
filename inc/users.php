<?php
/**
 * CoursePress users functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */

/**
 * Helper function to get user option.
 *
 * @param int $user_id
 * @param string $key
 *
 * @return mixed
 */
function coursepress_get_user_option( $user_id, $key ) {
	global $wpdb;

	// Prefix key if it's multisite
	if ( is_multisite() )
		$key = $wpdb->prefix . $key;

	return get_user_option( $key, $user_id );
}

/**
 * Get CoursePress user.
 * @param int $user_id
 *
 * @return CoursePress_User|int|WP_Error
 */
function coursepress_get_user( $user_id = 0 ) {
	global $CoursePress_User;

	if ( $user_id instanceof CoursePress_User )
		return $user_id;

	if ( $CoursePress_User instanceof CoursePress_User
		&& $user_id == $CoursePress_User->__get( 'ID' ) )
			return $CoursePress_User;

	$user = new CoursePress_User( $user_id );

	if ( $user->__get( 'is_error' ) )
		return $user->wp_error();

	// @todo: Save in global variable

	return $user;
}

/**
 * Add user as instructor to a course.
 *
 * @since 3.0
 * @param int $user_id
 * @param int $course_id
 * @return bool
 */
function coursepress_add_instructor( $user_id = 0, $course_id = 0 ) {
	if ( empty( $user_id ) || empty( $course_id ) )
		return false;

	update_post_meta( $course_id, 'instructor', $user_id, $user_id );

	/**
	 * Trigger whenever a new instructor is added to a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 */
	do_action( 'coursepress_add_instructor', $user_id, $course_id );

	return true;
}

/**
 * Remove user as instructor from a course.
 *
 * @since 3.0
 * @param int $user_id
 * @param int $course_id
 * @return void
 */
function coursepress_delete_instructor( $user_id = 0, $course_id = 0 ) {
	global $CoursePress_Data_Courses;

	if ( empty( $user_id ) || empty( $course_id ) )
		return false;

	$CoursePress_Data_Courses->delete_course_meta( $course_id, 'instructors', $user_id );

	/**
	 * Trigger whenever an instructor is removed from the course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 */
	do_action( 'coursepress_delete_instructor', $user_id, $course_id );
}

/**
 * Get courses where user is an instructor at.
 *
 * @param int $instructor_id
 * @param bool $publish Whether to get only published courses or all status type.
 * @param bool $ids Whether to return on the IDs or as post object.
 * @param bool $all Whether to return all courses from DB or not.
 *
 * @return null
 */
function coursepress_get_instructor_courses( $instructor_id = 0, $publish = true, $ids = true, $all = false ) {
	if ( empty( $instructor_id ) )
		return null;

	$args = array(
		'meta_key' => 'instructor',
		'meta_value' => $instructor_id,
		'post_status' => $publish ? 'publish' : 'any',
		'posts_per_page' => $all ? -1 : 20,
		'suppress_filters' => true,
	);

	if ( $ids )
		$args['fields'] = 'ids';

	return coursepress_get_courses( $args );
}

if ( ! function_exists( 'coursepress_get_instructor_profile_link' ) ) :
	function coursepress_get_instructor_profile_link( $instructor_id ) {
		$instructor = new CoursePress_User( $instructor_id );

		return ! $instructor->__get( 'is_error' ) ?
			$instructor->get_instructor_profile_link()
			: '';
	}
endif;

if ( ! function_exists( 'coursepress_add_student' ) ) :
	/**
	 * Add user as student to a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 * @return bool Returns true on success or false.
	 */
	function coursepress_add_student( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return null;

		add_post_meta( $course_id, 'student', $user_id );

		$time = current_time( 'timestamp' );
		$key = 'enrolled_course_date_' . $course_id;
		update_user_option( $user_id, $key, $time );

		/**
		 * Fire whenever a new student is added to a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_add_student', $user_id, $course_id );

		return true;
	}
endif;

if ( ! function_exists( 'coursepress_delete_student' ) ) :
	/**
	 * Remove user as student from a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 * @return void
	 */
	function coursepress_delete_student( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return null;

		$CoursePress_Data_Courses->delete_course_meta( $course_id, 'student', $user_id );

		/**
		 * Fire whenever an student is removed from a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_delete_student', $user_id, $course_id );
	}
endif;

if ( ! function_exists( 'coursepress_get_enrolled_courses' ) ) :
	/**
	 * Get the user's currently enrolled courses.
	 *
	 * @param int $user_id
	 * @param bool $publish
	 * @param bool $ids
	 * @param bool $all
	 *
	 * @return null|array Returns array of enrolled courses or null.
	 */
	function coursepress_get_enrolled_courses( $user_id, $publish = true, $ids = true, $all = false ) {
		if ( empty( $user_id ) )
			return null;

		$args = array(
			'meta_key' => 'student',
			'meta_value' => $user_id,
			'post_status' => $publish ? 'publish' : 'any',
			'posts_per_page' => $all ? -1 : 20,
			'suppress_filters' => true,
		);

		if ( $ids )
			$args['fields'] = 'ids';

		return coursepress_get_courses( $args );
	}
endif;

if ( ! function_exists( 'coursepress_add_facilitator' ) ) :
	/**
	 * Add user as facilitator to a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 * @return bool
	 */
	function coursepress_add_facilitator( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return false;

		$CoursePress_Data_Courses->add_course_meta( $course_id, 'facilitator', $user_id );

		/**
		 * Fire whenever a new facilitator is added to a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_add_facilitator', $user_id, $course_id );

		return true;
	}
endif;

if ( ! function_exists( 'coursepress_remove_facilitator' ) ) :
	/**
	 * Remove user as facilitator from a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 * @return void
	 */
	function coursepress_remove_facilitator( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return null;

		$CoursePress_Data_Courses->delete_course_meta( $course_id, 'facilitator', $user_id );

		/**
		 * Fire whenever user is remove as facilitator from a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $coures_id
		 */
		do_action( 'coursepress_remove_facilitator', $user_id, $course_id );
	}
endif;

if ( ! function_exists( 'coursepress_get_user_facilitated_courses' ) ) :
	/**
	 * Get the user facilitated courses.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param bool $publish
	 * @param bool $ids
	 * @param bool $all
	 *
	 * @return array
	 */
	function coursepress_get_user_facilitated_courses( $user_id, $publish = true, $ids = false, $all = false ) {
		$args = array(
			'meta_key' => 'facilitator',
			'meta_value' => $user_id,
			'post_status' => $publish ? 'publish' : 'any',
		);

		if ( $ids )
			$args['fields'] = 'ids';
		if ( $all )
			$args['posts_per_page'] = -1;

		return coursepress_get_courses( $args );
	}
endif;
