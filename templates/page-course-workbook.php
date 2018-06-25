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
<?php
/**
 * To override course submenu template to your theme or a child-theme,
 * create a template `course-submenu.php` and it will be loaded instead.
 *
 * @since 3.0
 */
coursepress_get_template( 'course', 'submenu' );
coursepress_breadcrumb();
?>
					<h1 class="cp-page-title"><?php _e( 'My Workbook', 'cp' ); ?></h1>
                </header>
<div class="coursepress-content-wrapper">
                <?php
				coursepress_render( 'templates/content-workbook', array(
					'user_id' => 0,
					'course_id' => 0,
				) );
				?>
</div>
			</div>
		</div>
	</div>
<?php
get_footer();
