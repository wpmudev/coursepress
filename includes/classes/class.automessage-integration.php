<?php

/*
 * Integration with Automessage plugin
 * http://premium.wpmudev.org/project/automatic-follow-up-emails-for-new-users/
 * 
 */
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'CP_Automessage_Integration' ) ) {

	class CP_Automessage_Integration {

		function __construct() {
			add_filter( 'automessage_custom_user_hooks', array( &$this, 'add_new_hooks' ), 10, 1 );
		}

		function add_new_hooks( $hooks ) {
			//Student Enrolled - Instructors Notification
			$hooks[ 'student_enrolled_instructor_notification' ]						 = array( 'action_nicename' => __( 'Student Enrolled - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'student_enrolled_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)

			//Student Enrolled - Student Notification
			$hooks[ 'student_enrolled_student_notification' ]						 = array( 'action_nicename' => __( 'Student Enrolled - Student Notification', 'cp' ) );
			$hooks[ 'student_enrolled_student_notification' ][ 'arg_with_user_id' ]	 = 1; //$user_id (1), $course_id
			
			//Student Response / Require Grade - Instructor(s) Notification
			$hooks[ 'student_response_required_grade_instructor_notification' ]						 = array( 'action_nicename' => __( 'Student Submitted Answer - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'student_response_required_grade_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)
			
			//Student Response / Auto Grade - Instructor(s) Notification
			$hooks[ 'student_response_not_required_grade_instructor_notification' ]						 = array( 'action_nicename' => __( 'Student Submitted Answer (automatically graded) - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'student_response_not_required_grade_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)
			
			//Student Withdraw from a course - Instructor(s) Notification
			$hooks[ 'student_withdraw_from_course_instructor_notification' ]						 = array( 'action_nicename' => __( 'Student Withdraw from a Course - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'student_withdraw_from_course_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)
			
			//Student Withdraw from a course - Instructor(s) Notification
			$hooks[ 'student_withdraw_from_course_instructor_notification' ]						 = array( 'action_nicename' => __( 'Student Withdraw from a Course - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'student_withdraw_from_course_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)
	
			//Student Withdraw from a course - Student Notification
			$hooks[ 'student_withdraw_from_course_student_notification' ]						 = array( 'action_nicename' => __( 'Student Withdraw from a Course - Student Notification', 'cp' ) );
			$hooks[ 'student_withdraw_from_course_student_notification' ][ 'arg_with_user_id' ]	 = 1; //$user_id (1), $course_id
			
			//New Discussion Added to a course - Instructor(s) Notification
			$hooks[ 'new_discussion_added_instructor_notification' ]						 = array( 'action_nicename' => __( 'New Discussion Added to a Course - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'new_discussion_added_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)
			
			//New Discussion Added to a course - Student(s) Notification
			$hooks[ 'new_discussion_added_student_notification' ]						 = array( 'action_nicename' => __( 'New Discussion Added to a Course - Student(s) Notification', 'cp' ) );
			$hooks[ 'new_discussion_added_student_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $students (3)
			
			
			return $hooks;
		}

	}

}

$cp_automessage_integration = new CP_Automessage_Integration();
