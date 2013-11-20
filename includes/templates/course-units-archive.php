<?php
echo do_shortcode('[course_breadcrumbs type="unit_archive"]');
//echo do_shortcode('[course_units]');
do_shortcode('[course_units_loop]');//required for getting unit results
?>
<ol>
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post();?>
            <li><a href="<?php echo do_shortcode('[course_unit_details field="permalink" unit_id="'.get_the_ID().'"]'); ?>" rel="bookmark"><?php the_title(); ?></a></li>
            <?php
        endwhile;
    endif;
    ?>
</ol>
<?php
do_shortcode('[course_discussion]');

?>