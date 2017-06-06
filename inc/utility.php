<?php
/**
 * CoursePress utility functions and definitions
 *
 * @since 3.0
 * @package CoursePress
 */

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

/**
 * Helper function to set an array value base on path.
 *
 * @param $array
 * @param $path
 * @param $value
 *
 * @return array
 */
function coursepress_set_array_val( $array, $path, $value ) {
	if ( ! is_array( $path ) )
		$path = explode( '/', $path );

	if ( ! is_array( $array ) )
		$array = array();

	$key = array_shift( $path );

	if ( count( $path ) > 0 ) {
		if ( ! isset( $array[ $key ] ) )
			$array[ $key ] = array();

		$array[ $key ] = coursepress_set_array_val( $array[$key], $path, $value );
	} else {
		$array[ $key ] = $value;
	}

	return $array;
}

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

/**
 * Get CoursePress courses url.
 *
 * @return string
 */
function coursepress_get_url() {
	$slug = coursepress_get_setting( 'slugs/course', 'courses' );

	return trailingslashit( home_url( '/' . $slug ) );
}

function coursepress_user_have_comments( $student_id, $post_id ) {
	$args = array(
		'post_id' => $post_id,
		'user_id' => $student_id,
		'order' => 'ASC',
		'offset' => 0,
		'number' => 1, // We only need one to verify if current user posted a comment.
		'fields' => 'ids',
		'status' => 'all',
	);
	$comments = get_comments( $args );

	return count( $comments ) > 0;
}
