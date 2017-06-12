<?php
/**
 * The template use to print course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'course-overview' ); ?>>
	<header class="entry-header">
		<span class="page-title-tag"><?php _e( 'Course', 'cp' ); ?></span>
		<?php the_title( '<h1 class="entry-title course-title">', '</h1>' ); ?>
	</header>

	<div class="course-details">
		<div class="course-media">
			<?php echo coursepress_get_course_media( false, 420, 220 ); ?>
		</div>
		<div class="course-metas">
			<p class="course-meta">
				<span class="meta-title"><?php _e( 'Availability', 'cp' ); ?>: </span>
				<span class="course-meta-dates"><?php echo coursepress_get_course_availability_dates(); ?></span>
			</p>
			<p class="course-meta">
				<span class="meta-title"><?php _e( 'Enrollment', 'cp' ); ?>: </span>
				<span class="course-meta-enrollment-dates"><?php echo coursepress_get_course_enrollment_dates(); ?></span>
			</p>
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
