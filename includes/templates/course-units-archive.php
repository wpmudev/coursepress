<?php
$course_id = do_shortcode('[get_parent_course_id]');
$progress = do_shortcode('[course_progress course_id="' . $course_id . '"]');
do_shortcode('[course_units_loop]'); //required for getting unit results
?>

<?php
do_shortcode('[course_unit_archive_submenu]');
$complete_message = '';
if( 100 == (int) $progress) {
	$complete_message = sprintf( '<div class="unit-archive-course-complete cp-wrap">%s %s</div>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', 'cp' ) );
}
echo __('<h2>Course Units ' . $complete_message . '</h2>', 'cp');
?>
<div class="units-archive">
    <ul class="units-archive-list">
        <?php if ( have_posts() ) : ?>
            <?php
            while ( have_posts() ) : the_post();
                $additional_class = '';
                $additional_li_class = '';

                if ( do_shortcode('[course_unit_details field="is_unit_available"]') == false ) {
                    $additional_class = 'locked-unit';
                    $additional_li_class = 'li-locked-unit';
                }
                ?>
                <li class="<?php echo $additional_li_class; ?>">
                    <div class='<?php echo $additional_class; ?>'></div>
                    <a href="<?php echo do_shortcode('[course_unit_details field="permalink" last_visited="true" unit_id="' . get_the_ID() . '"]'); ?>" rel="bookmark"><?php the_title(); ?></a><?php echo do_shortcode('[course_unit_details field="percent" format="true" style="flat"]'); ?>
                    <?php do_shortcode('[module_status format="true" course_id="' . $course_id . '" unit_id="' . get_the_ID() . '"]'); ?>
                </li>
                <?php
            endwhile;
        endif;
        ?>
    </ul>
</div>