<?php

global $wp;

$paged = isset($wp->query_vars['paged']) ? absint($wp->query_vars['paged']) : 1;

echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="parent_course"]');
do_shortcode('[course_unit_archive_submenu]');
?>

<?php echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="unit_page_title"]'); ?>

<?php

if ( $paged == 1 ) {
    echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="post_content"]');
}
?>
<?php

$module = new Unit_Module();
$module->get_modules_front($unit_id);
?>
