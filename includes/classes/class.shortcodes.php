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
            add_shortcode('course_instructor_avatar', array(&$this, 'course_instructor_avatar'));
            add_shortcode('course_details', array(&$this, 'course_details'));
            add_shortcode('courses_student_dashboard', array(&$this, 'courses_student_dashboard'));
            add_shortcode('courses_student_settings', array(&$this, 'courses_student_settings'));
            add_shortcode('courses_urls', array(&$this, 'courses_urls'));
            add_shortcode('course_units', array(&$this, 'course_units'));
            add_shortcode('course_unit_details', array(&$this, 'course_unit_details'));
            add_shortcode('course_breadcrumbs', array(&$this, 'course_breadcrumbs'));

            $GLOBALS['units_breadcrumbs'] = '';
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
                return $cp->get_signup_slug(true);
            }
        }

        function course_details($atts) {
            global $wp_query;

            $student = new Student(get_current_user_id());

            extract(shortcode_atts(array(
                'course_id' => $wp_query->post->ID,
                'field' => 'course_start_date'
                            ), $atts));

            $course = new Course($course_id);
            $course = $course->get_course();

            if ($field == 'action_links') {

                $unenroll_link_visible = false;

                if ($student->user_enrolled_in_course($course_id)) {
                    if (strtotime($course->course_start_date) <= time() && strtotime($course->course_end_date) >= time()) {//course is currently active
                        $unenroll_link_visible = true;
                    }
                }

                $course->action_links = '<div class="apply-links">';

                if ($unenroll_link_visible === true) {
                    $course->action_links .= '<a href="?unenroll=' . $course->ID . '" onClick="return unenroll();">' . __('Un-enroll', 'cp') . '</a> | ';
                }
                $course->action_links .= '<a href="' . get_permalink($course->ID) . '">' . __('Course Details', 'cp') . '</a></div>';
            }

            if ($field == 'class_size') {
                if ($course->class_size == '0' || $course->class_size == '') {
                    $course->class_size = __('Infinite', 'cp');
                }
            }

            $passcode_box_visible = false;

            if ($course->enroll_type == 'passcode') {
                $course->enroll_type = __('Anyone with a Passcode', 'cp');
                $passcode_box_visible = true;
            }

            if ($field == 'enroll_type') {

                if ($course->enroll_type == 'anyone') {
                    $course->enroll_type = __('Anyone', 'cp');
                }


                if ($course->enroll_type == 'manually') {
                    $course->enroll_type = __('Public enrollments are disabled', 'cp');
                }
            }

            if ($field == 'course_start_date' or $field == 'course_end_date' or $field == 'enrollment_start_date' or $field == 'enrollment_end_date') {
                $date_format = get_option('date_format');
                if ($course->$field == '') {
                    $course->$field = __('N/A', 'cp');
                } else {
                    $course->$field = sp2nbsp(date($date_format, strtotime($course->$field)));
                }
            }

            if ($field == 'price') {
                $course->price = 'FREE (to do)';
            }

            if ($field == 'button') {


                if (current_user_can('student')) {

                    if (!$student->user_enrolled_in_course($course_id)) {
                        if ($course->enroll_type != 'manually') {
                            if (strtotime($course->course_end_date) <= time()) {//Course is no longer active
                                $course->button = '<span class="apply-button-finished">' . __('Finished', 'cp') . '</span>';
                            } else {
                                if ($course->enrollment_start_date !== '' && $course->enrollment_end_date !== '' && strtotime($course->enrollment_start_date) <= time() && strtotime($course->enrollment_end_date) >= time()) {
                                    $course->button = '<input type="submit" class="apply-button" value="' . __('Enroll Now', 'cp') . '" />';
                                    $course->button .= '<div class="passcode-box">' . do_shortcode('[course_details field="passcode_input"]') . '</div>';
                                } else {
                                    $course->button = '<span class="apply-button-finished">' . __('Not available yet', 'cp') . '</span>';
                                }
                            }
                        } else {
                            //don't show any button because public enrollments are disabled with manuall enroll type
                        }
                    } else {
                        if ($course->course_start_date !== '' && $course->course_end_date !== '') {//Course is currently active
                            if (strtotime($course->course_start_date) <= time() && strtotime($course->course_end_date) >= time()) {//Course is currently active
                                $course->button = '<a href="' . get_permalink($course->ID) . 'units/" class="apply-button-enrolled">' . __('Go to Class', 'cp') . '</a>';
                            } else {

                                if (strtotime($course->course_start_date) >= time()) {//Waiting for a course to start
                                    $course->button = '<span class="apply-button-pending">' . __('You are enrolled', 'cp') . '</span>';
                                }
                                if (strtotime($course->course_end_date) <= time()) {//Course is no longer active
                                    $course->button = '<span class="apply-button-finished">' . __('Finished', 'cp') . '</span>';
                                }
                            }
                        } else {//Course is inactive or pending
                            $course->button = '<span class="apply-button-finished">' . __('Not available yet', 'cp') . '</span>';
                        }
                    }
                } else {
                    if ($course->enroll_type != 'manually') {
                        if (strtotime($course->course_end_date) <= time()) {//Course is no longer active
                            $course->button = '<span class="apply-button-finished">' . __('Finished', 'cp') . '</span>';
                        } else if ($course->course_start_date == '' || $course->course_end_date == '') {
                            $course->button = '<span class="apply-button-finished">' . __('Not available yet', 'cp') . '</span>';
                        } else {
                            $cp = new CoursePress();
                            $course->button = '<a href="' . $cp->get_signup_slug(true) . '" class="apply-button">' . __('Signup', 'cp') . '</a>';
                        }
                    }
                }
            }

            if ($field == 'passcode_input') {
                if ($passcode_box_visible) {
                    $course->passcode_input = '<label>' . __("Passcode: ", "cp") . '<input type="password" name="passcode" /></label>';
                }
            }
            return $course->$field;
        }

        function course_instructor_avatar($atts) {
            global $wp_query;

            extract(shortcode_atts(array('instructor_id' => 0), $atts));


            $avatar_url = preg_match('@src="([^"]+)"@', get_avatar($instructor_id, 80), $match);
            $avatar_url = $match[1];
?>
            <?php

            $content .= '<div class="instructor-avatar">';
            $content .= '<div class="small-circle-profile-image" style="background: url(' . $avatar_url . ');"></div>';
            $content .= '</div>';

            return $content;
        }

        function course_instructors($atts) {
            global $wp_query;
            global $instructor_profile_slug;

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

                $content .= '<div class="instructor"><a href="' . trailingslashit(site_url()) . trailingslashit($instructor_profile_slug) . trailingslashit($instructor->user_nicename) . '">';
                $content .= '<div class="small-circle-profile-image" style="background: url(' . $avatar_url . ');"></div>';
                $content .= '<div class="instructor-name">' . $instructor->display_name . '</div>';
                $content .= '</a></div>';
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

        function course_units($atts) {
            $course = new Course();

            if (empty($course_id)) {
                $course_id = 0;
            }

            $content = '';

            extract(shortcode_atts(array('course_id' => $course_id), $atts));

            $units = $course->get_units($course_id);

            $student = new Student(get_current_user_id());
            //redirect to the parent course page if not enrolled
            if (!$student->has_access_to_course($course_id)) {
                wp_redirect(get_permalink($course_id));
                exit;
            }

            $content .= '<ol>';
            foreach ($units as $unit) {
                $unit_details = new Unit($unit->ID);
                $content .= '<li><a href="' . $unit_details->get_permalink($course_id) . '">' . $unit->post_title . '</a></li>';
            }
            $content .= '</ol>';

            return $content;
        }

        function course_unit_details($atts) {

            extract(shortcode_atts(array(
                'unit_id' => 0,
                'field' => 'title'
                            ), $atts));

            $unit = new Unit($unit_id);
            $student = new Student(get_current_user_id());

            //redirect to the parent course page if not enrolled
            if (!$student->has_access_to_course($unit->course_id)) {
                wp_redirect(get_permalink($unit->course_id));
                exit;
            }

            return $unit->details->$field;
        }

        function course_breadcrumbs($atts) {
            global $course_slug;
            global $units_slug;
            global $units_breadcrumbs;

            extract(shortcode_atts(array(
                'type' => 'unit_archive',
                'course_id' => 0,
                'position' => 'shortcode'
                            ), $atts));

            $course = new Course($course_id);

            if ($type == 'unit_archive') {
                $units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit(get_option('home')) . $course_slug . '/">' . __('Courses', 'cp') . '</a> » <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a></div>';
            }

            if ($type == 'unit_single') {
                $units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit(get_option('home')) . $course_slug . '/">' . __('Courses', 'cp') . '</a> » <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a> » <a href="' . $course->get_permalink() . 'units/">' . __('Units', 'cp') . '</a></div>';
            }

            if ($position == 'shortcode') {
                return $units_breadcrumbs;
            }

            /* if ($position = 'before_title') {

              add_filter('the_title', 'breadcrumb_before_post_title');

              function breadcrumb_before_post_title($title) {
              global $units_breadcrumbs;

              $title = $units_breadcrumbs . $title;
              return $title;
              }

              } */
        }

    }

}

$coursepress_shortcodes = new CoursePress_Shortcodes();
                ?>
