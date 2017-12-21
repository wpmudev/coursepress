<?php
/**
 * Class CoursePress_Utility
 *
 * @since 3.0
 * @package CoursePress
 */
abstract class CoursePress_Utility {
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

	public function date_time_now() {
		$time_now = current_time( 'timestamp' );
		$date_now = date( 'M/d/y', current_time( 'timestamp' ) );

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
}
