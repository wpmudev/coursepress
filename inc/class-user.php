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

	/**
	 * CoursePress_User constructor.
	 *
	 * @param bool|int|WP_User $user
	 */
	public function __construct( $user = false ) {
		if ( ! $user instanceof WP_User ) {
			$user = get_userdata( (int) $user );
		}

		if ( empty( $user ) || ! $user instanceof  WP_User ) {
			$this->is_error = true;

			return;
		}

		// Inherit WP_User object
		foreach ( $user as $key => $value ) {
			if ( 'data' == $key )
				foreach ( $value as $k => $v )
					$this->__set( $k, $v );
			else
				$this->__set( $key, $value );
		}
	}

	function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Invalid user ID!', 'cp' ) );
	}

	function is_super_admin() {
		return isset( $this->roles ) && in_array( 'administrator', $this->roles );
	}

	function is_instructor() {
		return isset( $this->roles ) && in_array( 'coursepress_instructor', $this->roles );
	}

	function is_facilitator() {
		return isset( $this->roles ) && in_array( 'coursepress_facilitator', $this->roles );
	}

	function is_student() {
		return isset( $this->roles) && in_array( 'coursepress_student', $this->roles );
	}

	function is_enrolled_at( $course_id ) {
		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$key = 'enrolled_course_date_' . $course_id;

		$enrolled = coursepress_get_user_option( $id, $key );

		return ! empty( $enrolled );
	}

	function is_instructor_at( $course_id ) {
		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$instructor = get_user_meta( $id, 'instructor_' . $course_id, true );

		return $instructor == $id;
	}

	function is_facilitator_at( $course_id ) {
		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$facilitator = get_user_meta( $id, 'facilitator_' . $course_id, true );

		return $facilitator == $id;
	}

	function has_access_at( $course_id ) {
		if ( $this->is_super_admin()
			|| ( $this->is_instructor() && $this->is_instructor_at( $course_id ) )
			|| ( $this->is_facilitator() && $this->is_facilitator_at( $course_id ) )
		) {
			return true;
		}

		return false;
	}

	function get_completion_data( $course_id ) {
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

	function validate_completion_data( $course_id ) {
		if ( ! $this->is_enrolled_at( $course_id ) )
			return false;

		$user_id = $this->__get( 'ID' );
		$course = coursepress_get_course( $course_id );
		$progress = $this->get_completion_data( $course_id );
		$is_done = coursepress_get_array_val( $progress, 'completion/completed' );
		$completion = array();

		$units = $course->get_units( true ); // Only validated published units
		$with_modules = $course->__get( 'with_modules' );
		$unit_grade = 0;
		$total_gradeable = 0;
		$total_unit = count( $units );

		foreach ( $units as $unit ) {
			$unit_id = $unit->__get( 'ID' );
			$unit_completion = $this->validate_unit( $unit, $with_modules, $progress );
			$completion = coursepress_set_array_val( $completion, 'completion/' . $unit_id, $unit_completion );

			$unit_grade += coursepress_get_array_val( $unit_completion, 'average' );

			$gradeable = coursepress_get_array_val( $unit_completion, 'gradeable' );

			if ( ! empty( $gradeable ) )
				$total_gradeable += $gradeable;

			//error_log( print_r( $unit_completion, true ) );
			/**
			 * @var $unit CoursePress_Unit
			 *
			$unit_id = $unit->__get( 'ID' );
			$unit_available = $unit->is_available();
			$unit_accessible = $unit->is_accessible_by( $user_id );
			$path = 'completion/' . $unit_id;
			$unit_required_steps = 0;
			$unit_assessable_steps = 0;
			$unit_progress = 0;
			$total_modules = 0;

			if ( $course->__get( 'with_modules' ) ) {
				$modules = $unit->get_modules_with_steps( true );
				$completion = coursepress_set_array_val( $completion, $path . '/modules', array() );

				foreach ( $modules as $module_id => $module ) {
					$completion = coursepress_set_array_val( $completion, $path . '/modules/' . $module_id, array() );
					$total_steps = 0;
					$required_steps = 0;
					$assessable_steps = 0;
					$answered_steps = 0;
					$module_progress = 0;
					$total_modules++;
					$module_seen = coursepress_get_array_val( $progress, 'units/' . $unit_id . '/visited_pages/' . $module_id );

					if ( $module['steps'] ) {
						foreach ( $module['steps'] as $step ) {
							$step_id = $step->__get( 'ID' );
							$is_answerable = $step->is_answerable();
							$is_required = $step->__get( 'mandatory' );
							$is_assessable = $step->__get( 'assessable' );
							$min_grade_required = (int) $step->__get( 'minimum_grade' );
							$step_type = $this->__get( 'module_type' );

							$step_seen = coursepress_get_array_val(
								$progress,
								$path . '/modules_seen/' . $step_id
							);

							$total_steps++;

							if ( $is_required ) {
								$required_steps++;
							}

							if ( $is_assessable ) {
								$assessable_steps++;
							}

							if ( $is_answerable ) {
								$response = coursepress_get_array_val( $completion, 'units/' . $unit_id . '/responses/' . $step_id );

								if ( is_array( $response ) )
									$response = array_pop( $response );

								if ( ! empty( $response ) ) {
									$completion = coursepress_set_array_val(
										$completion,
										$path . '/answered/' . $step_id,
										true
									);
									$grades = coursepress_get_array_val(
										$response,
										'grades'
									);

									if ( is_array( $grades ) )
										$grades = array_pop( $grades );

									$grade = coursepress_get_array_val( $grades, 'grade' );
									$graded_by = coursepress_get_array_val( $grades, 'graded_by' );

									$completion = coursepress_set_array_val(
										$completion,
										$path . '/grade/' . $step_id,
										$grade
									);

									$pass = $grade >= $min_grade_required;

									if ( $pass ) {
										$completion = coursepress_set_array_val(
											$completion,
											$path . '/passed/' . $step_id,
											true
										);
										$module_progress += 100;
									} else {
										if ( ! $is_required )
											$module_progress += 100;
									}
								} else {
									if ( ! $is_required && $step_seen )
										$module_progress += 100;
								}
							} else {
								if ( 'discussion' == $step_type ) {
									if ( ! $is_required ) {
										$module_progress += 100;
									} elseif ( coursepress_user_have_comments( $user_id, $step_id ) ) {
										$module_progress += 100;
									}
								} elseif ( 'video' == $step_type || 'audio' == $step_type ) {
									if ( ! $is_required ) {
										$module_progress += 100;
									}
								} else {
									if ( $step_seen ) {
										$module_progress += 100;
									}
								}
							}
						}
					}

					$unit_required_steps += $required_steps;
					$unit_assessable_steps += $assessable_steps;

					if ( $module_progress > 0 ) {
						$module_progress = $module_progress / $total_steps;
					}

					$unit_progress += $module_progress;
					$completion = coursepress_set_array_val(
						$completion,
						$path . '/modules/' . $module_id . '/progress',
						$module_progress
					);
				}
			}

			if ( $unit_progress > 0 && $total_modules > 0 ) {
				$unit_progress = $unit_progress / $total_modules;
			}
			$completion = coursepress_set_array_val(
				$completion,
				$path . '/progress',
				$unit_progress
			);
			//$completion = coursepress_set_array_val( $completion, $path . '/required_steps', $unit_required_steps );
			//$completion = coursepress_set_array_val( $completion, $path . '/assessable_steps', $unit_assessable_steps );
			 *
			 */
		}

		if ( $unit_grade > 0 && $total_gradeable > 0 )
			$unit_grade = $unit_grade / $total_gradeable;

		$completion['average'] = $unit_grade;

		error_log( print_r( $completion, true ) );
		//error_log( print_r( $progress, true ) );
	}

	function validate_unit( $unit, $with_modules, $progress ) {
		$completion = array();
		$unit_id = $unit->__get( 'ID' );
		$course_id = $unit->__get( 'course_id' );
		$unit_progress = 0;
		$unit_grade = 0;
		$unit_gradable = 0;

		if ( $with_modules ) {
			$modules = $unit->get_modules_with_steps();

			if ( $modules ) {
				$module_progress = 0;
				$total_modules = count( $modules );

				foreach ( $modules as $module_id => $module ) {
					$module_completion = array();

					if ( $module['steps'] ) {
						$steps_completion = $this->validate_steps( $module['steps'], $course_id, $unit_id, $progress );
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

						$seen = coursepress_get_array_val( $steps_completion, 'seen' );
						if ( ! empty( $seen ) )
							$completion = coursepress_set_array_val( $completion, 'modules_seen', $seen );

						$average = coursepress_get_array_val( $steps_completion, 'average' );
						if ( ! empty( $average ) )
							$unit_grade += $average;

						$gradable = coursepress_get_array_val( $steps_completion, 'gradable' );

						if ( ! empty( $gradable ) )
							$unit_grade += $gradable;

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

		return $completion;
	}

	function validate_steps( $steps, $course_id, $unit_id, $progress ) {
		$completion = array();
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

			if ( $is_required )
				$required_steps++;
			if ( $is_assessable )
				$assessable_steps++;

			if ( $step_seen )
				$seen[ $step_id ] = $step_id;

			if ( $is_answerable ) {
				$response = $this->get_response( $course_id, $unit_id, $step_id, $progress );

				if ( $is_required )
					$gradable++;

				if ( ! empty( $response ) ) {
					$answered[ $step_id ] = $step_id;
					$grades = coursepress_get_array_val( $response, 'grades' );

					if ( is_array( $grades ) )
						$grades = array_pop( $grades );

					$grade = coursepress_get_array_val( $grades, 'grade' );
					$pass = $grade >= $min_grade;
					$steps_grade += $grade;

					if ( $pass ) {
						$step_progress += 100;
						$passed[ $step_id ] = $step_id;
					} else {
						if ( ! $is_required ) {
							$step_progress += 100;
						}
					}


				} else {
					if ( ! $is_required && $step_seen ) {
						$step_progress += 100;
					}
				}
			} else {
				if ( 'discussion' == $step_type ) {
					if ( ! $is_required )
						$step_progress += 100;
					elseif ( coursepress_user_have_comments( $user_id, $step_id ) )
						$step_progress += 100;
				} elseif ( 'video' == $step_type || 'audio' == $step_type ) {
					if ( ! $is_required )
						$step_progress += 100;
				} elseif ( ! $is_required )
					$step_progress += 100;
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
		);

		return $completion;
	}

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

	function is_course_completed( $course_id ) {
		$progress = $this->get_completion_data( $course_id );

		$course_progress = coursepress_get_array_val( $progress, 'completion/progress' );

		return $course_progress >= 100;
	}

	function get_course_grade( $course_id ) {
		$progress = $this->get_completion_data( $course_id );
		return coursepress_get_array_val( $progress, 'completion/average' );
	}

	function get_course_progress( $course_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/progress' );
	}

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

	function get_unit_grade( $course_id, $unit_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/average' );
	}

	function get_unit_progress( $course_id, $unit_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/progress' );
	}

	function is_unit_completed( $course_id, $unit_id ) {
		$progress = $this->get_unit_progress( $course_id, $unit_id );

		return (int) $progress >= 100;
	}

	function has_pass_course_unit( $course_id, $unit_id ) {
		$is_completed = $this->is_unit_completed( $course_id, $unit_id );

		if ( ! $is_completed )
			return false;

		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/completed' );
	}

	function is_module_completed( $course_id, $unit_id, $module_id ) {
		$progress = $this->get_completion_data( $course_id );

		//return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/' . $module_id );
	}

	function get_step_grade( $course_id, $unit_id, $step_id ) {}

	function get_step_progress( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );

		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/steps/' . $step_id . '/progress' );
	}

	function get_instructor_profile_link() {
	//	if ( false == $this->is_instructor() )
	//		return null;

		$slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );

		return site_url( '/' ) . trailingslashit( $slug ) . $this->__get( 'display_name' );
	}

	function get_name() {
		$names = array(
			get_user_meta( $this->ID, 'first_name', true ),
			get_user_meta( $this->ID, 'last_name', true ),
		);

		$names = array_filter( $names );
		$display_name = $this->__get( 'display_name' );
		$name = '';

		if ( ! empty( $names ) )
			$name .= $this->create_html( 'span', array( 'class' => 'fn name' ), implode( ' ', $names ) );

		$name .= $this->create_html( 'span', array( 'class' => 'fn nickname' ), ' (' . $display_name . ') ' );

		return $name;
	}

	function get_avatar( $size = 42 ) {
		$avatar = get_avatar( $size, $this->__get( 'user_email' ) );

		return $avatar;
	}

	function get_accessable_courses( $publish = true, $ids = false, $all = true ) {
		$courses = array();

		$args = array(
			'post_status' => $publish ? 'publish' : 'any',
			//'author__in' => array( $this->ID ),
		);

		if ( $ids )
			$args['fields'] = 'ids';
		if ( $all )
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
}
