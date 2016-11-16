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
			0 => false,
			'0' => false,
			1 => true,
			'1' => true,
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
		);
		foreach ( $test_values as $value => $expected ) {
			$res = cp_is_true( $value );
			$this->assertEquals( $res, $expected );
		}

		$this->assertTrue( cp_is_true( true ) );
		$this->assertfalse( cp_is_true( false ) );
		$this->assertfalse( cp_is_true( null ) );
		$this->assertfalse( cp_is_true( array() ) );
		$this->assertfalse( cp_is_true( array( 1 ) ) );
		$this->assertfalse( cp_is_true( array( 'no' ) ) );
		$this->assertfalse( cp_is_true( array( 'yes' ) ) );
	}
}
