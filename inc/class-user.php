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
	private $is_error = false;

	/**
	 * @return bool
	 */
	public function is_error() {
		return $this->is_error;
	}

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
			if ( 'data' == $key ) {
				foreach ( $value as $k => $v ) {
					$this->__set( $k, $v );
				}
			} else {
				$this->__set( $key, $value );
			}
		}
		$this->__set( 'first_name', get_user_meta( $user->ID, 'first_name', true ) );
		$this->__set( 'last_name', get_user_meta( $user->ID, 'last_name', true ) );
		$this->__set( 'description', get_user_meta( $user->ID, 'description', true ) );
		/**
		 * latest activity
		 */
		$this->__set( 'latest_activity', get_user_meta( $user->ID, 'latest_activity', true ) );
		$this->__set( 'latest_activity_kind', get_user_meta( $user->ID, 'latest_activity_kind', true ) );
		$this->__set( 'latest_activity_id', get_user_meta( $user->ID, 'latest_activity_id', true ) );
		/**
		 * clear student data after delete student
		 */
		add_action( 'deleted_user', array( $this, 'clear_student_data' ) );
		add_action( 'remove_user_from_blog', array( $this, 'clear_student_data' ) );
	}

	/**
	 * Helper function to return WP_Error.
	 * @return WP_Error
	 */
	public function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Invalid user ID!', 'cp' ) );
	}

	/**
	 * Check if user is an administrator.
	 *
	 * @return bool
	 */
	public function is_super_admin() {
		/**
		 * super admin with no role!
		 */
		if ( is_multisite() && is_super_admin( $this->ID ) ) {
			return true;
		}
		return isset( $this->roles ) && in_array( 'administrator', $this->roles );
	}

	/**
	 * Check if user is an instructor of any course.
	 *
	 * @return bool
	 */
	public function is_instructor() {
		return isset( $this->roles ) && in_array( 'coursepress_instructor', $this->roles );
	}

	/**
	 * Check if user is a facilitator of any course.
	 *
	 * @return bool
	 */
	public function is_facilitator() {
		return isset( $this->roles ) && in_array( 'coursepress_facilitator', $this->roles );
	}

	/**
	 * Check if user is an student of any course.
	 *
	 * @return bool
	 */
	public function is_student() {
		return isset( $this->roles ) && in_array( 'coursepress_student', $this->roles );
	}

	/**
	 * Check if user is an instructor of the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	public function is_instructor_at( $course_id ) {
		$id = $this->__get( 'ID' );
		if ( ! $id ) {
			return false;
		}
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
	public function is_facilitator_at( $course_id ) {
		$id = $this->__get( 'ID' );
		if ( ! $id ) {
			return false;
		}
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
	public function has_access_at( $course_id ) {
		if ( $this->is_super_admin()
			|| ( $this->is_instructor() && $this->is_instructor_at( $course_id ) )
			|| ( $this->is_facilitator() && $this->is_facilitator_at( $course_id ) )
		) {
			return true;
		}
		return false;
	}

	public function get_name() {
		$id = $this->__get( 'ID' );
		$names = array(
			get_user_meta( $id, 'first_name', true ),
			get_user_meta( $id, 'last_name', true ),
		);
		$names = array_filter( $names );
		$display_name = $this->__get( 'display_name' );
		if ( empty( $names ) ) {
			return $display_name;
		}
		return implode( ' ', $names );
	}

	public function get_avatar( $size = 42, $default = null ) {
		$avatar = get_avatar( $this->__get( 'user_email' ), $size, $default );
		return $avatar;
	}

	public function get_description() {
		$id = $this->__get( 'ID' );
		$description = get_user_meta( $id, 'description', true );
		return $description;
	}

	/**
	 * Get the list of courses where user is either an instructor or facilitator.
	 *
	 * @param bool $publish   Only published courses?
	 * @param bool $returnAll Should return all items?
	 * @param int  $count     Count of total courses (pass by ref.).
	 *
	 * @return array
	 */
	public function get_accessible_courses( $publish = true, $returnAll = true, &$count = 0 ) {
		$courses = array();
		$args = array();
		if ( is_bool( $publish ) ) {
		    $args['post_status'] = $publish ? true : false;
		} else {
		    $args['post_status'] = $publish;
		}
		if ( $returnAll ) {
			$args['posts_per_page'] = -1;
		}
		if ( $this->is_super_admin()|| CoursePress_Data_Capabilities::can_view_others_course() ) {
			$courses = coursepress_get_courses( $args, $count );
		} elseif ( $this->is_instructor() || $this->is_facilitator() ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => 'instructor',
					'value' => $this->ID,
				),
				array(
					'key' => 'facilitator',
					'value' => $this->ID,
				),
			);
			$courses = coursepress_get_courses( $args, $count );
		}

		return $courses;
	}

	/************************************************
	 * USER AS STUDENT
	 ***********************************************/
	private function get_student_id( $course_id ) {
		global $wpdb;
		$id = $this->__get( 'ID' );
		if ( ! $id ) {
			return false;
		}
		// Get from cache if exist.
		$student_id = wp_cache_get( 'student_id', 'cp_user_' . $id );
		if ( false === $student_id ) {
			$sql = $wpdb->prepare( "SELECT ID FROM `$this->student_table` WHERE `student_id`=%d AND `course_id`=%d", $id, $course_id );
			$student_id = $wpdb->get_var( $sql );
			wp_cache_set( 'student_id', $student_id, 'cp_user_' . $id );
		}
		return $student_id;
	}

	private function get_progress_id( $student_id ) {
		// Get from cache if exist.
		$progress_id = wp_cache_get( 'progress_id', 'cp_student_' . $student_id );
		if ( false === $progress_id ) {
			global $wpdb;
			$sql = $wpdb->prepare( "SELECT ID FROM `$this->progress_table` WHERE `student_id`=%d", $student_id );
			$progress_id = $wpdb->get_var( $sql );
			wp_cache_set( 'progress_id', $progress_id, 'cp_student_' . $student_id );
		}
		return (int) $progress_id;
	}

	public function get_enrolled_courses_ids( $per_page = 0, $paged = 1 ) {
		$id = $this->__get( 'ID' );
		$offset = $per_page * ($paged - 1);
		$limit = $per_page * $paged;
		// Get from cache if exists.
		$course_ids = wp_cache_get( 'enrolled_courses_ids', 'cp_user_' . $id );
		if ( false === $course_ids ) {
			global $wpdb;
			if ( ! $id ) {
				return array();
			}
			$sql = "SELECT `course_id` FROM `$this->student_table` WHERE `student_id`=%d";
			if ( $per_page > 0 ) {
				$sql .= ' LIMIT %d, %d';
				$sql = $wpdb->prepare( $sql, $id, $offset, $limit );
			} else {
				$sql = $wpdb->prepare( $sql, $id );
			}
			$results = $wpdb->get_results( $sql, OBJECT );
			$course_ids = array();
			if ( $results ) {
				foreach ( $results as $result ) {
					$course_ids[] = $result->course_id;
				}
			}
			// Store in cache only if not paginated, so we can use it later.
			if ( 0 === $per_page ) {
				wp_cache_set( 'enrolled_courses_ids', $course_ids, 'cp_user_' . $id );
			}
		} elseif ( $per_page > 0 && is_array( $course_ids ) ) {
			// For paginated queries.
			$course_ids = array_slice( $course_ids, $offset, $per_page );
		}
		return $course_ids;
	}

	/**
	 * Check if user is enrolled to the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	public function is_enrolled_at( $course_id ) {
		global $wpdb;
		$id = $this->__get( 'ID' );
		if ( ! $id ) {
			return false;
		}
		$student_id = $this->get_student_id( $course_id );
		return (int) $student_id > 0;
	}

	/**
	 * Add student to course
	 */
	public function add_course_student( $course, $check_passcode = true ) {
		global $wpdb;
		$course_id = $course->ID;
		if (  $this->is_enrolled_at( $course_id ) ) {
			return true;
		}
		$id = $this->__get( 'ID' );
		if ( empty( $id ) ) {
			return;
		}
		/**
		 * Check passcode
		 */
		if ( $check_passcode ) {

			$passcode = filter_input( INPUT_POST, 'course_passcode' );
			$course_passcode = coursepress_course_get_setting( $course_id, 'enrollment_passcode', '' );
			if ( $course_passcode != trim( $passcode ) && ! CoursePress_Data_Capabilities::can_add_course_student( $course_id )  ) {
				coursepress_set_cookie( 'cp_incorrect_passcode', true, time() + HOUR_IN_SECONDS );
				$redirect = $course->get_permalink();
				wp_safe_redirect( $redirect );
				exit;
			}
		}
		$array = array(
			'course_id' => $course_id,
			'student_id' => $id,
		);
		$wpdb->insert( $this->student_table, $array );
		// Delete cache after enroll.
		wp_cache_delete( 'student_ids', 'cp_course_' . $course_id );
		wp_cache_delete( 'enrolled_courses_ids', 'cp_user_' . $id );
		return $wpdb->insert_id;
	}

	public function remove_course_student( $course_id ) {
		global $wpdb;
		$id = $this->__get( 'ID' );
		if ( ! $id ) {
			return false;
		}
		$student_id = $this->get_student_id( $course_id );
		if ( (int) $student_id > 0 ) {
			// Delete as student
			$wpdb->delete( $this->student_table, array( 'ID' => $student_id ), array( '%d' ) );
			// Delete student progress
			$progress_id = $this->get_progress_id( $student_id );
			if ( $progress_id > 0 ) {
				$wpdb->delete( $this->progress_table, array( 'ID' => $progress_id ), array( '%d' ) );
			}
			/**
			 * certificate
			 */
			$certificate = new CoursePress_Certificate();
			$certificate->delete_certificate( $id, $course_id );
			// Delete cache after enroll.
			wp_cache_delete( 'student_ids', 'cp_course_' . $course_id );
			wp_cache_delete( 'enrolled_courses_ids', 'cp_user_' . $id );
			return true;
		}
		return false;
	}

	public function add_student_progress( $course_id = 0, $progress = array() ) {
		global $wpdb;
		$id = $this->__get( 'ID' );
		if ( empty( $course_id ) || empty( $progress ) || ! $id ) {
			return false;
		}
		$student_id = $this->get_student_id( $course_id );
		if ( (int) $student_id > 0 ) {
			$progress = maybe_serialize( $progress );
			$param = array(
				'course_id' => $course_id,
				'student_id' => $student_id,
				'progress' => $progress,
			);
			$progress_id = $this->get_progress_id( $student_id );
			if ( (int) 0 === $progress_id ) {
				$wpdb->insert( $this->progress_table, $param );
			} else {
				$wpdb->update( $this->progress_table, $param, array( 'ID' => $progress_id ) );
			}
			// Delete cache after progress update.
			wp_cache_delete( 'course_progress_data_' . $id, 'cp_course_' . $course_id );
			return true;
		}
		return false;
	}

	public function get_course_progress_data( $course_id ) {
		global $wpdb;
		$id = $this->__get( 'ID' );
		if ( ! $id ) {
			return null;
		}
		if ( ! $this->is_enrolled_at( $course_id ) ) {
			return null;
		}
		$student_id = $this->get_student_id( $course_id );
		$progress_id = $this->get_progress_id( $student_id );
		// Get from cache if exist.
		$progress = wp_cache_get( 'course_progress_data_' . $id, 'cp_course_' . $course_id );
		if ( (int) $progress_id > 0 && false === $progress ) {
			$sql = $wpdb->prepare( "SELECT `progress` FROM `{$this->progress_table}` WHERE `ID`=%d", $progress_id );
			$progress = $wpdb->get_var( $sql );
			if ( ! empty( $progress ) ) {
				$progress = maybe_unserialize( $progress );
			}
			// Set in cache.
			wp_cache_set( 'course_progress_data_' . $id, $progress, 'cp_course_' . $course_id );
		}
		return $progress;
	}

	/**
	 * Returns an array courses where user is enrolled at.
	 *
	 * @param bool $published
	 * @param bool $returnAll
	 *
	 * @return array An array of CoursePress_Course object.
	 */
	public function get_user_enrolled_at( $published = true, $returnAll = true ) {
		$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );
		$course_ids = $this->get_enrolled_courses_ids();
		if ( empty( $course_ids ) ) {
			return $course_ids;
		}
		$args = array(
			'post_status' => $published ? 'publish' : 'any',
			'posts_per_page' => $returnAll ? -1 : $posts_per_page,
			'suppress_filters' => true,
			'post__in' => $course_ids,
		);
		return coursepress_get_courses( $args );
	}

	public function get_completion_data( $course_id ) {
		global $CoursePress;
		$id = $this->__get( 'ID' );
		$defaults = array( 'version' => $CoursePress->version );
		$progress = $this->get_course_progress_data( $course_id );
		if ( empty( $progress ) ) {
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

	public function add_visited_module( $course_id, $unit_id, $module_id ) {
		$progress = $this->get_completion_data( $course_id );
		$visited_pages = coursepress_get_array_val( $progress, 'units/' . $unit_id . '/visited_pages' );
		if ( ! $visited_pages ) {
			$visited_pages = array();
		}
		$visited_pages[ $module_id ] = $module_id;
		$progress = coursepress_set_array_val( $progress, 'units/' . $unit_id . '/visited_pages', $visited_pages );
		$this->add_student_progress( $course_id, $progress );
		return $progress;
	}

	public function add_visited_step( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		$modules_seen = coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/modules_seen' );
		if ( ! $modules_seen ) {
			$modules_seen = array();
		}
		$modules_seen[ $step_id ] = $step_id;
		$progress = coursepress_set_array_val( $progress, 'completion/' . $unit_id . '/modules_seen', $modules_seen );
		$this->add_student_progress( $course_id, $progress );
	}

	public function validate_completion_data( $course_id, $progress = array() ) {
		if ( ! $this->is_enrolled_at( $course_id ) ) {
			return false;
		}
		if ( ( $completion = $this->__get( 'completion_data' ) ) ) {
			return $completion;
		}
		if ( empty( $progress ) ) {
			$progress = $this->get_completion_data( $course_id );
		}
		$course = coursepress_get_course( $course_id );
		$completion = coursepress_get_array_val( $progress, 'completion' );
		if ( empty( $completion ) ) {
			$completion = array();
		}
		$units = $course->get_units( true ); // Only validate published units
		$with_modules = $course->is_with_modules();
		$course_progress = 0;
		$course_progress_counter = 0;
		foreach ( $units as $unit ) {
			$unit_id = $unit->__get( 'ID' );
			$progress = $this->validate_unit( $unit, $with_modules, $progress );
			$unit_progress = coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/progress' );
			$course_progress += (int) $unit_progress;
			$course_progress_counter++;
		}
		/**
		 * Count avarage, becouse sum is not a good value!
		 */
		if ( 0 < $course_progress_counter ) {
			$course_progress = intval( $course_progress / $course_progress_counter );
		}
		$progress = coursepress_set_array_val( $progress, 'completion/progress', $course_progress );
		/**
		 * is course progress larger or equal minimum course completion?
		 */
		$is_done = $course_progress >= $course->minimum_grade_required;
		$progress = coursepress_set_array_val( $progress, 'completion/completed', $is_done );
		if ( $is_done ) {
			$certificate = new CoursePress_Certificate();
			$certificate->generate_certificate( $this->ID, $course_id );
		}
		/**
		* set
		*/
		return $progress;
	}

	public function get_unit_progress_ratio( $unit, $with_modules ) {
		$count = 0;
		if ( $with_modules ) {
			$modules = $unit->get_modules_with_steps();
			$count += count( $modules );
			if ( $modules ) {
				foreach ( $modules as $module ) {
					if ( $module['steps'] ) {
						$steps = $module['steps'];
						$count += count( $steps );
					}
				}
			}
		} else {
			$steps = $unit->get_steps();
			$count = count( $steps );
		}
		if ( $count > 0 ) {
			return 100 / $count;
		}
		return 100;
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
		$unit_id = $unit->__get( 'ID' );
		$course_id = $unit->__get( 'course_id' );
		$unit_progress = 0;
		$unit_grade = 0;
		$unit_gradable = 0;
		$unit_pass_grade = 0;
		$force_completion = $unit->__get( 'force_current_unit_completion' );
		$force_pass_completion = $unit->__get( 'force_current_unit_successful_completion' );
		$completion = coursepress_get_array_val( $progress, 'completion/' . $unit_id );
		$unit_ratio = $this->get_unit_progress_ratio( $unit, $with_modules );
		$unit_completion = coursepress_get_array_val( $progress, 'completion/' . $unit_id );
		if ( ! $unit_completion ) {
			$unit_completion = array();
		}
		if ( $with_modules ) {
			$modules = $unit->get_modules_with_steps();
			if ( $modules ) {
				foreach ( $modules as $module_id => $module ) {
					$module_progress = 0;
					$steps_count = 1 + count( $module['steps'] );
					$module_count = $steps_count;
					$module_ratio = 100 / $module_count;
					$module_seen = coursepress_get_array_val(
						$progress,
						'units/' . $unit_id . '/visited_pages/' . $module_id
					);
					if ( $module_seen ) {
						$module_progress += $module_ratio;
						$unit_progress += $unit_ratio;
					}
					if ( $module['steps'] ) {
						$steps_completion = $this->validate_steps( $module['steps'], $course_id, $unit_id, $progress, $force_completion, $force_pass_completion, $progress );
						if ( ! empty( $steps_completion['passed'] ) ) {
							$prev_passed = coursepress_get_array_val( $unit_completion, 'passed' );
							if ( ! $prev_passed ) {
								$prev_passed = array();
							}
							$passed = array_unique( array_merge( $prev_passed, $steps_completion['passed'] ) );
							$unit_completion = coursepress_set_array_val( $unit_completion, 'passed', $passed );
						}
						if ( ! empty( $steps_completion['progress'] ) ) {
							$unit_progress += $steps_completion['progress'];
						}
						if ( ! empty( $steps_completion['module_progress'] ) ) {
							$module_progress = $module_progress + $steps_completion['module_progress'];
						}
						if ( ! empty( $steps_completion['steps'] ) ) {
							foreach ( $steps_completion['steps'] as $step_id => $_step ) {
								$unit_completion = coursepress_set_array_val( $unit_completion, 'steps/' . $step_id, $_step );
							}
						}
					}
					$unit_completion = coursepress_set_array_val( $unit_completion, 'modules/' . $module['id'] . '/progress', $module_progress );
				}
			}
		} else {
			$steps = $unit->get_steps();
			if ( $steps ) {
				$steps_completion = $this->validate_steps( $steps, $course_id, $unit_id, $progress, $force_completion, $force_pass_completion, $progress );
				if ( ! empty( $steps_completion['passed'] ) ) {
					$prev_passed = coursepress_get_array_val( $unit_completion, 'passed' );
					if ( ! $prev_passed ) {
						$prev_passed = array();
					}
					$passed = array_unique( array_merge( $prev_passed, $steps_completion['passed'] ) );
					$unit_completion = coursepress_set_array_val( $unit_completion, 'passed', $passed );
				}
				if ( ! empty( $steps_completion['progress'] ) ) {
					$unit_progress += $steps_completion['progress'];
				}
				if ( ! empty( $steps_completion['steps'] ) ) {
					foreach ( $steps_completion['steps'] as $step_id => $_step ) {
						$unit_completion = coursepress_set_array_val( $unit_completion, 'steps/' . $step_id, $_step );
					}
				}
			}
		}
		$unit_completion = coursepress_set_array_val( $unit_completion, 'progress', $unit_progress );
		$progress = coursepress_set_array_val( $progress, 'completion/' . $unit_id, $unit_completion );
		return $progress;
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
	private function validate_steps( $steps, $course_id, $unit_id, $progress, $force_completion, $force_pass_completio ) {
		$total_steps = count( $steps );
		$required_steps = 0;
		$assessable_steps = 0;
		$step_progress = 0;
		$steps_grade = 0;
		$user_id = $this->__get( 'ID' );
		$passed = array();
		$answered = array();
		$seen = array();
		$completed = array();
		$steps_grades = array();
		$unit = coursepress_get_unit( $unit_id );
		$course = coursepress_get_course( $course_id );
		$unit_progress_ratio = $this->get_unit_progress_ratio( $unit, $course->is_with_modules() );
		$steps_completion = array();
		$module_count = 1 + count( $steps );
		$module_ratio = 100 / $module_count;
		$module_progress = 0;
		foreach ( $steps as $step ) {
			$step_id = $step->__get( 'ID' );
			$is_required = $step->__get( 'mandatory' );
			$is_assessable = $step->__get( 'assessable' );
			$minimum_grade = $step->__get( 'minimum_grade' );
			$step_type = $step->__get( 'module_type' );
			$is_answerable = $step->is_answerable();
			$step_seen = coursepress_get_array_val(
				$progress,
				'completion/' . $unit_id . '/modules_seen/' . $step_id
			);
			$valid = false;
			$count = 1;
			$item_progress = 0;
			if ( $is_required ) {
				$required_steps++;
				$count += 1;
			}
			if ( $is_assessable ) {
				$assessable_steps++;
				$count += 1;
			}
			$step_progress_ratio = $unit_progress_ratio / $count;
			$item_ratio = 100 / $count;
			$m_ratio = $module_ratio / $count;
			if ( $step_seen ) {
				$seen[ $step_id ] = $step_id;
				$step_progress += $step_progress_ratio;
				$item_progress += $item_ratio;
				$module_progress += $m_ratio;
			}
			if ( $is_answerable ) {
				$response = $this->get_response( $course_id, $unit_id, $step_id, $progress );
				if ( ! empty( $response ) ) {
					$grade = (int) coursepress_get_array_val( $response, 'grade' );
					$pass  = $grade >= $minimum_grade;
					$steps_grade += $grade;
					if ( $is_required ) {
						$step_progress += $step_progress_ratio;
						$item_progress += $item_ratio;
						$module_progress += $m_ratio;
					}
					if ( $pass ) {
						$passed[ $step_id ] = $step_id;
						if ( $is_assessable ) {
							$step_progress += $step_progress_ratio;
							$item_progress += $item_ratio;
							$module_progress += $m_ratio;
						}
					} else {
						if ( ! empty( $response['assessable'] ) ) {
							$step_progress += $step_progress_ratio;
							$item_progress += $item_ratio;
							$module_progress += $m_ratio;
						}
					}
				}
			} else {
				if ( 'discussion' == $step_type ) {
					$has_comments = coursepress_user_have_comments( $user_id, $step_id );
					if ( $is_required ) {
						if ( $has_comments ) {
							$step_progress += $step_progress_ratio;
							$item_progress += $item_ratio;
							$module_progress += $m_ratio;
						}
					} else {
						$step_progress += $step_progress_ratio;
						$item_progress += $item_ratio;
						$module_progress += $m_ratio;
					}
				} elseif ( 'video' == $step_type || 'audio' == $step_type ) {
					if ( ! $is_required ) {
						if ( $step_seen ) {
							$step_progress += $step_progress_ratio;
							$item_progress += $item_ratio;
							$module_progress += $m_ratio;
						}
					} elseif ( $step_seen ) {
					}
				} elseif ( ! $is_required ) {
					if ( $step_seen ) {
						$valid = true;
					} elseif ( ! $force_completion ) {
						$valid = true;
					}
				}
			}
			$item_progress = $this->normalize( $item_progress );
			$steps_completion = coursepress_set_array_val( $steps_completion, $step_id . '/progress', $item_progress );
		}
		$completion = array(
			'required_steps' => $required_steps,
			'assessable_steps' => $assessable_steps,
			'total_steps' => $total_steps,
			'progress' => $this->normalize( $step_progress ),
			'passed' => $passed,
			'answered' => $answered,
			'average' => $steps_grade,
			'completed_steps' => $completed,
			'steps_grades' => $steps_grades,
			'steps' => $steps_completion,
			'module_progress' => $this->normalize( $module_progress ),
		);
		return $completion;
	}

	/**
	 * Normalize
	 */
	private function normalize( $value ) {
		return max( 0, min( 100, $value ) );
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
	public function get_response( $course_id, $unit_id, $step_id, $progress = false ) {
		if ( ! $progress ) {
			$progress = $this->get_completion_data( $course_id );
		}
		$response = coursepress_get_array_val(
			$progress,
			'units/' . $unit_id . '/responses/' . $step_id
		);
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
	public function is_course_completed( $course_id ) {
		$course_progress = $this->get_course_progress( $course_id );
		return $course_progress >= 100;
	}

	/**
	 * Format date & time to WP settings
	 *
	 * @since 3.0.0
	 *
	 */
	private function date_format( $date ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		return date_i18n( $date_format . ' ' . $time_format, strtotime( $date ) );
	}

	public function get_date_enrolled( $course_id ) {
		$date_enrolled = get_user_meta( $this->__get( 'ID' ), 'enrolled_course_date_' . $course_id );
		if ( is_array( $date_enrolled ) ) {
			$date_enrolled = array_pop( $date_enrolled );
		}
		if ( empty( $date_enrolled ) ) {
			return esc_html__( 'Unknown enrolled date.', 'cp' );
		}
		return $this->date_format( $date_enrolled );
	}

	/**
	 * Returns user's overall acquired grade.
	 *
	 * @param $course_id
	 *
	 * @return mixed|null|string
	 */
	public function get_course_grade( $course_id ) {
		$progress = $this->get_completion_data( $course_id );
		return coursepress_get_array_val( $progress, 'completion/progress' );
	}
	/**
	 * Returns user's course progress percentage.
	 *
	 * @param $course_id
	 *
	 * @return mixed|null|string
	 */
	public function get_course_progress( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course = coursepress_get_course();
			$course_id = $course->__get( 'ID' );
		}
		if ( ! $course_id ) {
			return false;
		}
		$progress = $this->get_completion_data( $course_id );
		return (int) coursepress_get_array_val( $progress, 'completion/progress' );
	}

	/**
	 * Returns user's course completion status. Statuses are `ongoing`|`passed`|`failed`.
	 * User is automatically mark as failed if the course had already ended.
	 *
	 * @param $course_id
	 *
	 * @return string
	 */
	public function get_course_completion_status( $course_id, $progress = array() ) {
		if ( empty( $progress ) ) {
			$progress = $this->get_completion_data( $course_id );
		}
		$status = 'ongoing';
		if ( $this->is_course_completed( $course_id ) ) {
			$status = 'completed';
			// Check if user pass the course
			$completed = coursepress_get_array_val( $progress, 'completion/completed' );
			$failed = coursepress_get_array_val( $progress, 'completion/failed' );
			if ( $completed ) {
				$status = 'pass';
			} elseif ( $failed ) {
				$status = 'failed';
			}
		}
		if ( 'ongoing' == $status ) {
			$course = coursepress_get_course( $course_id );
			if ( $course->has_course_ended() ) {
				$status = 'incomplete';
			}
		}
		return $status;
	}

	public function get_course_completion_url( $course_id ) {
		$progress = $this->get_course_progress( $course_id );
		$completion = coursepress_get_array_val( $progress, 'completion' );
		$course = coursepress_get_course( $course_id );
		if ( ! $completion ) {
			$completion = 'almost-there';
		}
		return $course->get_permalink() . trailingslashit( 'completion/' . $completion );
	}

	/**
	 * Returns user's grade of the given unit ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 *
	 * @return mixed|null|string
	 */
	public function get_unit_grade( $course_id, $unit_id ) {
		$progress = $this->get_completion_data( $course_id );
		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/progress' );
	}

	/**
	 * Returns users' progress of the given unit ID.
	 *
	 * @param $course_id
	 * @param $unit_id
	 * @param $progress
	 *
	 * @return mixed|null|string
	 */
	public function get_unit_progress( $course_id, $unit_id, $progress = false ) {
		if ( false === $progress ) {
			$progress = $this->get_completion_data( $course_id );
		}

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
	public function is_unit_seen( $course_id, $unit_id ) {
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
	public function is_unit_completed( $course_id, $unit_id ) {
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
	public function has_pass_course_unit( $course_id, $unit_id ) {
		$is_completed = $this->is_unit_completed( $course_id, $unit_id );
		if ( ! $is_completed ) {
			return false;
		}
		$progress = $this->get_completion_data( $course_id );
		return 100 <= coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/progress' );
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
	public function get_module_progress( $course_id, $unit_id, $module_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/modules/' . $module_id . '/progress';
		return (int) coursepress_get_array_val( $progress, $path );
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
	public function is_module_seen( $course_id, $unit_id, $module_id ) {
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
	public function is_module_completed( $course_id, $unit_id, $module_id ) {
		$module_progress = $this->get_module_progress( $course_id, $unit_id, $module_id );
		return (int) $module_progress > 99;
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
	public function get_step_grade( $course_id, $unit_id, $step_id ) {
		$response = $this->get_response( $course_id, $unit_id, $step_id );
		$grade = coursepress_get_array_val( $response, 'grade' );
		if ( ! empty( $response['assessable'] ) ) {
			$graded_by = coursepress_get_array_val( $response, 'graded_by' );
			if ( 'auto' === $graded_by ) {
				$grade = 'pending';
			}
		}
		return $grade;
	}

	public function get_step_progress( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		return coursepress_get_array_val( $progress, 'completion/' . $unit_id . '/steps/' . $step_id . '/progress' );
	}

	public function is_step_seen( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/modules_seen/' . $step_id;
		$seen = coursepress_get_array_val( $progress, $path );
		return ! empty( $seen );
	}

	public function is_step_completed( $course_id, $unit_id, $step_id ) {
		$progress = $this->get_completion_data( $course_id );
		$path = 'completion/' . $unit_id . '/steps/' . $step_id . '/progress';
		$step_progress = coursepress_get_array_val( $progress, $path );
		return (int) $step_progress > 99;
	}

	public function get_step_status( $course_id, $unit_id, $step_id ) {
		$is_completed = $this->is_step_completed( $course_id, $unit_id, $step_id );
		if ( $is_completed ) {
			return 'completed';
		} else {
			$stepClass = coursepress_get_course_step( $step_id );
			$progress = $this->get_step_progress( $course_id, $unit_id, $step_id );
			if ( (int) $progress > 0 ) {
				// TODO
			}
		}
	}

	public function get_step_grade_status( $course_id, $unit_id, $step_id ) {
		$status = '';
		$response = $this->get_response( $course_id, $unit_id, $step_id );
		if ( ! empty( $response ) ) {
			$grade     = $this->get_step_grade( $course_id, $unit_id, $step_id );
			$step      = coursepress_get_course_step( $step_id );
			$min_grade = $step->__get( 'minimum_grade' );
			$pass      = $grade != 'pending' && $grade >= $min_grade;
			$status    = '';
			if ( $pass ) {
				$status = 'pass';
			} else {
				if ( $step->is_assessable() ) {
					$status = 'failed';
					$is_assessable = coursepress_get_array_val( $response, 'assessable' );
					if ( $is_assessable ) {
						$graded_by = coursepress_get_array_val( $response, 'graded_by' );
						$status = 'pending';
						if ( 'auto' !== $graded_by ) {
							$status = 'failed';
						}
					}
				}
			}
		}
		return $status;
	}

	public function record_response( $course_id, $unit_id, $step_id, $response, $graded_by = 'auto' ) {
		$date = current_time( 'mysql' );
		$response['date'] = $date;
		$response['graded_by'] = $graded_by;
		$progress = $this->get_completion_data( $course_id );
		$previous_response = $this->get_response( $course_id, $unit_id, $step_id );
		$response['attempts'] = ! isset( $previous_response['attempts'] ) ? 1 : intval( $previous_response['attempts'] ) + 1;
		$progress = coursepress_set_array_val( $progress, 'units/' . $unit_id . '/responses/' . $step_id, $response );
		$progress = $this->validate_completion_data( $course_id, $progress );
		$this->add_student_progress( $course_id, $progress );
		do_action( 'coursepress_record_response', $step_id );
		return $progress;
	}

	/**
	 * Get an instructor feedback.
	 *
	 * @param int $course_id The course ID.
	 * @param int $unit_id The unit ID the module belongs to.
	 * @param int $module_id The module ID the feedback belongs to.
	 * @param int $feedback_index The array key position of the feedback in the feedback list.
	 * @param array $data Optional. An array of previously fetch course completion data.
	 *
	 * @return Returns the feedback given if not empty, otherwise false.
	 **/
	public function get_instructor_feedback( $course_id, $unit_id, $module_id, $feedback_index = false, $progress = false ) {

		if ( false === $progress ) {
			$progress = $this->get_completion_data( $course_id );
		}

		$response = $this->get_response( $course_id, $unit_id, $module_id, $progress );

		$feedback = isset( $response['feedback'] ) ? $response['feedback'] : array();

		// Get last grade
		if ( ! $feedback_index ) {
			$feedback_index = ( count( $feedback ) - 1 );
		}

		return ! empty( $feedback ) && isset( $feedback[ $feedback_index ] ) ? $feedback[ $feedback_index ] : false;
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
	public function get_instructed_courses( $published = true, $returnAll = true ) {
		if ( $this->is_error ) {
			return array();
		}
		$args = array(
			'post_status' => $published ? 'publish' : 'any',
			'meta_key' => 'instructor',
			'meta_value' => $this->__get( 'ID' ),
			'meta_compare' => 'IN',
		);
		if ( $returnAll ) {
			$args['posts_per_page'] = - 1;
		}
		$courses = coursepress_get_courses( $args );
		return $courses;
	}

	public function get_instructor_profile_link() {
		if ( false == $this->is_instructor() ) {
			return null;
		}
		$slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );
		$show_username = coursepress_is_true( coursepress_get_setting( 'instructor_show_username', true ) );
		$hash = md5( $this->__get( 'user_login' ) );
		$instructor_hash = CoursePress_Data_Instructor::get_hash( $this->__get( 'ID' ) );
		if ( empty( $instructor_hash ) ) {
			CoursePress_Data_Instructor::create_hash( $this->__get( 'ID' ) );
		}
		$user_login = $show_username ? $this->__get( 'user_login' ) : $hash;
		return site_url( '/' ) . trailingslashit( $slug ) . $user_login;
	}

	/******************************************
	 * USER AS FACILITATOR
	 *****************************************/

	public function get_facilitated_courses( $published = true, $returnAll = true ) {
		if ( $this->is_error ) {
			return array();
		}
		$args = array(
			'post_status' => $published ? 'publish' : 'any',
			'meta_key' => 'facilitator',
			'meta_value' => $this->__get( 'ID' ),
		);
		if ( $returnAll ) {
			$args['posts_per_page'] = - 1;
		}
		$courses = coursepress_get_courses( $args );
		return $courses;
	}

	/**
	 * Get last activity of the user.
	 *
	 * @return string
	 */
	public function get_last_activity_time() {
		return $this->date_format( $this->latest_activity );
	}

	/**
	 * Get last activity of the user.
	 *
	 * @return string|bool
	 */
	public function get_last_activity_kind() {
		$message = __( 'Unknown.', 'cp' );
		$kind = $this->latest_activity_kind;
		switch ( $kind ) {
			case 'course_module_seen':
			return __( 'Module seen', 'cp' );
			break;

			case 'course_seen':
			return __( 'Course seen', 'cp' );
			break;

			case 'course_unit_seen':
			return __( 'Unit seen', 'cp' );
			break;

			case 'course_step_seen':
			return __( 'Step seen', 'cp' );
			break;

			case 'enrolled':
			return __( 'Enrolled to course', 'cp' );
			break;

			case 'login':
			return __( 'Login', 'cp' );
			break;

			case 'module_answered':
			return __( 'Module answered', 'cp' );
			break;
		}
		return apply_filters( 'coursepress_ get_last_activity_kind', $message, $kind );
	}

	/**
	 * clear student data after delete
	 */
	public function clear_student_data( $student_id ) {
		global $wpdb;
		$args = array( 'student_id' => $student_id );
		$wpdb->delete( $this->progress_table, $args );
		$wpdb->delete( $this->student_table, $args );
	}
}
