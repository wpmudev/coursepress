<?php
class CoursePress_View_Admin_Setting_Export extends CoursePress_View_Admin_Setting_SettingPage {
	var $slug = 'export';

	public static function init() {
		self::$_instance = new self;
	}

	public static function add_tabs( $tabs ) {
		$tabs['export'] = array(
			'title' => __( 'Export Courses', 'coursepress' ),
			'description' => __( 'Export courses', 'coursepress' ),
			'order' => 30,
		);

		return $tabs;
	}
}