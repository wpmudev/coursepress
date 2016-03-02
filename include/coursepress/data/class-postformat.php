<?php

class CoursePress_Data_PostFormat {

	/**
	 * Holds a list with all custom post-types that were registered by the
	 * plugin.
	 *
	 * @var array
	 */
	private static $registered_formats = array();

	/**
	 * Holds a list of all custom taxonomies that were registered by the plugin.
	 *
	 * @var array
	 */
	private static $registered_taxonomies = array();

	/**
	 * A custom prefix for the post-type slug.
	 * The prefix can be defined via the constant COURSEPRESS_CPT_PREFIX in
	 * wp-config.php. This prefix should not end with an underscore!
	 *
	 * @var string
	 */
	private static $prefix = null;

	/**
	 * Hook up this module.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_action(
			'init',
			array( __CLASS__, 'register_post_types' )
		);
	}

	/**
	 * Register all custom post-types and taxonomies that come with this plugin.
	 *
	 * @since  2.0.0
	 */
	public static function register_post_types() {
		// Add the formats array in CoursePress_Core
		$classes = apply_filters( 'coursepress_post_formats', array() );

		foreach ( $classes as $the_class ) {
			// Register post-types.
			if ( method_exists( $the_class, 'get_format' ) ) {
				$format = call_user_func( $the_class . '::get_format' );
				if ( ! $format ) { continue; }

				$pt_slug = self::prefix( $format['post_type'] );
				self::$registered_formats[] = $pt_slug;

				register_post_type(
					$pt_slug,
					$format['post_args']
				);
			}

			// Register taxonomies.
			if ( method_exists( $the_class, 'get_taxonomy' ) ) {
				$format = call_user_func( $the_class . '::get_taxonomy' );
				if ( ! $format ) { continue; }

				$tx_slug = self::prefix( $format['taxonomy_type'] );
				self::$registered_taxonomies[] = $tx_slug;

				register_taxonomy(
					$tx_slug,
					$format['post_type'],
					$format['taxonomy_args']
				);
			}
		}
	}

	/**
	 * Returns a list of all custom post-type slugs that were registered.
	 * Available after init action.
	 *
	 * @since  1.0.0
	 * @return array List of post-type slugs.
	 */
	public static function registered_formats() {
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong(
				'registered_formats',
				'Function is called too early, it is available after "init" action.',
				'2.0.0'
			);
		}

		return self::$registered_formats;
	}

	/**
	 * Returns a string with the prefixed post-type.
	 *
	 * @since  2.0.0
	 * @param  string $post_type The post-type slug.
	 * @return string Post-type slug with custom prefix.
	 */
	public static function prefix( $post_type = '' ) {
		if ( null === self::$prefix ) {
			self::$prefix = '';

			/**
			 * Override post type prefixes with COURSEPRESS_CPT_PREFIX defined
			 * in wp-config.php.
			 *
			 * Use this to help with conflicts.
			 * Only define the prefix, without trailing underscore!
			 *   Example:
			 *   define( 'COURSEPRESS_CPT_PREFIX', 'my' )  // GOOD :)
			 *   define( 'COURSEPRESS_CPT_PREFIX', 'my_' ) // BAD!
			 */
			if ( defined( 'COURSEPRESS_CPT_PREFIX' ) ) {
				self::$prefix = strtolower(
					sanitize_html_class( COURSEPRESS_CPT_PREFIX ) . '_'
				);
			}
		}

		return self::$prefix . $post_type;
	}
}
