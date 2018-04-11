<?php
/**
 * @var $columns
 * @var $hidden_columns
 * @var $courses
 * @var $course CoursePress_Course
 */
?>
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
                    <tr style="font-weight:bold; font-size: 4mm; background-color: <?php echo esc_attr( $colors['unit_bg'] ); ?> '; color: <?php echo esc_attr( $colors['unit'] ); ?>">
                    <th colspan="3"><?php echo esc_html( $unit->post_title ); ?></th>
                        </tr>
<?php
	$assessable_modules = 0;
foreach ( $unit->steps as $module_id => $module ) {
	if ( ! $module->assessable ) {
		continue;
	}
		$assessable_modules++;
		$date_display = __( 'Not yet submitted', 'cp' );
	if ( isset( $student->progress['units'][ $module->unit_id ]['responses'][ $module_id ]['date'] ) ) {
		$date_display = $student->progress['units'][ $module->unit_id ]['responses'][ $module_id ]['date'];
	}
		$module_progress = '--';
	if ( isset( $student->progress['completion'][ $module->unit_id ]['steps'][ $module_id ]['progress'] ) ) {
		$module_progress = sprintf(
			'%d%%',
			$student->progress['completion'][ $module->unit_id ]['steps'][ $module_id ]['progress']
		);
	}
?>
<tr style="font-size: 4mm; background-color: <?php echo esc_attr( $colors['item_bg'] ); ?>; color: <?php echo esc_attr( $colors['item'] ); ?>">
<td style="border-bottom: 0.5mm solid <?php echo esc_attr( $colors['item_line'] ); ?>"><?php echo esc_html( $module->post_title ); ?></td>
<td style="border-bottom: 0.5mm solid <?php echo esc_attr( $colors['item_line'] ); ?>"><?php echo esc_html( $date_display ); ?></td>
<td style="border-bottom: 0.5mm solid <?php echo esc_attr( $colors['item_line'] ); ?>"><?php echo esc_html( $module_progress ); ?></td>
</tr>
<?php
}
if ( empty( $assessable_modules ) ) {
?>
<tr style="font-style:oblique; font-size: 4mm; background-color:<?php echo esc_attr( $colors['item_bg'] ); ?>; color:<?php echo esc_attr( $colors['no_items'] ); ?>;">
<td colspan="3"><em><?php esc_html_e( 'No assessable items.', 'cp' ); ?></em></td>
						</tr>
<?php
}
?>
</tbody>
<?php
}
?>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-size: 4mm; background-color:<?php echo esc_attr( $colors['footer_bg'] ); ?>;color:<?php echo esc_attr( $colors['footer'] ); ?>;"><?php printf( __( 'Average response grade: %d%%', 'cp' ), $student->average ); ?></td>
                    <td style="text-align:right; font-size: 4mm; background-color:<?php echo esc_attr( $colors['footer_bg'] ); ?>;color:<?php echo esc_attr( $colors['footer'] ); ?>;"><?php printf( __( 'Total Average: %d%%', 'cp' ), $student->course_average ); ?></td>
                </tr>
            </tfoot>
</table>
