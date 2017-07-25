<?php
/**
 * Class CoursePress_Extension
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension {
    public function __construct() {
    	// Set extensions
        add_filter( 'coursepress_extensions', array( $this, 'set_extensions' ) );

        // Check for active extensions
	    $this->active_extensions();
    }

    function active_extensions() {
    	global $CoursePress;

    	$extensions = coursepress_get_setting( 'extensions' );

    	if ( ! empty( $extensions ) ) {
    		foreach ( $extensions as $extension ) {
    			if ( 'marketpress' == $extension ) {
    				$mpClass = $CoursePress->get_class( 'CoursePress_Extension_MarketPress' );
			    }
		    }
	    }
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
