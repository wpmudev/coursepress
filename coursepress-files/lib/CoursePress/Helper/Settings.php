<?php

class CoursePress_Helper_Settings {


	private static $page_refs = array();
	private static $valid_pages = array();
	private static $pages = array();

	public static function init() {

		add_action( 'plugins_loaded', array( __CLASS__, 'admin_plugins_loaded' ) );
		add_action( 'admin_menu', array( get_class(), 'admin_menu' ) );
		add_action( 'admin_init', array( get_class(), 'admin_init' ) );

	}

	public static function admin_menu() {

		$parent_handle                     = 'coursepress';
		self::$page_refs[ $parent_handle ] = add_menu_page( CoursePress_Core::$name, CoursePress_Core::$name, 'coursepress_dashboard_cap', $parent_handle, array(
			get_class(),
			'menu_handler'
		), CoursePress_Core::$plugin_lib_url . 'assets/coursepress-icon.png' );

		$pages = self::_get_pages();

		foreach ( $pages as $handle => $page ) {

			// Use default callback if not defined
			$callback = empty( $page['callback'] ) ? array(
				get_class(),
				'menu_handler'
			) : $page['callback'];

			// Use default capability if not defined
			$capability = empty( $page['cap'] ) ? 'coursepress_dashboard_cap' : $page['cap'];

			self::$page_refs[ $handle ] = add_submenu_page( $parent_handle, $page['title'], $page['menu_title'], $capability, $handle, $callback );
		}

	}

	public static function menu_handler() {

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'coursepress';

		if ( in_array( $page, self::get_valid_pages() ) ) {
			do_action( 'coursepress_admin_' . $page );
		}

	}

	public static function get_valid_pages() {
		return apply_filters( 'coursepress_admin_valid_pages', self::$valid_pages );
	}

	public static function get_page_references() {
		return self::$page_refs;
	}

	private static function _get_pages() {
		return apply_filters( 'coursepress_admin_pages', self::$pages );
	}

	public static function admin_init() {

		add_action( 'admin_enqueue_scripts', array( get_class(), 'admin_style' ) );
	}

	public static function admin_plugins_loaded() {
		do_action( 'coursepress_settings_page_pre_render' );
	}

	public static function admin_style() {

		$style        = CoursePress_Core::$plugin_lib_url . 'styles/admin-general.css';
		$style_global = CoursePress_Core::$plugin_lib_url . 'styles/admin-global.css';
		$script       = CoursePress_Core::$plugin_lib_url . 'scripts/admin-general.js';
		$sticky       = CoursePress_Core::$plugin_lib_url . 'scripts/sticky.min.js';

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( in_array( $page, self::get_valid_pages() ) ) {
			wp_enqueue_style( 'coursepress_admin_general', $style, array( 'dashicons' ), CoursePress_Core::$version );
			wp_enqueue_script( 'coursepress_admin_general_js', $script, array( 'jquery' ), CoursePress_Core::$version, true );
			wp_enqueue_script( 'sticky_js', $sticky, array( 'jquery' ), CoursePress_Core::$version, true );
		}

		wp_enqueue_style( 'coursepress_admin_global', $style_global, array(), CoursePress_Core::$version );
	}

}