<?php
/**
 * The template use to print course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
$course = coursepress_get_course();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'course-overview' ); ?>>
    <header class="page-header">
        <h1 class="page-title"><?php _e( 'Course', 'cp' ); ?></h1>
        <h2 class="entry-title"><?php echo coursepress_get_course_title(); ?></h2>
    </header>

    <div class="course-details">
<?php
$media = coursepress_get_course_media( false, 420, 220 );
if ( ! empty( $media ) ) {
?>
		<div class="course-media">
			<?php echo $media; ?>
        </div>
<?php } ?>
		<div class="course-metas">
			<p class="course-meta">
				<span class="meta-title"><?php _e( 'Availability', 'cp' ); ?>: </span>
				<span class="course-meta-dates"><?php echo coursepress_get_course_availability_dates(); ?></span>
			</p>
			<p class="course-meta">
				<span class="meta-title"><?php _e( 'Enrollment', 'cp' ); ?>: </span>
				<span class="course-meta-enrollment-dates"><?php echo coursepress_get_course_enrollment_dates(); ?></span>
			</p>
            <p class="course-meta">
                <span class="meta-title"><?php _e( 'Language', 'cp' ); ?>:</span>
                <span class="course-meta course-meta-language"><?php echo $course->get_course_language(); ?></span>
            </p>
<?php
$price = $course->get_course_cost();
if ( ! empty( $price ) ) {
?>
            <p class="course-meta">
                <span class="meta-title"><?php _e( 'Price', 'cp' ); ?>:</span>
                <span class="course-meta course-meta-price"><?php echo $price; ?></span>
            </p>
<?php
}
?>
            <p class="course-button">
                <?php coursepress_get_course_enrollment_button(); ?>
            </p>
		</div>
	</div>

	<div class="additional-summary">
		<div class="social-shares">
			<?php echo do_shortcode( '[course_social_links course_id="' . $course->ID . '"]' ); ?>
		</div>
	</div>

	<div class="entry-content course-description">
		<?php echo apply_filters( 'the_content', coursepress_get_course_description() ); ?>
	</div>

	<div class="course-structure">
		<h3 class="sub-title course-sub-title"><?php _e( 'Course Structure', 'cp' ); ?></h3>
		<?php echo coursepress_get_course_structure(); ?>
	</div>
</article>
