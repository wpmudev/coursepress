<?php
$user = coursepress_get_user();
$courses = $user->get_facilitated_courses();
$statuses = array(
    'active' => __( 'Active', 'cp' ),
    'ended' => __( 'Ended', 'cp' ),
    'future' => __( 'Not started', 'cp' ),
);

if ( ! empty( $courses ) ) : ?>

	<h3><?php esc_html_e( 'Courses I Facilitated', 'cp' ); ?></h3>
	<table class="coursepress-table courses-table">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Course', 'cp' ); ?></th>
			<th><?php esc_html_e( 'Students', 'cp' ); ?></th>
            <th><?php esc_html_e( 'Status', 'cp' ); ?></th>
            <th></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $course ) : ?>
			<tr>
				<td>
					<a href="<?php echo esc_url( $course->get_permalink() ); ?>"><?php echo esc_html( $course->post_title ); ?></a>
				</td>
				<td>
					<?php echo (int) $course->count_students(); ?>
				</td>
                <td>
                    <?php
                        $course_status = $course->get_status();
                        echo esc_html( $statuses[ $course_status ] );
                    ?>
                </td>
                <td align="right">
                    <a href="<?php echo esc_url($course->get_edit_url()); ?>" class="button">
                        <i class="fa fa-pencil"></i>
						<?php esc_html_e( 'Edit', 'cp' ); ?>
                    </a>
                </td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php
endif;
