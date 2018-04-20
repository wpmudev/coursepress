<?php

class CoursePress_View_Admin_Setting_Course {

	public static function init() {
		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_course', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_course', array( __CLASS__, 'return_content' ), 10, 3 );
	}

	public static function add_tabs( $tabs ) {
		$tabs['setup'] = array(
			'title' => __( 'Setup Guide', 'coursepress' ),
			'description' => __( 'This is the description of what you can do on this page.', 'coursepress' ),
			'order' => 20,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$content = 'course!';

		return $content;
	}

	public static function process_form() {
		// ...
	}
}
