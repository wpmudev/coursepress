<?php
/**
 * The units archive / grades template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode('[get_parent_course_id]');

//redirect to the parent course page if not enrolled
$coursepress->check_access($course_id);

add_thickbox();
?>

<?php
do_shortcode('[course_unit_archive_submenu]');
echo __('<h2>Workbook</h2>', 'cp');
?>

<div class="clearfix"></div>

<?php
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        ?>
        <div class="workbook_units">
            <div class="unit_title">
                <h3><?php the_title(); ?>
                    <span><?php echo do_shortcode('[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]'); ?>% <?php _e('completed', 'cp');?></span>
                </h3>
            </div>
            <div class="accordion-inner">
                <?php echo do_shortcode('[student_workbook_table]');?>
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <div class="zero-courses"><?php _e('0 Units in the course', 'cp'); ?></div>
    <?php
}
?>