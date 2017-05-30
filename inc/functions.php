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

if ( ! function_exists( 'coursepress_get_setting' ) ) :
	/**
	 * Get coursepress global setting.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function coursepress_get_setting( $key, $default = '' ) {
		$settings = coursepress_get_option( 'coursepress_settings' );

		return coursepress_get_array_val( $settings, $key, $default );
	}
	endif;

if ( ! function_exists( 'coursepress_get_courses' ) ) :
	function coursepress_get_courses( $args = array() ) {
		global $CoursePress_Data_Courses;

		return $CoursePress_Data_Courses->get_courses( $args );
	}
	endif;

if ( ! function_exists( 'coursepress_get_instructor_courses' ) ) :
	function coursepress_get_instructor_courses( $instructor_id = 0, $publish = true, $ids = true, $all = false ) {
		if ( empty( $instructor_id ) )
			return null;

		$args = array(
			'meta_key' => 'instructor',
			'meta_value' => $instructor_id,
			'post_status' => $publish ? 'publish' : 'any',
			'posts_per_page' => $all ? -1 : 20,
			'suppress_filters' => true,
		);

		if ( $ids )
			$args['fields'] = 'ids';

		return coursepress_get_courses( $args );
	}
endif;

if ( ! function_exists( 'coursepress_get_enrolled_courses' ) ) :
	function coursepress_get_enrolled_courses( $user_id, $publish = true, $ids = true, $all = false ) {
		if ( empty( $user_id ) )
			return null;

		$args = array(
			'meta_key' => 'student',
			'meta_value' => $user_id,
			'post_status' => $publish ? 'publish' : 'any',
			'posts_per_page' => $all ? -1 : 20,
			'suppress_filters' => true,
		);

		if ( $ids )
			$args['fields'] = 'ids';

		return coursepress_get_courses( $args );
	}
	endif;

if ( ! function_exists( 'coursepress_add_instructor' ) ) :
	function coursepress_add_instructor( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return false;

		$CoursePress_Data_Courses->add_course_meta( $course_id, 'instructors', $user_id );

		/**
		 * Trigger whenever a new instructor is added to a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_add_instructor', $user_id, $course_id );

		return true;
	}
	endif;

if ( ! function_exists( 'coursepress_delete_instructor' ) ) :
	function coursepress_delete_instructor( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return false;

		$CoursePress_Data_Courses->delete_course_meta( $course_id, 'instructors', $user_id );

		/**
		 * Trigger whenever an instructor is removed from the course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_delete_instructor', $user_id, $course_id );
	}
	endif;

if ( ! function_exists( 'coursepress_add_student' ) ) :
	function coursepress_add_student( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return null;

		$CoursePress_Data_Courses->add_course_meta( $course_id, 'student', $user_id );

		/**
		 * Fire whenever a new student is added to a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_add_student', $user_id, $course_id );

		return true;
	}
	endif;

if ( ! function_exists( 'coursepress_delete_student' ) ) :
	function coursepress_delete_student( $user_id = 0, $course_id = 0 ) {
		global $CoursePress_Data_Courses;

		if ( empty( $user_id ) || empty( $course_id ) )
			return null;

		$CoursePress_Data_Courses->delete_course_meta( $course_id, 'student', $user_id );

		/**
		 * Fired whenever an student is removed from a course.
		 *
		 * @since 3.0
		 * @param int $user_id
		 * @param int $course_id
		 */
		do_action( 'coursepress_delete_student', $user_id, $course_id );

		return true;
	}
	endif;
