<?php

class CoursePress_Data_Notification {

	private static $post_type = 'notifications';  // Plural because of legacy

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Notifications', 'cp' ),
					'singular_name' => __( 'Notification', 'cp' ),
					'add_new' => __( 'Create New', 'cp' ),
					'add_new_item' => __( 'Create New Notification', 'cp' ),
					'edit_item' => __( 'Edit Notification', 'cp' ),
					'edit' => __( 'Edit', 'cp' ),
					'new_item' => __( 'New Notification', 'cp' ),
					'view_item' => __( 'View Notification', 'cp' ),
					'search_items' => __( 'Search Notifications', 'cp' ),
					'not_found' => __( 'No Notifications Found', 'cp' ),
					'not_found_in_trash' => __( 'No Notifications found in Trash', 'cp' ),
					'view' => __( 'View Notification', 'cp' ),
				),
				'public' => false,
				'show_ui' => false,
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
		$course_title = ! empty( $course_id ) ? get_the_title( $course_id ) : __( 'All courses', 'cp' );
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
			'post_per_page' => 20,
		);

		return get_posts( $args );

	}
}
