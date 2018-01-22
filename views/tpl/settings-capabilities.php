<script type="text/template" id="coursepress-capabilities-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php _e( 'Instructor Capabilities', 'cp' ); ?></h2>
	</div>

	<div class="cp-box-content cp-caps-list cp-odd">
        <h3 class="label"><?php _e( 'Browse capabilities', 'cp' ); ?></h3>
        <ul class="cp-input-group cp-select-list cp-capabilities">
            <li data-id="cp-cap-general" class="active"><?php _e( 'General', 'cp' ); ?></li>
            <li data-id="cp-cap-courses"><?php _e( 'Courses', 'cp' ); ?></li>
            <li data-id="cp-cap-units"><?php _e( 'Units', 'cp' ); ?></li>
            <li data-id="cp-cap-instructors"><?php _e( 'Instructors', 'cp' ); ?></li>
            <li data-id="cp-cap-students"><?php _e( 'Students', 'cp' ); ?></li>
            <li data-id="cp-cap-notifications"><?php _e( 'Notifications', 'cp' ); ?></li>
            <li data-id="cp-cap-discussions"><?php _e( 'Discussions', 'cp' ); ?></li>
        </ul>
	</div>

	<?php
    $config = array();
    $toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );

    // General capabilities.
    $config['capabilities/general'] = array(
        'title' => __( 'General', 'cp' ),
        'id' => 'cp-cap-general',
        'description' => __( 'Instructor of my courses can:', 'cp' ),
        'fields' => array(
            'coursepress_dashboard_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access the main CoursePress menu', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_dashboard_cap', true ),
            ),
            'coursepress_courses_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to Courses submenu', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_courses_cap', true ),
            ),
            'coursepress_instructors_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to the Intructors page', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_instructors_cap', true ),
            ),
            'coursepress_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to the Students page', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_students_cap', true ),
            ),
            'coursepress_assessments_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to the Assessments page', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assessments_cap', true ),
            ),
            'coursepress_notifications_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to the Notifications page', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_notifications_cap', true ),
            ),
            'coursepress_discussions_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to the Discussions page', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_discussions_cap', true ),
            ),
            'coursepress_settings_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Access to the Settings page', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_settings_cap', true ),
            ),
        ),
    );

    // Course capabilities.
    $config['capabilities/courses'] = array(
        'title' => __( 'Courses', 'cp' ),
        'id' => 'cp-cap-courses',
        'fields' => array(
            'coursepress_create_course_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Edit courses', 'cp' ),
                //'desc' => __( 'Allow instructor to create, edit and delete own courses.', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_course_cap', true ),
            ),
            'coursepress_update_assigned_courses' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update assigned courses', 'cp' ),
                //'desc' => __( 'Allow user to edit courses where user is an instructor at.', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_assigned_courses', true ),
            ),
            'coursepress_update_course_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update any course', 'cp' ),
                //'desc' => __( 'Allow instructor to update any course.', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_course_cap', true ),
            ),
            'coursepress_delete_my_course_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete own courses', 'cp' ),
                //'desc' => __( 'Allow user to delete courses where user is the author.', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_course_cap', true ),
            ),
            'coursepress_delete_assigned_course' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete assigned courses', 'cp' ),
                //'desc' => __( 'Allow user to delete courses where user is an instructor at.', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_assigned_course', true ),
            ),
            'coursepress_change_course_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change status of any assigned course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_course_status_cap', true ),
            ),
            'coursepress_change_my_course_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change status of courses made by the instructor only', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_course_status_cap', true ),
            ),
        ),
    );

    // Unit capabilities.
    $config['capabilities/unit'] = array(
        'title' => __( 'Units', 'cp' ),
        'id' => 'cp-cap-units',
        'fields' => array(
            'coursepress_create_course_unit_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Create new course units', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_course_unit_cap', true ),
            ),
            'coursepress_view_all_units_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'View units in every course ( can view from other Instructors as well )', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_view_all_units_cap', true ),
            ),
            'coursepress_update_my_course_unit_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update units made by the instructor only', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_course_unit_cap', true ),
            ),
            'coursepress_update_course_unit_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update any unit (within assigned courses)', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_course_unit_cap', true ),
            ),
            'coursepress_delete_my_course_units_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete course units made by the instructor only', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_course_units_cap', true ),
            ),
            'coursepress_delete_course_units_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete any unit (within assigned courses)', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_course_units_cap', true ),
            ),
            'coursepress_change_course_unit_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change status of any unit (within assigned courses)', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_course_unit_status_cap', true ),
            ),
            'coursepress_change_my_course_unit_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change statuses of course units made by the instructor only', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_course_unit_status_cap', true ),
            ),
        ),
    );

    // Instructors capabilities.
    $config['capabilities/instructors'] = array(
        'title' => __( 'Instructors', 'cp' ),
        'id' => 'cp-cap-instructors',
        'fields' => array(
            'coursepress_assign_and_assign_instructor_my_course_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Assign instructors to any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assign_and_assign_instructor_my_course_cap', true ),
            ),
            'coursepress_assign_and_assign_instructor_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Assign instructors to courses made by the instructor only )', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assign_and_assign_instructor_cap', true ),
            ),
        ),
    );

    // Students capabilities.
    $config['capabilities/students'] = array(
        'title' => __( 'Students', 'cp' ),
        'id' => 'cp-cap-students',
        'fields' => array(
            'coursepress_invite_my_student_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Invite students to own courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_invite_my_student_cap', true ),
            ),
            'coursepress_invite_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Invite students to any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_invite_students_cap', true ),
            ),
            'coursepress_withdraw_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Withdraw students from any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_withdraw_students_cap', true ),
            ),
            'coursepress_withdraw_my_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Withdraw students from any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_withdraw_my_students_cap', true ),
            ),
            'coursepress_add_move_my_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Add students to my courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_add_move_my_students_cap', true ),
            ),
            'coursepress_add_move_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Add students to any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_add_move_students_cap', true ),
            ),
            'coursepress_add_move_students_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Add students to assigned courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_add_move_students_cap', true ),
            ),
        ),
    );

    // Notifications capabilities.
    $config['capabilities/notifications'] = array(
        'title' => __( 'Notifications', 'cp' ),
        'id' => 'cp-cap-notifications',
        'fields' => array(
            'coursepress_create_my_notification_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input. __( 'Create notifications to own courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_my_notification_cap', true ),
            ),
            'coursepress_create_notification_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Create notifications to assigned courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_notification_cap', true ),
            ),
            'coursepress_update_my_notification_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update own published notifications', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_notification_cap', true ),
            ),
            'coursepress_update_notification_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update every notification', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_notification_cap', true ),
            ),

            'coursepress_delete_my_notification_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete own notification', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_notification_cap', true ),
            ),
            'coursepress_delete_notification_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete any notification', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_notification_cap', true ),
            ),
            'coursepress_change_my_notification_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change own notification status', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_notification_status_cap', true ),
            ),
            'coursepress_change_notification_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change status of every notification', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_notification_status_cap', true ),
            ),
        ),
    );

    // Discussions capabilities.
    $config['capabilities/discussions'] = array(
        'title' => __( 'Discussions', 'cp' ),
        'id' => 'cp-cap-discussions',
        'fields' => array(
            'coursepress_create_my_discussion_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Create discussion from own courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_my_discussion_cap', true ),
            ),
            'coursepress_create_discussion_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Create discussion to any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_discussion_cap', true ),
            ),
            'coursepress_update_my_discussion_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update own published discussion', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_discussion_cap', true ),
            ),
            'coursepress_update_discussion_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Update discussion from any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_discussion_cap', true ),
            ),
            'coursepress_delete_my_discussion_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete discussion from own courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_discussion_cap', true ),
            ),
            'coursepress_delete_discussion_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Delete discussion from any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_discussion_cap', true ),
            ),
            'coursepress_change_my_discussion_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change discussion status from own courses', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_discussion_status_cap', true ),
            ),
            'coursepress_change_discussion_status_cap' => array(
                'type' => 'checkbox',
                'title' => $toggle_input . __( 'Change discussion status from any course', 'cp' ),
                'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_discussion_status_cap', true ),
            ),
        ),
    );

    $options = apply_filters( 'coursepress_settings-capabilities', $config );
    $i = 0;
	foreach ( $options as $option ) : ?>
		<div class="cp-box-content cp-caps-fields <?php echo $i > 0 ? 'inactive' : ''; ?>" id="<?php echo $option['id']; ?>">
            <div class="cp-box cp-sep">
                <h2 class="cp-box-header"><?php echo $option['title']; ?></h2>
                <?php if ( isset( $option['description'] ) ) : ?>
                    <?php printf( '<p class="description">%s</p>', $option['description'] ); ?>
                <?php endif; ?>
            </div>

			<div class="cp-columns">
				<?php foreach ( $option['fields'] as $key => $data ) : ?>
					<div class="option option-<?php esc_attr_e( $key ); ?>">
						<?php if ( isset( $data['label'] ) ) : ?>
							<?php printf( '<h3>%s</h3>', $data['label'] ); ?>
						<?php endif; ?>
						<?php $data['name'] = $key; ?>
						<?php lib3()->html->element( $data ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php $i++; ?>
	<?php endforeach; ?>
</script>