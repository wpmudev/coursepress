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

		// Initialize Capabilities
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

		// Add query vars
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );

		// Initialise the rewrite tules
		add_filter( 'rewrite_rules_array', array( __CLASS__, 'add_rewrite_rules' ) );

		// Initialize JavaScript Object Helper
		CoursePress_Helper_JavaScript::init();

		// Initialize Plugin Integrations
		CoursePress_Helper_Integration::init();


		if ( is_admin() ) {
			// Initialize Admin Settings
			CoursePress_Helper_Settings::init();

			// Initialize Admin Views
			CoursePress_View_Admin_CoursePress::init();
			CoursePress_View_Admin_Communication::init();
			CoursePress_View_Admin_Settings::init();


			// Admin AJAX
			CoursePress_View_Front_Course::init_ajax();

		} else {
			// Init shortcodes
			CoursePress_Model_Shortcodes::init();

			// Now we're in the front
			CoursePress_View_Front_General::init();
			CoursePress_View_Front_Course::init();
			CoursePress_View_Front_Instructor::init();
			CoursePress_View_Front_Dashboard::init();
			CoursePress_View_Front_Login::init();

		}

		// Initialize Utility actions
		CoursePress_Helper_Utility::init();

		// Init Module hooks
		CoursePress_Model_Module::module_init_hooks();

		// Upgrade CoursePress if needed
		CoursePress_Upgrade::init();

	}

	public static function get_network_setting( $key, $default = null ) {
		return self::get_setting( $key, $default, is_multisite() );
	}


	public static function get_setting( $key, $default = null, $network = false ) {

		if ( false === $network ) {
			$settings = get_option( 'coursepress_settings' );
		} else {
			$settings = get_site_option( 'coursepress_settings' );
		}

		// Return all settings
		if ( empty( $key ) ) {
			return $settings;
		}

		$setting = CoursePress_Helper_Utility::get_array_val( $settings, $key );
		$setting = is_null( $setting ) ? $default : $setting;
		$setting = ! is_array( $setting ) ? trim( $setting ) : $setting;

		return $setting;
	}

	public static function merge_settings( $settings_old, $settings_new ) {
		$settings_old = ! empty( $settings_old ) && is_array( $settings_old ) ? $settings_old : array();
		$settings_new = ! empty( $settings_new ) && is_array( $settings_new ) ? $settings_new : array();
		return CoursePress_Helper_Utility::merge_distinct( $settings_old, $settings_new );
	}

	public static function update_network_setting( $key, $value ) {
		return self::update_setting( $key, $value, is_multisite() );
	}

	public static function update_setting( $key, $value, $network = false ) {

		$x = '';
		if ( false === $network ) {
			$settings = get_option( 'coursepress_settings' );
		} else {
			$settings = get_site_option( 'coursepress_settings' );
		}

		if ( ! empty( $key ) ) {
			// Replace only one setting
			CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
		} else {
			// Replace all settings
			$settings = $value;
		}

		if ( false === $network ) {
			return update_option( 'coursepress_settings', $settings );
		} else {
			return update_site_option( 'coursepress_settings', $settings );
		}
	}

	public static function get_slug_array() {
		return apply_filters( 'coursepress_slug_array', array(

			'course'            => array(
				'default' => 'courses',
				'option'  => 'slugs/course'
			),
			'category'          => array(
				'default' => 'course_category',
				'option'  => 'slugs/category',
			),
			'module'            => array(
				'default' => 'module',
				'option'  => 'slugs/module'
			),
			'unit'              => array(
				'default' => 'units',
				'option'  => 'slugs/units'
			),
			'notification'      => array(
				'default' => 'notifications',
				'option'  => 'slugs/notifications'
			),
			'discussion'        => array(
				'default' => 'discussion',
				'option'  => 'slugs/discussions',
			),
			'discussion_new'    => array(
				'default' => 'add_new_discussion',
				'option'  => 'slugs/discussion_new'
			),
			'grade'             => array(
				'default' => 'grades',
				'option'  => 'slugs/grades',
			),
			'workbook'          => array(
				'default' => 'workbook',
				'option'  => 'slugs/workbook'
			),
			'enrollment'        => array(
				'default'     => 'enrollment_process',
				'option'      => 'slugs/enrollment',
				'page_option' => 'pages/enrollment'
			),
			'login'             => array(
				'default'     => 'student-login',
				'option'      => 'slugs/login',
				'page_option' => 'pages/login'
			),
			'signup'            => array(
				'default'     => 'courses-signup',
				'option'      => 'slugs/signup',
				'page_option' => 'pages/signup'
			),
			'student_dashboard' => array(
				'default'     => 'courses-dashboard',
				'option'      => 'slugs/student_dashboard',
				'page_option' => 'pages/student_dashboard'
			),
			'student_settings'  => array(
				'default'     => 'student-settings',
				'option'      => 'slugs/student_settings',
				'page_option' => 'pages/student_settings'
			),
			'instructor'        => array(
				'default' => 'instructor',
				'option'  => 'slugs/instructor_profile'
			),
			'inbox'             => array(
				'default' => 'student-inbox',
				'option'  => 'slugs/inbox'
			),
			'messages_sent'     => array(
				'default' => 'student-sent-messages',
				'option'  => 'slugs/sent_messages'
			),
			'messages_new'      => array(
				'default' => 'student-new-message',
				'option'  => 'slugs/new_messages'
			),
		) );
	}

	public static function get_slug( $context, $url = false ) {

		$default_slug = '';
		$option       = '';

		$map      = array(
			'courses'         => 'course',
			'categories'      => 'category',
			'modules'         => 'module',
			'units'           => 'unit',
			'notifications'   => 'notification',
			'discussions'     => 'discussion',
			'discussions_new' => 'discussion_new',
			'grades'          => 'grade',
			'enrollments'     => 'enrollment',
			'instructors'     => 'instructor',
			'message_sent'    => 'messages_sent',
			'message_new'     => 'messages_new'
		);
		$map_keys = array_keys( $map );

		$context = in_array( $context, $map_keys ) ? $map[ $context ] : $context;

		$slug_array = self::get_slug_array();

		$options = $slug_array[ $context ];


		switch ( $context ) {
			case 'courses':
			case 'course':
				$default_slug = 'courses';
				$option       = 'slugs/course';
				break;

		}

		if ( ! empty( $options ) ) {

			if ( ! $url ) {
				return CoursePress_Core::get_setting( $options['option'], $options['default'] );
			} else {

				$custom = isset( $options['page_option'] ) ? CoursePress_Core::get_setting( $options['page_option'], 0 ) : 0;

				if ( ! empty( $custom ) ) {
					if ( empty( $GLOBALS['wp_rewrite'] ) ) {
						$GLOBALS['wp_rewrite'] = new WP_Rewrite();
					}
					$return_value = trailingslashit( get_permalink( (int) $custom ) );
				} else {
					$return_value = trailingslashit( home_url( trailingslashit( CoursePress_Core::get_setting( $options['option'], $options['default'] ) ) ) );
				}

				$page_option = isset( $options['page_option'] ) ? $options['page_option'] : '';
				return apply_filters( 'coursepress_slug_return', $return_value, $options[ 'option' ], $page_option, $options[ 'default' ], $url );
			}
		}

		return '';
	}


	public static function register_formats( $formats ) {
		return array_merge( $formats, array(
			'Course',
			'Unit',
			'Module',
			'Discussion',
			'Notification'
		) );
	}

	public static function upgrade() {

	}

	public static function add_query_vars( $query_vars ) {
		$query_vars[]	 = 'coursename';
		$query_vars[]	 = 'course_category';
		$query_vars[]	 = 'unitname';
		$query_vars[]	 = 'instructor_username';
		$query_vars[]	 = 'discussion_name';
		$query_vars[]	 = 'discussion_archive';
		$query_vars[]	 = 'notifications_archive';
		$query_vars[]	 = 'grades_archive';
		$query_vars[]	 = 'workbook';
		$query_vars[]	 = 'discussion_action';
		$query_vars[]	 = 'inbox';
		$query_vars[]	 = 'new_message';
		$query_vars[]	 = 'sent_messages';
		$query_vars[]	 = 'paged';
		$query_vars[]	 = 'course';
		$query_vars[]	 = 'unit';
		$query_vars[]	 = 'type';
		$query_vars[]	 = 'item';
		$query_vars[]	 = 'coursepress_focus';
		//$query_vars[]	 = 'focus_course';
		//$query_vars[]	 = 'focus_unit';
		//$query_vars[]	 = 'focus_type';
		//$query_vars[]	 = 'focus_item';

		return $query_vars;
	}



	public static function add_rewrite_rules( $rules ) {
		$new_rules = array();

		// Special Rules for CoursePress Focus mode
		$new_rules[ '^coursepress_focus/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&course=$matches[1]&unit=$matches[2]&type=$matches[3]&item=$matches[4]'; // Matches item
		$new_rules[ '^coursepress_focus/([^/]*)/([^/]*)/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&course=$matches[1]&unit=$matches[2]&type=$matches[3]'; // Matches type
		$new_rules[ '^coursepress_focus/([^/]*)/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&course=$matches[1]&unit=$matches[2]'; // Matches unit
		$new_rules[ '^coursepress_focus/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&course=$matches[1]'; // Matches course
		$new_rules[ '^coursepress_focus/.*?$' ]  = 'index.php?coursepress_focus=1';  // Not useful practically

		//$new_rules[ '^coursepress_focus/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&focus_course=$matches[1]&focus_unit=$matches[2]&focus_type=$matches[3]&focus_item=$matches[4]'; // Matches item
		//$new_rules[ '^coursepress_focus/([^/]*)/([^/]*)/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&focus_course=$matches[1]&focus_unit=$matches[2]&focus_type=$matches[3]'; // Matches type
		//$new_rules[ '^coursepress_focus/([^/]*)/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&focus_course=$matches[1]&focus_unit=$matches[2]'; // Matches unit
		//$new_rules[ '^coursepress_focus/([^/]*)/?$' ]  = 'index.php?coursepress_focus=1&focus_course=$matches[1]'; // Matches course
		//$new_rules[ '^coursepress_focus/.*?$' ]  = 'index.php?coursepress_focus=1';  // Not useful practically

		//$new_rules[ '^' . self::get_slug( 'course' )  ]                  = 'index.php?page_id=-1&course_category';
		$new_rules[ '^' . self::get_slug( 'course' ) . '/' . self::get_slug( 'category' ) . '/([^/]*)/page/([^/]*)/?' ]     = 'index.php?page_id=-1&course_category=$matches[1]&paged=$matches[2]';
		$new_rules[ '^' . self::get_slug( 'course' ) . '/' . self::get_slug( 'category' ) . '/([^/]*)/?' ]                  = 'index.php?page_id=-1&course_category=$matches[1]';

		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'discussion' ) . '/page/([^/]*)/?' ]   = 'index.php?page_id=-1&coursename=$matches[1]&discussion_archive&paged=$matches[2]'; ///page/?( [0-9]{1,} )/?$
		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'discussion' ) . '/([^/]*)/?' ]        = 'index.php?page_id=-1&coursename=$matches[1]&discussion_name=$matches[2]';
		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'discussion' ) ]                       = 'index.php?page_id=-1&coursename=$matches[1]&discussion_archive';

		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'grades' ) ]                           = 'index.php?page_id=-1&coursename=$matches[1]&grades_archive';
		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'workbook' ) ]                         = 'index.php?page_id=-1&coursename=$matches[1]&workbook';

		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'unit' ) . '/([^/]*)/page/([^/]*)/?' ] = 'index.php?page_id=-1&coursename=$matches[1]&unitname=$matches[2]&paged=$matches[3]'; ///page/?( [0-9]{1,} )/?$
		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'unit' ) . '/([^/]*)/?' ]              = 'index.php?page_id=-1&coursename=$matches[1]&unitname=$matches[2]';
		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'unit' ) ]                             = 'index.php?page_id=-1&coursename=$matches[1]';

		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'notification' ) . '/page/([^/]*)/?' ] = 'index.php?page_id=-1&coursename=$matches[1]&notifications_archive&paged=$matches[2]'; ///page/?( [0-9]{1,} )/?$
		$new_rules[ '^' . self::get_slug( 'course' ) . '/([^/]*)/' . self::get_slug( 'notification' ) ]                     = 'index.php?page_id=-1&coursename=$matches[1]&notifications_archive';

		$new_rules[ '^' . self::get_slug( 'instructor' ) . '/([^/]*)/?' ]                                                   = 'index.php?page_id=-1&instructor_username=$matches[1]';

		// Courses slug need to redirect to course archive pages
		$new_rules[ '^' . self::get_slug( 'course' ) . '/page/([^/]*)/?' ]     = 'index.php?page_id=-1&course_category=all&paged=$matches[1]';
		$new_rules[ '^' . self::get_slug( 'course' ) . '/?$' ]  = 'index.php?page_id=-1&course_category=all';

		$upload_dir = wp_upload_dir();
		$upload_path = trailingslashit( str_replace( home_url(), '', $upload_dir['baseurl'] ) );
		$new_rules[ '^' . self::get_slug( 'course' ) . '/file/([^/]*)/'  ]                           = 'wp-content/uploads/$matches[1]';

		//Remove potential conflicts between single and virtual page on single site
		/* if ( !is_multisite() ) {
		  unset( $rules['( [^/]+ )( /[0-9]+ )?/?$'] );
		  } */

		$new_rules[ '^' . self::get_slug( 'inbox' ) . '/?' ]			 = 'index.php?page_id=-1&inbox';
		$new_rules[ '^' . self::get_slug( 'messages_new' ) . '/?' ]	 = 'index.php?page_id=-1&new_message';
		$new_rules[ '^' . self::get_slug( 'messages_sent' ) . '/?' ]	 = 'index.php?page_id=-1&sent_messages';

		/* Resolve possible issue with rule formating and avoid 404s */
		foreach ( $new_rules as $new_rule => $value ) {
			$newer_rule					 = str_replace( ' ', '', $new_rule );
			unset( $new_rules[ $new_rule ] );
			$new_rules[ $newer_rule ]	 = $value;
		}

		$x = '';
		return array_merge( $new_rules, $rules );
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