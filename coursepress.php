<?php
/**
 * Plugin Name: CoursePress Base
 * Version:     PLUGIN_VERSION
 * Description: CoursePress Pro turns WordPress into a powerful online learning platform. Set up online courses by creating learning units with quiz elements, video, audio etc. You can also assess student work, sell your courses and much much more.
 * Author:      WPMU DEV
 * Author URI:  http://premium.wpmudev.org
 * Plugin URI:  http://premium.wpmudev.org/project/coursepress/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * TextDomain:  cp
 * Domain Path: /languages
 * Build Time:  2016-04-07T13:37:59.644Z
 * WDP ID:      913071
 *
 * @package CoursePress
 */

/**
 * Copyright notice.
 *
 * @copyright Incsub (http://incsub.com/)
 *
 * Authors: WPMU DEV
 * Contributors: Rheinard Korf (Incsub), Paul Menard
 *
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( is_admin() ) {
	//require_once 'inc/admin/admin-functions.php';
}

final class CoursePress {
	/**
	 * @var string Current version number.
	 */
	var $version = '3';

	/**
	 * @var string Plugin name, it will be replaced by grunt build command.
	 */
	var $name = 'CoursePress Base';

	/**
	 * @var string
	 */
	var $plugin_url;

	/**
	 * @var string The absolute path where CP is installed.
	 */
	var $plugin_path;

	/**
	 * @var array List of classes that are loaded both admin and front.
	 */
	protected $core_classes = array(
		'CoursePress_Data_Users',
		'CoursePress_Core',
		'CoursePress_Extension',
		'CoursePress_Cron_Discussion',
		'CoursePress_Cron_EmailAlert',
	);

	/**
	 * @var array List of classes that are loaded in CP admin pages only.
	 */
	protected $core_admin_classes = array(
		'CoursePress_Admin_Page',
		'CoursePress_Admin_Ajax',
		'CoursePress_Admin_Actions',
	);

	/**
	 * @var array List of classes that are loaded in front front pages only.
	 */
	protected $core_front_classes = array(
		'CoursePress_FrontPage',
		'CoursePress_Shortcode',
		'CoursePress_Menu',
	);

	public function __construct() {
		$this->plugin_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		$this->plugin_url = plugins_url( 'coursepress/' );

		// Load functions files
		try {
			require_once $this->plugin_path . 'inc/functions/utility.php';
			require_once $this->plugin_path . 'inc/functions/user.php';
			require_once $this->plugin_path . 'inc/functions/course.php';
			require_once $this->plugin_path . 'inc/functions/unit.php';
			require_once $this->plugin_path . 'inc/functions/admin.php';
			// Load external source
			require_once $this->plugin_path . 'inc/external/wpmu-lib/core.php';
		} catch ( Exception $e ) {
			// @todo: Throw error
			return;
		}

		// Autload classes on demand
		spl_autoload_register( array( $this, 'class_loader' ) );

		// Register activation hook
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Register deactivation hook
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Load core files
		add_action( 'plugins_loaded', array( $this, 'load_core' ) );

		// Register CP theme directory
		$this->register_cp_theme();

		/********************************************************
		 * LEGACY CALL
		 * Note*: WHILE DEVELOPMENT ONLY, REMOVE AFTERWARDS!
		********************************************************/
		// Run legacy
		//$legacy_key = 'coursepress_legacy_beta_c6';
		//add_action( $legacy_key, array( 'CoursePress_Legacy', 'instance' ) );

		//if ( ! wp_get_schedule( $legacy_key ) )
		//wp_schedule_single_event( time(), $legacy_key );
		/*********************************************************/
		/**
		 * Install new tables.
		 */
		$install = new CoursePress_Admin_Install( $this );
		$install->install_tables();
		/**
		 * upgrade site data
		 */
		$cp_db_version = get_option( 'coursepress_version', '0' );
		if ( 0 > version_compare( $cp_db_version, $this->version ) ) {
			if ( 0 > version_compare( $cp_db_version, '3.0.0' ) ) {
				update_option( 'coursepress_upgrade', 'need to be upgraded' );
			}
			add_action( 'init', array( $this, 'upgrade_flush_rewrite_rules' ) );
			update_option( 'coursepress_version', $this->version );
		}
	}

	/**
	 * Do not use this so often! This should be use onlt when we upgrade the
	 * plugin.
	 *
	 * @since 3.0.0
	 */
	public function upgrade_flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	private function class_loader( $class_name ) {
		if ( ! preg_match( '%CoursePress_%', $class_name ) ) {
			return false;
		}

		$class = explode( '_', strtolower( str_replace( 'CoursePress_', '', $class_name ) ) );
		array_unshift( $class, 'inc' );
		$file = array_pop( $class );
		array_push( $class, 'class-' . $file );

		$filename = implode( DIRECTORY_SEPARATOR, $class );

		try {
			$file = $this->plugin_path . $filename . '.php';

			if ( file_exists( $file ) && is_readable( $file ) ) {
				require_once $file;
			}
		} catch ( Exception $e ) {
			// @todo: Log error?
		}
	}

	function get_class( $class_name ) {
		if ( ! isset( $GLOBALS[ $class_name ] ) ) {
			$GLOBALS[ $class_name ] = new $class_name();
		}
		return $GLOBALS[ $class_name ];
	}

	public function activate() {
		$install = new CoursePress_Admin_Install( $this );
		$install->install();
	}

	public function deactivate() {
	}

	function load_core() {
		// Load core classses
		array_map( array( $this, 'get_class' ), $this->core_classes );

		if ( is_admin() ) {
			coursepress_render( 'inc/admin/class-page' );
			array_map( array( $this, 'get_class' ), $this->core_admin_classes );

		} else {
			array_map( array( $this, 'get_class' ), $this->core_front_classes );
		}

		/**
		 * Trigger when all CP classes are loaded.
		 *
		 * @since 2.0
		 */
		do_action( 'coursepress_initialized' );

		add_action( 'init', array( $this, 'set_current_user' ) );

		// We speak languages!
		load_plugin_textdomain(
			'cp', // Text domain.
			false, // Deprecated. Set to false.
			$this->plugin_path. '/languages'
		);
		/**
		 * need to be upgraded?
		 */
		if ( is_admin() ) {
			new CoursePress_Admin_Upgrade( $this );
		}
	}

	function set_current_user() {
		global $CoursePress_User;
		$CoursePress_User = new CoursePress_User( get_current_user_id() );
	}

	function register_cp_theme() {
		$theme_directories = apply_filters( 'coursepress_theme_directory_array', array(
				$this->plugin_path . 'themes'
			)
		);
		foreach ( $theme_directories as $theme_directory ) {
			register_theme_directory( $theme_directory );
		}
	}
}

global $CoursePress;
$CoursePress = new CoursePress();
