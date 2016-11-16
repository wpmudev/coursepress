<?php
/**
 * @group coursepress-core
 */
class CoursepressDataStudentTest extends WP_UnitTestCase {

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
		$this->course = $helper->get_course( $this->instructor->ID );
	}

	public function test_exists() {

		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_course_enrollment_meta' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'filter_course_meta_array' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'course_id_from_meta' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'count_enrolled_courses_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'meta_key' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_enrolled_courses_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'is_enrolled_in_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'update_student_data' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'init_completion_data' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_completion_data' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'update_completion_data' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'visited_page' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'visited_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'module_response' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_responses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_grade' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'record_grade' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_response' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_feedback' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'record_feedback' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_calculated_completion_data' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_mandatory_completion' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_unit_progress' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_course_progress' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'is_mandatory_done' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'is_unit_complete' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'is_course_complete' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'count_course_responses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'average_course_responses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'send_registration' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_workbook_url' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_admin_workbook_link' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_all_unit_progress' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'is_section_seen' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'is_module_completed' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_vars' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'my_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'log_student_activity' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_course_status' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'remove_from_all_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'withdraw_from_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Student', 'get_nonce_action' ) ) );
	}

	public function test_get_course_enrollment_meta() {
		$this->assertEquals( array(), CoursePress_Data_Student::get_course_enrollment_meta( $this->student->ID ) );
	}

	public function test_count_enrolled_courses_ids() {
		$this->assertEquals( 0, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID ) );
		$this->assertEquals( 0, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, true ) );
		$this->assertEquals( 0, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, false ) );
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$this->assertEquals( 1, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID ) );
		$this->assertEquals( 1, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, true ) );
		$this->assertEquals( 1, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, false ) );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
	}

	public function test_meta_key() {
		$key = array( 'meta_key' => 'foo' );
		$this->assertEquals( 'foo', CoursePress_Data_Student::meta_key( $key ) );
	}

	public function test_get_enrolled_courses_ids() {
		$this->assertEquals( array(), CoursePress_Data_Student::get_enrolled_courses_ids( $this->student->ID ) );
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$assert = CoursePress_Data_Student::get_enrolled_courses_ids( $this->student->ID );
		$assert = array_shift( $assert );
		$this->assertEquals( $this->course->ID, $assert );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
	}

	public function test_is_enrolled_in_course() {
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$this->assertTrue( CoursePress_Data_Student::is_enrolled_in_course( $this->student->ID, $this->course->ID ) );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
		$this->assertFalse( CoursePress_Data_Student::is_enrolled_in_course( $this->student->ID, $this->course->ID ) );
	}

	public function test_update_student_data() {
		$url = 'http://iworks.pl/';
		$this->assertNotEquals( $url, get_the_author_meta( 'url', $this->student->ID ) );
		$this->assertTrue( CoursePress_Data_Student::update_student_data( $this->student->ID, array( 'user_url' => $url ) ) );
		$this->assertEquals( $url, get_the_author_meta( 'url', $this->student->ID ) );
		$this->assertTrue( CoursePress_Data_Student::update_student_data( $this->student->ID, array( 'user_url' => 'https://premium.wpmudev.or/' ) ) );
	}

	public function test_init_completion_data() {
		$data = array( 'version' => '2.0' );
		$assert = CoursePress_Data_Student::init_completion_data( $this->student->ID, $this->course->ID );
		$this->assertEquals( $data, $assert );
	}

	public function test_get_completion_data() {
		$data = array();
		$assert = CoursePress_Data_Student::get_completion_data( $this->student->ID, $this->course->ID );
		$this->assertEquals( $data, $assert );
	}

	public function test_update_completion_data() {
		$data = array( 'foo' => 'bar' );
		CoursePress_Data_Student::update_completion_data( $this->student->ID, $this->course->ID, $data );
		$assert = CoursePress_Data_Student::get_completion_data( $this->student->ID, $this->course->ID );
		$data['version'] = '2.0';
		$this->assertEquals( $data, $assert );
	}

	public function test_is_course_complete() {
		$assert = CoursePress_Data_Student::is_course_complete( $this->student->ID, $this->course->ID );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Student::is_course_complete( $this->student->ID, $this->course->ID );
		$this->assertFalse( $assert );
		$data = array();
		$assert = CoursePress_Data_Student::is_course_complete( $this->student->ID, $this->course->ID, $data );
		$this->assertFalse( $assert );
	}

	public function test_count_course_responses() {
		$assert = CoursePress_Data_Student::count_course_responses( $this->student->ID, $this->course->ID );
		$this->assertEquals( 0, $assert );
		$data = array();
		$assert = CoursePress_Data_Student::count_course_responses( $this->student->ID, $this->course->ID, $data );
		$this->assertEquals( 0, $assert );
	}

	public function test_average_course_responses() {
		$assert = CoursePress_Data_Student::average_course_responses( $this->student->ID, $this->course->ID );
		$this->assertEquals( 0, $assert );
		$data = array();
		$assert = CoursePress_Data_Student::average_course_responses( $this->student->ID, $this->course->ID, $data );
		$this->assertEquals( 0, $assert );
	}

	public function test_get_workbook_url() {
		$assert = CoursePress_Data_Student::get_workbook_url( $this->course->ID );
		$this->assertNotEmpty( $assert );
	}

	public function test_get_admin_workbook_link() {
		$assert = CoursePress_Data_Student::get_admin_workbook_link( $this->student->ID, $this->course->ID );
		$this->assertNotEmpty( $assert );
	}

	public function test_get_vars() {
		$assert = CoursePress_Data_Student::get_vars( $this->student->ID );
		$this->assertEquals( 'Albert', $assert['FIRST_NAME'] );
		$this->assertEquals( 'Einstein', $assert['LAST_NAME'] );
	}

	public function test_my_courses() {
		$assert = CoursePress_Data_Student::my_courses();
		$this->assertEmpty( $assert );
		$assert = CoursePress_Data_Student::my_courses( $this->student->ID );
		$this->assertEmpty( $assert );
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$assert = CoursePress_Data_Student::my_courses( $this->student->ID );
		$this->assertNotEmpty( $assert );
		$this->assertCount( 5, $assert );
		$keys = array(
			'completed',
			'incomplete',
			'future',
			'past',
		);
		foreach ( $keys as $key ) {
			$this->assertEquals( array(), $assert[ $key ] );
		}
		$this->assertNotEmpty( $assert['current'] );
		$this->assertCount( 1, $assert['current'] );
		$this->assertContainsOnlyInstancesOf( 'WP_Post',  $assert['current'] );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
	}

	public function test_log_student_activity() {
		$allowed_kinds = array(
			'course_module_seen',
			'course_seen',
			'course_unit_seen',
			'enrolled',
			'login',
			'module_answered',
		);
		foreach ( $allowed_kinds as $kind ) {
			CoursePress_Data_Student::log_student_activity( $kind, $this->student->ID );
			$assert = get_user_meta( $this->student->ID, 'latest_activity_kind', true );
			$this->assertEquals( $kind, $assert );
		}

		$kind = 'not allowed random kinds';
		CoursePress_Data_Student::log_student_activity( $kind, $this->student->ID );
		$assert = get_user_meta( $this->student->ID, 'latest_activity_kind', true );
		$this->assertEquals( 'unknown', $assert );
		$this->assertNotEquals( $kind, $assert );
	}
}
