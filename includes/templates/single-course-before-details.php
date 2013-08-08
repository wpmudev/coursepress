<?php
the_excerpt();
?>


<div class="instructors-box">
    <?php
    //Get instructors count for this course
    $instructors = do_shortcode('[course_instructors count="true"]');

    if ($instructors > 0) {
        if ($instructors >= 2) {
            ?>
            <h2><?php _e('About Instructors', 'cp'); ?></h2>
            <?php
        } else {
            ?>
            <h2><?php _e('About Instructor', 'cp'); ?></h2>
            <?php
        }
    }

    //List of instructors
    echo do_shortcode('[course_instructors]');
    ?>
</div><div class="devider"></div>

<div class="enroll-box">
    <div class="enroll-box-left">
        <div class="course-box">
            <span class="strong"><?php _e('Course Dates: ', 'cp'); ?></span><?php
            if (do_shortcode('[course_details field="course_start_date"]') == 'Open-ended') {
                _e('Open-ended', 'cp');
            } else {
                echo do_shortcode('[course_details field="course_start_date"]') . ' - ' . do_shortcode('[course_details field="course_end_date"]');
            }
            ?><br />
            <span class="strong"><?php _e('Enrollment Dates: ', 'cp'); ?></span><?php if (do_shortcode('[course_details field="enrollment_start_date"]') == 'Open-ended') {
                _e('Open-ended', 'cp');
            } else {
                echo do_shortcode('[course_details field="enrollment_start_date"]') . ' - ' . do_shortcode('[course_details field="enrollment_end_date"]');
            } ?><br />
            <span class="strong"><?php _e('Class Size: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="class_size"]'); ?><br />
            <span class="strong"><?php _e('Who can Enroll: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="enroll_type"]'); ?><br />
            <span class="strong"><?php _e('Price: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="price"]'); ?>
        </div></div>
    <div class="enroll-box-right">
        <div class="apply-box">
<?php echo do_shortcode('[course_details field="button"]'); ?>
        </div>
    </div>
</div>
<div class="devider"></div>