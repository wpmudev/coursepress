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
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT `user_id` FROM {$wpdb->user_meta} WHERE `meta_key`=%s", 'enrolled_course_date_' . $course_id );
		$user_ids = $wpdb->get_results( $sql, OBJECT );

		if ( ! empty( $user_ids ) ) {
			// Add user as student
			foreach ( $user_ids as $user_id ) {
				$user = coursepress_get_user( $user_id );

				if ( $user->is_enrolled_at( $course_id ) ) {
					// Don't add if already added
					continue;
				}

				coursepress_add_student( $user_id, $course_id );

				// Get completion data
				$progress = get_user_option( 'course_' . $course_id . '_progress', $user_id );

				if ( $progress ) {
					$user->add_student_progress( $course_id, $progress );
				}
			}
		}

		/*
		$user_ids = get_users( array(
			'meta_key' => 'enrolled_course_date_' . $course_id,
			'fields' => 'ids',
		) );

		//error_log( 'students:' );
		//error_log( print_r( $user_ids, true ) );

		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				$user = coursepress_get_user( $user_id );
				coursepress_add_student( $user_id, $course_id );

				// Get completion data
				$progress = get_user_option( 'course_' . $course_id . '_progress', $user_id );

				if ( $progress ) {
					$user->add_student_progress( $course_id, $progress );
				}
			}
		}
		*/
	}
}