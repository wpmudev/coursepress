<?php
/*
Plugin Name: CoursePress Pro
Version: 2.0
Description: CoursePress Pro turns WordPress into a powerful online learning platform. Set up online courses by creating learning units with quiz elements, video, audio etc. You can also assess student work, sell your courses and much much more.
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
Plugin URI: http://premium.wpmudev.org/project/coursepress/
Developers: Marko Miljus ( https://twitter.com/markomiljus ), Rheinard Korf ( https://twitter.com/rheinardkorf )
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
TextDomain: cp
Domain Path: /languages/
WDP ID: 913071
*/

/**
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
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


// Launch CoursePress
CoursePress::init();

class CoursePress {

	public static $plugin_lib = 'coursepress-files';
	const TD = 'cp';

	public static function init() {

		// Initialise the autoloader
		spl_autoload_register( array( get_class(), 'class_loader' ) );

		/** Prepare CoursePress Core */
		// Get plugin details from Header
		$default_headers = array( 'name' => 'Plugin Name', 'version' => 'Version', 'td' => 'TextDomain' );
		$default_headers = get_file_data( __FILE__, $default_headers, 'plugin' );

		CoursePress_Core::$name            = $default_headers['name'];
		CoursePress_Core::$version         = $default_headers['version'];
		CoursePress_Core::$plugin_lib      = self::$plugin_lib;
		CoursePress_Core::$plugin_file     = __FILE__;
		CoursePress_Core::$plugin_path     = plugin_dir_path( __FILE__ );
		CoursePress_Core::$plugin_url      = plugin_dir_url( __FILE__ );
		CoursePress_Core::$plugin_lib_path = trailingslashit( CoursePress_Core::$plugin_path ) . trailingslashit( CoursePress_Core::$plugin_lib );
		CoursePress_Core::$plugin_lib_url  = trailingslashit( CoursePress_Core::$plugin_url ) . trailingslashit( CoursePress_Core::$plugin_lib );
		CoursePress_Core::$DEBUG           = false;

		CoursePress_Core::init();

		$screen_base = str_replace( ' ', '-', strtolower( CoursePress_Core::$name ) );
		$page_base = $screen_base . '_page_';

		global $wpmudev_notices;

		$wpmudev_notices[] = array(
			'id'		 => 913071,
			'name'		 => CoursePress_Core::$name,
			'screens'	 => array(
				'coursepress_settings',
				'toplevel_page_courses',
				'toplevel_page_coursepress',
				$page_base . 'coursepress_settings',
				$page_base . 'coursepress_course',
				//$screen_base . '_page_coursepress_settings',
				//$screen_base . '_page_course_details',
				//$screen_base . '_page_instructors',
				//$screen_base . '_page_students',
				//$screen_base . '_page_assessment',
				//$screen_base . '_page_reports',
				//$screen_base . '_page_notifications',
				//$screen_base . '_page_settings'
			)
		);

		/**
		 * Include WPMUDev Dashboard.
		 */
		//include_once( CoursePress_Core::$plugin_path . 'includes/external/dashboard/wpmudev-dash-notification.php' );



	}

	private static function class_loader( $class ) {

		$namespaces = apply_filters( 'coursepress_class_loader_namespaces', array(
			'CoursePress'
		) );

		$basedir = trailingslashit( dirname( __FILE__ ) ) . self::$plugin_lib;
		$class   = trim( $class );

		foreach ( $namespaces as $namespace ) {
			if ( preg_match( '/^' . $namespace . '/', $class ) ) {
				$filename = $basedir . '/lib/' . str_replace( '_', DIRECTORY_SEPARATOR, $class ) . '.php';
				$filename = apply_filters( 'coursepress_class_file_override', $filename );
				if ( is_readable( $filename ) ) {
					include_once $filename;

					return true;
				}
			}
		}

		return false;
	}


}