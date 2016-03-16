<?php

class CoursePress_View_Admin_Course_Student {

	public static function render() {
		/**
		 * Student List
		 */
		$list_course = new CoursePress_Helper_Table_CourseStudent();

		$list_course->set_course( (int) $_GET['id'] );
		if ( CoursePress_Data_Capabilities::can_add_course_student( $list_course->get_course_id() ) ) {
			$list_course->set_add_new( true );
		}
		$list_course->prepare_items();

		$content = '<div class="coursepress_Course_Student_wrapper">';

		ob_start();
		$list_course->display();
		$content .= ob_get_clean();

		$content .= '</div>';

		/**
		 * Invite Student
		 */
		if ( CoursePress_Data_Capabilities::can_assign_course_student( $list_course->get_course_id() ) ) {
			$nonce = wp_create_nonce( 'invite_student' );
			$content .= '<div class="coursepress_course_invite_student_wrapper">';
			$content .= '<h3>' . esc_html__( 'Invite Student', 'CP_TD' ) .'</h3>';
			$content .= '<label class="invite-firstname"><span>' . esc_html__( 'First Name', 'CP_TD' ) . '</span><input type="text" name="invite-firstname"></label>';
			$content .= '<label class="invite-lastname"><span>' . esc_html__( 'Last Name', 'CP_TD' ) . '</span><input type="text" name="invite-lastname"></label>';
			$content .= '<label class="invite-email"><span>' . esc_html__( 'E-mail', 'CP_TD' ) . '</span><input type="text" name="invite-email"></label>';
			$content .= '<div class="invite-submit button button-primary" name="invite-submit" data-nonce="' . $nonce . '">' . esc_html__( 'Invite', 'CP_TD' ) . '</div>';
			$content .= '</div>';
		}

		return $content;
	}
}
