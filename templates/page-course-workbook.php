<?php
/**
 * The template use to show student's course workbook.
 *
 * @since 3.0
 * @package CoursePress
 */
$student = coursepress_get_student();
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Workbook', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>
				</header>

				<!-- @todo: Put student's workbook -->
			</div>
		</div>
	</div>
<?php get_footer();
