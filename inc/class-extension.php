<?php
/**
 * Class CoursePress_Extension
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Extension {

	/**
	 * CoursePress_Extension constructor.
	 */
	public function __construct() {

		// Set extensions.
		add_filter( 'coursepress_extensions', array( $this, 'set_extensions' ) );

		// Check for active extensions.
	    $this->active_extensions();
	}

	/**
	 * Load active extension class.
	 *
	 * @global $CoursePress
	 */
	function active_extensions() {

		global $CoursePress;

		$extensions = coursepress_get_setting( 'extensions' );
		$active = array(
			'commerce' => false,
		);

		if ( ! empty( $extensions ) ) {
			foreach ( $extensions as $extension ) {
				switch ( $extension ) {
					case 'marketpress':
						$CoursePress->get_class( 'CoursePress_Extension_MarketPress' );
					break;
					case 'woocommerce':
						$CoursePress->get_class( 'CoursePress_Extension_WooCommerce' );
					break;
				}

				// Set extension type to active
				if ( isset( $extension['type'] ) ) {
					$active[ $extension['type'] ] = true;
				}
			}
		}

		// Load some code for missing extensions - like fix missing
		// shortcodes for commerce.
		if ( false === $active['commerce'] ) {
			$CoursePress->get_class( 'CoursePress_Extension_Commerce' );
		}
	}

	/**
	 * Set active extensions data details.
	 *
	 * @param array $extensions Extensions data.
	 *
	 * @global $CoursePress
	 *
	 * @return mixed
	 */
	function set_extensions( $extensions ) {

		global $CoursePress;

		$extensions['marketpress'] = array(
			'name' => 'MarketPress',
			'source_info' => sprintf( __( 'From %s', 'cp' ), 'WPMU DEV' ),
			'file' => 'marketpress/marketpress.php',
			'link' => 'https://premium.wpmudev.org/project/e-commerce/',
			'type' => 'commerce',
			'class' => $CoursePress->get_class( 'CoursePress_Extension_MarketPress' ),
		);

		$extensions['woocommerce'] = array(
			'name' => 'WooCommerce',
			'source_info' => sprintf( __( 'Requires %s plugin.', 'cp' ), 'WooCommerce' ),
			'file' => 'woocommerce/woocommerce.php',
			'link' => '',
			'type' => 'commerce',
			'class' => $CoursePress->get_class( 'CoursePress_Extension_WooCommerce' ),
		);

		// Check status.
		foreach ( $extensions as $key => $data ) {
			$extensions[ $key ]['is_active'] = $extensions[ $key ]['is_installed'] = false;
			if ( method_exists( $data['class'], 'installed' ) ) {
				$extensions[ $key ]['is_installed'] = $data['class']->installed();
			}
			if ( method_exists( $data['class'], 'activated' ) ) {
				$extensions[ $key ]['is_active'] = $data['class']->activated();
			}
			if ( $extensions[ $key ]['is_active'] ) {
				$class_name = 'CoursePress_Extension_'.$data['name'];
				new $class_name;
			}
		}

		return $extensions;
	}
}
