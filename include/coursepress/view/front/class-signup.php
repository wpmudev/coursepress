<?php
/**
 * Handle signup form
 *
 * @since 2.0.6
 */

class CoursePress_View_Front_Signup {

	/**
	 * init function
	 *
	 * @since 2.0.6
	 */
	public static function init() {
		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );
	}

	/**
	 * render signup page function
	 *
	 * @since 2.0.6
	 */
	public static function render_signup_page() {
		$content = CoursePress_Template_Dashboard::render_signup_page();
		return $content;
	}

	/**
	 * Parse request to show signup page.
	 *
	 * @since 2.0.6
	 */
	public static function parse_request( &$wp ) {
		$check = CoursePress_Helper_Front::check_and_redirect( 'signup', false );
		if ( ! $check ) {
			return;
		}
		$content = '';
		$page_title = __( 'New Signup', 'coursepress' );
		$args = array(
			'slug' => CoursePress_Core::get_slug( 'signup' ),
			'title' => esc_html( $page_title ),
			'content' => ! empty( $content ) ? esc_html( $content ) : self::render_signup_page(),
			'type' => 'coursepress_student_signup',
		);
		$pg = new CoursePress_Data_VirtualPage( $args );
	}

	/**
	 * render page
	 *
	 * @since 2.0.6
	 */
	public static function render_student_signup_page() {
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
				'[course_signup page="signup" signup_title="" redirect_url="%s" signup_url="%s" login_url="%s"]',
				$redirect_url,
				CoursePress_Core::get_slug( 'signup', true ),
				cp_student_login_address()
			)
		);

	}
}
