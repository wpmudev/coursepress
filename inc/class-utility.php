<?php
/**
 * Class CoursePress_Utility
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Utility {
	protected static $_instance;

	public static function instance() {
		if ( ! static::$_instance ) {
			$class = get_called_class();
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	function __set( $name, $value ) {
		$this->{$name} = $value;
	}

	function __get( $name ) {
		if ( isset( $this->{$name} ) )
			return $this->{$name};

		return null;
	}
}