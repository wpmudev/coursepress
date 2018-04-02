<?php
/**
 * Reports table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class CoursePress_Admin_Table_Reports extends WP_List_Table {
	var $courses = array();
	var $course_id = 0;
	var $last_student_progress = array();
	var $is_cache_path_writable = false;

	/** Class constructor */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Reports', 'cp' ),
			'plural' => __( 'Reports', 'cp' ),
			'ajax' => false,// should this table support ajax?
		) );
		$this->courses = coursepress_get_accessible_courses();
		if ( empty( $_REQUEST['course_id'] ) && ! empty( $this->courses ) ) {
			$tmp = array_keys( $this->courses );
			$this->course_id = array_shift( $tmp );
		} else {
			$this->course_id = (int) ( isset( $_REQUEST['course_id'] )? $_REQUEST['course_id'] : 0 );
		}
	}

	public function prepare_items() {
		global $wpdb;

		$screen = get_current_screen();
		/**
		 * Per Page
		 */
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $this->get_option( 'per_page', 'default' );
			if ( ! $per_page ) {
				$per_page = 20;
			}
		}
		$per_page = $this->get_items_per_page( 'coursepress_reports_per_page', $per_page );

		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

		$users = coursepress_get_students_ids( $this->course_id, $offset, $per_page );
		$this->items = array();
		/**
		 */
		foreach ( $users as $id ) {
			$user = coursepress_get_user( $id );
			/**
			 * progress
			 */
			$user->progress = $user->get_completion_data( $this->course_id );
			/**
			 * responses
			 */
			$user->responses = coursepress_count_course_responses( $user, $this->course_id, $user->progress );
			/**
			 * preview
			 */
			$args = array(
				'student_id' => $id,
				'course_id' => $this->course_id,
				'mode' => 'html',
			);
			$user->preview_url = wp_nonce_url( add_query_arg( $args ), 'coursepress_preview_report' );
			/**
			 */
			$this->items[] = $user;
		}

		$total_items = coursepress_get_students_ids( $this->course_id, 0, 0 );
		$total_items = count( $total_items );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
				'course_id' => $this->course_id,
			)
		);
	}
}
