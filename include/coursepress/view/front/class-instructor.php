<?php

class CoursePress_View_Front_Instructor {

	public static $discussion = false;  // Used for hooking discussion filters
	public static $title = ''; // The page title
	public static $last_instructor;

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

		/**
		 * Intercep virtual page when dealing with invitation code.
		 **/
		add_filter( 'coursepress_virtual_page', array( __CLASS__, 'instructor_verification' ), 10, 2 );

	}

	public static function render_instructor_page() {
		CoursePress_Core::$is_cp_page = true;

		if ( $theme_file = locate_template( array( 'instructor-single.php' ) ) ) {
		} else {

			if ( locate_template( array( 'instructor-single.php' ) ) ) {
				// add custom content in the single template ONLY if the post type doesn't already has its own template
				// just output the content
			} else {
				$content = CoursePress_Template_User::render_instructor_page();
			}
		}

		return $content;
	}


	public static function parse_request( &$wp ) {

		if ( array_key_exists( 'instructor_username', $wp->query_vars ) ) {

			$username = sanitize_text_field( $wp->query_vars['instructor_username'] );
			$instructor = CoursePress_Data_Instructor::instructor_by_login( $username );
			if ( empty( $instructor ) ) {
				$instructor = CoursePress_Data_Instructor::instructor_by_hash( $username );
			}
			$content = '';
			if ( empty( $instructor ) ) {
				$content = __( 'The requested instuctor does not exists', 'cp' );
			}

			self::$last_instructor = empty( $instructor ) ? 0 : $instructor->ID;

			$page_title = ! empty( self::$last_instructor ) ? CoursePress_Helper_Utility::get_user_name( self::$last_instructor, false, false ) : __( 'Instructor not found.', 'cp' );
			$args = array(
				'slug' => 'instructor_' . self::$last_instructor,
				'title' => $page_title,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_instructor_page(),
				'type' => 'coursepress_instructor',
			);

			$pg = new CoursePress_Data_VirtualPage( $args );

			return;

		}
	}

	/**
	 * Intercep the virtual page rendered in main course page.
	 *
	 * @since 2.0
	 *
	 * @param (mixed) $_vr_args		 The previous arguments used to construct a virtual page or (bool) false.
	 * @param (object) $cp		 The object.
	 **/
	public static function instructor_verification( $_vp_args, $cp ) {
		if ( ! isset( $_GET['action'] ) || 'course_invite' != $_GET['action'] ) {
			return $_vp_args;
		}
		$course_invite = CoursePress_Data_Instructor::is_course_invite();

		$vp_args = array(
			'slug' => 'instructor_verification' . $course_invite->course_id,
			'type' => CoursePress_Data_Course::get_post_type_name() . '_archive',
			'is_page' => true,
		);

		$args = array();

		if ( $course_invite ) {

			$is_verified = CoursePress_Data_Instructor::verify_invitation_code( $course_invite->course_id, $course_invite->code, $course_invite->invitation_data );

			if ( $is_verified ) {

				/**
				 * redirect to registration form
				 */
				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Core::get_setting( 'general/use_custom_login' ) ) {
						$url = CoursePress_Core::get_slug( 'signup', true );
					} else {
						$url = wp_login_url();
					}

					$content = sprintf( '<p><a href="%s">%s</a> %s</p>', esc_url( $url ), __( 'Login', 'cp' ), __( 'to continue.', 'cp' ) );

					$args = array(
						'show_title' => false,
						'title' => apply_filters( 'coursepress_instructor_invitation_title', esc_html__( 'Instructor Invitation', 'cp' ) ),
						'content' => apply_filters( 'coursepress_instructor_invitation_content', $content ),
					);
					$vp_args = wp_parse_args( $args, $vp_args );

					return $vp_args;
				}

				$user = get_user_by( 'email', $is_verified['email'] );
				$user_id = $user->ID;

				$is_added = CoursePress_Data_Instructor::add_from_invitation( $course_invite->course_id, $user_id, $course_invite->code );

				if ( $is_added ) {
					$main_course = apply_filters( 'coursepress_view_course', CoursePress_View_Front_Course::render_course_main(), $course_invite->course_id, 'main' );
					$args = array(
						'show_title' => true,
						'title' => esc_html__( 'Invitation activated', 'cp' ),
						'content' => sprintf(
							'<p>%s %s</p>%s',
							esc_html__( 'Congratulations. You are now an instructor of this course. ', 'cp' ),
							sprintf(
								'<a href="%s" class="blue-button small-button button-a">%s</a>',
								esc_url( get_permalink( $course_invite->course_id ) ),
								__( 'Course Details', 'cp' )
							),
							$main_course
						),
					);
				} else {
					$args = array(
						'show_title' => false,
						'title' => esc_html__( 'Invalid invitation', 'cp' ),
						'content' => sprintf(
							'<p>%s</p><p>%s</p>',
							esc_html__( 'This invitation link is not associated with your email address.', 'cp' ),
							esc_html__( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'cp' )
						),
					);
				}
			}
		}

		if ( empty( $args ) ) {
			$args = array(
				'show_title' => false,
				'title' => esc_html__( 'Invitation not found', 'cp' ),
				'content' => sprintf(
					'<div class="cp-warning-box"><p>%s</p><p>%s</p></div>',
					esc_html__( 'This invitation could not be found or is no longer available.', 'cp' ),
					esc_html__( 'Please contact us if you believe this to be an error.', 'cp' )
				),
			);
		}

		$vp_args = wp_parse_args( $args, $vp_args );

		return $vp_args;
	}

	public static function modal_view() {
		$invite_data = CoursePress_Data_Instructor::is_course_invite();
		?>
		<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="instructor-verified">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invitation activated.', 'cp' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'Congratulations. You are now an instructor of this course. ', 'cp' ); ?></p>
			</div>
			<div class="bbm-modal__bottombar">
				<a href="<?php echo esc_url( get_permalink( $invite_data->course_id ) ); ?>" class="bbm-button button"><?php esc_html_e( 'Continue...', 'cp' ); ?></a>
			</div>
		</script>

		<script type="text/template" id="modal-view5-template" data-type="modal-step" data-modal-action="verification-failed">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invalid invitation.', 'cp' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'This invitation link is not associated with your email address.', 'cp' ); ?></p>
				<p><?php esc_html_e( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'cp' ); ?></p>
			</div>
			<div class="bbm-modal__bottombar">
				<a href="<?php echo esc_url( get_permalink( $invite_data->course_id ) ); ?>" class="bbm-button button"><?php esc_html_e( 'Continue...', 'cp' ); ?></a>
			</div>
		</script>
		<?php
	}
}
