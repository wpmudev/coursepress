<?php

class CoursePress_View_Front_Login {

	public static $title = ''; // The page title

	public static function init() {
		add_filter( 'the_content', array( __CLASS__, 'the_content_login_page' ) );
		add_filter( 'the_content', array( __CLASS__, 'the_content_signup_page' ) );
	}

	public static function render_login_page() {
		$content = CoursePress_Template_Dashboard::render_login_page();
		return $content;
	}

	public static function render_signup_page() {
		$content = CoursePress_Template_Dashboard::render_signup_page();
		return $content;
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

	/**
	 * Display login page.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post * $post The WP_Post object.

	 * @param string $content Current entry content.
	 *
	 * @return string Current entry content.
	 */

	public static function the_content_login_page( $content ) {
		/**
		 * we do not need change other post type than page
		 */
		if ( ! is_page() ) {
			return $content;
		}
		/**
		 * check setup is pages/student_settings a page?
		 */
		$student_settings_page_id = CoursePress_Core::get_setting( 'pages/login', 0 );
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
		$content .= self::render_login_page();
		return $content;
	}

	/**
	 * Display signup page.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post * $post The WP_Post object.

	 * @param string $content Current entry content.
	 *
	 * @return string Current entry content.
	 */

	public static function the_content_signup_page( $content ) {
		/**
		 * we do not need change other post type than page
		 */
		if ( ! is_page() ) {
			return $content;
		}
		/**
		 * check setup is pages/student_settings a page?
		 */
		$student_settings_page_id = CoursePress_Core::get_setting( 'pages/signup', 0 );
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
		$content .= self::render_signup_page();
		return $content;
	}
}
