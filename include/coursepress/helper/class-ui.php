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

			$content .= '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
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
		if ( CP_IS_WPMUDEV || ! empty( $roles ) ) {
			$users = array();
			if ( empty( $include_users ) ) {
				$roles = empty( $roles ) && CP_IS_WPMUDEV ? array( 'administrator' ) : $roles;
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

	public static function course_instructors_avatars( $course_id, $options = array(), $show_pending = false ) {
		global $post_id, $wpdb;

		$remove_buttons = false;
		if ( CoursePress_Data_Capabilities::can_assign_course_instructor( $course_id ) ) {
			$remove_buttons = isset( $options['remove_buttons'] ) ? $options['remove_buttons'] : true;
		}

		$just_count = isset( $options['count'] ) ? $options['count'] : false;

		$content = '';

		$args = array(
			'meta_key' => 'course_' . $course_id,
			// 'meta_value' => $course_id,
			'meta_compare' => 'EXISTS',
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

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
			$args['meta_key'] = $wpdb->prefix . 'course_' . $course_id;
		}

		$instructors = get_users( $args );

		if ( $just_count ) {
			return count( $instructors );
		} else {
			foreach ( $instructors as $instructor ) {
				if ( $remove_buttons ) {
					// $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' . $instructor->ID . ' );"><span class="dashicons dashicons-dismiss"></span></a></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
					$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
				} else {
					// $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
					$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
				}
			}

			// Pending from invites
			if ( $show_pending ) {
				$instructor_invites = get_post_meta( $course_id, 'instructor_invites', true );
				if ( $instructor_invites ) {
					foreach ( $instructor_invites as $invite ) {
						if ( $remove_buttons ) {
							$content .= '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' . $invite['code'] . '"><div class="instructor-status">' . esc_html__( 'Pending', 'CP_TD' ) . '</div><div class="invite-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' . get_avatar( $invite['email'], 80 ) . '<span class="instructor-name">' . $invite['first_name'] . ' ' . $invite['last_name'] . '</span></div>';
						} else {
							$content .= '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' . $invite['code'] . '"><div class="instructor-status">' . esc_html__( 'Pending', 'CP_TD' ) . '</div>' . get_avatar( $invite['email'], 80 ) . '<span class="instructor-name">' . $invite['first_name'] . ' ' . $invite['last_name'] . '</span></div>';
						}
					}
				}
			}

			return $content;
		}
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
}
