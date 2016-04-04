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

		add_filter( 'the_content', array( __CLASS__, 'the_content_show_instructor' ) );
		add_filter( 'the_title', array( __CLASS__, 'display_instructor_name' ), 10, 2 );
	}

	public static function render_instructor_page() {
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

		if ( ! array_key_exists( 'instructor_username', $wp->query_vars ) ) {
			return;
		}

		$username = sanitize_text_field( $wp->query_vars['instructor_username'] );
		$instructor = CoursePress_Data_Instructor::instructor_by_login( $username );
		if ( empty( $instructor ) ) {
			$instructor = CoursePress_Data_Instructor::instructor_by_hash( $username );
		}
		if ( empty( $instructor ) ) {
			$wp->set_query_var( 'error', 404 );
			$wp->set_query_var( 'page_id', false );
		}
		self::$last_instructor = empty( $instructor ) ? 0 : $instructor->ID;

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
		$course_invite = CoursePress_Data_Instructor::is_course_invite();

		if ( $course_invite ) {

			$is_verified = CoursePress_Data_Instructor::verify_invitation_code( $course_invite->course_id, $course_invite->code, $course_invite->invitation_data );
			$vp_args = array(
				'slug' => 'instructor_verification' . $course_invite->course_id,
				'type' => CoursePress_Data_Course::get_post_type_name() . '_archive',
				'is_page' => true,
			);

			if ( $is_verified ) {

				if ( ! is_user_logged_in() ) {

					add_filter( 'coursepress_localize_object', array( 'CoursePress_Data_Instructor', 'invitation_data' ) );
					add_action( 'wp_footer', array( __CLASS__, 'modal_view' ) );
					$vp_args = $_vp_args;

				} else {
					$user_id = get_current_user_id();
					$is_added = CoursePress_Data_Instructor::add_from_invitation( $course_invite->course_id, $user_id, $course_invite->code );

					if ( $is_added ) {
						$main_course = apply_filters( 'coursepress_view_course', CoursePress_View_Front_Course::render_course_main(), $course_invite->course_id, 'main' );
						$args = array(
							'show_title' => true,
							'title' => get_the_title( $course_invite->course_id ),
							'content' => sprintf( '<h3>%s</h3><p>%s</p>%s',
								esc_html__( 'Invitation activated.', 'CP_TD' ),
								esc_html__( 'Congratulations. You are now an instructor of this course. ', 'CP_TD' ),
								$main_course
							),
						);
						$vp_args = wp_parse_args( $args, $vp_args );
					} else {
						$args = array(
							'show_title' => false,
							'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p>',
								esc_html__( 'Invalid invitation.', 'CP_TD' ),
								esc_html__( 'This invitation link is not associated with your email address.', 'CP_TD' ),
								esc_html__( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'CP_TD' )
							),
						);
						$vp_args = wp_parse_args( $args, $vp_args );
					}
				}
			} else {
				$args = array(
					'show_title' => false,
					'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p>',
						esc_html__( 'Invitation not found.', 'CP_TD' ),
						esc_html__( 'This invitation could not be found or is no longer available.', 'CP_TD' ),
						esc_html__( 'Please contact us if you believe this to be an error.', 'CP_TD' )
					),
				);
				$vp_args = wp_parse_args( $args, $vp_args );
			}
		} else {
			$vp_args = $_vp_args;
		}

		return $vp_args;
	}

	public static function modal_view() {
		$invite_data = CoursePress_Data_Instructor::is_course_invite();
		?>
		<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="instructor-verified">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invitation activated.', 'CP_TD' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'Congratulations. You are now an instructor of this course. ', 'CP_TD' ); ?></p>
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

	/**
	 * Display instructor.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @global WP_Post * $post The WP_Post object.

	 * @param string $content Current entry content.
	 *
	 * @return string Current entry content.
	 */

	public static function the_content_show_instructor( $content ) {
		/**
		 * we do not need change other post type than page
		 */
		if ( ! is_page() ) {
			return $content;
		}
		/**
		 * check setup is pages/student_settings a page?
		 */
		$page_id = CoursePress_Core::get_setting( 'pages/instructor', 0 );
		if ( empty( $page_id ) ) {
			return $content;
		}
		/**
		 * check current page
		 */
		global $post;
		if ( $page_id != $post->ID ) {
			return $content;
		}
		$content .= self::render_instructor_page();
		return $content;
	}


	/**
	 * Display instructor.
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Current entry title.
	 *
	 * @return string Current entry title.
	 */

	public static function display_instructor_name( $title, $post_id ) {
		/**
		 * we do not need change other post type than page
		 */
		if ( ! is_page() ) {
			return $title;
		}
		/**
		 * check setup is pages/student_settings a page?
		 */
		$page_id = CoursePress_Core::get_setting( 'pages/instructor', 0 );
		if ( empty( $page_id ) ) {
			return $title;
		}
		/**
		 * check current page
		 */
		if ( $page_id != $post_id ) {
			return $title;
		}
		$title = ! empty( self::$last_instructor ) ? CoursePress_Helper_Utility::get_user_name( self::$last_instructor, false, false ) : __( 'Instructor not found.', 'CP_TD' );
		return $title;
	}
}
