<?php
/**
 * Upgrade class.
 *
 * This class is used during upgrade process.
 *
 * @since 2.0.0
 *
 */
class CoursePress_View_Admin_Upgrade {

	private static $slug = 'coursepress_upgrade';
	private static $title = '';
	private static $menu_title = '';

	public static function render_page() {

		$courses_ids = CoursePress_Helper_Upgrade::upgrade_get_courses_list();

		echo ' <div class="wrap">';
		printf( '<h1>%s</h1>', self::$menu_title );

		$count = count( $courses_ids );
		if ( 0 == $count ) {
			delete_option( 'coursepress_courses_need_update' );
			printf(
				'<p>%s</p>',
				__( 'There is no courses to update.', 'CP_TD' )
			);
			return;
		}
		/**
		 * Flush rewrites
		 */
		flush_rewrite_rules();
		/**
		 * Comunicate how many courses we have to upgrade.
		 */
		printf(
			'<p>%s</p>',
			sprintf( _n( 'You have %d course to update.', 'You have %d courses to update.', $count, 'CP_TD' ), $count )
		);

		$labels = array(
			'working' => __( 'Working...', 'CP_TD' ),
			'empty-list' => __( 'There is no courses to update!', 'CP_TD' ),
			'done' => __( 'Upgrade is done.', 'CP_TD' ),
			'fail' => __( 'Something went wrong.', 'CP_TD' ),
		);

		echo '<div id="coursepress-updater-holder">';
		echo '<form id="coursepress-update-courses-form"';
		foreach ( $labels as $key => $label ) {
			printf( ' data-label-%s="%s"', esc_attr( $key ), esc_attr( $label ) );
		}
		echo '>';
		printf( '<input type="hidden" value="%d" name="user_id" />', esc_attr( get_current_user_id() ) );
		printf( '<input type="hidden" value="%d" name="course" />', esc_attr( $courses_ids[0] ) );
		$nonce_name = CoursePress_Helper_Upgrade::get_update_nonce();
		wp_nonce_field( $nonce_name );
		submit_button( __( 'Beginning update!', 'CP_TD' ) );
		echo '</form></div>';
		echo '</div>';
	}

	/**
	 * Get page slug
	 */
	public static function get_slug() {
		return self::$slug;
	}

	/**
	 * Enqueue script, but only on upgrade page.
	 */
	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();
		$re = sprintf( '/_page_%s$/', self::$slug );
		if ( ! preg_match( $re, $screen->id ) ) {
			return;
		}
		$script = CoursePress::$url . 'asset/js/admin-upgrade.js';
		wp_enqueue_script( 'coursepress_admin_upgrade_js', $script, array( 'jquery' ), CoursePress::$version, true );
	}
}

