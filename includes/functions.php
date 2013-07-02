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

function get_the_course_excerpt($id=false, $length = 55) {
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
?>
