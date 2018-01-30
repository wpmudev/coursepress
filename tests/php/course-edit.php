<?php
/**
 * Test Course Edit
 **/
$data_dir = dirname( dirname(__FILE__) ) . '/data/';

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

		$instructors = CoursePress_Data_Course::get_setting( $course->ID, 'instructors', array() );

		do_action( 'dbx_post_advanced', $course );

		do_action( 'edit_form_after_editor', $course );

		$content = ob_get_clean();

		preg_match_all( '|<div class="step-title[^>]*>|', $content, $matches );

		$this->assertCount( 7, array_keys( $matches[0] ) );
	}

	function test_settings() {
		$c1 = CoursePressData::course_data(array(
			'post_title' => 'Course 1'
		));
		$c1 = self::factory()->post->create( $c1 );

		$settings = array(
			'course_start_date' => '2016-21-05 04:14'
		);

		update_post_meta( $c1, 'course_settings', $settings );

		$instructors = CoursePress_Data_Course::get_setting( $c1, 'instructors', array() );

		$this->assertTrue( empty( $instructors ) );
	}

	function test_edit_course() {
		$hooks = new CoursePress_Hooks();
		$edit = new CoursePress_Admin_Edit();

		// Setup hooks
		$hooks->init();
		//$edit->init_hooks();

		$c1 = CoursePressData::course_data(array(
			'post_title' => 'Course 1'
		));
		$c1 = self::factory()->post->create( $c1 );

		$_REQUEST['action'] = 'edit';
		$_REQUEST['post'] = $c1;

		do_action( 'plugins_loaded' );

		ob_start();

		$course = get_post( $c1 );
		do_action( 'dbx_post_advanced', $course );

		do_action( 'edit_form_after_editor', $course );

		$content = ob_get_clean();

		preg_match_all( '|<div class="step-title[^>]*>|', $content, $matches );

		$this->assertCount( 7, array_keys( $matches[0] ) );
	}
}
