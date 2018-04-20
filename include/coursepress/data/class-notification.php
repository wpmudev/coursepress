<?php

class CoursePress_Data_Notification {

	private static $post_type = 'notifications';  // Plural because of legacy

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Notifications', 'coursepress' ),
					'singular_name' => __( 'Notification', 'coursepress' ),
					'add_new' => __( 'Create New', 'coursepress' ),
					'add_new_item' => __( 'Create New Notification', 'coursepress' ),
					'edit_item' => __( 'Edit Notification', 'coursepress' ),
					'edit' => __( 'Edit', 'coursepress' ),
					'new_item' => __( 'New Notification', 'coursepress' ),
					'view_item' => __( 'View Notification', 'coursepress' ),
					'search_items' => __( 'Search Notifications', 'coursepress' ),
					'not_found' => __( 'No Notifications Found', 'coursepress' ),
					'not_found_in_trash' => __( 'No Notifications found in Trash', 'coursepress' ),
					'view' => __( 'View Notification', 'coursepress' ),
					'insert_into_item' => __( 'Insert into notification', 'coursepress' ),
					'uploaded_to_this_item' => __( 'Uploaded to this notification', 'coursepress' ),
					'items_list' => __( 'Notifications list', 'coursepress' ),
					'filter_items_list' => __( 'Filter notification list', 'coursepress' ),
					'items_list_navigation' => __( 'Notifications list navigation', 'coursepress' ),
				),
				'public' => false,
				'show_ui' => false,
				'publicly_queryable' => false,
				'capability_type' => 'notification',
				'capabilities' => array(
					'read' => 'read',
					'edit_post' => 'coursepress_update_notification_cap',
					'edit_posts' => 'coursepress_update_notification_cap'
				),
				'map_meta_cap' => false,
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
		$course_title = ! empty( $course_id ) ? get_the_title( $course_id ) : __( 'All courses', 'coursepress' );
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

	public static function ajax_update() {

		$data = json_decode( file_get_contents( 'php://input' ) );

		$json_data = array();
		$success = false;

		$action = isset( $data->action ) ? $data->action : '';
		$json_data['action'] = $action;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Notification Update: No action.', 'coursepress' );
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
						$json_data['message'] = __( 'Notification update failed.', 'coursepress' );
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

	/**
	 * Get courses list if curen user do not have 'manage_options'
	 *
	 * @since 2.0.0
	 *
	 * @return array $courses Array of WP_Post objects
	 */
	public static function get_courses() {
		$user_id = get_current_user_id();
		if ( empty( $user_id ) ) {
			return array();
		}
		$courses = self::get_accessable_courses();
		if ( empty( $courses ) ) {
			return $courses;
		}
		/** This filter is documented in include/coursepress/helper/class-setting.php */
		$capability = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_assigned_notification_cap' );
		$is_instructor = user_can( $user_id, $capability );
		$capability2 = apply_filters( 'coursepress_capabilities', 'coursepress_create_my_notification_cap' );
		$is_author = user_can( $user_id, $capability2 );
		foreach ( $courses as $index => $course ) {
			if ( CoursePress_Data_Capabilities::is_course_instructor( $course ) && ! $is_instructor ) {
				unset( $courses[ $index ] );
			}
			if ( $user_id == $course->post_author && ! $is_author ) {
				unset( $courses[ $index ] );
			}
		}
		return $courses;
	}

	/**
	 * Get accessable courses for notifications.
	 *
	 * @since 2.0.0
	 *
	 * @param integer|null $user_id Current user ID.
	 * @param string $post_status Post status.
	 * @return array array of matched posts.
	 */
	public static function get_accessable_courses( $user_id = '', $post_status = 'publish' ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		} elseif ( is_object( $user_id ) ) {
			$user_id = $user_id->ID;
		}
		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => $post_status,
			'posts_per_page' => -1,
		);
		if ( ! user_can( $user_id, 'manage_options' ) ) {
			$can_search = false;
			if ( user_can( $user_id, 'coursepress_create_my_notification_cap' ) ) {
				$args['author'] = $user_id;
				$can_search = true;
			}
			if ( user_can( $user_id, 'coursepress_create_my_assigned_notification_cap' ) ) {
				$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
				$args['include'] = $assigned_courses;
				if ( $can_search ) {
					// Let's add the author param via filter hooked.
					unset( $args['author'] );
					add_filter( 'posts_where', array( 'CoursePress_Data_Instructor', 'filter_by_where' ) );
				}
				$can_search = true;
			}
			if ( ! $can_search ) {
				// Bail early
				return array();
			}
		}
		$posts = get_posts( $args );
		return $posts;
	}

}
