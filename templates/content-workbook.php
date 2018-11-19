<?php
/**
 * @var int $user_id
 * @var int $course_id
 */
$workbook_data = coursepress_get_student_workbook_data( $user_id, $course_id );
if ( ! empty( $workbook_data ) ) {
	echo '<table class="coursepress-table workbook-table">';
	$i = 0;
	foreach ( $workbook_data as $data ) {
		if ( 'unit' === $data['type'] ) {
			if ( 0 < $i ) {
				echo '</tbody>';
				echo '<tbody class="separator"><tr><td colspan="4"></td></tr>';
			}
			echo '<tbody>';
		}
		$i++;
		printf(
			'<tr class="data-%s row-%s">',
			esc_attr( $data['type'] ),
			esc_attr( $data['type'] )
		);
		echo '<td>';
		if ( 'unit' === $data['type'] ) {
			echo '<span>';
		}
		echo esc_html( $data['title'] );
		if ( 'unit' === $data['type'] ) {
			echo '</span>';
		}
		echo '</td>';
		echo '<td></td>';
		echo '<td>';
		if ( isset( $data['grade'] ) ) {
			echo esc_html( $data['grade'] );
		}
		echo '</td>';
		echo '<td align="right" class="cp-progress">';
		if ( 'unit' === $data['type'] ) {
			_e( 'Progress: ', 'cp' );
		}
		echo esc_html( $data['progress'] ) . '%';
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
}

