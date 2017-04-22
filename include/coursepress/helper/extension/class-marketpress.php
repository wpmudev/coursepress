<?php

class CoursePress_Helper_Extension_MarketPress {

	/**
	 * Whether or not a MarketPress version (free or pro) is installed.
	 * @var bool
	 */
	private static $installed = false;

	/**
	 * Whether or not a MarketPress version (free or pro) is active.
	 * @var bool
	 */
	private static $activated = false;

	/**
	 * If we have already checked for MarketPress once during the current request then we can get the required values from static variables and another search isn't necessary.
	 * @var bool
	 */
	private static $cached = false;

	/**
	 * Base path of the MarketPress version (free or pro) currently installed on the site. Default value is the ideal pro base path.
	 * @var string
	 */
	private static $base_path = 'marketpress/marketpress.php';

	public static function init() {

		if ( CP_IS_CAMPUS ) {
			return false;
		}

		add_filter( 'coursepress_extensions_plugins', array( __CLASS__, 'add_to_extensions_list' ) );
	}

	public static function add_to_extensions_list( $plugins ) {
		self::maybe_initialize_values();

		/**
		 * We'll giving out MP to all verions, yay!!!
		 **/
		$plugins[] = array(
			'name' => 'MarketPress',
			'slug' => 'marketpress',
			'base_path' => self::$base_path,
			'source' => CoursePress::$path . 'asset/file/marketpress-pro.zip',
			'source_message' => __( 'Included in the CoursePress Plugin', 'CP_TD' ),
			'external_url' => '', /* http://premium.wpmudev.org/project/e-commerce/ */
			'external' => 'no',
			'protocol' => '',
		);
		/**
		 * Just hide this for now
		if ( CP_IS_PREMIUM ) {
		} else {
			$plugins[] = array(
				'name' => 'MarketPress - WordPress eCommerce',
				'slug' => 'wordpress-ecommerce',
				'base_path' => self::$base_path['free'],
				'source' => 'downloads.wordpress.org/plugin/wordpress-ecommerce.zip',
				'source_message' => __( 'WordPress.org Repository', 'CP_TD' ),
				'external_url' => '', /* https://wordpress.org/plugins/wordpress-ecommerce/
				'external' => 'yes',
				'protocol' => 'https',
			);

		}
		*/

		return $plugins;
	}

	public static function installed() {
		self::maybe_initialize_values();

		return self::$installed;
	}

	public static function activated() {
		self::maybe_initialize_values();

		return self::$activated;
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

		self::maybe_initialize_values();

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

	private static function maybe_initialize_values()
	{
		if (self::$cached) {
			return;
		}

		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // Need for plugins_api.
		}

		// Create a list of all plugins to check
		$installed_plugins = array_merge(
			get_plugins(),
			get_mu_plugins()
		);

		// Start checking each plugin
		foreach ($installed_plugins as $base_path => $plugin) {

			// Check if this is a MarketPress version (free or pro)
			// Both free and pro can be installed at the same time, only one can be active.
			if (strpos($base_path, 'marketpress.php') !== false) {

				self::$base_path = $base_path;
				self::$installed = true;

				if (is_plugin_active($base_path)) {
					self::$activated = true;
					// No matter which version this is, if it is active, we can't activate any other version so we have to break.
					break;
				}
			}
		}

		self::$cached = true;
	}
}
