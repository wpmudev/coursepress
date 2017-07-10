<?php
/**
 * Class CoursePress_Extension_MarketPress
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension_MarketPress {

	private $base_path = array(
		'pro' => 'marketpress/marketpress.php',
		'free' => 'wordpress-ecommerce/marketpress.php',
	);

	public function __construct() {
	}

	public function installed_scope() {
		$scope = '';
		foreach ( $this->base_path as $key => $path ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $path;
			$plugin_mu_dir = WP_CONTENT_DIR . '/mu-plugins/' . $path;
			$location = file_exists( $plugin_dir ) ? trailingslashit( WP_PLUGIN_DIR ) : ( file_exists( $plugin_mu_dir ) ?  WP_CONTENT_DIR . '/mu-plugins/' : '' ) ;
			$scope = ! empty( $location ) ? $key : $scope;
		}
		return $scope;
	}

	public function installed() {
		$scope = $this->installed_scope();
		return ! empty( $scope );
	}

	public function activated() {
		$scope = $this->installed_scope();
		require_once ABSPATH . 'wp-admin/includes/plugin.php'; // Need for plugins_api.
		return ! empty( $scope ) ? is_plugin_active( $this->base_path[ $scope ] ) : false;
	}
}
