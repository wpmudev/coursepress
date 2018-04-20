<?php

class CoursePress_View_Admin_Student_Profile extends CoursePress_View_Admin_Student_Workbook {
	public static function display() {
		$student_id = (int) $_GET['student_id'];
		$student = get_userdata( $student_id );
		$date_format = get_option( 'date_format' );
		?>
		<div class="wrap student-workbook student-profile">
			<h1><?php esc_html_e( 'Student Profile', 'coursepress' ); ?></h1>
			<hr />
			<?php
				self::profile();
			?>
			<h2><?php esc_html_e( 'Courses', 'coursepress' ); ?></h2>
			<?php
				$enrolled_courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
				$args = array(
					'post_type' => CoursePress_Data_Course::get_post_type_name(),
					'post_status' => array( 'publish', 'draft' ),
					'post__in' => (array) $enrolled_courses,
				);
				$query = new WP_Query( $args );

				if ( $query->have_posts() ) :
			?>
				<table class="widefat">
					<?php while ( $query->have_posts() ) :
							$query->the_post();
							$course = CoursePress_Data_Course::get_course( get_the_ID() );
							$workbook_link = CoursePress_Data_Student::get_admin_workbook_link( $student_id, get_the_ID() );
						?>
						<tr>
							<td>
<ul>
<li><a href="<?php echo $workbook_link; ?>" class="button button-units workbook-button">
									<?php esc_html_e( 'View Workbook', 'coursepress' ); ?>
								</a></li>
<?php
							/**
							 * Insert send button only when user has
							 * Certificate.
							 */
							$certificate_id = CoursePress_Data_Certificate::get_certificate_id( $student_id, $course->ID );
if ( ! empty( $certificate_id ) ) {
	echo '<li>';
	printf(
		'<a href="#" data-certificate-id="%s" data-nonce="%s" class="button button-certificate-send" data-label-default="%s" data-label-sending="%s">%s</a>',
		esc_attr( $certificate_id ),
		esc_attr( wp_create_nonce( 'send-certificate-'.$certificate_id ) ),
		esc_attr__( 'Send Certificate', 'coursepress' ),
		esc_attr__( 'Sending...', 'coursepress' ),
		__( 'Send Certificate', 'coursepress' )
	);
	echo '</li>';
}
?>
</ul>
							</td>
							<td>
								<div class="student-course">
									<div class="course-top">
										<div class="course-title">
											<a href="<?php echo $course->edit_link; ?>"><?php the_title(); ?></a>
											<a href="<?php echo $course->edit_link; ?>"><i class="fa fa-pencil"></i></a>
											<a href="<?php the_permalink(); ?>" target="_blank"><i class="fa fa-external-link"></i></a>
										</div>
									</div>
									<div class="course-bottom">
										<div class="course-summary"><?php the_excerpt(); ?></div>
										<div class="course-info-holder">
											<span class="info_caption">
												<?php esc_html_e( 'Start', 'coursepress' ); ?>
												<i class="fa fa-calendar"></i>
											</span>
											<span class="info">
												<?php echo $course->start_date; ?>
											</span>
											<span class="info_caption">
												<?php esc_html_e( 'End', 'coursepress' ); ?>
											</span>
											<span class="info">
												<?php echo $course->end_date; ?>
											</span>
											<span class="info_caption">
												<?php esc_html_e( 'Duration', 'coursepress' ); ?>
												<i class="fa fa-clock-o"></i>
											</span>
											<span class="info"><?php echo $course->duration; ?></span>
										</div>
									</div>
								</div>
							</td>
						</tr>
					<?php
						endwhile;
					?>
				</table>
			<?php
				endif;
				wp_reset_postdata();
			?>
		</div>
		<?php
	}
}
