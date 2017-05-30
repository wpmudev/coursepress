<?php
/**
 * Class CoursePress_User
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_User extends CoursePress_Utility {
	/**
	 * @var string
	 */
	protected $user_type = 'guest'; // Default to guest user

	/**
	 * @var array of user CP capabilities
	 */
	protected $user_caps = array();

	protected $capabilities = array(
		'instructor' => array(
			/* General */
			'coursepress_dashboard_cap' => 1,
			'coursepress_courses_cap' => 1,
			'coursepress_instructors_cap' => 0, // DEPRECATED
			'coursepress_students_cap' => 1,
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
			'edit_pages' => 0,
			'edit_published_pages' => 0,
			'edit_posts' => 0,
			'publish_pages' => 0,
			'publish_posts' => 0,
			'edit_comments' => 1,
		),
		'facilitator' => array(
		)
	);

	/**
	 * CoursePress_User constructor.
	 *
	 * @param bool|int|WP_User $user
	 */
	public function __construct( $user = 0 ) {
		if ( ! $user instanceof WP_User ) {
			$user = get_userdata( (int) $user );
		}

		if ( empty( $user ) )
			return;

		// Inherit WP_User object
		foreach ( $user as $key => $value )
			$this->__set( $key, $value );

		// Set user capabilities
		$this->setup_caps();
	}

	function is_super_admin() {
		return in_array( 'administrator', $this->roles );
	}

	function is_instructor() {
		return in_array( 'coursepress_instructor', $this->roles );
	}

	function is_facilitator() {
		return in_array( 'coursepress_facilitator', $this->roles );
	}

	function setup_caps() {
		global $current_user;

		$user_caps = array();

		if ( $this->is_super_admin() )
			$user_caps = array_map( '__return_true', $this->capabilities['instructor'] );
		elseif ( $this->is_instructor() )
			$user_caps = coursepress_get_setting( 'capabilities/instructor', $this->capabilities['instructor'] );
		elseif ( $this->is_facilitator() )
			$user_caps = coursepress_get_setting( 'capabilities/facilitator', $this->capabilities['facilitator'] );

		if ( isset( $current_user->allcaps ) )
			$current_user->allcaps = wp_parse_args( $current_user->allcaps, $user_caps );
		if ( isset( $current_user->caps ) )
			$current_user->caps = wp_parse_args( $current_user->caps, $user_caps );

		$this->user_caps = $user_caps;
	}

	function get_accessable_courses( $publish = true, $ids = false, $all = true ) {
		$courses = array();

		$args = array(
			'post_status' => $publish ? 'publish' : 'any',
		);

		if ( $ids )
			$args['fields'] = 'ids';
		if ( $all )
			$args['posts_per_page'] = -1;

		if ( $this->is_super_admin() )
			$courses = coursepress_get_courses( $args );
		elseif ( $this->is_instructor() || $this->is_facilitator() ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'meta_key' => 'instructor',
					'meta_value' => $this->ID,
				),
				array(
					'meta_key' => 'facilitator',
					'meta_value' => $this->ID,
				),
			);
			$courses = coursepress_get_courses( $args );
		}

		return $courses;
	}
}