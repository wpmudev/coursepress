<?php
/*
  Template Name: Contact Page
 */

/**
 * The template for displaying contact page
 *
 * Alternatevelly, you may use shortocode to display contact form like this [CONTACT_FORM]
 *
 * @package CoursePress
 */
get_header();
?>

<div id="primary" class="content-area content-side-area">
    <main id="main" class="site-main" role="main">

        <?php while ( have_posts() ) : the_post(); ?>

            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header><!-- .entry-header -->

            <div class="entry-content">
                <?php the_content(); ?>
                <?php echo do_shortcode( '[contact_form]' ); ?>
            </div><!-- .entry-content -->

        <?php endwhile; // end of the loop. ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>