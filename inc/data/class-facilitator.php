<?php

class CoursePress_Data_Facilitator {

	/**
	 * Get courses where user is a facilitator.
	 *
	 * @param int $user_id
	 * @param array $status
	 * @param bool $ids_only
	 * @param int $page
	 * @param int $per_page
	 *
	 * @return array
	 */
	public static function get_facilitated_courses( $user_id = 0, $status = array( 'publish' ), $ids_only = false, $page = 0, $per_page = 20 ) {

		if ( empty( $user_id ) ) {
			$user_id = coursepress_get_user_id();
		}

		$args = array(
			'post_type' => 'course',
			'post_status' => $status,
			'meta_key' => 'facilitator',
			'meta_value' => $user_id,
			'meta_compare' => 'IN',
			'suppress_filters' => true,
		);

		if ( 0 < $per_page ) {
			$args['paged'] = $page;
			$args['posts_per_page'] = $per_page;
		} else {
			$args['nopaging'] = true;
		}

		if ( $ids_only ) {
			$args['fields'] = 'ids';
		}

		$courses = get_posts( $args );

		return $courses;
	}
}
