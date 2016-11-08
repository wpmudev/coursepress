<?php
/**
 * @group coursepress-core
 */
class CoursepressTest  extends WP_UnitTestCase {
	
	public function test_main_class_exists () {
		$this->assertTrue(class_exists('CoursePress_Core'));
	}
	
}
