<?php
/**
 * Class CoursePress_Utility
 *
 * @since 3.0
 * @package CoursePress
 */
abstract class CoursePress_Utility {

	// Used by the array uasort() callbacks.
	private $sort_key;
	private $embed_args;

	public function __set( $name, $value ) {
		$this->{$name} = $value;
	}

	public function __get( $name ) {
		if ( isset( $this->{$name} ) ) {
			return $this->{$name}; }

			return null;
	}

	public function setUp( $args ) {
		if ( ! empty( $args ) ) {
			foreach ( $args as $key => $value ) {
				$this->__set( $key, $value );
			}
		}
	}

	/**
	 * Return prepared timestamp, after day starts.
	 *
	 * @param string $hour Hour after day start, by default it is 1 secound
	 *                     (plus local settings of timezone).
	 *
	 */
	public function date_time_now( $hour = '00:00:01' ) {
		$time_now = current_time( 'timestamp' );
		$date_now = date( 'c', current_time( 'timestamp' ) );
		if ( ! is_string( $hour ) || ! preg_match( '/^\d\d:\d\d:\d\d$/', $hour ) ) {
			$hour = '00:00:01';
		}
		$date_now = preg_replace( '/T\d\d:\d\d:\d\d/', 'T'.$hour, $date_now );
		// Time now is not the current time but rather the timestamp of the starting date today (00:01).
		$time_now = strtotime( $date_now, $time_now );
		return $time_now;
	}

	public function strtotime( $date_string ) {
		$timestamp = 0;
		if ( is_numeric( $date_string ) ) {
			// Apparently we got a timestamp already. Simply return it.
			$timestamp = (int) $date_string;
		} elseif ( is_string( $date_string ) && ! empty( $date_string ) ) {
			/*
             * Convert the date-string into a timestamp; PHP assumes that the
             * date string is in servers default timezone.
             * We assume that date string is in "yyyy-mm-dd" format, not a
             * relative date and also without timezone suffix.
             */
			$timestamp = strtotime( $date_string . ' UTC' );
		}

		return (int) $timestamp;
	}

	/**
	 * Get total secounds.
	 *
	 * @since 2.0.4
	 *
	 * @param integer $secounds Number of secounds.
	 * @param integer $hours Number of hours.
	 * @param integer $minutes Number of minutes.
	 * @return array {
	 *      @type string Time in format hh:mm:ss
	 *      @type integer Number of hours.
	 *      @type integer Number of minutes.
	 *      @type integer Number of seconds.
	 * }
	 * internet Total number of seconds.
	 */
	public function get_time( $seconds = 0, $minutes = 0, $hours = 0 ) {

		$time = (int) $seconds + (int) $minutes * MINUTE_IN_SECONDS + (int) $hours * HOUR_IN_SECONDS;

		return array(
			'total_seconds' => $time,
			'time' => date( 'H:i:s', $time ),
			'hours' => date( 'H', $time ),
			'minutes' => date( 'i', $time ),
			'seconds' => date( 's', $time ),
		);
	}

	/**
	 * get date in WP format
	 *
	 * @since 3.0.0
	 *
	 * @param string $date_input Input string date.
	 * @return string $date_output Input date formated by WP format.
	 */
	public function date( $date_input ) {
		$date_string = $this->strtotime( $date_input );
		$date_format = get_option( 'date_format' );
		$date_output = date_i18n( $date_format, $date_string );
		return $date_output;
	}

	public function setAttributes( $attr = array() ) {
		if ( ! $attr ) {
			return ''; }

		$vars = array();

		foreach ( $attr as $key => $value ) {
			$vars[] = $key . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $vars );
	}

	public function create_html( $tag, $attributes = array(), $content = '' ) {
		$html = '<' . $tag;
		if ( ! empty( $attributes ) ) {
			$html .= ' ' . $this->setAttributes( $attributes );
		}
		$single_tags = array( 'img', 'input', 'hr', 'br' );
		if ( in_array( $tag, $single_tags ) ) {
			$html .= ' />';
		} else {
			$html .= '>' . $content . '</' . $tag . '>';
		}
		return $html;
	}

