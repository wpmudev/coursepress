<?php

$student = new Student(get_current_user_id());
//enroll_in_course
$course_price = 0;

if (isset($_POST['course_id']) && is_numeric($_POST['course_id'])) {

    $course_id = $_POST['course_id'];
    $course = new Course($course_id);
    $course = $course->get_course();

    if (!$student->user_enrolled_in_course($course_id)) {

        if ($course_price == 0) {//Course is FREE
            //Enroll student in
            if ($student->enroll_in_course($course_id)) {
                printf(__('Congratulations, you have successfully enrolled in "<strong>%s</strong>" course!', 'cp'), $course->post_title);
            } else {
                _e('Something went wrong during the enrollment process. Please try again later.', 'cp');
            }
        } else {
            //coursepress_show_payment_form();
        }
    } else {
        _e('You have already enrolled in the course.', 'cp');//can't enroll more than once to the same course at the time
    }
} else {
    _e('Please select a course first you want to enroll in.', 'cp');
}
?>