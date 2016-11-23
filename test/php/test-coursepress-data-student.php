<?php
/**
 * @group coursepress-core
 */
class CoursepressDataStudentTest extends CoursePress_UnitTestCase {

	public function __construct() {
		parent::__construct();
	}

	public function xxxx_exists() {

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

	/**
	 * get_course_enrollment_meta( $user_id )
	 */
	public function xxxx_get_course_enrollment_meta() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Student::get_course_enrollment_meta( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( array(), $assert );
		}
		/**
		 * Good data
		 */
		$value = $this->student->ID;
		$assert = CoursePress_Data_Student::get_course_enrollment_meta( $value );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
	}

	/**
	 * count_enrolled_courses_ids( $student_id, $refresh = false )
	 */
	public function xxxx_count_enrolled_courses_ids() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Student::count_enrolled_courses_ids( $value );
			$this->assertEquals( 0, $assert );
		}

		/**
		 * Good data
		 */

		$this->assertEquals( 0, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID ) );
		$this->assertEquals( 0, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, true ) );
		$this->assertEquals( 0, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, false ) );
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$this->assertEquals( 1, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID ) );
		$this->assertEquals( 1, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, true ) );
		$this->assertEquals( 1, CoursePress_Data_Student::count_enrolled_courses_ids( $this->student->ID, false ) );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
	}

	/**
	 * meta_key( $key )
	 */
	public function xxxx_meta_key() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Student::meta_key( $value );
			$this->assertInternalType( 'string', $assert );
			$this->assertEquals( '', $assert );
		}
		/**
		 * Good data
		 */
		$value = array( 'meta_key' => 'foo' );
		$assert = CoursePress_Data_Student::meta_key( $value );
		$this->assertInternalType( 'string', $assert );
		$this->assertEquals( $value['meta_key'], $assert );
	}

	/**
	 * get_enrolled_courses_ids( $student_id )
	 */
	public function xxxx_get_enrolled_courses_ids() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Student::get_enrolled_courses_ids( $value );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( array(), $assert );
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_enrolled_courses_ids( $this->student->ID );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );

		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$assert = CoursePress_Data_Student::get_enrolled_courses_ids( $this->student->ID );
		$assert = array_shift( $assert );
		$this->assertEquals( $this->course->ID, $assert );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
	}

	/**
	 *
	 */
	public function xxxx_is_enrolled_in_course() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Course::enroll_student( $student_id, $course_id );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertFalse( $assert );
			}
		}
		/**
		 * Good data
		 */
		CoursePress_Data_Course::enroll_student( $this->student->ID, $this->course->ID );
		$this->assertTrue( CoursePress_Data_Student::is_enrolled_in_course( $this->student->ID, $this->course->ID ) );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
		$this->assertFalse( CoursePress_Data_Student::is_enrolled_in_course( $this->student->ID, $this->course->ID ) );
	}

	/**
	 * update_student_data( $student_id, $student_data )
	 */
	public function xxxx_update_student_data() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $student_data ) {
				$assert = CoursePress_Data_Student::update_student_data( $student_id, $student_data );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertFalse( $assert );
			}
		}
		/**
		 * Good data
		 */
		$url = 'http://iworks.pl/';
		$student_data = array( 'user_url' => $url );

		$assert = get_the_author_meta( 'url', $this->student->ID );
		$this->assertNotEquals( $url, $assert );

		$assert = CoursePress_Data_Student::update_student_data( $this->student->ID, $student_data );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertTrue( $assert );

		$assert = get_the_author_meta( 'url', $this->student->ID );
		$this->assertEquals( $url, $assert );

		$assert = CoursePress_Data_Student::update_student_data( $this->student->ID, array( 'user_url' => 'https://premium.wpmudev.or/' ) );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertTrue( $assert );
	}

	/**
	 * init_completion_data( $student_id, $course_id )
	 */
	public function xxxx_init_completion_data() {
		$data = array( 'version' => '2.0' );
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::init_completion_data( $student_id, $course_id );
				$this->assertInternalType( 'array', $assert );
				$this->assertEquals( $data, $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::init_completion_data( $this->student->ID, $this->course->ID );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( $data, $assert );
	}

	/**
	 * get_completion_data( $student_id, $course_id )
	 */
	public function xxxx_get_completion_data() {
		$data = array();
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
				$this->assertInternalType( 'array', $assert );
				$this->assertEquals( $data, $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_completion_data( $this->student->ID, $this->course->ID );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( $data, $assert );
	}

	/**
	 *
	 */
	public function xxxx_update_completion_data() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$data = array( 'foo' => 'bar' );
		CoursePress_Data_Student::update_completion_data( $this->student->ID, $this->course->ID, $data );
		$assert = CoursePress_Data_Student::get_completion_data( $this->student->ID, $this->course->ID );
		$data['version'] = '2.0';
		$this->assertEquals( $data, $assert );
	}

	/**
	 *
	 */
	public function xxxx_is_course_complete() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::is_course_complete( $this->student->ID, $this->course->ID );
		$this->assertFalse( $assert );
		$assert = CoursePress_Data_Student::is_course_complete( $this->student->ID, $this->course->ID );
		$this->assertFalse( $assert );
		$data = array();
		$assert = CoursePress_Data_Student::is_course_complete( $this->student->ID, $this->course->ID, $data );
		$this->assertFalse( $assert );
	}

	/**
	 *
	 */
	public function xxxx_count_course_responses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::count_course_responses( $this->student->ID, $this->course->ID );
		$this->assertEquals( 0, $assert );
		$data = array();
		$assert = CoursePress_Data_Student::count_course_responses( $this->student->ID, $this->course->ID, $data );
		$this->assertEquals( 0, $assert );
	}

	/**
	 *
	 */
	public function xxxx_average_course_responses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::average_course_responses( $this->student->ID, $this->course->ID );
		$this->assertEquals( 0, $assert );
		$data = array();
		$assert = CoursePress_Data_Student::average_course_responses( $this->student->ID, $this->course->ID, $data );
		$this->assertEquals( 0, $assert );
	}

	/**
	 *
	 */
	public function xxxx_get_workbook_url() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_workbook_url( $this->course->ID );
		$this->assertNotEmpty( $assert );
	}

	/**
	 *
	 */
	public function xxxx_get_admin_workbook_link() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_admin_workbook_link( $this->student->ID, $this->course->ID );
		$this->assertNotEmpty( $assert );
	}

	/**
	 *
	 */
	public function xxxx_get_vars() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_vars( $this->student->ID );
		$this->assertEquals( 'Albert', $assert['FIRST_NAME'] );
		$this->assertEquals( 'Einstein', $assert['LAST_NAME'] );
	}

	/**
	 *
	 */
	public function xxxx_my_courses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
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

	/**
	 *
	 */
	public function xxxx_log_student_activity() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
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

	/**
	 * filter_course_meta_array( $var )
	 */
	public function test_filter_course_meta_array() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Student::filter_course_meta_array( $value );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
		/**
		 * Good data
		 */
		$values = 'enrolled_course_date_'.$this->course->ID;
		$assert = CoursePress_Data_Student::filter_course_meta_array( $value );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertFalse( $assert );
	}
	/**
	 *
	 */
	public function xxxx_course_id_from_meta() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_visited_page() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_visited_module() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_module_response() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_responses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_grade() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_record_grade() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_response() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_feedback() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_record_feedback() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_calculated_completion_data() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_mandatory_completion() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_unit_progress() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_course_progress() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_is_mandatory_done() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_is_unit_complete() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_all_unit_progress() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_is_section_seen() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_is_module_completed() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_course_status() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_remove_from_all_courses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_withdraw_from_course() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
	/**
	 *
	 */
	public function xxxx_get_nonce_action() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
		}
		/**
		 * Good data
		 */
	}
}
/*

    print_r( array( gettype( $assert) , $assert ) );
 */
