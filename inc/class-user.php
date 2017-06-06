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

		$enrolled = get_user_option( $id, $key );

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

		$defaults = array( 'version' => $CoursePress->version );

		if ( ! $this->is_enrolled_at( $course_id ) )
			return $defaults;

		$key = 'course_' . $course_id . '_progress';

		$progress = $this->__get( $key );

		if ( $progress )
			return $progress;

		$id = $this->__get( 'ID' );

		$progress = get_user_option( $key, $id );
error_log(print_r($progress,true));
		if ( ! $progress )
			return $defaults;

		return $progress;
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
