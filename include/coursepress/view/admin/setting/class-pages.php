<?php

require_once dirname( __FILE__ ) . '/class-settings.php';

class CoursePress_View_Admin_Setting_Pages extends CoursePress_View_Admin_Setting_Setting {

	public static function init() {

		add_action( 'coursepress_settings_process_pages', array( __CLASS__, 'process_form' ), 10, 2 );
		add_filter( 'coursepress_settings_render_tab_pages', array( __CLASS__, 'return_content' ), 10, 3 );
		add_filter( 'coursepress_settings_tabs', array( __CLASS__, 'add_tabs' ) );

	}

	public static function add_tabs( $tabs ) {

		self::$slug = 'pages';
		$tabs[ self::$slug ] = array(
			'title' => __( 'Pages', 'coursepress' ),
			'description' => __( 'Configure the pages for CoursePress.', 'coursepress' ),
			'order' => 1,
		);

		return $tabs;

	}

	public static function return_content( $content, $slug, $tab ) {

		$page_dropdowns = array();

		$pages_args = array(
			'selected' => CoursePress_Core::get_setting( 'pages/enrollment', 0 ),
			'echo' => 0,
			'show_option_none' => __( '&mdash; Select &mdash;', 'coursepress' ),
			'option_none_value' => 0,
			'name' => 'coursepress_settings[pages][enrollment]',
		);
		$page_dropdowns['enrollment'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/login', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][login]';
		$page_dropdowns['login'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/signup', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][signup]';
		$page_dropdowns['signup'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/student_dashboard', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][student_dashboard]';
		$page_dropdowns['student_dashboard'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/student_settings', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][student_settings]';
		$page_dropdowns['student_settings'] = wp_dropdown_pages( $pages_args );

		$pages_args['selected'] = CoursePress_Core::get_setting( 'pages/instructor', 0 );
		$pages_args['name'] = 'coursepress_settings[pages][instructor]';
		$page_dropdowns['instructor'] = wp_dropdown_pages( $pages_args );

		$content = '';

		$content .= self::page_start( $slug, $tab );
		$content .= self::table_start();

		/**
		 * Student Dashboard
		 */
		$content .= self::row(
			__( 'Student Dashboard', 'coursepress' ),
			$page_dropdowns['student_dashboard'],
			__( 'Select a page where students can view courses.', 'coursepress' )
		);

		/**
		 * Student Settings
		 */
		$content .= self::row(
			__( 'Student Settings', 'coursepress' ),
			$page_dropdowns['student_settings'],
			__( 'Select a page where students can change their account settings.', 'coursepress' )
		);

		/**
		 * login
		 */
		$content .= self::row(
			__( 'Login', 'coursepress' ),
			$page_dropdowns['login'],
			__( 'Select a page where students can login.', 'coursepress' )
		);

		/**
		 * Signup
		 */
		$content .= self::row(
			__( 'Signup', 'coursepress' ),
			$page_dropdowns['signup'],
			__( 'Select a page where students can create an account.', 'coursepress' )
		);

		/**
		 * Instructor.
		 */
		$content .= self::row(
			__( 'Instructor', 'coursepress' ),
			$page_dropdowns['instructor'],
			__( 'Select a page where we display the instructor profile.', 'coursepress' )
		);

		/**
		 * Enrollment
		 */
		$content .= self::row(
			__( 'Enrollment Process Page', 'coursepress' ),
			$page_dropdowns['enrollment'],
			sprintf( __( 'Select a page where we display the enrollment process.', 'coursepress' ) . '</a>' )
		);
		$content .= self::table_end();
		return $content;

	}
}
