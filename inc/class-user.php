<?php
/**
 * Class CoursePress_User
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_User extends CoursePress_Utility {
	/**
	 * @var string
	 */
	protected $user_type = 'guest'; // Default to guest user

	/**
	 * @var array of user CP capabilities
	 */
	protected $user_caps = array();

	protected $progress_table;
	protected $student_table;

	/**
	 * CoursePress_User constructor.
	 *
	 * @param bool|int|WP_User $user
	 */
	public function __construct( $user = false ) {
		global $wpdb;

		if ( ! $user instanceof WP_User ) {
			$user = get_userdata( (int) $user );
		}

		if ( empty( $user ) || ! $user instanceof  WP_User ) {
			$this->is_error = true;

			return;
		}

		$this->progress_table = $wpdb->prefix . 'coursepress_student_progress';
		$this->student_table = $wpdb->prefix . 'coursepress_students';

		// Inherit WP_User object
		foreach ( $user as $key => $value ) {
			if ( 'data' == $key )
				foreach ( $value as $k => $v )
					$this->__set( $k, $v );
			else
				$this->__set( $key, $value );
		}
	}

	/**
	 * Helper function to return WP_Error.
	 * @return WP_Error
	 */
	function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Invalid user ID!', 'cp' ) );
	}

	/**
	 * Check if user is an administrator.
	 *
	 * @return bool
	 */
	function is_super_admin() {
		return isset( $this->roles ) && in_array( 'administrator', $this->roles );
	}

	/**
	 * Check if user is an instructor of any course.
	 *
	 * @return bool
	 */
	function is_instructor() {
		return isset( $this->roles ) && in_array( 'coursepress_instructor', $this->roles );
	}

	/**
	 * Check if user is a facilitator of any course.
	 *
	 * @return bool
	 */
	function is_facilitator() {
		return isset( $this->roles ) && in_array( 'coursepress_facilitator', $this->roles );
	}

	/**
	 * Check if user is an student of any course.
	 *
	 * @return bool
	 */
	function is_student() {
		return isset( $this->roles) && in_array( 'coursepress_student', $this->roles );
	}

	/**
	 * Check if user is an instructor of the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	function is_instructor_at( $course_id ) {
		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$instructor = get_user_meta( $id, 'instructor_' . $course_id, true );

		return $instructor == $id;
	}

	/**
	 * Check if user is a facilitator of the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	function is_facilitator_at( $course_id ) {
		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$facilitator = get_user_meta( $id, 'facilitator_' . $course_id, true );

		return $facilitator == $id;
	}

	/**
	 * Check if user has administrator, instructor or facilitator access of the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	function has_access_at( $course_id ) {
		if ( $this->is_super_admin()
			|| ( $this->is_instructor() && $this->is_instructor_at( $course_id ) )
			|| ( $this->is_facilitator() && $this->is_facilitator_at( $course_id ) )
		) {
			return true;
		}

		return false;
	}

	function get_name() {
		$id = $this->__get( 'ID' );

		$names = array(
			get_user_meta( $id, 'first_name', true ),
			get_user_meta( $id, 'last_name', true ),
		);

		$names = array_filter( $names );
		$display_name = $this->__get( 'display_name' );

		if ( empty( $names ) )
			return $display_name;
		else
			return implode( ' ', $names );
	}

	function get_avatar( $size = 42 ) {
		$avatar = get_avatar( $size, $this->__get( 'user_email' ) );

		return $avatar;
	}

	function get_description() {
		$id = $this->__get( 'ID' );
		$description = get_user_meta( $id, 'description', true );

		return $description;
	}

	/**
	 * Get the list of courses where user is either an instructor or facilitator.
	 *
	 * @param bool $publish
	 * @param bool $returnAll
	 *
	 * @return array
	 */
	function get_accessible_courses( $publish = true, $returnAll = true ) {
		$courses = array();

		$args = array( 'post_status' => $publish ? 'publish' : 'any' );

		if ( $returnAll )
			$args['posts_per_page'] = -1;

		if ( $this->is_super_admin() )
			$courses = coursepress_get_courses( $args );
		elseif ( $this->is_instructor() || $this->is_facilitator() ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'meta_key' => 'instructor',
					'meta_value' => $this->ID,
				),
				array(
					'meta_key' => 'facilitator',
					'meta_value' => $this->ID,
				),
			);
			$courses = coursepress_get_courses( $args );
		}

		return $courses;
	}

	/************************************************
	 * USER AS STUDENT
	 ***********************************************/

	private function get_student_id( $course_id ) {
		global $wpdb;

		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$sql = $wpdb->prepare( "SELECT ID FROM `$this->student_table` WHERE `ID`=%d AND `course_id`=%d", $id, $course_id );
		$student_id = $wpdb->get_var( $sql );

		return $student_id;
	}

	private function get_progress_id( $student_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT ID FROM `$this->progress_table` WHERE `student_id`=%d", $student_id );
		$progress_id = $wpdb->get_var( $sql );

		return (int) $progress_id > 0;
	}

	function get_enrolled_courses_ids() {
		global $wpdb;

		$id = $this->__get( 'ID' );

		if ( ! $id )
			return null;

		$sql = $wpdb->prepare( "SELECT ID FROM `$this->>student_table` WHERE `student_id`=%d", $id );
		$results = $wpdb->get_results( $sql, OBJECT );
		$course_ids = array();


		if ( $results )
			foreach ( $results as $result )
				$course_ids[] = $result->ID;

		return $course_ids;
	}

	/**
	 * Check if user is enrolled to the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	function is_enrolled_at( $course_id ) {
		global $wpdb;

		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$student_id = $this->get_student_id( $course_id );

		return (int) $student_id > 0;
	}

	function add_course_student( $course_id ) {
		global $wpdb;

		if (  $this->is_enrolled_at( $course_id ) )
			return true;

		$id = $this->__get( 'ID' );

		$array = array(
			'course_id' => $course_id,
			'student_id' => $id,
		);

		$wpdb->insert( $this->student_table, $array );

		return true;
	}

	function remove_course_student( $course_id ) {
		global $wpdb;

		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$student_id = $this->get_student_id( $course_id );

		if ( (int) $student_id > 0 ) {
			// Delete as student
			$wpdb->delete( $this->student_table, array( 'ID' => $student_id ), array( '%d' ) );
			// Delete student progress
			$progress_id = $this->get_progress_id( $student_id );

			if ( $progress_id > 0 )
				$wpdb->delete( $this->progress_table, array( 'ID' => $progress_id ), array( '%d' ) );
		}
	}

	function add_student_progress( $course_id = 0, $progress = array() ) {
		global $wpdb;

		if ( empty( $course_id ) || empty( $progress ) )
			return false;

		$student_id = $this->get_student_id( $course_id );

		if ( (int) $student_id > 0 ) {
			$progress = maybe_serialize( $progress );

			$param = array(
				'course_id' => $course_id,
				'student_id' => $student_id,
				'progress' => $progress,
			);

			$progress_id = $this->get_progress_id( $student_id );

			if ( ! $progress_id ) {
				$wpdb->insert( $this->progress_table, $param, array( '%d', '%d', '%s' ) );
			} else {
				$wpdb->update( $this->progress_table, $param, array( 'ID' => $student_id ) );
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns an array courses where user is enrolled at.
	 *
	 * @param bool $published
	 * @param bool $returnAll
	 *
	 * @return array An array of CoursePress_Course object.
	 */
	function get_user_enrolled_at( $published = true, $returnAll = true ) {
		$user_id = $this->__get( 'ID' );

		$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );
		$args = array(
			'meta_key' => 'student',
			'meta_value' => $user_id,
			'post_status' => $published ? 'publish' : 'any',
			'posts_per_page' => $returnAll ? -1 : $posts_per_page,
			'suppress_filters' => true,
		);

		return coursepress_get_courses( $args );
	}

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
				$status = 'pass';
			elseif ( $failed )
				$status = 'failed';
		}

		if ( 'ongoing' == $status ) {
			$course = coursepress_get_course( $course_id );

			if ( $course->has_course_ended() ) {
				$status = 'incomplete';
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

	/*******************************************
	 * USER AS INSTRUCTOR
	 ******************************************/

	/**
	 * Returns courses where user is an instructor at.
	 *
	 * @param bool $published
	 * @param bool $returnAll
	 *
	 * @return array Returns an array of courses where each course is an instance of CoursePress_Course
	 */
	function get_instructed_courses( $published = true, $returnAll = true ) {
		$args = array(
			'post_status' => $published ? 'publish' : 'any',
			'meta_key' => 'instructor',
			'meta_value' => $this->__get( 'ID' ),
		);

		if ( $returnAll )
			$args['posts_per_page'] = -1;

		$courses = coursepress_get_courses( $args );

		return $courses;
	}

	function get_instructor_profile_link() {
		if ( false == $this->is_instructor() )
			return null;

		$slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );

		return site_url( '/' ) . trailingslashit( $slug ) . $this->__get( 'user_login' );
	}

	/******************************************
	 * USER AS FACILITATOR
	 *****************************************/

	function get_facilitated_courses( $published = true, $returnAll = true ) {
		$args = array(
			'post_status' => $published ? 'publish' : 'any',
			'meta_key' => 'facilitator',
			'meta_value' => $this->__get( 'ID' ),
		);

		if ( $returnAll )
			$args['posts_per_page'] = -1;

		$courses = coursepress_get_courses( $args );

		return $courses;
	}
}
