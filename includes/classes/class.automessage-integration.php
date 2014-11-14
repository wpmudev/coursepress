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
			//Student Enrolled - Instructor Notification
			$hooks[ 'student_enrolled_instructor_notification' ]						 = array( 'action_nicename' => __( 'Student Enrolled - Instructor(s) Notification', 'cp' ) );
			$hooks[ 'student_enrolled_instructor_notification' ][ 'arg_with_user_id' ]	 = 3; //$user_id, $course_id, $instructors (3)

			return $hooks;
		}

	}

}

$cp_automessage_integration = new CP_Automessage_Integration();
