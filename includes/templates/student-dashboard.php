<?php if ( is_user_logged_in() ) { ?>
    <?php
	    global $coursepress;
	    $student = new Student( get_current_user_id() );
	    $student_courses = $student->get_enrolled_courses_ids();
    ?>
	
	<?php
		// Course List
		echo do_shortcode('[course_list student="' . $student->ID . '" class="course course-student-dashboard" left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_class="h1-title" title_link="no" show_media="yes"]');
	?>
	
    <?php
} else {
    //ob_start();
	// if( defined('DOING_AJAX') && DOING_AJAX ) { cp_write_log('doing ajax'); }
    wp_redirect( get_option('use_custom_login_form', 1) ? CoursePress::instance()->get_signup_slug( true ) : wp_login_url() );
    exit;
}
?>
