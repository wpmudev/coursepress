<?php
/**
 * Test upgrade cycle
 **/
$data_dir = dirname( __DIR__ ) . '/data/';

require $data_dir . 'course.php';

class CoursePressUpgradeTest extends WP_UnitTestCase {
	public static function bootstrap() {
		if ( defined( 'WP_COURSEPRESS_DIR' ) ) {
			return;
		}

		$bootstrap = WP_COURSEPRESS_DIR . 'tests/bootstrap.php';

		require $bootstrap;
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_version_1() {
		self::bootstrap();

		$courseupgrade = WP_COURSEPRESS_DIR . 'coursepress.php';

		// Pre-create courses
		$c1 = CoursePressData::course_data(array(
			'post_title' => 'Course 1'
		));

		$c1 = self::factory()->post->create( $c1 );

		require_once $courseupgrade;

		global $coursepress;
		$this->assertEquals( true, CoursePressUpgrade::check_old_courses() );
		$this->assertTrue( class_exists( 'CoursePress' ) );
		$this->assertStringStartsWith( '1.', $coursepress->version );
	}

	/**
	 * Set CP 1.x directory name
	 **/
	public static function before_init_vars( $instance ) {
		preg_match( '%coursepress-tests-lib%', WP_TESTS_DIR, $matches );

		$instance->dir_name = '1.x';
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_new_install() {
		self::bootstrap();

		$course_upgrade = WP_COURSEPRESS_DIR . 'coursepress.php';

		require_once $course_upgrade;

		$this->assertTrue( class_exists( 'CoursePress' ) );
		$this->assertEquals( false, CoursePressUpgrade::check_old_courses() );
		$this->assertStringStartsWith( '2.0', CoursePress::$version );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 **/
	public function test_version_2() {
		self::bootstrap();

		$course_upgrade = WP_COURSEPRESS_DIR . 'coursepress.php';

		require_once $course_upgrade;

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
}
