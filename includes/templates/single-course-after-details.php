<?php
require($this->plugin_dir.'includes/classes/class.course.php');

$course = new Course(get_the_ID());
$course = $course->get_course();
?>

<!--Content after course content goes here-->