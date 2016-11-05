<?php
/**
 * Feedback email class
 *
 * Used to send instructors feedback to the student.
 **/
class CoursePress_Admin_FeedbackEmail extends CoursePress_Email {
	protected $email_type = 'instructor_module_feedback';

	public function default_email_fields() {
		return array(
			'subject' => __( 'New Feedback', 'CP_TD' ),
			'content' => sprintf( __(
				'
				Howdy %s %s,

				A new feedback is given by your instructor at %s in %s at %s

				%s says
				%s

				Cheers,
				%s
				%s
				', 'CP_TD' ),
				'FIRST_NAME',
				'LAST_NAME',
				'COURSE_NAME',
				'CURRENT_UNIT',
				'CURRENT_MODULE',
				'INSTRUCTOR_FIRST_NAME INSTRUCTOR_LAST_NAME',
				'INSTRUCTOR_FEEDBACK',
				'BLOG_NAME',
				'BLOG_ADDRESS'
			),
		);
	}

	public function email_settings() {
		return array(
			'title' => __( 'Instructor Feedback', 'CP_TD' ),
			'description' => __( 'Template for sending instructor feedback to students.', 'CP_TD' )
		);
	}

	public function mail_tokens() {
		$mail_tokens = parent::mail_tokens();
		$mail_tokens += array(
			'COURSE_NAME', 'COURSE_ADDRESS', 'CURRENT_UNIT', 'CURRENT_MODULE',
			'INSTRUCTOR_FIRST_NAME', 'INSTRUCTOR_LAST_NAME', 'INSTRUCTOR_FEEDBACK',
			'COURSE_GRADE'
		);

		return $mail_tokens;
	}

	public function send_feedback( $course_id, $unit_id, $module_id, $student_id, $feedback_text ) {
		$vars = array_merge(
			$this->prepare_tokens(),
			$this->prepare_course_tokens( $course_id ),
			$this->prepare_user_tokens( $student_id )
		);

		// Generate unit object
		$unit = get_post( $unit_id );
		$vars['CURRENT_UNIT'] = $unit->post_title;

		// Generate module object
		$module = get_post( $module_id );
		$vars['CURRENT_MODULE'] = $module->post_title;

		// Get course grade
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		$course_grade = CoursePress_Helper_Utility::get_array_val(
			$student_progress,
			'completion/average'
		);
		$vars['COURSE_GRADE'] = $course_grade;

		// Instructor
		$instructor = get_userdata( get_current_user_id() );
		$vars['INSTRUCTOR_FIRST_NAME'] = $instructor->first_name;
		$vars['INSTRUCTOR_LAST_NAME'] = $instructor->last_name;
		$vars['INSTRUCTOR_FEEDBACK'] = $feedback_text;

		$this->send_email(
			array(
				'email' => $vars['EMAIL']
			),
			$vars
		);
	}
}