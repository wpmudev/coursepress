<?php
/**
 * Class CoursePress_Data_Users
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Data_Users {
	public function __construct() {
		// Hook into `coursepress_add_instructor`
		add_action( 'coursepress_add_instructor', array( $this, 'add_instructor_role' ) );
		// Hook into `coursepress_delete_instructor`
		add_action( 'coursepress_delete_instructor', array( $this, 'remove_instructor_role' ), 10, 2 );
		// Hook into `coursepress_add_student`
		add_action( 'coursepress_add_student', array( $this, 'add_student_role' ) );
		// Hook into `coursepress_delete_student`
		add_action( 'coursepress_delete_student', array( $this, 'delete_student_role' ) );
	}

	function add_instructor_role( $user_id ) {
		if ( ! user_can( $user_id, 'coursepress_instructor' ) ) {
			$user = get_userdata( $user_id );
			$user->add_role( 'coursepress_instructor' );
		}
	}

	function remove_instructor_role( $user_id ) {
		$course_ids = coursepress_get_instructor_courses( $user_id, false, true, true );

		if ( count( $course_ids ) <= 0 ) {
			$user = get_userdata( $user_id );
			$user->remove_role( 'coursepress_instructor' );
		}
	}

	function add_student_role( $user_id ) {
		if ( ! user_can( $user_id, 'coursepress_student' ) ) {
			$user = get_userdata( $user_id );
			$user->add_role( 'coursepress_student' );
		}
	}

	function delete_student_role( $user_id ) {
		$enrolled_courses_ids = coursepress_get_enrolled_courses( $user_id, false, true, true );

		if ( count( $enrolled_courses_ids ) <= 0 ) {
			$user = get_userdata( $user_id );
			$user->remove_role( 'coursepress_student' );
		}
	}
}