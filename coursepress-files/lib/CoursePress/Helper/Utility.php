<?php

class CoursePress_Helper_Utility {

	// Used by the array uasort() callbacks
	private static $sort_key;

	// Sort multi-dimension arrays on 'order' value.
	public static function sort_on_key( $array, $sort_key, $sort_asc = true ) {
		self::$sort_key = $sort_key;

		if ( $sort_asc === false ) {
			uasort( $array, array( __CLASS__, 'sort_desc' ) );
		} else {
			uasort( $array, array( __CLASS__, 'sort_asc' ) );
		}

		return $array;
	}

	// uasort callback to sort ascending
	public static function sort_asc( $x, $y ) {
		if ( $x[ self::$sort_key ] == $y[ self::$sort_key ] ) {
			return 0;
		} else if ( $x[ self::$sort_key ] < $y[ self::$sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// uasort callback to sort descending
	public static function sort_desc( $x, $y ) {
		if ( $x[ self::$sort_key ] == $y[ self::$sort_key ] ) {
			return 0;
		} else if ( $x[ self::$sort_key ] > $y[ self::$sort_key ] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	// set array value based on path
	public static function set_array_val( &$a, $path, $value ) {
		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
		}

		$key = array_pop( $path );
		foreach ( $path as $k ) {
			if ( ! isset( $a[ $k ] ) ) {
				$a[ $k ] = array();
			}
			$a = &$a[ $k ];
		}
		$a[ $key ? $key : count( $a ) ] = $value;
	}

	// get array value based on path
	public static function get_array_val( $a, $path ) {
		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
		}

		foreach ( $path as $k ) {
			if ( isset( $a[ $k ] ) ) {
				$a = &$a[ $k ];
			} else {
				return null;
			}
		}

		return $a;
	}

	public static function unset_array_val( &$a, $path ) {
		if ( ! is_array( $path ) ) {
			$path = explode( '/', $path );
		}

		$key = array_pop( $path );
		foreach ( $path as $k ) {
			if ( ! isset( $a[ $k ] ) ) {
				$a[ $k ] = array();
			}
			$a = &$a[ $k ];
		}
		unset( $a[ $key ? $key : count( $a ) ] );
	}

	public static function object_to_array( $object ) {
		if ( is_object( $object ) ) {
			$object = get_object_vars( $object );
		}

		if ( is_array( $object ) ) {
			return array_map( array( __CLASS__, 'object_to_array' ), $object );
		} else {
			return $object;
		}
	}

	public static function array_to_object( $array ) {
		if ( is_array( $array ) ) {
			return (object) array_map( array( __CLASS__, 'array_to_object' ), $array );
		} else {
			return $array;
		}
	}


	// Does a recursive array merge without creating 'mini' arrays as array_merge_recursive() does
	public static function merge_distinct( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset ( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				$merged [ $key ] = self::merge_distinct( $merged [ $key ], $value );
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
	}

	function delete_user_meta_by_key( $meta_key ) {
		global $wpdb;

		$legacy = delete_metadata( 'user', 0, $meta_key, '', true );

		$meta_key = $wpdb->prefix . $meta_key;

		if ( $legacy || delete_metadata( 'user', 0, $meta_key, '', true ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function get_id( $user ) {
		if ( ! is_object( $user ) ) {
			return $user;
		} else {
			return $user->ID;
		}
	}


	// Deals with legacy 'on' / 'off' values for checkboxes
	public static function checked( $value, $compare = true, $echo = false ) {
		$checked = false;
		if( $compare === true ) {
			$checked =  ! empty( $value ) || ( ! empty( $value ) && 'on' === $value ) ? 'checked="checked"' : '';
		} else {
			$checked = $compare === $value ? 'checked="checked"' : '';

		}

		if( $echo ) {
			echo $checked;
		} else {
			return $checked;
		}
	}

	// Get appropriate AJAX URL
	public static function get_ajax_url() {
		$scheme = ( is_ssl() || force_ssl_admin() ? 'https' : 'http' );

		return admin_url( "admin-ajax.php", $scheme );
	}

	// Allowed image extensions
	public static function get_image_extensions() {
		return apply_filters( 'coursepress_allowed_image_extensions', array(
			'jpg',
			'jpeg',
			'jpe',
			'gif',
			'png',
			'bmp',
			'tif',
			'tiff',
			'ico'
		) );
	}

	// Filter HTML
	public static function filter_content( $content, $none_allowed = false ) {
		if ( $none_allowed ) {
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
						$content[ $content_key ] = wp_kses( $content_value, self::filter_content_rules() );
					}
				} else {
					$content = wp_kses( $content, self::filter_content_rules() );
				}
			}
		}

		return $content;
	}

	// Allowed tags
	public static function filter_content_rules() {
		$allowed_tags = wp_kses_allowed_html( 'post' );

		return apply_filters( 'coursepress_allowed_post_tags', $allowed_tags );
	}

}