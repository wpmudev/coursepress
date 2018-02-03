<?php
/**
 * Shortcode handlers.
 *
 * @package  CoursePress
 */

/**
 * Instructor-related shortcodes.
 */
class CoursePress_Data_Shortcode_Instructor {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public function init() {

		add_shortcode( 'course_instructors', array( $this, 'course_instructors' ) );
		add_shortcode( 'coursecourse_media_instructor_avatar', array( $this, 'course_instructor_avatar' ) );
		add_shortcode( 'course_instructor_avatar', array( $this, 'course_instructor_avatar' ) );
		add_shortcode( 'instructor_profile_url', array( $this, 'instructor_profile_url' ) );
	}

	/**
	 * Shows all the instructors of the given course.
	 *
	 * Supported styles:
	 *
	 * style="block" - List profile blocks including name, avatar, description
	 *                 (optional) and profile link. You can choose to make the
	 *                 entire block clickable ( link_all="yes" ) or only the
	 *                 profile link ( link_all="no", Default).
	 * style="list"  - Lists instructor display names (separated by list_separator).
	 * style="link"  - Same as 'list', but returns links to instructor profiles.
	 * style="count" - Outputs a simple integer value with the total of
	 *                 instructors for the course.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function course_instructors( $atts ) {

		global $wp_query;

		$instructor_profile_slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );

		extract( shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => __( 'Instructor', 'cp' ),
			'label_plural' => __( 'Instructors', 'cp' ),
			'label_delimeter' => ':&nbsp;',
			'label_tag' => '',
			'count' => false,
			'list' => false,
			'link' => false,
			'link_text' => __( 'View Full Profile', 'cp' ),
			'show_label' => 'no', // Yes, no.
			'summary_length' => 50,
			'style' => 'block', // List, list-flat, block, count.
			'list_separator' => ', ',
			'avatar_size' => 80,
			'avatar_position' => 'bottom',
			'default_avatar' => '',
			'show_divider' => 'yes',
			'link_all' => 'no',
			'class' => '',
		), $atts, 'course_instructors' ) );

		$course_id = (int) $course_id;

		$label = sanitize_text_field( $label );
		$label_plural = sanitize_text_field( $label_plural );
		$label_delimeter = sanitize_text_field( $label_delimeter );
		$label_tag = sanitize_html_class( $label_tag );
		$link = coursepress_is_true( sanitize_text_field( $link ) );
		$link_text = sanitize_text_field( $link_text );
		$show_label = coursepress_is_true( sanitize_text_field( $show_label ) );
		$summary_length = (int) $summary_length;
		$style = sanitize_html_class( $style );
		$avatar_size = (int) $avatar_size;
		$avatar_position = sanitize_text_field( $avatar_position );
		$show_divider = coursepress_is_true( sanitize_html_class( $show_divider ) );
		$link_all = coursepress_is_true( sanitize_html_class( $link_all ) );
		$class = sanitize_html_class( $class );

		// Support deprecated arguments.
		$count = coursepress_is_true( sanitize_html_class( $count ) );
		$list = coursepress_is_true( sanitize_html_class( $list ) );
		$style = $count ? 'count' : $style;
		$style = $list ? 'list-flat' : $style;

		$show_label = 'list-flat' === $style && ! $show_label ? 'yes' : $show_label;

		if ( empty( $course_id ) ) {
			$instructors = get_users( array( 'meta_value' => 'instructor' ) );
		} else {
			$instructors = coursepress_get_course_instructors( $course_id );
		}

		$list = array();
		$content = '';

		if ( 0 < count( $instructors ) && $show_label ) {
			if ( ! empty( $label_tag ) ) {
				$content .= '<' . $label_tag . '>';
			}

			if ( count( $instructors ) > 1 ) {
				$content .= $label_plural . $label_delimeter;
			} else {
				$content .= $label . $label_delimeter;
			}

			if ( ! empty( $label_tag ) ) {
				$content .= '</' . $label_tag . '>';
			}
		}

		if ( 'count' != $style ) {
			if ( ! empty( $instructors ) ) {
				foreach ( $instructors as $instructor ) {
					$profile_href = trailingslashit( home_url() ) . trailingslashit( $instructor_profile_slug );
					$hash = md5( $instructor->user_login );
					$instructor_hash = '';

					if ( empty( $instructor_hash ) ) {
						//CoursePress_Data_Instructor::create_hash( $instructor );
					}

					$show_username = coursepress_is_true( coursepress_get_setting( 'instructor/show_username', true ) );
					$profile_href .= $show_username ? trailingslashit( $instructor->user_login ) : trailingslashit( $hash );

					$display_name = ' ' . apply_filters(
						'coursepress_schema',
						esc_html( coursepress_get_user_name( $instructor->ID, false, false ) ),
						'title'
					);

					switch ( $style ) {
						case 'block':
							/**
							 * schema.org
							 */
							$schema = apply_filters( 'coursepress_schema', '', 'itemscope-person' );