	public function to_array( $array ) {
		if ( is_object( $array ) ) {
			$array = get_object_vars( $array );
		}

		if ( is_array( $array ) && ! empty( $array ) ) {
			foreach ( $array as $key => $value ) {
				$array[ $key ] = $this->to_array( $value );
			}
		}

		return $array;
	}

	/**
	 * Convert an associative array to html params.
	 *
	 * @since 2.0.4
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public function convert_array_to_params( $array ) {

		$content = '';
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				if ( preg_match( '/^\d+$/', $key ) ) {
					continue;
				}

				$content .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
			}
		}

		return $content;
	}

	/**
	 * Check if current install is PRO or FREE
	 */
	public function is_pro() {
	}

	/**
	 * Replaces the defined placeholders in the content with specified values.
	 *
	 * @since  2.0.0
	 * @param  string $content The full content, with placeholders.
	 * @param  array  $vars List of placeholder => value.
	 * @return string The content but with all placeholders replaced.
	 */
	public function replace_vars( $content, $vars ) {
		$keys   = array();
		$values = array();

		foreach ( $vars as $key => $value ) {
			$keys[]   = $key;
			$values[] = $value;
		}

		return str_replace( $keys, $values, $content );
	}

	/**
	 * Converts a hex color into an RGB array.
	 *
	 * @param $hex_color string The color in format #FFFFFF
	 * @param $default string The value to return if the color to convert turns out to be invalid.
	 * @return array An array containing RGB values.
	 */
	public function convert_hex_color_to_rgb( $hex_color, $default ) {

		$color_valid = (boolean) preg_match( '/^#[a-f0-9]{6}$/i', $hex_color );
		if ( $color_valid ) {
			$values = CP_TCPDF_COLORS::convertHTMLColorToDec( $hex_color, CP_TCPDF_COLORS::$spotcolor );
			return array_values( $values );
		}

		return $default;
	}

	protected function get_pagenum() {
		return isset( $_REQUEST['paged'] )? intval( $_REQUEST['paged'] ) : 1;
	}

	protected function get_per_page() {
		return 20;
	}

	protected function get_items_per_page( $type, $per_page ) {
		return $per_page;
	}

	public function meta_key( $key ) {
		return $key['meta_key'];
	}

	/**
	 * Get the items per page option value.
	 *
	 * @param string $option Screen option name.
	 *
	 * @return int
	 */
	public function items_per_page( $option = '' ) {

		$per_page = 0;

		if ( ! empty( $option ) ) {
			$per_page = get_user_meta( get_current_user_id(), $option, true );
		}

		// If screen option is not set or empty, default posts per page.
		if ( empty( $per_page ) ) {
			$per_page = coursepress_get_option( 'posts_per_page', 20 );
		}

		return $per_page;
	}

	/**
	 * Set pagination based on the arguments.
	 *
	 * @param int $count Total items.
	 * @param string $option_name Items per page option name.
	 * @param int $total_pages Total number of pages to show. Ignore for all pages.
	 *
	 * @return WP_List_Table
	 */
	public function set_pagination( $count = 0, $option_name = '', $total_pages = 0 ) {

		// Using WP_List table for pagination.
		$listing = new WP_List_Table();

		$args = array(
			'total_items' => $count,
			'per_page' => $this->items_per_page( $option_name ),
		);

		// Consider total pages argument only if not empty.
		if ( ! empty( $total_pages ) ) {
			$args['total_pages'] = (int) $total_pages;
		}

		$listing->set_pagination_args( $args );

		return $listing;
	}

	public function is_youtube_url( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host && (strpos( $host, 'youtube' ) !== false || strpos( $host, 'youtu.be' ) !== false);
	}

	public function is_vimeo_url( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host && strpos( $host, 'vimeo' ) !== false;
	}

