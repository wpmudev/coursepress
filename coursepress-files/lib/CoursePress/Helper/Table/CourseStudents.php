<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_CourseStudents extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		//$post_format = CoursePress_Model_Course::get_format();

		parent::__construct( [
			'singular' => __( 'Student', CoursePress::TD ),
			'plural'   => __( 'Students', CoursePress::TD ),
			'ajax'     => false //should this table support ajax?
		] );

		//$this->post_type = CoursePress_Model_PostFormats::prefix() . $post_format['post_type'];
		//$this->count     = wp_count_posts( $this->post_type );

	}


	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'ID'         => __( 'ID', CoursePress::TD ),
			'post_title' => __( 'Username', CoursePress::TD ),
			'units'      => __( 'First Name', CoursePress::TD ),
			'students'   => __( 'Last Name', CoursePress::TD ),
			'status'     => __( 'Profile', CoursePress::TD ),
			'actions'    => __( 'Actions', CoursePress::TD ),
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

	public function prepare_items() {

		//$accepted_tabs = array( 'publish', 'private', 'all' );
		//$tab           = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $accepted_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'publish';
		//$valid_categories = CoursePress_Model_Course::get_course_categories();
		//$valid_categories = array_keys( $valid_categories );
		//$category      = isset( $_GET['category'] ) && in_array( $_GET['category'], $valid_categories ) ? sanitize_text_field( $_GET['category'] ) : false;
		//
		//$post_status = 'all' == $tab ? array( 'publish', 'private' ) : $tab;
		//
		//// Debug
		//$post_status = 'all';
		//
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		//
		//$perPage     = 10;
		//$currentPage = $this->get_pagenum();
		//
		//// Debug
		//$perPage = 10;
		//
		//$offset = ( $currentPage - 1 ) * $perPage;
		//
		$this->_column_headers = array( $columns, $hidden, $sortable );

		//$post_args             = array(
		//	'post_type'      => $this->post_type,
		//	'post_status'    => $post_status,
		//	'posts_per_page' => $perPage,
		//	'offset'         => $offset,
		//	's'              => isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : ''
		//);
		//
		//// @todo: Add permissions
		//
		//
		//// Add category filter
		//if ( $category ) {
		//	$post_args['tax_query'] = array(
		//		array(
		//			'taxonomy' => 'course_category',
		//			'field'    => 'term_id',
		//			'terms'    => array( $category ),
		//		)
		//	);
		//}
		//
		//
		//$query = new WP_Query( $post_args );

		//

		add_action( 'pre_user_query', array( &$this, 'add_first_and_last' ) );

		$users = new CoursePress_Helper_Query_Student('','', array( 'override' => 'everything' ) );

		error_log( print_r( $users, true ) );
		$this->items = get_users( array(
			//'blog_id'      => $GLOBALS['blog_id'],
			'fields' => 'all_with_meta'
		) );
		//

		//error_log( print_r( $this->items, true ) );
		//$totalItems = $query->found_posts;
		//$this->set_pagination_args( array(
		//	'total_items' => $totalItems,
		//	'per_page'    => $perPage
		//) );

	}

	public static function add_first_and_last( $user_search ) {
		global $wpdb;

		$user_search->query_from .= " INNER JOIN {$wpdb->usermeta} m1 ON " .
		                            "{$wpdb->users}.ID=m1.user_id AND (m1.meta_key='first_name')";
		$user_search->query_from .= " INNER JOIN {$wpdb->usermeta} m2 ON " .
		                            "{$wpdb->users}.ID=m2.user_id AND (m2.meta_key='last_name')";

	}


}