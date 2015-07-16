<?php

class CoursePress_Core {

	public static $name;
	public static $version;
	public static $DEBUG = false;
	public static $plugin_path;
	public static $plugin_url;
	public static $plugin_lib_path;
	public static $plugin_lib_url;
	public static $plugin_lib = 'coursepress-files';
	public static $plugin_file;
	private static $_settings = array();
	private static $config_data;

	public static function init() {

		// Initializing CoursePress_Core

		// Languages
		//load_plugin_textdomain( SNAPSHOT_I18N_DOMAIN, false, self::$plugin_path . self::$plugin_lib . '/languages/' );

		//self::$_settings['options_key'] = "wpmudev_snapshot";
		// add_action('plugins_loaded', array( __CLASS__, 'plugin_activation' ) );

		//add_action( 'init', array( __CLASS__, 'init_process' ) );
		//add_action( 'admin_init', array( __CLASS__, 'admin_init_process' ) );

		// Initialise Capabilities
		CoursePress_Model_Capabilities::init();

		/**
		 * Initialise CoursePress Post Formats (post types not available until after WordPress 'init' action)
		 *
		 * Custom Post Types can be prefixed by setting COURSEPRESS_CPT_PREFIX in wp-config.php.
		 * Warning: Doing this will make previous courses inaccessible. Do this early if you want
		 * to use a custom prefix.
		 */
		CoursePress_Model_PostFormats::init();
		add_filter( 'coursepress_post_formats', array( __CLASS__, 'register_formats' ) );

		// Initialise JavaScript Object Helper
		CoursePress_Helper_JavaScript::init();

		if( is_admin() ) {
			// Initialize Admin Settings
			CoursePress_Helper_Settings::init();

			// Initialize Admin Views
			CoursePress_View_Admin_CoursePress::init();
			CoursePress_View_Admin_Settings::init();

		}

		// Upgrade CoursePress if needed
		CoursePress_Upgrade::init();

	}


	public static function get_network_setting( $key, $default = null ) {
		return self::get_setting( $key, $default, is_multisite() );
	}


	public static function get_setting( $key, $default = null, $network = false ) {

		if( false === $network ) {
			$settings = get_option( 'coursepress_settings' );
		} else {
			$settings = get_site_option( 'coursepress_settings' );
		}

		// Return all settings
		if( empty( $key ) ) {
			return $settings;
		}

		$setting = CoursePress_Helper_Utility::get_array_val( $settings, $key );
		$setting = is_null( $setting ) ? $default : $setting;
		$setting = !is_array( $setting ) ? trim( $setting ) : $setting;

		return $setting;
	}

	public static function merge_settings( $settings_old, $settings_new ) {
		return CoursePress_Helper_Utility::merge_distinct( $settings_old, $settings_new );
	}

	public static function update_network_setting( $key, $value ) {
		return self::update_setting( $key, $value, is_multisite() );
	}

	public static function update_setting( $key, $value, $network = false ) {

		if( false === $network ) {
			$settings = get_option( 'coursepress_settings' );
		} else {
			$settings = get_site_option( 'coursepress_settings' );
		}

		if( ! empty( $key ) ) {
			// Replace only one setting
			CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
		} else {
			// Replace all settings
			$settings = $value;
		}

		if( false === $network ) {
			return update_option( 'coursepress_settings', $settings );
		} else {
			return update_site_option( 'coursepress_settings', $settings );
		}
	}

	public static function get_slug( $context, $url = false ) {

		$default_slug = '';
		$option = '';

		switch( $context ) {
			case 'course':
				$default_slug = 'courses';
				$option = 'slugs/course';
				break;
		}


		if( ! empty( $default_slug ) && ! empty( $option ) ) {

			if ( ! $url ) {
				return self::get_setting( $option, $default_slug );
			} else {
				return home_url() . '/' . get_option( $option, $default_slug );
			}

		}


	}

	public static function register_formats( $formats ) {
		return array_merge( $formats, array(
			'Course',
			'Unit',
			'Module',
		) );
	}

	public static function upgrade() {

	}


