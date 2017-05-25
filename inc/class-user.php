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
	 * CoursePress_User constructor.
	 *
	 * @param bool|int|WP_User $user
	 */
	public function __construct( $user = true ) {
		if ( is_bool( $user ) && TRUE === $user ) {
			global $current_user;

			$user = $current_user;
			if ( empty( $current_user ) )
				$user = get_userdata( get_current_user_id() );
		} elseif ( ! $user instanceof WP_User && (int) $user > 0 ) {
			$user = get_userdata( $user );
		}

		foreach ( $user as $key => $value )
			$this->__set( $key, $value );

		if ( is_super_admin( $this->ID ) || in_array( 'administrator', $this->roles ) )
			$this->user_type = 'administrator';
		// Being an instructor is a priority first
		elseif ( in_array( 'instructor', $this->roles ) )
			$this->user_type = 'instructor';
		elseif ( in_array( 'facilitator', $this->roles ) )
			$this->user_type = 'facilitator';
		elseif ( in_array( 'student', $this->roles ) )
			$this->user_type = 'student';

		// Get user caps
	}

	/**
	 * Check if current user has the given capability
	 *
	 * @param string $cap
	 * @return bool
	 */
	function user_can( $cap ) {
		return true;
	}
}