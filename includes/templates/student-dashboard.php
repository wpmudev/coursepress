<?php if ( is_user_logged_in() ) { ?>
    <?php
    global $coursepress;
    $student = new Student( get_current_user_id() );
    $student_courses = $student->get_enrolled_courses_ids();
    
    foreach ( $student_courses as $course_id ) {
        $course = new Course( $course_id );
        $course_details = $course->get_course();
        ?>

        <div class="course course-student-dashboard">

            <div class="enroll-box">
                <h3 class="h1-title"><?php echo $course_details->post_title; ?></h3>
                <div class="enroll-box-left">
                    <div class="course-box">
						<?php echo do_shortcode( '[course_dates course_id="' . $course->details->ID . '"]' ); ?>
						<?php echo do_shortcode( '[course_enrollment_dates course_id="' . $course->details->ID . '"]' ); ?>
						<?php echo do_shortcode( '[course_class_size course_id="' . $course->details->ID . '"]' ); ?>
						<?php echo do_shortcode( '[course_cost course_id="' . $course->details->ID . '"]' ); ?>
                    </div><!--course-box-->
                </div><!--enroll-box-left-->

                <div class="enroll-box-right">
                    <form name="enrollment-process" method="post" action="<?php echo trailingslashit( site_url() . '/' . get_option( 'enrollment_process_slug', 'enrollment-process' ) ); ?>">
                        <div class="apply-box">
							<?php echo do_shortcode( '[course_join_button course_id="' . $course->details->ID . '"]' ); ?>
							<?php echo do_shortcode( '[course_action_links course_id="' . $course->details->ID . '"]' ); ?>
                        </div>
                    </form>
                </div>

            </div>

        </div>


        <?php
    }
    if ( count( $student_courses ) == 0 ) {
        printf( __( 'You have not yet enrolled in a course. Browse courses %s', 'cp' ), '<a target="_blank" href="'.trailingslashit( site_url() . '/' . $coursepress->get_course_slug() ).'">'.__( 'here', 'cp' ).'</a>' );
    }
    ?>
    <?php
} else {
    //ob_start();
    wp_redirect( wp_login_url() );
    exit;
}
?>
