<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CoursePress_Helper_Table_CourseList extends WP_List_Table {

	private $count = array();
	private $post_type;
	private $_categories;

	/** Class constructor */
	public function __construct() {
		$post_format = CoursePress_Data_Course::get_format();

		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,// should this table support ajax?
		) );

		$this->post_type = CoursePress_Data_Course::get_post_type_name();
		$this->count = wp_count_posts( $this->post_type );
	}

	/** No items */
	public function no_items() {
		_e( 'No courses found.', 'CP_TD' );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'ID' => __( 'ID', 'CP_TD' ),
			'post_title' => __( 'Title', 'CP_TD' ),
			'units' => __( 'Units', 'CP_TD' ),
			'students' => __( 'Students', 'CP_TD' ),
			'status' => __( 'Status', 'CP_TD' ),
			'actions' => __( 'Actions', 'CP_TD' ),
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

		$edit_page = CoursePress_View_Admin_Course_Edit::$slug;

		$actions = array(
			'edit' => sprintf( '<a href="?page=%s&action=%s&id=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), __( 'Edit', 'CP_TD' ) ),
			'units' => sprintf( '<a href="?page=%s&action=%s&id=%s&tab=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), 'units', __( 'Units', 'CP_TD' ) ),
			'students' => sprintf( '<a href="?page=%s&action=%s&id=%s&tab=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), 'students',  __( 'Students', 'CP_TD' ) ),
			'view_course' => sprintf( '<a href="%s">%s</a>', get_permalink( $item->ID ), __( 'View Course', 'CP_TD' ) ),
			// 'view_units' => sprintf( '<a href="?page=%s&action=%s&id=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'view_units', absint( $item->ID ), __( 'View Units', 'CP_TD' ) ),
			'duplicate' => sprintf( '<a data-nonce="%s" data-id="%s" class="duplicate-course-link">%s</a>', $duplicate_nonce, $item->ID, __( 'Duplicate Course', 'CP_TD' ) ),
		);

		/**
		 * check instructor privileges
		 */
		if ( ! CoursePress_Data_Capabilities::can_update_course( $item ) ) {
			unset( $actions['edit'] );
			unset( $actions['units'] );
			unset( $actions['students'] );
		}

		return $title . $this->row_actions( $actions );
	}

	function get_bulk_actions() {
		$actions = array(
			'publish' => __( 'Publish', 'CP_TD' ),
			'unpublish' => __( 'Unpublish', 'CP_TD' ),
			'delete' => __( 'Delete', 'CP_TD' ),
		);
		return $actions;
	}

	public function column_units( $item ) {

		$post_args = array(
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_parent' => $item->ID,
			'post_status' => array( 'publish', 'private', 'draft' ),
		);

		$query = new WP_Query( $post_args );
		$published = 0;
		foreach ( $query->posts as $post ) {
			if ( 'publish' === $post->post_status ) {
				$published += 1;
			}
		}
		$output = sprintf( '<div><p>%d %s<br />%d %s</p>',
			$query->found_posts,
			__( 'Units', 'CP_TD' ),
			$published,
			__( 'Published', 'CP_TD' )
		);

		return $output;
	}

	public function column_students( $item ) {
		return CoursePress_Data_Course::count_students( $item->ID );
	}

	public function column_status( $item ) {

		/**
		 * check instructor privileges
		 */
		if ( ! CoursePress_Data_Capabilities::can_update_course( $item ) ) {
			return '&nbsp;';
		}

		// Publish Course Toggle
		$course_id = $item->ID;
		$status = get_post_status( $course_id );
		$ui = array(
			'label' => '',
			'left' => '<i class="fa fa-ban"></i>',
			'left_class' => 'red',
			'right' => '<i class="fa fa-check"></i>',
			'right_class' => 'green',
			'state' => 'publish' === $status ? 'on' : 'off',
			'data' => array(
				'nonce' => wp_create_nonce( 'publish-course' ),
			),
		);
		$ui['class'] = 'course-' . $course_id;
		$publish_toggle = ! empty( $course_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-course-toggle-' . $course_id, 'publish-course-toggle-' . $course_id, $ui ) : '';

		return $publish_toggle;
	}

	public function column_actions( $item ) {
		/**
		 * check instructor privileges
		 */
		if ( ! CoursePress_Data_Capabilities::can_delete_course( $item ) ) {
			return '&nbsp;';
		}
		$delete_nonce = wp_create_nonce( 'delete_course' );
		return sprintf(
			'<a data-id="%s" data-nonce="%s" class="delete-course-link"><i class="fa fa-times-circle remove-btn"></i></a>', $item->ID, $delete_nonce
		);
	}

	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'ID':
				// case 'post_title':
				return $item->{$column_name};

		}

	}

	public function prepare_items() {

		$accepted_tabs = array( 'publish', 'private', 'all' );
		$tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $accepted_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'publish';
		$valid_categories = CoursePress_Data_Course::get_course_categories();
		$valid_categories = array_keys( $valid_categories );
		$category = isset( $_GET['category'] ) && in_array( $_GET['category'], $valid_categories ) ? sanitize_text_field( $_GET['category'] ) : false;

		$post_status = 'all' == $tab ? array( 'publish', 'private' ) : $tab;

		// Debug
		$post_status = 'all';

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$per_page = 10;
		$current_page = $this->get_pagenum();

		// Debug
		$per_page = 10;

		$offset = ( $current_page - 1 ) * $per_page;

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $per_page,
			'offset' => $offset,
			's' => isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
		);

		// @todo: Add permissions
		// Add category filter
		if ( $category ) {
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => 'course_category',
					'field' => 'term_id',
					'terms' => array( $category ),
				),
			);
		}

		$query = new WP_Query( $post_args );

		$this->items = $query->posts;

		$total_items = $query->found_posts;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
			)
		);
	}


	protected function course_filter( $which = '' ) {
		if ( 'top' !== $which ) {
			return;
		}

		if ( is_null( $this->_categories ) ) {
			$this->_categories = CoursePress_Data_Course::get_course_categories();

			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_categories ) ) {
			return;
		}

		$page = get_query_var( 'page', 'coursepress' );
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
		$s = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$selected = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';

		echo '<form method="GET">';
		echo '<input type="hidden" name="page" value="' . $page . '" />';
		echo '<input type="hidden" name="tab" value="' . $tab . '" />';
		echo '<input type="hidden" name="s" value="' . $s . '" />';
		echo "<label for='course-category-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select course category', 'CP_TD' ) . '</label>';
		echo "<select name='category$two' id='course-category-selector-" . esc_attr( $which ) . "'>\n";
		echo "<option value='-1' " . selected( $selected, -1, false ) . '>' . __( 'All Course Categories' ) . "</option>\n";

		foreach ( $this->_categories as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class " . selected( $selected, $name, false ) . ">$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Filter', 'CP_TD' ), 'category-filter', '', false, array( 'id' => "filter-courses$two" ) );
		echo '</form>';
		echo "\n";
	}

	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return; }

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />'; }
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />'; }
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />'; }
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />'; }

		$category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
		echo '<input type="hidden" name="category" value="' . $category . '" />';

		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
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
		<div class="alignleft actions category-filter">
			<?php $this->course_filter( $which ); ?>
		</div>
			<?php
			$this->extra_tablenav( $which );

			$accepted_tabs = array( 'publish', 'private', 'all' );
			$tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $accepted_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'publish';

			if ( 'top' == $which ) {
				?>
				<form method="get">
					<input type="hidden" name="page" value="coursepress"/>
					<input type="hidden" name="tab" value="<?php esc_attr( $tab ) ?>"/>
					<?php $this->search_box( __( 'Search Courses', 'CP_TD' ), 'search_id' ); ?>
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
