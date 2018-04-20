<?php
class CoursePress_Helper_Message {

	private static $message_code = null;
	private static $slug = 'course-message';
	private static $allowed_keys = array(
		'no-access',
		'only-enroled',
		'unit-not-available',
	);

	/**
	 * Initialize class CoursePress_Helper_Message
	 *
	 * @since 2.0.0
	 *
	 */
	public static function init() {
		if ( is_admin() ) {
			return;
		}
		self::set_message_key();
		add_shortcode( 'course_message', array( __CLASS__, 'course_message' ) );
	}

	/**
	 * Check request and set message key.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function set_message_key() {
		if ( ! isset( $_REQUEST[ self::$slug ] ) ) {
			return;
		}
		$key = $_REQUEST[ self::$slug ];
		if ( ! self::sanitize_key( $key ) ) {
			return;
		}
		self::$message_code = apply_filters( 'coursepress_helper_message_get_message_code', $key );
	}

	/**
	 * Sanitize message key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key key to sanitization.
	 * @return string/boolean result of sanitization.
	 */
	public static function sanitize_key( $key ) {
		if ( in_array( $key, self::$allowed_keys ) ) {
			return $key;
		}
		return false;
	}

	/**
	 * Add message key to url.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url URL.
	 * @param string $key Message key to add.
	 * @return string URL after processing.
	 */
	public static function add_message_query_arg( $url, $key ) {
		$key = self::sanitize_key( $key );
		if ( empty( $key ) ) {
			return $url;
		}
		$url = add_query_arg(
			self::$slug,
			$key,
			$url
		);
		return apply_filters( 'coursepress_helper_message_add_query_arg', $url, $key );
	}

	/**
	 * Return shortcode result.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode content.
	 */
	public static function course_message( $atts ) {
		if ( empty( self::$message_code ) ) {
			return '';
		}
		$message = '';
		$classes = array(
			'course-message',
			sprintf( 'course-message-%s', self::$message_code ),
		);
		switch ( self::$message_code ) {

			case 'no-access':
				$message = __( 'You need a membership account to access this course.', 'coursepress' );
				$classes[] = 'course-message-alert';
			break;

			case 'only-enroled':
				$message = __( 'Only enrolled students can access this course material.', 'coursepress' );
			break;

			case 'unit-not-available':
				$message = self::_get_not_available_message();
			break;

			default:
			return '';
		}

		$message = apply_filters( 'coursepress_helper_message_message', $message, $message_code, $atts );
		if ( empty( $message ) ) {
			return;
		}

		$content = '<div class="course-message-container">';
		$content .= sprintf(
			'<p class="%s">%s</p>',
			esc_attr( implode( ' ', $classes ) ),
			$message
		);
		$content .= '<a href="#" class="course-message-close i-wpmu-dev-close"></a>';
		$content .= '</div>';
		return apply_filters( 'coursepress_helper_message_html', $content, $message, $message_code, $atts );
	}

	/**
	 * Return the array with specyfic classes, depend on $message_code
	 *
	 * @since 2.0.6
	 */
	public static function course_message_classes() {
		if ( empty( self::$message_code ) ) {
			return '';
		}

		$classes = array(
			sprintf( '%s', self::$message_code ),
		);

		switch ( self::$message_code ) {

			case 'no-access':
				$classes = array(
				'access_classes' => 'no-access',
				'style_classes' => 'alert',
				);
			break;

			case 'only-enroled':
				$classes = array(
				'access_classes' => 'only-enroled',
				'style_classes' => 'alert',
				);
			break;

			case 'unit-not-available':
				$classes = array(
				'access_classes' => 'unit-not-available',
				);
			break;

			default:
			return '';
		}

		return apply_filters( 'coursepress_helper_message_classes', $classes, self::$message_code );
	}

	/**
	 * Get message for unavailable unit or section.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return string Message.
	 */
	private static function _get_not_available_message() {
		if (
			isset( $_REQUEST['type'] )
			&& preg_match( '/^(module|section)$/', $_REQUEST['type'] )
			&& isset( $_REQUEST['id'] )
		) {
			$unit_id = $_REQUEST['id'];
			if ( 'module' == $_REQUEST['type'] ) {
				$unit_id = CoursePress_Data_Module::get_unit_id_by_module( $_REQUEST['id'] );
			}
			$course_id = CoursePress_Data_Unit::get_course_id_by_unit( $unit_id );

			$user_id = get_current_user_id();
			$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id, $user_id );
			$when = date( 'M d', CoursePress_Data_Course::strtotime( $unit_availability_date ) );

			return sprintf(
				__( 'This unit will be available on %s', 'coursepress' ),
				sprintf(
					'<span class="unit-delay-date">%s</span>',
					$when
				)
			);
		}
		return __( 'This unit is not available.', 'coursepress' );
	}
}
