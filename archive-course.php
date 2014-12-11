<?php
/**
 * The template for displaying Course ( archive )
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package CoursePress
 */
get_header();
?>

<section id="primary" class="content-area content-side-area">
    <main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<!--<h1 class="page-title">
				<?php _e( 'Courses', 'cp' ); ?>
				</h1>-->
				<?php
				// Show an optional term description.
				$term_description = term_description();

				if ( !empty( $term_description ) ) :
					printf( '<div class="taxonomy-description">%s</div>', $term_description );
				endif;
				?>
			</header><!-- .page-header -->

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
				// $status = the_post();
				if ( 'publish' != get_post_status() ) {
					continue;
				}
				get_template_part( 'content-course' );
				?>

			<?php endwhile; ?>

			<?php cp_numeric_posts_nav( 'navigation-pagination' ); ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

    </main><!-- #main -->
</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>