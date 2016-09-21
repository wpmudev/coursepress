<?php
/**
 * Course Template
 **/
class CoursePress_Template_Course {
	public static function course_instructors() {
		$content = '[COURSE INSTRUCTORS]';

		return $content;
	}

	public static function course_archive() {
		return do_shortcode( '[course_archive]' );
	}

	public static function course() {
		return do_shortcode( '[course_page]' );
	}

	/**
	 * Template for instructor of facilitator pending avatar.
	 *
	 * @since 2.0.0
	 * @param array $invite Invitation data.
	 * @param boolean $remove_buttons Show or hide remove button.
	 * @param string $type Instructor or facilitator.
	 * @return string Content.
	 */
	public static function course_edit_avatar( $user, $remove_buttons = false, $type = 'instructor' ) {
		$content = '';
		/**
		 * check type!
		 */
		if ( '{{{data.who}}}' != $type && ! preg_match( '/^(instructor|facilitator)$/', $type ) ) {
			return $content;
		}
		$id = '';
		if ( $remove_buttons ) {
			$id = sprintf(
				'id="%s_holder_%s"',
				esc_attr( $type ),
				esc_attr( $user->ID )
			);
		}
			$content = sprintf(
				'<div class="avatar-holder %s-avatar-holder" data-who="%s" data-id="%s" data-status="confirmed" %s>',
				esc_attr( $type ),
				esc_attr( $type ),
				esc_attr( $user->ID ),
				$id
			);
			$content .= sprintf(
				'<div class="%s-status"></div>',
				esc_attr( $type )
			);
			if ( $remove_buttons ) {
				$content .= '<div class="remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>';
			}
			if ( is_numeric( $user->ID ) ) {
				$content .= get_avatar( $user->ID, 80 );
			} else {
				$content .= '{{{data.avatar}}}';
			}
			$content .= sprintf(
				'<span class="%s-name">%s</span>',
				esc_attr( $type ),
				esc_attr( $user->display_name )
			);
			$content .= '</div>';
			return $content;
	}

	/**
	 * Template for instructor of facilitator pending avatar.
	 *
	 * @since 2.0.0
	 * @param array $invite Invitation data.
	 * @param boolean $remove_buttons Show or hide remove button.
	 * @param string $type Instructor or facilitator.
	 * @return string Content.
	 */
	public static function course_edit_avatar_pending_invite( $invite, $remove_buttons = false, $type = 'instructor' ) {
		$content = '';
		if ( empty( $invite ) ) {
			return $content;
		}
		/**
		 * check type!
		 */
		if ( '{{{data.who}}}' != $type && ! preg_match( '/^(instructor|facilitator)$/', $type ) ) {
			return $content;
		}
		$id = '';
		if ( $remove_buttons ) {
			$id = sprintf(
				'id="%s_holder_%s"',
				esc_attr( $type ),
				esc_attr( $invite['code'] )
			);
		}
			$content = sprintf(
				'<div class="avatar-holder %s-avatar-holder pending-invite" data-who="%s" data-code="%s" data-status="pending" %s>',
				esc_attr( $type ),
				esc_attr( $type ),
				esc_attr( $invite['code'] ),
				$id
			);
			$content .= sprintf(
				'<div class="%s-status">%s</div>',
				esc_attr( $type ),
				esc_html__( 'Pending', 'cp' )
			);
			if ( $remove_buttons ) {
				$content .= '<div class="remove"><a><span class="dashicons dashicons-dismiss"></span></a></div>';
			}
			if ( '{{{data.avatar}}}' == $invite['email'] ) {
				$content .= $invite['email'];
			} else {
				$content .= get_avatar( $invite['email'], 80 );
			}
			$content .= sprintf(
				'<span class="%s-name">%s %s</span>',
				esc_attr( $type ),
				$invite['first_name'],
				$invite['last_name']
			);
			$content .= '</div>';
			return $content;
	}

	/**
	 * JavaScript template for invited person.
	 *
	 * @since 2.0.0
	 *
	 * @return string Invitation template.
	 */
	public static function javascript_templates() {
		$invite = array(
			'code' => '{{{data.code}}}',
			'first_name' => '{{{data.first_name}}}',
			'last_name' => '{{{data.last_name}}}',
			'email' => '{{{data.avatar}}}',
		);
		/**
		 * Invitation template
		 */
		$content = '<script type="text/html" id="tmpl-course-invitation">';
		$content .= self::course_edit_avatar_pending_invite( $invite, true, '{{{data.who}}}' );
		$content .= '</script>';

		/**
		 * User template
		 */
		$invite = array(
			'ID' => '{{{data.id}}}',
			'display_name' => '{{{data.display_name}}}',
			'avatar' => '{{{data.avatar}}}',
			'course_id' => '{{{data.course_id}}}',
		);
		$user = (object) $invite;
		$content .= '<script type="text/html" id="tmpl-course-person">';
		$content .= self::course_edit_avatar( $user, true, '{{{data.who}}}' );
		$content .= '</script>';
		return $content;
	}

}
