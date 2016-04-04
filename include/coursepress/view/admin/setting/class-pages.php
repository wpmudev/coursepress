<?php

class CoursePress_View_Admin_Setting_Pages {

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_pages', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_pages', array( __CLASS__, 'return_content' ), 10, 3 );
	}


	public static function add_tabs( $tabs ) {

		$tabs['pages'] = array(
			'title' => __( 'Pages', 'CP_TD' ),
			'description' => __( 'Configure the pages for CoursePress.', 'CP_TD' ),
			'order' => 1,
		);

		return $tabs;
	}

	public static function return_content( $content, $slug, $tab ) {

		$my_course_prefix = __( 'my-course', 'CP_TD' );
		$my_course_prefix = sanitize_text_field( CoursePress_Core::get_setting( 'slugs/course', 'courses' ) ) . '/'. $my_course_prefix;
		$page_dropdowns = array();

		$pages_args = array(
			'selected' => CoursePress_Core::get_setting( 'pages/enrollment', 0 ),
			'echo' => 0,
			'show_option_none' => __( 'Use virtual page', 'CP_TD' ),
			'option_none_value' => 0,
			'name' => 'coursepress_settings[pages][enrollment]',
		);
		$page_dropdowns['enrollment'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/login', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][login]';
		$page_dropdowns['login'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/signup', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][signup]';
		$page_dropdowns['signup'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/student_dashboard', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][student_dashboard]';
		$page_dropdowns['student_dashboard'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/student_settings', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][student_settings]';
		$page_dropdowns['student_settings'] = wp_dropdown_pages( $pages_args );

		$content = '
			<input type="hidden" name="page" value="' . esc_attr( $slug ) . '"/>
			<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '"/>
			<input type="hidden" name="action" value="updateoptions"/>
			' . wp_nonce_field( 'update-coursepress-options', '_wpnonce', true, false );
		$content .= '<div class="inside"><table class="form-table slug-settings"><tbody>';
		/**
		 * Student Dashboard Page
		 */
		$content .= '<tr valign="top">
			<th scope="row">' . esc_html__( 'Student Dashboard Page', 'CP_TD' ) . '</th>
			<td>' .
			$page_dropdowns['student_dashboard'] .
			'<p class="description">' . __( 'Select page where student can view courses.', 'CP_TD' ) . '</p>
			</td>
			</tr>';
		/**
		 * Student Settings Page
		 */
		$content .= '<tr valign="top">
			<th scope="row">' . esc_html__( 'Student Settings Page', 'CP_TD' ) . '</th>
			<td>' .
			$page_dropdowns['student_settings'] .
			'<p class="description">' . __( 'Select page where student can change accont settings.', 'CP_TD' ) . '</p>
			</td>
			</tr>';

		$content .= '</tbody></table></div>';
		return $content;

	}

	public static function process_form( $page, $tab ) {

		if ( isset( $_POST['action'] ) && 'updateoptions' === $_POST['action'] && 'pages' === $tab && wp_verify_nonce( $_POST['_wpnonce'], 'update-coursepress-options' ) ) {

			$settings = CoursePress_Core::get_setting( false ); // false returns all settings
			$post_settings = (array) $_POST['coursepress_settings'];

			$post_settings = CoursePress_Helper_Utility::sanitize_recursive( $post_settings );

			// Don't replace settings if there is nothing to replace
			if ( ! empty( $post_settings ) ) {
				$new_settings = CoursePress_Core::merge_settings( $settings, $post_settings );

				CoursePress_Core::update_setting( false, $new_settings ); // false will replace all settings
			}
		}

	}
}
