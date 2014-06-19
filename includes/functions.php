<?php

function non_nonce_url() {
    
}

function url_origin($s, $use_forwarded_host = false) {
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host = false) {
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

function preg_array_key_exists($pattern, $array) {
    $keys = array_keys($array);
    return (int) preg_grep($pattern, $keys);
}

function cp_get_fragment() {
    $url = parse_url($_SERVER["REQUEST_URI"]);
    return $url["fragment"];
}

function is_chat_plugin_active() {
    $plugins = get_option('active_plugins');

    if (is_multisite()) {
        $active_sitewide_plugins = get_site_option("active_sitewide_plugins");
    } else {
        $active_sitewide_plugins = array();
    }

    $required_plugin = 'wordpress-chat/wordpress-chat.php';

    if (in_array($required_plugin, $plugins) || is_plugin_network_active($required_plugin) || preg_grep('/^wordpress-chat.*/', $plugins) || preg_array_key_exists('/^wordpress-chat.*/', $active_sitewide_plugins)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Unit unit module pagination
 */
function coursepress_unit_module_pagination($unit_id, $pages_num, $check_is_last_page = false) {
    global $wp, $wp_query, $paged, $coursepress_modules;

    $modules_class = new Unit_Module();

    if (!isset($unit_id)) {// || !is_singular()
        echo '<br clear="all"><div class="navigation module-pagination" id="navigation-pagination"></div>';
        return;
    }

    $paged = isset($wp->query_vars['paged']) ? absint($wp->query_vars['paged']) : 1;

    $max = intval($pages_num); //number of page-break modules + 1

    $wp_query->max_num_pages = $max;

    if ($check_is_last_page) {
        if ($max <= 1 || ($max == $paged)) {
            return true;
        } else {
            return false;
        }
    }

    if ($wp_query->max_num_pages <= 1) {
        echo '<br clear="all"><div class="navigation module-pagination" id="navigation-pagination"></div>';
        return;
    }

    echo '<br clear="all"><div class="navigation module-pagination" id="navigation-pagination"><ul>' . "\n";

    for ($link_num = 1; $link_num <= $max; $link_num++) {
        $class = ($paged == $link_num ? ' class="active"' : '');

        printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link_num)), $link_num);
    }

    echo '</ul></div>' . "\n";
}

function coursepress_unit_module_pagination_ellipsis($unit_id, $pages_num) {
    global $wp, $wp_query, $paged, $coursepress_modules;

    $modules_class = new Unit_Module();

    if (!isset($unit_id) || !is_singular()) {
        return;
    }


    $paged = $wp->query_vars['paged'] ? absint($wp->query_vars['paged']) : 1;

    $max = intval($pages_num); //number of page-break modules + 1

    $wp_query->max_num_pages = $max;

    if ($wp_query->max_num_pages <= 1)
        return;

    /** 	Add current page to the array */
    if ($paged >= 1)
        $links[] = $paged;

    /** 	Add the pages around the current page to the array */
    if ($paged >= 3) {
        $links[] = $paged - 1;
        $links[] = $paged - 2;
    }

    if (( $paged + 2 ) <= $max) {
        $links[] = $paged + 2;
        $links[] = $paged + 1;
    }


    echo '<br clear="all"><div class="navigation"><ul>' . "\n";

    /** 	Previous Post Link */
    if (get_previous_posts_link())
        printf('<li>%s</li>' . "\n", get_previous_posts_link('<span class="meta-nav">&larr;</span>'));

    /** 	Link to first page, plus ellipses if necessary */
    if (!in_array(1, $links)) {
        $class = 1 == $paged ? ' class="active"' : '';

        printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link(1)), '1');

        if (!in_array(2, $links))
            echo '<li>…</li>';
    }

    /** 	Link to current page, plus 2 pages in either direction if necessary */
    sort($links);

    foreach ((array) $links as $link) {
        $class = $paged == $link ? ' class="active"' : '';
        printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link)), $link);
    }

    /** 	Link to last page, plus ellipses if necessary */
    if (!in_array($max, $links)) {
        if (!in_array($max - 1, $links))
            echo '<li>…</li>' . "\n";

        $class = $paged == $max ? ' class="active"' : '';
        printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($max)), $max);
    }

    $nextpage = intval($paged) + 1;
    /** 	Next Post Link */
    if ($nextpage <= $pages_num) {
        $attr = apply_filters('next_posts_link_attributes', '');


        printf('<li>%s</li>' . "\n", get_next_posts_link('<span class="meta-nav">&rarr;</span>'));
    }

    echo '</ul></div>' . "\n";
}

