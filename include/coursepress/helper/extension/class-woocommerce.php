<?php

class CoursePress_Helper_Extension_WooCommerce {

	private static $installed = false;

	private static $activated = false;

	private static $base_path = array(
		'woocommerce' => 'woocommerce/woocommerce.php',
	);

	public static function init() {
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
