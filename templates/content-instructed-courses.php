<?php
$user    = coursepress_get_user();
$courses = $user->get_instructed_courses();
if ( ! empty( $courses ) ) : ?>
	<h3><?php _e( 'Courses I Manage', 'cp' ); ?></h3>
	<table class="coursepress-table courses-table">
		<thead>
		<tr>
			<th><?php _e( 'Course', 'cp' ); ?></th>
			<th><?php _e( 'Students', 'cp' ); ?></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $course ) : ?>
			<tr>
				<td>
					<a href="<?php echo esc_url( $course->get_permalink() ); ?>"><?php echo $course->post_title; ?></a>
				</td>
				<td>
					<?php echo (int) $course->count_students(); ?>
				</td>
				<td align="right">
					<a href="<?php echo esc_url( $course->get_edit_url() ); ?>" class="button">
						<i class="fa fa-pencil"></i>
						<?php _e( 'Edit', 'cp' ); ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
