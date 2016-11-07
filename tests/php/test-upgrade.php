<?php
/**
 * Test upgrade cycle
 **/
class CoursePressUpgradeTest extends WP_UnitTestCase {
	public function testVersionSwitch() {
		$this->assertThat( CoursePressUpgrade::check_old_courses(), true, $this->assertTrue( '1.x', CoursePressUpgrade::$current_version ) );
	}
}
