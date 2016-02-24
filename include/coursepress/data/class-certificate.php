<?php

/**
 * @copyright Incsub ( http://incsub.com/ )
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 ( GPL-2.0 )
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
 */

/**
 * Helper class for working with CoursePress Certificate.
 *
 * @since 2.0.0
 *
 * @return object
 */
class CoursePress_Data_Certificate {

	/**
	 * Static init.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		/**
		 * Allow to do something with email data.
		 *
		 * @since 2.0.0
		 *
		 * @param string $email_address Recipient email.
		 * @param string $subject Email subject.
		 * @param string $message Email message.
		 * @param array $email_args Email arguments see more for this array
		 * content in ./includes/classes/class.student.php
		 */
		add_action( 'coursepress_student_course_completed', array( $this, 'send_certificate' ), 10, 4 );

	}

	/**
	 * Send certificate to student
	 *
	 *
	 * @since 2.0.0
	 */
	public function send_certificate( $student_id, $course_id, $post_title, $course_id ) {

		// If student doesn't exist, exit.
		$student = get_userdata( $student_id );
		if ( empty( $student ) ) {
			return false;
		}

		$email_args = array();
		$email_args['email_type'] = 'basic_certificate';
		$email_args['course_id'] = $course_id;
		$email_args['email'] = sanitize_email( $student->user_email );
		$email_args['first_name'] = $student->user_firstname;
		$email_args['last_name'] = $student->user_lastname;

		CoursePress_Helper_Email::send_email( $email_args );

	}

}

