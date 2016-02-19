<?php

class CoursePress_Data_Discussion {

	private static $post_type = 'discussions';  // Plural because of legacy
	public static $last_discussion;

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Discussions', 'CP_TD' ),
					'singular_name' => __( 'Discussion', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Discussion', 'CP_TD' ),
					'edit_item' => __( 'Edit Discussion', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Discussion', 'CP_TD' ),
					'view_item' => __( 'View Discussion', 'CP_TD' ),
					'search_items' => __( 'Search Discussions', 'CP_TD' ),
					'not_found' => __( 'No Discussions Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Discussions found in Trash', 'CP_TD' ),
					'view' => __( 'View Discussion', 'CP_TD' ),
				),
				'public' => false,
				'show_ui' => true,
				'publicly_queryable' => false,
				'capability_type' => 'discussion',
				'map_meta_cap' => true,
				'query_var' => true,
				// 'rewrite' => array(
				// 'slug' => trailingslashit( CoursePress_Core::get_slug( 'course' ) ) . '%course%/' . CoursePress_Core::get_slug( 'discussion' )
				// )
			),
		);

	}

	public static function get_post_type_name( $with_prefix = true ) {
		if ( ! $with_prefix ) {
			return self::$post_type;
		} else {
			$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
			$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';

			return $prefix . self::$post_type;
		}
	}

	public static function attributes( $n_id ) {

		if ( is_object( $n_id ) ) {
			$n_id = $n_id->ID;
		} else {
			$n_id = (int) $n_id;
		}

		$course_id = (int) get_post_meta( $n_id, 'course_id', true );
		$course_title = ! empty( $course_id ) ? get_the_title( $course_id ) : __( 'All courses', 'CP_TD' );
		$course_id = ! empty( $course_id ) ? $course_id : 'all';

		$unit_id = (int) get_post_meta( $n_id, 'unit_id', true );
		$unit_title = ! empty( $unit_id ) ? get_the_title( $unit_id ) : __( 'All units', 'CP_TD' );
		$unit_id = ! empty( $unit_id ) ? $unit_id : 'course';
		$unit_id = 'all' === $course_id ? 'course' : $unit_id;

		return array(
			'course_id' => $course_id,
			'course_title' => $course_title,
			'unit_id' => $unit_id,
			'unit_title' => $unit_title,
		);

	}

	public static function get_discussions( $course ) {

		$course = (array) $course;

		$args = array(
			'post_type' => self::get_post_type_name(),
			'meta_query' => array(
				array(
					'key' => 'course_id',
					'value' => $course,
					'compare' => 'IN',
				),
			),
			'post_per_page' => 20,
		);

		return get_posts( $args );

	}


	// Hook from CoursePress_View_Front
	public static function permalink( $permalink, $post, $leavename ) {

		$x = '';

	}
}
