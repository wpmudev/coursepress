<?php

class CoursePress_Data_Notification {

	private static $post_type = 'notifications';  // Plural because of legacy

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Notifications', 'CP_TD' ),
					'singular_name' => __( 'Notification', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Notification', 'CP_TD' ),
					'edit_item' => __( 'Edit Notification', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Notification', 'CP_TD' ),
					'view_item' => __( 'View Notification', 'CP_TD' ),
					'search_items' => __( 'Search Notifications', 'CP_TD' ),
					'not_found' => __( 'No Notifications Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Notifications found in Trash', 'CP_TD' ),
					'view' => __( 'View Notification', 'CP_TD' ),
				),
				'public' => false,
				'show_ui' => true,
				'publicly_queryable' => false,
				'capability_type' => 'notification',
				'map_meta_cap' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => CoursePress_Core::get_slug( 'course/' ) . '%course%/' . CoursePress_Core::get_slug( 'notification' ),
				),
			),
		);

	}

	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
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

		return array(
			'course_id' => $course_id,
			'course_title' => $course_title,
		);

	}

	public static function get_notifications( $course ) {

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
			'posts_per_page' => 20,
		);

		return get_posts( $args );

	}
}
