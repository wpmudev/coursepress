<?php
/**
 * The template use for course discussion.
 *
 * @since 3.0
 * @package CoursePress
 */

get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Discussions', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>
				</header>

				<!-- @todo: Put discussion here -->
			</div>
		</div>
	</div>

<?php get_footer();
