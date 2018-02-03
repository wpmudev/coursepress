<?php
/**
 * Class CoursePress_Data_Course
 *
 * @since 3.0
 * @package CoursePress
 */
final class CoursePress_Data_Course extends CoursePress_Utility {
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

	/**
	 * Return the course that is associated with current page.
	 * i.e. this function returns the course ID that is currently displayed on
	 * front end.
	 *
	 * @since  2.0.0
	 *
	 * @return int The course ID or 0 if not called inside a course/unit/module.
	 */
	public static function get_current_course_id() {

		global $wp;

		if ( empty( $wp->query_vars ) ) {
			return 0;
		}

		if ( ! is_array( $wp->query_vars ) ) {
			return 0;
		}

		if ( empty( $wp->query_vars['coursename'] ) ) {
			return 0;
		}

		$coursename = $wp->query_vars['coursename'];
		$course_id = CoursePress_Data_Course::by_name( $coursename, true );

		return (int) $course_id;
	}

	/**
	 * Get course by name.
	 *
	 * @param string $slug Course slug.
	 * @param int $id_only Need only course id?
	 *
	 * @return array|bool|int|null|WP_Post
	 */
	public static function by_name( $slug, $id_only ) {

		$res = false;

		// First try to fetch the course by the slug (name).
		$args = array(
			'name' => $slug,
			'post_type' => self::get_post_type_name(),
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		if ( $id_only ) {
			$args['fields'] = 'ids';
		}

		$post = get_posts( $args );

		if ( $post ) {
			$res = $post[0];
		} elseif ( is_numeric( $slug ) ) {
			// If we did not find a course by name, try to fetch it via ID.
			$post = get_post( $slug );

			if ( self::get_post_type_name() == $post->post_type ) {
				if ( $id_only ) {
					$res = $post->ID;
				} else {
					$res = $post;
				}
			}
		}

		return $res;
	}

	/**
	 * Get meta name "last seen unit".
	 *
	 * @since 2.0.4
	 * @param integer $course_id Course ID
	 *
	 * @return string
	 */
	public static function get_last_seen_unit_meta_key( $course_id ) {

		return sprintf( 'course_%s_last_seen_unit', $course_id );
	}
}
