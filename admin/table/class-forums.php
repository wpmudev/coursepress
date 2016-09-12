<?php
/**
 * A sub-class of WP_Posts_List_Table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Table_Forums extends CoursePress_Admin_Table_Notifications {
	private $count = array();
	private $post_type;
	private $_categories;

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
		global $wp_query;

		$post_status = 'any';
		$per_page = $this->get_items_per_page( 'coursepress_discussions_per_page', 20 );
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
		$this->items = $wp_query->posts;
		$total_items = $wp_query->found_posts;

		$this->set_pagination_args(
			array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/** No items */
	public function no_items() {
		echo __( 'No topics found.', 'cp' );
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
		);
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Topic', 'cp' ),
			'course' => __( 'Course', 'cp' ),
			'status' => __( 'Status', 'cp' ),
		);

		return $columns;
	}

	public function column_title( $item ) {
		$title = $item->post_title;

		$row_actions = array();

		// @todo: Add validation
		$edit_url = add_query_arg(
			array(
				'action' => 'edit',
				'id' => $item->ID,
			)
		);
		$row_actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'cp' ) );

		// @todo: Validate delete cap
		$delete_url = add_query_arg(
			array(
				'_wpnonce' => wp_create_nonce( 'coursepress_delete_discussion' ),
				'id' => $item->ID,
				'action' => 'delete'
			)
		);
		$row_actions['delete'] = sprintf( '<a href="%s">%s</a>', esc_url( $delete_url ), __( 'Delete', 'cp' ) );

		return $title . $this->row_actions( $row_actions );
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		?>
		<div class="alignleft actions category-filter">
			<?php $this->course_filter( $which ); ?>
			<input type="submit" class="button" name="action" value="<?php esc_attr_e( 'Filter', 'cp' ); ?>" />
		</div>
		<?php
		$this->search_box( __( 'Search Forums', 'cp' ), 'search_discussions' );
	}
}