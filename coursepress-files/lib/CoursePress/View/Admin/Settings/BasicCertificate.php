<?php

class CoursePress_View_Admin_Settings_BasicCertificate{

	public static function init() {

		if ( ! CoursePress_Model_Capabilities::is_pro() ) {
			add_filter( 'coursepress_default_email_settings', array( __CLASS__, 'remove_basic_certificate_email' ) );
			add_filter( 'coursepress_default_email_settings_sections', array( __CLASS__, 'remove_basic_certificate_email' ) );
			return;
		}

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_basic_certificate', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_basic_certificate', array( __CLASS__, 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['basic_certificate'] = array(
			'title' => __( 'Basic Certificate', CoursePress::TD ),
			'description' => __( 'Setup the settings for the certificates issued upon course completion.', CoursePress::TD ),
			'order' => 40
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$content = '
			<input type="hidden" name="page" value="' . esc_attr( $slug ) .'"/>
			<input type="hidden" name="tab" value="' . esc_attr( $tab ) .'"/>
			<input type="hidden" name="action" value="updateoptions"/>
		' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false ) . '

		';

		// Enable Checkbox
		$content .= '
				<h3 class="hndle" style="cursor:auto;"><span>' . esc_html__( 'Certificate Options', CoursePress::TD ) . '</span></h3>
				<div class="inside">
					<table class="form-table compressed">
						<tbody id="items">
							<tr>
								<td><label><input type="checkbox" ' . checked( CoursePress_Core::get_setting('basic_certificate/enabled', 1 ), 1, false ) . ' name="coursepress_settings[basic_certificate][enabled]" value="1"> ' . esc_html__( 'Enable Basic Certificate', CoursePress::TD ) . '</label></td>
							</tr>
						</tbody>
					</table>
				</div>
			';

		// Certificate Layout
		$content .= '
				<h3 class="hndle" style="cursor:auto;"><span>' . esc_html__( 'Certificate Layout', CoursePress::TD ) . '</span></h3>
				<p class="description">' . esc_html__( 'Use the editor below to create the layout of your certificate.', CoursePress::TD ) . '</p>
				<p class="description">' . esc_html__( 'These codes will be replaced with actual data: FIRST_NAME, LAST_NAME, COURSE_NAME, COMPLETION_DATE, CERTIFICATE_NUMBER, UNIT_LIST', CoursePress::TD ) . '</p>
				<div class="inside">
					<table class="form-table compressed">
						<tbody id="items">
							<tr>
								<td colspan="2">';

		ob_start();
		$editor_name    = "coursepress_settings[basic_certificate][content]";
		$editor_id      = "coursepress_settings_basic_certificate_content";
		$editor_content = CoursePress_Core::get_setting('basic_certificate/content', self::default_certificate_content() );
		$args = array(
			"textarea_name" => $editor_name,
			"textarea_rows" => 10,
			'wpautop'       => true,
			'quicktags'     => true
		);
		wp_editor( $editor_content, $editor_id, $args );
		$content .= ob_get_clean();

		$supported_image_extensions = implode( ', ', CoursePress_Helper_Utility::get_image_extensions() );

		$content .= '
								</td>
							</tr>

							<tr>
								<th>' . esc_html__( 'Background Image', CoursePress::TD ) . '</th>
								<td>

								<div class="certificate_background_image_holder">
									<input class="image_url certificate_background_url" type="text" size="36" name="coursepress_settings[basic_certificate][background_image]" value="' . CoursePress_Core::get_setting('basic_certificate/background_image') . '" placeholder="' . esc_attr__( 'Add Image URL or Browse for Image', CoursePress::TD ) . '"/>
									<input class="certificate_background_button button-secondary" type="button" value="' . esc_attr__( 'Browse', CoursePress::TD ) .'"/>
									<div class="invalid_extension_message">' . sprintf( esc_html__( 'Extension of the file is not valid. Please use one of the following: %s', CoursePress::TD ), $supported_image_extensions ) . '</div>
								</div>
		                        <!-- <p class="description">' . sprintf( '%s', __( 'The image will be resized to fit the full page. For best results use 1:1414 as the ratio for image dimensions.<br /><strong>Examples Sizes:</strong><br />595x842px (Portrait 72dpi)<br />1754x1240px (Landscape 150dpi)<br />2480x3508px (Portrait 300dpi).', CoursePress::TD ) ) . '</p> -->
								</td>
							</tr>

							<tr>
								<th>
									' . esc_html__( 'Content Padding', CoursePress::TD ) . '<br />
								</th>
								<td>
									<span>' . esc_html__( 'Top', CoursePress::TD ) . '</span><input type="text" size="6" style="width: 80px;" class="padding_top" name="coursepress_settings[basic_certificate][padding][top]" value="' . CoursePress_Core::get_setting( 'basic_certificate/padding/top' ) . '" />
									<span>' . esc_html__( 'Bottom', CoursePress::TD ) . '</span><input type="text" size="6" style="width: 80px;" class="padding_bottom" name="coursepress_settings[basic_certificate][padding][bottom]" value="' . CoursePress_Core::get_setting( 'basic_certificate/padding/bottom' ) . '" />
									<span>' . esc_html__( 'Left', CoursePress::TD ) . '</span><input type="text" size="6" style="width: 80px;" class="padding_left" name="coursepress_settings[basic_certificate][padding][left]" value="' . CoursePress_Core::get_setting( 'basic_certificate/padding/left' ) . '" />
									<span>' . esc_html__( 'Right', CoursePress::TD ) . '</span><input type="text" size="6" style="width: 80px;" class="padding_right" name="coursepress_settings[basic_certificate][padding][right]" value="' . CoursePress_Core::get_setting( 'basic_certificate/padding/right' ) . '" /><br >
									<span class="description">' . esc_html__( 'Can be any CSS units. E.g. "0.2em"', CoursePress::TD ) . '</span>
								</td>
							</tr>

							<tr>
								<th>
									' . esc_html__( 'Page Orientation', CoursePress::TD ) . '<br />
								</th>
								<td>
									<select name="coursepress_settings[basic_certificate][orientation]" style="width: max-width: 200px;" id="cert_field_orientation">
										<option value="L" ' . selected( CoursePress_Core::get_setting( 'basic_certificate/orientation', 'L' ), 'L', false ) . '>' . esc_html__( 'Landscape', CoursePress::TD ) . '</option>
										<option value="P" ' . selected( CoursePress_Core::get_setting( 'basic_certificate/orientation', 'L' ), 'P', false ) . '>' . esc_html__( 'Portrait', CoursePress::TD ) . '</option>
									</select>
								</td>
							</tr>

						</tbody>
					</table>
				</div>
			';


		return $content;

	}

	public static function default_certificate_content() {
		// Certificate Content
		$fields = array(
			'FIRST_NAME',
			'LAST_NAME',
			'COURSE_NAME',
			'COMPLETION_DATE',
			'CERTIFICATE_NUMBER',
			'UNIT_LIST',
		);

		$default_certification_content = sprintf( __( '%1$s %2$s
				has successfully completed the course

				%3$s

				Date: %4$s
				Certificate no.: %5$s', CoursePress::TD ), $fields[0], $fields[1], $fields[2], $fields[3], $fields[4] );
		return $default_certification_content;
	}

	public static function default_email_subject() {
		return sprintf( __( '[%s] Congratulations. You passed your course.', CoursePress::TD ), get_option( 'blogname' ) );
	}

	public static function default_email_content() {

		$default_basic_certificate_email = sprintf( __(
			'Hi %1$s,

				Congratulations! You have completed the course: %2$s

				Please find attached your certificate of completion.'
			, CoursePress::TD ), 'FIRST_NAME', 'COURSE_NAME'
		);

		return get_option( 'coursepress_basic_certificate_email_body', $default_basic_certificate_email );
	}

	public static function process_form( $page, $tab ) {

		if ( isset( $_POST['action'] ) && 'updateoptions' === $_POST['action'] && 'basic_certificate' === $tab && wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) {

			$settings      = CoursePress_Core::get_setting( false ); // false returns all settings
			$post_settings = (array) $_POST['coursepress_settings'];

			// Now is a good time to make changes to $post_settings, especially to fix up unchecked checkboxes
			$post_settings['basic_certificate']['enabled'] = isset( $post_settings['basic_certificate']['enabled'] ) ? : false;
			//$post_settings['general']['use_custom_login'] = isset($post_settings['general']['use_custom_login']) ? : false;
			//$post_settings['general']['redirect_after_login'] = isset($post_settings['general']['redirect_after_login']) ? : false;
			//$post_settings['instructor']['show_username'] = isset( $post_settings['instructor']['show_username'] ) ? $post_settings['instructor']['show_username'] : false;

			$post_settings = CoursePress_Helper_Utility::sanitize_recursive( $post_settings );

			// Don't replace settings if there is nothing to replace
			if ( ! empty( $post_settings ) ) {
				CoursePress_Core::update_setting( false, CoursePress_Core::merge_settings( $settings, $post_settings ) ); // false will replace all settings
			}

		}

	}

	public static function remove_basic_certificate_email( $defaults ) {

		if( isset( $defaults['basic_certificate'] ) ) {
			unset( $defaults['basic_certificate'] );
		}

		return $defaults;
	}

}