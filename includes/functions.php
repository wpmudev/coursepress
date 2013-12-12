<?php

//get_site_option instead of get_option

function coursepress_send_email($email_args = array()) {

    if ($email_args['email_type'] == 'student_registration') {
        global $course_slug;
        $student_email = $email_args['student_email'];
        $subject = coursepress_get_registration_email_subject();
        $courses_address = '<a href="' . trailingslashit(site_url()) . trailingslashit($course_slug) . '">' . trailingslashit(site_url()) . trailingslashit($course_slug) . '</a>';

        $tags = array('STUDENT_FIRST_NAME', 'STUDENT_LAST_NAME', 'BLOG_NAME', 'LOGIN_ADDRESS', 'COURSES_ADDRESS', 'WEBSITE_ADDRESS');
        $tags_replaces = array($email_args['student_first_name'], $email_args['student_last_name'], get_bloginfo(), wp_login_url(), $courses_address, site_url());

        $message = coursepress_get_registration_content_email();

        $message = str_replace($tags, $tags_replaces, $message);

        add_filter('wp_mail_from', 'my_mail_from_function');

        function my_mail_from_function($email) {
            return coursepress_get_registration_from_email();
        }

        add_filter('wp_mail_from_name', 'my_mail_from_name_function');

        function my_mail_from_name_function($name) {
            return coursepress_get_registration_from_name();
        }

    }

    if ($email_args['email_type'] == 'student_invitation') {
        global $course_slug;

        $student_email = $email_args['student_email'];

        if (isset($email_args['course_id'])) {
            $course = new Course($email_args['course_id']);
        }

        $tags = array('STUDENT_FIRST_NAME', 'STUDENT_LAST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', 'COURSE_ADDRESS', 'WEBSITE_ADDRESS', 'PASSCODE');
        $tags_replaces = array($email_args['student_first_name'], $email_args['student_last_name'], get_bloginfo(), $course->details->post_excerpt, $course->get_permalink(), site_url(), $course->details->passcode);

        if ($email_args['enroll_type'] == 'passcode') {
            $message = coursepress_get_invitation_content_passcode_email();
            $subject = coursepress_get_invitation_passcode_email_subject();
        } else {
            $message = coursepress_get_invitation_content_email();
            $subject = coursepress_get_invitation_email_subject();
        }

        $message = str_replace($tags, $tags_replaces, $message);

        add_filter('wp_mail_from', 'my_mail_from_function');

        function my_mail_from_function($email) {
            return coursepress_get_invitation_passcode_from_email();
        }

        add_filter('wp_mail_from_name', 'my_mail_from_name_function');

        function my_mail_from_name_function($name) {
            return coursepress_get_invitation_passcode_from_name();
        }

    }

    add_filter('wp_mail_content_type', 'set_content_type');

    function set_content_type($content_type) {
        return 'text/html';
    }

    add_filter('wp_mail_charset', 'set_charset');

    function set_charset($charset) {
        return get_option('blog_charset');
    }

    wp_mail($student_email, $subject, nl2br($message));
}

/* Get Student Invitation with Passcode to a Course E-mail data */

function coursepress_get_invitation_passcode_from_name() {
    return get_option('invitation_passcode_from_name', get_option('blogname'));
}

function coursepress_get_invitation_passcode_from_email() {
    return get_option('invitation_passcode_from_email', get_option('admin_email'));
}

function coursepress_get_invitation_passcode_email_subject() {
    return get_option('invitation_passcode_email_subject', 'Invitation to a Course (Psss...for selected ones only)');
}

