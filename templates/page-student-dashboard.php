<?php
/**
 * The template use for user's dashboard.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Courses', 'cp' ); ?></h1>
				</header>

				<?php
				// My courses template.
				coursepress_get_template( 'content', 'my-courses' );
				// Instructed courses.
				coursepress_get_template( 'content', 'instructed-courses' );
				// Facilitated courses.
				coursepress_get_template( 'content', 'facilitated-courses' );
				?>
			</div>
		</div>
	</div>

<?php
get_footer();
