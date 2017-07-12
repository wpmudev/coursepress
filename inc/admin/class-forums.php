<?php
/**
 * Class CoursePress_Admin_Forums
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Forums extends CoursePress_Admin_Page {
	protected $slug = 'coursepress_forums';
	private $items;
	private $post_type = 'discussions';

	public function __construct() {
		parent::__construct();
	}

	function columns() {
		$columns = array(
			'topic' => __( 'Topic', 'cp' ),
			'course' => __( 'Course', 'cp' ),
			'comments' => __( 'Comments', 'cp' ),
			'status' => __( 'Status', 'cp' ),
		);
		return $columns;
	}

	public function get_page() {
		$search = isset( $_GET['s'] ) ? $_GET['s'] : '';
		$args = array(
			'columns' => $this->columns(),
			'courses' => coursepress_get_accessible_courses( false ),
			'hidden_columns' => array(),
			'forums' => $this->get_list(),
			'page' => $this->slug,
			'search' => $search,
			'instructor_edit_link' => '',
		);
		coursepress_render( 'views/admin/forums', $args );
		coursepress_render( 'views/admin/footer-text' );
	}

	public function get_list() {
		/**
		 * search
		 */
		$s = isset( $_POST['s'] )? mb_strtolower( trim( $_POST['s'] ) ):false;
		/**
		 * Per Page
		 */
		$per_page = $this->get_per_page();
		$per_page = $this->get_items_per_page( 'coursepress_forums_per_page', $per_page );
		/**
		 * Pagination
		 */
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		$post_args = array(
			'post_type' => $this->post_type,
			'posts_per_page' => $per_page,
			'paged' => $current_page,
			's' => $s,
			'post_status' => 'any',
		);
		/**
		 * Course ID
		 */
		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';
		if ( ! empty( $course_id ) ) {
			$post_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key' => 'course_id',
					'value' => (int) $course_id,
				)
			);
		}
		$wp_query = new WP_Query( $post_args );
		$this->items = array();
		foreach ( $wp_query->posts as $one ) {
			$one->course_id = get_post_meta( $one->ID, 'course_id', true );
			$one->unit_id = get_post_meta( $one->ID, 'unit_id', true );
			$one->comments_number = get_comments_number( $one->ID );
			$this->items[] = $one;
		}
		return $this->items;
	}
}
