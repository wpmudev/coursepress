<?php
/**
 * Test upgrade cycle
 **/

class CoursePressUpgradeTest extends WP_UnitTestCase {
	public function test_version_switch() {
		$coursepress = WP_COURSEPRESS_DIR . 'coursepress.php';

		require $coursepress;

		// Expect 2.0
		$this->assertEquals( false, CoursePressUpgrade::check_old_courses() );

		$c1 = self::factory()->post->create(array(
			'post_type' => 'course',
			'post_title' => 'Course 1',
		));

		// Expect 1.x
		$this->assertEquals( true, CoursePressUpgrade::check_old_courses() );
	}

/*

	public function test_load_version_1() {
		$upgrade_class = WP_COURSEPRESS_DIR . 'upgrade/class-upgrade.php';

		// Load upgrade class
		require $upgrade_class;

		add_action( 'coursepress_before_init_vars', array( __CLASS__, 'before_init_vars' ) );
		add_action( 'coursepress_init_vars', array( __CLASS__, 'init_vars' ) );

		$version_1 = WP_COURSEPRESS_DIR . '1.x/';
		$version_file = $version_1 . 'coursepress.php';

		require $version_file;

		$this->assertTrue( class_exists( 'CoursePress' ) );
	}

	public static function before_init_vars( $instance ) {
		$instance->dir_name = '1.x';
	}

	public static function init_vars( $instance ) {
		$instance->location = 'plugins';
		$instance->plugin_dir = WP_PLUGIN_DIR . '/coursepress/1.x/';
		$instance->plugin_url = WP_PLUGIN_URL . '/coursepress/1.x/';
	}

	public function test_load_version_2() {
		$version_2 = WP_COURSEPRESS_DIR . '2.0/';
		$version_file = $version_2 . 'coursepress.php';

		require $version_file;

		$this->assertTrue( class_exists( 'CoursePress' ) );
	}
*/
}
