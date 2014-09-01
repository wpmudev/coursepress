<?php
/**
 * The units archive / grades template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode('[get_parent_course_id]');
$progress = do_shortcode('[course_progress course_id="' . $course_id . '"]');

//redirect to the parent course page if not enrolled
$coursepress->check_access($course_id);

add_thickbox();
?>

<?php
do_shortcode('[course_unit_archive_submenu]');
if( 100 == (int) $progress) {
	$complete_message = sprintf( '<span class="unit-archive-course-complete">%s %s</span>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', 'cp' ) );
}			
echo sprintf( '<h2>%s %s</h2>', __('Workbook', 'cp'), $complete_message);
?>

<div class="clearfix"></div>

<?php
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
		$input_module_count = do_shortcode('[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]');
		$has_assessable = $input_module_count  > 0 ? true : false;
        ?>
        <div class="workbook_units">
            <div class="unit_title">
                <h3><?php the_title(); ?>
                    <span><?php echo do_shortcode('[course_unit_progress course_id="' . $course_id . '" unit_id="' . get_the_ID() . '"]'); ?>% <?php _e('completed', 'cp');?></span>
                </h3>
            </div>
			<?php if ( $has_assessable ) { ?>
            <div class="accordion-inner">
                <?php 
						echo do_shortcode('[student_workbook_table]');
				?>
            </div>
			<?php } else { ?>
            <div class="accordion-inner">
				<div class="zero-inputs"><?php _e('There are no activities to complete in this unit.', 'cp'); ?></div>
            </div>
			<?php } ?>
        </div>
        <?php
    } // While
} else {
    ?>
    <div class="zero-courses"><?php _e('0 Units in the course', 'cp'); ?></div>
    <?php
}
?>