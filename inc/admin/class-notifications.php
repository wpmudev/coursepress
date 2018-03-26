<?php
/**
 * Class CoursePress_Admin_Notifications
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Notifications extends CoursePress_Admin_Page {

	/**
	 * Notifications page slug.
	 *
	 * @var string
	 */
	protected $slug = 'coursepress_notifications';

	/**
	 * Notifications post type.
	 *
	 * @var string
	 */
	protected $post_type = 'cp_notification';

	/**
	 * CoursePress_Admin_Notifications constructor.
	 */
	public function __construct() {

		// Initialize parent class.
		parent::__construct();
		add_filter( 'coursepress_admin_localize_array', array( $this, 'change_localize_array' ) );
	}

	/**
	 * JS localized sstrings.
	 *
	 * @since 3.0.0
	 */
	function change_localize_array( $localize_array ) {
		$localize_array['text']['deleting_post'] = __( 'Deleting alert... please wait', 'cp' );
		$localize_array['text']['delete_post'] = __( 'Are you sure you want to delete this alert?', 'cp' );
		if ( ! isset( $localize_array['text']['notifications'] ) ) {
			$localize_array['text']['notifications'] = array();
		}
		$localize_array['text']['notifications']['notification_content_is_empty'] = __( 'You should add somehing to the notification email body before sending.', 'cp' );
		$localize_array['text']['notifications']['notification_title_is_empty'] = __( 'You should add title before sending.', 'cp' );
		$localize_array['text']['notifications']['alert_content_is_empty'] = __( 'You should add somehing to the alert  body before save.', 'cp' );
		$localize_array['text']['notifications']['alert_title_is_empty'] = __( 'You should add title before save.', 'cp' );
		$localize_array['text']['notifications']['no_items'] = __( 'Please select at least one notifications.', 'cp' );
		$localize_array['text']['notifications']['delete_confirm'] = __( 'Are you sure to delete selected notifications?', 'cp' );
		$localize_array['text']['notifications']['deleting_items'] = __( 'Deleting notifications... please wait', 'cp' );
		return $localize_array;
	}

	/**
	 * Get notifications listing page content and set pagination.
	 *
	 * @uses get_current_screen().
	 * @uses get_hidden_columns().
	 * @uses get_column_headers().
	 * @uses coursepress_render().
	 */
	function get_page() {

		$count = 0;
		$screen = get_current_screen();

		// Data for email form.
		$email_args = array(
			'courses' => $this->get_courses(),
			'students' => coursepress_get_students_by_course(),
		);

		$current_status = $this->get_status();
		// Data for alerts listing.
		$alert_args = array(
			'columns' => get_column_headers( $screen ),
			'notifications' => $this->get_notifications( $current_status, $count ),
			'statuses' => coursepress_get_post_statuses( $this->post_type, $current_status, $this->slug ),
			'current_status' => $current_status,
			'list_table' => $this->set_pagination( $count, 'coursepress_notifications_per_page' ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'page' => $this->slug,
			'bulk_actions'   => $this->get_bulk_actions(),
		);

		// Data for alert form.
		$alert_form_args = array(
			'courses' => $this->get_courses(),
		);

		// Tokens for email form.
		$tokens = array(
			'STUDENT_FIRST_NAME',
			'STUDENT_LAST_NAME',
			'STUDENT_USERNAME',
			'BLOG_NAME',
			'LOGIN_ADDRESS',
			'WEBSITE_ADDRESS',
		);

		$format = sprintf( '<p>%1$s</p> <p>%2$s</p>', __( 'These codes will be replaced with actual data:', 'cp' ), '<b>%s</b>' );
		$email_args['tokens'] = sprintf( $format, implode( ', ', $tokens ) );

		// Render templates.
		coursepress_render( 'views/admin/notifications' );
		coursepress_render( 'views/tpl/common' );
		coursepress_render( 'views/tpl/notification-emails', $email_args );
		coursepress_render( 'views/tpl/notification-alerts', $alert_args );
		coursepress_render( 'views/tpl/notification-alerts-form', $alert_form_args );
	}

	/**
	 * Get the list of notifications.
	 *
	 * @param string $current_status Post status
	 * @param int $count Total count of the notifications (pass by ref.).
	 *
	 * @return array Notification objects.
	 */
	function get_notifications( $current_status, &$count = 0 ) {

		// Set the parameters for pagination.
		$per_page = $this->items_per_page( 'coursepress_notifications_per_page' );
		$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$args = array(
			'post_type' => $this->post_type,
			'suppress_filters' => true,
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'post_status' => $current_status,
		);

		/**
		 * Filter notifications query arguments.
		 *
		 * @since 3.0
		 * @param array $args
		 */
		$args = apply_filters( 'coursepress_pre_get_notifications', $args );

		$query = new WP_Query();
		$results = $query->query( $args );

		// Update the total courses count (ignoring items per page).
		$count = $query->found_posts;

		return $results;
	}

	/**
	 * Custom screen options for notification listing page.
	 *
	 * Setup our custom screen options for listing page.
	 *
	 * @uses get_current_screen().
	 */
	function screen_options() {

		$screen_id = get_current_screen()->id;

		// Setup columns.
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'get_columns' ) );

		//  Notifications per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_notifications_per_page' ) );
	}

	/**
	 * Get column for the listing page.
	 *
	 * @return array
	 */
	function get_columns( $current_status ) {

		$columns = array(
			'title' => __( 'Notification title', 'cp' ),
			'course' => __( 'Course', 'cp' ),
		);

		if ( 'trash' !== $current_status ) {
			$columns['status'] = __( 'Status', 'cp' );
		}

		/**
		 * Trigger to allow custom column values.
		 *
		 * @since 3.0
		 * @param array $columns
		 */
		$columns = apply_filters( 'coursepress_notifications_list_columns', $columns );

		return $columns;
	}

	/**
	 * Default columns to be hidden on listing page.
	 *
	 * @return array
	 */
	function hidden_columns() {

		/**
		 * Trigger to modify hidden columns.
		 *
		 * @since 3.0
		 * @param array $hidden_columns.
		 */
		return apply_filters( 'coursepress_notifications_list_hidden_columns', array() );
	}

	/**
	 * Get accessible course for the user.
	 *
	 * @return array
	 */
	private function get_courses() {

		$user = coursepress_get_user();

		if ( is_wp_error( $user ) ) {
			return array();
		}

		return $user->get_accessible_courses( 'publish' );
	}
}
