<?php
/**
 * The template use for course completion.
 *
 * @since 3.0
 * @package CoursePress
 */
$completion = coursepress_get_user_course_completion_data();
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php echo coursepress_get_course_title(); ?></h1>
					<h2 class="entry-title"><?php echo $completion['title']; ?></h2>
				</header>
			</div>

			<div class="page-content">
				<?php echo apply_filters( 'the_content', $completion['content'] ); ?>
			</div>
		</div>
	</div>

<?php
get_footer();
