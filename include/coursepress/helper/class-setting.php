<?php

class CoursePress_Helper_Setting {

	private static $page_refs = array();
	private static $valid_pages = array();
	private static $pages = array();

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'admin_plugins_loaded' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
	}

	public static function admin_menu() {
		$parent_handle = 'coursepress';
		self::$page_refs[ $parent_handle ] = add_menu_page( CoursePress::$name, CoursePress::$name, 'coursepress_dashboard_cap', $parent_handle, array(
			__CLASS__,
			'menu_handler',
		), CoursePress::$url . 'asset/img/coursepress-icon.png' );

		$pages = self::_get_pages();

		foreach ( $pages as $handle => $page ) {
			// Use default callback if not defined
			$callback = empty( $page['callback'] ) ? array(
				__CLASS__,
				'menu_handler',
			) : $page['callback'];

			// Remove callback to use URL instead
			if ( 'none' == $callback ) {
				$callback = '';
			}

			// Use default capability if not defined
			$capability = empty( $page['cap'] ) ? 'coursepress_dashboard_cap' : $page['cap'];

			if ( empty( $page['parent'] ) ) {
				$page['parent'] = $parent_handle;
			}

			if ( empty( $page['handle'] ) ) {
				$page['handle'] = $handle;
			}

			if ( 'none' != $page['parent'] ) {
				self::$page_refs[ $handle ] = add_submenu_page( $page['parent'], $page['title'], $page['menu_title'], $capability, $page['handle'], $callback );
			} else {
				self::$page_refs[ $handle ] = add_submenu_page( null, $page['title'], $page['menu_title'], $capability, $page['handle'], $callback );
			}
		}

		// error_log( print_r( self::$page_refs, true  ) );
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

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'coursepress';
		if ( in_array( $page, self::get_valid_pages() ) ) {
			do_action( 'coursepress_settings_page_pre_render_' . $page );
		}

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_style' ) );
		add_filter( 'coursepress_custom_allowed_extensions', array( __CLASS__, 'allow_zip_extension' ) );
	}

	public static function admin_plugins_loaded() {
		do_action( 'coursepress_settings_page_pre_render' );
	}

	public static function admin_style() {

		$style = CoursePress::$url . 'asset/css/admin-general.css';
		$style_global = CoursePress::$url . 'asset/css/admin-global.css';
		$script = CoursePress::$url . 'asset/js/admin-general.js';
		$sticky = CoursePress::$url . 'asset/js/external/sticky.min.js';
		$editor_style = CoursePress::$url . 'asset/css/editor.css';
		$fontawesome = CoursePress::$url . 'asset/css/external/font-awesome.min.css';

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( in_array( $page, self::get_valid_pages() ) ) {
			wp_enqueue_style( 'coursepress_admin_general', $style, array( 'dashicons' ), CoursePress::$version );
			wp_enqueue_script( 'coursepress_admin_general_js', $script, array( 'jquery' ), CoursePress::$version, true );
			wp_enqueue_script( 'sticky_js', $sticky, array( 'jquery' ), CoursePress::$version, true );

			add_editor_style( $editor_style );

			// Add chosen.
			$style = CoursePress::$url . 'asset/css/external/chosen.css';
			$script = CoursePress::$url . 'asset/js/external/chosen.jquery.min.js';
			wp_enqueue_style( 'chosen_css', $style, array( 'dashicons' ), CoursePress::$version );
			wp_enqueue_script( 'chosen_js', $script, array( 'jquery' ), CoursePress::$version, true );

			// Font Awesome.
			wp_enqueue_style( 'fontawesome', $fontawesome, array(), CoursePress::$version );
		}

		wp_enqueue_style( 'coursepress_admin_global', $style_global, array(), CoursePress::$version );
	}

	public static function allow_zip_extension( $extensions ) {

		if ( empty( $extensions ) ) {
			$extensions = array();
		}

		$extensions[] = 'zip';

		return $extensions;

	}
}
