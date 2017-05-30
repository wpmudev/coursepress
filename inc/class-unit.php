<?php
/**
 * Class CoursePress_Unit
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Unit extends CoursePress_User {
	public function __construct( $unit ) {
		if ( !! $unit instanceof WP_Post ) {
			if ( (int) $unit > 0 )
				$unit = get_post( $unit );
			else
				$this->is_error = true;
		}

		foreach ( $unit as $key => $value )
			$this->__set( $key, $value );
	}

	function is_available() {
	}
}