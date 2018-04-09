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
					<h1 class="page-title"><?php echo esc_html( coursepress_get_course_title() ); ?></h1>
					<h2 class="entry-title"><?php echo esc_html( $completion['title'] ); ?></h2>
					</div>
				</header>

				<div class="page-content">
					<?php $the_content = apply_filters( 'the_content', $completion['content'] ); ?>
					<?php echo esc_html( $the_content ); ?>
				</div>
			</div>
		</div>
	</div>

<?php
get_footer();
