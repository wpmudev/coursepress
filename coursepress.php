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
 * Text Domain: cp
 * Domain Path: /languages
 * Build Time:  BUILDTIME
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

define( 'COURSEPRESS_UPGRADE', true );

class CoursePressUpgrade {
	/** @var (boolean) Whether all courses are upgraded to the new version. **/
	private static $coursepress_is_upgraded = false;

	public static $coursepress_version;

	public static function init() {
		self::$coursepress_is_upgraded = get_option( 'coursepress_20_upgraded', false );
		$coursepress_version = false === self::$coursepress_is_upgraded ? '1.x' : '2.0';

		if ( '1.x' == $coursepress_version ) {
			// Check for existing courses, maybe a new install?
			$is_using_old = self::check_old_courses();

			if ( false == $is_using_old ) {
				// No need to upgrade, load 2.0
				$coursepress_version = '2.0';
			} else {
				// Include the upgrade class
				$upgrade_class = dirname( __FILE__ ) . '/upgrade/class-upgrade.php';

				if ( is_readable( $upgrade_class ) ) {
					require $upgrade_class;

					CoursePress_Upgrade_1x_Data::init();

					add_action( 'plugins_loaded', array( __CLASS__, 'coursepress_theme' ) );
					add_action( 'maybe_run_coursepress_theme_once', array( __CLASS__, 'run_coursepress_theme' ) );

					// Run theme check once
					wp_schedule_single_event( time(), 'maybe_run_coursepress_theme_once' );

					if ( ! is_admin() ) {
						self::get_coursepress( '2.0' );
					}
				}
			}
		}

		/**
		 * Retrieve the current coursepress version use.
		 **/
		self::$coursepress_version = $coursepress_version;
		if ( '2.0' == $coursepress_version ) {
			self::get_coursepress( $coursepress_version );
		}

		/**
		 * Set activation hook
		 **/
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );

