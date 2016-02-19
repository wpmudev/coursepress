<?php

ob_start();

/**
 * Set up environment for my plugin's tests suite.
 */

/**
 * Find the path to the WordPress tests installation.
 */
$test_dirs = array(
	getenv( 'WP_TESTS_DIR' ),
	'/srv/www/wptest/wordpress-develop/tests/phpunit/', // Vagrant.
	'/srv/www/wordpress-develop/trunk/tests/phpunit/',  // Vagrant.
);
foreach ( $test_dirs as $dir_name ) {
	if ( $dir_name && is_dir( $dir_name ) ) {
		define( 'WP_TESTS_DIR', $dir_name );
	}
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
