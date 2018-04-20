<?php

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
	public static $version = '2.1.6';

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
		/**
		 * Translate plugin name
		 */
		self::$name = _x( 'CoursePress Pro', 'plugin name', 'coursepress' ); // Translated by grunt.
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
		 * update plugin if needed
		 */
		$db_version = get_site_option( 'coursepress_version', '0.0.0' );
		if ( 0 > version_compare( $db_version, self::$version ) ) {
			update_site_option( 'coursepress_version', self::$version );
		}

		/**
		 * Add sample courses when CP is activated.
		 **/
		add_action( 'coursepress_activate', array( 'CoursePress_Admin_SampleCourses', 'add_sample_courses' ) );

		/**
		register_activation_hook * register_activation_hook
		 */
		register_activation_hook( __FILE__, array( __CLASS__, 'register_activation_hook' ) );

		/**
		 * Clean up when this plugin is deactivated.
		 **/
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate_coursepress' ) );

		// Define custom theme directory for CoursePress theme
		self::register_cp_theme_directory();
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
			'CoursePressPro'    => array(
				'namespace_folder' => 'premium/include', // Base folder for classes.
				'filename_prefix' => 'class-',           // Prefix filenames.
			),
			'CoursePressCampus' => array(
				'namespace_folder' => 'campus/include', // Base folder for classes.
				'filename_prefix' => 'class-',          // Prefix filenames.
			),
			'CoursePress'       => array(
				'namespace_folder' => 'include/coursepress', // Base folder for classes.
				'filename_prefix' => 'class-',               // Prefix filenames.
			),
			'CP_TCPDF'          => array(
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
				$class_folder = self::$path . strtolower(
					implode( DIRECTORY_SEPARATOR, $class_path )
				);
				$dir_folder = dirname( __FILE__ ) . strtolower(
					DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $class_path )
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
				} elseif ( is_readable( $dir_folder ) ) {
					include_once $dir_folder . DIRECTORY_SEPARATOR . $class_file;
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

		/**
		 * Fire whenever this plugin is activated.
		 *
		 * @since 2.0.7
		 **/
		do_action( 'coursepress_activate' );
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

		/**
		 * Fire whenever this plugin is deactivated.
		 *
		 * @since 2.0.7
		 **/
		do_action( 'coursepress_deactivate' );
	}

	/**
	 * Registering CP Theme
	 *
	 * @since 2.0.0
	 **/
	private static function register_cp_theme_directory() {
		$theme_directories = apply_filters( 'coursepress_theme_directory_array', array(
				self::$path . 'themes/'
			)
		);
		foreach ( $theme_directories as $theme_directory ) {
			register_theme_directory( $theme_directory );
		}
	}

	public static function get_file() {
		return self::$file;
	}
}
