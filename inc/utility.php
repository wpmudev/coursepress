<?php
/**
 * CoursePress utility functions and definitions
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

if ( ! function_exists( 'coursepress_get_array_val' ) ) :
	/**
	 * Helper function to get the value of an dimensional array base on path.
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed|null|string
	 */
	function coursepress_get_array_val( $array, $key, $default = '' ) {
		if ( ! is_array( $array ) )
			return null;

		$keys = explode( '/', $key );
		$last_key = array_pop( $keys );

		foreach ( $keys as $k ) {
			if ( isset( $array[ $k ] ) )
				$array = $array[ $k ];
		}

		if ( isset( $array[ $last_key ] ) )
			return $array[ $last_key ];

		return $default;
	}
endif;

if ( ! function_exists( 'coursepress_set_array_val' ) ) :
	/**
	 * Helper function to set an array value base on path.
	 *
	 * @param $array
	 * @param $key
	 * @param $value
	 *
	 * @return array
	 */
	function coursepress_set_array_val( $array, $key, $value ) {
		$keys = explode( '/', $key );
		$last_key = array_pop( $keys );

		foreach ( $keys as $k ) {
			if ( isset( $array[ $k ] ) )
				$array = $array[ $k ];
		}

		if ( isset( $array[ $last_key ] ) )
			$array[ $last_key ] = $value;

		return $array;
	}
endif;

if ( ! function_exists( 'coursepress_get_option' ) ) :
	/**
	 * Helper function to get global option in either single or multi site.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function coursepress_get_option( $key, $default = '' ) {
		if ( is_multisite() )
			$value = get_site_option( $key, $default );
		else
			$value = get_option( $key, $default );

		return $value;
	}
endif;

if ( ! function_exists( 'coursepress_get_url' ) ) :
	function coursepress_get_url() {
		$slug = coursepress_get_setting( 'slugs/course', 'courses' );

		return trailingslashit( home_url( '/' . $slug ) );
	}
endif;