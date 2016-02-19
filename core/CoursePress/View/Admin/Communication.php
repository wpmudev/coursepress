<?php

class CoursePress_View_Admin_Communication {

	private static $admin_pages = array(
		'Discussion',
		'Notification',
	);

	public static function init() {

		// Init Communication Admin Views
		foreach ( self::$admin_pages as $page ) {
			$class = 'CoursePress_View_Admin_Communication_' . $page;

			if ( method_exists( $class, 'init' ) ) {
				call_user_func( $class . '::init' );
			}
		}

	}
}
