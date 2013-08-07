<?php
/**
 * The EXAMPLE template for displaying Archive courses.
 * If you want to use custom archive page for courses, just put this file within your theme directory
 * If you want to add more course details to this page, check [course_details] shortcode in plugin Settings > Shortcodes
 */
get_header();
?>

<section id="primary" class="site-content">
    <div id="content" role="main">
        <?php if (have_posts()) : ?>
            <?php
            /* Start the Loop */
            while (have_posts()) : the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title">
                            <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(sprintf(__('Permalink to %s', 'cp'), the_title_attribute('echo=0'))); ?>" rel="bookmark"><?php the_title(); ?></a>
                        </h1>
                    </header><!-- .entry-header -->

                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                        <?php echo do_shortcode('[course_details field="button"]'); ?>
                        <br /><br />
                    </div><!-- .entry-summary -->

                </article>
                <?php
            endwhile;
        endif;
        ?>


    </div><!-- #content -->
</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>