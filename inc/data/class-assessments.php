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

		if ( ! $this->course instanceof CoursePress_Course ) {
			return $this->wp_error();
		}
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
	 * @param string $graded Graded or ungraded.
	 * @param int $count Count of assessments.
	 *
	 * @return array
	 */
	function get_assessments( $unit_id, $graded = 'all', &$count = 0 ) {

		$course_settings = $this->course->get_settings();
		// Minimum grade required.
		$minimum_grade = isset( $course_settings['minimum_grade_required'] ) ? $course_settings['minimum_grade_required'] : 100;
		$assessments = array(
			'pass_grade' => $minimum_grade,
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
		if ( empty( $students ) || empty( $units ) ) {
			return array();
		}

		// Flag to check if modules count updated.
		$modules_counted = false;
		// Prepare assessment data for each students.
		foreach ( $students as $student_id => $student ) {

			// Set the user object to main array.
			$assessments['students'][ $student_id ] = $student;

			// Do not continue if user not completed the course.
			if ( ! $student->is_course_completed( $this->course->ID ) ) {
				// We need to exclude this user from count.
				$count = $count -= 1;
				unset( $assessments['students'][ $student_id ] );
				continue;
			}

			// If filtered by unit and that unit is not accessable to student.
			if ( ! empty( $unit_id ) && count( $units ) === 1 ) {
				$unit = reset( $units );
				if ( ! $unit->is_accessible_by( $student_id ) ) {
					// We need to exclude this user from count.
					$count -= 1;
					unset( $assessments['students'][ $student_id ] );
					continue;
				}
			}

			$grade = $student->get_course_grade( $course_id );
			//Filter based on the graded param.
			if ( $graded == 'graded' && $grade < $minimum_grade ) {
				$count -= 1;
				unset( $assessments['students'][ $student_id ] );
				continue;
			} elseif ( $graded == 'ungraded' && $grade >= $minimum_grade ) {
				$count -= 1;
				unset( $assessments['students'][ $student_id ] );
				continue;
			}

			// Set unit data under user.
			$assessments['students'][ $student_id ]->units = $units;

			// Student grade for the course.
			$assessments['students'][ $student_id ]->grade = $grade;

			// Loop through each units.
			foreach ( $units as $unit_id => $unit ) {

				// Get the modules for the unit.
				$modules_steps = $unit->get_modules_with_steps();
				$answerable_modules = 0;

				// Remove non answerable items.
				foreach ( $modules_steps as $mkey => $module ) {
					foreach ( $module['steps'] as $step_id => $step ) {
						// If step is not answerable or assessable, unset.
						if ( ! $step->is_answerable() ) {
							unset( $modules_steps[ $mkey ]['steps'][ $step_id ] );
						} else {
							$answerable_modules++;
							$step->grade = $student->get_step_grade( $course_id, $unit->ID, $step_id );
						}
					}
				}

				// Update the modules count.
				if ( ! $modules_counted ) {
					$assessments['modules_count'] += count( $modules_steps );
				}

				// If modules not found, skip.
				if ( ! empty( $modules_steps ) ) {
					$assessments['students'][ $student_id ]->units[ $unit_id ]->modules = $modules_steps;
				}

				// Flag this unit as non answerable if not answerable.
				$assessments['students'][ $student_id ]->units[ $unit_id ]->is_answerable = $answerable_modules > 0 ? true : false;
			}

			$modules_counted = true;
		}

		$assessments['students_count'] = $count;

		return $assessments;
	}

	/**
	 * Get details of an assessment
	 *
	 * @param int $student_id Student ID.
	 * @param int $unit_id Unit ID.
	 * @param string $progress Unit progress.
	 *
	 * @return arary
	 */
	public function get_assessment_details( $student_id, $display = 'all' ) {

		$course_settings = $this->course->get_settings();

		// Minimum grade required.
		$minimum_grade = isset( $course_settings['minimum_grade_required'] ) ? $course_settings['minimum_grade_required'] : 100;
		$assessment = array(
			'pass_grade' => $minimum_grade,
			'modules_count' => 0,
		);

		// If course id not found.
		if ( empty( $this->course->ID ) ) {
			return array();
		}

		$course_id = $this->course->ID;

		$assessment['course'] = $this->course;

		$student = coursepress_get_user( $student_id );

		// Get units for the course.
		$units = $this->_get_units();

		// If no students found, return early.
		if ( empty( $student ) ) {
			return array();
		}

		// Set the user object to main array.
		$assessment['student'] = $student;

		$grade = $student->get_course_grade( $course_id );

		// Set unit data under user.
		$assessment['units'] = $units;

		// Student grade for the course.
		$assessment['grade'] = $grade;

		// Loop through each units.
		foreach ( $units as $unit_key => $unit ) {
			// Get the modules for the unit.
			$modules_steps = $unit->get_modules_with_steps();
			$answerable_modules = $gradable_modules = 0;
			foreach ( $modules_steps as $mkey => $module ) {
				foreach ( $module['steps'] as $step_id => $step ) {
					// If step is not answerable or assessable, unset.
					if ( ! $step->is_answerable() || ( ! $step->is_assessable() && 'all_assessable' == $display ) ) {
						unset( $modules_steps[ $mkey ]['steps'][ $step_id ] );
					} else {
						// Set grade.
						$step_grade = $student->get_step_grade( $course_id, $unit->ID, $step_id );
						if ( $step->type === 'fileupload' && ( empty( $step_grade ) || $step_grade === 'pending' ) ) {
							$modules_steps[ $mkey ]['steps'][ $step_id ]->is_graded = false;
							$modules_steps[ $mkey ]['steps'][ $step_id ]->grade = 0;
						} else {
							$modules_steps[ $mkey ]['steps'][ $step_id ]->is_graded = true;
							$modules_steps[ $mkey ]['steps'][ $step_id ]->grade = round( $step_grade );
							$gradable_modules++;
						}
						$answerable_modules++;
					}
				}
			}

			// If modules not found, skip.
			if ( ! empty( $modules_steps ) ) {
				$assessment['units'][ $unit_key ]->modules = $modules_steps;
			}

			// Flag this unit as non answerable if not answerable.
			$assessment['units'][ $unit_key ]->is_answerable = $answerable_modules > 0 ? true : false;
			// Flag this unit if grade can be displayed.
			$assessment['units'][ $unit_key ]->is_graded = $gradable_modules > 0 ? true : false;
		}

		return $assessment;
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
