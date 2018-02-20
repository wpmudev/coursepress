<?php

/**
 * This class is responsible for CoursePress upgrade process.
 */

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
			$message = __( 'Migration was done. There is no more students to migrate.', 'CP_TD' );
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
				'CP_TD'
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
				__( 'Update students (%d) data in course: %d.', 'CP_TD' ),
				count( $results ),
				$course_id
			);
			foreach ( $results as $one ) {
				add_post_meta( $course_id, 'course_enrolled_student_id', $one->user_id );
			}
		} else {
			$message = sprintf(
				__( 'We tried to update the students data in course: %d, but there were no students enrolled to this course.', 'CP_TD' ),
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
					'CP_TD'
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
		$plugin_version = get_option( 'coursepress_version', '0' );
		$coursepress_courses_need_update = 'no';
		if ( 0 > version_compare( $plugin_version, CoursePress::$version ) ) {
			update_option( 'coursepress_version', CoursePress::$version, 'no' );
			/**
			 * Counts posts and decide.
			 */
			$post_type = CoursePress_Data_Course::get_post_type_name();
			$count_courses = (array) wp_count_posts( $post_type );
			$count_courses = array_sum( $count_courses );
			if ( ! empty( $count_courses ) ) {
				$coursepress_courses_need_update = 'yes';
			}
			add_option( 'coursepress_courses_need_update', $coursepress_courses_need_update );
		}
		$coursepress_courses_need_update = get_option( 'coursepress_courses_need_update', $coursepress_courses_need_update );
		if ( 'yes' == $coursepress_courses_need_update ) {
			$slug = CoursePress_View_Admin_Upgrade::get_slug();
			$hide = isset( $_GET['page'] ) && $_GET['page'] == $slug;
			if ( ! $hide ) {
				$url = add_query_arg(
					array(
						'post_type' => CoursePress_Data_Course::get_post_type_name(),
						'page' => CoursePress_View_Admin_Upgrade::get_slug(),
					),
					admin_url( 'edit.php' )
				);
				CoursePress_Helper_Upgrade::add_message(
					sprintf(
						'Courses needs an upgrade. Please go to <a href="%s">Upgrade Courses</a> page.',
						esc_url( $url )
					)
				);
			}
			CoursePress_Helper_Upgrade::admin_init();
		} else {
			add_option( 'coursepress_courses_need_update', 'no' );
		}
	}

	public static function get_update_nonce( $user_id = null ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		return sprintf( 'coursepress_update_by_%d', $user_id );
	}

	/**
	 * get update list
	 */
	public static function upgrade_get_courses_list() {
		$args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'nopaging' => true,
			'ignore_sticky_posts' => true,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => '_cp_updated_to_version_2',
					'compare' => 'NOT EXISTS',
				),
			),
			'suppress_filters' => true,
		);
		$query = new WP_Query( $args );
		return $query->posts;
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
			return __( 'This course was already updated.', 'CP_TD' );
		}
		$updates = array(
			'begin',
			'module_page',
			'course_details_video',
			'course_details_structure',
			'course_instructors',
			'course_dates',
			'course_classes_discusion_and_workbook',
			'course_enrollment_and_cost',
			'course_completion',
			'unit_page_title',
			'student_enrolled',
			'student_progress',
			'end',
		);
		$section = isset( $_POST['section'] )? $_POST['section']:$updates[0];
		if ( ! in_array( $section, $updates ) ) {
			$section = $updates[0];
		}
		$index = array_search( $section, $updates );
		$function = 'course_upgrade_'.$section;
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'COURSE UPDATE: before call function: %s', $function ) );
		}
		$json = array(
			'success' => true,
			'message' => self::$function( $course ),
			'course_id' => $course->ID,
			'section' => '',
		);
		if ( empty( $json['message'] ) ) {
			$json['message'] = $section;
		}
		switch ( $section ) {
			case 'begin':
				$json['message'] = sprintf( '<h2>%s</h2><ol>', $json['message'] );
			break;
			case 'end':
				$json['message'] = sprintf( '<li class="%s">%s</li></ol>', esc_attr( $section ), $json['message'] );
			break;
			default:
				$json['message'] = sprintf( '<li class="%s">%s</li>', esc_attr( $section ), $json['message'] );
		}
		/**
		 * try to use new section
		 */
		$index++;
		if ( isset( $updates[ $index ] ) ) {
			$json['section'] = $updates[ $index ];
		} else {
			$courses_ids = self::upgrade_get_courses_list();
			$index = array_search( $course->ID, $courses_ids );
			$index++;
			if ( isset( $courses_ids[ $index ] ) ) {
				$json['course_id'] = $courses_ids[ $index ];
			} else {
				$json['course_id'] = 'stop';
			}
		}
		echo json_encode( $json );
		wp_die();
	}

	public static function course_upgrade_begin( $course ) {
		return sprintf( 'Start updating course: <b>%s</b>', apply_filters( 'the_title', $course->post_title ) );
	}

	/**
	 * Last settings
	 */
	public static function course_upgrade_end( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( ! $done ) {
			$settings = CoursePress_Data_Course::get_setting( $course->ID, true );
			CoursePress_Data_Course::set_setting( $settings, 'course_view', 'normal' );
			for ( $i = 1; $i < 8; $i++ ) {
				CoursePress_Data_Course::set_setting( $settings, 'setup_step_'.$i, 'saved' );
			}
			CoursePress_Data_Course::update_setting( $course->ID, true, $settings );
			self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		}
		$title = sprintf( '<b>%s</b>', apply_filters( 'the_title', $course->post_title ) );
		$content = sprintf( __( 'Course %s was successful updated.', 'CP_TD' ), $title );
		CoursePress_Helper_Utility::add_meta_unique( $course->ID, '_cp_updated_to_version_2', true );
		return $content;
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
			! isset( $_POST['user_id'] )
			|| ! isset( $_POST['_wpnonce'] )
			|| ! isset( $_POST['course_id'] )
			|| ! isset( $_POST['section'] )
		) {
			$message = __( 'Course update fail: wrong data!', 'CP_TD' );
			self::print_json_and_die( $message );
		}
		/**
		 * Check nonce
		 */
		$user_id = intval( $_POST['user_id'] );
		$nonce_name = self::get_update_nonce( $user_id );
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $nonce_name ) ) {
			$message = __( 'Course update fail: security check!', 'CP_TD' );
			self::print_json_and_die( $message );
		}
		/**
		 * check is a course?
		 */
		$course_id = intval( $_POST['course_id'] );
		if ( ! CoursePress_Data_Course::is_course( $course_id ) ) {
			$message = __( 'Course update fail: wrong course ID!', 'CP_TD' );
			self::print_json_and_die( $message );
		}
		/**
		 * get course
		 */
		$course = get_post( $course_id );
		if ( empty( $course ) ) {
			$message = __( 'Course update fail: wrong course!', 'CP_TD' );
			self::print_json_and_die( $message );
		}
		/**
		 * upgrade course
		 */
		$success = self::course_upgrade( $course );
		if ( is_string( $success ) ) {
			$message = sprintf( __( 'Course update fail: %s!', 'CP_TD' ), $success );
			self::print_json_and_die( $message );
		}
		/**
		 * return data
		 */
		$title = sprintf( '<b>%s</b>', apply_filters( 'the_title', $course->post_title ) );
		$message = sprintf( __( 'Course %s was successful updated.', 'CP_TD' ), $title );
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
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Course video settings do not need to be updated.', 'CP_TD' );
		}
		$fields = array(
			array(
				'meta_key_old' => 'course_video_url',
				'meta_key_new' => 'cp_featured_video',
				'settings' => 'featured_video',
			),
		);
		self::update_array( $course->ID, $fields );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Course video settings have been updated.', 'CP_TD' );
	}

	/**
	 * Course Details: Course Structure
	 */
	private static function course_upgrade_course_details_structure( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Course structure settings do not need to be updated.', 'CP_TD' );
		}
		$fields = array(
			array(
				'meta_key_old' => 'course_structure_options',
				'meta_key_new' => 'meta_structure_visible',
			),
			array(
				'meta_key_old' => 'course_structure_time_display',
				'meta_key_new' => 'cp_structure_show_duration',
			),
			/**
			 * Pages
			 */
			array(
				'meta_key_old' => 'preview_page_boxes',
				'meta_key_new' => 'cp_structure_preview_pages',
				'settings' => 'structure_preview_pages',
			),
			array(
				'meta_key_old' => 'show_page_boxes',
				'meta_key_new' => 'cp_structure_visible_pages',
				'settings' => 'structure_visible_pages',
			),
			/**
			 * units
			 */
			array(
				'meta_key_old' => 'preview_unit_boxes',
				'meta_key_new' => 'cp_structure_preview_units',
				'settings' => 'structure_preview_units',
			),
			array(
				'meta_key_old' => 'show_unit_boxes',
				'meta_key_new' => 'cp_structure_visible_units',
				'settings' => 'structure_visible_units',
			),
		);
		self::update_array( $course->ID, $fields );
		/**
		 * show & preview all modules
		 */
		$visible_pages = CoursePress_Data_Course::get_setting( $course->ID, 'structure_visible_pages' );
		$cp1_visible_pages = array();
		foreach ( $visible_pages as $page => $status ) {
			if ( cp_is_true( $status ) && preg_match( '/^(\d+)_(\d+)$/', $page, $matches ) ) {
				$cp1_visible_pages[] = sprintf( '%d_%d', $matches[1], $matches[2] );
			}
		}
		$preview_pages = CoursePress_Data_Course::get_setting( $course->ID, 'structure_preview_pages' );
		$cp1_preview_pages = array();
		foreach ( $preview_pages as $page => $status ) {
			if ( cp_is_true( $status ) && preg_match( '/^(\d+)_(\d+)$/', $page, $matches ) ) {
				$cp1_preview_pages[] = sprintf( '%d_%d', $matches[1], $matches[2] );
			}
		}
		/**
		 * Update unit visibility - by default - all units in visible page
		 */
		$keys = array(
			'structure_preview_modules',
			'structure_preview_pages',
			'structure_visible_modules',
			'structure_visible_pages',
		);
		foreach ( $keys as $key ) {
			$$key = array();
		}
		/**
		 * get units
		 */
		$units = CoursePress_Data_Course::get_units_with_modules( $course->ID, array( 'publish', 'draft' ) );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );
		/**
		 * Update pages and try to update modules too.
		 */
		foreach ( $units as $unit ) {
			if ( ! isset( $unit['pages'] ) ) {
				continue;
			}
			foreach ( $unit['pages'] as $key => $page ) {
				$page_key = (int) $unit['unit']->ID . '_' . (int) $key;
				/**
				 * Visible
				 */
				if ( in_array( $page_key, $cp1_visible_pages ) ) {
					$structure_visible_pages[ $page_key ] = true;
					foreach ( $page['modules'] as $module ) {
						$mod_key = $page_key . '_' . (int) $module->ID;
						$structure_visible_modules[ $mod_key ] = true;
					}
				}
				/**
				 * Preview
				 */
				if ( in_array( $page_key, $cp1_preview_pages ) ) {
					$structure_preview_pages[ $page_key ] = true;
					foreach ( $page['modules'] as $module ) {
						$mod_key = $page_key . '_' . (int) $module->ID;
						$structure_preview_modules[ $mod_key ] = true;
					}
				}
			}
		}
		$settings = CoursePress_Data_Course::get_setting( $course->ID, true );
		foreach ( $keys as $key ) {
			CoursePress_Data_Course::set_setting( $settings, $key, $$key );
		}
		CoursePress_Data_Course::update_setting( $course->ID, true, $settings );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Course structure settings have been updated.', 'CP_TD' );
	}

	/**
	 * Step 4 – Course Dates
	 */
	private static function course_upgrade_course_dates( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Course Dates settings do not need to be updated.', 'CP_TD' );
		}
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
		self::update_array( $course->ID, $dates );
		/**
		 * do not convert
		 */
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
		self::update_array( $course->ID, $dates );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Course Dates settings have been updated.', 'CP_TD' );
	}

	/**
	 * Step 3 – Instructors and Facilitators
	 */
	private static function course_upgrade_course_instructors( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Instructors settings do not need to be updated.', 'CP_TD' );
		}
		$fields = array(
			array(
				'meta_key_old' => 'instructors',
				'meta_key_new' => 'cp_instructors',
				'settings' => 'instructors',
			),
		);
		self::update_array( $course->ID, $fields );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Instructors settings have been updated.', 'CP_TD' );
	}

	/**
	 * Step 5 – Classes, Discussion & Workbook
	 */
	private static function course_upgrade_course_classes_discusion_and_workbook( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Classes, Discussion & Workbook settings do not need to be updated.', 'CP_TD' );
		}
		$fields = array(
			array(
				'meta_key_old' => 'allow_course_discussion',
				'settings' => 'allow_discussion',
			),
			array(
				'meta_key_old' => 'allow_workbook_page',
				'settings' => 'allow_workbook',
			),
			array(
				'meta_key_old' => 'class_size',
				'settings' => 'class_size',
			),
			array(
				'meta_key_old' => 'limit_class_size',
				'settings' => 'class_limited',
			),
		);
		self::update_array( $course->ID, $fields );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Classes, Discussion & Workbook settings have been updated.', 'CP_TD' );
	}

	/**
	 * Step 6 – Enrollment & Course Cost
	 */
	private static function course_upgrade_course_enrollment_and_cost( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Course Enrollment & Cost settings do not need to be updated.', 'CP_TD' );
		}
		$fields = array(
			array(
				'meta_key_old' => 'enroll_type',
				'settings' => 'enrollment_type',
			),
			array(
				'meta_key_old' => 'paid_course',
				'settings' => 'payment_paid_course',
			),
			array(
				'meta_key_old' => 'passcode',
				'settings' => 'enrollment_passcode',
			),
			array(
				'meta_key_old' => 'prerequisite',
				'settings' => 'enrollment_prerequisite',
			),
		);
		self::update_array( $course->ID, $fields );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Course Enrollment & Cost settings have been updated.', 'CP_TD' );
	}

	private static function course_upgrade_student_enrolled( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Course enrolled students do not need to be updated.', 'CP_TD' );
		}
		$meta_key = sprintf( 'enrolled_course_date_%d', $course->ID );
		$args = array(
			'meta_key' => $meta_key,
			'fields' => 'ids',
			'number' => -1,
		);
		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}
		$user_query = new WP_User_Query( $args );
		$ids = $user_query->get_results();
		if ( empty( $ids ) ) {
			return __( 'There is no enrolled students to update.', 'CP_TD' );
		}
		foreach ( $ids as $user_id ) {
			$success = update_post_meta( $course->ID, 'course_enrolled_student_id', $user_id, $user_id );
			if ( ! $success ) {
				add_post_meta( $course->ID, 'course_enrolled_student_id', $user_id );
			}
			//delete_user_meta( $user_id, $meta_key );
		}
		$count = count( $ids );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		$message = __( 'Course Enrollment & Cost settings have been updated.', 'CP_TD' );
		$message .= ' ';
		$message .= sprintf( _n( '%s student enrolled to this course.', '%s students enroled to this course.', $count, 'CP_TD' ), $count );
		return $message;
	}

	/**
	 * Rename progress
	 */
	private static function course_upgrade_student_progress( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Student Progress do not need to be updated.', 'CP_TD' );
		}
		/**
		 * get units
		 */
		$units = CoursePress_Data_Course::get_units( $course->ID, array( 'any' ), true );
		/**
		 * Get course modules
		 */
		$modules = array();
		$all_modules = array();
		foreach ( $units as $unit_id ) {
			$modules[ $unit_id ] = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'any' ), true );
			$all_modules = array_merge( $all_modules, array_values( $modules[ $unit_id ] ) );
		}
		/**
		 * get students
		 */
		$student_ids = CoursePress_Data_Course::get_student_ids( $course->ID );
		foreach ( $student_ids as $student_id ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course->ID );
			if ( ! empty( $student_progress ) ) {
				continue;
			}
			$student_progress = array();
			/**
			 * Completed
			 */
			$meta_key = sprintf( '_course_%d_completed', $course->ID );
			$completed = get_user_meta( $student_id, $meta_key, true );
			if ( is_array( $completed ) && isset( $completed['units'] ) ) {
				foreach ( $completed['units'] as $unit_id => $status ) {
					if ( ! $status ) {
						continue;
					}
					if ( ! isset( $modules[ $unit_id ] ) ) {
						continue;
					}
					foreach ( $modules[ $unit_id ] as $module_id ) {
						/**
						 * Modules seen
						 */
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'completion/' . $unit_id . '/modules_seen/'.$module_id,
							true
						);
					}
				}
			}
			/**
			 * Build fake structure
			 */
			foreach ( $modules as $unit_id => $module_ids ) {
				foreach ( $module_ids as $module_id ) {
					$student_progress = CoursePress_Helper_Utility::set_array_value(
						$student_progress,
						'units/' . $unit_id . '/responses/'.$module_id,
						array()
					);
				}
			}
			/**
			 * Get student responses
			 */
			$args = array(
				'post_type' => 'module_response',
				'nopaging' => true,
				'ignore_sticky_posts' => true,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'course_id',
						'value' => $course->ID,
					),
					array(
						'key' => 'user_ID',
						'value' => $student_id,
					),
				),
			);
			$query = new WP_Query( $args );
			$responses = $query->posts;
			$unit_id = 0;
			$index = 0;
			foreach ( $responses as $response ) {
				/**
				 * Module & Unit iD
				 */
				$module_id = $response->post_parent;
				$module_type = get_post_meta( $module_id, 'module_type', true );
				$unit_id = CoursePress_Data_Module::get_unit_id_by_module( $module_id );
				/**
				 * Modules seen
				 */
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'completion/' . $unit_id . '/modules_seen/'.$module_id,
					true
				);
				/**
				 */
				$meta = get_post_meta( $response->ID );
				switch ( $module_type ) {
					case 'input-text':
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/response',
							$response->post_content
						);
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/date',
							$response->post_date
						);
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/grades',
							array()
						);
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/feedback',
							array()
						);
					break;
					case 'input-radio':
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/response',
							$response->post_content
						);
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/date',
							$response->post_date
						);
					break;
					case 'input-checkbox':
						/**
					 * student_checked_answers
					 */
						if ( isset( $meta['student_checked_answers'] ) ) {
							foreach ( $meta['student_checked_answers'] as $index => $response_student_checked_answer ) {
								$response_student_checked_answer = maybe_unserialize( $response_student_checked_answer );
								$student_progress = CoursePress_Helper_Utility::set_array_value(
									$student_progress,
									'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/response',
									maybe_unserialize( $response_student_checked_answer )
								);
							}
						}
					break;
					default:
						error_log( $module_type );
				}
				/**
				 * response_grade
				 */
				if ( isset( $meta['response_grade'] ) ) {
					foreach ( $meta['response_grade'] as $index => $grade ) {
						$grade = maybe_unserialize( $grade );
						/**
						 * Module response
						 */
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/grades/0',
							array(
								'grade_by' => $student_id == $grade['instructor'] ? 'auto' : $grade['instructors'],
								'grade' => $grade['grade'],
								'date' => date( 'Y-m-d H:i:s', $grade['time'] ),
							)
						);
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/date',
							date( 'Y-m-d H:i:s', $grade['time'] )
						);
					}
				} elseif ( preg_match( '/^input/', $module_type ) ) {
					$student_progress = CoursePress_Helper_Utility::set_array_value(
						$student_progress,
						'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/grades/0',
						array()
					);
				}
				/**
				 * Response comment
				 */
				if ( isset( $meta['response_comment'] ) ) {
					foreach ( $meta['response_comment'] as $index => $comment ) {
						$student_progress = CoursePress_Helper_Utility::set_array_value(
							$student_progress,
							'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/feedback/0',
							array(
								'feedback' => $comment,
							)
						);
					}
				}
			}
			/**
			 * input-file
			 */
			$args = array(
				'post_type' => 'attachment',
				'nopaging' => true,
				'ignore_sticky_posts' => true,
				'post_parent__in' => $all_modules,
				'post_status' => 'inherit',
			);
			$query = new WP_Query( $args );
			$responses = $query->posts;
			$unit_id = 0;
			$index = 0;
			foreach ( $responses as $response ) {
				/**
				 * Module & Unit iD
				 */
				$module_id = $response->post_parent;
				$unit_id = CoursePress_Data_Module::get_unit_id_by_module( $module_id );
				$module_id = $response->post_parent;
				$unit_id = CoursePress_Data_Module::get_unit_id_by_module( $module_id );
				$meta = get_post_meta( $response->ID );
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/grades/0',
					array(
						'grade_by' => $student_id == $grade['instructor'] ? 'auto' : $grade['instructors'],
						'grade' => 100,
						'date' => $response->post_date,
					)
				);
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/date',
					$response->post_date
				);
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'units/' . $unit_id . '/responses/'.$module_id.'/'.$index.'/response',
					array(
						'file' => '',
						'url' => get_attachment_link( $response->ID ),
						'type' => $response->post_mime_type,
						'size' => '',
					)
				);
			}
			/**
			 * Visited pages for unit
			 */
			if ( $unit_id ) {
				/**
				 * visited pages
				 */
				$value = get_user_meta( $student_id, 'visited_unit_pages_'.$unit_id.'_page', true );
				$value = explode( '|', get_user_meta( $student_id, 'visited_unit_pages_'.$unit_id.'_page', true ) );
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'units/' . $unit_id . '/visited_pages/',
					$value
				);
				/**
				 * last visited page
				 */
				$student_progress = CoursePress_Helper_Utility::set_array_value(
					$student_progress,
					'units/' . $unit_id . '/last_visited_page/',
					explode( '|', get_user_meta( $student_id, 'last_visited_unit_pages_'.$unit_id.'_page', true ) )
				);
			}
			$student_progress = CoursePress_Data_Student::get_calculated_completion_data( $student_id, $course->ID, $student_progress );
			CoursePress_Data_Student::update_completion_data( $student_id, $course->ID, $student_progress );
		}
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Students progress have been updated.', 'CP_TD' );
	}

	/**
	 * Rename post meta
	 */
	private static function rename_post_meta( $course_id, $meta_key_old, $meta_key_new ) {
		$value = get_post_meta( $course_id, $meta_key_old, true );
		if ( empty( $value ) ) {
			return;
		}
		/**
		 * Add post meta
		 */
		CoursePress_Helper_Utility::add_meta_unique( $course_id, $meta_key_new, $value );
		/**
		 * return value
		 */
		return $value;
	}

	/**
	 * Update array of post meta fields.
	 */
	private static function update_array( $course_id, $fields ) {
		foreach ( $fields as $data ) {
			$value = false;
			if ( isset( $data['meta_key_new'] ) ) {
				$value = self::rename_post_meta( $course_id, $data['meta_key_old'], $data['meta_key_new'] );
			} else {
				$value = get_post_meta( $course_id, $data['meta_key_old'], true );
			}
			if ( empty( $value ) ) {
				continue;
			}
			if ( isset( $data['settings'] ) ) {
				CoursePress_Data_Course::update_setting( $course_id, $data['settings'], $value );
			}
		}
	}

	/**
	 * split to pages
	 */
	public static function course_upgrade_module_page( $course ) {
		$units = CoursePress_Data_Course::get_units( $course->ID, array( 'any' ), true );
		if ( empty( $units ) ) {
			return __( 'Page breaks do not need to be updated.', 'CP_TD' );
		}
		foreach ( $units as $unit_id ) {
			$split_to_pages = get_post_meta( $unit_id, '_cp_split_to_pages', true );
			if ( empty( $split_to_page ) || 'done' != $split_to_pages ) {
				$args = array(
					'post_type' => CoursePress_Data_Module::get_post_type_name(),
					'post_parent' => $unit_id,
					'post_status' => 'any',
					'order' => 'ASC',
					'orderby' => 'meta_value_num',
					'meta_key' => 'module_order',
					'nopaging' => true,
					'ignore_sticky_posts' => true,
				);
				$query = new WP_Query( $args );
				$page = 1;
				foreach ( $query->posts as $module ) {
					$type = get_post_meta( $module->ID, 'module_type', true );
					if ( 'page_break_module' == $type ) {
						$page++;
						//						wp_delete_post( $module->ID, true );
					} else {
						CoursePress_Helper_Utility::add_meta_unique( $module->ID, 'module_page', $page );
					}
				}
				CoursePress_Helper_Utility::add_meta_unique( $unit_id, '_cp_split_to_pages', 'done' );
			}
		}
		return __( 'Page breaks have been updated.', 'CP_TD' );
	}

	/**
	 * Step 7 - Course Completion
	 */
	private static function course_upgrade_course_completion( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Course Completion do not need to be updated.', 'CP_TD' );
		}
		$settings = CoursePress_Data_Course::get_setting( $course->ID, true );
		CoursePress_Data_Course::set_setting( $settings, 'minimum_grade_required', 100 );
		$defaults = CoursePress_Data_Course::get_defaults_setup_pages_content();
		foreach ( $defaults as $group => $data ) {
			foreach ( $data as $name => $content ) {
				$key = sprintf( '%s_%s', $group, $name );
				CoursePress_Data_Course::set_setting( $settings, $key, $content );
			}
		}
		CoursePress_Data_Course::update_setting( $course->ID, true, $settings );
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return  __( 'Added content of default Course Completion pages.', 'CP_TD' );
	}

	/**
	 * Unit - Section Title (former Page title)
	 */
	private static function course_upgrade_unit_page_title( $course ) {
		$done = self::upgrade_step_check( $course->ID, __FUNCTION__ );
		if ( $done ) {
			return __( 'Units pages do not need to be updated.', 'CP_TD' );
		}
		$units = CoursePress_Data_Course::get_units( $course->ID, array( 'any' ), true );
		foreach ( $units as $unit_id ) {
			$page_title = get_post_meta( $unit_id, 'page_title', true );
			$titles = maybe_unserialize( $page_title );
			if ( empty( $titles ) ) {
				continue;
			}
			$new = array();
			$i = 1;
			foreach ( $titles as $title ) {
				$new[ 'page_'.$i++ ] = $title;
			}
			//delete_post_meta( $unit_id, 'page_title' );
			CoursePress_Helper_Utility::add_meta_unique( $unit_id, 'page_title', $new );
		}
		self::upgrade_step_set_done( $course->ID, __FUNCTION__ );
		return __( 'Section titles (former page titles) inside units converted.', 'CP_TD' );
	}

	/**
	 * Check is upgrade of this section needed?
	 */
	private static function upgrade_step_check( $course_id, $name ) {
		$meta_key = sprintf( '_cp_us_%s', $name );
		$done = get_post_meta( $course_id, $meta_key, true );
		return 'done' == $done;
	}

	/**
	 * Set upgrade is done for function.
	 */
	private static function upgrade_step_set_done( $course_id, $name ) {
		$meta_key = sprintf( '_cp_us_%s', $name );
		CoursePress_Helper_Utility::add_meta_unique( $course_id, $meta_key, 'done' );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'COURSE UPDATE: done: %s', $name ) );
		}
	}
}
