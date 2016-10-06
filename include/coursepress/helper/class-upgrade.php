<?php

class CoursePress_Helper_Upgrade {

	private static $message_meta_name = 'course_upgrade_messsage';

	public static function init() {
		add_action( 'wp_ajax_coursepress_upgrade_update', array( __CLASS__, 'ajax_courses_upgrade' ) );
	}

	public static function admin_init() {
		/**
		 * show migration message
		 */
		add_action( 'admin_notices', array( __CLASS__, 'show_migration_messages' ) );
	}

	public static function add_message( $message ) {
		$user_id = get_current_user_id();
		add_user_meta( $user_id, self::$message_meta_name, $message, false );
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

	/**
	 * Is an upgrade nessarry?
	 *
	 * @since 2.0.0
	 */
	public static function maybe_upgrade() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		$plugin_version = get_option( 'coursepress_version', '1.3' );
		if ( 0 > version_compare( $plugin_version, CoursePress::$version ) ) {
			update_option( 'coursepress_courses_need_update', true );
			update_option( 'coursepress_version', CoursePress::$version, 'no' );
		}
		$coursepress_courses_need_update = get_option( 'coursepress_courses_need_update', false );
		if ( $coursepress_courses_need_update ) {
			$slug = CoursePress_View_Admin_Upgrade::get_slug();
			$hide = isset( $_GET['page'] ) && $_GET['page'] == $slug;
			if ( ! $hide ) {
				CoursePress_Helper_Upgrade::add_message(
					sprintf(
						'Courses needs an upgrade. Please go to <a href="%s">Upgrade Courses</a> page.',
						esc_url( add_query_arg( 'page', CoursePress_View_Admin_Upgrade::get_slug(), admin_url( 'admin.php' ) ) )
					)
				);
			}
			CoursePress_Helper_Upgrade::admin_init();
		}
	}

	public static function get_update_nonce( $user_id = null ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		return sprintf( 'coursepress_update_by_%d', $user_id );
	}

	/**
	 * Upgrade course - main function for upgrade!
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $course Course object.
	 * @return status of upgrade true/false.
	 */
	public static function course_upgrade( $course ) {
		// _cp_updated_to_version_2
		return true;
	}

	/**
	 * Ajax function to handla courses upgrades.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function ajax_courses_upgrade() {
		/**
		 * check data
		 */
		if (
			!isset( $_POST['user_id'])
			|| ! isset( $_POST['_wpnonce'] )
			|| ! isset( $_POST['course_id'] )
		) {
			$message = __( 'Course update fail: wrong data!', 'cp' );
			self::print_json_and_die( $message );
		}
		/**
		 * Check nonce
		 */
		$user_id = intval( $_POST['user_id'] );
		$nonce_name = self::get_update_nonce( $user_id );
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $nonce_name ) ) {
			$message = __( 'Course update fail: security check!', 'cp' );
			self::print_json_and_die( $message );
		}
		/**
		 * check is a course?
		 */
		$course_id = intval( $_POST['course_id'] );
		if ( ! CoursePress_Data_Course::is_course( $course_id ) ) {
			$message = __( 'Course update fail: wrong course ID!', 'cp' );
			self::print_json_and_die( $message );
		}
		/**
		 * get course
		 */
		$course = get_post( $course_id );
		if ( empty( $course ) ) {
			$message = __( 'Course update fail: wrong course!', 'cp' );
			self::print_json_and_die( $message );
		}
		/**
		 * upgrade course
		 */
		$success = self::course_upgrade( $course );
		if ( ! $success ) {
			$message = __( 'Course update fail: someting went wrong!', 'cp' );
			self::print_json_and_die( $message );
		}
		/**
		 * return data
		 */
		$title = sprintf( '<b>%s</b>', apply_filters( 'the_title', $course->post_title ) );
		$message = sprintf( __( 'Course %s was successful updated.', 'cp' ), $title );
		self::print_json_and_die( $message, true );
	}

	/**
	 * Print json and die - short helper function for ajax call.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message Message to add.
	 * @param boolean $success Information about status of operation.
	 */
	private static function print_json_and_die( $message, $success = false ) {
		$json = array(
			'success' => $success,
			'message' => $message,
		);
		echo json_encode( $json );
		wp_die();
	}

}
