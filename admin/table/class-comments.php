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

	public function __construct() {
		parent::__construct();
		if ( ! empty( $_REQUEST['course_id'] ) ) {
			$course_id = (int) $_REQUEST['course_id'];
			if ( CoursePress_Data_Course::is_course( $course_id ) ) {
				$this->course_id = $course_id;
			}
		}
	}

	public function prepare_items() {
		global $post_id, $comment_status, $search, $comment_type;

		$course_id = ( isset( $_REQUEST['course_id'] ) ) ? $_REQUEST['course_id'] : null;
		$discussions = CoursePress_Data_Module::get_all_modules_ids_by_type( 'discussion', $course_id );
		if ( empty( $discussions ) ) {
			return;
		}

		$comment_status = isset( $_REQUEST['comment_status'] ) ? $_REQUEST['comment_status'] : 'all';
		if ( ! in_array( $comment_status, array( 'all', 'moderated', 'approved', 'spam', 'trash' ) ) ) {
			$comment_status = 'all'; }

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

		$this->set_pagination_args( array(
			'total_items' => $total_comments,
			'per_page' => $comments_per_page,
		) );
	}

		/**
		 * Generate and display row actions links.
		 *
		 * @since 4.3.0
		 * @access protected
		 *
		 * @global string $comment_status Status for the current listed comments.
		 *
		 * @param WP_Comment $comment     The comment object.
		 * @param string     $column_name Current column name.
		 * @param string     $primary     Primary column name.
		 * @return string|void Comment row actions output.
		 */
	protected function handle_row_actions( $comment, $column_name, $primary ) {
		global $comment_status;

		if ( $primary !== $column_name ) {
			return '';
		}

		$the_comment_status = wp_get_comment_status( $comment );

		$out = '';

		$del_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "delete-comment_$comment->comment_ID" ) );
		$approve_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "approve-comment_$comment->comment_ID" ) );

		$url = "edit.php?post_type=course&page=coursepress_comments&c=$comment->comment_ID";

		$approve_url = esc_url( $url . "&action=approvecomment&$approve_nonce" );
		$unapprove_url = esc_url( $url . "&action=unapprovecomment&$approve_nonce" );

		// Preorder it: Approve | Reply | Quick Edit | Edit | Spam | Trash.
		$actions = array(
		'approve' => '',
		'unapprove' => '',
		'edit' => '',
		);

		$actions['edit'] = "<a href='".$url."&action=editcomment&amp;c={$comment->comment_ID}' aria-label='" . esc_attr__( 'Edit this comment' ) . "'>". __( 'Edit' ) . '</a>';

		// Not looking at all comments.
		if ( $comment_status && 'all' != $comment_status ) {
			if ( 'approved' === $the_comment_status ) {
				$actions['unapprove'] = "<a href='$unapprove_url' data-wp-lists='delete:the-comment-list:comment-$comment->comment_ID:e7e7d3:action=dim-comment&amp;new=unapproved' class='vim-u vim-destructive' aria-label='" . esc_attr__( 'Unapprove this comment' ) . "'>" . __( 'Unapprove' ) . '</a>';
			} elseif ( 'unapproved' === $the_comment_status ) {
				$actions['approve'] = "<a href='$approve_url' data-wp-lists='delete:the-comment-list:comment-$comment->comment_ID:e7e7d3:action=dim-comment&amp;new=approved' class='vim-a vim-destructive' aria-label='" . esc_attr__( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
			}
		} else {
			$actions['approve'] = "<a href='$approve_url' data-wp-lists='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=approved' class='vim-a' aria-label='" . esc_attr__( 'Approve this comment' ) . "'>" . __( 'Approve' ) . '</a>';
			$actions['unapprove'] = "<a href='$unapprove_url' data-wp-lists='dim:the-comment-list:comment-$comment->comment_ID:unapproved:e7e7d3:e7e7d3:new=unapproved' class='vim-u' aria-label='" . esc_attr__( 'Unapprove this comment' ) . "'>" . __( 'Unapprove' ) . '</a>';
		}

		/** This filter is documented in wp-admin/includes/dashboard.php */
		$actions = apply_filters( 'comment_row_actions', array_filter( $actions ), $comment );

		$i = 0;
		$out .= '<div class="row-actions">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( ( ( 'approve' === $action || 'unapprove' === $action ) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';

			// Reply and quickedit need a hide-if-no-js span when not added with ajax
			if ( ( 'reply' === $action || 'quickedit' === $action ) && ! defined( 'DOING_AJAX' ) ) {
				$action .= ' hide-if-no-js'; } elseif ( ( $action === 'untrash' && $the_comment_status === 'trash' ) || ( $action === 'unspam' && $the_comment_status === 'spam' ) ) {
				if ( '1' == get_comment_meta( $comment->comment_ID, '_wp_trash_meta_status', true ) ) {
					$action .= ' approve'; } else { 					$action .= ' unapprove'; }
				}

				$out .= "<span class='$action'>$sep$link</span>";
		}
		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

		return $out;
	}
	/**
	 *
	 * @global string $comment_status
	 * @global string $comment_type
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		global $comment_status, $comment_type;
?>
		<div class="alignleft actions">
<?php
if ( 'top' === $which ) {
	$options = array();
	$options['value'] = $this->course_id;
	$options['class'] = 'medium dropdown';
	$options['first_option'] = array(
	'text' => __( 'All courses', 'coursepress' ),
	'value' => 'all',
	);

		$assigned_courses = array();
		$user_id = get_current_user_id();
	if ( CoursePress_Data_Capabilities::is_facilitator() ) {
		$assigned_courses = CoursePress_Data_Facilitator::get_facilitated_courses( $user_id, array( 'any' ), 0, -1 );
	} else if ( CoursePress_Data_Capabilities::is_instructor() ) {
		$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
	}

	$assigned_courses = array_filter( $assigned_courses );
	$assigned_courses = array_map( 'get_post', $assigned_courses );

	$courses = CoursePress_Helper_UI::get_course_dropdown( 'filter-by-course', 'course_id', $assigned_courses, $options );
?>
	<label class="screen-reader-text" for="filter-by-course"><?php _e( 'Filter by course' ); ?></label>
	<?php echo $courses; ?>
		<?php
		/**
			 * Fires just before the Filter submit button for comment types.
			 *
			 * @since 3.5.0
			 */
		do_action( 'restrict_manage_comments' );
		submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
}

		/**
		 * Fires after the Filter submit button for comment types.
		 *
		 * @since 2.5.0
		 *
		 * @param string $comment_status The comment status name. Default 'All'.
		 */
		do_action( 'manage_comments_nav', $comment_status );
		echo '</div>';
	}

	/**
	 * @access public
	 *
	 * @param WP_Comment $comment The comment object.
	 */
	public function column_response( $comment ) {
		$post = get_post();

		if ( ! $post ) {
			return;
		}

		if ( isset( $this->pending_count[ $post->ID ] ) ) {
			$pending_comments = $this->pending_count[ $post->ID ];
		} else {
			$_pending_count_temp = get_pending_comments_num( array( $post->ID ) );
			$pending_comments = $this->pending_count[ $post->ID ] = $_pending_count_temp[ $post->ID ];
		}

		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$post_link = "<a href='" . get_edit_post_link( $post->ID ) . "' class='comments-edit-item-link'>";
			$post_link .= esc_html( get_the_title( $post->ID ) ) . '</a>';
		} else {
			$post_link = esc_html( get_the_title( $post->ID ) );
		}

		echo '<div class="response-links">';
		if ( 'attachment' === $post->post_type && ( $thumb = wp_get_attachment_image( $post->ID, array( 80, 60 ), true ) ) ) {
			echo $thumb;
		}
		echo $post_link;
		echo '<br />';
		echo '<span class="post-com-count-wrapper post-com-count-', $post->ID, '">';
		$this->comments_bubble( $post->ID, $pending_comments );
		echo '</span> ';
		echo '</div>';
	}

	/**
	 * Display a comment count bubble
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param int $post_id          The post ID.
	 * @param int $pending_comments Number of pending comments.
	 */
	protected function comments_bubble( $post_id, $pending_comments ) {
		$approved_comments = get_comments_number();

		$approved_comments_number = number_format_i18n( $approved_comments );
		$pending_comments_number = number_format_i18n( $pending_comments );

		$approved_only_phrase = sprintf( _n( '%s comment', '%s comments', $approved_comments ), $approved_comments_number );
		$approved_phrase = sprintf( _n( '%s approved comment', '%s approved comments', $approved_comments ), $approved_comments_number );
		$pending_phrase = sprintf( _n( '%s pending comment', '%s pending comments', $pending_comments ), $pending_comments_number );

		// No comments at all.
		if ( ! $approved_comments && ! $pending_comments ) {
			printf( '<span aria-hidden="true">—</span><span class="screen-reader-text">%s</span>',
				__( 'No comments' )
			);
			// Approved comments have different display depending on some conditions.
		} elseif ( $approved_comments ) {
			printf( '<span href="%s" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
				esc_url( add_query_arg( array( 'p' => $post_id, 'comment_status' => 'approved' ), admin_url( 'edit-comments.php' ) ) ),
				$approved_comments_number,
				$pending_comments ? $approved_phrase : $approved_only_phrase
			);
		} else {
			printf( '<span class="post-com-count post-com-count-no-comments"><span class="comment-count comment-count-no-comments" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
				$approved_comments_number,
				$pending_comments ? __( 'No approved comments' ) : __( 'No comments' )
			);
		}

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

	protected function get_bulk_actions() {
		return array();
	}
}
