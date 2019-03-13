<?php
/**
 * Class CoursePress_Menu
 */
class CoursePress_Menu extends CoursePress_Utility {
	var $menu_location = 'primary';

	private $current = null;

	public function __construct() {

		// Only assign our custom menu if it is enabled.
		$is_menu_enabled = coursepress_is_true( coursepress_get_setting( 'general/show_coursepress_menu', 1 ) );
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

	/**
	 * Check current menu and set $current property.
	 *
	 * @since 3.0.0
	 */
	private function compare_url( $url ) {
		if ( empty( $this->current ) ) {
			global $wp;
			$this->current = home_url( $wp->request );
		}
		if ( $url === $this->current ) {
			return true;
		}
		$url = trim( $url, '/' );
		if ( $url === $this->current ) {
			return true;
		}
		return false;
	}

	public function maybe_setup_menu( $menu_items, $args ) {
		global $wp;
		if ( $args->theme_location != $this->menu_location ) {
			return $menu_items;
		}

		// Add main CP menu
		$menu = $this->get_menu_object();
		$menu->title = __( 'Courses', 'cp' );
		$menu->url = coursepress_get_main_courses_url();
		$menu->ID = 'cp-courses';
		$menu->current = $this->compare_url( $menu->url );
		array_push( $menu_items, $menu );
		$parent_id = time();
		// If current user is logged in, set dashboard
		if ( is_user_logged_in() ) {
			/**
			 * Dashboard
			 */
			$dashboard_menu = $this->get_menu_object();
			$dashboard_menu->title = __( 'Dashboard', 'cp' );
			$dashboard_menu->ID = 'cp-dashboard';
			$dashboard_menu->db_id = $parent_id;
			$dashboard_menu->url = coursepress_get_dashboard_url();
			$dashboard_menu->current = $this->compare_url( $dashboard_menu->url );
			array_push( $menu_items, $dashboard_menu );
			/**
			 * My Dashboard
			 */
			$my_dashboard = $this->get_menu_object();
			$my_dashboard->title = __( 'My Dashboard', 'cp' );
			$my_dashboard->ID = 'cp-my-dashboard';
			$my_dashboard->url = $dashboard_menu->url;
			$my_dashboard->menu_item_parent = $parent_id;
			$my_dashboard->current = $this->compare_url( $dashboard_menu->url );
			array_push( $menu_items, $my_dashboard );
			/**
			 * My Profile
			 */
			$student_menu = $this->get_menu_object();
			$student_menu->title = __( 'My Profile', 'cp' );
			$student_menu->ID = 'cp-settings';
			$student_menu->menu_item_parent = $parent_id;
			$student_menu->url = coursepress_get_student_settings_url();
			$student_menu->current = $this->compare_url( $student_menu->url );
			array_push( $menu_items, $student_menu );
			/**
			 *  Logout
			 */
			$logout_menu = $this->get_menu_object();
			$logout_menu->title = __( 'Logout', 'cp' );
			$logout_menu->ID = 'cp-logout';
			$logout_menu->url = wp_logout_url( $menu->url );
			$logout_menu->current = $this->compare_url( $logout_menu->url );
			array_push( $menu_items, $logout_menu );
		} else {
			/**
			 * Add login menu
			 */
			$login_menu = $this->get_menu_object();
			$login_menu->title = __( 'Log In', 'cp' );
			$login_menu->ID = 'cp-login';
			$login_menu->url = coursepress_get_student_login_url( $menu->url );
			$login_menu->current = $this->compare_url( $login_menu->url );
			array_push( $menu_items, $login_menu );
		}
		return $menu_items;
	}
}
