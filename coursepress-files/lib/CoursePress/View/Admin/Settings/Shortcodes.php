<?php

class CoursePress_View_Admin_Settings_Shortcodes{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_shortcodes', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_shortcodes', array( __CLASS__, 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['shortcodes'] = array(
			'title' => __( 'Shortcodes', CoursePress::TD ),
			'description' => __( 'This is the description of what you can do on this page.', CoursePress::TD ),
			'order' => 50
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$content = 'shortcodes!';


		return $content;

	}

	public static function process_form() {



	}

}