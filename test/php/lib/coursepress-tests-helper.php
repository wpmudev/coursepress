<?php

class CoursePress_Tests_Helper {

	private $admin;
	private $unit;

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
		add_user_meta( $student->ID, 'last_login', array( 'time' => 1478721593 ), true );
		return $student;
	}

	public function get_course() {
		$this->admin = get_user_by( 'login', 'admin' );
		/**
		 * set title
		 */
		$title = 'test course title';
		/**
		 * Course Data
		 */
		$course = get_page_by_title( $title, OBJECT, CoursePress_Data_Course::get_post_type_name() );
		if ( empty( $course ) ) {
			$postarr = (object) array(
				'post_author' => $this->admin->ID,
				'post_status' => 'publish',
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'course_excerpt' => 'test course excerpt',
				'course_description' => 'test course content',
				'course_name' => $title,
			);
			$course_id = CoursePress_Data_Course::update( false, $postarr );
			$course = get_post( $course_id );
			/**
			 * Course Taxonomy
			 */
			$taxonomy = CoursePress_Data_Course::get_post_category_name();
			$category = get_term_by( 'name', 'Test Category', $taxonomy );
			$term = array();
			if ( empty( $category ) ) {
				$term = wp_insert_term( 'Test Category', $taxonomy );
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
		 * Publish course
		 */
		wp_update_post( array( 'ID' => $course->ID, 'post_status' => 'publish' ) );

		/**
		 * add unit
		 */
		$course->units = array();
		$course->units[] = $this->add_unit( $course );

		/**
		 * Add instructor
		 */
		$instructor = $this->get_instructor();
		CoursePress_Data_Course::add_instructor( $course->ID, $instructor->ID );

		/**
		 * return course
		 */
		return $course;
	}

	private function add_unit( $course ) {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_parent' => $course->ID,
			'post_excerpt' => 'test unit excerpt',
			'post_content' => 'test unit content',
			'post_title' => 'Test Unit Title',
			'meta_input' => array(
				'page_title' => array( 'page_1' => 'page one', 'page_2' => 'page two' ),
				'show_page_title' => array( true ),
				'unit_order' => 1,
			),
		);
		$unit = get_page_by_title( $postarr->post_title, OBJECT, CoursePress_Data_Unit::get_post_type_name() );
		if ( empty( $unit ) ) {
			$unit_id = wp_insert_post( $postarr );
			$unit = get_post( $unit_id );
		}
		CoursePress_Data_Unit::show_new_pages( $unit->ID, $postarr->meta_input );
		CoursePress_Data_Unit::show_new_on_list( $unit->ID, $course->ID, $postarr->meta_input );
		$unit->modules = $this->add_modules( $unit );
		return $unit;
	}

	private function add_module( $postarr ) {
		$module = get_page_by_title( $postarr->post_title, OBJECT, CoursePress_Data_Module::get_post_type_name() );
		if ( empty( $module ) ) {
			$module_id = wp_insert_post( $postarr );
			$module = get_post( $module_id );
			CoursePress_Data_Module::show_on_list( $module->ID, $this->unit->ID, $postarr->meta_input );
		}
		return $module;
	}

	/**
	 * Text Module
	 */
	private function add_module_text() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Nullam auctor commodo eleifend. Integer aliquet, ex a rutrum tempor, mauris dolor finibus orci, elementum auctor lorem quam eu nibh.',
			'post_title' => 'Text Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 1,
				'module_page' => 1,
				'module_type' => 'text',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module Image
	 */
	private function add_module_image() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_title' => 'Image Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'caption_custom_text' => 'Image Custom Caption',
				'caption_field' => 'custom',
				'duration' => '0:00',
				'image_url' => 'http://incsub/sample.jpg',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 2,
				'module_page' => 1,
				'module_type' => 'image',
				'order' => 0,
				'retry_attempts' => 0,
				'show_media_caption' => 1,
				'show_title' => 1,
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module Video
	 */
	private function add_module_video() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_title' => 'Video Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'caption_custom_text' => 'Video Custom Caption',
				'caption_field' => 'custom',
				'duration' => '0:00',
				'hide_related_media' => 1,
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 3,
				'module_page' => 1,
				'module_type' => 'video',
				'order' => 0,
				'retry_attempts' => 0,
				'show_media_caption' => 1,
				'show_title' => 1,
				'video_url' => 'http://incsub/sample.mp4',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module Audio
	 */
	private function add_module_audio() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_title' => 'Audio Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'audio_url' => 'http://incsub/sample.mp3',
				'autoplay' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 4,
				'module_page' => 1,
				'module_type' => 'audio',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module File Download
	 */
	private function add_module_file_download() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_title' => 'File Download Module',
			'post_content' => 'Lorem Ipsum File Download Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'duration' => '0:00',
				'file_url' => 'http://incsub/sample.zip',
				'link_text' => 'Link to File Download',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 5,
				'module_page' => 1,
				'module_type' => 'download',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module Zipped Object
	 */
	private function add_module_zipped_object() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_title' => 'Zipped Object Module',
			'post_content' => 'Lorem Ipsum Zipped Object Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'duration' => '0:00',
				'link_text' => 'Link to Zipped Object',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 6,
				'module_page' => 1,
				'module_type' => 'zipped',
				'order' => 0,
				'primary_file' => 'index.html',
				'retry_attempts' => 0,
				'show_title' => 1,
				'zip_url' => 'http://incsub/sample.zip',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module Discussion
	 */
	private function add_module_discussion() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_title' => 'Discussion Module',
			'post_content' => 'Lorem Ipsum Discussion Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 7,
				'module_page' => 1,
				'module_type' => 'discussion',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
			),
		);
		$module = $this->add_module( $postarr );
		/**
		 * delete all comments
		 */
		$args = array(
			'post_id' => $module->ID,
			'status' => 'all',
		);
		$comments = get_comments( $args );
		foreach ( $comments as $comment ) {
			print_r( $comment );
			wp_delete_comment( $comment->ID, true );
		}
		/**
		 * add comment
		 */
		$student = $this->get_student();
		$data = array(
			'comment_post_ID' => $module->ID,
			'comment_author' => $student->data->user_login,
			'comment_author_email' => $student->data->user_email,
			'comment_author_url' => $student->data->user_url,
			'comment_content' => 'Comment content.',
			'user_id' => $student->ID,
			'comment_author_IP' => '127.0.0.1',
			'comment_approved' => 1,
			'comment_meta' => array(
				'last_login' => 1478686730,
			),
		);
		$comment_id = wp_insert_comment( $data );
		return $module;
	}

	function add_module_() {
	}

	/**
	 * INPUT MODULES
	 */
	/**
	 * Multiple Choice
	 */
	private function add_module_multiple_choice() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Multiple Choice',
			'post_title' => ' Module Multiple Choice',
			'meta_input' => array(
				'allow_retries' => 1,
				'answers' => array( 'Answer A', 'Answer B', 'Answer C', 'Answer D' ),
				'answers_selected' => array( 0, 2 ),
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 1,
				'module_page' => 2,
				'module_type' => 'input-checkbox',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Module Single Choice
	 */
	private function add_module_single_choice() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Single Choice',
			'post_title' => 'Single Choice Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'answers' => array( 'Answer A', 'Answer B' ),
				'answers_selected' => 0,
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 2,
				'module_page' => 2,
				'module_type' => 'input-radio',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',

			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Selectable
	 */
	private function add_module_selectable() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Selectable',
			'post_title' => 'Selectable Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'answers' => array( 'Answer A', 'Answer B', 'Answer C', 'Answer D' ),
				'answers_selected' => 0,
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 3,
				'module_page' => 2,
				'module_type' => 'input-select',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Short Answer
	 */
	private function add_module_short_answer() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Short Answer',
			'post_title' => 'Short Answer Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 4,
				'module_page' => 2,
				'module_type' => 'input-text',
				'order' => 0,
				'placeholder_text' => 'Placeholder text for Short Answer',
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Long Answer
	 */
	private function add_module_long_answer() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Long Answer',
			'post_title' => 'Long Answer Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 5,
				'module_page' => 2,
				'module_type' => 'input-textarea',
				'order' => 0,
				'placeholder_text' => 'Placeholder text for Long Answer',
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * File Upload
	 */
	private function add_module_file_upload() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum File Upload',
			'post_title' => 'File Upload Module Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 1,
				'duration' => '0:00',
				'instructor_assessable' => 1,
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 6,
				'module_page' => 1,
				'module_type' => 'input-upload',
				'order' => 0,
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Quiz
	 */
	private function add_module_quiz() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Quiz',
			'post_title' => 'Quiz Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 7,
				'module_page' => 2,
				'module_type' => 'input-quiz',
				'order' => 0,
				'questions' => array(
					array(
						'type' => 'multiple',
						'question' => 'Multiple Choice:',
						'options' => array(
							'answers' => array( 'Answer A', 'Answer B', 'Answer C', 'Answer D' ),
							'checked' => array( 1, 1, '', '' ),
							),
						),
						array(
							'type' => 'single',
							'question' => 'Single Choice:￼',
							'options' => array(
								'answers' => array( 'Answer A', 'Answer B' ),
								'checked' => array( 1, '' ),
								),
							),
						),
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	/**
	 * Form
	 */
	private function add_module_form() {
		$postarr = (object) array(
			'post_author' => $this->admin->ID,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $this->unit->ID,
			'post_content' => 'Lorem Ipsum Form',
			'post_title' => 'Form Module',
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 1,
				'duration' => '0:00',
				'mandatory' => 0,
				'minimum_grade' => 100,
				'module_order' => 8,
				'module_page' => 2,
				'module_type' => 'input-form',
				'order' => 0,
				'questions' => array(
					array(
						'type' => 'short',
						'question' => 'Short Answer:',
						'options' => array(),
						'placeholder' => 'Placeholder Text',
					),
					array(
						'type' => 'long',
						'question' => 'Long Answer:',
						'options' => array(),
						'placeholder' => 'Placeholder Text',
					),
					array(
						'type' => 'selectable',
						'question' => 'Selectable Choice:',
						'options' => array(
							'answers' => array( 'Answer A', 'Answer B' ),
							'checked' => array( 1, '' ),
							),
						),
					),
				'retry_attempts' => 0,
				'show_title' => 1,
				'use_timer' => '',
			),
		);
		return $this->add_module( $postarr );
	}

	private function add_modules( $unit ) {
		$this->admin = get_user_by( 'login', 'admin' );
		$this->unit = $unit;
		/**
		 * Add Modules
		 */
		$modules = array();
		$modules[] = $this->add_module_text();
		$modules[] = $this->add_module_image();
		$modules[] = $this->add_module_video();
		$modules[] = $this->add_module_audio();
		$modules[] = $this->add_module_file_download();
		$modules[] = $this->add_module_zipped_object();
		$modules[] = $this->add_module_discussion();
		$modules[] = $this->add_module_multiple_choice();
		$modules[] = $this->add_module_single_choice();
		$modules[] = $this->add_module_selectable();
		$modules[] = $this->add_module_short_answer();
		$modules[] = $this->add_module_long_answer();
		$modules[] = $this->add_module_file_upload();
		$modules[] = $this->add_module_quiz();
		$modules[] = $this->add_module_form();
		/**
		 * return modules
		 */
		return $modules;
	}
}
