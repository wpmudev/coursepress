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
			'title' => __( 'Pages', 'CP_TD' ),
			'description' => __( 'Configure the pages for CoursePress.', 'CP_TD' ),
			'order' => 1,
		);

		return $tabs;

	}

	public static function return_content( $content, $slug, $tab ) {

		$page_dropdowns = array();

		$pages_args = array(
			'selected' => CoursePress_Core::get_setting( 'pages/enrollment', 0 ),
			'echo' => 0,
			'show_option_none' => __( '&mdash; Select &mdash;', 'CP_TD' ),
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
			__( 'Student Dashboard', 'CP_TD' ),
			$page_dropdowns['student_dashboard'],
			__( 'Select a page where students can view courses.', 'CP_TD' )
		);

		/**
		 * Student Settings
		 */
		$content .= self::row(
			__( 'Student Settings', 'CP_TD' ),
			$page_dropdowns['student_settings'],
			__( 'Select a page where students can change their account settings.', 'CP_TD' )
		);

		/**
		 * login
		 */
		$content .= self::row(
			__( 'Login', 'CP_TD' ),
			$page_dropdowns['login'],
			__( 'Select a page where students can login.', 'CP_TD' )
		);

		/**
		 * Signup
		 */
		$content .= self::row(
			__( 'Signup', 'CP_TD' ),
			$page_dropdowns['signup'],
			__( 'Select a page where students can create an account.', 'CP_TD' )
		);

		/**
		 * Instructor.
		 */
		$content .= self::row(
			__( 'Instructor', 'CP_TD' ),
			$page_dropdowns['instructor'],
			__( 'Select a page where we display the instructor profile.', 'CP_TD' )
		);

		/**
		 * Enrollment
		 */
		$content .= self::row(
			__( 'Enrollment Process Page', 'CP_TD' ),
			$page_dropdowns['enrollment'],
			sprintf( __( 'Select a page where we display the enrollment process.', 'CP_TD' ) . '</a>' )
		);
		$content .= self::table_end();
		return $content;

	}
}
