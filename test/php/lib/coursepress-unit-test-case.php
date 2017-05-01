<?php

class CoursePress_UnitTestCase extends WP_UnitTestCase {

	protected $admin;
	protected $course;
	protected $facilitator;
	protected $instructor;
	protected $modules;
	protected $student;

	public function setUp() {
		parent::setUp();

		$helper = new CoursePress_Tests_Helper();
		$this->admin = get_user_by( 'login', 'admin' );
		/**
		 * Set instructor data
		 */
		$this->instructor = $helper->get_instructor();
		/**
		 * Set facilitator data
		 */
		$this->facilitator = $helper->get_facilitator();
		/**
		 * Set student data
		 */
		$this->student = $helper->get_student();
		/**
		 * Set course data
		 */
		$this->course = $helper->get_course();
	}

	public function tearDown()
	{
		_delete_all_data();

		parent::tearDown();
	}

	protected function get_modules() {
		if ( ! empty( $this->modules ) ) {
			return $this->modules;
		}
		$this->modules = array();
		foreach ( $this->course->units as $unit ) {
			$this->modules = array_merge( $this->modules, $unit->modules );
		}
		return $this->modules;
	}

	protected function has_keys( $keys, $assert ) {
		foreach ( $keys as $key => $value ) {
			if ( is_array( $value ) ) {
				$this->has_keys( $value, $assert[ $key ] );
				continue;
			}
			$this->assertArrayHasKey( $value, $assert );
		}
	}

	protected function get_wrong_values() {
		return array(
			'',
			0,
			array(),
			'foo',
			new stdClass(),
			null,
		);
	}
}