function coursepress_get_invitation_content_passcode_email() {
    $default_invitation_content_passcode_email = sprintf(__('Hi %1$s,

we would like to invite you to participate in the course: "%2$s"

Since the course is only for selected ones, it is passcode protected. Here is the passcode for you: %6$s

What is all about: 
%3$s

Check this page for more info on the course: %4$s

If you have any question feel free to contact us.

Yours sincerely,
%5$s Team'), 'STUDENT_FIRST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', 'COURSE_ADDRESS', 'WEBSITE_ADDRESS', 'PASSCODE');

    return get_option('invitation_content_passcode_email', $default_invitation_content_passcode_email);
}

/* Get Student Invitation to a Course E-mail data */

function coursepress_get_invitation_from_name() {
    return get_option('invitation_from_name', get_option('blogname'));
}

function coursepress_get_invitation_from_email() {
    return get_option('invitation_from_email', get_option('admin_email'));
}

function coursepress_get_invitation_email_subject() {
    return get_option('invitation_email_subject', 'Invitation to a Course');
}

function coursepress_get_invitation_content_email() {
    $default_invitation_content_email = sprintf(__('Hi %1$s,

we would like to invite you to participate in the course: "%2$s"

What is all about: 
%3$s

Check this page for more info on the course: %4$s

If you have any question feel free to contact us.

Yours sincerely,
%5$s Team'), 'STUDENT_FIRST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', 'COURSE_ADDRESS', 'WEBSITE_ADDRESS');
    return get_option('invitation_content_email', $default_invitation_content_email);
}

/* Get registration email data */

function coursepress_get_registration_from_name() {
    return get_option('registration_from_name', get_option('blogname'));
}

function coursepress_get_registration_from_email() {
    return get_option('registration_from_email', get_option('admin_email'));
}

function coursepress_get_registration_email_subject() {
    return get_option('registration_email_subject', 'Registration Status');
}

function coursepress_get_registration_content_email() {
    $default_registration_content_email = sprintf(__('Hi %1$s,

Congratulations! You have registered account with %2$s successfully! You may log into your account here: %3$s.

Get started by exploring our courses here: %4$s

Yours sincerely,
%5$s Team'), 'STUDENT_FIRST_NAME', 'BLOG_NAME', 'LOGIN_ADDRESS', 'COURSES_ADDRESS', 'WEBSITE_ADDRESS');

    return get_option('registration_content_email', $default_registration_content_email);
}

function coursepress_admin_notice($notice, $type = 'updated') {
    if ($notice <> '') {
        echo '<div class="' . $type . '"><p>' . $notice . '</p></div>';
    }
}

function coursepress_get_number_of_instructors() {

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => 'instructor',
        'count_total' => false,
        'fields' => array('display_name', 'ID'),
        'who' => ''
    );

    $instructors = get_users($args);

    return count($instructors);
}

function coursepress_instructors_avatars($course_id, $remove_buttons = true, $just_count = false) {
    global $post_id;

    $content = '';

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => 'instructor',
        'meta_key' => 'course_' . $course_id,
        'meta_value' => $course_id,
        'meta_compare' => '',
        'meta_query' => array(),
        'include' => array(),
        'exclude' => array(),
        'orderby' => 'display_name',
        'order' => 'ASC',
        'offset' => '',
        'search' => '',
        'number' => '',
        'count_total' => false,
        'fields' => array('display_name', 'ID'),
        'who' => ''
    );

    $instructors = get_users($args);

    if ($just_count == true) {
        return count($instructors);
    } else {

        foreach ($instructors as $instructor) {
            if ($remove_buttons) {
                $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-remove"><a href="javascript:removeInstructor(' . $instructor->ID . ');"></a></div>' . get_avatar($instructor->ID, 80) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
            } else {
                $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-remove"></div>' . get_avatar($instructor->ID, 80) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
            }
        }

        echo $content;
    }
}

function coursepress_instructors_avatars_array($args = array()) {

    $content = '<script type="text/javascript" language="JavaScript">        
    var instructor_avatars = new Array();';

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => 'instructor',
        'meta_key' => (isset($args['meta_key']) ? $args['meta_key'] : ''),
        'meta_value' => (isset($args['meta_value']) ? $args['meta_value'] : ''),
        'meta_compare' => '',
        'meta_query' => array(),
        'include' => array(),
        'exclude' => array(),
        'orderby' => 'display_name',
        'order' => 'ASC',
        'offset' => '',
        'search' => '',
        'number' => '',
        'count_total' => false,
        'fields' => array('display_name', 'ID'),
        'who' => ''
    );

    $instructors = get_users($args);

    foreach ($instructors as $instructor) {
        $content .= 'instructor_avatars[' . $instructor->ID . '] = "' . get_avatar($instructor->ID, 80, "", $instructor->display_name) . '";';
    }

    $content .= '</script>';
    echo $content;
}

function coursepress_students_drop_down() {
    $content = '';
    $content .= '<select name="students">';

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => 'student',
        'meta_key' => '',
        'meta_value' => '',
        'meta_compare' => '',
        'meta_query' => array(),
        'include' => array(),
        'exclude' => array(),
        'orderby' => 'display_name',
        'order' => 'ASC',
        'offset' => '',
        'search' => '',
        'number' => '',
        'count_total' => false,
        'fields' => array('display_name', 'ID'),
        'who' => ''
    );

    $students = get_users($args);

    $number = 0;
    foreach ($students as $student) {
        $number++;
        $content .= '<option value="' . $student->ID . '">' . $student->display_name . '</option>';
    }
    $content .= '</select>';

    if ($number == 0) {
        $content = '';
    }

    echo $content;
}

function coursepress_instructors_drop_down() {
    $content = '';
    $content .= '<select name="instructors" id="instructors">';

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => 'instructor',
        'meta_key' => '',
        'meta_value' => '',
        'meta_compare' => '',
        'meta_query' => array(),
        'include' => array(),
        'exclude' => array(),
        'orderby' => 'display_name',
        'order' => 'ASC',
        'offset' => '',
        'search' => '',
        'number' => '',
        'count_total' => false,
        'fields' => array('display_name', 'ID'),
        'who' => ''
    );

    $instructors = get_users($args);

    $number = 0;
    foreach ($instructors as $instructor) {
        $number++;
        $content .= '<option value="' . $instructor->ID . '">' . $instructor->display_name . '</option>';
    }
    $content .= '</select>';

    if ($number == 0) {
        $content = '';
    }

    echo $content;
}

if (!function_exists('delete_user_meta_by_key')) {

    function delete_user_meta_by_key($meta_key) {
        global $wpdb;

        if ($wpdb->query($wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE meta_key = %s", $meta_key))) {
            return true;
        } else {
            return false;
        }
    }

}

function get_the_post_excerpt($id = false, $length = 55) {
    global $post;

    if ($id != $post->ID) {
        $post = get_page($id);
    }

    if (!$excerpt = trim($post->post_excerpt)) {
        $excerpt = $post->post_content;
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = apply_filters('the_content', $excerpt);
        $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
        $excerpt = strip_tags($excerpt);
        $excerpt_length = apply_filters('excerpt_length', $length);
        $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');

        $words = preg_split("/[\n\r\t ]+/", $excerpt, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > $excerpt_length) {
            array_pop($words);
            $excerpt = implode(' ', $words);
            $excerpt = $excerpt . $excerpt_more;
        } else {
            $excerpt = implode(' ', $words);
        }
    }

    return $excerpt;
}

function get_the_course_excerpt($id = false, $length = 55) {
    global $post;

    if (empty($post)) {
        $post = new StdClass;
        $post->ID = 0;
    }

    $old_post = $post;
    if ($id != $post->ID) {
        $post = get_page($id);
    }

    if (!$excerpt = trim($post->post_excerpt)) {
        $excerpt = $post->post_content;
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = apply_filters('the_content', $excerpt);
        $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
        $excerpt = strip_tags($excerpt);
        $excerpt_length = apply_filters('excerpt_length', $length);
        $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');

        $words = preg_split("/[\n\r\t ]+/", $excerpt, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > $excerpt_length) {
            array_pop($words);
            $excerpt = implode(' ', $words);
            $excerpt = $excerpt . $excerpt_more;
        } else {
            $excerpt = implode(' ', $words);
        }
    }

    $post = $old_post;

    return $excerpt;
}

function get_number_of_days_between_dates($start_date, $end_date) {

    $startTimeStamp = strtotime($start_date);
    $endTimeStamp = strtotime($end_date);

    $timeDiff = abs($endTimeStamp - $startTimeStamp);

    $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
    $numberDays = intval($numberDays);

    return $numberDays;
}

if (!function_exists('coursepress_register_module')) {
    //to do
}

function sp2nbsp($string) {
    return str_replace(' ', '&nbsp;', $string);
}

if (!function_exists('get_userdatabynicename')) :

    function get_userdatabynicename($user_nicename) {
        global $wpdb;
        $user_nicename = sanitize_title($user_nicename);

        if (empty($user_nicename))
            return false;

        if (!$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_nicename = '$user_nicename' LIMIT 1"))
            return false;

        $wpdb->hide_errors();
        $metavalues = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = '$user->ID'");
        $wpdb->show_errors();

        if ($metavalues) {
            foreach ($metavalues as $meta) {
                $value = maybe_unserialize($meta->meta_value);
                $user->{$meta->meta_key} = $value;

                // We need to set user_level from meta, not row 
                if ($wpdb->prefix . 'user_level' == $meta->meta_key)
                    $user->user_level = $meta->meta_value;
            }
        }

        // For backwards compat. 
        if (isset($user->first_name))
            $user->user_firstname = $user->first_name;
        if (isset($user->last_name))
            $user->user_lastname = $user->last_name;
        if (isset($user->description))
            $user->user_description = $user->description;

        return $user;
    }

endif;

function coursepress_get_count_of_users($role = '') {
    $result = count_users();
    if ($role == '') {
        return $result['total_users'];
    } else {
        foreach ($result['avail_roles'] as $roles => $count)
            if ($roles == $role) {
                return $count;
            }
    }
    return 0;
}

function curPageURL() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

if (!function_exists('coursepress_register_module')) {

    function coursepress_register_module($module_name, $class_name, $section) {
        global $coursepress_modules;

        if (!is_array($coursepress_modules)) {
            $coursepress_modules = array();
        }

        if (class_exists($class_name)) {
            $coursepress_modules[$section][$module_name] = $class_name;
        } else {
            return false;
        }
    }

}

if (!function_exists('cp_write_log')) {

    function cp_write_log($log) {
        //if ( true === WP_DEBUG ) {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
        //}
    }

}

if (!function_exists('is_plugin_network_active')) {

    function is_plugin_network_active($plugin_file) {
        if (is_multisite()) {
            return ( array_key_exists($plugin_file, maybe_unserialize(get_site_option('active_sitewide_plugins'))) );
        }
    }

}

function get_terms_dropdown($taxonomies, $args) {
    $myterms = get_terms($taxonomies, $args);
    $output = "<select>";
    foreach ($myterms as $term) {
        $root_url = get_bloginfo('url');
        $term_taxonomy = $term->taxonomy;
        $term_slug = $term->slug;
        $term_name = $term->name;
        $link = $root_url . '/' . $term_taxonomy . '/' . $term_slug;
        $output .="<option value='" . $link . "'>" . $term_name . "</option>";
    }
    $output .="</select>";
    return $output;
}

?>
