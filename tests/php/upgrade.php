<?php
/**
 * Test upgrade cycle
 **/
$data_dir = dirname( __DIR__ ) . '/data/';

require $data_dir . 'course.php';

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

	public static function require_coursepress() {
		require_once WP_COURSEPRESS_DIR . 'coursepress.php';
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
	}
}
