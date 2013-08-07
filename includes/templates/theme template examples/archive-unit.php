<?php
/**
 * The EXAMPLE template for displaying Archive units.
 * If you want to use custom archive page for units, just put this file within your theme directory
 * If you want to add more unit details to this page, check [course_unit_details] shortcode in plugin Settings > Shortcodes
 */
get_header();
?>

<section id="primary" class="site-content">
    <div id="content" role="main">
        <header class="entry-header">
            <h1 class="entry-title">
                <?php _e('Course Units', 'cp');?>
            </h1>
        </header><!-- .entry-header -->
        <ol>
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <li><a href="<?php echo do_shortcode('[course_unit_details field="permalink" unit_id="'.get_the_ID().'"]'); ?>" rel="bookmark"><?php the_title(); ?></a></li>
                    <?php
                endwhile;
            endif;
            ?>
        </ol>
    </div><!-- #content -->
</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>