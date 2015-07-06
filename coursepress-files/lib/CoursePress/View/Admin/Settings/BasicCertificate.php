<?php

class CoursePress_View_Admin_Settings_BasicCertificate{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( get_class(), 'add_tabs' ) );
		add_action( 'coursepress_settings_process_basic_certificate', array( get_class(), 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_basic_certificate', array( get_class(), 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['basic_certificate'] = array(
			'title' => __( 'Basic Certificate', CoursePress::TD ),
			'description' => __( 'This is the description of what you can do on this page.', CoursePress::TD ),
			'order' => 40
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$content = 'certificate!';


		return $content;

	}

	public static function process_form() {



	}

}