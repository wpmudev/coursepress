<?php

class CoursePress_View_Front_Login {

	public static $title = ''; // The page title

	public static function init() {
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );
	}

	/**
	 * @todo: Why is this commented? Find out and finish function is needed!
	 */
	public static function render_login_page() {
		// if ( $theme_file = locate_template( array( 'instructor-single.php' ) ) ) {
		// } else {
		// wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
		// if ( locate_template( array( 'instructor-single.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
		// just output the content
		// } else {
		$content = CoursePress_Template_Dashboard::render_login_page();

		// }
		// }
		return $content;
	}

	/**
	 * @todo: Why is this commented? Find out and finish function is needed!
	 */
	public static function render_signup_page() {
		// if ( $theme_file = locate_template( array( 'instructor-single.php' ) ) ) {
		// } else {
		// wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
		// if ( locate_template( array( 'instructor-single.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
		// just output the content
		// } else {
		$content = CoursePress_Template_Dashboard::render_signup_page();

		// }
		// }
		return $content;
	}


	public static function parse_request( &$wp ) {
		// Login Page
		if ( array_key_exists( 'pagename', $wp->query_vars ) && CoursePress_Core::get_slug( 'login' ) == $wp->query_vars['pagename'] ) {

			// Redirect to a page
			$vp = (int) CoursePress_Core::get_setting( 'pages/login', 0 );
			if ( ! empty( $vp ) ) {
				wp_redirect( get_permalink( $vp ) );
				exit;
			}

			$content = '';
			$page_title = __( 'Student Login', 'CP_TD' );

			$args = array(
				'slug' => CoursePress_Core::get_slug( 'login' ),
				'title' => esc_html( $page_title ),
				// 'show_title' => false,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_login_page(),
				'type' => 'coursepress_student_login',
			);

			$pg = new CoursePress_Data_VirtualPage( $args );

			return;
		}

		// Signup Page
		if ( array_key_exists( 'pagename', $wp->query_vars ) && CoursePress_Core::get_slug( 'signup' ) == $wp->query_vars['pagename'] ) {

			// Redirect to a page
			$vp = (int) CoursePress_Core::get_setting( 'pages/signup', 0 );
			if ( ! empty( $vp ) ) {
				wp_redirect( get_permalink( $vp ) );
				exit;
			}

			$content = '';
			$page_title = __( 'New Signup', 'CP_TD' );

			$args = array(
				'slug' => CoursePress_Core::get_slug( 'signup' ),
				'title' => esc_html( $page_title ),
				// 'show_title' => false,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_signup_page(),
				'type' => 'coursepress_student_signup',
			);

			$pg = new CoursePress_Data_VirtualPage( $args );

			return;
		}
	}

	public static function render_student_signup_page() {

		if ( is_user_logged_in() ) {
			_e( 'You are already logged in.', 'CP_TD' );
			return;
		}

		$redirect_url = '';
		if ( ! empty( $_REQUEST['redirect_url'] ) ) {
			$redirect_url = $_REQUEST['redirect_url'];
		}
		echo do_shortcode(
			sprintf(
				'[course_signup page="signup" signup_title="" redirect_url="%s" signup_url="%s" login_url="%s"]',
				$redirect_url,
				CoursePress_Core::get_slug( 'signup', true ),
				cp_student_login_address()
			)
		);

	}

	public static function render_student_login_page() {

		if ( is_user_logged_in() ) {
			_e( 'You are already logged in.', 'CP_TD' );
			return;
		}

		$redirect_url = '';
		if ( ! empty( $_REQUEST['redirect_url'] ) ) {
			$redirect_url = $_REQUEST['redirect_url'];
		}
		echo do_shortcode(
			sprintf(
				'[course_signup page="login" login_title="" redirect_url="%s" signup_url="%s" login_url="%s"]',
				$redirect_url,
				CoursePress_Core::get_slug( 'signup', true ),
				cp_student_login_address()
			)
		);

	}
}
