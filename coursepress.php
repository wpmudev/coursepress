<?php
/*
Plugin Name: CoursePress Pro
Plugin URI: http://premium.wpmudev.org/project/coursepress/
Description: CoursePress Pro turns WordPress into a powerful online learning platform. Set up online courses by creating learning units with quiz elements, video, audio etc. You can also assess student work, sell your courses and much much more.
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
Developers: Marko Miljus ( https://twitter.com/markomiljus ), Rheinard Korf ( https://twitter.com/rheinardkorf )
Version: 1.3.1.2
TextDomain: cp
Domain Path: /languages/
WDP ID: 913071
License: GNU General Public License ( Version 2 - GPLv2 )

Copyright 2016 Incsub ( http://incsub.com )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License ( Version 2 - GPLv2 ) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

include_once( 'eb-mods/eb-mods.php' );
/**
 * Load the common functions.
 */
require_once( 'includes/functions.php' );

if ( ! class_exists( 'CoursePress' ) ) {

	/**
	 * CoursePress plugin setup class.
	 *
	 * @package CoursePress
	 * @since 1.0.0
	 */
	class CoursePress {

		public $mp_file; // Set in constructor

		/**
		 * Current running instance of CoursePress.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Current plugin version.
		 *
		 * NOTE: This should be the same version as set above in the plugin header and should also be set in
		 * Gruntfile.js
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = '1.3.1';

		/**
		 * Plugin friendly name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $name = 'CoursePress Pro';

		/**
		 * Plugin directory name.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $dir_name = 'coursepress';

		/**
		 * Plugin installation location.
		 *
		 * Possible values: subfolder-plugins, plugins, mu-plugins
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $location = '';

		/**
		 * Plugin installation directory.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_dir = '';

		/**
		 * Plugin installation url.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $plugin_url = '';

		/**
		 * Is MarketPress active.
		 *
		 * MarketPress integration.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		public $marketpress_active = false;

		/**
		 * Active MarketPress gateways.
		 *
		 * MarketPress integration.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		public static $gateway = array();

		/**
		 * Used for naming plugin screens.
		 *
		 * Changes depending on CoursePress Pro or CoursePress.
		 *
		 * @since 1.2.1
		 * @var string
		 */
		public $screen_base = '';

		/**
		 * Are we on a preview unit/page/module?
		 *
		 * @since 1.2.6.1
		 * @var mixed
		 */
		public $preview_data = null;

		/**
		 * CoursePress constructor.
		 *
		 * @since 1.0.0
		 * @return self
		 */
		function __construct() {
			// Setup CoursePress properties
			$this->init_vars();

			
			$this->mp_file = '128762_marketpress-ecommerce-3.0.0.2.zip';
			
			

			/**
			 * CoursePress Sessions
			 * Better handling of session data using WP_Session_Tokens introduced in 4.0.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.session.php' );
			CoursePress_Session::session_start();


			// Register Globals
			$GLOBALS['plugin_dir']              = $this->plugin_dir;
			$GLOBALS['coursepress_url']         = $this->plugin_url;
			$GLOBALS['coursepress_version']     = $this->version;
			$GLOBALS['course_slug']             = $this->get_course_slug();
			$GLOBALS['units_slug']              = $this->get_units_slug();
			$GLOBALS['notifications_slug']      = $this->get_notifications_slug();
			$GLOBALS['module_slug']             = $this->get_module_slug();
			$GLOBALS['instructor_profile_slug'] = $this->get_instructor_profile_slug();
			$GLOBALS['enrollment_process_url']  = $this->get_enrollment_process_slug( true );
			$GLOBALS['signup_url']              = $this->get_signup_slug( true );

			/**
			 * CoursePress Utilities
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-utility.php' );

			/**
			 * CoursePress custom non-persistent cache.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-cache.php' );

			/**
			 * CoursePress Object Class.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-object.php' );

			/**
			 * CoursePress Capabilities Class.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-capabilities.php' );


			/**
			 * CoursePress WordPress compatibility hooks.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-compatibility.php' );

			/**
			 * CoursePress Plugin Integrations.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-integration.php' );

			
			if ( CoursePress_Capabilities::is_pro() && ! CoursePress_Capabilities::is_campus() ) {
				// Prepare WPMUDev Dashboard Notifications
				global $wpmudev_notices;

				$wpmudev_notices[] = array(
					'id'      => 913071,
					'name'    => $this->name,
					'screens' => array(
						'toplevel_page_courses',
						$this->screen_base . '_page_course_details',
						$this->screen_base . '_page_instructors',
						$this->screen_base . '_page_students',
						$this->screen_base . '_page_assessment',
						$this->screen_base . '_page_reports',
						$this->screen_base . '_page_notifications',
						$this->screen_base . '_page_settings'
					)
				);

				/**
				 * Include WPMUDev Dashboard.
				 */
				include_once( $this->plugin_dir . 'includes/external/dashboard/wpmudev-dash-notification.php' );
			}
			

			// Define custom theme directory for CoursePress theme
			if ( ! CoursePress_Capabilities::is_campus() ) {
				$this->register_theme_directory();
			}

			// Install Plugin
			register_activation_hook( __FILE__, array( $this, 'install' ) );

			/**
			 * @todo: Document this
			 */
			global $last_inserted_unit_id; //$last_inserted_module_id
			global $last_inserted_front_page_module_id; //$last_inserted_module_id

			/**
			 * CampusPress/Edublogs Specifics.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-campus.php' );

			
			/**
			 * Basic certificates
			 * This is Pro only, by changing this flag in the free version you will break it!
			 */
			if ( CoursePress_Capabilities::is_pro() ) {
				require_once( $this->plugin_dir . 'includes/classes/class.basic.certificate.php' );
			}
			

			//Administration area
			if ( is_admin() ) {

				/**
				 * Course search.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.coursesearch.php' );

				/**
				 * Notificatioon search.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.notificationsearch.php' );

				/**
				 * Contextual help.
				 *
				 * @todo: Finish this class
				 */
				//require_once( $this->plugin_dir . 'includes/classes/class.help.php' );

				/**
				 * Search Students class.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.studentsearch.php' );

				/**
				 * Search Instructor class.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.instructorsearch.php' );

				/**
				 * Pagination Class.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.pagination.php' );

				/**
				 * Tooltip Helper.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.cp-helper-tooltip.php' );

				/**
				 * CoursePress Menu meta box.
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.menumetabox.php' );

				/**
				 * Add instructor to a course (AJAX).
				 *
				 * This also assigns the instructor capabilities.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_add_course_instructor', array( &$this, 'add_course_instructor' ) );

				/**
				 * Remove instructor from a course (AJAX).
				 *
				 * If the instructor is no longer an instructor of any courses
				 * then the instructor's capabilities will also be removed.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_remove_course_instructor', array( &$this, 'remove_course_instructor' ) );

				add_action( 'wp_ajax_update_unit', array( &$this, 'update_unit' ) );

				/**
				 * Add instructor MD5 as meta.
				 *
				 * Used to conceal instructor ids.
				 *
				 * @since 1.2.1
				 */
				add_action( 'coursepress_course_instructor_added', array( &$this, 'create_instructor_hash' ), 10, 2 );
				add_action( 'coursepress_instructor_invite_confirmed', array(
					&$this,
					'create_instructor_hash'
				), 10, 2 );

				/**
				 * Update course during setup (AJAX).
				 *
				 * This method is executed during setup in the 'Course Overview'.
				 * Each time the user moved from one section to another or when triggered
				 * via the 'Update' button.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_autoupdate_course_settings', array( &$this, 'autoupdate_course_settings' ) );

				/**
				 * Determined if a gateway is active (AJAX).
				 *
				 * MarketPress integration:
				 * An active gateway is required to be able to sell a course.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_course_has_gateway', array( &$this, 'course_has_gateway' ) );

				/**
				 * Invite an instructor to join a course (AJAX).
				 *
				 * Sends the instructor an email with a confirmation link.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_send_instructor_invite', array( &$this, 'send_instructor_invite' ) );

				/**
				 * Remove instructor invite (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_remove_instructor_invite', array( &$this, 'remove_instructor_invite' ) );

				/**
				 * Change course state (draft/publish) (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_change_course_state', array( &$this, 'change_course_state' ) );

				/**
				 * Change unit state (draft/publish) (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_change_unit_state', array( &$this, 'change_unit_state' ) );

				/**
				 * Update Course Calendar widget/shortcode (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_refresh_course_calendar', array( &$this, 'refresh_course_calendar' ) );

				/**
				 * Update Course Calendar for all visitors (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_nopriv_refresh_course_calendar', array( &$this, 'refresh_course_calendar' ) );

				/**
				 * Handle popup registration/signup forms (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_cp_popup_signup', array( &$this, 'popup_signup' ) );

				/**
				 * Handle popup registration/signup forms for everyone (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_nopriv_cp_popup_signup', array( &$this, 'popup_signup' ) );

				/**
				 * Returns whether the user already exists (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_cp_popup_user_exists', array( &$this, 'cp_popup_user_exists' ) );

				/**
				 * Returns whether the user already exists for everyone (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_nopriv_cp_popup_user_exists', array( &$this, 'cp_popup_user_exists' ) );


				/**
				 * Removes uppercase restriction for username registration
				 */
				add_filter( 'wpmu_validate_user_signup', array( $this, 'ms_validate_username' ) );

				/**
				 * Returns whether the email already exists (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_cp_popup_email_exists', array( &$this, 'cp_popup_email_exists' ) );

				/**
				 * Returns whether the course passcode is valid (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_cp_valid_passcode', array( &$this, 'cp_valid_passcode' ) );
				add_action( 'wp_ajax_nopriv_cp_valid_passcode', array( &$this, 'cp_valid_passcode' ) );

				/**
				 * Returns whether the email already exists for everyone (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_nopriv_cp_popup_email_exists', array( &$this, 'cp_popup_email_exists' ) );

				/**
				 * Login the user from the popup (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_cp_popup_login_user', array( &$this, 'cp_popup_login_user' ) );

				/**
				 * Login the user from the popup for everyone (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_nopriv_cp_popup_login_user', array( &$this, 'cp_popup_login_user' ) );

				/**
				 * Get the URL for the next unit in a course (AJAX).
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_get_next_unit_url', array( &$this, 'get_next_unit_url' ) );

				/**
				 * Get the URL for the next unit in a course for everyone (AJAX).
				 *
				 * Available to everyone because of the course preview options.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_nopriv_get_next_unit_url', array( &$this, 'get_next_unit_url' ) );

				/**
				 * Create a unit element draft post (AJAX).
				 *
				 * Allows the user to preview a unit.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_create_unit_element_draft', array( &$this, 'create_unit_element_draft' ) );

				/**
				 * MarketPress Gateway Settings (AJAX).
				 *
				 * Allows access to MarketPress gateways from Course Setup.
				 *
				 * @since 1.0.0
				 */
				add_action( 'mp_gateway_settings', array( &$this, 'cp_marketpress_popup' ) );

				/**
				 * Activate MarketPress or MarketPress Lite (AJAX).
				 *
				 * Dependending on whether this is CoursePress or CoursePress Pro.
				 *
				 * @since 1.0.0
				 */
				add_action( 'wp_ajax_cp_activate_mp_lite', array( &$this, 'activate_marketpress_lite' ) );

				/**
				 * Hook Unit creation to add course meta.
				 */
				add_action( 'coursepress_unit_created', array( &$this, 'update_course_meta_on_unit_creation' ), 10, 2 );
				add_action( 'coursepress_unit_updated', array( &$this, 'update_course_meta_on_unit_creation' ), 10, 2 );

				/**
				 * Hook WordPress Editor filters and actions.
				 *
				 * But do so with WordPress compatibility in mind. Therefore,
				 * create a new action hook to be used by CoursePress_Compatibility().
				 *
				 * @since 1.2.1
				 */
				do_action( 'coursepress_editor_compatibility' );

				/**
				 * Hook CoursePress admin initialization.
				 *
				 * Allows plugins and themes to add additional hooks during CoursePress constructor
				 * for admin specific actions.
				 *
				 * @since 1.2.1
				 *
				 */
				do_action( 'coursepress_admin_init' );

				/**
				 * Add certificate admin settings
				 *
				 * @since 1.2.6
				 */
				if ( CoursePress_Capabilities::is_pro() ) {
					CP_Basic_Certificate::init_settings();
				}

				/*
				 * Plugin activation class
				 */
				require_once( $this->plugin_dir . 'includes/classes/class.plugin-activation.php' );
			}

			if ( CoursePress_Capabilities::is_pro() ) {
				CP_Basic_Certificate::init_front();
			}
			/**
			 * Add's ?action=view_certificate
			 *
			 * @since 1.2.6
			 */
			/**
			 * Setup payment gateway array.
			 *
			 * MarketPress integration.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( $this, 'setup_gateway_array' ) );

			/**
			 * Output buffer workaround
			 *
			 * Prevents errors from CoursePress and other plugins or themes
			 * from breaking AJAX calls.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'output_buffer' ), 0 );

			/**
			 * Is there a version of MarketPress active.
			 *
			 * MarketPress integration.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'marketpress_check' ), 0 );

			/**
			 * Class for rendering course calendar.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.coursecalendar.php' );

			/**
			 * Class for creating/participating in course discussions.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.discussion.php' );

			/**
			 * Class for certificate template
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.certificate_template.php' );

			/**
			 * Class for certificate templates
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.certificate_templates.php' );

			/**
			 * Class for certificate template elements
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.certificate_template_elements.php' );

			/**
			 * Class for certificate templates search
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.certificate_templates_search.php' );

			/**
			 * Class to search course discussions.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.discussionsearch.php' );

			/**
			 * Class for managing instructors.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.instructor.php' );

			/**
			 * Class for managing Units.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.course.unit.php' );

			/**
			 * The Course class.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.course.php' );

			/**
			 * The hooks for course settings.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.course.settings.php' );


			/**
			 * Class to determine course completion.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.course.completion.php' );

			/**
			 * Class to determine course completion.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.student.completion.php' );

			/**
			 * Class for creating course or sitewide course notifications.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.notification.php' );

			/**
			 * Class to manage students.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.student.php' );

			/**
			 * Class to manage unit (page) elements.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.course.unit.module.php' );

			/**
			 * Load all unit element classes.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'load_modules' ), 11 );

			/**
			 * Load CoursePress widgets.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'load_widgets' ), 0 );

			/**
			 * Class to handle shortcodes.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.shortcodes.php' );

			/**
			 * Class to create virtual pages.
			 * Does not use existing pages.
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.virtualpage.php' );

			/**
			 * Register all CoursePress custom post types.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'register_custom_posts' ), 1 );

			/**
			 * Check for forced download in 'File' unit element.
			 *
			 * Checks to see if the file needs to be downloaded on click and then
			 * serve up the file.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'check_for_force_download_file_request' ), 1 );

			/**
			 * Initiate plugin localization.
			 *
			 * @since 1.0.0
			 */
			add_action( 'plugins_loaded', array( &$this, 'localization' ), 9 );

			/**
			 * Handle $_GET actions.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'check_for_get_actions' ), 98 );

			/**
			 * Add virtual pages.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'create_virtual_pages' ), 99 );

			/**
			 * Add custom image sizes.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'add_custom_image_sizes' ) );

			/**
			 * Add custom image sizes to media library.
			 *
			 * @todo: decide to keep it or remove it
			 * @since 1.0.0
			 */
			//add_filter( 'image_size_names_choose', array( &$this, 'add_custom_media_library_sizes' ) );

			/**
			 * Add plugin menu for network installs.
			 *
			 * @since 1.0.0
			 */
			add_action( 'network_admin_menu', array( &$this, 'add_admin_menu_network' ) );

			/**
			 * Add admin menu.
			 *
			 * @since 1.0.0
			 */
			add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );

			/**
			 * Check for admin notices.
			 *
			 * @since 1.0.0
			 */
			add_action( 'admin_notices', array( &$this, 'admin_nopermalink_warning' ) );

			/**
			 * Custom header actions.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_enqueue_scripts', array( &$this, 'header_actions' ) );

			/**
			 * Custom header actions.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_head', array( &$this, 'head_actions' ) );

			/**
			 * Custom footer actions.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_footer', array( &$this, 'footer_actions' ) );

			/**
			 * Add jQueryUI.
			 *
			 * @todo: decide if we need to keep this hook
			 * @since 1.0.0
			 */
			//add_action( 'admin_enqueue_scripts', array( &$this, 'add_jquery_ui' ) );

			add_action( 'admin_enqueue_scripts', array( &$this, 'cp_jquery_admin' ), 0, 1 );

			/**
			 * Admin header actions.
			 *
			 * @since 1.0.0
			 */
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header_actions' ) );

			/**
			 * Load Course Details (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_course_details', array(
				&$this,
				'admin_coursepress_page_course_details'
			) );

			/**
			 * Load CoursePress Settings (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_settings', array(
				&$this,
				'admin_coursepress_page_settings'
			) );

			/**
			 * Load Course Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-toplevel_page_courses', array( &$this, 'admin_coursepress_page_courses' ) );

			/**
			 * Load Course Notifications Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_notifications', array(
				&$this,
				'admin_coursepress_page_notifications'
			) );

			/**
			 * Load Course Discussions Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_discussions', array(
				&$this,
				'admin_coursepress_page_discussions'
			) );

			/**
			 * Load Course Reports Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_reports', array(
				&$this,
				'admin_coursepress_page_reports'
			) );

			/**
			 * Load Course Assessments Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_assessment', array(
				&$this,
				'admin_coursepress_page_assessment'
			) );

			/**
			 * Load Course Certificates Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_certificates', array(
				&$this,
				'admin_coursepress_page_certificates'
			) );

			/**
			 * Load Course Students Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_students', array(
				&$this,
				'admin_coursepress_page_students'
			) );

			/**
			 * Load Course Instructors Page (admin).
			 *
			 * @since 1.0.0
			 */
			add_action( 'load-' . $this->screen_base . '_page_instructors', array(
				&$this,
				'admin_coursepress_page_instructors'
			) );

			/**
			 * Redirect users after login.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'login_redirect', array( &$this, 'login_redirect' ), 10, 3 );

			/**
			 * Check for valid permalinks.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'post_type_link', array( &$this, 'check_for_valid_post_type_permalinks' ), 10, 3 );

			/**
			 * Enable comments for discussions pages.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'comments_open', array( &$this, 'comments_open_filter' ), 10, 2 );

			/**
			 * Remove comments from Virtual Pages.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'comments_template', array( &$this, 'no_comments_template' ) );

			/**
			 * Load CoursePress templates.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp', array( &$this, 'load_plugin_templates' ) );

			/**
			 * Add CoursePress rewrite rules.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'rewrite_rules_array', array( &$this, 'add_rewrite_rules' ) );

			/**
			 * Prevent Virtual Pages from redirecting.
			 *
			 * @since 1.0.0
			 */
			add_action( 'pre_get_posts', array( &$this, 'remove_canonical' ) );

			add_action( 'pre_get_posts', array( &$this, 'course_archive_categories' ) );

			add_action( 'pre_get_posts', array( &$this, 'course_archive' ) );


			/**
			 * Filter searches.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'pre_get_posts', array( &$this, 'filter_search' ) );

			/**
			 * Add post type filtering.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'posts_where', array( &$this, 'posts_where' ) );

			/**
			 * Update unit positions of reordering (AJAX).
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_ajax_update_units_positions', array( $this, 'update_units_positions' ) );

			/**
			 * Update course positions of reordering (AJAX).
			 *
			 */
			add_action( 'wp_ajax_update_course_positions', array( $this, 'update_course_positions' ) );

			/**
			 * Apply custom filter to WP query variables (AJAX).
			 *
			 * @since 1.0.0
			 */
			add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );

			/**
			 * Filter 'edit' link for Course post type.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'get_edit_post_link', array( $this, 'courses_edit_post_link' ), 10, 3 );

			/**
			 * Continue parsing requests when WordPress is done.
			 *
			 * @since 1.0.0
			 */
			add_action( 'parse_request', array( $this, 'action_parse_request' ) );

			/**
			 * Redirect to Setup Guide on plugin activation.
			 *
			 * @since 1.0.0
			 */
			add_action( 'admin_init', array( &$this, 'coursepress_plugin_do_activation_redirect' ), 0 );

			/**
			 * Record last student login.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_login', array( &$this, 'set_latest_student_activity_upon_login' ), 10, 2 );

			/**
			 * Upgrade legacy instructor meta on login.
			 *
			 * @since 1.0.0
			 */
			add_action( 'init', array( &$this, 'upgrade_instructor_meta' ) );

			/**
			 * Did MarketPress process a successful order.
			 *
			 * If MarketPress payment was successful, then enrol the user.
			 *
			 * @since 1.0.0
			 */
			if ( cp_use_woo() ) {
				add_action( 'woocommerce_order_status_processing', array(
					$this,
					'woo_listen_for_paid_status_for_courses'
				), 10, 1 );
				add_action( 'woocommerce_order_status_completed', array(
					$this,
					'woo_listen_for_paid_status_for_courses'
				), 10, 1 );
			} else {
				// Moved to MarketPress_Integration
			}

			/**
			 * Course taxonomies (not in this version).
			 *
			 * @todo: on the roadmap to implement
			 * @since 1.0.0
			 */
			add_action( 'parent_file', array( &$this, 'parent_file_correction' ) );

			/**
			 * Update CoursePress login/logout menu item.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'wp_nav_menu_objects', array( &$this, 'menu_metabox_navigation_links' ), 10, 2 );

			//add_filter( 'wp_nav_menu_args', array( &$this, 'modify_nav_menu_args' ), 10 );

			if ( get_option( 'display_menu_items', 1 ) ) {

				/**
				 * Create CoursePress basic menus automatically.
				 *
				 * @since 1.0.0
				 */
				add_filter( 'wp_nav_menu_objects', array( &$this, 'main_navigation_links' ), 10, 2 );
			}

			/*
			 * If allowing CoursePress to create a basic menu then
			 * make sure that there is somewhere to put it.
			 */
			if ( get_option( 'display_menu_items', 1 ) ) {

				$theme_location = 'primary';

				if ( ! has_nav_menu( $theme_location ) ) {
					$theme_locations = get_nav_menu_locations();
					foreach ( (array) $theme_locations as $key => $location ) {
						$theme_location = $key;
						break;
					}
				}

				if ( ! has_nav_menu( $theme_location ) ) {
					if ( get_option( 'display_menu_items', 1 ) ) {

						/**
						 * Fallback if there is no menu location.
						 *
						 * @since 1.0.0
						 */
						add_filter( 'wp_page_menu', array( &$this, 'main_navigation_links_fallback' ), 20, 2 );
						if ( wp_get_theme() == 'CoursePress' ) {

							/**
							 * Special case for the CoursePress theme.
							 *
							 * @todo: replace this with a hook so that it can be extended for other themes.
							 * @since 1.0.0
							 */
							add_filter( 'wp_page_menu', array( &$this, 'mobile_navigation_links_fallback' ), 21, 3 );
						}
					}
				}
			}

			/**
			 * Add image filter for content.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'element_content_filter', array( &$this, 'element_content_img_filter' ), 98, 1 );

			/**
			 * Add link filter for content.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'element_content_filter', array( &$this, 'element_content_link_filter' ), 99, 1 );

			/**
			 * Redirect user after logout.
			 *
			 * Works with custom shortcode and CoursePress custom login.
			 *
			 * @since 1.0.0
			 */
			add_action( 'wp_logout', array( &$this, 'redirect_after_logout' ) );

			/**
			 * Load the correct Virtual Page template.
			 *
			 * @since 1.0.0
			 */
			add_action( 'template_redirect', array( &$this, 'virtual_page_template' ) );

			/**
			 * Display the Instructor invite confirmation page.
			 *
			 * @since 1.0.0
			 */
			add_action( 'template_redirect', array( &$this, 'instructor_invite_confirmation' ) );

			/**
			 * MarketPress: Making it a little bit more friendly for non-physical goods (aka Courses).
			 *
			 * @since 1.0.0
			 */
			add_filter( 'gettext', array( &$this, 'change_mp_shipping_to_email' ), 20, 3 );

			/**
			 * Use the course list image as the MarketPress product image.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'mp_product_image', array( &$this, 'course_product_image' ), 10, 4 );

			/**
			 * Show extra instructor profile fields.
			 *
			 * @since 1.0.0
			 */
			add_action( 'show_user_profile', array( &$this, 'instructor_extra_profile_fields' ) );

			/**
			 * Edit/show extra instructor profile fields.
			 *
			 * @since 1.0.0
			 */
			add_action( 'edit_user_profile', array( &$this, 'instructor_extra_profile_fields' ) );

			/**
			 * Save instructor profile fields.
			 *
			 * Grant/Revoke instructor capabilities.
			 *
			 * @since 1.0.0
			 */
			add_action( 'personal_options_update', array( &$this, 'instructor_save_extra_profile_fields' ) );

			/**
			 * Save instructor profile fields.
			 *
			 * Grant/Revoke instructor capabilities.
			 *
			 * @since 1.0.0
			 */
			add_action( 'edit_user_profile_update', array( &$this, 'instructor_save_extra_profile_fields' ) );

			/**
			 * Add extra classes to HTML body.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'body_class', array( &$this, 'add_body_classes' ) );

			// Handle MP payment confirmation
			/* $gateways = get_option( 'mp_settings', false );
			  if ( !empty( $gateways ) && !empty( $gateways[ 'gateways' ][ 'allowed' ] ) ) {
			  $gateways = $gateways[ 'gateways' ][ 'allowed' ];
			  foreach ( $gateways as $gateway ) {
			  // Don't enroll students automatically with manual payments.
			  if ( 'manual-payments' != $gateway ) {

			  add_action( 'mp_payment_confirm_' . $gateway, array(
			  &$this,
			  'enroll_on_payment_confirmation'
			  ), 10, 2 );
			  //add_action( 'mp_create_order', array( &$this, 'enroll_on_payment_confirmation' ), 10, 1 );
			  }
			  }
			  } */

			//add_action( 'mp_order_paid', array( &$this, 'enroll_on_order_status_paid' ), 10, 1 );

			/**
			 * Change the MarketPress message to be more suitable for Courses.
			 *
			 * MarketPress integration.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'mp_setting_msgsuccess', array( &$this, 'course_checkout_success_msg' ), 10, 2 );


			add_filter( 'get_edit_post_link', array( &$this, 'get_edit_post_link' ), 10, 1 );

			/**
			 * Class to manage integration with automessage plugin (if installed)
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.automessage-integration.php' );

			/**
			 * Class to manage integration with WooCommerce plugin (if installed)
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.woocommerce-integration.php' );

			/**
			 * Class to manage integration with MarketPress 3 plugin (if installed)
			 */
			require_once( $this->plugin_dir . 'includes/classes/class.marketpress-integration.php' );



			/**
			 * Hook CoursePress initialization.
			 *
			 * Allows plugins and themes to add aditional hooks during CoursePress constructor.
			 *
			 * @since 1.2.1
			 *
			 */
			do_action( 'coursepress_init' );
		}

		function get_edit_post_link( $link ) {
			$link = str_replace( ' ', '', $link );

			return $link;
		}

		function add_body_classes( $classes ) {
			global $post;
			if ( isset( $post ) ) {
				$classes[] = str_replace( '_', '-', $post->post_type . '-' . $post->post_name );
			}

			return $classes;
		}

		function filter_search( $query ) {
			// Get post types
			if ( $query->is_search ) {
				if ( ! is_admin() ) {

					// Bail if it is a bbpress topic-reply query.
					if ( function_exists('bbp_get_topic_post_type') &&
						( array( bbp_get_topic_post_type(), bbp_get_reply_post_type() ) === $query->get( 'post_type' ) ) ) {
						return $query;
					}

					$post_types = get_post_types( array( 'public' => true ), 'objects' );

					$searchable_types = array();
					// Add available post types
					$remove_mp_products_from_search = apply_filters( 'coursepress_remove_mp_products_from_search', true );
					if ( $post_types ) {
						foreach ( $post_types as $type ) {
							//if ( $remove_mp_products_from_search ) {
							//if ( $type->name != 'product' ) {//remove MP products from search so we won't have duplicated posts in search
							//	$searchable_types[] = $type->name;
							//}
							//} else {
							$searchable_types[] = $type->name;
						}
						//}
					}
					$searchable_types[] = 'course';
					$query->set( 'post_type', $searchable_types );
				}
			}

			return $query;
		}

		function posts_where( $where ) {

			if ( is_search() ) {
				$where = preg_replace(
					"/post_title\s+LIKE\s*( \'[^\']+\' )/", "post_title LIKE $1 ) OR ( post_excerpt LIKE $1", $where );
			}

			return $where;
		}

		function activate_marketpress_lite() {

			// Don't allow on campus
			if ( CoursePress_Capabilities::is_campus() ) {
				return;
			}

			$ajax_response = array();

			// Same file regardless of Lite or full version of MP
			$result = activate_plugin( $this->dir_name . '/marketpress.php' );

			if ( is_wp_error( $result ) ) {
				$ajax_response['mp_lite_activated'] = false;
			} else {
				$ajax_response['mp_lite_activated'] = true;
			}

			$response = array(
				'what'   => 'cp_activate_mp_lite',
				'action' => 'cp_activate_mp_lite',
				'id'     => 1, // success status
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function update_course_meta_on_unit_creation( $post_id, $course_id ) {

			if ( ! $course_id ) {
				$post      = get_post( $post_id );
				$course_id = $post->post_parent;
			}

			// Update course structure
			$structure_option = get_post_meta( $course_id, 'course_structure_options', true );
			$structure_option = ! empty( $structure_option ) && 'on' == $structure_option ? 'on' : 'off';

			$show_unit_boxes = get_post_meta( $course_id, 'show_unit_boxes', true );
			$keys            = array_keys( $show_unit_boxes );

			// We only want to do this once to prevent accidental override.
			if ( ! in_array( $post_id, $keys ) ) {
				$show_unit_boxes[ $post_id ] = $structure_option;
			}

			update_post_meta( $course_id, 'show_unit_boxes', $show_unit_boxes );

			$show_page_boxes = get_post_meta( $course_id, 'show_page_boxes', true );
			$keys            = array_keys( $show_page_boxes );

			$page_count = Unit::get_page_count( $post_id );
			for ( $i = 1; $i <= $page_count; $i ++ ) {
				$key = $post_id . '_' . $i;
				// Avoid accidental overrides.
				if ( ! in_array( $key, $keys ) ) {
					$show_page_boxes[ $key ] = $structure_option;
				}
			}
			update_post_meta( $course_id, 'show_page_boxes', $show_page_boxes );
		}

		function course_checkout_success_msg( $setting, $default ) {
			$init_message = $setting;
			// cp_write_log( 'MP Success Setting: ' . $setting );
			$cookie_id = 'cp_checkout_keys_' . COOKIEHASH;
			$cookie    = '';

			if ( ! is_admin() ) {
				if ( isset( $_COOKIE[ $cookie_id ] ) ) {
					$cookie = unserialize( $_COOKIE[ $cookie_id ] );
				}

				if ( 2 == count( $cookie ) ) {
					// Thank you for signing up for Course Name Here. We hope you enjoy your experience.
					$setting = sprintf( __( '<p>Thank you for signing up for <a href ="%s">%s</a>. We hope you enjoy your experience.</p>', 'cp' ), get_permalink( $cookie[1] ), get_the_title( $cookie[1] ) );
					$setting = $setting . '<br />' . $init_message;
					setcookie( $cookie_id, '' );
					add_filter( 'gettext', array( &$this, 'alter_tracking_text' ), 20, 3 );
				}
			}

			return $setting;
		}

		function alter_tracking_text( $translated_text, $text, $domain ) {

			// "You may track the latest status of your order( s ) here:<br />%s"
			switch ( $text ) {
				case "You may track the latest status of your order( s ) here:<br />%s":
					$translated_text = __( 'You may track the status of this order here:<br />%s', 'cp' );
					remove_filter( 'gettext', array( &$this, 'alter_tracking_text' ) );
					break;
			}

			return $translated_text;
		}

		/* function enroll_on_payment_confirmation( $cart, $session ) {
		  if ( count( $cart ) > 0 ) {
		  $product_id	 = array_keys( $cart );
		  $product_id	 = end( $product_id );

		  $course_id = get_post_meta( $product_id, 'cp_course_id', true );

		  if ( !empty( $course_id ) ) {
		  $student			 = new Student( get_current_user_id() );
		  $existing_student	 = $student->has_access_to_course( $course_id );
		  if ( !$existing_student ) {
		  $student->enroll_in_course( $course_id );
		  }
		  }
		  } else {
		  cp_write_log( 'Error in cart. This should not happen.' );
		  }
		  } */


		/* function enroll_on_payment_confirmation_new( $order_id ) {
		  global $mp;
		  $order	 = $mp->get_order( $order_id );
		  $cart	 = $order->mp_cart_info;

		  if ( count( $cart ) > 0 ) {
		  $product_id	 = array_keys( $cart );
		  $product_id	 = end( $product_id );

		  $course_id = get_post_meta( $product_id, 'cp_course_id', true );

		  if ( !empty( $course_id ) ) {
		  $student			 = new Student( get_current_user_id() );
		  $existing_student	 = $student->has_access_to_course( $course_id );
		  if ( !$existing_student ) {
		  $student->enroll_in_course( $course_id );
		  }
		  }
		  } else {
		  cp_write_log( 'Error in cart. This should not happen.' );
		  }
		  } */

		function course_product_image( $image, $context, $post_id, $size ) {
			$course_id = get_post_meta( $post_id, 'cp_course_id', true );
			if ( ! empty( $course_id ) ) {
				$image = do_shortcode( '[course_list_image course_id ="' . $course_id . '" width ="' . $size[0] . '" height ="' . $size[0] . '"]' );
			}

			return $image;
		}


		// MP3.0 BUG
		function change_mp_shipping_to_email( $translated_text, $text, $domain ) {
			//if ( defined( 'COOKIEHASH' ) ) {
			//	$cookie_id = 'mp_globalcart_' . COOKIEHASH;
			//	$cookie    = '';
			//
			//	if ( isset( $_COOKIE[ $cookie_id ] ) ) {
			//		$cookie = unserialize( $_COOKIE[ $cookie_id ] );
			//		// Get product ID
			//		if ( count( $cookie ) > 0 ) {
			//
			//			$product_id = end( $cookie );  // Get first cookie that match
			//			$product_id = array_keys( $product_id ); // Get the first product ( will be an array )
			//			$product_id = end( $product_id ); // Get the actual product id
			//
			//			if ( $product_id == 0 ) {
			//				// If we're on the success message.
			//				if ( 2 == count( $cookie ) ) {
			//					$product_id = $cookie[0];
			//				} else {
			//					return $translated_text;
			//				}
			//			}
			//			$cp_course_id = get_post_meta( $product_id, 'cp_course_id', true );
			//			if ( ! empty( $cp_course_id ) ) {
			//				switch ( $text ) {
			//					case 'Shipping' :
			//						$translated_text = __( 'E-Mail', 'cp' );
			//						break;
			//				}
			//			}
			//		}
			//	}
			//}

			return $translated_text;
		}

		function create_unit_element_draft() {
			$unit_id              = $_POST['unit_id'];
			$temp_unit_id         = $_POST['temp_unit_id'];
			$data['temp_unit_id'] = $temp_unit_id;
			//$data['temp_unit_id'] = $temp_unit_id;
			$unit_id = Unit_Module::create_auto_draft( $unit_id );
			echo $unit_id;
			exit;
		}

		function get_last_inserted_id() {
			$post = get_posts( array(
				'post_type'   => array( 'unit' ),
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'numberposts' => '1'
			) );
			$post = array_pop( $post );

			return $post->ID;
		}

		function get_next_unit_url() {
			global $wpdb;

			$course_id    = (int) $_POST['course_id'];
			$next_unit_id = $this->get_last_inserted_id();
			echo admin_url( 'admin.php?page = course_details&tab = units&course_id =' . $course_id . '&unit_id =' . $next_unit_id . '&action = edit' );
			exit;
		}

		function setup_gateway_array() {

			$array = array(
				'paypal-express'  => array(
					'class'    => 'MP_Gateway_Paypal_Express',
					'friendly' => __( 'Pay with PayPal', 'cp' ),
				),
				'manual-payments' => array(
					'class'    => 'MP_Gateway_ManualPayments',
					'friendly' => __( 'Bank Transfer', 'cp' ),
				),
				'simplify'        => array(
					'class'    => 'MP_Gateway_Simplify',
					'friendly' => __( 'Pay by Credit Card', 'cp' ),
				),
			);

			CoursePress::$gateway = $array;
		}

		function cp_popup_login_user() {

			$creds                  = array();
			$creds['user_login']    = $_POST['username'];
			$creds['user_password'] = $_POST['password'];
			$creds['remember']      = true;

			$user = wp_signon( $creds, false );

			if ( is_wp_error( $user ) ) {
				echo 'failed';
			} else {
				echo 'success';
			}
			exit;
		}

		function cp_popup_user_exists() {
			if ( isset( $_POST['username'] ) ) {
				if ( ! is_multisite() ) {
					if ( ! validate_username( $_POST['username'] ) ) {//username is not valid
						echo 1;
						exit;
					}
				} else {
					//email
					if ( ! wpmu_validate_user_signup( $_POST['username'], $_POST['email'] ) ) {
						echo 1;
						exit;
					};
				}
				echo username_exists( $_POST['username'] );
				exit;
			}
		}

		function ms_validate_username( $result ) {
			if ( ! is_wp_error( $result['errors'] ) ) {
				return $result;
			}

			$username = $result['user_name'];

			$new_errors = new WP_Error();
			$errors     = $result['errors'];
			$codes      = $errors->get_error_codes();

			foreach ( $codes as $code ) {
				$messages = $errors->get_error_messages( $code );

				if ( $code == 'user_name' ) {
					foreach ( $messages as $message ) {
						if ( $message == __( 'Only lowercase letters (a-z) and numbers are allowed.' ) ) {
							if ( is_email( $username ) ) {
								if ( ! $this->options['allow_email_addresses'] ) {
									$new_errors->add( $code, $message );
								}
							} else {
								$allowed = '';
								$allowed .= '-';
								$allowed .= '_';
								$allowed .= '.';
								$allowed .= 'A-Z';

								preg_match( '/[' . $allowed . 'a-z0-9]+/', $username, $maybe );

								if ( $username != $maybe[0] ) {
									$new_errors->add( $code, $message );
								}
							}
						}
					}
				} else {
					foreach ( $messages as $message ) {
						$new_errors->add( $code, $message );
					}
				}
			}

			$result['errors'] = $new_errors;

			return $result;
		}

		function cp_popup_email_exists() {
			if ( isset( $_POST['email'] ) ) {
				if ( ! is_email( $_POST['email'] ) ) {//username is not valid
					echo 1;
					exit;
				}
				echo email_exists( $_POST['email'] );
				exit;
			}
		}

		function cp_valid_passcode() {
			if ( isset( $_POST['passcode'] ) ) {
				$course_id       = $_POST['course_id'];
				$course          = new Course( $course_id );
				$course_passcode = $course->details->passcode;

				if ( $course_passcode == $_POST['passcode'] ) {
					echo 'valid';
				} else {
					echo 'invalid';
				}
				exit;
			}
		}

		// Popup Signup Process
		function popup_signup( $step = false, $args = array() ) {
			$x = '';
			global $mp;

			if ( ! $step && isset( $_POST['step'] ) ) {
				$step = $_POST['step'];
			}

			if ( empty( $args ) && isset( $_POST['data'] ) ) {
				//$args = $_POST[ 'data' ];
				parse_str( $_POST['data'], $args );
			}

			$ajax_response = array();

			$course_id = ! empty( $_REQUEST['course_id'] ) ? (int) $_REQUEST['course_id'] : ( isset( $args['course_id'] ) && ! empty( $args['course_id'] ) ? $args['course_id'] : 0 );

			$is_paid = get_post_meta( $course_id, 'paid_course', true );
			$is_paid = $is_paid && 'on' == $is_paid ? true : false;

			// cp_write_log( $_POST );
			$signup_steps = apply_filters( 'coursepress_signup_steps', array(
				'login'              => array(
					'action'     => 'template',
					'template'   => $this->plugin_dir . 'includes/templates/popup-window-login.php',
					'on_success' => 'process_login',
				),
				'process_login'      => array(
					'action'     => 'callback',
					'callback'   => array( &$this, 'signup_login_user' ),
					'on_success' => 'enrollment',
					'on_fail'    => 'login',
				),
				'signup'             => array(
					'action'     => 'template',
					'template'   => $this->plugin_dir . 'includes/templates/popup-window-signup.php',
					'on_success' => 'process_signup',
				),
				'process_signup'     => array(
					'action'     => 'callback',
					'callback'   => array( &$this, 'signup_create_user' ),
					'on_success' => 'enrollment',
				),
				'enrollment'         => array(
					'action'     => 'callback',
					'callback'   => array( &$this, 'signup_enroll_student', $args ),
					'on_success' => 'success-enrollment',
				),
				'redirect_to_course' => array(
					'action' => 'redirect',
					'url'    => trailingslashit( get_permalink( $course_id ) ) . trailingslashit( $this->get_units_slug() ),
				),
			) );

			global $mp;

			$course     = new Course( $course_id );

			if( $is_paid ) {
				if ( cp_use_woo() ) {
					global $woocommerce;
					$product_id = CP_WooCommerce_Integration::woo_product_id( $course_id );
					if ( ! empty( $product_id ) ) {
						$signup_steps = array_merge( $signup_steps, array(
							'payment_checkout'  => array(
								// WooCommerce integration
								// 'action' => 'template',
								// 'template' => $this->plugin_dir . 'includes/templates/popup-window-payment.php',
								'data'       => $this->woo_signup_pre_redirect_to_cart( $args ),
								'action'     => 'redirect',
								'url'        => $woocommerce->cart->get_cart_url(),
								'on_success' => 'process_payment',
							),
							'process_payment'   => array(
								// MP3 integration
								// 'action' => 'callback',
								// 'action' => 'render',
								// 'callback' => array( &$this, 'signup_payment_processing' ),
								'data'   => $this->signup_payment_processing( $args ),
								'action' => 'redirect',
								'url'    => $woocommerce->cart->get_cart_url(),
								//home_url(),//home_url( $mp->get_setting( 'slugs->store' ) . '/' . $mp->get_setting( 'slugs->cart' ) . '/confirm-checkout' ),
								// 'on_success' => 'payment_confirmed',
							),
							'payment_confirmed' => array(
								'template' => '',
							),
							'payment_pending'   => array(
								'template' => '',
							),
						) );
					}
				} else {
					$product_id = $course->mp_product_id();
					if ( $mp && ! empty( $product_id ) ) {

						$cart_url = home_url( $mp->get_setting( 'slugs->store' ) . '/' . $mp->get_setting( 'slugs->cart' ) . '/' );
						if( '3.0' === CoursePress_MarketPress_Integration::get_base() ) {
							$cart_url = MP_Cart::get_instance()->cart_url();
						}

						$signup_steps = array_merge( $signup_steps, array(
							'payment_checkout'  => array(
								// MP3 integration
								// 'action' => 'template',
								// 'template' => $this->plugin_dir . 'includes/templates/popup-window-payment.php',
								'data'       => $this->signup_pre_redirect_to_cart( $args ),
								'action'     => 'redirect',
								'url'        => esc_url_raw( $cart_url ),
								'on_success' => 'process_payment',
							),
							'process_payment'   => array(
								// MP3 integration
								// 'action' => 'callback',
								// 'action' => 'render',
								// 'callback' => array( &$this, 'signup_payment_processing' ),
								'data'   => $this->signup_payment_processing( $args ),
								'action' => 'redirect',
								'url'    => home_url( $mp->get_setting( 'slugs->store' ) . '/' . $mp->get_setting( 'slugs->cart' ) . '/confirm-checkout' ),
								// 'on_success' => 'payment_confirmed',
							),
							'payment_confirmed' => array(
								'template' => '',
							),
							'payment_pending'   => array(
								'template' => '',
							),
						) );
					}
				}
			}

			$signup_steps = array_merge( $signup_steps, array(
				'success-enrollment' => array(
					'action'     => 'template',
					'template'   => $this->plugin_dir . 'includes/templates/popup-window-success-enrollment.php',
					'on_success' => 'done',
				),
			) );

			if ( ! empty( $step ) ) {
				if ( 'template' == $signup_steps[ $step ]['action'] ) {
					ob_start();
					include( $signup_steps[ $step ]['template'] );
					$html                  = ob_get_clean();
					$ajax_response['html'] = $html;
				} elseif ( 'callback' == $signup_steps[ $step ]['action'] ) {
					$classname = get_class( $signup_steps[ $step ]['callback'][0] );
					$method    = $signup_steps[ $step ]['callback'][1];

					if ( isset( $signup_steps[ $step ]['callback'][2] ) ) {//args
						// call_user_func( $classname . '::' . $method, $signup_steps[$step]['callback'][2] );
						call_user_func( array( &$this, $method ), $signup_steps[ $step ]['callback'][2] );
					} else {
						// call_user_func( $classname . '::' . $method );
						call_user_func( array( &$this, $method ) );
					}
				} elseif ( 'render' == $signup_steps[ $step ]['action'] ) {
					$data                     = $signup_steps[ $step ]['data'];
					$ajax_response['html']    = $data['html'];
					$ajax_response['gateway'] = $data['gateway'];
				} elseif ( 'redirect' == $signup_steps[ $step ]['action'] ) {
					$ajax_response['redirect_url'] = $signup_steps[ $step ]['url'];
				}

				$ajax_response['current_step'] = $step;
				$ajax_response['next_step']    = $signup_steps[ $step ]['on_success'];
				$ajax_response['all_steps']    = array_keys( $signup_steps );

				$response = array(
					'what'   => 'instructor_invite',
					'action' => 'instructor_invite',
					'id'     => 1, // success status
					'data'   => json_encode( $ajax_response ),
				);
				ob_end_clean();
				ob_start();
				$xmlResponse = new WP_Ajax_Response( $response );
				$xmlResponse->send();
				ob_end_flush();

				exit;
			}
		}

		function signup_login_user() {
			// cp_write_log( 'logging in....' );
			// Handle login stuff
			$this->popup_signup( 'enrollment' );
		}

		function signup_create_user() {
			// cp_write_log( 'creating user....' );

			parse_str( $_POST['data'], $posted_data );

			if ( wp_verify_nonce( $posted_data['submit_signup_data'], 'popup_signup_nonce' ) ) {

				$student      = new Student( 0 );
				$student_data = array();

				$student_data['role']       = get_option( 'default_role', 'subscriber' );
				$student_data['user_login'] = $posted_data['username'];
				$student_data['user_pass']  = $posted_data['cp_popup_password'];
				$student_data['user_email'] = $posted_data['email'];
				$student_data['first_name'] = $posted_data['student_first_name'];
				$student_data['last_name']  = $posted_data['student_last_name'];

				$student_id = $student->add_student( $student_data );

				if ( $student_id !== 0 ) {

					$email_args['email_type']         = 'student_registration';
					$email_args['student_id']         = $student_id;
					$email_args['student_email']      = $student_data['user_email'];
					$email_args['student_first_name'] = $student_data['first_name'];
					$email_args['student_last_name']  = $student_data['last_name'];
					$email_args['student_username']   = $student_data['user_login'];
					$email_args['student_password']   = $student_data['user_pass'];

					coursepress_send_email( $email_args );

					$creds                  = array();
					$creds['user_login']    = $student_data['user_login'];
					$creds['user_password'] = $student_data['user_pass'];
					$creds['remember']      = true;

					$user = wp_signon( $creds, false );

					$args['student_id'] = $student_id;
					$args['course_id']  = $posted_data['course_id'];

					$this->popup_signup( 'enrollment', $args );
					exit;
				}
			}
		}

		function signup_enroll_student( $args = array() ) {
			// cp_write_log( 'enrolling user ( or passing them on to payment )....' );
			// Handle enrollment stuff
			$student_id = get_current_user_id();
			$student_id = $student_id > 0 ? $student_id : $args['student_id'];
			$course_id  = false;

			if ( ! empty( $args ) ) {
				$course_id = isset( $args['course_id'] ) ? $args['course_id'] : false;
			} else {
				$course_id = ! empty( $_POST['course_id'] ) ? (int) $_POST['course_id'] : false;
			}

			if ( isset( $course_id ) ) {

				$is_paid = get_post_meta( $course_id, 'paid_course', true );
				$is_paid = $is_paid && 'on' == $is_paid ? true : false;

				$student          = new Student( $student_id );
				$existing_student = $student->has_access_to_course( $course_id );

				// If it is a paid course we have a different path.
				if ( $is_paid && ! $existing_student ) {
					// Start to use the methods in the popup_signup_payment hook
					$this->popup_signup( 'payment_checkout', $args );

					return;
				}

				if ( ! $existing_student ) {//only if he don't have access already
					$student->enroll_in_course( $course_id );

					$args['course_id'] = $course_id;

					$this->enrollment_processed = true;

					//show success message
					$this->popup_signup( 'success-enrollment', $args );
				} else {
					$this->popup_signup( 'redirect_to_course' );
				}
			} else {
				echo 'course id not set';
			}
		}

		// Current Woo integration
		function woo_signup_pre_redirect_to_cart( $args = array() ) {
			$course_id = 0;
			if ( ! empty( $args ) ) {
				$course_id = isset( $args['course_id'] ) ? $args['course_id'] : false;
			} else {
				$course_id = ! empty( $_POST['course_id'] ) ? (int) $_POST['course_id'] : false;
			}

			$course     = new Course( $course_id );
			$product_id = $course->mp_product_id();

			CP_WooCommerce_Integration::add_product_to_cart( $product_id );
		}

		// Current MP integration
		function signup_pre_redirect_to_cart( $args = array() ) {
			global $mp;

			if ( ! $mp ) {
				return;
			}

			$course_id = 0;
			if ( ! empty( $args ) ) {
				$course_id = isset( $args['course_id'] ) ? $args['course_id'] : false;
			} else {
				$course_id = ! empty( $_POST['course_id'] ) ? (int) $_POST['course_id'] : false;
			}

			$course     = new Course( $course_id );
			$product_id = $course->mp_product_id();

			// Try some MP alternatives
			$product_id = empty( $product_id ) ? (int) get_post_meta( $course_id, 'mp_product_id', true ) : $product_id;
			$product_id = empty( $product_id ) ? (int) get_post_meta( $course_id, 'marketpress_product', true ) : $product_id;

			// Set ID's to be used in final step of checkout
			$cookie_id = 'cp_checkout_keys_' . COOKIEHASH;
			$post_keys = array( (int) $product_id, (int) $course_id );
			$expire    = time() + 2592000; //1 month expire
			setcookie( $cookie_id, serialize( $post_keys ), $expire, COOKIEPATH, COOKIE_DOMAIN );
			$_COOKIE[ $cookie_id ] = serialize( $post_keys );

			// Add course to cart
			$product   = get_post( $product_id );
			$quantity  = 1;
			$variation = 0;

			// $cart = $mp->get_cart_cookie();

			switch( CoursePress_MarketPress_Integration::get_base() ) {

				case '3.0':
					$cart = MP_Cart::get_instance();
					$cart->add_item( $product_id );

					break;

				case '2.0':
					$cart                              = array(); // remove all cart items
					$cart[ $product_id ][ $variation ] = $quantity;
					$mp->set_cart_cookie( $cart );

					break;
			}


		}

		// Future MP3 integration
		function signup_payment_processing( $args = array() ) {
			// cp_write_log( 'processing payment....' );
			// global $mp;
			$return_data = array( 'html' => '' );

			// $course_id = ! empty( $_POST['course_id'] ) ? ( int ) $_POST['course_id'] : 0;
			// $product_id = ! empty( $_POST['data'] ) && is_array( $_POST['data'] ) ? ( int ) $_POST['data']['product_id'] : 0;
			// $gateway =  empty( $args['gateway'] ) ? '' : $args['gateway'];
			// $product = false;
			// $product_meta = false;
			//
			// $_SESSION['mp_payment_method'] = $gateway;
			// $_SESSION['mp_shipping_info'] = '';

			return $return_data;
		}

		function instructor_save_extra_profile_fields( $user_id ) {

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}

			if ( current_user_can( 'manage_options' ) ) {

				check_admin_referer( 'update-user_' . $user_id );
				$global_option = ! is_multisite();
				if ( $_POST['cp_instructor_capabilities'] == 'grant' ) {
					update_user_option( $user_id, 'role_ins', 'instructor', $global_option );
					CoursePress::instance()->assign_instructor_capabilities( $user_id );
				} else {
					delete_user_option( $user_id, 'role_ins', $global_option );
					// Legacy
					delete_user_meta( $user_id, 'role_ins', 'instructor' );
					CoursePress::instance()->drop_instructor_capabilities( $user_id );
				}
			}
		}

		function instructor_extra_profile_fields( $user ) {

			if ( current_user_can( 'manage_options' ) ) {
				?>
				<h3><?php _e( 'Instructor Capabilities', 'cp' ); ?></h3>

				<?php
				// If user has no role i.e. can't "read", don't even go near capabilities, it wont work.
				if ( ! user_can( $user, 'read' ) ) {
					_e( "Can't assign instructor capabilities. User has no assigned role on this blog. See 'Role' above.", 'cp' );

					return false;
				}
				?>

				<?php
				$has_instructor_role = 'instructor' == cp_get_user_option( 'role_ins', $user->ID ) ? true : false;
				?>
				<table class="form-table">
					<tr>
						<th><label for="instructor_capabilities"><?php _e( 'Capabilities', 'cp' ); ?></label></th>

						<td>
							<input type="radio" name="cp_instructor_capabilities" value="grant" <?php echo( $has_instructor_role ? 'checked' : '' ); ?>><?php _e( 'Granted Instructor Capabilities', 'cp' ) ?>
							<br/><br/>
							<input type="radio" name="cp_instructor_capabilities" value="revoke" <?php echo( ! $has_instructor_role ? 'checked' : '' ); ?>><?php _e( 'Revoked Instructor Capabilities', 'cp' ) ?>
							<br/>
						</td>
					</tr>

				</table>
			<?php
			}
		}

		function cp_marketpress_popup() {
			if ( ( isset( $_GET['cp_admin_ref'] ) && $_GET['cp_admin_ref'] == 'cp_course_creation_page' ) || ( isset( $_POST['cp_admin_ref'] ) && $_POST['cp_admin_ref'] == 'cp_course_creation_page' ) ) {
				?>
				<input type="hidden" name="cp_admin_ref" value="cp_course_creation_page"/>
			<?php
			}
		}

		function install_and_activate_plugin( $plugin ) {
			$current = get_option( 'active_plugins' );
			$plugin  = plugin_basename( trim( $plugin ) );

			if ( ! in_array( $plugin, $current ) ) {
				$current[] = $plugin;
				sort( $current );
				do_action( 'activate_plugin', trim( $plugin ) );
				update_option( 'active_plugins', $current );
				do_action( 'activate_' . trim( $plugin ) );
				do_action( 'activated_plugin', trim( $plugin ) );
			}

			return null;
		}

		function virtual_page_template() {
			global $post, $wp_query;

			if ( isset( $post ) && $post->post_type == 'virtual_page' ) {
				$theme_file = locate_template( array( 'page.php' ) );
				if ( $theme_file != '' ) {
					include( TEMPLATEPATH . "/page.php" );
					exit;
				}
			}

			if ( cp_use_woo() && cp_redirect_woo_to_course() ) {
				if ( isset( $post ) && $post->post_type == 'product' ) {
					if ( isset( $post->post_parent ) ) {//parent course
						if ( $post->post_parent !== 0 && get_post_type( $post->post_parent ) == 'course' ) {
							$course = new Course( $post->post_parent );
							wp_redirect( $course->get_permalink() );
							exit;
						}
					}
				}
			} elseif( cp_redirect_mp_to_course() ) {
				if ( isset( $post ) && $post->post_type == 'product' && $wp_query->is_single ) {
					$course_id = (int) get_post_meta( $post->ID, 'course_id', true );
					if ( !empty($course_id) ) {//related course
						$course = new Course( $course_id );
						wp_redirect( $course->get_permalink() );
						exit;
					}
				}
			}
		}

		function register_theme_directory() {
			global $wp_theme_directories;
			// Allow registration of other theme directories or moving the CoursePress theme.
			$theme_directories = apply_filters( 'coursepress_theme_directory_array', array(
					$this->plugin_dir . 'themes/',
				)
			);
			foreach ( $theme_directories as $theme_directory ) {
				register_theme_directory( $theme_directory );
			}
		}

		/* Fix for the broken images in the Unit elements content */

		function redirect_after_logout() {
			// if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			//     cp_write_log( 'ajax' );
			// }
			if ( get_option( 'use_custom_login_form', 1 ) ) {
				$url = trailingslashit( home_url() ) . trailingslashit( $this->get_login_slug() );
				wp_redirect( $url );
				exit;
			}
		}

		function element_content_img_filter( $content ) {
			$rule = '#(<img\s[^>]*src )="([^"]+)"#';
			$rule = str_replace( ' ', '', $rule );

			return preg_replace_callback( $rule, "cp_callback_img", $content );
		}

		function element_content_link_filter( $content ) {
			$rule = '#(<a\s[^>]*href )="([^"]+)".*<img#';
			$rule = str_replace( ' ', '', $rule );

			return preg_replace_callback( $rule, "cp_callback_link", $content );
		}

		function is_preview( $unit_id, $page_num = false ) {
			global $wp, $wpquery;

			if ( isset( $_GET['try'] ) ) {

				if ( null === $this->preview_data ) {
					$this->preview_data = array();
				}

				if ( ! isset( $this->preview_data['course_id'] ) ) {

					$unit   = get_post( $unit_id );
					$course = new Course( $unit->post_parent );

					$this->preview_data['course_id']    = $unit->post_parent;
					$this->preview_data['preview_unit'] = $course->details->preview_unit_boxes;
					$this->preview_data['preview_page'] = $course->details->preview_page_boxes;
				}

				if ( $page_num ) {
					$paged = $page_num;
				} else {
					$paged = ! empty( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
				}

				if ( isset( $this->preview_data['preview_unit'][ $unit_id ] ) && $this->preview_data['preview_unit'][ $unit_id ] == 'on' ) {
					return true;
				} else {
					if ( isset( $this->preview_data['preview_page'][ $unit_id . '_' . $paged ] ) && $this->preview_data['preview_page'][ $unit_id . '_' . $paged ] == 'on' ) {
						return true;
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		}

		function check_access( $course_id, $unit_id = false ) {

			// if( defined( 'DOING_AJAX' ) && DOING_AJAX ) { cp_write_log( 'doing ajax' ); }
			// $page_num not set...
			// @TODO: implement $page_num and remove next line.

			if ( $this->is_preview( $unit_id ) ) {
				//have access
			} else {
				$student    = new Student( get_current_user_id() );
				$instructor = new Instructor( get_current_user_id() );
				$has_access = false;

				if ( current_user_can( 'manage_options' ) || $student->has_access_to_course( $course_id ) || $instructor->is_assigned_to_course( $course_id, get_current_user_id() ) ) {
					$has_access = true;
				}

				if ( ! $has_access ) {
					wp_redirect( get_permalink( $course_id ) );
					exit;
				}
			}

			return true;
		}

		function comments_open_filter( $open, $post_id ) {
			global $wp;

			$current_post = get_post( $post_id );
			if ( $current_post && $current_post->post_type == 'discussions' ) {
				$qv = isset( $wp->query_vars ) ? $wp->query_vars : array();
				if ( array_key_exists( 'discussion_archive', $qv ) ) {
					return false;
				} else {
					return true;
				}
			} else {
				return $open;
			}
		}

		function add_custom_image_sizes() {
			// if( defined( 'DOING_AJAX' ) && DOING_AJAX ) { cp_write_log( 'doing ajax' ); }
			if ( function_exists( 'add_image_size' ) ) {
				$course_image_width  = get_option( 'course_image_width', 235 );
				$course_image_height = get_option( 'course_image_height', 225 );
				add_image_size( 'course_thumb', $course_image_width, $course_image_height, true );
			}
		}

		function add_custom_media_library_sizes( $sizes ) {
			$sizes['course_thumb'] = __( 'Course Image', 'cp' );

			return $sizes;
		}

		/* highlight the proper top level menu */

		function parent_file_correction( $parent_file ) {
			global $current_screen;

			$taxonomy  = $current_screen->taxonomy;
			$post_type = $current_screen->post_type;

			if ( $taxonomy == 'course_category' ) {
				$parent_file = 'courses';
			}

			return $parent_file;
		}

		/* change Edit link for courses post type */

		function courses_edit_post_link( $url, $post_id, $context ) {
			if ( get_post_type( $post_id ) == 'course' ) {
				$url = trailingslashit( get_admin_url() ) . 'admin.php?page = course_details&course_id =' . $post_id;
			}

			return $url;
		}

		/* Save last student activity ( upon login ) */

		function set_latest_student_activity_upon_login( $user_login, $user ) {
			$this->set_latest_activity( $user->data->ID );
		}

		/* Save last student activity */

		function set_latest_activity( $user_id ) {
			update_user_meta( $user_id, 'latest_activity', current_time( 'timestamp' ) );
		}

		/* Upgrade user meta for multisite */

		function upgrade_user_meta( $user_id, $course_id ) {
			// Update old meta then remove it
			if ( is_multisite() && get_user_meta( $user_id, 'enrolled_course_date_' . $course_id, true ) ) {
				$global_option = ! is_multisite();
				// Only for instructor... so skipping it here for now.
				//$m_course_id = get_user_meta( $user_id, 'course_' . $course_id, true );
				//update_user_option( $user_id, 'course_' . $course_id , $m_course_id, $global_option );
				//delete_user_meta( $user_id, 'course_' . $course_id );

				$m_enrolled_course_class = get_user_meta( $user_id, 'enrolled_course_class_' . $course_id, true );
				$m_enrolled_course_date  = get_user_meta( $user_id, 'enrolled_course_date_' . $course_id, true );
				$m_enrolled_course_group = get_user_meta( $user_id, 'enrolled_course_group_' . $course_id, true );

				update_user_option( $user_id, 'enrolled_course_class_' . $course_id, $m_enrolled_course_class, $global_option );
				update_user_option( $user_id, 'enrolled_course_date_' . $course_id, $m_enrolled_course_date, $global_option );
				update_user_option( $user_id, 'enrolled_course_group_' . $course_id, $m_enrolled_course_group, $global_option );

				delete_user_meta( $user_id, 'enrolled_course_date_' . $course_id );
				delete_user_meta( $user_id, 'enrolled_course_class_' . $course_id );
				delete_user_meta( $user_id, 'enrolled_course_group_' . $course_id );

				/* Other meta to upgrade */
				$field_patterns = array(
					'visited_units',
					'last_visited_unit_.*_page',
					'visited_unit_pages_.*_page',
					'visited_course_units_.*',
				);

				$meta = get_user_meta( $user_id );

				foreach ( $meta as $key => $value ) {
					foreach ( $field_patterns as $pattern ) {

						if ( preg_match( '/^' . $pattern . '/', $key ) ) {
							$new_val = array_pop( $value );

							update_user_option( $user_id, $key, $new_val, $global_option );
							delete_user_meta( $user_id, $key );
						}
					}
				}
			}
		}

		/* Upgrade Instructor Meta */

		function upgrade_instructor_meta() {
			global $wpdb;

			$user_id          = get_current_user_id();
			$original_blog_id = get_current_blog_id();
			// If they are not an instructor, don't do it
			if ( get_user_meta( $user_id, 'role_ins' ) ) {

				// Please note: Patterns uses capturing groups which allows us to pull blog_id and course_id from the matches.
				$pattern = '';
				if ( is_multisite() ) {
					$pattern = '/^' . $wpdb->base_prefix . '(?<=' . $wpdb->base_prefix . ')(\d*)_course_(\d*)$/';
				} else {
					// Nothing to do for single site.
					return false;
				}

				$all_meta = get_user_meta( $user_id );

				foreach ( $all_meta as $meta_key => $meta_value ) {

					$matches = '';
					if ( preg_match( $pattern, $meta_key, $matches ) ) {
						$blog_id   = (int) $matches[1] != 0 ? $matches[1] : '';
						$course_id = $matches[2];

						// Use update_user_meta for some extra control
						if ( ! empty( $blog_id ) ) {

							// Deal with inconsistency in *_user_option() functions for first blog
							if ( 1 == $blog_id ) {
								update_user_meta( $user_id, $wpdb->base_prefix . 'role_ins', 'instructor' );
							} else {
								update_user_meta( $user_id, $wpdb->base_prefix . $blog_id . '_role_ins', 'instructor' );
							}

							switch_to_blog( $blog_id );
							$instructors = get_post_meta( $course_id, 'instructors', true );

							if(!is_array($instructors)) {
								$instructors = array();
							}
							// User is not yet an instructor
							if ( ! in_array( $user_id, $instructors ) ) {
								$instructors[] = $user_id;
								update_post_meta( $course_id, 'instructors', $instructors );
							}
						} else {
							// Update failed...
							return false;
						}

						switch_to_blog( $original_blog_id );
						delete_user_meta( $user_id, 'role_ins' );
					}
				}
			}
		}

		/* Force requested file downlaod */

		function check_for_force_download_file_request() {

			if ( isset( $_GET['fdcpf'] ) ) {
				ob_start();

				require_once( $this->plugin_dir . 'includes/classes/class.encryption.php' );
				$encryption     = new CP_Encryption();
				$requested_file = $encryption->decode( $_GET['fdcpf'] );

				$requested_file_obj = wp_check_filetype( $requested_file );
				header( 'Pragma: public' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check = 0, pre-check = 0' );
				header( 'Cache-Control: private', false );
				header( 'Content-Type: ' . $requested_file_obj["type"] );
				header( 'Content-Disposition: attachment; filename ="' . basename( $requested_file ) . '"' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Connection: close' );

				/**
				 * Filter used to alter header params. E.g. removing 'timeout'.
				 */
				$force_download_parameters = apply_filters( 'coursepress_force_download_parameters', array(
					'timeout'    => 60,
					'user-agent' => $this->name . ' / ' . $this->version . ';'
				) );
				echo wp_remote_retrieve_body( wp_remote_get( $requested_file ), $force_download_parameters );
				exit();
			}
		}

		function course_archive( $query ) {
			if ( is_post_type_archive( 'course' ) ) {
				$selected_course_order_by_type = get_option( 'course_order_by_type', 'DESC' );
				$selected_course_order_by      = get_option( 'course_order_by', 'post_date' );

				if ( $selected_course_order_by == 'course_order' ) {
					set_query_var( 'meta_query', array(
							'relation' => 'OR',
							array(
								'key'     => 'course_order',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key'     => 'course_order',
								'compare' => 'EXISTS',
							)
						)
					);
					set_query_var( 'meta_key', 'course_order' );
					set_query_var( 'orderby', 'meta_value' );
					set_query_var( 'order', $selected_course_order_by_type );
				} else {
					set_query_var( 'orderby', $selected_course_order_by );
					set_query_var( 'order', $selected_course_order_by_type );
				}
				//$query->set( 'post_status', 'published' );
			}
		}

		function course_archive_categories() {
			global $wp_query;
			if ( isset( $wp_query->query_vars['taxonomy'] ) && $wp_query->query_vars['taxonomy'] == 'course_category' ) {
				add_filter( 'the_content', array(
					&$this,
					'add_custom_before_course_single_content_course_category_archive'
				), 1 );
			}
		}

		function load_plugin_templates() {
			global $wp_query;

			if ( get_query_var( 'course' ) != '' ) {
				add_filter( 'the_content', array( &$this, 'add_custom_before_course_single_content' ), 1 );
				//add_filter( 'the_excerpt', array( &$this, 'add_custom_before_course_single_content' ), 1 );
			}

			if ( get_post_type() == 'course' && is_archive() ) {
				add_filter( 'the_content', array( &$this, 'courses_archive_custom_content' ), 1 );
				add_filter( 'the_excerpt', array( &$this, 'courses_archive_custom_content' ), 1 );
				add_filter( 'get_the_excerpt', array( &$this, 'courses_archive_custom_content' ), 1 );
			}

			if ( get_post_type() == 'discussions' && is_single() ) {
				add_filter( 'the_content', array( &$this, 'add_custom_before_discussion_single_content' ), 1 );
			}

			if ( is_post_type_archive( 'course' ) ) {
				add_filter( 'post_type_archive_title', array( &$this, 'courses_archive_title' ), 1 );
			}
		}

		function remove_canonical( $wp_query ) {

			global $wp_query;
			if ( is_admin() || empty( $wp_query ) ) {
				return;
			}

			//stop canonical problems with virtual pages redirecting
			$page   = get_query_var( 'pagename' );
			$course = get_query_var( 'course' );

			if ( $page == 'dashboard' or $course !== '' ) {
				remove_action( 'template_redirect', 'redirect_canonical' );
			}
		}

		function action_parse_request( &$wp ) {
			global $wp_query;

			do_action( 'coursepress_pre_parse_action' );

			/* Show instructor invite pages */
			$pg = $this->instructor_invite_confirmation();

			/* Show Units archive template */
			if ( array_key_exists( 'course_category', $wp->query_vars ) ) {

				$category_template_file = locate_template( array( 'archive-course-' . $wp->query_vars['course_category'] . '.php' ) );

				if ( $category_template_file != '' ) {
					do_shortcode( '[courses_loop]' );
					require_once( $category_template_file );
					exit;
				} else {
					$theme_file = locate_template( array( 'archive-course.php' ) );

					if ( $theme_file != '' ) {
						do_shortcode( '[courses_loop]' );
						require_once( $theme_file );
						exit;
					} else {
						$theme_file = locate_template( array( 'archive.php' ) );
						if ( $theme_file != '' ) {
							do_shortcode( '[courses_loop]' );
							require_once( $theme_file );
							exit;
						}
					}
				}
			}

			/* Show Discussion single template */
			if ( array_key_exists( 'discussion_name', $wp->query_vars ) ) {
				$this->remove_pre_next_post();
				$vars['discussion_name'] = $wp->query_vars['discussion_name'];
				$vars['course_id']       = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			}

			/* Add New Discussion template */

			if ( array_key_exists( 'discussion_archive', $wp->query_vars ) || ( array_key_exists( 'discussion_name', $wp->query_vars ) && $wp->query_vars['discussion_name'] == $this->get_discussion_slug_new() ) ) {
				$this->remove_pre_next_post();
				$vars['course_id'] = Course::get_course_id_by_name( $wp->query_vars['coursename'] );

				if ( ( array_key_exists( 'discussion_name', $wp->query_vars ) && $wp->query_vars['discussion_name'] == $this->get_discussion_slug_new() ) ) {
					$this->units_archive_subpage = 'discussions';

					$theme_file = locate_template( array( 'page-add-new-discussion.php' ) );

					if ( $theme_file != '' ) {
						require_once( $theme_file );
						exit;
					} else {
						$args = array(
							'slug'        => $wp->request,
							'title'       => __( 'Add New Discussion', 'cp' ),
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/page-add-new-discussion.php', $vars ),
							'type'        => 'discussion',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);
						$pg   = new CoursePress_Virtual_Page( $args );
					}
				} else {

					/* Archive Discussion template */
					$this->units_archive_subpage = 'discussions';
					$theme_file                  = locate_template( array( 'archive-discussions.php' ) );

					if ( $theme_file != '' ) {
						//do_shortcode( '[course_notifications_loop]' );
						require_once( $theme_file );
						exit;
					} else {
						$course_id = do_shortcode( '[get_parent_course_id]' );

						// DISCUSSIONS

						$args = array(
							'slug'        => $wp->request,
							'title'       => get_the_title( $course_id ),
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/course-discussion-archive.php', $vars ),
							'type'        => 'discussions',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);
						$pg   = new CoursePress_Virtual_Page( $args );
						do_shortcode( '[course_discussion_loop]' );
					}
				}
			}

			/* Show Instructor single template only if the user is an instructor of at least 1 course */
			if ( array_key_exists( 'instructor_username', $wp->query_vars ) ) {//&& 0 < Instructor::get_courses_number( cp_get_userdatabynicename( $wp->query_vars[ 'instructor_username' ] )->ID ) 
				$this->remove_pre_next_post();
				$vars                        = array();
				$vars['instructor_username'] = $wp->query_vars['instructor_username'];

				$user = wp_cache_get( $wp->query_vars['instructor_username'], 'cp_instructor_hash' );

				if ( false === $user ) {
					if ( get_option( 'show_instructor_username', 1 ) == 1 ) {
						$username = str_replace( '%20', ' ', $wp->query_vars['instructor_username'] ); //support for usernames with spaces
						$user     = Instructor::instructor_by_login( $username );
					} else {
						$user = Instructor::instructor_by_hash( $wp->query_vars['instructor_username'] );
						wp_cache_set( $wp->query_vars['instructor_username'], $user, 'cp_instructor_hash' );
					}
				}

				$vars['user'] = $user;

				$theme_file = locate_template( array( 'single-instructor.php' ) );

				// $course_count = Instructor::get_courses_number( $vars['user']->ID );
				// if ( $course_count <= 1 ) {
				// 	exit;
				// }

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {

					$args = array(
						'slug'    => $wp->request,
						'title'   => __( $vars['user']->display_name, 'cp' ),
						'content' => $this->get_template_details( $this->plugin_dir . 'includes/templates/instructor-single.php', $vars ),
						'type'    => 'virtual_page'
					);

					$pg = new CoursePress_Virtual_Page( $args );
				}
				$user_id = get_current_user_id();
				$this->set_latest_activity( $user_id );
			}

			$url = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

			$inbox_page         = false;
			$new_message_page   = false;
			$sent_messages_page = false;

			if ( preg_match( '/' . $this->get_inbox_slug() . '/', $url ) ) {
				$inbox_page = true;
			}

			if ( preg_match( '/' . $this->get_new_message_slug() . '/', $url ) ) {
				$new_message_page = true;
			}

			if ( preg_match( '/' . $this->get_sent_messages_slug() . '/', $url ) ) {
				$sent_messages_page = true;
			}

			if ( $inbox_page ) {

				$this->inbox_subpage = 'inbox';

				$theme_file = locate_template( array( 'page-inbox.php' ) );

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {
					$args = array(
						'slug'        => $wp->request,
						'title'       => __( 'Inbox', 'cp' ),
						'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/page-inbox.php', array(), true ),
						'type'        => 'page',
						'is_page'     => true,
						'is_singular' => true,
						'is_archive'  => false
					);
					$pg   = new CoursePress_Virtual_Page( $args );
				}
			}

			if ( $sent_messages_page ) {

				$this->inbox_subpage = 'sent_messages';

				$theme_file = locate_template( array( 'page-sent-messages.php' ) );

				if ( $theme_file != '' ) {
					//do_shortcode( '[course_units_loop]' );
					require_once( $theme_file );
					exit;
				} else {
					$args = array(
						'slug'        => $wp->request,
						'title'       => __( 'Sent Message', 'cp' ),
						'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/page-sent-messages.php', array(), true ),
						'type'        => 'page',
						'is_page'     => true,
						'is_singular' => true,
						'is_archive'  => false
					);
					$pg   = new CoursePress_Virtual_Page( $args );
				}
			}

			if ( $new_message_page ) {

				$this->inbox_subpage = 'new_message';

				$theme_file = locate_template( array( 'page-new-message.php' ) );

				if ( $theme_file != '' ) {
					//do_shortcode( '[course_units_loop]' );
					require_once( $theme_file );
					exit;
				} else {
					$args = array(
						'slug'        => $wp->request,
						'title'       => __( 'New Message', 'cp' ),
						'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/page-new-message.php', array(), true ),
						'type'        => 'page',
						'is_page'     => true,
						'is_singular' => true,
						'is_archive'  => false
					);
					$pg   = new CoursePress_Virtual_Page( $args );
				}
			}

			/* Show Units archive template */
			if ( array_key_exists( 'coursename', $wp->query_vars ) && ! array_key_exists( 'unitname', $wp->query_vars ) ) {
				$this->remove_pre_next_post();
				$units_archive_page         = false;
				$units_archive_grades_page  = false;
				$notifications_archive_page = false;
				$units_workbook_page        = false;

				$url = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

				if ( preg_match( '/' . $this->get_units_slug() . '/', $url ) ) {
					$units_archive_page = true;
				}

				if ( preg_match( '/' . $this->get_grades_slug() . '/', $url ) ) {
					$units_archive_grades_page = true;
				}

				if ( preg_match( '/' . $this->get_workbook_slug() . '/', $url ) ) {
					$units_workbook_page = true;
				}

				if ( preg_match( '/' . $this->get_notifications_slug() . '/', $url ) ) {
					$notifications_archive_page = true;
				}

				$vars              = array();
				$vars['course_id'] = Course::get_course_id_by_name( $wp->query_vars['coursename'] );

				if ( $notifications_archive_page ) {
					$this->units_archive_subpage = 'notifications';

					$theme_file = locate_template( array( 'archive-notifications.php' ) );

					if ( $theme_file != '' ) {
						do_shortcode( '[course_notifications_loop]' );
						require_once( $theme_file );
						exit;
					} else {
						$course_id = do_shortcode( '[get_parent_course_id]' );

						// NOTIFICATIONS

						$args = array(
							'slug'        => $wp->request,
							'title'       => get_the_title( $course_id ),
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/course-notifications-archive.php', $vars ),
							'type'        => 'notifications',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);

						$pg = new CoursePress_Virtual_Page( $args );
						do_shortcode( '[course_notifications_loop]' );
					}
				}

				if ( $units_archive_page ) {
					$this->units_archive_subpage = 'units';

					$theme_file = locate_template( array( 'archive-unit.php' ) );

					if ( $theme_file != '' ) {
//						do_shortcode( '[course_units_loop]' );
						require_once( $theme_file );
						exit;
					} else {
						$course_id = do_shortcode( '[get_parent_course_id]' );

						// COURSE UNITS

						$args = array(
							'slug'        => $wp->request,
							// 'title' => __( 'Course Units', 'cp' ),
							'title'       => get_the_title( $course_id ),
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/course-units-archive.php', $vars ),
							'type'        => 'unit',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);
						$pg   = new CoursePress_Virtual_Page( $args );
						do_shortcode( '[course_units_loop]' );
					}

					$user_id = get_current_user_id();
					$this->set_latest_activity( $user_id );
					$this->upgrade_user_meta( $user_id, $course_id );
				}

				if ( $units_archive_grades_page ) {

					$this->units_archive_subpage = 'grades';

					$theme_file = locate_template( array( 'archive-unit-grades.php' ) );

					if ( $theme_file != '' ) {
						do_shortcode( '[course_units_loop]' );
						require_once( $theme_file );
						exit;
					} else {
						$course_id = do_shortcode( '[get_parent_course_id]' );

						// COURSE GRADES

						$args = array(
							'slug'        => $wp->request,
							'title'       => get_the_title( $course_id ),
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/course-units-archive-grades.php', $vars ),
							'type'        => 'unit',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);
						$pg   = new CoursePress_Virtual_Page( $args );
						do_shortcode( '[course_units_loop]' );
					}
					$this->set_latest_activity( get_current_user_id() );
				}

				if ( $units_workbook_page ) {

					$this->units_archive_subpage = 'workbook';

					$theme_file = locate_template( array( 'archive-unit-workbook.php' ) );

					if ( $theme_file != '' ) {
						do_shortcode( '[course_units_loop]' );
						require_once( $theme_file );
						exit;
					} else {
						$course_id = do_shortcode( '[get_parent_course_id]' );

						// WORKBOOK

						do_shortcode( '[course_units_loop]' );
						$args = array(
							'slug'        => $wp->request,
							'title'       => get_the_title( $course_id ),
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/archive-unit-workbook.php', $vars ),
							'type'        => 'unit',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);
						$pg   = new CoursePress_Virtual_Page( $args );
						do_shortcode( '[course_units_loop]' );
					}
					$this->set_latest_activity( get_current_user_id() );
				}
			}


			/* Show Unit single template */
			if ( array_key_exists( 'coursename', $wp->query_vars ) && array_key_exists( 'unitname', $wp->query_vars ) ) {
				$this->remove_pre_next_post();
				$vars = array();
				$unit = new Unit();

				$vars['course_id'] = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
				$vars['unit_id']   = $unit->get_unit_id_by_name( $wp->query_vars['unitname'], $vars['course_id'] );

				//$this->set_course_visited( get_current_user_id(), Course::get_course_id_by_name( $wp->query_vars['coursename'] ) );

				$unit = new Unit( $vars['unit_id'] );

				$this->set_unit_visited( get_current_user_id(), $vars['unit_id'] );

				$theme_file = locate_template( array( 'single-unit.php' ) );

				$forced_previous_completion_template = locate_template( array( 'single-previous-unit.php' ) );

				if ( ! Unit::is_unit_available( $vars['unit_id'] ) && ( ! current_user_can( 'manage_options' ) && ! CoursePress_Capabilities::can_update_course( $vars['course_id'] ) ) ) {
					if ( $forced_previous_completion_template != '' ) {
						do_shortcode( '[course_unit_single]' ); //required for getting unit results
						require_once( $forced_previous_completion_template );
						exit;
					} else {
						$args = array(
							'slug'        => $wp->request,
							'title'       => $unit->details->post_title,
							'content'     => __( 'This Unit is not available at the moment. Please check back later.', 'cp' ),
							'type'        => 'page',
							'is_page'     => true,
							'is_singular' => false,
							'is_archive'  => false
						);

						$pg = new CoursePress_Virtual_Page( $args );
					}
				} else {
					if ( $theme_file != '' ) {
						global $wp;
						do_shortcode( '[course_unit_single unit_id="' . $vars['unit_id'] . '"]' ); //required for getting unit results
						require_once( $theme_file );
						do_action( 'wp' ); //fix for gravity
						exit;
					} else {
						$args = array(
							'slug'        => $wp->request,
							// 'title' => $unit->details->post_title,
							'title'       => isset( $unit->details ) ? get_the_title( $unit->details->post_parent ) : '',
							'content'     => $this->get_template_details( $this->plugin_dir . 'includes/templates/course-units-single.php', $vars ),
							'type'        => 'unit',
							'is_page'     => true,
							'is_singular' => true,
							'is_archive'  => false
						);

						$pg = new CoursePress_Virtual_Page( $args );
					}
					$this->set_latest_activity( get_current_user_id() );
				}
			}
		}

		//function set_course_visited( $user_ID, $course_ID ) {
		//  $global_option = ! is_multisite();
		//	$get_old_values = get_user_option( 'visited_courses', $user_ID );
		//	if ( !cp_in_array_r( $course_ID, $get_old_values ) ) {
		//		$get_old_values[] = $course_ID;
		//	}
		//	update_user_option( $user_ID, 'visited_courses', $get_old_values, $global_option );
		//}

		/* Set that student read unit */

		function set_unit_visited( $user_ID, $unit_ID ) {
			$global_option  = ! is_multisite();
			$get_old_values = get_user_option( 'visited_units' );
			$get_new_values = explode( '|', $get_old_values );

			if ( ! cp_in_array_r( $unit_ID, $get_new_values ) ) {
				$get_old_values = $get_old_values . '|' . $unit_ID;
				update_user_option( $user_ID, 'visited_units', $get_old_values, $global_option );
			}
		}

		function filter_query_vars( $query_vars ) {
			$query_vars[] = 'coursename';
			$query_vars[] = 'course_category';
			$query_vars[] = 'unitname';
			$query_vars[] = 'instructor_username';
			$query_vars[] = 'discussion_name';
			$query_vars[] = 'discussion_archive';
			$query_vars[] = 'notifications_archive';
			$query_vars[] = 'grades_archive';
			$query_vars[] = 'workbook';
			$query_vars[] = 'discussion_action';
			$query_vars[] = 'inbox';
			$query_vars[] = 'new_message';
			$query_vars[] = 'sent_messages';
			$query_vars[] = 'paged';

			return $query_vars;
		}

		function add_rewrite_rules( $rules ) {
			$new_rules = array();

			$new_rules[ '^' . $this->get_course_slug() . '/' . $this->get_course_category_slug() . '/([^/]*)/page/([^/]*)/?' ] = 'index.php?page_id=-1&course_category=$matches[1]&paged=$matches[2]';
			$new_rules[ '^' . $this->get_course_slug() . '/' . $this->get_course_category_slug() . '/([^/]*)/?' ]              = 'index.php?page_id=-1&course_category=$matches[1]';

			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_discussion_slug() . '/page/([^/]*)/?' ] = 'index.php?page_id=-1&coursename=$matches[1]&discussion_archive&paged=$matches[2]'; ///page/?( [0-9]{1,} )/?$
			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_discussion_slug() . '/([^/]*)/?' ]      = 'index.php?page_id=-1&coursename=$matches[1]&discussion_name=$matches[2]';
			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_discussion_slug() ]                     = 'index.php?page_id=-1&coursename=$matches[1]&discussion_archive';

			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_grades_slug() ]   = 'index.php?page_id=-1&coursename=$matches[1]&grades_archive';
			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_workbook_slug() ] = 'index.php?page_id=-1&coursename=$matches[1]&workbook';

			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_units_slug() . '/([^/]*)/page/([^/]*)/?' ] = 'index.php?page_id=-1&coursename=$matches[1]&unitname=$matches[2]&paged=$matches[3]'; ///page/?( [0-9]{1,} )/?$
			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_units_slug() . '/([^/]*)/?' ]              = 'index.php?page_id=-1&coursename=$matches[1]&unitname=$matches[2]';
			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_units_slug() ]                             = 'index.php?page_id=-1&coursename=$matches[1]';

			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_notifications_slug() . '/page/([^/]*)/?' ] = 'index.php?page_id=-1&coursename=$matches[1]&notifications_archive&paged=$matches[2]'; ///page/?( [0-9]{1,} )/?$
			$new_rules[ '^' . $this->get_course_slug() . '/([^/]*)/' . $this->get_notifications_slug() ]                     = 'index.php?page_id=-1&coursename=$matches[1]&notifications_archive';


			$new_rules[ '^' . $this->get_instructor_profile_slug() . '/([^/]*)/?' ] = 'index.php?page_id=-1&instructor_username=$matches[1]';
			//Remove potential conflicts between single and virtual page on single site
			/* if ( !is_multisite() ) {
			  unset( $rules['( [^/]+ )( /[0-9]+ )?/?$'] );
			  } */

			$new_rules[ '^' . $this->get_inbox_slug() . '/?' ]         = 'index.php?page_id=-1&inbox';
			$new_rules[ '^' . $this->get_new_message_slug() . '/?' ]   = 'index.php?page_id=-1&new_message';
			$new_rules[ '^' . $this->get_sent_messages_slug() . '/?' ] = 'index.php?page_id=-1&sent_messages';

			/* Resolve possible issue with rule formating and avoid 404s */
			foreach ( $new_rules as $new_rule => $value ) {
				$newer_rule = str_replace( ' ', '', $new_rule );
				unset( $new_rules[ $new_rule ] );
				$new_rules[ $newer_rule ] = $value;
			}

			return array_merge( $new_rules, $rules );
		}

		function add_custom_before_course_single_content( $content ) {
			global $wpdb;

			if ( get_post_type() == 'course' ) {
				if ( is_single() ) {
					if ( $theme_file = locate_template( array( 'single-course.php' ) ) ) {
						//template will take control of the look so don't do anything
					} else {
						wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
						if ( locate_template( array( 'single-course.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
							//just output the content
						} else {

							//if ( get_post_type( $wpdb->last_result[ 0 ]->post_id ) == 'course' ) {
							if ( get_post_type() == 'course' ) {
								$prepend_content = $this->get_template_details( $this->plugin_dir . 'includes/templates/single-course-before-details.php' );
								$content         = do_shortcode( $prepend_content . $content );
							} else {
								return $content;
							}
						}
					}
				}
			}

			return $content;
		}

		function add_custom_before_discussion_single_content( $content ) {


			if ( $theme_file = locate_template( array( 'single-discussions.php' ) ) ) {
				//template will take control of the look so don't do anything
			} else {

				if ( locate_template( array( 'single-discussions.php' ) ) ) {//add custom content in the single template ONLY if the post type doesn't already has its own template
					//just output the content
				} else {
					$prepend_content = $this->get_template_details( $this->plugin_dir . 'includes/templates/single-discussion-before-details.php' );
					$content         = do_shortcode( $prepend_content . $content );
				}
			}

			return $content;
		}

		function add_custom_before_course_single_content_course_category_archive( $content ) {
			if ( locate_template( array( 'archive-course.php' ) ) ) {
				return $post->post_excerpt;
			}

			$post_type = get_post_type( $GLOBALS['post']->ID );

			if ( $post_type == 'course' ) {
				include( $this->plugin_dir . 'includes/templates/archive-courses-single.php' );
			} else {
				return $content;
			}
		}

		function courses_archive_custom_content( $content ) {
			global $wp, $post, $content_shown;
			//array_key_exists( 'course_category', $wp->query_vars )
			if ( locate_template( array( 'archive-course.php' ) ) ) {
				return $post->post_excerpt;
			}

			if ( ! isset( $content_shown[ $GLOBALS['post']->ID ] ) || $content_shown[ $GLOBALS['post']->ID ] !== 1 ) {//make sure that we don't apply the filter on more than one content / excerpt on the page per post
				global $wpdb;
				if ( ( ! empty( $wpdb->last_result ) && ! empty( $wpdb->last_result[0]->post_id ) && 'course' == get_post_type( $wpdb->last_result[0]->post_id ) ) || 'course' == get_post_type() ) {
					// cp_write_log( get_post_type() );
					include( $this->plugin_dir . 'includes/templates/archive-courses-single.php' );
				} else {
					return $content;
				}
				if ( ! isset( $content_shown[ $GLOBALS['post']->ID ] ) ) {
					$content_shown[ $GLOBALS['post']->ID ] = 1;
				} else {
					$content_shown[ $GLOBALS['post']->ID ] ++;
				}
			}
		}

		function courses_archive_title( $title ) {
			return __( 'All Courses', 'cp' );
		}

		function get_template_details( $template, $args = array(), $remove_wpautop = false ) {
			ob_start();
			if ( $remove_wpautop ) {
				remove_filter( 'the_content', 'wpautop' );
			}
			extract( $args );
			include_once( $template );
			$content = ob_get_clean();

			return $content;
		}

		function update_units_positions() {
			global $wpdb;

			$positions = explode( ",", $_REQUEST['positions'] );
			$response  = '';
			$i         = 1;
			foreach ( $positions as $position ) {
				$response .= 'Position #' . $i . ': ' . $position . '<br />';
				update_post_meta( $position, 'unit_order', $i );
				$i ++;
			}
			//echo $response; //just for debugging purposes
			die();
		}

		function update_course_positions() {
			global $wpdb;

			$course_page_number = $_REQUEST['course_page_number'] * 999;
			$positions          = explode( ",", $_REQUEST['positions'] );
			$response           = '';
			$i                  = 1;
			foreach ( $positions as $position ) {
				$response .= 'Position #' . $i . ': ' . $position . '<br />';
				update_post_meta( $position, 'course_order', (int) $course_page_number + (int) $i );
				/* $post = array( 'ID'		 => $position,
				  'menu_order' => $i ); */
				wp_update_post( $post );
				$i ++;
			}
			//echo $response; //just for debugging purposes
			die();
		}

		function dev_check_current_screen() {
			if ( ! is_admin() ) {
				return;
			}

			global $current_screen;

			print_r( $current_screen );
		}

		function plugin_activation() {

			// Register types to register the rewrite rules
			$this->register_custom_posts();

			// Then flush them (run it through a check first)
			cp_flush_rewrite_rules();

			//First install
			$installed = get_option( 'cp_first_install', false );
			if ( ! $installed ) {
				first_install();
			}

			//Welcome Screen
			//$this->coursepress_plugin_do_activation_redirect();
		}

		function install() {
			update_option( 'display_menu_items', 1 );
			$this->coursepress_plugin_activate();
			update_option( 'coursepress_version', $this->version );
			$this->add_user_roles_and_caps(); //This setting is saved to the database ( in table wp_options, field wp_user_roles ), so it might be better to run this on theme/plugin activation
			//Set default course groups
			if ( ! get_option( 'course_groups' ) ) {
				$default_groups = range( 'A', 'Z' );
				update_option( 'course_groups', $default_groups );
			}

			//Redirect to Create New Course page
			require( ABSPATH . WPINC . '/pluggable.php' );

			//add_action( 'admin_init', 'my_plugin_redirect' );


			$this->plugin_activation();
		}

		function coursepress_plugin_do_activation_redirect() {
			// if( defined( 'DOING_AJAX' ) && DOING_AJAX ) { cp_write_log( 'doing ajax' ); }
			if ( get_option( 'coursepress_plugin_do_first_activation_redirect', false ) ) {
				ob_start();
				delete_option( 'coursepress_plugin_do_first_activation_redirect' );
				wp_redirect( admin_url( 'admin.php?page = courses&quick_setup' ) );
				ob_end_clean();
				exit;
			}
		}

		function coursepress_plugin_activate() {
			add_option( 'coursepress_plugin_do_first_activation_redirect', true );
		}

		/* SLUGS */

		function set_course_slug( $slug = '' ) {
			if ( $slug == '' ) {
				update_option( 'coursepress_course_slug', get_course_slug() );
			} else {
				update_option( 'coursepress_course_slug', $slug );
			}
		}

		function get_course_slug( $url = false ) {
			$default_slug_value = 'courses';
			if ( ! $url ) {
				return get_option( 'coursepress_course_slug', $default_slug_value );
			} else {
				return home_url() . '/' . get_option( 'coursepress_course_slug', $default_slug_value );
			}
		}

		function get_module_slug() {
			$default_slug_value = 'module';

			return get_option( 'coursepress_module_slug', $default_slug_value );
		}

		function get_units_slug() {
			$default_slug_value = 'units';

			return get_option( 'coursepress_units_slug', $default_slug_value );
		}

		function get_notifications_slug() {
			$default_slug_value = 'notifications';

			return get_option( 'coursepress_notifications_slug', $default_slug_value );
		}

		function get_discussion_slug() {
			$default_slug_value = 'discussion';

			return get_option( 'coursepress_discussion_slug', $default_slug_value );
		}

		function get_course_category_slug() {
			$default_slug_value = 'course_category';

			return get_option( 'coursepress_course_category_slug', $default_slug_value );
		}

		function get_grades_slug() {
			$default_slug_value = 'grades';

			return get_option( 'coursepress_grades_slug', $default_slug_value );
		}

		function get_workbook_slug() {
			$default_slug_value = 'workbook';

			return get_option( 'coursepress_workbook_slug', $default_slug_value );
		}

		function get_inbox_slug( $url = false ) {
			$default_slug_value = 'student-inbox';

			if ( ! $url ) {
				return get_option( 'coursepress_inbox_slug', $default_slug_value );
			} else {
				return trailingslashit( home_url() . '/' . get_option( 'coursepress_inbox_slug', $default_slug_value ) );
			}
		}

		function get_new_message_slug( $url = false ) {
			$default_slug_value = 'student-new-message';
			if ( ! $url ) {
				return get_option( 'coursepress_new_message_slug', $default_slug_value );
			} else {
				return trailingslashit( home_url() . '/' . get_option( 'coursepress_new_message_slug', $default_slug_value ) );
			}
		}

		function get_sent_messages_slug( $url = false ) {
			$default_slug_value = 'student-sent-messages';
			if ( ! $url ) {
				return get_option( 'coursepress_sent_messages_slug', $default_slug_value );
			} else {
				return trailingslashit( home_url() . '/' . get_option( 'coursepress_sent_messages_slug', $default_slug_value ) );
			}
		}

		function get_discussion_slug_new() {
			$default_slug_value = 'add_new_discussion';

			return get_option( 'coursepress_discussion_slug_new', $default_slug_value );
		}

		function get_enrollment_process_slug( $url = false ) {
			$default_slug_value = 'enrollment-process';
			$option             = 'enrollment_process_slug';
			$page_option        = 'coursepress_enrollment_process_page';

			return $this->get_slug_variant( $option, $page_option, $default_slug_value, $url );
		}

		function get_student_dashboard_slug( $url = false ) {
			$default_slug_value = 'courses-dashboard';
			$option             = 'student_dashboard_slug';
			$page_option        = 'coursepress_student_dashboard_page';

			return $this->get_slug_variant( $option, $page_option, $default_slug_value, $url );
		}

		function get_student_settings_slug( $url = false ) {
			$default_slug_value = 'settings';
			$option             = 'student_settings_slug';
			$page_option        = 'coursepress_student_settings_page';

			return $this->get_slug_variant( $option, $page_option, $default_slug_value, $url );
		}

		function get_instructor_profile_slug() {
			$default_slug_value = 'instructor';

			return get_option( 'instructor_profile_slug', $default_slug_value );
		}

		function get_login_slug( $url = false ) {
			$default_slug_value = 'student-login';
			$option             = 'login_slug';
			$page_option        = 'coursepress_login_page';

			return $this->get_slug_variant( $option, $page_option, $default_slug_value, $url );
		}

		function get_signup_slug( $url = false ) {
			$default_slug_value = 'courses-signup';
			$option             = 'signup_slug';
			$page_option        = 'coursepress_signup_page';

			return $this->get_slug_variant( $option, $page_option, $default_slug_value, $url );
		}

		function get_slug_variant( $option, $page_option, $default_slug_value, $url = false ) {
			$return_value = false;
			if ( ! $url ) {
				$return_value = get_option( $option, $default_slug_value );
			} else {
				$custom = get_option( $page_option, 0 );

				if ( ! empty( $custom ) ) {
					if ( empty( $GLOBALS['wp_rewrite'] ) ) {
						$GLOBALS['wp_rewrite'] = new WP_Rewrite();
					}
					$return_value = trailingslashit( get_permalink( (int) $custom ) );
				} else {
					$return_value = trailingslashit( home_url( trailingslashit( get_option( $option, $default_slug_value ) ) ) );
				}
			}

			return apply_filters( 'coursepress_slug_return', $return_value, $option, $page_option, $default_slug_value, $url );
		}

		function localization() {
			// Load up the localization file if we're using WordPress in a different language
			if ( $this->location == 'mu-plugins' ) {
				load_muplugin_textdomain( 'cp', '/languages/' );
			} else if ( $this->location == 'subfolder-plugins' ) {
				load_plugin_textdomain( 'cp', false, $this->dir_name . '/languages/' );
			} else if ( $this->location == 'plugins' ) {
				load_plugin_textdomain( 'cp', false, '/languages/' );
			}
		}

		function init_vars() {
			//setup proper directories
			if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename( __FILE__ ) ) ) {
				$this->location   = 'subfolder-plugins';
				$this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
				$this->plugin_url = plugins_url( '/', __FILE__ );
			} else if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location   = 'plugins';
				$this->plugin_dir = WP_PLUGIN_DIR . '/';
				$this->plugin_url = plugins_url( '/', __FILE__ );
			} else if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
				$this->location   = 'mu-plugins';
				$this->plugin_dir = WPMU_PLUGIN_DIR;
				$this->plugin_url = WPMU_PLUGIN_URL;
			} else {
				wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'cp' ), $this->name ) );
			}

			$this->screen_base      = str_replace( ' ', '-', strtolower( $this->name ) );
			$GLOBALS['screen_base'] = $this->screen_base;
		}

		//Load unit elements / modules / building blocks and other add-ons and plugins
		function load_modules() {

			global $mem_modules;

			$dir = $this->plugin_dir . 'includes/unit-modules/';

			$mem_modules = apply_filters( 'coursepress_mem_modules_files', array(
				$dir . 'audio.php',
				$dir . 'chat.php',
				$dir . 'checkbox_input.php',
				$dir . 'file.php',
				$dir . 'file_input.php',
				$dir . 'image.php',
				$dir . 'page_break.php',
				$dir . 'radio_input.php',
				$dir . 'section_break.php',
				$dir . 'text.php',
				$dir . 'text_input.php',
				$dir . 'video.php'
			) );
			sort( $mem_modules );

			foreach ( $mem_modules as $mem_module ) {
				include_once( $mem_module );
			}

			$this->load_certificate_template_elements();

			do_action( 'coursepress_modules_loaded' );
		}

		function load_certificate_template_elements() {

			$dir = $this->plugin_dir . 'includes/certificate-elements/';

			$certificate_template_elements = apply_filters( 'coursepress_certificate_template_elements_files', array(
				$dir . 'text.php',
				$dir . 'course_name.php',
				$dir . 'issue_date.php',
				$dir . 'logo.php',
				$dir . 'student_name.php',
				$dir . 'website.php',
			) );
			sort( $certificate_template_elements );

			//include them suppressing errors
			foreach ( $certificate_template_elements as $file ) {
				include( $file );
			}

			//allow plugins from an external location to register themselves
			do_action( 'coursepress_load_certificate_template_elements' );
		}

		function load_widgets() {

			$dir = $this->plugin_dir . '/includes/widgets/';

			$widgets = apply_filters( 'coursepress_widget_files', array(
				$dir . 'course-calendar.php',
				$dir . 'course-categories.php',
				$dir . 'course-structure.php',
				$dir . 'featured-course.php',
				$dir . 'latest-courses.php'
			) );

			sort( $widgets );

			foreach ( $widgets as $file ) {
				include_once( $file );
			}
		}

		function add_admin_menu_network() {
			//special menu for network admin
		}

		//Add plugin admin menu items
		function add_admin_menu() {
			// Add the menu page

			add_menu_page( $this->name, $this->name, 'coursepress_dashboard_cap', 'courses', array(
				&$this,
				'coursepress_courses_admin'
			), $this->plugin_url . 'images/coursepress-icon.png' );

			do_action( 'coursepress_add_menu_items_up' );

			// Add the sub menu items

			add_submenu_page( 'courses', __( 'Courses', 'cp' ), __( 'Courses', 'cp' ), 'coursepress_courses_cap', 'courses', array(
				&$this,
				'coursepress_courses_admin'
			) );

			do_action( 'coursepress_add_menu_items_after_courses' );

			if ( isset( $_GET['page'] ) && $_GET['page'] == 'course_details' && isset( $_GET['course_id'] ) ) {
				$new_or_current_course_menu_item_title = __( 'Course', 'cp' );
			} else {
				$new_or_current_course_menu_item_title = __( 'New Course', 'cp' );
			}

			add_submenu_page( 'courses', $new_or_current_course_menu_item_title, $new_or_current_course_menu_item_title, 'coursepress_courses_cap', 'course_details', array(
				&$this,
				'coursepress_course_details_admin'
			) );

			do_action( 'coursepress_add_menu_items_after_new_courses' );

			add_submenu_page( 'courses', __( 'Course Categories', 'cp' ), __( 'Course Categories', 'cp' ), 'coursepress_courses_cap', 'edit-tags.php?taxonomy=course_category&post_type=course' );
			do_action( 'coursepress_add_menu_items_after_course_categories' );

			add_submenu_page( 'courses', __( 'Instructors', 'cp' ), __( 'Instructors', 'cp' ), 'coursepress_instructors_cap', 'instructors', array(
				&$this,
				'coursepress_instructors_admin'
			) );
			do_action( 'coursepress_add_menu_items_after_instructors' );

			add_submenu_page( 'courses', __( 'Students', 'cp' ), __( 'Students', 'cp' ), 'coursepress_students_cap', 'students', array(
				&$this,
				'coursepress_students_admin'
			) );

			do_action( 'coursepress_add_menu_items_after_instructors' );

			$count = Unit_Module::get_ungraded_response_count();

			if ( $count == 0 ) {
				$count_output = '';
			} else {
				$count_output = '&nbsp;<span class ="update-plugins"><span class ="updates-count count-' . $count . '">' . $count . '</span></span>';
			}

			add_submenu_page( 'courses', __( 'Assessment', 'cp' ), __( 'Assessment', 'cp' ) . $count_output, 'coursepress_assessment_cap', 'assessment', array(
				&$this,
				'coursepress_assessment_admin'
			) );
			do_action( 'coursepress_add_menu_items_after_assessment' );


			add_submenu_page( 'courses', __( 'Reports', 'cp' ), __( 'Reports', 'cp' ), 'coursepress_reports_cap', 'reports', array(
				&$this,
				'coursepress_reports_admin'
			) );
			do_action( 'coursepress_add_menu_items_after_reports' );

			add_submenu_page( 'courses', __( 'Notifications', 'cp' ), __( 'Notifications', 'cp' ), 'coursepress_notifications_cap', 'notifications', array(
				&$this,
				'coursepress_notifications_admin'
			) );
			do_action( 'coursepress_add_menu_items_after_course_notifications' );

			add_submenu_page( 'courses', __( 'Discussions', 'cp' ), __( 'Discussions', 'cp' ), 'coursepress_discussions_cap', 'discussions', array(
				&$this,
				'coursepress_discussions_admin'
			) );
			do_action( 'coursepress_add_menu_items_after_course_discussions' );

			// Certificates
			if ( defined( 'CP_EA' ) && CP_EA == true ) {
				add_submenu_page( 'courses', __( 'Certificates', 'cp' ), __( 'Certificates', 'cp' ), 'coursepress_certificates_cap', 'certificates', array(
					&$this,
					'coursepress_certificates_admin'
				) );
				do_action( 'coursepress_add_menu_items_after_course_certificates' );
			}

			add_submenu_page( 'courses', __( 'Settings', 'cp' ), __( 'Settings', 'cp' ), 'coursepress_settings_cap', $this->screen_base . '_settings', array(
				&$this,
				'coursepress_settings_admin'
			) );
			do_action( 'coursepress_add_menu_items_after_settings' );

			do_action( 'coursepress_add_menu_items_down' );
		}

		function register_custom_posts() {

			//Register Courses post type
			$args = array(
				'labels'              => array(
					'name'               => __( 'Courses', 'cp' ),
					'singular_name'      => __( 'Course', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Course', 'cp' ),
					'edit_item'          => __( 'Edit Course', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Course', 'cp' ),
					'view_item'          => __( 'View Course', 'cp' ),
					'search_items'       => __( 'Search Courses', 'cp' ),
					'not_found'          => __( 'No Courses Found', 'cp' ),
					'not_found_in_trash' => __( 'No Courses found in Trash', 'cp' ),
					'view'               => __( 'View Course', 'cp' )
				),
				'public'              => false,
				'exclude_from_search' => false,
				'has_archive'         => true,
				'show_ui'             => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'course',
				'map_meta_cap'        => true,
				'query_var'           => true,
				'rewrite'             => array(
					'slug'       => $this->get_course_slug(),
					'with_front' => false
				),
				'supports'            => array( 'thumbnail' ),
				'taxonomies'          => array( 'course_category' ),
				//coursepress_course_categories_cap
			);

			register_post_type( 'course', $args );
			// Register custom taxonomy
			/* register_taxonomy( 'course_category', 'course', apply_filters( 'coursepress_register_course_category', array(
			  "hierarchical"	 => true,
			  'label'			 => __( 'Course Categories', 'cp' ),
			  'singular_label' => __( 'Course Category', 'cp' ),
			  'rewrite'		 => array(
			  'slug' => $this->get_course_category_slug()
			  ),
			  'capabilities'	 => array(
			  'manage_terms'	 => 'coursepress_course_categories_manage_terms_cap',
			  'edit_terms'	 => 'coursepress_course_categories_edit_terms_cap',
			  'delete_terms'	 => 'coursepress_course_categories_delete_terms_cap',
			  'assign_terms'	 => 'coursepress_courses_cap'
			  ),
			  ) )
			  ); */

			register_taxonomy( 'course_category', 'course', apply_filters( 'coursepress_register_course_category', array(
					'labels'            => array(
						'name'          => __( 'Course Categories', 'cp' ),
						'singular_name' => __( 'Course Category', 'cp' ),
						'search_items'  => __( 'Search Course Categories', 'cp' ),
						'all_items'     => __( 'All Course Categories', 'cp' ),
						'edit_item'     => __( 'Edit Course Categories', 'cp' ),
						'update_item'   => __( 'Update Course Category', 'cp' ),
						'add_new_item'  => __( 'Add New Course Category', 'cp' ),
						'new_item_name' => __( 'New Course Category Name', 'cp' ),
						'menu_name'     => __( 'Course Category', 'cp' ),
					),
					'hierarchical'      => true,
					'sort'              => true,
					'args'              => array( 'orderby' => 'term_order' ),
					'rewrite'           => array( 'slug' => $this->get_course_category_slug() ),
					'show_admin_column' => true,
					'capabilities'      => array(
						'manage_terms' => 'coursepress_course_categories_manage_terms_cap',
						'edit_terms'   => 'coursepress_course_categories_edit_terms_cap',
						'delete_terms' => 'coursepress_course_categories_delete_terms_cap',
						'assign_terms' => 'coursepress_courses_cap'
					),
				)
			) );
			//add_theme_support( 'post-thumbnails' );
			//Register Units post type
			$args = array(
				'labels'             => array(
					'name'               => __( 'Units', 'cp' ),
					'singular_name'      => __( 'Unit', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Unit', 'cp' ),
					'edit_item'          => __( 'Edit Unit', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Unit', 'cp' ),
					'view_item'          => __( 'View Unit', 'cp' ),
					'search_items'       => __( 'Search Units', 'cp' ),
					'not_found'          => __( 'No Units Found', 'cp' ),
					'not_found_in_trash' => __( 'No Units found in Trash', 'cp' ),
					'view'               => __( 'View Unit', 'cp' )
				),
				'public'             => false,
				'show_ui'            => false,
				'publicly_queryable' => false,
				'capability_type'    => 'unit',
				'map_meta_cap'       => true,
				'query_var'          => true
			);

			register_post_type( 'unit', $args );

			//Register Modules ( Unit Module ) post type
			$args = array(
				'labels'             => array(
					'name'               => __( 'Modules', 'cp' ),
					'singular_name'      => __( 'Module', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Module', 'cp' ),
					'edit_item'          => __( 'Edit Module', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Module', 'cp' ),
					'view_item'          => __( 'View Module', 'cp' ),
					'search_items'       => __( 'Search Modules', 'cp' ),
					'not_found'          => __( 'No Modules Found', 'cp' ),
					'not_found_in_trash' => __( 'No Modules found in Trash', 'cp' ),
					'view'               => __( 'View Module', 'cp' )
				),
				'public'             => false,
				'show_ui'            => false,
				'publicly_queryable' => false,
				'capability_type'    => 'module',
				'map_meta_cap'       => true,
				'query_var'          => true
			);

			register_post_type( 'module', $args );

			//Register Certificate Templates
			$args = array(
				'labels'             => array(
					'name'               => __( 'Certificate Templates', 'cp' ),
					'singular_name'      => __( 'Certificate Template', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Template', 'cp' ),
					'edit_item'          => __( 'Edit Template', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Template', 'cp' ),
					'view_item'          => __( 'View Template', 'cp' ),
					'search_items'       => __( 'Search Templates', 'cp' ),
					'not_found'          => __( 'No Templates Found', 'cp' ),
					'not_found_in_trash' => __( 'No Templates found in Trash', 'cp' ),
					'view'               => __( 'View Template', 'cp' )
				),
				'public'             => false,
				'show_ui'            => false,
				'publicly_queryable' => false,
				'capability_type'    => 'certificates',
				'map_meta_cap'       => true,
				'query_var'          => true
			);

			register_post_type( 'certificates', $args );

			//Register Modules Responses ( Unit Module Responses ) post type
			$args = array(
				'labels'             => array(
					'name'               => __( 'Module Responses', 'cp' ),
					'singular_name'      => __( 'Module Response', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Response', 'cp' ),
					'edit_item'          => __( 'Edit Response', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Response', 'cp' ),
					'view_item'          => __( 'View Response', 'cp' ),
					'search_items'       => __( 'Search Responses', 'cp' ),
					'not_found'          => __( 'No Module Responses Found', 'cp' ),
					'not_found_in_trash' => __( 'No Responses found in Trash', 'cp' ),
					'view'               => __( 'View Response', 'cp' )
				),
				'public'             => false,
				'show_ui'            => false,
				'publicly_queryable' => false,
				'capability_type'    => 'module_response',
				'map_meta_cap'       => true,
				'query_var'          => true
			);

			register_post_type( 'module_response', $args );

			//Register Notifications post type
			$args = array(
				'labels'             => array(
					'name'               => __( 'Notifications', 'cp' ),
					'singular_name'      => __( 'Notification', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Notification', 'cp' ),
					'edit_item'          => __( 'Edit Notification', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Notification', 'cp' ),
					'view_item'          => __( 'View Notification', 'cp' ),
					'search_items'       => __( 'Search Notifications', 'cp' ),
					'not_found'          => __( 'No Notifications Found', 'cp' ),
					'not_found_in_trash' => __( 'No Notifications found in Trash', 'cp' ),
					'view'               => __( 'View Notification', 'cp' )
				),
				'public'             => false,
				'show_ui'            => false,
				'publicly_queryable' => false,
				'capability_type'    => 'notification',
				'map_meta_cap'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => trailingslashit( $this->get_course_slug() ) . '%course%/' . $this->get_notifications_slug() )
			);

			register_post_type( 'notifications', $args );

			//Register Discussion post type
			$args = array(
				'labels'             => array(
					'name'               => __( 'Discussions', 'cp' ),
					'singular_name'      => __( 'Discussions', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Discussion', 'cp' ),
					'edit_item'          => __( 'Edit Discussion', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Discussion', 'cp' ),
					'view_item'          => __( 'View Discussion', 'cp' ),
					'search_items'       => __( 'Search Discussions', 'cp' ),
					'not_found'          => __( 'No Discussions Found', 'cp' ),
					'not_found_in_trash' => __( 'No Discussions found in Trash', 'cp' ),
					'view'               => __( 'View Discussion', 'cp' )
				),
				'public'             => false,
				//'has_archive' => true,
				'show_ui'            => false,
				'publicly_queryable' => true,
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
				'query_var'          => true,
				//'rewrite' => array( 'slug' => trailingslashit( $this->get_course_slug() ) . '%course%/' . $this->get_discussion_slug() )
			);

			register_post_type( 'discussions', $args );

			do_action( 'coursepress_after_custom_post_types' );
		}

		function course_has_gateway() {

			$gateways = get_option( 'mp_settings', false );
			if ( ! empty( $gateways ) ) {
				$gateways = ! empty( $gateways['gateways']['allowed'] ) ? true : false;
			}

			$ajax_response = array( 'has_gateway' => $gateways );
			$ajax_status   = 1;

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => $ajax_status,
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		/**
		 * Handles AJAX call for Course Settings auto-update.
		 */
		function autoupdate_course_settings() {

			$user_id       = (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$nonce_check   = wp_verify_nonce( $_POST['course_nonce'], 'auto-update-' . $course_id );
			$cap           = 0 == $course_id ? CoursePress_Capabilities::can_create_course( $user_id ) : CoursePress_Capabilities::can_update_course( $course_id, $user_id );
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();

			if ( $nonce_check && $cap && $doing_ajax ) {

				/**
				 * Course auto-update about to start.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id Course ID about to be updated.
				 * @param int user_id User initiating the update.
				 */
				do_action( 'coursepress_course_autoupdate_started', $course_id, $user_id );

				$course = new Course( $course_id );

				if ( $course->details ) {
					$course->data['status'] = $course->details->post_status;
				} else {
					$course->data['status'] = 'draft';
				}

				if ( ! empty( $_POST['meta_course_setup_marker'] ) && 'step-2' == $_POST['meta_course_setup_marker'] ) {
					$course_categories = $_POST['course_category'];

					wp_delete_object_term_relationships( $course_id, 'course_category' );

					if ( ! empty( $course_categories ) && is_array( $course_categories ) ) {
						foreach ( $course_categories as $course_category ) {
							wp_set_post_terms( $course_id, $course_category, 'course_category', true );
						}
					}
				}

				if ( ! empty( $user_id ) && 0 == $course_id ) {
					$course->data['uid']         = $user_id;
					$ajax_response['instructor'] = $user_id;
				}

				$course_id     = $course->update_course();
				$mp_product_id = $course->mp_product_id();

				$ajax_response['success']       = true;
				$ajax_response['course_id']     = $course_id;
				$ajax_response['mp_product_id'] = $mp_product_id;
				$ajax_response['nonce']         = wp_create_nonce( 'auto-update-' . $course_id );

				if ( ! empty( $_POST['meta_course_setup_marker'] ) && 'step-6' == $_POST['meta_course_setup_marker'] ) {
					update_post_meta( $course_id, 'course_setup_complete', 'yes' );
				}

				/**
				 * Course auto-update completed.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id Course ID about to be updated.
				 * @param int user_id User initiating the update.
				 */
				do_action( 'coursepress_course_autoupdate_complete', $course_id, $user_id );
			} else {
				$ajax_response['success'] = false;
				$ajax_response['reason']  = __( 'Invalid request. Security check failed.', 'cp' );
			}

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => 1, // success status
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function change_course_state() {

			$user_id       = empty( $_POST['user_id'] ) ? get_current_user_id() : (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$nonce_check   = wp_verify_nonce( $_POST['course_nonce'], 'toggle-' . $course_id );
			$cap           = CoursePress_Capabilities::can_change_course_status( $course_id, $user_id );
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();

			if ( $nonce_check && $cap && $doing_ajax ) {
				$course = new Course( $course_id );
				$course->change_status( $_POST['course_state'] );
				$ajax_response['toggle'] = true;
				$ajax_response['nonce']  = wp_create_nonce( 'toggle-' . $course_id );

				/**
				 * Course status toggled.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id Course ID about to be updated.
				 * @param int user_id User initiating the update.
				 */
				do_action( 'coursepress_course_status_changed', $course_id, $user_id );
			} else {
				$ajax_response['toggle'] = false;
				$ajax_response['reason'] = __( 'Invalid request. Security check failed.', 'cp' );

				/**
				 * Course status not changed.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id Course ID about to be updated.
				 * @param int user_id User initiating the update.
				 */
				do_action( 'coursepress_course_status_change_fail', $course_id, $user_id );
			}

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => 1, // success status
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function change_unit_state() {

			$user_id       = empty( $_POST['user_id'] ) ? get_current_user_id() : (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$unit_id       = (int) $_POST['unit_id'];
			$nonce_check   = wp_verify_nonce( $_POST['unit_nonce'], 'toggle-' . $unit_id );
			$cap           = CoursePress_Capabilities::can_change_course_unit_status( $course_id, $unit_id, $user_id );
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();

			if ( $nonce_check && $cap && $doing_ajax ) {
				$unit = new Unit( $unit_id );
				$unit->change_status( $_POST['unit_state'] );

				$ajax_response['toggle'] = true;
				$ajax_response['nonce']  = wp_create_nonce( 'toggle-' . $unit_id );

				/**
				 * Unit status toggled.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id Parent course ID.
				 * @param int unit_id Unit ID about to be updated.
				 * @param int user_id User initiating the update.
				 */
				do_action( 'coursepress_course_status_changed', $course_id, $unit_id, $user_id );
			} else {
				$ajax_response['toggle'] = false;
				$ajax_response['reason'] = __( 'Invalid request. Security check failed.', 'cp' );

				/**
				 * Unit status toggled.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id Parent course ID.
				 * @param int unit_id Unit ID about to be updated.
				 * @param int user_id User initiating the update.
				 */
				do_action( 'coursepress_course_status_change_fail', $course_id, $unit_id, $user_id );
			}

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => 1, // success status
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function add_course_instructor() {

			$instructor_id = (int) $_POST['instructor_id'];
			$user_id       = (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$nonce_check   = wp_verify_nonce( $_POST['instructor_nonce'], 'manage-instructors-' . $user_id );
			// Course creator should be able to assign self as instructor (or many other things will break)
			$cap           = CoursePress_Capabilities::can_assign_course_instructor( $course_id, $user_id ) ? true : $instructor_id == $user_id ? true : false;
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();

			if ( $nonce_check && $cap && $doing_ajax ) {

				$instructors = get_post_meta( $_POST['course_id'], 'instructors', true );

				$exists = false;
				if ( is_array( $instructors ) ) {
					foreach ( $instructors as $instructor ) {
						if ( $instructor == $instructor_id ) {
							$instructor_course_id = get_user_option( 'course_' . $course_id, $instructor_id );
							if ( ! empty( $instructor_course_id ) ) {
								$exists = true;
							};
						}
					}
				}

				// User is not yet an instructor
				if ( ! $exists ) {
					// Assign Instructor capabilities

					$this->assign_instructor_capabilities( $instructor_id );

					$global_option = ! is_multisite();

					$instructors[] = $instructor_id;
					update_post_meta( $course_id, 'instructors', $instructors );
					update_user_option( $instructor_id, 'course_' . $course_id, $course_id, $global_option );

					$ajax_response['instructors']      = json_encode( $instructors );
					$ajax_response['instructor_added'] = true;

					$user_info = get_userdata( $instructor_id );

					$ajax_response['instructor_gravatar'] = get_avatar( $instructor_id, 80, "", $user_info->display_name );
					$ajax_response['instructor_name']     = $user_info->display_name;

					/**
					 * Instructor added successfully to course.
					 *
					 * @since 1.2.1
					 *
					 * @param int course_id The course instructor was added to.
					 * @param int instructor_id The user ID of the new instructor.
					 *
					 */
					do_action( 'coursepress_course_instructor_added', $course_id, $instructor_id );
				} else {
					$ajax_response['instructor_added'] = false;
					$ajax_response['reason']           = __( 'Instructor already added.', 'cp' );

					/**
					 * Instructor already exists in the course.
					 *
					 * @since 1.2.1
					 *
					 * @param int course_id The course instructor was added to.
					 * @param int instructor_id The user ID of the new instructor.
					 *
					 */
					do_action( 'coursepress_course_instructor_exists', $course_id, $instructor_id );
				}

				// Nonce failed, User doesn't have the capability
			} else {
				$ajax_response['instructor_added'] = false;
				$ajax_response['reason']           = __( 'Invalid request. Security check failed.', 'cp' );

				/**
				 * Failed to add an instructor to the course.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id The course instructor was added to.
				 * @param int instructor_id The user ID of the new instructor.
				 *
				 */
				do_action( 'coursepress_course_instructor_not_added', $course_id, $instructor_id );
			}

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => 1, // success status
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function update_unit() {
			global $user_id;

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'update_unit' ) {

				if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'unit_details_overview_' . $user_id ) ) {

					$unit = new Unit( $_POST['unit_id'] );

					if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_create_course_unit_cap' ) || current_user_can( 'coursepress_update_course_unit_cap' ) || current_user_can( 'coursepress_update_my_course_unit_cap' ) || current_user_can( 'coursepress_update_all_courses_unit_cap' ) ) {
						$new_post_id = $unit->update_unit( isset( $_POST['unit_id'] ) ? $_POST['unit_id'] : 0 );
					}

					if ( isset( $_POST['unit_state'] ) ) {
						if ( current_user_can( 'manage_options' ) || current_user_can( 'coursepress_change_course_unit_status_cap' ) || current_user_can( 'coursepress_change_my_course_unit_status_cap' ) || current_user_can( 'coursepress_change_all_courses_unit_status_cap' ) ) {
							$unit = new Unit( $new_post_id );
							$unit->change_status( $_POST['unit_state'] );
						}
					}
				}

				echo 'RESPONSE!';
			}
		}

		function remove_course_instructor() {

			$instructor_id = (int) $_POST['instructor_id'];
			$user_id       = (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$nonce_check   = wp_verify_nonce( $_POST['instructor_nonce'], 'manage-instructors-' . $user_id );
			$cap           = CoursePress_Capabilities::can_assign_course_instructor( $course_id, $user_id );  // same capability as adding
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();

			if ( $nonce_check && $cap && $doing_ajax ) {

				$instructors = get_post_meta( $course_id, 'instructors', true );

				$updated_instructors = array();
				foreach ( $instructors as $instructor ) {
					if ( $instructor != $instructor_id ) {
						$updated_instructors[] = $instructor;
					}
				}

				$global_option = ! is_multisite();

				update_post_meta( $course_id, 'instructors', $updated_instructors );
				delete_user_option( $instructor_id, 'course_' . $course_id, $global_option );

				// Legacy
				delete_user_meta( $instructor_id, 'course_' . $course_id, $course_id );

				$instructor = new Instructor( $instructor_id );

				// If user is no longer an instructor of any courses, remove his capabilities.
				$assigned_courses_ids = $instructor->get_assigned_courses_ids();
				if ( empty( $assigned_courses_ids ) ) {
					$this->drop_instructor_capabilities( $instructor_id );

					delete_user_option( $instructor_id, 'role_ins', $global_option );

					// Legacy
					delete_user_meta( $instructor_id, 'role_ins' );
				}

				$ajax_response['instructor_removed'] = true;

				/**
				 * Instructor has been removed from course.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id The course instructor was added to.
				 * @param int instructor_id The user ID of the new instructor.
				 *
				 */
				do_action( 'coursepress_course_instructor_removed', $course_id, $instructor_id );

				// Nonce failed, User doesn't have the capability
			} else {
				$ajax_response['instructor_removed'] = false;
				$ajax_response['reason']             = __( 'Invalid request. Security check failed.', 'cp' );

				/**
				 * Instructor has NOT been removed from course.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id The course instructor was added to.
				 * @param int instructor_id The user ID of the new instructor.
				 *
				 */
				do_action( 'coursepress_course_instructor_not_removed', $course_id, $instructor_id );
			}

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => 1, // success status
				'data'   => json_encode( $ajax_response ),
			);

			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		// Create instructor MD5 user meta key if it doesn't exist
		function create_instructor_hash( $course_id, $instructor_id ) {
			Instructor::create_hash( $instructor_id );
		}

		function send_instructor_invite() {

			$user_id       = (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$email         = sanitize_email( $_POST['email'] );
			$nonce_check   = wp_verify_nonce( $_POST['instructor_nonce'], 'manage-instructors-' . $user_id );
			$cap           = CoursePress_Capabilities::can_assign_course_instructor( $course_id, $user_id );  // same capability as adding
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();

			if ( $nonce_check && $cap && $doing_ajax ) {

				$email_args['email_type']       = 'instructor_invitation';
				$email_args['first_name']       = sanitize_text_field( $_POST['first_name'] );
				$email_args['last_name']        = sanitize_text_field( $_POST['last_name'] );
				$email_args['instructor_email'] = $email;

				$user = get_user_by( 'email', $email_args['instructor_email'] );
				if ( $user ) {
					$email_args['user'] = $user;
				}

				$email_args['course_id'] = $course_id;

				$ajax_status = 1; //success
				// Get the invite meta for this course and add the new invite
				$invite_exists = false;
				if ( $instructor_invites = get_post_meta( $email_args['course_id'], 'instructor_invites', true ) ) {
					foreach ( $instructor_invites as $i ) {
						$invite_exists = array_search( $email_args['instructor_email'], $i );
					}
				} else {
					$instructor_invites = array();
				}

				if ( ! $invite_exists ) {

					// Generate invite code.
					$characters  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$invite_code = '';
					for ( $i = 0; $i < 20; $i ++ ) {
						$invite_code .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
					}

					// Save the invite in the course meta. Hash will be used for user authentication.
					$email_args['invite_code'] = $invite_code;
					$invite_hash               = sha1( $email_args['instructor_email'] . $email_args['invite_code'] );

					$email_args['invite_hash'] = $invite_hash;

					if ( coursepress_send_email( $email_args ) ) {

						$invite = array(
							'first_name' => $email_args['first_name'],
							'last_name'  => $email_args['last_name'],
							'email'      => $email_args['instructor_email'],
							'code'       => $email_args['invite_code'],
							'hash'       => $email_args['invite_hash'],
						);

						$instructor_invites[ $email_args['invite_code'] ] = $invite;

						update_post_meta( $email_args['course_id'], 'instructor_invites', $instructor_invites );

						$course = new Course( $course_id );

						if ( ( current_user_can( 'coursepress_assign_and_assign_instructor_course_cap' ) ) || ( current_user_can( 'coursepress_assign_and_assign_instructor_my_course_cap' ) && $course->details->post_author == get_current_user_id() ) ) {
							$ajax_response['capability'] = true;
						} else {
							$ajax_response['capability'] = false;
						}

						$ajax_response['data']    = $invite;
						$ajax_response['content'] = '<i class ="fa fa-check status status-success"></i> ' . __( 'Invitation successfully sent.', 'cp' );

						/**
						 * Instructor has been invited.
						 *
						 * @since 1.2.1
						 *
						 * @param int course_id The course instructor was added to.
						 * @param string email The email invite was sent to.
						 *
						 */
						do_action( 'coursepress_instructor_invite_sent', $course_id, $email );
					} else {
						$ajax_status              = new WP_Error( 'mail_fail', __( 'Email failed to send.', 'cp' ) );
						$ajax_response['content'] = '<i class ="fa fa-exclamation status status-fail"></i> ' . __( 'Email failed to send.', 'cp' );

						/**
						 * Instructor invite not sent.
						 *
						 * @since 1.2.1
						 *
						 * @param int course_id The course instructor was added to.
						 * @param int instructor_id The user ID of the new instructor.
						 *
						 */
						do_action( 'coursepress_instructor_invite_mail_fail', $course_id, $email );
					}
				} else {
					$ajax_response['content'] = '<i class ="fa fa-info-circle status status-exist"></i> ' . __( 'Invitation already exists.', 'cp' );
					/**
					 * Instructor already invited.
					 *
					 * @since 1.2.1
					 *
					 * @param int course_id The course instructor was added to.
					 * @param int instructor_id The user ID of the new instructor.
					 *
					 */
					do_action( 'coursepress_instructor_invite_exists', $course_id, $email );
				}
			} else {
				$ajax_status              = new WP_Error( 'nonce_fail', __( 'Invalid request. Security check failed.', 'cp' ) );
				$ajax_response['content'] = '<i class ="fa fa-exclamation status status-fail"></i> ' . __( 'Invalid request. Security check failed.', 'cp' );
			}

			$response = array(
				'what'   => 'instructor_invite',
				'action' => 'instructor_invite',
				'id'     => $ajax_status,
				'data'   => json_encode( $ajax_response ),
			);

			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function remove_instructor_invite() {
			$user_id       = (int) $_POST['user_id'];
			$course_id     = (int) $_POST['course_id'];
			$invite_code   = sanitize_text_field( $_POST['invite_code'] );
			$nonce_check   = wp_verify_nonce( $_POST['instructor_nonce'], 'manage-instructors-' . $user_id );
			$cap           = CoursePress_Capabilities::can_assign_course_instructor( $course_id, $user_id );  // same capability as adding
			$doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false;
			$ajax_response = array();
			$ajax_status   = 1; //success

			if ( $nonce_check && $cap && $doing_ajax ) {

				$instructor_invites = get_post_meta( $course_id, 'instructor_invites', true );

				unset( $instructor_invites[ $invite_code ] );

				update_post_meta( $course_id, 'instructor_invites', $instructor_invites );

				$ajax_response['invite_removed'] = true;
				$ajax_response['content']        = __( 'Instructor invitation cancelled.', 'cp' );

				/**
				 * Instructor invite has been cancelled.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id The course instructor was added to.
				 * @param int invite_code The code of the invite that was cancelled.
				 *
				 */
				do_action( 'coursepress_instructor_invite_cancelled', $course_id, $invite_code );
			} else {
				$ajax_response['invite_removed'] = false;
				$ajax_response['reason']         = __( 'Invalid request. Security check failed.', 'cp' );
				/**
				 * Instructor invite has NOT been cancelled.
				 *
				 * @since 1.2.1
				 *
				 * @param int course_id The course instructor was added to.
				 * @param int invite_code The code of the invite that was cancelled.
				 *
				 */
				do_action( 'coursepress_instructor_invite_not_cancelled', $course_id, $invite_code );
			}

			$response = array(
				'what'   => 'remove_instructor_invite',
				'action' => 'remove_instructor_invite',
				'id'     => $ajax_status,
				'data'   => json_encode( $ajax_response ),
			);

			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function instructor_invite_confirmation() {
			$pg = false;

			if ( ( isset( $_GET['action'] ) && 'course_invite' == $_GET['action'] ) ) {
				$this->remove_pre_next_post();
				// get_header();
				$content     = '';
				$title       = '';
				$course_id   = (int) $_GET['course_id'];
				$user_id     = get_current_user_id();
				$invites     = get_post_meta( $_GET['course_id'], 'instructor_invites', true );
				$invite_keys = array_keys( $invites );
				$valid_code  = in_array( $_GET['c'], $invite_keys ) ? true : false;

				if ( is_user_logged_in() && $valid_code ) {

					$current_user = wp_get_current_user();
					$hash         = sha1( $current_user->user_email . $_GET['c'] );

					if ( $hash == $_GET['h'] ) {


						$instructors = get_post_meta( $_GET['course_id'], 'instructors', true );


						foreach ( $invites as $key => $invite ) {
							if ( $_GET['c'] == $invite['code'] ) {

								$exists = false;
								foreach ( $instructors as $instructor ) {
									if ( $instructor == $user_id ) {
										$exists = true;
										//exit;
									}
								}

								if ( ! $exists ) {
									// Assign Instructor capabilities
									$this->assign_instructor_capabilities( $user_id );

									$instructors[] = $user_id;

									$global_option = ! is_multisite();

									update_post_meta( $course_id, 'instructors', $instructors );
									update_user_option( $user_id, 'course_' . $course_id, $course_id, $global_option );
									unset( $invites[ $key ] );
									update_post_meta( $course_id, 'instructor_invites', $invites );

									// Don't add space between =
									$course_link = '<a href ="' . admin_url( 'admin.php?page=course_details&course_id=' . $course_id ) . '">' . get_the_title( $course_id ) . '</a>';

									$title   = __( '<h3>Invitation activated.</h3>', 'cp' );
									$content = do_shortcode( sprintf( __( '<p>Congratulations. You are now an instructor in the following course:</p>
										<p>%s</p>
									', 'cp' ), $course_link ) );

									/**
									 * Instructor invite confirmed.
									 *
									 * @since 1.2.1
									 *
									 * @param int course_id The course instructor was added to.
									 * @param int user_id The user ID of instructor assigned.
									 *
									 */
									do_action( 'coursepress_instructor_invite_confirmed', $course_id, $user_id );
								}
								break;
							}
						}
					} else {
						$title   = __( '<h3>Invalid Invitation</h3>', 'cp' );
						$content = do_shortcode( __( '
							<p>This invitation link is not associated with your email address.</p>
							<p>Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.</p>
						', 'cp' ) );

						/**
						 * Instructor confirmation failed.
						 *
						 * Usually when the email sent to and the one trying to register don't match.
						 *
						 * @since 1.2.1
						 *
						 * @param int course_id The course instructor was added to.
						 * @param int user_id The user ID of instructor assigned.
						 *
						 */
						do_action( 'coursepress_instructor_invite_confirm_fail', $course_id, $user_id );
					}
				} else {
					if ( ! $valid_code ) {
						$title   = __( '<h3>Invitation not found.</h3>', 'cp' );
						$content = do_shortcode( __( '
							<p>This invitation could not be found or is no longer available.</p>
							<p>Please contact us if you believe this to be an error.</p>
						', 'cp' ) );

						/**
						 * Instructor confirmation failed.
						 *
						 * Usually when the email sent to and the one trying to register don't match.
						 *
						 * @since 1.2.1
						 *
						 * @param int course_id The course instructor was added to.
						 * @param int user_id The user ID of instructor assigned.
						 *
						 */
						do_action( 'coursepress_instructor_invite_not_found', $course_id, $user_id );
					} else {
						$title   = __( '<h3>Login Required</h3>', 'cp' );
						$content = do_shortcode( __( '
							<p>To accept your invitation request you will need to be logged in.</p>
							<p>Please login with the account associated with this email.</p>
						', 'cp' ) );

						ob_start();
						echo do_shortcode( '[course_signup page ="login" login_title ="" redirect_url ="' . urlencode( home_url( $_SERVER['REQUEST_URI'] ) ) . '" signup_url ="' . CoursePress::instance()->get_signup_slug( true ) . '" logout_url ="' . CoursePress::instance()->get_signup_slug( true ) . '"]' );
						$content .= ob_get_clean();
					}
				}
				// get_sidebar();
				//                 get_footer();
				$args = array(
					'slug'        => 'instructor_invite',
					'title'       => $title,
					'content'     => $content,
					'type'        => 'virtual_page',
					'is_page'     => true,
					'is_singular' => true,
					'is_archive'  => false
				);
				$pg   = new CoursePress_Virtual_Page( $args );
			}

			return $pg;
		}

		function refresh_course_calendar() {
			$ajax_response = array();
			$ajax_status   = 1; //success

			if ( ! empty( $_POST['date'] ) && ! empty( $_POST['course_id'] ) ) {

				$date = getdate( strtotime( str_replace( '-', '/', $_POST['date'] ) ) );
				$pre  = ! empty( $_POST['pre_text'] ) ? $_POST['pre_text'] : false;
				$next = ! empty( $_POST['next_text'] ) ? $_POST['next_text'] : false;

				$calendar = new Course_Calendar( array(
					'course_id' => $_POST['course_id'],
					'month'     => $date['mon'],
					'year'      => $date['year']
				) );

				$html = '';
				if ( $pre && $next ) {
					$html = $calendar->create_calendar( $pre, $next );
				} else {
					$html = $calendar->create_calendar();
				}

				$ajax_response['calendar'] = $html;
			}

			$response = array(
				'what'   => 'refresh_course_calendar',
				'action' => 'refresh_course_calendar',
				'id'     => $ajax_status,
				'data'   => json_encode( $ajax_response ),
			);
			ob_end_clean();
			ob_start();
			$xmlResponse = new WP_Ajax_Response( $response );
			$xmlResponse->send();
			ob_end_flush();
		}

		function assign_instructor_capabilities( $user_id ) {

			//updated to using CoursePress settings
			// The default capabilities for an instructor
			$default = array_keys( CoursePress_Capabilities::$capabilities['instructor'], 1 );

			$instructor_capabilities = get_option( 'coursepress_instructor_capabilities', $default );

			$role = new WP_User( $user_id );

			$global_option = ! is_multisite();
			update_user_option( $user_id, 'role_ins', 'instructor', $global_option );

			$role->add_cap( 'can_edit_posts' );
			$role->add_cap( 'read' );
			$role->add_cap( 'upload_files' );

			foreach ( $instructor_capabilities as $cap ) {
				$role->add_cap( $cap );
			}
		}

		function drop_instructor_capabilities( $user_id ) {

			if ( user_can( $user_id, 'manage_options' ) ) {
				return;
			}

			$role = new Instructor( $user_id );

			$global_option = ! is_multisite();
			delete_user_option( $user_id, 'role_ins', $global_option );
			// Legacy
			delete_user_meta( $user_id, 'role_ins', 'instructor' );

			$role->remove_cap( 'can_edit_posts' );
			$role->remove_cap( 'read' );
			$role->remove_cap( 'upload_files' );

			$capabilities = array_keys( CoursePress_Capabilities::$capabilities['instructor'] );
			foreach ( $capabilities as $cap ) {
				$role->remove_cap( $cap );
			}

			CoursePress_Capabilities::grant_private_caps( $user_id );
		}

		//Add new roles and user capabilities
		function add_user_roles_and_caps() {
			global $user, $wp_roles;

			/* ---------------------- Add initial capabilities for the admins */
			$role = get_role( 'administrator' );
			$role->add_cap( 'read' );

			// Add ALL instructor capabilities
			$admin_capabilities = array_keys( CoursePress_Capabilities::$capabilities['instructor'] );
			foreach ( $admin_capabilities as $cap ) {
				$role->add_cap( $cap );
			}

			CoursePress_Capabilities::drop_private_caps( '', $role );
		}

		//Functions for handling admin menu pages

		function coursepress_courses_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/courses.php' );
		}

		function coursepress_course_details_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/courses-details.php' );
		}

		function coursepress_instructors_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/instructors.php' );
		}

		function coursepress_students_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/students.php' );
		}

		function coursepress_assessment_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/assessment.php' );
		}

		function coursepress_notifications_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/notifications.php' );
		}

		function coursepress_discussions_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/discussions.php' );
		}

		function coursepress_reports_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/reports.php' );
		}

		function coursepress_settings_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings.php' );
		}

		function coursepress_certificates_admin() {
			include_once( $this->plugin_dir . 'includes/admin-pages/certificates.php' );
		}

		/* Functions for handling tab pages */

		function show_courses_details_overview() {
			include_once( $this->plugin_dir . 'includes/admin-pages/courses-details-overview.php' );
		}

		function show_courses_details_units() {
			include_once( $this->plugin_dir . 'includes/admin-pages/courses-details-units.php' );
		}

		function show_courses_details_students() {
			include_once( $this->plugin_dir . 'includes/admin-pages/courses-details-students.php' );
		}

		function show_settings_general() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-general.php' );
		}

		function show_settings_groups() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-groups.php' );
		}

		function show_settings_payment() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-payment.php' );
		}

		function show_settings_shortcodes() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-shortcodes.php' );
		}

		function show_settings_instructor_capabilities() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-instructor-capabilities.php' );
		}

		function show_settings_email() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-email.php' );
		}

		function show_settings_marketpress() {
			include_once( $this->plugin_dir . 'includes/admin-pages/settings-marketpress.php' );
		}

		function show_unit_details( $unit_page_num = 1, $active_element = 1, $preview_redirect_url ) {
			require_once( $this->plugin_dir . 'includes/admin-pages/unit-details.php' );
		}

		/* Custom header actions */

		function header_actions() {//front
			global $post, $wp_query, $mp;
			wp_enqueue_style( 'font_awesome', $this->plugin_url . 'css/font-awesome.css' );
			wp_enqueue_script( 'enrollment_process', $this->plugin_url . 'js/front-enrollment-process.js', array( 'jquery' ), $this->version );
			wp_localize_script( 'enrollment_process', 'cp_vars', array(
				'admin_ajax_url'                  => cp_admin_ajax_url(),
				'message_all_fields_are_required' => __( 'All fields are required.', 'cp' ),
				'message_username_minimum_length' => __( 'Username must be at least 4 characters in length', 'cp' ),
				'message_username_exists'         => __( 'Username already exists or invalid. Please choose another one.', 'cp' ),
				'message_email_exists'            => __( 'E-mail already exists or invalid. Please choose another one.', 'cp' ),
				'message_emails_dont_match'       => __( "E-mails mismatch.", 'cp' ),
				'message_passwords_dont_match'    => __( "Passwords mismatch.", 'cp' ),
				'message_password_minimum_length' => sprintf( __( 'Password must be at least %d characters in length.', 'cp' ), apply_filters( 'coursepress_min_password_length', 6 ) ),
				'minimum_password_lenght'         => apply_filters( 'coursepress_min_password_length', 6 ),
				'message_login_error'             => __( 'Username and/or password is not valid.', 'cp' ),
				'message_passcode_invalid'        => __( 'Passcode is not valid.', 'cp' ),
				'message_tos_invalid'             => __( 'You must agree to the Terms of Service in order to signup.', 'cp' ),
				'debug'                           => 0, // Set to 1 for debugging enrollment scripts
			) );

			wp_enqueue_script( 'coursepress_front', $this->plugin_url . 'js/coursepress-front.js', array( 'jquery' ), $this->version );

			wp_enqueue_script( 'coursepress_calendar', $this->plugin_url . 'js/coursepress-calendar.js', array( 'jquery' ), $this->version );
			if ( $post && ! $this->is_preview( $post->ID ) && ! isset( $_GET['try'] ) ) {
				wp_enqueue_script( 'coursepress_front_elements', $this->plugin_url . 'js/coursepress-front-elements.js', array( 'jquery' ), $this->version );
			}
			$course_id         = do_shortcode( '[get_parent_course_id]' );
			$units_archive_url = is_numeric( $course_id ) ? get_permalink( $course_id ) . trailingslashit( $this->get_units_slug() ) : '';

			wp_localize_script( 'coursepress_front', 'front_vars', array(
				'withdraw_alert'    => __( 'Please confirm that you want to withdraw from the course. If you withdraw, you will no longer be able to see your records for this course.', 'cp' ),
				'units_archive_url' => $units_archive_url
			) );

			if ( ! is_admin() ) {
				wp_enqueue_style( 'front_general', $this->plugin_url . 'css/front_general.css', array(), $this->version );
				wp_enqueue_style( 'front_enrollment_process', $this->plugin_url . 'css/front-enrollment-process.css', array(), $this->version );
			}

			wp_enqueue_script( 'coursepress-knob', $this->plugin_url . 'js/jquery.knob.js', array(), $this->version, true );

			if ( isset( $wp_query->query_vars['order_id'] ) || isset( $_GET['order_id'] ) ) {
				$order_id = isset( $wp_query->query_vars['order_id'] ) ? $wp_query->query_vars['order_id'] : ( isset( $_GET['order_id'] ) ? $_GET['order_id'] : '' );
				if ( ! empty( $order_id ) && isset( $mp ) ) {
					$order = $mp->get_order( $order_id );
					if ( count( $order ) == 1 ) {//CP supports only one item in the cart per order so there is no reason to do the check otherwise
						if ( cp_get_order_course_id( $order_id ) ) {
							wp_enqueue_style( 'front_mp_fix', $this->plugin_url . 'css/front_mp_fix.css', array(), $this->version );
							add_filter( 'mp_order_status_section_title_shipping_info', array(
								&$this,
								'return_empty'
							) );
						}
					}
				}
			}

			// Responsive Video
			//wp_enqueue_script( 'responsive-video', $this->plugin_url . 'js/responsive-video.js' );
		}

		function return_empty() {
			return;
		}

		/* Custom footer actions */

		function footer_actions() {
			if ( ( isset( $_GET['saved'] ) && $_GET['saved'] == 'ok' ) ) {
				?>
				<div class="save_elements_message_ok">
					<?php _e( 'The data has been saved successfully.', 'cp' ); ?>
				</div>
			<?php
			}
			if ( ( isset( $_GET['saved'] ) && $_GET['saved'] == 'progress_ok' ) ) {
				?>
				<div class="save_elements_message_ok">
					<?php _e( 'Your progress has been saved successfully.', 'cp' ); ?>
				</div>
			<?php
			}
			$this->load_popup_window();
		}

		/* custom header actions */

		function head_actions() {
			$generate_cp_generator_meta = apply_filters( 'coursepress_generator_meta', true );
			if ( $generate_cp_generator_meta ) {
				?>
				<meta name="generator" content="<?php echo $this->name . ' ' . $this->version; ?>"/>
			<?php
			}
		}

		function load_popup_window() {
			include_once( $this->plugin_dir . 'includes/templates/popup-window.php' );
		}

		/* Add required jQuery scripts */

		function add_jquery_ui() {
			/* wp_enqueue_script( 'jquery' );
			  wp_enqueue_script( 'jquery-ui-core' );
			  wp_enqueue_script( 'jquery-ui-widget' );
			  wp_enqueue_script( 'jquery-ui-mouse' );
			  wp_enqueue_script( 'jquery-ui-accordion' );
			  wp_enqueue_script( 'jquery-ui-autocomplete' );
			  wp_enqueue_script( 'jquery-ui-slider' );
			  wp_enqueue_script( 'jquery-ui-tabs');
			  wp_enqueue_script( 'jquery-ui-sortable' );
			  wp_enqueue_script( 'jquery-ui-draggable' );
			  wp_enqueue_script( 'jquery-ui-droppable' );
			  wp_enqueue_script( 'jquery-ui-datepicker' );
			  wp_enqueue_script( 'jquery-ui-resize' );
			  wp_enqueue_script( 'jquery-ui-dialog' );
			  wp_enqueue_script( 'jquery-ui-button' ); */
		}

		function cp_jquery_admin( $hook_sufix ) {
			if ( strpos( $hook_sufix, 'course' ) !== false ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui', '//code.jquery.com/ui/1.11.4/jquery-ui.js', array( 'jquery' ), '1.11.4' ); //need to change this to built-in
			}
		}

		function admin_header_actions() {
			global $pagenow;
			
			/** Bail when in category page. **/
			if( $pagenow == 'edit-tags.php' ) {
				return;
			}

			if ( is_admin() && ! CoursePress_Capabilities::is_campus() ) {
				if ( ( isset( $_GET['cp_admin_ref'] ) && $_GET['cp_admin_ref'] == 'cp_course_creation_page' ) || ( isset( $_POST['cp_admin_ref'] ) && $_POST['cp_admin_ref'] == 'cp_course_creation_page' ) ) {
					wp_enqueue_style( 'admin_coursepress_marketpress_popup', $this->plugin_url . 'css/admin_marketpress_popup.css', array(), $this->version );
				}
			}

			wp_enqueue_style( 'font_awesome', $this->plugin_url . 'css/font-awesome.css' );
			wp_enqueue_style( 'admin_general', $this->plugin_url . 'css/admin_general.css', array(), $this->version );
			wp_enqueue_style( 'admin_general_responsive', $this->plugin_url . 'css/admin_general_responsive.css', array(), $this->version );
			/* wp_enqueue_script( 'jquery-ui-datepicker' );
			  wp_enqueue_script( 'jquery-ui-accordion' );
			  wp_enqueue_script( 'jquery-ui-sortable' );
			  wp_enqueue_script( 'jquery-ui-resizable' );
			  wp_enqueue_script( 'jquery-ui-draggable' );
			  wp_enqueue_script( 'jquery-ui-droppable' ); */
			//add_action( 'wp_enqueue_scripts', array( &$this, 'add_jquery_ui' ) );
			//wp_enqueue_script( 'jquery' );
			//wp_enqueue_script( 'jquery-ui-core' );
			//wp_enqueue_script( 'jquery-ui', '//code.jquery.com/ui/1.10.3/jquery-ui.js', array( 'jquery' ), '1.10.3' ); //need to change this to built-in
			wp_enqueue_script( 'jquery-ui-spinner' );

			// CryptoJS.MD5
			wp_enqueue_script( 'cryptojs-md5', $this->plugin_url . 'js/md5.js' );

			$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

			$this->add_jquery_ui();

			if ( $page == 'course_details' || $page == $this->screen_base . '_settings' ) {
				wp_enqueue_style( 'cp_settings', $this->plugin_url . 'css/settings.css', array(), $this->version );
				wp_enqueue_style( 'cp_settings_responsive', $this->plugin_url . 'css/settings_responsive.css', array(), $this->version );
				wp_enqueue_style( 'cp_tooltips', $this->plugin_url . 'css/tooltips.css', array(), $this->version );
				wp_enqueue_script( 'cp-plugins', $this->plugin_url . 'js/plugins.js', array( 'jquery' ), $this->version );
				wp_enqueue_script( 'cp-tooltips', $this->plugin_url . 'js/tooltips.js', array( 'jquery' ), $this->version );
				wp_enqueue_script( 'cp-settings', $this->plugin_url . 'js/settings.js', array(
					'jquery',
					'jquery-ui',
					'jquery-ui-spinner'
				), $this->version );
				wp_enqueue_script( 'cp-chosen-config', $this->plugin_url . 'js/chosen-config.js', array( 'cp-settings' ), $this->version, true );
			}

			$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

			$included_pages = apply_filters( 'cp_settings_localize_pages', array(
				'course',
				'courses',
				'course_details',
				'instructors',
				'students',
				'assessment',
				'reports',
				$this->screen_base . '_settings',
			) );
			if ( in_array( $page, $included_pages ) || ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'course_category' ) ) {

				$unit_pagination = false;
				if ( isset( $_GET['unit_id'] ) ) {
					$unit_pagination = cp_unit_uses_new_pagination( (int) $_GET['unit_id'] );
				}

				wp_enqueue_script( 'courses_bulk', $this->plugin_url . 'js/coursepress-admin.js', array( 'jquery-ui-tabs' ), $this->version );
				//wp_enqueue_script( 'courses_bulk', $this->plugin_url . 'js/coursepress-admin.js', array(), $this->version );
				wp_enqueue_script( 'wplink' );

				wp_localize_script( 'courses_bulk', 'coursepress', array(
					'delete_instructor_alert'             => __( 'Please confirm that you want to remove the instructor from this course?', 'cp' ),
					'delete_pending_instructor_alert'     => __( 'Please confirm that you want to cancel the invite. Instuctor will receive a warning when trying to activate.', 'cp' ),
					'delete_course_alert'                 => __( 'Please confirm that you want to permanently delete the course, its units, unit elements and responses?', 'cp' ),
					'delete_student_response_alert'       => __( 'Please confirm that you want to permanently delete this student answer / reponse?', 'cp' ),
					'delete_notification_alert'           => __( 'Please confirm that you want to permanently delete the notification?', 'cp' ),
					'delete_discussion_alert'             => __( 'Please confirm that you want to permanently delete the discussion?', 'cp' ),
					'withdraw_student_alert'              => __( 'Please confirm that you want to withdraw student from this course. If you withdraw, you will no longer be able to see student\'s records for this course.', 'cp' ),
					'delete_unit_alert'                   => __( 'Please confirm that you want to permanently delete the unit, its elements and responses?', 'cp' ),
					'active_student_tab'                  => ( isset( $_REQUEST['active_student_tab'] ) ? $_REQUEST['active_student_tab'] : 0 ),
					'delete_module_alert'                 => __( 'Please confirm that you want to permanently delete selected element and its responses?', 'cp' ),
					'delete_unit_page_and_elements_alert' => __( 'Please confirm that you want to permanently delete this unit page, all its elements and student responses?', 'cp' ),
					'remove_unit_page_and_elements_alert' => __( 'Please confirm that you want to remove this unit page and all its elements?', 'cp' ),
					'remove_module_alert'                 => __( 'Please confirm that you want to remove selected element?', 'cp' ),
					'delete_unit_page_label'              => __( 'Delete unit page and all elements', 'cp' ),
					'remove_row'                          => __( 'Remove', 'cp' ),
					'empty_class_name'                    => __( 'Class name cannot be empty', 'cp' ),
					'duplicated_class_name'               => __( 'Class name already exists', 'cp' ),
					'course_taxonomy_screen'              => ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == 'course_category' ? true : false ),
					'unit_page_num'                       => ( isset( $_GET['unit_page_num'] ) && $_GET['unit_page_num'] !== '' ? $_GET['unit_page_num'] : 1 ),
					'allowed_video_extensions'            => wp_get_video_extensions(),
					'allowed_audio_extensions'            => wp_get_audio_extensions(),
					'allowed_image_extensions'            => cp_wp_get_image_extensions(),
					'start_of_week'                       => get_option( 'start_of_week', 0 ),
					'unit_pagination'                     => $unit_pagination ? 1 : 0,
					'admin_ajax_url'                      => cp_admin_ajax_url(),
				) );

				do_action( 'coursepress_editor_options' );
			}
		}

		function admin_coursepress_page_course_details() {
			wp_enqueue_script( 'courses-units', $this->plugin_url . 'js/coursepress-courses.js', array(), $this->version );
			wp_enqueue_script( 'units-slimscroll', $this->plugin_url . 'js/jquery.slimscroll.min.js', array( 'jquery' ), $this->version );

			$unit_pagination = false;
			if ( isset( $_GET['unit_id'] ) ) {
				$unit_pagination = cp_unit_uses_new_pagination( (int) $_GET['unit_id'] );
			}

			add_action( 'admin_footer', array( &$this, 'add_cp2_editor' ) );

			wp_localize_script( 'courses-units', 'coursepress_units', array(
				'admin_ajax_url'              => admin_url( 'admin-ajax.php' ),
				'withdraw_class_alert'        => __( 'Please confirm that you want to withdraw all students from this class?', 'cp' ),
				'delete_class'                => __( 'Please confirm that you want to permanently delete the class? All students form this class will be moved to the Default class automatically.', 'cp' ),
				'setup_gateway'               => __( "You have selected 'This is a Paid Course'.\n In order to continue you must first setup a payment gateway by clicking on 'Setup Payment Gateways'", 'cp' ),
				'unit_setup_prompt'           => __( '<div>You have successfully completed your Basic Course Setup.</div><div>This can be changed anytime by clicking on "Course Overview".</div><div>Add and create <strong>Units</strong> for your course and add <strong>Students</strong>.</div><div>You must have at least <strong>one</strong> unit created to publish the course.</div>', 'cp' ),
				'mp_activated_prompt'         => __( '<div>Marketpress has been activated successfully.</div>', 'cp' ),
				'required_course_name'        => __( '<strong>Course Name</strong> is a required field.', 'cp' ),
				'required_course_excerpt'     => __( '<strong>Course Excerpt</strong> is a required field.', 'cp' ),
				'required_course_description' => __( '<strong>Course Description</strong> is a required field.', 'cp' ),
				'required_course_start'       => __( '<strong>Course Start Date</strong> is a required field.', 'cp' ),
				'required_course_end'         => __( '<strong>Course Start Date</strong> is a required field when "This course has no end date" is <strong>not</strong> selected.', 'cp' ),
				'required_enrollment_start'   => __( '<strong>Enrollment Start Date</strong> is a required field when "Users can enroll anytime" is <strong>not</strong> selected.', 'cp' ),
				'required_enrollment_end'     => __( '<strong>Enrollment End Date</strong> is a required field when "Users can enroll anytime" is <strong>not</strong> selected.', 'cp' ),
				'required_course_class_size'  => __( 'Value can not be 0 if "Limit class size" is selected.', 'cp' ),
				'required_course_passcode'    => __( '<strong>Pass Code</strong> required when "Anyone with a pass code" is selected', 'cp' ),
				'required_gateway'            => __( '<strong>Payment Gateway</strong> needs to be setup before you can sell this course.', 'cp' ),
				'required_price'              => __( '<strong>Price</strong> is a required field when "This is a Paid Course" is selected.', 'cp' ),
				'required_sale_price'         => __( '<strong>Sale Price</strong> is a required field when "Enable Sale Price" is selected.', 'cp' ),
				'section_error'               => __( 'There is some information missing or incorrect. Please check your input and try again.', 'cp' ),
				'cp_editor_style'             => $this->plugin_url . 'css/editor_style_fix.css',
				'unit_pagination'             => $unit_pagination ? 1 : 0,
				'admin_ajax_url'              => cp_admin_ajax_url(),
			) );

			wp_enqueue_style( 'jquery-ui-admin', $this->plugin_url . 'css/jquery-ui.css' );
			wp_enqueue_style( 'admin_coursepress_page_course_details', $this->plugin_url . 'css/admin_coursepress_page_course_details.css', array(), $this->version );
			wp_enqueue_style( 'admin_coursepress_page_course_details_responsive', $this->plugin_url . 'css/admin_coursepress_page_course_details_responsive.css', array(), $this->version );
		}

		function add_cp2_editor() {

			// Create a dummy editor to by used by the CoursePress JS object
			remove_all_filters('media_buttons'); // We can't use 3rd parties with dynamic editors
			add_action('media_buttons','media_buttons');
			add_action('media_buttons', array( $this, 'coursepress_media_button_message' ) );
			ob_start();
			wp_editor( 'dummy_editor_content', 'dummy_editor_id', array( 'wpautop'       => false,
			                                                             "textarea_name" => 'dummy_editor_name',
			) );
			$dummy_editor = ob_get_clean();

			$localize_array = array(
				'_dummy_editor'             => $dummy_editor,
				'editor_visual'             => __( 'Visual', 'cp' ),
				'editor_text'               => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', 'cp' ),
			);

			wp_enqueue_script( 'coursepress_object', $this->plugin_url . 'js/coursepress2p0-editor.js', array(
				'jquery',
				'backbone',
				'underscore'
			), $this->version );

			wp_localize_script( 'coursepress_object', '_coursepress', $localize_array );

		}

		// Media buttons on CoursePress don't work well with dynamic editor, so let users know why their buttons are gone.
		function coursepress_media_button_message() {
			echo '<div class="coursepress-media-button-message"><i class="fa fa-info-circle"></i> <span class="hidden">' . esc_html__('<p>WordPress does not normally allow dynamic visual editors, which CoursePress use quite extensively for the Course setup and Unit Builder.</p><p>As a result many plugins load their editor code too late to work properly in CoursePress.</p><p>To avoid showing broken buttons on CoursePress pages only the core "Add Media" button will be visible at this time.</p><p><strong>Close</strong></p>', 'cp') . '</span></div>';
		}

		function admin_coursepress_page_settings() {
			wp_enqueue_script( 'settings_groups', $this->plugin_url . 'js/admin-settings-groups.js', array(), $this->version );
			wp_localize_script( 'settings_groups', 'group_settings', array(
				'remove_string'      => __( 'Remove', 'cp' ),
				'delete_group_alert' => __( 'Please confirm that you want to permanently delete the group?', 'cp' )
			) );
		}

		function admin_coursepress_page_courses() {
			wp_enqueue_style( 'courses', $this->plugin_url . 'css/admin_coursepress_page_courses.css', array(), $this->version );
			wp_enqueue_style( 'courses_responsive', $this->plugin_url . 'css/admin_coursepress_page_courses_responsive.css', array(), $this->version );
		}

		function admin_coursepress_page_notifications() {
			wp_enqueue_style( 'notifications', $this->plugin_url . 'css/admin_coursepress_page_notifications.css', array(), $this->version );
			wp_enqueue_style( 'notifications_responsive', $this->plugin_url . 'css/admin_coursepress_page_notifications_responsive.css', array(), $this->version );
		}

		function admin_coursepress_page_discussions() {
			wp_enqueue_style( 'discussions', $this->plugin_url . 'css/admin_coursepress_page_discussions.css', array(), $this->version );
			wp_enqueue_style( 'discussions_responsive', $this->plugin_url . 'css/admin_coursepress_page_discussions_responsive.css', array(), $this->version );
		}

		function admin_coursepress_page_reports() {
			wp_enqueue_style( 'reports', $this->plugin_url . 'css/admin_coursepress_page_reports.css', array(), $this->version );
			wp_enqueue_style( 'reports_responsive', $this->plugin_url . 'css/admin_coursepress_page_reports_responsive.css', array(), $this->version );
			wp_enqueue_script( 'reports-admin', $this->plugin_url . 'js/reports-admin.js', array(), $this->version );
			wp_enqueue_style( 'jquery-ui-admin', $this->plugin_url . 'css/jquery-ui.css' ); //need to change this to built-in
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-tabs' );
		}

		function admin_coursepress_page_assessment() {
			wp_enqueue_style( 'assessment', $this->plugin_url . 'css/admin_coursepress_page_assessment.css', array(), $this->version );
			wp_enqueue_style( 'assessment_responsive', $this->plugin_url . 'css/admin_coursepress_page_assessment_responsive.css', array(), $this->version );
			wp_enqueue_script( 'assessment-admin', $this->plugin_url . 'js/assessment-admin.js', array(), $this->version );
			wp_enqueue_style( 'jquery-ui-admin', $this->plugin_url . 'css/jquery-ui.css' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-tabs' );
		}

		function admin_coursepress_page_certificates() {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_media();
			wp_enqueue_script( 'media-upload' );

			wp_enqueue_style( 'certificates', $this->plugin_url . 'css/admin_coursepress_page_certificates.css', array(), $this->version );
			wp_enqueue_script( 'certificates-admin', $this->plugin_url . 'js/certificates-admin.js', array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'jquery-ui-droppable',
				'jquery-ui-accordion',
				'wp-color-picker',
				'thickbox',
				'media-upload'
			), $this->version );

			wp_localize_script( 'certificates-admin', 'certificate', array(
				'max_elements_message' => __( 'Maximum of 4 certificate elements are allowed per row.', 'cp' ),
			) );
			//wp_enqueue_style( 'jquery-ui-admin', $this->plugin_url . 'css/jquery-ui.css' );
			//wp_enqueue_script( 'jquery-ui-core' );
			//wp_enqueue_script( 'jquery-ui-tabs' );
		}

		function admin_coursepress_page_students() {
			wp_enqueue_style( 'students', $this->plugin_url . 'css/admin_coursepress_page_students.css', array(), $this->version );
			wp_enqueue_style( 'students_responsive', $this->plugin_url . 'css/admin_coursepress_page_students_responsive.css', array(), $this->version );
			wp_enqueue_script( 'students', $this->plugin_url . 'js/students-admin.js', array(), $this->version );
			wp_localize_script( 'students', 'student', array(
				'delete_student_alert' => __( 'Please confirm that you want to remove the student and the all associated records?', 'cp' ),
			) );
		}

		function admin_coursepress_page_instructors() {
			wp_enqueue_style( 'instructors', $this->plugin_url . 'css/admin_coursepress_page_instructors.css', array(), $this->version );
			wp_enqueue_style( 'instructors_responsive', $this->plugin_url . 'css/admin_coursepress_page_instructors_responsive.css', array(), $this->version );
			wp_enqueue_script( 'instructors', $this->plugin_url . 'js/instructors-admin.js', array(), $this->version );
			wp_localize_script( 'instructors', 'instructor', array(
				'delete_instructors_alert' => __( 'Please confirm that you want to remove the instructor and the all associated records?', 'cp' ),
			) );
		}

		function remove_pre_next_post() {
			// Prevent previous next links from showing on virtual pages
			remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		}

		function create_virtual_pages() {

			$full_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$site_url = site_url();

			$uri = untrailingslashit( trim( ltrim( str_replace( $site_url, '', $full_url ), '/' ) ) );

			//$uri = untrailingslashit( trim( ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) ) );

			$match_uri = $uri; // Use this to test regex pattern

			$multisite = false;
			if ( is_multisite() && ! is_subdomain_install() ) {
				$blog_details = get_blog_details( get_current_blog_id() );
				$multisite    = true;
				$site_uri     = ltrim( $blog_details->path, '/' );
				$match_uri    = str_replace( $site_uri, '', $uri );
			}


			$args = array(
				'name'        => $uri,
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => 1
			);

			$last_redirect = get_transient( 'coursepress_last_redirect_' . get_current_blog_id() );


			//Enrollment process page
			$pattern = '/^(' . $this->get_enrollment_process_slug() . '|' . get_option( 'enrollment_process_slug', 'enrollment-process' ) . ')/';
			if ( preg_match( $pattern, $match_uri ) ) {
				//if ( preg_match( '/' . $this->get_enrollment_process_slug() . '/', $uri ) ) {
				$theme_file = locate_template( array( 'enrollment-process.php' ) );

				$page = get_option( 'coursepress_enrollment_process_page', '0' );

				if ( $this->virtual_page_redirect( $page, $this->get_enrollment_process_slug( $uri ), $last_redirect ) ) {
					return false;
				};

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {

					$args = array(
						'slug'    => $this->get_enrollment_process_slug(),
						'title'   => __( 'Enrollment', 'cp' ),
						'content' => $this->get_template_details( $this->plugin_dir . 'includes/templates/enrollment-process.php' ),
						'type'    => 'virtual_page'
					);

					$pg = new CoursePress_Virtual_Page( $args );
				}
				$this->set_latest_activity( get_current_user_id() );
			}

			//Custom login page
			$pattern = '/^(' . $this->get_login_slug() . '|' . get_option( 'login_slug', 'student-login' ) . ')/';
			if ( preg_match( $pattern, $match_uri ) ) {
				//if ( preg_match( '/' . $this->get_login_slug() . '/', $uri ) ) {

				$theme_file = locate_template( array( 'student-login.php' ) );

				$page = get_option( 'coursepress_login_page', '0' );
				if ( $this->virtual_page_redirect( $page, $this->get_login_slug( $uri ), $last_redirect ) ) {
					return false;
				};

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {

					$args = array(
						'slug'    => $this->get_login_slug(),
						'title'   => __( 'Login', 'cp' ),
						'content' => $this->get_template_details( $this->plugin_dir . 'includes/templates/student-login.php' ),
						'type'    => 'virtual_page',
						'is_page' => true,
					);
					$pg   = new CoursePress_Virtual_Page( $args );
				}
				$this->set_latest_activity( get_current_user_id() );
			}

			//Custom signup page
			$pattern = '/^(' . $this->get_signup_slug() . '|' . get_option( 'signup_slug', 'courses-signup' ) . ')/';
			if ( preg_match( $pattern, $match_uri ) ) {
				//if ( preg_match( '/' . $this->get_signup_slug() . '/', $uri ) ) {
				$theme_file = locate_template( array( 'student-signup.php' ) );

				$page = get_option( 'coursepress_signup_page', '0' );
				if ( $this->virtual_page_redirect( $page, $this->get_signup_slug( $uri ), $last_redirect ) ) {
					return false;
				};

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {

					$args = array(
						'slug'    => $this->get_signup_slug(),
						'title'   => __( 'Sign Up', 'cp' ),
						'content' => $this->get_template_details( $this->plugin_dir . 'includes/templates/student-signup.php' ),
						'type'    => 'virtual_page',
						'is_page' => true,
					);
					$pg   = new CoursePress_Virtual_Page( $args );
				}
				$this->set_latest_activity( get_current_user_id() );
			}

			//Student Dashboard page
			$pattern = '/^(' . $this->get_student_dashboard_slug() . '|' . get_option( 'student_dashboard_slug', 'courses-dashboard' ) . ')/';
			if ( preg_match( $pattern, $match_uri ) ) {
				//if ( preg_match( '/' . $this->get_student_dashboard_slug() . '/', $uri ) ) {
				$theme_file = locate_template( array( 'student-dashboard.php' ) );

				$page = get_option( 'coursepress_student_dashboard_page', '0' );
				if ( $this->virtual_page_redirect( $page, $this->get_student_dashboard_slug( $uri ), $last_redirect ) ) {
					return false;
				};

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {

					$args = array(
						'slug'    => $this->get_student_dashboard_slug(),
						'title'   => __( 'Dashboard - Courses', 'cp' ),
						'content' => $this->get_template_details( $this->plugin_dir . 'includes/templates/student-dashboard.php' ),
						'type'    => 'virtual_page'
					);
					$pg   = new CoursePress_Virtual_Page( $args );
				}
				$this->set_latest_activity( get_current_user_id() );
			}

			//Student Settings page
			$pattern = '/^(' . $this->get_student_settings_slug() . '|' . get_option( 'student_settings_slug', 'settings' ) . ')/';
			if ( preg_match( $pattern, $match_uri ) ) {
				$theme_file = locate_template( array( 'student-settings.php' ) );

				$page = get_option( 'coursepress_student_settings_page', '0' );
				if ( $this->virtual_page_redirect( $page, $this->get_student_settings_slug( $uri ), $last_redirect ) ) {
					return false;
				};

				if ( $theme_file != '' ) {
					require_once( $theme_file );
					exit;
				} else {

					$args = array(
						'slug'    => $this->get_student_settings_slug(),
						'title'   => __( 'Dashboard - My Profile', 'cp' ),
						'content' => $this->get_template_details( $this->plugin_dir . 'includes/templates/student-settings.php' ),
						'type'    => 'virtual_page'
					);

					$pg = new CoursePress_Virtual_Page( $args );
				}
				$this->set_latest_activity( get_current_user_id() );
			}
		}

		function virtual_page_redirect( $page, $url, $last_redirect = false ) {
			if ( ! empty( $page ) && $page != $last_redirect ) {
				// Transient expires in 5 seconds... prevents loop, but still allows original slug to be used.

				set_transient( 'coursepress_last_redirect_' . get_current_blog_id(), $page, 5 );
				wp_redirect( esc_url_raw( $url ) );
				die();
			} elseif ( ! empty( $page ) ) {
				return true;
			} else {
				return false;
			}
		}

		function check_for_get_actions() {

			/* Withdraw a Student from course in frontend Student Dashboard */
			//Allows logged in user to withdraw only himself from a course.
			if ( ! empty( $_GET['withdraw'] ) && is_numeric( $_GET['withdraw'] ) && is_user_logged_in() ) {
				if ( ! empty( $_GET['course_nonce'] ) && wp_verify_nonce( $_GET['course_nonce'], 'withdraw_from_course_' . $_GET['withdraw'] ) ) {
					$student = new Student( get_current_user_id() );
					$student->withdraw_from_course( $_GET['withdraw'] );
				}
			}
		}

		//shows a warning notice to admins if pretty permalinks are disabled
		function admin_nopermalink_warning() {
			if ( current_user_can( 'manage_options' ) && ! get_option( 'permalink_structure' ) ) {
				// toplevel_page_courses
				$screen       = get_current_screen();
				$show_warning = true;

				if ( 'toplevel_page_courses' == $screen->id && isset( $_GET['quick_setup'] ) ) {
					$show_warning = false;
				}

				if ( $show_warning ) {
					echo '<div class ="error"><p>' . sprintf( __( '<strong>%s is almost ready</strong>. You must <a href ="options-permalink.php">update your permalink structure</a> to something other than the default for it to work.', 'cp' ), $this->name ) . '</p></div>';
				}
			}
		}

		// updates login/logout navigation link
		function menu_metabox_navigation_links( $sorted_menu_items, $args ) {
			$is_in = is_user_logged_in();

			$new_menu_items = array();
			foreach ( $sorted_menu_items as $menu_item ) {
				// LOGIN / LOGOUT
				if ( CoursePress::instance()->get_login_slug( true ) == $menu_item->url && $is_in ) {
					$menu_item->post_title = __( 'Log Out', 'cp' );
					$menu_item->title      = $menu_item->post_title;
					$menu_item->url        = wp_logout_url();
				}

				// Remove personalised items
				if ( ( CoursePress::instance()->get_student_dashboard_slug( true ) == $menu_item->url ||
				       CoursePress::instance()->get_student_settings_slug( true ) == $menu_item->url ) &&
				     ! $is_in
				) {
					continue;
				}

				$new_menu_items[] = $menu_item;
			}

			return $new_menu_items;
		}

		//adds our links to custom theme nav menus using wp_nav_menu()
		function main_navigation_links( $sorted_menu_items, $args ) {
			if ( ! is_admin() ) {

				$theme_location = 'primary';
				if ( ! has_nav_menu( $theme_location ) ) {
					$theme_locations = get_nav_menu_locations();
					foreach ( (array) $theme_locations as $key => $location ) {
						$theme_location = $key;
						break;
					}
				}

				if ( $args->theme_location == $theme_location ) {//put extra menu items only in primary ( most likely header ) menu
					$is_in = is_user_logged_in();

					$courses = new stdClass;

					$courses->title            = __( 'Courses', 'cp' );
					$courses->description      = '';
					$courses->menu_item_parent = 0;
					$courses->ID               = 'cp-courses';
					$courses->db_id            = '';
					$courses->url              = $this->get_course_slug( true );
					if ( cp_curPageURL() == $courses->url ) {
						$courses->classes[] = 'current_page_item';
					}
					$sorted_menu_items[] = $courses;

					/* Student Dashboard page */

					if ( $is_in ) {
						$dashboard = new stdClass;

						$dashboard->title            = __( 'Dashboard', 'cp' );
						$dashboard->description      = '';
						$dashboard->menu_item_parent = 0;
						$dashboard->ID               = 'cp-dashboard';
						$dashboard->db_id            = - 9998;
						$dashboard->url              = $this->get_student_dashboard_slug( true );
						$dashboard->classes[]        = 'dropdown';
						/* if ( cp_curPageURL() == $dashboard->url ) {
						  $dashboard->classes[] = 'current_page_item';
						  } */
						$sorted_menu_items[] = $dashboard;


						/* Student Dashboard > Courses page */

						$dashboard_courses = new stdClass;

						$dashboard_courses->title            = __( 'My Courses', 'cp' );
						$dashboard_courses->description      = '';
						$dashboard_courses->menu_item_parent = - 9998;
						$dashboard_courses->ID               = 'cp-dashboard-courses';
						$dashboard_courses->db_id            = '';
						$dashboard_courses->url              = $this->get_student_dashboard_slug( true );
						if ( cp_curPageURL() == $dashboard_courses->url ) {
							$dashboard_courses->classes[] = 'current_page_item';
						}
						$sorted_menu_items[] = $dashboard_courses;

						/* Student Dashboard > Settings page */

						$settings_profile = new stdClass;

						$settings_profile->title            = __( 'My Profile', 'cp' );
						$settings_profile->description      = '';
						$settings_profile->menu_item_parent = - 9998;
						$settings_profile->ID               = 'cp-dashboard-settings';
						$settings_profile->db_id            = '';
						$settings_profile->url              = $this->get_student_settings_slug( true );
						if ( cp_curPageURL() == $settings_profile->url ) {
							$settings_profile->classes[] = 'current_page_item';
						}
						$sorted_menu_items[] = $settings_profile;

						/* Inbox */
						if ( get_option( 'show_messaging', 0 ) == 1 ) {
							$unread_count = cp_messaging_get_unread_messages_count();
							if ( $unread_count > 0 ) {
								$unread_count = ' (' . $unread_count . ')';
							} else {
								$unread_count = '';
							}
							$settings_inbox = new stdClass;

							$settings_inbox->title            = __( 'Inbox', 'cp' ) . $unread_count;
							$settings_inbox->description      = '';
							$settings_inbox->menu_item_parent = - 9998;
							$settings_inbox->ID               = 'cp-dashboard-inbox';
							$settings_inbox->db_id            = '';
							$settings_inbox->url              = $this->get_inbox_slug( true );
							if ( cp_curPageURL() == $settings_inbox->url ) {
								$settings_profile->classes[] = 'current_page_item';
							}
							$sorted_menu_items[] = $settings_inbox;
						}
					}

					/* Sign up page */

					// $signup = new stdClass;
					//
					// if ( ! $is_in ) {
					//     $signup->title = __( 'Sign Up', 'cp' );
					//     $signup->menu_item_parent = 0;
					//     $signup->ID = 'cp-signup';
					//     $signup->db_id = '';
					//     $signup->url = trailingslashit( site_url() . '/' . $this->get_signup_slug() );
					//     $sorted_menu_items[] = $signup;
					// }

					/* Log in / Log out links */

					$login = new stdClass;
					if ( $is_in ) {
						$login->title = __( 'Log Out', 'cp' );
					} else {
						$login->title = __( 'Log In', 'cp' );
					}
					$login->description      = '';
					$login->menu_item_parent = 0;
					$login->ID               = 'cp-logout';
					$login->db_id            = '';
					$login->url              = $is_in ? wp_logout_url() : ( get_option( 'use_custom_login_form', 1 ) ? $this->get_login_slug( true ) : wp_login_url() );

					$sorted_menu_items[] = $login;
				}

				return $sorted_menu_items;
			}
		}

		function main_navigation_links_fallback( $current_menu ) {

			if ( ! is_admin() ) {
				$is_in = is_user_logged_in();

				$courses = new stdClass;

				$courses->title            = __( 'Courses', 'cp' );
				$courses->menu_item_parent = 0;
				$courses->ID               = 'cp-courses';
				$courses->db_id            = '';
				$courses->url              = $this->get_course_slug( true );
				if ( cp_curPageURL() == $courses->url ) {
					$courses->classes[] = 'current_page_item';
				}
				$main_sorted_menu_items[] = $courses;

				/* Student Dashboard page */

				if ( $is_in ) {
					$dashboard = new stdClass;

					$dashboard->title            = __( 'Dashboard', 'cp' );
					$dashboard->menu_item_parent = 0;
					$dashboard->ID               = 'cp-dashboard';
					$dashboard->db_id            = - 9998;
					$dashboard->url              = $this->get_student_dashboard_slug( true );
					/* if ( cp_curPageURL() == $dashboard->url ) {
					  $dashboard->classes[] = 'current_page_item';
					  } */
					$main_sorted_menu_items[] = $dashboard;

					/* Student Dashboard > Courses page */

					$dashboard_courses                   = new stdClass;
					$dashboard_courses->title            = __( 'My Courses', 'cp' );
					$dashboard_courses->menu_item_parent = - 9998;
					$dashboard_courses->ID               = 'cp-dashboard-courses';
					$dashboard_courses->db_id            = '';
					$dashboard_courses->url              = $this->get_student_dashboard_slug( true );
					if ( cp_curPageURL() == $dashboard_courses->url ) {
						$dashboard_courses->classes[] = 'current_page_item';
					}
					$sub_sorted_menu_items[] = $dashboard_courses;


					/* Student Dashboard > Settings page */

					$settings_profile = new stdClass;

					$settings_profile->title            = __( 'My Profile', 'cp' );
					$settings_profile->menu_item_parent = - 9998;
					$settings_profile->ID               = 'cp-dashboard-settings';
					$settings_profile->db_id            = '';
					$settings_profile->url              = $this->get_student_settings_slug( true );
					if ( cp_curPageURL() == $settings_profile->url ) {
						$settings_profile->classes[] = 'current_page_item';
					}
					$sub_sorted_menu_items[] = $settings_profile;

					/* Inbox */
					if ( get_option( 'show_messaging', 0 ) == 1 ) {
						$unread_count = cp_messaging_get_unread_messages_count();
						if ( $unread_count > 0 ) {
							$unread_count = ' (' . $unread_count . ')';
						} else {
							$unread_count = '';
						}

						$settings_inbox = new stdClass;

						$settings_inbox->title            = __( 'Inbox', 'cp' ) . $unread_count;
						$settings_inbox->menu_item_parent = - 9998;
						$settings_inbox->ID               = 'cp-dashboard-inbox';
						$settings_inbox->db_id            = '';
						$settings_inbox->url              = $this->get_inbox_slug( true );
						if ( cp_curPageURL() == $settings_inbox->url ) {
							$settings_profile->classes[] = 'current_page_item';
						}
						$sub_sorted_menu_items[] = $settings_inbox;
					}
				}

				/* Sign up page */

				// $signup = new stdClass;
				//
				// if ( ! $is_in ) {
				//     $signup->title = __( 'Sign Up', 'cp' );
				//     $signup->menu_item_parent = 0;
				//     $signup->ID = 'cp-signup';
				//     $signup->db_id = '';
				//     $signup->url = trailingslashit( site_url() . '/' . $this->get_signup_slug() );
				//     $main_sorted_menu_items[] = $signup;
				// }

				/* Log in / Log out links */

				$login = new stdClass;
				if ( $is_in ) {
					$login->title = __( 'Log Out', 'cp' );
				} else {
					$login->title = __( 'Log In', 'cp' );
				}

				$login->menu_item_parent = 0;
				$login->ID               = 'cp-logout';
				$login->db_id            = '';
				$login->url              = $is_in ? wp_logout_url() : ( get_option( 'use_custom_login_form', 1 ) ? $this->get_login_slug( true ) : wp_login_url() );

				$main_sorted_menu_items[] = $login;
				?>
				<div class="menu">
					<ul class='nav-menu'>
						<?php
						foreach ( $main_sorted_menu_items as $menu_item ) {
							?>
							<li class='menu-item-<?php echo $menu_item->ID; ?>'>
								<a id="<?php echo $menu_item->ID; ?>" href="<?php echo $menu_item->url; ?>"><?php echo $menu_item->title; ?></a>
								<?php if ( $menu_item->db_id !== '' ) { ?>
									<ul class="sub-menu dropdown-menu">
										<?php
										foreach ( $sub_sorted_menu_items as $menu_item ) {
											?>
											<li class='menu-item-<?php echo $menu_item->ID; ?>'>
												<a id="<?php echo $menu_item->ID; ?>" href="<?php echo $menu_item->url; ?>"><?php echo $menu_item->title; ?></a>
											</li>
										<?php } ?>
									</ul>
								<?php } ?>
							</li>
						<?php
						}
						?>
					</ul>
				</div>

			<?php
			}
		}

		function mobile_navigation_links_fallback( $current_menu ) {

			if ( ! is_admin() ) {
				$is_in = is_user_logged_in();

				$courses = new stdClass;

				$courses->title            = __( 'Courses', 'cp' );
				$courses->menu_item_parent = 0;
				$courses->ID               = 'cp-courses-mobile';
				$courses->db_id            = '';
				$courses->url              = $this->get_course_slug( true );
				if ( cp_curPageURL() == $courses->url ) {
					$courses->classes[] = 'current_page_item';
				}
				$main_sorted_menu_items[] = $courses;

				/* Student Dashboard page */

				if ( $is_in ) {
					$dashboard = new stdClass;

					$dashboard->title            = __( 'Dashboard', 'cp' );
					$dashboard->menu_item_parent = 0;
					$dashboard->ID               = 'cp-dashboard-mobile';
					$dashboard->db_id            = - 9998;
					$dashboard->url              = $this->get_student_dashboard_slug( true );

					$main_sorted_menu_items[] = $dashboard;

					/* Student Dashboard > Courses page */

					$dashboard_courses                   = new stdClass;
					$dashboard_courses->title            = __( 'My Courses', 'cp' );
					$dashboard_courses->menu_item_parent = - 9998;
					$dashboard_courses->ID               = 'cp-dashboard-courses-mobile';
					$dashboard_courses->db_id            = '';
					$dashboard_courses->url              = $this->get_student_dashboard_slug( true );
					if ( cp_curPageURL() == $dashboard_courses->url ) {
						$dashboard_courses->classes[] = 'current_page_item';
					}
					$sub_sorted_menu_items[] = $dashboard_courses;

					/* Student Dashboard > Settings page */

					$settings_profile = new stdClass;

					$settings_profile->title            = __( 'My Profile', 'cp' );
					$settings_profile->menu_item_parent = - 9998;
					$settings_profile->ID               = 'cp-dashboard-settings-mobile';
					$settings_profile->db_id            = '';
					$settings_profile->url              = $this->get_student_settings_slug( true );
					if ( cp_curPageURL() == $settings_profile->url ) {
						$settings_profile->classes[] = 'current_page_item';
					}
					$sub_sorted_menu_items[] = $settings_profile;
				}

				/* Log in / Log out links */

				$login = new stdClass;
				if ( $is_in ) {
					$login->title = __( 'Log Out', 'cp' );
				} else {
					$login->title = __( 'Log In', 'cp' );
				}

				$login->menu_item_parent = 0;
				$login->ID               = 'cp-logout-mobile';
				$login->db_id            = '';
				$login->url              = $is_in ? wp_logout_url() : ( get_option( 'use_custom_login_form', 1 ) ? $this->get_login_slug( true ) : wp_login_url() );

				$main_sorted_menu_items[] = $login;
				?>
				<div class="menu">
					<ul id="mobile_menu" class='mobile_menu'>
						<?php
						foreach ( $main_sorted_menu_items as $menu_item ) {
							?>
							<li class='menu-item-<?php echo $menu_item->ID; ?>'>
								<a id="<?php echo $menu_item->ID; ?>" href="<?php echo $menu_item->url; ?>"><?php echo $menu_item->title; ?></a>
							</li>
							<?php if ( $menu_item->db_id !== '' ) { ?>
								<?php
								foreach ( $sub_sorted_menu_items as $menu_item ) {
									?>
									<li><a href="<?php echo $menu_item->url; ?>"><?php echo $menu_item->title; ?></a>
									</li>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</ul>
				</div>
			<?php
			}
		}

		function login_redirect( $redirect_to, $request, $user ) {

			$redirect_users_after_login = get_option( 'redirect_students_to_dashboard', 1 );

			if ( $redirect_users_after_login ) {
				if ( defined( 'DOING_AJAX' ) && 'DOING_AJAX ' ) {
					exit;
				}
				global $user;

				if ( isset( $user->ID ) ) {

					if ( current_user_can( 'manage_options' ) ) {
						return admin_url();
					} else {
						$role_s = get_user_option( 'role', $user->ID );
						$role_i = get_user_option( 'role_ins', $user->ID );

						if ( $role_i == 'instructor' ) {
							return admin_url();
						} else if ( $role_s == 'student' || $role_s == false || $role_s == '' ) {
							return trailingslashit( home_url() ) . trailingslashit( $this->get_student_dashboard_slug() );
						} else {//unknown case
							return admin_url();
						}
					}
				}
			} else {
				return $redirect_to;
			}
		}

		function no_comments_template( $template ) {
			global $post;
			$post_types = array( 'virtual_page', 'course' );
			if ( in_array( $post->post_type, $post_types ) ) {
				$template = $this->plugin_dir . 'includes/templates/no-comments.php';
			}

			return $template;
		}

		function comments_template( $template ) {
			global $wp_query, $withcomments, $post, $wpdb, $id, $comment, $user_login, $user_ID, $user_identity, $overridden_cpage;

			if ( get_post_type( $id ) == 'course' ) {
				$template = $this->plugin_dir . 'includes/templates/no-comments.php';
			}

			return $template;
		}

		function check_for_valid_post_type_permalinks( $permalink, $post, $leavename ) {
			if ( get_post_type( $post->ID ) == 'discussions' ) {
				$course_id = get_post_meta( $post->ID, 'course_id', true );
				if ( ! empty( $course_id ) ) {
					$course_obj = new Course( $course_id );
					$course     = $course_obj->get_course();

					return str_replace( '%course%', $course->post_name, $permalink );
				} else {
					return $permalink;
				}
			} else if ( get_post_type( $post->ID ) == 'notifications' ) {
				$course_id = get_post_meta( $post->ID, 'course_id', true );
				if ( ! empty( $course_id ) ) {
					$course_obj = new Course( $course_id );
					$course     = $course_obj->get_course();

					return str_replace( '%course%', $course->post_name, $permalink );
				} else {
					return $permalink;
				}
			} else if ( get_post_type( $post->ID ) == 'unit' ) {
				return Unit::get_permalink( $post->ID );
			} else {
				return $permalink;
			}
		}

		function output_buffer() {
			// if( defined( 'DOING_AJAX' ) && DOING_AJAX ) { cp_write_log( 'doing ajax' ); }
			ob_start();
		}

		/* Check if user is currently active on the website */

		function user_is_currently_active( $user_id, $latest_activity_in_minutes = 5 ) {
			if ( empty( $user_id ) ) {
				exit;
			}
			$latest_user_activity = get_user_meta( $user_id, 'latest_activity', true );
			$current_time         = current_time( 'timestamp' );

			$minutes_ago = round( abs( $current_time - $latest_user_activity ) / 60, 2 );

			if ( $minutes_ago <= $latest_activity_in_minutes ) {
				return true;
			} else {
				return false;
			}
		}

		/* Check if MarketPress plugin is installed and active ( using in Course Overview ) */

		function is_marketpress_active() {

			// Don't allow on campus
			if ( CoursePress_Capabilities::is_campus() ) {
				return false;
			}

			$plugins = get_option( 'active_plugins' );

			if ( is_multisite() ) {
				$active_sitewide_plugins = get_site_option( "active_sitewide_plugins" );
			} else {
				$active_sitewide_plugins = array();
			}

			if ( preg_grep( '/marketpress.php/', $plugins ) || preg_grep( '/marketpress.php/', $active_sitewide_plugins ) ) {
				return true;
			} else {
				return false;
			}
		}

		/* Check if MarketPress Lite plugin is installed and active */

		function is_marketpress_lite_active() {

			// Don't allow on campus
			if ( CoursePress_Capabilities::is_campus() ) {
				return;
			}

			$plugins = get_option( 'active_plugins' );

			if ( is_multisite() ) {
				$active_sitewide_plugins = get_site_option( "active_sitewide_plugins" );
			} else {
				$active_sitewide_plugins = array();
			}

			$required_plugin = 'wordpress-ecommerce/marketpress.php';

			if ( in_array( $required_plugin, $plugins ) || cp_is_plugin_network_active( $required_plugin ) || preg_grep( '/^marketpress.*/', $plugins ) || cp_preg_array_key_exists( '/^marketpress.*/', $active_sitewide_plugins ) ) {
				return true;
			} else {
				return false;
			}
		}

		/* Check if MarketPress Lite ( included in CoursePress ) plugin is installed and active */

		function is_cp_marketpress_lite_active( $req_plugin = '' ) {

			// Don't allow on campus
			if ( CoursePress_Capabilities::is_campus() ) {
				return false;
			}

			$plugins = get_option( 'active_plugins' );

			if ( is_multisite() ) {
				$active_sitewide_plugins = get_site_option( "active_sitewide_plugins" );
			} else {
				$active_sitewide_plugins = array();
			}

			if ( $req_plugin !== '' ) {
				$required_plugin = $req_plugin;
			} else {
				$required_plugin = 'marketpress/marketpress.php';
			}

			if ( in_array( $required_plugin, $plugins ) || cp_is_plugin_network_active( $required_plugin ) || preg_grep( '/^marketpress.*/', $plugins ) || cp_preg_array_key_exists( '/^marketpress.*/', $active_sitewide_plugins ) ) {
				return true;
			} else {
				return false;
			}
		}

		function marketpress_check() {

			// Don't allow on campus
			if ( CoursePress_Capabilities::is_campus() ) {
				return false;
			}

			if ( CoursePress::instance()->is_marketpress_lite_active() || CoursePress::instance()->is_cp_marketpress_lite_active() || CoursePress::instance()->is_marketpress_active() ) {
				CoursePress::instance()->marketpress_active = true;
			} else {
				CoursePress::instance()->marketpress_active = false;
			}
		}

		/* Check if Chat plugin is installed and activated ( using in Chat unit module ) */

		function cp_is_chat_plugin_active() {
			$plugins = get_option( 'active_plugins' );

			if ( is_multisite() ) {
				$active_sitewide_plugins = get_site_option( "active_sitewide_plugins" );
			} else {
				$active_sitewide_plugins = array();
			}

			$required_plugin = 'wordpress-chat/wordpress-chat.php';

			if ( in_array( $required_plugin, $plugins ) || cp_is_plugin_network_active( $required_plugin ) || preg_grep( '/^wordpress-chat.*/', $plugins ) || cp_preg_array_key_exists( '/^wordpress-chat.*/', $active_sitewide_plugins ) ) {
				return true;
			} else {
				return false;
			}
		}

		/* Listen for WooCommerce purchase status changes */

		function woo_listen_for_paid_status_for_courses( $order_id ) {
			$wc_order = wc_get_order( $order_id );
			$items    = $wc_order->get_items();

			$post_customer_id = get_post_meta( $order_id, '_customer_user', true );

			$student = new Student( $post_customer_id );

			foreach ( $items as $item ) {
				$course_id = get_post_meta( $item['product_id'], 'cp_course_id', true );
				if ( ! empty( $course_id ) && ! $student->user_enrolled_in_course($course_id) ) {
					$student->enroll_in_course( $course_id );
				}
			}
		}


		/* Make PDF report */

		function pdf_report( $report = '', $report_name = '', $report_title = 'Student Report', $preview = false ) {
			//ob_end_clean();
			ob_start();

			// Use CoursePress_PDF which extends TCPDF
			require_once( $this->plugin_dir . 'includes/classes/class.coursepress-pdf.php' );

			// create new PDF document
			$pdf = new CoursePress_PDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

			// set document information
			$pdf->SetCreator( $this->name );
			$pdf->SetTitle( $report_title );
			$pdf->SetKeywords( '' );

			// remove default header/footer
			$pdf->setPrintHeader( false );
			$pdf->setPrintFooter( false );

			// set default monospaced font
			$pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

			//set margins
			$pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
			$pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
			$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );

			//set auto page breaks
			$pdf->SetAutoPageBreak( true, PDF_MARGIN_BOTTOM );

			//set image scale factor
			//$pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );
			//set some language-dependent strings
			//			$pdf->setLanguageArray( $l );
			// ---------------------------------------------------------
			// set font
			$reports_font = get_option( 'reports_font', 'helvetica' );
			$pdf->SetFont( $reports_font, '', apply_filters( 'cp_report_font_size', 12 ) );
			// add a page
			$pdf->AddPage();
			$html = '';
			$html .= make_clickable( wpautop( $report ) );
			// output the HTML content
			$pdf->writeHTML( $html, true, false, true, false, '' );
			//Close and output PDF document

			ob_get_clean();

			if ( $preview ) {
				$pdf->Output( $report_name, 'I' );
			} else {
				$pdf->Output( $report_name, 'D' );
			}

			exit;
		}

		public static function instance( $instance = null ) {
			if ( ! $instance || 'CoursePress' != get_class( $instance ) ) {
				if ( is_null( self::$instance ) ) {
					self::$instance = new CoursePress();
				}
			} else {
				if ( is_null( self::$instance ) ) {
					self::$instance = $instance;
				}
			}

			return self::$instance;
		}

	}

}

CoursePress::instance( new CoursePress() );
global $coursepress;
$coursepress = CoursePress::instance();


//DEBUG
//add_action( 'init', '_20151203_test' );
//function _20151203_test () {
//	$test = Course::get_units_with_modules( 77 );
//	$test2 = Course::get_units_with_modules( 5489 );
//	$test3 = Course::get_units_with_modules( 2382 );
//	$test4 = Course::get_units_with_modules( 1480 );
//	$test5 = Course::get_units_with_modules( 4943 );
//}
