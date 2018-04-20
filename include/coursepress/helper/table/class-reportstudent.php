<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_ReportStudent extends WP_List_Table {

	private $course_id = 0;
	private $add_new = false;
	private $students = array();
	private $last_student_progress = array();
	private $is_cache_path_writable;

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Student', 'coursepress' ),
			'plural' => __( 'Students', 'coursepress' ),
			'ajax' => false,// should this table support ajax?
		) );

		$this->is_cache_path_writable = CoursePress_Helper_PDF::is_cache_path_writable();

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
			'ID' => __( 'ID', 'coursepress' ),
			'display_name' => __( 'Username', 'coursepress' ),
			'first_name' => __( 'First Name', 'coursepress' ),
			'last_name' => __( 'Last Name', 'coursepress' ),
			'responses' => __( 'Responses', 'coursepress' ) . '<span style="display:inline-block;" class="help-tooltip">' . __( 'Assessable items only.', 'coursepress' ) . '</span>',
			'average' => __( 'Average', 'coursepress' ),
			'report' => __( 'Report', 'coursepress' ),
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
		if ( $this->is_cache_path_writable ) {
			return sprintf(
				'<a class="pdf" data-student="%d" data-course="%d">&nbsp;</a>',
				esc_attr( $item->ID ),
				esc_attr( $this->course_id )
			);
		}
		return sprintf( '<span class="pdf" title="%s" data-click="false"></span>', esc_attr__( 'We can not generata PDF. Cache directory is not writable.', 'coursepress' ) );
	}

	public function prepare_items() {
		global $wpdb;

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$per_page = 20;
		$current_page = $this->get_pagenum();

		$offset = ( $current_page - 1 ) * $per_page;

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// $post_args = array(
		// 'post_type' => $this->post_type,
		// 'post_status' => $post_status,
		// 'posts_per_page' => $per_page,
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
			'number' => $per_page,
			'offset' => $offset,
		) );

		$this->items = $users->get_results();

		$total_items = $users->get_total();
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
			)
		);
	}

	public function extra_tablenav( $which ) {
	}

	public function no_items() {
		_e( 'There are no students enrolled in this course.', 'coursepress' );
	}
}
