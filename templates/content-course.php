<?php
/**
 * The template use for courses list.
 *
 * @since 3.0
 * @package CoursePress
 *
 * @var $post CoursePress_Course
 */
global $post;
$course = coursepress_get_course( $post );
$thumbnail = $course->get_feature_image( 'full' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'course-item-box' ); ?>>
	<div class="course-info">
		<header class="entry-header course-entry-header">
			<?php the_title( '<h3 class="entry-title course-title"><a href="' . esc_url( $course->get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
		</header>
<?php if ( ! empty( $thumbnail ) ) {
	printf( '<a href="%s" class="post-thumbnail" aria-hidden="true">%s</a>', esc_url( $course->get_permalink() ), $thumbnail );
} ?>
        <div class="entry-content">
            <div class="course-description">
                <?php echo coursepress_get_course_summary( false, 180 ); ?>
            </div>
            <?php if ( ( $instructors = $course->get_instructors_link() ) ) : ?>
                <div class="course-instructors entry-meta">
                    <strong><?php echo _nx( 'Instructor:', 'Instructors:', count( $instructors ), 'Before instructors list on course details page.', 'cp' ); ?></strong>
                    <?php echo implode( ', ', $instructors ); ?>
                </div>
            <?php endif; ?>
        </div>
        <footer class="entry-footer">
            <div class="entry-meta course-metas">
                <span class="course-meta course-meta-start-date"><?php echo $course->get_course_start_date(); ?></span>
                <span class="course-meta course-meta-language"><?php echo $course->get_course_language(); ?></span>
                <span class="course-meta course-meta-cost"><?php echo $course->get_course_cost(); ?></span>
            </div>
        </footer>
	</div>
</article>
