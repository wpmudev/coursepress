<?php
/**
 * The template use to show student's per course notifications.
 *
 * @since 3.0
 * @package CoursePress
 */
$course = coursepress_get_course();
$student = coursepress_get_student();

get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Notifications', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>
				</header>

				<!-- @todo: Put student's notification -->
			</div>
		</div>
	</div>
<?php get_footer();
