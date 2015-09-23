<?php
// Avatar
echo do_shortcode( '[course_instructor_avatar instructor_id="' . $user->ID . '"]' );
// Bio
echo get_user_meta( $user->ID, 'description', true );
?>

	<h2 class="h2-instructor-bio"><?php _e( 'Courses', 'cp' ); ?></h2>

<?php
// Course List
echo do_shortcode( '[course_list instructor="' . $user->ID . '" class="course" left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_link="yes" show_media="yes"]' );
?>