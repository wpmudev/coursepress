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
	 * @param array $course_ids Course IDs.
	 *
	 * @return array Array of posts.
	 */
	public static function get_notifications( $course_ids ) {

		$course_ids = (array) $course_ids;
		$meta_course_ids = array();
		// Do not return anything if course id is empty.
		if ( empty( $course_ids ) ) {
			return $meta_course_ids;
		}

		// Add each course ids to meta query args.
		foreach ( $course_ids as $course_id ) {
			$meta_course_ids[] = array(
				'key' => 'alert_course',
				'value' => $course_id,
			);
		}
		// Meta query relation.
		$meta_course_ids['relation'] = 'OR';

		// @todo Add receivers if required.

		// WP Query args.
		$args = array(
			'post_type' => 'cp_notification',
			'meta_query' => $meta_course_ids,
			'post_per_page' => 20,
		);

		// Finally get notification posts.
		return get_posts( $args );
	}
}