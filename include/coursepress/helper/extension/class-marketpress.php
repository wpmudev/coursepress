<?php

class CoursePress_Helper_Extension_MarketPress {

	private static $installed = false;

	private static $activated = false;

	private static $base_path = array(
		'pro' => 'marketpress/marketpress.php',
		'free' => 'wordpress-ecommerce/marketpress.php',
	);

	public static function init() {

		if ( CP_IS_CAMPUS ) {
			return false;
		}

		add_filter( 'coursepress_extensions_plugins', array( __CLASS__, 'add_to_extensions_list' ) );
	}

	public static function add_to_extensions_list( $plugins ) {
		$download_source = 'downloads.wordpress.org/plugin/wordpress-ecommerce.zip';
		$external_url = 'https://wordpress.org/plugins/wordpress-ecommerce/';
		$source_message = __( 'WordPress Repository', 'CP_TD' );
		$is_link = false;
		$base_path = self::$base_path['free'];

		if ( defined( 'CP_IS_PREMIUM' ) && CP_IS_PREMIUM ) {
			/**
			 * Redirect to WPMUDEV Dashboard page
			 */
			$source_message = 'WPMU DEV';
			$external_url = '';
			$is_link = true;
			$download_source = 'https://premium.wpmudev.org/project/e-commerce/';
			$base_path = self::$base_path['pro'];

			if ( is_plugin_active( 'wpmudev-updates/update-notifications.php' ) ) {
				$download_source = add_query_arg( 'page', 'wpmudev', admin_url( 'admin.php' ) );

				if ( class_exists( 'WPMUDEV_Dashboard' ) && WPMUDEV_Dashboard::$api->has_key() ) {
					$download_source = add_query_arg( 'page', 'wpmudev-plugins', $download_source );
				}
			}
		}

		$plugins[] = array(
			'name' => 'MarketPress',
			'slug' => 'marketpress',
			'base_path' => $base_path,
			'source' => $download_source,
			'source_message' => $source_message,
			'external_url' => $external_url,
			'external' => 'yes',
			'protocol' => 'https',
			'is_link' => $is_link,
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

	/**
	 * Show MP install/activation notice
	 **/
	public static function mp_notice() {
		/**
		 * check screen
		 */
		$post_type = CoursePress_Data_Course::get_post_type_name();
		$screen = get_current_screen();
		if ( ! isset( $screen->post_type ) || $post_type != $screen->post_type ) {
			return;
		}
		/**
		 * check user meta
		 */
			$user_id = get_current_user_id();
		$show = get_user_option( 'marketpress-run-notice' );
		if ( 'hide' == $show ) {
			return;
        }
        /**
         * Do not show message, when user already use WooCommerce.
         */
        if ( CoursePress_Helper_Integration_WooCommerce::$is_active ) {
            $woocommerce_is_enabled = CoursePress_Core::get_setting( 'woocommerce/enabled', false );
            if ( $woocommerce_is_enabled ) {
                return;
            }
        }
		$message = '';
		if ( ! self::installed() ) {
			$mp_settings_url = add_query_arg( array(
				'post_type' => $post_type,
				'page' => 'coursepress_settings',
				'tab' => 'extensions',
				),
				admin_url( 'edit.php' )
			);
			$message = sprintf( '<strong>%s</strong> ', __( 'Install MarketPress plugin in order to sell courses.', 'CP_TD' ) );
			$message .= sprintf( '<a href="%s">%s</a>', $mp_settings_url, __( 'Install MarketPress', 'CP_TD' ) );
		} elseif ( ! self::activated() ) {
			$mp_link = sprintf( '<a href="%s">%s</a>', admin_url( 'plugins.php' ), __( 'MarketPress', 'CP_TD' ) );
			$message = sprintf( __( 'Activate %s to start selling courses.', 'CP_TD' ), $mp_link );
		} elseif ( self::activated() ) {
			if ( defined( 'MP_VERSION' ) ) {
				if ( version_compare( MP_VERSION, '3.1.2' ) < 0 ) {
					$plugin_url = admin_url( 'plugins.php' );
					$mp = sprintf( '<a href="%s">%s</a>', $plugin_url, '<strong>MarketPress</strong>' );
					$cp = defined( 'CP_IS_PREMIUM' ) && CP_IS_PREMIUM ? '<strong>CoursePress Pro</strong>' : '<strong>CoursePress</strong>';
					$cp = sprintf( '<a href="%s">%s</a>', $plugin_url, $cp );
					$message = __( 'You are using an older version of %s plugin. %s require the latest version for compatilibity.', 'CP_TD' );
					$message .= __( ' Update your %s now!', 'CP_TD' );
					$message = sprintf( $message, $mp, $cp, $mp );
				}
			}
		}

		if ( ! empty( $message ) ) {
			$data = array(
				'dismissible' => true,
				'option-name' => 'marketpress-run-notice',
				'nonce' => wp_create_nonce( 'marketpress-run-notice'.$user_id ),
				'user_id' => $user_id,
			);
			echo CoursePress_Helper_UI::admin_notice( $message, 'warning', 'marketpress-run-notice', $data );
		}
	}
}
