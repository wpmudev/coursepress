<?php
/**
 * The units archive template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode( '[get_parent_course_id]' );

//redirect to the parent course page if not enrolled
$coursepress->check_access( $course_id );

get_header( );
?>
<script>
    jQuery( function( $ ) {
        jQuery( ".knob" ).knob( );
    } );
</script>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php echo do_shortcode( '[course_details field="post_title" course_id="' . $course_id . '"]' ); ?></h1>

        <div class="instructors-content">
            <?php echo do_shortcode( '[course_instructors list="true" course_id="' . $course_id . '"]' ); ?>
        </div>

        <?php
        do_shortcode( '[course_unit_archive_submenu]' );
        ?>

        <div class="clearfix"></div>

        <ul class="units-archive-list">
            <?php if ( have_posts( ) ) { ?>
                <?php
                while ( have_posts( ) ) {
                    the_post( );

                    $additional_class = '';
                    $additional_li_class = '';

                    $is_unit_available = do_shortcode( '[course_unit_details field="is_unit_available"]' );

                    if ( $is_unit_available == false ) {
                        $additional_class = 'locked-unit';
                        $additional_li_class = 'li-locked-unit';
                    }

                    $input_modules_count = do_shortcode( '[course_unit_details field="input_modules_count"]' );
                    $assessable_input_modules_count = do_shortcode( '[course_unit_details field="assessable_input_modules_count"]' );
                    ?>
                    <li class="<?php echo $additional_li_class; ?>">
                        <div class='<?php echo $additional_class; ?>'></div>
                        <div class="unit-archive-single">

                            <?php if ( $assessable_input_modules_count > 0 ) { ?>
                                <a class="tooltip" alt="<?php _e( 'Percent of the unit completion', 'coursepress' ); ?>">
                                    <input class="knob" data-fgColor="#24bde6" data-bgColor="#e0e6eb" data-thickness=".35" data-width="70" data-height="70" data-readOnly=true value="<?php echo do_shortcode( '[course_unit_details field="percent"]' ); ?>">
                                </a>
                            <?php } ?>
                            <a class="unit-archive-single-title" href="<?php the_permalink( ); ?>" rel="bookmark"><?php the_title( ); ?></a>
                            <?php if ( $input_modules_count > 0 ) { ?>
                                <span class="unit-archive-single-module-status"><?php if ( $is_unit_available ) {
                        echo do_shortcode( '[course_unit_details field="student_module_responses" additional="mandatory"]' ); ?> <?php _e( 'of', 'coursepress' ); ?> <?php echo do_shortcode( '[course_unit_details field="mandatory_input_modules_count"]' ); ?> <?php _e( 'mandatory elements completed', 'coursepress' );
                } else {
                    echo __( 'Available', 'coursepress' ) . ' ' . date( get_option( 'date_format' ), strtotime( do_shortcode( '[course_unit_details field="unit_availability"]' ) ) );
                } ?></span>
                    <?php } else { ?>
                                <span class="unit-archive-single-module-status"><?php if ( $is_unit_available ) {
                            _e( 'Read-only' );
                        } else {
                            echo __( 'Available', 'coursepress' ) . ' ' . date( get_option( 'date_format' ), strtotime( do_shortcode( '[course_unit_details field="unit_availability"]' ) ) );
                        } ?></span>
                    <?php } ?>
                        </div>
                    </li>
        <?php
    }
} else {
    ?>
                <h1 class="zero-course-units"><?php _e( "0 units in the course currently. Please check back later." ); ?></h1>
    <?php
}
?>
        </ul>
    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar( 'footer' ); ?>
<?php get_footer( ); ?>