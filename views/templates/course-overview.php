<?php
/**
 * Course overview template
 */
$course = new CoursePress_Course( $course_id );
?>
<div class="course-overview">
	[course_instructors label_tag="label"]
    [course_media]
    [course_summary]
    [course_description]
</div>
