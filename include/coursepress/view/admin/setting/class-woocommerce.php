<?php
/**
 * Admin view.
 *
 * @package CoursePress
 */

/**
 * Settings for Basic Certificate.
 */
class CoursePress_View_Admin_Setting_WooCommerce {

	public static function init() {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return;
		}
		// TODO Find out if Certificates are a premium feature?
		//      Or only certificate-emails... Or if this condition is wrong...
		if ( ! CP_IS_PREMIUM ) {
			add_filter(
				'coursepress_default_email_settings',
				array( __CLASS__, 'remove_woocommerce_email' )
			);
			add_filter(
				'coursepress_default_email_settings_sections',
				array( __CLASS__, 'remove_woocommerce_email' )
			);
			return;
		}

		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_woocommerce',
			array( __CLASS__, 'process_form' ),
			10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_woocommerce',
			array( __CLASS__, 'return_content' ),
			10, 3
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['woocommerce'] = array(
			'title' => __( 'WooCommerce', 'CP_TD' ),
			'description' => __( 'Allow to integrate WooCommerce to sell courses..', 'CP_TD' ),
			'order' => 69,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$is_enabled = CoursePress_Core::get_setting( 'woocommerce/enabled', true );
		$use_redirect = CoursePress_Core::get_setting( 'woocommerce/redirect', true );

		ob_start();
		?>
		<input type="hidden" name="page" value="<?php echo esc_attr( $slug ); ?>" />
		<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
		<input type="hidden" name="action" value="updateoptions" />
		<?php wp_nonce_field( 'update-coursepress-options', '_wpnonce' ); ?>

			<table class="form-table compressed">
				<tbody>
					<tr>
						<td><label>
							<input type="checkbox"
								<?php checked( cp_is_true( $is_enabled ) ); ?>
								name="coursepress_settings[woocommerce][enabled]"
								class="certificate_enabled"
								value="1" />
							<?php esc_html_e( 'Use WooCommerce to sell courses', 'CP_TD' ); ?>
						</label></td>
					</tr>
					<tr>
						<td><label>
							<input type="checkbox"
								<?php checked( cp_is_true( $is_enabled ) ); ?>
								name="coursepress_settings[woocommerce][redirect]"
								class="certificate_enabled"
								value="1" />
							<?php esc_html_e( 'Redirect WooCommerce product post to a parent course post', 'CP_TD' ); ?>
						</label></td>
					</tr>
				</tbody>
				</tbody>
			</table>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	public static function process_form( $page, $tab ) {
		if ( ! isset( $_POST['action'] ) ) { return; }
		if ( 'updateoptions' != $_POST['action'] ) { return; }
		if ( 'woocommerce' != $tab ) { return; }
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) { return; }

		$settings = CoursePress_Core::get_setting( false ); // false: Get all settings.
		$post_settings = (array) $_POST['coursepress_settings'];

		// Sanitize $post_settings, especially to fix up unchecked checkboxes.
		if ( isset( $post_settings['woocommerce']['enabled'] ) ) {
			$post_settings['woocommerce']['enabled'] = true;
		} else {
			$post_settings['woocommerce']['enabled'] = false;
		}

		$post_settings = CoursePress_Helper_Utility::sanitize_recursive( $post_settings );

		// Don't replace settings if there is nothing to replace.
		if ( ! empty( $post_settings ) ) {
			CoursePress_Core::update_setting(
				false, // False will replace all settings.
				CoursePress_Core::merge_settings( $settings, $post_settings )
			);
		}
	}
}
