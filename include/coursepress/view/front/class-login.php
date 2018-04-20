<?php

class CoursePress_View_Front_Login {

	public static $title = ''; // The page title

	public static function init() {
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );
		add_action( 'wp_login', array( __CLASS__, 'log_student_activity_login' ), 10, 2 );
	}

	/**
	 * @todo: Why is this commented? Find out and finish function is needed!
	 */
	public static function render_login_page() {
		$content = CoursePress_Template_Dashboard::render_login_page();
		return $content;
	}


	public static function parse_request( &$wp ) {
			$check = CoursePress_Helper_Front::check_and_redirect( 'login', false );
		if ( ! $check ) {
			return;
		}
			$content = '';
			$page_title = __( 'Student Login', 'coursepress' );
			$args = array(
				'slug' => CoursePress_Core::get_slug( 'login' ),
				'title' => esc_html( $page_title ),
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_login_page(),
				'type' => 'coursepress_student_login',
			);
			$pg = new CoursePress_Data_VirtualPage( $args );
	}

	public static function render_student_login_page() {

		if ( is_user_logged_in() ) {
			_e( 'You are already logged in.', 'coursepress' );
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
	 * Save student activity - login
	 *
	 * @since 2.0.0
	 */
	public static function log_student_activity_login( $user_login, $user ) {
		CoursePress_Data_Student::log_student_activity( 'login', $user->ID );
	}
}
