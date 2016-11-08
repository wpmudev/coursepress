<?php
/**
 * Test upgrade cycle
 **/

class CoursePressUpgradeTest extends WP_UnitTestCase {
	public static function load_version_1() {
		$upgrade_class = WP_COURSEPRESS_DIR . 'upgrade/class-upgrade.php';

		// Load upgrade class
		require $upgrade_class;
		//CoursePress_Upgrade::init();

		add_action( 'coursepress_init_vars', array( __CLASS__, 'init_vars' ) );

		$version_1 = WP_COURSEPRESS_DIR . '1.x/';
		$version_file = $version_1 . 'coursepress.php';

		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			define( 'WP_PLUGIN_DIR', dirname( WP_COURSEPRESS_DIR ) );
			define( 'WP_PLUGIN_URL', false ); // @todo: Get the correct value
		}

		require $version_file;
	}

	public static function init_vars( $instance ) {
		$instance->location = 'plugins';
		$instance->plugin_dir = WP_PLUGIN_DIR . '/coursepress/1.x/';
		$instance->plugin_url = WP_PLUGIN_URL . '/coursepress/1.x/';
	}

	public function testVersion1() {
		self::load_version_1();

		$this->assertTrue( class_exists( 'CoursePress' ) );
	}
}