function coursepress_unit_pages($unit_id) {
    $pages_num = 1;

    $module = new Unit_Module;
    $modules = $module->get_modules($unit_id);

    foreach ($modules as $mod) {
        if ($module->get_module_type($mod->ID) == 'page_break_module') {
            $pages_num++;
        }
    }

    return $pages_num;
}

//get_site_option instead of get_option

function coursepress_send_email($email_args = array()) {

    if ($email_args['email_type'] == 'student_registration') {
        global $course_slug;
        $student_email = $email_args['student_email'];
        $subject = coursepress_get_registration_email_subject();
        $courses_address = trailingslashit(site_url()) . trailingslashit($course_slug);

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
        $tags_replaces = array($email_args['student_first_name'], $email_args['student_last_name'], $course->details->post_title, $course->details->post_excerpt, $course->get_permalink(), site_url(), $course->details->passcode);

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

    wp_mail($student_email, stripslashes($subject), stripslashes(nl2br($message)));
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
%5$s Team'), 'STUDENT_FIRST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', '<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>', 'PASSCODE');

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
%5$s Team'), 'STUDENT_FIRST_NAME', 'COURSE_NAME', 'COURSE_EXCERPT', '<a href="COURSE_ADDRESS">COURSE_ADDRESS</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>');
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
%5$s Team'), 'STUDENT_FIRST_NAME', 'BLOG_NAME', '<a href="LOGIN_ADDRESS">LOGIN_ADDRESS</a>', '<a href="COURSES_ADDRESS">COURSES_ADDRESS</a>', '<a href="WEBSITE_ADDRESS">WEBSITE_ADDRESS</a>');

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
        //'role' => 'instructor',
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

    //coursepress_courses_cap

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        //'role' => 'instructor',
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
                $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-status"></div><div class="instructor-remove"><a href="javascript:removeInstructor(' . $instructor->ID . ');"><i class="fa fa-times-circle cp-move-icon remove-btn"></i></a></div>' . get_avatar($instructor->ID, 80) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
            } else {
                $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-status"></div><div class="instructor-status"></div><div class="instructor-remove"></div>' . get_avatar($instructor->ID, 80) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
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
        //'role' => 'instructor',
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
        $content .= "instructor_avatars[" . $instructor->ID . "] = '" . str_replace("'", '"', get_avatar($instructor->ID, 80, "", $instructor->display_name)) . "';";
    }

    $content .= '</script>';
    echo $content;
}

