<?php if ( is_user_logged_in() ) { ?>
    <?php
	    global $coursepress;
	    $student = new Student( get_current_user_id() );
	    $student_courses = $student->get_enrolled_courses_ids();
    ?>
	
	<?php
	
		// Instructor Course List
		$show = 'dates,class_size';
		$course_list = do_shortcode('[course_list instructor="' . get_current_user_id() . '" instructor_msg="" status="all" title_column="left" title_tag="h4" show_divider="yes" class="course course-student-dashboard" left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_link="no" show="' . $show . '" show_title="no" admin_links="true" show_button="no" show_media="no"]');

		if ( ! empty( $course_list ) ) {
			echo __('<h1 class="title">You manage the following courses:</h1>', 'cp');
			echo '<hr />' . $course_list;
			echo '<div class="clearfix" />';
		}
		
		// Course List
		echo __('<h2 class="title enrolled-courses-title">You are enrolled in the following courses:</h2>', 'cp');
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
