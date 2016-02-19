<?php
/**
 * Unit tests.
 *
 * @package CoursePress
 */

/**
 * Test cases for Utility functions (Helper).
 */
class CoursePress_Utilty_Tests extends WP_UnitTestCase {

	/**
	 * Assert that the utility `cp_is_true` works correctly.
	 */
	function test_cp_is_true() {
		$test_values = array(
			null => false,
			0 => false,
			'0' => false,
			1 => true,
			'1' => true,
			true => true,
			false => false,
			'true' => true,
			'false' => false,
			'' => false,
			'foo' => false,
			-1 => true,
			15 => true,
			'on' => true,
			'off' => false,
			'yes' => true,
			'no' => false,
			'YES' => true,
			'Yes' => true,
			'NO' => false,
			array() => false,
			array( 1 ) => false,
			array( 'no' ) => false,
		);
		foreach ( $test_values as $value => $expected ) {
			$res = cp_is_true( $value );
			$this->assertEquals( $res, $expected );
		}
	}
}
