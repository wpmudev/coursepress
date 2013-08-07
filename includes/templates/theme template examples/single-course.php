<?php
/**
 * The EXAMPLE Template for displaying all single courses.
 * If you want to use custom single page for courses, just put this file within your theme directory
 * If you want to add more course details to this page, check [course_details] shortcode in plugin Settings > Shortcodes
 */
get_header();
?>

<div id="primary" class="site-content">
    <div id="content" role="main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php while (have_posts()) : the_post(); ?>

                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <div class="entry-summary">
                    <?php
                    the_excerpt();
                    ?>
                </div>

                <div class="entry-content">

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
                    </div><!--/instructors-box-->

                    <div class="devider"></div>

                    <div class="enroll-box">

                        <div class="enroll-box-left">
                            <div class="course-box">
                                <span class="strong"><?php _e('Course Dates: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="course_start_date"]') . ' - ' . do_shortcode('[course_details field="course_end_date"]'); ?><br />
                                <span class="strong"><?php _e('Enrollment Dates: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="enrollment_start_date"]') . ' - ' . do_shortcode('[course_details field="enrollment_end_date"]'); ?><br />
                                <span class="strong"><?php _e('Class Size: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="class_size"]'); ?><br />
                                <span class="strong"><?php _e('Who can Enroll: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="enroll_type"]'); ?><br />
                                <span class="strong"><?php _e('Price: ', 'cp'); ?></span><?php echo do_shortcode('[course_details field="price"]'); ?>
                            </div>
                        </div><!--/enroll-box-left-->

                        <div class="enroll-box-right">
                            <div class="apply-box">
                                <?php echo do_shortcode('[course_details field="button"]'); ?>
                            </div>
                        </div><!--/enroll-box-right-->

                    </div><!--/enroll-box-->

                    <div class="devider"></div>

                    <?php the_content(); ?>

                </div><!--/entry-content-->

            <?php endwhile; // end of the loop. ?>
        </article>
    </div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>