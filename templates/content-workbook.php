<?php
/**
 * @var int $user_id
 * @var int $course_id
 */
$workbook_data = coursepress_get_student_workbook_data( $user_id, $course_id );

if ( ! empty( $workbook_data ) ) : ?>

<table class="coursepress-table workbook-table">
	<?php foreach ( $workbook_data as $data ) : ?>
		<tr class="data-<?php echo esc_attr( $data['type'] ); ?> row-<?php echo esc_attr( $data['type'] ); ?>">
			<td><?php echo esc_html( $data['title'] ); ?></td>
			<td></td>
			<td>
				<?php
				if ( isset( $data['grade'] ) ) :
					echo esc_html( $data['grade'] );
				endif;
				?>
			</td>
			<td align="right">
			<?php
				//if ( 'module' !== $data['type'] ) :
					if ( 'unit' === $data['type'] ) :
						esc_html_e( 'Progress: ', 'cp' );
					endif;
					echo esc_html( $data['progress'] . '%' );
				//endif;
			?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php
endif;
