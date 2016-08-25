<?php
/**
 * The Template for displaying all single posts.
 *
 * @package CoursePress
 */
global $coursepress;

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        while ( have_posts() ) : the_post();
            $course_id = get_post_meta( get_the_ID(), 'course_id', true );
            $coursepress->check_access( $course_id );
            ?>
            <h1><?php echo do_shortcode( '[course_title course_id="' . $course_id . '"]' ); ?></h1>
            <div class="instructors-content">
                <?php echo do_shortcode( '[course_instructors style="list-flat" course_id="' . $course_id . '"]' ); ?>
            </div>
            
            <?php
            echo do_shortcode( '[course_unit_archive_submenu course_id="' . $course_id . '"]' );
            ?>

            <div class="clearfix"></div>

            <?php get_template_part( 'content-discussion', 'single' ); ?>

            <?php coursepress_post_nav(); ?>

            <?php
            // If comments are open or we have at least one comment, load up the comment template
            /* if ( comments_open() || '0' != get_comments_number() ) :
              comments_template();
              endif; */
            ?>


        <?php endwhile; // end of the loop.  ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar( 'footer' ); ?>
<?php get_footer(); ?>