		/**
		 * Set deactivation hook
		 **/
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );

		/**
		 * load translations
		 */
		add_action('plugins_loaded', array( __CLASS__, 'load_l10n' ) );
	}

	/** Use to reset CP into 1.x version */
	private static function reset() {
		delete_option( 'cp1_flushed' );
		delete_option( 'coursepress_20_upgraded' );
		delete_option( 'cp2_flushed' );
		delete_option( 'coursepress_settings' );
		$args = array(
			'post_type' => 'course',
			'post_status' => 'any',
			'fields' => 'ids',
			'suppress_filters' => true,
			'posts_per_page' => -1,
		);
		$courses = get_posts( $args );

		foreach ( $courses as $course_id ) {
			delete_post_meta( $course_id, '_cp_updated_to_version_2' );
			delete_post_meta( $course_id, 'course_settings' );
		}
	}

	/** Check if current courses contains un-upgraded to the current version. **/
	public static function check_old_courses() {
		$args = array(
			'post_type' => 'course',
			'post_status' => 'any',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_key' => 'course_settings',
			'meta_compare' => 'NOT EXISTS',
			'suppress_filters' => true,
		);
		$courses = get_posts( $args );

		return count( $courses ) > 0 || intval(get_option('students_to_upgrade_to_2.0', 0)) > 0;
	}

	private static function get_coursepress( $version ) {
		$dir = dirname( __FILE__ );
		$version_file = $dir . '/' . $version . '/coursepress.php';

		if ( is_readable( $version_file ) ) {
			if ( '1.x' == $version ) {
				// Hooked to 1.x
				add_action( 'coursepress_before_init_vars', array( __CLASS__, 'before_init_vars' ), 10 );
				add_action( 'coursepress_init_vars', array( __CLASS__, 'init_vars' ) );
				// Flush the rewrite rules
				// @note: While development only: must be removed
				add_action( 'init', array( __CLASS__, 'cp1_flush_rewrite_rules' ) );
			} else {
				// Flush rewrite rules
				//@note: While devevelopment only, must be removed:
				add_action( 'init', array( __CLASS__, 'cp2_flush_rewrite_rules' ) );
			}

			include $version_file;
		} else {
			$error = sprintf( __( 'Error loading %s v%s plugin!', 'cp' ), 'CoursePress', $version );
			throw new Exception( $error );
		}
	}

	/**
	 * Set CP 1.x directory name
	 **/
	public static function before_init_vars( $instance ) {
		$instance->dir_name = 'coursepress/1.x';
	}

	public static function init_vars( $instance ) {
		$instance->location = 'plugins';
		$instance->plugin_dir = WP_PLUGIN_DIR . '/coursepress/1.x/';
		$instance->plugin_url = WP_PLUGIN_URL . '/coursepress/1.x/';
	}

	public static function coursepress_theme() {
		$current_theme = wp_get_theme();

		register_theme_directory( dirname( __FILE__ ) . '/2.0/themes' );

		if ( 'coursepress' == $current_theme->get_stylesheet() ) {
			add_filter( 'stylesheet_directory_uri', array( __CLASS__, 'theme_directory' ) );
			add_filter( 'theme_root', array( __CLASS__, 'theme_root' ) );
			add_filter( 'template_directory_uri', array( __CLASS__, 'theme_directory_uri' ) );
		}
	}

	static function run_coursepress_theme() {
		$current_theme = wp_get_theme();

		if ( 'coursepress' == $current_theme->get_stylesheet() ) {
			register_theme_directory( dirname( __FILE__ ) . '/2.0/themes' );
			wp_clean_themes_cache( true );
			add_filter( 'stylesheet_directory_uri', array( __CLASS__, 'theme_directory' ) );
			add_filter( 'theme_root', array( __CLASS__, 'theme_root' ) );
			add_filter( 'template_directory_uri', array( __CLASS__, 'theme_directory_uri' ) );
			switch_theme( $current_theme->get_stylesheet() );
			flush_rewrite_rules();
		}
	}

	public static function theme_directory() {
		return plugins_url( 'coursepress/2.0/themes/coursepress' );
	}

	public static function theme_root() {
		return __DIR__ . '/2.0/themes';
	}

	public static function theme_directory_uri() {
		return plugins_url( 'coursepress/2.0/themes/coursepress' );
	}

	public static function maybe_switch_theme() {
		$current_theme = wp_get_theme();

		if ( 'coursepress' == $current_theme->get_stylesheet() ) {
			wp_clean_themes_cache( true );
			switch_theme( $current_theme->get_stylesheet() );
		}
	}

	public static function cp1_flush_rewrite_rules() {
		$is_flushed = get_option( 'cp1_flushed', false );

		if ( false == $is_flushed ) {
			delete_option( 'cp2_flushed' );
			update_option( 'cp1_flushed', true );
			cp_flush_rewrite_rules();

			add_action( 'admin_init', array( __CLASS__, 'maybe_switch_theme' ) );
		}
	}

	public static function cp2_flush_rewrite_rules() {
		$is_flushed = get_option( 'cp2_flushed', false );

		if ( false == $is_flushed ) {
			delete_option( 'cp1_flushed' );

			if ( class_exists( 'CoursePress_Upgrade' ) )
				CoursePress_Upgrade::init();

			//@todo: wrap this
			flush_rewrite_rules();

			add_action( 'admin_init', array( __CLASS__, 'maybe_switch_theme' ) );
			update_option( 'cp2_flushed', true );
		}
	}

	/**
	 * Helper function to set activation hook for verion 2.x
	 **/
	static function activate() {
		if ( method_exists( 'CoursePress', 'register_activation_hook' ) ) {
			CoursePress::register_activation_hook();
		}
	}

	/**
	 * Helper function to set deactivation hook for version 2.x
	 **/
	static function deactivate() {
		if ( method_exists( 'CoursePress', 'deactivate_coursepress' ) ) {
			CoursePress::deactivate_coursepress();
		}
	}

	/**
	 * load translations
	 *
	 * @since 2.1.2
	 */
	static function load_l10n() {
		$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
		load_plugin_textdomain( 'cp', false, $plugin_rel_path );
	}
}
CoursePressUpgrade::init();
