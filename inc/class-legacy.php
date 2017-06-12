<?php
/**
 * Class CoursePress_Legacy
 *
 * Note*: Legacy only
 */
class CoursePress_Legacy {
	static function instance() {
		return new self();
	}

	public function __construct() {
		$this->set_user_roles();
		$this->find_courses();

		flush_rewrite_rules();
	}

	function set_user_roles() {
		add_role( 'coursepress_student', __( 'Course Student', 'cp' ) );
		add_role( 'coursepress_instructor', __( 'Course Instructor', 'cp' ) );
		add_role( 'coursepress_facilitator', __( 'Course Facilitator', 'cp' ) );
	}

	function find_courses() {
		$args = array(
			'post_type' => 'course',
			'post_status' => 'any',
			'fields' => 'ids',
			'suppress_filters' => true,
			'posts_per_page' => -1,
		);
		$courses = get_posts( $args );

		if ( ! empty( $courses ) ) {
			foreach ( $courses as $course_id ) {
				$this->find_course_instructor( $course_id );
				$this->find_course_students( $course_id );
			}
		}
	}

	function find_course_instructor( $course_id ) {
		$user_ids = get_users( array(
			'meta_key' => 'course_' . $course_id,
			'meta_value' => $course_id,
			'fields' => 'ids',
		) );

		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				coursepress_add_course_facilitator( $user_id, $course_id );
			}
		}
	}

	function find_course_students( $course_id ) {
		$user_ids = get_users( array(
			'meta_key' => 'enrolled_course_date_' . $course_id,
			//'meta_value' => $course_id,
			'fields' => 'ids',
		) );

		//error_log( 'students:' );
		//error_log( print_r( $user_ids, true ) );

		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				// Marked user as student of the course
				add_post_meta( $course_id, 'student', $user_id );

				/**
				 * Fired whenever a new student is added to a course.
				 *
				 * @since 3.0
				 * @param int $user_id
				 * @param int $course_id
				 */
				do_action( 'coursepress_add_student', $user_id, $course_id );
			}
		}
	}
}