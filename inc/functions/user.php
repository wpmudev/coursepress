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
		&& $user_id == $CoursePress_User->__get( 'ID' ) ) {
		return $CoursePress_User;
	}
	if ( isset( $CoursePress_Core->users[ $user_id ] ) ) {
		return $CoursePress_Core->users[ $user_id ];
	}
	$user = new CoursePress_User( $user_id );
	if ( is_wp_error( $user ) ) {
		return $user->wp_error();
	}
	$CoursePress_Core->users[ $user_id ] = $user;
	return $user;
}

/**
 * Get user id of user.
 *
 * This function validates if given object is
 * actual user object, or if int, it will check
 * if it is actual user id. If not arg passed,
 * it will return current user id.
 *
 * @param int|object User object or user id.
 *
 * @return bool|null
 */
function coursepress_get_user_id( $user = 0 ) {

	$user = coursepress_get_user( $user );

	if ( ! is_wp_error( $user ) ) {
		return $user->__get( 'ID' );
	}

	return false;
}

function coursepress_user_meta_prefix_required() {
	return is_multisite() && ! is_main_site();
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
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return false;
	}
	$user = coursepress_get_user( $user_id );
	if ( is_wp_error( $user ) ) {
		return false;
	}
	$course = coursepress_get_course( $course_id );
	if ( is_wp_error( $course ) ) {
		return false;
	}
	if ( $user->is_instructor_at( $course_id ) ) {
		return true; // User is already an instructor of the course
	}
	// Include user as instructor to the course
	add_post_meta( $course_id, 'instructor', $user_id );
	/**
	 * Add user to MU
	 */
	coursepress_add_user_to_blog( $user_id, 'instructor' );
	// Marked user as instructor
	$add_site_prefix = coursepress_user_meta_prefix_required();
	update_user_option( $user_id, 'course_' . $course_id, $course_id, ! $add_site_prefix );
	update_user_option( $user_id, 'role_ins', 'instructor', ! $add_site_prefix );
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
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return false; // No empty params!
	}
	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) ) {
		return false;
	}

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) ) {
		return false;
	}

	// Remove marker
	delete_post_meta( $course_id, 'instructor', $user_id );

	$add_site_prefix = coursepress_user_meta_prefix_required();
	// Remove user marker
	delete_user_option( $user_id, 'course_' . $course_id, ! $add_site_prefix );

	// Reduce instructor count.
	$count = get_user_meta( $user_id, 'cp_instructor_course_count', true );
	if ( ! empty( $count ) ) {
		update_user_meta( $user_id, 'cp_instructor_course_count', $count - 1 );
	}

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

	if ( is_wp_error( $user ) ) {
		return null;
	}

	if ( ! $user->is_instructor() ) {
		return null; // User is not an instructor, bail!
	}

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

	if ( is_wp_error( $user ) ) {
		return null;
	}

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
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return null;
	}

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) ) {
		return false;
	}

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) ) {
		return false;
	}

	if ( $user->is_enrolled_at( $course_id ) ) {
		return new WP_Error( 'already_enrolled', __( 'User can not be added. User is already enrolled.', 'cp' ) );
	}

	$user->add_course_student( $course_id );

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
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return null; // Don't allow empty param
	}
	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) ) {
		return null;
	}

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) ) {
		return null;
	}

	if ( ! $user->is_enrolled_at( $course_id ) ) {
		return null; // User is not enrolled? bail!
	}
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
	if ( empty( $user_id ) ) {
		return false;
	}

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) ) {
		return false;
	}

	if ( $user->is_student() ) {
		return false; // Not a student of any course? bail!
	}
	if ( empty( $user_id ) ) {
		return null;
	}

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
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return false;
	}
	$user = coursepress_get_user( $user_id );
	if ( is_wp_error( $user ) ) {
		return false;
	}
	$course = coursepress_get_course( $course_id );
	if ( is_wp_error( $course ) ) {
		return false;
	}
	// Check if user is already a facilitator of the course
	if ( $user->is_facilitator_at( $course_id ) ) {
		return true;
	}
	// Include user as facilitator to the course
	update_post_meta( $course_id, 'facilitator', $user_id, $user_id );
	/**
	 * Add user to MU
	 */
	coursepress_add_user_to_blog( $user_id, 'facilitator' );
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
	if ( empty( $user_id ) || empty( $course_id ) ) {
		return null; // No empty params!
	}
	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) ) {
		return false;
	}

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) ) {
		return false;
	}

	if ( ! $user->is_facilitator_at( $course_id ) ) {
		return false; // Not a facilitator? bail!
	}
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
	if ( empty( $user_id ) ) {
		return false;
	}

	$user = coursepress_get_user( $user_id );

	if ( is_wp_error( $user ) ) {
		return false;
	}

	if ( ! $user->is_facilitator() ) {
		return false; // User is not a facilitator? bail!
	}
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

	if ( is_wp_error( $user ) ) {
		return null;
	}

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

	if ( is_wp_error( $user ) ) {
		return $user; // Let's return the error
	}

	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) ) {
		return $course;
	}

	$status = $user->get_course_completion_status( $course_id );
	$results = array( 'status' => $status );

	if ( 'pass' == $status ) {
		$results['title'] = $course->__get( 'course_completion_title' );
		$results['content'] = $course->__get( 'course_completion_content' );
	} elseif ( 'failed' == $status ) {
		$results['title'] = $course->__get( 'course_failed_title' );
		$results['content'] = $course->__get( 'course_failed_content' );
	} elseif ( 'completed' == $status ) {
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
	/**
	 * If content EXISTS filter it
	 */
	if ( isset( $results['content'] ) ) {
		/**
		 * Filter allow to replace placeholders.
		 *
		 * @since 3.0.0
		 *
		 * @param string $results['content'] Content of the message, it can contain placeholders.
		 * @param integer $course_id Course ID.
		 * @param integer $user_id User ID.
		 *
		 */
		$results['content'] = apply_filters( 'coursepress_replace_placeholders', $results['content'], $course_id, $user_id );
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
function coursepress_get_available_users( $course_id = 0, $type = '', $search = '' ) {

	$args = array();

	// Do not include already assigned users.
	if ( ! empty( $course_id ) && ! empty( $type ) && in_array( $type, array( 'instructor', 'facilitator' ) ) ) {
		$args['meta_query'] = array(
			array(
				'relation' => 'AND',
				array(
					'key'     => $type . '_' . $course_id,
					'compare' => 'NOT EXISTS',
				)
			),
		);
	}

	// Search user fields.
	if ( ! empty( $search ) ) {
		$args['search'] = '*' . $search . '*';
		$args['search_columns'] = array( 'user_login', 'user_nicename', 'user_email' );
		$args['fields'] = array( 'ID', 'user_login' );
	}

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
	if ( ! empty( $_GET['s'] ) ) {
		$args['search'] = '*' . $_GET['s'] . '*';
	}

	// Get only the student roles.
	$args['role'] = 'coursepress_student';

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

function coursepress_get_student_workbook_data( $user_id = 0, $course_id = 0 ) {
	$user = coursepress_get_user( $user_id );
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) ) {
		return false;
	}

	$data = array();
	$course_id = $course->__get( 'ID' );
	$with_modules = $course->is_with_modules();
	$units = $course->get_units();

	if ( $units ) {
		foreach ( $units as $unit ) {
			$unit_id = $unit->__get( 'ID' );

			$data[ $unit_id ] = array(
				'progress' => (int) $user->get_unit_progress( $course_id, $unit_id ),
				'title' => $unit->__get( 'post_title' ),
				'type' => 'unit',
			);

			if ( $with_modules ) {
				$modules = $unit->get_modules_with_steps();

				if ( $modules ) {
					foreach ( $modules as $module ) {
						$cid = $unit_id . $module['id'];

						$data[ $cid ] = array(
							'progress' => (int) $user->get_module_progress( $course_id, $unit_id, $module['id'] ),
							'title' => $module['title'],
							'type' => 'module',
						);

						if ( $module['steps'] ) {
							foreach ( $module['steps'] as $step ) {
								$step_id = $step->__get( 'ID' );
								$step_status = $user->get_step_status( $course_id, $unit_id, $step_id );
								$is_completed = $user->is_step_completed( $course_id, $unit_id, $step_id );
								$grade = $user->get_step_grade( $course_id, $unit_id, $step_id );

								if ( 'pending' === $grade ) {
									$grade = __( 'Pending', 'cp' );
								}

								$data[ $step_id ] = array(
									'progress' => (int) $user->get_step_progress( $course_id, $unit_id, $step_id ),
									'title' => $step->__get( 'post_title' ),
									'type' => 'step',
									'grade' => $grade,//$is_completed ? $grade : $step_status,
								);
							}
						}
					}
				}
			}
		}
	}

	return $data;
}

function coursepress_wp_login_form() {
	$redirect_after_login = coursepress_get_setting( 'general/redirect_after_login' );
	$redirect             = '';

	if ( $redirect_after_login ) {
		$redirect = coursepress_get_dashboard_url();
	}

	$args = array(
		'redirect' => $redirect,
	);

	wp_login_form( $args );
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

		$sql = $wpdb->prepare( "SELECT student_id FROM `$students_table` WHERE `course_id`=%d GROUP BY student_id", $course_id );
	} else {
		// Get all students.
		$sql = "SELECT student_id FROM `$students_table` GROUP BY student_id";
	}

	return $wpdb->get_col( $sql );
}

