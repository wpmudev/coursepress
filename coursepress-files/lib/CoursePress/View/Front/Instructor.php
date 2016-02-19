<?php

class CoursePress_View_Front_Instructor {

	public static $discussion = false;  // Used for hooking discussion filters
	public static $title = ''; // The page title
	public static $last_instructor;

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

	}

	public static function render_instructor_page() {
		if ( $theme_file = locate_template( array( 'instructor-single.php' ) ) ) {
		} else {
			// wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'instructor-single.php' ) ) ) {// add custom content in the single template ONLY if the post type doesn't already has its own template
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
				$content = __( 'The requested instuctor does not exists', CoursePress::TD );
			}

			self::$last_instructor = empty( $instructor ) ? 0 : $instructor->ID;

			$page_title = ! empty( self::$last_instructor ) ? CoursePress_Helper_Utility::get_user_name( self::$last_instructor, false, false ) : __( 'Instructor not found.', CoursePress::TD );
			$args = array(
				'slug' => 'instructor_' . self::$last_instructor,
				'title' => $page_title,
				// 'show_title' => false,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_instructor_page(),
				'type' => 'coursepress_instructor',
			);

			$pg = new CoursePress_Data_VirtualPage( $args );

			return;

		}
	}
}
