<?php

class CoursePress_View_Admin_Settings_Extensions{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_extensions', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_extensions', array( __CLASS__, 'return_content' ), 10, 3 );
		add_action( 'coursepress_settings_page_pre_render', array( __CLASS__, 'remove_dashboard_notification' ) );
	}


	public static function add_tabs( $tabs ) {

		$tabs['extensions'] = array(
			'title' => __( 'Extensions', CoursePress::TD ),
			'description' => __( 'Extensions and plugins to enhance CoursePress.', CoursePress::TD ),
			'order' => 60,
			'is_form' => false,
			'buttons' => 'none'
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		CoursePress_Helper_Extensions::init();

		$content = '
			<input type="hidden" name="page" value="' . esc_attr( $slug ) .'"/>
			<input type="hidden" name="tab" value="' . esc_attr( $tab ) .'"/>
			<input type="hidden" name="action" value="updateoptions"/>
		' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false ) . '

		';

		$content .= CoursePress_Helper_Extensions::plugins_table();

		//if( ! empty( $section['description'] ) ) {
		//	$content .= '<p class="description">' . esc_html( $section['description'] ) . '</p>';
		//}

		return $content;

	}

	public static function process_form( $page, $tab ) {

		if ( isset( $_POST['action'] ) && 'install-plugin' === $_POST['action'] && 'extensions' === $tab && wp_verify_nonce( $_POST['_wp_nonce'], 'install-plugin' ) ) {

			echo '<div class="coursepress_settings_wrapper">' .
			     '<h3>' . esc_html( CoursePress::$name ) . ' : ' . esc_html__( 'Installing plugin...', CoursePress::TD ) . '</h3>
		            <hr />';
			echo '</div>';
			echo '<h3>' . esc_html( $_POST['plugin_name'] ) . '</h3>';

			//add_filter( 'coursepress_settings_page_main', array( __CLASS__, 'return_content_plugin_install' ) );

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api.
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes.

			// Prefix a default path to pre-packaged plugins.
			if ( 'yes' === $_POST['external'] ) {
				$source = esc_url_raw( $_POST['protocol'] . '://' . $_POST['plugin_source'] );
			} else {
				$source = $_POST['plugin_source'];
			}


			// Create a new instance of Plugin_Upgrader.
			$upgrader = new Plugin_Upgrader( $skin = new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

			// Perform the action and install the plugin from the $source urldecode().
			$upgrader->install( $source );

			wp_cache_flush();

			add_filter( 'coursepress_settings_page_main', array( __CLASS__, 'return_content_plugin_install' ) );

		}

		if ( isset( $_POST['action'] ) && 'activate-plugin' === $_POST['action'] && 'extensions' === $tab && wp_verify_nonce( $_POST['_wp_nonce'], 'activate-plugin' ) ) {

			activate_plugin( $_POST['base'] );

		}

		if ( isset( $_POST['action'] ) && 'deactivate-plugin' === $_POST['action'] && 'extensions' === $tab && wp_verify_nonce( $_POST['_wp_nonce'], 'deactivate-plugin' ) ) {

			deactivate_plugins( $_POST['base'] );

		}



	}

	public static function return_content_plugin_install( $content ) {
		$return_url = add_query_arg( array( 'page' => $_GET['page'], 'tab' => $_GET['tab'] ),  admin_url( 'admin.php' ) );
		return '<a href="' . $return_url . '">' . esc_html__( 'Return to CoursePress settings.', CoursePress::TD ) . '</a>';
	}

	public static function remove_dashboard_notification() {

		if ( isset( $_POST['action'] ) && 'install-plugin' === $_POST['action'] ) {
			global $wpmudev_notices;
			$wpmudev_notices = array();
		}
	}

}