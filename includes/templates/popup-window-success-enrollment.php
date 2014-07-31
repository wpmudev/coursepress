<div class="cp_popup_title"><?php _e('Congratulations', 'cp'); ?></div>
<?php
global $coursepress;
$course_id = $args['course_id'];
$course = new Course($course_id);
$dashboard_link = '<a href="' . $coursepress->get_student_dashboard_slug(true) . '">' . __('Dashboard', 'cp') . '</a>';
$course_link = '<a href="' . get_permalink($course_id) . '">' . $course->details->post_title . '</a>';
?>
<div class="cp_popup_success_message">
    <?php echo sprintf(__('You have successfully enrolled in %s', 'cp'), $course_link); ?>
    <br />
    <?php
    _e('You will receive an e-mail confirmation shortly.');
    ?>
    <br /><br />
    <?php echo sprintf(__('You course will be available at any time in your %s', 'cp'), $dashboard_link); ?>
</div>