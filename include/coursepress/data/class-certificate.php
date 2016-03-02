<?php
/**
 * Data access module.
 *
 * @package CoursePress
 */

/**
 * Handles access to the Certificate details.
 */
class CoursePress_Data_Certificate {

	/**
	 * The post-type slug for certificates.
	 *
	 * @type string
	 */
	private static $post_type = 'cp_certificate';

	/**
	 * Returns details about the custom post-type.
	 *
	 * @since  2.0.0
	 * @return array Details needed to register the post-type.
	 */
	public static function get_format() {
		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Certificates', 'CP_TD' ),
					'singular_name' => __( 'Certificate', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Certificate', 'CP_TD' ),
					'edit_item' => __( 'Edit Certificate', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Certificate', 'CP_TD' ),
					'view_item' => __( 'View Certificate', 'CP_TD' ),
					'search_items' => __( 'Search Certificates', 'CP_TD' ),
					'not_found' => __( 'No Certificates Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Certificates found in Trash', 'CP_TD' ),
					'view' => __( 'View Certificate', 'CP_TD' ),
				),
				'public' => false,
				'show_ui' => false,
				'show_in_menu' => false,
				'publicly_queryable' => false,
				'capability_type' => 'certificate',
				'map_meta_cap' => true,
				'query_var' => true,
			),
		);
	}

	/**
	 * Return the post-type slug for certificates.
	 *
	 * @since  2.0.0
	 * @return string The prefixed post-type slug.
	 */
	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	/**
	 * Send certificate to student.
	 *
	 * @since 2.0.0
	 */
	public function send_certificate( $student_id, $course_id ) {
		// If student doesn't exist, exit.
		$student = get_userdata( $student_id );
		if ( empty( $student ) ) {
			return false;
		}

		$email_args = array();
		$email_args['email_type'] = CoursePress_Helper_Email::BASIC_CERTIFICATE;
		$email_args['course_id'] = $course_id;
		$email_args['email'] = sanitize_email( $student->user_email );
		$email_args['first_name'] = $student->user_firstname;
		$email_args['last_name'] = $student->user_lastname;

		return CoursePress_Helper_Email::send_email( $email_args );
	}
}
