<?php
/**
 * @group coursepress-core
 */
class CoursePress_Data_Unit_Test extends CoursePress_UnitTestCase {

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

	/**
	 * get_format()
	 */
	public function test_get_format() {
		$keys = array(
			'post_type',
			'post_args' => array(
				'labels' => array(
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
				),
				'public',
				'show_ui',
				'publicly_queryable',
				'capability_type',
				'map_meta_cap',
				'query_var',
				'rewrite',
			),
		);
		$assert = CoursePress_Data_Unit::get_format();
		$this->has_keys( $keys, $assert );
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
		$keys = array(
			'unit' => array(
				'estimation',
				'components' => array(
					'hours',
					'minutes',
					'seconds',
				),
			),
		);

		$modules = $this->get_modules();
		$time = count( $modules );
		$units = CoursePress_Data_Course::get_units_with_modules( $this->course->ID, array( 'publish' ) );
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::get_time_estimation( $unit->ID, $units );
			$this->has_keys( $keys, $assert );
			$this->assertRegExp( '/\d\d:\d\d:\d\d/', $assert['unit']['estimation'] );
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

	/**
	 * is_unit_structure_visible( $course_id, $unit_id, $user_id = 0 )
	 */
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
			$this->assertTrue( $assert );
		}
	}

	/**
	 * is_module_structure_visible( $course_id, $unit_id, $module_id, $user_id = 0 )
	 */
	public function test_is_module_structure_visible() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::is_module_structure_visible( 'foo', 'bar', 'foobar', 'baz' );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_module_structure_visible( 0, 0, 0 );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Unit::is_module_structure_visible( $this->course->ID, 0, 0 );
		$this->assertFalse( $assert );
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Unit::is_module_structure_visible( $this->course->ID, $module->post_parent, $module->ID );
			$this->assertInternalType( 'boolean', $assert );
			$assert = CoursePress_Data_Unit::is_module_structure_visible( $this->course->ID, $module->post_parent, $module->ID, $this->student->ID );
			$this->assertInternalType( 'boolean', $assert );
		}
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

	/**
	 * show_new_on_list( $unit_id, $course_id, $meta = array() )
	 */
	public function test_show_new_on_list() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::show_new_on_list( 'foo', 'bar' );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Unit::show_new_on_list( 0, 0 );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::show_new_on_list( $unit->ID, $unit->post_parent );
			$this->assertEmpty( $assert );
		}
	}

	/**
	 * show_page( $unit_id, $page_id, $course_id = false )
	 */
	public function test_show_page() {
		/**
		print_r(array( $assert ) );
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::show_page( 'foo', 'bar', 'baz' );
			$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Unit::show_page( 0, 0, 0 );
			$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::show_page( $unit->ID, 1, $unit->post_parent );
			$this->assertEmpty( $assert );
		}
	}

	/**
	 * show_new_pages( $unit_id, $meta )
	 */
	public function test_show_new_pages() {
		/**
		 * Wrong data
		 */
		$assert = CoursePress_Data_Unit::show_new_pages( 'foo', 'bar' );
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Unit::show_new_pages( 0, 0 );
		$this->assertEmpty( $assert );
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Unit::show_new_pages( $unit->ID, array( 'page_title' => 'foo' ) );
			$this->assertEmpty( $assert );
		}
	}
}
