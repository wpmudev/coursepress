<?php

class CoursePress_View_Front_General {

	public static function init() {
		// CoursePress Menus
		if ( cp_is_true( CoursePress_Core::get_setting( 'general/show_coursepress_menu', 1 ) ) ) {

			/**
			 * Create CoursePress basic menus automatically.
			 *
			 * @since 1.0.0
			 */
			add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'main_navigation_links' ), 10, 2 );
		}
		/**
		 * Handle coustom endpoints
		 *
		 * @since 2.0.5
		 */
		add_filter( 'wp_nav_menu_objects', array( __CLASS__, 'handle_custom_endpoints' ), 10, 2 );
	}

	public static function main_navigation_links( $sorted_menu_items, $args ) {
		$current_url = CoursePress_Helper_Utility::get_current_url();

		$theme_location = 'primary';
		if ( ! has_nav_menu( $theme_location ) ) {
			$theme_locations = get_nav_menu_locations();
			foreach ( (array) $theme_locations as $key => $location ) {
				$theme_location = $key;
				break;
			}
		}

		if ( $args->theme_location == $theme_location ) {
			// Put extra menu items only in primary (most likely header) menu.
			$is_in = is_user_logged_in();

			$courses = new stdClass();

			$courses->title = __( 'Courses', 'CP_TD' );
			$courses->description = '';
			$courses->menu_item_parent = 0;
			$courses->ID = 'cp-courses';
			$courses->db_id = '';
			$courses->classes = array();
			$courses->url = CoursePress_Core::get_slug( 'courses', true );
			if ( $current_url == $courses->url ) {
				$courses->classes[] = 'current_page_item';
			}
			$sorted_menu_items[] = $courses;

			/* Student Dashboard page */

			if ( $is_in ) {
				$dashboard = new stdClass();

				$dashboard->title = __( 'Dashboard', 'CP_TD' );
				$dashboard->description = '';
				$dashboard->menu_item_parent = 0;
				$dashboard->ID = 'cp-dashboard';
				$dashboard->db_id = - 9998;
				$dashboard->url = CoursePress_Core::get_slug( 'student_dashboard', true );
				$dashboard->classes[] = 'dropdown';
				/*
				if ( $current_url == $dashboard->url ) {
					$dashboard->classes[] = 'current_page_item';
				}
				*/
				$sorted_menu_items[] = $dashboard;

				/* Student Dashboard > Courses page */

				$dashboard_courses = new stdClass();

				$dashboard_courses->title = __( 'My Courses', 'CP_TD' );
				$dashboard_courses->description = '';
				$dashboard_courses->menu_item_parent = - 9998;
				$dashboard_courses->ID = 'cp-dashboard-courses';
				$dashboard_courses->db_id = '';
				$dashboard_courses->classes = array();
				$dashboard_courses->url = CoursePress_Core::get_slug( 'student_dashboard', true );
				if ( $current_url == $dashboard_courses->url ) {
					$dashboard_courses->classes[] = 'current_page_item';
				}
				$sorted_menu_items[] = $dashboard_courses;

				/* Student Dashboard > Settings page */

				$settings_profile = new stdClass();

				$settings_profile->title = __( 'My Profile', 'CP_TD' );
				$settings_profile->description = '';
				$settings_profile->menu_item_parent = - 9998;
				$settings_profile->ID = 'cp-dashboard-settings';
				$settings_profile->db_id = '';
				$settings_profile->classes = array();
				$settings_profile->url = CoursePress_Core::get_slug( 'student_settings', true );
				if ( $current_url == $settings_profile->url ) {
					$settings_profile->classes[] = 'current_page_item';
				}
				$sorted_menu_items[] = $settings_profile;

				/*
				Inbox */
				// if ( get_option( 'show_messaging', 0 ) == 1 ) {
				// $unread_count = cp_messaging_get_unread_messages_count();
				// if ( $unread_count > 0 ) {
				// $unread_count = ' (' . $unread_count . ')';
				// } else {
				// $unread_count = '';
				// }
				// $settings_inbox = new stdClass;
				//
				// $settings_inbox->title = __( 'Inbox', 'CP_TD' ) . $unread_count;
				// $settings_inbox->description = '';
				// $settings_inbox->menu_item_parent = - 9998;
				// $settings_inbox->ID = 'cp-dashboard-inbox';
				// $settings_inbox->db_id = '';
				// $settings_inbox->url = $this->get_inbox_slug( true );
				// if ( cp_curPageURL() == $settings_inbox->url ) {
				// $settings_profile->classes[] = 'current_page_item';
				// }
				// $sorted_menu_items[] = $settings_inbox;
				// }
			}

			/* Log in / Log out links */
			$login = new stdClass();
			if ( $is_in ) {
				$login->title = __( 'Log Out', 'CP_TD' );
			} else {
				$login->title = __( 'Log In', 'CP_TD' );
			}
			$login->description = '';
			$login->menu_item_parent = 0;
			$login->ID = 'cp-logout';
			$login->db_id = '';
			$login->url = self::get_log_in_out_link();
			$login->classes = array();

			$sorted_menu_items[] = $login;
		}

		return $sorted_menu_items;
	}

	/**
	 * get login or logut link depend on settings.
	 *
	 * @since 2.0.5
	 *
	 * @return string Login/logout URL.
	 */
	public static function get_log_in_out_link() {
		$use_custom = cp_is_true( CoursePress_Core::get_setting( 'general/use_custom_login', 1 ) );
		if ( is_user_logged_in() ) {
			return wp_logout_url();
		}
		if ( $use_custom ) {
			$login_page = CoursePress_Core::get_setting( 'pages/login', false );
			if ( empty( $login_page ) ) {
				return CoursePress_Core::get_slug( 'login', true );
			}
			return get_permalink( (int) $login_page );
		}
		$url = '';
		$course_id = CoursePress_Helper_Utility::the_course( true );
		if ( ! empty( $course_id ) ) {
			// Make sure the user returns to the course
			$url = CoursePress_Data_Course::get_course_url( $course_id );
		}
		return wp_login_url( $url );
	}

	/**
	 * Handle custom end points.
	 *
	 * See more https://developer.wordpress.org/reference/hooks/wp_nav_menu_objects/
	 *
	 * @since 2.0.5
	 *
	 * @param array $sorted_menu_items
	 * @param stdClass $args
	 */
	public static function handle_custom_endpoints( $sorted_menu_items, $args ) {
		$current_url = CoursePress_Helper_Utility::get_current_url();
		foreach ( $sorted_menu_items as $index => $item ) {
			/**
			 * next if not WP_Post
			 */
			if ( ! is_a( $item, 'WP_Post' ) ) {
				continue;
			}
			/**
			 * next if not custom
			 */
			if ( 'custom' != $item->type ) {
				continue;
			}
			switch ( $item->url ) {
				case '#coursepress-endpoints-login':
					if ( is_user_logged_in() ) {
						if ( __( 'Log In', 'CP_TD' ) == $item->title  ) {
							$item->title = __( 'Log Out', 'CP_TD' );
						}
					}
					$item->url = self::get_log_in_out_link();
				break;
				case '#coursepress-endpoints-courses':
					$item->url = CoursePress_Core::get_slug( 'courses', true );
				break;
				case '#coursepress-endpoints-dashboard':
					$item->url = CoursePress_Core::get_slug( 'student_dashboard', true );
				break;
				case '#coursepress-endpoints-profile':
					$item->url = CoursePress_Core::get_slug( 'student_settings', true );
				break;
			}
			if ( $current_url == $item->url ) {
				$item->classes[] = 'current_page_item';
			}
		}
		return $sorted_menu_items;
	}
}
