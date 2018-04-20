<?php

class CoursePress_View_Admin_Setting_Extensions {

	public static function init() {
		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_extensions',
			array( __CLASS__, 'process_form' ),
			10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_extensions',
			array( __CLASS__, 'return_content' ),
			10, 3
		);
		add_action(
			'coursepress_settings_process_extensions',
			array( __CLASS__, 'activating_deactivating_plugin' )
		);

		// TODO: This is premium only. move to premium folder!
		add_action(
			'coursepress_settings_page_pre_render',
			array( __CLASS__, 'remove_dashboard_notification' )
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['extensions'] = array(
			'title' => __( 'Extensions', 'coursepress' ),
			'description' => __( 'Extensions and plugins to enhance CoursePress.', 'coursepress' ),
			'order' => 60,
			'is_form' => false,
			'buttons' => 'none',
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		CoursePress_Helper_Extension::init();
		if ( isset( $_POST['action'] ) && 'install-plugin' === $_POST['action'] && 'extensions' === $tab && wp_verify_nonce( $_POST['_wp_nonce'], 'install-plugin' ) ) {

			ob_start();

			echo '<div class="coursepress_settings_wrapper">' .
				'<h1>' . esc_html( CoursePress::$name ) . ' : ' . esc_html__( 'Installing plugin...', 'coursepress' ) . '</h1>
                <hr />';
			echo '</div>';
			echo '<h2>' . esc_html( $_POST['plugin_name'] ) . '</h2>';

			// add_filter( 'coursepress_settings_page_main', array( __CLASS__, 'return_content_plugin_install' ) );
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.

			// Prefix a default path to pre-packaged plugins.
			if ( cp_is_true( $_POST['external'] ) ) {
				$source = esc_url_raw( $_POST['protocol'] . '://' . $_POST['plugin_source'] );
			} else {
				$source = $_POST['plugin_source'];
			}

			// Create a new instance of Plugin_Upgrader.
			$data = compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' );
			$skin = new Plugin_Installer_Skin( $data );
			$upgrader = new Plugin_Upgrader( $skin );

			// Perform the action and install the plugin from the $source urldecode().
			$upgrader->install( $source );

			wp_cache_flush();
			$content = ob_get_contents();
			ob_end_flush();

			$content .= add_filter( 'coursepress_settings_page_main', array( __CLASS__, 'return_content_plugin_install' ), $content );
		} else {

			$content = '
            <input type="hidden" name="page" value="' . esc_attr( $slug ) .'"/>
            <input type="hidden" name="tab" value="' . esc_attr( $tab ) .'"/>
            <input type="hidden" name="action" value="updateoptions"/>
        ' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false );

			$content .= CoursePress_Helper_Extension::plugins_table();
		}

		// if ( ! empty( $section['description'] ) ) {
		// $content .= '<p class="description">' . esc_html( $section['description'] ) . '</p>';
		// }
		return $content;

	}

	public static function process_form( $page, $tab ) {
	}

	// hooked to `admin_init` so we can redirect to pages
	public static function activating_deactivating_plugin() {
		$data = ! empty( $_POST ) ? stripslashes_deep( $_POST ) : array();
		$tab = ( isset( $data['tab'] ) ) ? $data['tab'] : false;

		if ( $data && $tab === 'extensions' ) {
			$action = ( isset( $data['action'] ) ) ? $data['action'] : false ;
			$plugin = ( isset( $data['plugin'] ) ) ? $data['plugin'] : false ;
			$plugin_base = ( isset( $data['base'] ) ) ? $data['base'] : false ;
			$nonce = ( isset( $data['_wp_nonce'] ) ) ? $data['_wp_nonce'] : false ;

			if ( $action && $plugin_base && $nonce ) {
				// plugin activation
				if ( 'activate-plugin' === $action && wp_verify_nonce( $nonce, 'activate-plugin' ) ) {
					activate_plugin( $plugin_base, null, false, true );
					wp_safe_redirect( add_query_arg( 'tab',$plugin ) );
					exit;
				}
				// plugin deactivation
				if ( 'deactivate-plugin' === $action && wp_verify_nonce( $nonce, 'deactivate-plugin' ) ) {
					deactivate_plugins( $plugin_base, true );
					wp_safe_redirect( add_query_arg( 'tab',$tab ) );
					exit;
				}
			}
		}
	}

	public static function return_content_plugin_install( $content ) {
		$return_url = add_query_arg(
			array(
				'page' => $_GET['page'],
				'tab' => $_GET['tab'],
			),
			admin_url( 'admin.php' )
		);

		return '<a href="' . $return_url . '">' . esc_html__( 'Return to CoursePress settings.', 'coursepress' ) . '</a>';
	}

	public static function remove_dashboard_notification() {
		if ( isset( $_POST['action'] ) && 'install-plugin' == $_POST['action'] ) {
			global $wpmudev_notices;
			$wpmudev_notices = array();
		}
	}
}
