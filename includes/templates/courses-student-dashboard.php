<?php if(current_user_can('student')) { ?>
<?php
$student = new Student(get_current_user_id());
$student_courses = $student->get_enrolled_courses_ids();

foreach($student_courses as $course_id){
    echo $course_id.'<br />';
}
?>
<?php } ?>
