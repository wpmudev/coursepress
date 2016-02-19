<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_ReportStudents extends WP_List_Table {

	private $course_id = 0;
	private $add_new = false;
	private $students = array();
	private $last_student_progress = array();

	/** Class constructor */
	public function __construct() {

		// $post_format = CoursePress_Data_Course::get_format();
		parent::__construct( array(
			'singular' => __( 'Student', CoursePress::TD ),
			'plural' => __( 'Students', CoursePress::TD ),
			'ajax' => false,// should this table support ajax?
		) );

		// $this->post_type = CoursePress_Data_PostFormats::prefix() . $post_format['post_type'];
		// $this->count = wp_count_posts( $this->post_type );
	}

	public function set_course( $id ) {
		$this->course_id = (int) $id;
	}

	public function set_add_new( $bool ) {
		$this->add_new = $bool;
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'ID' => __( 'ID', CoursePress::TD ),
			'display_name' => __( 'Username', CoursePress::TD ),
			'first_name' => __( 'First Name', CoursePress::TD ),
			'last_name' => __( 'Last Name', CoursePress::TD ),
			'responses' => __( 'Responses', CoursePress::TD ) . '<span style="display:inline-block;" class="help-tooltip">' . __( 'Assessable items only.', CoursePress::TD ) . '</span>',
			'average' => __( 'Average', CoursePress::TD ),
			'report' => __( 'Report', CoursePress::TD ),
		);

		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	public function get_sortable_columns() {
		return array( 'title' => array( 'title', false ) );
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
		);
	}

	public function column_ID( $item ) {
		$this->students[] = $item->ID;
		return sprintf(
			'%d', $item->ID
		);
	}

	public function column_display_name( $item ) {
		return sprintf(
			'%s', $item->display_name
		);
	}

	public function column_first_name( $item ) {
		return sprintf(
			'%s', get_user_option( 'first_name', $item->ID )
		);
	}

	public function column_last_name( $item ) {
		return sprintf(
			'%s', get_user_option( 'last_name', $item->ID )
		);
	}

	public function column_responses( $item ) {

		$this->last_student_progress = CoursePress_Data_Student::get_completion_data( $item->ID, $this->course_id );
		$responses = (int) CoursePress_Data_Student::count_course_responses( $item->ID, $this->course_id, $this->last_student_progress );

		return sprintf(
			'%d', $responses
		);
	}

	public function column_average( $item ) {

		$average = (int) CoursePress_Data_Student::average_course_responses( $item->ID, $this->course_id, $this->last_student_progress );

		return sprintf(
			'%d%%', $average
		);
	}

	public function column_report( $item ) {
		return sprintf(
			'<a class="pdf" data-student="%d" data-course="%d">&nbsp;</a>', $item->ID, $this->course_id
		);
	}

	public function prepare_items() {
		global $wpdb;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$perPage = 20;
		$currentPage = $this->get_pagenum();

		$offset = ( $currentPage - 1 ) * $perPage;

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// $post_args = array(
		// 'post_type' => $this->post_type,
		// 'post_status' => $post_status,
		// 'posts_per_page' => $perPage,
		// 'offset' => $offset,
		// 's' => isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : ''
		// );
		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $this->course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $this->course_id;
		}

		// Could use the Course Model methods here, but lets try stick to one query
		$users = new WP_User_Query( array(
			'meta_key' => $course_meta_key,
			'meta_compare' => 'EXISTS',
			'number' => $perPage,
			'offset' => $offset,
		) );

		$this->items = $users->get_results();

		$totalItems = $users->get_total();
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page' => $perPage,
		) );

	}

	public function extra_tablenav( $which ) {
	}

	public function no_items() {
		_e( 'There are no students enrolled in this course.', CoursePress::TD );
	}
}
