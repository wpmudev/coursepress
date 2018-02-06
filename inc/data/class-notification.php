<?php
/**
 * Course notification class.
 *
 * @since 2.0
 **/
class CoursePress_Data_Notification {

	/**
	 * Get notification posts.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array Array of posts.
	 */
	public static function get_notifications( $course_id ) {

		$meta_query = array();
		// Do not return anything if course id is empty.
		if ( empty( $course_id ) ) {
			return array();
		}

		$course_id = (int) $course_id;

		// Add each course ids to meta query args.
		$meta_course_id = array(
			'key' => 'alert_course',
			'value' => $course_id,
		);

		// Meta query relation.
		$meta_query['relation'] = 'OR';

		// If available for all courses.
		$meta_query[] = array(
			'key' => 'alert_course',
			'value' => 'all',
		);

		// Receivers meta not set.
		$meta_query[] = array(
			array(
				'key' => 'receivers',
				'compare' => 'NOT EXISTS',
			),
			$meta_course_id,
		);

		// Receivers of type enrolled.
		$meta_query[] = array(
			array(
				'key' => 'receivers',
				'value' => 'enrolled',
			),
			$meta_course_id,
		);

		// Current student.
		$student = coursepress_get_user();
		$completion_status = $student->get_course_completion_status( $course_id );

		// Course passed students.
		if ( $completion_status === 'pass' ) {
			$meta_query[] = array(
				array(
					'key' => 'receivers',
					'value' => 'passed',
				),
				$meta_course_id,
			);
		}

		// Course failed students.
		if ( $completion_status === 'failed' ) {
			$meta_query[] = array(
				array(
					'key' => 'receivers',
					'value' => 'failed',
				),
				$meta_course_id,
			);
		}

		// WP Query args.
		$args = array(
			'post_type' => 'cp_notification',
			'meta_query' => $meta_query,
			'post_per_page' => 20,
		);

		// Finally get notification posts.
		return get_posts( $args );
	}
}