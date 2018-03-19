<?php
/**
 * Students Table
 *
 * This class extends WP_Users_List_Table to manage courses students.
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_Comments_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-comments-list-table.php';
}
class CoursePress_Admin_Table_Comments extends WP_Comments_List_Table {
	var $course_id = 0;

	private $users = array();
	private $modules = array();
	private $count = 0;

	public function __construct() {
		parent::__construct();
		if ( ! empty( $_REQUEST['course_id'] ) ) {
			$course_id = (int) $_REQUEST['course_id'];
			if ( coursepress_is_course( $course_id ) ) {
				$this->course_id = $course_id;
			}
		}
	}

	public function prepare_items() {
		global $post_id, $comment_status, $search, $comment_type, $CoursePress_Core;

		$course_id = ( isset( $_REQUEST['course_id'] ) ) ? $_REQUEST['course_id'] : null;
		if ( !empty( $course_id ) ) {

			$discussions = get_posts( array(
				'fields' => 'ids',
				'meta_query' => array(
					array(
						'key' => 'course_id',
						'value' => $course_id,
					),
				),
				'post_type'	=> $CoursePress_Core->step_post_type,
				'posts_per_page' => -1
			));
			if ( empty( $discussions ) ) {
				return;
			}
		} else {
			$discussions = get_posts( array(
				'fields' => 'ids',
				'post_type'	=> $CoursePress_Core->step_post_type,
				'posts_per_page' => -1
			));
			if ( empty( $discussions ) ) {
				return;
			}
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
			'fields' => 'ids',
			'offset' => $start,
			'post__in' => $discussions,
			'search' => $search,
			'status' => isset( $status_map[ $comment_status ] ) ? $status_map[ $comment_status ] : $comment_status,
			'type' => $comment_type,
			'user_id' => $user_id,
		);
		/**
		 * only count
		 */
		$_comments = get_comments( $args );
		$this->count = count( $_comments );

		/**
		 * get comments
		 */
		unset( $args['fields'] );
		$args['number'] = $number;
		$args['orderby'] = $order;
		$args['order'] = $orderby;
		$_comments = get_comments( $args );

		if ( is_array( $_comments ) ) {
			update_comment_cache( $_comments );
			$this->items = array_slice( $_comments, 0, $comments_per_page );
			$this->extra_items = array_slice( $_comments, $comments_per_page );
			$_comment_post_ids = array_unique( wp_list_pluck( $_comments, 'comment_post_ID' ) );
			$this->pending_count = get_pending_comments_num( $_comment_post_ids );
			/**
			 * add user data
			 */
			foreach ( $this->items as $i => $item ) {
				if ( empty( $item->user_id ) ) {
					continue;
				}
				if ( ! isset( $this->users[ $item->user_id ] ) ) {
					$user = get_userdata( $item->user_id );
					$this->users[ $item->user_id ] = array(
						'display_name' => $user->data->display_name,
						'avatar' => get_avatar( $item->user_id, 32 ),
					);
				}
				$this->items[ $i ]->user = $this->users[ $item->user_id ];
			}
			/**
			 * add parent data
			 */
			foreach ( $this->items as $i => $item ) {
				if ( empty( $item->comment_post_ID ) ) {
					continue;
				}
				if ( ! isset( $this->modules[ $item->comment_post_ID ] ) ) {
					$this->modules[ $item->comment_post_ID ] = array(
						'title' => get_the_title( $item->comment_post_ID ),
						'link' => get_permalink( $item->comment_post_ID ),
					);
				}
				$this->items[ $i ]->parent = $this->modules[ $item->comment_post_ID ];
			}
			/**
			 * add date & edit link
			 */
			$dateformatstring = get_option( 'date_format' );
			$timeformatstring = get_option( 'time_format' );
			foreach ( $this->items as $i => $item ) {
				$unixtimestamp = strtotime( $item->comment_date );
				$item->date = date_i18n( $dateformatstring, $unixtimestamp );
				$item->time = date_i18n( $timeformatstring, $unixtimestamp );
				$item->edit_comment_link = get_edit_comment_link( $item->comment_ID );
				$item->status_nonce = wp_create_nonce( 'coursepress_comment_status_'.$item->comment_ID );
				$item->in_response_to_link = coursepress_discussion_link( '', $item );
			}
		}
		$total_comments = get_comments( array_merge( $args, array(
			'count' => true,
			'offset' => 0,
			'number' => 0,
		) ) );

		$this->set_pagination_args( array(
			'total_items' => $total_comments,
			'per_page' => $comments_per_page,
		) );
	}

	public function get_per_page( $comment_status = 'all' ) {
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $this->get_option( 'per_page', 'default' );
			if ( ! $per_page ) {
				$per_page = 20;
			}
		}
		$per_page = $this->get_items_per_page( 'coursepress_comments_per_page', $per_page );
		return $per_page;
	}

	public function get_count() {
		return $this->count;
	}
}
