<?php
/**
 * Test Course Edit
 **/
$data_dir = dirname( __DIR__ ) . '/data/';

require_once $data_dir . 'course.php';

class TestCourseEdit extends WP_UnitTestCase {
	function setUp() {
		parent::setUp();

		set_current_screen( 'course' );
		$GLOBALS['hook_suffix'] = '';

		require_once WP_COURSEPRESS_DIR . '2.0/coursepress.php';
	}

	function test_new_course() {
		$hooks = new CoursePress_Hooks();
		$edit = new CoursePress_Admin_Edit();

		// Setup hooks
		$hooks->init();
		//$edit->init_hooks();

		$course = get_default_post_to_edit( 'course', true );

		do_action( 'plugins_loaded' );

		ob_start();

		do_action( 'dbx_post_advanced', $course );

		do_action( 'edit_form_after_editor', $course );

		$content = ob_get_clean();

		preg_match_all( '|<div class="step-title[^>]*>|', $content, $matches );

		$this->assertCount( 7, array_keys( $matches[0] ) );
	}
}
