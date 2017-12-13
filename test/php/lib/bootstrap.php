<?php

ob_start();

require_once  dirname(dirname(dirname(dirname(__FILE__)))) . '/vendor/antecedent/patchwork/Patchwork.php';

/**
 * Set up environment for my plugin's tests suite.
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wp-tests/coursepress-tests-lib';
}

/**
 * The path to the WordPress tests checkout.
 */
if ( file_exists( $_tests_dir ) ) {
	define( 'WP_TESTS_DIR', '/tmp/wp-tests/coursepress-tests-lib/' );
} else { // Without the "wptest" but with "trunk" subfolder...
	define( 'WP_TESTS_DIR', '/srv/www/wordpress-develop/trunk/tests/phpunit/' );
}

/**
 * The path to the main file of the plugin to test.
 */
define( 'TEST_PLUGIN_FILE', 'coursepress.php' );

define( 'IS_UNIT_TEST', true );

/**
 * The WordPress tests functions.
 *
 * We are loading this so that we can add our tests filter
 * to load the plugin, using tests_add_filter().
 */
require_once WP_TESTS_DIR . 'includes/functions.php';

/**
 * Manually load the plugin main file.
 *
 * The plugin won't be activated within the test WP environment,
 * that's why we need to load it manually.
 *
 * You will also need to perform any installation necessary after
 * loading your plugin, since it won't be installed.
 */
function _manually_load_plugin() {

	require TEST_PLUGIN_FILE;

	// Make sure plugin is installed here ...
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now,
 * and viola, the tests begin.
 */
require WP_TESTS_DIR . 'includes/bootstrap.php';
require( dirname( __FILE__ ) . '/coursepress-tests-helper.php' );
require( dirname( __FILE__ ) . '/coursepress-unit-test-case.php' );
