<?php
/**
 * Class CoursePress_Utility
 *
 * @since 3.0
 * @package CoursePress
 */
abstract class CoursePress_Utility {
    function __set( $name, $value ) {
        $this->{$name} = $value;
    }

    function __get( $name ) {
        if ( isset( $this->{$name} ) )
            return $this->{$name};

        return null;
    }

    function setUp( $args ) {
        if ( ! empty( $args ) ) {
            foreach ( $args as $key => $value ) {
                $this->__set( $key, $value );
            }
        }
    }

    function date_time_now() {
        $time_now = current_time( 'timestamp' );
        $date_now = date( 'M/d/y', current_time( 'timestamp' ) );

        // Time now is not the current time but rather the timestamp of the starting date today (00:01).
        $time_now = strtotime( $date_now, $time_now );

        return $time_now;
    }

    function setAttributes( $attr = array() ) {
        if ( ! $attr )
            return '';

        $vars = array();

        foreach ( $attr as $key => $value ) {
            $vars[] = $key . '="' . esc_attr( $value ) . '"';
        }

        return implode( ' ', $vars );
    }

    function create_html( $tag, $attributes = array(), $content = '' ) {
        $html = '<' . $tag;

        if ( ! empty( $attributes ) )
            $html .= ' ' . $this->setAttributes( $attributes );

        $single_tags = array( 'img', 'input' );

        if ( in_array( $tag, $single_tags ) )
            $html .= ' />';
        else
            $html .= '>' . $content . '</' . $tag . '>';

        return $html;
    }

    function to_array( $array ) {
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
    function is_pro() {
    }

    /**
     * Replaces the defined placeholders in the content with specified values.
     *
     * @since  2.0.0
     * @param  string $content The full content, with placeholders.
     * @param  array  $vars List of placeholder => value.
     * @return string The content but with all placeholders replaced.
     */
    function replace_vars( $content, $vars ) {
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
    public function convert_hex_color_to_rgb($hex_color, $default)
    {
        $color_valid = (boolean) preg_match('/^#[a-f0-9]{6}$/i', $hex_color);
        if($color_valid)
        {
            $values = CP_TCPDF_COLORS::convertHTMLColorToDec($hex_color, CP_TCPDF_COLORS::$spotcolor);
            return array_values($values);
        }

        return $default;
    }

	/**
	 * Get the items per page option value.
	 *
	 * @param string $option Screen option name.
	 *
	 * @return int
	 */
    function items_per_page( $option = '' ) {

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

	function is_youtube_url( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host && (strpos($host, 'youtube') !== false || strpos($host, 'youtu.be') !== false);
	}

	function is_vimeo_url( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host && strpos($host, 'vimeo') !== false;
	}

	function create_video_js_setup_data( $url, $hide_related_media = true, $width = 0 ) {
		//$url = 'http://local.wordpress.dev/wp-content/uploads/2017/07/Recording-3.mp4';
		//$url = 'https://www.youtube.com/watch?v=FxYw0XPEoKE';
		//$url = 'https://vimeo.com/6370469';
		$src = false;
		$extra_data = array();

		if( $this->is_youtube_url( $url ) ) {
			$src = 'youtube';
			$show_related_media = ! $hide_related_media;
			$extra_data['youtube'] = array( 'rel' => intval( $show_related_media ) );
		} elseif( $this->is_vimeo_url( $url ) ) {
			$src = 'vimeo';
		}

		$setup_data = array();
		$player_width = $width;

		if( ! $player_width ) {
			$setup_data['fluid'] = true;
		}

		if( $src ) {
			$setup_data['techOrder'] = array($src);
			$setup_data['sources'] = array(
				array(
					'type' => 'video/' . $src,
					'src' => $url,
				)
			);
		}

		$setup_data = array_merge( $setup_data, $extra_data );

		return json_encode($setup_data);
	}
}