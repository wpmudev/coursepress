<?php
/**
 * Class CoursePress_Extension
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension {
	public function __construct() {
		add_filter( 'coursepress_extensions', array( $this, 'set_extensions' ) );
	}

	function is_plugin_installed( $plugin_name ) {
	}

	function is_plugin_active( $plugin_name ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		return is_plugin_active( $plugin_name );
	}

	function set_extensions( $extensions ) {
		global $CoursePress;
		$marketpress = array(
			'name' => 'MarketPress',
			'source_info' => sprintf( __( 'Bundled with %s', 'cp' ), 'CoursePress' ),
			'file' => 'marketpress/marketpress.php',
		);
		$extensions['marketpress'] = $marketpress;

		$woo = array(
			'name' => 'WooCommerce',
			'source_info' => sprintf( __( 'Requires %s plugin.', 'cp' ), 'WooCommerce' ),
			'file' => 'woocommerce/woocommerce.php',
		);
		$extensions['woocommerce'] = $woo;
		/**
		 * check status
		 */
		foreach ( $extensions as $key => $data ) {
			$extensions[ $key ]['is_active'] = $this->is_plugin_active( $data['file'] );
			if ( $extensions[ $key ]['is_active'] ) {
				$class_name = 'CoursePress_Extension_'.$data['name'];
				new $class_name;
			}
		}
		return $extensions;
	}
}
