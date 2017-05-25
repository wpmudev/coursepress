<?php
/**
 * Class CoursePress_Admin_CourseList
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_CourseList extends CoursePress_Admin_Page {
	protected $slug = 'coursepress';

	function set_admin_menu() {
		$label = __( 'CoursePress Pro', 'cp' );
		$screen_id = add_menu_page( $label, $label, $this->cap, $this->slug, array( $this, 'get_admin_page' ), '', 25 );
	}
}