<?php
/**
 * The template use for course discussion.
 *
 * @since 3.0
 * @package CoursePress
 */
$user = coursepress_get_user();
$course = coursepress_get_course();
get_header(); ?>
	<div class="coursepress-wrap course-unit">
		<div class="container">
			<div class="content-area">
				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Discussions', 'cp' ); ?></h1>
					<h2 class="entry-title"><?php echo esc_html( coursepress_get_course_title() ); ?></h2>
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
				<?php

				$allowed = $course->__get( 'allow_discussion' );
				if ( ! $allowed ) :
					coursepress_render( 'templates/content-discussion-off' );
				else :
					coursepress_render( 'templates/content-discussion-form', array(
						'course' => $course,
						'id' => 0,
						'section' => 0,
					) );
				endif;
				?>
			</div>
		</div>
	</div>
<?php
get_footer();
