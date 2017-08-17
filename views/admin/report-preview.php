<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
<div class="wrap coursepress-wrap" id="coursepress-reports-list">
<h1 class="wp-heading-inline"><?php
_e( 'Report: ', 'cp' );
echo ' ';
echo $course->post_title;
?></h1>
    <div class="coursepress-page coursepress-page-report">
<?php
echo '<h3 class="cp-flex">';
echo '<span class="gravatar">';
echo $student->get_avatar( 60 );
echo '</span>';
echo ' ';
echo '<span class="user_login">';
echo $student->user_login;
echo '</span>';
echo ' ';
echo '<span class="display_name">(';
echo $student->display_name;
echo ')</span>';
echo '</h3>';
?>
        <table class="coursepress-table">
<?php
foreach ( $units as $unit ) {



?>
                    <tbody>
                    <tr style="font-weight:bold; font-size: 4mm; background-color: <?php esc_attr_e( $colors['unit_bg'] ); ?> '; color: <?php esc_attr_e( $colors['unit'] ); ?>">
                    <th colspan="3"><?php esc_html_e( $unit->post_title ); ?></th>
                        </tr>
<?php
	$assessable_modules = 0;
foreach ( $unit->unit_modules_with_steps as $id => $module_with_steps ) {
	foreach ( $module_with_steps['steps'] as $module_id => $module ) {
		if ( 1 != $module->assessable ) {
			continue;
		}
		$assessable_modules++;
		$date_display = '--';
?>
<tr style="font-size: 4mm; background-color: <?php esc_attr_e( $colors['item_bg'] ); ?>; color: <?php esc_attr_e( $colors['item'] ); ?>">
<td style="border-bottom: 0.5mm solid <?php esc_attr_e( $colors['item_line'] ); ?>"><?php esc_html_e( $module->post_title ); ?></td>
<td style="border-bottom: 0.5mm solid <?php esc_attr_e( $colors['item_line'] ); ?>"><?php esc_html_e( $date_display ); ?></td>
<td style="border-bottom: 0.5mm solid <?php esc_attr_e( $colors['item_line'] ); ?>"><?php esc_html_e( $student->progress['completion'][ $module->unit_id ]['steps'][ $module_id ]['progress'] ); ?></td>
</tr>
<?php
	}
}
if ( empty( $assessable_modules ) ) {
?>
<tr style="font-style:oblique; font-size: 4mm; background-color:<?php esc_attr_e( $colors['item_bg'] ); ?>; color:<?php esc_attr_e( $colors['no_items'] ); ?>;">
<td colspan="3"><em><?php esc_html_e( 'No assessable items.', 'cp' ); ?></em></td>
						</tr>
<?php
}
?>
</tbody>
<?php
}
/*
?>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-size: 4mm; background-color:<?php esc_attr_e( $colors['footer_bg'] );?>;color:<?php esc_attr_e( $colors['footer'] );?>;"><?php printf( __( 'Average response grade: %d%%', 'cp' ), $student->average_display ); ?></td>
                    <td style="text-align:right; font-size: 4mm; background-color:<?php esc_attr_e( $colors['footer_bg'] ); ?>;color:<?php esc_attr_e( $colors['footer'] ); ?>;"><?php printf( __( 'Total Average: %d%%', 'cp' ), $student->progress['completion']['progress'] ); ?></td>
                </tr>
            </tfoot>
<?php
 */
?>
            </table>
    </div>
</div>
