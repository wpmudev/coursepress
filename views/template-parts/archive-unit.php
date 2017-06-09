<?php
/**
 * The template use for course's units archive.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="wrap coursepress-wrap">
		<div class="container">
			<main class="site-main" role="main">
				<article id="post-<?php the_ID();?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="page-title"><?php _e( 'Course', 'cp' ); ?></h1>
						<h2 class="entry-title course-title"><?php echo coursepress_get_the_title(); ?></h2>
					</header>

					<div class="course-submenu">

					</div>
				</article>
			</main>
		</div>
	</div>

<?php get_footer();
