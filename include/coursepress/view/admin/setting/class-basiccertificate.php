<?php
/**
 * Admin view.
 *
 * @package CoursePress
 */

/**
 * Settings for Basic Certificate.
 */
class CoursePress_View_Admin_Setting_BasicCertificate {

	public static function init() {
		// TODO Find out if Certificates are a premium feature?
		//      Or only certificate-emails... Or if this condition is wrong...
		if ( ! CP_IS_PREMIUM ) {
			add_filter(
				'coursepress_default_email_settings',
				array( __CLASS__, 'remove_basic_certificate_email' )
			);
			add_filter(
				'coursepress_default_email_settings_sections',
				array( __CLASS__, 'remove_basic_certificate_email' )
			);
			return;
		}

		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_basic_certificate',
			array( __CLASS__, 'process_form' ),
			10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_basic_certificate',
			array( __CLASS__, 'return_content' ),
			10, 3
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['basic_certificate'] = array(
			'title' => __( 'Basic Certificate', 'CP_TD' ),
			'description' => __( 'Setup the settings for the certificates issued upon course completion.', 'CP_TD' ),
			'order' => 40,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$is_enabled = CoursePress_Core::get_setting( 'basic_certificate/enabled', true );
		$cert_background = CoursePress_Core::get_setting( 'basic_certificate/background_image' );
		$cert_padd_top = CoursePress_Core::get_setting( 'basic_certificate/padding/top' );
		$cert_padd_bottom = CoursePress_Core::get_setting( 'basic_certificate/padding/bottom' );
		$cert_padd_left = CoursePress_Core::get_setting( 'basic_certificate/padding/left' );
		$cert_padd_right = CoursePress_Core::get_setting( 'basic_certificate/padding/right' );
		$cert_orientation = CoursePress_Core::get_setting( 'basic_certificate/orientation', 'L' );
		$allowed_extensions = CoursePress_Helper_Utility::get_image_extensions();

		ob_start();
		?>
		<input type="hidden" name="page" value="<?php echo esc_attr( $slug ); ?>" />
		<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
		<input type="hidden" name="action" value="updateoptions" />
		<?php wp_nonce_field( 'update-coursepress-options', '_wpnonce' ); ?>

		<!-- Enable Checkbox -->
		<h3 class="hndle" style="cursor:auto;">
			<span><?php esc_html_e( 'Certificate Options', 'CP_TD' ); ?></span>
		</h3>
		<div class="inside">
			<table class="form-table compressed">
				<tbody id="items">
					<tr>
						<td><label>
							<input type="checkbox"
								<?php checked( cp_is_true( $is_enabled ) ); ?>
								name="coursepress_settings[basic_certificate][enabled]"
								class="certificate_enabled"
								value="1" />
							<?php esc_html_e( 'Enable Basic Certificate', 'CP_TD' ); ?>
						</label></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Certificate Layout -->
		<div class="certificate-details" style="display: none">
		<h3 class="hndle" style="cursor:auto;">
			<span><?php esc_html_e( 'Certificate Layout', 'CP_TD' ); ?></span>
		</h3>
		<p class="description">
			<?php esc_html_e( 'Use the editor below to create the layout of your certificate.', 'CP_TD' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'These codes will be replaced with actual data: FIRST_NAME, LAST_NAME, COURSE_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER, UNIT_LIST', 'CP_TD' ); ?>
		</p>
		<div class="inside">
			<table class="form-table compressed">
			<tbody id="items">
			<tr>
				<td colspan="2">
				<?php
				$editor_name = 'coursepress_settings[basic_certificate][content]';
				$editor_id = 'coursepress_settings_basic_certificate_content';
				$editor_content = CoursePress_Core::get_setting(
					'basic_certificate/content',
					self::default_certificate_content()
				);

				$args = array(
					'textarea_name' => $editor_name,
					'textarea_rows' => 10,
					'wpautop' => true,
					'quicktags' => true,
				);
				wp_editor( $editor_content, $editor_id, $args );
				?>
				</td>
			</tr>

			<tr>
				<th><?php esc_html_e( 'Background Image', 'CP_TD' ); ?></th>
				<td>
				<div class="certificate_background_image_holder">
					<input class="image_url certificate_background_url"
						type="text"
						size="36"
						name="coursepress_settings[basic_certificate][background_image]"
						value="<?php echo esc_attr( $cert_background ); ?>"
						placeholder="<?php esc_attr_e( 'Add Image URL or Browse for Image', 'CP_TD' ); ?>" />
					<input class="certificate_background_button button-secondary"
						type="button"
						value="<?php esc_attr_e( 'Browse', 'CP_TD' ); ?>" />
					<div class="invalid_extension_message">
						<?php
						printf(
							esc_html__( 'Extension of the file is not valid. Please use one of the following: %s', 'CP_TD' ),
							implode( ', ', $allowed_extensions )
						);
						?>
					</div>
				</div>
				</td>
			</tr>

			<tr>
				<th>
					<?php esc_html_e( 'Content Padding', 'CP_TD' ); ?><br />
				</th>
				<td>
					<span><?php esc_html_e( 'Top', 'CP_TD' ); ?></span>
					<input type="text"
						size="6"
						style="width: 80px;"
						class="padding_top"
						name="coursepress_settings[basic_certificate][padding][top]"
						value="<?php echo esc_attr( $cert_padd_top ); ?>" />

					<span><?php esc_html_e( 'Bottom', 'CP_TD' ); ?></span>
					<input type="text"
						size="6"
						style="width: 80px;"
						class="padding_bottom"
						name="coursepress_settings[basic_certificate][padding][bottom]
						" value="<?php echo esc_attr( $cert_padd_bottom ); ?>" />

					<span><?php esc_html_e( 'Left', 'CP_TD' ); ?></span>
					<input type="text"
						size="6"
						style="width: 80px;"
						class="padding_left"
						name="coursepress_settings[basic_certificate][padding][left]"
						value="<?php echo esc_attr( $cert_padd_left ); ?>" />

					<span><?php esc_html_e( 'Right', 'CP_TD' ); ?></span>
					<input type="text"
						size="6"
						style="width: 80px;"
						class="padding_right"
						name="coursepress_settings[basic_certificate][padding][right]"
						value="<?php echo esc_attr( $cert_padd_right ); ?>" />
					<br />

					<span class="description">
						<?php esc_html_e( 'Can be any CSS units. E.g. "0.2em"', 'CP_TD' ); ?>
					</span>
				</td>
			</tr>

			<tr>
				<th>
					<?php esc_html_e( 'Page Orientation', 'CP_TD' ); ?><br />
				</th>
				<td>
					<select name="coursepress_settings[basic_certificate][orientation]"
						style="width: max-width: 200px;"
						id="cert_field_orientation">
						<option value="L" <?php selected( $cert_orientation, 'L' ); ?>>
							<?php esc_html_e( 'Landscape', 'CP_TD' ); ?>
						</option>
						<option value="P" <?php selected( $cert_orientation, 'P' ); ?>>
							<?php esc_html_e( 'Portrait', 'CP_TD' ); ?>
						</option>
					</select>
				</td>
			</tr>

			</tbody>
			</table>
		</div>
		</div>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	public static function default_certificate_content() {
		$msg = __(
			'%1$s %2$s
			has successfully completed the course

			%3$s

			Date: %4$s
			Certificate no.: %5$s', 'CP_TD'
		);

		$default_certification_content = sprintf(
			$msg,
			'FIRST_NAME',
			'LAST_NAME',
			'COURSE_NAME',
			'COMPLETION_DATE',
			'CERTIFICATE_NUMBER',
			'UNIT_LIST'
		);

		return $default_certification_content;
	}

	public static function default_email_subject() {
		return sprintf(
			__( '[%s] Congratulations. You passed your course.', 'CP_TD' ),
			get_option( 'blogname' )
		);
	}

	public static function default_email_content() {
		$msg = __(
			'Hi %1$s,

			Congratulations! You have completed the course: %2$s

			Please find attached your certificate of completion.'
			, 'CP_TD'
		);

		$default_basic_certificate_email = sprintf(
			$msg,
			'FIRST_NAME',
			'COURSE_NAME'
		);

		return get_option(
			'coursepress_basic_certificate_email_body',
			$default_basic_certificate_email
		);
	}

	public static function process_form( $page, $tab ) {
		if ( ! isset( $_POST['action'] ) ) { return; }
		if ( 'updateoptions' != $_POST['action'] ) { return; }
		if ( 'basic_certificate' != $tab ) { return; }
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) { return; }

		$settings = CoursePress_Core::get_setting( false ); // false: Get all settings.
		$post_settings = (array) $_POST['coursepress_settings'];

		// Sanitize $post_settings, especially to fix up unchecked checkboxes.
		if ( isset( $post_settings['basic_certificate']['enabled'] ) ) {
			$post_settings['basic_certificate']['enabled'] = true;
		} else {
			$post_settings['basic_certificate']['enabled'] = false;
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

	public static function remove_basic_certificate_email( $defaults ) {
		if ( isset( $defaults['basic_certificate'] ) ) {
			unset( $defaults['basic_certificate'] );
		}

		return $defaults;
	}
}
