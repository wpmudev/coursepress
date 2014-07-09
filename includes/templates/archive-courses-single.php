<?php
global $post;

$course = new Course( get_the_ID() );
$course_language = $course->details->course_language;
?>

<?php
$course_thumbnail = $course->get_course_thumbnail();
if ( $course_thumbnail ) {
    ?>
    <figure>
        <img src="<?php echo $course_thumbnail; ?>">
    </figure>
    <?php
} else {
    $extended_class = 'quick-course-info-extended';
}
?>

<div class="instructors-content">
    <?php echo do_shortcode( '[course_instructors list="true" link="true"]' ); ?>
</div>

<div class="course-excerpt">
    <?php echo do_shortcode( $post->post_excerpt ); ?>
</div>

<div class="quick-course-info <?php echo ( isset( $extended_class ) ? $extended_class : '' ); ?>">
    <span class="course-time"><?php echo do_shortcode( '[course_start label=""]' ); ?></span>
    <?php if ( isset( $course_language ) && $course_language !== '' ) { ?>
        <span class="course-lang"><?php echo do_shortcode( '[course_language label=""]' ); ?></span>
    <?php } ?>
    <a class="go-to-course-button" href="<?php the_permalink(); ?>"><?php _e( 'Go to Course', 'coursepress' ); ?></a>
</div>
