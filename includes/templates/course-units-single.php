<?php
global $wp;

$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;

echo do_shortcode( '[course_unit_archive_submenu]' );
?>
<h2><?php echo get_the_title( (int) $unit_id ); ?></h2>

<?php
echo do_shortcode( '[course_unit_details unit_id="' . $unit_id . '" field="unit_page_title"]' );
?>

<?php
if ( $paged == 1 ) {
	echo do_shortcode( '[course_unit_details unit_id="' . $unit_id . '" field="post_content"]' );
}
?>
<?php
Unit_Module::get_modules_front( (int) $unit_id );
?>
