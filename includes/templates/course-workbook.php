<?php
$course_id			 = do_shortcode( '[get_parent_course_id]' );
$course_id			 = (int) $course_id;
$coursepress->check_access( $course_id );
echo do_shortcode( '[course_unit_archive_submenu]' );
?> 
