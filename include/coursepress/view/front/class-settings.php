<?php

class CoursePress_View_Front_Settings {

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

	}

	public static function render_dashboard_page() {

		ob_start();
			CoursePress_View_Front_Student::render_student_settings_page();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;

	}

	public static function parse_request( &$wp ) {
		$check = CoursePress_Helper_Front::check_and_redirect( 'student_settings' );
		if ( ! $check ) {
			return;
		}
		$content = '';
		$page_title = __( 'My Profile', 'coursepress' );
		$args = array(
			'slug' => CoursePress_Core::get_slug( 'student_settings' ),
			'title' => esc_html( $page_title ),
			'content' => ! empty( $content ) ? esc_html( $content ) : self::render_dashboard_page(),
			'type' => 'coursepress_student_settings',
		);
		$pg = new CoursePress_Data_VirtualPage( $args );
	}
}
