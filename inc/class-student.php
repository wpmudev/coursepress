<?php
/**
 * Class CoursePress_Student
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Student extends CoursePress_User {
	function _get_completion_data( $course_id ) {
		global $CoursePress;

		$id = $this->__get( 'ID' );
		$defaults = array( 'version' => $CoursePress->version );
		$progress = false;

		if ( $this->is_enrolled_at( $course_id ) ) {

			$key = 'course_' . $course_id . '_progress';

			$progress = $this->__get( $key );

			if ( ! $progress ) {
				$progress = get_user_option( $key, $id );
			}
		}

		if ( ! $progress ) {
			$progress = $defaults;
		}

		/**
		 * Fire before returning student's course progress.
		 *
		 * @since 2.0
		 */
		$progress = apply_filters( 'coursepress_get_student_progress', $progress, $id, $course_id );

		return $progress;
	}

	function get_completion_data( $course_id ) {
		$c = $this->validate_completion_data( $course_id );

		return $c;
	}

	function validate_completion_data( $course_id ) {
		if ( ! $this->is_enrolled_at( $course_id ) )
			return false;

		if ( ( $completion = $this->__get( 'completion_data' ) ) )
			return $completion;

		$user_id = $this->__get( 'ID' );
		$course = coursepress_get_course( $course_id );
		$progress = $this->_get_completion_data( $course_id );
		$is_done = coursepress_get_array_val( $progress, 'completion/completed' );
		$completion = array();

		$units = $course->get_units( true ); // Only validated published units
		$with_modules = $course->__get( 'with_modules' );
		$unit_grade = 0;
		$total_gradable = 0;
		$total_unit = count( $units );

		foreach ( $units as $unit ) {
			$unit_id = $unit->__get( 'ID' );
			$unit_completion = $this->validate_unit( $unit, $with_modules, $progress );
			$completion = coursepress_set_array_val( $completion, 'completion/' . $unit_id, $unit_completion );

			$unit_grade += coursepress_get_array_val( $unit_completion, 'average' );

			$gradable = coursepress_get_array_val( $unit_completion, 'gradeable' );

			if ( ! empty( $gradeable ) )
				$total_gradable += $gradable;
		}

		if ( $unit_grade > 0 && $total_gradable > 0 )
			$unit_grade = $unit_grade / $total_gradable;

		$completion['average'] = $unit_grade;

		$completion = array_filter( $completion );
		//error_log( print_r( $completion, true ) );
		//error_log( print_r( $progress, true ) );

		$this->__set( 'completion_data', $completion );

		return $completion;
	}

	/**
	 * Helper method to validate per unit user progress.
	 *
	 * @param CoursePress_Unit $unit
	 * @param bool $with_modules
	 * @param array $progress
	 *
	 * @return array
	 */
	private function validate_unit( $unit, $with_modules, $progress ) {
		$completion = array();
		$unit_id = $unit->__get( 'ID' );
		$course_id = $unit->__get( 'course_id' );
		$unit_progress = 0;
		$unit_grade = 0;
		$unit_gradable = 0;
		$unit_pass_grade = 0;
		$force_completion = $unit->__get( 'force_current_unit_completion' );
		$force_pass_completion = $unit->__get( 'force_current_unit_successful_completion' );

		if ( $with_modules ) {
			$modules = $unit->get_modules_with_steps();

			if ( $modules ) {
				$module_progress = 0;
				$total_modules = count( $modules );

				foreach ( $modules as $module_id => $module ) {
					$module_completion = array();
					$module_seen = coursepress_get_array_val(
						$progress,
						'units/' . $unit_id . '/visited_pages/' . $module_id
					);

					if ( $module_seen ) {
						$completion = coursepress_set_array_val(
							$completion,
							'course_module_seen/' . $module_id,
							$module_id
						);
					}

					if ( $module['steps'] ) {
						$steps_completion = $this->validate_steps( $module['steps'], $course_id, $unit_id, $progress, $force_completion, $force_pass_completion );
						$step_progress = $steps_completion['progress'];
						$module_progress += $step_progress;
						$completion = coursepress_set_array_val(
							$completion,
							'modules/' . $module_id . '/progress',
							$step_progress
						);

						$passed = coursepress_get_array_val( $steps_completion, 'passed' );

						if ( ! empty( $passed ) )
							$completion = coursepress_set_array_val( $completion, 'passed', $passed );

						$answered = coursepress_get_array_val( $steps_completion, 'answered' );
						if ( ! empty( $answered ) )
							$completion = coursepress_set_array_val( $completion, 'answered', $answered );

						$seen = coursepress_get_array_val( $steps_completion, 'modules_seen' );
						if ( ! empty( $seen ) )
							$completion = coursepress_set_array_val( $completion, 'modules_seen', $seen );

						$average = coursepress_get_array_val( $steps_completion, 'average' );
						if ( ! empty( $average ) )
							$unit_grade += $average;

						$gradable = coursepress_get_array_val( $steps_completion, 'gradable' );

						if ( ! empty( $gradable ) )
							$unit_grade += $gradable;

						$completed_steps = coursepress_get_array_val( $steps_completion, 'completed_steps' );
						if ( $completed_steps )
							$completion = coursepress_set_array_val( $completion, 'completed_steps', $completed_steps );

						$steps_grades = coursepress_get_array_val( $steps_completion, 'steps_grades' );
						if ( $steps_grades )
							$completion = coursepress_set_array_val( $completion, 'steps_grade', $steps_grades );

					}

					if ( ! empty( $module_completion ) ) {
						$completion = coursepress_set_array_val(
							$completion,
							'modules/' . $module_id,
							$module_completion
						);
					}
				}

				if ( $module_progress > 0 && $total_modules > 0 )
					$unit_progress += $module_progress / $total_modules;

				if ( $unit_grade > 0 && $unit_gradable )
					$unit_grade = $unit_grade / $unit_gradable;

			}
		}

		$completion['progress'] = $unit_progress;
		$completion['average'] = $unit_grade;
		$completion['gradable'] = $unit_gradable;

		if ( $unit_progress >= 100 ) {
			if ( 0 == $unit_gradable ) {
				$completion['completed'] = true;
			}
		}

		return $completion;
	}

	/**
	 * Helper method to validate course steps progress.
	 *
	 * @param array $steps
	 * @param int $course_id
	 * @param int $unit_id
	 * @param array $progress
	 * @param bool $force_completion
	 * @param bool $force_pass_completion
	 *
	 * @return array
	 */
	private function validate_steps( $steps, $course_id, $unit_id, $progress, $force_completion, $force_pass_completion ) {
		$total_steps = count( $steps );
		$required_steps = 0;
		$assessable_steps = 0;
		$step_progress = 0;
		$steps_grade = 0;
		$user_id = $this->__get( 'ID' );
		$passed = array();
		$answered = array();
		$seen = array();
		$gradable = 0;
		$completed = array();
		$steps_grades = array();

		foreach ( $steps as $step ) {
			$step_id = $step->__get( 'ID' );
			$is_required = $step->__get( 'mandatory' );
			$is_assessable = $step->__get( 'assessable' );
			$min_grade = $step->__get( 'minimum_grade' );
			$step_type = $step->__get( 'module_type' );
			$is_answerable = $step->is_answerable();
			$step_seen = coursepress_get_array_val(
				$progress,
				'completion/' . $unit_id . '/modules_seen/' . $step_id
			);
			$valid = false;

			if ( $is_required )
				$required_steps++;
			if ( $is_assessable )
				$assessable_steps++;
			if ( $step_seen )
				$seen[ $step_id ] = $step_id;

			if ( $is_answerable ) {
				$response = $this->get_response( $course_id, $unit_id, $step_id, $progress );

				if ( $is_required || $is_assessable )
					$gradable++;

				if ( ! empty( $response ) ) {
					$answered[ $step_id ] = $step_id;
					$grades = coursepress_get_array_val( $response, 'grades' );

					if ( is_array( $grades ) )
						$grades = array_pop( $grades );

					$grade = coursepress_get_array_val( $grades, 'grade' );

					$steps_grades[ $step_id ] = $grade;

					$pass = $grade >= $min_grade;
					$steps_grade += $grade;

					if ( $pass ) {
						$valid = true;
						$passed[ $step_id ] = $step_id;
					} else {
						if ( ! $is_required )
							$valid = true;
					}


				} else {
					if ( ! $is_required && $step_seen ) {
						$valid = true;
					}
				}
			} else {
				if ( 'discussion' == $step_type ) {
					$has_comments = coursepress_user_have_comments( $user_id, $step_id );

					if ( $has_comments ) {
						$valid = true;
					} elseif ( ! $is_required ) {
						if ( $step_seen )
							$valid = true;
						elseif ( ! $force_completion )
							$valid = true;
					}

				} elseif ( 'video' == $step_type || 'audio' == $step_type ) {
					if ( ! $is_required ) {
						if ( $step_seen )
							$valid = true;
						elseif ( ! $force_completion )
							$valid = true;
					} elseif ( $step_seen )
						$valid = true;

				} elseif ( ! $is_required ) {
					if ( $step_seen )
						$valid = true;
					elseif ( ! $force_completion )
						$valid = true;
				}
			}

			if ( $valid ) {
				$step_progress += 100;
				$completed[ $step_id ] = $step_id;
			}
		}

		if ( $step_progress > 0 && $total_steps > 0 )
			$step_progress = $step_progress / $total_steps;

		$completion = array(
			'required_steps' => $required_steps,
			'assessable_steps' => $assessable_steps,
			'total_steps' => $total_steps,
			'progress' => $step_progress,
			'passed' => $passed,
			'answered' => $answered,
			'modules_seen' => $seen,
			'average' => $steps_grade,
			'gradable' => $gradable,
			'completed_steps' => $completed,
			'steps_grades' => $steps_grades,
		);

		return $completion;
	}

	/**
	 * Returns the user response.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $step_id
	 * @param bool $progress
	 *
	 * @return array|mixed|null|string
	 */
	function get_response( $course_id, $unit_id, $step_id, $progress = false ) {
		if ( ! $progress )
			$progress = $this->get_completion_data( $course_id );

		$response = coursepress_get_array_val(
			$progress,
			'units/' . $unit_id . '/responses/' . $step_id
		);

		if ( is_array( $response ) ) {
			$response = array_pop( $response );
		}

		return $response;
	}

	/**
	 * Check if user had completed the given course ID.
	 * The completion status return here only provide status according to user interaction and
	 * course requisite. It does not tell if the user have pass nor failed the course.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	function is_course_completed( $course_id ) {
		$progress = $this->get_completion_data( $course_id );

		$course_progress = coursepress_get_array_val( $progress, 'completion/progress' );

		return $course_progress >= 100;
	}

	/**
	 * Returns user's overall acquired grade.
	 *
	 * @param $course_id
	 *
	 * @return mixed|null|string
	 */
	function get_course_grade( $course_id ) {
		$progress = $this->get_completion_data( $course_id );
		return coursepress_get_array_val( $progress, 'completion/average' );
	}

	/**
	 * Returns user's course progress percentage.
	 *
	 * @param $course_id
	 *
	 * @return mixed|null|string
	 */
	function get_course_progress( $course_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/progress' );
	}

	/**
	 * Returns user's course completion status. Statuses are `ongoing`|`passed`|`failed`.
	 * User is automatically mark as failed if the course had already ended.
	 *
	 * @param $course_id
	 *
	 * @return string
	 */
	function get_course_completion_status( $course_id ) {
		$progress = $this->get_completion_data( $course_id );
		$status = 'ongoing';

		if ( $this->is_course_completed( $course_id ) ) {
			$status = 'completed';

			// Check if user pass the course
			$completed = coursepress_get_array_val( $progress, 'completion/completed' );
			$failed = coursepress_get_array_val( $progress, 'completion/failed' );

			if ( $completed )
				$status = 'passed';
			elseif ( $failed )
				$status = 'failed';
		}

		if ( 'ongoing' == $status ) {
			$course = coursepress_get_course( $course_id );

			if ( $course->has_course_ended() ) {
				// Marked the student failed if the course has already ended
				$status = 'failed';
			}
		}

		return $status;
	}

	/**
	 * Returns user's grade of the given unit ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 *
	 * @return mixed|null|string
	 */
	function get_unit_grade( $course_id, $unit_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/average' );
	}

	/**
	 * Returns users' progress of the given unit ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 *
	 * @return mixed|null|string
	 */
	function get_unit_progress( $course_id, $unit_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/progress' );
	}

	/**
	 * Check if user have already seen the unit.
	 *
	 * @param $course_id
	 * @param $unit_id
	 *
	 * @return bool
	 */
	function is_unit_seen( $course_id, $unit_id ) {
		$progress = $this->get_completion_data( $course_id );
		$seen = coursepress_get_array_val( $progress, 'units/' . $unit_id );

		return ! empty( $seen );
	}

	/**
	 * Check if user has completed the unit.
	 *
	 * @param $course_id
	 * @param $unit_id
	 *
	 * @return bool
	 */
	function is_unit_completed( $course_id, $unit_id ) {
		$progress = $this->get_unit_progress( $course_id, $unit_id );

		return (int) $progress >= 100;
	}

	/**
	 * Check if user have pass the unit.
	 *
	 * @param $course_id
	 * @param $unit_id
	 *
	 * @return bool|mixed|null|string
	 */
	function has_pass_course_unit( $course_id, $unit_id ) {
		$is_completed = $this->is_unit_completed( $course_id, $unit_id );

		if ( ! $is_completed )
			return false;

		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/completed' );
	}

	/**
	 * Returns progress percentage of the given module ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $module_id
	 *
	 * @return mixed|null|string
	 */
	function get_module_progress( $course_id, $unit_id, $module_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/modules/' . $module_id . '/progress';

		return coursepress_get_array_val( $progress, $path );
	}

	/**
	 * Check if user has seen the given module ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $module_id
	 *
	 * @return bool
	 */
	function is_module_seen( $course_id, $unit_id, $module_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/course_module_seen/' . $module_id;

		$seen = coursepress_get_array_val( $progress, $path );

		return ! empty( $seen );
	}

	/**
	 * Check if user have completed the given module ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $module_id
	 *
	 * @return bool
	 */
	function is_module_completed( $course_id, $unit_id, $module_id ) {
		$module_progress = $this->get_module_progress( $course_id, $unit_id, $module_id );

		return (int) $module_progress >= 100;
	}

	/**
	 * Returns user's grade of the given step ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $step_id
	 *
	 * @returns int|null
	 */
	function get_step_grade( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/steps_grade/' . $step_id;

		return coursepress_get_array_val( $progress, $path );
	}

	function get_step_progress( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/steps/' . $step_id . '/progress' );
	}

	function is_step_seen( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/modules_seen/' . $step_id;

		$seen = coursepress_get_array_val( $progress, $path );

		return ! empty( $seen );
	}

	function is_step_completed( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/completed_steps/' . $step_id;

		$completed = coursepress_get_array_val( $progress, $path );

		return ! empty( $completed );
	}
}
