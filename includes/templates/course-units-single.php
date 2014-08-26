<?php

global $wp;

$paged = isset($wp->query_vars['paged']) ? absint($wp->query_vars['paged']) : 1;

do_shortcode('[course_unit_archive_submenu]');

echo sprintf( __('<h2>%s</h2>', 'cp'), get_the_title( $unit_id ) );
//cp_write_log(do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="unit_page_title"]'));
?>

<?php 
	$show_title = get_post_meta( $unit_id, 'show_page_title', true );
	
	if ( isset( $show_title[ $paged - 1 ] ) && 'yes' == $show_title[ $paged - 1 ] ) {
		echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="unit_page_title"]');		
	}

?>

<?php

if ( $paged == 1 ) {
    echo do_shortcode('[course_unit_details unit_id="' . $unit_id . '" field="post_content"]');
}
?>
<?php

$module = new Unit_Module();
$module->get_modules_front($unit_id);
?>
