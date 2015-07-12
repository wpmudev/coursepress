<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_CourseList extends WP_List_Table {

	private $count = array();
	private $post_type;

	/** Class constructor */
	public function __construct() {

		$post_format = CoursePress_Model_Course::get_format();

		parent::__construct( [
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural'   => $post_format['post_args']['labels']['name'],
			'ajax'     => false //should this table support ajax?
		] );

		$this->post_type = CoursePress_Model_PostFormats::prefix() . $post_format['post_type'];
		$this->count     = wp_count_posts( $this->post_type );

	}


	/** No items */
	public function no_items() {
		_e( 'No courses found.', CoursePress::TD );
	}


	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox" />',
			'ID'         => __( 'ID', CoursePress::TD ),
			'post_title' => __( 'Title', CoursePress::TD ),
			'units'      => __( 'Units', CoursePress::TD ),
			'students'   => __( 'Students', CoursePress::TD ),
			'status'     => __( 'Status', CoursePress::TD ),
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

	// column_{key}
	public function column_post_title( $item ) {
		// create a nonce
		$duplicate_nonce = wp_create_nonce( 'duplicate_course' );

		$title = '<strong>' . $item->post_title . '</strong>';

		$actions = [
			'edit' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item->id ), $duplicate_nonce, __( 'Edit', CoursePress::TD ) ),
			'units' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'units', absint( $item->id ), $duplicate_nonce, __( 'Units', CoursePress::TD ) ),
			'students' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'students', absint( $item->id ), $duplicate_nonce, __( 'Students', CoursePress::TD ) ),
			'view_course' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'view_course', absint( $item->id ), $duplicate_nonce, __( 'View Course', CoursePress::TD ) ),
			'view_units' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'view_units', absint( $item->id ), $duplicate_nonce, __( 'View Units', CoursePress::TD ) ),
			'duplicate' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'duplicate_course', absint( $item->id ), $duplicate_nonce, __( 'Duplicate Course', CoursePress::TD ) ),
		];

		return $title . $this->row_actions( $actions );
	}

	public function column_units( $item ) {

		$post_args = array(
			'post_type'   => 'unit',
			'post_parent' => $item->ID,
			'post_status' => array( 'publish', 'private' )
		);

		$query     = new WP_Query( $post_args );
		$published = 0;
		foreach ( $query->posts as $post ) {
			if ( 'publish' === $post->post_status ) {
				$published += 1;
			}
		}
		$output = sprintf( '<div><p>%d %s<br />%d %s</p>',
			$query->found_posts,
			__( 'Units', CoursePress::TD ),
			$published,
			__( 'Published', CoursePress::TD )
		);

		return $output;
	}

	public function column_students( $item ) {
		return 2;
	}

	public function column_status( $item ) {
		return '<strong>Meh</strong>';
	}

	public function column_actions( $item ) {
		return '<em>Yawn</em>';
	}

	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'ID':
			//case 'post_title':
				return $item->{$column_name};

		}

	}

	public function prepare_items() {

		$accepted_tabs = array( 'publish', 'private', 'all' );
		$tab           = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $accepted_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'publish';

		$post_status = 'all' == $tab ? array( 'publish', 'private' ) : $tab;

		// Debug
		$post_status = 'all';

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$perPage     = 10;
		$currentPage = $this->get_pagenum();

		// Debug
		$perPage = 10;

		$offset = ( $currentPage - 1 ) * $perPage;

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$post_args             = array(
			'post_type'      => $this->post_type,
			'post_status'    => $post_status,
			'posts_per_page' => $perPage,
			'offset'         => $offset,
			's'              => isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : ''
		);

		$query = new WP_Query( $post_args );

		//foreach( $query->posts as $post ) {
		//	$post->post_parent = 4;
		//	wp_update_post( $post );
		//}

		error_log( print_r( $query, true ) );
		$this->items = $query->posts;

		$totalItems = $query->found_posts;
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

	}


	protected function display_tablenav( $which ) {
		if ( 'top' == $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
			<?php
			$this->extra_tablenav( $which );

			$accepted_tabs = array( 'publish', 'private', 'all' );
			$tab           = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $accepted_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'publish';

			if ( 'top' == $which ) {
				?>
				<form method="get">
				    <input type="hidden" name="page" value="coursepress"/>
					<input type="hidden" name="tab" value="<?php esc_attr( $tab ) ?>"/>
					<?php $this->search_box( __( 'Search Courses', CoursePress::TD ), 'search_id' ); ?>
				</form>
			<?php
			} else {
				$this->pagination( $which );
			}
			?>

			<br class="clear"/>
	</div>
	<?php
	}

}