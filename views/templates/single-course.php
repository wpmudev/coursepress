<?php
/**
 * The template use to show course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="wrap">
		<main role="main" class="site-main">
			<div class="container">
				<section class="course-info">
					<header class="entry-header">
						<h1 class="entry-title course-title">
							<?php echo coursepress_get_the_title(); ?>
						</h1>
					</header>

					<div class="entry-content">
						<?php
							echo apply_filters( 'the_content', coursepress_get_description() );
						?>
					</div>

					<div class="course-structure">
						<?php echo coursepress_get_course_structure(); ?>
					</div>
				</section>
			</div>
		</main>
	</div>

<?php get_footer();
