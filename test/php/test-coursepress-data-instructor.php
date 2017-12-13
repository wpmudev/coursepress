<?php
/**
 * @group coursepress-core
 */
class CoursePress_Data_Instructor_Test extends CoursePress_UnitTestCase {

	public function test_exists() {
		$this->assertTrue( class_exists( 'CoursePress_Data_Instructor' ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_first_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_last_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_course_count' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_course_meta_keys' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'filter_course_meta_array' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'filter_by_where' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'filter_by_whereall' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_assigned_courses_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_accessable_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'unassign_from_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'unassign_from_all_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_courses_number' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'is_assigned_to_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'remove_instructor_status' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'delete_instructor' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'instructor_by_hash' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'instructor_by_login' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'create_hash' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_hash' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'count_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'meta_key' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'instructor_key' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'added_to_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'remove_from_all_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'removed_from_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'delete_invitation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'send_invitation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'create_invite_code_hash' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'is_course_invite' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'invitation_data' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'verify_invitation_code' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'add_from_invitation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_students_count' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', '_get_students_count' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'reset_students_count' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Instructor', 'get_nonce_action' ) ) );
	}

	public function test_instructor() {
		$this->assertInstanceOf( 'WP_User', $this->instructor );
		$this->assertEquals( 'Jon', CoursePress_Data_Instructor::get_first_name( $this->instructor ) );
		$this->assertEquals( 'Snow', CoursePress_Data_Instructor::get_last_name( $this->instructor ) );
	}

	public function test_no_courses() {
		$assert = CoursePress_Data_Instructor::get_course_count( $this->instructor );
		if ( ! empty( $assert ) ) {
			return;
		}
		$this->assertEmpty( CoursePress_Data_Instructor::get_course_meta_keys( $this->instructor ) );
		$this->assertEmpty( CoursePress_Data_Instructor::get_assigned_courses_ids( $this->instructor ) );
		$this->assertEmpty( CoursePress_Data_Instructor::get_accessable_courses( $this->instructor ) );
		$statuses = array(
		   'inherit',
		   'publish',
		   'draft',
		   'future',
		   'private',
		);
		foreach ( $statuses as $post_status ) {
			$this->assertEmpty( CoursePress_Data_Instructor::get_assigned_courses_ids( $this->instructor, $post_status ) );
			$this->assertEmpty( CoursePress_Data_Instructor::get_accessable_courses( $this->instructor, $post_status ) );
		}
		$this->assertEmpty( CoursePress_Data_Instructor::count_courses( $this->instructor->ID ) );
		$this->assertEmpty( CoursePress_Data_Instructor::get_courses_number( $this->instructor->ID ) );
		$this->assertFalse( CoursePress_Data_Instructor::is_assigned_to_course( $this->instructor->ID, 0 ) );
		$this->assertEquals( 0, CoursePress_Data_Instructor::get_students_count( $this->instructor->ID ) );
		$this->assertEquals( 0, CoursePress_Data_Instructor::get_students_count( $this->instructor->ID, true ) );
		$this->assertEquals( 0, CoursePress_Data_Instructor::_get_students_count( $this->instructor->ID ) );
	}

	public function test_courses() {
		$this->assertEquals( 1, CoursePress_Data_Instructor::get_course_count( $this->instructor ) );
		$this->assertNotEmpty( CoursePress_Data_Instructor::get_course_meta_keys( $this->instructor ) );
		$assert = CoursePress_Data_Instructor::get_course_meta_keys( $this->instructor );
		$assert = array_shift( $assert );
		$meta = sprintf( 'course_%d', $this->course->ID );
		$this->assertEquals( $meta, $assert );
		$assert = CoursePress_Data_Instructor::get_assigned_courses_ids( $this->instructor );
		$this->assertNotEmpty( $assert );
		$this->assertEquals( array( $this->course->ID ), $assert );
		$assert = CoursePress_Data_Instructor::get_assigned_courses_ids( $this->instructor, 'publish' );
		$this->assertEquals( array( $this->course->ID ), $assert );
		/**
		 * coursepress_update_my_course_cap
		 * it should empty
		 */
		$this->instructor->add_cap( 'coursepress_update_my_course_cap' );
		$statuses = array(
		   'inherit',
		   'private',
		   'draft',
		   'future',
		);
		foreach ( $statuses as $post_status ) {
			$this->assertEquals( array(), CoursePress_Data_Instructor::get_assigned_courses_ids( $this->instructor, $post_status ) );
			$this->assertEmpty( CoursePress_Data_Instructor::get_accessable_courses( $this->instructor, $post_status ) );
		}
		$this->assertEquals( 1, CoursePress_Data_Instructor::count_courses( $this->instructor->ID ) );
		$this->assertEquals( 1, CoursePress_Data_Instructor::get_courses_number( $this->instructor->ID ) );
		$this->assertTrue( CoursePress_Data_Instructor::is_assigned_to_course( $this->instructor->ID, $this->course->ID ) );
	}

	public function test_course_with_student() {
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$this->assertEquals( 0, CoursePress_Data_Instructor::get_students_count( $this->instructor->ID ) );
		$this->assertEquals( 0, CoursePress_Data_Instructor::_get_students_count( $this->instructor->ID ) );
	}

	public function test_hash() {
		CoursePress_Data_Instructor::create_hash( $this->instructor );
		$hash = CoursePress_Data_Instructor::get_hash( $this->instructor );
		$this->assertNotEmpty( $hash );
		$instructor = CoursePress_Data_Instructor::instructor_by_hash( $hash );
		$this->assertEquals( $this->instructor->ID, $instructor->ID );
	}

	public function test_instructor_by_login() {
		$instructor = CoursePress_Data_Instructor::instructor_by_login( 'instructor' );
		$this->assertEquals( $this->instructor->ID, $instructor->ID );
	}

	public function test_instructor_key() {
		$this->assertFalse( CoursePress_Data_Instructor::instructor_key( 'foo_progress' ) );
		$this->assertTrue( CoursePress_Data_Instructor::instructor_key( 'foo' ) );
	}

	public function test_meta_key() {
		$meta = array( 'meta_key' => 'foo' );
		$this->assertEquals( 'foo', CoursePress_Data_Instructor::meta_key( $meta ) );
	}

	public function test_get_nonce_action() {
		$assert = CoursePress_Data_Instructor::get_nonce_action( 'foo' );
		$this->assertEquals( 'CoursePress_Data_Instructor_foo_0_0', $assert );
	}

	public function test_create_invite_code_hash() {
		$args = array(
			'email' => 'admin@example.com',
		);
		$assert = CoursePress_Data_Instructor::create_invite_code_hash( $args );
		$this->assertNotEmpty( $assert['code'] );
		$this->assertNotEmpty( $assert['hash'] );
		$hash = sha1( sanitize_email( $args['email'] ) . $assert['code'] );
		$this->assertEquals( $hash, $assert['hash'] );
	}

	public function test_course_invite() {
		$this->assertFalse( CoursePress_Data_Instructor::is_course_invite() );
		$this->assertFalse( CoursePress_Data_Instructor::verify_invitation_code( $this->course->ID, '1' ) );
		$this->assertFalse( CoursePress_Data_Instructor::add_from_invitation( $this->course->ID, $this->instructor->ID, false ) );
	}
}

