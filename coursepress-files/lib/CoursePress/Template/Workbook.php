<?php

class CoursePress_Template_Workbook {

	public static function render_workbook() {

		$course = CoursePress_Helper_Utility::the_course();
		$course_id = $course->ID;

		$content = '';
		$content .= do_shortcode( '[course_unit_submenu]' );

		$content .= do_shortcode( '[student_workbook_table]' );

		return $content;

	}



}