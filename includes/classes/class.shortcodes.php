<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

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
            add_shortcode('student_registration_form', array(&$this, 'student_registration_form'));
            add_shortcode('courses_urls', array(&$this, 'courses_urls'));
            add_shortcode('course_units', array(&$this, 'course_units'));
            add_shortcode('course_units_loop', array(&$this, 'course_units_loop'));
            add_shortcode('course_notifications_loop', array(&$this, 'course_notifications_loop'));
            add_shortcode('course_discussion_loop', array(&$this, 'course_discussion_loop'));
            add_shortcode('course_unit_single', array(&$this, 'course_unit_single'));
            add_shortcode('course_unit_details', array(&$this, 'course_unit_details'));
            add_shortcode('course_unit_archive_submenu', array(&$this, 'course_unit_archive_submenu'));
            add_shortcode('course_breadcrumbs', array(&$this, 'course_breadcrumbs'));
            add_shortcode('course_discussion', array(&$this, 'course_discussion'));
            add_shortcode('instructor_profile_url', array(&$this, 'instructor_profile_url'));
            add_shortcode('get_parent_course_id', array(&$this, 'get_parent_course_id'));
            add_shortcode('units_dropdown', array(&$this, 'units_dropdown'));

            //add_shortcode('unit_discussion', array(&$this, 'unit_discussion'));


            $GLOBALS['units_breadcrumbs'] = '';
        }

        function course_unit_archive_submenu($atts) {
            global $coursepress;

            extract(shortcode_atts(array(
                'course_id' => ''
                            ), $atts));

            if ($course_id == '') {
                $course_id = do_shortcode('[get_parent_course_id]');
            }

            $subpage = $coursepress->units_archive_subpage;
            ?>
            <div class="submenu-main-container">
                <ul id="submenu-main" class="submenu nav-submenu">
                    <li class="submenu-item submenu-units <?php echo(isset($subpage) && $subpage == 'units' ? 'submenu-active' : ''); ?>"><a href="<?php echo get_permalink($course_id) . $coursepress->get_units_slug(); ?>/"><?php _e('Units', 'coursepress'); ?></a></li>
                    <li class="submenu-item submenu-notifications <?php echo(isset($subpage) && $subpage == 'notifications' ? 'submenu-active' : ''); ?>"><a href="<?php echo get_permalink($course_id) . $coursepress->get_notifications_slug(); ?>/"><?php _e('Notifications', 'coursepress'); ?></a></li>
                    <?php
                    $course_obj = new Course($course_id);
                    $course = $course_obj->get_course();
                    if ($course->allow_course_discussion == 'on') {
                        ?>
                        <li class="submenu-item submenu-discussions <?php echo(isset($subpage) && $subpage == 'discussions' ? 'submenu-active' : ''); ?>"><a href="<?php echo get_permalink($course_id) . $coursepress->get_discussion_slug(); ?>/"><?php _e('Discussions', 'coursepress'); ?></a></li>
                        <?php
                    }
                    if ($course->allow_course_grades_page == 'on') {
                        ?>
                        <li class="submenu-item submenu-grades <?php echo(isset($subpage) && $subpage == 'grades' ? 'submenu-active' : ''); ?>"><a href="<?php echo get_permalink($course_id) . $coursepress->get_grades_slug(); ?>/"><?php _e('Grades', 'coursepress'); ?></a></li>
                    <?php } ?>
                    <li class="submenu-item submenu-info"><a href="<?php echo get_permalink($course_id); ?>"><?php _e('Course Info', 'coursepress'); ?></a></li>
                </ul><!--submenu-main-->
            </div><!--submenu-main-container-->
            <?php
        }

        function courses_urls($atts) {
            global $enrollment_process_url, $signup_url;

            extract(shortcode_atts(array(
                'url' => ''
                            ), $atts));

            if ($url == 'enrollment-process') {
                return $enrollment_process_url;
            }

            if ($url == 'signup') {
                return $signup_url;
            }
        }

        function units_dropdown($atts) {
            extract(shortcode_atts(array('course_id' => (isset($wp_query->post->ID) ? $wp_query->post->ID : 0), 'include_general' => false, 'general_title' => ''), $atts));
            $course_obj = new Course($course_id);
            $units = $course_obj->get_units();

            $dropdown = '<div class="units_dropdown_holder"><select name="units_dropdown" class="units_dropdown">';
            if ($include_general) {
                if ($general_title == '') {
                    $general_title = __('-- General --', 'cp');
                }
                $dropdown .= '<option value="">' . $general_title . '</option>';
            }
            foreach ($units as $unit) {
                $dropdown .= '<option value="' . $unit->ID . '">' . $unit->post_title . '</option>';
            }
            $dropdown .= '</select></div>';

            return $dropdown;
        }

        function course_details($atts) {
            global $wp_query, $signup_url;

            $student = new Student(get_current_user_id());

            extract(shortcode_atts(array(
                'course_id' => (isset($wp_query->post->ID) ? $wp_query->post->ID : 0),
                'field' => 'course_start_date'
                            ), $atts));

            $course_obj = new Course($course_id);
            $course = $course_obj->get_course();

            if ($field == 'action_links') {

                $unenroll_link_visible = false;

                if ($student->user_enrolled_in_course($course_id)) {
                    if (((strtotime($course->course_start_date) <= time() && strtotime($course->course_end_date) >= time()) || (strtotime($course->course_end_date) >= time())) || $course->open_ended_course == 'on') {//course is currently active or is not yet active (will be active in the future)
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
                } else {
                    $count_left = $course->class_size - $course_obj->get_number_of_students();
                    $course->class_size = $course->class_size . ' ' . sprintf(__('(%d left)', 'cp'), $count_left);
                }
            }

            $passcode_box_visible = false;

            if (!isset($course->enroll_type)) {
                $course->enroll_type = 'anyone';
            } else {
                if ($course->enroll_type == 'passcode') {
                    $course->enroll_type = __('Anyone with a Passcode', 'cp');
                    $passcode_box_visible = true;
                }

                if ($course->enroll_type == 'prerequisite') {
                    $course->init_enroll_type = 'prerequisite';
                    $course->enroll_type = sprintf(__('Anyone who attanded to the %1s', 'cp'), '<a href="' . get_permalink($course->prerequisite) . '">' . __('prerequisite course', 'cp') . '</a>'); //__('Anyone who attended to the ', 'cp');
                }
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
                if ($course->open_ended_course == 'on') {
                    $course->$field = __('Open-ended', 'cp');
                } else {
                    if ($course->$field == '') {
                        $course->$field = __('N/A', 'cp');
                    } else {
                        $course->$field = sp2nbsp(date($date_format, strtotime($course->$field)));
                    }
                }
            }

            if ($field == 'price') {
                global $coursepress;
                if (isset($course->marketpress_product) && $course->marketpress_product != '' && $coursepress->is_marketpress_active()) {
                    echo do_shortcode('[mp_product_price product_id="' . $course->marketpress_product . '" label=""]');
                } else {
                    $course->price = __('FREE', 'cp');
                }
            }

            if ($field == 'button') {

                $course->button = '<form name="enrollment-process" method="post" action="' . do_shortcode("[courses_urls url='enrollment-process']") . '">';

                if (current_user_can('student')) {

                    if (!$student->user_enrolled_in_course($course_id)) {
                        if (!$course_obj->is_populated()) {
                            if ($course->enroll_type != 'manually') {
                                if (strtotime($course->course_end_date) <= time() && $course->open_ended_course == 'off') {//Course is no longer active
                                    $course->button .= '<span class="apply-button-finished">' . __('Finished', 'cp') . '</span>';
                                } else {
                                    if (($course->enrollment_start_date !== '' && $course->enrollment_end_date !== '' && strtotime($course->enrollment_start_date) <= time() && strtotime($course->enrollment_end_date) >= time()) || $course->open_ended_course == 'on') {
                                        if (($course->init_enroll_type == 'prerequisite' && $student->user_enrolled_in_course($course->prerequisite)) || $course->init_enroll_type !== 'prerequisite') {
                                            $course->button .= '<input type="submit" class="apply-button" value="' . __('Enroll Now', 'cp') . '" />';
                                            $course->button .= '<div class="passcode-box">' . do_shortcode('[course_details field="passcode_input"]') . '</div>';
                                        } else {
                                            $course->button .= '<span class="apply-button-finished">' . __('Prerequisite Required', 'cp') . '</span>';
                                        }
                                    } else {
                                        if (strtotime($course->enrollment_end_date) <= time()) {
                                            $course->button .= '<span class="apply-button-finished">' . __('Not available any more', 'cp') . '</span>';
                                        } else {
                                            $course->button .= '<span class="apply-button-finished">' . __('Not available yet', 'cp') . '</span>';
                                        }
                                    }
                                }
                            } else {
                                //don't show any button because public enrollments are disabled with manuall enroll type
                            }
                        } else {
                            $course->button .= '<span class="apply-button-finished">' . __('Populated', 'cp') . '</span>';
                        }
                    } else {
                        if (($course->course_start_date !== '' && $course->course_end_date !== '') || $course->open_ended_course == 'on') {//Course is currently active
                            if ((strtotime($course->course_start_date) <= time() && strtotime($course->course_end_date) >= time()) || $course->open_ended_course == 'on') {//Course is currently active
                                $course->button .= '<a href="' . get_permalink($course->ID) . 'units/" class="apply-button-enrolled">' . __('Go to Class', 'cp') . '</a>';
                            } else {

                                if (strtotime($course->course_start_date) >= time()) {//Waiting for a course to start
                                    $course->button .= '<span class="apply-button-pending">' . __('You are enrolled', 'cp') . '</span>';
                                }
                                if (strtotime($course->course_end_date) <= time()) {//Course is no longer active
                                    $course->button .= '<span class="apply-button-finished">' . __('Finished', 'cp') . '</span>';
                                }
                            }
                        } else {//Course is inactive or pending
                            $course->button .= '<span class="apply-button-finished">' . __('Not available yet', 'cp') . '</span>';
                        }
                    }
                } else {

                    if ($course->enroll_type != 'manually') {
                        if (!$course_obj->is_populated()) {
                            if ((strtotime($course->course_end_date) <= time()) && $course->open_ended_course == 'off') {//Course is no longer active
                                $course->button .= '<span class="apply-button-finished">' . __('Finished', 'cp') . '</span>';
                            } else if (($course->course_start_date == '' || $course->course_end_date == '') && $course->open_ended_course == 'off') {
                                $course->button .= '<span class="apply-button-finished">' . __('Not available yet', 'cp') . '</span>';
                            } else {


                                if ((strtotime($course->enrollment_end_date) <= time()) && $course->open_ended_course == 'off') {
                                    $course->button .= '<span class="apply-button-finished">' . __('Not available any more', 'cp') . '</span>';
                                } else {
                                    $course->button .= '<a href="' . $signup_url . '" class="apply-button">' . __('Signup', 'cp') . '</a>';
                                }
                            }
                        } else {
                            $course->button .= '<span class="apply-button-finished">' . __('Populated', 'cp') . '</span>';
                        }
                    }
                }
                $course->button .= '<div class="clearfix"></div>';
                $course->button .= wp_nonce_field('enrollment_process');
                $course->button .= '<input type="hidden" name="course_id" value="' . $course_id . '" />';
                $course->button .= '</form>';
            }

            if ($field == 'passcode_input') {
                if ($passcode_box_visible) {
                    $course->passcode_input = '<label>' . __("Passcode: ", "cp") . '<input type="password" name="passcode" /></label>';
                }
            }

            if (!isset($course->$field)) {
                $course->$field = '';
            }

            return $course->$field;
        }

        function course_instructor_avatar($atts) {
            global $wp_query;

            extract(shortcode_atts(array('instructor_id' => 0, 'thumb_size' => 80, 'class' => 'small-circle-profile-image'), $atts));

            $doc = new DOMDocument();
            $doc->loadHTML(get_avatar($instructor_id, $thumb_size));
            $imageTags = $doc->getElementsByTagName('img');

            $content = '';

            foreach ($imageTags as $tag) {
                $avatar_url = $tag->getAttribute('src');
            }
            ?>
            <?php
            $content .= '<div class="instructor-avatar">';
            $content .= '<div class="' . $class . '" style="background: url(' . $avatar_url . ');"></div>';
            $content .= '</div>';

            return $content;
        }

        function instructor_profile_url($atts) {
            global $instructor_profile_slug;

            extract(shortcode_atts(array(
                'instructor_id' => 0), $atts));

            $instructor = get_userdata($instructor_id);

            if ($instructor_id) {
                return trailingslashit(site_url()) . trailingslashit($instructor_profile_slug) . trailingslashit($instructor->user_login);
            }
        }

        function get_parent_course_id($atts) {
            global $wp;

            if (array_key_exists('coursename', $wp->query_vars)) {
                $course = new Course();
                $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
            } else {
                $course_id = 0;
            }
            return $course_id;
        }

        function course_instructors($atts) {
            global $wp_query;
            global $instructor_profile_slug;

            extract(shortcode_atts(array(
                'course_id' => (isset($wp_query->post->ID) ? $wp_query->post->ID : 0),
                'count' => false,
                'list' => false,
                'link' => true,
                'avatar_size' => 80
                            ), $atts));


            $course = new Course($course_id);
            $instructors = $course->get_course_instructors();

            $instructors_count = 0;
            $content = '';
            $list = array();

            foreach ($instructors as $instructor) {
                $list[] = ($link == true ? '<a href="' . trailingslashit(site_url()) . trailingslashit($instructor_profile_slug) . trailingslashit($instructor->user_login) . '">' . $instructor->display_name . '</a>' : $instructor->display_name);
                $doc = new DOMDocument();
                $doc->loadHTML(get_avatar($instructor->ID, $avatar_size));
                $imageTags = $doc->getElementsByTagName('img');

                foreach ($imageTags as $tag) {
                    $avatar_url = $tag->getAttribute('src');
                }
                ?>
                <?php
                $content .= '<div class="instructor"><a href="' . trailingslashit(site_url()) . trailingslashit($instructor_profile_slug) . trailingslashit($instructor->user_login) . '">';
                $content .= '<div class="small-circle-profile-image" style="background: url(' . $avatar_url . ');"></div>';
                $content .= '<div class="instructor-name">' . $instructor->display_name . '</div>';
                $content .= '</a></div>';
                $instructors_count++;
            }

            $list = implode(", ", $list);

            if ($count) {
                return $instructors_count;
            } elseif ($list) {
                return $list;
            } else {
                return $content;
            }
        }

        function courses_student_dashboard($atts) {
            global $plugin_dir;
            load_template($plugin_dir . 'includes/templates/student-dashboard.php', false);
        }

        function courses_student_settings($atts) {
            global $plugin_dir;
            load_template($plugin_dir . 'includes/templates/student-settings.php', false);
        }

        function course_unit_single($atts) {
            global $wp;

            extract(shortcode_atts(array('unit_id' => 0), $atts));

            if (empty($unit_id)) {
                if (array_key_exists('unitname', $wp->query_vars)) {
                    $unit = new Unit();
                    $unit_id = $unit->get_unit_id_by_name($wp->query_vars['unitname']);
                } else {
                    $unit_id = 0;
                }
            }

            $args = array(
                'post_type' => 'unit',
                'p' => $unit_id
            );

            cp_suppress_errors();
            query_posts($args);
            //cp_show_errors();
        }

        function course_units_loop($atts) {
            global $wp;

            extract(shortcode_atts(array('course_id' => 0), $atts));

            if (empty($course_id)) {
                if (array_key_exists('coursename', $wp->query_vars)) {
                    $course = new Course();
                    $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
                } else {
                    $course_id = 0;
                }
            }

            $args = array(
                'category' => '',
                'order' => 'ASC',
                'post_type' => 'unit',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'publish',
                'meta_key' => 'unit_order',
                'orderby' => 'meta_value_num',
                'posts_per_page' => '-1',
                'meta_query' => array(
                    array(
                        'key' => 'course_id',
                        'value' => $course_id
                    ),
                )
            );

            query_posts($args);
        }

        function course_notifications_loop($atts) {
            global $wp;

            extract(shortcode_atts(array('course_id' => 0), $atts));

            if (empty($course_id)) {
                if (array_key_exists('coursename', $wp->query_vars)) {
                    $course = new Course();
                    $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
                } else {
                    $course_id = 0;
                }
            }

            $args = array(
                'category' => '',
                'order' => 'ASC',
                'post_type' => 'notifications',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'publish',
                'orderby' => 'meta_value_num',
                'posts_per_page' => '-1',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'course_id',
                        'value' => $course_id
                    ),
                    array(
                        'key' => 'course_id',
                        'value' => ''
                    ),
                )
            );

            query_posts($args);
        }

        function course_discussion_loop($atts) {
            global $wp;

            extract(shortcode_atts(array('course_id' => 0), $atts));

            if (empty($course_id)) {
                if (array_key_exists('coursename', $wp->query_vars)) {
                    $course = new Course();
                    $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
                } else {
                    $course_id = 0;
                }
            }

            $args = array(
                'category' => '',
                'order' => 'DESC',
                'post_type' => 'discussions',
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'publish',
                'posts_per_page' => '-1',
                'meta_key' => 'course_id',
                'meta_value' => $course_id
            );

            query_posts($args);
        }

        function course_units($atts) {
            global $wp;

            $content = '';

            extract(shortcode_atts(array('course_id' => $course_id), $atts));

            if (empty($course_id)) {
                if (array_key_exists('coursename', $wp->query_vars)) {
                    $course = new Course();
                    $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
                } else {
                    $course_id = 0;
                }
            }

            $course = new Course($course_id);
            $units = $course->get_units($course_id, 'publish');

            $student = new Student(get_current_user_id());
            //redirect to the parent course page if not enrolled
            if (!current_user_can('administrator')) {//If current user is not admin, check if he can access to the units
                if ($course->details->post_author != get_current_user_id()) {//check if user is an author of a course (probably instructor)
                    if (!current_user_can('coursepress_view_all_units_cap')) {//check if the instructor, even if it's not the author of the course, maybe has a capability given by the admin
                        if (!$student->has_access_to_course($course_id)) {//if it's not an instructor who made the course, check if he is enrolled to course
                            //ob_start();
                            wp_redirect(get_permalink($course_id)); //if not, redirect him to the course page so he may enroll it if the enrollment is available
                            exit;
                        }
                    }
                }
            }

            $content .= '<ol>';
            $last_unit_url = '';

            foreach ($units as $unit) {
                $unit_details = new Unit($unit->ID);
                $content .= '<li><a href="' . $unit_details->get_permalink($course_id) . '">' . $unit->post_title . '</a></li>';
                $last_unit_url = $unit_details->get_permalink($course_id);
            }

            $content .= '</ol>';

            if (count($units) >= 1) {
                $content .= do_shortcode('[course_discussion]');
            }

            if (count($units) == 0) {
                $content = __('0 course units prepared yet. Please check back later.', 'cp');
            }

            if (count($units) == 1) {
                //ob_start();
                wp_redirect($last_unit_url);
                exit;
            }
            return $content;
        }

        function course_unit_details($atts) {
            global $post_id;

            extract(shortcode_atts(array(
                'unit_id' => 0,
                'field' => 'post_title',
                'format' => false,
                'student_id' => get_current_user_ID(),
                            ), $atts));

            if ($unit_id == 0) {
                $unit_id = get_the_ID();
            }

            $unit = new Unit($unit_id);

            $student = new Student(get_current_user_id());

            /* ------------ */
            $unit_module = new Unit_Module();

            $front_save_count = 0;

            $modules = $unit_module->get_modules($unit_id);

            foreach ($modules as $mod) {

                $class_name = $mod->module_type;

                if (class_exists($class_name)) {
                    $module = new $class_name();
                    if ($module->front_save) {
                        $front_save_count++;
                    }
                }
            }

            $input_modules_count = $front_save_count;
            /* ------------ */
            //$input_modules_count = do_shortcode('[course_unit_details field="input_modules_count" unit_id="' . $unit_id . '"]');
            $unit_module = new Unit_Module();
            $responses_count = 0;

            $modules = $unit_module->get_modules($unit_id);
            foreach ($modules as $module) {
                $unit_module = new Unit_Module();
                if ($unit_module->did_student_responed($module->ID, $student_id)) {
                    $responses_count++;
                }
            }
            $student_modules_responses_count = $responses_count;

            //$student_modules_responses_count = do_shortcode('[course_unit_details field="student_module_responses" unit_id="' . $unit_id . '"]');

            if ($student_modules_responses_count > 0) {
                $percent_value = round((100 / $input_modules_count) * $student_modules_responses_count, 0);
            } else {
                $percent_value = 0;
            }

            if ($input_modules_count == 0) {
                $unit_module = new Unit_Module();
                $grade = 0;
                $front_save_count = 0;
                $responses = 0;
                $graded = 0;
                //$input_modules_count = do_shortcode('[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]');
                $modules = $unit_module->get_modules($unit_id);


                if ($input_modules_count > 0) {
                    foreach ($modules as $mod) {

                        $class_name = $mod->module_type;

                        if (class_exists($class_name)) {
                            $module = new $class_name();
                            if ($module->front_save) {
                                $front_save_count++;
                                $response = $module->get_response($student_id, $mod->ID);

                                if (isset($response->ID)) {
                                    $grade_data = $unit_module->get_response_grade($response->ID);
                                    $grade = $grade + $grade_data['grade'];

                                    if (get_post_meta($response->ID, 'response_grade')) {
                                        $graded++;
                                    }

                                    $responses++;
                                }
                            } else {
                                //read only module
                            }
                        }
                    }
                    $percent_value = ($format == true ? ($responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">') . ($grade > 0 ? round(($grade / $front_save_count), 0) : 0) . '%</span>' : ($grade > 0 ? round(($grade / $front_save_count), 0) : 0));
                } else {
                    $student = new Student($student_id);
                    if ($student->is_unit_visited($unit_id, $student_id)) {
                        $grade = 100;
                        $percent_value = ($format == true ? '<span class="grade-active">' . $grade . '%</span>' : $grade);
                    } else {
                        $grade = 0;
                        $percent_value = ($format == true ? '<span class="grade-inactive">' . $grade . '%</span>' : $grade);
                    }
                }
                
                //$percent_value = do_shortcode('[course_unit_details field="student_unit_grade" unit_id="' . get_the_ID() . '"]');
            }

            //redirect to the parent course page if not enrolled
            if (!current_user_can('administrator')) {
                if (!$student->has_access_to_course($unit->course_id)) {
                    //ob_start();
                    wp_redirect(get_permalink($unit->course_id));
                    exit;
                }
            }

            if ($field == 'percent') {
                $unit->details->$field = $percent_value;
            }

            if ($field == 'permalink') {
                $unit->details->$field = $unit->get_permalink($unit->course_id);
            }

            if ($field == 'input_modules_count') {
                $unit_module = new Unit_Module();

                $front_save_count = 0;

                $modules = $unit_module->get_modules($unit_id);

                foreach ($modules as $mod) {

                    $class_name = $mod->module_type;

                    if (class_exists($class_name)) {
                        $module = new $class_name();
                        if ($module->front_save) {
                            $front_save_count++;
                        }
                    }
                }

                $unit->details->$field = $front_save_count;
            }

            if ($field == 'student_module_responses') {
                $unit_module = new Unit_Module();
                $responses_count = 0;

                $modules = $unit_module->get_modules($unit_id);
                foreach ($modules as $module) {
                    $unit_module = new Unit_Module();
                    if ($unit_module->did_student_responed($module->ID, $student_id)) {
                        $responses_count++;
                    }
                }
                $unit->details->$field = $responses_count;
            }

            if ($field == 'student_unit_grade') {
                $unit_module = new Unit_Module();
                $grade = 0;
                $front_save_count = 0;
                $responses = 0;
                $graded = 0;
                $input_modules_count = do_shortcode('[course_unit_details field="input_modules_count" unit_id="' . get_the_ID() . '"]');
                $modules = $unit_module->get_modules($unit_id);


                if ($input_modules_count > 0) {
                    foreach ($modules as $mod) {

                        $class_name = $mod->module_type;

                        if (class_exists($class_name)) {
                            $module = new $class_name();
                            if ($module->front_save) {
                                $front_save_count++;
                                $response = $module->get_response($student_id, $mod->ID);

                                if (isset($response->ID)) {
                                    $grade_data = $unit_module->get_response_grade($response->ID);
                                    $grade = $grade + $grade_data['grade'];

                                    if (get_post_meta($response->ID, 'response_grade')) {
                                        $graded++;
                                    }

                                    $responses++;
                                }
                            } else {
                                //read only module
                            }
                        }
                    }
                    $unit->details->$field = ($format == true ? ($responses == $graded && $responses == $front_save_count ? '<span class="grade-active">' : '<span class="grade-inactive">') . ($grade > 0 ? round(($grade / $front_save_count), 0) : 0) . '%</span>' : ($grade > 0 ? round(($grade / $front_save_count), 0) : 0));
                } else {
                    $student = new Student($student_id);
                    if ($student->is_unit_visited($unit_id, $student_id)) {
                        $grade = 100;
                        $unit->details->$field = ($format == true ? '<span class="grade-active">' . $grade . '%</span>' : $grade);
                    } else {
                        $grade = 0;
                        $unit->details->$field = ($format == true ? '<span class="grade-inactive">' . $grade . '%</span>' : $grade);
                    }
                }
            }

            if ($field == 'student_unit_modules_graded') {
                $unit_module = new Unit_Module();
                $grade = 0;
                $front_save_count = 0;
                $responses = 0;
                $graded = 0;

                $modules = $unit_module->get_modules($unit_id);

                foreach ($modules as $mod) {

                    $class_name = $mod->module_type;

                    if (class_exists($class_name)) {
                        $module = new $class_name();
                        if ($module->front_save) {
                            $front_save_count++;
                            $response = $module->get_response($student_id, $mod->ID);

                            if (isset($response->ID)) {
                                $grade_data = $unit_module->get_response_grade($response->ID);
                                $grade = $grade + $grade_data['grade'];

                                if (get_post_meta($response->ID, 'response_grade')) {
                                    $graded++;
                                }

                                $responses++;
                            }
                        } else {
                            //read only module
                        }
                    }
                }

                $unit->details->$field = $graded;
            }

            return $unit->details->$field;
        }

        function course_breadcrumbs($atts) {
            global $course_slug, $units_slug, $units_breadcrumbs, $wp;

            extract(shortcode_atts(array(
                'type' => 'unit_archive',
                'course_id' => 0,
                'position' => 'shortcode'
                            ), $atts));

            if (empty($course_id)) {
                if (array_key_exists('coursename', $wp->query_vars)) {
                    $course = new Course();
                    $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
                } else {
                    $course_id = 0;
                }
            }

            $course = new Course($course_id);

            if ($type == 'unit_archive') {
                $units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit(get_option('home')) . $course_slug . '/">' . __('Courses', 'cp') . '</a> » <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a></div>';
            }

            if ($type == 'unit_single') {
                $units_breadcrumbs = '<div class="units-breadcrumbs"><a href="' . trailingslashit(get_option('home')) . $course_slug . '/">' . __('Courses', 'cp') . '</a> » <a href="' . $course->get_permalink() . '">' . $course->details->post_title . '</a> » <a href="' . $course->get_permalink() . $units_slug . '/">' . __('Units', 'cp') . '</a></div>';
            }

            if ($position == 'shortcode') {
                return $units_breadcrumbs;
            }
        }

        function course_discussion($atts) {
            global $wp;

            if (array_key_exists('coursename', $wp->query_vars)) {
                $course = new Course();
                $course_id = $course->get_course_id_by_name($wp->query_vars['coursename']);
            } else {
                $course_id = 0;
            }

            $course = new Course($course_id);

            if ($course->details->allow_course_discussion == 'on') {

                $comments_args = array(
                    // change the title of send button 
                    'label_submit' => __('Send', 'cp'),
                    // change the title of the reply section
                    'title_reply' => __('Write a Reply or Comment', 'cp'),
                    // remove "Text or HTML to be displayed after the set of comment fields"
                    'comment_notes_after' => '',
                    // redefine your own textarea (the comment body)
                    'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x('Comment', 'noun') . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
                );

                $defaults = array(
                    'author_email' => '',
                    'ID' => '',
                    'karma' => '',
                    'number' => '',
                    'offset' => '',
                    'orderby' => '',
                    'order' => 'DESC',
                    'parent' => '',
                    'post_id' => $course_id,
                    'post_author' => '',
                    'post_name' => '',
                    'post_parent' => '',
                    'post_status' => '',
                    'post_type' => '',
                    'status' => '',
                    'type' => '',
                    'user_id' => '',
                    'search' => '',
                    'count' => false,
                    'meta_key' => '',
                    'meta_value' => '',
                    'meta_query' => '',
                );

                $wp_list_comments_args = array(
                    'walker' => null,
                    'max_depth' => '',
                    'style' => 'ul',
                    'callback' => null,
                    'end-callback' => null,
                    'type' => 'all',
                    'reply_text' => __('Reply', 'cp'),
                    'page' => '',
                    'per_page' => '',
                    'avatar_size' => 32,
                    'reverse_top_level' => null,
                    'reverse_children' => '',
                    'format' => 'xhtml', //or html5 @since 3.6
                    'short_ping' => false // @since 3.6
                );

                comment_form($comments_args = array(), $course_id);
                wp_list_comments($wp_list_comments_args, get_comments($defaults));
                //comments_template()
            }
        }

        function unit_discussion($atts) {
            global $wp;
            if (array_key_exists('unitname', $wp->query_vars)) {
                $unit = new Unit();
                $unit_id = $unit->get_unit_id_by_name($wp->query_vars['unitname']);
            } else {
                $unit_id = 0;
            }

            $comments_args = array(
                // change the title of send button 
                'label_submit' => 'Send',
                // change the title of the reply secpertion
                'title_reply' => 'Write a Reply or Comment',
                // remove "Text or HTML to be displayed after the set of comment fields"
                'comment_notes_after' => '',
                // redefine your own textarea (the comment body)
                'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x('Comment', 'noun') . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
            );

            comment_form($comments_args, $unit_id);
        }

        function student_registration_form() {
            global $plugin_dir;
            load_template($plugin_dir . 'includes/templates/student-signup.php', true);
        }

    }

}

$coursepress_shortcodes = new CoursePress_Shortcodes();
?>
