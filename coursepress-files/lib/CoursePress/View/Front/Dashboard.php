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

		// Dashboard Page
		if ( array_key_exists( 'pagename', $wp->query_vars ) && CoursePress_Core::get_slug( 'student_dashboard' ) === $wp->query_vars['pagename'] ) {

			// Redirect to a page
			$vp = (int) CoursePress_Core::get_setting( 'pages/student_dashboard', 0 );
			if ( ! empty( $vp ) ) {
				wp_redirect( get_permalink( $vp ) );
				exit;
			}

			// $username = sanitize_text_field( $wp->query_vars['instructor_username'] );
			// $instructor = CoursePress_Data_Instructor::instructor_by_login( $username );
			// if ( empty( $instructor ) ) {
			// $instructor = CoursePress_Data_Instructor::instructor_by_hash( $username );
			// }
			$content = '';
			// if ( empty( $instructor ) ) {
			// $content = __( 'The requested instuctor does not exists', CoursePress::TD );
			// }
			//
			// self::$last_instructor = empty( $instructor ) ? 0 : $instructor->ID;
			// $page_title = ! empty( self::$last_instructor ) ? CoursePress_Helper_Utility::get_user_name( self::$last_instructor, false, false ) : __( 'Instructor not found.', CoursePress::TD );
			$page_title = __( 'My Courses', CoursePress::TD );

			$args = array(
				'slug' => CoursePress_Core::get_slug( 'student_dashboard' ),
				'title' => esc_html( $page_title ),
				// 'show_title' => false,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_dashboard_page(),
				'type' => 'coursepress_student_dashboard',
			);

			$pg = new CoursePress_Data_VirtualPage( $args );

			return;

		}
	}
}
