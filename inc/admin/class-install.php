<?php
/**
 * Class CoursePress_Install
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Install {
	protected $cp;

	public function __construct( CoursePress $cp ) {
		$this->cp = $cp;
	}

	public function install() {
		/**
		 * install tables
		 */
		$this->install_tables();
		// Run legacy
		$this->run_legacy();
	}

	public function install_tables() {
		$option_name = 'coursepress_tables';
		$installed = get_option( $option_name, 'not installed' );
		if ( 'not installed' === $installed ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$this->create_student_table();
			$this->create_student_progress_table();
			add_option( $option_name, 'installed', '', 'no' );
		}
	}

	private function create_student_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'coursepress_students';
		$sql = "CREATE TABLE $table_name (
            ID BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            course_id BIGINT NOT NULL,
            student_id BIGINT NOT NULL
        ) $charset_collate;";
		dbDelta( $sql );
	}

	private function create_student_progress_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'coursepress_student_progress';
		$sql = "CREATE TABLE $table_name (
            ID BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            course_id BIGINT NOT NULL,
            student_id BIGINT NOT NULL,
            progress LONGTEXT
        ) $charset_collate;";
		dbDelta( $sql );
	}

	public function run_legacy() {
		global $cp_coursepress;
		if ( ! $cp_coursepress instanceof CoursePress ) {
			$cp_coursepress = $this->cp;
		}
		// Run the core
		$this->cp->load_core();
		$this->cp->get_class( 'CoursePress_Legacy' );
	}
}
