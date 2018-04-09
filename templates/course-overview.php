<?php
/**
 * The template use to print course overview.
 *
 * @since 3.0
 * @package CoursePress
 */
$course = coursepress_get_course();
$coursepress_schema = apply_filters( 'coursepress_schema', coursepress_get_course_title(), 'title' );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php echo esc_html( $coursepress_schema ); ?></h1>
	</header>
	<div class="entry-content">
		<?php $messages = apply_filters( 'coursepress_overview_messages', array() ); ?>
		<?php if ( ! empty( $messages ) ) : ?>
			<div class="cp-warning-box">
				<?php foreach ( $messages as $message ) : ?>
					<p><?php echo esc_html( $message ); ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="course-details">
			<?php $media = coursepress_get_course_media( false, 420, 220 ); ?>
			<?php if ( ! empty( $media ) ) : ?>
				<div class="course-media">
					<?php echo esc_html( $media ); ?>
				</div>
			<?php endif; ?>
			<div class="course-metas">
				<p class="course-meta">
					<span class="meta-title"><?php esc_html_e( 'Availability:', 'cp' ); ?></span>
					<span class="course-meta-dates"><?php echo esc_html( coursepress_get_course_availability_dates() ); ?></span>
				</p>
				<p class="course-meta">
					<span class="meta-title"><?php esc_html_e( 'Enrollment:', 'cp' ); ?></span>
					<span class="course-meta-enrollment-dates"><?php echo esc_html( coursepress_get_course_enrollment_dates() ); ?></span>
				</p>
				<p class="course-meta">
					<span class="meta-title"><?php esc_html_e( 'Who can Enroll:', 'cp' ); ?></span>
					<span class="course-meta-enrollment-dates"><?php echo esc_html( coursepress_get_course_enrollment_type() ); ?></span>
				</p>
				<p class="course-meta">
					<span class="meta-title"><?php esc_html_e( 'Language:', 'cp' ); ?></span>
					<span class="course-meta course-meta-language"><?php echo esc_html( $course->get_course_language() ); ?></span>
				</p>
				<?php $price = $course->get_course_cost(); ?>
				<?php if ( ! empty( $price ) ) : ?>
					<p class="course-meta">
						<span class="meta-title"><?php esc_html_e( 'Price:', 'cp' ); ?></span>
						<span class="course-meta course-meta-price"><?php echo esc_html( $price ); ?></span>
					</p>
				<?php endif; ?>
				<?php $coursepress_schema = apply_filters( 'coursepress_schema', '', 'description' ); ?>
				<?php $the_content = apply_filters( 'the_content', coursepress_get_course_description() ); ?>
				<p class="course-button">
					<?php echo esc_html( do_shortcode( '[course_join_button ]' ) ); ?>
				</p>

				<div class="social-shares">
					<?php echo do_shortcode( '[course_social_links course_id="' . $course->ID . '"]' ); ?>
				</div>
				<?php echo do_shortcode( '[course_instructors course_id="' . $course->ID . '"]' ); ?>
			</div>
		</div>

		<div class="course-description"<?php echo esc_html( $coursepress_schema ); ?>>
			<h3 class="sub-title course-sub-title"><?php esc_html_e( 'About this course', 'cp' ); ?></h3>
			<?php echo esc_html( $the_content ); ?>
		</div>

		<div class="course-structure">
			<h3 class="sub-title course-sub-title"><?php esc_html_e( 'Course Structure', 'cp' ); ?></h3>
			<?php echo esc_html( coursepress_get_course_structure() ); ?>
		</div>

	</div>
</article>
