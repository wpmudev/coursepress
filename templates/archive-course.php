<?php
/**
 * The template use for course archive.
 *
 * @since 3.0
 * @package CoursePress
 */
get_header(); ?>
    <div class="coursepress-wrap">
        <div class="container">
            <div class="content-area">
                <header class="page-header">
                    <h1 class="page-title"><?php _e( 'All Courses', 'cp' ); ?></h1>
                </header>
<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		/**
		 * To override this template in your theme or to a child-theme,
		 * create a template `content-course.php` and it will be loaded instead.
		 *
		 * @since 3.0
		 */
		coursepress_get_template( 'content', 'course' );
	}
} else {
	_e( 'There is currently no course, please come back later.', 'cp' );
}
?>
            </div>
        </div>
    </div>
<?php get_footer();
