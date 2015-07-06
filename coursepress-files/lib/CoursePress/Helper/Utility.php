<?php

class CoursePress_Helper_Utility {

	// Used by the array uasort() callbacks
	private static $sort_key;

	// Sort multi-dimension arrays on 'order' value.
	public static function sort_on_key( $array, $sort_key, $sort_asc = true ) {
		self::$sort_key = $sort_key;

		if ( $sort_asc === false ) {
			uasort( $array, array( get_class(), 'sort_desc' ) );
		} else {
			uasort( $array, array( get_class(), 'sort_asc' ) );
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

	public static function get_id( $user ) {
		if ( ! is_object( $user ) ) {
			return $user;
		} else {
			return $user->ID;
		}
	}

}