function coursepress_students_drop_down() {
    $content = '';
    $content .= '<select name="students" data-placeholder="' . __('Choose a Student...', 'cp') . '" class="chosen-select">';

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        'role' => '',
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

function coursepress_instructors_drop_down($class = '') {
    $content = '';
    $content .= '<select name="instructors" id="instructors" data-placeholder="' . __('Choose a Course Instructor...', 'cp') . '" class="' . $class . '">';

    $args = array(
        'blog_id' => $GLOBALS['blog_id'],
        //'role' => 'instructor',
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
        'class' => $class,
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
        $post->post_excerpt = '';
        $post->post_content = '';
    }

    $old_post = $post;

    if ($id != $post->ID) {
        $post = get_page($id);
    }

    $excerpt = trim($post->post_excerpt);

    if (!$excerpt) {
        $excerpt = $post->post_content;
    }

    $excerpt = strip_shortcodes($excerpt);
    //$excerpt = apply_filters('the_content', $excerpt);
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

        if (!$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_nicename = %s LIMIT 1", $user_nicename)))
            return false;

        $wpdb->hide_errors();
        $metavalues = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = %d", $user->ID));
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

function natkrsort($array) {
    $keys = array_keys($array);
    natsort($keys);

    foreach ($keys as $k) {
        $new_array[$k] = $array[$k];
    }

    $new_array = array_reverse($new_array, true);

    return $new_array;
}

if (!function_exists('coursepress_register_module')) {

    function coursepress_register_module($module_name, $class_name, $section) {
        global $coursepress_modules, $coursepress_modules_labels, $coursepress_modules_descriptions, $coursepress_modules_ordered;

        //cp_write_log($_POST);

        if (!is_array($coursepress_modules)) {
            $coursepress_modules = array();
        }

        if (class_exists($class_name)) {
            $class = new $class_name();
            $coursepress_modules_labels[$module_name] = $class->label;
            $coursepress_modules_descriptions[$module_name] = $class->description;
            $coursepress_modules[$section][$module_name] = $class_name;
            $coursepress_modules_ordered[$section][$class->order] = $class_name;
        } else {
            return false;
        }

        //print_r($coursepress_modules_ordered);
    }

}

if (!function_exists('cp_write_log')) {

    function cp_write_log($message) {
        //if ( true === WP_DEBUG ) {
        $trace = debug_backtrace();
        $debug = array_shift($trace);
        $caller = array_shift($trace);

        if (true === WP_DEBUG) {
            if (is_array($message) || is_object($message)) {
                $class = isset($caller['class']) ? '[' . $caller['class'] . ']\n' : '';
                error_log($class . print_r($message, true));
            } else {
                $class = isset($caller['class']) ? $caller['class'] . ': ' : '';
                error_log($class . $message);
            }
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

function cp_in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && cp_in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function cp_suppress_errors() {
    ini_set('display_errors', 0);
    ini_set('scream.enabled', false);
}

function cp_show_errors() {
    ini_set('display_errors', 1);
    ini_set('scream.enabled', true);
}

function cp_replace_img_src($original_img_tag, $new_src_url) {
    $doc = new DOMDocument();
    $doc->loadHTML($original_img_tag);

    $tags = $doc->getElementsByTagName('img');
    if (count($tags) > 0) {
        $tag = $tags->item(0);
        $tag->setAttribute('src', $new_src_url);
        return $doc->saveXML($tag);
    }

    return false;
}

function callback_img($match) {
    list(, $img, $src) = $match;
    $new_src = str_replace('../wp-content', WP_CONTENT_URL, $src);
    return "$img=\"$new_src\" ";
}

function callback_link($match) {
    $new_url = str_replace('../wp-content', WP_CONTENT_URL, $match[0]);
    return $new_url;
}

function user_has_role($check_role, $user_id = NULL) {
    // Get user by ID, else get current user
    if ($user_id)
        $user = get_userdata($user_id);
    else
        $user = wp_get_current_user();

    // No user found, return
    if (empty($user))
        return FALSE;

    // Append administrator to roles, if necessary
    /* if (!in_array('administrator',$roles)) */
    $roles[] = '';

    // Loop through user roles
    foreach ($user->roles as $role) {
        // Does user have role
        if ($role == $check_role) {
            return TRUE;
        }
    }

    // User not in roles
    return FALSE;
}

/**
 * Numeric pagination
 */
if (!function_exists('coursepress_numeric_posts_nav')) {

    function coursepress_numeric_posts_nav($navigation_id = '') {

        if (is_singular())
            return;

        global $wp_query, $paged;
        /** Stop execution if there's only 1 page */
        if ($wp_query->max_num_pages <= 1)
            return;

        $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

        $max = intval($wp_query->max_num_pages);

        /** 	Add current page to the array */
        if ($paged >= 1)
            $links[] = $paged;

        /** 	Add the pages around the current page to the array */
        if ($paged >= 3) {
            $links[] = $paged - 1;
            $links[] = $paged - 2;
        }

        if (( $paged + 2 ) <= $max) {
            $links[] = $paged + 2;
            $links[] = $paged + 1;
        }

        if ($navigation_id != '') {
            $id = 'id="' . $navigation_id . '"';
        } else {
            $id = '';
        }

        echo '<div class="navigation" ' . $id . '><ul>' . "\n";

        /** 	Previous Post Link */
        if (get_previous_posts_link())
            printf('<li>%s</li>' . "\n", get_previous_posts_link('<span class="meta-nav">&larr;</span>'));

        /** 	Link to first page, plus ellipses if necessary */
        if (!in_array(1, $links)) {
            $class = 1 == $paged ? ' class="active"' : '';

            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link(1)), '1');

            if (!in_array(2, $links))
                echo '<li>…</li>';
        }

        /** 	Link to current page, plus 2 pages in either direction if necessary */
        sort($links);
        foreach ((array) $links as $link) {
            $class = $paged == $link ? ' class="active"' : '';
            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link)), $link);
        }

        /** 	Link to last page, plus ellipses if necessary */
        if (!in_array($max, $links)) {
            if (!in_array($max - 1, $links))
                echo '<li>…</li>' . "\n";

            $class = $paged == $max ? ' class="active"' : '';
            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($max)), $max);
        }

        /** 	Next Post Link */
        if (get_next_posts_link())
            printf('<li>%s</li>' . "\n", get_next_posts_link('<span class="meta-nav">&rarr;</span>'));

        echo '</ul></div>' . "\n";
    }

}

require_once('first-install.php');
?>
