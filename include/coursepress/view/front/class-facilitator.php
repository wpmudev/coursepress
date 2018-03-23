<?php

class CoursePress_View_Front_Facilitator {

	public static $discussion = false;  // Used for hooking discussion filters
	public static $title = ''; // The page title
	public static $last_facilitator;

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

		/**
		 * Intercep virtual page when dealing with invitation code.
		 **/
		add_filter( 'coursepress_virtual_page', array( __CLASS__, 'facilitator_verification' ), 10, 2 );

	}

	public static function render_facilitator_page() {
		CoursePress_Core::$is_cp_page = true;

		$theme_file = locate_template( array( 'facilitator-single.php' ) );

		if ( $theme_file ) {
			CoursePress_View_Front_Course::$template = $theme_file;
			$content = '';
		} else {
			$content = CoursePress_Template_User::render_facilitator_page();
		}

		return $content;
	}


	public static function parse_request( &$wp ) {
		if ( array_key_exists( 'facilitator_username', $wp->query_vars ) ) {

			$username = sanitize_text_field( $wp->query_vars['instructor_username'] );
			$facilitator = CoursePress_Data_Instructor::instructor_by_login( $username );
			if ( empty( $facilitator ) ) {
				$facilitator = CoursePress_Data_Instructor::instructor_by_hash( $username );
			}
			$content = '';
			if ( empty( $facilitator ) ) {
				$content = __( 'The requested facilitator does not exists', 'CP_TD' );
			}

			self::$last_facilitator = empty( $facilitator ) ? 0 : $facilitator->ID;

			$page_title = ! empty( self::$last_facilitator ) ? CoursePress_Helper_Utility::get_user_name( self::$last_facilitator, false, false ) : __( 'Facilitator not found.', 'CP_TD' );
			$args = array(
				'slug' => 'facilitator_' . self::$last_facilitator,
				'title' => $page_title,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_facilitator_page(),
				'type' => 'coursepress_facilitator',
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
	public static function facilitator_verification( $_vp_args, $cp ) {
		if ( ! isset( $_GET['action'] ) || 'course_invite_facilitator' != $_GET['action'] ) {
			return $_vp_args;
		}
		$course_invite = CoursePress_Data_Facilitator::is_course_invite();

		$vp_args = array(
			'slug' => 'facilitator_verification' . $course_invite->course_id,
			'type' => CoursePress_Data_Course::get_post_type_name() . '_archive',
			'is_page' => true,
		);

		$args = array();

		if ( $course_invite ) {

			$is_verified = CoursePress_Data_Facilitator::verify_invitation_code( $course_invite->course_id, $course_invite->code, $course_invite->invitation_data );

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
					$args = array(
						'show_title' => false,
						'title' => apply_filters( 'coursepress_facilitator_invitation_title', esc_html__( 'Facilitator Invitation', 'CP_TD' ) ),
						'content' => apply_filters( 'coursepress_facilitator_invitation_content', sprintf(
							'<p>%s</p>',
							esc_html__( 'You must log in to confirm this invitation.', 'CP_TD' )
						) ),
					);
					$vp_args = wp_parse_args( $args, $vp_args );

					return $vp_args;
				}

				$user = get_user_by( 'email', $is_verified['email'] );
				$user_id = $user->ID;

				$is_added = CoursePress_Data_Facilitator::add_from_invitation( $course_invite->course_id, $user_id, $course_invite->code );

				if ( $is_added ) {
					$main_course = apply_filters( 'coursepress_view_course', CoursePress_View_Front_Course::render_course_main(), $course_invite->course_id, 'main' );
					$args = array(
						'show_title' => true,
						'title' => esc_html__( 'Invitation activated', 'CP_TD' ),
						'content' => sprintf(
							'<p>%s %s</p>%s',
							esc_html__( 'Congratulations. You are now a facilitator of this course. ', 'CP_TD' ),
							sprintf(
								'<a href="%s" class="blue-button small-button button-a">%s</a>',
								esc_url( get_permalink( $course_invite->course_id ) ),
								__( 'Course Details', 'CP_TD' )
							),
							$main_course
						),
					);
				} else {
					$args = array(
						'show_title' => false,
						'title' => esc_html__( 'Invalid invitation', 'CP_TD' ),
						'content' => sprintf(
							'<p>%s</p><p>%s</p>',
							esc_html__( 'This invitation link is not associated with your email address.', 'CP_TD' ),
							esc_html__( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'CP_TD' )
						),
					);
				}
			}
		}

		if ( empty( $args ) ) {
			$args = array(
				'show_title' => false,
				'title' => esc_html__( 'Invitation not found', 'CP_TD' ),
				'content' => sprintf(
					'<p>%s</p><p>%s</p>',
					esc_html__( 'This invitation could not be found or is no longer available.', 'CP_TD' ),
					esc_html__( 'Please contact us if you believe this to be an error.', 'CP_TD' )
				),
			);
		}

		$vp_args = wp_parse_args( $args, $vp_args );

		return $vp_args;
	}

	public static function modal_view() {
		$invite_data = CoursePress_Data_Facilitator::is_course_invite();
		?>
		<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="facilitator-verified">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invitation activated.', 'CP_TD' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'Congratulations. You are now a facilitator of this course. ', 'CP_TD' ); ?></p>
			</div>
			<div class="bbm-modal__bottombar">
				<a href="<?php echo esc_url( get_permalink( $invite_data->course_id ) ); ?>" class="bbm-button button"><?php esc_html_e( 'Continue...', 'CP_TD' ); ?></a>
			</div>
		</script>

		<script type="text/template" id="modal-view5-template" data-type="modal-step" data-modal-action="verification-failed">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invalid invitation.', 'CP_TD' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'This invitation link is not associated with your email address.', 'CP_TD' ); ?></p>
				<p><?php esc_html_e( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'CP_TD' ); ?></p>
			</div>
			<div class="bbm-modal__bottombar">
				<a href="<?php echo esc_url( get_permalink( $invite_data->course_id ) ); ?>" class="bbm-button button"><?php esc_html_e( 'Continue...', 'CP_TD' ); ?></a>
			</div>
		</script>
		<?php
	}
}
