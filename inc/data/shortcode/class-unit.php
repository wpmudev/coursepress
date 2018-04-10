<?php
/**
 * Shortcode handlers.
 *
 * @package CoursePress
 */

/**
 * Unit and module-related shortcodes.
 */
class CoursePress_Data_Shortcode_Unit extends CoursePress_Utility {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public function init() {

		$shortcodes = array(
			'course_unit_details',
			'course_unit_submenu',
			'course_unit_archive_submenu',
			'module_status',
			'unit_discussion',
			'course_unit_title',
		);

		foreach ( $shortcodes as $shortcode ) {
			$method = 'get_' . $shortcode;

			if ( method_exists( $this, $method ) ) {
				add_shortcode( $shortcode, array( $this, $method ) );
			}
		}
	}

	/**
	 * Get the unit details.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_unit_details( $atts ) {

		$atts = shortcode_atts( array(
			'unit_id' => 0,
			'field' => 'permalink',
		), $atts, 'course_unit_details' );

		$atts = apply_filters( 'shortcode_atts_course_unit_details', $atts );

		$unit_id = (int) $atts['unit_id'];
		$field = sanitize_html_class( $atts['field'] );

		$content = '';
		if ( 'permalink' == $field ) {
			$unit = coursepress_get_unit( $unit_id );
			if ( ! empty( $unit ) ) {
				$content = $unit->get_permalink();
			}
		}

		return $content;
	}

	/**
	 * Get course unit submenu.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_unit_submenu( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
		), $atts, 'course_unit_archive_submenu' );

		$course_id = (int) $atts['course_id'];

		if ( empty( $course_id ) ) {
			return '';
		}

		return do_shortcode( '[course_unit_archive_submenu course_id="' . $course_id . '"]' );
	}

	/**
	 * Get course unit archive submenu.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_unit_archive_submenu( $atts ) {

		global $CoursePress;

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
		), $atts, 'course_unit_archive_submenu' );

		$course_id = (int) $atts['course_id'];

		if ( empty( $course_id ) ) {
			return '';
		}

		$course = coursepress_get_course( $course_id );
		$subpage = CoursePress_Data_Course::$last_course_subpage;

		$content = '<div class="submenu-main-container course-submenu-container">';
		$content .= '<ul id="submenu-main" class="submenu course-submenu">';
		$content .= '<li class="submenu-item menu-item submenu-units ' . ( 'units' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course->get_units_url() ) . '" class="course-units-link">' . esc_html__( 'Units', 'cp' ) . '</a></li>';

		$student = coursepress_get_user();
		$enrolled = ! empty( $student ) ? $student->is_enrolled_at( $course_id ) : false;
		$is_instructor = $student->is_instructor_at( $course_id );

		if ( $enrolled || $is_instructor ) {
			$content .= '<li class="submenu-item menu-item submenu-notifications ' . ( 'notifications' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course->get_notifications_url() ) . '">' . esc_html__( 'Notifications', 'cp' ) . '</a></li>';
		}

		$pages = CoursePress_Data_Course::allow_pages( $course_id );

		if ( $pages['course_discussion'] && ( $enrolled || $is_instructor ) ) {
			$content .= '<li class="submenu-item menu-item submenu-discussions ' . ( 'discussions' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course->get_discussion_url() ) . '">' . esc_html__( 'Discussions', 'cp' ) . '</a></li>';
		}

		if ( $pages['workbook'] && $enrolled ) {
			$content .= '<li class="submenu-item menu-item submenu-workbook ' . ( 'workbook' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course->get_workbook_url() ) . '">' . esc_html__( 'Workbook', 'cp' ) . '</a></li>';
		}

		if ( $pages['grades'] && $enrolled ) {
			$content .= '<li class="submenu-item menu-item submenu-grades ' . ( 'grades' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course->get_grades_url() ) . '">' . esc_html__( 'Grades', 'cp' ) . '</a></li>';
		}

		$content .= '<li class="submenu-item menu-item submenu-info"><a href="' . esc_url_raw( $course->get_permalink() ) . '">' . esc_html__( 'Course Details', 'cp' ) . '</a></li>';

		$show_link = false;

		if ( defined( 'CP_IS_PREMIUM' ) && CP_IS_PREMIUM ) {
			// CERTIFICATE CLASS.
			$show_link = CoursePress_Data_Certificate::is_enabled() && $enrolled;
		}

		if ( is_user_logged_in() && $show_link ) {
			// COMPLETION LOGIC.
			if ( $student->is_course_completed( $course_id ) ) {
				$certificate = $CoursePress->get_class( 'CoursePress_Certificate' );
				$certificate_link = $certificate->get_encoded_url( $course->__get( 'ID' ), $student->__get( 'ID' ) );

				$content .= '<li class="submenu-item menu-item submenu-certificate ' . ( 'certificate' == $subpage ? 'submenu-active' : '') . '">' . $certificate_link . '</li>';
			}
		}

		$content .= '</ul></div>';

		return $content;
	}

	/**
	 * Get module status.
	 *
	 * @since  1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_module_status( $atts ) {

		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'unit_id' => coursepress_get_unit_id(),
			'previous_unit' => false,
			'message' => __( '%d of %d required elements completed.', 'cp' ),
			'format' => 'true',
		), $atts, 'module_status' );

		$message = sanitize_text_field( $atts['message'] );

		$course_id = (int) $atts['course_id'];
		$unit_id = (int) $atts['unit_id'];
		$previous_unit = empty( $atts['previous_unit'] ) ? false : (int) $atts['previous_unit'];

		if ( empty( $unit_id ) || empty( $course_id ) ) {
			return '';
		}

		$unit_status = CoursePress_Data_Unit::get_unit_availability_status( $course_id, $unit_id, $previous_unit );

		$unit_available = isset( $unit_status['available'] )? $unit_status['available'] : false;

		$content = '<span class="unit-archive-single-module-status">';

		if ( $unit_available ) {
			$content .= do_shortcode( '[course_mandatory_message course_id="' . $course_id . '" unit_id="' . $unit_id . '" message="' . $message . '"]' );
		} else {
			if ( $unit_status['mandatory_required']['enabled'] && ! $unit_status['mandatory_required']['result'] && ! $unit_status['completion_required']['enabled'] ) {
				$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'cp' );
				$content .= $this->get_message_required_modules( $first_line );
			} elseif ( $unit_status['completion_required']['enabled'] && ! $unit_status['completion_required']['result'] ) {
				$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'cp' );
				$content .= $this->get_message_required_modules( $first_line );
			} elseif ( $unit_status['passed_required']['enabled'] && ! $unit_status['passed_required']['result'] ) {
				/**
				 * User also needs to pass all required assessments
				 */
				$first_line = __( 'You need to pass all the REQUIRED modules before this unit.', 'cp' );
				$content .= $this->get_message_required_modules( $first_line );
			}
			if ( ! empty( $unit_status['date_restriction'] ) && ! $unit_status['date_restriction']['result'] ) {
				$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id );
				if ( ! empty( $unit_availability_date ) ) {
					$available_on = date_i18n( get_option( 'date_format' ), $this->strtotime( $unit_availability_date ) );
					$content .= esc_html__( 'This unit will be available on ', 'cp' ) . ' ' . $available_on;
				}
			}
		}

		$content .= '</span>';

		return $content;
	}

	/**
	 * Get unit discussion.
	 *
	 * @since  1.0.0
	 *
	 * @return string Shortcode output.
	 */
	public function get_unit_discussion() {

		global $wp;

		if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
			$unit_id = CoursePress_Data_Unit::by_name( $wp->query_vars['unitname'] );
		} else {
			$unit_id = 0;
		}

		$comments_args = array(
			// Change the title of send button.
			'label_submit' => 'Send',
			// Change the title of the reply secpertion.
			'title_reply' => 'Write a Reply or Comment',
			// Remove "Text or HTML to be displayed after the set of comment fields".
			'comment_notes_after' => '',
			// Redefine your own textarea (the comment body).
			'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
		);

		ob_start();
		comment_form( $comments_args, $unit_id );
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Shows the course title.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_unit_title( $atts ) {

		$atts = shortcode_atts( array(
			'unit_id'   => in_the_loop() ? get_the_ID() : '',
			'title_tag' => '',
			'link'      => 'no',
			'class'     => '',
			'last_page' => 'no',
		), $atts, 'course_unit_title' );

		$unit_id   = (int) $atts['unit_id'];
		$title_tag = sanitize_html_class( $atts['title_tag'] );
		$link      = sanitize_html_class( $atts['link'] );
		$class     = sanitize_html_class( $atts['class'] );

		$title = get_the_title( $unit_id );
		$unit = coursepress_get_unit( $unit_id );

		$draft = get_post_status( $unit_id ) !== 'publish';
		$show_draft = $draft && CoursePress_Data_Capabilities::can_see_unit_draft();

		$the_permalink = $unit->get_permalink();

		$content = '';
		if ( ! $draft || ( $draft && $show_draft ) ) {
			$content = ! empty( $title_tag ) ? '<' . $title_tag . ' class="course-unit-title course-unit-title-' . $unit_id . ' ' . $class . '">' : '';
			$content .= 'yes' == $link ? '<a href="' . esc_url( $the_permalink ) . '" title="' . esc_attr( $title ) . '" class="unit-archive-single-title">' : '';
			$content .= $title;
			$content .= 'yes' == $link ? '</a>' : '';
			$content .= ! empty( $title_tag ) ? '</' . $title_tag . '>' : '';
		}

		// Return the html in the buffer.
		return $content;
	}
}
