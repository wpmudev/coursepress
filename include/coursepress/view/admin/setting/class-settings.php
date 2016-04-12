<?php

abstract class CoursePress_View_Admin_Setting_Setting {

	/**
	 * Slug of current tab.
	 *
	 * @since 2.0.0
	 * @var string $slug Slug of current tab.
	 */
	protected static $slug = '';

	/**
	 * Allowed tags.
	 *
	 * @since 2.0.0
	 * @var array $allowed_tags Array of allowed tags for wp_kses() function.
	 */
	protected static $allowed_tags;

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
			$content .= self::_add_description( $description );
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
	 * @param string $title If exist, then add title before table open.
	 *
	 * @return string Table open.
	 */
	protected static function table_start( $title = null, $description = null ) {

		$content = '';
		if ( ! empty( $title ) ) {
			$content .= sprintf(
				'<h3 class="hndle" style="cursor:auto;"><span>%s</span></h3>',
				esc_html( $title )
			);
			if ( ! empty( $description ) ) {
				$content .= self::_add_description( $description );
			}
		}
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

	/**
	 * Produce list with radio option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $group Group of settings.
	 * @param string $name Name of settings.
	 * @param array $options Array of options.
	 * @param string $selected Selected item.
	 *
	 * @return string List of radio.
	 */
	protected static function radio( $group, $name, $options, $selected = '' ) {

		$content = '<ul>';
		foreach ( $options as $key => $data ) {
			$content .= '<li>';
			$content .= sprintf(
				'<label><input type="radio" name="coursepress_settings[%s][%s]" %s %s value="%s" /> %s</label>',
				$group,
				$name,
				checked( $selected, $key, false ),
				isset( $data['disabled'] ) && 'disabled' == $data['disabled'] ? 'disabled="disabled"' : '',
				esc_attr( $key ),
				$data['label']
			);
			if ( isset( $data['description'] ) ) {
				$content .= self::_add_description( $data['description'] );
			}
			$content .= '</li>';
		}
		$content .= '</ul>';
		return $content;

	}

	private static function _add_description( $description ) {

		if ( empty( self::$allowed_tags ) ) {
			self::$allowed_tags = wp_kses_allowed_html( 'post' );
		}
		return  sprintf(
			'<p class="description">%s</p>',
			wp_kses( $description, self::$allowed_tags )
		);

	}
}

