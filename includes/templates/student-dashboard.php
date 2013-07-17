<?php if (current_user_can('student')) { ?>
    <?php
    $student = new Student(get_current_user_id());
    $student_courses = $student->get_enrolled_courses_ids();

    foreach ($student_courses as $course_id) {
        $course = new Course($course_id);
        $course_details = $course->get_course();
        ?>

        <div class="course">

            <div class="enroll-box">
                <h3><?php echo $course_details->post_title; ?></h3>
                <div class="enroll-box-left">
                    <div class="course-box">
                        <span class="strong"><?php _e('Course Dates: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="course_start_date" course_id="' . $course_details->ID . '"]') . ' - ' . do_shortcode('[course_details field="course_end_date" course_id="' . $course_details->ID . '"]'); ?><br />
                        <span class="strong"><?php _e('Enrollment Dates: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="enrollment_start_date" course_id="' . $course_details->ID . '"]') . ' - ' . do_shortcode('[course_details field="enrollment_end_date" course_id="' . $course_details->ID . '"]'); ?><br />
                        <span class="strong"><?php _e('Class Size: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="class_size" course_id="' . $course_details->ID . '"]'); ?><br />
                        <span class="strong"><?php _e('Price: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="price" course_id="' . $course_details->ID . '"]'); ?>
                    </div></div>

                <div class="enroll-box-right"><form name="enrollment-process" method="post" action="<?php echo do_shortcode('[courses_urls url="enrollment-process" course_id="' . $course_details->ID . '"]'); ?>">
                        <div class="apply-box">
                            <?php echo do_shortcode('[course_details field="button" course_id="' . $course_details->ID . '"]'); ?>
                            <div class="apply-links"><a href="?unenroll=<?php echo $course_details->ID; ?>" onClick="return unenroll();">Un-enroll</a> | <a href="<?php echo get_permalink($course_details->ID);?>">Course Details</a></div>
                        </div>
                    </form>
                </div>

            </div>

        </div><div class="devider"></div>

        <?php
    }
    if(count($student_courses) == 0){
        _e('You have not yet enrolled in a course.', 'cp');
    }
    ?>
<?php
} else {
    //_e('You don\'t have required permissions to access this page', 'cp');
}
?>
