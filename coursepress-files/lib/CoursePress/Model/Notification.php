<?php

class CoursePress_Model_Notification {

	private static $post_type = 'notifications';  // Plural because of legacy

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels'             => array(
					'name'               => __( 'Notifications', CoursePress::TD ),
					'singular_name'      => __( 'Notification', CoursePress::TD ),
					'add_new'            => __( 'Create New', CoursePress::TD ),
					'add_new_item'       => __( 'Create New Notification', CoursePress::TD ),
					'edit_item'          => __( 'Edit Notification', CoursePress::TD ),
					'edit'               => __( 'Edit', CoursePress::TD ),
					'new_item'           => __( 'New Notification', CoursePress::TD ),
					'view_item'          => __( 'View Notification', CoursePress::TD ),
					'search_items'       => __( 'Search Notifications', CoursePress::TD ),
					'not_found'          => __( 'No Notifications Found', CoursePress::TD ),
					'not_found_in_trash' => __( 'No Notifications found in Trash', CoursePress::TD ),
					'view'               => __( 'View Notification', CoursePress::TD )
				),
				'public'             => false,
				'show_ui'            => true,
				'publicly_queryable' => false,
				'capability_type'    => 'notification',
				'map_meta_cap'       => true,
				'query_var'          => true,
				'rewrite'            => array(
					'slug'  => trailingslashit( CoursePress_Core::get_slug( 'course' ) ) . '%course%/' . CoursePress_Core::get_slug( 'notification' )
				)
			)
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

		if( is_object( $n_id ) ) {
			$n_id = $n_id->ID;
		} else {
			$n_id = (int) $n_id;
		}

		$course_id = (int) get_post_meta( $n_id, 'course_id', true );
		$course_title = ! empty( $course_id ) ? get_the_title( $course_id ) : __( 'All courses', CoursePress::TD );
		$course_id = ! empty( $course_id ) ? $course_id : 'all';

		return array(
			'course_id' => $course_id,
			'course_title' => $course_title
		);

	}

	public static function get_notifications( $course ) {

		$course = (array) $course;

		$args = array(
			'post_type' => self::get_post_type_name(),
			'meta_query' => array(
				array(
					'key'     => 'course_id',
					'value'   => $course,
					'compare' => 'IN',
				),
			),
			'post_per_page' => 20
		);

		return get_posts( $args );

	}

}