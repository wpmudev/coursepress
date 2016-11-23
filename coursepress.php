<?php
/**
 * Plugin Name: CoursePress Base
 * Version:     2.0.0
 * Description: CoursePress Pro turns WordPress into a powerful online learning platform. Set up online courses by creating learning units with quiz elements, video, audio etc. You can also assess student work, sell your courses and much much more.
 * Author:      WPMU DEV
 * Author URI:  http://premium.wpmudev.org
 * Plugin URI:  http://premium.wpmudev.org/project/coursepress/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * TextDomain:  cp
 * Domain Path: /language/
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

class CoursePressUpgrade {
	/** @var (boolean) Whether all courses are upgraded to the new version. **/
	private static $coursepress_is_upgraded = false;

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

					CoursePress_Upgrade::init();
				}
			}
		}

		/**
		 * Retrieve the current coursepress version use.
		 **/
		self::get_coursepress( $coursepress_version );
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

		return count( $courses ) > 0;
	}

	private static function get_coursepress( $version ) {
		$dir = dirname( __FILE__ );
		$version_file = $dir . '/' . $version . '/coursepress.php';

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

		if ( is_readable( $version_file ) ) {
			include $version_file;
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

			/** Update 2.0 Settings **/
			CoursePress_Upgrade::init();

			/** Check users to update **/
			$users_to_update = get_option( 'cp2_users_to_update', array() );

			if ( ! empty( $users_to_update ) ) {
				foreach ( $users_to_update as $course_id => $users ) {
					foreach ( $users as $user_id ) {
						CoursePress_Data_Student::get_calculated_completion_data( $user_id, $course_id );
					}
					unset( $users_to_update[ $course_id ] );
				}
				if ( ! empty( $users_to_update ) ) {
					update_option( 'cp2_users_to_update', $users_to_update );
				} else {
					delete_option( 'cp2_users_to_update' );
				}
			}

			//@todo: wrap this
			flush_rewrite_rules();

			add_action( 'admin_init', array( __CLASS__, 'maybe_switch_theme' ) );
			update_option( 'cp2_flushed', true );
		}
	}
}
CoursePressUpgrade::init();
