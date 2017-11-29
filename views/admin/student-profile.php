<?php
/**
 * @var $student CoursePress_User
 * @var $courses array
 */
?>
<div class="coursepress-wrap">
	<div class="coursepress-student-data">
		<div class="coursepress-avatar">
			<span><?php echo get_avatar( $student->__get( 'user_email' ), 128 ); ?></span>
		</div>
		<div class="coursepress-data-table">
			<ul>
				<li>
					<span><?php esc_html_e( 'Student ID', 'cp' ); ?></span>
					<?php echo $student->__get( 'ID' ); ?>
				</li>
				<li>
					<span><?php esc_html_e( 'First Name', 'cp' ); ?></span>
					<?php echo $student->__get( 'first_name' ); ?>
				</li>
				<li>
					<span><?php esc_html_e( 'Last Name', 'cp' ); ?></span>
					<?php echo $student->__get( 'last_name' ); ?>
				</li>
				<li>
					<span><?php esc_html_e( 'Display Name', 'cp' ); ?></span>
					<?php echo $student->__get( 'display_name' ); ?>
				</li>
				<li><span><?php esc_html_e( 'Email', 'cp' ); ?></span><?php echo $student->__get( 'user_email' ); ?>
				</li>
				<li>
					<span><?php esc_html_e( 'Registered', 'cp' ); ?></span><?php echo $student->__get( 'user_registered' ); ?>
				</li>
			</ul>
		</div>
	</div>
	<table class="coursepress-table">
		<thead>
		<tr>
			<th class="column-date-enrolled">
				<?php esc_html_e( 'Title', 'cp' ); ?>
			</th>
			<th class="column-date-enrolled">
				<?php esc_html_e( 'Date Enrolled', 'cp' ); ?>
			</th>
			<th class="column-last-active">
				<?php esc_html_e( 'Last Active', 'cp' ); ?>
			</th>
			<th class="column-average">
				<?php esc_html_e( 'Average', 'cp' ); ?>
			</th>
			<th class="column-average">
				<?php esc_html_e( 'Progress', 'cp' ); ?>
			</th>
			<th class="column-certificate">
				<?php esc_html_e( 'Certificate', 'cp' ); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $courses as $course_id ): ?>
			<?php
			$course        = new CoursePress_Course( $course_id );
			$progress_data = $student->get_course_progress_data( $course_id );
			?>
			<tr>
				<td><?php echo $course->get_the_title(); ?></td>
				<td><?php echo $student->get_date_enrolled( $course_id ); ?></td>
				<td><?php echo $student->get_last_activity_time(); ?></td>
				<td><?php echo $progress_data['completion/average']; ?></td>
				<td><?php printf( '%s%%', $student->get_course_progress( $course_id ) ); ?></td>
				<td>
					<?php
					$course_completed = $student->is_course_completed( $course_id );
					if ( $course_completed ) {
						$course_certificate = new CoursePress_Certificate();
						printf(
							'<a href="%s">%s</a>',
							$course_certificate->get_pdf_file_url( $course_id, $student_id ),
							esc_html__( 'Download', 'cp' )
						);
					} else {
						esc_html_e( 'Not available', 'cp' );
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $pagination ) ) : ?>
		<div class="tablenav cp-admin-pagination">
			<?php $pagination->pagination( 'bottom' ); ?>
		</div>
	<?php endif; ?>
</div>