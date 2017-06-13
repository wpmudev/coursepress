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

	function set_extensions( $extensions ) {
		$extensions = wp_parse_args( $extensions, array(
			'marketpress' => array(
				'name' => 'MarketPress',
				'source_info' => sprintf( __( 'Bundled with %s', 'cp' ), 'CoursePress' ),
				'source' => '',
			),
			'woocommerce' => array(
				'name' => 'WooCommerce',
				'source_info' => sprintf( __( 'Requires %s plugin.', 'cp' ), 'WooCommerce' ),
				'source' => '',
			),
		));
		return $extensions;
	}
}
