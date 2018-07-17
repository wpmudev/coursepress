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

	function get_user_ids( $meta_key ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT `user_id` FROM {$wpdb->usermeta} WHERE `meta_key`=%s", $meta_key );
		$results = $wpdb->get_results( $sql, OBJECT );

		return $results;
	}

	function find_course_instructor( $course_id ) {
		$results = $this->get_user_ids( 'course_' . $course_id );
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$user_id = $result->user_id;
				coursepress_add_course_instructor( $user_id, $course_id );
			}
		}
	}

	function find_course_students( $course_id ) {
		$results = $this->get_user_ids( 'enrolled_course_date_' . $course_id );

		if ( ! empty( $results ) ) {
			// Add user as student
			foreach ( $results as $result ) {
				$user_id = $result->user_id;
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
	}
}
