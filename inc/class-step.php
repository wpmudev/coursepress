<?php
/**
 * Class CoursePress_Step
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step extends CoursePress_Utility {
	public function __construct( $step ) {
		if ( ! $step instanceof WP_Post )
			$step = get_post( $step );

		if ( ! $step instanceof  WP_Post ) {
			$this->is_error = true;

			return;
		}

		foreach ( $step as $key => $value ) {
			$this->__set( $key, $value );
		}
	}
}