<?php

/**
 * Editor helper class
 *
 * @since 2.0.6
 */

class CoursePress_Helper_Editor {
	/**
	 * Get Wp Editor.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $editor_id WP Editor ID
	 * @param string $editor_name WP Editor name
	 * @param string $editor_content Edited content.
     * @param array $args WP Editor args, see https://codex.wordpress.org/Function_Reference/wp_editor#Parameters
     * @param boolean $echo Print editor?
	 * @return string WP Editor.
	 */
    public static function get_wp_editor( $editor_id, $editor_name, $editor_content = '', $args = array(), $echo = false ) {
		wp_enqueue_script( 'post' );
		$_wp_editor_expand = $_content_editor_dfw = false;

		$post_type = CoursePress_Data_Course::get_post_type_name();
		global $is_IE;

		if (
			! wp_is_mobile()
			&& ! ( $is_IE && preg_match( '/MSIE [5678]/', $_SERVER['HTTP_USER_AGENT'] ) )
			&& apply_filters( 'wp_editor_expand', true, $post_type )
		) {

			wp_enqueue_script( 'editor-expand' );
			$_content_editor_dfw = true;
			$_wp_editor_expand = ( get_user_setting( 'editor_expand', 'on' ) === 'on' );
		}

		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}

		/** This filter is documented in wp-includes/class-wp-editor.php  */
		add_filter( 'teeny_mce_plugins', array( __CLASS__, 'teeny_mce_plugins' ) );

		$defaults = array(
			'_content_editor_dfw' => $_content_editor_dfw,
			'drag_drop_upload' => true,
			'tabfocus_elements' => 'content-html,save-post',
			'textarea_name' => $editor_name,
			'media_buttons' => false,
			'tinymce' => array(
				'resize' => false,
				'wp_autoresize_on' => $_wp_editor_expand,
                'add_unload_trigger' => false,
                'height' => 300,
            ),
        );
		$args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		ob_start();
		wp_editor( $editor_content, $editor_id, $args );
		$editor_html = sprintf( '<div class="postarea%s">', $_wp_editor_expand? ' wp-editor-expand':'' );
		$editor_html .= ob_get_clean();
        $editor_html .= '</div>';
        if ( !$echo ) {
            return $editor_html;
        }
        echo $editor_html;
    }

	/**
	 * Add tinymce plugins
	 *
	 * @since 2.0.5
	 *
	 * @param array $plugins An array of teenyMCE plugins.
	 * @return array The list of teenyMCE plugins.
	 */
	public static function teeny_mce_plugins( $plugins ) {
		$plugins[] = 'paste';
		//$plugins[] = 'wpautoresize'; it cause problem with very too tall tinyMCE editor 
		return $plugins;
    }

}

