<?php
/**
 * Test upgrade cycle
 **/

class CoursePressUpgradeTest extends WP_UnitTestCase {

	public function test_load_version_1() {
		$this->setUp();

		$upgrade_class = WP_COURSEPRESS_DIR . 'upgrade/class-upgrade.php';

		// Load upgrade class
		require $upgrade_class;

		add_action( 'coursepress_init_vars', array( __CLASS__, 'init_vars' ) );

		$version_1 = WP_COURSEPRESS_DIR . '1.x/';
		$version_file = $version_1 . 'coursepress.php';

		require $version_file;
	}

	public static function init_vars( $instance ) {
		$instance->location = 'plugins';
		$instance->plugin_dir = WP_PLUGIN_DIR . '/coursepress/1.x/';
		$instance->plugin_url = WP_PLUGIN_URL . '/coursepress/1.x/';
	}

	/**
	 * @depends test_load_version_1
	 **/
	public function testVersion1() {
		$this->assertTrue( class_exists( 'CoursePress' ) );
	}
}