							$content .= '<div class="instructor-profile ' . $class . '"'.$schema.'>';

							if ( $link_all ) {
								$content .= '<a href="' . esc_url_raw( $profile_href ) . '">';
							}

							if ( 'bottom' == $avatar_position ) {
								$content .= '<div class="profile-name">' . $display_name . '</div>';
							}

							/**
							 * schema.org
							 */
							$schema = apply_filters( 'coursepress_schema', '', 'image' );

							$content .= '<div class="profile-avatar"'.$schema.'>';
							$content .= get_avatar(
								$instructor->ID,
								$avatar_size,
								'',
								$instructor->display_name,
								array( 'force_display' => true )
							);
							$content .= '</div>';

							if ( 'top' == $avatar_position ) {
								$schema = apply_filters( 'coursepress_schema', '', 'itemscope-person' );
								$content .= sprintf(
									'<div class="profile-name" %s>%s</div>',
									$schema,
									$display_name
								);
							}

							if ( $link_all ) {
								$content .= '</a>';
							}

							if ( ! empty( $summary_length ) ) {
								$content .= '<div class="profile-description">' . $instructor->get_description() . '</div>';
							}

							if ( ! empty( $link_text ) ) {
								$content .= '<div class="profile-link">';
								$content .= ! $link_all ? '<a href="' . esc_url_raw( $profile_href ) . '">' : '';
								$content .= $link_text;
								$content .= ! $link_all ? '</a>' : '';
								$content .= '</div>';
							}

							$content .= '</div>';
							break;

						case 'link':
						case 'list':
						case 'list-flat':
							if ( $link ) {
								$schema = apply_filters( 'coursepress_schema', '', 'itemscope-person' );
								$list[] = sprintf(
									'<a href="%s" %s>%s</a>',
									esc_url_raw( $profile_href ),
									$schema,
									$display_name
								);
							} else {
								$list[] = $display_name;
							}
							break;
					}
				}
			}
		}

		switch ( $style ) {
			case 'block':
				$content = '<div class="instructor-block ' . $class . '">' . $content . '</div>';
				if ( $show_divider && ( 0 < count( $instructors ) ) ) {
					$content .= '<div class="divider"></div>';
				}
				break;

			case 'list-flat':
				$content .= implode( $list_separator, $list );
				$content = '<div class="instructor-list instructor-list-flat ' . $class . '">' . $content . '</div>';
				break;

			case 'list':
				$content .= '<ul>';
				foreach ( $list as $instructor ) {
					$content .= '<li>' . $instructor . '</li>';
				}
				$content .= '</ul>';
				$content = '<div class="instructor-list ' . $class . '">' . $content . '</div>';
				break;

			case 'count':
				$content = count( $instructors );
				break;
		}

		return $content;
	}

	/**
	 * Display avatar of course instructor.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function course_instructor_avatar( $atts ) {

		global $wp_query;

		extract( shortcode_atts( array(
			'instructor_id' => 0,
			'thumb_size' => 80,
			'force_display' => 'no',
			'class' => 'small-circle-profile-image',
		), $atts ) );

		$instructor_id = (int) $instructor_id;
		if ( empty( $instructor_id ) ) { return ''; }

		$thumb_size = (int) $thumb_size;
		$class = sanitize_html_class( $class );
		$force_display = cp_is_true( $force_display );

		$content = '';

		$avatar = get_avatar(
			$instructor_id,
			$thumb_size,
			'',
			'',
			array( 'force_display' => $force_display )
		);

		if ( ! empty( $avatar ) ) {
			preg_match( '/src=(\'|")(\S*)(\'|")/', $avatar, $match );
			$avatar_url = $match[2];

			$content .= '<div class="instructor-avatar">';
			$content .= '<div class="' . $class . '" style="background: url( ' . $avatar_url . ' ); width: ' . $thumb_size . 'px; height: ' . $thumb_size . 'px;"></div>';
			$content .= '</div>';
		}

		return $content;
	}

	/**
	 * Display URL to the instructors profile page.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function instructor_profile_url( $atts ) {

		$instructor_profile_slug = CoursePress_Core::get_setting(
			'slugs/instructor_profile',
			'instructor'
		);

		extract( shortcode_atts( array(
			'instructor_id' => 0,
		), $atts ) );

		$instructor_id = (int) $instructor_id;
		if ( empty( $instructor_id ) ) { return ''; }

		$instructor = get_userdata( $instructor_id );
		if ( get_option( 'show_instructor_username', 1 ) ) {
			$username = trailingslashit( $instructor->user_login );
		} else {
			$username = trailingslashit(
				CoursePress_Helper_Utility::md5( $instructor->user_login )
			);
		}

		return trailingslashit( home_url() ) . trailingslashit( $instructor_profile_slug ) . $username;
	}
}
