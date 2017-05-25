<?php
/**
 * Class CoursePress_Admin_Students
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Admin_Students extends CoursePress_Admin_Page {
	protected $slug = 'coursepress_students';

	public function __construct() {
		$this->menu_title = __( 'Students', 'cp' );
		$this->label = __( 'Students', 'cp' );

		parent::__construct();
	}
}