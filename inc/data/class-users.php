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
			'coursepress_assessment_cap' => 1,
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
			'coursepress_add_move_my_assigned_students_cap' => 1,
			/* Notifications */
			'coursepress_create_my_assigned_notification_cap' => 1,
			'coursepress_create_my_notification_cap' => 1,
			'coursepress_update_notification_cap' => 1,
			'coursepress_update_my_notification_cap' => 1,
			'coursepress_delete_notification_cap' => 1,
			'coursepress_delete_my_notification_cap' => 1,
			'coursepress_change_notification_status_cap' => 0,
			'coursepress_change_my_notification_status_cap' => 1,
			/* Discussions */
			'coursepress_create_my_assigned_discussion_cap' => 1,
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
			/* Posts and Pages */
			//'edit_pages' => 0,
			//'edit_published_pages' => 0,
			//'edit_posts' => 0,
			//'publish_pages' => 0,
			//'publish_posts' => 0,
			//'edit_comments' => 1,
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
		add_action( 'delete_user', array( $this, 'delete_student_data' ) );
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
			$user->add_role( 'coursepress_instructor' );
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

	public function get_instructor_caps() {
		if ( ! $this->__get( 'instructor_caps' ) ) {
			$cp_caps = coursepress_get_setting( 'capabilities/instructor', $this->capabilities['instructor'] );
			$this->__set( 'instructor_caps', $cp_caps );
		}
		return $this->__get( 'instructor_caps' );
	}

	public function get_facilitator_caps() {
		if ( ! $this->__get( 'facilitator_caps' ) ) {
			$cp_caps = coursepress_get_setting( 'capabilities/facilitator', $this->capabilities['facilitator'] );
			$this->__set( 'facilitator_caps', $cp_caps );
		}
		return $this->__get( 'facilitator_caps' );
	}

	public function map_coursepress_user_cap( $caps, $cap, $args, $user ) {
		// Set all CP caps for administrator
		if ( in_array( 'administrator', $user->roles ) ) {
			$caps = wp_parse_args( $this->get_all_caps(), $caps );
		}
		// Set instructor caps
		if ( in_array( 'coursepress_instructor', $user->roles ) ) {
			$caps = wp_parse_args( $this->get_instructor_caps(), $caps );
		}
		// Set facilitator caps
		if ( in_array( 'coursepress_facilitator', $user->roles ) ) {
			$caps = wp_parse_args( $this->get_facilitator_caps(), $caps );
		}
		return $caps;
	}

	public function delete_student_id( $user_id ) {
		$user = coursepress_get_user( $user_id );
		if ( is_wp_error( $user ) ) {
			return null; }
		// Find courses where user are enrolled at
		$course_ids = $user->get_enrolled_courses_ids();
		if ( is_array( $course_ids ) && ! empty( $course_ids ) ) {
			foreach ( $course_ids as $course_id ) {
				coursepress_delete_student( $user_id, $course_id );
			}
		}
	}
}
