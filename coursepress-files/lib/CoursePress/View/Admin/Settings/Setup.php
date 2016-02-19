<?php

class CoursePress_View_Admin_Settings_Setup{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_setup', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_setup', array( __CLASS__, 'return_content' ), 10, 3 );

		if ( isset( $_GET['tab'] ) && 'setup' === $_GET['tab'] ) {
			add_filter( 'coursepress_settings_tabs_content', array( __CLASS__, 'remove_tabs' ), 10, 2 );
			add_filter( 'coursepress_settings_page_main', array( __CLASS__, 'return_content' ) );
			add_action( 'coursepress_settings_page_pre_render', array( __CLASS__, 'remove_dashboard_notification' ) );
		}

	}


	public static function add_tabs( $tabs ) {

		$tabs['setup'] = array(
			'title' => __( 'Setup Guide', 'CP_TD' ),
			'description' => __( 'This is the description of what you can do on this page.', 'CP_TD' ),
			'order' => 70,
			'class' => 'setup_tab',
		);

		return $tabs;
	}

	public static function return_content( $content ) {

		// Show some things differently if this is CoursePress has just been activated
		$show_setup_guide = CoursePress_Core::get_setting( 'general/show_setup_guide', 1 );

		$content = 'setup!';

		// Return to setup
		if ( empty( $show_setup_guide ) ) {
			$return_url = add_query_arg( array(
				'page' => $_GET['page'],
				'tab' => 'general',
			), admin_url( 'admin.php' ) );
			$content .= '<p><a href="' . $return_url . '">' . esc_html__( 'Return to CoursePress settings.', 'CP_TD' ) . '</a></p>';
		}

		return $content;

	}

	public static function remove_tabs( $wrapper, $content ) {
		$wrapper = $content;
		return $wrapper;
	}


	public static function remove_dashboard_notification() {

		if ( isset( $_GET['tab'] ) && 'setup' === $_GET['tab'] ) {
			global $wpmudev_notices;
			$wpmudev_notices = array();
		}
	}


	public static function process_form() {

	}
}
