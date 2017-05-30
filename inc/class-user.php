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
	public function __construct( $user = 0 ) {
		if ( ! $user instanceof WP_User ) {
			$user = get_userdata( (int) $user );
		}

		if ( empty( $user ) || ! $user instanceof  WP_User ) {
			$this->is_error = true;

			return;
		}

		// Inherit WP_User object
		foreach ( $user as $key => $value ) {
			$this->__set( $key, $value );
		}
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
		$enrolled = get_user_meta( $this->ID, 'student_' . $course_id, true );

		return $this->ID == $enrolled;
	}

	function is_instructor_at( $course_id ) {
		$instructor = get_user_meta( $this->ID, 'instructor_' . $course_id, true );

		return $instructor == $this->ID;
	}

	function is_facilitator_at( $course_id ) {
		$facilitator = get_user_meta( $this->ID, 'facilitator_' . $course_id, true );

		return $facilitator == $this->ID;
	}

	function get_accessable_courses( $publish = true, $ids = false, $all = true ) {
		$courses = array();

		$args = array(
			'post_status' => $publish ? 'publish' : 'any',
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
