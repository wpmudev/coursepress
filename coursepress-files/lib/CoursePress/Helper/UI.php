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
		$selected = ! empty( $first_option ) && $value !== false ? selected( $value, $first_option['value'], false ) : '';
		$content .= ! empty( $first_option ) ? '<option value="' . $first_option['value'] . '" ' . $selected . '>' . esc_html( $first_option['text'] ) . '</option>' : '';

		foreach ( $courses as $course ) {

			$selected = $value !== false ? selected( $value, $course->ID, false ) : '';

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
		$selected = ! empty( $first_option ) && $value !== false ? selected( $value, $first_option['value'], false ) : '';
		$content .= ! empty( $first_option ) ? '<option value="' . $first_option['value'] . '" ' . $selected . '>' . esc_html( $first_option['text'] ) . '</option>' : '';

		if ( 'all' !== $course_id ) {
			foreach ( $units as $unit ) {
				$selected = $value !== false ? selected( $value, $unit->ID, false ) : '';
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
				if ( $meta['module_type'] != CoursePress_Helper_UI_Module::OUTPUT_DISCUSSION ) {
					continue;
				}

				$discussions[ $module->ID ] = $module->post_title;
			}
		}

		$first_discussion = array_keys( $discussions );
		$first_discussion = $first_discussion[0];

		$value = empty( $value ) ? $first_discussion : $value;

		foreach ( $discussions as $module_id => $module_title ) {
			$selected = $value !== false ? selected( $value, $module_id, false ) : '';
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

		if ( $number == 0 ) {
			$content = '';
		}

		return $content;
	}

	public static function course_instructors_avatars( $course_id, $options = array(), $show_pending = false ) {
		global $post_id, $wpdb;

		$remove_buttons = isset( $options['remove_buttons'] ) ? $options['remove_buttons'] : true;
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

		if ( $just_count == true ) {
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
}
