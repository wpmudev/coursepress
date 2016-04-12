<?php

class CoursePress_View_Front_Dashboard {

	public static $title = ''; // The page title

	public static function init() {

		add_filter( 'the_content', array( __CLASS__, 'the_content_student_dashboard_page' ) );

	}

	public static function render_dashboard_page() {
		$content = CoursePress_Template_Dashboard::render_dashboard_page();
		return $content;
	}

	/**
	 * Display dashboard page.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post * $post The WP_Post object.

	 * @param string $content Current entry content.
	 *
	 * @return string Current entry content.
	 */

	public static function the_content_student_dashboard_page( $content ) {
		/**
		 * we do not need change other post type than page
		 */
		if ( ! is_page() ) {
			return $content;
		}
		/**
		 * check setup is pages/student_settings a page?
		 */
		$student_settings_page_id = CoursePress_Core::get_setting( 'pages/student_dashboard', 0 );
		if ( empty( $student_settings_page_id ) ) {
			return $content;
		}
		/**
		 * check current page
		 */
		global $post;
		if ( $student_settings_page_id != $post->ID ) {
			return $content;
		}
		$content .= self::render_dashboard_page();
		return $content;
	}
}
