<?php

class CoursePress_Helper_Integration {


	private static $plugins = array();

	public static function init() {

		self::$plugins[] = 'MarketPress';

		// Bring in other integrations that hook 'coursepress_extensions_plugins'
		self::$plugins = self::get_plugins();

		foreach ( self::$plugins as $plugin ) {

			// Hooks for other devs to add their own integrations
			$plugin_class = apply_filters( 'coursepress_integration_plugin_class', 'CoursePress_Helper_Integration_' . $plugin, $plugin );
			$plugin_init  = apply_filters( 'coursepress_integration_plugin_init', 'init', $plugin );

			if ( method_exists( $plugin_class, $plugin_init ) ) {
				call_user_func( $plugin_class . '::' . $plugin_init );
			}
		}

		// Other Integrations

		// Add TCPDF
		add_filter( 'coursepress_class_loader_namespaces', array( __CLASS__, 'add_tcpf' ) );


	}

	private static function get_plugins() {
		return apply_filters( 'coursepress_extensions_plugins', self::$plugins );
	}

	public static function add_tcpf( $namespaces ) {

		$namespaces['TCPDF'] = array(
			'namespace_folder' => true,
			'overrides' => array(
				'TCPDF.php' => 'tcpdf.php'
			)
		);

		return $namespaces;
	}

}