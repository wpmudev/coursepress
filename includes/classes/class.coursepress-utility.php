<?php

class CoursePress_Helper_Utility {

	public static $sort_key = '';
	public static $sort_item = '';

	// Sort multi-dimension arrays on 'order' value.
	public static function sort_on_object_key( $array, $sort_key, $sort_asc = true, $item = '' ) {
		self::$sort_key = $sort_key;
		self::$sort_item = $item;

		// Suppress errors on uasort() because of PHP bug. Remove suppression if testing.
		if ( $sort_asc === false ) {
			@uasort( $array, array( __CLASS__, 'sort_obj_desc' ) );
		} else {
			@uasort( $array, array( __CLASS__, 'sort_obj_asc' ) );
		}

		return $array;
	}

	// uasort callback to sort ascending
	public static function sort_obj_asc( $x, $y ) {

		if( empty( self::$sort_item ) ) {
			$obj1 = $x;
			$obj2 = $y;
		} else {
			if( ! isset( $x[ self::$sort_item ] ) || ! isset( $y[ self::$sort_item ] ) ) {
				return 0;
			}
			$obj1 = $x[ self::$sort_item ];
			$obj2 = $y[ self::$sort_item ];
		}

		if ( $obj1->{self::$sort_key} == $obj2->{self::$sort_key} ) {
			return 0;
		} else if ( $obj1->{self::$sort_key} < $obj2->{self::$sort_key} ) {
			return -1;
		} else {
			return 1;
		}
	}

	// uasort callback to sort descending
	public static function sort_obj_desc( $x, $y ) {

		if( empty( self::$sort_item ) ) {
			$obj1 = $x;
			$obj2 = $y;
		} else {
			if( ! isset( $x[ self::$sort_item ] ) || ! isset( $y[ self::$sort_item ] ) ) {
				return 0;
			}
			$obj1 = $x[ self::$sort_item ];
			$obj2 = $y[ self::$sort_item ];
		}

		if ( $obj1->{self::$sort_key} == $obj2->{self::$sort_key} ) {
			return 0;
		} else if ( $obj1->{self::$sort_key} > $obj2->{self::$sort_key} ) {
			return - 1;
		} else {
			return 1;
		}
	}

}