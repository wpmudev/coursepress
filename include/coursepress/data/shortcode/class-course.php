<?php
/**
 * Shortcode handlers.
 *
 * @package CoursePress
 */

/**
 * Course-related shortcodes.
 *
 * Shortcodes output details about a single course.
 */
class CoursePress_Data_Shortcode_Course {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public static function init() {

		add_shortcode(
			'course',
			array( __CLASS__, 'course' )
		);
		// Almost identical to [course] but returns a single value only.
		add_shortcode(
			'course_details',
			array( __CLASS__, 'course_details' )
		);

		add_shortcode(
			'courses_urls',
			array( __CLASS__, 'courses_urls' )
		);
		add_shortcode(
			'course_title',
			array( __CLASS__, 'course_title' )
		);
		add_shortcode(
			'course_summary',
			array( __CLASS__, 'course_summary' )
		);
		add_shortcode(
			'course_description',
			array( __CLASS__, 'course_description' )
		);
		add_shortcode(
			'course_start',
			array( __CLASS__, 'course_start' )
		);
		add_shortcode(
			'course_end',
			array( __CLASS__, 'course_end' )
		);
		add_shortcode(
			'course_dates',
			array( __CLASS__, 'course_dates' )
		);
		add_shortcode(
			'course_enrollment_start',
			array( __CLASS__, 'course_enrollment_start' )
		);
		add_shortcode(
			'course_enrollment_end',
			array( __CLASS__, 'course_enrollment_end' )
		);
		add_shortcode(
			'course_enrollment_dates',
			array( __CLASS__, 'course_enrollment_dates' )
		);
		add_shortcode(
			'course_class_size',
			array( __CLASS__, 'course_class_size' )
		);
		add_shortcode(
			'course_cost',
			array( __CLASS__, 'course_cost' )
		);
		add_shortcode(
			'course_language',
			array( __CLASS__, 'course_language' )
		);
		add_shortcode(
			'course_category',
			array( __CLASS__, 'course_category' )
		);
		add_shortcode(
			'course_enrollment_type',
			array( __CLASS__, 'course_enrollment_type' )
		);
		add_shortcode(
			'course_list_image',
			array( __CLASS__, 'course_list_image' )
		);
		add_shortcode(
			'course_featured_video',
			array( __CLASS__, 'course_featured_video' )
		);
		add_shortcode(
			'course_thumbnail',
			array( __CLASS__, 'course_thumbnail' )
		);
		add_shortcode(
			'course_action_links',
			array( __CLASS__, 'course_action_links' )
		);
		add_shortcode(
			'course_media',
			array( __CLASS__, 'course_media' )
		);
		add_shortcode(
			'course_link',
			array( __CLASS__, 'course_link' )
		);
		add_shortcode(
			'course_length',
			array( __CLASS__, 'course_length' )
		);
		add_shortcode(
			'course_time_estimation',
			array( __CLASS__, 'course_time_estimation' )
		);
		add_shortcode(
			'course_random',
			array( __CLASS__, 'course_random' )
		);
		add_shortcode(
			'get_parent_course_id',
			array( __CLASS__, 'get_parent_course_id' )
		);
	}

