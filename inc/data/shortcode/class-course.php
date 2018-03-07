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
class CoursePress_Data_Shortcode_Course extends CoursePress_Utility {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public function init() {
		$shortcodes = array(
			'course',
			'course_action_links',
			'course_category',
			'course_class_size',
			'course_cost',
			'course_dates',
			'course_description',
			'course_details',
			'course_end',
			'course_enrollment_dates',
			'course_enrollment_end',
			'course_enrollment_start',
			'course_enrollment_type',
			'course_featured_video',
			'course_language',
			'course_length',
			'course_link',
			'course_list_image',
			'course_media',
			'course_random',
			'course_start',
			'course_summary',
			'courses_urls',
			'course_thumbnail',
			'course_time_estimation',
			'course_title',
			'get_parent_course_id',
		);
		foreach ( $shortcodes as $shortcode ) {
			$method = 'get_' . $shortcode;
			if ( method_exists( $this, $method ) ) {
				add_shortcode( $shortcode, array( $this, $method ) );
			}
		}
	}

	/**
	 * Get the course class object.
	 *
	 * @since  2.0.0
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return objecy CoursePress_Course
	 */
	private function get_course_class( $course_id ) {
		return coursepress_get_course( $course_id );
	}

	/**
	 * Get single course details.
	 *
	 * @param $atts
	 *
	 * @return mixed
	 */
	public function get_course( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'show' => 'summary',
			'date_format' => coursepress_get_option( 'date_format' ),
			'label_delimiter' => ',',
			'label_tag' => 'label',
			'show_title' => true,
		), $atts, 'course' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( is_wp_error( $course ) ) {
			return $course->get_error_message();
		}
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$shows = explode( ',', $atts['show'] );
		$shows = array_map( 'trim', $shows );
		$template = '';
		if ( 'yes' == $atts['show_title'] ) {
			$template .= '[course_title course_id="' . $atts['course_id'] . '"]';
		}
		$content = '';
		foreach ( $shows as $show ) {
			$template = '[course_' . $show . ' course_id="' . $atts['course_id'] . '"]';
			$content .= $this->create_html( 'div', array( 'class' => 'course-overview' ), do_shortcode( $template ) );
		}
		return $content;
	}

	/**
	 * Alias for the course shortcode. However, this shortcode has less options
	 * as it only takes an course-ID and a single field name as input.
	 *
	 * @since  2.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Requested course detail.
	 */
	public function get_course_details( $atts ) {
		global $wp_query;
		$atts = shortcode_atts( array(
			'course_id' => ( isset( $wp_query->post->ID ) ? $wp_query->post->ID : 0 ),
			'field' => 'course_start_date',
		), $atts, 'course_details' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$action = sanitize_html_class( $atts['field'] );
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
		$content = $this->get_course( $args );
		coursepress_log_student_activity( 'course_seen' );
		return $content;
	}

	/**
	 * Alias for the course shortcode. However, this shortcode has less options
	 * as it only takes an course-ID and a single field name as input.
	 *
	 * @since  2.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Requested course detail.
	 */
	public function get_courses_urls( $atts ) {
		global $enrollment_process_url, $signup_url;
		$atts = shortcode_atts( array(
			'url' => '',
		), $atts, 'courses_urls' );
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
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_title( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'class' => '',
			'title_tag' => 'h3',
			'clickable' => 'yes',
		), $atts, 'course_title' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$class = 'course-title';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		if ( 'yes' == $atts['clickable'] ) {
			$attr = array(
				'href' => $course->get_permalink(),
				'rel' => 'bookmark',
			);
			$template = $this->create_html( 'a', $attr, $course->post_title );
		} else {
			$template = $course->post_title;
		}
		return $this->create_html( $atts['title_tag'], array( 'class' => $class ), $template );
	}

	/**
	 * Shows the course summary/excerpt.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_summary( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
		), $atts, 'course_summary' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		return $this->create_html( 'div', array( 'class' => 'course-summary' ), $course->post_excerpt );
	}

	/**
	 * Shows the course description.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_description( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'label' => '',
			'class' => '',
		), $atts, 'course_description' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( is_wp_error( $course ) ) {
			return $course->get_error_message();
		}
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = $atts['label'];
		$class = 'course-description';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		$template .= $course->post_content;
		$template = $this->create_html( 'div', array( 'class' => $class ), $template );
		return $template;
	}

	/**
	 * Shows the course start date.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_start( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => __( 'Start Date', 'cp' ),
			'label_delimiter' => ':',
			'label_tag' => 'strong',
			'date_format' => coursepress_get_option( 'date_format' ),
			'class' => '',
		), $atts, 'course_start' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( is_wp_error( $course ) ) {
			return;
		}
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = '';
		if ( ! empty( $atts['label'] ) ) {
			$template .= $this->create_html( $atts['label_tag'], array(), $atts['label'] . $atts['label_delimiter'] );
		}
		if ( $course->course_open_ended ) {
			$template .= __( 'Already started', 'cp' );
		} else {
			$create_date = date_create( $course->course_start_date );
			$template   .= date_format( $create_date, $atts['date_format'] );
		}
		$class = 'course-start-date';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		$template = $this->create_html( 'span', array( 'class' => $class ), $template );
		return $template;
	}

	/**
	 * Shows the course end date.
	 *
	 * If the course has no end date, the no_date_text will be displayed instead of the date.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_end( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Course End Date', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'no_date_text' => __( 'No End Date', 'cp' ),
			'class' => '',
		), $atts, 'course_end' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format = sanitize_text_field( $atts['date_format'] );
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_date_text = sanitize_text_field( $atts['no_date_text'] );
		$class = sanitize_html_class( $atts['class'] );
		$end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$open_ended = coursepress_is_true( coursepress_course_get_setting( $course_id, 'course_open_ended', false ) );
		$class = 'course-end-date course-end-date-' . $course_id . ' ' . $class;
		$content = '';
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, $this->strtotime( $end_date ) ) );
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course start and end date.
	 *
	 * If the course has no end date, the no_date_text will be displayed instead of the date.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_dates( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Course Dates', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'no_date_text' => __( 'No End Date', 'cp' ),
			'alt_display_text' => __( 'Open-ended', 'cp' ),
			'show_alt_display' => 'no',
			'class' => '',
		), $atts, 'course_dates' );
		/**
		 * Check course ID
		 */
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		/**
		 * Check course
		 */
		$course = $this->get_course_class( $course_id );
		if ( is_wp_error( $course ) ) {
			return $course->get_error_message();
		}
		$date_format = sanitize_text_field( $atts['date_format'] );
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_date_text = sanitize_text_field( $atts['no_date_text'] );
		$alt_display_text = sanitize_text_field( $atts['alt_display_text'] );
		$show_alt_display = sanitize_html_class( $atts['show_alt_display'] );
		$class = sanitize_html_class( $atts['class'] );
		$start_date = coursepress_course_get_setting( $course_id, 'course_start_date' );
		$end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$open_ended = coursepress_is_true( coursepress_course_get_setting( $course_id, 'course_open_ended', false ) );
		$end_output = $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', ( date_i18n( $date_format, $this->strtotime( $end_date ) ) ) );
		$show_alt_display = coursepress_is_true( $show_alt_display );
		$content = '';
		$class = 'course-dates course-dates-' . esc_attr( $course_id ) . ' ' . esc_attr( $class );
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= ' ';
		if ( $show_alt_display && $open_ended ) {
			$content .= $alt_display_text;
		} else {
			$content .= str_replace( ' ', '&nbsp;', date_i18n( $date_format, $this->strtotime( $start_date ) ) ) . ' - ' . $end_output;
		}
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the enrollment start date.
	 *
	 * If it is an open ended enrollment the no_date_text will be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_enrollment_start( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Enrollment Start Date', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'no_date_text' => __( 'Enroll Anytime', 'cp' ),
			'class' => '',
		), $atts, 'course_enrollment_start' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format = sanitize_text_field( $atts['date_format'] );
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_date_text = sanitize_text_field( $atts['no_date_text'] );
		$class = sanitize_html_class( $atts['class'] );
		$now = $this->date_time_now();
		$start_date = coursepress_course_get_setting( $course_id, 'enrollment_start_date' );
		$start_date = $this->strtotime( $start_date );
		$date = str_replace( ' ', '&nbsp;', date_i18n( $date_format, $start_date ) );
		$open_ended = coursepress_is_true( coursepress_course_get_setting( $course_id, 'enrollment_open_ended' ) );
		$content = '';
		$class = 'enrollment-start-date enrollment-start-date-' . $course_id . ' ' . $class;
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		if ( $start_date > $now ) {
			$content .= $date;
		} else {
			$content .= $open_ended ? $no_date_text : $date;
		}
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
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
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_enrollment_end( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Enrollment End Date', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'no_date_text' => __( 'Enroll Anytime ', 'cp' ),
			'show_all_dates' => 'no',
			'class' => '',
		), $atts, 'course_enrollment_end' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format = sanitize_text_field( $atts['date_format'] );
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_date_text = sanitize_text_field( $atts['no_date_text'] );
		$show_all_dates = sanitize_html_class( $atts['show_all_dates'] );
		$class = sanitize_html_class( $atts['class'] );
		$end_date = coursepress_course_get_setting( $course_id, 'enrollment_end_date' );
		$open_ended = coursepress_is_true( coursepress_course_get_setting( $course_id, 'enrollment_open_ended' ) );
		$content = '';
		$class = 'enrollment-end-date enrollment-end-date-' . $course_id . ' ' . $class;
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, $this->strtotime( $end_date ) ) );
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
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
	public function get_course_enrollment_dates( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'date_format' => get_option( 'date_format' ),
			'label' => __( 'Enrollment Dates', 'cp' ),
			'label_enrolled' => __( 'You Enrolled on', 'cp' ),
			'show_enrolled_display' => 'yes',
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'no_date_text' => __( 'Enroll Anytime', 'cp' ),
			'alt_display_text' => __( 'Open-ended', 'cp' ),
			'show_alt_display' => 'no',
			'class' => '',
		), $atts, 'course_enrollment_dates' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$date_format = sanitize_text_field( $atts['date_format'] );
		$label = sanitize_text_field( $atts['label'] );
		$label_enrolled = sanitize_text_field( $atts['label_enrolled'] );
		$show_enrolled_display = sanitize_html_class( $atts['show_enrolled_display'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_date_text = sanitize_text_field( $atts['no_date_text'] );
		$alt_display_text = sanitize_text_field( $atts['alt_display_text'] );
		$show_alt_display = sanitize_text_field( $atts['show_alt_display'] );
		$class = sanitize_html_class( $atts['class'] );
		$start_date = coursepress_course_get_setting( $course_id, 'enrollment_start_date' );
		$end_date = coursepress_course_get_setting( $course_id, 'enrollment_end_date' );
		$open_ended = coursepress_is_true( coursepress_course_get_setting( $course_id, 'enrollment_open_ended', false ) );
		$show_alt_display = coursepress_is_true( $show_alt_display );
		if ( 'yes' == strtolower( $show_enrolled_display ) ) {
			$enrollment_date = coursepress_get_student_date_enrolled( $course_id );
			if ( ! empty( $enrollment_date ) ) {
				$enrollment_date = date_i18n( $date_format, $this->strtotime( $enrollment_date ) );
				$label = $label_enrolled;
			}
		}
		$content = '';
		$class = 'enrollment-dates enrollment-dates-' . $course_id . ' ' . $class;
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		if ( empty( $enrollment_date ) ) {
			if ( $show_alt_display && $open_ended ) {
				$content .= $alt_display_text;
			} else {
				$content .= $open_ended ? $no_date_text : str_replace( ' ', '&nbsp;', date_i18n( $date_format, $this->strtotime( $start_date ) ) ) . ' - ' . str_replace( ' ', '&nbsp;', date_i18n( $date_format, $this->strtotime( $end_date ) ) );
			}
		} else {
			// User is enrolled
			$content .= $enrollment_date;
		}
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
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
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_class_size( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'class' => '',
			'label_delimeter' => ': ',
			'label_tag' => 'strong',
			'label' => __( 'Class Size', 'cp' ),
			'no_limit_text' => __( 'Unlimited', 'cp' ),
			'remaining_text' => __( '(%d places left)', 'cp' ),
			'show_no_limit' => 'no',
			'show_remaining' => 'yes',
		), $atts, 'course_class_size' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$course = coursepress_get_course( $course_id );
		$show_no_limit = sanitize_html_class( $atts['show_no_limit'] );
		$show_remaining = sanitize_html_class( $atts['show_remaining'] );
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_limit_text = sanitize_text_field( $atts['no_limit_text'] );
		$remaining_text = sanitize_text_field( $atts['remaining_text'] );
		$class = sanitize_html_class( $atts['class'] );
		$content = '';
		$class_size = (int) coursepress_course_get_setting( $course_id, 'class_size' );
		$is_limited = 0 != $class_size;
		$show_no_limit = coursepress_is_true( $show_no_limit );
		$show_remaining = coursepress_is_true( $show_remaining );
		if ( $is_limited ) {
			$content .= $this->create_html( 'span', array( 'class' => 'total' ), $class_size );
			if ( $show_remaining ) {
				$remaining = $class_size - (int) $course->count_students();
				$content .= $this->create_html( 'span', array( 'class' => 'remaining' ), sprintf( $remaining_text, $remaining ) );
			}
		} else {
			if ( $show_no_limit ) {
				$content .= $no_limit_text;
			}
		}
		if ( ! empty( $content ) ) {
			$display_content = $content;
			$content = '';
			$class = 'course-class-size course-class-size-' . $course_id . ' ' . $class;
			if ( ! empty( $label ) ) {
				$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
			}
			$content .= $display_content;
			$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		}
		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course cost.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_cost( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'class' => '',
			'label_delimeter' => ': ',
			'label_tag' => 'strong',
			'label' => __( 'Price:&nbsp;', 'cp' ),
			'no_cost_text' => __( 'FREE', 'cp' ),
			'show_icon' => 'no',
		), $atts, 'course_cost' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_cost_text = sanitize_text_field( $atts['no_cost_text'] );
		$show_icon = sanitize_text_field( $atts['show_icon'] );
		$class = sanitize_html_class( $atts['class'] );
		$show_icon = coursepress_is_true( $show_icon );
		$is_paid = coursepress_is_true( coursepress_course_get_setting( $course_id, 'payment_paid_course', false ) );
		$content = '';
		if ( $is_paid ) {
			$content .= apply_filters( 'coursepress_shortcode_course_cost', '', $course_id );
		} else {
			if ( $show_icon ) {
				$content .= $this->create_html( 'span', array( 'class' => 'product_price' ), $no_cost_text );
			} else {
				$content .= $no_cost_text;
			}
		}
		if ( ! empty( $content ) ) {
			$display_content = $content;
			$content = '';
			$class = 'course-cost course-cost-' . $course_id . ' ' . $class;
			if ( ! empty( $label ) ) {
				$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
			}
			$content .= $display_content;
			$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		}
		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course language.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_language( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'label' => __( 'Language', 'cp' ),
			'label_tag' => 'strong',
			'label_delimiter' => ':',
			'class' => '',
		), $atts, 'course_language' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( is_wp_error( $course ) ) {
			return '';
		}
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		$template = '';
		if ( ! empty( $atts['label'] ) ) {
			$template .= $this->create_html( $atts['label_tag'], array(), $atts['label'] . $atts['label_delimiter'] );
		}
		$template .= $course->__get( 'course_language' );
		$class = 'course-language';
		if ( ! empty( $atts['class'] ) ) {
			$class .= ' ' . $atts['class'];
		}
		$template = $this->create_html( 'span', array( 'class' => $class ), $template );
		return $template;
	}

	/**
	 * Shows the course category.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_category( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'label' => __( 'Course Category', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'no_category_text' => __( 'None', 'cp' ),
			'class' => '',
		), $atts, 'course_category' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$no_category_text = sanitize_text_field( $atts['no_category_text'] );
		$class = sanitize_html_class( $atts['class'] );
		$content = '';
		$categories = coursepress_get_course_categories( $course_id );
		if ( empty( $categories ) ) {
			return $no_category_text;
		}
		$counter = 0;
		foreach ( $categories as $key => $category ) {
			$counter += 1;
			$content .= $category;
			$content .= count( $categories ) > $counter ? ', ' : '';
		}
		$display_content = $content;
		$content = '';
		$class = 'course-category course-category-' . $course_id . ' ' . $class;
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= $display_content;
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		return $content;
	}

	/**
	 * Shows a friendly course enrollment type message.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_enrollment_type( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'anyone_text' => __( 'Anyone', 'cp' ),
			'class' => '',
			'label_delimeter' => ': ',
			'label_tag' => 'strong',
			'label' => __( 'Who can Enroll', 'cp' ),
			'manual_text' => __( 'Students are added by instructors.', 'cp' ),
			'passcode_text' => __( 'A passcode is required to enroll.', 'cp' ),
			'prerequisite_text' => __( 'Students need to complete %s first.', 'cp' ),
			'registered_text' => __( 'Registered users.', 'cp' ),
		), $atts, 'course_enrollment_type' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$manual_text = sanitize_text_field( $atts['manual_text'] );
		$prerequisite_text = sanitize_text_field( $atts['prerequisite_text'] );
		$passcode_text = sanitize_text_field( $atts['passcode_text'] );
		$anyone_text = sanitize_text_field( $atts['anyone_text'] );
		$registered_text = sanitize_text_field( $atts['registered_text'] );
		$class = sanitize_html_class( $atts['class'] );
		$enrollment_type = coursepress_course_get_setting( $course_id, 'enrollment_type' );
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
				$prereq = coursepress_get_enrollment_prerequisite( $course_id );
				$prereq_courses = array();
				if ( ! empty( $prereq ) ) {
					foreach ( $prereq as $prereq_id ) {
						$prereq_courses[] = sprintf(
							'<a href="%s">%s</a>',
							esc_url( get_permalink( $prereq_id ) ),
							get_the_title( $prereq_id )
						);
					}
				}
				$enrollment_text = sprintf( $prerequisite_text, implode( ', ', $prereq_courses ) );
				break;
			case 'manually':
				$enrollment_text = $manual_text;
				break;
		}
		// For non-standard enrolment types.
		$enrollment_text = apply_filters( 'coursepress_course_enrollment_type_text', $enrollment_text );
		$content = '';
		$class = 'course-enrollment-type course-enrollment-type-' . $course_id . ' ' . $class;
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= $enrollment_text;
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the course list image.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_list_image( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'class' => '',
		), $atts, 'course_list_image' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( is_wp_error( $course ) ) {
			return '';
		}
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		if ( ! empty( $course->listing_image ) ) {
			$class = 'course-feature-image';
			if ( ! empty( $atts['class'] ) ) {
				$class .= ' ' . $atts['class'];
			}
			$attr = array(
				'class' => $class,
				'src' => esc_url_raw( $course->listing_image ),
				'width' => $atts['width'],
				'height' => $atts['height'],
			);
			return $this->create_html( 'img', $attr );
		}
		return '';
	}

	/**
	 * Shows the course featured video.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_featured_video( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'class' => '',
		), $atts, 'course_featured_video' );
		$course = $this->get_course_class( $atts['course_id'] );
		if ( is_wp_error( $course ) ) {
			return $course->get_error_message();
		}
		if ( $course->__get( 'is_error' ) ) {
			return $course->__get( 'error_message' );
		}
		if ( ! empty( $course->featured_video ) ) {
			$class = 'course-featured-video';
			if ( ! empty( $atts['class'] ) ) {
				$class .= ' ' . $atts['class'];
			}
			$attr = array(
				'class' => $class,
				'src' => esc_url_raw( $course->featured_video ),
			);
			// @todo: apply CP video.js
		}
		return '';
	}

	/**
	 * Shows the course thumbnail.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_thumbnail( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'wrapper' => 'figure',
			'class' => '',
		), $atts, 'course_thumbnail' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$wrapper = sanitize_html_class( $atts['wrapper'] );
		$class = sanitize_html_class( $atts['class'] );
		return do_shortcode( '[course_media course_id="' . $course_id . '" wrapper="' . $wrapper . '" class="' . $class . '" type="thumbnail"]' );
	}

	/**
	 * Shows the course action links.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_action_links( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'class' => '',
		), $atts, 'course_action_links' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$class = sanitize_html_class( $atts['class'] );
		$course_start_date = coursepress_course_get_setting( $course_id, 'course_start_date' );
		$course_end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$open_ended_course = coursepress_is_true( coursepress_course_get_setting( $course_id, 'course_open_ended' ) );
		$withdraw_link_visible = false;
		$student_id = get_current_user_id();
		$now = $this->date_time_now();
		if ( ! empty( $student_id ) && coursepress_is_student_enrolled_at( $course_id, $student_id ) ) {
			if ( ( ( $this->strtotime( $course_start_date ) <= $now && $this->strtotime( $course_end_date ) >= $now ) || ( $this->strtotime( $course_end_date ) >= $now ) ) || $open_ended_course ) {
				// Course is currently active or is not yet active (will be active in the future).
				$withdraw_link_visible = true;
			}
		}
		$content = '';
		$class = 'apply-links course-action-links course-action-links-' . $course_id . ' ' . $class;
		if ( $withdraw_link_visible ) {
			$link = wp_nonce_url( '?withdraw=' . $course_id, 'withdraw_from_course_' . $course_id, 'course_nonce' );
			$content .= $this->create_html( 'a', array( 'href' => $link, 'onClick' => 'return withdraw();' ), esc_html__( 'Withdraw', 'cp' ) );
			$content .= ' | ';
		}
		$content .= $this->create_html( 'a', array( 'href' => get_permalink( $course_id ) ), esc_html__( 'Course Details', 'cp' ) );
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		return $content;
	}

	/**
	 * Shows the course media (video or image).
	 *
	 * @since 1.0.0
	 * @param  array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function get_course_media( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'class' => '',
			'height' => coursepress_get_setting( 'course/image_height' ),
			'list_page' => 'no',
			'priority' => '', // Gives priority to video (or image).
			'type' => '', // Default, video, image.
			'width' => coursepress_get_setting( 'course/image_width' ),
			'wrapper' => '',
		), $atts, 'course_media' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$course = $this->get_course_class( $course_id );
		if ( is_wp_error( $course ) ) {
			return '';
		}
		$type = sanitize_text_field( $atts['type'] );
		$priority = sanitize_text_field( $atts['priority'] );
		$list_page = coursepress_is_true( sanitize_html_class( $atts['list_page'] ) );
		$class = sanitize_html_class( $atts['class'] );
		$wrapper = sanitize_html_class( $atts['wrapper'] );
		$height = sanitize_text_field( $atts['height'] );
		$width = sanitize_text_field( $atts['width'] );
		// We'll use pixel if none is set
		if ( ! empty( $width ) && (int) $width == $width ) {
			$width .= 'px';
		}
		if ( ! empty( $height ) && (int) $height == $height ) {
			$height .= 'px';
		}
		if ( ! $list_page ) {
			$type = empty( $type ) ? coursepress_get_setting( 'course/details_media_type', 'default' ) : $type;
			$priority = empty( $priority ) ? coursepress_get_setting( 'course/details_media_priority', 'video' ) : $priority;
		} else {
			$type = empty( $type ) ? coursepress_get_setting( 'course/listing_media_type', 'default' ) : $type;
			$priority = empty( $priority ) ? coursepress_get_setting( 'course/listing_media_priority', 'image' ) : $priority;
		}
		$priority = 'default' != $type ? false : $priority;
		// Saves some overhead by not loading the post again if we don't need to.
		$class = sanitize_html_class( $class );
		$course_video = coursepress_course_get_setting( $course_id, 'featured_video' );
		$course_image = coursepress_course_get_setting( $course_id, 'listing_image' );
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
		$content = '';
		if ( ( ( 'default' == $type && 'video' == $priority ) || 'video' == $type || ( 'default' == $type && 'image' == $priority && empty( $course_image ) ) ) && ! empty( $course_video ) ) {
			$class = 'video_player course-featured-media course-featured-media-' . $course_id . ' ' . $class;
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
					add_filter( 'oembed_result', array( $this, 'remove_related_videos' ), 10, 3 );
				}
				$content .= wp_oembed_get( $course_video, $embed_args );
			}
			$content = empty( $wrapper ) ? $content : $this->create_html( $wrapper, array( 'style' => $wrapper_style ), $content );
			$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		}
		if ( ( ( 'default' == $type && 'image' == $priority ) || 'image' == $type || ( 'default' == $type && 'video' == $priority && empty( $course_video ) ) ) && ! empty( $course_image ) ) {
			$class = 'course-thumbnail course-featured-media course-featured-media-' . $course_id . ' ' . $class;
			$content_img = $this->create_html( 'img', array( 'src' => esc_url( $course_image ), 'class' => 'course-media-img' ) );
			$content_wrapper = empty( $wrapper ) ? $content_img : $this->create_html( $wrapper, array( 'style' => $wrapper_style ), $content_img );
			$content .= $this->create_html( 'div', array( 'class' => $class ), $content_wrapper );
		}
		return $content;
	}

	/**
	 * Shows the course title with a link to the course.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_link( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'element' => 'span',
			'class' => 'course-link',
		), $atts, 'course_link' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$element = sanitize_html_class( $atts['element'] );
		$class = sanitize_html_class( $atts['class'] );
		$content = do_shortcode( '[course_title course_id="' . $course_id . '" title_tag="' . $element . '" link="yes" class="' . $class . '"]' );
		return $content;
	}

	/**
	 * Shows the course length in weeks.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_length( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'label' => __( 'Course Length', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'class' => '',
			'suffix' => ' Weeks',
		), $atts, 'course_start' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = sanitize_text_field( $atts['label_delimeter'] );
		$class = sanitize_html_class( $atts['class'] );
		$open_ended = coursepress_is_true( coursepress_course_get_setting( $course_id, 'course_open_ended' ) );
		if ( $open_ended ) {
			return '';
		}
		$start_date = coursepress_course_get_setting( $course_id, 'course_start_date' );
		$end_date = coursepress_course_get_setting( $course_id, 'course_end_date' );
		$length = ceil( ( $this->strtotime( $end_date ) - $this->strtotime( $start_date ) ) / 604800 );
		if ( $length <= 0 ) {
			return '';
		}
		$class = 'course-length course-length-' . $course_id . ' ' . $class;
		$content = '';
		if ( ! empty( $label ) ) {
			$content .= $this->create_html( esc_html( $label_tag ), array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= $length . $atts['suffix'];
		$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		// Return the html in the buffer.
		return $content;
	}

	/**
	 * Shows the estimated course time.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_time_estimation( $atts ) {
		$content = '';
		$atts = shortcode_atts( array(
			'course_id' => coursepress_get_course_id(),
			'label' => __( 'Estimated Duration:&nbsp;', 'cp' ),
			'label_tag' => 'strong',
			'label_delimeter' => ': ',
			'wrapper' => 'no',
			'class' => '',
		), $atts, 'course_time_estimation' );
		$course_id = (int) $atts['course_id'];
		if ( empty( $course_id ) ) {
			return '';
		}
		$label = sanitize_text_field( $atts['label'] );
		$label_tag = sanitize_html_class( $atts['label_tag'] );
		$label_delimeter = $atts['label_delimeter'];
		$class = sanitize_html_class( $atts['class'] );
		$wrapper = coursepress_is_true( sanitize_text_field( $atts['wrapper'] ) );
		if ( $wrapper && ! empty( $label ) ) {
			$content .= $this->create_html( $label_tag, array( 'class' => 'label' ), esc_html( $label ) . esc_html( $label_delimeter ) );
		}
		$content .= CoursePress_Data_Course::get_time_estimation( $course_id );
		if ( $wrapper ) {
			$class = 'course-time-estimate course-time-estimate-' . $course_id . ' ' . $class;
			$content = $this->create_html( 'div', array( 'class' => $class ), $content );
		}
		return $content;
	}

	/**
	 * Display list of random courses. By default 3 random courses are displayed.
	 *
	 * Note: This shortcode uses the SQL `ORDER BY RAND` option, which is
	 * disabled on WPEngine sites by default (it can slow down the site).
	 * Very likely this shortcode is not working on WPEngine.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public function get_course_random( $atts ) {
		$atts = shortcode_atts( array(
			'number' => 3,
			'featured_title' => 'default',
			'button_title' => 'default',
			'media_type' => 'default',
			'media_priority' => 'default',
			'course_class' => 'default',
			'class' => '',
		), $atts, 'course_random' );
		$number = (int) $atts['number'];
		$featured_title = sanitize_text_field( $atts['featured_title'] );
		$button_title = sanitize_text_field( $atts['button_title'] );
		$media_type = sanitize_html_class( $atts['media_type'] );
		$media_priority = sanitize_html_class( $atts['media_priority'] );
		$course_class = sanitize_html_class( $atts['course_class'] );
		$class = sanitize_html_class( $atts['class'] );
		$args = array(
			'post_type' => 'course',
			'posts_per_page' => $number,
			'orderby' => 'rand',
			'fields' => 'ids',
		);
		$courses = new WP_Query( $args );
		$courses = $courses->posts;
		$class = sanitize_html_class( $class );
		$featured_atts = $content = '';
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
			$shortcode_content = do_shortcode( '[course_featured course_id="' . $course . '" ' . $featured_atts . ']' );
			$content .= $this->create_html( 'div', array( 'class' => 'course-item course-item-' . $course ), $shortcode_content );
		}
		$content = 0 < count( $courses ) ? $this->create_html( 'div', array( 'class' => 'course-random' . $class ), $content ) : $content;
		return $content;
	}

	/**
	 * Return the course-ID of the parent course.
	 * i.e. the course of the unit/module.
	 *
	 * @since  2.0.0
	 *
	 * @return int The course ID or 0 if not called inside a course/unit/module.
	 */
	public function get_parent_course_id() {
		return CoursePress_Data_Course::get_current_course_id();
	}
}
