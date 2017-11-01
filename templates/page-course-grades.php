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

                <?php coursepress_render( 'templates/content-grades', array( 'student' => $student, 'course' => $course ) ); ?>
            </div>
        </div>
    </div>
<?php get_footer();
