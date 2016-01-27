<?php

class CoursePress_Debug {

	public static function log( $message, $echo_file = false ) {
		$trace		 = defined( 'DEBUG_BACKTRACE_IGNORE_ARGS' ) ? debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) : debug_backtrace( FALSE );
		$exception	 = new Exception();
		$debug		 = array_shift( $trace );
		$caller		 = array_shift( $trace );
		$exception	 = $exception->getTrace();
		$callee		 = array_shift( $exception );

		if ( true === WP_DEBUG ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				$class = isset( $caller[ 'class' ] ) ? $caller[ 'class' ] . '[' . $callee[ 'line' ] . '] ' : '';
				if ( $echo_file ) {
					error_log( $class . print_r( $message, true ) . 'In ' . $callee[ 'file' ] . ' on line ' . $callee[ 'line' ] );
				} else {
					error_log( $class . print_r( $message, true ) );
				}
			} else {
				$class = isset( $caller[ 'class' ] ) ? $caller[ 'class' ] . '[' . $callee[ 'line' ] . ']: ' : '';
				if ( $echo_file ) {
					error_log( $class . $message . ' In ' . $callee[ 'file' ] . ' on line ' . $callee[ 'line' ] );
				} else {
					error_log( $class . $message );
				}
			}
		}
	}

	public static function e ( $message, $echo_file = false ) {

		if ( CoursePress_Core::$DEBUG || ( defined( 'COURSEPRESS_DEBUG') && true === COURSEPRESS_DEBUG ) ) {

			self::log( $message, $echo_file );

		}

	}


}