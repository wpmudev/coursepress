<?php

class CoursePress_Tests_Helper {

	public function get_instructor() {
		$instructor = get_user_by( 'login', 'instructor' );
		if ( false === $instructor ) {
			$userdata = array(
				'user_login'  => 'instructor',
				'user_url'    => 'https://premium.wpmudev.org/',
				'user_pass'   => 'instructor',
				'first_name'  => 'Jon',
				'last_name'   => 'Snow',
				'nickname'    => 'bastard',
				'description' => 'Winter is comming.',
				'user_email'  => 'snow@winterfell.com',
			);
			$user_id = wp_insert_user( $userdata );
			$instructor = get_userdata( $user_id );
		}
		return $instructor;
	}

	public function get_student() {
		$student = get_user_by( 'login', 'student' );
		if ( false === $student ) {
			$userdata = array(
				'user_login'  => 'student',
				'user_url'    => 'https://premium.wpmudev.org/',
				'user_pass'   => 'student',
				'first_name'  => 'Albert',
				'last_name'   => 'Einstein',
				'nickname'    => 'brain',
				'description' => 'E=mc^2',
				'user_email'  => 'einstein@example.com',
			);
			$user_id = wp_insert_user( $userdata );
			$student = get_userdata( $user_id );
		}
		return $student;
	}

	public function get_course( $admin_id ) {
		/**
		 * Course Data
		 */
		$course = get_page_by_title( 'test course title', OBJECT, CoursePress_Data_Course::get_post_type_name() );
		if ( empty( $course ) ) {
			$course = (object) array(
				'post_author' => $admin_id,
				'post_status' => 'private',
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'course_excerpt' => 'test course excerpt',
				'course_description' => 'test course content',
				'course_name' => 'test course title',
			);
			$course_id = CoursePress_Data_Course::update( false, $course );
			$course = get_post( $course_id );
			/**
			 * Course Taxonomy
			 */
			$taxonomy = CoursePress_Data_Course::get_post_category_name();
			$category = get_term_by( 'name', 'Test Category', $taxonomy );
			$term = array();
			if ( empty( $category ) ) {
				$term = wp_insert_term( 'Test Category', $taxonomy );
				print_r( $term );
			}
			wp_set_post_terms( $course_id, array( $term['term_id'] ), $taxonomy );
		}
		/**
		 * Add settings
		 */
		$settings = CoursePress_Data_Course::get_setting( $course->ID );
		/**
		 * Step 1 – Course Overview
		 */
		CoursePress_Data_Course::set_setting( $settings, 'listing_image', '' );
		CoursePress_Data_Course::set_setting( $settings, 'course_language', 'English' );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_1', 'saved' );

		/**
		 * Step 2 – Course Details
		 */
		CoursePress_Data_Course::set_setting( $settings, 'featured_video', '' );
		CoursePress_Data_Course::set_setting( $settings, 'course_view', 'normal' );
		CoursePress_Data_Course::set_setting( $settings, 'focus_hide_section', 'unit' );
		CoursePress_Data_Course::set_setting( $settings, 'structure_visible', 'on' );
		CoursePress_Data_Course::set_setting( $settings, 'structure_show_duration', 'on' );
		CoursePress_Data_Course::set_setting( $settings, 'structure_level', 'section' );
		CoursePress_Data_Course::set_setting( $settings, 'structure_preview_pages', array() );
		CoursePress_Data_Course::set_setting( $settings, 'structure_visible_pages', array() );
		CoursePress_Data_Course::set_setting( $settings, 'structure_preview_units', array() );
		CoursePress_Data_Course::set_setting( $settings, 'structure_visible_units', array() );
		CoursePress_Data_Course::set_setting( $settings, 'structure_preview_modules', array() );
		CoursePress_Data_Course::set_setting( $settings, 'structure_visible_modules', array() );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_2', 'saved' );

		/**
		 * Step 3 – Instructors and Facilitators
		 */
		CoursePress_Data_Course::set_setting( $settings, 'instructors', array() );
		CoursePress_Data_Course::set_setting( $settings, 'facilitators', array() );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_3', 'saved' );

		/**
		 * Step 4 – Course Dates
		 */
		CoursePress_Data_Course::set_setting( $settings, 'course_start_date', '2016-10-01' );
		CoursePress_Data_Course::set_setting( $settings, 'course_end_date', '2116-10-01' );
		CoursePress_Data_Course::set_setting( $settings, 'enrollment_end_date', '2016-10-11' );
		CoursePress_Data_Course::set_setting( $settings, 'enrollment_start_date', '2116-10-01' );
		CoursePress_Data_Course::set_setting( $settings, 'course_open_ended', 'off' );
		CoursePress_Data_Course::set_setting( $settings, 'open_ended_enrollment', 'off' );
		CoursePress_Data_Course::set_setting( $settings, 'enrollment_open_ended', 'off' );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_4', 'saved' );

		/**
		 * Step 5 – Classes, Discussion & Workbook
		 */
		CoursePress_Data_Course::set_setting( $settings, 'class_limited', 'on' );
		CoursePress_Data_Course::set_setting( $settings, 'class_size', '10' );
		CoursePress_Data_Course::set_setting( $settings, 'allow_discussion', 'on' );
		CoursePress_Data_Course::set_setting( $settings, 'allow_workbook', 'on' );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_5', 'saved' );

		/**
		 * Step 6 – Enrollment & Course Cost
		 */
		CoursePress_Data_Course::set_setting( $settings, 'enrollment_type', 'anyone' );
		CoursePress_Data_Course::set_setting( $settings, 'enrollment_passcode', false );
		CoursePress_Data_Course::set_setting( $settings, 'enrollment_prerequisite', false );
		CoursePress_Data_Course::set_setting( $settings, 'payment_paid_course', 'off' );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_6', 'saved' );

		/**
		 * Step 7 - Course Completion
		 */

		CoursePress_Data_Course::set_setting( $settings, 'minimum_grade_required', '100' );
		CoursePress_Data_Course::set_setting( $settings, 'pre_completion_title', 'Almost there!' );
		CoursePress_Data_Course::set_setting( $settings, 'pre_completion_content', 'Almost there content.' );
		CoursePress_Data_Course::set_setting( $settings, 'course_completion_title', 'Congratulations, You Passed!' );
		CoursePress_Data_Course::set_setting( $settings, 'course_completion_content', 'Congratulations, You Passed! content' );
		CoursePress_Data_Course::set_setting( $settings, 'course_failed_title', 'Sorry, you did not pass this course!' );
		CoursePress_Data_Course::set_setting( $settings, 'course_failed_content', 'I\'m sorry to say you didn\'t pass COURSE_NAME. Better luck next time!' );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_7', 'saved' );

		/**
		 * Save course settings
		 */
		CoursePress_Data_Course::update_setting( $course->ID, true, $settings );

		/**
		 * add unit
		 */
		$unit_id = $this->add_unit( $course );

		/**
		 * return course
		 */
		return $course;
	}

	private function add_unit( $course ) {
		return 0;
	}
}
