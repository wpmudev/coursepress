<?php
global $wp_roles;

if ( isset( $_POST[ 'submit' ] ) && current_user_can( 'manage_options' ) ) {

	/* Set capabilities for each instructor user */

	$wp_user_search = new Instructor_Search();
	// $wp_user_search = new Instructor_Search( $usersearch, $page_num );

	foreach ( $wp_user_search->get_results() as $user ) {

		CoursePress_Capabilities::grant_private_caps( $user->ID );

		// Don't remove capabilities from administrators
		/* if( user_can( $user->ID, 'manage_options' ) ){
		  continue;
		  } */

		$role				 = new WP_User( $user->ID );
		$user_capabilities	 = $role->wp_capabilities;

		if ( isset( $_POST[ 'instructor_capability' ] ) ) {
			update_option( 'coursepress_instructor_capabilities', $_POST[ 'instructor_capability' ] );
			foreach ( $user_capabilities as $key => $old_cap ) {
				// Make sure to only remove CoursePress instructor capabilities
				if ( !in_array( $key, $_POST[ 'instructor_capability' ] ) &&
				in_array( $key, array_keys( CoursePress_Capabilities::$capabilities[ 'instructor' ] ) )
				) {//making the operation less expensive
					if ( !user_can( $user->ID, 'manage_options' ) ) {
						$role->remove_cap( $key );
					}
				}
			}

			foreach ( $_POST[ 'instructor_capability' ] as $new_cap ) {
				$role->add_cap( $new_cap );
			}
		} else {//all unchecked, remove all instructor capabilities
			foreach ( $user_capabilities as $key => $old_cap ) {
				if ( in_array( $key, array_keys( CoursePress_Capabilities::$capabilities[ 'instructor' ] ) ) ) {
					if ( !user_can( $user->ID, 'manage_options' ) ) {
						$role->remove_cap( $key );
					}
				}
			}
		}
	}
}

// The default capabilities for an instructor
$default_capabilities	 = array_keys( CoursePress_Capabilities::$capabilities[ 'instructor' ], 1 );
$instructor_capabilities = get_option( 'coursepress_instructor_capabilities', $default_capabilities );

$capability_boxes = array(
	'instructor_capabilities_general'			 => __( 'General', 'cp' ),
	'instructor_capabilities_courses'			 => __( 'Courses', 'cp' ),
	'instructor_capabilities_course_categories'	 => __( 'Course Categories', 'cp' ),
	'instructor_capabilities_units'				 => __( 'Units', 'cp' ),
	'instructor_capabilities_instructors'		 => __( 'Instructors', 'cp' ),
	//'instructor_capabilities_classes' => __( 'Classes', 'cp' ),
	'instructor_capabilities_students'			 => __( 'Students', 'cp' ),
	'instructor_capabilities_notifications'		 => __( 'Notifications', 'cp' ),
	'instructor_capabilities_discussions'		 => __( 'Discussions', 'cp' ),
	'instructor_capabilities_posts_and_pages'	 => __( 'Posts and Pages', 'cp' )
//'instructor_capabilities_groups' => __( 'Settings Pages', 'cp' ),
);

$instructor_capabilities_general = array(
	'coursepress_dashboard_cap'		 => __( 'Access to plugin menu', 'cp' ),
	'coursepress_courses_cap'		 => __( 'Access to the Courses menu item', 'cp' ),
	'coursepress_instructors_cap'	 => __( 'Access to the Intructors menu item', 'cp' ),
	'coursepress_students_cap'		 => __( 'Access to the Students menu item', 'cp' ),
	'coursepress_assessment_cap'	 => __( 'Assessment', 'cp' ),
	'coursepress_reports_cap'		 => __( 'Reports', 'cp' ),
	'coursepress_notifications_cap'	 => __( 'Notifications', 'cp' ),
	'coursepress_discussions_cap'	 => __( 'Discussions', 'cp' ),
	'coursepress_settings_cap'		 => __( 'Access to the Settings menu item', 'cp' ),
);

