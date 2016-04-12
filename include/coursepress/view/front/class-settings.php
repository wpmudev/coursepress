<?php

class CoursePress_View_Front_Settings {

	public static function init() {

		add_filter( 'the_content', array( __CLASS__, 'the_content_student_settings_page' ) );

	}

	public static function render_dashboard_page() {

		ob_start();
		CoursePress_View_Front_Student::render_student_settings_page();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;

	}

	/**
	 * Display settings page.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post * $post The WP_Post object.

	 * @param string $content Current entry content.
	 *
	 * @return string Current entry content.
	 */

	public static function the_content_student_settings_page( $content ) {
		/**
		 * we do not need change other post type than page
		 */
		if ( ! is_page() ) {
			return $content;
		}
		/**
		 * check setup is pages/student_settings a page?
		 */
		$student_settings_page_id = CoursePress_Core::get_setting( 'pages/student_settings', 0 );
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
