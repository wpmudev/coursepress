<?php

class CoursePress_View_Admin_Settings {

	private static $slug = 'coursepress_settings';
	private static $title = '';
	private static $menu_title = '';
	private static $tabs = array();
	private static $settings_classes = array(
		'General',
		'Email',
		'Capabilities',
		'BasicCertificate',
		'Shortcodes',
		'Extensions',
		'Setup'
	);

	public static function init() {

		self::$title      = __( 'Settings/CoursePress', CoursePress::TD );
		self::$menu_title = __( 'Settings', CoursePress::TD );

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'process_form' ), 1 );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );

		// Init all the settings classes
		foreach ( self::$settings_classes as $page ) {
			$class = 'CoursePress_View_Admin_Settings_' . $page;

			if ( method_exists( $class, 'init' ) ) {
				call_user_func( $class . '::init' );
			}
		}

	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title'      => self::$title,
			'menu_title' => self::$menu_title,
		);

		return $pages;
	}

	public static function get_tabs() {

		// Make it a filter so we can add more tabs easily
		self::$tabs = apply_filters( self::$slug . '_tabs', self::$tabs );

		// Make sure that we have all the fields we need
		foreach ( self::$tabs as $key => $tab ) {
			self::$tabs[ $key ]['buttons'] = isset( $tab['buttons'] ) ? $tab['buttons'] : 'both';
			self::$tabs[ $key ]['class']   = isset( $tab['class'] ) ? $tab['class'] : '';
			self::$tabs[ $key ]['is_form'] = isset( $tab['is_form'] ) ? $tab['is_form'] : true;
			self::$tabs[ $key ]['order']   = isset( $tab['order'] ) ? $tab['order'] : 999; // Set default order to 999... bottom of the list
		}

		// Order the tabs
		self::$tabs = CoursePress_Helper_Utility::sort_on_key( self::$tabs, 'order' );

		return self::$tabs;
	}

	public static function process_form() {

		$tabs      = self::get_tabs();
		$tab_keys  = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';

		$tab = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );

		$method = preg_replace( '/\_$/', '', 'process_' . $tab );

		do_action( self::$slug . '_' . $method, self::$slug, $tab );
	}

	public static function render_page() {

		$tabs      = self::get_tabs();
		$tab_keys  = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';

		$tab     = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );
		$content = '';

		$method = preg_replace( '/\_$/', '', 'render_tab_' . $tab );
		if ( method_exists( __CLASS__, $method ) ) {
			ob_start();
			call_user_func( __CLASS__ . '::' . $method );
			$content = ob_get_clean();
		}

		// Get the content using a filter if its not in this class
		$content = apply_filters( self::$slug . '_' . $method, $content, self::$slug, $tab );

		$hidden_args = $_GET;
		unset( $hidden_args['_wpnonce'] );

		$content = '<div class="coursepress_settings_wrapper">' .
		           '<h3>' . esc_html( CoursePress_Core::$name ) . ' : ' . esc_html( self::$menu_title ) . '</h3>
		            <hr />' .
		           CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $hidden_args, self::$slug, $tab, false ) .
		           '</div>';

		echo apply_filters( 'coursepress_settings_page_main', $content );

	}

}