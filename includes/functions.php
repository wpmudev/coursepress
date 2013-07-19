<?php

function coursepress_admin_notice($notice, $type = 'updated') {
    if ($notice <> '') {
        echo '<div class="' . $type . '"><p>' . $notice . '</p></div>';
    }
}

function coursepress_instructors_avatars($course_id) {
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

    foreach ($instructors as $instructor) {
        $content .= '<div class="instructor-avatar-holder" id="instructor_holder_' . $instructor->ID . '"><div class="instructor-remove"><a href="javascript:removeInstructor(' . $instructor->ID . ');"></a></div>' . get_avatar($instructor->ID, 80) . '<span class="instructor-name">' . $instructor->display_name . '</span></div><input type="hidden" id="instructor_' . $instructor->ID . '" name="instructor[]" value="' . $instructor->ID . '" />';
    }

    echo $content;
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
        $content .= "instructor_avatars[" . $instructor->ID . "] = '" . get_avatar($instructor->ID, 80, "", $instructor->display_name) . "';";
    }

    $content .= '</script>';
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

/*
  if (!function_exists('M_register_rule')) {

  function M_register_rule($rule_name, $class_name, $section) {

  global $M_Rules, $M_SectionRules;

  if (!is_array($M_Rules)) {
  $M_Rules = array();
  }

  if (!is_array($M_SectionRules)) {
  $M_SectionRules = array();
  }

  if (class_exists($class_name)) {
  $M_SectionRules[$section][$rule_name] = $class_name;
  $M_Rules[$rule_name] = $class_name;
  } else {
  return false;
  }
  }

  }
 */

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
?>
