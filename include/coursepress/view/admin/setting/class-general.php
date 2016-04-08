<?php

require_once dirname( __FILE__ ) . '/class-settings.php';

class CoursePress_View_Admin_Setting_General extends CoursePress_View_Admin_Setting_Setting {

	public static function init() {

		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );
		add_action( 'coursepress_settings_process_general', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_general', array( __CLASS__, 'return_content' ), 10, 3 );

	}

	public static function add_tabs( $tabs ) {

		self::$slug = 'general';

		$tabs[ self::$slug ] = array(
			'title' => __( 'General Settings', 'CP_TD' ),
			'description' => __( 'Configure the general settings for CoursePress.', 'CP_TD' ),
			'order' => 0,// first tab
		);

		return $tabs;

	}

	public static function return_content( $content, $slug, $tab ) {

		$content .= self::page_start( $slug, $tab );

		/**
		 * Messaging
		 */
		if ( function_exists( 'messaging_init' ) ) {
			$content .= self::table_start( __( 'Messaging', 'CP_TD' ) );
			$content .= self::row(
				__( 'Messaging: Inbox Slug', 'CP_TD' ),
				trailingslashit( esc_url( home_url() ) ) . '&nbsp;<input type="text" name="coursepress_settings[slugs][inbox]" id="inbox_slug" value="' . esc_attr( CoursePress_Core::get_setting( 'slugs/inbox', 'student-inbox' ) ) . '" />&nbsp;/'
			);

			$content .= self::row(
				__( 'Sent Messages Slug', 'CP_TD' ).
				trailingslashit( esc_url( home_url() ) ) . '&nbsp;<input type="text" name="coursepress_settings[slugs][sent_messages]" id="sent_messages" value="' . esc_attr( CoursePress_Core::get_setting( 'slugs/sent_messages', 'student-sent-messages' ) ) . '" />&nbsp;/'
			);

			$content .= self::row(
				__( 'New Messages Slug', 'CP_TD' ),
				trailingslashit( esc_url( home_url() ) ) . '&nbsp;<input type="text" name="coursepress_settings[slugs][new_messages]" id="new_messages_slug" value="' . esc_attr( CoursePress_Core::get_setting( 'slugs/new_messages', 'student-new-message' ) ) . '" />&nbsp;/'
			);
			$content .= self::table_end();
		}

		/**
		 * General
		 */
		$content .= self::table_start( __( 'General', 'CP_TD' ) );

		/**
		 * Course Payments
		 */
		$options = CoursePress_Helper_Setting::get_course_payment_options_array();
		$selected = CoursePress_Helper_Setting::get_course_payment_options_selected_value( $options );
		$options = self::radio( 'general', 'course_payment', $options, $selected );
		$content .= self::row(
			__( 'Courses Payments', 'CP_TD' ),
			$options
		);

		/**
		 * Direction
		 */
		$selected_dir = CoursePress_Core::get_setting( 'course/order_by_direction', 'DESC' );
		$content .= self::row(
			__( 'Direction', 'CP_TD' ),
			'<select name="coursepress_settings[course][order_by_direction]" id="course_order_by_direction"><option value="DESC" ' . selected( $selected_dir, 'DESC', false ) .'>' . __( 'Descending', 'CP_TD' ) . '</option><option value="ASC" ' . selected( $selected_dir, 'ASC', false ) .'>' . __( 'Ascending', 'CP_TD' ) . '</option></select>'
		);
		$checked = cp_is_true( CoursePress_Core::get_setting( 'general/show_coursepress_menu', 1 ) ) ? 'checked' : '';
		$content .= self::row(
			__( 'Theme Menu Items', 'CP_TD' ),
			'<label><input type="checkbox" name="coursepress_settings[general][show_coursepress_menu]" ' . $checked  . ' /> '.__( 'Display Menu Items', 'CP_TD' ) . '</label>',
			__( 'Attach default CoursePress menu items ( Courses, Student Dashboard, Log Out ) to the <strong>Primary Menu</strong>. Items can also be added from Appearance &gt; Menus and the CoursePress panel.', 'CP_TD' )
		);

		if ( current_user_can( 'manage_options' ) ) {
			$menu_error = true;
			$locations = get_theme_mod( 'nav_menu_locations' );
			if ( is_array( $locations ) ) {
				foreach ( $locations as $location => $value ) {
					if ( $value > 0 ) {
						$menu_error = false; // at least one is defined
					}
				}
			}
			if ( $menu_error ) {

				$content .= self::row(
					'&nbsp;',
					'<span class="settings-error">' . __( 'Please add at least one menu and select its theme location in order to show CoursePress menu items automatically.', 'CP_TD' ) . '</span>'
				);

			}
		}

		$content .= self::table_end();

		/**
		 * Login Form
		 */
		$content .= self::table_start( __( 'Login Form', 'CP_TD' ) );

		$checked = cp_is_true( CoursePress_Core::get_setting( 'general/use_custom_login', 1 ) ) ? 'checked' : '';
		$content .= self::row(
			__( 'Custom Login Form', 'CP_TD' ),
			'<label><input type="checkbox" name="coursepress_settings[general][use_custom_login]" ' . $checked  . ' /> '.__( 'Use Custom Login Form', 'CP_TD' ).'</label>',
			__( 'Uses a custom Login Form to keep students on the front-end of your site.', 'CP_TD' )
		);

		$checked = cp_is_true( CoursePress_Core::get_setting( 'general/redirect_after_login', 1 ) ) ? 'checked' : '';
		$content .= self::row(
			__( 'WordPress Login Redirect', 'CP_TD' ),
			'<Label><input type="checkbox" name="coursepress_settings[general][redirect_after_login]" ' . $checked  . ' /> ' . __( 'Redirect After Login', 'CP_TD' ) . '</label>',
			__( 'Redirect students to their Dashboard upon login via wp-login form.', 'CP_TD' )
		);

		$content .= self::table_end();

		$content .= self::table_start(
			__( 'Course Details Page', 'CP_TD' ),
			__( 'Media to use when viewing course details.', 'CP_TD' )
		);

		$selected_type = CoursePress_Core::get_setting( 'course/details_media_type', 'default' );
		$content .= self::row(
			__( 'Media Type', 'CP_TD' ),
			'<select name="coursepress_settings[course][details_media_type]" id="course_details_media_type"><option value="default" ' . selected( $selected_type, 'default', false ) .'>' . __( 'Priority Mode (default)', 'CP_TD' ) . '</option><option value="video" ' . selected( $selected_type, 'video', false ) .'>' . __( 'Featured Video', 'CP_TD' ) . '</option><option value="image" ' . selected( $selected_type, 'image', false ) .'>' . __( 'List Image', 'CP_TD' ) . '</option></select>',
			__( '"Priority" - Use the media type below, with the other type as a fallback.', 'CP_TD' )
		);

		$selected_priority = CoursePress_Core::get_setting( 'course/details_media_priority', 'default' );
		$content .= self::row(
			__( 'Priority', 'CP_TD' ),
			'<select name="coursepress_settings[course][details_media_priority]" id="course_details_media_priority"><option value="video" ' . selected( $selected_priority, 'video', false ) .'>' . __( 'Featured Video (image fallback)', 'CP_TD' ) . '</option><option value="image" ' . selected( $selected_priority, 'image', false ) .'>' . __( 'List Image (video fallback)', 'CP_TD' ) . '</option></select>',
			__( 'Example: Using "video", the featured video will be used if available. The listing image is a fallback.', 'CP_TD' )
		);
		$content .= self::table_end();

		$content .= self::table_start(
			__( 'Course Listings', 'CP_TD' ),
			__( 'Media to use when viewing course listings (e.g. Courses page or Instructor page).', 'CP_TD' )
		);

		$selected_type = CoursePress_Core::get_setting( 'course/listing_media_type', 'default' );
		$content .= self::row(
			__( 'Media Type', 'CP_TD' ),
			'<select name="coursepress_settings[course][listing_media_type]" id="course_listing_media_type"><option value="default" ' . selected( $selected_type, 'default', false ) .'>' . __( 'Priority Mode (default)', 'CP_TD' ) . '</option><option value="video" ' . selected( $selected_type, 'video', false ) .'>' . __( 'Featured Video', 'CP_TD' ) . '</option><option value="image" ' . selected( $selected_type, 'image', false ) .'>' . __( 'List Image', 'CP_TD' ) . '</option></select>',
			__( '"Priority" - Use the media type below, with the other type as a fallback.', 'CP_TD' )
		);

		$selected_priority = CoursePress_Core::get_setting( 'course/listing_media_priority', 'default' );
		$content .= self::row(
			__( 'Priority', 'CP_TD' ),
			'<select name="coursepress_settings[course][listing_media_priority]" id="course_listing_media_priority"><option value="video" ' . selected( $selected_priority, 'video', false ) .'>' . __( 'Featured Video (image fallback)', 'CP_TD' ) . '</option><option value="image" ' . selected( $selected_priority, 'image', false ) .'>' . __( 'List Image (video fallback)', 'CP_TD' ) . '</option></select>',
			__( 'Example: Using "video", the featured video will be used if available. The listing image is a fallback.', 'CP_TD' )
		);

		$content .= self::table_end();

		/**
		 * COURSE IMAGES
		 */
		$content .= self::table_start(
			__( 'Course Images', 'CP_TD' ),
			__( 'Size for (newly uploaded) course images.', 'CP_TD' )
		);
		$content .= self::row(
			__( 'Image Width', 'CP_TD' ),
			'<input type="number" name="coursepress_settings[course][image_width]" value="' . esc_attr( CoursePress_Core::get_setting( 'course/image_width', 235 ) ) . '"/>'
		);
		$content .= self::row(
			__( 'Image Height', 'CP_TD' ),
			'<input type="number" name="coursepress_settings[course][image_height]" value="' . esc_attr( CoursePress_Core::get_setting( 'course/image_height', 225 ) ) . '"/>'
		);
		$content .= self::table_end();

		/**
		 * Course Order
		 */
		$content .= self::table_start(
			__( 'Course Order', 'CP_TD' ),
			__( 'Order of courses in admin and on front. If you choose "Post Order Number", you will have option to reorder courses from within the Courses admin page.', 'CP_TD' )
		);

		$selected_order = CoursePress_Core::get_setting( 'course/order_by', 'post_date' );
		$content .= self::row(
			__( 'Order by', 'CP_TD' ),
			' <select name="coursepress_settings[course][order_by]" id="course_order_by"><option value="post_date" ' . selected( $selected_order, 'post_date', false ) .'>' . __( 'Post Date', 'CP_TD' ) . '</option><option value="course_order" ' . selected( $selected_order, 'course_order', false ) .'>' . __( 'Post Order Number', 'CP_TD' ) . '</option></select>'
		);

		$selected_dir = CoursePress_Core::get_setting( 'course/order_by_direction', 'DESC' );
		$content .= self::row(
			__( 'Direction', 'CP_TD' ),
			'<select name="coursepress_settings[course][order_by_direction]" id="course_order_by_direction"><option value="DESC" ' . selected( $selected_dir, 'DESC', false ) .'>' . __( 'Descending', 'CP_TD' ) . '</option><option value="ASC" ' . selected( $selected_dir, 'ASC', false ) .'>' . __( 'Ascending', 'CP_TD' ) . '</option></select>'
		);

		$content .= self::table_end();

		/**
		 * REPORTS
		 */
		$content .= self::table_start(
			__( 'Reports', 'CP_TD' ),
			__( 'Select font which will be used in the PDF reports.', 'CP_TD' )
		);

		$reports_font = CoursePress_Core::get_setting( 'reports/font', 'helvetica' );
		$reports_font = empty( $reports_font ) ? 'helvetica' : $reports_font;
		$fonts = CoursePress_Helper_PDF::fonts();
		$font_content = '<select name="coursepress_settings[reports][font]" id="course_order_by_direction">';
		foreach ( $fonts as $font_php => $font_name ) {
			if ( ! empty( $font_name ) ) {
				$font = str_replace( '.php', '', $font_php );
				$font_content .= sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $font ),
					selected( $reports_font, $font, false ),
					esc_html( $font_name )
				);
			}
		}
		$font_content .= ' </select>';
		$content .= self::row(
			__( 'Font', 'CP_TD' ),
			$font_content
		);

		$content .= self::table_end();

		/**
		 * Other Settings
		 */
		$content .= self::table_start( __( 'Other', 'CP_TD' ) );

		/**
		 * instructor Privacy
		 */
		$checked = cp_is_true( CoursePress_Core::get_setting( 'instructor/show_username', 1 ) ) ? 'checked' : '';
		$content .= self::row(
			__( 'Privacy', 'CP_TD' ),
			'<label><input type="checkbox" name="coursepress_settings[instructor][show_username]" ' . $checked  . ' /> ' . __( 'Show Instructor Username in URL', 'CP_TD' ) . '</label>',
			__( 'If checked, instructors username will be shown in the url. Otherwise, hashed (MD5) version will be shown.', 'CP_TD' )
		);
		/**
		 * schema.org
		 */
		$content .= self::row(
			__( 'schema.org', 'CP_TD' ),
			sprintf(
				'<Label><input type="checkbox" name="coursepress_settings[general][add_structure_data]" %s /> %s</Label>',
				cp_is_true( CoursePress_Core::get_setting( 'general/add_structure_data', 1 ) ) ? 'checked' : '',
				esc_html__( 'Add microdata syntax.', 'CP_TD' )
			),
			esc_html__( 'Add structure data to courses.', 'CP_TD' )
		);

		$content .= self::table_end();

		return $content;

	}
}
