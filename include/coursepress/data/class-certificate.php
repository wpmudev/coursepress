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
	 * @var string
	 */
	private static $post_type = 'cp_certificate';

	/**
	 * If the certificate module is enabled or not.
	 *
	 * @var bool
	 */
	private static $is_enabled = null;

	/**
	 * Returns details about the custom post-type.
	 *
	 * @since  2.0.0
	 * @return array Details needed to register the post-type.
	 */
	public static function get_format() {
		if ( ! self::is_enabled() ) { return false; }

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
	 * Checks if the Basic Certificate module is enabled or not.
	 *
	 * @since  2.0.0
	 * @return bool False means that all functions here are disabled.
	 */
	public static function is_enabled() {
		if ( null === self::$is_enabled ) {
			$flag = CoursePress_Core::get_setting(
				'basic_certificate/enabled',
				true
			);

			self::$is_enabled = cp_is_true( $flag );
		}

		return self::$is_enabled;
	}

	/**
	 * Generate the certificate, store it in DB and send email to the student.
	 *
	 * @since  2.0.0
	 * @param  int $student_id The WP user-ID.
	 * @param  int $course_id The course-ID that was completed.
	 */
	public function generate_certificate( $student_id, $course_id ) {
		if ( ! self::is_enabled() ) { return false; }

		// First check, if the student is already certified for the course.
		$params = array(
			'author' => $student_id,
			'post_parent' => $course_id,
			'post_type' => self::get_post_type_name(),
			'post_status' => 'any',
		);
		$res = get_posts( $params );

		if ( is_array( $res ) && count( $res ) ) {
			$the_id = $res[0]->ID;
		} else {
			$the_id = self::create_certificate( $student_id, $course_id );
		}

		// And finally: Send that email :)
		self::send_certificate( $the_id );
	}

	/**
	 * Send certificate to student.
	 *
	 * @since 2.0.0
	 * @param  int $student_id The WP user-ID.
	 * @param  int $course_id The course-ID that was completed.
	 * @return bool True on success.
	 */
	public function send_certificate( $certificate_id ) {
		if ( ! self::is_enabled() ) { return false; }

		$email_args = self::fetch_params( $certificate_id );

		// TODO: We want to add PDF attachment to the email.
		return CoursePress_Helper_Email::send_email(
			CoursePress_Helper_Email::BASIC_CERTIFICATE,
			$email_args
		);
	}

	/**
	 * Inserts a new certificate into the DB and returns the created post_id.
	 *
	 * Note that we need to save this twice:
	 * First time the post_content is empty/dummy, then on the second pass we
	 * populate the content, as we need to know the post_id to generate it.
	 *
	 * @since  2.0.0
	 * @return int Post-ID
	 */
	protected static function create_certificate( $student_id, $course_id ) {
		$post = array(
			'post_author' => $student_id,
			'post_parent' => $course_id,
			'post_status' => 'private', // Post is only visible for post_author.
			'post_type' => self::get_post_type_name(),
			'post_content' => 'Processing...', // Intentional value.
			'post_title' => 'Basic Certificate',
			'ping_status' => 'closed',
		);

		// Stage 1: Save data to get post_id!
		$certificate_id = wp_insert_post( $post );

		$post['ID'] = $certificate_id;
		$post['post_content'] = self::get_certificate_content( $certificate_id );

		// Stage 2: Save final certificate data!
		wp_update_post(
			apply_filters( 'coursepress_pre_insert_post', $post )
		);

		return $certificate_id;
	}

	/**
	 * Returns an array with all certificate details needed fo send the email
	 * and to process the certificate contents.
	 *
	 * @since  2.0.0
	 * @param  int $certificate_id The post-ID of the certificate.
	 * @return array Array with certificate details.
	 */
	protected static function fetch_params( $certificate_id ) {
		if ( ! self::is_enabled() ) { return array(); }

		$student_id = (int) get_post_field( 'post_author', $certificate_id );
		$course_id = (int) get_post_field( 'post_parent', $certificate_id );
		$completion_date = get_post_field( 'post_date', $certificate_id );

		if ( empty( $student_id ) ) { return false; }
		if ( empty( $course_id ) ) { return false; }

		$student = get_userdata( $student_id );
		if ( empty( $student ) ) { return false; }

		$course = get_post( $course_id );
		$course_name = $course->post_title;
		$valid_stati = array( 'draft', 'pending', 'auto-draft' );

		if ( in_array( $course->post_status, $valid_stati ) ) {
			$course_address = CoursePress_Core::get_slug( 'course/', true ) . $course->post_name . '/';
		} else {
			$course_address = get_permalink( $course_id );
		}

		$params = array();
		$params['course_id'] = $course_id;
		$params['email'] = sanitize_email( $student->user_email );
		$params['first_name'] = $student->user_firstname;
		$params['last_name'] = $student->user_lastname;
		$params['completion_date'] = $completion_date;
		$params['certificate_id'] = $certificate_id;
		$params['course_name'] = $course_name;
		$params['course_address'] = $course_address;
		$params['unit_list'] = '...'; // TODO: Insert the Unit-List!

		return $params;
	}

	/**
	 * Parse the Certificate template and return HTML code to render the
	 * certificate.
	 *
	 * @since  2.0.0
	 * @param  int $certificate_id The post-ID of the certificate.
	 * @return string HTML code to display the certificate.
	 */
	protected static function get_certificate_content( $certificate_id ) {
		$data = self::fetch_params( $certificate_id );

		$content = CoursePress_Core::get_setting(
			'basic_certificate/content',
			$fields['content']
		);

		// TODO: Add background and padding...
		// TODO: Convert certificate into PDF...

		$vars = array(
			'FIRST_NAME' => sanitize_text_field( $data['first_name'] ),
			'LAST_NAME' => sanitize_text_field( $data['last_name'] ),
			'COURSE_NAME' => sanitize_text_field( $data['course_name'] ),
			'COMPLETION_DATE' => sanitize_text_field( $data['completion_date'] ),
			'CERTIFICATE_NUMBER' => (int) $data['certificate_id'],
			'UNIT_LIST' => $data['unit_list'],
		);

		return CoursePress_Helper_Utility::replace_vars( $content, $vars );
	}
}
