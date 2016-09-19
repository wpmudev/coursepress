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
					'insert_into_item' => __( 'Insert into notification', 'cp' ),
					'uploaded_to_this_item' => __( 'Uploaded to this notification', 'cp' ),
					'items_list' => __( 'Notifications list', 'cp' ),
					'filter_items_list' => __( 'Filter notification list', 'cp' ),
					'items_list_navigation' => __( 'Notifications list navigation', 'cp' ),
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

	/**
	 * Get notifications.
	 */
	public static function get_notifications( $course ) {
		$course = (array) $course;
		$course_id = $course[0];
		/**
		 * Base query
		 */
		$args = array(
			'post_type' => self::get_post_type_name(),
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'course_id',
					'value' => 'all',
				),
				/**
				 * Receivers are not set.
				 */
				array(
					array(
						'key' => 'course_id',
						'value' => $course_id,
					),
					array(
						'key' => 'receivers',
						'compare' => 'NOT EXISTS',
					),
				),
				/**
				 * Enrolled to this course!
				 */
				array(
					array(
						'key' => 'course_id',
						'value' => $course_id,
					),
					array(
						'key' => 'receivers',
						'value' => 'enrolled',
					),
				),
			),
			'post_per_page' => 20,
		);
		/**
		 * Get student progress
		 */
		$student_id = get_current_user_id();
		$student_progress = CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course_id );
		/**
		 * Course is completed
		 */
		$is_done = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/completed' );
		if ( $is_done ) {
			$args['meta_query'][] = array(
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
				array(
					'key' => 'receivers',
					'value' => 'passed',
				),
			);
		}
		/**
		 * Course is failed
		 */
		$is_failed = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/failed' );
		if ( $is_failed ) {
			$args['meta_query'][] = array(
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
				array(
					'key' => 'receivers',
					'value' => 'failed',
				),
			);
		}
		/**
		 * Completed Units
		 */
		$units = CoursePress_Data_Course::get_units( $course_id, $status = array( 'publish' ), true );
		foreach ( $units as $unit_id ) {
			$unit_completed = CoursePress_Helper_Utility::get_array_val(
				$student_progress,
				'completion/' . $unit_id . '/completed'
			);
			if ( ! $unit_completed ) {
				continue;
			}
			$args['meta_query'][] = array(
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
				array(
					'key' => 'receivers',
					'value' => sprintf( 'unit-%d', $unit_id ),
				),
			);
		}
		/**
		 * Finally get posts.
		 */
		return get_posts( $args );
	}

	public static function update_notification() {
		$data = json_decode( file_get_contents( 'php://input' ) );

		$json_data = array();
		$success = false;

		$action = isset( $data->action ) ? $data->action : '';
		$json_data['action'] = $action;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Notification Update: No action.', 'cp' );
			wp_send_json_error( $json_data );
		}

		switch ( $action ) {

			case 'delete':
				if ( wp_verify_nonce( $data->data->nonce, 'delete-notification' ) ) {
					$notification_id = $data->data->notification_id;
					if ( self::is_correct_post_type( $notification_id ) ) {
						wp_delete_post( $notification_id );
						$json_data['notification_id'] = $notification_id;
						$json_data['nonce'] = wp_create_nonce( 'delete-notification' );
						$success = true;
					}
				}
				break;

			case 'toggle':
				if ( wp_verify_nonce( $data->data->nonce, 'publish-notification' ) ) {
					$notification_id = $data->data->notification_id;
					if ( self::is_correct_post_type( $notification_id ) ) {
						wp_update_post( array(
							'ID' => $notification_id,
							'post_status' => $data->data->status,
						) );
						$json_data['nonce'] = wp_create_nonce( 'publish-notification' );
						$json_data['notification_id'] = $notification_id;
						$json_data['state'] = $data->data->state;
						$success = true;
					} else {
						$json_data['message'] = __( 'Notification update failed.', 'cp' );
						$json_data['ID'] = $notification_id;
					}
				}
				break;

			case 'bulk_unpublish':
			case 'bulk_publish':
			case 'bulk_delete':

				$ids = $data->data->ids;

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					foreach ( $ids as $id ) {

						if ( ! self::is_correct_post_type( $id ) ) {
							continue;
						}

						if ( 'bulk_unpublish' === $action ) {
							if ( CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => 'draft',
								) );
							}
						}

						if ( 'bulk_publish' === $action ) {
							if ( CoursePress_Data_Capabilities::can_update_notification( $id ) ) {
								wp_update_post( array(
									'ID' => $id,
									'post_status' => 'publish',
								) );
							}
						}

						if ( 'bulk_delete' === $action ) {
							if ( CoursePress_Data_Capabilities::can_delete_notification( $id ) ) {
								wp_delete_post( $id );
							}
						}
					}

					$success = true;

				}

				$json_data['ids'] = $ids;

				break;

		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}

	}

	/**
	 * Check is post type match?
	 *
	 * @since 2.0.0
	 *
	 * @param int|WP_Post Post ID or post object.
	 * @return boolean True on success, false on failure.
	 */
	public static function is_correct_post_type( $post ) {
		$post_type = get_post_type( $post );
		return self::$post_type == $post_type;
	}

}
