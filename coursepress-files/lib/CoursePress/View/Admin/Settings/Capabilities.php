<?php

class CoursePress_View_Admin_Settings_Capabilities {

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_capabilities', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_capabilities', array( __CLASS__, 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['capabilities'] = array(
			'title'       => __( 'Instructor Capabilities', CoursePress::TD ),
			'description' => __( 'Setup the capabilities of instructors within CoursePress.', CoursePress::TD ),
			'order'       => 30
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$instructor_capabilities = CoursePress_Model_Capabilities::get_instructor_capabilities();

		$content = '
			<input type="hidden" name="page" value="' . esc_attr( $slug ) . '"/>
			<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '"/>
			<input type="hidden" name="action" value="updateoptions"/>
		' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false );

		$boxes = self::_capability_boxes();
		foreach( $boxes as $method => $title ) {
			if( ! method_exists( __CLASS__, '_' . $method ) ) {
				continue;
			}

			$items = call_user_func( __CLASS__ . '::_' . $method );

			$content .= '
				<h3 class="hndle" style="cursor:auto;"><span>' . esc_html( $title ) . '</span></h3>
				<div class="inside">
					<table class="form-table compressed">
						<tbody id="items">';

			foreach( $items as $key => $value ) {
				$checked = ! empty( $instructor_capabilities[ $key ] );
				$checked_attr = $checked ? 'checked="checked"' : '';
				$content .= '
							<tr>
								<td><label><input type="checkbox" ' . $checked_attr . ' name="coursepress_settings[instructor][capabilities][' . $key . ']" value="1"> ' . esc_html( $value ) . '</label></td>
							</tr>

				';
			}

			$content .= '
						</tbody>
					</table>
				</div>
			';

		}


		return $content;

	}

	private static function _capability_boxes() {
		return array(
			'instructor_capabilities_general'           => __( 'General', CoursePress::TD ),
			'instructor_capabilities_courses'           => __( 'Courses', CoursePress::TD ),
			'instructor_capabilities_course_categories' => __( 'Course Categories', CoursePress::TD ),
			'instructor_capabilities_units'             => __( 'Units', CoursePress::TD ),
			'instructor_capabilities_instructors'       => __( 'Instructors', CoursePress::TD ),
			//'instructor_capabilities_classes' => __( 'Classes', CoursePress::TD ),
			'instructor_capabilities_students'          => __( 'Students', CoursePress::TD ),
			'instructor_capabilities_notifications'     => __( 'Notifications', CoursePress::TD ),
			'instructor_capabilities_discussions'       => __( 'Discussions', CoursePress::TD ),
			'instructor_capabilities_posts_and_pages'   => __( 'Posts and Pages', CoursePress::TD )
			//'instructor_capabilities_groups' => __( 'Settings Pages', CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_general() {
		return array(
			'coursepress_dashboard_cap'     => __( 'Access to plugin menu', CoursePress::TD ),
			'coursepress_courses_cap'       => __( 'Access to the Courses menu item', CoursePress::TD ),
			'coursepress_instructors_cap'   => __( 'Access to the Intructors menu item', CoursePress::TD ),
			'coursepress_students_cap'      => __( 'Access to the Students menu item', CoursePress::TD ),
			'coursepress_assessment_cap'    => __( 'Assessment', CoursePress::TD ),
			'coursepress_reports_cap'       => __( 'Reports', CoursePress::TD ),
			'coursepress_notifications_cap' => __( 'Notifications', CoursePress::TD ),
			'coursepress_discussions_cap'   => __( 'Discussions', CoursePress::TD ),
			'coursepress_settings_cap'      => __( 'Access to the Settings menu item', CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_courses() {
		return array(
			'coursepress_create_course_cap'           => __( 'Create new courses', CoursePress::TD ),
			'coursepress_update_course_cap'           => __( 'Update any assigned course', CoursePress::TD ),
			'coursepress_update_my_course_cap'        => __( 'Update courses made by the instructor only', CoursePress::TD ),
			// 'coursepress_update_all_courses_cap' => __( 'Update ANY course', CoursePress::TD ),
			'coursepress_delete_course_cap'           => __( 'Delete any assigned course', CoursePress::TD ),
			'coursepress_delete_my_course_cap'        => __( 'Delete courses made by the instructor only', CoursePress::TD ),
			// 'coursepress_delete_all_courses_cap' => __( 'Delete ANY course', CoursePress::TD ),
			'coursepress_change_course_status_cap'    => __( 'Change status of any assigned course', CoursePress::TD ),
			'coursepress_change_my_course_status_cap' => __( 'Change status of courses made by the instructor only', CoursePress::TD ),
			// 'coursepress_change_all_courses_status_cap' => __( 'Change status of ALL course', CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_course_categories() {
		return array(
			'coursepress_course_categories_manage_terms_cap' => __( 'Manage Categories', CoursePress::TD ),
			'coursepress_course_categories_edit_terms_cap'   => __( 'Edit Categories', CoursePress::TD ),
			'coursepress_course_categories_delete_terms_cap' => __( 'Delete Categories', CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_units() {
		return array(
			'coursepress_create_course_unit_cap'           => __( 'Create new course units', CoursePress::TD ),
			'coursepress_view_all_units_cap'               => __( 'View units in every course ( can view from other Instructors as well )', CoursePress::TD ),
			'coursepress_update_course_unit_cap'           => __( 'Update any unit (within assigned courses)', CoursePress::TD ),
			'coursepress_update_my_course_unit_cap'        => __( 'Update units made by the instructor only', CoursePress::TD ),
			// 'coursepress_update_all_courses_unit_cap' => __( 'Update units of ALL courses', CoursePress::TD ),
			'coursepress_delete_course_units_cap'          => __( 'Delete any unit (within assigned courses)', CoursePress::TD ),
			'coursepress_delete_my_course_units_cap'       => __( 'Delete course units made by the instructor only', CoursePress::TD ),
			// 'coursepress_delete_all_courses_units_cap' => __( 'Delete units of ALL courses', CoursePress::TD ),
			'coursepress_change_course_unit_status_cap'    => __( 'Change status of any unit (within assigned courses)', CoursePress::TD ),
			'coursepress_change_my_course_unit_status_cap' => __( 'Change statuses of course units made by the instructor only', CoursePress::TD ),
			// 'coursepress_change_all_courses_unit_status_cap' => __( 'Change status of any unit of ALL courses', CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_instructors() {
		return array(
			'coursepress_assign_and_assign_instructor_course_cap'    => __( 'Assign instructors to any course', CoursePress::TD ),
			'coursepress_assign_and_assign_instructor_my_course_cap' => __( 'Assign instructors to courses made by the instructor only', CoursePress::TD )
		);
	}

	private static function _instructor_capabilities_classes() {
		return array(
			'coursepress_add_new_classes_cap'    => __( 'Add new course classes to any course', CoursePress::TD ),
			'coursepress_add_new_my_classes_cap' => __( 'Add new course classes to courses made by the instructor only', CoursePress::TD ),
			'coursepress_delete_classes_cap'     => __( 'Delete any course class', CoursePress::TD ),
			'coursepress_delete_my_classes_cap'  => __( 'Delete course classes from courses made by the instructor only', CoursePress::TD )
		);
	}

	private static function _instructor_capabilities_students() {
		return array(
			'coursepress_invite_students_cap'               => __( 'Invite students to any course', CoursePress::TD ),
			'coursepress_invite_my_students_cap'            => __( 'Invite students to courses made by the instructor only', CoursePress::TD ),
			'coursepress_withdraw_students_cap'             => __( 'Withdraw students from any course', CoursePress::TD ),
			'coursepress_withdraw_my_students_cap'          => __( 'Withdraw students from courses made by the instructor only', CoursePress::TD ),
			'coursepress_add_move_students_cap'             => __( 'Add students to any course', CoursePress::TD ),
			'coursepress_add_move_my_students_cap'          => __( 'Add students to courses made by the instructor only', CoursePress::TD ),
			'coursepress_add_move_my_assigned_students_cap' => __( 'Add students to courses assigned to the instructor only', CoursePress::TD ),
			//'coursepress_change_students_group_class_cap' => __( "Change student's group", CoursePress::TD ),
			//'coursepress_change_my_students_group_class_cap' => __( "Change student's group within a class made by the instructor only", CoursePress::TD ),
			'coursepress_add_new_students_cap'              => __( 'Add new users with Student role to the blog', CoursePress::TD ),
			'coursepress_send_bulk_my_students_email_cap'   => __( "Send bulk e-mail to students", CoursePress::TD ),
			'coursepress_send_bulk_students_email_cap'      => __( "Send bulk e-mail to students within a course made by the instructor only", CoursePress::TD ),
			'coursepress_delete_students_cap'               => __( "Delete Students (deletes ALL associated course records)", CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_groups() {
		return array(
			'coursepress_settings_groups_page_cap' => __( 'View Groups tab within the Settings page', CoursePress::TD ),
			//'coursepress_settings_shortcode_page_cap' => __( 'View Shortcode within the Settings page', CoursePress::TD )
		);
	}

	private static function _instructor_capabilities_notifications() {
		return array(
			'coursepress_create_notification_cap'             => __( 'Create new notifications', CoursePress::TD ),
			'coursepress_create_my_notification_cap'          => __( 'Create new notifications for courses created by the instructor only', CoursePress::TD ),
			'coursepress_create_my_assigned_notification_cap' => __( 'Create new notifications for courses assigned to the instructor only', CoursePress::TD ),
			'coursepress_update_notification_cap'             => __( 'Update every notification', CoursePress::TD ),
			'coursepress_update_my_notification_cap'          => __( 'Update notifications made by the instructor only', CoursePress::TD ),
			'coursepress_delete_notification_cap'             => __( 'Delete every notification', CoursePress::TD ),
			'coursepress_delete_my_notification_cap'          => __( 'Delete notifications made by the instructor only', CoursePress::TD ),
			'coursepress_change_notification_status_cap'      => __( 'Change status of every notification', CoursePress::TD ),
			'coursepress_change_my_notification_status_cap'   => __( 'Change statuses of notifications made by the instructor only', CoursePress::TD )
		);
	}

	private static function _instructor_capabilities_discussions() {
		return array(
			'coursepress_create_discussion_cap'             => __( 'Create new discussions', CoursePress::TD ),
			'coursepress_create_my_discussion_cap'          => __( 'Create new discussions for courses created by the instructor only', CoursePress::TD ),
			'coursepress_create_my_assigned_discussion_cap' => __( 'Create new discussions for courses assigned to the instructor only', CoursePress::TD ),
			'coursepress_update_discussion_cap'             => __( 'Update every discussions', CoursePress::TD ),
			'coursepress_update_my_discussion_cap'          => __( 'Update discussions made by the instructor only', CoursePress::TD ),
			'coursepress_delete_discussion_cap'             => __( 'Delete every discussions', CoursePress::TD ),
			'coursepress_delete_my_discussion_cap'          => __( 'Delete discussions made by the instructor only', CoursePress::TD ),
		);
	}

	private static function _instructor_capabilities_posts_and_pages() {
		return array(
			'edit_pages'           => __( 'Edit Pages (required for MarketPress)', CoursePress::TD ),
			'edit_published_pages' => __( 'Edit Published Pages', CoursePress::TD ),
			'edit_posts'           => __( 'Edit Posts', CoursePress::TD ),
			'publish_pages'        => __( 'Publish Pages', CoursePress::TD ),
			'publish_posts'        => __( 'Publish Posts', CoursePress::TD )
		);
	}


	public static function process_form( $page, $tab ) {

		if ( isset( $_POST['action'] ) && 'updateoptions' === $_POST['action'] && 'capabilities' === $tab && wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) {

			$settings      = CoursePress_Core::get_setting( false ); // false returns all settings
			$post_settings = (array) $_POST['coursepress_settings'];

			// Now is a good time to make changes to $post_settings, especially to fix up unchecked checkboxes
			$caps = array_keys( CoursePress_Model_Capabilities::$capabilities['instructor'] );
			$set_caps = array_keys( $post_settings['instructor']['capabilities'] );
			foreach( $caps as $cap ) {
				if( ! in_array( $cap, $set_caps ) ) {
					$post_settings['instructor']['capabilities'][ $cap ] = 0;
				}
			}

			// Don't replace settings if there is nothing to replace
			if ( ! empty( $post_settings ) ) {
				CoursePress_Core::update_setting( false, CoursePress_Core::merge_settings( $settings, $post_settings ) ); // false will replace all settings
			}

		}
	}

}