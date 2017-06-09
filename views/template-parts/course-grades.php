<?php
/**
 * The template use for course grades.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="wrap coursepress-wrap">
		<div class="container">
			<main class="site-main" role="main">
				<header class="entry-header">
					<h1 class="page-title"><?php _e( 'Grades', 'cp' ); ?></h1>
					<h2 class="entry-title course-title"><?php echo coursepress_get_the_title(); ?></h2>
				</header>

				<div class="entry-content">
				</div>
			</main>
		</div>
	</div>

<?php get_footer();
