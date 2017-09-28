<?php
/**
 * Class CoursePress_Data_Courses
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Courses extends CoursePress_Utility {
	/**
	 * get courses list
	 *
	 * @since 3.0.0
	 */
	public function get_list() {
		global $CoursePress_Core;
		$args = array(
			'post_type' => $CoursePress_Core->course_post_type,
			'post_status' => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
		);
		$list = array();
		$courses = new WP_Query( $args );
		if ( $courses->have_posts() ) {
			while ( $courses->have_posts() ) {
				$courses->the_post();
				$list[ get_the_ID() ] = get_the_title();
			}
		}
		return $list;
	}

	/**
	 * Get course invitations by course ID.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param string $type Invitation type.
	 *
	 * @return array Array of invitations.
	 */
	public static function get_invitations_by_course_id( $course_id, $type = 'instructor' ) {

		return get_post_meta( $course_id, $type. '_invites', true );
	}

	/**
	 * Generate invitation code and hash.
	 *
	 * @param string $email Email ID.
	 *
	 * @return array
	 */
	public static function create_invite_code_hash( $email ) {

		// Generate invite code.
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$invite_code = '';
		for ( $i = 0; $i < 20; $i ++ ) {
			$invite_code .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		$data = array(
			'code' => $invite_code,
			'hash' => sha1( sanitize_email( $email ) . $invite_code ),
		);

		return $data;
	}
}
