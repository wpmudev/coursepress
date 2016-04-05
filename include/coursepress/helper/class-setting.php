<?php

class CoursePress_Helper_Setting {

	private static $page_refs = array();
	private static $valid_pages = array();
	private static $pages = array();
	private static $default_capability;

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'admin_plugins_loaded' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		/** This filter is documented in /wp-admin/includes/misc.php */
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * allow to get default capability
	 *
	 * @since 2.0.0
	 *
	 * @return string default capability
	 */
	private static function get_default_capability() {
		$userdata = get_userdata( get_current_user_id() );
		if ( empty( self::$default_capability ) ) {
			self::$default_capability = 'coursepress_dashboard_cap';
			if ( current_user_can( 'manage_options' ) ) {
				self::$default_capability = 'manage_options';
			}
			/**
			 * Filer allow to change default capability.
			 *
			 * @since 2.0.0
			 *
			 * @param string $capability CoursePress capability.
			 * @param string $slug CoursePress page slug
			 *
			 */
			self::$default_capability = apply_filters( 'coursepress_capabilities', self::$default_capability );
		}
		return self::$default_capability;
	}

	public static function admin_menu() {
		$parent_handle = 'coursepress';
		self::$page_refs[ $parent_handle ] = add_menu_page(
			CoursePress::$name,
			CoursePress::$name,
			/**
			 * Filer allow to change capability.
			 *
			 * @since 2.0.0
			 *
			 * @param string $capability CoursePress capability.
			 * @param string $slug CoursePress page slug
			 *
			 */
			apply_filters( 'coursepress_capabilities', self::get_default_capability() ),
			$parent_handle,
			array(
				__CLASS__,
				'menu_handler',
			),
			CoursePress::$url . 'asset/img/coursepress-icon.png'
		);

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
			$capability = empty( $page['cap'] ) ? self::get_default_capability() : $page['cap'];

			if ( empty( $page['parent'] ) ) {
				$page['parent'] = $parent_handle;
			}

			if ( empty( $page['handle'] ) ) {
				$page['handle'] = $handle;
			}

			if ( CoursePress_Data_Capabilities::can_manage_courses() ) {
				if ( 'none' != $page['parent'] ) {
					self::$page_refs[ $handle ] = add_submenu_page( $page['parent'], $page['title'], $page['menu_title'], $capability, $page['handle'], $callback );
				} else {
					self::$page_refs[ $handle ] = add_submenu_page( null, $page['title'], $page['menu_title'], $capability, $page['handle'], $callback );
				}
			}
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

	public static function reorder_menu( $a, $b ) {
		return $a > $b;
	}

	/**
	 * Internal helper function used by array_map() to get numeric order values.
	 *
	 * @since  2.0.0
	 * @param  array $a
	 * @return int
	 */
	public static function _page_order( $a ) {
		if ( empty( $a['order'] ) ) {
			return 0;
		} else {
			return (int) $a['order'];
		}
	}

	private static function _get_pages() {
		$pages = apply_filters( 'coursepress_admin_pages', self::$pages );
		$order = array_map( array( self, '_page_order' ), $pages );
		$max_order = max( $order );
		$new_order = array();

		foreach ( $pages as $key => $page ) {
			$page_order = ! empty( $page['order'] ) ? $page['order'] : ( $max_order += 5 );
			$page['order'] = $page_order;
			$pages[ $key ] = $page;
			$new_order[ $page_order ] = $key;
		}

		uksort( $new_order, array( __CLASS__, 'reorder_menu' ) );
		$new_pages = array();
		foreach ( $new_order as $order => $key ) {
			$new_pages[ $key ] = $pages[ $key ];
		}

		return $new_pages;

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

			// Add instructor stylesheet
			if ( CoursePress_View_Admin_Instructor::$slug == $page ) {
				$instructor_css = CoursePress::$url . 'asset/css/admin-instructor.css';
				wp_enqueue_style( 'coursepress_admin_instructor', $instructor_css, false, CoursePress::$version );
			}

			// Add student stylesshet
			if ( CoursePress_View_Admin_Student::$slug == $page ) {
				$student_css = CoursePress::$url . 'asset/css/admin-student.css';
				wp_enqueue_style( 'coursepress_admin_student', $student_css, false, CoursePress::$version );
			}
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

	/**
	 * Function return value for CoursePress options.
	 *
	 * @since 2.0.0
	 *
	 * @param bool|int $value Screen option value. Default false to skip.
	 * @param string $option The option name.
	 * @param integer $value The number of rows to use.
	 *
	 * @return mixed value or status.
	 */
	public static function set_screen_option( $status, $option, $value ) {
		if ( preg_match( '/^coursepress_/', $option ) ) {
			return $value;
		}
		return $status;
	}
}
