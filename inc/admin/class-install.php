<?php

/**
 * Class CoursePress_Install
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Install {
	public function __construct() {
		$this->create_student_table();
		$this->create_student_progress_table();
	}

	function create_student_table() {
		global $wpdb;

		$table = $wpdb->prefix . 'coursepress_students';
		$sql = "CREATE TABLE IF NOT EXISTS `$table`(
			ID BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
			course_id BIGINT NOT NULL,
			student_id BIGINT NOT NULL
		)";
		$wpdb->query( $sql );
	}

	function create_student_progress_table() {
		global $wpdb;

		$table = $wpdb->prefix . 'coursepress_student_progress';
		$sql = "CREATE TABLE IF NOT EXISTS `$table` (
			ID BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
			course_id BIGINT NOT NULL,
			student_id BIGINT NOT NULL,
			progress LONGTEXT
		)";
		$wpdb->query( $sql );
	}
}