<?php
/**
 * Class CoursePress_Page
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Page extends CoursePress_Utility {
	protected $cap = 'manage_options'; // Default cap to use to all CP pages
	protected $slug = 'coursepress';
	protected $is_current_page = false;

	/**
	 * @var array List of CP screen_id page
	 */
	protected $screens = array();

	var $localize_array = array();

	public function __construct() {
		// Check if user can't access coursepress
		if ( ! current_user_can( 'coursepress_dashboard_cap' ) ) {
			$this->is_error = true;

			return;
		}

		// Setup the page
		add_action( 'admin_menu', array( $this, 'set_admin_menus' ) );
		// Setup admin assets need for this page
		add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_css' ) );
		// Hook to ajax call
		add_action( 'wp_ajax_coursepress_request', array( $this, 'process_ajax_request' ) );
	}

	/**
	 * Helper function to add `coursepress` submenu.
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
	}

	function set_admin_menus() {
		global $submenu;

		// Main CP Page
		$label = __( 'CoursePress Base', 'cp' );
		$screen_id = add_menu_page( $label, $label, 'coursepress_dashboard_cap', $this->slug, array( $this, 'get_courselist_page' ), '', 25 );

		// Add screen ID to the list of valid CP pages
		array_unshift( $this->screens, $screen_id );

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

	function set_admin_css() {
		$coursepress_pagenow = coursepress_is_admin();

		if ( ! $coursepress_pagenow )
			return; // Do not continue

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

		// Set stylesheets
		$this->enqueue_style( 'fontawesome', 'assets/external/css/font-awesome.min.css' );
		$this->enqueue_style( 'coursepress-admin-common', 'assets/css/admin-common.min.css' );
		$this->enqueue_style( $coursepress_pagenow, 'assets/css/' . $coursepress_pagenow . '.min.css' );

		// Set js
		add_action( 'admin_footer', array( $this, 'set_admin_scripts' ) );
		// Show custom WPMU footer text
		add_action( 'in_admin_footer', array( $this, 'wpmu_footer_text' ) );
	}

	function enqueue_style( $id, $src ) {
		global $CoursePress;

		wp_enqueue_style( $id, $CoursePress->plugin_url . $src, false, $CoursePress->version );
	}

	function set_admin_scripts() {
		global $CoursePress;

		$coursepress_pagenow = coursepress_is_admin();

		if ( ! $coursepress_pagenow )
			return; // Do not continue

		$plugin_url = $CoursePress->plugin_url;

		$this->localize_array = wp_parse_args( $this->localize_array, array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( 'coursepress_nonce' ),
			'cookie' => array(
				'hash' => COOKIEHASH,
				'path' => COOKIEPATH,
				'ssl' => is_ssl(),
			),
			'coursepress_page' => add_query_arg( 'page', 'coursepress', admin_url() ),
			// Common use texts
			'text' => array(
				'media' => array(
					'select_image' => __( 'Select Image', 'cp' ),
					'select_feature_image' => __( 'Select Feature Image', 'cp' ),
				),
			),
		) );

		// General admin js
		wp_enqueue_script( 'coursepress-admin-general', $plugin_url . 'assets/js/admin-general.min.js', array( 'jquery', 'backbone', 'underscore' ), $CoursePress->version, true );
		$this->enqueue_script( $coursepress_pagenow, 'assets/js/' . $coursepress_pagenow . '.min.js' );

		// Set local vars
		$localize_array = apply_filters( 'coursepress_admin_localize_array', $this->localize_array );
		wp_localize_script( 'coursepress-admin-general', '_coursepress', $localize_array );
	}

	function enqueue_script( $id, $src ) {
		global $CoursePress;

		wp_enqueue_script( $id, $CoursePress->plugin_url . $src, false, $CoursePress->version, true );
	}

	function wpmu_footer_text() {
		$url = sprintf( 'by <a href="%s" target="_blank">WPMU DEV</a>', 'https://premium.wpmudev.org' );

		printf( '<p class="wpmu-footer-text">%s %s %s</p>', __( 'Made with', 'cp' ), '<i class="fa fa-heart"></i>', $url );
	}

	function process_ajax_request() {
		$input = json_decode( file_get_contents( 'php://input' ) );
		$error = array( 'code' => 'cannot_process', 'message' => __( 'Something went wrong. Please try again.', 'cp' ) );

		if ( isset( $input->_wpnonce ) && wp_verify_nonce( $input->_wpnonce, 'coursepress_nonce' ) ) {
			$action = $input->action;
			$input->success = false;
			$input->response = array();
			$input->error = array();

			/**
			 * Trigger when an ajax request is sent base on the given `action` name.
			 *
			 * @since 3.0
			 * @param object $input
			 */
			do_action( 'coursepress_' . $action, $input );

			if ( $input->success )
				wp_send_json_success( $input->response );
			else
				$error = wp_parse_args( $error, $input->error );
		}

		wp_send_json_error( $error );
	}

	function get_courselist_page() {
		$args = array(
			'page_title' => 'CoursePress', // @note: DO NOT TRANSLATE
		);

		coursepress_render( 'views/admin/courselist', $args );
	}

	function get_course_edit_page() {
		// We need the image editor here, enqueue it!!!
		wp_enqueue_media();

		$course_id = filter_input( INPUT_GET, 'cid', FILTER_VALIDATE_INT );

		// If it's a new course, create a draft course
		if ( empty( $course_id ) )
			$course = coursepress_get_course( get_default_post_to_edit( 'course', true ) );
		else
			$course = coursepress_get_course( $course_id );

		// Add $course object to localize array for quick editing
		$this->localize_array['course'] = $course;
		$this->localize_array['course_units'] = $course->get_units();

		$menu_list = array(
			'course-type' => __( 'Type of Course', 'cp' ),
			'course-settings' => __( 'Course Settings', 'cp' ),
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

		coursepress_render('views/admin/course-edit', $args );

		// Load templates
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/course-type', array( 'course_id' => $course_id ) );
		coursepress_render( 'views/tpl/course-settings' );
		coursepress_render( 'views/tpl/course-units' );
	}

	function get_students_page() {
		$args = array(
			'courses' => coursepress_get_accessable_courses( true, false, true ),
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
		coursepress_render( 'views/admin/settings' );
	}
}