	/**
	 * Creates a [course] shortcode.
	 *
	 * This is just a wrapper shortcode for several other shortcodes.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course( $atts ) {
		extract(
			shortcode_atts(
				array(
					'course_id' => CoursePress_Helper_Utility::the_course( true ),
					'show' => 'summary',
					'date_format' => get_option( 'date_format' ),
					'label_tag' => 'strong',
					'label_delimeter' => ':',
					'show_title' => 'no',
				),
				$atts,
				'course'
			)
		);

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$show = sanitize_text_field( $show );
		$date_format = sanitize_text_field( $date_format );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$show_title = cp_is_true( $show_title );

		$sections = explode( ',', $show );
		$content = '';

		foreach ( $sections as $section ) {
			$section = trim( strtolower( $section ) );
			$code = false;

			switch ( $section ) {
				case 'title': // [course_title].
					if ( $show_title ) {
						$code = '[course_title title_tag="h3" course_id="' . $course_id . '" course_id="' . $course_id . '"]';
					}
					break;

				case 'summary': // [course_summary].
					$code = '[course_summary course_id="' . $course_id . '"]';
					break;

				case 'description': // [course_description].
					$code = '[course_description course_id="' . $course_id . '"]';
					break;

				case 'start': // [course_start].
					$code = '[course_start date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]';
					break;

				case 'end': // [course_end].
					$code = '[course_end date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]';
					break;

				case 'dates': // [course_dates].
					$code = '[course_dates date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]';
					break;

				case 'enrollment_start': // [course_enrollment_start].
					$code = '[course_enrollment_start date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]';
					break;

				case 'enrollment_end': // [course_enrollment_end].
					$code = '[course_enrollment_end date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]';
					break;

				case 'enrollment_dates': // [course_enrollment_dates].
					$code = '[course_enrollment_dates date_format="' . $date_format . '" label_tag="' . $label_tag . '" label_delimeter="' . $label_delimeter . '" course_id="' . $course_id . '"]';
					break;

				case 'class_size': // [course_summary].
					$code = '[course_class_size course_id="' . $course_id . '"]';
					break;

				case 'cost': // [course_cost].
					$code = '[course_cost course_id="' . $course_id . '"]';
					break;

				case 'language': // [course_language].
					$code = '[course_language course_id="' . $course_id . '"]';
					break;

				case 'category': // [course_category].
					$code = '[course_category course_id="' . $course_id . '"]';
					break;

				case 'enrollment_type': // [course_enrollment_type].
					$code = '[course_enrollment_type course_id="' . $course_id . '"]';
					break;

				case 'instructors': // [course_instructors].
					$code = '[course_instructors course_id="' . $course_id . '"]';
					break;

				case 'image': // [course_list_image].
					$code = '[course_list_image course_id="' . $course_id . '"]';
					break;

				case 'video': // [course_featured_video].
					$code = '[course_featured_video course_id="' . $course_id . '"]';
					break;

				case 'button': // [course_join_button].
					$code = '[course_join_button course_id="' . $course_id . '"]';
					break;

				case 'thumbnail': // [course_thumbnail].
					$code = '[course_thumbnail course_id="' . $course_id . '"]';
					break;

				case 'action_links': // [course_action_links].
					$code = '[course_action_links course_id="' . $course_id . '"]';
					break;

				case 'media': // [course_media].
					$code = '[course_media course_id="' . $course_id . '"]';
					break;

				case 'calendar': // [course_calendar].
					$code = '[course_calendar course_id="' . $course_id . '"]';
					break;
			}

			if ( $code ) {
				$content .= do_shortcode( $code );
			}
		}

		return $content;
	}

	/**
	 * Alias for the course shortcode. However, this shortcode has less options
	 * as it only takes an course-ID and a single field name as input.
	 *
	 * @since  2.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Requested course detail.
	 */
	public static function course_details( $atts ) {
		global $wp_query;

		extract(
			shortcode_atts(
				array(
					'course_id' => ( isset( $wp_query->post->ID ) ? $wp_query->post->ID : 0 ),
					'field' => 'course_start_date',
				),
				$atts
			)
		);

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$action = sanitize_html_class( $field );

		// Specify alias values for certain actions.
		$translate_action = array(
			'enroll_type' => 'enrollment_type',
			'course_start_date' => 'start',
			'course_end_date' => 'end',
			'enrollment_start_date' => 'enrollment_start',
			'enrollment_end_date' => 'enrollment_end',
			'price' => 'cost',
		);

		// Check if user specified an alias.
		if ( array_key_exists( $action, $translate_action ) ) {
			$action = $translate_action[ $action ];
		}

		$args = array(
			'course_id' => $course_id,
			'show' => $action,
		);
		$content = self::course( $args );

		CoursePress_Data_Student::log_student_activity( 'course_seen' );

		return $content;
	}

	public static function courses_urls( $atts ) {
		global $enrollment_process_url, $signup_url;

		shortcode_atts(
			array(
				'url' => '',
			),
			$atts
		);

		switch ( $atts['url'] ) {
			case 'enrollment-process':
				return $enrollment_process_url;

			case 'signup':
				return $signup_url;
		}

		return '';
	}

	/**
	 * Shows the course title.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_title( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'title_tag' => 'h3',
			'link' => 'no',
			'class' => '',
		), $atts, 'course_title' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$title_tag = sanitize_html_class( $title_tag );
		$link = sanitize_html_class( $link );
		$class = sanitize_html_class( $class );

		$title = get_the_title( $course_id );

		$content = ! empty( $title_tag ) ? '<' . $title_tag . ' class="course-title course-title-' . $course_id . ' ' . $class . '">' : '';
		$content .= 'yes' == $link ? '<a href="' . get_permalink( $course_id ) . '" title="' . $title . '">' : '';
		$content .= apply_filters( 'coursepress_schema', $title, 'title' );
		$content .= 'yes' == $link ? '</a>' : '';
		$content .= ! empty( $title_tag ) ? '</' . $title_tag . '>' : '';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course title with a link to the course.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_link( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'element' => 'span',
			'class' => 'course-link',
		), $atts, 'course_link' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }
		$element = sanitize_html_class( $element );
		$class = sanitize_html_class( $class );

		$title = get_the_title( $course_id );

		$content = do_shortcode( '[course_title course_id="' . $course_id . '" title_tag="' . $element . '" link="yes" class="' . $class . '"]' );

		return $content;
	}

	/**
	 * Shows the course summary/excerpt.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_summary( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class' => '',
			'length' => '',
		), $atts, 'course_summary' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }
		$class = sanitize_html_class( $class );
		$length = (int) $length;
		$length = empty( $length ) ? '' : $length;
		$course = get_post( $course_id );

		$content = '<div class="course-summary course-summary-' . $course_id . ' ' . $class . '">';

		if ( $course && is_object( $course ) ) {
			if ( is_numeric( $length ) ) {
				$content .= CoursePress_Helper_Utility::truncate_html( do_shortcode( $course->post_excerpt ), $length );
			} else {
				$content .= do_shortcode( $course->post_excerpt );
			}
		}

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course description.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_description( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class' => '',
			'label' => '',
		), $atts, 'course_description' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }
		$class = sanitize_html_class( $class );
		$title = sanitize_text_field( $label );
		$title = ! empty( $title ) ? '<h3 class="section-title">' . esc_html( $title ) . '</h3>' : $title;
		$course = get_post( $course_id );

		$course_title = apply_filters( 'coursepress_schema', $course->post_title, 'title' );
		$content = '<span style="display:none;">' . $course_title . '</span>';

		/**
		 * schema.org
		 */
		$content_schema = apply_filters( 'coursepress_schema', '', 'description' );

