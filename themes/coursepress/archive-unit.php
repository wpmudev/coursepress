<?php
/**
 * The units archive template file
 * 
 * @package CoursePress
 */
global $coursepress;
$course_id = do_shortcode('[get_parent_course_id]');

//redirect to the parent course page if not enrolled
$coursepress->check_access($course_id);

get_header();
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1><?php echo do_shortcode('[course_title course_id="' . $course_id . '"]'); ?></h1>

        <div class="instructors-content">
            <?php
            // Flat hyperlinked list of instructors
            echo do_shortcode('[course_instructors style="list-flat" link="true" course_id="' . $course_id . '"]');
            ?>
        </div>

        <?php
        do_shortcode('[course_unit_archive_submenu]');
        ?>

        <div class="clearfix"></div>

        <ul class="units-archive-list">
            <?php if ( have_posts() ) { ?>
                <?php
                while ( have_posts() ) {
                    the_post();

                    $additional_class = '';
                    $additional_li_class = '';

                    $is_unit_available = do_shortcode('[course_unit_details field="is_unit_available"]');

                    if ( $is_unit_available == false ) {
                        $additional_class = 'locked-unit';
                        $additional_li_class = 'li-locked-unit';
                    }

                    $input_modules_count = do_shortcode('[course_unit_details field="input_modules_count"]');
                    $assessable_input_modules_count = do_shortcode('[course_unit_details field="assessable_input_modules_count"]');
                    ?>
                    <li class="<?php echo $additional_li_class; ?>">
                        <div class='<?php echo $additional_class; ?>'></div>
                        <div class="unit-archive-single">
                            <?php echo do_shortcode('[course_unit_details field="percent" format="true" style="extended"]'); ?>
                            <a class="unit-archive-single-title" href="<?php echo do_shortcode('[course_unit_details field="permalink" last_visited="true" unit_id="' . get_the_ID() . '"]'); ?>" rel="bookmark"><?php the_title(); ?></a>
                            <?php do_shortcode('[module_status format="true"]'); ?>
                        </div>
                    </li>
                    <?php
                }
            } else {
                ?>
                <h1 class="zero-course-units"><?php _e("0 units in the course currently. Please check back later."); ?></h1>
                <?php
            }
            ?>
        </ul>
    </main><!-- #main -->
</div><!-- #primary -->
<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>