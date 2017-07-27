<?php
/**
 * Class CoursePress_Data_Assessments
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Data_Assessments extends CoursePress_Utility {
	/**
	 * @var CoursePress_Course The parent course.
	 */
	private $course;

	/**
	 * CoursePress_Data_Assessments constructor.
	 *
	 * @param int $course_id Course id.
	 */
	public function __construct( $course_id ) {

		// Get the course object.
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
	 *
	 * @param int $unit_id Unit ID.
	 * @param int $count Count of assessments.
	 *
	 * @return array
	 */
	function get_assessments( $unit_id, &$count = 0 ) {

		$assessments = array(
			'pass_grade' => $this->course->get_settings()['minimum_grade_required'],
			'modules_count' => 0,
			'students_count' => 0,
			'grade_system' => ( empty( $unit_id ) || $unit_id == 'all' )
				? __( 'total acquired grade % total number of gradable modules', 'cp' )
				: __( 'total acquired assessable grade % total number of assessable modules', 'cp' ),
		);

		// If course id not found.
		if ( empty( $this->course->ID ) ) {
			return array();
		}

		$course_id = $this->course->ID;

		$students = $this->_get_students( $course_id, $count );

		// Get units for the course.
		$units = $this->_get_units( $unit_id );

		// If no students found, return early.
		if ( empty( $students ) || ! empty( $units ) ) {
			return array();
		}

		// Flag to check if modules count updated.
		$modules_counted = false;
		// Prepare assessment data for each students.
		foreach ( $students as $student_id => $student ) {

			// Do not continue if user not completed the course.
			if ( ! $student->is_course_completed( $this->course->ID ) ) {
				// We need to exclude this user from count.
				$count = $count -= 1;
				continue;
			}

			// If filtered by unit and that unit is not accessable to student.
			if ( ! empty( $unit_id ) && count( $units ) === 1 ) {
				$unit = reset( $units );
				if ( ! $unit->is_accessible_by( $student_id ) ) {
					// We need to exclude this user from count.
					$count -= 1;
					continue;
				}
			}

			// Set the user object to main array.
			$assessments['students'][ $student_id ] = $student;

			// Set unit data under user.
			$assessments['students'][ $student_id ]->units = $units;

			// Loop through each units.
			foreach ( $units as $unit_id => $unit ) {

				// Get the modules for the unit.
				$modules_steps = $unit->get_modules_with_steps();
				// Update the modules count.
				if ( ! $modules_counted ) {
					$assessments['modules_count'] += count( $modules_steps );
				}

				// If modules not found, skip.
				if ( ! empty( $modules_steps ) ) {
					$assessments['students'][ $student_id ]['units'][$unit_id]['modules'] = $modules_steps;
				}
			}

			$modules_counted = true;
		}

		$assessments['students_count'] = $count;

		return $assessments;
	}

	/**
	 * Get the list of student users.
	 *
	 * @param int $course_id Course ID.
	 * @param int $count Total count of the students (pass by ref.).
	 *
	 * @return array CoursePress_User objects.
	 */
	private function _get_students( $course_id, &$count = 0 ) {

		// Query arguments for WP_User_Query.
		$args = array();

		$student_ids = coursepress_get_students_ids( $course_id );
		// Include only these courses in result.
		if ( ! empty( $student_ids ) ) {
			$args['include'] = $student_ids;
		} else {
			return array();
		}

		// Add multisite support.
		if ( is_multisite() ) {
			$args['blog_id'] = get_current_blog_id();
		}

		// Set the parameters for pagination.
		$args['number'] = $this->items_per_page( 'coursepress_assesments_per_page' );
		$args['paged'] = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

		return coursepress_get_students( $args, $count );
	}

	/**
	 * Get units for the course assessment.
	 *
	 * @param int $unit_id Unit ID (optional).
	 *
	 * @return array Array of unit objects.
	 */
	private function _get_units( $unit_id = 0 ) {

		$units = array();

		// If unit id is given, get the unit object.
		if ( ! empty( $unit_id ) ) {
			$unit = coursepress_get_unit( $unit_id );
			if ( ! empty( $unit->post_parent ) && $unit->post_parent == $this->course->ID ) {
				$units[ $unit->ID ] = $unit;
			}
		} else {
			// Get all units for the course.
			$units = $this->course->get_units();
		}

		return $units;
	}
}