<?php
/**
 * @var int $user_id
 * @var int $course_id
 */
$data = coursepress_get_student_workbook_data( $user_id, $course_id );


d($data);


if ( ! empty( $data ) ) : ?>

<table class="coursepress-table table-workbook">
	<?php foreach ( $data as $data ) : ?>
		<tr class="data-<?php echo $data['type']; ?>">
			<td><?php echo $data['title']; ?></td>
			<td></td>
			<td>
				<?php if ( isset( $data['grade'] ) ) :
					echo $data['grade'];
				endif; ?>
			</td>
			<td align="right"><?php
				//if ( 'module' != $data['type'] ) :
					if ( 'unit' == $data['type'] ) :
						_e( 'Progress: ', 'cp' );
					endif;
					echo $data['progress'] . '%';
				//endif;
			?></td>
		</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>
