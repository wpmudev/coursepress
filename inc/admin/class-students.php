<?php
/**
 * Class CoursePress_Admin_Students
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Students extends CoursePress_Admin_Page {

	/**
	 * CoursePress_Admin_Students constructor.
	 */
	public function __construct() {

		// Initialize parent class.
		parent::__construct();
	}

	/**
	 * Get students listing page content and set pagination.
	 *
	 * @uses get_current_screen().
	 * @uses get_hidden_columns().
	 * @uses get_column_headers().
	 * @uses coursepress_render().
	 */
	function get_page() {

		$count = 0;
		$screen = get_current_screen();

		// Set query parameters back.
		$page = isset( $_GET[ 'page' ] ) ? esc_attr( $_GET[ 'page' ] ) : 'coursepress';
		$search = isset( $_GET[ 's' ] ) ? $_GET[ 's' ] : '';

		// Data for template.
		$args = array(
			'columns' => get_column_headers( $screen ),
			'students' => $this->get_students( $count ),
			'pagination' => $this->set_pagination( $count ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'page' => $page,
			'search' => $search,
		);

		// Render templates.
		coursepress_render( 'views/admin/students', $args );
		coursepress_render( 'views/admin/footer-text' );
	}

	/**
	 * Get the list of users with students role.
	 *
	 * @param int $count Total count of the students (pass by ref.).
	 *
	 * @return array CoursePress_User objects.
	 */
	function get_students( &$count = 0 ) {

		// Query arguments for WP_User_Query.
		$args = array();

		// Filter by course ID.
		if ( ! empty( $_GET['course_id'] ) ) {

		}

		// Add multisite support.
		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		// Set the parameters for pagination.
		$args['number'] = $this->items_per_page( 'coursepress_students_per_page' );
		$args['paged'] = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

		return coursepress_get_students( $args, $count );
	}

	/**
	 * Custom screen options for course listing page.
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

		// Students per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_students_per_page' ) );
	}

	/**
	 * Get column for the listing page.
	 *
	 * @return array
	 */
	function get_columns() {

		$columns = array(
			'id' => __( 'ID', 'cp' ),
			'student' => __( 'Student', 'cp' ),
			'last_active' => __( 'Last active', 'cp' ),
			'number_of_courses' => __( 'Number of courses', 'cp' ),
		);

		/**
		 * Trigger to allow custom column values.
		 *
		 * @since 3.0
		 * @param array $columns
		 */
		$columns = apply_filters( 'coursepress_studentlist_columns', $columns );

		return $columns;
	}

	/**
	 * Default columns to be hidden on listing page.
	 *
	 * @return array
	 */
	function hidden_columns() {

		$hidden_columns = array( 'id' );

		/**
		 * Trigger to modify hidden columns.
		 *
		 * @since 3.0
		 * @param array $hidden_columns.
		 */
		return apply_filters( 'coursepress_studentlist_hidden_columns', $hidden_columns );
	}

	/**
	 * Set pagination for students listing page.
	 *
	 * We are using WP_Listing_Table class to set pagination.
	 *
	 * @param int $count Total students.
	 *
	 * @return object
	 */
	function set_pagination( $count = 0 ) {

		// Using WP_List table for pagination.
		$listing = new WP_List_Table();

		$args = array(
			'total_items' => $count,
			'per_page' => $this->items_per_page( 'coursepress_students_per_page' ),
		);

		$listing->set_pagination_args( $args );

		return $listing;
	}
}