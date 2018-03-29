<?php
/**
 * The template use to print course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
$course = coursepress_get_course();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?>>
    <header class="entry-header">
        <h1 class="entry-title"><?php echo coursepress_get_course_title(); ?></h1>
    </header>
    <div class="entry-content">
	<?php $messages = apply_filters( 'coursepress_overview_messages', array() ); ?>
	<?php if ( !empty( $messages ) ) { ?>
		<div class="cp-warning-box">
		<?php foreach ( $messages as $message ) { ?>
			<p><?php echo $message ?></p>
		<?php } ?>
		</div>
	<?php } ?>
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
				<span class="meta-title"><?php _e( 'Availability:', 'cp' ); ?></span>
				<span class="course-meta-dates"><?php echo coursepress_get_course_availability_dates(); ?></span>
			</p>
			<p class="course-meta">
				<span class="meta-title"><?php _e( 'Enrollment:', 'cp' ); ?></span>
				<span class="course-meta-enrollment-dates"><?php echo coursepress_get_course_enrollment_dates(); ?></span>
			</p>
			<p class="course-meta">
				<span class="meta-title"><?php _e( 'Who can Enroll:', 'cp' ); ?></span>
				<span class="course-meta-enrollment-dates"><?php echo coursepress_get_course_enrollment_type(); ?></span>
			</p>
      <p class="course-meta">
          <span class="meta-title"><?php _e( 'Language:', 'cp' ); ?></span>
          <span class="course-meta course-meta-language"><?php echo $course->get_course_language(); ?></span>
      </p>
<?php
$price = $course->get_course_cost();
if ( ! empty( $price ) ) {
?>
            <p class="course-meta">
                <span class="meta-title"><?php _e( 'Price:', 'cp' ); ?></span>
                <span class="course-meta course-meta-price"><?php echo $price; ?></span>
            </p>
<?php
}
?>
            <p class="course-button">
                <?php echo do_shortcode( '[course_join_button ]' ) ?>
            </p>

            <div class="social-shares">
                <?php echo do_shortcode( '[course_social_links course_id="' . $course->ID . '"]' ); ?>
            </div>

            <?php echo do_shortcode('[course_instructors course_id="' . $course->ID . '"]'); ?>

		</div>
        </div>

        <div class="course-description">
            <h3 class="sub-title course-sub-title"><?php _e( 'About this course', 'cp' ); ?></h3>
            <?php echo apply_filters( 'the_content', coursepress_get_course_description() ); ?>
        </div>

        <div class="course-structure">
            <h3 class="sub-title course-sub-title"><?php _e( 'Course Structure', 'cp' ); ?></h3>
            <?php echo coursepress_get_course_structure(); ?>
        </div>

    </div>
</article>
