<?php
/**
 * Class CoursePress_Admin_Comments
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Comments extends CoursePress_Admin_Page {
	protected $slug = 'coursepress_comments';
	private $items;
	private $post_type = 'discussions';

	public function __construct() {
		parent::__construct();
	}

	function columns() {
		$columns = array(
			'student' => __( 'Student', 'cp' ),
			'comment' => __( 'Comment', 'cp' ),
			'course' => __( 'Course', 'cp' ),
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
			'comments' => $this->get_list(),
			'page' => $this->slug,
			'search' => $search,
			'edit_link' => add_query_arg(
				array(
					'page' => $this->slug,
				),
				admin_url( 'admin.php' )
			),
		);
		coursepress_render( 'views/admin/comments', $args );
		coursepress_render( 'views/admin/footer-text' );
	}

	public function get_list() {
		$modules = new CoursePress_Data_Modules();
		$course_id = ( isset( $_REQUEST['course_id'] ) ) ? $_REQUEST['course_id'] : null;
		$discussions = $modules->get_all_modules_ids_by_type( 'discussion', $course_id );
		if ( empty( $discussions ) ) {
			return;
		}

		$comment_status = isset( $_REQUEST['comment_status'] ) ? $_REQUEST['comment_status'] : 'all';
		if ( ! in_array( $comment_status, array( 'all', 'moderated', 'approved', 'spam', 'trash' ) ) ) {
			$comment_status = 'all';
		}

		$comment_type = ! empty( $_REQUEST['comment_type'] ) ? $_REQUEST['comment_type'] : '';

		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : '';

		$user_id = ( isset( $_REQUEST['user_id'] ) ) ? $_REQUEST['user_id'] : '';

		$orderby = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : '';
		$order = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : '';

		$comments_per_page = $this->get_per_page( $comment_status );

		$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( isset( $_REQUEST['number'] ) ) {
			$number = (int) $_REQUEST['number'];
		} else {
			$number = $comments_per_page + min( 8, $comments_per_page ); // Grab a few extra
		}

		$page = $this->get_pagenum();

		if ( isset( $_REQUEST['start'] ) ) {
			$start = $_REQUEST['start'];
		} else {
			$start = ( $page - 1 ) * $comments_per_page;
		}

		if ( $doing_ajax && isset( $_REQUEST['offset'] ) ) {
			$start += $_REQUEST['offset'];
		}

		$status_map = array(
			'moderated' => 'hold',
			'approved' => 'approve',
			'all' => '',
		);

		$args = array(
			'status' => isset( $status_map[ $comment_status ] ) ? $status_map[ $comment_status ] : $comment_status,
			'search' => $search,
			'user_id' => $user_id,
			'offset' => $start,
			'number' => $number,
			'type' => $comment_type,
			'orderby' => $orderby,
			'order' => $order,
			'post__in' => $discussions,
		);
		$_comments = get_comments( $args );
		if ( is_array( $_comments ) ) {
			update_comment_cache( $_comments );

			$this->items = array_slice( $_comments, 0, $comments_per_page );
			$this->extra_items = array_slice( $_comments, $comments_per_page );

			$_comment_post_ids = array_unique( wp_list_pluck( $_comments, 'comment_post_ID' ) );

			$this->pending_count = get_pending_comments_num( $_comment_post_ids );
		}

		$total_comments = get_comments( array_merge( $args, array(
			'count' => true,
			'offset' => 0,
			'number' => 0,
		) ) );

		/*
		$this->set_pagination_args( array(
			'total_items' => $total_comments,
			'per_page' => $comments_per_page,
        ) );
         */

		return $this->items;
	}
}
