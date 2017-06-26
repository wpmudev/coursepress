<?php
/**
 * Control configuration pages
 *
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Configuration {

	public function __construct() {
		add_filter( 'coursepress_settings-certificate', array( $this, 'certificate' ) );
		add_filter( 'coursepress_settings-general', array( $this, 'general' ) );
		add_filter( 'coursepress_settings-import-export', array( $this, 'import_export' ) );
		add_filter( 'coursepress_settings-capabilities', array( $this, 'capabilities' ) );
	}

	/**
	 * Contol certificate configuration pages
	 *
	 *
	 * @since 3.0
	 */
	public function certificate( $config ) {
		/**
		 * Certificate Options
		 */
		$config['certificate-options'] = array(
			'title' => __( 'Certificate options', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][enabled]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Enable basic certificate', 'CoursePress' ),
					'value' => coursepress_get_setting( 'basic_certificate/enabled', true ),
				),
				'coursepress_settings[basic_certificate][use_cp_default]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Use custom CoursePress certificate', 'CoursePress' ),
					'value' => ! coursepress_get_setting( 'basic_certificate/use_cp_default', false ),
				),
			),
		);
		/**
		 * Custom Certificate
		 * /
		 $config['custom-certificate'] = array(
			 'title' => __( 'Custom Certificate', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][content]' => array(
					'type' => 'wp_editor',
					'id' => 'coursepress_settings_basic_certificate_content',
					'value' => coursepress_get_setting( 'basic_certificate/content', $this->default_certificate_content() ),
				),
			),
		);
		/**
		 * Background Image
		 */
		$config['background_image'] = array(
			'title' => __( 'Background Image', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][background_image]' => array(
					'type' => 'image',
					'value' => coursepress_get_setting( 'basic_certificate/background_image' ),
				),
			),
		);
		/**
		 *
		 */
		$config['content_margin'] = array(
			'title' => __( 'Content Margin', 'CoursePress' ),
			'description' => __( '', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][margin][top]' => array(
					'type' => 'number',
					'title' => __( 'Top', 'CoursePress' ),
					'value' => coursepress_get_setting( 'basic_certificate/margin/top' ),
				),
				'coursepress_settings[basic_certificate][margin][left]' => array(
					'type' => 'number',
					'title' => __( 'Left', 'CoursePress' ),
					'value' => coursepress_get_setting( 'basic_certificate/margin/left' ),
				),
				'coursepress_settings[basic_certificate][margin][right]' => array(
					'type' => 'number',
					'title' => __( 'Right', 'CoursePress' ),
					'value' => coursepress_get_setting( 'basic_certificate/margin/right' ),
				),
			),
		);
		/**
		 * Page orientation
		 */
		$config['page_orientation'] = array(
			'title' => __( 'Page orientation', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][orientation]' => array(
					'type' => 'radio',
					'value' => coursepress_get_setting( 'basic_certificate/orientation', 'L' ),
					'field_options' => array(
						'L' => __( 'Landscape', 'CoursePress' ),
						'P' => __( 'Portrait', 'CoursePress' ),
					),
				),
			),
		);
		/**
		 * Text Color
		 */
		$config['text_color'] = array(
			'title' => __( 'Text Color', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][text_color]' => array(
					'type' => 'wp_color_picker',
					'value' => coursepress_get_setting( 'basic_certificate/text_color', '#000' ),
				),
			),
		);
		/**
		 * Preview
		 */
		$config['preview'] = array(
			'title' => __( 'Preview', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[basic_certificate][preview]' => array(
					'type' => 'button',
					'value' => __( 'Preview Certificate', 'CoursePress' ),
				),
			),
		);

		return $config;
	}

	/**
	 * Contol general configuration pages
	 *
	 *
	 * @since 3.0
		 */
	public function general( $config = array() ) {
		// Course details page
		$config['course-details-page'] = array(
            'title' => __( 'Course details page', 'CoursePress' ),
            'description' => __( 'Specify Media to use when viewing course details.', 'CoursePress' ),
            'fields' => array(
                'details_media_type' => array(
                    'type' => 'select',
                    'label' => __( 'Media Type', 'CoursePress' ),
                    'field_options' => array(
                        'default' => __( 'Priority Mode (default)', 'CP_TD' ),
                        'video' => __( 'Featured Video', 'CP_TD' ),
                        'image' => __( 'List Image', 'CP_TD' ),
                    ),
                    'value' => coursepress_get_setting( 'general/details_media_type', 'default' ),
                ),
                'details_media_priority' => array(
                    'type' => 'select',
                    'label' => __( 'Priority', 'CoursePress' ),
                    'field_options' => array(
                        'default' => __( 'Default', 'CP_TD' ),
                        'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
                        'image' => __( 'List Image (video fallback)', 'CP_TD' ),
                    ),
                    'value' => coursepress_get_setting( 'general/details_media_priority', 'default' ),
                ),
            ),
		);

		// Course listings
		$config['course-listings'] = array(
            'title' => __( 'Course Listings', 'CoursePress' ),
            'description' => __( 'Media to use when viewing course listings (e.g. Courses page or Instructor page).', 'CoursePress' ),
            'fields' => array(
                'listing_media_type' => array(
                    'type' => 'select',
                    'label' => __( 'Media Type', 'CoursePress' ),
                    'field_options' => array(
                        'default' => __( 'Priority Mode (default)', 'CP_TD' ),
                        'video' => __( 'Featured Video', 'CP_TD' ),
                        'image' => __( 'List Image', 'CP_TD' ),
                    ),
                    'value' => coursepress_get_setting( 'general/listing_media_type', 'default' ),
                ),
                'listing_media_priority' => array(
                    'type' => 'select',
                    'label' => __( 'Priority', 'CoursePress' ),
                    'field_options' => array(
                        'default' => __( 'Default', 'CP_TD' ),
                        'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
                        'image' => __( 'List Image (video fallback)', 'CP_TD' ),
                    ),
                    'value' => coursepress_get_setting( 'general/listing_media_priority', 'default' ),
                ),
            ),
		);

		// Course images
		$config['course-images'] = array(
            'title' => __( 'Course Images', 'CoursePress' ),
            'description' => __( 'Size for (newly uploaded) course images.', 'CoursePress' ),
            'fields' => array(
                'image_width' => array(
                    'type' => 'number',
                    'label' => __( 'Image Width', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/image_width', '235' ),
                    'config' => array(
                        'min' => 0,
                    ),
                ),
                'image_height' => array(
                    'type' => 'number',
                    'label' => __( 'Image Height', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/image_height', '225' ),
                    'config' => array(
                        'min' => 0,
                    ),
                ),
            ),
		);

		// Course order
		$config['course-order'] = array(
            'title' => __( 'Course Order', 'CoursePress' ),
            'description' => __( 'Order of courses in admin and on front.', 'CoursePress' ),
            'fields' => array(
                'order_by' => array(
                    'type' => 'select',
                    'desc' => __( '', 'CoursePress' ),
                    'label' => __( 'Order by', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/order_by', 'course_start_date' ),
                    'field_options' => array(
                        'post_date' => __( 'Post Date', 'CP_TD' ),
                        'start_date' => __( 'Course start date', 'CP_TD' ),
                        'enrollment_start_date' => __( 'Course enrollment start date', 'CP_TD' ),
                    ),
                ),
                'order_by_direction' => array(
                    'type' => 'select',
                    'label' => __( 'Direction', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/order_by_direction', 'DESC' ),
                    'field_options' => array(
                        'DESC' => __( 'Descending', 'CoursePress' ),
                        'ASC' => __( 'Ascending', 'CoursePress' ),
                    ),
                ),
            ),
		);

		$toggle_input = coursepress_create_html( 'span', array( 'class' => 'cp-toggle-btn' ) );

		// Menu items
		$config['theme-menu-items'] = array(
            'title' => __( 'Theme Menu Items', 'CoursePress' ),
            'fields' => array(
                'show_coursepress_menu' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Show menu items', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/show_coursepress_menu', 1 ),
                    'desc' => __( 'Attach default CoursePress menu items ( Courses, Student Dashboard, Log Out ) to the <strong>Primary Menu</strong>.<br />Items can also be added from Appearance &gt; Menus and the CoursePress panel.', 'CoursePress' ),
                ),
            ),
		);
		/**
		 * Login Form
		 */
		$config['general/login-form'] = array(
            'title' => __( 'Login form', 'CoursePress' ),
            'fields' => array(
                'use_custom_login' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Use Custom Login Form', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/use_custom_login', 1 ),
                    'desc' => __( 'Uses a custom Login Form to keep students on the front-end of your site.', 'CoursePress' ),
                ),
            ),
		);
		/**
		 * Privacy
		 */
		$config['instructor/show_username'] = array(
            'title' => __( 'Privacy', 'CoursePress' ),
            'fields' => array(
                'instructor_show_username' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Show instructor username in URL', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'instructor_show_username', 1 ),
                    'desc' => __( 'If checked, instructors username will be shown in the url. Otherwise, hashed (MD5) version will be shown.', 'CoursePress' ),
                ),
            ),
		);
		/**
		 * schema
		 */
		$config['general/add_structure_data'] = array(
            'title' => __( 'schema.org', 'CoursePress' ),
            'fields' => array(
                'add_structure_data' => array(
                    'type' => 'checkbox',
                    'desc' => __( 'Add structure data to courses.', 'CoursePress' ),
                    'title' => $toggle_input . __( 'Add microdata syntax', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/add_structure_data', 1 ),
                ),
            ),
		);

		/**
		 * WordPress Login Redirect
		 */
		$config['general/redirect_after_login'] = array(
            'title' => __( 'WP Login Redirect', 'CoursePress' ),
            'fields' => array(
                'redirect_after_login' => array(
                    'type' => 'checkbox',
                    'desc' => __( 'Redirect students to their Dashboard upon login via wp-login form.', 'CoursePress' ),
                    'title' => $toggle_input . __( 'Redirect After Login', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/redirect_after_login', 1 ),
                ),
            ),
		);
		/**
		 * Enrollment Restrictions
		 */

        $default_enrollment_type = coursepress_get_default_enrollment_type();
        $default_enrollment_type = coursepress_get_setting( 'general/enrollment_type_default', $default_enrollment_type );
		$config['course/enrollment_type_default'] = array(
            'title' => __( 'Enrollment restrictions', 'CoursePress' ),
            'description' => __( 'Select the default limitations on accessing and enrolling in this course.', 'CoursePress' ),
            'fields' => array(
                'enrollment_type_default' => array(
                    'type' => 'select',
                    'title' => __( 'Who can enroll', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'general/enrollment_type_default', $default_enrollment_type ),
                    'field_options' => coursepress_get_enrollment_types(),
                ),
            ),
		);
		/**
		 * Reports
		 */
		$config['reports/font'] = array(
            'title' => __( 'Reports', 'CoursePress' ),
            'description' => __( 'Select font which will be used in the PDF reports.', 'CoursePress' ),
            'fields' => array(
                'reports_font' => array(
                    'type' => 'select',
                    'title' => __( 'Use this font', 'CoursePress' ),
                    'value' => coursepress_get_setting( 'reports_font', 'helvetica' ),
                    'field_options' => array(),
                ),
            ),
		);
		return $config;
	}

	/**
	 * Contol import configuration pages
	 *
	 *
	 * @since 3.0
	 */
	public function import_export( $config ) {
		$config['import'] = array(
			'title' => __( 'Import', 'CoursePress' ),
			'description' => __( 'Upload your exported courses to import here.', 'CoursePress' ),
			'fields' => array(
				'import' => array(
					'type' => 'file',
				),
				'coursepress[replace]' => array(
					'type' => 'checkbox',
					'title' => __( 'Replace course if exists', 'CoursePress' ),
					'desc' => __( 'Courses with the same title will be automatically replaced by the new one.', 'CoursePress' ),
				),
				'coursepress[students]' => array(
					'type' => 'checkbox',
					'title' => __( 'Include course students', 'CoursePress' ),
					'desc' => __( 'Students listing must also included in your export for this to work.', 'CoursePress' ),
				),
				'coursepress[comments]' => array(
					'type' => 'checkbox',
					'title' => __( 'Include course thread/comments', 'CoursePress' ),
					'desc' => __( 'Comments listing must also included in your export for this to work.', 'CoursePress' ),
					'disabled' => true,
				),
				'' => array(
					'type' => 'button',
					'value' => __( 'Upload file and import', 'CoursePress' ),
					'class' => 'button-primary disabled',
				),
			),
		);
		/**
		 * export
		 */
		$config['export'] = array(
			'title' => __( 'Export', 'CoursePress' ),
			'description' => __( 'Select courses to export to another site.', 'CoursePress' ),
			'fields' => array(
				'coursepress[all]' => array(
					'type' => 'checkbox',
					'title' => __( 'All Courses', 'CoursePress' ),
				),
			),
		);
		/**
		 * Courses list
		 */
		$course = new CoursePress_Data_Courses();
		$list = $course->get_list();
		foreach ( $list as $course_id => $course_title ) {
			$config['export']['fields'][ 'coursepress[courses]['.$course_id.']' ] = array(
				'type' => 'checkbox',
				'title' => empty( $course_title )? __( '-[This course has no title]-', 'CoursePress' ):$course_title,
			);
		}
		$config['export']['fields'] += array(
			'coursepress[export][students]' => array(
				'type' => 'checkbox',
				'title' => __( 'Include course students', 'CoursePress' ),
				'desc' => __( 'Will include course students and their course submission progress.', 'CoursePress' ),
			),
			'coursepress[export][comments]' => array(
				'type' => 'checkbox',
				'title' => __( 'Include course thread/comments', 'CoursePress' ),
				'desc' => __( 'Will include course students and their course submission progress.', 'CoursePress' ),
				'disabled' => true,
			),
			'coursepress[export][button]' => array(
				'type' => 'button',
				'value' => __( 'Export Courses', 'CoursePress' ),
				'class' => 'button-primary disabled',
			),
		);
		return $config;
	}

	/**
	 * Contol export configuration pages
	 *
	 *
	 * @since 3.0
	 */
	public function export( $config ) {
		return $config;
	}

	/**
	 * defaulr certificate content
	 *
	 * @since 3.0.0
	 */
	private function default_certificate_content() {
		$msg = '<h2>%1$s %2$s</h2>
%3$s
<h3>%4$s</h3>

<h4>%5$s: %6$s</h4>
<small>%7$s: %8$s</small>
%9$s
		';

        $default_certification_content = sprintf(
            $msg,
            'FIRST_NAME',
            'LAST_NAME',
            __( 'has successfully completed the course', 'cp' ),
            'COURSE_NAME',
            __( 'Date', 'cp' ),
            'COMPLETION_DATE',
            __( 'Certifidate no.', 'cp' ),
            'CERTIFICATE_NUMBER',
            'UNIT_LIST'
        );

        return $default_certification_content;
    }

	/**
	 * Capabilities settings page.
	 *
	 * @since 3.0
	 *
	 * @return array $config
	 */
	public function capabilities( $config ) {

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
				'coursepress_students_dap' => array(
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
				'coursepress_edit_course' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Edit courses', 'cp' ),
					'desc' => __( 'Allow instructor to create, edit and delete own courses.', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_create_course', true ),
				),
				'coursepress_update_assigned_courses' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Update assigned courses', 'cp' ),
					'desc' => __( 'Allow user to edit courses where user is an instructor at.', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_assigned_courses', true ),
				),
				'coursepress_update_course_cap' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Update any course', 'cp' ),
					'desc' => __( 'Allow instructor to update any course.', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_update_course_cap', true ),
				),
                'coursepress_delete_my_course_cap' => array(
                    'type' => 'checkbox',
                    'title' => $toggle_input . __( 'Delete own courses', 'cp' ),
                    'desc' => __( 'Allow user to delete courses where user is the author.', 'cp' ),
                    'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_my_course_cap', true ),
                ),
				'coursepress_delete_assigned_course' => array(
					'type' => 'checkbox',
					'title' => $toggle_input . __( 'Delete assigned courses', 'cp' ),
					'desc' => __( 'Allow user to delete courses where user is an instructor at.', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructor/coursepress_delete_assigned_course', true ),
				),
				'coursepress_settings[capabilities][courses][assigned_courses_status]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Change status of any assigned course', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/courses/assigned_courses_status', true ),
				),
				'coursepress_settings[capabilities][courses][instructor_courses_status]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Change status of courses made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/courses/instructor_courses_status', true ),
				),
			),
		);

		// Unit capabilities.
		$config['capabilities/unit'] = array(
			'title' => __( 'Units', 'cp' ),
			'id' => 'cp-cap-units',
			'fields' => array(
				'coursepress_settings[capabilities][unit][create_course_units]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new course units', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/create_course_units', true ),
				),
				'coursepress_settings[capabilities][unit][view_every_units]' => array(
					'type' => 'radio_slider',
					'title' => __( 'View units in every course ( can view from other Instructors as well )', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/view_every_units', true ),
				),
				'coursepress_settings[capabilities][unit][update_any_unit]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Update any unit (within assigned courses)', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/update_any_unit', true ),
				),
				'coursepress_settings[capabilities][unit][update_instructor_units]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Update units made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/update_instructor_units', true ),
				),
				'coursepress_settings[capabilities][unit][delete_any_unit]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete any unit (within assigned courses)', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/delete_any_unit', true ),
				),
				'coursepress_settings[capabilities][unit][delete_instructor_units]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete course units made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/delete_instructor_units', true ),
				),
				'coursepress_settings[capabilities][unit][any_unit_status]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Change status of any unit (within assigned courses)', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/any_unit_status', true ),
				),
				'coursepress_settings[capabilities][unit][instructor_unit_status]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Change statuses of course units made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/unit/instructor_unit_status', true ),
				),
			),
		);

		// Instructors capabilities.
		$config['capabilities/instructors'] = array(
			'title' => __( 'Instructors', 'cp' ),
			'id' => 'cp-cap-instructors',
			'fields' => array(
				'coursepress_settings[capabilities][instructors][assign_any_course]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Assign instructors to any course', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructors/assign_any_course', true ),
				),
				'coursepress_settings[capabilities][instructors][assign_instructor_courses]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Assign instructors to courses made by the instructor only )', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/instructors/assign_instructor_courses', true ),
				),
			),
		);

		// Students capabilities.
		$config['capabilities/students'] = array(
			'title' => __( 'Students', 'cp' ),
			'id' => 'cp-cap-students',
			'fields' => array(
				'coursepress_settings[capabilities][students][any_course_invite]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Invite students to any course', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/any_course_invite', true ),
				),
				'coursepress_settings[capabilities][students][instructors_course_invite]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Invite students to courses made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/instructors_course_invite', true ),
				),
				'coursepress_settings[capabilities][students][any_course_withdraw]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Withdraw students from any course', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/any_course_withdraw', true ),
				),
				'coursepress_settings[capabilities][students][instructors_course_withdraw]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Withdraw students from courses made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/instructors_course_withdraw', true ),
				),
				'coursepress_settings[capabilities][students][any_course_add]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Add students to any course', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/any_course_add', true ),
				),
				'coursepress_settings[capabilities][students][instructors_course_add]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Add students to courses made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/instructors_course_add', true ),
				),
				'coursepress_settings[capabilities][students][instructors_assigned_course_add]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Add students to courses assigned to the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/instructors_assigned_course_add', true ),
				),
				'coursepress_settings[capabilities][students][add_student_user]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Add new users with Student role to the blog', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/add_student_user', true ),
				),
				'coursepress_settings[capabilities][students][send_students_bulk_email]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Send bulk e-mail to students', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/send_students_bulk_email', true ),
				),
				'coursepress_settings[capabilities][students][send_instructor_course_bulk_email]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Send bulk e-mail to students within a course made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/send_instructor_course_bulk_email', true ),
				),
				'coursepress_settings[capabilities][students][delete]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete Students (deletes ALL associated course records)', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/students/delete', true ),
				),
			),
		);

		// Notifications capabilities.
		$config['capabilities/notifications'] = array(
			'title' => __( 'Notifications', 'cp' ),
			'id' => 'cp-cap-notifications',
			'fields' => array(
				'coursepress_settings[capabilities][notifications][create]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new notifications', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/create', true ),
				),
				'coursepress_settings[capabilities][notifications][create_instructors_course_notifications]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new notifications for courses created by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/create_instructors_course_notifications', true ),
				),
				'coursepress_settings[capabilities][notifications][create_instructors_assigned_course_notifications]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new notifications for courses assigned to the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/create_instructors_assigned_course_notifications', true ),
				),
				'coursepress_settings[capabilities][notifications][update_every_notifications]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Update every notification', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/update_every_notifications', true ),
				),
				'coursepress_settings[capabilities][notifications][update_instructors_notifications]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Update notifications made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/update_instructors_notifications', true ),
				),
				'coursepress_settings[capabilities][notifications][delete_every_notifications]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete every notification', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/delete_every_notifications', true ),
				),
				'coursepress_settings[capabilities][notifications][delete_instructors_notifications]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete notifications made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/delete_instructors_notifications', true ),
				),
				'coursepress_settings[capabilities][notifications][every_notification_status]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Change status of every notification', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/every_notification_status', true ),
				),
				'coursepress_settings[capabilities][notifications][instructors_notifications_status]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Change statuses of notifications made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/notifications/instructors_notifications_status', true ),
				),
			),
		);

		// Discussions capabilities.
		$config['capabilities/discussions'] = array(
			'title' => __( 'Discussions', 'cp' ),
			'id' => 'cp-cap-discussions',
			'fields' => array(
				'coursepress_settings[capabilities][discussions][create]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new discussions', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/create', true ),
				),
				'coursepress_settings[capabilities][discussions][create_instructors_discussions]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new discussions for courses created by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/create_instructors_discussions', true ),
				),
				'coursepress_settings[capabilities][discussions][create_instructors_assigned_discussions]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Create new discussions for courses assigned to the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/create_instructors_assigned_discussions', true ),
				),
				'coursepress_settings[capabilities][discussions][update_every_discussion]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Update every discussions', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/update_every_discussion', true ),
				),
				'coursepress_settings[capabilities][discussions][update_instructors_discussion]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Update discussions made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/update_instructors_discussion', true ),
				),
				'coursepress_settings[capabilities][discussions][delete_every_discussions]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete every discussions', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/delete_every_discussions', true ),
				),
				'coursepress_settings[capabilities][discussions][delete_instructors_discussions]' => array(
					'type' => 'radio_slider',
					'title' => __( 'Delete discussions made by the instructor only', 'cp' ),
					'value' => coursepress_get_setting( 'capabilities/discussions/delete_instructors_discussions', true ),
				),
			),
		);

		return apply_filters( 'coursepress_capabilities', $config );
	}
}
