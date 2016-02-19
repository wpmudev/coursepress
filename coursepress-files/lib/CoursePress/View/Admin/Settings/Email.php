<?php

class CoursePress_View_Admin_Settings_Email{

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_email', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_email', array( __CLASS__, 'return_content' ), 10, 3 );

	}


	public static function add_tabs( $tabs ) {

		$tabs['email'] = array(
			'title' => __( 'E-mail Settings', CoursePress::TD ),
			'description' => __( 'Setup the e-mail templates to be sent to users.', CoursePress::TD ),
			'order' => 10,
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

		$email_sections = CoursePress_Helper_Utility::sort_on_key( CoursePress_Helper_Settings_Email::get_settings_sections(), 'order' );
		$default_settings = CoursePress_Helper_Settings_Email::get_defaults();

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
								<th>' . esc_html__( 'From Name', CoursePress::TD ) . '</th>
								<td><input type="text" class="widefat" name="coursepress_settings[email][' . $key . '][from]" value="' . CoursePress_Core::get_setting( 'email/' . $key . '/from', $default_settings[ $key ]['from_name'] ) . '"/></td>
							</tr>
			';
			$content .= '
							<tr>
								<th>' . esc_html__( 'From Email', CoursePress::TD ) . '</th>
								<td><input type="text" class="widefat" name="coursepress_settings[email][' . $key . '][email]" value="' . CoursePress_Core::get_setting( 'email/' . $key . '/email', $default_settings[ $key ]['from_email'] ) . '"/></td>
							</tr>
			';
			$content .= '
							<tr>
								<th>' . esc_html__( 'Subject', CoursePress::TD ) . '</th>
								<td><input type="text" class="widefat" name="coursepress_settings[email][' . $key . '][subject]" value="' . CoursePress_Core::get_setting( 'email/' . $key . '/subject', $default_settings[ $key ]['subject'] ) . '"/></td>
							</tr>
			';
			$content .= '
							<tr>
								<th>
								' . esc_html__( 'Email Body', CoursePress::TD ) . '</th>
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

		return $content;

	}

	public static function process_form( $page, $tab ) {

		if ( isset( $_POST['action'] ) && 'updateoptions' === $_POST['action'] && 'email' === $tab && wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) {

			$settings = CoursePress_Core::get_setting( false ); // false returns all settings
			$post_settings = (array) $_POST['coursepress_settings'];

			$post_settings = CoursePress_Helper_Utility::sanitize_recursive( $post_settings );

			// Don't replace settings if there is nothing to replace
			if ( ! empty( $post_settings ) ) {
				CoursePress_Core::update_setting( false, CoursePress_Core::merge_settings( $settings, $post_settings ) ); // false will replace all settings
			}
		}

	}
}
