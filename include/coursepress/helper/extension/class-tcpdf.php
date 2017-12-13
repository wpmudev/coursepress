<?php

class CoursePress_Helper_Extension_TCPDF {

	private static $base_path = array(
		'free' => 'tcpdf/tcpdf.php',
	);

	public static function init() {
		//add_filter( 'coursepress_extensions_plugins', array( __CLASS__, 'add_to_extensions_list' ) );
	}

	public static function add_to_extensions_list( $plugins ) {

		$plugins[] = array(
			'name' => 'TCPDF Library',
			'slug' => 'CP_TCPDF',
			'base_path' => self::$base_path['free'],
			'source' => 'downloads.wordpress.org/plugin/tcpdf.zip',
			'source_message' => __( 'Complete TCPDF Library with additional fonts (WordPress.org)', 'CP_TD' ),
			'external_url' => 'https://wordpress.org/plugins/tcpdf/',
			'external' => 'yes',
			'protocol' => 'https',
		);

		return $plugins;
	}

	public static function installed_scope() {

		$scope = '';
		foreach ( self::$base_path as $key => $path ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $path;
			$plugin_mu_dir = WP_CONTENT_DIR . '/mu-plugins/' . $path;
			$location = file_exists( $plugin_dir ) ? trailingslashit( WP_PLUGIN_DIR ) : ( file_exists( $plugin_mu_dir ) ?  WP_CONTENT_DIR . '/mu-plugins/' : '' ) ;
			$scope = ! empty( $location ) ? $key : $scope;
		}

		return $scope;
	}

	public static function installed() {

		$scope = self::installed_scope();
		return ! empty( $scope );

	}

	public static function activated() {

		$scope = self::installed_scope();

		require_once ABSPATH . 'wp-admin/includes/plugin.php'; // Need for plugins_api.

		return ! empty( $scope ) ? is_plugin_active( self::$base_path[ $scope ] ) : false;
	}
}
