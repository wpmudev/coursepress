<?php

class CoursePress_Helper_Extension {

	private static $plugins = array();

	public static function init() {
		$plugins = array( 'MarketPress', 'CP_TCPDF' );
		foreach ( $plugins as $plugin ) {
			if ( method_exists( 'CoursePress_Helper_Extension_' . $plugin, 'init' ) ) {
				call_user_func( 'CoursePress_Helper_Extension_' . $plugin . '::init' );
			}
		}

		self::$plugins = self::get_plugins();
	}

	private static function get_plugins() {
		return apply_filters( 'coursepress_extensions_plugins', self::$plugins );
	}

	public static function plugins_table() {
		$content = '';

		if ( empty( self::$plugins ) ) {
			return $content;
		}

		$content .= '<h3>' . esc_html__( 'Plugins', 'CP_TD' ) . '</h3>';
		$content .= '<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th>' . esc_html__( 'Plugin', 'CP_TD' ) . '</th>
					<th>' . esc_html__( 'Source', 'CP_TD' ) . '</th>
					<th>' . esc_html__( 'Status', 'CP_TD' ) . '</th>
					<th>' . esc_html__( 'Action', 'CP_TD' ) . '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ( self::$plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin['base_path'];
			$plugin_mu_dir = WP_CONTENT_DIR . '/mu-plugins/' . $plugin['base_path'];
			$location = file_exists( $plugin_dir ) ? trailingslashit( WP_PLUGIN_DIR ) : ( file_exists( $plugin_mu_dir ) ?  WP_CONTENT_DIR . '/mu-plugins/' : '' ) ;
			$installed = ! empty( $location );
			$activated = $installed ? is_plugin_active( $plugin['base_path'] ) : false;

			if ( $installed && $activated ) {
				$status = 'Installed/Activated';
				if ( current_user_can( 'manage_options' ) ) {

					$action = '<form method="post">
						<input type="hidden" name="page" value="' . $_GET['page'] . '">
						<input type="hidden" name="tab" value="' . $_GET['tab'] . '">
						<input type="hidden" name="action" value="' . 'deactivate-plugin' . '">
						<input type="hidden" name="plugin" value="' . $plugin['slug'] . '">
						<input type="hidden" name="plugin_name" value="' . $plugin['name'] . '">
						<input type="hidden" name="location" value="' . $location . '">
						<input type="hidden" name="base" value="' . $plugin['base_path'] . '">
						<input type="hidden" name="_wp_nonce" value="' . wp_create_nonce( 'deactivate-plugin' ) . '">
						<input type="submit" class="button" value="' . esc_attr__( 'De-Activate', 'CP_TD' ) . '" />
					</form>
					';
				}
			} elseif ( $installed ) {
				$status = 'Installed/Not Activated';
				if ( current_user_can( 'activate_plugins' ) ) {
					$action = '<form method="post">
						<input type="hidden" name="page" value="' . $_GET['page'] . '">
						<input type="hidden" name="tab" value="' . $_GET['tab'] . '">
						<input type="hidden" name="action" value="' . 'activate-plugin' . '">
						<input type="hidden" name="plugin" value="' . $plugin['slug'] . '">
						<input type="hidden" name="plugin_name" value="' . $plugin['name'] . '">
						<input type="hidden" name="location" value="' . $location . '">
						<input type="hidden" name="base" value="' . $plugin['base_path'] . '">
						<input type="hidden" name="_wp_nonce" value="' . wp_create_nonce( 'activate-plugin' ) . '">
						<input type="submit" class="button" value="' . esc_attr__( 'Activate', 'CP_TD' ) . '" />
					</form>
					';
				}
			} else {
				$status = 'Not Installed';
				// http://network1.dev/wp-admin/network/update.php?action=install-plugin&plugin=jetpack&_wpnonce=3cee8117d8
				if ( current_user_can( 'install_plugins' ) ) {

					if ( empty( $plugin['is_link'] ) ) {
						$action = '<form method="post">
							<input type="hidden" name="page" value="' . $_GET['page'] . '">
							<input type="hidden" name="tab" value="' . $_GET['tab'] . '">
							<input type="hidden" name="action" value="' . 'install-plugin' . '">
							<input type="hidden" name="plugin" value="' . $plugin['slug'] . '">
							<input type="hidden" name="plugin_name" value="' . $plugin['name'] . '">
							<input type="hidden" name="plugin_source" value="' . $plugin['source'] . '">
							<input type="hidden" name="external" value="' . $plugin['external'] . '">
							<input type="hidden" name="protocol" value="' . $plugin['protocol'] . '">
							<input type="hidden" name="_wp_nonce" value="' . wp_create_nonce( 'install-plugin' ) . '">
							<input type="submit" class="button" value="' . esc_attr__( 'Install', 'CP_TD' ) . '" />
						</form>
						';
					} else {
						$action = sprintf( '<a href="%1$s" class="button">%2$s</a>', esc_url_raw( $plugin['source'] ), __( 'Install', 'CP_TD') );
					}
				}
			}

			$content .= '
				<tr>
					<td>' . $plugin['name'] . '</td>
					<td>' . $plugin['source_message'] . '</td>
					<td>' . esc_html( $status ) . '</td>
					<td>' . sprintf( '%s', $action ) . '</td>
				</tr>
				';
		}

		$content .= '
			</tbody>
		</table>
		';

		return $content;
	}
}
