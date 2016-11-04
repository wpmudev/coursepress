<?php
/**
 * CoursePress Comments
 *
 * This comments only works with CP export.
 *
 * @since 2.0
 **/
class CoursePress_Admin_Comments extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_comments';
	private static $start_time = 0;
	private static $current_time = 0;
	private static $time_limit_reached = false;
	protected $cap = 'coursepress_settings_cap';
	var $comments_list = null;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Comments', 'cp' ),
			'menu_title' => __( 'Comments', 'cp' ),
		);
	}

	/**
	 * Process the commentsed courses
	 *
	 * @since 2.0
	 **/
	public function process_form() {
        if ( empty( $_REQUEST['view'] ) ) {
			// Set up comments table
			$this->comments_list = new CoursePress_Admin_Table_Comments;
			$this->comments_list->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20, 'option' => 'coursepress_comments_per_page', 'label' => __( 'Number of comments per page:', 'cp' ) ) );
        } else {
        }
	}

}
