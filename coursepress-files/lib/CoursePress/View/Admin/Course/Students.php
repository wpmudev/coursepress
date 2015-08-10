<?php

class CoursePress_View_Admin_Course_Students {



	public static function render() {

		$courseListTable = new CoursePress_Helper_Table_CourseStudents();
		$courseListTable->prepare_items();

		$content = '<div class="coursepress_course_students_wrapper">' .
		ob_start();
		$courseListTable->display();
		$content .= ob_get_clean();

		$content .= '</div>';

		return $content;
	}




}