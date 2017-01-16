<?php
// THIS CLASS IS DEPRACATED AS OF 2.0

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
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
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

class CoursePress_Helper_Table_CourseList extends WP_List_Table {

	private $count = array();
	private $post_type;
	private $_categories;
	private $columns_config;
	private $date_format;

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	public function __construct() {

		$post_format = CoursePress_Data_Course::get_format();
		$this->date_format = get_option( 'date_format' );

		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,// should this table support ajax?
		) );

		$this->post_type = CoursePress_Data_Course::get_post_type_name();
		$this->count = wp_count_posts( $this->post_type );
		$this->columns_config = array(
			'cb' => '<input type="checkbox" />',
			'ID' => __( 'ID', 'CP_TD' ),
			'post_title' => __( 'Title', 'CP_TD' ),
			'categories' => __( 'Categories', 'CP_TD' ),
			'date_start' => __( 'Start Date', 'CP_TD' ),
			'date_end' => __( 'End Date', 'CP_TD' ),
			'date_enrollment_start' => __( 'Enrollment Start', 'CP_TD' ),
			'date_enrollment_end' => __( 'Enrollment End', 'CP_TD' ),
			'units' => __( 'Units', 'CP_TD' ),
			'students' => __( 'Students', 'CP_TD' ),
			'certificates' => __( 'Certified', 'CP_TD' ),
			'status' => __( 'Status', 'CP_TD' ),
			'actions' => __( 'Actions', 'CP_TD' ),
		);

	}

	/** No items */
	public function no_items() {
		_e( 'No courses found.', 'CP_TD' );
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
		$columns = $this->columns_config;
		if ( ! CoursePress_Data_Capabilities::can_manage_courses() ) {
			unset( $columns['cb'], $columns['actions'], $columns['units'] );
		}
		if ( ! CoursePress_Data_Capabilities::can_delete_course( 0 ) ) {
			unset( $columns['actions'] );
		}
		/**
		 * WordPress standard action for defeult column - it allows to add
		 * special columns.
		 */
		$columns = apply_filters( "manage_{$this->post_type}_posts_columns", $columns );
		return $columns;
	}

	public function get_hidden_columns() {
		return CoursePress_Helper_Setting::get_hidden_columns();
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
		$sortable_columns = array(
			'ID' => array( 'id' ),
			'post_title' => array( 'title', true ),
			'date_start' => array( 'date_start', true ),
			'date_enrollment_start' => array( 'date_enrollment_start', true ),
		);
		return $sortable_columns;
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

	private function _get_course_meta_date( $name, $item ) {
		$meta_key = sprintf( 'cp_%s_date', $name );
		$date = get_post_meta( $item->ID, $meta_key, true );
		if ( empty( $date ) ) {
			return '-';
		} else {
			$date = date_i18n( $this->date_format, $date );
		}
		return $date;
	}

	/**
	 * Course Categories
	 *
	 * @since 2.0.0
	 */
	public function column_categories( $item ) {
		$taxonomy = CoursePress_Data_Course::get_post_category_name();
		$taxonomy_object = get_taxonomy( $taxonomy );
		$terms = CoursePress_Data_Course::get_course_terms( $item->ID );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$out = array();
			$args = array();
			$args['page'] = CoursePress_View_Admin_CoursePress::get_slug();
			foreach ( $terms as $t ) {
				$args['category'] = $t->term_id;
				$url = add_query_arg( $args, 'admin.php' );
				$out[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $url ),
					esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) )
				);
			}
			echo join( __( ', ', 'CP_TD' ), $out );
		} else {
			echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . $taxonomy_object->labels->no_terms . '</span>';
		}
	}

	/**
	 * Start date
	 */
	public function column_date_start( $item ) {
		return $this->_get_course_meta_date( 'course_start', $item );
	}

	/**
	 * end date
	 */
	public function column_date_end( $item ) {
		return $this->_get_course_meta_date( 'course_end', $item );
	}

	/**
	 * enrollment_end date
	 */
	public function column_date_enrollment_end( $item ) {
		return $this->_get_course_meta_date( 'enrollment_end', $item );
	}

	/**
	 * enrollment_start date
	 */
	public function column_date_enrollment_start( $item ) {
		return $this->_get_course_meta_date( 'enrollment_start', $item );
	}

	// column_{key}
	public function column_post_title( $item ) {
		// Apply course capabilities
		$user_id = get_current_user_id();

		// create a nonce
		$duplicate_nonce = wp_create_nonce( 'duplicate_course' );

		$title = '<strong>' . apply_filters( 'the_title', $item->post_title, $item->ID ) . '</strong>';

		$edit_page = CoursePress_View_Admin_Course_Edit::$slug;

		$actions = array();
		/**
		 * check instructor privileges
		 */
		if ( CoursePress_Data_Capabilities::can_update_course( $item->ID ) ) {
			$actions['edit'] = sprintf( '<a href="?page=%s&action=%s&id=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), __( 'Edit', 'CP_TD' ) );
			$actions['units'] = sprintf( '<a href="?page=%s&action=%s&id=%s&tab=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), 'units', __( 'Units', 'CP_TD' ) );
			$actions['students'] = sprintf( '<a href="?page=%s&action=%s&id=%s&tab=%s">%s</a>', esc_attr( $edit_page ), 'edit', absint( $item->ID ), 'students',  __( 'Students', 'CP_TD' ) );
			if ( CoursePress_Data_Capabilities::can_create_course( $user_id ) ) {
				$actions['duplicate'] = sprintf( '<a data-nonce="%s" data-id="%s" class="duplicate-course-link">%s</a>', $duplicate_nonce, $item->ID, __( 'Duplicate Course', 'CP_TD' ) );
			}
		}

		/**
		 * link to units
		 */
		if ( 'publish' === $item->post_status || CoursePress_Data_Capabilities::can_update_course( $item->ID ) ) {
			$actions['view_units'] = sprintf(
				'<a href="%s%s" target="_blank">%s</a>',
				CoursePress_Data_Course::get_course_url( $item->ID ),
				CoursePress_Core::get_slug( 'units/' ),
				'publish' == $item->post_status ? __( 'View Units', 'CP_TD' ) : __( 'Preview Units', 'CP_TD' )
			);
		}

		if ( 'publish' === $item->post_status || CoursePress_Data_Capabilities::can_update_course( $item->ID ) ) {
			$actions['view_course'] = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				CoursePress_Data_Course::get_course_url( $item->ID ),
				'publish' == $item->post_status ? __( 'View Course', 'CP_TD' ) : __( 'Preview Course', 'CP_TD' )
			);
		}

		if ( ! CoursePress_Data_Capabilities::can_update_course( $item->ID, $user_id ) ) {
			unset( $actions['edit'] );
		}

		/**
		 * Export course only when user can edit this course.
		 */
		if ( CoursePress_Data_Capabilities::can_update_course( $item->ID, $user_id ) ) {
			/**
			 * single course export
			 */
			$action = 'coursepress_export';
			$nonce = wp_create_nonce( $action );
			$url = add_query_arg(
				array(
					'page' => $action,
					'coursepress' => array( 'courses' => array( absint( $item->ID ) ) ),
					'coursepress_export' => $nonce,
				),
				admin_url( 'admin.php' )
			);

			$url = wp_nonce_url( $url, $action, $nonce );
			$actions['export'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $url ),
				__( 'Export', 'CP_TD' )
			);
		}
		if ( ! CoursePress_Data_Capabilities::can_view_course_units( $item->ID, $user_id ) ) {
			unset( $actions['units'] );
		}
		if ( ! CoursePress_Data_Capabilities::can_view_course_students( $item->ID ) ) {
			unset( $actions['students'] );
		}

		return $title . $this->row_actions( $actions );
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
		$actions = array();
		if ( CoursePress_Data_Capabilities::can_change_course_status( 0 ) ) {
			$actions['publish'] = __( 'Publish', 'CP_TD' );
			$actions['unpublish'] = __( 'Unpublish', 'CP_TD' );
			$actions['export'] = __( 'Export', 'CP_TD' );
		}
		if ( CoursePress_Data_Capabilities::can_delete_course( 0 ) ) {
			$actions['delete'] = __( 'Delete', 'CP_TD' );
		}
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
		$output = sprintf( '<div><p>%d&nbsp;%s<br />%d&nbsp;%s</p>',
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

	/**
	 * Column contain number of certified students.
	 *
	 * @since 2.0.0
	 */
	public function column_certificates( $item ) {
		return intval( $item->students_with_certificate );
	}

	public function column_status( $item ) {

		$user_id = get_current_user_id();
		$publish_toggle = ucfirst( $item->post_status );

		if ( CoursePress_Data_Capabilities::can_change_course_status( $item->ID, $user_id ) ) {
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
		}

		return $publish_toggle;
	}

	public function column_actions( $item ) {
		$delete_link = '--';
		$user_id = get_current_user_id();

		if ( CoursePress_Data_Capabilities::can_delete_course( $item->ID, $user_id ) ) {
			$delete_nonce = wp_create_nonce( 'delete_course' );
			$delete_link = sprintf(
				'<a data-id="%s" data-nonce="%s" class="delete-course-link"><i class="fa fa-times-circle remove-btn"></i></a>', $item->ID, $delete_nonce
			);
		}

		return $delete_link;
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
		switch ( $column_name ) {
			case 'ID':
				return $item->{$column_name};
		}
		/**
		 * WordPress standard action for defeult column - it allows to add
		 * special columns.
		 */
		do_action( "manage_{$this->post_type}_posts_custom_column", $column_name, $item->ID );
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

		$accepted_tabs = array( 'publish', 'private', 'all' );
		$tab = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $accepted_tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'publish';
		$valid_categories = CoursePress_Data_Course::get_course_categories();
		$valid_categories = array_keys( $valid_categories );
		$category = isset( $_GET['category'] ) && in_array( $_GET['category'], $valid_categories ) ? sanitize_text_field( $_GET['category'] ) : false;

		$post_status = array( 'publish', 'private', 'draft' );

		// Hide private courses
		if ( ! CoursePress_Data_Capabilities::can_manage_courses() ) {
			$post_status = 'publish';
		}

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
		$user_id = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = intval( get_user_meta( $user_id, $option, true ) );
		if ( 1 > $per_page ) {
			$per_page = 10;
		}

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

		/**
		 * order by
		 */
		$orderby = 'title';
		if ( isset( $_GET['orderby'] ) && is_string( $_GET['orderby'] ) ) {
			switch ( $_GET['orderby'] ) {
				case 'ID':
					$orderby = 'ID';
				break;
				case 'date_start':
				case 'date_enrollment_start':
					$orderby = 'meta_value_num';
				break;
			}
		}
		$order = isset( $_GET['order'] ) && 'asc' == $_GET['order']? 'asc' : 'desc';
		if ( ! isset( $_GET['order'] ) && 'title' == $orderby ) {
			$order = 'asc';
		}

		/**
		 * Build args for WP_Query
		 */
		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $per_page,
			'offset' => $offset,
			's' => $s,
			'orderby' => $orderby,
			'order' => $order,
		);

		/**
		 * If date sort, then add meta_query!
		 */
		if ( 'meta_value_num' == $orderby ) {
			$key = 'cp_course_start_date';
			if ( isset( $_GET['orderby'] ) && 'date_enrollment_start' == $_GET['orderby'] ) {
				$key = 'cp_enrollment_start_date';
			}
			$post_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => $key,
					'compare' => 'EXISTS',
				),
				array(
					'key' => $key,
					'compare' => 'NOT EXISTS',
				),
			);
		}

		if ( ! CoursePress_Data_Capabilities::can_view_others_course() ) {
			$user_id = get_current_user_id();
			$post_args['author'] = $user_id;

			if ( user_can( $user_id, 'coursepress_update_course_cap' ) ) {
				$assigned_courses = CoursePress_Data_Instructor::get_assigned_courses_ids( $user_id );
				if ( ! empty( $assigned_courses ) ) {
					$post_args['post__in'] = $assigned_courses;
					unset( $post_args['author'] );
					add_filter( 'posts_where', array( 'CoursePress_Data_Instructor', 'filter_by_whereall' ) );
				}
			}
		}

		if ( $category && CoursePress_Data_Capabilities::can_manage_categories() ) {
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => 'course_category',
					'field' => 'term_id',
					'terms' => array( $category ),
				),
			);
		}

		if ( isset( $_GET['instructor_id'] ) ) {
			$post_args['author'] = (int) $_GET['instructor_id'];
		}

		$query = new WP_Query( $post_args );

		/**
		 * fil certificates
		 */
		$certificates = CoursePress_Data_Certificate::get_certificates_count();

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		foreach ( $query->posts as $post ) {
			$post->students_with_certificate = isset( $certificates[ $post->ID ] )? $certificates[ $post->ID ] : 0;
			$this->items[] = $post;
		}

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

		<?php
		if ( Coursepress_Data_Capabilities::can_manage_courses() ) {
		?>
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
		<?php
		}
		?>
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
