<?php
/**
 * Plugin Name: CoursePress Base
 * Version:     2.0.0
 * Description: CoursePress Base turns WordPress into a powerful online learning platform. Set up online courses by creating learning units with quiz elements, video, audio etc. You can also assess student work, sell your courses and much much more.
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

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
	public static $name = 'CoursePress Base'; // Translated by grunt.

	/**
	 * Absolut path to this file (main plugin file).
	 *
	 * @var string
	 */
	public static $file = '';

	/**
	 * File-root of the premium files.
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
		self::$file = __FILE__;
		self::$path = plugin_dir_path( __FILE__ );
		self::$dir = dirname( self::$path );
		self::$url = plugin_dir_url( __FILE__ );

		/** @deprecated Because $plugin_lib will be removed. */
		CoursePress_Core::$plugin_lib_path = trailingslashit( self::$path . self::$plugin_lib );
		CoursePress_Core::$plugin_lib_url = trailingslashit( self::$url . self::$plugin_lib );

		// Allow WP to load other plugins before we continue!
		add_action( 'plugins_loaded', array( 'CoursePress_Core', 'init' ) );

		// Load additional features if available.
		if ( file_exists( self::$path . self::$plugin_lib . '/premium/init.php' ) ) {
			include_once self::$path . self::$plugin_lib . '/premium/init.php';
		}

		if ( file_exists( self::$path . self::$plugin_lib . '/campus/init.php' ) ) {
			include_once self::$path . self::$plugin_lib . '/campus/init.php';
		}
	}

	/**
	 * Handler for spl_autoload_register (autoload classes on demand).
	 *
	 * Note how the folder structure is build:
	 *   plugin_lib + namespace + 'lib' + classpath
	 *   classpath = class name, while each _ is actually a subfolder separator.
	 *
	 *   @todo  simplify this! should be simply <'lib' + classpath>
	 *          (reason: classpath is already prefixed with namespace!)
	 *
	 * @since  2.0.0
	 * @param  string $class Class name.
	 * @return bool True if the class-file was found and loaded.
	 */
	private static function class_loader( $class ) {
		$namespaces = apply_filters(
			'coursepress_class_loader_namespaces',
			array(
				'CoursePress' => array(),
			)
		);

		$class = trim( $class );

		foreach ( $namespaces as $namespace => $options ) {
			// Continue if the class name is prefixed with <namespace>.
			if ( substr( $namespace, 0, strlen( $class ) ) === $namespace ) {

				$namespace_folder = 'lib';
				$overrides = array();

				if ( ! empty( $options['namespace_folder'] ) ) {
					/**
					 * Search for class file in a subfolder?
					 *
					 * Note: When using this, note that folder name must match
					 * upper/lowecase of namespace name!
					 *
					 * @todo  Find out if/where this is used. Drop this is possible!
					 *
					 * @param namespace_folder
					 * @var   bool
					 */
					$namespace_folder .= DIRECTORY_SEPARATOR . $namespace;
				}

				if ( ! empty( $options['overrides'] ) ) {
					/**
					 * Define custom class file paths for special classes.
					 *
					 * @param overrides
					 * @var   array. Key is class name, value is file name.
					 */
					$overrides = (array) $options['overrides'];
				}

				$class_folder = join(
					DIRECTORY_SEPARATOR,
					array(
						dirname( __FILE__ ),
						self::$plugin_lib,
						$namespace_folder,
					)
				);
				$class_file = str_replace( '_', DIRECTORY_SEPARATOR, $class ) . '.php';

				// Override filename via array.
				if ( isset( $overrides[ $class_file ] ) ) {
					$class_file = $overrides[ $class_file ];
				}

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

		return false;
	}
}
