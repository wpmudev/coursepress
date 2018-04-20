<?php
/**
 * This class is responsible for CoursePress upgrade process.
 *
 * @since 2.0
 *
 * @package WordPress
 * @subpackage CoursePress
 */
class CoursePress_Upgrade_1x_Data {
	/** @var (string) The upgrade version. **/
	private static $version = '2.0.0';

	/**
	 *
	 */
	public static function init() {
		// Listen to upgrade call
		add_action( 'wp_ajax_coursepress_upgrade_from_1x', array( __CLASS__, 'ajax_courses_upgrade' ) );

		// Include our upgrade assets
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'upgrade_assets' ) );

		// Set upgrade page
		add_action( 'admin_menu', array( __CLASS__, 'set_upgrade_page' ) );

		// Notify the user the need for Upgrade!
		add_action( 'admin_notices', array( __CLASS__, 'upgrade_notice' ) );

		if ( '2.0' != CoursePressUpgrade::$coursepress_version && ! is_admin() ) {
			add_filter( 'coursepress_get_setting', array( __CLASS__, 'pre_settings' ), 999, 3 );
			add_filter( 'coursepress_virtual_page', array( __CLASS__, 'maintenance_page' ), 999 );
		}
	}

	public static function pre_settings( $setting, $key, $cp_settings ) {
		$defaults = array(
			'general' => array(
				'show_coursepress_menu' => get_option( 'display_menu_items', 1 ),
				'use_custom_login' => get_option( 'use_custom_login_form', 1 ),
			),
			'slugs' => array(
				'course' => get_option( 'coursepress_course_slug', 'courses' ),
				'category' => get_option( 'coursepress_course_category_slug', 'course_category' ),
				'module' => get_option( 'coursepress_module_slug', 'module' ),
				'units' => get_option( 'coursepress_units_slug', 'units' ),
				'notifications' => get_option( 'coursepress_notifications_slug', 'notifications' ),
				'discussions' => get_option( 'coursepress_discussion_slug', 'discussion' ),
				'grades' => get_option( 'coursepress_grades_slug', 'grades' ),
				'workbook' => get_option( 'coursepress_workbook_slug', 'workbook' ),
				'enrollment' => get_option( 'enrollment_process_slug', 'enrollment_process' ),
				'student_dashboard' => get_option( 'student_dashboard_slug', 'courses-dashboard' ),
				'student_settings' => get_option( 'student_settings_slug', 'student-settings' ),
				'instructor_profile' => get_option( 'instructor_profile_slug', 'instructor' ),
			),
			'pages' => array(
				'enrollment' => get_option( 'coursepress_enrollment_process_page', 0 ),
				'login' => get_option( 'coursepress_login_page', 0 ),
				'student_dashboard' => get_option( 'coursepress_signup_page', 0 ),
				'student_settings' => get_option( 'coursepress_student_settings_page', 0 ),
			),
		);

		if ( is_bool( $key ) ) {
			$setting = $defaults;
		} else {
			$setting = CoursePress_Helper_Utility::get_array_val( $defaults, $key );
		}

		return $setting;
	}

	public static function maintenance_page( $vp_args ) {
		global $wp;

		$show = false;
		$other_pages = (array) CoursePress_Core::get_setting( 'slugs' );
		$qvars = $wp->query_vars;
		$name = isset( $qvars['name'] ) ? $qvars['name'] : '';

		if ( ! $name && isset( $qvars['pagename'] ) ) {
			$name = $qvars['pagename']; }

		if ( ! empty( $vp_args ) || isset( $wp->query_vars['coursename'] ) ) {
			$show = true; } elseif ( ! empty( $name ) && in_array( $name, $other_pages ) ) {
			$show = true; }

			if ( $show ) {
				self::upgrade_assets();

				// Set custom page
				add_action( 'template_include', array( __CLASS__, 'preload' ) );

				// Set custom body class
				add_filter( 'body_class', array( __CLASS__, 'custom_upgrade_class' ) );
			}

			return $vp_args;
	}

	public static function preload() {

		$template = __DIR__ . '/page.php';

		return $template;
	}

	public static function custom_upgrade_class( $class ) {
		array_push( $class, 'cp-upgrade-body' );

		return $class;
	}

	public static function is_upgrade_page() {
		return ! empty( $_REQUEST['page'] ) && 'coursepress-upgrade' == $_REQUEST['page'];
	}

	public static function set_upgrade_page() {
		$upgrade = add_menu_page( __( 'CoursePress Upgrade', 'coursepress' ), __( 'CoursePress Upgrade', 'coursepress' ), 'manage_options', 'coursepress-upgrade', array( __CLASS__, 'get_upgrade_page' ) );

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

	public static function upgrade_notice( $classes = '' ) {
		$snapshot_pro = '//premium.wpmudev.org/project/snapshot/';
		$snapshot = sprintf( '<a href="%s" class="button-primary" target="_blank">%s</a>', $snapshot_pro, __( 'backup', 'coursepress' ) );
		$upgrade_view = add_query_arg( 'page', 'coursepress-upgrade', admin_url() );
		$upgrade = sprintf( '<a href="%s" class="button-primary">%s</a>', esc_url( $upgrade_view ), __( 'here', 'coursepress' ) );

		if ( current_user_can( 'install_plugins' ) ) {
			$message = '<p>' . sprintf( __( 'It looks like you had CoursePress 1 installed. In order to upgrade your course data to CoursePress 2, we strongly recommend you to %s your website before upgrading %s. Once the upgrade is complete you will be able to use CoursePress again.', 'coursepress' ), $snapshot, $upgrade ) . '</p>';
		} else {
			$message = '<p>' . __( 'This page is undergoing routine maintenance. Please try again later.', 'coursepress' );
		}

		// Remind the user to backup their system in upgrade page
		if ( self::is_upgrade_page() ) {
			$message = '<p>' . __( 'We strongly recommend that you backup your site before you start updating.', 'coursepress' ) . '</p>';
		}

		printf( '<div class="notice notice-warning is-dismissible coursepress-upgrade-nag %s">%s</div>', $classes, $message );
	}

	public static function upgrade_assets() {
		$host = WP_PLUGIN_URL . '/coursepress/upgrade/';

		// Include upgrade stylesheet
		wp_enqueue_style( 'coursepress-upgrade-style', $host . 'css/upgrade.css', array(), self::$version );

		// Include upgrade.js
		$script = $host . 'js/admin-upgrade.js';
		wp_enqueue_script( 'coursepress_admin_upgrade_js', $script, array( 'jquery', 'backbone', 'underscore' ), self::$version, true );

		$cp_url = admin_url( 'edit.php?post_type=course' );
		$cp_url = sprintf( '<a href="%s" class="cp2-button">%s</a>', esc_url( $cp_url ), __( 'here', 'coursepress' ) );
		$localize_array = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( 'coursepress-upgrade-nonce' ),
			'flush_nonce' => wp_create_nonce( 'cp2_flushed' ),
			'settings_nonce' => wp_create_nonce( 'coursepress-settings' ),
			'server_error' => __( 'An error occur while updating. Please contact your administrator to fix the problem.', 'coursepress' ),
			'noloading' => __( 'Please refrain from reloading the page while updating!', 'coursepress' ),
			'failed' => __( 'Update unsuccessful. Please try again!', 'coursepress' ),
			'success' => sprintf( __( 'Hooray! Update completed. Redirecting in %1$s. If you are not redirected in 5 seconds click %2$s.', 'coursepress' ),  '<span class="coursepress-counter">5</span>', $cp_url ),
			'cp2_url' => admin_url( 'edit.php?post_type=course' ),
			'upgrading_students' => __( 'Please wait while we upgrade and verify the student data. Students yet to be upgraded:' ),
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

			// Include CoursePress 2.0 in just this ajax call so that some migration functions will work
			$cp_2_0 = dirname( dirname( __FILE__ ) ) . '/2.0/coursepress.php';
			require_once $cp_2_0;

			CoursePress_Core::init();

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
						$success = CoursePress_Helper_Upgrade_1x_Data::update_course( $course_id );
					}
					break;

				case 'flush':
					update_option( 'coursepress_20_upgraded', true );
					delete_option( 'cp2_flushed' );
					$success = true;
					break;

				case 'check-students':
					$success = true;
					$remaining_students = CoursePress_Helper_Upgrade_1x_Data::get_all_remaining_students();
					if ( $remaining_students > 0 ) {
						CoursePress_Helper_Upgrade_1x_Data::update_course_students_progress();
					}

					$ok = wp_parse_args(
						$ok,
						array(
							'remaining_students' => CoursePress_Helper_Upgrade_1x_Data::get_all_remaining_students(),
						)
					);

					if ( (int) $ok['remaining_students'] <= 0 ) {
						update_option( 'coursepress_20_upgraded', true );
						delete_option( 'cp2_flushed' );
					}
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
