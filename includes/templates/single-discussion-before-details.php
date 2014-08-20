<?php
global $post;
$course_id = get_post_meta($post->ID, 'course_id', true);
do_shortcode('[course_unit_archive_submenu course_id="'.$course_id.'"]');
?>