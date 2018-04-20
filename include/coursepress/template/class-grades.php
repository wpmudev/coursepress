<?php

/**
 * Grades
 *
 * @since 2.0.5
 */

class CoursePress_Template_Grades {
	/**
	 * Render page.
	 *
	 * @since 2.0.5
	 */
	public static function render() {
		$course = CoursePress_Helper_Utility::the_course();
		$course_id = $course->ID;
		$content = '';
		$content .= do_shortcode( '[course_unit_submenu]' );
		$content .= '<div class="cp-student-grades">';
		/**
		 * table
		 */
		$content .= do_shortcode( '[student_grades_table]' );
		/**
		 * Total
		 */
		$content .= '<div class="total_grade">';
		$shortcode = sprintf( '[course_progress course_id="%d"]', $course_id );
		$content .= apply_filters( 'coursepress_grade_caption', __( 'Total:', 'coursepress' ) );
		$content .= ' ';
		$content .= do_shortcode( $shortcode );
		$content .= '%</div>';
		$content .= '</div>';
		return $content;
	}
}
