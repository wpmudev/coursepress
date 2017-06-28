<?php
/**
 * Class CoursePress_Page
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Page extends CoursePress_Utility {
	/**
	 * @var string the main menu slug.
	 */
	protected $slug = 'coursepress';

	/**
	 * @var array List of CP screen_id
	 */
	protected $screens = array();

	/**
	 * @var array An array of variables use for localization.
	 */
	var $localize_array = array();

	public function __construct() {
		// Check if user can't access coursepress
		if ( ! current_user_can( 'coursepress_dashboard_cap' ) ) {
			$this->is_error = true;

			return;
		}

		// Setup CP pages
		add_action( 'admin_menu', array( $this, 'set_admin_menus' ) );

		// Setup admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_css' ) );
	}

	/**
	 * Iterate CP admin pages.
	 *
	 * @access private
	 */
	function set_admin_menus() {
		global $submenu;

		// Main CP Page
		$label = __( 'CoursePress Base', 'cp' );
		$screen_id = add_menu_page( $label, $label, 'coursepress_dashboard_cap', $this->slug, array( $this, 'get_courselist_page' ), '', 25 );
		// Add screen ID to the list of valid CP pages
		array_unshift( $this->screens, $screen_id );
		// Add preload callback
		add_action( 'load-' . $screen_id, array( $this, 'process_courselist_page' ) );

		// Set course edit page
		$edit_label = __( 'New Course', 'cp' );
		$this->add_submenu( $edit_label, 'coursepress_create_course_cap', 'coursepress_course', 'get_course_edit_page' );

		// Set students page
		$student_label = __( 'Students', 'cp' );
		$this->add_submenu( $student_label, 'coursepress_students_cap', 'coursepress_students', 'get_students_page' );

		// Set instructor page
		$instructor_label = __( 'Instructors', 'cp' );
		$this->add_submenu( $instructor_label, 'coursepress_instructors_cap', 'coursepress_instructors', 'get_instructors_page' );

		// Set assessment page
		$assessment_label = __( 'Assessments', 'cp' );
		$this->add_submenu( $assessment_label, 'coursepress_assessment_cap', 'coursepress_assessments', 'get_assessments_page' );

		// Set Forum page
		$forum_label = __( 'Forum', 'cp' );
		$this->add_submenu( $forum_label, 'coursepress_discussions_cap', 'coursepress_forum', 'get_forum_page' );

		// Set Comments page
		$comment_label = __( 'Comments', 'cp' );
		$this->add_submenu( $comment_label, 'coursepress_comments_cap', 'coursepress_comments', 'get_comments_page' );

		// Set Notification page
		$notification_label = __( 'Notifications', 'cp' );
		$this->add_submenu( $notification_label, 'coursepress_notifications_cap', 'coursepress_notifications', 'get_notification_page' );

		// Set Settings page
		$settings_label = __( 'Settings', 'cp' );
		$this->add_submenu( $settings_label, 'coursepress_settings_cap', 'coursepress_settings', 'get_settings_page' );

		// Change top menu label
		$submenu['coursepress'][0][0] = __( 'Courses', 'cp' );
	}

	/**
	 * Helper method to add `coursepress` submenu.
	 *
	 * @param string $label
	 * @param string $cap
	 * @param string $slug
	 * @param string $callback
	 */
	function add_submenu( $label = '', $cap, $slug, $callback ) {
		$menu = add_submenu_page( $this->slug, 'CoursePress ' . $label, $label, $cap, $slug, array( $this, $callback ) );
		// Add to the list of valid CP pages
		array_unshift( $this->screens, $menu );
		return $menu;
	}

	/**
	 * Helper method to set needed JS and CSS stylesheets needed.
	 *
	 * @access private
	 */
	function set_admin_css() {
		$coursepress_pagenow = coursepress_is_admin();

		if ( ! $coursepress_pagenow ) {
			return; // Do not continue
		}
		/**
		 * The key ID of current CP page loaded.
		 * Both JS and CSS are autoloaded base on this ID.
		 *
		 * Currents keys: {
		 *  `coursepress` - use in courses list page
		 *  `coursepress_students`
		 *  `coursepress_instructors`
		 *  `coursepress_assessments`
		 *  `coursepress_forum`
		 *  `coursepress_comments`
		 *  `coursepress_notifications`
		 *  `coursepress_settings`
		 * }
		 */

		// External CSS
		$this->enqueue_style( 'coursepress-select2', 'assets/external/css/select2.min.css' );

		// Set stylesheets
		$this->enqueue_style( 'fontawesome', 'assets/external/css/font-awesome.min.css' );
		$this->enqueue_style( 'coursepress-admin-common', 'assets/css/admin-common.min.css' );
		$this->enqueue_style( $coursepress_pagenow, 'assets/css/' . $coursepress_pagenow . '.min.css' );

		/**
		 * We'll set JS files at the bottom of the page so that we could add
		 * localize script as page running.
		 */
		add_action( 'admin_footer', array( $this, 'set_admin_scripts' ) );
	}

	private function enqueue_style( $id, $src ) {
		global $CoursePress;

		wp_enqueue_style( $id, $CoursePress->plugin_url . $src, false, $CoursePress->version );
	}

	function set_admin_scripts() {
		global $CoursePress;

		$coursepress_pagenow = coursepress_is_admin();

		if ( ! $coursepress_pagenow ) {
			return; // Do not continue
		}

		$plugin_url = $CoursePress->plugin_url;
		$this->localize_array = wp_parse_args( $this->localize_array, array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( 'coursepress_nonce' ),
			'cookie' => array(
				'hash' => COOKIEHASH,
				'path' => COOKIEPATH,
			),
			'coursepress_page' => add_query_arg( 'page', 'coursepress', admin_url() ),
			// Common use texts
			'text' => array(
			    'ok' => __( 'Ok', 'cp' ),
				'cancel' => __( 'Cancel', 'cp' ),
				'error' => __( 'Error', 'cp' ),
				'media' => array(
					'select_image' => __( 'Select Image', 'cp' ),
					'select_feature_image' => __( 'Select Feature Image', 'cp' ),
				),
			),
		) );

		// External scripts
		$this->enqueue_script( 'coursepress-select2', 'assets/external/js/select2.min.js' );

		// General admin js
		wp_enqueue_script( 'coursepress-admin-general', $plugin_url . 'assets/js/admin-general.min.js', array( 'jquery', 'backbone', 'underscore', 'jquery-ui-autocomplete' ), $CoursePress->version, true );
		$this->enqueue_script( $coursepress_pagenow, 'assets/js/' . $coursepress_pagenow . '.min.js' );

		// Set local vars
		$localize_array = apply_filters( 'coursepress_admin_localize_array', $this->localize_array );
		wp_localize_script( 'coursepress-admin-general', '_coursepress', $localize_array );
	}

	function enqueue_script( $id, $src ) {
		global $CoursePress;

		wp_enqueue_script( $id, $CoursePress->plugin_url . $src, false, $CoursePress->version, true );
	}

	function courselist_columns() {
		$columns = array(
			'category' => __( 'Categories', 'cp' ),
			'units' => __( 'Units', 'cp' ),
			'students' => __( 'Students', 'cp' ),
			'start_date' => __( 'Start Date', 'cp' ),
			'end_date' => __( 'End Date', 'cp' ),
			'enrollment_start' => __( 'Enrollment Start', 'cp' ),
			'enrollment_end' => __( 'Enrollment End', 'cp' ),
			'certified' => __( 'Certified', 'cp' ),
		);

		return $columns;
	}

	function hidden_columns() {
		return array( 'category', 'start_date', 'end_date', 'enrollment_start', 'enrollment_end' );
	}

	function process_courselist_page() {
		$screen_id = get_current_screen()->id;
		add_filter( 'hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'courselist_columns' ) );

		add_screen_option( 'per_page', array( 'default' => 20, 'coursepress_course_per_page' ) );
	}

	function get_courselist_page() {
		global $CoursePress_User;

		$screen = get_current_screen();

		$args = array(
			'columns' => get_column_headers( $screen ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'courses' => $CoursePress_User->get_accessible_courses( false ),
			'course_edit_link' => add_query_arg( 'page', 'coursepress_course', admin_url( 'admin.php' ) ),
		);

		coursepress_render( 'views/admin/courselist', $args );
		coursepress_render( 'views/admin/footer-text' );
	}

	function get_course_edit_page() {
		// We need the image editor here, enqueue it!!!
		wp_enqueue_media();
		// Include datepicker
        wp_enqueue_script( 'jquery-ui-datepicker' );

		$course_id = filter_input( INPUT_GET, 'cid', FILTER_VALIDATE_INT );

		// If it's a new course, create a draft course
		if ( empty( $course_id ) ) {
            $course = coursepress_get_course( get_default_post_to_edit('course', true ) );
            $course->post_title = '';
        } else {
            $course = coursepress_get_course( $course_id );
        }

		// Set course category
		$category = array_values( $course->get_category() );
		$course->__set( 'course_category', $category );

		// Add $course object to localize array for quick editing
		$local_vars = array(
			'course' => $course, // Use in most steps
			//'course_units' => $course->get_units(), // Use in units steps
			'categories' => coursepress_get_categories(),
		);
		$this->localize_array = wp_parse_args( $local_vars, $this->localize_array );

		$menu_list = array(
			'course-type' => __( 'Type of Course', 'cp' ),
			'course-settings' => __( 'Course Settings', 'cp' ),
			'course-completion' => __( 'Course Completion', 'cp' ),
			'course-units' => __( 'Units', 'cp' ),
			'course-students' => __( 'Students', 'cp' ),
		);

		/**
		 * Allow population of additional menu list.
		 *
		 * @since 3.0
		 * @param array $menu_list
		 */
		$menu_list = apply_filters( 'coursepress_course_edit_menus', $menu_list );

		$args = array(
			'course_id' => $course_id,
			'page_title' => $course_id > 0 ? get_the_title( $course_id ) : __( 'New Course', 'cp' ),
			'menu_list' => $menu_list,
		);

		coursepress_render( 'views/admin/course-edit', $args );
		coursepress_render( 'views/admin/footer-text' );

		// Load templates
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/course-type', array( 'course_id' => $course_id ) );
		coursepress_render( 'views/tpl/course-settings' );
		coursepress_render( 'views/tpl/course-completion' );
		coursepress_render( 'views/tpl/course-units' );
	}

	function get_students_page() {
		$args = array(
			'courses' => coursepress_get_accessible_courses( true ),
		);
		coursepress_render( 'views/admin/students', $args );
	}

	function get_instructors_page() {
		coursepress_render( 'views/admin/instructors' );
	}

	function get_forum_page() {
		coursepress_render( 'views/admin/forum' );
	}

	function get_comments_page() {
		coursepress_render( 'views/admin/comments' );
	}

	function get_assessments_page() {
		coursepress_render( 'views/admin/assessments' );
	}

	function get_notification_page() {
		coursepress_render( 'views/admin/notifications' );
	}

	function get_settings_page() {
		$this->lib3();
		// Add global setting to localize array
		$this->localize_array['settings'] = coursepress_get_setting( true );
		$this->localize_array['messages'] = array(
		    'no_mp_woo' => sprintf( __( '%s and %s cannot be activated simultaneously!', 'cp' ), 'MarketPress', 'WooCommerce' ),
        );

        /**
         * Fire to get all available extensions.
         *
         * @since 3.0
         * @param array $extensions
         */
        $extensions = apply_filters( 'coursepress_extensions', array() );

        if ( ! $extensions ) {
            $extensions = array();
        }
        $this->localize_array['extensions'] = $extensions;

		coursepress_render( 'views/admin/settings' );

		// Add TPL
        coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/settings-general' );
		coursepress_render( 'views/tpl/settings-slugs' );
		coursepress_render( 'views/tpl/settings-emails' );
		coursepress_render( 'views/tpl/settings-capabilities' );
		coursepress_render( 'views/tpl/settings-certificate' );
		coursepress_render( 'views/tpl/settings-shortcodes' );
		coursepress_render( 'views/tpl/settings-extensions', array( 'extensions' => $extensions ) );
		coursepress_render( 'views/extensions/marketpress' );
		coursepress_render( 'views/tpl/settings-import-export' );
	}

	public function lib3() {
		global $CoursePress;
		$file = $CoursePress->plugin_path.'inc/external/wpmu-lib/core.php';
		include_once $file;
		lib3()->ui->add( 'core' );
		lib3()->ui->add( 'html' );
	}
}
