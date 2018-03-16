<?php
/**
 * Class CoursePress_Comments
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Comments extends CoursePress_Admin_Page {
	/**
	 * @var string the main menu slug.
	 */
	protected $slug = 'coursepress_comments';

	/**
	 * CoursePress_Admin_Comments constructor.
	 */
	public function __construct() {
		$this->list = new CoursePress_Admin_Table_Comments();
	}

	/**
	 * Get column for the listing page.
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'author' => __( 'Author', 'cp' ),
			'comment' => __( 'Comment', 'cp' ),
			'in_response_to' => __( 'In response to', 'cp' ),
			'added' => __( 'Added', 'cp' ),
		);
		/**
		 * Trigger to allow custom column values.
		 *
		 * @since 3.0
		 *
		 * @param array $columns
		 */
		return apply_filters( 'coursepress_commentlist_columns', $columns );
	}

	/**
	 * Columns to be hidden by default.
	 *
	 * @return array
	 */
	function hidden_columns() {
		/**
		 * Trigger to modify hidden columns.
		 *
		 * @since 3.0
		 *
		 * @param array $hidden_columns.
		 */
		return apply_filters( 'coursepress_commentlist_hidden_columns', array() );
	}

	/**
	 * Custom screen options for comments listing page.
	 *
	 * Setup our custom screen options for listing page.
	 *
	 * @uses get_current_screen().
	 */
	public function screen_options() {
		// Get current screen id.
		$screen_id = get_current_screen()->id;
		// Setup columns.
		add_filter( 'default_hidden_columns', array( $this, 'hidden_columns' ) );
		add_filter( 'manage_' . $screen_id . '_columns', array( $this, 'get_columns' ) );
		// Comments per page.
		add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_comments_per_page' ) );
	}

	/**
	 * Get comments listing page content and set pagination.
	 *
	 * @uses get_current_screen().
	 * @uses get_hidden_columns().
	 * @uses coursepress_render().
	 */
	function get_page() {

		$screen = get_current_screen();
		$page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : $this->slug;
		$statuses = array();
		$this->list->prepare_items();
		$count = $this->list->get_count();
		$args = array(
			'columns' => $this->get_columns(),
			'course_edit_link' => add_query_arg( 'page', 'coursepress_course', admin_url( 'admin.php' ) ),
			'courses' => coursepress_get_accessible_courses(),
			'hidden_columns' => get_hidden_columns( $screen ),
			'items' => $this->list->items,
			'page' => $page,
			'pagination' => $this->set_pagination( $count, 'coursepress_comments_per_page' ),
			'statuses' => $statuses,
		);
		coursepress_render( 'views/admin/comments', $args );
		coursepress_render( 'views/admin/footer-text' );
	}
}
