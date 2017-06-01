<?php
/**
 * Class CoursePress_Shortcode
 *
 * @since 2.0
 * @package CoursePress
 */
class CoursePress_Shortcode extends CoursePress_Utility {
	protected $courses = array();

	public function __construct() {
		$shortcodes = array(
			'course',
			'course_action_links',
			'course_calendar',
			'course_class_size',
			'course_cost',
			'course_dates',
			'course_details',
			'course_end',
			'course_start',
			'course_title',
			'course_summary',
			'course_description',
			'course_enrollment_dates',
			'course_enrollment_start',
			'course_enrollment_end',
			'course_enrollment_type',
			'course_featured',
			'course_featured_video',
			'course_instructor_avatar',
			'course_instructors',
			'course_join_button',
			'course_language',
			'course_list',
			'course_list_image',
			'course_media',
			'course_signup',
			'course_structure',
			'course_time_estimation',
			'courses_student_dashboard',
			'courses_student_settings',
			'instructor_profile_url',
		);

		foreach ( $shortcodes as $shortcode ) {
			$method = 'get_' . $shortcode;

			if ( method_exists( $this, $method ) )
				add_shortcode( $shortcode, array( $this, $method ) );
		}
	}

	private function get_course_class( $course_id ) {
		if ( empty( $this->courses[ $course_id ] ) ) {
			$course = new CoursePress_Course( $course_id );
			$this->courses[ $course_id ] = $course;
		}

		return $this->courses[ $course_id ];
	}

	function get_course( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'show' => 'summary',
			'date_format' => coursepress_get_option( 'date_format' ),
			'label_delimiter' => ',',
			'label_tag' => 'label',
			'show_title' => true,
		), $atts, 'course' );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		$shows = explode( ',', $atts['show'] );
		$shows = array_map( 'trim', $shows );
		$template = '';

		if ( 'yes' == $atts['show_title'] )
			$template .= '[course_title]';

		foreach ( $shows as $show ) {
			$template = '[course_' . $show . ']';
		}

		return $this->create_html( 'div', array( 'class' => 'course-overview' ), do_shortcode( $template ) );
	}

	function get_course_title( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'class' => '',
			'title_tag' => 'h3',
		), $atts, 'course_title' );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		$class = 'course-title';

		if ( ! empty( $atts['class'] ) )
			$class .= ' ' . $atts['class'];

		return $this->create_html( $atts['title_tag'], array( 'class' => $class ), $course->post_title );
	}

	function get_course_instructors( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'avatar_size' => 42,
			'default_avatar' => '',
			'label' => __( 'Instructor', 'cp' ),
			'label_delimiter' => ':',
			'label_plural' => __( 'Instructors', 'cp' ),
			'label_tag' => 'h3',
			'link_all' => false,
			'link_text' => __( 'View Profile', 'cp' ),
			'list_separator' => '',
			'show_divider' => true,
			'style' => 'block',
			'summary_length' => 50,
		), $atts, 'course_instructors' );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		$instructors = $course->get_instructors();
		$count = count( $instructors );

		if ( 0 == $count )
			return '';

		$class = array( 'course-instructors', $atts['style'] );
		$link_all = 'yes' == $atts['link_all'];
		$templates = '';

		if ( ! empty( $atts['label'] ) || ! empty( $atts['label_plural'] ) )
			$templates .= $this->create_html(
				$atts['label_tag'],
				array(),
				_n( $atts['label'], $atts['label_plural'], $count ) . $atts['label_delimiter']
			);

		foreach ( $instructors as $instructor ) {
			$template = $instructor->get_avatar( $atts['avatar_size'] );

			$link = $instructor->get_instructor_profile_link();

			if ( ! $link_all ) {
				$attr = array( 'href'  => esc_url_raw( $link ), 'class' => 'fn instructor' );
				$template .= $this->create_html( 'a', $attr, $instructor->get_name() );
			} else {
				$template .= $instructor->get_name();
			}

			$templates .= $template;
		}

		return $this->create_html( 'div', array( 'class' => implode( ' ', $class ) ), $templates );
	}

	function get_course_summary( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
		), $atts );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		return $this->create_html( 'div', array( 'class' => 'course-summary' ), $course->post_excerpt );
	}

	function get_course_description( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'label' => '',
			'class' => '',
		), $atts );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		$template = $atts['label'];
		$class = 'course-description';

		if ( ! empty( $atts['class'] ) )
			$class .= ' ' . $atts['class'];

		$template .= $this->create_html( 'div', array( 'class' => $class ), $course->post_content );

		return $template;
	}

	function get_course_list_image( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'class' => '',
		), $atts, 'course_list_image' );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		if ( ! empty( $course->list_image ) ) {
			$class = 'course-feature-image';

			if ( ! empty( $atts['class'] ) )
				$class .= ' ' . $atts['class'];

			$attr = array( 'class' => $class, 'src' => esc_url_raw( $course->list_image ) );

			return $this->create_html( 'img', $attr );
		}

		return '';
	}

	function get_course_featured_video( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'class' => '',
		), $atts, 'course_featured_video' );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		if ( ! empty( $course->featured_video ) ) {
			$class = 'course-featured-video';

			if ( ! empty( $atts['class'] ) )
				$class .= ' ' . $atts['class'];

			$attr = array( 'class' => $class, 'src' => esc_url_raw( $course->featured_video ) );

			// @todo: apply CP video.js
		}

		return '';
	}

	function get_course_media( $atts ) {
		$atts = shortcode_atts( array(
			'course_id' => get_the_ID(),
			'class' => '',
			'height' => coursepress_get_setting( 'course/image_height', 235 ),
			'width' => coursepress_get_setting( 'course/image_width', 235 ),
			'priority' => coursepress_get_setting( 'course/listing_media_priority', 'image' ),
			'type' => coursepress_get_setting( 'course/listing_media_type', 'image' ),
			'wrapper' => 'div',
		), $atts, 'course_media' );

		$course = $this->get_course_class( $atts['course_id'] );

		if ( $course->__get( 'is_error' ) )
			return $course->__get( 'error_message' );

		$class = 'course-media';
		if ( ! empty( $atts['class'] ) )
			$class .= ' ' . $atts['class'];

		if ( 'image' == $atts['type'] )
			$template = $this->get_course_list_image( array( 'height' => $atts['height'], 'width' => $atts['width'] ) );
		else
			$template = $this->get_course_featured_video( array() );

		if ( ! empty( $template ) )
			$template = $this->create_html(
				$atts['wrapper'],
				array( 'class' => $class ),
				$template
			);

		return $template;
	}
}
