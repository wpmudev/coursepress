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
	protected $screens = array();

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
		add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_assets' ) );
	}

	function is_coursepress_page( $screen_id ) {
		return in_array( $screen_id, $this->screens );
	}

	function set_admin_assets() {
		global $CoursePress;

		$screen_id = get_current_screen()->id;

		if ( ! $this->is_coursepress_page( $screen_id ) )
			return;

		$coursepress_pagenow = preg_replace( '%top_level_page|coursepress-pro_page_%', '', $screen_id );

		// Set css here
		// Set js here
		// Set local vars here
	}

	function set_admin_menus() {
		// Main CP Page
		$label = __( 'CoursePress Pro', 'cp' );
		$screen_id = add_menu_page( $label, $label, $this->cap, $this->slug, array( $this, 'get_admin_page' ), '', 25 );

		// Add screen ID to the list of valid CP pages
		array_unshift( $this->screens, $screen_id );

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
	}

	function add_submenu( $label = '', $cap, $slug, $callback ) {
		$menu = add_submenu_page( $this->slug, 'CoursePress ' . $label, $label, $cap, $slug, array( $this, $callback ) );
		add_action( "load-{$menu}", array( $this, 'process_page' ) );

		// Add to the list of valid CP pages
		array_unshift( $this->screens, $menu );
	}

	function get_admin_page() {
		$args = array(
			'page_title' => 'CoursePress',
		);

		coursepress_render( 'views/admin/main-coursepress-page', $args );
	}

	function get_students_page() {}
	function get_instructors_page() {}
	function get_forum_page() {}
	function get_assessments_page() {}
	function get_notification_page() {}
	function get_settings_page() {}
}