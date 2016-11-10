<?php

class CoursePress_UnitTestCase extends WP_UnitTestCase {

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
		foreach ( $keys as $key ) {
			if ( is_array( $key ) ) {
				$this->has_keys( $key, $assert[ $key ] );
				continue;
			}
			$this->assertArrayHasKey( $key, $assert );
		}
	}

}

