<?php
/**
 * Class CoursePress_Export
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Export extends CoursePress_Utility {

	/**
	 * Course data to export.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * CoursePress_Export constructor.
	 *
	 * @param int $course_id Course id.
	 */
	public function __construct() {
	}

	/**
	 * Prepare data to be exported.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return bool
	 */
	private function prepare_data( $course_id ) {

		// WP_Post object for course.
		$post = get_post( $course_id );
		// Get course from course id.
		$course = coursepress_get_course( $post );

		// If course do not found, bail out.
		if ( is_wp_error( $course ) || empty( $course ) ) {
			return false;
		}

		// Set the couse data.
		$this->data['course'] = $post;
		// Course author user.
		$this->data['author'] = $course->get_author();
		// Get course categories.
		$this->data['categories'] = $course->get_category();
		// Course meta data.
		$this->data['meta'] = $this->_get_course_meta( $course_id );
		// Course instructors.
		$this->data['instructors'] = $course->get_instructors();
		// Course facilitators.
		$this->data['facilitators'] = $course->get_facilitators();

		// Course all units.
		$units = $course->get_units( false );
		if ( ! empty( $units ) ) {
			foreach ( $units as $unit ) {
				// Set other sub items like modules, steps for the unit.
				$this->_set_unit_data( $unit );
			}
		}

		/**
		 * Filter hook to include/exclude students from export.
		 *
		 * @param bool
		 * @param $course_id Course ID.
		 */
		if ( apply_filters( 'coursepress_export_course_include_students', true, $course_id ) ) {
			// Set students list for the course.
			$students = $course->get_students();
			if ( ! empty( $students ) ) {
				$this->data['students'] = $students;
			}
		}
	}

	/**
	 * Get course meta data to export.
	 *
	 * @param int $course_id Course ID.
	 *
	 * @return array
	 */
	private function _get_course_meta( $course_id ) {

		// If course id not set.
		if ( empty( $course_id ) ) {
			return array();
		}

		// Get course post meta.
		$meta = get_post_meta( $course_id );

		// Array of meta keys to exclude.
		// @todo Add excluded meta keys here.
		$exclude = array();

		if ( ! empty( $exclude ) ) {
			// Unset excluded meta values.
			foreach ( $exclude as $key ) {
				if ( isset( $meta[ $key ] ) ) {
					unset( $meta[ $key ] );
				}
			}
		}

		return $meta;
	}

	/**
	 * Get unit sub items and other data.
	 *
	 * @param object $unit Unit object.
	 *
	 * @return array Unit data.
	 */
	private function _set_unit_data( $unit ) {

		// Do not continue if unit exists.
		if ( empty( $unit->ID ) ) {
			return array();
		}

		$unit_id = $unit->ID;
		// Get unit meta values.
		$meta = get_post_meta( $unit_id );
		if ( ! empty( $meta ) ) {
			$this->data['units'][ $unit_id ] = $unit;
			$this->data['units'][ $unit_id ]->meta = $meta;
		}

		// Get unit modules.
		$modules = $unit->get_modules();
		foreach ( $modules as $module_id => $module ) {
			// Get module meta.
			$module_meta = get_post_meta( $module_id );
			$module['meta'] = $module_meta;
			$this->data['units'][ $unit_id ]->modules = array( $module_id => $module );

			/**
			 * Filter hook to include/exclude comments from export.
			 *
			 * @param bool
			 * @param $module_id Module ID.
			 */
			if ( apply_filters( 'coursepress_export_course_include_module_comments', true, $module_id ) ) {
				// Get module comments
				$comments = get_comments( 'post_id=' . $module_id );
				foreach ( $comments as $comment_id => $comment ) {
					$comment->user = coursepress_get_user( $comment->user_id );
					$comment->unit_id = $unit_id;
					$comment->module_id = $module_id;
					$this->data['comments']['modules'][ $module_id ][] = $comment;
				}
			}
		}
	}

	/**
	 * Generate export file name dynamically.
	 *
	 * Generate a unique file name to export course in json.
	 *
	 * @return string File name.
	 */
	private function get_file_name() {

		// Get site name.
		$site_name = sanitize_key( get_bloginfo( 'name' ) );
		$site_name = empty( $site_name ) ? '' : $site_name . '.';

		// Create export file name.
		$filename = $site_name . 'coursepress.' . time() . '.json';

		// Course slug.
		if ( isset( $this->data['course'] ) ) {
			$course_name = empty( $this->data['course'] ) ? '' : '.'. $this->data['course']->post_name;
			$filename = $site_name . 'coursepress.' . time() . $course_name . '.json';
		}
		return $filename;
	}

	/**
	 * Export course data to JSON file.
	 *
	 * If course data is set properly, export them to a json file
	 * and send to user browser for download.
	 *
	 * @return void
	 */
	private function export() {
		// If valid data found, export it.
		if ( ! empty( $this->data ) ) {
			// Get the file name.
			$file_name = $this->get_file_name();
			// Set proper headers for json file.
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . $file_name );
			header( 'Content-Type: text/json; charset=' . get_option( 'blog_charset' ), true );
			$option = defined( 'JSON_PRETTY_PRINT' )? JSON_PRETTY_PRINT : null;
			echo json_encode( $this->data, $option );
			exit;
		}
	}

	/**
	 * Export single course
	 */
	public function export_course( $course_id ) {
		$courses = array( $course_id );
		$this->export_courses( $courses );
	}

	/**
	 * Export Courses
	 */
	public function export_courses( $courses ) {
		$data = array();
		foreach ( $courses as $course_id ) {
			$this->prepare_data( $course_id );
			$data[ $course_id ] = $this->data;
		}
		$this->data = $data;
		$this->export();
	}
}
