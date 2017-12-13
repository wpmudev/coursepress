<?php
/**
 * Admin view.
 *
 * @package CoursePress
 */

/**
 * Settings for Basic Certificate.
 */
class CoursePress_View_Admin_Setting_MarketPress {

	public static function init() {
		/**
		 * do not run if integration is not active
		 */
		if ( ! CoursePress_Helper_Extension_MarketPress::activated() ) {
			return;
		}

		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_marketpress',
			array( __CLASS__, 'process_form' ),
			10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_marketpress',
			array( __CLASS__, 'return_content' ),
			10, 3
		);
		add_action(
			'coursepress_general_options_page',
			array( __CLASS__, 'add_marketpress_general_option' )
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['marketpress'] = array(
			'title' => __( 'MarketPress', 'CP_TD' ),
			'description' => __( 'Allow to integrate MarketPress to sell courses...', 'CP_TD' ),
			'order' => 69,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$is_enabled = CoursePress_Core::get_setting( 'marketpress/enabled', false );
		$use_redirect = CoursePress_Core::get_setting( 'marketpress/redirect', false );
		$unpaid = CoursePress_Core::get_setting( 'marketpress/unpaid', 'change_status' );
		$delete = CoursePress_Core::get_setting( 'marketpress/delete', 'change_status' );

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
								name="coursepress_settings[marketpress][enabled]"
								class="certificate_enabled"
								value="1" />
							<?php esc_html_e( 'Use MarketPress to sell courses', 'CP_TD' ); ?>
						</label>
						<p class="description"><?php _e( 'If checked, MarketPress will be use for selling courses', 'CP_TD' ) ?></p>
</td>
					</tr>
					<tr>
						<td><label>
							<input type="checkbox"
								<?php checked( cp_is_true( $use_redirect ) ); ?>
								name="coursepress_settings[marketpress][redirect]"
								class="certificate_enabled"
								value="1" />
							<?php esc_html_e( 'Redirect MarketPress product post to a parent course post', 'CP_TD' ); ?>
						</label>
							<p class="description"><?php _e( 'If checked, visitors who try to access MarketPress single post will be automatically redirected to a parent course single post.', 'CP_TD' ) ?></p>
						</td>
					</tr>
					<tr>
						<td>
							<h3><?php esc_html_e( 'When the course becomes unpaid, then:', 'CP_TD' ); ?></h3>
							<ul>
								<li><label><input type="radio"
									<?php checked( $unpaid, 'change_status' ); ?>
									name="coursepress_settings[marketpress][unpaid]"
									class="certificate_enabled"
									value="change_status" /> <?php esc_html_e( 'Change to draft related MarketPress product.', 'CP_TD' ); ?></label></li>
								<li><label><input type="radio"
									<?php checked( $unpaid, 'delete' ); ?>
									name="coursepress_settings[marketpress][unpaid]"
									class="certificate_enabled"
									value="delete" /> <?php esc_html_e( 'Delete related MarketPress product.', 'CP_TD' ); ?></label></li>
							</ul>
						</td>
					</tr>
					<tr>
						<td>
							<h3><?php esc_html_e( 'When the course is deleted, then:', 'CP_TD' ); ?></h3>
							<ul>
								<li><label><input type="radio"
									<?php checked( $delete, 'change_status' ); ?>
									name="coursepress_settings[marketpress][delete]"
									class="certificate_enabled"
									value="change_status" /> <?php esc_html_e( 'Change to draft related MarketPress product.', 'CP_TD' ); ?></label></li>
								<li><label><input type="radio"
									<?php checked( $delete, 'delete' ); ?>
									name="coursepress_settings[marketpress][delete]"
									class="certificate_enabled"
									value="delete" /> <?php esc_html_e( 'Delete related MarketPress product.', 'CP_TD' ); ?></label></li>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	public static function process_form( $page, $tab ) {
		
		if ( ! isset( $_POST['_wpnonce'] ) ) { return; }
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) { return; }

		if ( ! isset( $_POST['action'] ) ) { return; }
		if ( 'updateoptions' != $_POST['action'] ) { return; }
		if ( 'marketpress' != $tab ) { return; }

		$settings = CoursePress_Core::get_setting( false ); // false: Get all settings.

		$post_settings = array(
			'marketpress' => array(
				'enabled' => false,
				'redirect' => false,
				'unpaid' => 'change_status',
				'delete' => 'change_status'
			)
		);
		
		/**
		 * check data and if exists, then update
		 */
		if (
			isset( $_POST['coursepress_settings'] )
			&& is_array( $_POST['coursepress_settings'] )
			&& isset( $_POST['coursepress_settings']['marketpress'] )
			&& is_array( $_POST['coursepress_settings']['marketpress'] )
		) {
			foreach ( $post_settings['marketpress'] as $key => $value ) {
				if ( isset( $_POST['coursepress_settings']['marketpress'][ $key ] ) ) {
					$post_settings['marketpress'][ $key ] = true;
				}
			}
			if (
				isset( $_POST['coursepress_settings']['marketpress']['unpaid'] )
				&& 'delete' == $_POST['coursepress_settings']['marketpress']['unpaid']
			) {
				$post_settings['marketpress']['unpaid'] = 'delete';
			} else {
				$post_settings['marketpress']['unpaid'] = 'change_status';
			}
			if (
				isset( $_POST['coursepress_settings']['marketpress']['delete'] )
				&& 'delete' == $_POST['coursepress_settings']['marketpress']['delete']
			) {
				$post_settings['marketpress']['delete'] = 'delete';
			} else {
				$post_settings['marketpress']['delete'] = 'change_status';
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
