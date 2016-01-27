<?php
// Show course media
echo do_shortcode( '[course_media]' );

// Show course summary/excerpt
echo do_shortcode( '[course_summary length="50"]' );
?>

	<div class="divider"></div>
	<div class="enroll-box">
		<div class="enroll-box-left">
			<div class="course-box">
				<?php echo do_shortcode( '[course_dates show_alt_display="yes"]' ); ?>
				<?php echo do_shortcode( '[course_enrollment_dates show_enrolled_display="no"]' ); ?>
				<?php echo do_shortcode( '[course_class_size]' ); ?>
				<?php echo do_shortcode( '[course_enrollment_type]' ); ?>
				<?php echo do_shortcode( '[course_language]' ); ?>
				<?php echo do_shortcode( '[course_cost]' ); ?>
			</div>
		</div>
		<div class="enroll-box-right">
			<div class="apply-box">
				<?php echo do_shortcode( '[course_join_button]' ); ?>
			</div>
		</div>
	</div>
	<div class="divider"></div>

<?php
//List of instructors
echo do_shortcode( '[course_instructors show_label="yes" label_element="h2" label_delimeter="" class="instructors-box"]' );
?>

<?php
// Course Structure
echo do_shortcode( '[course_structure show_label="yes" label_element="h2" label_delimeter="" show_title="no" show_divider="yes"]' );
?>