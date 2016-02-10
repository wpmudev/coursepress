<?php
/**
 * Plugin Name: CoursePress Pro
 * Version:     2.0.0
 * Description: CoursePress Pro turns WordPress into a powerful online learning platform. Set up online courses by creating learning units with quiz elements, video, audio etc. You can also assess student work, sell your courses and much much more.
 * Author:      WPMU DEV
 * Author URI:  http://premium.wpmudev.org
 * Plugin URI:  http://premium.wpmudev.org/project/coursepress/
 * Developers:  Marko Miljus ( https://twitter.com/markomiljus ), Rheinard Korf ( https://twitter.com/rheinardkorf )
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * TextDomain:  cp
 * Domain Path: /languages/
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Launch CoursePress.
CoursePress::init();

/**
 * Main plugin class. Main purpose is to load all required files.
 */
class CoursePress {

	/**
	 * Current plugin version, must match the version in the header comment.
	 * @var string
	 */
	public static $version = '2.0.0';

	/**
	 * Plugin name, this reflects the Pro/Standard version.
	 * @var string
	 */
	public static $name = 'CoursePress';

	/**
	 * Folder that contains all plugin files.
	 * @deprecated This makes stuff _VERY_ confusing, this dir should not exist.
	 * @var  string
	 */
	public static $plugin_lib = 'coursepress-files';

	/**
	 * Textdomain.
	 * @deprecated We should use plain string for textdomain, no variables!
	 */
	const TD = 'cp';

	/**
	 * Initialize the plugin!
	 * @since  2.0.0
	 */
	public static function init() {
		// Initialise the autoloader.
		spl_autoload_register( array( __CLASS__, 'class_loader' ) );

		// Prepare CoursePress Core parameters.
		CoursePress_Core::$name            = self::$name;
		CoursePress_Core::$version         = self::$version;
		CoursePress_Core::$plugin_lib      = self::$plugin_lib;
		CoursePress_Core::$plugin_file     = __FILE__;
		CoursePress_Core::$plugin_path     = trailingslashit( plugin_dir_path( __FILE__ ) );
		CoursePress_Core::$plugin_url      = trailingslashit( plugin_dir_url( __FILE__ ) );
		CoursePress_Core::$plugin_lib_path = trailingslashit( CoursePress_Core::$plugin_path . self::$plugin_lib );
		CoursePress_Core::$plugin_lib_url  = trailingslashit( CoursePress_Core::$plugin_url . self::$plugin_lib );
		CoursePress_Core::$DEBUG           = false;  // @todo check if this should be a define( '' ) option...

		CoursePress_Core::init();

		$screen_base = str_replace( ' ', '-', strtolower( CoursePress_Core::$name ) );
		$page_base = $screen_base . '_page_';

		global $wpmudev_notices;

		$wpmudev_notices[] = array(
			'id' => 913071,
			'name' => CoursePress_Core::$name,
			'screens' => array(
				'coursepress_settings',
				'toplevel_page_courses',
				'toplevel_page_coursepress',
				$page_base . 'coursepress_settings',
				$page_base . 'coursepress_course',
			),
		);

		/**
		 * Include WPMUDev Dashboard.
		 */
		include_once CoursePress_Core::$plugin_path . 'includes/external/dashboard/wpmudev-dash-notification.php';
	}

	/**
	 * Handler for spl_autoload_register (autoload classes on demand).
	 *
	 * @since  2.0.0
	 * @param  string $class Class name.
	 * @return bool True if the class-file was found and loaded.
	 */
	private static function class_loader( $class ) {
		$namespaces = apply_filters( 'coursepress_class_loader_namespaces', array(
			'CoursePress' => false,
		) );

		$basedir = trailingslashit( dirname( __FILE__ ) ) . self::$plugin_lib;
		$class   = trim( $class );

		foreach ( $namespaces as $namespace => $options ) {
			if ( preg_match( '/^' . $namespace . '/', $class ) ) {

				$namespace_folder = isset( $options['namespace_folder'] ) && true === $options['namespace_folder'] ? $namespace . '/' : '';

				$filename = $basedir . '/lib/' . $namespace_folder . str_replace( '_', DIRECTORY_SEPARATOR, $class ) . '.php';

				// Override filename via array.
				if ( isset( $options['overrides'] ) && is_array( $options['overrides'] ) ) {

					$file = explode( DIRECTORY_SEPARATOR, $filename );
					$file_base = array_pop( $file );

					if ( array_key_exists( $file_base, $options['overrides'] ) ) {
						$file[] = $options['overrides'][ $file_base ];
						$filename = implode( DIRECTORY_SEPARATOR, $file );
					}
				}

				// Override filename via filter.
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
