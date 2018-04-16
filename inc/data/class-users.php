<?php
/**
 * Class CoursePress_Data_Users
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Users extends CoursePress_Utility {
	protected $capabilities_map = false;

	protected $capabilities = array(
		'instructor' => array(
			/* General */
			'coursepress_dashboard_cap' => 1,
			'coursepress_courses_cap' => 1,
			'coursepress_students_cap' => 1,
			'coursepress_instructors_cap' => 1,
			'coursepress_assessments_cap' => 1,
			'coursepress_reports_cap' => 1,
			'coursepress_notifications_cap' => 1,
			'coursepress_discussions_cap' => 1,
			'coursepress_comments_cap' => 1,
			'coursepress_settings_cap' => 1,
			/* Courses */
			'coursepress_create_course_cap' => 1,
			'coursepress_view_others_course_cap' => 1,
			'coursepress_update_course_cap' => 1,
			'coursepress_update_my_course_cap' => 1,
			'coursepress_update_all_courses_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_delete_course_cap' => 1,
			'coursepress_delete_my_course_cap' => 1,
			'coursepress_delete_all_courses_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_change_course_status_cap' => 0,
			'coursepress_change_my_course_status_cap' => 1,
			'coursepress_change_all_courses_status_cap' => 0, // NOT IMPLEMENTED YET
			/* Units */
			'coursepress_create_course_unit_cap' => 1,
			'coursepress_view_all_units_cap' => 0,
			'coursepress_update_course_unit_cap' => 1,
			'coursepress_update_my_course_unit_cap' => 1,
			'coursepress_update_all_courses_unit_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_delete_course_units_cap' => 1,
			'coursepress_delete_my_course_units_cap' => 1,
			'coursepress_delete_all_courses_units_cap' => 0, // NOT IMPLEMENTED YET
			'coursepress_change_course_unit_status_cap' => 1,
			'coursepress_change_my_course_unit_status_cap' => 1,
			'coursepress_change_all_courses_unit_status_cap' => 0, // NOT IMPLEMENTED YET
			/* Instructors */
			'coursepress_assign_and_assign_instructor_course_cap' => 0,
			'coursepress_assign_and_assign_instructor_my_course_cap' => 1,
			/* Facilitators */
			'coursepress_assign_my_course_facilitator_cap' => 1,
			'coursepress_assign_facilitator_cap' => 1,
			/* Classes */
			'coursepress_add_new_classes_cap' => 0,
			'coursepress_add_new_my_classes_cap' => 0,
			'coursepress_delete_classes_cap' => 0,
			'coursepress_delete_my_classes_cap' => 0,
			/* Students */
			'coursepress_invite_students_cap' => 0,
			'coursepress_invite_my_students_cap' => 1,
			'coursepress_withdraw_students_cap' => 0,
			'coursepress_withdraw_my_students_cap' => 1,
			'coursepress_add_move_students_cap' => 0,
			'coursepress_add_move_my_students_cap' => 1,
			'coursepress_add_move_students_cap' => 1,
			/* Notifications */
			'coursepress_create_notification_cap' => 1,
			'coursepress_create_my_notification_cap' => 1,
			'coursepress_update_notification_cap' => 1,
			'coursepress_update_my_notification_cap' => 1,
			'coursepress_delete_notification_cap' => 1,
			'coursepress_delete_my_notification_cap' => 1,
			'coursepress_change_notification_status_cap' => 0,
			'coursepress_change_my_notification_status_cap' => 1,
			/* Discussions */
			'coursepress_create_discussion_cap' => 1,
			'coursepress_create_my_discussion_cap' => 1,
			'coursepress_update_discussion_cap' => 0,
			'coursepress_update_my_discussion_cap' => 1,
			'coursepress_delete_discussion_cap' => 0,
			'coursepress_delete_my_discussion_cap' => 1,
			'coursepress_change_discussion_status_cap' => 0,
			'coursepress_change_my_discussion_status_cap' => 1,
			/* Certificates */
			'coursepress_certificates_cap' => 0,
			'coursepress_create_certificates_cap' => 0,
			'coursepress_update_certificates_cap' => 0,
			'coursepress_delete_certificates_cap' => 0,
			/* Course Categories */
			'coursepress_course_categories_manage_terms_cap' => 1,
			'coursepress_course_categories_edit_terms_cap' => 1,
			'coursepress_course_categories_delete_terms_cap' => 0,
			/* Pages */
			'edit_other_pages' => 0,
			'edit_pages' => 0,
			'edit_published_pages' => 0,
			'publish_pages' => 0,
			/* Posts*/
			'edit_other_posts' => 0,
			'edit_posts' => 0,
			'edit_published_posts' => 0,
			'publish_posts' => 0,
			/* Other */
			'edit_comments' => 1,
			'read' => 1,
		),
		'facilitator' => array(),
	);

	public function __construct() {
		// Hook into `coursepress_add_instructor`
		add_action( 'coursepress_add_instructor', array( $this, 'add_instructor_meta' ), 10, 2 );
		// Hook into `coursepress_delete_instructor`
		add_action( 'coursepress_delete_instructor', array( $this, 'remove_instructor_meta' ), 10, 2 );
		// Hook into `coursepress_add_student`
		add_action( 'coursepress_add_student', array( $this, 'add_student_meta' ), 10, 2 );
		// Hook into `coursepress_delete_student`
		add_action( 'coursepress_delete_student', array( $this, 'delete_student_meta' ), 10, 2 );
		// Hook into `coursepress_add_facilitator`
		add_action( 'coursepress_add_facilitator', array( $this, 'add_facilitator_meta' ), 10, 2 );
		// Hook into `coursepress_remove_facilitator`
		add_action( 'coursepress_remove_facilitator', array( $this, 'delete_facilitator_meta' ), 10, 2 );
		// Map coursepress caps
		add_filter( 'user_has_cap', array( $this, 'map_coursepress_user_cap' ), 99, 4 );
		// Delete student data whenever a user is deleted
		add_action( 'delete_user', array( $this, 'delete_student_id' ) );
		/**
		 * log user activity
		 */
		add_action( 'wp_login', array( $this, 'log_student_activity_login' ), 10, 2 );
		add_action( 'coursepress_add_student', array( $this, 'log_student_activity_enroll' ), 10, 2 );
		add_action( 'coursepress_get_template', array( $this, 'log_student_activity_course' ), 10, 5 );
		add_action( 'coursepress_record_response', array( $this, 'log_student_activity_answer' ), 10, 2 );
	}

	public function add_instructor_meta( $user_id, $course_id ) {
		// Maybe add instructor role?
		$this->add_instructor_role( $user_id );
		// Set instructor user meta
		add_user_meta( $user_id, 'instructor_' . $course_id, $user_id );
	}

	public function add_instructor_role( $user_id ) {
		if ( ! user_can( $user_id, 'coursepress_instructor' ) ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$user->add_role( 'coursepress_instructor' );
			}
		}
	}

	public function remove_instructor_meta( $user_id, $course_id ) {
		// Maybe remove instructor role?
		$this->remove_instructor_role( $user_id );
		// Remove user meta as instructor
		delete_user_meta( $user_id, 'instructor_' . $course_id, $user_id );
	}

	public function remove_instructor_role( $user_id ) {
		$course_ids = coursepress_get_user_instructed_courses( $user_id, false, true, true );
		if ( count( $course_ids ) <= 0 ) {
			$user = get_userdata( $user_id );
			$user->remove_role( 'coursepress_instructor' );

			delete_user_option( $user_id, 'role_ins', ! coursepress_user_meta_prefix_required() );
		}
	}

	public function add_student_meta( $user_id, $course_id ) {
		// Maybe add student role?
		$this->add_student_role( $user_id );
		// Add user student meta
		add_user_meta( $user_id, 'student_' . $course_id, $user_id );
		add_user_meta( $user_id, 'enrolled_course_date_' . $course_id, current_time( 'mysql' ) );
	}

	public function add_student_role( $user_id ) {
		if ( ! user_can( $user_id, 'coursepress_student' ) ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$user->add_role( 'coursepress_student' );
			}
		}
	}

	public function delete_student_meta( $user_id, $course_id ) {
		// Maybe delte student role?
		$this->delete_student_role( $user_id );
		// Delete user student meta
		delete_user_meta( $user_id, 'student_' . $course_id, $user_id );
	}

	public function delete_student_role( $user_id ) {
		$enrolled_courses_ids = coursepress_get_enrolled_courses( $user_id, false, true, true );
		if ( count( $enrolled_courses_ids ) <= 0 ) {
			$user = get_userdata( $user_id );
			$user->remove_role( 'coursepress_student' );
		}
	}

	public function add_facilitator_meta( $user_id, $course_id ) {
		// Maybe add facilitator role?
		$this->add_facilitator_role( $user_id );
		// Set user facilitator meta
		add_user_meta( $user_id, 'facilitator_' . $course_id, $user_id );
	}

	public function add_facilitator_role( $user_id ) {
		if ( ! user_can( $user_id, 'coursepress_facilitator' ) ) {
			$user = get_userdata( $user_id );
			$user->add_role( 'coursepress_facilitator' );
		}
	}

	public function delete_facilitator_meta( $user_id, $course_id ) {
		// Maybe delete facilitator role?
		$this->delete_facilitator_role( $user_id );
		// Delete user facilitator meta
		delete_user_meta( $user_id, 'facilitator_' . $course_id, $user_id );
	}

	public function delete_facilitator_role( $user_id ) {
		$facilitated_courses = coursepress_get_user_facilitated_courses( $user_id, false, true, true );
		if ( count( $facilitated_courses ) <= 0 ) {
			$user = get_userdata( $user_id );
			$user->remove_role( 'coursepress_facilitator' );
		}
	}

	public function get_all_caps() {
		if ( ! $this->__get( 'allcaps' ) ) {
			$allcaps = array_map( '__return_true', $this->capabilities['instructor'] );
			$this->__set( 'allcaps', $allcaps );
		}
		return $this->__get( 'allcaps' );
	}

	/**
	 * Filter capabilities by value
	 *
	 * @since 3.0.0
	 *
	 */
	private function filter_caps( $allcaps ) {
		$caps = array();
		foreach ( $allcaps as $key => $value ) {
			if ( $value ) {
				$caps[ $key ] = $value;
			}
		}
		return $caps;
	}

	/**
	 * Get instructor capabilities.
	 *
	 * @since 3.0.0
	 *
	 */
	public function get_instructor_caps() {
		if ( ! $this->__get( 'instructor_caps' ) ) {
			$cp_caps = coursepress_get_setting( 'capabilities/instructor', $this->capabilities['instructor'] );
			$cp_caps = $this->filter_caps( $cp_caps );
			$this->__set( 'instructor_caps', $cp_caps );
		}
		return $this->__get( 'instructor_caps' );
	}

	/**
	 * Get facilitator capabilities.
	 *
	 * @since 3.0.0
	 *
	 */
	public function get_facilitator_caps() {
		if ( ! $this->__get( 'facilitator_caps' ) ) {
			$cp_caps = coursepress_get_setting( 'capabilities/facilitator', $this->capabilities['facilitator'] );
			$cp_caps = $this->filter_caps( $cp_caps );
			$this->__set( 'facilitator_caps', $cp_caps );
		}
		return $this->__get( 'facilitator_caps' );
	}

	public function map_coursepress_user_cap( $caps, $cap, $args, $user ) {
		// Set all CP caps for administrator
		if ( in_array( 'administrator', $user->roles, true ) ) {
			$caps = wp_parse_args( $this->get_all_caps(), $caps );
		}
		// Set instructor caps
		if ( in_array( 'coursepress_instructor', $user->roles, true ) ) {
			$caps = wp_parse_args( $this->get_instructor_caps(), $caps );
		}
		// Set facilitator caps
		if ( in_array( 'coursepress_facilitator', $user->roles, true ) ) {
			$caps = wp_parse_args( $this->get_facilitator_caps(), $caps );
		}
		return $caps;
	}

	/**
	 * Remove student from assigned courses when deleted.
	 *
	 * When a WP user is deleted, remove user from all his
	 * assigned courses.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return null
	 */
	public function delete_student_id( $user_id ) {
		$user = coursepress_get_user( $user_id );
		if ( is_wp_error( $user ) ) {
			return null;
		}
		// Find courses where user are enrolled at
		$course_ids = $user->get_enrolled_courses_ids();
		if ( is_array( $course_ids ) && ! empty( $course_ids ) ) {
			foreach ( $course_ids as $course_id ) {
				coursepress_delete_student( $user_id, $course_id );
			}
		}
	}

	/**
	 * Save last Student Activity,
	 *
	 * @since 2.0.0
	 *
	 * @param integer $user_id Student ID.
	 * @param string $kind Activity kind.
	 * @param integer $extra_id Extra id.
	 */
	public static function log_student_activity( $kind = 'login', $user_id = 0, $extra_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		if ( empty( $user_id ) ) {
			return;
		}
		$success = add_user_meta( $user_id, 'latest_activity', time(), true );
		if ( ! $success ) {
			update_user_meta( $user_id, 'latest_activity', time() );
		}
		$allowed_kinds = array(
			'course_module_seen',
			'course_seen',
			'course_unit_seen',
			'course_step_seen',
			'enrolled',
			'login',
			'module_answered',
			'unknown',
		);
		if ( ! in_array( $kind, $allowed_kinds, true ) ) {
			$kind = 'unknown';
		}
		$success = add_user_meta( $user_id, 'latest_activity_kind', $kind, true );
		if ( ! $success ) {
			update_user_meta( $user_id, 'latest_activity_kind', $kind );
		}
		/**
		 * Add extra ID
		 */
		if ( is_integer( $extra_id ) && 0 < $extra_id ) {
			$success = add_user_meta( $user_id, 'latest_activity_id', $extra_id, true );
			if ( ! $success ) {
				update_user_meta( $user_id, 'latest_activity_id', $extra_id );
			}
		}
	}

	/**
	 * Save student activity - login
	 *
	 * @since 2.0.0
	 */
	public function log_student_activity_login( $user_login, $user ) {
		self::log_student_activity( 'login', $user->ID );
	}

	/**
	 * Save student activity - enroll
	 *
	 * @since 3.0.0
	 */
	public function log_student_activity_enroll( $user_id, $course_id ) {
		self::log_student_activity( 'enrolled', $user_id, $course_id );
	}

	/**
	 * Save student activity - course
	 *
	 * @since 3.0.0
	 */
	public function log_student_activity_course( $type, $course_id, $unit_id, $step_id, $module_id ) {
		switch ( $type ) {
			case 'unit':
				self::log_student_activity( 'course_unit_seen', null, $unit_id );
				break;
			case 'module':
				self::log_student_activity( 'course_module_seen', null, $module_id );
				break;
			case 'step':
				self::log_student_activity( 'course_step_seen', null, $step_id );
				break;
		}
	}

	/**
	 * Save student activity - progress
	 *
	 * @since 3.0.0
	 */
	public function log_student_activity_answer( $step_id ) {
		$user_id = get_current_user_id();
		self::log_student_activity( 'module_answered', $user_id, $step_id );
	}
}
