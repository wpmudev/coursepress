<?php

class CoursePress_View_Admin_Settings_Email{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( get_class(), 'add_tabs' ) );
		add_action( 'coursepress_settings_process_email', array( get_class(), 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_email', array( get_class(), 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['email'] = array(
			'title' => __( 'E-mail Settings', CoursePress::TD ),
			'description' => __( 'This is the description of what you can do on this page.', CoursePress::TD ),
			'order' => 10
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$content = '
			<input type="hidden" name="page" value="' . esc_attr( $slug ) .'"/>
			<input type="hidden" name="tab" value="' . esc_attr( $tab ) .'"/>
			<input type="hidden" name="action" value="updateoptions"/>
		' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false ) . '

		';


		return $content;

	}

	public static function process_form() {



	}

}