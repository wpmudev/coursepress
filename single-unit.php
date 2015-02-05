<?php
/**
 * The Template for displaying single unit posts with modules
 *
 * @package CoursePress
 */
global $coursepress, $wp, $wp_query;

$course_id = do_shortcode('[get_parent_course_id]');

add_thickbox();

$paged = ! empty( $wp->query_vars['paged'] ) ? absint($wp->query_vars['paged']) : 1;
//redirect to the parent course page if not enrolled or not preview unit/page
while ( have_posts() ) : the_post();
    $coursepress->check_access($course_id, get_the_ID());
endwhile;

get_header();

$post = $unit->details;
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h3 class="entry-title"><?php echo do_shortcode('[course_title course_id="' . $course_id . '"]'); ?></h3>
                    <?php
                    //echo do_shortcode('[course_unit_details unit_id="' . get_the_ID() . '" field="parent_course"]');
                    ?>
                </header><!-- .entry-header -->
                <div class="instructors-content"></div>
                <?php
                echo do_shortcode('[course_unit_archive_submenu course_id="' . $course_id . '"]');
                ?>

                <div class="clearfix"></div>

                <?php echo do_shortcode('[course_unit_details unit_id="' . get_the_ID() . '" field="unit_page_title" unit_title="'.get_the_title().'"]'); ?>

                <div class="entry-content">
                    <?php
                    if ( $paged == 1 ) { //Unit introduction will be shown only on the first page
                        the_content();
                    }
                    ?>
                </div>
                <?php
                Unit_Module::get_modules_front($unit->details->ID);
                ?>
            </article>
        <?php endwhile; // end of the loop. ?>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>
