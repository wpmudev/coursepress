<?php
/**
 * The template use for user's dashboard.
 *
 * @since 3.0
 * @package CoursePress
 */
$user = coursepress_get_user();

get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'My Courses', 'cp' ); ?></h1>
				</header>

                <!-- @todo: Put student's courses here -->
			</div>
		</div>
	</div>

<?php get_footer();
