<?php

/**
 * course generate class
 *
 * @since 2.0.6
 */


class CoursePress_Helper_Course_Generator {

	private static $user_id;
	private static $unit_id;
	private static $lorem;

	/**
	 * Add schema information
	 *
	 * @since 2.0.6
	 */
	public static function run() {
		self::$user_id = get_current_user_id();
		if ( empty( self::$user_id ) ) {
			return;
		}
		self::$lorem = new CoursePress_Helper_Extension_LoremIpsum();
		/**
		 * Add course
		 */
		$postarr = (object) array(
			'post_author' => self::$user_id,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'course_excerpt' => self::$lorem->words( 10 + rand( 10, 20 ) ),
			'course_description' => self::$lorem->paragraphs( 1 + rand( 1, 3 ), 'p' ),
			'course_name' => self::$lorem->words( 3 + rand( 2, 5 ) ),
		);
		$course_id = CoursePress_Data_Course::update( false, $postarr );
		$course = get_post( $course_id );
		/**
		 * Add settings
		 */
		$settings = array();
		/**
		 * Step 1 – Course Overview
		 */
		$listing_image = CoursePress::$url.'asset/img/coursepress.png';
		CoursePress_Data_Course::set_setting( $settings, 'listing_image', $listing_image );
		CoursePress_Data_Course::set_setting( $settings, 'course_language', 'English' );
		CoursePress_Data_Course::set_setting( $settings, 'setup_step_1', 'saved' );

		/**
		 * Step 2 – Course Details
		 */
		CoursePress_Data_Course::set_setting( $settings, 'featured_video', 'https://www.youtube.com/watch?v=y_bIr1yAELw&list=UULgqhMisF-ykzHZzuMEfV4Q' );
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
		CoursePress_Data_Course::set_setting( $settings, 'instructors', array( self::$user_id ) );
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
		$course->units = array();
		$course->units[] = self::add_unit( $course );

		/**
		 * Publish course
		 */
		wp_update_post( array( 'ID' => $course->ID, 'post_status' => 'publish' ) );
	}

	private static function add_unit( $course ) {
		$postarr = (object) array(
			'post_author' => self::$user_id,
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_parent' => $course->ID,
			'post_excerpt' => self::$lorem->words( 10 + rand( 10, 20 ) ),
			'post_content' => self::$lorem->paragraphs( rand( 1, 3 ), 'p' ),
			'post_title' => self::$lorem->words( 3 + rand( 2, 5 ) ),
			'meta_input' => array(
				'page_title' => array(
					'page_1' => self::$lorem->words( rand( 1, 3 ) ),
					'page_2' => self::$lorem->words( rand( 1, 3 ) ),
				),
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
		$unit->modules = self::add_modules( $unit );
		return $unit;
	}

	private static function add_module( $postarr ) {
		$postarr->post_author = self::$user_id;
		$postarr->post_title = self::$lorem->words( 2 + rand( 1, 5 ) );
		$postarr->post_parent = self::$unit_id;
		$postarr->post_status = 'publish';
		$postarr->post_content = self::$lorem->paragraphs( rand( 1, 2 ), 'p' );
		$module = get_page_by_title( $postarr->post_title, OBJECT, CoursePress_Data_Module::get_post_type_name() );
		if ( empty( $module ) ) {
			$module_id = wp_insert_post( $postarr );
			$module = get_post( $module_id );
			CoursePress_Data_Module::show_on_list( $module->ID, self::$unit_id, $postarr->meta_input );
		}
		return $module;
	}

	/**
	 * Text Module
	 */
	private static function add_module_text() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
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
		return self::add_module( $postarr );
	}

	/**
	 * Module Image
	 */
	private static function add_module_image() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'caption_custom_text' => self::$lorem->words( rand( 3, 8 ) ),
				'caption_field' => 'custom',
				'duration' => '0:00',
				'image_url' => CoursePress::$url.'asset/img/coursepress.png',
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
		return self::add_module( $postarr );
	}

	/**
	 * Module File Download
	 */
	private static function add_module_file_download() {
		$postarr = (object) array(
			'post_status' => 'publish',
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'meta_input' => array(
				'allow_retries' => 1,
				'assessable' => 0,
				'duration' => '0:00',
				'file_url' => 'https://downloads.wordpress.org/plugin/coursepress.zip',
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
		return self::add_module( $postarr );
	}

	/**
	 * Module Discussion
	 */
	private static function add_module_discussion() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => self::$unit_id,
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
		$module = self::add_module( $postarr );
		return $module;
	}

	/**
	 * INPUT MODULES
	 */
	/**
	 * Multiple Choice
	 */
	private static function add_module_multiple_choice() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
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
		return self::add_module( $postarr );
	}

	/**
	 * Module Single Choice
	 */
	private static function add_module_single_choice() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
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
		return self::add_module( $postarr );
	}

	/**
	 * Selectable
	 */
	private static function add_module_selectable() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
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
		return self::add_module( $postarr );
	}

	/**
	 * Short Answer
	 */
	private static function add_module_short_answer() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
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
		return self::add_module( $postarr );
	}

	/**
	 * Long Answer
	 */
	private static function add_module_long_answer() {
		$postarr = (object) array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
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
		return self::add_module( $postarr );
	}

	private static function add_modules( $unit ) {
		self::$unit_id = $unit->ID;
		/**
		 * Add Modules
		 */
		$modules = array();
		$modules[] = self::add_module_text();
		$modules[] = self::add_module_image();
		$modules[] = self::add_module_file_download();
		$modules[] = self::add_module_discussion();
		$modules[] = self::add_module_multiple_choice();
		$modules[] = self::add_module_single_choice();
		$modules[] = self::add_module_selectable();
		$modules[] = self::add_module_short_answer();
		$modules[] = self::add_module_long_answer();
		/**
		 * return modules
		 */
		return $modules;
	}
}
