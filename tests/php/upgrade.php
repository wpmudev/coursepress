<?php
/**
 * Test upgrade cycle
 **/
$data_dir = dirname( dirname(__FILE__) ) . '/data/';

require_once $data_dir . 'course.php';

class CoursePressUpgradeTest extends WP_UnitTestCase {
	/**
	 * @before
	 **/
	public static function bootstrap() {
		if ( defined( 'WP_COURSEPRESS_DIR' ) ) {
			return;
		}

		$bootstrap = WP_COURSEPRESS_DIR . 'tests/bootstrap.php';

		require $bootstrap;
	}

	public static function require_coursepress( $version = '' ) {
		require_once WP_COURSEPRESS_DIR . $version . 'coursepress.php';
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_version_1() {
		// Pre-create courses
		$c1 = CoursePressData::course_data(array(
			'post_title' => 'Course 1'
		));

		$c1 = self::factory()->post->create( $c1 );

		add_action( 'coursepress_init_vars', array( __CLASS__, 'init_vars' ), 100 );

		self::require_coursepress();

		global $coursepress;
		$this->assertEquals( true, CoursePressUpgrade::check_old_courses() );
		$this->assertTrue( class_exists( 'CoursePress' ) );
		$this->assertStringStartsWith( '1.', $coursepress->version );
	}

	public static function init_vars( $instance ) {
		$instance->location = 'plugins';
		$instance->plugin_dir = WP_COURSEPRESS_DIR . '1.x/';
		$instance->plugin_url = WP_COURSEPRESS_DIR . '2.0/';
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_new_install() {
		self::require_coursepress();

		$this->assertTrue( class_exists( 'CoursePress' ) );
		$this->assertEquals( false, CoursePressUpgrade::check_old_courses() );
		$this->assertStringStartsWith( '2.0', CoursePress::$version );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_version_2() {
		self::require_coursepress();

		// Pre-create courses
		$c1 = CoursePressData::course_data(array(
			'post_title' => 'Course 1'
		));

		$c1 = self::factory()->post->create( $c1 );

		$settings = array(
			'course_start_date' => '2016-21-05 04:14'
		);

		CoursePress_Data_Course::update_setting( $c1, true, $settings );

		$this->assertEquals( false, CoursePressUpgrade::check_old_courses() );
		$this->assertStringStartsWith( '2.0', CoursePress::$version );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public static function create_course() {
		// Pre-create courses
		$c1 = CoursePressData::course_data(array(
			'post_title' => 'Course 1'
		));

		$c1 = self::factory()->post->create( $c1 );

		$settings = array(
			'course_start_date' => '2016-21-05 04:14'
		);

		update_post_meta( $c1, 'course_settings', $settings );

		return $c1;
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_global_settings_migration() {
		$course_id = self::create_course();

		$old_settings = array(
			'display_menu_items' => 1,
			'use_custom_login_form' => 1,
			'redirect_students_to_dashboard' => 1,
			'coursepress_course_slug' => 'courses1',
			'coursepress_course_category_slug' => 'course_category1',
			'coursepress_module_slug' => 'module1',
			'coursepress_units_slug' => 'units1',
			'coursepress_notifications_slug' => 'notifications1',
			'coursepress_discussion_slug' => 'discussion1',
			'coursepress_discussion_slug_new' => 'add_new_discussion1',
			'coursepress_grades_slug' => 'grades1',
			'coursepress_workbook_slug' => 'workbook1',
			'enrollment_process_slug' => 'enrollment_process1',
			'login_slug' => 'student-login1',
			'signup_slug' => 'student-signup1',
			'student_dashboard_slug' => 'student-dashboard1',
			'student_settings_slug' => 'student-settings1',
			'instructor_profile_slug' => 'instructor1',
			'coursepress_inbox_slug' => 'student-inbox1',
			'coursepress_sent_messages_slug' => 'student-sent-messages1',
			'coursepress_new_message_slug' => 'student-new-message1',
			'coursepress_enrollment_process_page' => 0,
			'coursepress_login_page' => 0,
			'coursepress_signup_page' => 0,
			'coursepress_student_dashboard_page' => 0,
			'coursepress_student_settings_page' => 0,

			'details_media_type' => 'default',
			'details_media_priority' => 'video',
			'listings_media_type' => 'default',
			'listings_media_priority' => 'image',
		);

		foreach ( $old_settings as $key => $value ) {
			update_option( $key, $value );
		}

		self::require_coursepress();
		$this->assertStringStartsWith( '2.0', CoursePress::$version );

		// Update global course settings
		CoursePress_Upgrade::init();

		$new_settings = CoursePress_Core::get_setting( '' );

		$this->assertTrue( ! empty( $new_settings ) );

		// Test general settings
		$general = CoursePress_Core::get_setting( 'general' );
		$this->assertEquals( 1, $general['show_coursepress_menu'] );
		$this->assertEquals( 1, $general['use_custom_login'] );
		$this->assertEquals( 1, $general['redirect_after_login'] );

		// Slugs
		$slugs = CoursePress_Core::get_setting( 'slugs' );
		$this->assertEquals( 'courses1', $slugs['course'] );
		$this->assertEquals( 'course_category1', $slugs['category'] );
		$this->assertEquals( 'module1', $slugs['module'] );
		$this->assertEquals( 'units1', $slugs['units'] );
		$this->assertEquals( 'notifications1', $slugs['notifications'] );
		$this->assertEquals( 'discussion1', $slugs['discussions'] );
		$this->assertEquals( 'add_new_discussion1', $slugs['discussions_new'] );
		$this->assertEquals( 'grades1', $slugs['grades'] );
		$this->assertEquals( 'workbook1', $slugs['workbook'] );
		$this->assertEquals( 'enrollment_process1', $slugs['enrollment'] );
		$this->assertEquals( 'student-login1', $slugs['login'] );
		$this->assertEquals( 'student-signup1', $slugs['signup'] );
		$this->assertEquals( 'student-dashboard1', $slugs['student_dashboard'] );
		$this->assertEquals( 'student-settings1', $slugs['student_settings'] );

		// Pages
		// Course
		// Reports
		// Instructor
		// Basic Certificate
		// Email Settings
		// MarketingPress
		// WooCommerce
		// Terms of Service
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_course_settings_migration() {
		$c1 = self::factory()->post->create(array(
			'post_type' => 'course',
			'post_title' => 'Course 2',
			'post_excerpt' => 'course-except',
			'post_content' => 'course-description',
		));

		register_taxonomy( 'course_category', 'course_category' );
		$tax1 = self::factory()->term->create(array(
			'name' => 'Technology',
			'taxonomy' => 'course_category'
		));

		wp_set_object_terms( $c1, $tax1, 'course_category' );

		$instructor1 = self::factory()->user->create();
		$instructor2 = self::factory()->user->create();

		// Set featured url
		$featured_url = 'http://local.wordpress-trunk.dev/wp-content/uploads/2016/11/20161027_194251.jpg';
		update_post_meta( $c1, 'featured_url', $featured_url );
		// Set instructors
		$instructors = array( $instructor1, $instructor2 );
		update_post_meta( $c1, 'instructors', $instructors );
		// Course dates
		$start = Date( 'Y-m-d', strtotime( '-5 Days' ) );
		update_post_meta( $c1, 'course_start_date', $start );
		$end = Date( 'Y-m-d', strtotime( '15 Days' ) );
		update_post_meta( $c1, 'course_end_date', $end );

		// Enrollments
		update_post_meta( $c1, 'enrollment_start_date', $start );
		update_post_meta( $c1, 'enrollment_end_date', $end );

		// Discussion
		update_post_meta( $c1, 'allow_course_discussion', 'on' );
		update_post_meta( $c1, 'allow_workbook_page', 'on' );
		update_post_meta( $c1, 'allow_grades', 'on' );
		update_post_meta( $c1, 'enroll_type', 'registered' );

		// Units
		register_post_type( 'unit' );
		$unit1 = self::factory()->post->create(array(
			'post_type' => 'unit',
			'post_title' => 'Unit 1',
			'post_status' => 'publish',
			'post_parent' => $c1,
		));
		update_post_meta( $unit1, 'unit_availability', $start );
		update_post_meta( $unit1, 'force_current_unit_completion', 'on' );
		update_post_meta( $unit1, 'force_current_unit_successful_completion', 'on' );
		update_post_meta( $unit1, 'refresh_unit_completion_progres', 'on' );

		update_post_meta( $unit1, 'page_title', array(
			'page_1' => 'Page 1: Section 1',
			'page_2' => 'Page 2: Section 2'
		));
		update_post_meta( $unit1, 'show_page_title', array(
			'yes',
			'no'
		));

		// Modules
		$mt = self::factory()->post->create(array(
			'post_type' => 'module',
			'post_status' => 'publish',
			'post_title' => 'Multiple Choice',
			'post_content' => 'Multiple Choice Module',
		));
		$mt_meta = array(
			'module_type' => 'checkbox_input_module',
			'module_page' => 1,
			'answers' => array(
				'Option 1',
				'Option 2',
				'Option 3',
				'Option 4'
			),
			'checked_answers' => array(
				'Option 1',
				'Option 3'
			)
		);

		// Students
		$current_time = current_time( 'mysql' );
		$global_option = !is_multisite();
		$class = '';
		$group = '';

		$user1 = self::factory()->user->create();

		update_user_option( $user1, 'enrolled_course_date_' . $c1, $current_time, $global_option );
		update_user_option( $user1, 'enrolled_course_class_' . $c1, $class, $global_option );
		update_user_option( $user1, 'enrolled_course_group_' . $c1, $group, $global_option );
		update_user_option( $user1, 'role', 'student', $global_option );

		//$user2 = self::factory()->user->create();
		//$user3 = self::factory()->user->create();

		require_once WP_COURSEPRESS_DIR . 'upgrade/class-helper-upgrade.php';

		CoursePress_Helper_Upgrade::update_course( $c1 );

		self::require_coursepress();
		CoursePress_Core::init();

		$course_setting = CoursePress_Data_Course::get_setting( $c1 );

		$this->assertEquals( $featured_url, $course_setting['listing_image'] );
		$this->assertEquals( $start, $course_setting['course_start_date'] );
		$this->assertEquals( $end, $course_setting['course_end_date'] );
		$this->assertEquals( $start, $course_setting['enrollment_start_date'] );
		$this->assertEquals( $end, $course_setting['enrollment_end_date'] );
		$this->assertEquals( 1, $course_setting['allow_discussion'] );
		$this->assertEquals( 1, $course_setting['allow_workbook'] );
		$this->assertEquals( 1, $course_setting['allow_grades'] );
		$this->assertEquals( 'registered', $course_setting['enrollment_type'] );

		// Instructors
		$cp2_instructors = CoursePress_Data_Course::get_instructors( $c1 );
		$this->assertTrue( ! empty( $cp2_instructors ) );
		$this->assertTrue( in_array( $instructor1, $cp2_instructors ) );
		$this->assertTrue( in_array( $instructor2, $cp2_instructors ) );

		// Students
		$students = CoursePress_Data_Course::get_student_ids( $c1 );
		$this->assertTrue( ! empty( $students ) );
		$this->assertTrue( CoursePress_Data_Student::is_enrolled_in_course( $user1, $c1 ) );

		// Units
		$this->assertTrue( CoursePress_Data_Unit::is_unit_available( $c1, $unit1, false ) );
		$this->assertEquals( $start, get_post_meta( $unit1, 'unit_availability_date', true ) );
	}

}
