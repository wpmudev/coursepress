<?php
global $wp;

$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;

echo do_shortcode( '[course_unit_archive_submenu]' );
?>
<h2><?php echo get_the_title( (int) $unit_id ); ?></h2>

<?php
echo do_shortcode( '[course_unit_page_title unit_id="' . $unit_id . '"]' );
?>

<?php
Unit_Module::get_modules_front( (int) $unit_id );
?>