$instructor_capabilities_courses = array(
	'coursepress_create_course_cap'				 => __( 'Create new courses', 'cp' ),
	'coursepress_update_course_cap'				 => __( 'Update any assigned course', 'cp' ),
	'coursepress_update_my_course_cap'			 => __( 'Update courses made by the instructor only', 'cp' ),
	// 'coursepress_update_all_courses_cap' => __( 'Update ANY course', 'cp' ),
	'coursepress_delete_course_cap'				 => __( 'Delete any assigned course', 'cp' ),
	'coursepress_delete_my_course_cap'			 => __( 'Delete courses made by the instructor only', 'cp' ),
	// 'coursepress_delete_all_courses_cap' => __( 'Delete ANY course', 'cp' ),
	'coursepress_change_course_status_cap'		 => __( 'Change status of any assigned course', 'cp' ),
	'coursepress_change_my_course_status_cap'	 => __( 'Change status of courses made by the instructor only', 'cp' ),
 // 'coursepress_change_all_courses_status_cap' => __( 'Change status of ALL course', 'cp' ),
);

$instructor_capabilities_course_categories = array(
	'coursepress_course_categories_manage_terms_cap' => __( 'Manage Categories', 'cp' ),
	'coursepress_course_categories_edit_terms_cap'	 => __( 'Edit Categories', 'cp' ),
	'coursepress_course_categories_delete_terms_cap' => __( 'Delete Categories', 'cp' ),
);

$instructor_capabilities_units = array(
	'coursepress_create_course_unit_cap'			 => __( 'Create new course units', 'cp' ),
	'coursepress_view_all_units_cap'				 => __( 'View units in every course ( can view from other Instructors as well )', 'cp' ),
	'coursepress_update_course_unit_cap'			 => __( 'Update any unit (within assigned courses)', 'cp' ),
	'coursepress_update_my_course_unit_cap'			 => __( 'Update units made by the instructor only', 'cp' ),
	// 'coursepress_update_all_courses_unit_cap' => __( 'Update units of ALL courses', 'cp' ),	
	'coursepress_delete_course_units_cap'			 => __( 'Delete any unit (within assigned courses)', 'cp' ),
	'coursepress_delete_my_course_units_cap'		 => __( 'Delete course units made by the instructor only', 'cp' ),
	// 'coursepress_delete_all_courses_units_cap' => __( 'Delete units of ALL courses', 'cp' ),
	'coursepress_change_course_unit_status_cap'		 => __( 'Change status of any unit (within assigned courses)', 'cp' ),
	'coursepress_change_my_course_unit_status_cap'	 => __( 'Change statuses of course units made by the instructor only', 'cp' ),
 // 'coursepress_change_all_courses_unit_status_cap' => __( 'Change status of any unit of ALL courses', 'cp' ),
);

$instructor_capabilities_instructors = array(
	'coursepress_assign_and_assign_instructor_course_cap'	 => __( 'Assign instructors to any course', 'cp' ),
	'coursepress_assign_and_assign_instructor_my_course_cap' => __( 'Assign instructors to courses made by the instructor only', 'cp' )
);

$instructor_capabilities_classes = array(
	'coursepress_add_new_classes_cap'	 => __( 'Add new course classes to any course', 'cp' ),
	'coursepress_add_new_my_classes_cap' => __( 'Add new course classes to courses made by the instructor only', 'cp' ),
	'coursepress_delete_classes_cap'	 => __( 'Delete any course class', 'cp' ),
	'coursepress_delete_my_classes_cap'	 => __( 'Delete course classes from courses made by the instructor only', 'cp' )
);

$instructor_capabilities_students = array(
	'coursepress_invite_students_cap'				 => __( 'Invite students to any course', 'cp' ),
	'coursepress_invite_my_students_cap'			 => __( 'Invite students to courses made by the instructor only', 'cp' ),
	'coursepress_withdraw_students_cap'				 => __( 'Withdraw students from any course', 'cp' ),
	'coursepress_withdraw_my_students_cap'			 => __( 'Withdraw students from courses made by the instructor only', 'cp' ),
	'coursepress_add_move_students_cap'				 => __( 'Add students to any course', 'cp' ),
	'coursepress_add_move_my_students_cap'			 => __( 'Add students to courses made by the instructor only', 'cp' ),
	'coursepress_add_move_my_assigned_students_cap'	 => __( 'Add students to courses assigned to the instructor only', 'cp' ),
	//'coursepress_change_students_group_class_cap' => __( "Change student's group", 'cp' ),
	//'coursepress_change_my_students_group_class_cap' => __( "Change student's group within a class made by the instructor only", 'cp' ),
	'coursepress_add_new_students_cap'				 => __( 'Add new users with Student role to the blog', 'cp' ),
	'coursepress_send_bulk_my_students_email_cap'	 => __( "Send bulk e-mail to students", 'cp' ),
	'coursepress_send_bulk_students_email_cap'		 => __( "Send bulk e-mail to students within a course made by the instructor only", 'cp' ),
	'coursepress_delete_students_cap'				 => __( "Delete Students (deletes ALL associated course records)", 'cp' ),
);

