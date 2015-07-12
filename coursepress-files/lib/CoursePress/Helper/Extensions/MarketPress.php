<?php

class CoursePress_Helper_Extensions_MarketPress {

	const PLUGIN_FILE = '128762_marketpress-ecommerce-2.9.6.2.zip';


	public static function init() {

		if( CoursePress_Model_Capabilities::is_campus() ) {
			return false;
		}

		add_filter( 'coursepress_extensions_plugins', array( __CLASS__, 'add_to_extensions_list' ) );
	}

	public static function add_to_extensions_list( $plugins ) {

		if( CoursePress_Model_Capabilities::is_pro() ) {

			$plugins[] = array(
				'name'           => 'MarketPress',
				'slug'           => 'marketpress',
				'base_path'      => 'marketpress/marketpress.php',
				'source'         => CoursePress_Core::$plugin_lib_path . 'files/plugins/' . self::PLUGIN_FILE,
				'source_message' => __( 'Included in the CoursePress Plugin', 'cp' ),
				'external_url'   => '',
				// http://premium.wpmudev.org/project/e-commerce/
				'external'       => 'no',
				'protocol'       => '',
			);

		} else {

			$plugins[] = array(
				'name'           => 'MarketPress - WordPress eCommerce',
				'slug'           => 'wordpress-ecommerce',
				'base_path'      => 'wordpress-ecommerce/marketpress.php',
				'source'         => 'downloads.wordpress.org/plugin/wordpress-ecommerce.zip',
				'source_message' => __( 'WordPress.org Repository', 'cp' ),
				'external_url'   => '',
				// https://wordpress.org/plugins/wordpress-ecommerce/
				'external'       => 'yes',
				'protocol'       => 'https',
			);

		}

		return $plugins;
	}


}