<?php
ob_start();
// Set coursepress main location
define( 'WP_COURSEPRESS_DIR', dirname( dirname( __FILE__ ) ) . '/' );
$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Setup environment
if ( ! empty( $_tests_dir ) ) {
	$_tests_dir = '/tmp/wp-tests/coursepress-tests-lib';
}

$_test_bootstrap = 'includes/bootstrap.php';
$bootstrap = $_tests_dir . '/' . $_test_bootstrap;

// Verify test location
if ( ! file_exists( $bootstrap ) ) {
	// Got problem with test dir location, coursepress in 6 level deep
	$_tests_dir = dirname( dirname( dirname( dirname( WP_COURSEPRESS_DIR ) ) ) ) . '/tests/phpunit';
	$bootstrap = $_tests_dir . '/' . $_test_bootstrap;
}

define( 'WP_TESTS_DIR', $_tests_dir );

// Load tests bootstrap
require $bootstrap;
