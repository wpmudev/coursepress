<?php
/**
 * Class CoursePress_Page
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Page extends CoursePress_User {
	protected $cap = 'manage_options'; // Default cap to use to all CP pages
	protected $slug = 'coursepress';
	protected $is_current_page = false;

	/**
	 * @var array List of CP screen_id page
	 */
	protected $screens = array();

	var $localize_array = array();

	public function __construct() {
		parent::__construct( true );

		// Check if user can't access coursepress
		if ( ! $this->user_can( $this->cap ) ) {
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

	function is_coursepress_page( $screen_id ) {
		return in_array( $screen_id, $this->screens );
	}

	function get_coursepress_screen_id( $screen_id = '' ) {
		if ( empty( $screen_id ) )
			$screen_id = get_current_screen()->id;

		$id = preg_replace( '%toplevel_page_|coursepress-pro_page_|coursepress-base_page_|coursepress_page%', '', $screen_id );

		return $id;
	}

	/**
	 * Helper function to add `coursepress` submenu page.
	 *
	 * @param string $label
	 * @param string $cap
	 * @param string $slug
	 * @param string $callback
	 */
	function add_submenu( $label = '', $cap, $slug, $callback ) {
		$menu = add_submenu_page( $this->slug, 'CoursePress ' . $label, $label, $cap, $slug, array( $this, $callback ) );
		$screen_id = $this->get_coursepress_screen_id( $menu );

//		add_action( "load-{$menu}", array( $this, 'process_' . $screen_id . '_page' ) );

		// Add to the list of valid CP pages
		array_unshift( $this->screens, $menu );
	}

	function set_admin_menus() {
		global $submenu;

		// Main CP Page
		$label = __( 'CoursePress Base', 'cp' );
		$screen_id = add_menu_page( $label, $label, $this->cap, $this->slug, array( $this, 'get_courselist_page' ), '', 25 );

		// Add screen ID to the list of valid CP pages
		array_unshift( $this->screens, $screen_id );

		// Set course edit page
		$edit_label = __( 'New Course', 'cp' );
		$this->add_submenu( $edit_label, $this->cap, 'coursepress_course', 'get_course_edit_page' );

		// Set students page
		$student_label = __( 'Students', 'cp' );
		$this->add_submenu( $student_label, $this->cap, 'coursepress_students', 'get_students_page' );

		// Set instructor page
		$instructor_label = __( 'Instructors', 'cp' );
		$this->add_submenu( $instructor_label, $this->cap, 'coursepress_instructors', 'get_instructors_page' );

		// Set assessment page
		$assessment_label = __( 'Assessments', 'cp' );
		$this->add_submenu( $assessment_label, $this->cap, 'coursepress_assessments', 'get_assessments_page' );

		// Set Forum page
		$forum_label = __( 'Forum', 'cp' );
		$this->add_submenu( $forum_label, $this->cap, 'coursepress_forum', 'get_forum_page' );

		// Set Notification page
		$notification_label = __( 'Notifications', 'cp' );
		$this->add_submenu( $notification_label, $this->cap, 'coursepress_notifications', 'get_notification_page' );

		// Set Settings page
		$settings_label = __( 'Settings', 'cp' );
		$this->add_submenu( $settings_label, $this->cap, 'coursepress_settings', 'get_settings_page' );

		// Change top menu label
		$submenu['coursepress'][0][0] = __( 'Courses', 'cp' );
	}

	function set_admin_css() {
		global $CoursePress;

		$screen_id = get_current_screen()->id;

		// If current page is not CP return!
		if ( ! $this->is_coursepress_page( $screen_id ) )
			return;

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
		 *  `coursepress_notifications`
		 *  `coursepress_settings`
		 * }
		 */
		$coursepress_pagenow = $this->get_coursepress_screen_id();
		$plugin_url = $CoursePress->plugin_url;

		// Set stylesheets
		$this->enqueue_style( 'fontawesome', 'assets/external/css/font-awesome.min.css' );
		$this->enqueue_style( 'coursepress-admin-common', 'assets/css/admin-common.min.css' );
		$this->enqueue_style( $coursepress_pagenow, 'assets/css/' . $coursepress_pagenow . '.min.css' );

		// Set js
		add_action( 'admin_footer', array( $this, 'set_admin_scripts' ) );
	}

	function enqueue_style( $id, $src ) {
		global $CoursePress;

		wp_enqueue_style( $id, $CoursePress->plugin_url . $src, false, $CoursePress->version );
	}

	function set_admin_scripts() {
		global $CoursePress;

		$coursepress_pagenow = $this->get_coursepress_screen_id();
		$plugin_url = $CoursePress->plugin_url;

		$this->localize_array = wp_parse_args( $this->localize_array, array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( 'coursepress_nonce' ),
			'cookiehash' => COOKIEHASH,
			'coursepress_page' => add_query_arg( 'page', 'coursepress', admin_url() ),
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

		coursepress_render( 'views/admin/main-coursepress-page', $args );
	}

	function get_course_edit_page() {
		$course_id = filter_input( INPUT_GET, 'cid', FILTER_VALIDATE_INT );

		// If it's a new course, create a draft course
		if ( empty( $course_id ) )
			$course = new CoursePress_Course( get_default_post_to_edit( 'course', true ) );
		else
			$course = new CoursePress_Course( $course_id );

		// Add $course object to localize array for quick editing
		$this->localize_array['course'] = $course;

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
		coursepress_render('views/tpl/course-type');
	}

	function get_students_page() {}

	function get_instructors_page() {}

	function get_forum_page() {}

	function get_assessments_page() {}

	function get_notification_page() {}

	function get_settings_page() {}
}