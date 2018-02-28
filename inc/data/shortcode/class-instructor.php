<?php
/**
 * Shortcode handlers.
 *
 * @package  CoursePress
 */

/**
 * Instructor-related shortcodes.
 */
class CoursePress_Data_Shortcode_Instructor extends CoursePress_Utility {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public function init() {
		$shortcodes = array(
			'course_instructors',
			'coursecourse_media_instructor_avatar',
			'course_instructor_avatar',
			'instructor_profile_url',
		);
		foreach ( $shortcodes as $shortcode ) {
			$method = 'get_' . $shortcode;
			if ( method_exists( $this, $method ) ) {
				add_shortcode( $shortcode, array( $this, $method ) );
			}
		}
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
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_instructors( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'avatar_size' => 42,
			'default_avatar' => '',
			'label' => __( 'Instructor', 'cp' ),
			'label_delimiter' => ':',
			'label_plural' => __( 'Instructors', 'cp' ),
			'label_tag' => 'h3',
			'link_all' => false,
			'link_text' => __( 'View Profile', 'cp' ),
			'list_separator' => ', ',
			'show_divider' => true,
			'style' => 'block',
			'summary_length' => 50,
		), $atts, 'course_instructors' );
		$course = coursepress_get_course( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$instructors = $course->get_instructors();
		$count = count( $instructors );
		if ( 0 == $count ) {
			return '';
		}
		$class = array( 'course-instructors', $atts['style'] );
		$link_all = 'yes' == $atts['link_all'];
		$templates = '';
		if ( ! empty( $atts['label'] ) ) {
			$templates .= $this->create_html(
				$atts['label_tag'],
				array( 'class' => 'label' ),
				_n( $atts['label'], $atts['label_plural'], $count ) . $atts['label_delimiter']
			);
		}
		$instructors_template = array();
		foreach ( $instructors as $instructor ) {
			// @var $instructor CoursePress_User
			$template = '';
			if ( 'block' == $atts['style'] ) {
				$template .= $instructor->get_avatar( $atts['avatar_size'] );
			}
			$link = $instructor->get_instructor_profile_link();
			if ( ! $link_all ) {
				$attr = array( 'href' => esc_url_raw( $link ), 'class' => 'fn instructor' );
				$template .= $this->create_html( 'a', $attr, $instructor->get_name() );
			} else {
				$template .= $instructor->get_name();
			}
			$instructors_template[] = $template;
		}
		if ( 'flat' == $atts['style'] ) {
			$templates .= ' ';
		}
		$templates .= implode( $atts['list_separator'], $instructors_template );
		return $this->create_html( 'div', array( 'class' => implode( ' ', $class ) ), $templates );
	}

	/**
	 * Display avatar of course instructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_instructor_avatar( $atts ) {
		$atts = shortcode_atts( array(
			'instructor_id' => 0,
			'thumb_size' => 80,
			'force_display' => 'no',
			'class' => 'small-circle-profile-image',
		), $atts );
		/**
		 * check id
		 */
		$instructor_id = (int) $atts['instructor_id'];
		if ( empty( $instructor_id ) ) {
			return '';
		}
		/**
		 * get user
		 */
		$user = new CoursePress_User( $instructor_id );
		/**
		 * check user
		 */
		$is_error = $user->is_error();
		if ( $is_error ) {
			return '';
		}
		/**
		 * check instructor
		 */
		$is_instructor = $user->is_instructor();
		if ( ! $is_instructor ) {
			return '';
		}
		$thumb_size = (int) $atts['thumb_size'];
		$class = sanitize_html_class( $atts['class'] );
		$force_display = coursepress_is_true( $atts['force_display'] );
		$content = '';
		$avatar = get_avatar( $instructor_id, $thumb_size, '', '', array( 'force_display' => $force_display ) );
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
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_instructor_profile_url( $atts ) {
		$atts = shortcode_atts( array(
			'instructor_id' => 0,
		), $atts );
		$instructor_profile_slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );
		$instructor_id = (int) $atts['instructor_id'];
		if ( empty( $instructor_id ) ) {
			return '';
		}
		$instructor = get_userdata( $instructor_id );
		$username = trailingslashit( $instructor->user_login );
		return trailingslashit( home_url() ) . trailingslashit( $instructor_profile_slug ) . $username;
	}

	/**
	 * Display avatar of course instructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_coursecourse_media_instructor_avatar( $atts ) {
		return $this->get_course_instructor_avatar( $atts );
	}
}
