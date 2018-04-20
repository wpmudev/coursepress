<?php
/**
 * A sub-class of WP_Posts_List_Table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
}

class CoursePress_Admin_Table_Notifications extends WP_Posts_List_Table {
	private $count = array();
	protected $post_type;
	private $_categories;
	private $recivers_allowed_options;
	protected $is_trash;
	protected $page = 'coursepress_notifications';

	public function __construct() {
		$post_format = CoursePress_Data_Notification::get_format();
		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,
		) );

		$this->post_type = CoursePress_Data_Notification::get_post_type_name();
		$this->count = wp_count_posts( $this->post_type );
	}

	public function prepare_items() {
		global $avail_post_stati, $wp_query, $per_page, $mode;
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
		$per_page = $this->get_items_per_page( 'coursepress_notifications_per_page', $per_page );
		/**
		 * Post statsu
		 */
		$post_status = isset( $_GET['post_status'] )? $_GET['post_status'] : 'any';
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		$s = isset( $_POST['s'] )? mb_strtolower( trim( $_POST['s'] ) ):false;

		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $per_page,
			'offset' => $offset,
			's' => $s,
		);

		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';

		if ( ! empty( $course_id ) && 'all' !== $course_id ) {
			$post_args['meta_query'] = array(
				array(
					'key' => 'course_id',
					'value' => (int) $course_id,
				),
			);
		} else {
			// Only show notifications where the current user have access with.
			$courses = CoursePress_Data_Notification::get_courses();
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

		// @todo: Add permissions
		$wp_query = new WP_Query( $post_args );
		$this->items = array();
		foreach ( $wp_query->posts as $post ) {
			$post->user_can_edit = CoursePress_Data_Capabilities::can_update_notification( $post->ID );
			$post->user_can_delete  = CoursePress_Data_Capabilities::can_delete_notification( $post->ID );
			$post->user_can_change_status = CoursePress_Data_Capabilities::can_change_status_notification( $post->ID );
			$post->user_can_change = $post->user_can_edit || $post->user_can_delete || $post->user_can_change_status;
			$this->items[] = $post;
		}
		$total_items = $wp_query->found_posts;

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash';

		$this->set_pagination_args(
			array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	protected function can_update( $item_id ) {
		return CoursePress_Data_Capabilities::can_update_notification( $item_id );
	}

	protected function can_delete( $item_id ) {
		return CoursePress_Data_Capabilities::can_delete_notification( $item_id );
	}

	protected function can_change_status( $item_id ) {
		return CoursePress_Data_Capabilities::can_change_status_notification( $item_id );
	}

	/** No items */
	public function no_items() {
		$post_type_object = get_post_type_object( $this->post_type );
		if ( $this->is_trash ) {
			echo $post_type_object->labels->not_found_in_trash;
		} else {
			echo $post_type_object->labels->not_found;
		}
	}

	public function column_cb( $item ) {
		if ( $item->user_can_edit ) {
			return sprintf(
				'<input type="checkbox" name="post[]" value="%s" />', $item->ID
			);
		}
		return '';
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'notification' => __( 'Notification', 'coursepress' ),
			'course' => __( 'Course', 'coursepress' ),
			'receivers' => __( 'Receivers', 'coursepress' ),
			'status' => __( 'Status', 'coursepress' ),
		);

		return $columns;
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( 'notification' !== $column_name ) {
			return '';
		}

		$row_actions = array();

		/**
		 * check current_user_can update?
		 */
		if ( $this->can_update( $item ) ) {
			if ( $this->is_trash ) {
				$url = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_untrash_notification' ),
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
		if ( $this->can_delete( $item ) ) {
			if ( $this->is_trash ) {
				$url = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_delete_notification' ),
						'id' => $item->ID,
						'action' => 'delete',
					)
				);
				$row_actions['delete'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Delete Permanently', 'coursepress' ) );
			} else {
				$url = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_trash_notification' ),
						'id' => $item->ID,
						'action' => 'trash',
					)
				);
				$row_actions['trash'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), __( 'Trash', 'coursepress' ) );
			}
		}
		return $this->row_actions( $row_actions );
	}

	public function column_notification( $item ) {
		$title = '<strong>' . apply_filters( 'the_title', $item->post_title ) . '</strong>';
		return $title;
	}

	/**
	 * Coulmn Notifications Receivers
	 *
	 * @since 2.0.0
	 */
	public function column_receivers( $item ) {
		$receivers = get_post_meta( $item->ID, 'receivers', true );
		if ( empty( $receivers ) ) {
			$receivers = 'all';
		}
		$attributes = CoursePress_Data_Notification::attributes( $item->ID );
		$course_id = $attributes['course_id'];
		if ( 'all' == $course_id ) {
			return sprintf(
				'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				__( 'Option not available for all courses.', 'coursepress' )
			);
		}
		$recivers_allowed_options = array();
		if ( isset( $this->recivers_allowed_options[ $course_id ] ) ) {
			$recivers_allowed_options = $this->recivers_allowed_options[ $course_id ];
		} else {
			$recivers_allowed_options = CoursePress_Admin_Notifications::get_allowed_options( $course_id );
			$this->recivers_allowed_options[ $course_id ] = $recivers_allowed_options;
		}
		if ( isset( $recivers_allowed_options[ $receivers ] ) ) {
			return $recivers_allowed_options[ $receivers ]['label'];
		}
		return __( 'Wrong receivers!', 'coursepress' );
	}

	protected function get_bulk_actions() {
		$actions = array(
			'publish' => __( 'Publish', 'coursepress' ),
			'draft' => __( 'Change status to Draft', 'coursepress' ),
			'trash' => __( 'Move to Trash', 'coursepress' ),
		);
		if ( $this->is_trash ) {
			$actions = array(
				'untrash' => __( 'Restore', 'coursepress' ),
				'delete' => __( 'Delete Permanently', 'coursepress' ),
			);
		}
		return $actions;
	}

	public function column_course( $item ) {
		$attributes = CoursePress_Data_Notification::attributes( $item->ID );
		$output = sprintf( '<div data-course="%s">%s</div>',
			$attributes['course_id'],
			$attributes['course_title']
		);

		return $output;
	}

	public function column_status( $item ) {
		/**
		 * check permissions
		 */
		if ( ! $item->user_can_change_status ) {
			return ucfirst( $item->post_status );
		}
		// Publish Course Toggle
		$d_id = $item->ID;
		$status = get_post_status( $d_id );
		$ui = array(
			'label' => '',
			'left' => '<i class="fa fa-key"></i>',
			'left_class' => '',
			'right' => '<i class="fa fa-globe"></i>',
			'right_class' => '',
			'state' => 'publish' === $status ? 'on' : 'off',
			'data' => array(
				'nonce' => wp_create_nonce( 'publish-notification' ),
			),
		);
		$ui['class'] = 'notification-' . $d_id;
		$publish_toggle = ! empty( $d_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-notification-toggle-' . $d_id, 'publish-notification-toggle-' . $d_id, $ui ) : '';

		return $publish_toggle;
	}

	public static function get_course_id( $course ) {
		return is_object( $course ) ? $course->ID : null;
	}

	protected function course_filter( $which = '' ) {
		if ( 'top' !== $which ) {
			return;
		}

		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';

		$options = array();
		$options['value'] = $course_id;
		$options['class'] = 'medium dropdown';
		$options['first_option'] = array(
			'text' => __( 'All courses', 'coursepress' ),
			'value' => 'all',
		);

		$courses = CoursePress_Data_Notification::get_courses();
		if ( current_user_can( 'manage_options' ) ) {
			$courses = false;
		} elseif ( CoursePress_Data_Capabilities::can_add_notification_to_all() ) {
			$courses = false;
		}

		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'course_id', $courses, $options );
	}

	protected function pagination( $which ) {
		// Show pagination only at the bottom
		if ( 'top' !== $which ) {
			parent::pagination( $which );
		}
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
		$this->search_box( __( 'Search Notifications', 'coursepress' ), 'search_notifications' );
	}

	/**
	 *
	 * @global array $locked_post_status This seems to be deprecated.
	 * @global array $avail_post_stati
	 * @return array
	 */
	protected function get_views() {
		global $locked_post_status, $avail_post_stati;

		$post_type = $this->post_type;

		if ( ! empty( $locked_post_status ) ) {
			return array(); }

		$status_links = array();
		$num_posts = wp_count_posts( $post_type, 'readable' );
		$total_posts = array_sum( (array) $num_posts );
		$class = '';

		$current_user_id = get_current_user_id();
		$all_args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'page' => $this->page,
		);
		$mine = '';

		// Subtract post types that are not included in the admin all list.
		foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
			$total_posts -= $num_posts->$state;
		}

		if ( $this->user_posts_count && $this->user_posts_count !== $total_posts ) {
			if ( isset( $_GET['author'] ) && ( $_GET['author'] == $current_user_id ) ) {
				$class = 'current';
			}

			$mine_args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'page' => $this->page,
				'author' => $current_user_id,
			);

			$mine_inner_html = sprintf(
				_nx(
					'Mine <span class="count">(%s)</span>',
					'Mine <span class="count">(%s)</span>',
					$this->user_posts_count,
					'posts'
				),
				number_format_i18n( $this->user_posts_count )
			);

			$mine = $this->get_edit_link( $mine_args, $mine_inner_html, $class );

			$all_args['all_posts'] = 1;
			$class = '';
		}

		if ( empty( $class ) && ( $this->is_base_request() || isset( $_REQUEST['all_posts'] ) ) ) {
			$class = 'current';
		}

		$all_inner_html = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_posts,
				'posts'
			),
			number_format_i18n( $total_posts )
		);

		$status_links['all'] = $this->get_edit_link( $all_args, $all_inner_html, $class );
		if ( $mine ) {
			$status_links['mine'] = $mine;
		}

		foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
			$class = '';

			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati ) || empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset( $_REQUEST['post_status'] ) && $status_name === $_REQUEST['post_status'] ) {
				$class = 'current';
			}

			$status_args = array(
				'post_status' => $status_name,
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'page' => $this->page,
			);

			$status_label = sprintf(
				translate_nooped_plural( $status->label_count, $num_posts->$status_name ),
				number_format_i18n( $num_posts->$status_name )
			);

			$status_links[ $status_name ] = $this->get_edit_link( $status_args, $status_label, $class );
		}

		if ( ! empty( $this->sticky_posts_count ) ) {
			$class = ! empty( $_REQUEST['show_sticky'] ) ? 'current' : '';

			$sticky_args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'page' => $this->page,
				'show_sticky' => 1,
			);

			$sticky_inner_html = sprintf(
				_nx(
					'Sticky <span class="count">(%s)</span>',
					'Sticky <span class="count">(%s)</span>',
					$this->sticky_posts_count,
					'posts'
				),
				number_format_i18n( $this->sticky_posts_count )
			);

			$sticky_link = array(
				'sticky' => $this->get_edit_link( $sticky_args, $sticky_inner_html, $class ),
			);

			// Sticky comes after Publish, or if not listed, after All.
			$split = 1 + array_search( ( isset( $status_links['publish'] ) ? 'publish' : 'all' ), array_keys( $status_links ) );
			$status_links = array_merge( array_slice( $status_links, 0, $split ), $sticky_link, array_slice( $status_links, $split ) );
		}

		return $status_links;
	}
}
