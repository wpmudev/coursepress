<?php
/**
 * The template use for course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area"<?php echo apply_filters( 'coursepress_schema', '', 'itemscope' ); ?>>
				<?php
				/**
				 * To override this template in your theme or a child theme,
				 * create a template `course-overview.php` and it will be loaded instead.
				 *
				 * @since 3.0
				 */
				coursepress_get_template( 'course', 'overview' );
				?>
			</div>
		</div>
	</div>

<?php get_footer();
