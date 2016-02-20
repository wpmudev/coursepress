<?php

class CoursePress_Helper_Integration {

	private static $plugins = array();

	public static function init() {
		self::$plugins[] = 'MarketPress';

		// Bring in other integrations that hook 'coursepress_extensions_plugins'.
		self::$plugins = self::get_plugins();

		foreach ( self::$plugins as $plugin ) {

			// Hooks for other devs to add their own integrations.
			$plugin_class = apply_filters( 'coursepress_integration_plugin_class', 'CoursePress_Helper_Integration_' . $plugin, $plugin );
			$plugin_init = apply_filters( 'coursepress_integration_plugin_init', 'init', $plugin );

			if ( method_exists( $plugin_class, $plugin_init ) ) {
				call_user_func( $plugin_class . '::' . $plugin_init );
			}
		}
	}

	private static function get_plugins() {
		return apply_filters( 'coursepress_extensions_plugins', self::$plugins );
	}
}
