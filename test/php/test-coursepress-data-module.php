<?php
/**
 * @group coursepress-core
 */
class Coursepress_Data_Module_Test extends WP_UnitTestCase {

	protected $admin;
	protected $course;
	protected $instructor;
	protected $student;
	protected $modules;

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

	public function test_get_format() {
		$assert = CoursePress_Data_Module::get_format();
		$keys = array(
			'post_type',
			'post_args',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert );
		}
		$keys = array(
			'labels',
			'public',
			'show_ui',
			'publicly_queryable',
			'capability_type',
			'map_meta_cap',
			'query_var',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert['post_args'] );
		}
		$keys = array(
			'name',
			'singular_name',
			'add_new',
			'add_new_item',
			'edit_item',
			'edit',
			'new_item',
			'view_item',
			'search_items',
			'not_found',
			'not_found_in_trash',
			'view',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert['post_args']['labels'] );
		}
	}

	public function test_get_post_type_name() {
		$this->assertEquals( 'module', CoursePress_Data_Module::get_post_type_name() );
	}

	public function test_get_time_estimation() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_time_estimation( 'foo' );
		$this->assertEquals( '1:00', $assert );
		$assert = CoursePress_Data_Module::get_time_estimation( 0 );
		$this->assertEquals( '1:00', $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::get_time_estimation( $module->ID );
			$this->assertEquals( '1:00', $assert );
		}
	}

	public function test_legacy_map() {
		$assert = CoursePress_Data_Module::legacy_map();
		$keys = array(
			'audio_module',
			'chat_module',
			'checkbox_input_module',
			'file_module',
			'file_input_module',
			'image_module',
			'page_break_module',
			'radio_input_module',
			'page_break_module',
			'section_break_module',
			'text_module',
			'text_input_module',
			'textarea_input_module',
			'video_module',
		);
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert );
		}
	}

	public function test_attributes() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::attributes( 'foo' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Module::attributes( 0 );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		$keys = array(
			'module_type',
			'mode',
			'duration',
			'show_title',
			'allow_retries',
			'retry_attempts',
			'minimum_grade',
			'assessable',
			'mandatory',
			'module_order',
			'module_page',
			'order',
		);
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Module::attributes( $module );
			$this->assertInternalType( 'array', $assert );
			foreach ( $keys as $key ) {
				$this->assertArrayHasKey( $key, $assert );
			}
			if ( preg_match( '/^input/', $assert['module_type'] ) ) {
				$this->assertArrayHasKey( 'use_timer', $assert );
			}
			switch ( $assert['module_type'] ) {
				case 'input-radio':
					$this->assertArrayHasKey( 'answers', $assert );
					$this->assertArrayHasKey( 'answers_selected', $assert );
				break;
				case 'input-upload':
					$this->assertArrayHasKey( 'instructor_assessable', $assert );
				break;
			}
			/**
			 * TODO: add (if needed) another module types
			 */
		}
	}

	public function test_discussion_module_link() {
		/**
		 * TODO: add module discussion
		 */
	}

	public function test_discussions_comments_open() {
		/**
		 * TODO: add module discussion
		 */
	}

	public function test_discussion_post_link() {
		/**
		 * TODO: add module discussion
		 */
	}

	public function test_discussion_edit_redirect() {
		/**
		 * TODO: add module discussion
		 */
	}

	public function test_discussion_reply_link() {
		/**
		 * TODO: add module discussion
		 */
	}

	public function test_discussion_cancel_reply_link() {
		/**
		 * TODO: add module discussion
		 */
	}

	public function test_get_quiz_results() {
		/**
		 * TODO: add module quiz
		 */
	}

	public function test_quiz_result_content() {
		/**
		 * TODO: add module quiz
		 */
	}

	public function test_get_form_results() {
		/**
		 * TODO: add module form
		 */
	}


	public function test_get_args_mandatory_modules() {
		$expected = array(
			'fields' => 'ids',
			'meta_key' => 'mandatory',
			'meta_value' => '1',
			'nopaging' => '1',
			'post_parent' => 0,
			'post_type' => 'module',
		);
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Module::get_args_mandatory_modules( 0 );
		$this->assertInternalType( 'array', $assert );
		$this->assertEqualSetsWithIndex( $expected, $assert );
		$assert = CoursePress_Data_Module::get_args_mandatory_modules( 'foo' );
		$this->assertInternalType( 'array', $assert );
		$expected['post_parent'] = 'foo';
		$this->assertEqualSetsWithIndex( $expected, $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Module::get_args_mandatory_modules( $unit->ID );
			$this->assertInternalType( 'array', $assert );
			$expected['post_parent'] = $unit->ID;
			$this->assertEqualSetsWithIndex( $expected, $assert );
		}
	}

	private function get_modules() {
		if ( ! empty( $this->modules ) ) {
			return $this->modules;
		}
		$this->modules = array();
		foreach ( $this->course->units as $unit ) {
			$this->modules = array_merge( $this->modules, $unit->modules );
		}
		return $this->modules;
	}
}
		/**
		print_r(array( $assert));
		 * Wrong data
		 */
		/**
		 * Good data
		 */
