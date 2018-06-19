<?php
/**
 * The template use for course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header();

global $coursepress_course;
$user = coursepress_get_user();
$is_enrolled = $user->is_enrolled_at( $coursepress_course->ID );
?>
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
				$slug = 'overview';
				if ( $is_enrolled ) {
					$slug .= '-enrolled';
				}
				coursepress_get_template( 'course', $slug );
				?>
			</div>
		</div>
	</div>

<?php
get_footer();
