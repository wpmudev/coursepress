<?php
//echo do_shortcode( '[course_breadcrumbs type="unit_archive"]' );
do_shortcode( '[course_units_loop]' ); //required for getting unit results
?>

<?php
do_shortcode( '[course_unit_archive_submenu]' );
?>
<div class="units-archive">
    <ul class="units-archive-list">
        <?php if ( have_posts( ) ) : ?>
            <?php
            while ( have_posts( ) ) : the_post( );
                $additional_class = '';
                $additional_li_class = '';

                if ( do_shortcode( '[course_unit_details field="is_unit_available"]' ) == false ) {
                    $additional_class = 'locked-unit';
                    $additional_li_class = 'li-locked-unit';
                }
                ?>
                <li class="<?php echo $additional_li_class; ?>">
                    <div class='<?php echo $additional_class; ?>'></div>
                    <span class="percentage"><?php echo do_shortcode( '[course_unit_details field="percent"]' ); ?>%</span><a href="<?php echo do_shortcode( '[course_unit_details field="permalink" unit_id="' . get_the_ID( ) . '"]' ); ?>" rel="bookmark"><?php the_title( ); ?></a>
                    <?php if ( do_shortcode( '[course_unit_details field="input_modules_count"]' ) > 0 ) { ?>
                        <span class="unit-archive-single-module-status"><?php echo do_shortcode( '[course_unit_details field="student_module_responses"]' ); ?> <?php _e( 'of', 'coursepress' ); ?> <?php echo do_shortcode( '[course_unit_details field="input_modules_count"]' ); ?> <?php _e( 'elements completed', 'coursepress' ); ?></span>
                    <?php } else { ?>
                        <span class="unit-archive-single-module-status read-only-module"><?php _e( 'Read-only' ); ?></span>
                    <?php } ?>
                </li>
                <?php
            endwhile;
        endif;
        ?>
    </ul>
</div>