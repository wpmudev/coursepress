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

	public function test_get_format() {
		$assert = CoursePress_Data_Unit::get_format();
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
			'rewrite',
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
		$this->assertEquals( 'unit', CoursePress_Data_Unit::get_post_type_name() );
	}

	public function test_get_time_estimation() {
		/**
		 * Wrong data
		 */
		$units = CoursePress_Data_Course::get_units_with_modules( 0, array( 'publish' ) );
		$this->assertEmpty( $units );
		/**
		 * Good data
		 */
		$units = CoursePress_Data_Course::get_units_with_modules( $this->course->ID, array( 'publish' ) );
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_time_estimation( $unit->ID, $units );
			$this->assertArrayHasKey( 'unit', $assert );
			$this->assertArrayHasKey( 'estimation', $assert['unit'] );
			$this->assertArrayHasKey( 'components', $assert['unit'] );
			$this->assertArrayHasKey( 'hours', $assert['unit']['components'] );
			$this->assertArrayHasKey( 'minutes', $assert['unit']['components'] );
			$this->assertArrayHasKey( 'seconds', $assert['unit']['components'] );
			$this->assertEquals( '00:03:00', $assert['unit']['estimation'] );
			$this->assertEquals( 0, $assert['unit']['components']['hours'] );
			$this->assertEquals( 0, $assert['unit']['components']['minutes'] );
			$this->assertEquals( 0, $assert['unit']['components']['seconds'] );
		}
	}

	public function test_by_name() {
		/**
		 * Wrong data
		 */
		$assert = Coursepress_Data_Unit::by_name( rand(), true );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = Coursepress_Data_Unit::by_name( $unit->post_name, true );
			$this->assertEquals( $unit->ID, $assert );
			$assert = Coursepress_Data_Unit::by_name( $unit->post_name, false );
			$this->assertEquals( $unit->ID, $assert->ID );
			$this->assertInstanceOf( 'WP_Post', $assert );
		}
	}

	public function test_is_unit_available() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::is_unit_available( $this->course, 0, null );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_unit_available( 0, 0, null );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::is_unit_available( $this->course, $unit, null );
			$this->assertTrue( $assert );
		}
	}

	public function test_get_page_meta() {
		$keys = array(
			'title',
			'description',
			'feature_image',
			'visible',
		);
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_page_meta( 'foo', null );
		$this->assertInternalType( 'array', $assert );
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $assert );
		}
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_page_meta( $unit, null );
			$this->assertInternalType( 'array', $assert );
			foreach ( $keys as $key ) {
				$this->assertArrayHasKey( $key, $assert );
			}
		}
	}

	public function test_get_unit_availability_status() {
		/**
		 * Wrong data
		 */
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_unit_availability_status( $this->course, $unit );
			$keys = array(
				'mandatory_required',
				'completion_required',
				'available',
			);
			foreach ( $keys as $key ) {
				$this->assertArrayHasKey( $key, $assert );
			}
			$keys = array(
				'enabled',
				'result',
			);
			foreach ( $keys as $key ) {
				$this->assertArrayHasKey( $key, $assert['mandatory_required'] );
				$this->assertArrayHasKey( $key, $assert['completion_required'] );
				$this->assertFalse( $assert['mandatory_required'][ $key ] );
				$this->assertFalse( $assert['completion_required'][ $key ] );
			}
			$this->assertTrue( $assert['available'] );
		}
	}

	public function test_get_unit_availability_date() {
		/**
		 * Wrong data
		 */
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_unit_availability_date( $unit->ID, $this->course->ID, null, $this->student->ID );
			$this->assertEmpty( $assert );
		}
	}

	public function test_get_previous_unit_id() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_previous_unit_id( 0, 0 );
		$this->assertFalse( $assert );
		/**
		 * Strange data
		 */
		$assert = CoursePress_Data_Unit::get_previous_unit_id( $this->course->ID, 0 );
		$this->assertEquals( $this->course->units[0]->ID, $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_previous_unit_id( $this->course->ID, $unit->ID );
			$this->assertFalse( $assert );
		}
	}

	public function test_get_url() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_url( 0 );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_url( $unit->ID );
			$this->assertNotEmpty( $assert );
			$assert = CoursePress_Data_Unit::get_url( $unit->ID, true );
			$this->assertNotEmpty( $assert );
			$assert = CoursePress_Data_Unit::get_url( $unit->ID, false );
			$this->assertNotEmpty( $assert );
		}
	}

	public function test_get_number_of_mandatory() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_number_of_mandatory( 0 );
		$this->assertEquals( 0, $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_number_of_mandatory( $unit->ID );
			$this->assertEquals( 0, $assert );
		}
	}

	public function test_get_course_id_by_unit() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_course_id_by_unit( 0 );
		$this->assertEquals( 0, $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_course_id_by_unit( $unit );
			$this->assertEquals( $unit->post_parent, $assert );
			$assert = CoursePress_Data_Unit::get_course_id_by_unit( $unit->ID );
			$this->assertEquals( $unit->post_parent, $assert );
		}
	}

	public function test_get_instructors() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_instructors( false );
		$this->assertInternalType( 'array', $assert );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Unit::get_instructors( 0 );
		$this->assertInternalType( 'array', $assert );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Unit::get_instructors( 'foo' );
		$this->assertInternalType( 'array', $assert );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_instructors( $unit->ID );
			$this->assertInternalType( 'array', $assert );
			$this->assertContains( $this->instructor->ID, $assert );
		}
	}

	public function test_is_unit_structure_visible() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::is_unit_structure_visible( 'foo', 'bar' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_unit_structure_visible( 'foo', 'bar', 'foobar' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_unit_structure_visible( 0, 0, 0 );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_unit_structure_visible( $this->course->ID, 0, 0 );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::is_unit_structure_visible( $this->course->ID, $unit->ID );
			$this->assertTrue( $assert );
			$assert = CoursePress_Data_Unit::is_unit_structure_visible( $this->course->ID, $unit->ID, $this->student->ID );
			$this->assertTrue( $assert );
		}
	}

	public function test_is_page_structure_visible() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::is_page_structure_visible( 'foo', 'bar', 'foobar' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_page_structure_visible( 0, 0, 0 );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_page_structure_visible( $this->course->ID, 0, 0 );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::is_page_structure_visible( $this->course->ID, $unit->ID, 1 );
			$this->assertTrue( $assert );
			$assert = CoursePress_Data_Unit::is_page_structure_visible( $this->course->ID, $unit->ID, 1, $this->student->ID );
			$this->assertFalse( $assert );
		}
		/**
		 * TODO: test when we add modules
		 */

	}

	public function test_is_module_structure_visible() {
		/**
		 * TODO: test when we add modules
		 */
	}

	public function test_get_unit_ids_by_course_ids() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_unit_ids_by_course_ids( array( 0 ) );
		$this->assertEqualSets( array(), $assert );
		$assert = CoursePress_Data_Unit::get_unit_ids_by_course_ids( array( 'foo' ) );
		$this->assertEqualSets( array(), $assert );
		/**
		 * Good data
		 */
		$units = array();
		foreach ( $this->course->units as $unit ) {
			$units[] = $unit->ID;
		}
		$assert = CoursePress_Data_Unit::get_unit_ids_by_course_ids( array( $this->course->ID ) );
		$this->assertEqualSets( $units, $assert );
	}

	public function test_get_unit_url() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::get_unit_url( 0 );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Unit::get_unit_url( 'foo' );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Unit::get_unit_url();
		$this->assertEmpty( $assert );
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_unit_url( $unit->ID );
			$this->assertNotEmpty( $assert );
		}
	}
}
