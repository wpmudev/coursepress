<?php
/**
 * The template use for student's course grade.
 *
 * @since 3.0
 * @package CoursePress
 */
$student = coursepress_get_user();
get_header(); ?>

	<div class="coursepress-wrap">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Grades', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>
					<?php
					/**
					 * To override course submenu template to your theme or a child-theme,
					 * create a template `course-submenu.php` and it will be loaded instead.
					 *
					 * @since 3.0
					 */
					coursepress_get_template( 'course', 'submenu' );
					?>
				</header>
				<div class="cp-student-grades">
					<?php
					$course    = coursepress_get_course();
					$shortcode = sprintf( '[student_grades_table course_id="%d"]', $course->ID );
					echo do_shortcode( $shortcode );
					?>
					<div class="total_grade pull-right">
						<?php
						$shortcode = sprintf( '[course_progress course_id="%d"]', $course->ID );
						echo apply_filters( 'coursepress_grade_caption', __( 'Total:', 'cp' ) );
						printf( ' %1$s%', do_shortcode( $shortcode ) );
						?>%
					</div>
				</div>
			</div>
		</div>
	</div>
<?php get_footer();
