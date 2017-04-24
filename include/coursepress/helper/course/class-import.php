<?php

/**
 * course import
 *
 * @since 2.0.6
 */


class CoursePress_Helper_Course_Import {

	/**
	 * Import course.
	 *
	 * @since 2.0.6
	 */
	public static function import_sample_course() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		$filename = CoursePress::$path.'asset/file/sample-course.json';
		if ( is_readable( $filename ) ) {
			$file_content = file_get_contents( $filename );
			$courses = json_decode( $file_content );
			CoursePress_Admin_Import::course_importer( $courses, 0, true, false, false );
		}
	}
}
