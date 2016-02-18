<?php

class CoursePress_Helper_Extensions_MarketPress {

	private static $installed = false;

	private static $activated = false;

	private static $base_path = array(
		'pro' => 'marketpress/marketpress.php',
		'free' => 'wordpress-ecommerce/marketpress.php',
	);

	public static function init() {

		if ( CoursePress_Model_Capabilities::is_campus() ) {
			return false;
		}

		add_filter( 'coursepress_extensions_plugins', array( __CLASS__, 'add_to_extensions_list' ) );

	}

	public static function add_to_extensions_list( $plugins ) {

		if ( CoursePress_Model_Capabilities::is_pro() ) {

			$plugins[] = array(
				'name'           => 'MarketPress',
				'slug'           => 'marketpress',
				'base_path'      => self::$base_path['pro'],
				'source'         => CoursePress_Core::$plugin_lib_path . 'assets/files/marketpress-pro.zip',
				'source_message' => __( 'Included in the CoursePress Plugin', CoursePress::TD ),
				'external_url'   => '', /* http://premium.wpmudev.org/project/e-commerce/ */
				'external'       => 'no',
				'protocol'       => '',
			);

		} else {

			$plugins[] = array(
				'name'           => 'MarketPress - WordPress eCommerce',
				'slug'           => 'wordpress-ecommerce',
				'base_path'      => self::$base_path['free'],
				'source'         => 'downloads.wordpress.org/plugin/wordpress-ecommerce.zip',
				'source_message' => __( 'WordPress.org Repository', CoursePress::TD ),
				'external_url'   => '', /* https://wordpress.org/plugins/wordpress-ecommerce/ */
				'external'       => 'yes',
				'protocol'       => 'https',
			);

		}

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