<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

// class CoursePress_Helper_Table_NotificationList extends CoursePress_Helper_UI_ListTable {
class CoursePress_Helper_Table_NotificationList extends WP_List_Table {

	private $count = array();
	private $post_type;
	private $_categories;

	/** Class constructor */
	public function __construct() {

		$post_format = CoursePress_Data_Notification::get_format();

		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,// should this table support ajax?
		) );

		$this->post_type = CoursePress_Data_Notification::get_post_type_name();
		$this->count = wp_count_posts( CoursePress_Data_Notification::get_post_type_name() );

	}


	/** No items */
	public function no_items() {
		echo __( 'No notifications found.', 'CP_TD' );
	}


	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'notification' => __( 'Notification', 'CP_TD' ),
			'course' => __( 'Course', 'CP_TD' ),
			'status' => __( 'Status', 'CP_TD' ),
			'actions' => __( 'Actions', 'CP_TD' ),
		);

		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	public function get_sortable_columns() {
		// return array( 'course' => array( 'course', false ) );
		return array();
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
		);
	}

	public function column_notification( $item ) {
		// create a nonce
		// $duplicate_nonce = wp_create_nonce( 'duplicate_course' );
		$title = '<strong>' . $item->post_title . '</strong>';
		$excerpt = CoursePress_Helper_Utility::truncateHtml( $item->post_content );

		$edit_page = CoursePress_View_Admin_Communication_Notification::$slug;

		$actions = array(
			'edit' => sprintf( '<a href="?page=%s&action=%s&id=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), __( 'Edit', 'CP_TD' ) ),
		);

		return $title . '<br />' . $excerpt . $this->row_actions( $actions );
	}

	function get_bulk_actions() {
		$actions = array(
			'publish' => __( 'Visible', 'CP_TD' ),
			'unpublish' => __( 'Private', 'CP_TD' ),
			'delete' => __( 'Delete', 'CP_TD' ),
		);
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

	public function column_actions( $item ) {
		$delete_nonce = wp_create_nonce( 'delete-notification' );
		return sprintf(
			'<a data-id="%s" data-nonce="%s" class="delete-notification-link"><i class="fa fa-times-circle remove-btn"></i></a>', $item->ID, $delete_nonce
		);
	}

	public function column_default( $item, $column_name ) {

		if ( isset( $item->{$column_name} ) ) {
			return $item->{$column_name};
		} else {
			return '';
		}

	}

	public function prepare_items() {

		$post_status = 'all';

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$perPage = 10;
		$currentPage = $this->get_pagenum();

		$offset = ( $currentPage - 1 ) * $perPage;

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $perPage,
			'offset' => $offset,
			's' => isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
		);

		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';

		if ( ! empty( $course_id ) && 'all' !== $course_id ) {

			$post_args['meta_query'] = array(
				array(
					'key' => 'course_id',
					'value' => (int) $course_id,
				),
			);

		}

		// @todo: Add permissions
		$query = new WP_Query( $post_args );

		$this->items = $query->posts;

		$totalItems = $query->found_posts;
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page' => $perPage,
		) );

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
			return; }

		$page = get_query_var( 'page', 'coursepress_notifications' );

		$s = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';

		echo '<form method="GET">';
		echo '<input type="hidden" name="page" value="' . $page . '" />';
		echo '<input type="hidden" name="s" value="' . $s . '" />';
		echo "<label for='course-category-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select course category', 'CP_TD' ) . '</label>';

		$options = array();
		$options['value'] = $course_id;
		$options['first_option'] = array(
			'text' => __( 'All courses', 'CP_TD' ),
			'value' => 'all',
		);

		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id' . $two, 'course_id' . $two, false, $options );

		submit_button( __( 'Filter', 'CP_TD' ), 'category-filter', '', false, array( 'id' => "filter-courses$two" ) );
		echo '</form>';
		echo "\n";
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

			if ( 'top' == $which ) {
				?>
				<form method="get">
					<input type="hidden" name="page" value="coursepress_notifications"/>
					<?php $this->search_box( __( 'Search Notifications', 'CP_TD' ), 'search_notifications' ); ?>
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
