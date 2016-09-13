<?php if ( is_user_logged_in() ) { ?>
	<?php

	global $coursepress;
	$student         = new Student( get_current_user_id() );
	$student_courses = $student->get_enrolled_courses_ids();
	?>
	<div class="student-dashboard-wrapper">
		<?php

		// Instructor Course List
		$show = 'dates,class_size';

		$course_list = do_shortcode( '[course_list instructor="' . get_current_user_id() . '" instructor_msg="" status="all" title_tag="h1" title_class="h1-title" list_wrapper_before="" show_divider="yes"  left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_link="no" show="' . $show . '" show_title="no" admin_links="true" show_button="no" show_media="no"]' );


		$show_random_courses = true;

		if ( ! empty( $course_list ) ) {
			echo '<div class="dashboard-managed-courses-list">';
			echo '<h1 class="title managed-courses-title">' . __( 'Courses you manage:', 'cp' ) . '</h1>';
			echo '<div class="course-list course-list-managed course course-student-dashboard">';
			echo $course_list;
			echo '</div>';
			echo '</div>';
			echo '<div class="clearfix"></div>';
		}

		// Add some random courses.
		$course_list = do_shortcode( '[course_list student="' . $student->ID . '" student_msg="" course_status="incomplete" list_wrapper_before="" class="course course-student-dashboard" left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_class="h1-title" title_link="no" show_media="no"]' );

		if ( empty( $course_list ) && $show_random_courses ) {

			//Random Courses
			echo '<div class="dashboard-random-courses-list">';
			echo '<h3 class="title suggested-courses">' . __( 'You are not enrolled in any courses.', 'cp' ) . '</h3>';
			_e( 'Here are a few to help you get started:', 'cp' );
			echo '<hr />';
			echo '<div class="dashboard-random-courses">' . do_shortcode( '[course_random number="3" featured_title="" media_type="image"]' ) . '</div>';
			echo '</div>';
		} else {
			// Course List
			echo '<div class="dashboard-current-courses-list">';
			echo '<h1 class="title enrolled-courses-title current-courses-title">' . __( 'Your current courses:', 'cp' ) . '</h1>';
			echo '<div class="course-list course-list-current course course-student-dashboard">';
			echo $course_list;
			echo '</div>';
			echo '</div>';
			echo '<div class="clearfix"></div>';
		}

		// Completed courses
		$show        = 'dates,class_size';
		$course_list = do_shortcode( '[course_list student="' . $student->ID . '" student_msg="" course_status="completed" list_wrapper_before="" title_link="no" title_tag="h1" title_class="h1-title" show_divider="yes" left_class="enroll-box-left" right_class="enroll-box-right"]' );

		if ( ! empty( $course_list ) ) {
			// Course List
			echo '<div class="dashboard-completed-courses-list">';
			echo '<h1 class="title completed-courses-title">' . __( 'Completed courses:', 'cp' ) . '</h1>';
			echo '<div class="course-list course-list-completed course course-student-dashboard">';
			echo $course_list;
			echo '</div>';
			echo '</div>';
			echo '<div class="clearfix"></div>';
		}
		?>
	</div>  <!-- student-dashboard-wrapper -->
<?php
} else {
	//ob_start();
	// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
	wp_redirect( get_option( 'use_custom_login_form', 1 ) ? CoursePress::instance()->get_signup_slug( true ) : wp_login_url() );
	exit;
}