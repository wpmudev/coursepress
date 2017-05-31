<?php
/**
 * Class CoursePress_Course
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Course extends CoursePress_Utility {
	public function __construct( $course ) {
		if ( ! $course instanceof WP_Post ) {
			$course = get_post( (int) $course );
		}

		foreach ( $course as $key => $value ) {
			$this->__set( $key, $value );
		}

		// Set course meta
		$this->setUpCourseMetas();
	}

	function setUpCourseMetas() {
		$settings = $this->get_settings();
		$date_format = coursepress_get_option( 'date_format' );
		$timezone = coursepress_get_option( 'timezone_string', 'UTC' );
		$date_keys = array( 'course_start_date', 'course_end_date', 'enrollment_start_date', 'enrollment_end_date' );

		foreach ( $settings as $key => $value ) {
			if ( in_array( $key, $date_keys ) && ! empty( $value ) ) {
				$timestamp = strtotime( $value, $timezone );
				$value = date_i18n( $date_format, $timestamp );

				// Add timestamp info
				$this->__set( $key . '_timestamp', $timestamp );
			}

			$this->__set( $key, $value );
		}
	}

	function get_settings() {
		$defaults = array(
			'course_language' => __( 'English', 'CP_TD' ),
			'course_view' => 'normal',
			'structure_level' => 'unit',
			'structure_show_empty_units' => false,
			'structure_visible_units' => array(),
			'structure_preview_units' => array(),
			'structure_visible_pages' => array(),
			'structure_preview_pages' => array(),
			'structure_visible_modules' => array(),
			'structure_preview_modules' => array(),
			'course_open_ended' => true,
			'course_start_date' => 0,
			'course_end_date' => '',
			'enrollment_open_ended' => false,
			'enrollment_start_date' => '',
			'enrollment_end_date' => '',
			'class_limited' => '',
			'class_size' => '',
			'enrollment_type' => 'registered_user',
			'payment_paid_course' => false,
			'enrollment_passcode' => '',
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

		$settings = get_post_meta( $this->ID, 'course_settings', true );
		$settings = wp_parse_args( $settings, $defaults );

		return $settings;
	}

	function has_course_started() {
		$time_now = $this->date_time_now();

		if ( empty( $this->course_open_ended )
			&& $this->course_start_date_timestamp > 0
			&& $this->course_start_date_timestap > $time_now )
			return false;

		return true;
	}

	function has_course_ended() {
		$time_now = $this->date_time_now();

		if ( empty( $this->course_open_ended )
			&& $this->course_end_date_timestamp > 0
			&& $this->course_end_date_timestamp < $time_now )
			return true;

		return false;
	}

	function is_available() {
		$is_available = $this->has_course_started();

		if ( $is_available ) {
			// Check if the course hasn't ended yet
			if ( $this->has_course_ended() )
				$is_available = false;
		}

		return $is_available;
	}

	function has_enrollment_started() {
		$time_now = $this->date_time_now();

		if ( empty( $this->enrollment_open_ended )
			&& $this->enrollment_start_date_timestamp > 0
			&& $this->enrollment_start_date_timestamp > $time_now )
			return false;

		return true;
	}

	function has_enrollment_ended() {
		$time_now = $this->date_time_now();

		if ( empty( $this->enrollment_open_ended )
			&& $this->enrollment_end_date_timestamp > 0
			&& $this->enrollment_end_date_timestamp < $time_now )
			return true;

		return false;
	}

	function user_can_enroll() {
		$available = $this->is_available();

		if ( $available ) {
			// Check if enrollment has started
			$available = $this->has_enrollment_started();

			// Check if enrollment already ended
			if ( $available && $this->has_course_ended() )
				$available = false;
		}

		return $available;
	}

	private function _get_instructors() {
		$instructor_ids = get_post_meta( $this->ID, 'instructor' );

		if ( is_array( $instructor_ids ) && ! empty( $instructor_ids ) )
			return array_unique( array_filter( $instructor_ids ) );

		return array();
	}

	function count_instructors() {
		return count( $this->_get_instructors() );
	}

	/**
	 * Get course instructors.
	 *
	 * @return array An array of WP_User object on success.
	 */
	function get_instructors() {
		$instructor_ids = $this->_get_instructors();

		return array_map( 'get_userdata', $instructor_ids );
	}

	private function _get_facilitators() {
		$facilitator_ids = get_post_meta( $this->ID, 'facilitator' );

		if ( is_array( $facilitator_ids ) && ! empty( $facilitator_ids ) )
			return array_unique( array_filter( $facilitator_ids ) );

		return array();
	}

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
		$student_ids = get_post_meta( $this->ID, 'student' );

		if ( is_array( $student_ids ) && ! empty( $student_ids ) )
			return array_unique( array_filter( $student_ids ) );

		return array();
	}

	function count_students() {
		return count( $this->_get_students() );
	}

	/**
	 * Get course students
	 *
	 * @return array of WP_User object
	 */
	function get_students() {
		$student_ids = $this->_get_students();

		return array_map( 'get_userdata', $student_ids );
	}

	function count_certified_students() {
		// @todo: count certified students here
		return 0;
	}

	function get_units( $status = 'any' ) {
		global $CoursePress_Data_Units;

		$units = $CoursePress_Data_Units->get_course_units( $this->ID, $status );

		return $units;
	}
}