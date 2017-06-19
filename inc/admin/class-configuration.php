<?php
/**
 * Contol configuration pages
 *
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Configuration {

	public function __construct() {

		add_filter( 'coursepress_settings-general', array( $this, 'general' ) );
	}

	public function general( $config ) {
		/**
		 * Course details page
		 */
		$config['course-details-page'] = array(
			'title' => __( 'Course details page', 'CoursePress' ),
			'description' => __( 'Specify Media to use when viewing course details.', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[course][details_media_type]' => array(
					'type' => 'select',
					'label' => __( 'Media Type', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Priority Mode (default)', 'CP_TD' ),
						'video' => __( 'Featured Video', 'CP_TD' ),
						'image' => __( 'List Image', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/details_media_type', 'default' ),
				),
				'coursepress_settings[course][details_media_priority]' => array(
					'type' => 'select',
					'label' => __( 'Priority', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Default', 'CP_TD' ),
						'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
						'image' => __( 'List Image (video fallback)', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/details_media_priority', 'default' ),
				),
			),
		);
		/**
		 * Course Listings
		 */
		$config['course-listings'] = array(
			'title' => __( 'Course Listings', 'CoursePress' ),
			'description' => __( 'Media to use when viewing course listings (e.g. Courses page or Instructor page).', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[course][listing_media_type]' => array(
					'type' => 'select',
					'label' => __( 'Media Type', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Priority Mode (default)', 'CP_TD' ),
						'video' => __( 'Featured Video', 'CP_TD' ),
						'image' => __( 'List Image', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/listing_media_type', 'default' ),
				),
				'coursepress_settings[course][listing_media_priority]' => array(
					'type' => 'select',
					'label' => __( 'Priority', 'CoursePress' ),
					'field_options' => array(
						'default' => __( 'Default', 'CP_TD' ),
						'video' => __( 'Featured Video (image fallback)', 'CP_TD' ),
						'image' => __( 'List Image (video fallback)', 'CP_TD' ),
					),
					'value' => coursepress_get_setting( 'course/listing_media_priority', 'default' ),
				),
			),
		);
		/**
		 * Course Images
		 */
		$config['course-images'] = array(
			'title' => __( 'Course Images', 'CoursePress' ),
			'description' => __( 'Size for (newly uploaded) course images.', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[course][image_width]' => array(
					'type' => 'number',
					'label' => __( 'Image Width', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/image_width', '235' ),
					'config' => array(
						'min' => 0,
					),
				),
				'coursepress_settings[course][image_height]' => array(
					'type' => 'number',
					'label' => __( 'Image Height', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/image_height', '225' ),
					'config' => array(
						'min' => 0,
					),
				),
			),
		);
		/**
		 * Course Order
		 */
		$config['course-order'] = array(
			'title' => __( 'Course Order', 'CoursePress' ),
			'description' => __( 'Order of courses in admin and on front.', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[course][order_by]' => array(
					'type' => 'select',
					'desc' => __( '', 'CoursePress' ),
					'label' => __( 'Order by', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/order_by', 'course_start_date' ),
					'field_options' => array(
						'post_date' => __( 'Post Date', 'CP_TD' ),
						'start_date' => __( 'Course start date', 'CP_TD' ),
						'enrollment_start_date' => __( 'Course enrollment start date', 'CP_TD' ),
					),
				),
				'coursepress_settings[course][direction]' => array(
					'type' => 'select',
					'label' => __( 'Direction', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/order_by_direction', 'DESC' ),
					'field_options' => array(
						'DESC' => __( 'Descending', 'CoursePress' ),
						'ASC' => __( 'Ascending', 'CoursePress' ),
					),
				),
			),
		);
		/**
		 * Theme menu items
		 */
		$config['theme-menu-items'] = array(
			'title' => __( 'Theme Menu Items', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[course][show_coursepress_menu]' => array(
					'type' => 'checkbox',
					'title' => __( 'Show menu items', 'CoursePress' ),
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
				'coursepress_settings[general][use_custom_login]' => array(
					'type' => 'checkbox',
					'title' => __( 'Use Custom Login Form', 'CoursePress' ),
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
				'coursepress_settings[instructor][show_username]' => array(
					'type' => 'checkbox',
					'title' => __( 'Show instructor username in URL', 'CoursePress' ),
					'value' => coursepress_get_setting( 'instructor/show_username', 1 ),
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
				'coursepress_settings[general][add_structure_data]' => array(
					'type' => 'checkbox',
					'desc' => __( 'Add structure data to courses.', 'CoursePress' ),
					'title' => __( 'Add microdata syntax', 'CoursePress' ),
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
				'coursepress_settings[general][redirect_after_login]' => array(
					'type' => 'checkbox',
					'desc' => __( 'Redirect students to their Dashboard upon login via wp-login form.', 'CoursePress' ),
					'title' => __( 'Redirect After Login', 'CoursePress' ),
					'value' => coursepress_get_setting( 'general/redirect_after_login', 1 ),
				),
			),
		);
		/**
		 * Enrollment Restrictions
		 */
		$courses = new CoursePress_Data_Courses();
		$enrollment_type_default = $courses->get_enrollment_type_default();
		$config['course/enrollment_type_default'] = array(
			'title' => __( 'Enrollment restrictions', 'CoursePress' ),
			'description' => __( 'Select the default limitations on accessing and enrolling in this course.', 'CoursePress' ),
			'fields' => array(
				'coursepress_settings[course][enrollment_type_default]' => array(
					'type' => 'select',
					'title' => __( 'Who can enroll', 'CoursePress' ),
					'value' => coursepress_get_setting( 'course/enrollment_type_default', $enrollment_type_default ),
					'field_options' => $courses->get_enrollment_types_array(),
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
				'coursepress_settings[reports][font]' => array(
					'type' => 'select',
					'title' => __( 'Use this font', 'CoursePress' ),
					'value' => coursepress_get_setting( 'reports/font', 'helvetica' ),
					'field_options' => array(),
				),
			),
		);
		return $config;
	}
}
