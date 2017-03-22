<?php
/**
 * @group coursepress-core
 */
return;
class Coursepress_Data_PostFormat_Test extends WP_UnitTestCase {

	public function test_exists() {
		$this->assertTrue( is_callable( array( 'Coursepress_Data_PostFormat', 'init' ) ) );
		$this->assertTrue( is_callable( array( 'Coursepress_Data_PostFormat', 'register_post_types' ) ) );
		$this->assertTrue( is_callable( array( 'Coursepress_Data_PostFormat', 'registered_formats' ) ) );
		$this->assertTrue( is_callable( array( 'Coursepress_Data_PostFormat', 'prefix' ) ) );
	}

	public function test_prefix() {
		$this->assertEquals( '', Coursepress_Data_PostFormat::prefix() );
		$this->assertEquals( 'foo', Coursepress_Data_PostFormat::prefix( 'foo' ) );
	}

	public function test_registered_formats() {
		$post_types = array(
			'course',
			'cp_certificate',
			'discussions',
			'module',
			'notifications',
			'unit',
		);
		foreach ( $post_types as $post_type ) {
			$this->assertContains( $post_type, Coursepress_Data_PostFormat::registered_formats() );
		}
	}
}
