<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Instructor' ) ) {

	class Instructor extends WP_User {

		var $first_name = '';
		var $last_name = '';
		var $courses_number = 0;

		function __construct( $ID, $name = '' ) {
			if ( $ID != 0 ) {
				parent::__construct( $ID, $name );
			}

			/* Set meta vars */

			$this->first_name     = get_user_meta( $ID, 'first_name', true );
			$this->last_name      = get_user_meta( $ID, 'last_name', true );
			$this->courses_number = Instructor::get_courses_number( $ID );
		}

		function Instructor( $ID, $name = '' ) {
			$this->__construct( $ID, $name );
		}

		static function get_course_meta_keys( $user_id ) {
			$meta = get_user_meta( $user_id );
			$meta = array_filter( array_keys( $meta ), array( 'Instructor', 'filter_course_meta_array' ) );

			return $meta;
		}

		static function filter_course_meta_array( $var ) {
			global $wpdb;
			if ( preg_match( '/^course\_/', $var ) || preg_match( '/^' . $wpdb->prefix . 'course\_/', $var ) ||
			     ( is_multisite() && ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == get_current_blog_id() ) && preg_match( '/^' . $wpdb->base_prefix . 'course\_/', $var ) )
			) {
				return $var;
			}
		}

		function get_assigned_courses_ids( $status = 'all' ) {
			global $wpdb;
			$assigned_courses = array();

			$courses = Instructor::get_course_meta_keys( $this->ID );

			foreach ( $courses as $course ) {
				$course_id = $course;

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
					if ( $status !== 'all' ) {
						if ( get_post_status( $course_id ) == $status ) {
							$assigned_courses[] = $course_id;
						}
					} else {
						$assigned_courses[] = $course_id;
					}
				}
			}

			return $assigned_courses;
		}

		function get_accessable_courses() {

			$courses = $this->get_assigned_courses_ids();
			$new_course_array = array();

			foreach( $courses as $course ) {

				$can_update				 = CoursePress_Capabilities::can_update_course( $course->ID, $this->ID );
				$can_delete				 = CoursePress_Capabilities::can_delete_course( $course->ID, $this->ID );
				$can_publish			 = CoursePress_Capabilities::can_change_course_status( $course->ID, $this->ID );
				$can_view_unit			 = CoursePress_Capabilities::can_view_course_units( $course->ID, $this->ID );
				$my_course				 = CoursePress_Capabilities::is_course_instructor( $course->ID, $this->ID );
				$creator				 = CoursePress_Capabilities::is_course_creator( $course->ID, $this->ID );

				if ( !$my_course && !$creator && !$can_update && !$can_delete && !$can_publish && !$can_view_unit ) {
					continue;
				} else {
					$new_course_array[] = $course;
				}
			}

			return $new_course_array;

		}

		function unassign_from_course( $course_id = 0 ) {
			$global_option = ! is_multisite();
			delete_user_option( $this->ID, 'course_' . $course_id, $global_option );
			delete_user_option( $this->ID, 'enrolled_course_date_' . $course_id, $global_option );
			delete_user_option( $this->ID, 'enrolled_course_class_' . $course_id, $global_option );
			delete_user_option( $this->ID, 'enrolled_course_group_' . $course_id, $global_option );

			// Legacy
			delete_user_meta( $this->ID, 'course_' . $course_id );
			delete_user_meta( $this->ID, 'enrolled_course_date_' . $course_id );
			delete_user_meta( $this->ID, 'enrolled_course_class_' . $course_id );
			delete_user_meta( $this->ID, 'enrolled_course_group_' . $course_id );
		}

		function unassign_from_all_courses() {
			$courses = $this->get_assigned_courses_ids();
			foreach ( $courses as $course_id ) {
				$this->unassign_from_course( $course_id );
			}
		}

		//Get number of instructor's assigned courses
		static function get_courses_number( $user_id = false ) {

			if ( ! $user_id ) {
				return 0;
			}

			$courses_count = count( Instructor::get_course_meta_keys( $user_id ) );

			return $courses_count;
		}

		function is_assigned_to_course( $course_id, $instructor_id ) {
			$instructor_course_id = get_user_option( 'course_' . $course_id, $instructor_id );
			if ( ! empty( $instructor_course_id ) ) {
				return true;
			} else {
				return false;
			}
		}

		function delete_instructor( $delete_user = true ) {
			/* if ( $delete_user ) {
			  wp_delete_user( $this->ID ); //without reassign
			  }else{//just delete the meta which says that user is an instructor */
			$global_option = ! is_multisite();
			delete_user_option( $this->ID, 'role_ins', 'instructor', $global_option );
			// Legacy
			delete_user_meta( $this->ID, 'role_ins', 'instructor' );
			$this->unassign_from_all_courses();
			CoursePress::instance()->drop_instructor_capabilities( $this->ID );
			//}
		}

		public static function instructor_by_hash( $hash ) {
			global $wpdb;
			$sql     = $wpdb->prepare( "SELECT user_id FROM " . $wpdb->prefix . "usermeta WHERE meta_key = %s", $hash );
			$user_id = $wpdb->get_var( $sql );

			if ( ! empty( $user_id ) ) {
				return ( new Instructor( $user_id ) );
			} else {
				return false;
			}
		}

		public static function instructor_by_login( $login ) {
			$user = get_user_by( 'login', $login );
			if ( ! empty( $user ) ) {
				// relying on core's caching here
				return ( new Instructor( $user->ID ) );
			} else {
				return false;
			}
		}

		public static function create_hash( $user_id ) {
			$user          = get_user_by( 'id', $user_id );
			$hash          = md5( $user->user_login );
			$global_option = ! is_multisite();
			/*
			 * Just in case someone is actually using this hash for something,
			 * we'll populate it with current value. Will be an empty array if
			 * nothing exists. We're only interested in the key anyway.
			 */
			update_user_option( $user->ID, $hash, get_user_option( $hash, $user->ID ), $global_option );
		}

	}

}
?>
