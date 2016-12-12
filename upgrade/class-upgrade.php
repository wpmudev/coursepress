<?php
/**
 * This class is responsible for CoursePress upgrade process.
 *
 * @since 2.0
 *
 * @package WordPress
 * @subpackage CoursePress
 */
class CoursePress_Upgrade {
	/** @var (string) The upgrade version. **/
	private static $version = '2.0.0';

	public static function init() {
		// Listen to upgrade call
		add_action( 'wp_ajax_coursepress_upgrade_update', array( __CLASS__, 'ajax_courses_upgrade' ) );

		// Include our upgrade assets
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'upgrade_assets' ) );

		// Set upgrade page
		add_action( 'admin_menu', array( __CLASS__, 'set_upgrade_page' ) );

		// Notify the user the need for Upgrade!
		add_action( 'admin_notices', array( __CLASS__, 'upgrade_notice' ) );
	}

	public static function is_upgrade_page() {
		return ! empty( $_REQUEST['page'] ) && 'coursepress-upgrade' == $_REQUEST['page'];
	}

	public static function set_upgrade_page() {
		$upgrade = add_menu_page( __( 'CoursePress Upgrade', 'cp' ), __( 'CoursePress Upgrade', 'cp' ), 'manage_options', 'coursepress-upgrade', array( __CLASS__, 'get_upgrade_page' ) );

		add_action( "load-{$upgrade}", array( __CLASS__, 'before_upgrade_page' ) );
	}

	public static function before_upgrade_page() {
		// Remove all notices except CP2
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Notify the user the need to backup
		add_action( 'admin_notices', array( __CLASS__, 'upgrade_notice' ) );
	}

	public static function get_upgrade_page() {
		$upgrade_file = dirname( __FILE__ ) . '/upgrade-view.php';
		require_once $upgrade_file;
	}

	public static function upgrade_notice() {
		$snapshot_pro = '//premium.wpmudev.org/project/snapshot/';
		$snapshot = sprintf( '<a href="%s" class="button-primary" target="_blank">%s</a>', $snapshot_pro, __( 'backup', 'cp' ) );
		$upgrade_view = add_query_arg( 'page', 'coursepress-upgrade', admin_url() );
		$upgrade = sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $upgrade_view ), __( 'here', 'cp' ) );

		$message = '<p>' . sprintf( __( 'It looks like you had CoursePress 1 installed. In order to upgrade your course data to CoursePress 2, we strongly recommend you to %s your website before upgrading %s.', 'cp' ), $snapshot, $upgrade ) . '</p>';

		// Remind the user to backup their system in upgrade page
		if ( self::is_upgrade_page() ) {
			$message = '<p>' . __( 'We strongly recommend that you backup your site before you start updating.', 'cp' ) . '</p>';
		}

		printf( '<div class="notice notice-warning is-dismissible coursepress-upgrade-nag">%s</div>', $message );
	}

	public static function upgrade_assets() {
		$host = WP_PLUGIN_URL . '/coursepress/upgrade/';

		// Include upgrade stylesheet
		wp_enqueue_style( 'coursepress-upgrade-style', $host . 'css/upgrade.css', array(), self::$version );

		// Include upgrade.js
		$script = $host . 'js/admin-upgrade.js';
		wp_enqueue_script( 'coursepress_admin_upgrade_js', $script, array( 'jquery', 'backbone', 'underscore' ), self::$version, true );

		$cp_url = admin_url( 'edit.php?post_type=course');
		$cp_url = sprintf( '<a href="%s" class="cp2-button">%s</a>', esc_url( $cp_url ), __( 'here', 'cp' ) );
		$localize_array = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( 'coursepress-upgrade-nonce' ),
			'flush_nonce' => wp_create_nonce( 'cp2_flushed' ),
			'settings_nonce' => wp_create_nonce( 'coursepress-settings' ),
			'server_error' => __( 'An error occur while updating. Please contact your administrator to fix the problem.', 'cp' ),
			'noloading' => __( 'Please refrain from reloading the page while updating!', 'cp' ),
			'failed' => __( 'Update unsuccessful. Please try again!', 'cp' ),
			'success' => sprintf( __( 'Hooray! Update completed. Redirecting in %1$s. If you are not redirected in 5 seconds click %2$s.', 'cp' ),  '<span class="coursepress-counter">5</span>', $cp_url ),
			'cp2_url' => admin_url( 'edit.php?post_type=course' ),
		);
		wp_localize_script( 'coursepress_admin_upgrade_js', '_coursepress_upgrade', $localize_array );
	}

	public static function ajax_courses_upgrade() {
		$request = json_decode( file_get_contents( 'php://input' ) );

		if ( ! isset( $request->type ) || empty( $request->type ) ) {
			die();
		}
		if ( ! isset( $request->course_id ) || empty( $request->course_id ) ) {
			die();
		}

		if ( ! empty( $request->_wpnonce ) && wp_verify_nonce( $request->_wpnonce, 'coursepress-upgrade-nonce' ) ) {
			// include required classes
			$update_class = dirname( __FILE__ ) . '/class-helper-upgrade.php';
			require $update_class;

			// variables
			$type = $request->type;
			$ok = array( 'success' => true );
			$not_ok = array( 'success' => false );
			$success = false;

			preg_match_all( '!\d+!', $request->course_id, $course_id_matches );
			$course_id = (int) implode( '', $course_id_matches[0] );

			switch ( $type ) {
				case 'course':
					if ( $course_id ) {
						$success = CoursePress_Helper_Upgrade::update_course( $course_id );
					}
					break;

				case 'flush':
					update_option( 'coursepress_20_upgraded', true );
					delete_option( 'cp2_flushed' );
					$success = true;
					break;
			}

			// response
			if ( $success && ! is_wp_error( $success ) ) {
				wp_send_json_success( $ok );
			} else {
				wp_send_json_error( $not_ok );
			}
			exit;
		}
	}
}
