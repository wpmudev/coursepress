<?php
global $wp;
$course_id			 = do_shortcode( '[get_parent_course_id]' );
$course_id			 = (int) $course_id;
$unit = new Unit();
$unit_id = $unit->get_unit_id_by_name( $wp->query_vars['unitname'], $course_id );
CoursePress::instance()->check_access( $course_id, $unit_id );
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
