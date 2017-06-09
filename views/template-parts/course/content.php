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
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'course-item-box' ); ?>>
	<div class="course-feature-image-container">
		<?php echo $post->get_feature_image( 280 ); ?>
	</div>
    <div class="course-info">
        <header class="entry-header course-entry-header">
            <?php the_title( '<h3 class="entry-title course-title"><a href="' . esc_url( $post->get_permalink() ) . '" rel="bookmark">', '</a></h3>' ); ?>
        </header>
        <div class="entry-content course-description">
            <?php echo $post->get_summary( 140 ); ?>
        </div>

        <?php if ( ( $instructors = $post->get_instructors_link() ) ) : ?>
            <div class="course-instructors">
                <strong><?php echo _n( 'Instructor', 'Instructors', count( $instructors ), 'cp' ); ?>: </strong>
                <?php echo implode( ', ', $instructors ); ?>
            </div>
        <?php endif; ?>

        <div class="course-metas">
            <span class="course-meta course-meta-start-date"><?php echo $post->get_course_start_date(); ?></span>
            <span class="course-meta course-meta-language"><?php echo $post->get_course_language(); ?></span>
            <span class="course-meta course-meta-cost"><?php echo $post->get_course_cost(); ?></span>
        </div>
    </div>
</article>