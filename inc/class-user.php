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
	 * Check if user is enrolled to the given course ID.
	 *
	 * @param $course_id
	 *
	 * @return bool
	 */
	function is_enrolled_at( $course_id ) {
		$id = $this->__get( 'ID' );

		if ( ! $id )
			return false;

		$key = 'enrolled_course_date_' . $course_id;

		$enrolled = coursepress_get_user_option( $id, $key );

		return ! empty( $enrolled );
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

	function get_instructor_profile_link() {
	//	if ( false == $this->is_instructor() )
	//		return null;

		$slug = coursepress_get_setting( 'slugs/instructor_profile', 'instructor' );

		return site_url( '/' ) . trailingslashit( $slug ) . $this->__get( 'user_login' );
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
