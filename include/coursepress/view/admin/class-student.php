<?php

class CoursePress_View_Admin_Student {
	private static $slug = 'coursepress_students';
	private static $title = '';
	private static $menu_title = '';
	private static $table_manager = null;

	public static function init() {
		self::$title = __( 'Courses/Students', 'CP_TD' );
		self::$menu_title = __( 'Students', 'CP_TD' );

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_filter(
			'coursepress_admin_pages',
			array( __CLASS__, 'add_page' )
		);

		add_filter(
			'coursepress_admin_valid_pages',
			array( __CLASS__, 'add_valid' )
		);
		add_action(
			'coursepress_admin_' . self::$slug,
			array( __CLASS__, 'render_page' )
		);

		add_action(
			'coursepress_settings_page_pre_render_' . self::$slug,
			array( __CLASS__, 'pre_process' )
		);

	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			'cap' => self::$slug . '_cap',
			'order' => 20,
		);

		return $pages;
	}

	public static function pre_process() {
		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		if ( empty( $view ) ) {
			self::$table_manager = new CoursePress_Helper_Table_Student;
			self::$table_manager->prepare_items();
		}
	}

	public static function render_page() {
		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		if ( empty( $view ) ) {
			self::$table_manager->display();
		} elseif ( 'workbook' == $view ) {
			CoursePress_View_Admin_Student_Workbook::display();
		} elseif ( 'profile' == $view ) {
			CoursePress_View_Admin_Student_Profile::display();
		}
	}

	/**
	 * return slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string slug
	 */
	public static function get_slug() {
		return self::$slug;
	}

}
