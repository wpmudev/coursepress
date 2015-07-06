<?php

class CoursePress_View_Admin_CoursePress {

	private static $slug = 'coursepress';

	public static function init() {

		add_filter( 'coursepress_admin_valid_pages', array( get_class(), 'add_valid' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( get_class(), 'render_page' ) );

	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;
		return $valid_pages;
	}

	public static function render_page() {

		echo 'walkakakakkaka';

	}

}