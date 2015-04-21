<?php

/**
 * This file defines the Student class extending WP_User.
 *
 * @copyright Incsub (http://incsub.com/)
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( !class_exists( 'Student' ) ) {

	/**
	 * This class defines the methods and properties of a Student in CoursePress.
	 *
	 * If creating a Student object outside of CoursePress make sure that CoursePress
	 * has already loaded. Hooking 'plugins_loaded' should do the trick.
	 *
	 * @todo Make sure we need !class_exists as it should be require_once() anyway.
	 *
	 * @since 1.0.0
	 * @package CoursePress
	 */
	class Student extends WP_User {

		var $first_name		 = '';
		var $last_name		 = '';
		var $courses_number	 = 0;
		var $details			 = array();

		function __construct( $ID, $name = '' ) {

			/**
			 * If its an existing user, make sure we initialise it with WP_User[]
			 */
			if ( $ID != 0 ) {
				parent::__construct( $ID, $name );
			}

			/* Set meta vars */
			$this->first_name	 = get_user_meta( $ID, 'first_name', true );
			$this->last_name	 = get_user_meta( $ID, 'last_name', true );

			/**
			 * Get number of enrolled courses.
			 */
			$this->courses_number = Student::get_courses_number( $this->ID );

			/**
			 * Add hooks to handle completion data.
			 */
			add_action( 'coursepress_set_course_completed', array( &$this, 'add_course_completed_meta' ), 10, 2 );
			add_action( 'coursepress_set_unit_completed', array( &$this, 'add_unit_completed_meta' ), 10, 3 );
			add_action( 'coursepress_set_all_unit_pages_viewed', array( &$this, 'add_pages_viewed_meta' ), 10, 3 );
			add_action( 'coursepress_set_mandatory_question_answered', array( &$this, 'add_mandatory_questions_meta' ), 10, 4 );
			add_action( 'coursepress_set_gradable_question_passed', array( &$this, 'add_questions_passed_meta' ), 10, 4 );

			/**
			 * Add hooks to handle other tracking
			 * @todo More hooks coming.
			 */
			/**
			 * Perform action after a Student object is created.
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_student_init', $this );
		}

		// PHP legacy constructor
		function Student( $ID, $name = '' ) {
			$this->__construct( $ID, $name );
		}

		/**
		 * Check if the user is already enrolled in the course.
		 *
		 * @param $course_id
		 * @param bool $user_id
		 * @param string $action Obsolete parameter. No longer required.
		 *
		 * @return bool
		 */
		function user_enrolled_in_course( $course_id, $user_id = false, $action = '' ) {

			if ( empty( $user_id ) ) {
				$user_id = $this->ID;
			}

			if ( get_user_option( 'enrolled_course_date_' . $course_id, $user_id ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check to see if a user has visited a course.
		 *
		 * Better to use Course_Completion[] class. But keeping this method for legacy.
		 *
		 * @see Course_Completion
		 *
		 * @param int $course_ID
		 * @param string $user_ID
		 *
		 * @return bool True if user has accessed the course at least once.
		 */
		function is_course_visited( $course_ID = 0, $user_ID = '' ) {
			if ( $user_ID == '' ) {
				$user_ID = $this->ID;
			}

			$get_old_values = get_user_meta( $user_ID, 'visited_courses', false );

			if ( $get_old_values == false ) {
				$get_old_values = array();
			}

			if ( cp_in_array_r( $course_ID, $get_old_values ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check to see if a user has visited a specific unit.
		 *
		 * Better to use Course_Completion[] class. But keeping this method for legacy.
		 *
		 * @see Course_Completion
		 *
		 * @param int $unit_ID
		 * @param string $user_ID
		 *
		 * @return bool True if user has accessed the course at least once.
		 */
		function is_unit_visited( $unit_ID = 0, $user_ID = '' ) {
			if ( $user_ID == '' ) {
				$user_ID = $this->ID;
			}

			$get_old_values	 = get_user_option( 'visited_units', $user_ID );
			$get_old_values	 = explode( '|', $get_old_values );

			if ( cp_in_array_r( $unit_ID, $get_old_values ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Check to see if the student has completed a given course.
		 *
		 * Better to use Course_Completion[] class. See code in this function.
		 *
		 * @see Course_Completion
		 *
		 * @param int $course_ID
		 * @param string $user_ID
		 *
		 * @return bool True if course is complete.
		 */
		function is_course_complete( $course_ID = 0, $user_ID = '' ) {
			if ( $user_ID == '' ) {
				$user_ID = $this->ID;
			}

//			$completion	= new Course_Completion( $course_ID );
//			$completion->init_student_status( $user_ID );
//
//			return $completion->is_course_complete();
			if ( 100 == (int) Student_Completion::calculate_course_completion( $user_ID, $course_ID ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Enroll student in the given course.
		 *
		 * @todo $class and $group for future development
		 *
		 * @param $course_id
		 * @param string $class
		 * @param string $group
		 *
		 * @return bool
		 */
		function enroll_in_course( $course_id, $class = '', $group = '' ) {
			global $cp;
			$current_time = current_time( 'mysql' );

			$global_option = !is_multisite();
			
			/**
			 * Update metadata with relevant details.
			 */
			update_user_option( $this->ID, 'enrolled_course_date_' . $course_id, $current_time, $global_option ); //Link courses and student ( in order to avoid custom tables ) for easy MySql queries ( get courses stats, student courses, etc. )
			update_user_option( $this->ID, 'enrolled_course_class_' . $course_id, $class, $global_option );
			update_user_option( $this->ID, 'enrolled_course_group_' . $course_id, $group, $global_option );
			update_user_option( $this->ID, 'role', 'student', $global_option ); //alternative to roles used

			/**
			 * Filter can be used to override email details.
			 */
			$email_args = apply_filters( 'coursepress_student_enrollment_email_args', array(
				'email_type'		 => 'enrollment_confirmation',
				'course_id'			 => $course_id,
				'dashboard_address'	 => CoursePress::instance()->get_student_dashboard_slug( true ),
				'student_first_name' => $this->user_firstname,
				'student_last_name'	 => $this->user_lastname,
				'student_email'		 => $this->user_email
			) );

			/**
			 * If a valid email address is given, use it to email the student with enrollment information.
			 */
			if ( is_email( $email_args[ 'student_email' ] ) ) {
				coursepress_send_email( $email_args );
			}

			/**
			 * Setup actions for when a student enrolls.
			 * Can be used to create notifications or tracking student actions.
			 */
			$instructors = Course::get_course_instructors_ids( isset( $_GET[ 'course_id' ] ) ? $_GET[ 'course_id' ] : $course_id  );
			do_action( 'student_enrolled_instructor_notification', $this->ID, $course_id, $instructors );
			do_action( 'student_enrolled_student_notification', $this->ID, $course_id );

			/**
			 * Perform action after a Student is enrolled.
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_student_enrolled', $this->ID, $course_id );

			return true;
			//TO DO: add new payment status if it's paid
		}

		//Withdraw student from the course

		/**
		 * Withdraw a student from a course.
		 *
		 * @param $course_id
		 * @param bool $keep_withdrawed_record If true, the withdrawn date will be saved in user meta.
		 */
		function withdraw_from_course( $course_id, $keep_withdrawed_record = true ) {

			$current_time = current_time( 'mysql' );

			$global_option = !is_multisite();

			delete_user_option( $this->ID, 'enrolled_course_date_' . $course_id, $global_option );
			delete_user_option( $this->ID, 'enrolled_course_class_' . $course_id, $global_option );
			delete_user_option( $this->ID, 'enrolled_course_group_' . $course_id, $global_option );

			// Legacy
			delete_user_meta( $this->ID, 'enrolled_course_date_' . $course_id );
			delete_user_meta( $this->ID, 'enrolled_course_class_' . $course_id );
			delete_user_meta( $this->ID, 'enrolled_course_group_' . $course_id );

			if ( $keep_withdrawed_record ) {
				update_user_option( $this->ID, 'withdrawed_course_date_' . $course_id, $current_time, $global_option ); //keep a record of all withdrawed students
			}

			/**
			 * Perform actions after a Student is withdrawn.
			 *
			 * Can be used for notifications and student tracking.
			 *
			 * @since 1.2.2
			 */
			$instructors = Course::get_course_instructors_ids( $course_id );
			do_action( 'student_withdraw_from_course_instructor_notification', $this->ID, $course_id, $instructors );
			do_action( 'student_withdraw_from_course_student_notification', $this->ID, $course_id );
			do_action( 'coursepress_student_withdrawn', $this->ID, $course_id );
		}

		/**
		 * Withdraw a student from all courses.
		 *
		 * @uses Student::withdraw_from_course
		 */
		function withdraw_from_all_courses() {
			$courses = $this->get_enrolled_courses_ids();

			foreach ( $courses as $course_id ) {
				$this->withdraw_from_course( $course_id );
			}
		}

		/**
		 * Filters through student meta to return only the course IDs.
		 *
		 * @uses Student::filter_course_meta_array() to filter the meta array
		 *
		 * @param $user_id
		 *
		 * @return array|mixed
		 */
		static function get_course_enrollment_meta( $user_id ) {
			$meta = get_user_meta( $user_id );
			if ( $meta ) {
				// Get only the enrolled courses
				$meta	 = array_filter( array_keys( $meta ), array( 'Student', 'filter_course_meta_array' ) );
				// Map only the course IDs back to the array
				$meta	 = array_map( array( 'Student', 'course_id_from_meta' ), $meta );
			}

			return $meta;
		}

		/**
		 * Filters through student meta.
		 *
		 * @uses Student::course_id_from_meta()
		 *
		 * @return mixed
		 */
		static function filter_course_meta_array( $var ) {
			$course_id_from_meta = Student::course_id_from_meta( $var );
			if ( !empty( $course_id_from_meta ) ) {
				return $var;
			}

			return false;
		}

		/**
		 * Extracts the correct Course ID from the meta.
		 *
		 * Makes sure that the correct ID gets returned from the correct blog
		 * regardless of single- or multisite.
		 *
		 * @param $meta_value
		 *
		 * @return bool|mixed
		 */
		static function course_id_from_meta( $meta_value ) {
			global $wpdb;
			$prefix			 = $wpdb->prefix;
			$base_prefix	 = $wpdb->base_prefix;
			$current_blog	 = str_replace( '_', '', str_replace( $base_prefix, '', $prefix ) );
			if ( is_multisite() && empty( $current_blog ) && defined( 'BLOG_ID_CURRENT_SITE' ) ) {
				$current_blog = BLOG_ID_CURRENT_SITE;
			}

			if ( preg_match( '/enrolled\_course\_date\_/', $meta_value ) ) {

				if ( preg_match( '/^' . $base_prefix . '/', $meta_value ) ) {

					// Get the blog ID that this meta key belongs to
					$blog_id = '';
					preg_match( '/(?<=' . $base_prefix . ')\d*/', $meta_value, $blog_id );
					$blog_id = $blog_id[ 0 ];

					// First site...
					if ( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == $current_blog ) {
						$blog_id	 = $current_blog;
						$course_id	 = str_replace( $base_prefix . 'enrolled_course_date_', '', $meta_value );
					} else {
						$course_id = str_replace( $base_prefix . $blog_id . '_enrolled_course_date_', '', $meta_value );
					}

					// Only for current site...
					if ( $current_blog != $blog_id ) {
						return false;
					}
				} else {
					// old style, but should support it at least in the listings
					$course_id = str_replace( 'enrolled_course_date_', '', $meta_value );
				}

				if ( !empty( $course_id ) ) {
					return $course_id;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		/**
		 * Get the IDs of enrolled courses.
		 *
		 * @uses Student::get_course_enrollment_meta()
		 * @return array Contains enrolled course IDs.
		 */
		function get_enrolled_courses_ids() {
			return Student::get_course_enrollment_meta( $this->ID );
		}

		/**
		 * Alias to get_enrolled_courses_ids()
		 *
		 * @uses Student::get_enrolled_courses_ids()
		 * @return array
		 */
		function get_assigned_courses_ids() {
			return $this->get_enrolled_courses_ids();
		}

		/**
		 * Get number of courses the student is enrolled in.
		 *
		 * @param bool $user_id
		 *
		 * @return int
		 */
		static function get_courses_number( $user_id = false ) {
			if ( !$user_id ) {
				return 0;
			}
			$courses_count = count( Student::get_course_enrollment_meta( $user_id ) );

			return $courses_count;
		}

		/**
		 * Either deletes the WordPress user or simply withdraws the user.
		 *
		 * Defaults to withdrawing as deleting a user is quite a drastic action.
		 *
		 * @param bool $delete_user Defaults to 'false' only use 'true' if you know what you're doing.
		 */
		function delete_student( $delete_user = false ) {
			if ( $delete_user ) {
				wp_delete_user( $this->ID ); //without reassign
			} else {
				$this->withdraw_from_all_courses();

				$global_option = !is_multisite();

				delete_user_option( $this->ID, 'role', $global_option );
				// Legacy
				delete_user_meta( $this->ID, 'role' );
			}
		}

		/**
		 * Alias to user_enrolled_in_course()
		 *
		 * @uses Student::user_enrolled_in_course()
		 *
		 * @param string $course_id
		 * @param string $user_id
		 *
		 * @return bool
		 */
		function has_access_to_course( $course_id = '', $user_id = '' ) {
			return $this->user_enrolled_in_course( $course_id, $user_id );
		}

		/**
		 * Gets the total amount of module/unit element responses.
		 *
		 * @param $course_id
		 *
		 * @return int
		 */
		function get_number_of_responses( $course_id ) {
			$args = array(
				'post_type'		 => array( 'module_response', 'attachment' ),
				'post_status'	 => array( 'publish', 'inherit' ),
				'meta_query'	 => array(
					array(
						'key'	 => 'user_ID',
						'value'	 => $this->ID
					),
					array(
						'key'	 => 'course_ID',
						'value'	 => $course_id
					),
				)
			);

			return count( get_posts( $args ) );
		}

		/**
		 * Gets the average grade of module/unit element responses.
		 *
		 * @param $course_id
		 *
		 * @return int
		 */
		function get_avarage_response_grade( $course_id ) {
			$args = array(
				'post_type'		 => array( 'module_response', 'attachment' ),
				'post_status'	 => array( 'publish', 'inherit' ),
				'meta_query'	 => array(
					array(
						'key'	 => 'user_ID',
						'value'	 => $this->ID
					),
					array(
						'key'	 => 'course_ID',
						'value'	 => $course_id
					),
				)
			);

			$posts				 = get_posts( $args );
			$graded_responses	 = 0;
			$total_grade		 = 0;

			foreach ( $posts as $post ) {
				if ( isset( $post->response_grade[ 'grade' ] ) && is_numeric( $post->response_grade[ 'grade' ] ) ) {
					$assessable = get_post_meta( $post->post_parent, 'gradable_answer', true );
					if ( $assessable == 'yes' ) {
						$total_grade = $total_grade + (int) $post->response_grade[ 'grade' ];
					}
					$graded_responses ++;
				}
			}

			if ( $total_grade >= 1 ) {
				$avarage_grade = round( ( $total_grade / $graded_responses ), 2 );
			} else {
				$avarage_grade = 0;
			}

			return $avarage_grade;
		}

		/**
		 * Updates a student's data.
		 *
		 * @param $student_data
		 *
		 * @return bool
		 */
		function update_student_data( $student_data ) {
			$student_data = apply_filters( 'coursepress_student_update_data', $student_data );
			if ( wp_update_user( $student_data ) ) {

				/**
				 * Perform action after a Student object is updated.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_student_updated', $this->ID );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * Updates Student's group.
		 *
		 * @todo Future development.
		 *
		 * @param $course_id
		 * @param $group
		 *
		 * @return bool
		 */
		function update_student_group( $course_id, $group ) {
			$global_option = !is_multisite();

			if ( update_user_option( $this->ID, 'enrolled_course_group_' . $course_id, $group, $global_option ) ) {

				/**
				 * Perform action after updating a Student's group.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_student_group_updated', $this->ID, $course_id, $group );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * Update's a student's class in a course.
		 *
		 * @todo Future development.
		 *
		 * @param $course_id
		 * @param $class
		 *
		 * @return bool
		 */
		function update_student_class( $course_id, $class ) {
			$global_option = !is_multisite();

			if ( update_user_option( $this->ID, 'enrolled_course_class_' . $course_id, $class, $global_option ) ) {

				/**
				 * Perform action after updating a Student's class.
				 *
				 * @since 1.2.2
				 */
				do_action( 'coursepress_student_group_updated', $this->ID, $course_id, $class );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * Add's a new user to WordPress with relevant data.
		 *
		 * @param $student_data
		 *
		 * @return int|WP_Error
		 */
		function add_student( $student_data ) {
			$student_data[ 'role' ]		 = get_option( 'default_role', 'subscriber' );
			$student_data[ 'first_name' ]	 = str_replace( '\\', '', $student_data[ 'first_name' ] );

			return wp_insert_user( $student_data );
		}

		/**
		 * Updates student's completion meta-data.
		 *
		 * This also triggers relevant actions that are relevant for student tracking.
		 *
		 * @param $student_id
		 * @param $course_id
		 */
		function add_course_completed_meta( $student_id, $course_id ) {

			$global_option = !is_multisite();

			$course_completed_details	 = get_user_option( '_course_' . $course_id . '_completed', $student_id );
			$do_update					 = false;

			// If a course has not yet been marked as completed, mark it complete.
			if ( empty( $course_completed_details ) || (!isset( $course_completed_details[ 'completed' ] ) ) || ( isset( $course_completed_details[ 'completed' ] ) && empty( $course_completed_details[ 'completed' ] ) ) ) {
				$course_completed_details[ 'completed' ]	 = true;
				$do_update								 = true;
				// Will only fire once when a course is marked as complete, should not trigger again.
				do_action( 'coursepress_student_course_completed', $student_id, $course_id );
			}

			// If there is no certificate number yet, generate one
			if ( !isset( $course_completed_details[ 'certificate_number' ] ) || empty( $course_completed_details[ 'certificate_number' ] ) ) {
				$time											 = time();
				list( $year, $month, $day ) = explode( '/', date( 'Y/m/d', $time ) );
				$course_completed_details[ 'certificate_number' ]	 = sprintf( '%04d%02d%02d%05d%03d', $year, $month, $day, $course_id, $student_id );
				$course_completed_details[ 'date_completed' ]		 = time();
				$do_update										 = true;
			}

			if ( $do_update ) {
				update_user_option( $student_id, '_course_' . $course_id . '_completed', $course_completed_details, $global_option );
			}
		}

		/**
		 * Updates student's unit completion meta-data.
		 *
		 * This also triggers relevant actions that are relevant for student tracking.
		 *
		 * @param $student_id
		 * @param $course_id
		 * @param $unit_id
		 */
		function add_unit_completed_meta( $student_id, $course_id, $unit_id ) {

			$global_option = !is_multisite();

			$course_completed_details = get_user_option( '_course_' . $course_id . '_completed', $student_id );

			// If a course completion details don't exist, create it, only then add units to it.
			if ( empty( $course_completed_details ) || !isset( $course_completed_details[ 'completed' ] ) ) {
				$course_completed_details = array( 'completed' => false );
			}

			// Get units marked as completed or create the array
			$units		 = isset( $course_completed_details[ 'units' ] ) ? $course_completed_details[ 'units' ] : array();
			$unit_ids	 = array_keys( $units );

			// Only update the user option if there is something to add
			if ( !in_array( $unit_id, $unit_ids ) ) {
				$units[ $unit_id ]					 = true;
				$course_completed_details[ 'units' ]	 = $units;

				update_user_option( $student_id, '_course_' . $course_id . '_completed', $course_completed_details, $global_option );

				// Will only fire once when a unit is marked as complete, should not trigger again.
				do_action( 'coursepress_student_course_unit_completed', $student_id, $course_id, $unit_id );
			}
		}

		/**
		 * Updates student's unit pages viewed meta-data.
		 *
		 * This also triggers relevant actions that are relevant for student tracking.
		 *
		 * @param $student_id
		 * @param $course_id
		 * @param $unit_id
		 */
		public function add_pages_viewed_meta( $student_id, $course_id, $unit_id ) {
			$global_option = !is_multisite();

			$course_progress = get_user_option( '_course_' . $course_id . '_progress', $student_id );

			$update_option = false;

			// If a course progress don't exist, create it.
			if ( empty( $course_progress ) ) {
				$course_progress = array();
			}

			// Get units to mark pages as viewed
			$units		 = isset( $course_progress[ 'units' ] ) ? $course_progress[ 'units' ] : array();
			$unit_ids	 = array_keys( $units );

			if ( !in_array( $unit_id, $unit_ids ) ) {
				// Add something new
				$units[ $unit_id ]	 = array( 'all_pages_viewed' => true );
				do_action( 'coursepress_student_course_unit_pages_viewed', $student_id, $course_id, $unit_id );
				$update_option		 = true;
			} else {
				// Or update if needed
				if ( !isset( $units[ $unit_id ][ 'all_pages_viewed' ] ) || empty( $units[ $unit_id ][ 'all_pages_viewed' ] ) ) {
					$units[ $unit_id ][ 'all_pages_viewed' ]	 = true;
					do_action( 'coursepress_student_course_unit_pages_viewed', $student_id, $course_id, $unit_id );
					$update_option							 = true;
				}
			}

			if ( $update_option ) {
				$course_progress[ 'units' ] = $units;
//				update_user_option( $student_id, '_course_' . $course_id . '_progress', $course_progress, $global_option );
			}
		}

		/**
		 * Updates student's mandatory questions meta-data.
		 *
		 * This also triggers relevant actions that are relevant for student tracking.
		 *
		 * @param $student_id
		 * @param $course_id
		 * @param $unit_id
		 * @param $module_id
		 */
		public function add_mandatory_questions_meta( $student_id, $course_id, $unit_id, $module_id ) {
			$global_option = !is_multisite();

			$course_progress = get_user_option( '_course_' . $course_id . '_progress', $student_id );

			$update_option = false;

			// If a course progress don't exist, create it.
			if ( empty( $course_progress ) ) {
				$course_progress = array();
			}

			// Get units to mark pages as viewed
			$units		 = isset( $course_progress[ 'units' ] ) ? $course_progress[ 'units' ] : array();
			$unit_ids	 = array_keys( $units );

			if ( !in_array( $unit_id, $unit_ids ) ) {
				// Add something new
				$units[ $unit_id ]	 = array( 'mandatory_questions_answered' => array( $module_id => true ) );
				do_action( 'coursepress_student_course_unit_mandatory_question_answered', $student_id, $course_id, $unit_id, $module_id );
				$update_option		 = true;
			} else {
				// Or update if needed
				if ( isset( $units[ $unit_id ][ 'mandatory_questions_answered' ] ) && (!isset( $units[ $unit_id ][ 'mandatory_questions_answered' ][ $module_id ] ) || empty( $units[ $unit_id ][ 'mandatory_questions_answered' ][ $module_id ] ) ) ) {
					$units[ $unit_id ][ 'mandatory_questions_answered' ][ $module_id ] = true;
					do_action( 'coursepress_student_course_unit_mandatory_question_answered', $student_id, $course_id, $unit_id, $module_id );
					$update_option													 = true;
				} else {
					// If the unit already has data, but mandatory_questions_answered is unset
					$units[ $unit_id ][ 'mandatory_questions_answered' ]	 = array( $module_id => true );
					do_action( 'coursepress_student_course_unit_mandatory_question_answered', $student_id, $course_id, $unit_id, $module_id );
					$update_option										 = true;
				}
			}

			if ( $update_option ) {
				$course_progress[ 'units' ] = $units;
//				update_user_option( $student_id, '_course_' . $course_id . '_progress', $course_progress, $global_option );
			}
		}

		/**
		 * Updates student's questions passed meta-data.
		 *
		 * This also triggers relevant actions that are relevant for student tracking.
		 *
		 * @param $student_id
		 * @param $course_id
		 * @param $unit_id
		 * @param $module_id
		 */
		public function add_questions_passed_meta( $student_id, $course_id, $unit_id, $module_id ) {
			$global_option = !is_multisite();

			$course_progress = get_user_option( '_course_' . $course_id . '_progress', $student_id );

			$update_option = false;

			// If a course progress don't exist, create it.
			if ( empty( $course_progress ) ) {
				$course_progress = array();
			}

			// Get units to mark pages as viewed
			$units		 = isset( $course_progress[ 'units' ] ) ? $course_progress[ 'units' ] : array();
			$unit_ids	 = array_keys( $units );

			if ( !in_array( $unit_id, $unit_ids ) ) {
				// Add something new
				$units[ $unit_id ]	 = array( 'gradable_questions_passed' => array( $module_id => true ) );
				do_action( 'coursepress_student_course_unit_gradable_question_passed', $student_id, $course_id, $unit_id, $module_id );
				$update_option		 = true;
			} else {
				// Or update if needed
				if ( isset( $units[ $unit_id ][ 'gradable_questions_passed' ] ) && (!isset( $units[ $unit_id ][ 'gradable_questions_passed' ][ $module_id ] ) || empty( $units[ $unit_id ][ 'gradable_questions_passed' ][ $module_id ] ) ) ) {
					$units[ $unit_id ][ 'gradable_questions_passed' ][ $module_id ]	 = true;
					do_action( 'coursepress_student_course_unit_gradable_question_passed', $student_id, $course_id, $unit_id, $module_id );
					$update_option													 = true;
				} else {
					// If the unit already has data, but gradable_questions_passed is unset
					$units[ $unit_id ][ 'gradable_questions_passed' ]	 = array( $module_id => true );
					do_action( 'coursepress_student_course_unit_gradable_question_passed', $student_id, $course_id, $unit_id, $module_id );
					$update_option									 = true;
				}
			}

			if ( $update_option ) {
				$course_progress[ 'units' ] = $units;
//				update_user_option( $student_id, '_course_' . $course_id . '_progress', $course_progress, $global_option );
			}
		}

	}

}
