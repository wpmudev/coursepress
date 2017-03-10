<?php
/**
 * Class to helps with the front
 *
 * @since 2.0.6
 *
 */
class CoursePress_Helper_Front {
	/**
	 * Check redirect
	 *
	 * @since 2.0.6
	 *
	 * @param string $html HTML to show.
	 * @param array $args Configuration args.
	 */
	public static function check_and_redirect( $key, $redirect_not_logged = true ) {
		global $wp;
		if ( ! array_key_exists( 'pagename', $wp->query_vars ) ) {
			return false;
		}
		if ( CoursePress_Core::get_slug( $key ) != $wp->query_vars['pagename'] ) {
			return false;
		}
		/**
		 * redirect non logged
		 */
		if ( $redirect_not_logged && ! is_user_logged_in() ) {
			$url = wp_login_url();
			$use_custom = cp_is_true( CoursePress_Core::get_setting( 'general/use_custom_login', 1 ) );
			if ( $use_custom ) {
				$login_page = CoursePress_Core::get_setting( 'pages/login', false );
				if ( empty( $login_page ) ) {
					$url = CoursePress_Core::get_slug( 'login', true );
				} else {
					$url = get_permalink( (int) $login_page );
				}
			}
			wp_safe_redirect( $url );
			exit;
		}
		/**
		 *  Redirect to a page if it is nessary
		 */
		$vp = (int) CoursePress_Core::get_setting( 'pages/'.$key, 0 );
		if ( ! empty( $vp ) ) {
			$post = get_post( $vp );
			if ( $post->post_name != $wp->query_vars['pagename'] ) {
				wp_redirect( get_permalink( $vp ) );
				exit;
			}
		}
		return true;
	}
}
