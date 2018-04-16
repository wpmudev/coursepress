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
	public function get_page() {
		$view = isset( $_GET['view'] ) ? $_GET['view'] : 'list';
		if ( 'profile' === $view ) {
			$this->get_profile_view();
		} else {
			$this->get_list_view();
		}
	}

	/**
	 * Get the list of users with students role.
	 *
	 * @param int $count Total count of the students (pass by ref.).
	 *
	 * @return array CoursePress_User objects.
	 */
	public function get_students( &$count = 0 ) {
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
	public function get_students_by_course_id( $course_id ) {
		global $wpdb;
		if ( empty( $course_id ) ) {
			return array();
		}
		// Make sure it is int.
		$course_id = absint( $course_id );
		// Get the student IDs for the course.
		$sql = $wpdb->prepare( "SELECT student_id FROM `$this->students_table` WHERE `course_id`=%d GROUP BY student_id", $course_id );
		return $wpdb->get_col( $sql );
	}

	/**
	 * Custom screen options for course listing page.
	 *
	 * Setup our custom screen options for listing page.
	 *
	 * @uses get_current_screen().
	 */
	public function screen_options() {
		$screen_id = get_current_screen()->id;
		// Setup columns.
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'get_columns' ) );
		// Students per page.
		add_screen_option( 'per_page', array(
			'default' => 20,
			'option' => 'coursepress_students_per_page',
		) );
	}

	/**
	 * Get column for the listing page.
	 *
	 * @return array
	 */
	public function get_columns() {
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
	public function hidden_columns() {
		/**
		 * Trigger to modify hidden columns.
		 *
		 * @since 3.0
		 * @param array $hidden_columns.
		 */
		return apply_filters( 'coursepress_studentlist_hidden_columns', array() );
	}

	private function get_list_view() {
		$count  = 0;
		$screen = get_current_screen();
		// Set query parameters back.
		$search = isset( $_GET['student_search'] ) ? $_GET['student_search'] : '';
		// Data for template.
		$args = array(
			'columns'        => get_column_headers( $screen ),
			'students'       => $this->get_students( $count ),
			'courses'        => coursepress_get_accessible_courses(),
			'list_table'     => $this->set_pagination( $count, 'coursepress_students_per_page' ),
			'hidden_columns' => get_hidden_columns( $screen ),
			'page'           => $this->slug,
			'search'         => $search,
			'bulk_actions'   => $this->get_bulk_actions(),
		);
		// Render templates.
		coursepress_render( 'views/admin/students', $args );
	}

	private function get_profile_view() {
		$student_id = isset( $_GET['student_id'] ) ? $_GET['student_id'] : 0;
		$student    = new CoursePress_User( $student_id );
		if ( $student->is_error() && $student->is_student() ) {
			return;
		}

		$total_courses       = count( $student->get_enrolled_courses_ids() );
		$per_page            = $this->get_courses_per_page();
		$paged               = isset( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) : 1;
		$enrolled_course_ids = $student->get_enrolled_courses_ids( $per_page, $paged );

		$args = array(
			'student_id' => $student_id,
			'student'    => $student,
			'courses'    => $enrolled_course_ids,
			'pagination' => $this->get_pagination_list_table( $total_courses ),
		);
		coursepress_render( 'views/admin/student-profile', $args );
	}

	private function get_pagination_list_table( $count ) {
		// Using WP_List table for pagination.
		$listing = new WP_List_Table();
		$args    = array(
			'total_items' => $count,
			'per_page'    => $this->get_courses_per_page(),
		);
		$listing->set_pagination_args( $args );

		return $listing;
	}

	private function get_courses_per_page() {
		// Get no. of courses per page.
		$per_page = get_user_meta( get_current_user_id(), 'coursepress_course_per_page', true );
		$per_page = empty( $per_page ) ? coursepress_get_option( 'posts_per_page', 20 ) : $per_page;

		return $per_page;
	}

	/**
	 * Get bulk actions for students listing page.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'withdraw' => __( 'Withdraw', 'cp' ),
		);
		return $actions;
	}
}
