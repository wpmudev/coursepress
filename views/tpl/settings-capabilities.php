<script type="text/template" id="coursepress-capabilities-setting-tpl">
	<div class="cp-box-heading">
		<h2 class="box-heading-title"><?php esc_html_e( 'Instructor Capabilities', 'cp' ); ?></h2>
	</div>

	<?php $marketpress_active = apply_filters( 'coursepress_is_marketpress_active', false ); ?>
	<div class="cp-box-content cp-caps-list cp-odd">
        <h3 class="label"><?php esc_html_e( 'Browse capabilities', 'cp' ); ?></h3>
        <ul class="cp-input-group cp-select-list cp-capabilities">
            <li data-id="cp-cap-general" class="active"><?php esc_html_e( 'General', 'cp' ); ?></li>
            <li data-id="cp-cap-courses"><?php esc_html_e( 'Courses', 'cp' ); ?></li>
	        <li data-id="cp-cap-course-categories"><?php esc_html_e( 'Course Categories', 'cp' ); ?></li>
            <li data-id="cp-cap-units"><?php esc_html_e( 'Units', 'cp' ); ?></li>
            <li data-id="cp-cap-instructors"><?php esc_html_e( 'Instructors', 'cp' ); ?></li>
	        <li data-id="cp-cap-facilitators"><?php esc_html_e( 'Facilitators', 'cp' ); ?></li>
            <li data-id="cp-cap-students"><?php esc_html_e( 'Students', 'cp' ); ?></li>
            <li data-id="cp-cap-notifications"><?php esc_html_e( 'Notifications', 'cp' ); ?></li>
            <li data-id="cp-cap-discussions"><?php esc_html_e( 'Discussions', 'cp' ); ?></li>
	        <?php if ( $marketpress_active ) : ?>
	            <li data-id="cp-cap-wpdefault"><?php esc_html_e( 'Default WordPress Capabilities', 'cp' ); ?></li>
	        <?php endif; ?>
        </ul>
	</div>

	<?php
	$config = array();
	$toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );
	$can_update_course = coursepress_get_setting( 'capabilities/instructor/coursepress_update_course_cap', true );

	// General capabilities.
	$config['capabilities/general'] = array(

		'title' => esc_html__( 'General', 'cp' ),
		'id' => 'cp-cap-general',
		'description' => esc_html__( 'Instructor of my courses can:', 'cp' ),
		'fields' => array(
			'coursepress_dashboard_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'See the main CoursePress menu', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_dashboard_cap', true ),
			),
			'coursepress_courses_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access to Courses submenus', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_courses_cap', true ),
			),
			'coursepress_instructors_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access to instructors submenus', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_instructors_cap', true ),
			),
			'coursepress_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access the Students submenu', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_students_cap', true ),
			),
			'coursepress_assessments_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access the Assessment submenu', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assessments_cap', true ),
			),
			'coursepress_reports_cap' => array(
	            'type' => 'checkbox',
	            'title' => $toggle_input . esc_html__( 'Access the Reports submenu', 'cp' ),
	            'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_reports_cap', true ),
			),
			'coursepress_notifications_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access the Notifications submenu', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_notifications_cap', true ),
			),
			'coursepress_discussions_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access the Forum submenu', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_discussions_cap', true ),
			),
			'coursepress_settings_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Access to the Settings page', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_settings_cap', true ),
			),
		),
	);

	// Course capabilities.
	$config['capabilities/courses'] = array(

		'title' => esc_html__( 'Courses', 'cp' ),
		'id' => 'cp-cap-courses',
		'fields' => array(
			'coursepress_create_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Create new courses', 'cp' ),
				//'desc' => esc_html__( 'Allow instructor to create, edit and delete own courses.', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_course_cap', true ),
			),
			'coursepress_view_others_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'View other instructors course', 'cp' ),
				//'desc' => esc_html__( 'Allow user to edit courses where user is an instructor at.', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_view_others_course_cap', true ),
			),
			'coursepress_update_my_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update own courses', 'cp' ),
				//'desc' => esc_html__( 'Allow instructor to update any course.', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_course_cap', true ),
			),
			'coursepress_update_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update any assigned course', 'cp' ),
				//'desc' => esc_html__( 'Allow user to delete courses where user is the author.', 'cp' ),
				'value' => $can_update_course,
			),
			'coursepress_delete_my_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete own courses', 'cp' ),
				//'desc' => esc_html__( 'Allow user to delete courses where user is an instructor at.', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_course_cap', true ),
			),
			'coursepress_delete_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete any assigned course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_course_cap', true ),
			),
			'coursepress_change_my_course_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change status of own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_course_status_cap', true ),
			),
			'coursepress_change_course_status_cap' => array(
	            'type' => 'checkbox',
	            'title' => $toggle_input . esc_html__( 'Change status of any assigned course', 'cp' ),
	            'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_course_status_cap', true ),
			),
		),
	);

	// Course categories.
	$config['capabilities/course_categories'] = array(

		'title' => esc_html__( 'Course Categories', 'cp' ),
		'id' => 'cp-cap-course-categories',
		'fields' => array(
			'coursepress_course_categories_manage_terms_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'View and create categories', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_course_categories_manage_terms_cap', true ),
			),
			'coursepress_course_categories_edit_terms_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Edit any category', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_course_categories_edit_terms_cap', true ),
			),
			'coursepress_course_categories_delete_terms_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete any category', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_course_categories_delete_terms_cap', true ),
			),
		),
	);

	// Course facilitator capabilities.
	$config['capabilities/facilitator'] = array(

		'title' => esc_html__( 'Facilitators', 'cp' ),
		'id' => 'cp-cap-facilitators',
		'fields' => array(
			'coursepress_assign_my_course_facilitator_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Assign facilitator to own course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assign_my_course_facilitator_cap', true ),
			),
			'coursepress_assign_facilitator_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Assign facilitator to any course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assign_facilitator_cap', true ),
			),
		),
	);

	// Unit capabilities.
	$config['capabilities/unit'] = array(

		'title' => esc_html__( 'Units', 'cp' ),
		'id' => 'cp-cap-units',
		'fields' => array(
			'coursepress_create_course_unit_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Create new course units', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_course_unit_cap', true ),
			),
			'coursepress_view_all_units_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'View units in every course (also from other instructors)', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_view_all_units_cap', true ),
				'disabled' => ( ! $can_update_course ) ? true : false,
			),
			'coursepress_update_my_course_unit_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update own units', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_course_unit_cap', true ),
			),
			'coursepress_update_course_unit_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update any unit (within assigned courses)', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_course_unit_cap', true ),
			),
			'coursepress_delete_my_course_units_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete own units', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_course_units_cap', true ),
			),
			'coursepress_delete_course_units_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete any unit within assigned courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_course_units_cap', true ),
			),
			'coursepress_change_my_course_unit_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change status of own units', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_course_unit_status_cap', true ),
			),
			'coursepress_change_course_unit_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change status of any unit within assigned courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_course_unit_status_cap', true ),
			),
		),
	);

	// Instructors capabilities.
	$config['capabilities/instructors'] = array(

		'title' => esc_html__( 'Instructors', 'cp' ),
		'id' => 'cp-cap-instructors',
		'fields' => array(
			'coursepress_assign_and_assign_instructor_my_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Assign other instructors to own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assign_and_assign_instructor_my_course_cap', true ),
			),
			'coursepress_assign_and_assign_instructor_course_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Assign other instructors to any course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_assign_and_assign_instructor_course_cap', true ),
			),
		),
	);

	// Students capabilities.
	$config['capabilities/students'] = array(

		'title' => esc_html__( 'Students', 'cp' ),
		'id' => 'cp-cap-students',
		'fields' => array(
			'coursepress_invite_my_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Invite students to own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_invite_my_students_cap', true ),
			),
			'coursepress_invite_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Invite students to any course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_invite_students_cap', true ),
			),
			'coursepress_withdraw_my_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Withdraw students from own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_withdraw_my_students_cap', true ),
			),
			'coursepress_withdraw_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Withdraw students from any course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_withdraw_students_cap', true ),
			),
			'coursepress_add_move_my_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Add students to own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_add_move_my_students_cap', true ),
			),
			'coursepress_add_move_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Add students to any course', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_add_move_students_cap', true ),
			),
			'coursepress_add_move_my_assigned_students_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Add students to assigned courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_add_move_my_assigned_students_cap', true ),
			),
		),
	);

	// Notifications capabilities.
	$config['capabilities/notifications'] = array(

		'title' => esc_html__( 'Notifications', 'cp' ),
		'id' => 'cp-cap-notifications',
		'fields' => array(
			'coursepress_create_my_notification_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input. esc_html__( 'Create new notifications for own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_my_notification_cap', true ),
			),
			'coursepress_create_my_assigned_notification_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Create new notifications for assigned courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_my_assigned_notification_cap', true ),
			),
			'coursepress_update_my_notification_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update own published notification', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_notification_cap', true ),
			),
			'coursepress_update_notification_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update every notification', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_notification_cap', true ),
			),

			'coursepress_delete_my_notification_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete own notification', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_notification_cap', true ),
			),
			'coursepress_delete_notification_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete any notification', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_notification_cap', true ),
			),
			'coursepress_change_my_notification_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change own notification status', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_notification_status_cap', true ),
			),
			'coursepress_change_notification_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change status of every notification', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_notification_status_cap', true ),
			),
		),
	);

	// Discussions capabilities.
	$config['capabilities/discussions'] = array(

	    'title' => esc_html__( 'Discussions', 'cp' ),
		'id' => 'cp-cap-discussions',
		'fields' => array(
			'coursepress_create_my_discussion_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Create discussion from own courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_my_discussion_cap', true ),
			),
			'coursepress_create_my_assigned_discussion_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Create new discussions for assigned courses', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_my_assigned_discussion_cap', true ),
			),
			'coursepress_update_my_discussion_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update own published discussion', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_my_discussion_cap', true ),
			),
			'coursepress_update_discussion_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Update every discussion', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_discussion_cap', true ),
			),
			'coursepress_delete_my_discussion_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete own discussions', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_discussion_cap', true ),
			),
			'coursepress_delete_discussion_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Delete every discussion', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_discussion_cap', true ),
			),
			'coursepress_change_my_discussion_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change statuses of own discussions', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_my_discussion_status_cap', true ),
			),
			'coursepress_change_discussion_status_cap' => array(
				'type' => 'checkbox',
				'title' => $toggle_input . esc_html__( 'Change status of every discussion', 'cp' ),
				'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_change_discussion_status_cap', true ),
			),
		),
	);

	// Add these capabilities only when MarketPress is acctive.
	if ( $marketpress_active ) {

		// Default WP capabilities.
		$config['capabilities/wordpress'] = array(

			'title' => esc_html__( 'Grant default WordPress capabilities', 'cp' ),
			'id' => 'cp-cap-wpdefault',
			'fields' => array(
				'edit_pages' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . esc_html__( 'Edit Pages (required for MarketPress)', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/edit_pages', true ),
				),
				'edit_published_pages'    => array(
					'type' => 'checkbox',
					'title' => $toggle_input . esc_html__( 'Edit Published Pages', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/edit_published_pages', true ),
				),
				'edit_posts' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . esc_html__( 'Edit Posts', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/edit_posts', true ),
				),
				'publish_pages' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . esc_html__( 'Publish Pages', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/publish_pages', true ),
				),
				'publish_posts' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . esc_html__( 'Publish Posts', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/publish_posts', true ),
				),
			),
		);
	}

	$options = apply_filters( 'coursepress_settings_capabilities', $config );
	$i = 0;
	foreach ( $options as $option ) :
		?>
		<div class="cp-box-content cp-caps-fields <?php echo $i > 0 ? 'inactive' : ''; ?>" id="<?php echo esc_attr( $option['id'] ); ?>">
            <div class="cp-box cp-sep">
                <h2 class="cp-box-header"><?php echo esc_html( $option['title'] ); ?></h2>
                <?php if ( isset( $option['description'] ) ) : ?>
                    <?php printf( '<p class="description">%s</p>', esc_html( $option['description'] ) ); ?>
                <?php endif; ?>
            </div>

			<div class="cp-columns">
				<?php foreach ( $option['fields'] as $key => $data ) : ?>
					<div class="option option-<?php echo esc_html( $key ); ?>">
						<?php if ( isset( $data['label'] ) ) : ?>
							<?php printf( '<h3>%s</h3>', esc_html( $data['label'] ) ); ?>
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
