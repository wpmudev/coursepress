<?php

abstract class CoursePress_View_Admin_Setting_Setting {

	protected static $slug = '';

	/**
	 * Get one row of settings
	 *
	 * @since 2.0.0
	 *
	 * @param string $label Label of one row.
	 * @param string $content Content of one row.
	 * @param string $description Optional description.
	 *
	 * @return string Content part.
	 */
	protected static function row( $label, $content, $description = null ) {
		$row = '<tr valign="top">';
		$row .= sprintf( '<th scope="row">%s</th>', esc_html( $label ) );
		$row .= sprintf( '<td>%s', $content );
		if ( ! empty( $description ) ) {
			$row .= sprintf(
				'<p class="description">%s</p>',
				$description
			);
		}
		$row .= '</td></tr>';
		return $row;
	}

	/**
	 * Update form.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug current page
	 * @param string $tab current tab
	 *
	 */
	public static function process_form( $page, $tab ) {

		if ( isset( $_POST['action'] ) && 'updateoptions' === $_POST['action'] && wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) {

			$settings = CoursePress_Core::get_setting( false ); // false returns all settings
			$post_settings = (array) $_POST['coursepress_settings'];

			// Now is a good time to make changes to $post_settings, especially to fix up unchecked checkboxes
			$post_settings['general']['show_coursepress_menu'] = isset( $post_settings['general']['show_coursepress_menu'] ) ? $post_settings['general']['show_coursepress_menu'] : 'off';
			$post_settings['general']['use_custom_login'] = isset( $post_settings['general']['use_custom_login'] ) ? $post_settings['general']['use_custom_login'] : 'off';
			$post_settings['general']['redirect_after_login'] = isset( $post_settings['general']['redirect_after_login'] ) ? $post_settings['general']['redirect_after_login'] : 'off';
			$post_settings['instructor']['show_username'] = isset( $post_settings['instructor']['show_username'] ) ? $post_settings['instructor']['show_username'] : false;
			$post_settings['general']['add_structure_data'] = isset( $post_settings['general']['add_structure_data'] ) ? $post_settings['general']['add_structure_data'] : 'off';

			$post_settings = CoursePress_Helper_Utility::sanitize_recursive( $post_settings );

			// Don't replace settings if there is nothing to replace
			if ( ! empty( $post_settings ) ) {
				$new_settings = CoursePress_Core::merge_settings( $settings, $post_settings );
				CoursePress_Core::update_setting( false, $new_settings ); // false will replace all settings
				flush_rewrite_rules();
			}
		}

	}

	/**
	 * Open settings content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug current page
	 * @param string $tab current tab
	 *
	 * @return string Content part.
	 */
	protected static function page_start( $slug, $tab ) {

		$content = '<input type="hidden" name="page" value="' . esc_attr( $slug ) . '"/>';
		$content .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '"/>';
		$content .= '<input type="hidden" name="action" value="updateoptions"/>';
		$content .= wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false );
		return $content;
	}

	/**
	 * Open settings table.
	 *
	 * @since 2.0.0
	 *
	 * @return string Table open.
	 */
	protected static function table_start() {

		$content .= '<div class="inside">';
		$content .= '<table class="form-table"><tbody>';
		return $content;

	}

	/**
	 * Close settings table.
	 *
	 * @since 2.0.0
	 *
	 * @return string Table close.
	 */
	protected static function table_end() {

		return '</tbody></table></div>';

	}
}

