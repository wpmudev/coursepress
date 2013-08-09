<?php

echo do_shortcode('[course_breadcrumbs course_id="' . $course_id . '" type="unit_single"]');
echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="post_content"]');
?>
<?php

do_shortcode('[unit_discussion]');


$comments_args = array(
    'author_email' => '',
    'ID' => '',
    'karma' => '',
    'number' => '',
    'offset' => '',
    'orderby' => '',
    'order' => 'DESC',
    'parent' => '',
    'post_id' => $unit_id,
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

$args = array(
    'walker' => null,
    'max_depth' => '',
    'style' => 'ul',
    'callback' => null,
    'end-callback' => null,
    'type' => 'all',
    'reply_text' => 'Reply',
    'page' => '',
    'per_page' => '',
    'avatar_size' => 32,
    'reverse_top_level' => null,
    'reverse_children' => '',
    'format' => 'xhtml', //or html5 @since 3.6
    'short_ping' => false // @since 3.6
);

wp_list_comments($args, get_comments($comments_args));
?>