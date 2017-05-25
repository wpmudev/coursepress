<?php
/**
 * Class CoursePress_Course
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Course extends CoursePress_Utility {
	public function __construct( $course ) {
		if ( ! $course instanceof WP_Post ) {
			$course = get_post( (int) $course );
		}

		foreach ( $course as $key => $value ) {
			$this->__set( $key, $value );
		}
	}
}