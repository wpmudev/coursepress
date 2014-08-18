<?php
do_shortcode('[course_units_loop]'); //required for getting unit results
?>

<?php
do_shortcode('[course_unit_archive_submenu]');
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
                    <?php do_shortcode('[module_status format="true"]'); ?>
                </li>
                <?php
            endwhile;
        endif;
        ?>
    </ul>
</div>