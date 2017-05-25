<?php
/**
 * CoursePress functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */
if ( ! function_exists( 'coursepress_render' ) ) :
	/**
	 * Get or print the given filename.
	 *
	 * @param string $filename The relative path of the file.
	 * @param array $args Optional arguments to set as variable
	 * @param bool $echo Whether to return the result in string or not.
	 * @return mixed
	 */
	function coursepress_render( $filename, $args = array(), $echo = true ) {
		$path = plugin_dir_path( __DIR__ );
		$filename = $path . $filename . '.php';

		if ( file_exists( $filename ) && is_readable( $filename ) ) {
			if ( ! empty( $args ) ) {
				$args = (array) $args;

				foreach ( $args as $key => $value ) {
					$$key = $value;
				}
			}

			if ( $echo )
				include $filename;
			else {
				ob_start();

				include $filename;

				return ob_get_clean();
			}
			return true;
		}

		return false;
	}
	endif;
