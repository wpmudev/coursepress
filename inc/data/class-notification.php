<?php
/**
 * Course notification class.
 *
 * @since 2.0
 **/
class CoursePress_Data_Notification {

	/**
	 * Get notifications.
	 */
	public static function get_notifications( $course_ids ) {

		$course_ids = (array) $course_ids;
		$meta_course_ids = array();
		foreach ( $course_ids as $course_id ) {
			$meta_course_ids[] = array(
				'key' => 'alert_course',
				'value' => $course_id,
			);
		}
		$meta_course_ids['relation'] = 'OR';

		// WP Query args.
		$args = array(
			'post_type' => 'cp_notification',
			'meta_query' => $meta_course_ids,
		);

		// Finally get posts.
		return get_posts( $args );
	}
}