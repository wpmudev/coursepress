<?php

$course_thumbnail = Course::get_course_thumbnail( get_the_ID() );
if ( ! $course_thumbnail ) {
	$extended_class = 'quick-course-info-extended';
}

?>

<?php

// Replaces thumbnail with media
echo do_shortcode( '[course_media list_page="yes"]' );
?>

<?php
// Flat hyperlinked list of instructors
echo do_shortcode( '[course_instructors style="list-flat" link="true"]' );
?>

<?php
// Course summary/excerpt
echo do_shortcode( '[course_summary length="50"]' );
?>

<div class="quick-course-info <?php echo( isset( $extended_class ) ? esc_attr( $extended_class ) : '' ); ?>">
	<?php echo do_shortcode( '[course_start label="" class="course-time"]' ); ?>
	<?php echo do_shortcode( '[course_language label="" class="course-lang"]' ); ?>
	<?php echo do_shortcode( '[course_cost label="" class="course-cost" show_icon="true"]' ); ?>
	<?php echo do_shortcode( '[course_join_button list_page="yes"]' ); ?>
</div>
