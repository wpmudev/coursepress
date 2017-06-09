<?php
/**
 * The template use for course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="wrap coursepress-wrap">
		<div class="container">
			<main class="site-main" role="main">
				<article id="post-<?php get_the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="page-title"><?php _e( 'Course', 'cp' ); ?></h1>
						<h2 class="entry-title course-title"><?php echo coursepress_get_the_title(); ?></h2>
					</header>

					<div class="entry-content">
						<?php echo apply_filters( 'the_content', coursepress_get_description() ); ?>
					</div>

					<div class="course-structure">
                        <h3 class="sub-title course-sub-title"><?php _e( 'Course Structure', 'cp' ); ?></h3>
						<?php echo coursepress_get_course_structure(); ?>
					</div>
				</article>
			</main>
		</div>
	</div>

<?php get_footer();
