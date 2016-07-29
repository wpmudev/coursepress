<?php

class CoursePress_View_Admin_Setting_Email {

	public static function init() {
		add_filter(
			'coursepress_settings_tabs',
			array( __CLASS__, 'add_tabs' )
		);
		add_action(
			'coursepress_settings_process_email',
			array( __CLASS__, 'process_form' ),
			10, 2
		);
		add_filter(
			'coursepress_settings_render_tab_email',
			array( __CLASS__, 'return_content' ),
			10, 3
		);
	}

	public static function add_tabs( $tabs ) {
		$tabs['email'] = array(
			'title' => __( 'E-mail Settings', 'cp' ),
			'description' => __( 'Setup the e-mail templates to be sent to users.', 'cp' ),
			'order' => 10,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {
		$content = '
			<input type="hidden" name="page" value="' . esc_attr( $slug ) .'"/>
			<input type="hidden" name="tab" value="' . esc_attr( $tab ) .'"/>
			<input type="hidden" name="action" value="updateoptions"/>
		' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false );

		$email_sections = CoursePress_Helper_Utility::sort_on_key(
			CoursePress_Helper_Setting_Email::get_settings_sections(),
			'order'
		);

		$default_settings = CoursePress_Helper_Setting_Email::get_defaults();

		foreach ( $email_sections as $key => $section ) {
			$content .= '<h3>' . esc_html( $section['title'] ) . '</h3>';
			if ( ! empty( $section['description'] ) ) {
				$content .= '<p class="description">' . esc_html( $section['description'] ) . '</p>';
			}
			$content .= '
				<div class="inside">
					<table class="form-table compressed email-fields">
						<tbody id="items">';

			$content .= '
							<tr>
								<th>' . esc_html__( 'From Name', 'cp' ) . '</th>
								<td><input type="text" class="widefat" name="coursepress_settings[email][' . $key . '][from]" value="' . CoursePress_Core::get_setting( 'email/' . $key . '/from', $default_settings[ $key ]['from'] ) . '"/></td>
							</tr>
			';
			$content .= '
							<tr>
								<th>' . esc_html__( 'From Email', 'cp' ) . '</th>
								<td><input type="text" class="widefat" name="coursepress_settings[email][' . $key . '][email]" value="' . CoursePress_Core::get_setting( 'email/' . $key . '/email', $default_settings[ $key ]['email'] ) . '"/></td>
							</tr>
			';
			$content .= '
							<tr>
								<th>' . esc_html__( 'Subject', 'cp' ) . '</th>
								<td><input type="text" class="widefat" name="coursepress_settings[email][' . $key . '][subject]" value="' . CoursePress_Core::get_setting( 'email/' . $key . '/subject', $default_settings[ $key ]['subject'] ) . '"/></td>
							</tr>
			';
			$content .= '
							<tr>
								<th>
								' . esc_html__( 'Email Body', 'cp' ) . '</th>
								<td>
								<p class="description">' . esc_html( $section['content_help_text'] ) . '</p>';

			ob_start();
			$editor_name = 'coursepress_settings[email][' . $key . '][content]';
			$editor_id = 'coursepress_settings_email_' . $key . '_content';
			$editor_content = stripslashes( CoursePress_Core::get_setting( 'email/' . $key . '/content', $default_settings[ $key ]['content'] ) );

			$args = array( 'textarea_name' => $editor_name, 'textarea_rows' => 10, 'wpautop' => true );
			$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
			wp_editor( $editor_content, $editor_id, $args );

			$content .= ob_get_clean();

			$content .= '       <br /></td>
							</tr>
			';
			$content .= '
						</tbody>
					</table>
				</div>
			';
		}

		/**
		 * Add this hook for now until layout is fixed.
		 **/
		$content .= apply_filters( 'coursepress_email_settings_sections', $email_sections );

		return $content;
	}

	public static function process_form( $page, $tab ) {
		if ( empty( $_POST['_wpnonce'] ) ) { return; }
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) { return; }
		if ( empty( $_POST['action'] ) ) { return; }
		if ( 'updateoptions' != $_POST['action'] ) { return; }
		if ( 'email' != $tab ) { return; }

		$settings = CoursePress_Core::get_setting( false );
		$post_settings = (array) $_POST['coursepress_settings'];
		$post_settings = CoursePress_Helper_Utility::sanitize_recursive( $post_settings );

		// Don't replace settings if there is nothing to replace.
		if ( ! empty( $post_settings ) ) {
			CoursePress_Core::update_setting(
				false, // false .. replace all settings.
				CoursePress_Core::merge_settings( $settings, $post_settings )
			);
		}
	}
}
