<?php
/**
 * Class CoursePress_Menu
 */
class CoursePress_Menu extends CoursePress_Utility {
	var $menu_location = 'primary';

	public function __construct() {

		// Only assign our custom menu if it is enabled.
		$is_menu_enabled = coursepress_is_true( coursepress_get_setting( 'general/show_coursepress_menu', 0 ) );
		if ( $is_menu_enabled ) {
			// Maybe set CP menu of one of the active menu
			add_action( 'init', array( $this, 'find_active_menu' ) );
			add_action( 'wp_nav_menu_objects', array( $this, 'maybe_setup_menu' ), 10, 2 );
		}
	}

	function find_active_menu() {
		$theme_location = 'primary';
		$has_menu = has_nav_menu( $theme_location );

		if ( ! $has_menu ) {
			$menus = get_nav_menu_locations();
			$menus = array_keys( $menus );

			foreach ( $menus as $menu ) {
				if ( $menu != $theme_location ) {
					$theme_location = $menu;
					break;
				}
			}
		}

		$this->menu_location = $theme_location;
	}

	function get_menu_object() {
		global $post;

		$menu = new stdClass();

		if ( $post ) {
			foreach ( $post as $key => $value ) {
				$menu->{$key} = $value;
			}
		}
		$menu->menu_item_parent = 0;
		$menu->description = '';
		$menu->object_id = 0;
		$menu->object = 'page';
		$menu->db_id = 0;
		$menu->type = 'post_type';
		$menu->type_label = '';
		$menu->url = '';
		$menu->ID = 0;
		$menu->title = '';
		$menu->target = '';
		$menu->attr_title = '';
		$menu->classes = array(
			'menu-item'
		);

		return $menu;
	}

	function maybe_setup_menu( $menu_items, $args ) {
		if ( $args->theme_location != $this->menu_location ) {
			return $menu_items;
		}

		// Add main CP menu
		$menu = $this->get_menu_object();
		$menu->title = __( 'Courses', 'cp' );
		$menu->url = coursepress_get_main_courses_url();
		$menu->ID = 'cp-courses-menu';

		array_push( $menu_items, $menu );

		// If current user is logged in, set dashboard
		if ( is_user_logged_in() ) {
			$dashboard_menu = $this->get_menu_object();
			$dashboard_menu->title = __( 'Dashboard', 'cp' );
			$dashboard_menu->ID = 'cp-dashboard';
			$dashboard_menu->db_id = 9998;
			$dashboard_menu->url = coursepress_get_dashboard_url();

			array_push( $menu_items, $dashboard_menu );

			$my_dashboard = $this->get_menu_object();
			$my_dashboard->title = __( 'My Dashboard', 'cp' );
			$my_dashboard->ID = 'cp-my-dashboard';
			$my_dashboard->url = $dashboard_menu->url;
			$my_dashboard->menu_item_parent = 9998;

			array_push( $menu_items, $my_dashboard );

			$student_menu = $this->get_menu_object();
			$student_menu->title = __( 'My Profile', 'cp' );
			$student_menu->ID = 'cp-settings';
			$student_menu->menu_item_parent = 9998;
			$student_menu->url = coursepress_get_student_settings_url();

			array_push( $menu_items, $student_menu );

			// Logout
			$logout_menu = $this->get_menu_object();
			$logout_menu->title = __( 'Logout', 'cp' );
			$logout_menu->url = wp_logout_url( $menu->url );
			array_push( $menu_items, $logout_menu );
		} else {
			// Add login menu
			$login_menu = $this->get_menu_object();
			$login_menu->title = __( 'Log In', 'cp' );
			$login_menu->url = wp_login_url( $menu->url );

			$use_custom_login = coursepress_get_setting( 'general/use_custom_login' );

			if ( $use_custom_login ) {
				$login_menu->url = coursepress_get_student_login_url();
			}
			array_push( $menu_items, $login_menu );
		}

		return $menu_items;
	}
}