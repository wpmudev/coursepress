<?php
/**
 * A sub-class of WP_Posts_List_Table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Table_Forums extends CoursePress_Admin_Table_Notifications {
	private $count = array();
	private $_categories;
	protected $page = 'coursepress_discussions';

	public function __construct() {
		$post_format = CoursePress_Data_Discussion::get_format();
		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,
		) );

		$this->post_type = CoursePress_Data_Discussion::get_post_type_name();
		$this->count = wp_count_posts( $this->post_type );
	}

	public function prepare_items() {
		global $avail_post_stati, $wp_query, $per_page, $mode;

		//is going to call wp()
		$avail_post_stati = wp_edit_posts_query();
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
		$per_page = $this->get_items_per_page( 'coursepress_discussions_per_page', $per_page );
		/**
		 * Post statsu
		 */
		$post_status = isset( $_GET['post_status'] )? $_GET['post_status'] : 'any';

		/**
		 * Pagination
		 */
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		$s = isset( $_POST['s'] )? mb_strtolower( trim( $_POST['s'] ) ):false;

		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $per_page,
			'paged' => $current_page,
			's' => $s,
		);

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

		// @todo: Validate per course
		/*
		if ( ! empty( $course_id ) && 'all' !== $course_id ) {
			$post_args['meta_query'] = array(
				array(
					'key' => 'course_id',
					'value' => (int) $course_id,
				),
			);
		} else {
			// Only show notifications where the current user have access with.
			$courses = array();
			$courses_ids = array_map( array( __CLASS__, 'get_course_id' ), $courses );
			// Include notification for all courses
			$courses_ids[] = 'all';
			$post_args['meta_query'] = array(
				array(
					'key' => 'course_id',
					'value' => (array) $courses_ids,
					'compare' => 'IN',
				),
			);
		}
		 */

		// @todo: Add permissions
		$wp_query = new WP_Query( $post_args );
		$this->items = array();
		foreach ( $wp_query->posts as $post ) {
			$post->user_can_edit = CoursePress_Data_Capabilities::can_update_discussion( $post->ID );
			$post->user_can_delete  = CoursePress_Data_Capabilities::can_delete_discussion( $post->ID );
			$post->user_can_change_status = CoursePress_Data_Capabilities::can_change_status_discussion( $post->ID );
			$post->user_can_change = $post->user_can_edit || $post->user_can_delete || $post->user_can_change_status;
			$this->items[] = $post;
		}
		$total_items = $wp_query->found_posts;

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'	=> $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Topic', 'coursepress' ),
			'course' => __( 'Course', 'coursepress' ),
			'comments' => '<span class="vers comment-grey-bubble" title="' . esc_attr__( 'Comments', 'coursepress' ) . '"><span class="screen-reader-text">' . __( 'Comments', 'coursepress' ) . '</span></span>',
			'status' => __( 'Status', 'coursepress' ),
		);
		return $columns;
	}

	/**
	 * Row actions
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( 'title' !== $column_name ) {
			return '';
		}
		$row_actions = array();
		if ( $item->user_can_edit ) {
			if ( $this->is_trash ) {
				$url = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_untrash_discussion' ),
						'action' => 'untrash',
						'id' => $item->ID,
					)
				);
				$row_actions['untrash'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Restore', 'coursepress' ) );
			} else {
				$url = add_query_arg(
					array(
						'action' => 'edit',
						'id' => $item->ID,
					)
				);
				$row_actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Edit', 'coursepress' ) );
			}
		}
		if ( $item->user_can_delete ) {
			if ( $this->is_trash ) {
				$url = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_delete_discussion' ),
						'id' => $item->ID,
						'action' => 'delete',
					)
				);
				$row_actions['delete'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Delete Permanently', 'coursepress' ) );
			} else {
				$url = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_trash_discussion' ),
						'id' => $item->ID,
						'action' => 'trash',
					)
				);
				$row_actions['trash'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Trash', 'coursepress' ) );
			}
		}
		if ( 'publish' == $item->post_status ) {
			$url = CoursePress_Data_Discussion::get_url( $item );
			if ( ! empty( $url ) ) {
				$row_actions['view'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'View', 'coursepress' ) );
			}
		}
		return $this->row_actions( $row_actions );
	}

	public function column_title( $item ) {
		$title = $item->post_title;

		return $title;
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

?>
		<div class="alignleft actions category-filter">
			<?php $this->course_filter( $which ); ?>
			<input type="submit" class="button" name="action" value="<?php esc_attr_e( 'Filter', 'coursepress' ); ?>" />
		</div>
<?php
		$this->search_box( __( 'Search Forums', 'coursepress' ), 'search_discussions' );
	}

	/**
	 * Column Status
	 *
	 * @since 2.0.0
	 */
	public function column_status( $item ) {
		/**
		 * check permissions
		 */
		if ( ! $item->user_can_change_status ) {
			return ucfirst( $item->post_status );
		}
		// Publish Course Toggle
		$item->ID = $item->ID;
		$status = get_post_status( $item->ID );
		$ui = array(
			'label' => '',
			'left' => '<i class="fa fa-key"></i>',
			'left_class' => '',
			'right' => '<i class="fa fa-globe"></i>',
			'right_class' => '',
			'state' => 'publish' === $status ? 'on' : 'off',
			'data' => array(
				'nonce' => wp_create_nonce( 'publish-discussion-' . $item->ID ),
			),
		);
		$ui['class'] = 'discussion-' . $item->ID;
		$publish_toggle = ! empty( $item->ID ) ? CoursePress_Helper_UI::toggle_switch( 'publish-discussion-toggle-' . $item->ID, 'publish-discussion-toggle-' . $item->ID, $ui ) : '';
		return $publish_toggle;
	}
}