$instructor_capabilities_groups = array(
	'coursepress_settings_groups_page_cap' => __( 'View Groups tab within the Settings page', 'cp' ),
 //'coursepress_settings_shortcode_page_cap' => __( 'View Shortcode within the Settings page', 'cp' )
);

$instructor_capabilities_notifications = array(
	'coursepress_create_notification_cap'				 => __( 'Create new notifications', 'cp' ),
	'coursepress_create_my_notification_cap'			 => __( 'Create new notifications for courses created by the instructor only', 'cp' ),
	'coursepress_create_my_assigned_notification_cap'	 => __( 'Create new notifications for courses assigned to the instructor only', 'cp' ),
	'coursepress_update_notification_cap'				 => __( 'Update every notification', 'cp' ),
	'coursepress_update_my_notification_cap'			 => __( 'Update notifications made by the instructor only', 'cp' ),
	'coursepress_delete_notification_cap'				 => __( 'Delete every notification', 'cp' ),
	'coursepress_delete_my_notification_cap'			 => __( 'Delete notifications made by the instructor only', 'cp' ),
	'coursepress_change_notification_status_cap'		 => __( 'Change status of every notification', 'cp' ),
	'coursepress_change_my_notification_status_cap'		 => __( 'Change statuses of notifications made by the instructor only', 'cp' )
);

$instructor_capabilities_discussions = array(
	'coursepress_create_discussion_cap'				 => __( 'Create new discussions', 'cp' ),
	'coursepress_create_my_discussion_cap'			 => __( 'Create new discussions for courses created by the instructor only', 'cp' ),
	'coursepress_create_my_assigned_discussion_cap'	 => __( 'Create new discussions for courses assigned to the instructor only', 'cp' ),
	'coursepress_update_discussion_cap'				 => __( 'Update every discussions', 'cp' ),
	'coursepress_update_my_discussion_cap'			 => __( 'Update discussions made by the instructor only', 'cp' ),
	'coursepress_delete_discussion_cap'				 => __( 'Delete every discussions', 'cp' ),
	'coursepress_delete_my_discussion_cap'			 => __( 'Delete discussions made by the instructor only', 'cp' ),
);

$instructor_capabilities_posts_and_pages = array(
	'edit_pages'			 => __( 'Edit Pages (required for MarketPress)', 'cp' ),
	'edit_published_pages'	 => __( 'Edit Published Pages', 'cp' ),
	'edit_posts'			 => __( 'Edit Posts', 'cp' ),
	'publish_pages'			 => __( 'Publish Pages', 'cp' ),
	'publish_posts'			 => __( 'Publish Posts', 'cp' )
);
?>
<div id="poststuff" class="metabox-holder m-settings cp-wrap">
	<form action='' method='post'>

		<?php
		wp_nonce_field( 'update-coursepress-options' );
		?>
		<p class='description'><?php printf( __( 'Instructor capabilities define what the Instructors can or cannot do within the %s.', 'cp' ), $this->name ); ?></p>
		<?php
		foreach ( $capability_boxes as $box_key => $group_name ) {
			?>
			<div class="postbox">
				<h3 class="hndle" style='cursor:auto;'><span><?php echo $group_name; ?></span></h3>

				<div class="inside">

					<table class="form-table">
						<tbody id="items">
							<?php
							foreach ( ${$box_key} as $key => $value ) {
								?>
								<tr>
									<td width="50%"><?php echo $value; ?></td>
									<td><input type="checkbox" <?php
										// if ( array_key_exists( $key, $instructor_capabilities ) ) {
										if ( in_array( $key, $instructor_capabilities ) ) {
											echo 'checked';
										}
										?> name="instructor_capability[]" value="<?php echo $key; ?>"></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<!--/inside-->

			</div><!--/postbox-->
		<?php } ?>

		<p class="save-shanges">
			<?php submit_button( __( 'Save Changes', 'cp' ) ); ?>
		</p>

	</form>
</div>