/**
 * Get list of students filtered by completed unit.
 *
 * @param int $course_id Course ID
 * @param int $unit_id Unit ID
 *
 * @return array
 */
function coursepress_get_students_by_completed_unit( $course_id, $unit_id ) {

	// Get only the student roles.
	$args = array(
		'suppress_filters' => true,
		'fields' => 'ids',
		'role' => 'coursepress_student',
	);

	/**
	 * Filter students WP_User_Query arguments.
	 *
	 * @since 3.0
	 * @param array $args
	 */
	$args = apply_filters( 'coursepress_pre_get_students_by_completed_unit', $args );

	$query = new WP_User_Query( $args );
	$results = $query->results;

	$students = array();

	// If result found, get the CoursePress_User objects.
	if ( ! empty( $results ) ) {
		foreach ( $results as $result ) {
			$user = coursepress_get_user( $result );
			if ( $user->is_unit_completed( $course_id, $unit_id ) ) {
				$students[ $result ] = $user;
			}
		}
	}

	return $students;
}

/**
 * Count responses
 */
function coursepress_count_course_responses( $user, $course_id, $data = false ) {
	if ( ! is_a( $user, 'CoursePress_User' ) ) {
		$user = coursepress_get_user( $user );
	}
	if ( false === $data ) {
		$data = $user->get_completion_data( $course_id );
	}
	$units = isset( $data['units'] ) ? $data['units'] : array();
	$response_count = 0;
	foreach ( $units as $key => $unit ) {
		$modules = coursepress_get_array_val(
			$data,
			'units/' . $key . '/responses'
		);

		if ( ! empty( $modules ) ) {
			$response_count += count( $modules );
		}
	}
	return $response_count;
}

