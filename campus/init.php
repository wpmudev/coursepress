<?php
/**
 * Initialize the CampusPress functions of the CoursePress plugin.
 * File is included directly from main plugin file.
 *
 * @see coursepress.php
 * @package CoursePressCampus
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Launch CoursePressCampus.
CoursePressCampus::init();

/**
 * Main entry to hook up and initialize CampusPress features.
 *
 * @since 2.0.0
 */
class CoursePressCampus {

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
	 * Initialize the CampusPress features!
	 * This function runs before the `plugins_loaded` action.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		define( 'CP_IS_CAPUS', true );

		// Overwrite settings for CampusPress files.
		self::$file = __FILE__;
		self::$path = plugin_dir_path( __FILE__ );
		self::$dir = dirname( self::$path );
		self::$url = plugin_dir_url( __FILE__ );

		// And here comes the actual CampusPress code! Yay :)
		add_action(
			'plugins_loaded',
			array( 'CoursePressCampus_Core', 'init' ),
			11
		);
	}
}
