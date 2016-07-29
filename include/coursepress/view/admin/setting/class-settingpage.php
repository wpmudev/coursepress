<?php
class CoursePress_View_Admin_Setting_SettingPage {
	var $slug = '';
	static $_instance = null;

	public static function init() {
		if( ! self::$_instance ) {
			self::$_instance = new self;
		}
	}

	public function __construct() {
		add_filter( 'coursepress_settings_tabs', array( $this, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_' . $this->slug, array( $this, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_' . $this->slug, array( $this, 'return_content' ), 10, 3 );
	}

	/**
	 * Must be overriden in a sub-class
	 **/
	public static function add_tabs( $tabs ) {
		return $tabs;
	}

	public static function process_form() {}
	public static function return_content( $content, $slug, $tab ) {}
}