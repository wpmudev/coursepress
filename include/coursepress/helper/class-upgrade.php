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
		$coursepress_courses_need_update = false;
		if ( 0 > version_compare( $plugin_version, CoursePress::$version ) ) {
			update_option( 'coursepress_version', CoursePress::$version, 'no' );
			/**
			 * Counts posts and decide.
			 */
			$post_type = CoursePress_Data_Course::get_post_type_name();
			$count_courses = (array)wp_count_posts( $post_type );
			$count_courses = array_sum( $count_courses );
			if ( ! empty( $count_courses ) ) {
				$coursepress_courses_need_update = true;
			}
			update_option( 'coursepress_courses_need_update', $coursepress_courses_need_update );
        }
        $coursepress_courses_need_update = get_option( 'coursepress_courses_need_update', $coursepress_courses_need_update );
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
	 * @return boolean|string status of upgrade true or message.
	 */
	public static function course_upgrade( $course ) {
        $already_upgraded = get_post_meta( $course->ID, '_cp_updated_to_version_2', true );
        if ( $already_upgraded ) {
            return __( 'This course was already updated.', 'cp' );
        }
        $updates = array(
            'course_details_video',
            'course_details_structure',
            'course_dates',
        );
        foreach( $updates as $function_sufix ) {
            call_user_func( array( __CLASS__, 'course_upgrade_'.$function_sufix ), $course );
        }

        CoursePress_Data_Course::update_setting( $course->ID, 'course_view', 'normal' );



//l(CoursePress_Data_Course::get_setting( $course->ID ));

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
		if ( is_string( $success ) ) {
			$message = sprintf( __( 'Course update fail: %s!', 'cp' ), $success );
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

	/**
	 * Course Details: Course Video
	 */
	private static function course_upgrade_course_details_video( $course ) {
		$value = self::rename_post_meta( $course->ID, 'course_video_url', 'cp_featured_video' );
		if ( empty( $value ) ) {
			return;
		}
		CoursePress_Data_Course::update_setting( $course->ID, 'featured_video', $value );
    }

	/**
	 * Course Details: Course Structure
	 */
	private static function course_upgrade_course_details_structure( $course ) {
		$value = self::rename_post_meta( $course->ID, 'course_structure_options', 'meta_structure_visible' );
		if ( empty( $value ) ) {
			return;
		}
    }

	/**
	 * Course Dataes
	 */
    private static function course_upgrade_course_dates( $course ) {
        $dates = array(
            array(
                'meta_key_old' => 'course_end_date',
                'meta_key_new' => 'cp_course_end_date',
                'settings' => 'course_end_date',
            ),
            array(
                'meta_key_old' => 'course_start_date',
                'meta_key_new' => 'cp_course_start_date',
                'settings' => 'course_start_date',
            ),
            array(
                'meta_key_old' => 'enrollment_end_date',
                'meta_key_new' => 'cp_enrollment_end_date',
                'settings' => 'enrollment_end_date',
            ),
            array(
                'meta_key_old' => 'enrollment_start_date',
                'meta_key_new' => 'cp_enrollment_start_date',
                'settings' => 'enrollment_start_date',
            ),
            array(
                'meta_key_old' => 'open_ended_course',
                'meta_key_new' => 'cp_course_open_ended',
                'settings' => 'course_open_ended',
            ),
            array(
                'meta_key_old' => 'open_ended_enrollment',
                'meta_key_new' => 'cp_enrollment_open_ended',
                'settings' => 'open_ended_enrollment',
            ),
        );
        $options = array(
            'value_convert_function' => 'strtotime',
            'save_old_meta' => true,
        );
        foreach ( $dates as $data ) {
            $value = self::rename_post_meta( $course->ID, $data['meta_key_old'], $data['meta_key_new'], $options );
            if ( empty( $value ) ) {
                continue;
            }
            CoursePress_Data_Course::update_setting( $course->ID, $data['settings'], $value );
        }
        /**
         * do not convert
         */
        $options = array();
        $dates = array(
            array(
                'meta_key_old' => 'open_ended_course',
                'meta_key_new' => 'cp_open_ended_course',
                'settings' => 'course_open_ended',
            ),
            array(
                'meta_key_old' => 'open_ended_enrollment',
                'meta_key_new' => 'cp_enrollment_open_ended',
                'settings' => 'enrollment_open_ended',
            ),
        );
        foreach ( $dates as $data ) {
            $value = self::rename_post_meta( $course->ID, $data['meta_key_old'], $data['meta_key_new'], $options );
            if ( empty( $value ) ) {
                continue;
            }
            CoursePress_Data_Course::update_setting( $course->ID, $data['settings'], $value );
        }
    }

	private static function rename_post_meta( $course_id, $meta_key_old, $meta_key_new, $options = array() ) {
		$value = get_post_meta( $course_id, $meta_key_old, true );
		if ( empty( $value ) ) {
			return;
        }
        /**
         * convert value if is nessarry
         */
        if (
            isset( $options['value_convert_function'] )
            && $options['value_convert_function'] 
            && is_callable( $options['value_convert_function'] )
        ) {
            $value = call_user_func( $options['value_convert_function'], $value );
        }
        l(array( $course_id, $meta_key_old, $meta_key_new, $options, $value ) );
        /**
         * Add post meta
         */
        CoursePress_Helper_Utility::add_meta_unique( $course_id, $meta_key_new, $value );
        /**
         * resave old with new value
         */
        if ( isset( $options['save_old_meta'] ) && $options['save_old_meta'] ) {
            update_post_meta( $course_id, $meta_key_old, $value );
        }
        /**
         * delete old meta
         */
		if ( isset( $options['delete_old_meta'] ) && $options['delete_old_meta'] ) {
			delete_post_meta( $course_id, $meta_key_old );
		}
		return $value;
	}

}
