<?php

class CoursePress_Helper_Upgrade {

	public static function admin_init() {
		/**
		 * show migration message
		 */
		add_action( 'admin_notices', array( __CLASS__, 'show_migration_messages' ) );
	}

	/**
	 * update post meta
	 *
	 * @since 2.0.0.
	 */
	public static function copy_enroled_students_to_course() {
		$user_id = get_current_user_id();
		$meta_key = 'course_enrolled_students_done';
		$args = array(
			'post_type' => 'course',
			'post_status' => 'any',
			'meta_key' => $meta_key,
			'meta_compare' => 'NOT EXISTS',
			'fields' => 'ids',
			'posts_per_page' => -1,
		);
		$ids = get_posts( $args );
		if ( empty( $ids ) ) {
			/**
			 * Message: migration is ended.
			 */
			$message = __( 'Migration was done. There is no more students to migrate.', 'cp' );
			add_user_meta( $user_id, self::$message_meta_name, $message, false );
			return;
		}
		/**
		 * Message: number of courses.
		 */
		$count = count( $ids );
		$message = sprintf(
			_n(
				'Found %d course to migrate. Course id: %s.',
				'Found %d courses to migrate. Course ids: %s.',
				$count,
				'cp'
			),
			$count,
			implode( ', ', $ids )
		);
		add_user_meta( $user_id, self::$message_meta_name, $message, false );
		global $wpdb;
		$course_id = array_shift( $ids );
		$sql = sprintf(
			'select user_id from %s where meta_key = \'enrolled_course_date_%s\'',
			$wpdb->usermeta,
			$course_id
		);
		$results = $wpdb->get_results( $sql );
		$message = '';
		if ( ! empty( $results ) ) {
			$message = sprintf(
				__( 'Update students (%d) data in course: %d.', 'cp' ),
				count( $results ),
				$course_id
			);
			foreach ( $results as $one ) {
				add_post_meta( $course_id, 'course_enrolled_student_id', $one->user_id );
			}
		} else {
			$message = sprintf(
				__( 'Try to update students data in course: %d, but there was no students enroled to this course.', 'cp' ),
				$course_id
			);
		}
		/**
		 * Message: last updated course information.
		 */
		add_user_meta( $user_id, self::$message_meta_name, $message, false );
		add_post_meta( $course_id, $meta_key, 'done' );
		/**
		 * Message: number of courses to migrate
		 */
		$count = count( $ids );
		if ( 0 < $count ) {
			$message = sprintf(
				_n(
					'There is %d course to migrate. Course id: %s.',
					'There are %d courses to migrate. Course ids: %s.',
					$count,
					'cp'
				),
				$count,
				implode( ', ', $ids )
			);
			add_user_meta( $user_id, self::$message_meta_name, $message, false );
		}
	}

	/**
	 * Show migration messages.
	 *
	 * @since 2.0.0
	 */
	public static function show_migration_messages() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		$user_id = get_current_user_id();
		$messages = get_user_meta( $user_id, self::$message_meta_name, false );
		if ( empty( $messages ) ) {
			return;
		}
		echo '<div class="notice notice-success"><ul><li>';
		echo implode( '</li><li>', $messages );
		echo '</li></ul></div>';
		delete_user_meta( $user_id, self::$message_meta_name );
	}

}
