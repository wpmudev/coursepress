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
        $marketpress = array(
            'name' => 'MarketPress',
            'source_info' => sprintf( __( 'Bundled with %s', 'cp' ), 'CoursePress' ),
        );
        $extensions['marketpress'] = $marketpress;

        $woo = array(
            'name' => 'WooCommerce',
            'source_info' => sprintf( __( 'Requires %s plugin.', 'cp' ), 'WooCommerce' ),
        );
        $extensions['woocommerce'] = $woo;

        return $extensions;
    }
}