	public static function test() {

		//registration_from_name
		//registration_from_email
		//registration_email_subject
		//enrollment_from_name
		//enrollment_from_email
		//enrollment_email_subject
		//mp_order_from_name
		//mp_order_from_email
		//mp_order_email_subject
		//invitation_from_name
		//invitation_from_email
		//invitation_email_subject
		//invitation_passcode_from_name
		//invitation_passcode_from_email
		//invitation_passcode_email_subject
		//instructor_invitation_from_name
		//instructor_invitation_from_email
		//instructor_invitation_email_subject
		//details_media_type
		//details_media_priority
		//listings_media_type
		//listings_media_priority
		//course_order_by
		//course_order_by_type
		//reports_font
		//coursepress_course_slug
		//coursepress_course_category_slug
		//coursepress_units_slug
		//coursepress_notifications_slug
		//coursepress_discussion_slug
		//coursepress_discussion_slug_new
		//coursepress_grades_slug
		//coursepress_workbook_slug
		//enrollment_process_slug
		//login_slug
		//signup_slug
		//student_dashboard_slug
		//student_settings_slug
		//instructor_profile_slug
		//coursepress_inbox_slug
		//coursepress_sent_messages_slug
		//coursepress_new_message_slug
		//show_instructor_username
		//course_image_width
		//course_image_height
		//show_tos
		//use_woo
		//redirect_woo_to_course


		$settings = array();

		//$settings['email'] = array();
		//$settings['email']['registration'] = array();
		//$settings['email']['registration']['from'];
		//$settings['email']['registration']['email'];
		//$settings['email']['registration']['subject'];
		//$settings['email']['registration']['content'];
		//$settings['email']['enrollment_confirm'] = array();
		//$settings['email']['enrollment_confirm']['from'];
		//$settings['email']['enrollment_confirm']['email'];
		//$settings['email']['enrollment_confirm']['subject'];
		//$settings['email']['enrollment_confirm']['content'];
		//$settings['email']['course_invitation'] = array();
		//$settings['email']['course_invitation']['from'];
		//$settings['email']['course_invitation']['email'];
		//$settings['email']['course_invitation']['subject'];
		//$settings['email']['course_invitation']['content'];
		//
		//$settings['email']['course_invitation_password'] = array();
		//$settings['email']['course_invitation_password']['from'];
		//$settings['email']['course_invitation_password']['email'];
		//$settings['email']['course_invitation_password']['subject'];
		//$settings['email']['course_invitation_password']['content'];
		//
		//$settings['email']['instructor_invitation'] = array();
		//$settings['email']['instructor_invitation']['from'];
		//$settings['email']['instructor_invitation']['email'];
		//$settings['email']['instructor_invitation']['subject'];
		//$settings['email']['instructor_invitation']['content'];
		//
		//$settings['email']['new_order'] = array();
		//$settings['email']['new_order']['from'];
		//$settings['email']['new_order']['email'];
		//$settings['email']['new_order']['subject'];
		//$settings['email']['new_order']['content'];

		//$settings['course'] = array();
		//$settings['course']['details_media_type'];
		//$settings['course']['details_media_priority'];
		//$settings['course']['listing_media_type'];
		//$settings['course']['listing_media_priority'];
		//$settings['course']['order_by'];
		//$settings['course']['order_by_direction'];
		//$settings['course']['image_width'];
		//$settings['course']['image_height'];

		//$settings['reports'] = array();
		//$settings['reports']['font'];

		//$settings['slugs'] = array();
		//$settings['slugs']['course'];
		//$settings['slugs']['category'];
		//$settings['slugs']['units'];
		//$settings['slugs']['notifications'];
		//$settings['slugs']['discussions'];
		//$settings['slugs']['discussions_new'];
		//$settings['slugs']['grades'];
		//$settings['slugs']['workbook'];
		//$settings['slugs']['enrollment'];
		//$settings['slugs']['login'];
		//$settings['slugs']['signup'];
		//$settings['slugs']['student_dashboard'];
		//$settings['slugs']['student_settings'];
		//$settings['slugs']['instructor_profile'];
		//$settings['slugs']['inbox'];
		//$settings['slugs']['sent_messages'];
		//$settings['slugs']['new_messages'];
		//$settings['slugs']['enrollment'];

		//$settings['pages'] = array();
		//$settings['pages']['enrollment'];
		//$settings['pages']['login'];
		//$settings['pages']['signup'];
		//$settings['pages']['student_dashboard'];
		//$settings['pages']['student_settings'];

		//$settings['general'] = array();
		//$settings['general']['show_coursepress_menu'];
		//$settings['general']['use_custom_login'];
		//$settings['general']['redirect_after_login'];
		//$settings['general']['version'];

		//$settings['basic_certificate'] = array();
		//$settings['basic_certificate']['enabled'];
		//$settings['basic_certificate']['content'];
		//$settings['basic_certificate']['background_image'];
		//$settings['basic_certificate']['padding']['top'];
		//$settings['basic_certificate']['padding']['bottom'];
		//$settings['basic_certificate']['padding']['left'];
		//$settings['basic_certificate']['padding']['right'];
		//$settings['basic_certificate']['orientation'];

		//$settings['instructor'] = array();
		//$settings['instructor']['show_username'];
		//$settings['instructor']['capabilities'];

		//$settings['woo'] = array();
		//$settings['woo']['use'];
		//$settings['woo']['redirect_to_course'];
		//
		//$settings['tos'] = array();
		//$settings['tos']['use'];


	}





}