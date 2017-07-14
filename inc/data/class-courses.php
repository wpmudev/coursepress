<?php
/**
 * Class CoursePress_Data_Courses
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Courses extends CoursePress_Utility {
	/**
	 * get courses list
	 *
	 * @since 3.0.0
	 */
	public function get_list() {
		global $CoursePress_Core;
		$args = array(
			'post_type' => $CoursePress_Core->course_post_type,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
		);
		$list = array();
		$courses = new WP_Query( $args );
		if ( $courses->have_posts() ) {
			while ( $courses->have_posts() ) {
				$courses->the_post();
				$list[ get_the_ID() ] = get_the_title();
			}
		}
		return $list;
	}

	public function get_units( $course_id, $status = array( 'publish' ), $ids_only = false, $include_count = false ) {
		global $CoursePress_Core;
		/**
		 * TODO Sanitize course_id
		 */

		l( $CoursePress_Core );

		$post_args = array(
			'post_type' => $CoursePress_Core->course_post_type,
			'post_parent' => $course_id,
			'post_status' => $status,
			'posts_per_page' => - 1,
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'meta_key' => 'unit_order',
			'suppress_filters' => true,
		);

		if ( $ids_only ) {
			$post_args['fields'] = 'ids';
		}
		$query = new WP_Query( $post_args );

		if ( $include_count ) {
			// Handy if using pagination.
			return array(
				'units' => $query->posts,
				'found' => $query->found_posts,
			);
		} else {
			return $query->posts;
		}
	}
}
