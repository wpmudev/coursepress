<?php
/**
 * Helper functions.
 * Integrate other plugins with CoursePress.
 *
 * @package  CoursePress
 */

/**
 * Initialize all available integrations.
 */
class CoursePress_Helper_Integration {

	/**
	 * Internal list of available Integrations.
	 *
	 * @var array
	 */
	private static $plugins = null;

	/**
	 * Initialize all registered Integrations.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		$plugins = self::get_plugins();

		foreach ( $plugins as $data ) {
			$class = $data['class'];
			$method = $data['method'];

			if ( method_exists( $class, $method ) ) {
				call_user_func( $class . '::' . $method );
			}
		}
	}

	/**
	 * Returns a list of all known integrations.
	 *
	 * @since  1.0.0
	 * @return array List of plugin integrations to load.
	 */
	public static function get_plugins() {
		if ( null === self::$plugins ) {
			self::$plugins = array();
			self::$plugins['marketpress'] = array(
				'class' => 'CoursePress_Helper_Integration_MarketPress',
				'method' => 'init',
			);

			$use_woo = CoursePress_Core::get_setting( 'woocommerce/use' );
			if ( $use_woo ) {
				self::$plugins['woo'] = array(
					'class' => 'CoursePress_Helper_Integration_WooCommerce',
					'method' => 'init',
				);
			}

			/**
			 * This filter can be used to register or replace default plugin
			 * integrations.
			 *
			 * @since 2.0.0
			 * @var array $plugins. Must have value for 'class' and 'method'.
			 */
			self::$plugins = apply_filters(
				'coursepress_extensions_plugins',
				self::$plugins
			);
		}

		return self::$plugins;
	}
}