		$content .= '<div class="course-description course-description-' . $course_id . ' ' . $class . '"' . $content_schema . '>';
		$content .= $title;
		$content .= do_shortcode( $course->post_content );
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course start date.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_start( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Course Start Date', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'class' => '',
		), $atts, 'course_start' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }
		$date_format = apply_filters( 'coursepress_course_courses_list_date_format', sanitize_text_field( $date_format ) );
		$time_format = apply_filters( 'coursepress_course_courses_list_time_format', get_option( 'time_format' ) );
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_text_field( $label_delimeter );
		$class = sanitize_html_class( $class );

		$start_date = CoursePress_Data_Course::get_setting( $course_id, 'course_start_date' );
		$open_ended = CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended' );

		$content = '<div class="course-start-date course-start-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		if ( $open_ended || empty( $start_date ) ) {
			$content .= __( 'already started', 'coursepress' );
		} else {
			$content .= str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $start_date ) ) );
			// Add time if different to '00:00:00'
			$content .= ( date( 'H:i:s', CoursePress_Data_Course::strtotime( $start_date ) ) != '00:00:00' ) ? str_replace( ' ', '&nbsp;', ' / ' . date_i18n( $time_format , CoursePress_Data_Course::strtotime( $start_date ) ) ) : '';
		}
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course length in weeks.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_length( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'label' => __( 'Course Length', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'class' => '',
			'suffix' => ' Weeks',
		), $atts, 'course_start' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_text_field( $label_delimeter );
		$class = sanitize_html_class( $class );

		$open_ended = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended' ) );
		if ( $open_ended ) {
			return '';
		}

		$start_date = CoursePress_Data_Course::get_setting( $course_id, 'course_start_date' );
		$end_date = CoursePress_Data_Course::get_setting( $course_id, 'course_end_date' );

		$length = ceil( ( CoursePress_Data_Course::strtotime( $end_date ) - CoursePress_Data_Course::strtotime( $start_date ) ) / 604800 );

		if ( $length <= 0 ) {
			return '';
		}

		$content = '<div class="course-length course-length-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $length . $suffix;
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course end date.
	 *
	 * If the course has no end date, the no_date_text will be displayed instead of the date.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_end( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Course End Date: ', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'no_date_text' => __( 'No End Date', 'coursepress' ),
			'class' => '',
		), $atts, 'course_end' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }
		$date_format = sanitize_text_field( $date_format );
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text = sanitize_text_field( $no_date_text );
		$class = sanitize_html_class( $class );

		$end_date = CoursePress_Data_Course::get_setting( $course_id, 'course_end_date' );
		$open_ended = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended', false ) );

		$content = '<div class="course-end-date course-end-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}
		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $end_date ) ) );
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course start and end date.
	 *
	 * If the course has no end date, the no_date_text will be displayed instead of the date.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_dates( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Course Dates: ', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'no_date_text' => __( 'No End Date', 'coursepress' ),
			'alt_display_text' => __( 'Open-ended', 'coursepress' ),
			'show_alt_display' => 'no',
			'class' => '',
		), $atts, 'course_dates' ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) { return ''; }
		$date_format = sanitize_text_field( $date_format );
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text = sanitize_text_field( $no_date_text );
		$alt_display_text = sanitize_text_field( $alt_display_text );
		$show_alt_display = sanitize_html_class( $show_alt_display );
		$class = sanitize_html_class( $class );

		$start_date = CoursePress_Data_Course::get_setting( $course_id, 'course_start_date' );
		$end_date = CoursePress_Data_Course::get_setting( $course_id, 'course_end_date' );
		$open_ended = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended', false ) );

		$end_output = $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', ( date_i18n( $date_format, CoursePress_Data_Course::strtotime( $end_date ) ) ) );
		$show_alt_display = cp_is_true( $show_alt_display );

		$content = '
			<div class="course-dates course-dates-' . esc_attr( $course_id ) . ' ' . esc_attr( $class ) . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '>';
		}
		$content .= ' ';
		if ( $show_alt_display && $open_ended ) {
			$content .= $alt_display_text;
		} else {
			$content .= str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $start_date ) ) ) . ' - ' . $end_output;
		}

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the enrollment start date.
	 *
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_enrollment_start( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Enrollment Start Date: ', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'no_date_text' => __( 'Enroll Anytime', 'coursepress' ),
			'class' => '',
		), $atts, 'course_enrollment_start' ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) { return ''; }
		$date_format = sanitize_text_field( $date_format );
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text = sanitize_text_field( $no_date_text );
		$class = sanitize_html_class( $class );

		$now = CoursePress_Data_Course::time_now();
		$start_date = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_start_date' );
		$start_date = CoursePress_Data_Course::strtotime( $start_date );
		$date = str_replace( ' ', '&nbsp;', date_i18n( $date_format, $start_date ) );
		$open_ended = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'enrollment_open_ended', false ) );

		$content = '<div class="enrollment-start-date enrollment-start-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}
		if ( $start_date > $now ) {
			$content .= $date;
		} else {
			$content .= $open_ended ? $no_date_text : $date;
		}

		//$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $start_date ) ) );

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the enrollment end date.
	 *
	 * By default this will not show for open ended enrollments.
	 * Set show_all_dates="yes" to make it display.
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_enrollment_end( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Enrollment End Date: ', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'no_date_text' => __( 'Enroll Anytime ', 'coursepress' ),
			'show_all_dates' => 'no',
			'class' => '',
		), $atts, 'course_enrollment_end' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$date_format = sanitize_text_field( $date_format );
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text = sanitize_text_field( $no_date_text );
		$show_all_dates = sanitize_html_class( $show_all_dates );
		$class = sanitize_html_class( $class );

		$end_date = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_end_date' );
		$open_ended = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'enrollment_open_ended', false ) );

		$content = '<div class="enrollment-end-date enrollment-end-date-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $end_date ) ) );

		$content .= '</div>';

		if ( ! $open_ended || 'yes' == $show_all_dates ) {
			return $content;
		} else {
			return '';
		}

	}

	/**
	 * Shows the enrollment start and end date.
	 *
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_enrollment_dates( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Enrollment Dates: ', 'coursepress' ),
			'label_enrolled' => __( 'You Enrolled on: ', 'coursepress' ),
			'show_enrolled_display' => 'yes',
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'no_date_text' => __( 'Enroll Anytime', 'coursepress' ),
			'alt_display_text' => __( 'Open-ended', 'coursepress' ),
			'show_alt_display' => 'no',
			'class' => '',
		), $atts, 'course_enrollment_dates' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$date_format = sanitize_text_field( $date_format );
		$label = sanitize_text_field( $label );
		$label_enrolled = sanitize_text_field( $label_enrolled );
		$show_enrolled_display = sanitize_html_class( $show_enrolled_display );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_date_text = sanitize_text_field( $no_date_text );
		$alt_display_text = sanitize_text_field( $alt_display_text );
		$show_alt_display = sanitize_text_field( $show_alt_display );
		$class = sanitize_html_class( $class );

		$class = sanitize_html_class( $class );

		$start_date = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_start_date' );
		$end_date = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_end_date' );
		$open_ended = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'enrollment_open_ended', false ) );
		$show_alt_display = cp_is_true( $show_alt_display );

		$is_enrolled = false;

		if ( 'yes' == strtolower( $show_enrolled_display ) ) {
			$enrollment_date = CoursePress_Data_Course::student_enrolled( get_current_user_id(), $course_id );
			if ( ! empty( $enrollment_date ) ) {
				$enrollment_date = date_i18n( $date_format, CoursePress_Data_Course::strtotime( $enrollment_date ) );
				$label = $label_enrolled;
			}
		}

		$content = '<div class="enrollment-dates enrollment-dates-' . $course_id . ' ' . $class . '">';

		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		if ( empty( $enrollment_date ) ) {
			if ( $show_alt_display && $open_ended ) {
				$content .= $alt_display_text;
			} else {
				$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $start_date ) ) ) . ' - ' . str_replace( ' ', '&nbsp;', date_i18n( $date_format, CoursePress_Data_Course::strtotime( $end_date ) ) );
			}
		} else {
			// User is enrolled
			$content .= $enrollment_date;
		}

		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows a friendly course enrollment type message.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_enrollment_type( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'anyone_text' => __( 'Anyone', 'coursepress' ),
			'class' => '',
			'label_delimeter' => ':',
			'label_tag' => 'strong',
			'label' => __( 'Who can Enroll: ', 'coursepress' ),
			'manual_text' => __( 'Students are added by instructors.', 'coursepress' ),
			'passcode_text' => __( 'A passcode is required to enroll.', 'coursepress' ),
			'prerequisite_text' => __( 'Students need to complete %s first.', 'coursepress' ),
			'registered_text' => __( 'Registered users.', 'coursepress' ),
		), $atts, 'course_enrollment_type' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$manual_text = sanitize_text_field( $manual_text );
		$prerequisite_text = sanitize_text_field( $prerequisite_text );
		$passcode_text = sanitize_text_field( $passcode_text );
		$anyone_text = sanitize_text_field( $anyone_text );
		$registered_text = sanitize_text_field( $registered_text );
		$class = sanitize_html_class( $class );

		$enrollment_type = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type' );

		$enrollment_text = '';

		switch ( $enrollment_type ) {
			case 'anyone':
				$enrollment_text = $anyone_text;
				break;

			case 'registered':
				$enrollment_text = $registered_text;
				break;

			case 'passcode':
				$enrollment_text = $passcode_text;
				break;

			case 'prerequisite':
				$prereq = CoursePress_Data_Course::get_prerequisites( $course_id );
				$prereq_courses = array();
				foreach ( $prereq as $prereq_id ) {
					$prereq_courses[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( get_permalink( $prereq_id ) ),
						get_the_title( $prereq_id )
					);
				}
				$enrollment_text = sprintf( $prerequisite_text, implode( ', ', $prereq_courses ) );
				break;

			case 'manually':
				$enrollment_text = $manual_text;
				break;
		}

		// For non-standard enrolment types.
		$enrollment_text = apply_filters( 'coursepress_course_enrollment_type_text', $enrollment_text );

		$content = '<div class="course-enrollment-type course-enrollment-type-' . $course_id . ' ' . $class . '">';
		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $enrollment_text;
		$content .= '</div>';

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course class size.
	 *
	 * If there is no limit set on the course nothing will be displayed.
	 * You can make the no_limit_text display by setting show_no_limit="yes".
	 *
	 * By default it will show the remaining places,
	 * turn this off by setting show_remaining="no".
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_class_size( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class' => '',
			'label_delimeter' => ':',
			'label_tag' => 'strong',
			'label' => __( 'Class Size: ', 'coursepress' ),
			'no_limit_text' => __( 'Unlimited', 'coursepress' ),
			'remaining_text' => __( '(%d places left)', 'coursepress' ),
			'show_no_limit' => 'no',
			'show_remaining' => 'yes',
		), $atts, 'course_class_size' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$show_no_limit = sanitize_html_class( $show_no_limit );
		$show_remaining = sanitize_html_class( $show_remaining );
		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_limit_text = sanitize_text_field( $no_limit_text );
		$remaining_text = sanitize_text_field( $remaining_text );
		$class = sanitize_html_class( $class );

		$content = '';

		$is_limited = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'class_limited', false ) );
		$class_size = (int) CoursePress_Data_Course::get_setting( $course_id, 'class_size' );
		$show_no_limit = cp_is_true( $show_no_limit );
		$show_remaining = cp_is_true( $show_remaining );

		if ( $is_limited ) {
			$content .= '<span class="total">' . $class_size . '</span>';

			if ( $show_remaining ) {
				$remaining = $class_size - (int) CoursePress_Data_Course::count_students( $course_id );
				$content .= ' <span class="remaining">' . sprintf( $remaining_text, $remaining ) . '</span>';
			}
		} else {
			if ( $show_no_limit ) {
				$content .= $no_limit_text;
			}
		}

		if ( ! empty( $content ) ) {
			$display_content = $content;

			$content = '<div class="course-class-size course-class-size-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
			}
			$content .= $display_content;
			$content .= '</div>';
		}

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course cost.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_cost( $atts ) {
		global $coursepress;
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class' => '',
			'label_delimeter' => ': ',
			'label_tag' => 'strong',
			'label' => __( 'Price:&nbsp;', 'coursepress' ),
			'no_cost_text' => __( 'FREE', 'coursepress' ),
			'show_icon' => 'no',
		), $atts, 'course_cost' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_cost_text = sanitize_text_field( $no_cost_text );
		$show_icon = sanitize_text_field( $show_icon );
		$class = sanitize_html_class( $class );

		$show_icon = cp_is_true( $show_icon );
		$is_paid = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'payment_paid_course', false ) );

		$content = '';

		if ( $is_paid ) {
			$content .= apply_filters( 'coursepress_shortcode_course_cost', '', $course_id );
		} else {
			if ( $show_icon ) {
				$content .= '<span class="product_price">' . $no_cost_text . '</span>';
			} else {
				$content .= $no_cost_text;
			}
		}

		if ( ! empty( $content ) ) {
			$display_content = $content;

			$content = '<div class="course-cost course-cost-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
			}
			$content .= $display_content;
			$content .= '</div>';
		}

		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course language.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_language( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'label' => __( 'Course Language: ', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'class' => '',
		), $atts, 'course_language' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$class = sanitize_html_class( $class );

		$language = CoursePress_Data_Course::get_setting( $course_id, 'course_language', '' );

		if ( ! empty( $language ) ) {
			$content = '<div class="course-language course-language-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
			}

			$content .= $language;
			$content .= '</div>';

			return $content;
		}

		return '';
	}

	/**
	 * Shows the course category.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_category( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'label' => __( 'Course Category: ', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ':',
			'no_category_text' => __( 'None', 'coursepress' ),
			'class' => '',
		), $atts, 'course_category' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$no_category_text = sanitize_text_field( $no_category_text );
		$class = sanitize_html_class( $class );

		$content = '';

		$categories = CoursePress_Data_Course::get_course_categories( $course_id );

		$counter = 0;
		foreach ( $categories as $key => $category ) {
			$counter += 1;
			$content .= $category;
			$content .= count( $categories ) > $counter ? ', ' : '';
		}

		if ( empty( $categories ) ) {
			return '';
		}

		$display_content = $content;

		$content = '<div class="course-category course-category-' . $course_id . ' ' . $class . '">';
		if ( ! empty( $label ) ) {
			$content .= '<' . esc_html( $label_tag ) . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . esc_html( $label_tag ) . '> ';
		}

		$content .= $display_content;
		$content .= '</div>';

		return $content;
	}


	/**
	 * Shows the course list image.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_list_image( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'width' => 'default',
			'height' => 'default',
			'class' => '',
		), $atts, 'course_list_image' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$width = sanitize_html_class( $width );
		$height = sanitize_html_class( $height );
		$class = sanitize_html_class( $class );

		// Saves some overhead by not loading the post again if we don't need to.
		$course = get_post( $course_id );

		$image_src = CoursePress_Data_Course::get_setting( $course_id, 'listing_image', '' );

		if ( empty( $image_src ) ) {
			return '';
		}

		list( $img_w, $img_h ) = getimagesize( $image_src );

		// Note: by using both it usually reverts to the width.
		$width = 'default' == $width ? $img_w : $width;
		$height = 'default' == $height ? $img_h : $height;
		/**
		 * schema.org
		 */
		$schema = apply_filters( 'coursepress_schema', '', 'image' );
		/**
		 * wrapper start
		 */
		$content = sprintf(
			'<div class="course-list-image course-list-image-%d %s">',
			$course_id,
			esc_attr( $class )
		);
		$content .= '<img width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" src="' . esc_url( $image_src ) . '" alt="' . esc_attr( $course->post_title ) . '" title="' . esc_attr( $course->post_title ) . '"'.$schema.' />';
		/**
		 * wrapper end
		 */
		$content .= '</div>';
		return $content;
	}

	/**
	 * Shows the course featured video.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_featured_video( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'width' => 'default',
			'height' => 'default',
			'class' => '',
		), $atts, 'course_featured_video' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$width = sanitize_text_field( $width );
		$height = sanitize_html_class( $height );
		$class = sanitize_html_class( $class );

		$video_src = CoursePress_Data_Course::get_setting( $course_id, 'featured_video', '' );
		$video_extension = pathinfo( $video_src, PATHINFO_EXTENSION );
		$content = '';

		if ( ! empty( $video_extension ) ) {
			// It's a file, most likely on the server.
			$attr = array(
				'src' => $video_src,
			);

			if ( 'default' != $width ) {
				$attr['width'] = $width;
			}

			if ( 'default' != $height ) {
				$attr['height'] = $height;
			}

			$content .= wp_video_shortcode( $attr );
		} else {
			$embed_args = array();

			if ( 'default' != $width ) {
				$embed_args['width'] = $width;
			}

			if ( 'default' != $height ) {
				$embed_args['height'] = $height;
			}

			// Add YouTube filter.
			if ( preg_match( '/youtube.com|youtu.be/', $video_src ) ) {
				add_filter(
					'oembed_result',
					array( 'CoursePress_Helper_Utility', 'remove_related_videos' ),
					10,
					3
				);
			}

			$video = wp_oembed_get( $video_src, $embed_args );
			$content .= $video;
		}

		$video_content = $content;

		$content = '<div class="course-featured-video course-featured-video-' . $course_id . ' ' . $class . '">';
		$content .= $video_content;
		$content .= '</div>';

		return $content;
	}

	/**
	 * Shows the course thumbnail.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_thumbnail( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'wrapper' => 'figure',
			'class' => '',
		), $atts, 'course_thumbnail' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$wrapper = sanitize_html_class( $wrapper );
		$class = sanitize_html_class( $class );

		return do_shortcode( '[course_media course_id="' . $course_id . '" wrapper="' . $wrapper . '" class="' . $class . '" type="thumbnail"]' );
	}

	/**
	 * Shows the course media (video or image).
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_media( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class' => '',
			'height' => CoursePress_Core::get_setting( 'course/image_height' ),
			'list_page' => 'no',
			'priority' => '', // Gives priority to video (or image).
			'type' => '', // Default, video, image.
			'width' => CoursePress_Core::get_setting( 'course/image_width' ),
			'wrapper' => '',
		), $atts, 'course_media' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$type = sanitize_text_field( $type );
		$priority = sanitize_text_field( $priority );
		$list_page = cp_is_true( sanitize_html_class( $list_page ) );
		$class = sanitize_html_class( $class );
		$wrapper = sanitize_html_class( $wrapper );
		$height = sanitize_text_field( $height );
		$width = sanitize_text_field( $width );

		// We'll use pixel if none is set
		if ( ! empty( $width ) && (int) $width == $width ) {
			$width .= 'px';
		}
		if ( ! empty( $height ) && (int) $height == $height ) {
			$height .= 'px';
		}

		if ( ! $list_page ) {
			$type = empty( $type ) ? CoursePress_Core::get_setting( 'course/details_media_type', 'default' ) : $type;
			$priority = empty( $priority ) ? CoursePress_Core::get_setting( 'course/details_media_priority', 'video' ) : $priority;
		} else {
			$type = empty( $type ) ? CoursePress_Core::get_setting( 'course/listing_media_type', 'default' ) : $type;
			$priority = empty( $priority ) ? CoursePress_Core::get_setting( 'course/listing_media_priority', 'image' ) : $priority;
		}

		$priority = 'default' != $type ? false : $priority;

		// Saves some overhead by not loading the post again if we don't need to.
		$class = sanitize_html_class( $class );

		$course_video = CoursePress_Data_Course::get_setting( $course_id, 'featured_video' );
		$course_image = CoursePress_Data_Course::get_setting( $course_id, 'listing_image' );

		$content = '';

		if ( 'thumbnail' == $type ) {
			$type = 'image';
            $priority = 'image';
            $width = $height = '';
		}

		// If no wrapper and we're specifying a width and height, we need one, so will use div.
		if ( empty( $wrapper ) && ( ! empty( $width ) || ! empty( $height ) ) ) {
			$wrapper = 'div';
		}

		$wrapper_style = '';
		$wrapper_style .= ! empty( $width ) ? 'width:' . $width . ';' : '';
		$wrapper_style .= ! empty( $width ) ? 'height:' . $height . ';' : '';

		if ( is_singular( 'course' ) ) {
			$wrapper_style = '';
		}

		if ( ( ( 'default' == $type && 'video' == $priority ) || 'video' == $type || ( 'default' == $type && 'image' == $priority && empty( $course_image ) ) ) && ! empty( $course_video ) ) {

			$content = '<div class="video_player course-featured-media course-featured-media-' . $course_id . ' ' . $class . '">';

			$content .= ! empty( $wrapper ) ? '<' . $wrapper . ' style="' . $wrapper_style . '">' : '';

			$video_extension = pathinfo( $course_video, PATHINFO_EXTENSION );

			if ( ! empty( $video_extension ) ) {
				$attr = array(
					'src' => $course_video,
				);
				$content .= wp_video_shortcode( $attr );
			} else {
				$embed_args = array();

				// Add YouTube filter.
				if ( preg_match( '/youtube.com|youtu.be/', $course_video ) ) {
					add_filter( 'oembed_result', array(
						'CoursePress_Helper_Utility',
						'remove_related_videos',
					), 10, 3 );
				}

				$content .= wp_oembed_get( $course_video, $embed_args );
			}

			$content .= ! empty( $wrapper ) ? '</' . $wrapper . '>' : '';
			$content .= '</div>';
		}

		if ( ( ( 'default' == $type && 'image' == $priority ) || 'image' == $type || ( 'default' == $type && 'video' == $priority && empty( $course_video ) ) ) && ! empty( $course_image ) ) {

			$content .= '<div class="course-thumbnail course-featured-media course-featured-media-' . $course_id . ' ' . $class . '">';
			$content .= ! empty( $wrapper ) ? '<' . $wrapper . ' style="' . $wrapper_style . '">' : '';

			$content .= '<img src="' . esc_url( $course_image ) . '" class="course-media-img"></img>';

			$content .= ! empty( $wrapper ) ? '</' . $wrapper . '>' : '';
			$content .= '</div>';
		}

		return $content;
	}

	/**
	 * Shows the course action links.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_action_links( $atts ) {
		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'class' => '',
		), $atts, 'course_action_links' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$class = sanitize_html_class( $class );

		$course_start_date = CoursePress_Data_Course::get_setting( $course_id, 'course_start_date' );
		$course_end_date = CoursePress_Data_Course::get_setting( $course_id, 'course_end_date' );
		$open_ended_course = cp_is_true( CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended' ) );

		$withdraw_link_visible = false;
		$content = '';
		$student_id = get_current_user_id();
		$now = CoursePress_Data_Course::time_now();

		if ( ! empty( $student_id ) && CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) ) {
			if ( ( ( CoursePress_Data_Course::strtotime( $course_start_date ) <= $now && CoursePress_Data_Course::strtotime( $course_end_date ) >= $now ) || ( CoursePress_Data_Course::strtotime( $course_end_date ) >= $now ) ) || $open_ended_course ) {
				// Course is currently active or is not yet active (will be active in the future).
				$withdraw_link_visible = true;
			}
		}

		$content = '<div class="apply-links course-action-links course-action-links-' . $course_id . ' ' . $class . '">';

		if ( $withdraw_link_visible ) {
			$content .= '<a href="' . wp_nonce_url( '?withdraw=' . $course_id, 'withdraw_from_course_' . $course_id, 'course_nonce' ) . '" onClick="return withdraw();">' . esc_html__( 'Withdraw', 'coursepress' ) . '</a> | ';
		}
		$content .= '<a href="' . get_permalink( $course_id ) . '">' . esc_html__( 'Course Details', 'coursepress' ) . '</a>';

		// Add certificate link.
		if ( CP_IS_PREMIUM ) {
			// COMPLETION LOGIC.
			// $content .= CP_Basic_Certificate::get_certificate_link( get_current_user_id(), $course_id, __( 'Certificate', 'coursepress' ), ' | ' );
		}

		$content .= '</div>';

		return $content;
	}

	/**
	 * Display list of random courses. By default 3 random courses are displayed.
	 *
	 * Note: This shortcode uses the SQL `ORDER BY RAND` option, which is
	 * disabled on WPEngine sites by default (it can slow down the site).
	 * Very likely this shortcode is not working on WPEngine.
	 *
	 * @since  1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_random( $atts ) {
		extract( shortcode_atts( array(
			'number' => 3,
			'featured_title' => 'default',
			'button_title' => 'default',
			'media_type' => 'default',
			'media_priority' => 'default',
			'course_class' => 'default',
			'class' => '',
		), $atts, 'course_random' ) );

		$number = (int) $number;
		$featured_title = sanitize_text_field( $featured_title );
		$button_title = sanitize_text_field( $button_title );
		$media_type = sanitize_html_class( $media_type );
		$media_priority = sanitize_html_class( $media_priority );
		$course_class = sanitize_html_class( $course_class );
		$class = sanitize_html_class( $class );

		$args = array(
			'post_type' => 'course',
			'posts_per_page' => $number,
			'orderby' => 'rand',
			'fields' => 'ids',
		);

		$courses = new WP_Query( $args );
		$courses = $courses->posts;
		$class = sanitize_html_class( $class );

		$content = 0 < count( $courses ) ? '<div class="course-random ' . $class . '">' : '';

		$featured_atts = '';

		if ( 'default' != $featured_title ) {
			$featured_atts .= 'featured_title="' . $featured_title . '" ';
		}
		if ( 'default' != $button_title ) {
			$featured_atts .= 'button_title="' . $button_title . '" ';
		}
		if ( 'default' != $media_type ) {
			$featured_atts .= 'media_type="' . $media_type . '" ';
		}
		if ( 'default' != $media_priority ) {
			$featured_atts .= 'media_priority="' . $media_priority . '" ';
		}
		if ( 'default' != $course_class ) {
			$featured_atts .= 'class="' . $course_class . '" ';
		}

		foreach ( $courses as $course ) {
			$content .= '<div class="course-item course-item-' . $course . '">';
			$content .= do_shortcode( '[course_featured course_id="' . $course . '" ' . $featured_atts . ']' );
			$content .= '</div>';
		}

		$content .= 0 < count( $courses ) ? '</div>' : '';

		return $content;
	}

	/**
	 * Shows the estimated course time.
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public static function course_time_estimation( $atts ) {
		$content = '';

		extract( shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'label' => __( 'Estimated Duration:&nbsp;', 'coursepress' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'wrapper' => 'no',
			'class' => '',
		), $atts, 'course_time_estimation' ) );

		$course_id = (int) $course_id;
		if ( empty( $course_id ) ) { return ''; }

		$label = sanitize_text_field( $label );
		$label_tag = sanitize_html_class( $label_tag );
		$label_delimeter = sanitize_html_class( $label_delimeter );
		$class = sanitize_html_class( $class );
		$wrapper = cp_is_true( sanitize_text_field( $wrapper ) );

		if ( $wrapper ) {
			$content .= '<div class="course-time-estimate course-time-estimate-' . $course_id . ' ' . $class . '">';
			if ( ! empty( $label ) ) {
				$content .= '<' . $label_tag . ' class="label">' . esc_html( $label ) . esc_html( $label_delimeter ) . '</' . $label_tag . '>';
			}
		}

		$content .= CoursePress_Data_Course::get_time_estimation( $course_id );

		if ( $wrapper ) {
			$content .= '</div>';
		}

		return $content;
	}

	/**
	 * Return the course-ID of the parent course.
	 * i.e. the course of the unit/module.
	 *
	 * @since  2.0.0
	 * @param  array $atts Shortcode attributes. No options here.
	 * @return int The course ID or 0 if not called inside a course/unit/module.
	 */
	public static function get_parent_course_id( $atts ) {
		return CoursePress_Data_Course::get_current_course_id();
	}
}
