<?php

$student = new Student( get_current_user_id() );

$course_price = 0;

if ( is_user_logged_in() ) {

	if ( isset( $_POST['course_id'] ) && is_numeric( $_POST['course_id'] ) ) {

		check_admin_referer( 'enrollment_process' );

		$course_id   = (int) $_POST['course_id'];
		$course      = new Course( $course_id );
		$pass_errors = 0;

		global $coursepress;

		$is_paid = get_post_meta( $course_id, 'paid_course', true ) == 'on' ? true : false;

		if ( $is_paid && isset( $course->details->marketpress_product ) && $course->details->marketpress_product != '' && $coursepress->marketpress_active ) {
			$course_price = 1; //forces user to purchase course / show purchase form
			$course->is_user_purchased_course( $course->details->marketpress_product, get_current_user_ID() );
		}

		if ( $course->details->enroll_type == 'passcode' ) {
			if ( $_POST['passcode'] != $course->details->passcode ) {
				$pass_errors ++;
			}
		}

		if ( ! $student->user_enrolled_in_course( $course_id ) ) {
			if ( $pass_errors == 0 ) {
				if ( $course_price == 0 ) {//Course is FREE
					//Enroll student in
					if ( $student->enroll_in_course( $course_id ) ) {
						printf( __( 'Congratulations, you have successfully enrolled in "%s" course! Check your %s for more info.', 'cp' ), '<strong>' . $course->details->post_title . '</strong>', '<a href="' . $this->get_student_dashboard_slug( true ) . '">' . __( 'Dashboard', 'cp' ) . '</a>' );

					} else {
						_e( 'Something went wrong during the enrollment process. Please try again later.', 'cp' );
					}
				} else {
					if ( $course->is_user_purchased_course( $course->details->marketpress_product, get_current_user_ID() ) ) {
						//Enroll student in
						if ( $student->enroll_in_course( $course_id ) ) {
							printf( __( 'Congratulations, you have successfully enrolled in "%s" course! Check your %s for more info.', 'cp' ), '<strong>' . $course->details->post_title . '</strong>', '<a href="' . $this->get_student_dashboard_slug( true ) . '">' . __( 'Dashboard', 'cp' ) . '</a>' );
						} else {
							_e( 'Something went wrong during the enrollment process. Please try again later.', 'cp' );
						}
					} else {
						$course->show_purchase_form( $course->details->marketpress_product );
					}
				}
			} else {
				printf( __( 'Passcode is not valid. Please %s and try again.', 'cp' ), '<a href="' . esc_url( $course->get_permalink() ) . '">' . __( 'go back', 'cp' ) . '</a>' );

			}
		} else {
			// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
			// _e( 'You have already enrolled in the course.', 'cp' ); //can't enroll more than once to the same course at the time
			wp_redirect( trailingslashit( $course->get_permalink() ) . 'units' );
			exit;
		}
	} else {
		_e( 'Please select a course first you want to enroll in.', 'cp' );
	}
} else {
	_e( 'You must be logged in in order to complete the action', 'cp' );
}
?>