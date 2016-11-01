<?php
/**
 * @group coursepress-core
 */
class Coursepress_Data_Unit_Test extends WP_UnitTestCase {

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
		$this->course = $helper->get_course( $this->admin->ID );
	}

	public function test_exists() {
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'init_hooks' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_format' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_post_type_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_time_estimation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'is_unit_available' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_page_meta' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_unit_availability_status' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_unit_availability_date' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_previous_unit_id' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_url' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_number_of_mandatory' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_course_id_by_unit' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_instructors' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'is_unit_structure_visible' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'is_page_structure_visible' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'is_module_structure_visible' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_unit_ids_by_course_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'show_new_on_list' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'show_page' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'show_new_pages' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Unit', 'get_unit_url' ) ) );
	}
}
