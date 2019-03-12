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


$discussion = coursepress_get_discussion( $course );
if ( ! empty( $discussion ) ) {
?>
    <h1 class="cp-page-title cp-page-title-discussion"><?php _e( 'Discussion:', 'cp' ); ?> <?php echo esc_html( $discussion->post_title ); ?></h1>
    <div class="cp-page-meta">
        <b><?php esc_html_e( 'Started by:', 'cp' ); ?></b> <?php echo get_the_author_meta( 'display_name', $discussion->post_author ); ?>
        | <?php echo get_the_date( '', $discussion->ID ); ?>
        | <b><?php esc_html_e( 'Applies to:', 'cp' ); ?></b> <?php echo coursepress_get_course_title(); ?>
    </div>
<?php } else { ?>
                    <h1 class="cp-page-title"><?php _e( 'Discussion:', 'cp' ); ?> <?php echo coursepress_get_course_title(); ?></h1>
<?php } ?>
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
