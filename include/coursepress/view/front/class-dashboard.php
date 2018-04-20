<?php

class CoursePress_View_Front_Dashboard {

	public static $title = ''; // The page title

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

	}

	public static function render_dashboard_page() {
		CoursePress_Core::$is_cp_page = true;

		$theme_file = locate_template( array( 'cp-dashboard.php' ) );

		if ( $theme_file ) {
			CoursePress_View_Front_Course::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_Dashboard::render_dashboard_page();
		}

		return $content;
	}

	public static function parse_request( &$wp ) {
		$check = CoursePress_Helper_Front::check_and_redirect( 'student_dashboard' );
		if ( ! $check ) {
			return;
		}
		$content = '';
		$page_title = __( 'My Courses', 'coursepress' );
		$args = array(
			'slug' => CoursePress_Core::get_slug( 'student_dashboard' ),
			'title' => esc_html( $page_title ),
			'content' => ! empty( $content ) ? esc_html( $content ) : self::render_dashboard_page(),
			'type' => 'coursepress_student_dashboard',
		);
		$pg = new CoursePress_Data_VirtualPage( $args );
	}
}
