<?php
/**
 * Class CoursePress_Assessments
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Assessments extends CoursePress_Utility {
	/**
	 * @var CoursePress_Course The parent course.
	 */
	protected $course;

	/**
	 * CoursePress_Assessments constructor.
	 *
	 * @param int $course_id Course id.
	 */
	public function __construct( $course_id, $student_ids = array() ) {

		$this->course = coursepress_get_course( $course_id );

		if ( ! $this->course instanceof CoursePress_Course )
			return $this->wp_error();
	}

	/**
	 * Return error on invalid data.
	 *
	 * @return WP_Error
	 */
	function wp_error() {

		return new WP_Error( 'wrong_param', __( 'Unable to initialize CoursePress_Assessment!', 'cp' ) );
	}

	/**
	 * Get assessments data for a course.
	 */
	function get_assessments() {

		$assessments = array();

		if ( empty( $this->course->ID ) ) {
			return array();
		}

		$course_id = $this->course->ID;

		$student_ids = coursepress_get_students_ids( $course_id );
		$units = $this->course->get_units();

		if ( empty( $student_ids ) ) {
			return array();
		}

		foreach ( $student_ids as $student_id ) {

			$student = coursepress_get_user( $student_id );

			if ( is_wp_error( $student ) ) {
				continue;
			}

			$assessments[ $student_id ] = $student;

			$student_progress = $student->get_course_progress_data( $course_id );
			$have_submissions = is_array( $student_progress ) && count( $student_progress ) > 0;

			if ( false === $have_submissions ) {
				continue;
			}

			$i = 0;
			foreach ( $units as $unit ) {

				$assessments[ $student_id ]['units'][$i] = $unit;

				$modules = $unit->get_modules();

				if ( empty( $modules ) ) {
					continue;
				}

				foreach ( $modules as $module_id => $module ) {

					$assessments[ $student_id ]['units'][$i]['modules'][$module_id] = $module;
				}

				$i++;
			}
		}

		return $assessments;
	}
}