<?php
/**
 * Instructors Class
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
class CoursePress_Admin_Instructors extends CoursePress_Admin_Controller_Menu {
	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_instructors';
	var $with_editor = false;
	protected $cap = 'coursepress_instructors_cap';
	var $instructors_list;

	public function get_labels() {
		return array(
			'title' => __( 'CoursePress Instructors', 'cp' ),
			'menu_title' => __( 'Instructors', 'cp' ),
		);
	}

	public function process_form() {
		if ( empty( $_REQUEST['view'] ) ) {
			// Set up instructors table
			$this->instructors_list = new CoursePress_Admin_Table_Instructors;
			$this->instructors_list->prepare_items();

			add_screen_option( 'per_page', array( 'default' => 20 ) );
		}
	}
}
