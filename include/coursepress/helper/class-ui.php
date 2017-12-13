<?php

class CoursePress_Helper_UI {


	public static function browse_media_field( $id, $name, $args = array() ) {

		if ( ! $name ) {
			$name = $id;
		}

		$args['title'] = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		$args['container_class'] = isset( $args['container_class'] ) ? sanitize_text_field( $args['container_class'] ) : 'wide';
		$args['textbox_class'] = isset( $args['textbox_class'] ) ? sanitize_text_field( $args['textbox_class'] ) : 'medium';
		$args['title'] = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		$args['value'] = isset( $args['value'] ) ? sanitize_text_field( $args['value'] ) : '';
		$args['placeholder'] = isset( $args['placeholder'] ) ? sanitize_text_field( $args['placeholder'] ) : __( 'Add Media URL or Browse for Media', 'CP_TD' );
		$args['button_text'] = isset( $args['button_text'] ) ? sanitize_text_field( $args['button_text'] ) : __( 'Browse', 'CP_TD' );
		$args['type'] = isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : 'image';
		$args['invalid_message'] = isset( $args['invalid_message'] ) ? sanitize_text_field( $args['invalid_message'] ) : '';
		$args['description'] = isset( $args['description'] ) ? sanitize_text_field( $args['description'] ) : '';

		if ( 'image' === $args['type'] ) {
			$supported_extensions = implode( ', ', CoursePress_Helper_Utility::get_image_extensions() );
		}
		if ( 'audio' === $args['type'] ) {
			$supported_extensions = implode( ', ', wp_get_video_extensions() );
		}
		if ( 'video' === $args['type'] ) {
			$supported_extensions = implode( ', ', wp_get_audio_extensions() );
		}

		$content = '
		<div class="' . $args['container_class'] . '">
			<label for="' . $name . '">' .
			esc_html( $args['title'] );

		if ( ! empty( $args['description'] ) ) {
			$content .= '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}

		$content .= '
			</label>
			<input class="' . $args['textbox_class'] . ' ' . $args['type'] . '_url" type="text" name="' . $name . '" id="' . $name . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $args['value'] ) . '"/>
			<input class="button browse-media-field" type="button" name="' . $name . '-button" value="' . esc_attr( $args['button_text'] ) . '"/>
			<div class="invalid_extension_message">' . sprintf( esc_html__( 'Extension of the file is not valid. Please use one of the following: %s', 'CP_TD' ), $supported_extensions ) . '</div>
		</div>';

		return $content;

	}

	public static function get_user_avatar_array( $args = array(), $meta_options = array() ) {

		// meta_key => role_ins
		// meta_value => 'instructor'
		$args_default = array(
			'meta_key' => ( isset( $meta_options['meta_key'] ) ? $meta_options['meta_key'] : '' ),
			'meta_value' => ( isset( $meta_options['meta_value'] ) ? $meta_options['meta_value'] : '' ),
			'meta_compare' => '',
			'meta_query' => array(),
			'include' => array(),
			'exclude' => array(),
			'orderby' => 'display_name',
			'order' => 'ASC',
			'offset' => '',
			'search' => '',
			'number' => '',
			'count_total' => false,
			'fields' => array( 'display_name', 'ID' ),
			'who' => '',
		);

		$args = CoursePress_Helper_Utility::merge_distinct( $args, $args_default );

		if ( defined( 'COURSEPRESS_INSTRUCTOR_ROLE' ) ) {

			$args['role'] = COURSEPRESS_INSTRUCTOR_ROLE;

			$users = get_users( $args );

			$user_avatars = array();
			foreach ( $users as $user ) {
				$user_avatars[ $user->ID ] = str_replace( "'", '"', get_avatar( $user->ID, 80, '', $user->display_name ) );
			}
		}

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$user_avatars['default'] = str_replace( "'", '"', get_avatar( 0, 80, '', '', array( 'force_default' => true ) ) );

		return $user_avatars;

	}

	public static function get_course_dropdown( $id, $name, $courses = false, $options = array() ) {

		if ( false === $courses ) {
			$courses = get_posts( 'post_type=' . CoursePress_Data_Course::get_post_type_name() );
		}
		$courses = array_filter( $courses );

		$content = '';
		$content .= '<select name="' . $name . '" id="' . $id . '"';
		$content .= isset( $options['placeholder'] ) ? ' data_placeholder="' . esc_attr( $options['placeholder'] ) . '" ' : '';
		$content .= isset( $options['class'] ) ? ' class="' . esc_attr( $options['class'] ) . '" ' : '';
		$content .= '>';

		$value = isset( $options['value'] ) ? $options['value'] : false;

		$first_option = isset( $options['first_option'] ) ? (array) $options['first_option'] : false;
		$selected = ! empty( $first_option ) && false !== $value ? selected( $value, $first_option['value'], false ) : '';
		$content .= ! empty( $first_option ) ? '<option value="' . $first_option['value'] . '" ' . $selected . '>' . esc_html( $first_option['text'] ) . '</option>' : '';

		foreach ( $courses as $course ) {
			$selected = false !== $value ? selected( $value, $course->ID, false ) : '';
			$content .= '<option value="' . $course->ID . '" ' . $selected . '>';
			$content .= apply_filters( 'the_title', $course->post_title, $course->ID );
			$content .= '</option>';
		}

		$content .= '</select>';

		return $content;
	}

	public static function get_unit_dropdown( $id, $name, $course_id, $units = false, $options = array() ) {
		if ( false === $units && 'all' !== $course_id ) {
			$units = get_posts( array(
				'post_type' => CoursePress_Data_Unit::get_post_type_name(),
				'post_parent' => $course_id,
			) );
		}

		// Sort units
		if ( 'all' !== $course_id ) {
			foreach ( $units as $unit ) {
				$unit->unit_order = (int) get_post_meta( $unit->ID, 'unit_order', true );
			}
			$units = CoursePress_Helper_Utility::sort_on_object_key( $units, 'unit_order' );
		}

		$content = '';
		$content .= '<select name="' . $name . '" id="' . $id . '"';
		$content .= isset( $options['placeholder'] ) ? ' data_placeholder="' . esc_attr( $options['placeholder'] ) . '" ' : '';
		$content .= isset( $options['class'] ) ? ' class="' . esc_attr( $options['class'] ) . '" ' : '';
		$content .= '>';

		$value = isset( $options['value'] ) ? $options['value'] : false;

		$first_option = isset( $options['first_option'] ) ? (array) $options['first_option'] : false;
		$selected = ! empty( $first_option ) && false !== $value ? selected( $value, $first_option['value'], false ) : '';
		$content .= ! empty( $first_option ) ? '<option value="' . $first_option['value'] . '" ' . $selected . '>' . esc_html( $first_option['text'] ) . '</option>' : '';

		if ( 'all' !== $course_id ) {
			foreach ( $units as $unit ) {
				$selected = false !== $value ? selected( $value, $unit->ID, false ) : '';
				$content .= '<option value="' . $unit->ID . '" ' . $selected . '>' . $unit->post_title . '</option>';
			}
		}

		$content .= '</select>';

		return $content;

	}

	public static function get_discussion_module_dropdown( $id, $name, $unit_id, $units_and_modules, $options = array() ) {
		if ( empty( $unit_id ) || empty( $units_and_modules ) ) {
			return '';
		}

		$content = '';
		$content .= '<select name="' . $name . '" id="' . $id . '"';
		$content .= isset( $options['placeholder'] ) ? ' data_placeholder="' . esc_attr( $options['placeholder'] ) . '" ' : '';
		$content .= isset( $options['class'] ) ? ' class="' . esc_attr( $options['class'] ) . '" ' : '';
		$content .= '>';

		$value = isset( $options['value'] ) ? $options['value'] : false;

		$discussions = array();
		$unit = $units_and_modules[ $unit_id ];
		foreach ( $unit['pages'] as $page ) {
			foreach ( $page['modules'] as $module ) {

				$meta = CoursePress_Data_Module::attributes( $module );
				if ( CoursePress_Helper_UI_Module::OUTPUT_DISCUSSION != $meta['module_type'] ) {
					continue;
				}

				$discussions[ $module->ID ] = $module->post_title;
			}
		}

		$first_discussion = array_keys( $discussions );
		$first_discussion = $first_discussion[0];

		$value = empty( $value ) ? $first_discussion : $value;

		foreach ( $discussions as $module_id => $module_title ) {
			$selected = (false !== $value ? selected( $value, $module_id, false ) : '');
			$content .= '<option value="' . $module_id . '" ' . $selected . '>' . $module_title . '</option>';
		}

		$content .= '</select>';

		return $content;
	}


	public static function get_user_dropdown( $id, $name, $options = array() ) {
		$content = '';
		$content .= '<select name="' . $name . '" id="' . $id . '"';
		$content .= isset( $options['placeholder'] ) ? ' data_placeholder="' . esc_attr( $options['placeholder'] ) . '" ' : '';
		$content .= isset( $options['class'] ) ? ' class="' . esc_attr( $options['class'] ) . '" ' : '';
		$content .= '>';

		$context = isset( $options['context'] ) ? sanitize_text_field( $options['context'] ) : 'normal';
		$include_users = isset( $options['include'] ) ? $options['include'] : array();
		$exclude_users = isset( $options['exclude'] ) ? $options['exclude'] : array();

		$args = array(
			'meta_key' => '',
			'meta_value' => '',
			'meta_compare' => '',
			'meta_query' => array(),
			'include' => apply_filters( 'coursepress_user_dropdown_include', $include_users, $context ),
			'exclude' => apply_filters( 'coursepress_user_dropdown_exclude', $exclude_users, $context ),
			'orderby' => 'display_name',
			'order' => 'ASC',
			'offset' => '',
			'search' => '',
			'class' => isset( $options['class'] ) ? $options['class'] : '',
			'number' => '',
			'count_total' => false,
			'fields' => array( 'display_name', 'ID' ),
			'who' => '',
		);

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$roles = apply_filters( 'coursepress_allowed_roles', array(), $context );
		$get_by_role = apply_filters( 'coursepress_allowed_users_by_role', ! empty( $roles ) );

		if ( $get_by_role ) {
			$users = array();
			if ( empty( $include_users ) && empty( $roles ) ) {
				$roles = array( 'administrator' ); // Default to a list of administrators.
			}
			foreach ( $roles as $role ) {
				$args['role'] = $role;
				$result = get_users( $args );
				$users = ! empty( $result ) ? array_merge( $users, $result ) : $users;
			}
		} else {
			$users = get_users( $args );
		}

		$number = 0;
		foreach ( $users as $user ) {
			$number ++;
			$content .= '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
		}
		$content .= '</select>';

		if ( ! $number ) {
			$content = '';
		}

		return $content;
	}

	/**
	 * Get pending instructors invites
	 */
	public static function course_pendings_instructors_avatars( $course_id, $options = array() ) {
		$content = '';
		$remove_buttons = false;
		if ( CoursePress_Data_Capabilities::can_assign_course_instructor( $course_id ) ) {
			$remove_buttons = isset( $options['remove_buttons'] ) ? $options['remove_buttons'] : true;
		}
		$instructor_invites = (array) get_post_meta( $course_id, 'instructor_invites', true );

		if ( empty( $instructor_invites ) ) {
			return $content;
		}
		foreach ( $instructor_invites as $invite ) {
			$content .= CoursePress_Template_Course::course_edit_avatar_pending_invite( $invite, $remove_buttons, 'instructor' );
		}
		return $content;
	}

	public static function course_instructors_avatars( $course_id, $options = array(), $show_pending = false ) {
		global $post_id, $wpdb;

		$remove_buttons = false;
		if ( CoursePress_Data_Capabilities::can_assign_course_instructor( $course_id ) ) {
			$remove_buttons = isset( $options['remove_buttons'] ) ? $options['remove_buttons'] : true;
		}

		$just_count = ! empty( $options['count'] ) ? $options['count'] : false;

		$content = '';

		$instructors = CoursePress_Data_Course::get_setting( $course_id, 'instructors', array() );
		$instructors = array_filter( $instructors );

		if ( empty( $instructors ) ) {
			// Set current user the default instructor
			CoursePress_Data_Course::add_instructor( $course_id, get_current_user_id() );
			$instructors = array( get_current_user_id() );
		}

		$instructors = ! empty( $instructors ) ? array_map( 'get_userdata', $instructors ) : array();
		$instructors = array_filter( $instructors );

		if ( $just_count ) {
			return count( $instructors );
		}

		foreach ( $instructors as $instructor ) {
			$content .= CoursePress_Template_Course::course_edit_avatar( $instructor, $remove_buttons, 'instructor' );
		}

		// Pending from invites
		if ( $show_pending ) {
			$content .= self::course_pendings_instructors_avatars( $course_id, $options );
		}
		return $content;
	}

	public static function toggle_switch( $id, $name, $options = array() ) {
		$content = '';

		$control_class = isset( $options['class'] ) ? $options['class'] : '';
		$label = isset( $options['label'] ) ? $options['label'] : '';
		$label_class = isset( $options['label_class'] ) ? $options['label_class'] : '';
		$left = isset( $options['left'] ) ? $options['left'] : '';
		$left_class = isset( $options['left_class'] ) ? $options['left_class'] : '';
		$right = isset( $options['right'] ) ? $options['right'] : '';
		$right_class = isset( $options['right_class'] ) ? $options['right_class'] : '';
		$state = isset( $options['state'] ) ? $options['state'] : 'off';

		$data = '';
		if ( isset( $options['data'] ) && is_array( $options['data'] ) ) {
			foreach ( $options['data'] as $key => $value ) {
				$data .= is_string( $value ) ? 'data-' . $key . '="' . $value . '" ' : '';
			}
		}

		$content = '
			<div id="' . esc_attr( $id ) . '" class="toggle-switch coursepress-ui-toggle-switch ' . esc_attr( $control_class ) . ' ' . $state . '" name="' . esc_attr( $name ) . '" ' . $data . '>';

		if ( ! empty( $label ) ) {
			$content .= '
				<span class="label ' . esc_attr( $label_class ) . '">' . $label . '</span>
			';
		}

		if ( ! empty( $left ) ) {
			$content .= '
				<span class="left ' . esc_attr( $left_class ) . '">' . $left . '</span>
			';
		}

		$content .= '
				<div class="control">
					<div class="toggle"></div>
				</div>';

		if ( ! empty( $right ) ) {
			$content .= '
				<span class="right ' . esc_attr( $right_class ) . '">' . $right . '</span>
			';
		}

		$content .= '
			</div>
		';

		return $content;
	}

	/**
	 * Common Admin Page Header
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Header title.
	 * @param string $action_title Header action title.
	 * @param string $action_url Header action url,
	 * @param string $action_cap Header action capability
	 *
	 * @return string Admin Page Header.
	 */
	public static function get_admin_page_title( $title, $action_title = '', $action_url = '', $can_add = false ) {
		$page_title = esc_html( $title );

		/**
		 * title action
		 */
		if ( ! empty( $action_title ) && $can_add ) {
			$page_title .= sprintf(
				' <a href="%s" class="page-title-action">%s</a>',
				esc_url( $action_url ),
				esc_html( $action_title )
			);
		}

		$content = sprintf( '<h1>%s</h1>', $page_title );

		return $content;
	}

	/**
	 * Allow to setup posts per page
	 *
	 * @since 2.0.0
	 *
	 * @param string $name option name
	 * @param string $label Label for the option.
	 *
	 */
	public static function admin_per_page_add_options( $name, $label ) {
		$option = 'per_page';
		$args = array(
			'label' => $label,
			'default' => 10,
			'option' => sprintf( 'coursepress_%s_%s', $name, $option ),
		);
		add_screen_option( $option, $args );
	}

	/**
	 * Common Admin Page
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Currently edited post type.
	 * @param string $form_output Content to show before admin areas
	 *
	 * @return string Admin Page.
	 */
	public static function get_admin_screen( $post_type, $form_output = '' ) {
		$screen = get_current_screen();
		$columns = ( 1 == $screen->get_columns() ) ? '1' : '2';
		ob_start();
?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-<?php echo $columns ; ?>">
<?php echo $form_output; ?>
		<div id="postbox-container-1" class="postbox-container">
			<?php do_meta_boxes( $post_type, 'side', null ); ?>
		</div>
		<div id="postbox-container-2" class="postbox-container">
<?php
	do_meta_boxes( $post_type, 'normal', null );
	do_meta_boxes( $post_type, 'advanced', null );
?>
		</div>
	</div>
</div>
<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Common Admin edit field
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Title of edited entry.
	 * @param string $label Label for title, it is used as placeholder.
	 *
	 * @return string Edit tile field with proper wrapper.
	 */
	public static function get_admin_edit_title_field( $value, $label = '' ) {
		if ( empty( $label ) ) {
			$label = __( 'Enter title here.', 'CP_TD' );
		}
		$content = '<div id="titlediv">';
		$content .= '<div id="titlewrap">';
		$content .= sprintf(
			'<label class="screen-reader-text" id="title-prompt-text" for="title">%s</label>',
			esc_html( $label )
		);
		$content .= sprintf(
			'<input type="text" name="post_title" size="30" value="%s" id="title" spellcheck="true" autocomplete="off">',
			esc_attr( $value )
		);
		$content .= '</div>';
		$content .= '</div>';
		return $content;
	}

	/**
	 * get user box settings for current screen.
	 *
	 * @since 2.0.0
	 *
	 * @return array box context setting
	 */
	public static function get_user_boxes_settings() {
		$settings = array();
		$screen = get_current_screen();
		if ( empty( $screen ) || ! isset( $screen->base ) ) {
			return $settings;
		}
		$user_settings = get_user_meta( get_current_user_id(), 'meta-box-order_'.$screen->base, true );
		if ( empty( $user_settings ) ) {
			return $settings;
		}
		foreach ( $user_settings as $context => $data ) {
			if ( empty( $data ) ) {
				continue;
			}
			$boxes = explode( ',', $data );
			foreach ( $boxes as $box ) {
				if ( empty( $box ) ) {
					continue;
				}
				$settings[ $box ] = $context;
			}
		}
		return $settings;
	}

	/**
	 * Get message if module is REQUIRED.
	 *
	 * @since 2.0.0
	 *
	 * @param string $error_message First line of message.
	 * @return string Error message.
	 */
	public static function get_message_required_modules( $error_message ) {
		$error_message .= PHP_EOL;
		$error_message .= PHP_EOL;
		$error_message .= __( 'Please press the Prev button on the left to continue.', 'CP_TD' );
		return wpautop( $error_message );
	}

	/*
	 * Print admin notice.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message Message to display.
	 * @param string $class Class to add.
	 * @param string $id id to add.
	 * @param array $data data to add.
	 */
	public static function admin_notice( $message, $class = 'success', $id = '', $data = array() ) {
		$html = '<div';
		if ( ! empty( $id ) ) {
			$html .= sprintf( ' id="%s"', esc_attr( $id ) );
		}
		if ( ! empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$html .= sprintf( ' data-%s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}
		}
		$html .= ' class="notice notice-%s is-dismissible">%s</div>';
		return sprintf( $html, esc_attr( $class ), wpautop( $message ) );
	}

	/**
	 * Add form field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Field name.
	 * @param string $value Field value, default empty string.
	 * @param string $type Field type, default hidden.
	 * @param string $id Field id, default empty string.
	 * @param string $class Field class, default empty string.
	 * @param boolean $echo Print field or return? Default print.
	 * @return string/null Form field.
	 */
	public function add_form_field( $name, $value = '', $type = 'hidden', $id = '', $class = '', $echo = true ) {
		$field = $tag = $options = '';
		switch ( $type ) {
			case 'hidden':
				$tag = 'input';
			break;
		}
		if ( empty( $tag ) ) {
			return;
		}
		/**
		 * id
		 */
		if ( ! empty( $id ) ) {
			$options .= sprintf( ' id="%s"', esc_attr( $id ) );
		}
		/**
		 * class
		 */
		if ( ! empty( $class ) ) {
			$options .= sprintf( ' class="%s"', esc_attr( $class ) );
		}
		switch ( $tag ) {
			case 'input':
				$field = sprintf(
					'<%s type="%s" name="%s" value="%s" %s/>',
					$tag,
					esc_attr( $type ),
					esc_attr( $name ),
					esc_attr( $value ),
					$options
				);
			break;
		}
		if ( $echo ) {
			echo $field;
			return;
		}
			return $field;
	}

	public static function admin_paginate( $current, $total_items, $per_page = 20, $current_url = '', $type = 'student' ) {

		$current_url = ! empty( $current_url ) ? $current_url : set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$total_pages = ceil( $total_items / $per_page );
		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span>';
		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev = true;
		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '%s %s', '%s %ss', $total_items ), number_format_i18n( $total_items ), $type ) . '</span>';

		if ( $total_items <= $per_page ) { return sprintf( '<div class="tablenav-pages">%s</div>', $output ); }

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='first-page' href='%s' data-paged='1'><span class='screen-reader-text'>%s</span><span class='tablenav-pages-navspan' aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_number = max( 1, $current -1 );
			$page_links[] = sprintf( "<a class='prev-page' href='%s' data-paged='%s'><span class='screen-reader-text'>%s</span><span class='tablenav-pages-navspan' aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $page_number, $current_url ) ),
				$page_number,
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' />",
			'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
			$current,
			strlen( $total_pages )
		);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_number = min( $total_pages, $current + 1 );
			$page_links[] = sprintf( "<a class='next-page' href='%s' data-paged='%s'><span class='screen-reader-text'>%s</span><span class='tablenav-pages-navspan' aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $page_number, $current_url ) ),
				$page_number,
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='last-page' href='%s' data-paged='%s'><span class='screen-reader-text'>%s</span><span class='tablenav-pages-navspan' aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				$total_pages,
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$output = "<div class='tablenav-pages{$page_class}'>$output</div>";

		return $output;
	}

	public static function course_facilitator_avatars( $course_id, $options = array(), $show_pending = false ) {
		$facilitators = CoursePress_Data_Facilitator::get_course_facilitators( $course_id, false );
		$content = '';
		$can_assigned_facilitator = CoursePress_Data_Capabilities::can_assign_facilitator( $course_id );
		if ( count( $facilitators ) > 0 ) {
			foreach ( $facilitators as $facilitator_id => $userdata ) {
				$content .= CoursePress_Template_Course::course_edit_avatar( $userdata, $can_assigned_facilitator, 'facilitator' );
			}
		}
		// Pending from invites
		if ( $show_pending ) {
			$invites = CoursePress_Data_Facilitator::get_invitations_by_course_id( $course_id );
			if ( ! empty( $invites ) ) {
				foreach ( $invites as $invite ) {
					$content .= CoursePress_Template_Course::course_edit_avatar_pending_invite( $invite, $can_assigned_facilitator, 'facilitator' );
				}
			}
		}

		return $content;
	}

	/**
	 * Check redirect login to avoid 404 if non loged user has no access.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url Page to check.
	 * @return string redirect url
	 */
	public static function check_logout_redirect( $url = false ) {
		if ( false === $url ) {
			$url = esc_url( 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] );
		}
		/**
		 * remove dashboard from redirect url
		 */
		$dashbord = CoursePress_Core::get_slug( 'student_dashboard', false );
		if ( false !== strpos( $url, $dashbord ) ) {
			return site_url();
		}
		return $url;
	}

	/**
	 * Build select!
	 *
	 * @since 2.0.0
	 *
	 * @param string $name element name
	 * @param array $options Array options, option_key => option_label
	 * @param string $selected selected value.
	 * @param string $class element class
	 * @param string $id element id
	 * @return string Valid html select element.
	 */
	public static function select( $name, $options, $selected = '', $class = '', $id = '' ) {
		$select_atts = '';
		if ( ! empty( $class ) ) {
			$select_atts .= sprintf( ' class="%s"', esc_attr( $class ) );
		}
		if ( ! empty( $id ) ) {
			$select_atts .= sprintf( ' id="%s"', esc_attr( $id ) );
		}
		$content = sprintf(
			'<select name="%s"%s>',
			esc_attr( $name ),
			$select_atts
		);
		foreach ( $options as $key => $label ) {
			$content .= '<option value="' . $key . '" ' . selected( $selected, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$content .= '</select>';
		return $content;
	}

	/**
	 * Build course checkbox field.
	 *
	 * @since 2.0.5
	 *
	 * @param array $checkbox Array of checkbox.
	 * @param integer $course_id Course ID.
	 * @return string Valid html checkbox section.
	 */
	public static function course_edit_checkbox( $checkbox, $course_id = null ) {
		$content = '<div class="wide">';
		if ( isset( $checkbox['title'] ) ) {
			$content .= sprintf( '<label>%s</label>', esc_html( $checkbox['title'] ) );
		}
		$content .= '<label class="checkbox">';
		$content .= sprintf(
			'<input type="checkbox" name="meta_%s" %s />',
			esc_attr( $checkbox['meta_key'] ),
			CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, $checkbox['meta_key'], false ) )
		);
		if ( isset( $checkbox['label'] ) ) {
			$content .= sprintf( '<span class="label">%s</span>', esc_html( $checkbox['label'] ) );
		}
		$content .= '</label>';
		if ( isset( $checkbox['description'] ) ) {
			$content .= sprintf( '<p class="description">%s</p>', esc_html( $checkbox['description'] ) );
		}
		$content .= '</div>';
		return $content;
	}

	/**
	 * Displays a password strength meter and includes the necessary assets.
	 */
	public static function password_strength_meter()
	{
		if(!CoursePress_Helper_Utility::is_password_strength_meter_enabled())
		{
			return;
		}

		wp_enqueue_script( 'password-strength-meter' );
		CoursePress_Core::$is_cp_page = true;

		?><p class="password-strength-meter-container">
			<span class="password-strength-meter"></span>
			<input type="hidden" name="password_strength_level" value="3" />
		</p><?php
	}
}
