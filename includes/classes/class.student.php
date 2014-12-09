<?php

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'Student' ) ) {

	class Student extends WP_User {

		var $first_name		 = '';
		var $last_name		 = '';
		var $courses_number	 = 0;
		var $details			 = array();

		function __construct( $ID, $name = '' ) {
			global $wpdb;

			if ( $ID != 0 ) {
				parent::__construct( $ID, $name );
			}

			/* Set meta vars */

			$this->first_name		 = get_user_meta( $ID, 'first_name', true );
			$this->last_name		 = get_user_meta( $ID, 'last_name', true );
			$this->courses_number	 = Student::get_courses_number( $this->ID );

			/* Add hooks to handle completion data */
			add_action( 'coursepress_set_course_completed', array( &$this, 'add_course_completed_meta' ), 10, 2 );
			add_action( 'coursepress_set_unit_completed', array( &$this, 'add_unit_completed_meta' ), 10, 3 );

			/**
			 * Perform action after a Student object is created.
			 *
			 * @since 1.2.2
			 */
			do_action( 'coursepress_student_init', $this );
		}

		function Student( $ID, $name = '' ) {
			$this->__construct( $ID, $name );
		}

		// Check if the user is already enrolled in the course
		// 3rd parameter is to deal with legacy
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

		function is_course_complete( $course_ID = 0, $user_ID = '' ) {
			if ( $user_ID == '' ) {
				$user_ID = $this->ID;
			}

			$get_old_values = get_user_option( 'visited_courses', $user_ID );

			if ( $get_old_values == false ) {
				$get_old_values = array();
			}

			if ( cp_in_array_r( $course_ID, $get_old_values ) ) {
				return true;
			} else {
				return false;
			}
		}

		//Enroll student in the course
		function enroll_in_course( $course_id, $class = '', $group = '' ) {
			global $cp;
			$current_time = current_time( 'mysql' );

			$global_option = ! is_multisite();
			
			update_user_option( $this->ID, 'enrolled_course_date_' . $course_id, $current_time, $global_option ); //Link courses and student ( in order to avoid custom tables ) for easy MySql queries ( get courses stats, student courses, etc. )
			update_user_option( $this->ID, 'enrolled_course_class_' . $course_id, $class, $global_option );
			update_user_option( $this->ID, 'enrolled_course_group_' . $course_id, $group, $global_option );
			update_user_option( $this->ID, 'role', 'student', $global_option ); //alternative to roles used


			$email_args = apply_filters( 'coursepress_student_enrollment_email_args', array(
				'email_type' => 'enrollment_confirmation',
				'course_id' => $course_id,
				'dashboard_address' => CoursePress::instance()->get_student_dashboard_slug( true ),
				'student_first_name' => $this->user_firstname,
				'student_last_name' => $this->user_lastname,
				'student_email' => $this->user_email
			) );

			if ( is_email( $email_args[ 'student_email' ] ) ) {
				coursepress_send_email( $email_args );
			}
			
			$instructors = Course::get_course_instructors_ids( $_GET[ 'course_id' ]);
			do_action('student_enrolled_instructor_notification', $this->ID, $course_id, $instructors);
			do_action('student_enrolled_student_notification', $this->ID, $course_id);
			

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
		function withdraw_from_course( $course_id, $keep_withdrawed_record = true ) {

			$current_time = current_time( 'mysql' );

			$global_option = ! is_multisite();

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
			 * Perform action after a Student is withdrawn.
			 *
			 * @since 1.2.2
			 */
			$instructors = Course::get_course_instructors_ids( $course_id );
			do_action('student_withdraw_from_course_instructor_notification', $this->ID, $course_id, $instructors);
			do_action('student_withdraw_from_course_student_notification', $this->ID, $course_id);
			do_action( 'coursepress_student_withdrawn', $this->ID, $course_id );

		}

		//Withdraw from all courses

		function withdraw_from_all_courses() {
			$courses = $this->get_enrolled_courses_ids();

			foreach ( $courses as $course_id ) {
				$this->withdraw_from_course( $course_id );
			}
		}

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

		static function filter_course_meta_array( $var ) {
			$course_id_from_meta = Student::course_id_from_meta( $var );
			if ( !empty( $course_id_from_meta ) ) {
				return $var;
			}
		}

		static function course_id_from_meta( $meta_value ) {
			global $wpdb;
			$prefix		 = $wpdb->prefix;
			$base_prefix = $wpdb->base_prefix;
			$current_blog = str_replace( '_', '', str_replace( $base_prefix, '', $prefix ) );
			if( is_multisite() && empty( $current_blog ) && defined( 'BLOG_ID_CURRENT_SITE' ) ) {
				$current_blog = BLOG_ID_CURRENT_SITE;
			}

			if ( preg_match( '/enrolled\_course\_date\_/', $meta_value ) ) {

				if ( preg_match( '/^' . $base_prefix . '/', $meta_value ) ) {

					// Get the blog ID that this meta key belongs to
					$blog_id = '';
					preg_match('/(?<=' . $base_prefix . ')\d*/', $meta_value, $blog_id);
					$blog_id = $blog_id[0];

					// First site...
					if( defined( 'BLOG_ID_CURRENT_SITE' ) && BLOG_ID_CURRENT_SITE == $current_blog ) {
						$blog_id = $current_blog;
						$course_id = str_replace( $base_prefix . 'enrolled_course_date_', '', $meta_value );
					} else {
						$course_id = str_replace( $base_prefix . $blog_id . '_enrolled_course_date_', '', $meta_value );
					}

					// Only for current site...
					if( $current_blog != $blog_id ) {
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

		// alias to get_enrolled_course_ids()
		function get_assigned_courses_ids() {
			return $this->get_enrolled_courses_ids();
		}

		function get_enrolled_courses_ids() {
			// get_course_enrollment_meta returns the course_ids
			return Student::get_course_enrollment_meta( $this->ID );
		}

		//Get number of courses student enrolled in
		static function get_courses_number( $user_id = false ) {
			if ( !$user_id ) {
				return 0;
			}
			$courses_count = count( Student::get_course_enrollment_meta( $user_id ) );
			return $courses_count;
		}

		function delete_student( $delete_user = false ) {
			if ( $delete_user ) {
				wp_delete_user( $this->ID ); //without reassign				
			} else {
				$this->withdraw_from_all_courses();

				$global_option = ! is_multisite();

				delete_user_option( $this->ID, 'role', $global_option );
				// Legacy
				delete_user_meta( $this->ID, 'role' );
			}
		}

		function has_access_to_course( $course_id = '', $user_id = '' ) {
            global $wpdb;

//            if ( empty( $user_id ) ) {
//                $user_id = get_current_user_id();
//            }
//
//            if ( empty( $course_id ) ) {
//                return false;
//            }
//			$courses = $this->get_enrolled_courses_ids();
//	        return $this->user_enrolled_in_course( $course_id );
//            return is_array( $courses ) ? in_array( $course_id, $courses ) : false;
			return $this->user_enrolled_in_course( $course_id, $user_id );
		}

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
					$graded_responses++;
				}
			}

			if ( $total_grade >= 1 ) {
				$avarage_grade = round( ( $total_grade / $graded_responses ), 2 );
			} else {
				$avarage_grade = 0;
			}

			return $avarage_grade;
		}

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

		function update_student_group( $course_id, $group ) {
			$global_option = ! is_multisite();
			
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

		function update_student_class( $course_id, $class ) {
			$global_option = ! is_multisite();

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

		function add_student( $student_data ) {
			$student_data[ 'role' ]			 = 'subscriber';
			$student_data[ 'first_name' ]	 = str_replace( '\\', '', $student_data[ 'first_name' ] );
			return wp_insert_user( $student_data );
		}

		function add_course_completed_meta( $student_id, $course_id ) {

			$global_option = ! is_multisite();

			$course_completed_details = get_user_option( '_course_' . $course_id . '_completed', $student_id );

			// If a course has not yet been marked as completed, mark it complete.
			if( empty( $course_completed_details ) || ( ! isset( $course_completed_details['completed'] ) ) || ( isset( $course_completed_details['completed'] ) && empty ( $course_completed_details['completed'] ) ) ) {
				$course_completed_details['completed'] = true;
				update_user_option( $student_id, '_course_' . $course_id . '_completed', $course_completed_details, $global_option );

				// Will only fire once when a course is marked as complete, should not trigger again.
				do_action( 'coursepress_student_course_completed', $student_id, $course_id );
			}

		}

		function add_unit_completed_meta( $student_id, $course_id, $unit_id ) {

			$global_option = ! is_multisite();

			$course_completed_details = get_user_option( '_course_' . $course_id . '_completed', $student_id );

			// If a course completion details don't exist, create it, only then add units to it.
			if( empty( $course_completed_details ) ||  ! isset( $course_completed_details['completed'] ) ) {
				$course_completed_details = array( 'completed' => false );
			}

			// Get units marked as completed or create the array
			$units = isset( $course_completed_details['units'] ) ? $course_completed_details['units'] : array();
			$unit_ids = array_keys( $units );

			// Only update the user option if there is something to add
			if( ! in_array( $unit_id, $unit_ids ) ) {
				$units[ $unit_id ] = true;
				$course_completed_details['units'] = $units;

				update_user_option( $student_id, '_course_' . $course_id . '_completed', $course_completed_details, $global_option );

				// Will only fire once when a unit is marked as complete, should not trigger again.
				do_action( 'coursepress_student_course_unit_completed', $student_id, $course_id, $unit_id );
			}

		}

	}

}

