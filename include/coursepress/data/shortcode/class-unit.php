<?php
/**
 * Shortcode handlers.
 *
 * @package CoursePress
 */

/**
 * Unit and module-related shortcodes.
 */
class CoursePress_Data_Shortcode_Unit {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_shortcode(
			'course_unit_details',
			array( __CLASS__, 'course_unit_details' )
		);
		add_shortcode(
			'course_unit_archive_submenu',
			array( __CLASS__, 'course_unit_archive_submenu' )
		);
		add_shortcode(
			'course_unit_submenu',
			array( __CLASS__, 'course_unit_submenu' )
		);
		add_shortcode(
			'module_status',
			array( __CLASS__, 'module_status' )
		);
		add_shortcode(
			'unit_discussion',
			array( __CLASS__, 'unit_discussion' )
		);
		add_shortcode(
			'course_unit_title',
			array( __CLASS__, 'course_unit_title' )
		);
	}

	public static function course_unit_details( $atts ) {
		global $post_id, $wp, $coursepress;

		extract( shortcode_atts(
			apply_filters( 'shortcode_atts_course_unit_details',
				array(
					'unit_id' => 0,
					'field' => 'post_title',
					'format' => 'true',
					'additional' => '2',
					'style' => 'flat',
					'class' => 'course-name-content',
					'tooltip_alt' => __( 'Percent of the unit completion', 'CP_TD' ),
					'knob_fg_color' => '#24bde6',
					'knob_bg_color' => '#e0e6eb',
					'knob_data_thickness' => '.35',
					'knob_data_width' => '70',
					'knob_data_height' => '70',
					'unit_title' => '',
					'unit_page_title_tag' => 'h3',
					'unit_page_title_tag_class' => '',
					'last_visited' => 'false',
					'parent_course_preceding_content' => __( 'Course: ', 'CP_TD' ),
					'student_id' => get_current_user_id(),
				)
			),
			$atts
		) );

		$unit_id = (int) $unit_id;
		$field = sanitize_html_class( $field );
		$format = cp_is_true( $format );
		$additional = sanitize_text_field( $additional );
		$style = sanitize_html_class( $style );
		$tooltip_alt = sanitize_text_field( $tooltip_alt );
		$knob_fg_color = sanitize_text_field( $knob_fg_color );
		$knob_bg_color = sanitize_text_field( $knob_bg_color );
		$knob_data_thickness = sanitize_text_field( $knob_data_thickness );
		$knob_data_width = (int) $knob_data_width;
		$knob_data_height = (int) $knob_data_height;
		$unit_title = sanitize_text_field( $unit_title );
		$unit_page_title_tag = sanitize_html_class( $unit_page_title_tag );
		$unit_page_title_tag_class = sanitize_html_class( $unit_page_title_tag_class );
		$parent_course_preceding_content = sanitize_text_field( $parent_course_preceding_content );
		$student_id = (int) $student_id;
		$last_visited = cp_is_true( $last_visited );
		$class = sanitize_html_class( $class );

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$content = '';
		if ( 'permalink' == $field ) {
			$unit = get_post( $unit_id );
			$content = get_permalink( $course_id ) . CoursePress_Core::get_slug( 'unit/' ) . $unit->post_name;
		}
		return $content;
	}

	// Alias
	public static function course_unit_submenu( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
		), $atts, 'course_unit_archive_submenu' ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) { return ''; }

		return do_shortcode( '[course_unit_archive_submenu course_id="' . $course_id . '"]' );
	}

	public static function course_unit_archive_submenu( $atts ) {
		extract( shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
			),
			$atts,
			'course_unit_archive_submenu'
		) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) { return ''; }

		$subpage = CoursePress_Helper_Utility::the_course_subpage();
		$course_status = get_post_status( $course_id );
		$course_base_url = CoursePress_Data_Course::get_course_url( $course_id );

		$content = '
		<div class="submenu-main-container cp-submenu">
			<ul id="submenu-main" class="submenu nav-submenu">
				<li class="submenu-item submenu-units ' . ( 'units' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'unit/' ) ) . '" class="course-units-link">' . esc_html__( 'Units', 'CP_TD' ) . '</a></li>
		';

		$student_id = is_user_logged_in() ? get_current_user_id() : false;
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = CoursePress_Data_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		if ( $enrolled || $is_instructor ) {
			$content .= '
				<li class="submenu-item submenu-notifications ' . ( 'notifications' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'notification' ) ) . '">' . esc_html__( 'Notifications', 'CP_TD' ) . '</a></li>
			';
		}

		$pages = CoursePress_Data_Course::allow_pages( $course_id );

		if ( $pages['course_discussion'] && ( $enrolled || $is_instructor ) ) {
			$content .= '<li class="submenu-item submenu-discussions ' . ( 'discussions' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'discussion' ) ) . '">' . esc_html__( 'Discussions', 'CP_TD' ) . '</a></li>';
		}

		if ( $pages['workbook'] && $enrolled ) {
			$content .= '<li class="submenu-item submenu-workbook ' . ( 'workbook' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'workbook' ) ) . '">' . esc_html__( 'Workbook', 'CP_TD' ) . '</a></li>';
		}

		if ( $pages['grades'] && $enrolled ) {
			$content .= '<li class="submenu-item submenu-grades ' . ( 'grades' == $subpage ? 'submenu-active' : '' ) . '"><a href="' . esc_url_raw( $course_base_url . CoursePress_Core::get_slug( 'grades' ) ) . '">' . esc_html__( 'Grades', 'CP_TD' ) . '</a></li>';
		}

		$content .= '<li class="submenu-item submenu-info"><a href="' . esc_url_raw( $course_base_url ) . '">' . esc_html__( 'Course Details', 'CP_TD' ) . '</a></li>';

		$show_link = false;

		if ( CP_IS_PREMIUM ) {
			// CERTIFICATE CLASS.
			$show_link = CoursePress_Data_Certificate::is_enabled() && CoursePress_Data_Student::is_enrolled_in_course( $student_id, $course_id );
		}

		if ( is_user_logged_in() && $show_link ) {
			// COMPLETION LOGIC.
			if ( CoursePress_Data_Student::is_course_complete( get_current_user_id(), $course_id ) ) {
				$certificate = CoursePress_Data_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', 'CP_TD' ) );

				$content .= '<li class="submenu-item submenu-certificate ' . ( 'certificate' == $subpage ? 'submenu-active' : '') . '">' . $certificate . '</li>';
			}
		}

		$content .= '
			</ul>
		</div>
		';

		return $content;
	}

	public static function module_status( $atts ) {

		extract( shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
				'unit_id' => CoursePress_Helper_Utility::the_post( true ),
				'previous_unit' => false,
				'message' => __( '%d of %d required elements completed.', 'CP_TD' ),
				'format' => 'true',
			),
			$atts,
			'module_status'
		) );

		$message = sanitize_text_field( $message );
		$format = sanitize_text_field( $format );
		$format = 'true' == $format ? true : false;

		$course_id = (int) $course_id;
		$unit_id = (int) $unit_id;
		$previous_unit_id = empty( $previous_unit ) ? false : (int) $previous_unit;

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
				$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'CP_TD' );
				$content .= CoursePress_Helper_UI::get_message_required_modules( $first_line );
			} elseif ( $unit_status['completion_required']['enabled'] && ! $unit_status['completion_required']['result'] ) {
				$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'CP_TD' );
				$content .= CoursePress_Helper_UI::get_message_required_modules( $first_line );
			} elseif ( $unit_status['passed_required']['enabled'] && ! $unit_status['passed_required']['result'] ) {
				/**
				 * User also needs to pass all required assessments
				 */
				$first_line = __( 'You need to pass all the REQUIRED modules before this unit.', 'CP_TD' );
				$content .= CoursePress_Helper_UI::get_message_required_modules( $first_line );
			}
			if ( ! empty( $unit_status['date_restriction'] ) && ! $unit_status['date_restriction']['result'] ) {
				$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id );
				if ( ! empty( $unit_availability_date ) ) {
					$available_on = date_i18n( get_option( 'date_format' ), CoursePress_Data_Course::strtotime( $unit_availability_date ) );
					$content .= esc_html__( 'This unit will be available on ', 'CP_TD' ) . ' ' . $available_on;
				}
			}
		}

		$content .= '</span>';

		return $content;
	}

	public static function unit_discussion( $atts ) {
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
	 */
	public static function course_unit_title( $atts ) {
		extract( shortcode_atts( array(
			'unit_id'   => in_the_loop() ? get_the_ID() : '',
			'title_tag' => '',
			'link'      => 'no',
			'class'     => '',
			'last_page' => 'no',
		), $atts, 'course_unit_title' ) );

		$unit_id   = (int) $unit_id;
		$course_id = (int) get_post_field( 'post_parent', $unit_id );
		$title_tag = sanitize_html_class( $title_tag );
		$link      = sanitize_html_class( $link );
		$last_page = sanitize_html_class( $last_page );
		$class     = sanitize_html_class( $class );

		$title = get_the_title( $unit_id );

		$draft      = get_post_status( $unit_id ) !== 'publish';
		$show_draft = $draft && cp_can_see_unit_draft();

		$the_permalink = CoursePress_Data_Unit::get_url( $unit_id );

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
