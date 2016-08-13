<?php
/**
 * Plugin Name: CoursePress Base
 * Version:     2.0.0-BETA3
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

// Launch CoursePress.
CoursePress::init();

/**
 * Main plugin class. Main purpose is to load all required files.
 */
class CoursePress {

	/**
	 * Current plugin version, must match the version in the header comment.
	 *
	 * @var string
	 */
	public static $version = '2.0.0-BETA3.1.1470896844';

	/**
	 * Plugin name, this reflects the Pro/Standard version.
	 *
	 * @var string
	 */
	public static $name = 'CoursePress Pro'; // Translated by grunt.

	/**
	 * Absolut path to this file (main plugin file).
	 *
	 * @var string
	 */
	public static $file = '';

	/**
	 * Absolut path to the plugin files base-dir.
	 *
	 * @var string
	 */
	public static $path = '';

	/**
	 * Dir-name of this plugin (relative to wp-content/plugins).
	 *
	 * @var string
	 */
	public static $dir = '';

	/**
	 * Absolute URL to plugin folder.
	 *
	 * @var string
	 */
	public static $url = '';

	/**
	 * Initialize the plugin!
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		// Initialise the autoloader.
		spl_autoload_register( array( __CLASS__, 'class_loader' ) );

		// Prepare CoursePress Core parameters.
		self::$file = __FILE__;
		self::$path = plugin_dir_path( __FILE__ );
		self::$dir = dirname( self::$path );
		self::$url = plugin_dir_url( __FILE__ );

		// Allow WP to load other plugins before we continue!
		add_action( 'plugins_loaded', array( 'CoursePress_Core', 'init' ), 10 );

		// Load additional features if available.
		if ( file_exists( self::$path . '/premium/init.php' ) ) {
			include_once self::$path . '/premium/init.php';
		}

		if ( file_exists( self::$path . '/campus/init.php' ) ) {
			include_once self::$path . '/campus/init.php';
		}

		/**
		register_activation_hook * register_activation_hook
		 */
		register_activation_hook( __FILE__, array( __CLASS__, 'register_activation_hook' ) );

		/**
		 * Clean up when this plugin is deactivated.
		 **/
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate_coursepress' ) );

	}

	/**
	 * Handler for spl_autoload_register (autoload classes on demand).
	 *
	 * Note how the folder structure is build:
	 *   'core' + namespace + classpath
	 *   classpath = class name, while each _ is actually a subfolder separator.
	 *
	 * @since  2.0.0
	 * @param  string $class Class name.
	 * @return bool True if the class-file was found and loaded.
	 */
	private static function class_loader( $class ) {
		$namespaces = array(
			'CoursePressPro' => array(
				'namespace_folder' => 'premium/include', // Base folder for classes.
				'filename_prefix' => 'class-',           // Prefix filenames.
			),
			'CoursePressCampus' => array(
				'namespace_folder' => 'campus/include', // Base folder for classes.
				'filename_prefix' => 'class-',          // Prefix filenames.
			),
			'CoursePress' => array(
				'namespace_folder' => 'include/coursepress', // Base folder for classes.
				'filename_prefix' => 'class-',               // Prefix filenames.
			),
			'TCPDF' => array(
				'namespace_folder' => 'include/tcpdf', // Base folder for classes.
				'filename_prefix' => false,            // No prefix for filenames.
			),
		);

		$class = trim( $class );

		foreach ( $namespaces as $namespace => $options ) {
			// Continue if the class name is prefixed with <namespace>.
			if ( substr( $class, 0, strlen( $namespace ) ) === $namespace ) {

				if ( empty( $options['namespace_folder'] ) ) {
					continue;
				} else {
					$namespace_folder = $options['namespace_folder'];
				}

				// Get the class-filename.
				$class_path = explode( '_', $class );
				$class_file = strtolower( array_pop( $class_path ) ) . '.php';

				if ( ! empty( $options['filename_prefix'] ) ) {
					$class_file = $options['filename_prefix'] . $class_file;
				}

				// Build the path to the class file.
				array_shift( $class_path ); // Remove the first element (namespace-string).
				array_unshift( $class_path, $namespace_folder );
				$class_folder = strtolower(
					self::$path . implode( DIRECTORY_SEPARATOR, $class_path )
				);

				$filename = $class_folder . DIRECTORY_SEPARATOR . $class_file;

				// Override filename via filter.
				$filename = apply_filters(
					'coursepress_class_file_override',
					$filename,
					$class_folder,
					$class_file,
					$class,
					$namespace
				);

				if ( is_readable( $filename ) ) {
					include_once $filename;
					return true;
				}
			} // End of namespace condition.
		} // End of foreach loop.

		// Check new location
		$class_path = explode( '_', strtolower( $class ) );
		$namespace = array_shift( $class_path );

		if ( 'coursepress' == $namespace ) {
			$class_filename = array_pop( $class_path );
			$class_location = implode( DIRECTORY_SEPARATOR, $class_path );
			$class_filename = self::$path . $class_location . DIRECTORY_SEPARATOR . 'class-' . $class_filename . '.php';

			if ( is_readable( $class_filename ) ) {
				include_once $class_filename;
				return true;
			}
		}
	}

	/**
	 * Redirect to Guide page semaphore and reset schedule.
	 *
	 * @since 2.0.0
	 */
	public static function register_activation_hook() {
		add_option( 'coursepress_activate', true );

		// Reset the schedule during activation.
		wp_clear_scheduled_hook( 'coursepress_schedule-email_task' );
	}

	/**
	 * Clean up.
	 *
	 * @since 2.0.0
	 **/
	public static function deactivate_coursepress() {
		delete_option( 'coursepress_activate' );

		// Reset the schedule during deactivation.
		wp_clear_scheduled_hook( 'coursepress_schedule-email_task' );
	}
}
