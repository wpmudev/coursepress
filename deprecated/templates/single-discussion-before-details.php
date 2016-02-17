<?php
global $post, $coursepress;
$course_id = get_post_meta( $post->ID, 'course_id', true );
//redirect to the parent course page if not enrolled
$coursepress->check_access( $course_id );

echo do_shortcode( '[course_unit_archive_submenu course_id="' . $course_id . '"]' );
?>