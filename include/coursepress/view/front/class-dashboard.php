<?php

class CoursePress_View_Front_Dashboard {

	public static $title = ''; // The page title

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

	}

	public static function render_dashboard_page() {
		// if ( $theme_file = locate_template( array( 'instructor-single.php' ) ) ) {
		// } else {
		// wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
		// if ( locate_template( array( 'instructor-single.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
		// just output the content
		// } else {
		$content = CoursePress_Template_Dashboard::render_dashboard_page();

		// }
		// }
		return $content;
	}

	public static function parse_request( &$wp ) {
		$check = CoursePress_Helper_Front::check_and_redirect( 'student_dashboard' );
		if ( ! $check ) {
			return;
		}
		$content = '';
		$page_title = __( 'My Courses', 'CP_TD' );
		$args = array(
			'slug' => CoursePress_Core::get_slug( 'student_dashboard' ),
			'title' => esc_html( $page_title ),
			'content' => ! empty( $content ) ? esc_html( $content ) : self::render_dashboard_page(),
			'type' => 'coursepress_student_dashboard',
		);
		$pg = new CoursePress_Data_VirtualPage( $args );
	}
}
