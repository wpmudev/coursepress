<?php

/*
  CoursePress Shortcodes
 */

if (!class_exists('CoursePress_Shortcodes')) {

    class CoursePress_Shortcodes extends CoursePress {

        function CoursePress_Shortcodes() {
            $this->__construct();
        }

        function __construct() {

            //register plugin shortcodes
            add_shortcode('course_instructors', array(&$this, 'course_instructors'));
            add_shortcode('course_details', array(&$this, 'course_details'));
            add_shortcode('courses_student_dashboard', array(&$this, 'courses_student_dashboard'));
            add_shortcode('courses_student_settings', array(&$this, 'courses_student_settings'));
            add_shortcode('courses_urls', array(&$this, 'courses_urls'));
        }

        function courses_urls($atts) {
            $cp = new CoursePress;
            extract(shortcode_atts(array(
                'url' => ''
                            ), $atts));

            if ($url == 'enrollment-process') {
                return $cp->get_enrollment_process_slug(true);
            }

            if ($url == 'signup') {
                return $cp->get_signup_page_slug(true);
            }
        }

        function course_details($atts) {
            global $wp_query;

            extract(shortcode_atts(array(
                'course_id' => $wp_query->post->ID,
                'field' => 'course_start_date'
                            ), $atts));

            $course = new Course($course_id);
            $course = $course->get_course();

            if ($field == 'class_size') {
                if ($course->class_size == '0') {
                    $course->class_size = __('Infinite', 'cp');
                }
            }

            $passcode_box_visible = false;

            if ($field == 'enroll_type') {

                if ($course->enroll_type == 'anyone') {
                    $course->enroll_type = __('Anyone', 'cp');
                }

                if ($course->enroll_type == 'passcode') {
                    $course->enroll_type = __('Anyone with a Passcode', 'cp');
                    $passcode_box_visible = true;
                }

                if ($course->enroll_type == 'manually') {
                    $course->enroll_type = __('Public enrollments are disabled', 'cp');
                }
            }

            if ($field == 'course_start_date' or $field == 'course_end_date' or $field == 'enrollment_start_date' or $field == 'enrollment_end_date') {
                $date_format = get_option('date_format');
                $course->$field = sp2nbsp(date($date_format, strtotime($course->$field)));
            }

            if ($field == 'price') {
                $course->price = 'FREE (to do)';
            }

            if ($field == 'button') {

                $student = new Student(get_current_user_id());

                if (current_user_can('student')) {
                    if (!$student->user_enrolled_in_course($course_id)) {
                        $course->button = '<input type="submit" class="apply-button" value="' . __('Enroll Now', 'cp') . '" />';
                    } else {
                        $course->button = '<a href="" class="apply-button-enrolled">' . __('Go to Class', 'cp') . '</a>';
                    }
                } else {
                    $cp = new CoursePress();
                    $course->button = '<a href="' . $cp->get_signup_page_slug(true) . '" class="apply-button">' . __('Signup', 'cp') . '</a>';
                }
            }

            if ($field == 'passcode') {
                if ($passcode_box_visible) {
                    $course->passcode = '<label>' . __("Passcode: ", "cp") . '<input type="password" /></label>';
                }
            }
            return $course->$field;
        }

        function course_instructors($atts) {
            global $wp_query;

            extract(shortcode_atts(array(
                'course_id' => $wp_query->post->ID,
                'count' => false,
                            ), $atts));

            $course = new Course($course_id);
            $instructors = $course->get_course_instructors();

            $instructors_count = 0;

            foreach ($instructors as $instructor) {
                $avatar_url = preg_match('@src="([^"]+)"@', get_avatar($instructor->ID, 80), $match);
                $avatar_url = $match[1];
?>
                <?php

                $content .= '<div class="instructor">';
                $content .= '<div class="small-circle-profile-image" style="background: url(' . $avatar_url . ');"></div>';
                $content .= '<div class="instructor-name">' . $instructor->display_name . '</div>';
                $content .= '</div>';
                $instructors_count++;
            }
            if ($count) {
                return $instructors_count;
            } else {
                return $content;
            }
        }

        function courses_student_dashboard($atts) {
            $coursepress = new CoursePress();
            extract(shortcode_atts(array(), $atts));
            load_template($coursepress->plugin_dir . 'includes/templates/courses-student-dashboard.php', false);
        }

        function courses_student_settings($atts) {
            $coursepress = new CoursePress();
            extract(shortcode_atts(array(), $atts));
            load_template($coursepress->plugin_dir . 'includes/templates/courses-student-settings.php', false);
        }

    }

}

$coursepress_shortcodes = new CoursePress_Shortcodes();
                ?>
