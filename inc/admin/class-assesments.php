<?php
/**
 * Class CoursePress_Admin_Assesments
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Assesments extends CoursePress_Admin_Page {

	/**
	 * Assesments page slug.
	 *
	 * @var string
	 */
	protected $slug = 'coursepress_assessments';

	/**
	 * CoursePress_Admin_Assesments constructor.
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
		$search = isset( $_GET[ 's' ] ) ? $_GET[ 's' ] : '';

		// Data for template.
		$args = array(
			'columns' => get_column_headers( $screen ),
			'assessments' => $this->get_assesments( $count ),
			'courses' => coursepress_get_accessible_courses(),
			'list_table' => $this->set_pagination( $count ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'page' => $this->slug,
			'search' => $search,
		);

		// Render templates.
		coursepress_render( 'views/admin/assessments', $args );;
		coursepress_render( 'views/admin/footer-text' );
	}

	/**
	 * Get the list of users with students role.
	 *
	 * @param int $count Total count of the students (pass by ref.).
	 *
	 * @return array CoursePress_User objects.
	 */
	function get_assesments( &$count = 0 ) {

		//echo '<pre>'; print_r((new CoursePress_Assessment(1298))->get_assessments()); echo '</pre>'; exit;
		// Query arguments for WP_User_Query.
		$args = array();

		if ( empty( $_GET['course_id'] ) ) {
			return array();
		}

		$student_ids = $this->get_students_by_course_id( $_GET['course_id'] );
		echo '<pre>'; print_r($student_ids); echo '</pre>'; exit;

		if ( empty( $student_ids ) ) {
			return array();
		}

		// Set the parameters for pagination.
		//$args['number'] = $this->items_per_page( 'coursepress_assesments_per_page' );
		//$args['paged'] = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

		$assessments = new CoursePress_Assessments( $_GET['course_id'], $student_ids );

		return $assessments->get_assessments();
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
	 * Custom screen options for assesments listing page.
	 *
	 * @uses get_current_screen().
	 */
	function screen_options() {

		$screen_id = get_current_screen()->id;

		// Setup columns.
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'get_columns' ) );

		// Assesments per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_assesments_per_page' ) );
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
			'grade' => __( 'Grade', 'cp' ),
			'modules_progress' => __( 'Modules progress', 'cp' ),
			'reports' => __( 'Reports', 'cp' ),
		);

		/**
		 * Trigger to allow custom column values.
		 *
		 * @since 3.0
		 * @param array $columns
		 */
		$columns = apply_filters( 'coursepress_assesments_columns', $columns );

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
		return apply_filters( 'coursepress_assesments_hidden_columns', array() );
	}

	/**
	 * Set pagination for assesments listing page.
	 *
	 * We are using WP_Listing_Table class to set pagination.
	 *
	 * @param int $count Total assesments.
	 *
	 * @return object
	 */
	function set_pagination( $count = 0 ) {

		// Using WP_List table for pagination.
		$listing = new WP_List_Table();

		$args = array(
			'total_items' => $count,
			'per_page' => $this->items_per_page( 'coursepress_assesments_per_page' ),
		);

		$listing->set_pagination_args( $args );

		return $listing;
	}
}