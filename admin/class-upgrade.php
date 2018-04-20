<?php
/**
 * Course Certificates Page
 * Display and manages the generated certificates.
 **/
class CoursePress_Admin_Upgrade extends CoursePress_Admin_Controller_Menu {

	var $parent_slug = 'coursepress';
	var $slug = 'coursepress_upgrade';
	protected $cap = 'coursepress_settings_cap';

	public function init() {
		$coursepress_courses_need_update = get_option( 'coursepress_courses_need_update', false );
		if ( 'yes' == $coursepress_courses_need_update ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
	}

	public function get_labels() {
		$coursepress_courses_need_update = get_option( 'coursepress_courses_need_update', 'no' );
		if ( 'yes' == $coursepress_courses_need_update ) {
			$this->init();
			return array(
				'title' => __( 'CoursePress Upgrade', 'coursepress' ),
				'menu_title' => __( 'Upgrade', 'coursepress' ),
			);
		}
		return array();
	}

	/**
	 * Enqueue script, but only on upgrade page.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		$re = sprintf( '/_page_%s$/', 'coursepress_upgrade' );
		if ( ! preg_match( $re, $screen->id ) ) {
			return;
		}
		$script = CoursePress::$url . 'asset/js/admin-upgrade.js';
		wp_enqueue_script( 'coursepress_admin_upgrade_js', $script, array( 'jquery' ), CoursePress::$version, true );
	}
}
