<?php
/**
 * Class CoursePress_Course
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Course extends CoursePress_Utility {
	/**
	 * CoursePress_Course constructor.
	 *
	 * @param int|WP_Post $course
	 */
	public function __construct( $course ) {
		if ( ! $course instanceof WP_Post ) {
			$course = get_post( (int) $course );
		}

		if ( ! $course instanceof WP_Post
		     || $course->post_type != 'course' ) {
			return $this->wp_error();
		}

		foreach ( $course as $key => $value ) {
			$this->__set( $key, $value );
		}

		// Set course meta
		$this->setUpCourseMetas();
	}

	function wp_error() {
		return new WP_Error( 'wrong_param', __( 'Invalid course ID!', 'cp' ) );
	}

	function setUpCourseMetas() {
		$settings = $this->get_settings();
		$date_format = coursepress_get_option( 'date_format' );
		$time_now = current_time( 'timestamp' );
		$date_keys = array( 'course_start_date', 'course_end_date', 'enrollment_start_date', 'enrollment_end_date' );

		foreach ( $settings as $key => $value ) {
			if ( in_array( $key, $date_keys ) && ! empty( $value ) ) {
				$timestamp = strtotime( $value, $time_now );
				$value = date_i18n( $date_format, $timestamp );

				// Add timestamp info
				$this->__set( $key . '_timestamp', $timestamp );
			}

			// Legacy fixes
			if ( 'enrollment_type' == $key && 'anyone' == $value )
				$value = 'registered';
			if ( 'on' == $value || 'yes' == $value )
				$value = true;
			if ( 'off' == $value || '' == $value )
				$value = false;

			$this->__set( $key, $value );
		}

		// Legacy: fix course_type meta
		if ( ! $this->__get( 'with_modules' ) )
			$this->__set( 'with_modules', true );
		if ( ! $this->__get( 'course_type' ) )
			$this->__set( 'course_type', 'auto-moderated' );
	}

	function get_settings() {
		$course_meta = array(
			'course_type' => 'auto-moderated',
			'course_language' => __( 'English', 'cp' ),
			'allow_discussion' => false,
			'allow_workbook' => false,
			'payment_paid_course' => false,
			'listing_image' => '',
			'listing_image_thumbnail_id' => 0,
			'featured_video' => '',
			'enrollment_type' => 'registered',
			'enrollment_passcode' => '',

			'course_view' => 'normal',
			'structure_level' => 'unit',
			'course_open_ended' => true,
			'course_start_date' => 0,
			'course_end_date' => '',
			'enrollment_open_ended' => false,
			'enrollment_start_date' => '',
			'enrollment_end_date' => '',
			'class_limited' => '',
			'class_size' => '',

			'pre_completion_title' => __( 'Almost there!', 'CP_TD' ),
			'pre_completion_content' => '',
			'minimum_grade_required' => 100,
			'course_completion_title' => __( 'Congratulations, You Passed!', 'CP_TD' ),
			'course_completion_content' => '',
			'course_failed_title' => __( 'Sorry, you did not pass this course!', 'CP_TD' ),
			'course_failed_content' => '',
			'basic_certificate_layout' => '',
			'basic_certificate' => false,
			'certificate_background' => '',
			'cert_margin' => array(
				'top' => 0,
				'left' => 0,
				'right' => 0,
			),
			'page_orientation' => 'L',
			'cert_text_color' => '#5a5a5a'
		);

		$id = $this->__get( 'ID' );
		$settings = get_post_meta( $id, 'course_settings', true );
		$settings = wp_parse_args( $settings, $course_meta );

		//error_log(print_r($settings,true));
		return $settings;
	}

	/**
	 * Check if the course has already started.
	 *
	 * @return bool
	 */
	function is_course_started() {
		$time_now = $this->date_time_now();
		$openEnded = $this->__get( 'course_open_ended' );
		$start_date = $this->__get( 'course_start_date_timestamp' );

		if ( empty( $openEnded )
		     && $start_date > 0
		     && $start_date > $time_now )
			return false;

		return true;
	}

	/**
	 * Check if the course is no longer open.
	 *
	 * @return bool
	 */
	function has_course_ended() {
		$time_now = $this->date_time_now();
		$openEnded = $this->__get( 'course_open_ended' );
		$end_date = $this->__get( 'course_end_date_timestamp' );

		if ( empty( $openEnded )
		     && $end_date > 0
		     && $end_date < $time_now ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the course is available
	 *
	 * @return bool
	 */
	function is_available() {
		$is_available = $this->is_course_started();

		if ( $is_available ) {
			// Check if the course hasn't ended yet
			if ( $this->has_course_ended() )
				$is_available = false;
		}

		return $is_available;
	}

	/**
	 * Check if enrollment is open.
	 *
	 * @return bool
	 */
	function is_enrollment_started() {
		$time_now = $this->date_time_now();
		$enrollment_open = $this->__get( 'enrollment_open_ended' );
		$start_date = $this->__get( 'enrollment_start_date_timestamp' );

		if ( empty( $enrollment_open )
		     && $start_date > 0
		     && $start_date > $time_now )
			return false;

		return true;
	}

	/**
	 * Check if enrollment has closed.
	 *
	 * @return bool
	 */
	function has_enrollment_ended() {
		$time_now = $this->date_time_now();
		$enrollment_open = $this->__get( 'enrollment_open_ended' );
		$end_date = $this->__get( 'enrollment_end_date_timestamp' );

		if ( empty( $enrollment_open )
		     && $end_date > 0
		     && $end_date < $time_now )
			return true;

		return false;
	}

	/**
	 * Check if user can enroll to the course.
	 *
	 * @return bool
	 */
	function user_can_enroll() {
		$available = $this->is_available();

		if ( $available ) {
			// Check if enrollment has started
			$available = $this->is_enrollment_started();

			// Check if enrollment already ended
			if ( $available && $this->has_course_ended() )
				$available = false;
		}

		return $available;
	}

	private function _get_instructors() {
		$id = $this->__get( 'ID' );
		$instructor_ids = get_post_meta( $id, 'instructor' );
		$instructor_ids = array_filter( $instructor_ids );

		if ( ! empty( $instructor_ids ) )
			return $instructor_ids;

		// Legacy call
		// @todo: Delete this meta
		$instructor_ids = get_post_meta( $id, 'instructors', true );

		if ( ! empty( $instructor_ids ) )
			foreach ( $instructor_ids as $instructor_id )
				coursepress_add_instructor( $instructor_id, $id );

		return $instructor_ids;
	}

	/**
	 * Count total number of course instructors.
	 *
	 * @return int
	 */
	function count_instructors() {
		return count( $this->_get_instructors() );
	}

	/**
	 * Get course instructors.
	 *
	 * @return array An array of WP_User object on success.
	 */
	function get_instructors() {
		$instructors = array();
		$instructor_ids = $this->_get_instructors();

		if ( ! empty( $instructor_ids ) )
			foreach ( $instructor_ids as $instructor_id )
				$instructors[ $instructor_id ] = new CoursePress_User( $instructor_id );

		return $instructors;
	}

	private function _get_facilitators() {
		$id = $this->__get( 'ID' );
		$facilitator_ids = get_post_meta( $id, 'facilitator' );

		if ( is_array( $facilitator_ids ) && ! empty( $facilitator_ids ) )
			return array_unique( array_filter( $facilitator_ids ) );

		return array();
	}

	/**
	 * Count the total number of course facilitators.
	 *
	 * @return int
	 */
	function count_facilitators() {
		return count( $this->_get_facilitators() );
	}

	/**
	 * Get course facilitators.
	 *
	 * @return array of WP_User object
	 */
	function get_facilitators() {
		$facilitator_ids = $this->_get_facilitators();

		return array_map( 'get_userdata', $facilitator_ids );
	}

	private function _get_students() {
		$id = $this->__get( 'ID' );
		$student_ids = get_post_meta( $id, 'student' );

		if ( is_array( $student_ids ) && ! empty( $student_ids ) )
			return array_unique( array_filter( $student_ids ) );

		return array();
	}

	/**
	 * Count total number of students in a course.
	 *
	 * @return int
	 */
	function count_students() {
		return count( $this->_get_students() );
	}

	/**
	 * Get course students
	 *
	 * @return array of CoursePress_User object
	 */
	function get_students() {
		$students = array();
		$student_ids = $this->_get_students();

		if ( ! empty( $student_ids ) ) {
			foreach ( $student_ids as $student_id ) {
				$students[ $student_id ] = new CoursePress_User( $student_id );
			}
		}

		return $students;
	}

	function count_certified_students() {
		// @todo: count certified students here
		return 0;
	}

	/**
	 * Get an array of categories of the course.
	 *
	 * @return array
	 */
	function get_category() {
		$id = $this->__get( 'ID' );
		$course_category = wp_get_object_terms( $id, 'course_category' );
		$cats = array();

		if ( ! empty( $course_category ) )
			foreach ( $course_category as $term )
				$cats[ $term->term_id ] = $term->name;

		return $cats;
	}

	function get_permalink() {
		return coursepress_get_course_url( $this->__get('ID' ) );
	}

	function get_discussion_url() {
		$course_url = $this->get_permalink();
		$discussion_slug = coursepress_get_setting( 'slugs/discussions', 'discussions' );

		return $course_url . trailingslashit( $discussion_slug );
	}

	function get_grades_url() {
		$course_url = $this->get_permalink();
		$grades_slug = coursepress_get_setting( 'slugs/grades', 'grades' );

		return $course_url . trailingslashit( $grades_slug );
	}

	function get_workbook_url() {
		$course_url = $this->get_permalink();
		$workbook_slug = coursepress_get_setting( 'slugs/workbook', 'workbook' );

		return $course_url . trailingslashit( $workbook_slug );
	}

	private function _get_units( $published = true, $ids = true ) {
		$args = array(
			'post_type'      => 'unit',
			'post_status'    => $published ? 'publish' : 'any',
			'post_parent'    => $this->__get( 'ID' ),
			'posts_per_page' => - 1, // Units are often retrieve all at once
			'suppress_filters' => true,
			'orderby' => 'menu_order',
			'order' => 'ASC',
		);

		if ( $ids )
			$args['fields'] = 'ids';

		$units = get_posts( $args );

		return $units;
	}

	function count_units( $published = true ) {
		$units = $this->_get_units( $published );

		return count( $units );
	}

	function get_units( $published = true ) {
		$units = array();
		$results = $this->_get_units( $published, false );

		if ( ! empty( $results ) ) {
			$previousUnit = false;

			foreach ( $results as $unit ) {
				$unitClass = new CoursePress_Unit( $unit, $this );
				$unitClass->__set( 'previousUnit', $previousUnit );
				$previousUnit = $unitClass;
				$units[] = $unitClass;
			}
		}

		return $units;
	}

	function get_course_structure() {
		global $CoursePress_User;

		/**
		 * @var $CoursePress_User CoursePress_User
		 */

		$course_id = $this->__get( 'ID' );
		$user = $CoursePress_User;
		$user_id = $user->__get( 'ID' );
		$has_access = $user->has_access_at( $course_id );
		$is_student = $user->is_enrolled_at( $course_id );
		$published = ! $has_access;
		$structure = '';

		$user->validate_completion_data( $course_id );

		$units = $this->get_units( $published );

		if ( $units ) {
			foreach ( $units as $unit ) {
				/**
				 * @var $unit CoursePress_Unit
				 */
				$unit_id = $unit->__get( 'ID' );
				$unit_title = $unit->__get( 'post_title' );
				$unit_url = esc_url_raw( $unit->get_unit_url() );
				$is_unit_available = $unit->is_available();
				$is_unit_accessible = $unit->is_accessible_by( $user_id );
				$unit_locked = $is_student && ( ! $is_unit_available || ! $is_unit_accessible );
				$unit_suffix = '';
				$unit_class = array( 'unit' );
				$unit_structure = '';
				$unit_duration = '';

				if ( ! $unit_locked ) {
					if ( $this->__get( 'with_modules' ) ) {
						$modules = $unit->get_modules_with_steps( $published );

						if ( $modules ) {
							$module_structure = '';
							$module_locked = false;

							foreach ( $modules as $module_id => $module ) {
								$module_title = $module['title'];
								$module_url = esc_url_raw( $module['url'] );
								$module_suffix = '';
								$steps_structure = '';
								$module_class = array( 'module' );

								if ( $has_access ) {
									$module_title = $this->create_html( 'a', array( 'href' => $module_url ), $module_title );
								} elseif ( $is_student ) {
									if ( ! $unit->is_module_accessible_by( $user_id, $module ) ) {
										$module_class[] = 'module-locked';
										$module_locked = true;
									} else {
										if ( $user->is_module_completed( $course_id, $unit_id, $module_id ) ) {
											$module_class[] = 'module-seen module-completed';
											$module_title = $this->create_html( 'a', array( 'href' => $module_url ), $module_title );
										} elseif ( $user->is_module_seen( $course_id, $unit_id, $module_id ) ) {
											$module_class[] = 'module-seen';
										}
									}
								} else {
									if ( $module['preview'] ) {
										$module_class[] = 'has-preview';

										$attr = array(
											'href' => add_query_arg( 'preview', true, $module_url ),
											'class' => 'preview',
										);
										$module_suffix .= $this->create_html( 'a', $attr, __( 'Preview' ) );
									}
								}

								$module_title = $this->create_html( 'div', array( 'class' => 'module-title' ), $module_title . $module_suffix );

								if ( ! $module_locked
								     && ! empty( $module['steps'] ) ) {
									$steps_structure .= $this->get_steps_structure( $module['steps'], $unit, $user, $has_access, $is_student );
								}

								$attr = array( 'class' => implode( ' ', $module_class ) );
								$module_structure .= $this->create_html( 'li', $attr, $module_title  . $steps_structure );
							}

							$unit_structure .= $this->create_html( 'ol', array( 'class' => 'tree module-tree' ), $module_structure );
						}
					}
				}

				if ( $has_access ) {
					$unit_title = $this->create_html( 'a', array( 'href' => $unit_url ), $unit_title );
				} elseif ( $is_student ) {
					if ( ! $is_unit_available ) {
						$unit_class[] = 'unit-locked';
						$label = sprintf( __( 'Opens %s', 'cp' ), $unit->__get( 'unit_availability_date' ) );
						$unit_suffix .= $this->create_html( 'span', array( 'class' => 'unit-date' ), $label );
					} elseif ( ! $is_unit_accessible ) {
						$unit_class[] = 'unit-locked';
					} else {
						$unit_class[] = 'has-progress';
						$unit_progress = $user->get_unit_progress( $course_id, $unit_id );
						$unit_title = $this->create_html( 'a', array( 'href' => $unit_url ), $unit_title );

						if ( $user->is_unit_completed( $course_id, $unit_id ) ) {
							$unit_class[] = 'unit-seen unit-completed';
						} elseif ( $user->is_unit_seen( $course_id, $unit_id ) ) {
							$unit_class[] = 'unit-seen';
						}

						if ( $unit_progress > 0 )
							$unit_progress /= 100;

						$attr = array(
							'class' => 'course-progress-disc unit-progress',
							'data-value' => $unit_progress,
							'data-start-angle' => '4.7',
							'data-size' => 36,
							'data-knob-data-height' => 40,
							'data-empty-fill' => 'rgba(0, 0, 0, 0.2)',
							'data-fill-color' => '#24bde6',
							'data-bg-color' => '#e0e6eb',
							'data-thickness' => '6',
							'data-format' => true,
							'data-style' => 'extended',
							'data-animation-start-value' => '1.0',
							'data-knob-data-thickness' => 0.18,
							'data-knob-text-show' => true,
							'data-knob-text-color' => '#222222',
							'data-knob-text-align' => 'center',
							'data-knob-text-denominator' => '4.5',
						);

						/**
						 * Fire to allow changes on unit progress wheel attributes
						 * before printing the unit progress.
						 *
						 * @since 2.0
						 * @param $attr An array of wheel attributes.
						 */
						$attr = apply_filters( 'coursepress_unit_progress_wheel_atts', $attr );

						$unit_suffix .= $this->create_html( 'div', $attr );
					}
				} elseif ( $unit->__get( 'preview' ) ) {
					$attr = array(
						'href' => add_query_arg( 'preview', true, $unit_url ),
						'class' => 'preview',
						'target' => '_blank',
					);
					$unit_suffix .= $this->create_html( 'a', $attr, __( 'Preview', 'cp' ) );
				}

				if ( ! empty( $unit_duration ) && ( ! $has_access || ! $is_student ) ) {
					$unit_suffix = $this->create_html( 'span', array( 'class' => 'timer' ), $unit_duration ) . $unit_suffix;
				}

				$unit_title = $this->create_html( 'div', array( 'class' => 'unit-title' ), $unit_title . $unit_suffix );

				$attr = array( 'class' => implode( ' ', $unit_class ) );
				$structure .= $this->create_html( 'li', $attr, $unit_title . $unit_structure );
			}

			$attr = array( 'class' => 'tree course-structure' );
			$structure = $this->create_html( 'ul', $attr, $structure );
		}

		return $structure;
	}

	private function get_steps_structure( $steps, $unit, $user, $has_access, $is_student ) {
		$steps_structure = '';
		$user_id = $user->__get( 'ID' );
		$course_id = $this->__get( 'ID' );
		$unit_id = $unit->__get( 'ID' );

		foreach ( $steps as $step ) {
			$step_id = $step->__get( 'ID' );
			$step_title = $step->__get( 'post_title' );
			$step_url = esc_url( $step->get_permalink() );
			$step_suffix = '';
			$step_class = array( 'course-step' );

			if ( $has_access ) {
				$attr = array( 'href' => $step_url );
				$step_title = $this->create_html( 'a', $attr, $step_title );
			} elseif ( $is_student ) {
				if ( ! $step->is_accessible_by( $user_id ) ) {
					$step_class[] = 'step-locked';
				} elseif ( $user->is_step_completed( $course_id, $unit_id, $step_id ) ) {
					$step_class[] = 'step-seen step-completed';
				} elseif ( $user->is_step_seen ( $course_id, $unit_id, $step_id ) ) {
					$step_class[] = 'step-seen';
				}
			}
			$attr = array( 'class' => implode( ' ', $step_class ) );
			$steps_structure .= $this->create_html( 'li', $attr, $step_title . $step_suffix );
		}

		if ( ! empty( $steps_structure ) ) {
			$attr = array( 'class' => 'tree step-tree' );
			$steps_structure = $this->create_html( 'ol', $attr, $steps_structure );
		}

		return $steps_structure;
	}
}