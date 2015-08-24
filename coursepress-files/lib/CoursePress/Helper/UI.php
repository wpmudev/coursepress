<?php

class CoursePress_Helper_UI {


	public static function browse_media_field( $id, $name, $args = array() ) {

		if( ! $name ) {
			$name = $id;
		}

		$args['title'] = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		$args['container_class'] = isset( $args['container_class'] ) ? sanitize_text_field( $args['container_class'] ) : 'wide';
		$args['textbox_class'] = isset( $args['textbox_class'] ) ? sanitize_text_field( $args['textbox_class'] ) : 'medium';
		$args['title'] = isset( $args['title'] ) ? sanitize_text_field( $args['title'] ) : '';
		$args['value'] = isset( $args['value'] ) ? sanitize_text_field( $args['value'] ) : '';
		$args['placeholder'] = isset( $args['placeholder'] ) ? sanitize_text_field( $args['placeholder'] ) : __( 'Add Media URL or Browse for Media', CoursePress::TD );
		$args['button_text'] = isset( $args['button_text'] ) ? sanitize_text_field( $args['button_text'] ) : __( 'Browse', CoursePress::TD );
		$args['type'] = isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : 'image';
		$args['invalid_message'] = isset( $args['invalid_message'] ) ? sanitize_text_field( $args['invalid_message'] ) : '';
		$args['description'] = isset( $args['description'] ) ? sanitize_text_field( $args['description'] ) : '';

		if( 'image' === $args['type'] ) {
			$supported_extensions = implode( ', ', CoursePress_Helper_Utility::get_image_extensions() );
		}
		if( 'audio' === $args['type'] ) {
			$supported_extensions = implode( ', ', wp_get_video_extensions() );
		}
		if( 'video' === $args['type'] ) {
			$supported_extensions = implode( ', ', wp_get_audio_extensions() );
		}

		$content = '
		<div class="' . $args['container_class'] . '">
			<label for="' . $name . '">' .
	            esc_html( $args['title'] );

		if( ! empty( $args['description'] ) ) {
		    $content .= '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	    }

		$content .= '
			</label>
			<input class="' . $args['textbox_class'] . ' ' . $args['type'] . '_url" type="text" name="' . $name . '" id="' . $name . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $args['value'] ) . '"/>
			<input class="button browse-media-field" type="button" name="' . $name . '-button" value="' . esc_attr( $args['button_text'] ) . '"/>
			<div class="invalid_extension_message">' . sprintf( esc_html__( 'Extension of the file is not valid. Please use one of the following: %s', CoursePress::TD ), $supported_extensions ) . '</div>
		</div>';

		return $content;

	}

	public static function get_user_avatar_array( $args = array(), $meta_options = array() ) {

		//meta_key => role_ins
		//meta_value => 'instructor'

		$args_default = array(
			'meta_key'     => ( isset( $meta_options['meta_key'] ) ? $meta_options['meta_key'] : '' ),
			'meta_value'   => ( isset( $meta_options['meta_value'] ) ? $meta_options['meta_value'] : '' ),
			'meta_compare' => '',
			'meta_query'   => array(),
			'include'      => array(),
			'exclude'      => array(),
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'offset'       => '',
			'search'       => '',
			'number'       => '',
			'count_total'  => false,
			'fields'       => array( 'display_name', 'ID' ),
			'who'          => ''
		);

		$args = CoursePress_Helper_Utility::merge_distinct( $args, $args_default );

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$users = get_users( $args );

		$user_avatars = array();
		foreach ( $users as $user ) {
			$user_avatars[ $user->ID ] = str_replace( "'", '"', get_avatar( $user->ID, 80, "", $user->display_name ) );
		}
		$user_avatars['default'] = str_replace( "'", '"', get_avatar( 0, 80, '', '', array( 'force_default' => true ) ) );

		return $user_avatars;

	}

	public static function get_course_dropdown( $id, $name, $courses, $options = array() ) {

		$content = '';
		$content .= '<select name="' . $name . '" id="' . $id . '"';
		$content .= isset( $options['placeholder'] ) ? ' data_placeholder="' . esc_attr( $options['placeholder'] ) . '" ' : '';
		$content .= isset( $options['class'] ) ? ' class="' . esc_attr( $options['class'] ) . '" ' : '';
		$content .= '>';

		$value = isset( $options['value'] ) ? $options['value'] : false;

		foreach( $courses as $course ) {

			$selected = $value !== false ? selected( $value, $course->ID, false ) : '';

			$content .= '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
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

		$args = array(
			//'role' => 'instructor',
			'meta_key'     => '',
			'meta_value'   => '',
			'meta_compare' => '',
			'meta_query'   => array(),
			'include'      => array(),
			'exclude'      => isset( $options['exclude'] ) ? $options['exclude'] : array(),
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'offset'       => '',
			'search'       => '',
			'class'        => isset( $options['class'] ) ? $options['class'] : '',
			'number'       => '',
			'count_total'  => false,
			'fields'       => array( 'display_name', 'ID' ),
			'who'          => ''
		);

		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		$instructors = get_users( $args );

		$number = 0;
		foreach ( $instructors as $instructor ) {
			$number ++;
			$content .= '<option value="' . $instructor->ID . '">' . $instructor->display_name . '</option>';
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
			'meta_key'     => 'course_' . $course_id,
			//'meta_value'   => $course_id,
			'meta_compare' => 'EXISTS',
			'meta_query'   => array(),
			'include'      => array(),
			'exclude'      => array(),
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'offset'       => '',
			'search'       => '',
			'number'       => '',
			'count_total'  => false,
			'fields'       => array( 'display_name', 'ID' ),
			'who'          => ''
		);

		if ( is_multisite() ) {
			$args['blog_id']  = get_current_blog_id();
			$args['meta_key'] = $wpdb->prefix . 'course_' . $course_id;
		}

		$instructors = get_users( $args );

		if ( $just_count == true ) {
			return count( $instructors );
		} else {

			foreach ( $instructors as $instructor ) {
				if ( $remove_buttons ) {
					//$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor( ' . $instructor->ID . ' );"><span class="dashicons dashicons-dismiss"></span></a></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
					$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
				} else {
					//$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-remove"></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
					$content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div>' . get_avatar( $instructor->ID, 80 ) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
				}
			}

			// Pending from invites
			if( $show_pending ) {
				$instructor_invites = get_post_meta( $course_id, 'instructor_invites', true );
				if ( $instructor_invites ) {
					foreach ( $instructor_invites as $invite ) {
						if ( $remove_buttons ) {
							$content .= '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' . $invite['code'] . '"><div class="instructor-status">' . esc_html__( 'Pending', CoursePress::TD ) . '</div><div class="invite-remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>' . get_avatar( $invite['email'], 80 ) . '<span class="instructor-name">' . $invite['first_name'] . ' ' . $invite['last_name'] . '</span></div>';
						} else {
							$content .= '<div class="instructor-avatar-holder pending-invite" id="instructor_holder_' . $invite['code'] . '"><div class="instructor-status">' . esc_html__( 'Pending', CoursePress::TD ) . '</div>' . get_avatar( $invite['email'], 80 ) . '<span class="instructor-name">' . $invite['first_name'] . ' ' . $invite['last_name'] . '</span></div>';
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
		if( isset( $options['data'] ) && is_array( $options['data'] ) ) {
			foreach( $options['data'] as $key => $value ) {
				$data .= is_string( $value ) ? 'data-' . $key . '="' . $value . '" ' : '';
			}
		}

		$content = '
			<div id="' . esc_attr( $id ) . '" class="toggle-switch coursepress-ui-toggle-switch ' . esc_attr( $control_class ) . ' ' . $state . '" name="' . esc_attr( $name ) . '" ' . $data . '>';

		if( ! empty( $label ) ) {
			$content .= '
				<span class="label ' . esc_attr( $label_class ) . '">' . $label . '</span>
			';
		}

		if( ! empty( $left ) ) {
			$content .= '
				<span class="left ' . esc_attr( $left_class ) . '">' . $left . '</span>
			';
		}

		$content .= '
				<div class="control">
					<div class="toggle"></div>
				</div>';


		if( ! empty( $right ) ) {
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