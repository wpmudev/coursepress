<?php

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary. In this tutorial, we are
 * going to use the WP_List_Table class directly from WordPress core.
 *
 * IMPORTANT:
 * Please note that the WP_List_Table class technically isn't an official API,
 * and it could change at some point in the distant future. Should that happen,
 * I will update this plugin with the most current techniques for your reference
 * immediately.
 *
 * If you are really worried about future compatibility, you can make a copy of
 * the WP_List_Table class (file path is shown just below) to use and distribute
 * with your plugins. If you do that, just remember to change the name of the
 * class to avoid conflicts with core.
 *
 * Since I will be keeping this tutorial up-to-date for the foreseeable future,
 * I am going to work with the copy of the class provided in WordPress core.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 *
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 *
 * Our theme for this list table is going to be movies.
 */
class CoursePress_Helper_Table_DiscussionList extends WP_List_Table {

	private $count = array();
	private $post_type;
	private $_categories;

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	public function __construct() {

		$post_format = CoursePress_Data_Discussion::get_format();

		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,// should this table support ajax?
		) );

		$this->post_type = CoursePress_Data_Discussion::get_post_type_name();
		$this->count = wp_count_posts( CoursePress_Data_Discussion::get_post_type_name() );

	}


	/** No items */
	public function no_items() {
		echo __( 'No discussions found.', 'CP_TD' );
	}

	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'discussion' => __( 'Title', 'CP_TD' ),
			'course' => __( 'Course', 'CP_TD' ),
			'status' => __( 'Status', 'CP_TD' ),
			'actions' => __( 'Actions', 'CP_TD' ),
		);

		if ( ! CoursePress_Data_Capabilities::can_delete_discussion( 0 ) ) {
			unset( $columns['actions'] );
		}
		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	public function get_sortable_columns() {
		// return array( 'course' => array( 'course', false ) );
		return array();
	}

	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
		);
	}

	public function column_discussion( $item ) {
		// create a nonce
		// $duplicate_nonce = wp_create_nonce( 'duplicate_course' );
		$title = '<strong>' . $item->post_title . '</strong>';
		$excerpt = CoursePress_Helper_Utility::truncate_html( $item->post_content );

		$edit_page = CoursePress_View_Admin_Communication_Discussion::$slug;

		$actions = array();

		/**
		 * check current_user_can update?
		 */
		if ( CoursePress_Data_Capabilities::can_update_discussion( $item ) ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'page' => $edit_page,
							'action' => 'edit',
							'id' => $item->ID,
						),
						admin_url( 'admin.php' )
					)
				),
				__( 'Edit', 'CP_TD' )
			);
		}

		return $title . '<br />' . $excerpt . $this->row_actions( $actions );
	}

	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
			'publish' => __( 'Visible', 'CP_TD' ),
			'unpublish' => __( 'Private', 'CP_TD' ),
			'delete' => __( 'Delete', 'CP_TD' ),
		);

		if ( ! CoursePress_Data_Capabilities::can_delete_discussion( 0 ) ) {
			unset( $actions['delete'] );
		}

		if ( ! CoursePress_Data_Capabilities::can_change_status_discussion( 0 ) ) {
			unset( $actions['publish'], $actions['unpublish'] );
		}
		return $actions;
	}

	public function column_course( $item ) {
		$attributes = CoursePress_Data_Discussion::attributes( $item->ID );

		$output = sprintf( '<div data-course="%s">%s (%s)</div>',
			$attributes['course_id'],
			$attributes['course_title'], $attributes['unit_title']
		);

		return $output;
	}

	public function column_status( $item ) {
		/**
		 * check permissions
		 */
		if ( ! CoursePress_Data_Capabilities::can_change_status_discussion( $item ) ) {
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
				'nonce' => wp_create_nonce( 'publish-discussion' ),
			),
		);
		$ui['class'] = 'discussion-' . $d_id;
		$publish_toggle = ! empty( $d_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-discussion-toggle-' . $d_id, 'publish-discussion-toggle-' . $d_id, $ui ) : '';

		return $publish_toggle;
	}

	public function column_actions( $item ) {
		/**
		 * check permissions
		 */
		if ( ! CoursePress_Data_Capabilities::can_delete_discussion( $item ) ) {
			return '';
		}
		$delete_nonce = wp_create_nonce( 'delete-notification' );
		$delete_nonce = wp_create_nonce( 'delete-discussion' );
		return sprintf(
			'<a data-id="%s" data-nonce="%s" class="delete-discussion-link"><i class="fa fa-times-circle remove-btn"></i></a>', $item->ID, $delete_nonce
		);
	}

	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	public function column_default( $item, $column_name ) {
		if ( isset( $item->{$column_name} ) ) {
			return $item->{$column_name};
		} else {
			return '';
		}

	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	public function prepare_items() {
		$post_status = 'all';

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->get_items_per_page( 'coursepress_discussions_per_page', 10 );
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();

		$offset = ( $current_page - 1 ) * $per_page;

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		 */
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
		}

		// @todo: Add permissions
		$query = new WP_Query( $post_args );

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $query->posts;

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = $query->found_posts;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(
			array(
			'total_items' => $total_items,				  //WE have to calculate the total number of items
			'per_page'	=> $per_page,					 //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page ),//WE have to calculate the total number of pages
			)
		);
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

		$page = get_query_var( 'page', 'coursepress_discussions' );

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
		$courses = CoursePress_Data_Capabilities::can_add_discussion_to_all() ? false : CoursePress_View_Admin_Communication_Discussion::get_courses();

		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id' . $two, 'course_id' . $two, $courses, $options );

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
					<input type="hidden" name="page" value="coursepress_discussions"/>
					<?php $this->search_box( __( 'Search Threads', 'CP_TD' ), 'search_discussions' ); ?>
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
