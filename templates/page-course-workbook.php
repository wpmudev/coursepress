<?php
/**
 * The template use to show student's course workbook.
 *
 * @since 3.0
 * @package CoursePress
 */
$user   = coursepress_get_user();
$course = coursepress_get_course();
get_header(); ?>

	<div class="coursepress-wrap course-unit">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Workbook', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>

					<div class="course-unit-progress">
						<?php
						echo coursepress_progress_wheel(
							array(
								'class'      => 'per-course-progress',
								'data-value' => $user->get_course_progress( $course->ID ),
								'data-size'  => 62,
							)
						);
						?>
					</div>
				</header>

				<?php
				/**
				 * To override course submenu template to your theme or a child-theme,
				 * create a template `course-submenu.php` and it will be loaded instead.
				 *
				 * @since 3.0
				 */
				coursepress_get_template( 'course', 'submenu' );
				?>

				<?php coursepress_render( 'templates/content-workbook', array( 'user_id' => 0, 'course_id' => 0 ) ); ?>
			</div>
		</div>
	</div>
<?php get_footer();
