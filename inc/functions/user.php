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
	if ( is_multisite() ) {
        $key = $wpdb->prefix . $key;
    }

	return get_user_option( $key, $user_id );
}

/**
 * Returns an instance of CoursePress_User object on success or null.
 *
 * @param int $user_id  Optional. If omitted, will use current user.
 *
 * @return CoursePress_User|int|WP_Error
 */
function coursepress_get_user( $user_id = 0 ) {
	global $CoursePress_User, $CoursePress_Core;

	if ( empty( $user_id ) ) {
		// Assume current user
		$user_id = get_current_user_id();
	}

	if ( $CoursePress_User instanceof  CoursePress_User
		&& $user_id == $CoursePress_User->__get( 'ID' ) )
			return $CoursePress_User;

	if ( isset( $CoursePress_Core->users[ $user_id ] ) )
		return $CoursePress_Core->users[ $user_id ];

	$user = new CoursePress_User( $user_id );

	if ( is_wp_error( $user ) )
		return $user->wp_error();

	$CoursePress_Core->users[ $user_id ] = $user;

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
function coursepress_add_course_instructor( $user_id = 0, $course_id = 0 ) {
	// Do not allow empty params!!!
	if ( empty( $user_id ) || empty( $course_id ) )
		return false;

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return false;

	if ( $user->is_instructor_at( $course_id ) )
		return true; // User is already an instructor of the course

	// Include user as instructor to the course
	update_post_meta( $course_id, 'instructor', $user_id, $user_id );

	// Marked user as instructor
	update_user_option( $user_id, 'course_' . $course_id, $course_id, is_multisite() );

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
 * @return bool
 */
function coursepress_delete_course_instructor( $user_id = 0, $course_id = 0 ) {
	if ( empty( $user_id ) || empty( $course_id ) )
		return false; // No empty params!

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return false;

	// Remove marker
	delete_post_meta( $course_id, 'instructor', $user_id );

	// Remove user marker
	delete_user_option( $user_id, 'course_' . $course_id, is_multisite() );

	/**
	 * Trigger whenever an instructor is removed from the course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 */
	do_action( 'coursepress_delete_instructor', $user_id, $course_id );

	return true;
}

/**
 * Get courses where user is an instructor at.
 *
 * @param int $user_id
 * @param bool $published
 * @param bool $returnAll
 *
 * @return array|null
 */
function coursepress_get_user_instructed_courses( $user_id = 0, $published = true, $returnAll = true ) {
	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return null;

	if ( $user->is_instructor() )
		return null; // User is not an instructor, bail!

	return $user->get_instructed_courses( $published, $returnAll );
}

/**
 * Returns user instructor profile link if user is an instructor of any course, otherwise return's false.
 *
 * @param int $user_id
 *
 * @return null|string
 */
function coursepress_get_user_instructor_profile_url( $user_id = 0 ) {
	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return null;

	return $user->get_instructor_profile_link();
}

/**
 * Add user as student to a course.
 *
 * @param int $user_id
 * @param int $course_id
 *
 * @return bool|null
 */
function coursepress_add_student( $user_id = 0, $course_id = 0 ) {
	if ( empty( $user_id ) || empty( $course_id ) )
		return null;

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return false;

	if ( $user->is_enrolled_at( $course_id ) )
		return true; // User is already enrolled, bail!

	$user->add_course_student( $course_id );

	// Marked user as student of the course
	//add_post_meta( $course_id, 'student', $user_id );

	//$time = current_time( 'timestamp' );
	//$is_multisite = is_multisite();

	//update_user_option( $user_id, 'enrolled_course_date_' . $course_id, $time, $is_multisite );
	//update_user_option( $user_id, 'enrolled_course_class_' . $course_id, $course_id, $is_multisite );

	/**
	 * Fired whenever a new student is added to a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 */
	do_action( 'coursepress_add_student', $user_id, $course_id );

	return true;
}

/**
 * Remove user as student from a course.
 *
 * @since 3.0
 * @param int $user_id
 * @param int $course_id
 * @return void
 */
function coursepress_delete_student( $user_id = 0, $course_id = 0 ) {
	if ( empty( $user_id ) || empty( $course_id ) )
		return null; // Don't allow empty param

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return null;

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	if ( ! $user->is_enrolled_at( $course_id ) )
		return null; // User is not enrolled? bail!

	$user->remove_course_student( $course_id );
	// Add user as student to the course
	//delete_post_meta( $course_id, 'student', $user_id );

	// Now delete user options
	//delete_user_option( $user_id, 'enrolled_course_date_' . $course_id, is_multisite() );
	//delete_user_option( $user_id, 'enrolled_course_class_' . $course_id, is_multisite() );

	/**
	 * Fired whenever an student is removed from a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $course_id
	 */
	do_action( 'coursepress_delete_student', $user_id, $course_id );
}

/**
 * Get courses where user is enrolled at.
 *
 * @param int $user_id
 * @param bool $published
 * @param bool $returnAll
 *
 * @return array|bool|null
 */
function coursepress_get_enrolled_courses( $user_id = 0, $published = true, $returnAll = true ) {
	if ( empty( $user_id ) )
		return false;

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	if ( $user->is_student() )
		return false; // Not a student of any course? bail!


	if ( empty( $user_id ) )
		return null;

	return $user->get_user_enrolled_at( $published, $returnAll );
}

/**
 * Add user as facilitator to a course.
 *
 * @param int $user_id
 * @param int $course_id
 *
 * @return bool
 */
function coursepress_add_course_facilitator( $user_id = 0, $course_id = 0 ) {
	if ( empty( $user_id ) || empty( $course_id ) )
		return false;

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return false;

	// Check if user is already a facilitator of the course
	if ( $user->is_facilitator_at( $course_id ) )
		return true;

	// Include user as facilitator to the course
	update_post_meta( $course_id, 'facilitator', $user_id, $user_id );

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

/**
 * Remove user as course facilitator.
 *
 * @param int $user_id
 * @param int $course_id
 *
 * @return bool|null
 */
function coursepress_remove_course_facilitator( $user_id = 0, $course_id = 0 ) {
	if ( empty( $user_id ) || empty( $course_id ) )
		return null; // No empty params!

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return false;

	if ( ! $user->is_facilitator_at( $course_id ) )
		return false; // Not a facilitator? bail!

	// Remove marker
	delete_post_meta( $course_id, 'facilitator', $user_id );

	/**
	 * Fire whenever user is remove as facilitator from a course.
	 *
	 * @since 3.0
	 * @param int $user_id
	 * @param int $coures_id
	 */
	do_action( 'coursepress_remove_facilitator', $user_id, $course_id );

	return true;
}

/**
 * Get courses where user is a facilitator.
 *
 * @param int $user_id
 * @param bool $published
 * @param bool $returnAll
 *
 * @return array|bool
 */
function coursepress_get_user_facilitated_courses( $user_id = 0, $published = true, $returnAll = false ) {
	if ( empty( $user_id ) )
		return false;

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return false;

	if ( ! $user->is_facilitator() )
		return false; // User is not a facilitator? bail!

	return $user->get_facilitated_courses( $published, $returnAll );
}

/**
 * Returns list of courses where user have access at.
 * User must be either instructor or administrator to a course to get an access.
 *
 * @param bool $publish
 * @param bool $returnAll
 *
 * @return array|null
 */
function coursepress_get_accessible_courses( $returnAll = true ) {
	$user = coursepress_get_user();

	if ( is_wp_error( $user ) )
		return null;

	return $user->get_accessible_courses( false, $returnAll );
}

/**
 * Get student's course completion result data.
 *
 * @param int $user_id
 * @param int $course_id
 *
 * @return array|CoursePress_Course|CoursePress_User|int|WP_Error
 */
function coursepress_get_user_course_completion_data( $user_id = 0, $course_id = 0 ) {
	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) )
		return $user; // Let's return the error

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return $course;

	$status = $user->get_course_completion_status( $course_id );
	$results = array( 'status' => $status );

	if ( 'pass' == $status ) {
		$results['title'] = $course->__get( 'course_completion_title' );
		$results['content'] = $course->__get( 'course_completion_content' );
	} elseif ( 'failed' == $status ) {
		$results['title'] = $course->__get( 'course_failed_title' );
		$results['content'] = $course->__get( 'course_failed_content' );
	} elseif ( 'completed'  == $status ) {
		$results['title'] = $course->__get( 'pre_completion_title' );
		$results['content'] = $course->__get( 'pre_completion_content' );
	} elseif ( 'incomplete' == $status ) {
		$results['title'] = __( 'Oooops! Course incomplete!', 'cp' );
		$results['content'] = __( 'Looks like you failed to complete this course at the given period.', 'cp' );
	} else {
		// The course is still on going
		$results['title'] = __( 'Course is still on going!', 'cp' );
		$results['content'] = __( 'You haven\'t completed this course.', 'cp' );
	}

	return $results;
}

/**
 * Get users list excluding current instructors/facilitators.
 *
 * @param int $course_id Course ID.
 * @param string $type instructor/facilitator
 * @param string $search Search term.
 *
 * @return array
 */
function coursepress_get_available_users( $course_id, $type = 'instructor', $search = '' ) {

	// Do not continue if required values are empty.
	if ( empty( $course_id ) || ! in_array( $type, array( 'instructor', 'facilitator' ) ) ) {
		return array();
	}

	$args = array(
		// Do not include already assigned users.
		'meta_query'     => array(
			array(
				'relation' => 'AND',
				array(
					'key'     => $type . '_' . $course_id,
					'compare' => "NOT EXISTS",
				)
			)
		),
		// Search user fields.
		'search'         => '*' . $search . '*',
		'search_columns' => array( 'user_login', 'user_nicename', 'user_email' ),
		'fields'         => array( 'ID', 'user_login' ),
	);

	return get_users( $args );
}

/**
 * Returns list courses.
 *
 * @param array $args  Arguments to pass to WP_User_Query.
 * @param int   $count This is not the count of resulted students. This is the count
 *                     of total available students without applying pagination limit.
 *                     This parameter does not expect incoming value. Total count will
 *                     be passed as reference, as this function's return value is an
 *                     array of user objects.
 *
 * @return array Returns an array of students where each student is an instance of CoursePress_User object.
 */
function coursepress_get_students( $args = array(), &$count = 0 ) {

	// Handle the search if search query found.
	if ( ! empty( $_GET[ 's' ] ) ) {
		$args['search'] = '*' . $_GET['s'] . '*';
	}

	// Get only the student roles.
	//$args['role'] = 'coursepress_student';

	$args = wp_parse_args( array(
		'suppress_filters' => true,
		'fields' => 'ids',
	), $args );

	/**
	 * Filter students WP_User_Query arguments.
	 *
	 * @since 3.0
	 * @param array $args
	 */
	$args = apply_filters( 'coursepress_pre_get_students', $args );

	$query = new WP_User_Query( $args );
	$results = $query->results;

	// Update the total students count (ignoring items per page).
	$count = $query->total_users;

	$students = array();

	// If result found, get the CoursePress_User objects.
	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			$students[ $result ] = coursepress_get_user( $result );
		}
	}

	return $students;
}

/**
 * Get list of students user IDs.
 *
 * @param int $course_id Course ID
 *
 * @return array
 */
function coursepress_get_students_ids( $course_id = 0, $page = 0, $per_page = 0 ) {

	global $wpdb;

	$students_table = $wpdb->prefix . 'coursepress_students';

	// If pagination is set.
	$limit = '';
	if ( ! empty( $per_page ) && ! empty( $page ) ) {
		$offset = ceil( $per_page * ( $page - 1 ) );
		$limit = ' LIMIT ' . $offset . ', ' . $per_page;
	}

	if ( ! empty( $course_id ) ) {
		// Make sure it is int.
		$course_id = absint( $course_id );
		// Get students of specific course.
		$sql = $wpdb->prepare( "SELECT student_id FROM `$students_table` WHERE `course_id`=%d GROUP BY student_id $limit", $course_id );
	} else {
		// Get all students.
		$sql = "SELECT student_id FROM `$students_table` GROUP BY student_id %s";
	}

	//die($sql);

	return $wpdb->get_col( $sql );
}