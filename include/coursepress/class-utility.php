<?php
/**
 * CoursePress utility helper.
 **/
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'CoursePress_Utility' ) ) :
	class CoursePress_Utility {
		/**
		 * Include the given filename with additional args.
		 *
		 * @param (string) $filename
		 * @param (array) $args					Optional. Additional arguments to pass unto the included file.
		 **/
		public static function render( $filename, $args = array() ) {
			$filename = CoursePress::$path . $filename . '.php';

			if ( is_readable( $filename ) ) {
				// Iterate args
				if ( ! empty( $args ) ) {
					foreach ( $args as $key => $value ) {
						$$key = $value;
					}
				}

				require $filename;
			}
		}
	}
endif;
