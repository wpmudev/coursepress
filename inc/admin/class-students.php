<?php
/**
 * Class CoursePress_Admin_Students
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Students extends CoursePress_Admin_Page {

	/**
	 * Students custom table name.
	 *
	 * @var string
	 */
	protected $students_table;

	/**
	 * Students page slug.
	 *
	 * @var string
	 */
	protected $slug = 'coursepress_students';

	/**
	 * CoursePress_Admin_Students constructor.
	 */
	public function __construct() {

		global $wpdb;

		$this->students_table = $wpdb->prefix . 'coursepress_students';

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
		$search = isset( $_GET[ 's' ] ) ? $_GET[ 's' ] : '';

		// Data for template.
		$args = array(
			'columns' => get_column_headers( $screen ),
			'students' => $this->get_students( $count ),
			'courses' => coursepress_get_accessible_courses(),
			'list_table' => $this->set_pagination( $count, 'coursepress_students_per_page' ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'page' => $this->slug,
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

		// Filter by course ID, if set.
		if ( ! empty( $_GET['course_id'] ) ) {
			// Get student ids by course id.
			$student_ids = $this->get_students_by_course_id( $_GET['course_id'] );
			// Include only these courses in result.
			if ( ! empty( $student_ids ) ) {
				$args['include'] = $student_ids;
			} else {
				return array();
			}
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
	 * Get students ids by course id.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array|null|object
	 */
	function get_students_by_course_id( $course_id ) {

		global $wpdb;

		if ( empty( $course_id ) ) {
			return array();
		}

		// Make sure it is int.
		$course_id = absint( $course_id );

		// Get the student IDs for the course.
		$sql = $wpdb->prepare( "SELECT ID FROM `$this->students_table` WHERE `course_id`=%d GROUP BY student_id", $course_id );

		return $wpdb->get_col( $sql );
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

		/**
		 * Trigger to modify hidden columns.
		 *
		 * @since 3.0
		 * @param array $hidden_columns.
		 */
		return apply_filters( 'coursepress_studentlist_hidden_columns', array() );
	}
}