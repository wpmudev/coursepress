<?php
/**
 * @group coursepress-core
 */
class CoursepressCourseTest extends WP_UnitTestCase {

	private $course_id = 0;
	private $admin;

	public function test_exists() {
		$this->assertTrue( class_exists( 'CoursePress_Data_Course' ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_format' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_taxonomy' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_message' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_default_messages' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'update' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'add_instructor' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'remove_instructor' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_setting' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'update_setting' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'delete_setting' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'set_setting' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'allow_pages' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'upgrade_settings' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_post_type_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_post_category_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_terms' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_terms' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_categories' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_units' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_unit_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_listing_image' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_units_with_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_units_with_modules3' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'uasort_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_unit_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'filter_unit_module_where' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'set_last_course_id' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'last_course_id' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'is_paid_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_users' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_students' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_student_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'count_students' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_certified_student_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'student_enrolled' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'enroll_student' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'withdraw_student' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'withdraw_all_students' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'send_invitation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'is_full' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_time_estimation' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_instructors' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_facilitators' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'structure_visibility' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'previewability' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'can_view_page' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'can_view_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'can_view_unit' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_next_accessible_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_prev_accessible_module' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_navigation_items' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_current_course_id' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'by_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_permalink' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'count_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_url' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'is_course_preview' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'time_now' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'strtotime' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'is_course_available' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'reorder_modules' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_availability_status' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'can_access' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_id' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_courses_by_ids' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'course_class' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_vars' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_units_html_list' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_expired_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_enrollment_ended_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'return_id' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'current_and_upcoming_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'sort_courses' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_course_status' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_enrollment_status' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'check_post_type_by_post' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'save_course_number' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'add_numeric_identifier_to_course_name' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'delete_course_number' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'is_course' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_enrollment_types_array' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_enrollment_type_default' ) ) );
		$this->assertTrue( is_callable( array( 'CoursePress_Data_Course', 'get_defaults_setup_pages_content' ) ) );
	}

	public function test_wp_create_user() {
		wp_create_user( 'instructor', 'instructor', 'instructor@example.com' );
		wp_create_user( 'facilitator', 'facilitator', 'facilitator@example.com' );
		$this->admin = get_user_by( 'login', 'admin' );
		$this->assertEquals( 'admin', $this->admin->user_login );
	}

	public function test_get_format() {
		$stack = CoursePress_Data_Course::get_format();
		$this->assertNotEmpty( $stack );
		$this->assertEquals( 'course', $stack['post_type'] );
	}

	public function test_get_taxonomy() {
		$stack = CoursePress_Data_Course::get_taxonomy();
		$this->assertNotEmpty( $stack );
		$this->assertEquals( 'course_category', $stack['taxonomy_type'] );
		$this->assertEquals( 'course', $stack['post_type'] );
	}

	public function test_course() {
		global $user_id;
		$this->admin = get_user_by( 'login', 'admin' );
		$user_id = $this->admin->ID;
		$course = $this->course();
		$this->course_id = CoursePress_Data_Course::update( false, $course );
		$this->assertNotEmpty( $this->course_id );
		$this->assertTrue( is_numeric( $this->course_id ) );
		$this->assertTrue( CoursePress_Data_Course::is_course( $this->course_id ) );
		$post = get_post( $this->course_id );
		$this->assertEquals( $post->post_type, CoursePress_Data_Course::get_post_type_name() );
		$this->assertEquals( $post->post_author, $course->post_author );
		$this->assertEquals( $post->post_status, $course->post_status );
		$this->assertEquals( $post->post_title, $course->course_name );
		$this->assertEquals( $post->post_excerpt, $course->course_excerpt );
		$this->assertEquals( $post->post_content, $course->course_description );
		$this->assertEquals( $post->ping_status, 'closed' );
		$this->assertEquals( $post->comment_status, 'closed' );

		/**
		 * Settings
		 */
		$stack = CoursePress_Data_Course::update_setting( $this->course_id, 'test_key', 'test_value' );
		$this->assertTrue( $stack );

		$settings = CoursePress_Data_Course::get_setting( $this->course_id );
		$this->assertNotEmpty( $settings );

		$settings = CoursePress_Data_Course::get_setting( $this->course_id, 'test_key' );
		$this->assertEquals( $settings, 'test_value' );

	}

	public function course() {
		$course = array(
			'post_author' => $this->admin->ID,
			'post_status' => 'private',
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'course_excerpt' => 'test course excerpt',
			'course_description' => 'test course content',
			'course_name' => 'test course title',
		);
		return (object) $course;
	}
}

