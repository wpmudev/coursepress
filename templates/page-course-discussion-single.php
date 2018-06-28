<?php
/**
 * The template use for course discussion.
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
                    <h1 class="cp-page-title"><?php _e( 'Discussion:', 'cp' ); ?> <?php echo coursepress_get_course_title(); ?></h1>
				</header>
				<?php
				$allowed = $course->__get( 'allow_discussion' );
				if ( ! $allowed ) :
					coursepress_render( 'templates/content-discussion-off' );
				else :
					coursepress_render( 'templates/content-discussion-single', array(
						'user_id' => 0,
						'course' => $course,
					) );
				endif;
				?>
			</div>
		</div>
	</div>
<?php
get_footer();