/**
 * Returns the full name of the specified user.
 *
 * Depending on param $last_first the result will be either of those
 * "First Last (displayname)"
 * "Last, First (displayname)"
 *
 * @since  1.0.0
 * @param  int  $user_id The user ID.
 * @param  bool $last_first Which format to use. Default: "First Last"
 * @param  bool $show_username Append displayname in brackets. Default: yes.
 * @return string Full name of the user.
 */
function coursepress_get_user_name( $user_id, $last_first = false, $show_username = true ) {
	$user_id = (int) $user_id;
	$display_name = (string) get_user_option( 'display_name', $user_id );
	$last = (string) get_user_option( 'last_name', $user_id );
	$first = (string) get_user_option( 'first_name', $user_id );
	$result = '';
	if ( $last_first ) {
		if ( $last ) {
			$result .= $last;
		}
		if ( $first && $result ) {
			$result .= ', ';
		}
		if ( $first ) {
			$result .= $first;
		}
	} else {
		if ( $first ) {
			$result .= $first;
		}
		if ( $last && $result ) {
			$result .= ' ';
		}
		if ( $last ) {
			$result .= $last;
		}
	}
	if ( $display_name ) {
		if ( $result && $show_username ) {
			$result .= ' (' . $display_name . ')';
		} elseif ( ! $result ) {
			$result = $display_name;
		}
	}
	return $result;
}

/**
 * Add user to blog in multiSite installations.
 */
function coursepress_add_user_to_blog( $user_id, $role = 'student', $blog_id = 0 ) {
	if ( ! is_multisite() ) {
		return;
	}
	if ( 0 === $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	if ( empty( $blog_id ) ) {
		return;
	}
	if ( is_user_member_of_blog( $user_id, $blog_id ) ) {
		return;
	}
	switch ( $role ) {
		case 'instructor':
			$role = 'coursepress_instructor';
		break;
		case 'facilitator':
			$role = 'coursepress_facilitator';
		break;
		case 'student':
			$role = 'coursepress_student';
		break;
	}
	add_user_to_blog( $blog_id, $user_id, array( $role, 'subscriber' ) );
}

/**
 * Get student enrolled date.
 *
 * @param int $course_id Course ID.
 * @param int $student_id Student ID.
 *
 * @return array|bool|mixed
 */
function coursepress_get_student_date_enrolled( $course_id, $student_id = 0 ) {

	if ( empty( $course_id ) ) {
		return false;
	}

	$student_id = empty( $student_id ) ? get_current_user_id() : $student_id;

	$date_enrolled = get_user_meta( $student_id, 'enrolled_course_date_' . $course_id );
	if ( is_array( $date_enrolled ) ) {
		$date_enrolled = array_pop( $date_enrolled );
	}

	return $date_enrolled;
}

/**
 * Get enrolled date of student for the given course.
 *
 * @param int $course_id Course ID.
 * @param int $student_id Student ID.
 *
 * @return bool
 */
function coursepress_is_student_enrolled_at( $course_id, $student_id = 0 ) {

	// If student id not given, get current user.
	$student_id = empty( $student_id ) ? get_current_user_id() : $student_id;
	$student = coursepress_get_user( $student_id );

	if ( is_wp_error( $student ) || empty( $course_id ) ) {
		return false;
	}

	return $student->is_enrolled_at( $course_id );
}

/**
 * Save student Activity.
 *
 * @param string $kind Activity type.
 * @param int $user_id User ID.
 */
function coursepress_log_student_activity( $kind = 'login', $user_id = null ) {

	CoursePress_Data_Users::log_student_activity( $kind, $user_id );
}