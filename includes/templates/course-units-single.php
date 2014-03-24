<?php

global $wp;
$paged = $wp->query_vars['paged'] ? absint($wp->query_vars['paged']) : 1;
echo do_shortcode('[course_breadcrumbs course_id="' . $course_id . '" type="unit_single"]');

if ($paged == 1) {
    echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="post_content"]');
}
?>
<?php

$module = new Unit_Module();
$module->get_modules_front($unit_id);
?>