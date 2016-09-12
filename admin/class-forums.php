<?php
/**
 * Forum admin controller
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Forums extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_discussions';
	var $with_editor = false;
	protected $cap = 'coursepress_discussions_cap';
	protected $list_forums;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Forums', 'cp' ),
			'menu_title' => __( 'Forums', 'cp' ),
		);
	}

	public function process_form() {

		if ( empty( $_REQUEST['action'] ) || 'edit' !== $_REQUEST['action'] ) {
			$this->slug = 'coursepress_forums-table';

			// Prepare items
			$this->list_forums = new CoursePress_Admin_Table_Forums();
			$this->list_forums->prepare_items();
			add_screen_option( 'per_page', array( 'default' => 20 ) );

		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$this->slug = 'coursepress_edit-forum';

			// Set before the page
			add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
		}
	}
}
