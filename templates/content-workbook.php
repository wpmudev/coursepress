<?php
/**
 * @var int $user_id
 * @var int $course_id
 */
$workbook_data = coursepress_get_student_workbook_data( $user_id, $course_id );

if ( ! empty( $workbook_data ) ) : ?>

<table class="coursepress-table workbook-table">
	<?php foreach ( $workbook_data as $data ) : ?>
		<tr class="data-<?php echo $data['type']; ?> row-<?php echo $data['type']; ?>">
			<td><?php echo $data['title']; ?></td>
			<td></td>
			<td>
				<?php

				if ( isset( $data['grade'] ) ) :
					echo $data['grade'];
				endif;
				?>
			</td>
			<td align="right">
				<?php
				//if ( 'module' !==$data['type'] ) :
					if ( 'unit' === $data['type'] ) :
						_e( 'Progress: ', 'cp' );
					endif;
					echo $data['progress'] . '%';
				//endif;
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>
