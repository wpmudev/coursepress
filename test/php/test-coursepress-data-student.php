<?php
/**
 * @group coursepress-core
 */
class CoursepressDataStudentTest extends CoursePress_UnitTestCase {

	protected $student_progress;

	public function setUp() {
		parent::setUp();

		$this->student_progress = CoursePress_Data_Student::get_completion_data( $this->student->ID, $this->course->ID );
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

	/**
	 * get_course_enrollment_meta( $user_id )
	 */
	public function test_get_course_enrollment_meta() {
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
	public function test_count_enrolled_courses_ids() {
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
	public function test_meta_key() {
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
	public function test_get_enrolled_courses_ids() {
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
	public function test_is_enrolled_in_course() {
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
	public function test_update_student_data() {
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
	public function test_init_completion_data() {
		$data = array( 'version' => CoursePress::$version );
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
	public function test_get_completion_data() {
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
	public function test_update_completion_data() {
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
		$data['version'] = CoursePress::$version;
		$this->assertEquals( $data, $assert );
	}

	/**
	 *
	 */
	public function test_is_course_complete() {
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
	 * count_course_responses( $student_id, $course_id, $data = false )
	 */
	public function test_count_course_responses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::count_course_responses( $student_id, $course_id );
				$this->assertInternalType( 'integer', $assert );
				$this->assertEquals( 0, $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::count_course_responses( $this->student->ID, $this->course->ID, $this->student_progress );
		$this->assertInternalType( 'integer', $assert );
		$this->assertEquals( 0, $assert );
	}

	/**
	 * average_course_responses( $student_id, $course_id, $data = false )
	 */
	public function test_average_course_responses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::average_course_responses( $student_id, $course_id );
				$this->assertInternalType( 'integer', $assert );
				$this->assertEquals( 0, $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::average_course_responses( $this->student->ID, $this->course->ID, $this->student_progress );
		$this->assertInternalType( 'integer', $assert );
		$this->assertEquals( 0, $assert );
	}

	/**
	 * get_workbook_url( $course_id )
	 */
	public function test_get_workbook_url() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $course_id ) {
			$assert = CoursePress_Data_Student::get_workbook_url( $course_id );
			$this->assertInternalType( 'string', $assert );
			$this->assertEquals( 'workbook/', $assert );
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_workbook_url( $this->course->ID );
		$this->assertInternalType( 'string', $assert );
		$this->assertNotEmpty( $assert );
	}

	/**
	 * get_admin_workbook_link( $student_id, $course_id )
	 */
	public function test_get_admin_workbook_link() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::get_admin_workbook_link( $student_id, $course_id );
				$this->assertInternalType( 'string', $assert );
				$this->assertNotEmpty( $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_admin_workbook_link( $this->student->ID, $this->course->ID );
		$this->assertInternalType( 'string', $assert );
		$this->assertNotEmpty( $assert );
	}

	/**
	 * get_vars( $student_id )
	 */
	public function test_get_vars() {
		$keys = array( 'FIRST_NAME', 'LAST_NAME' );
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			$assert = CoursePress_Data_Student::get_vars( $student_id );
			$this->assertInternalType( 'array', $assert );
			$this->has_keys( $keys, $assert );
			foreach ( $keys as $key ) {
				$this->assertEmpty( $assert[ $key ] );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_vars( $this->student->ID );
		$this->assertInternalType( 'array', $assert );
		$this->has_keys( $keys, $assert );
		$this->assertEquals( 'Albert', $assert['FIRST_NAME'] );
		$this->assertEquals( 'Einstein', $assert['LAST_NAME'] );
	}

	/**
	 * my_courses( $student_id = 0, $courses = array() )
	 */
	public function test_my_courses() {
		$keys = array( 'current', 'completed', 'future', 'incomplete', 'past' );
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			$assert = CoursePress_Data_Student::my_courses( $student_id );
			$this->assertEmpty( $assert );
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
		$this->has_keys( $keys, $assert );
		CoursePress_Data_Course::withdraw_student( $this->student->ID, $this->course->ID );
	}

	/**
	 * log_student_activity( $kind = 'login', $user_id = 0 )
	 */
	public function test_log_student_activity() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $kind ) {
			foreach ( $values as $user_id ) {
				CoursePress_Data_Student::log_student_activity( $kind, $user_id );
			}
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
	 * course_id_from_meta( $meta_value )
	 */
	public function test_course_id_from_meta() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $value ) {
			$assert = CoursePress_Data_Student::course_id_from_meta( $value );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
		/**
		 * Good data
		 */
		$values = 'enrolled_course_date_'.$this->course->ID;
		$assert = CoursePress_Data_Student::course_id_from_meta( $value );
		$this->assertInternalType( 'boolean', $assert );
		$this->assertFalse( $assert );
	}
	/**
	 * visited_page( $student_id, $course_id, $unit_id, $page, &$data = false )
	 */
	public function test_visited_page() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::visited_page( $student_id, $course_id, $unit_id, 1 );
					$this->assertInternalType( 'array', $assert );
					$this->assertEquals( array(), $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::visited_page( $this->student->ID, $this->course->ID, $unit->ID, 1, $data );
			$this->assertInternalType( 'array', $assert );
			$this->assertArrayHasKey( 'units', $assert );
		}
	}

	/**
	 * visited_module( $student_id, $course_id, $unit_id, $module_id, &$data = false )
	 */
	public function test_visited_module() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					foreach ( $values as $module_id ) {
						$assert = CoursePress_Data_Student::visited_module( $student_id, $course_id, $unit_id, $module_id );
						$this->assertInternalType( 'array', $assert );
						$this->assertEquals( array(), $assert );
					}
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Student::visited_module( $this->student->ID, $this->course->ID, $module->post_parent, $module->ID, $data );
			$this->assertInternalType( 'array', $assert );
			$this->assertArrayHasKey( 'completion', $assert );
		}
	}

	/**
	 * get_responses( $student_id, $course_id, $unit_id, $module_id, $response_only = false, &$data = false )
	 */
	public function test_get_responses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					foreach ( $values as $module_id ) {
						$assert = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id );
						$this->assertInternalType( 'array', $assert );
						$this->assertEquals( array(), $assert );
						$assert = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id, true );
						$this->assertInternalType( 'array', $assert );
						$this->assertEquals( array(), $assert );
					}
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		$assert = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id );

		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Student::get_responses( $this->student->ID, $this->course->ID, $module->post_parent, $module->ID, false, $data );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( array(), $assert );
			$assert = CoursePress_Data_Student::get_responses( $this->student->ID, $this->course->ID, $module->post_parent, $module->ID, true, $data );
			$this->assertInternalType( 'array', $assert );
			$this->assertEquals( array(), $assert );
		}
	}

	/**
	* get_grade( $student_id, $course_id, $unit_id, $module_id, $response_index = false, $grade_index = false, &$data = false )
	 */
	public function test_get_grade() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					foreach ( $values as $module_id ) {
						$assert = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $module_id );
						$this->assertInternalType( 'array', $assert );
						$this->assertEquals( array(), $assert );
					}
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		$assert = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id );

		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			foreach ( array( true, false ) as $response_index ) {
				foreach ( array( true, false ) as $grade_index ) {
					$assert = CoursePress_Data_Student::get_grade( $this->student->ID, $this->course->ID, $module->post_parent, $module->ID, $response_index, $grade_index, $data );
					$this->assertInternalType( 'array', $assert );
					$this->assertEquals( array(), $assert );
				}
			}
		}
	}

	/**
	 * get_response( $student_id, $course_id, $unit_id, $module_id, $response_index = false, &$data = false )
	 */
	public function test_get_response() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					foreach ( $values as $module_id ) {
						foreach ( array( true, false ) as $response_index ) {
							$assert = CoursePress_Data_Student::get_response( $student_id, $course_id, $unit_id, $module_id, $response_index );
							$this->assertInternalType( 'boolean', $assert );
							$this->assertFalse( $assert );
						}
					}
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			foreach ( array( true, false ) as $response_index ) {
				$assert = CoursePress_Data_Student::get_response( $this->student->ID, $this->course->ID, $module->post_parent, $module->ID, $response_index, $data );
				$this->assertInternalType( 'boolean', $assert );
				$this->assertFalse( $assert );
			}
		}
	}

	/**
	 * get_feedback( $student_id, $course_id, $unit_id, $module_id, $response_index = false, $feedback_index = false, &$data = false)
	 */
	public function test_get_feedback() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					foreach ( $values as $module_id ) {
						foreach ( array( true, false ) as $response_index ) {
							foreach ( array( true, false ) as $feedback_index ) {
								$assert = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, $response_index, $feedback_index );
								$this->assertInternalType( 'boolean', $assert );
								$this->assertFalse( $assert );
							}
						}
					}
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		$assert = CoursePress_Data_Student::get_responses( $student_id, $course_id, $unit_id, $module_id );

		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			foreach ( array( true, false ) as $response_index ) {
				foreach ( array( true, false ) as $feedback_index ) {
					$assert = CoursePress_Data_Student::get_feedback( $student_id, $course_id, $unit_id, $module_id, $response_index, $feedback_index, $data );
					$this->assertInternalType( 'boolean', $assert );
					$this->assertFalse( $assert );
				}
			}
		}
	}

	/**
	 * get_calculated_completion_data( $student_id, $course_id, &$student_progress = false )
	 */
	public function test_get_calculated_completion_data() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
				$this->assertInternalType( 'array', $assert );
				$this->assertEquals( array(), $assert );
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		$assert = CoursePress_Data_Student::get_calculated_completion_data( $this->student->ID, $this->course->ID, $data );
		$this->assertInternalType( 'array', $assert );
		$this->assertEquals( array(), $assert );
	}

	/**
	 * get_mandatory_completion( $student_id, $course_id, $unit_id, &$data = false )
	 */
	public function test_get_mandatory_completion() {
		$keys = array( 'required', 'completed' );
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::get_mandatory_completion( $student_id, $course_id, $unit_id );
					$this->assertInternalType( 'array', $assert );
					$this->has_keys( $keys, $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::get_mandatory_completion( $this->student->ID, $this->course->ID, $unit->ID, $data );
			$this->assertInternalType( 'array', $assert );
			$this->has_keys( $keys, $assert );
		}
	}

	/**
	 * get_unit_progress( $student_id, $course_id, $unit_id, &$data = false )
	 */
	public function test_get_unit_progress() {
		$keys = array( 'required', 'completed' );
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::get_unit_progress( $student_id, $course_id, $unit_id );
					$this->assertInternalType( 'integer', $assert );
					$this->assertEquals( 0, $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::get_unit_progress( $this->student->ID, $this->course->ID, $unit->ID, $data );
			$this->assertInternalType( 'integer', $assert );
			$this->assertEquals( 0, $assert );
		}
	}

	/**
	 * get_course_progress( $student_id, $course_id, &$data = false )
	 */
	public function test_get_course_progress() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::get_course_progress( $student_id, $course_id );
				$this->assertInternalType( 'integer', $assert );
				$this->assertEquals( 0, $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_course_progress( $this->student->ID, $this->course->ID, $this->student_progress );
		$this->assertInternalType( 'integer', $assert );
		$this->assertEquals( 0, $assert );
	}

	/**
	 * is_mandatory_done( $student_id, $course_id, $unit_id, &$data = false )
	 */
	public function test_is_mandatory_done() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::is_mandatory_done( $student_id, $course_id, $unit_id );
					$this->assertInternalType( 'boolean', $assert );
					$this->assertFalse( $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::is_mandatory_done( $this->student->ID, $this->course->ID, $unit->ID, $data );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
	}

	/**
	  is_unit_complete( $student_id, $course_id, $unit_id, &$data = false )*
	 */
	public function test_is_unit_complete() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::is_unit_complete( $student_id, $course_id, $unit_id );
					$this->assertInternalType( 'boolean', $assert );
					$this->assertFalse( $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::is_unit_complete( $this->student->ID, $this->course->ID, $unit->ID, $data );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
	}
	/**
	 * get_all_unit_progress( $student_id, $course_id, $unit_id, &$data = false )
	 */
	public function test_get_all_unit_progress() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::get_all_unit_progress( $student_id, $course_id, $unit_id );
					$this->assertInternalType( 'integer', $assert );
					$this->assertEquals( 100, $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		$data = $this->student_progress;
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::get_all_unit_progress( $this->student_id, $this->course_id, $unit_id, $data );
			$this->assertInternalType( 'integer', $assert );
			$this->assertEquals( 100, $assert );
		}
	}

	/**
	 * is_section_seen( $course_id, $unit_id, $page, $student_id = 0 )
	 */
	public function test_is_section_seen() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					$assert = CoursePress_Data_Student::is_section_seen( $course_id, $unit_id, 1, $student_id );
					$this->assertInternalType( 'boolean', $assert );
					$this->assertFalse( $assert );
				}
			}
		}
		/**
		 * Good data
		 */
		foreach ( $this->course->units as $unit ) {
			$assert = CoursePress_Data_Student::is_section_seen( $this->course->ID, $unit->ID, 1, $this->student->ID );
			$this->assertInternalType( 'boolean', $assert );
			$this->assertFalse( $assert );
		}
	}

	/**
	 * is_module_completed( $course_id, $unit_id, $module_id, $student_id = 0 )
	 */
	public function test_is_module_completed() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				foreach ( $values as $unit_id ) {
					foreach ( $values as $module_id ) {
						$assert = CoursePress_Data_Student::is_module_completed( $course_id, $unit_id, $module_id, $student_id );
						$this->assertInternalType( 'boolean', $assert );
						$this->assertFalse( $assert );
					}
				}
			}
		}
		/**
		 * Good data
		 */
		$modules = $this->get_modules();
		foreach ( $modules as $module ) {
			$assert = CoursePress_Data_Student::is_module_completed( $this->course->ID, $module->post_parent, $module->ID, $this->student->ID );
			$this->assertInternalType( 'boolean', $assert );
		}
	}

	/**
	 * get_course_status( $course_id, $student_id = 0 )
	 */
	public function test_get_course_status() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $course_id ) {
				$assert = CoursePress_Data_Student::get_course_status( $course_id, $student_id );
				$this->assertInternalType( 'string', $assert );
				$this->assertEquals( __( 'Incomplete', 'CP_TD' ), $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_course_status( $this->course->ID, $this->student->ID );
		$this->assertInternalType( 'string', $assert );
		$this->assertEquals( __( 'Ongoing', 'CP_TD' ), $assert );
	}

	/**
	 * remove_from_all_courses( $student_id )
	 */
	public function test_remove_from_all_courses() {
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			CoursePress_Data_Student::remove_from_all_courses( $student_id );
		}
		/**
		 * Good data
		 */
		CoursePress_Data_Student::remove_from_all_courses( $this->student->ID );
	}

	/**
	 * withdraw_from_course()
	 */
	public function test_withdraw_from_course() {
		/**
		 * Can't test this functions it is ended by exit.
		 */
	}

	/**
	 * get_nonce_action( $action, $student_id = 0 )
	 */
	public function test_get_nonce_action() {
		$re = '/^CoursePress_Data_Student_(foo)?_0_0$/';
		/**
		 * Wrong data
		 */
		$values = $this->get_wrong_values();
		foreach ( $values as $student_id ) {
			foreach ( $values as $action ) {
				$assert = CoursePress_Data_Student::get_nonce_action( $action, $student_id );
				$this->assertInternalType( 'string', $assert );
				$this->assertRegExp( $re, $assert );
			}
		}
		/**
		 * Good data
		 */
		$assert = CoursePress_Data_Student::get_nonce_action( 'foo', $this->student->ID );
		$this->assertInternalType( 'string', $assert );
		$this->assertInternalType( 'string', $assert );
		$this->assertEquals( 'CoursePress_Data_Student_foo_0_'.$this->student->ID, $assert );
	}

	/**
	 * module_response( $student_id, $course_id, $unit_id, $module_id, $response, &$data = false )
	 *
	 * TODO
	 */
	public function test_module_response() {
	}

	/**
	 * TODO
	 * record_grade( $student_id, $course_id, $unit_id, $module_id, $grade, $response_index = false, &$data = false)
	 */
	public function test_record_grade() {
	}

	/**
	 * TODO
	 * record_feedback( $student_id, $course_id, $unit_id, $module_id, $feedback_new, $response_index = false, &$data = false, $is_draft = false)
	 */
	public function test_record_feedback() {
	}
}