	public function create_video_js_setup_data( $url, $hide_related_media = true, $width = 0 ) {
		//$url = 'http://local.wordpress.dev/wp-content/uploads/2017/07/Recording-3.mp4';
		//$url = 'https://www.youtube.com/watch?v=FxYw0XPEoKE';
		//$url = 'https://vimeo.com/6370469';
		$src = false;
		$extra_data = array();

		if ( $this->is_youtube_url( $url ) ) {
			$src = 'youtube';
			$show_related_media = ! $hide_related_media;
			$extra_data['youtube'] = array( 'rel' => intval( $show_related_media ) );
		} elseif ( $this->is_vimeo_url( $url ) ) {
			$src = 'vimeo';
		}

		$setup_data = array();
		$player_width = $width;

		if ( ! $player_width ) {
			$setup_data['fluid'] = true;
		}

		if ( $src ) {
			$setup_data['techOrder'] = array( $src );
			$setup_data['sources'] = array(
				array(
					'type' => 'video/' . $src,
					'src' => $url,
				),
			);
		}

		$setup_data = array_merge( $setup_data, $extra_data );

		return json_encode( $setup_data );
	}

	/**
	 * Filter HTML string and remove forbidden tags and attributes.
	 * This function uses the wp_kses() function to sanitize the content.
	 *
	 * @param  string $content Raw HTML code.
	 * @param  bool   $no_html Return sanitized HTML (false) or plain text (true)?
	 *
	 * @since  2.0.0
	 *
	 * @return string Sanitized content.
	 */
	public static function filter_content( $content, $no_html = false ) {

		$kses_rules = apply_filters( 'coursepress_allowed_post_tags', wp_kses_allowed_html( 'post' ) );

		if ( $no_html ) {
			if ( is_array( $content ) ) {
				foreach ( $content as $content_key => $content_value ) {
					$content[ $content_key ] = wp_filter_nohtml_kses( $content_value );
				}
			} else {
				$content = wp_filter_nohtml_kses( $content );
			}
		} else {
			if ( current_user_can( 'unfiltered_html' ) ) {
				$content = $content;
			} else {
				if ( is_array( $content ) ) {
					foreach ( $content as $content_key => $content_value ) {
						$content[ $content_key ] = wp_kses( $content_value,  $kses_rules );
					}
				} else {
					$content = wp_kses( $content, $kses_rules );
				}
			}
		}

		return $content;
	}

	/**
	 * Returns the full name of the specified user.
	 *
	 * Depending on param $last_first the result will be either of those
	 * "First Last (displayname)"
	 * "Last, First (displayname)"
	 *
	 * @since  1.0.0
	 * @param  int  $user_id The user ID.
	 * @param  bool $last_first Which format to use. Default: "First Last"
	 * @param  bool $show_username Append displayname in brackets. Default: yes.
	 * @return string Full name of the user.
	 */
	public static function get_user_name( $user_id, $last_first = false, $show_username = true ) {

		$user_id = (int) $user_id;
		$display_name = (string) get_user_option( 'display_name', $user_id );
		$last = (string) get_user_option( 'last_name', $user_id );
		$first = (string) get_user_option( 'first_name', $user_id );
		$result = '';

		if ( $last_first ) {
			if ( $last ) {
				$result .= $last;
			}
			if ( $first && $result ) {
				$result .= ', ';
			}
			if ( $first ) {
				$result .= $first;
			}
		} else {
			if ( $first ) {
				$result .= $first;
			}
			if ( $last && $result ) {
				$result .= ' ';
			}
			if ( $last ) {
				$result .= $last;
			}
		}

		if ( $display_name ) {
			if ( $result && $show_username ) {
				$result .= ' (' . $display_name . ')';
			} elseif ( ! $result ) {
				$result = $display_name;
			}
		}

		return $result;
	}

	/**
	 * Returns true if the WP installation allows user registration.
	 *
	 * @since  1.0.0
	 *
	 * @return bool If CoursePress allows user signup.
	 */
	public function users_can_register() {

		if ( is_multisite() ) {
			$allow_register = users_can_register_signup_filter();
		} else {
			$allow_register = get_option( 'users_can_register' );
		}

		/**
		 * Filter the return value to allow users to manually enable
		 * CoursePress registration only.
		 *
		 * @since 2.0.0
		 * @var bool $_allow_register
		 */
		$allow_register = apply_filters( 'coursepress_users_can_register', $allow_register );

		return $allow_register;
	}

