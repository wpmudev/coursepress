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
		if ( ! CoursePress_Helper_Integration_WooCommerce::is_active() ) {
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
		add_action(
			'coursepress_general_options_page',
			array( __CLASS__, 'add_woocommerce_general_option' )
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['woocommerce'] = array(
			'title' => __( 'WooCommerce', 'coursepress' ),
			'description' => __( 'Allow to integrate WooCommerce to sell courses.', 'coursepress' ),
			'order' => 69,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$is_enabled = CoursePress_Core::get_setting( 'woocommerce/enabled', false );
		$use_redirect = CoursePress_Core::get_setting( 'woocommerce/redirect', false );
		$unpaid = CoursePress_Core::get_setting( 'woocommerce/unpaid', 'change_status' );
		$delete = CoursePress_Core::get_setting( 'woocommerce/delete', 'change_status' );

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
							<?php esc_html_e( 'Use WooCommerce to sell courses', 'coursepress' ); ?>
						</label>
						<p class="description"><?php _e( 'If checked, WooCommerce will be use instead of the MarketPress for selling courses', 'coursepress' ) ?></p>
						</td>
					</tr>
					<tr>
						<td><label>
							<input type="checkbox"
								<?php checked( cp_is_true( $use_redirect ) ); ?>
								name="coursepress_settings[woocommerce][redirect]"
								class="certificate_enabled"
								value="1" />
							<?php esc_html_e( 'Redirect WooCommerce product post to a parent course post', 'coursepress' ); ?>
						</label>
							<p class="description"><?php _e( 'If checked, visitors who try to access WooCommerce single post will be automatically redirected to a parent course single post.', 'coursepress' ) ?></p>
						</td>
					</tr>
					<tr>
						<td>
							<h3><?php esc_html_e( 'When the course becomes unpaid, then:', 'coursepress' ); ?></h3>
							<ul>
								<li><label><input type="radio"
									<?php checked( $unpaid, 'change_status' ); ?>
									name="coursepress_settings[woocommerce][unpaid]"
									class="certificate_enabled"
									value="change_status" /> <?php esc_html_e( 'Change to draft related WooCommerce product.', 'coursepress' ); ?></label></li>
								<li><label><input type="radio"
									<?php checked( $unpaid, 'delete' ); ?>
									name="coursepress_settings[woocommerce][unpaid]"
									class="certificate_enabled"
									value="delete" /> <?php esc_html_e( 'Delete related WooCommerce product.', 'coursepress' ); ?></label></li>
							</ul>
						</td>
					</tr>
					<tr>
						<td>
							<h3><?php esc_html_e( 'When the course is deleted, then:', 'coursepress' ); ?></h3>
							<ul>
								<li><label><input type="radio"
									<?php checked( $delete, 'change_status' ); ?>
									name="coursepress_settings[woocommerce][delete]"
									class="certificate_enabled"
									value="change_status" /> <?php esc_html_e( 'Change to draft related WooCommerce product.', 'coursepress' ); ?></label></li>
								<li><label><input type="radio"
									<?php checked( $delete, 'delete' ); ?>
									name="coursepress_settings[woocommerce][delete]"
									class="certificate_enabled"
									value="delete" /> <?php esc_html_e( 'Delete related WooCommerce product.', 'coursepress' ); ?></label></li>
							</ul>
						</td>
					</tr>
				</tbody>
				</tbody>
			</table>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	public static function process_form( $page, $tab ) {
		// First verify nonce before checking any other POST value.
		if ( ! isset( $_POST['_wpnonce'] ) ) { return; }
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) { return; }

		if ( 'woocommerce' != $tab ) { return; }
		if ( ! isset( $_POST['action'] ) ) { return; }
		if ( 'updateoptions' != $_POST['action'] ) { return; }

		$settings = CoursePress_Core::get_setting( false ); // false: Get all settings.

		$post_settings = array(
			'woocommerce' => array(
				'enabled' => false,
				'redirect' => false,
				'unpaid' => 'change_status',
				'delete' => 'change_status',
			),
		);

		/**
		 * check data and if exists, then update
		 */
		if (
			isset( $_POST['coursepress_settings'] )
			&& is_array( $_POST['coursepress_settings'] )
			&& isset( $_POST['coursepress_settings']['woocommerce'] )
			&& is_array( $_POST['coursepress_settings']['woocommerce'] )
		) {
			foreach ( $post_settings['woocommerce'] as $key => $value ) {
				if ( isset( $_POST['coursepress_settings']['woocommerce'][ $key ] ) ) {
					$post_settings['woocommerce'][ $key ] = true;
				}
			}
			if (
				isset( $_POST['coursepress_settings']['woocommerce']['unpaid'] )
				&& 'delete' == $_POST['coursepress_settings']['woocommerce']['unpaid']
			) {
				$post_settings['woocommerce']['unpaid'] = 'delete';
			} else {
				$post_settings['woocommerce']['unpaid'] = 'change_status';
			}
			if (
				isset( $_POST['coursepress_settings']['woocommerce']['delete'] )
				&& 'delete' == $_POST['coursepress_settings']['woocommerce']['delete']
			) {
				$post_settings['woocommerce']['delete'] = 'delete';
			} else {
				$post_settings['woocommerce']['delete'] = 'change_status';
			}
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
