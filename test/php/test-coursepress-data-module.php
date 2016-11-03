<?php
/**
 * @group coursepress-core
 */
class Coursepress_Data_Module_Test extends WP_UnitTestCase {

	protected $admin;
	protected $course;
	protected $instructor;
	protected $student;

	public function __construct() {
		$helper = new CoursePress_Tests_Helper();
		$this->admin = get_user_by( 'login', 'admin' );
		/**
		 * Set instructor data
		 */
		$this->instructor = $helper->get_instructor();
		/**
		 * Set student data
		 */
		$this->student = $helper->get_student();
		/**
		 * Set course data
		 */
		$this->course = $helper->get_course();
	}

	public function test_exists() {
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'module_init_hooks' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_format' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_post_type_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_time_estimation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'legacy_map' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'fix_legacy_meta' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'attributes' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_module_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussions_comments_open' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_post_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_edit_redirect' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_reply_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'discussion_cancel_reply_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_quiz_results' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_form_results' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'quiz_result_content' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_args_mandatory_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_mandatory_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'is_module_done_by_student' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'add_last_login_time' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_modules_ids_by_unit' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_unit_id_by_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_course_id_by_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_instructors' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'add_instructors_to_comments_args' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'get_module_ids_by_unit_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Module', 'show_on_list' ) ) );
	}

}