	/**
	 * Sort multi-dimension arrays on 'order' value.
	 *
	 * @param $array
	 * @param $sort_key
	 * @param bool $sort_asc
	 *
	 * @return mixed
	 */
	public function sort_on_key( $array, $sort_key, $sort_asc = true ) {

		$this->sort_key = $sort_key;

		if ( ! $sort_asc ) {
			uasort( $array, array( $this, 'sort_desc' ) );
		} else {
			uasort( $array, array( $this, 'sort_asc' ) );
		}

		return $array;
	}

	/**
	 * uasort callback to sort ascending.
	 *
	 * @param $x
	 * @param $y
	 *
	 * @return int
	 */
	public function sort_asc( $x, $y ) {

		if ( $x[ $this->sort_key ] == $y[ $this->sort_key ] ) {
			return 0;
		} else if ( $x[ $this->sort_key ] < $y[ $this->sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	/**
	 * uasort callback to sort descending.
	 *
	 * @param $x
	 * @param $y
	 *
	 * @return int
	 */
	public function sort_desc( $x, $y ) {

		if ( $x[ $this->sort_key ] == $y[ $this->sort_key ] ) {
			return 0;
		} else if ( $x[ $this->sort_key ] > $y[ $this->sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	/**
	 * Sanitize data array.
	 *
	 * @param $mixed
	 *
	 * @return array|string
	 */
	public function sanitize_recursive( $mixed ) {

		if ( is_array( $mixed ) ) {
			foreach ( $mixed as $key => $value ) {
				$mixed[ $key ] = $this->sanitize_recursive( $value );
			}
		} else {
			if ( is_string( $mixed ) ) {
				return $this->filter_content( $mixed );
			}
		}

		return $mixed;
	}

	/**
	 * Encode a string.
	 *
	 * @param string $value
	 *
	 * @return bool|string
	 */
	public function encode( $value ) {

		if ( ! $value ) {
			return false;
		}

		if ( ! extension_loaded( 'mcrypt' ) ) {
			return $value;
		}

		if ( ! function_exists( 'mcrypt_module_open' ) ) {
			return $value;
		}

		$security_key = $this->get_security_key();
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$crypttext = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $security_key, $value, MCRYPT_MODE_ECB, $iv );

		return trim( $this->safe_b64encode( $crypttext ) );
	}

	/**
	 * Decode encoded strings.
	 *
	 * @param string $value
	 *
	 * @return bool|string
	 */
	public function decode( $value ) {

		if ( ! $value ) {
			return false;
		}

		if ( ! extension_loaded( 'mcrypt' ) ) {
			return $value;
		}

		if ( ! function_exists( 'mcrypt_module_open' ) ) {
			return $value;
		}

		$security_key = $this->get_security_key();
		$crypttext = $this->safe_b64decode( $value );

		if ( ! $crypttext ) { return false; }

		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$decrypttext = mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $security_key, $crypttext, MCRYPT_MODE_ECB, $iv );

		return trim( $decrypttext );
	}

	/**
	 * Encode string using base64.
	 *
	 * @param int $string
	 *
	 * @return mixed|string
	 */
	public function safe_b64encode( $string ) {

		$data = base64_encode( $string );
		$data = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), $data );

		return $data;
	}

	/**
	 * Decode base64 string.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public function safe_b64decode( $string ) {

		$data = str_replace( array( '-', '_' ), array( '+', '/' ), $string );
		$mod4 = strlen( $data ) % 4;
		if ( $mod4 ) {
			$data .= substr( '====', $mod4 );
		}

		return base64_decode( $data );
	}

	/**
	 * Get $security_key - get key as substring of NONCE_KEY, but check
	 * length.
	 *
	 * @since 2.0.3
	 *
	 * @return string
	 */
	private static function get_security_key() {

		$available_lengths = array( 32, 24, 16 );
		$security_key = NONCE_KEY;
		foreach ( $available_lengths as $key_length ) {
			if ( function_exists( 'mb_substr' ) ) {
				$security_key = mb_substr( $security_key, 0, $key_length );
			} else {
				$security_key = substr( $security_key, 0, $key_length );
			}
			if ( $key_length == strlen( $security_key ) ) {
				return $security_key;
			}
		}

		// md5 has always 16 characters length.
		$security_key = md5( NONCE_KEY );

		return $security_key;
	}

	/**
	 * Format file size.
	 *
	 * @param $bytes
	 *
	 * @return int|string
	 */
	public function format_file_size( $bytes ) {

		$bytes = (int) $bytes;

		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
			$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
		} elseif ( $bytes > 1 ) {
			$bytes = $bytes . ' bytes';
		} elseif ( 1 == $bytes ) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	/**
	 * Get appropriate AJAX URL.
	 *
	 * @return string
	 */
	public function get_ajax_url() {

		return set_url_scheme( admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Get message if module is REQUIRED.
	 *
	 * @since 2.0.0
	 *
	 * @param string $error_message First line of message.
	 *
	 * @return string Error message.
	 */
	public function get_message_required_modules( $error_message ) {

		$error_message .= PHP_EOL;
		$error_message .= PHP_EOL;
		$error_message .= __( 'Please press the Prev button on the left to continue.', 'cp' );

		return wpautop( $error_message );
	}

	/**
	 * Returns the minimum password length to use for validation when the strength meter is disabled.
	 *
	 * @return int
	 */
	public function get_minimum_password_length() {

		return apply_filters( 'coursepress_min_password_length', 6 );
	}

	/**
	 * If the strength meter is enabled, this method checks a hidden field to make sure that the password is strong enough.
	 *
	 * If the strength meter is disabled then this method makes sure that the password meets the minimum length requirement and has the required characters.
	 *
	 * @return string
	 */
	public function is_password_strong() {

		$confirm_weak_password = isset( $_POST['confirm_weak_password'] ) ? (boolean) $_POST['confirm_weak_password'] : false;
		$min_password_length = self::get_minimum_password_length();

		if ( self::is_password_strength_meter_enabled() ) {
			$password_strength = isset( $_POST['password_strength_level'] ) ? intval( $_POST['password_strength_level'] ) : 0;

			return $confirm_weak_password || $password_strength >= 3;
		} else {
			$password = isset( $_POST['password'] ) ? $_POST['password'] : '';
			$password_strong = strlen( $password ) >= $min_password_length && preg_match( '#[0-9a-z]+#i', $password );

			return $confirm_weak_password || $password_strong;
		}
	}

	/**
	 * Checks if password strength meter is enabled.
	 *
	 * @return bool
	 */
	public function is_password_strength_meter_enabled() {

		return (boolean) apply_filters( 'coursepress_display_password_strength_meter', true );
	}

	/**
	 * remove_youtube_controls
	 *
	 *
	 * @since 2.0
	 */
	public function remove_youtube_controls( $code ) {
		if ( false !== strpos( $code, 'youtu.be' ) || false !== strpos( $code, 'youtube.com' ) ) {
			$parameters = http_build_query( $this->embed_args );
			$code = preg_replace(
				"@src=(['\"])?([^'\">s]*)@",
				'src=$1$2&' . $parameters,
				$code
			);
		}
		return $code;
	}

	/**
	 * remove_related_videos
	 *
	 *
	 * @since 2.0
	 */
	public function remove_related_videos( $html, $url, $args ) {
		$atts = wp_parse_args(
			$args,
			array(
				'color' => 'white',
				'rel' => 0,
				'modestbranding' => 1,
				'showinfo' => 0,
			)
		);
		$atts = apply_filters( 'coursepress_video_embed_args', $atts, $html, $url, $args );
		$this->embed_args = $atts;;
		// build the query url.
		$parameters = http_build_query( $atts );
		// Another attempt to remove Youtube features.
		add_filter( 'embed_handler_html', array( $this, 'remove_youtube_controls' ) );
		add_filter( 'embed_oembed_html', array( $this, 'remove_youtube_controls' ) );
		// YouTube
		$html = str_replace(
			'feature=oembed',
			'feature=oembed&' . $parameters,
			$html
		);
		return $html;
	}
}
