<?php

class CoursePress_View_Admin_Settings_Extensions{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( get_class(), 'add_tabs' ) );
		add_action( 'coursepress_settings_process_extensions', array( get_class(), 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_extensions', array( get_class(), 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['extensions'] = array(
			'title' => __( 'Extensions', CoursePress::TD ),
			'description' => __( 'This is the description of what you can do on this page.', CoursePress::TD ),
			'order' => 60
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$content = 'extensions!';


		return $content;

	}

	public static function process_form() {



	}

}