<?php
/**
 * CoursePress users functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */
if ( ! function_exists( 'coursepress_add_instructor' ) ) :
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
endif;

if ( ! function_exists( 'coursepress_delete_instructor' ) ) :
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
endif;

if ( ! function_exists( 'coursepress_get_instructor_courses' ) ) :
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
endif;

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

		$CoursePress_Data_Courses->add_course_meta( $course_id, 'student', $user_id );

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
