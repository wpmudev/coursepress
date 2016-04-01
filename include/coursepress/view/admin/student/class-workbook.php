<?php

class CoursePress_View_Admin_Student_Workbook {

	public static function profile() {
		$student_id = (int) $_GET['student_id'];
		$student = get_userdata( $student_id );
		?>
			<table cellspacing="0">
				<tr>
					<td width="5%" valign="top">
						<?php echo get_avatar( $student_id, 80 ); ?>
					</td>
					<td valign="top">
						<div>
							<span class="info_caption"><?php esc_html_e( 'Student ID', 'CP_TD' ); ?>:</span>
							<span class="info"><?php echo $student_id; ?></span>
						</div>
						<div>
							<span class="info_caption"><?php esc_html_e( 'First Name', 'CP_TD' ); ?>:</span>
							<span class="info"><?php echo $student->first_name; ?></span>
						</div>
						<div>
							<span class="info_caption"><?php esc_html_e( 'Surname', 'CP_TD' ); ?>:</span>
							<span class="info"><?php echo $student->last_name; ?></span>
						</div>
					</td>
					<td valign="top">
						<div>
							<span class="info_caption"><?php esc_html_e( 'Courses', 'CP_TD' ); ?>:</span>
							<span class="info">
							<?php
								$courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
								echo count( $courses );
							?>
						</span>
						</div>
						<?php if ( CoursePress_Data_Capabilities::can_create_student() ) : ?>
						<div>
							<span class="info_caption"><?php esc_html_e( 'Edit', 'CP_TD' ); ?></span>
							<span class="info">
							<?php
								$edit_link = get_edit_user_link( $student_id );
								printf( '<a href="%s"><i class="fa fa-pencil"></i></a>', $edit_link );
							?>
							</span>
						</div>
						<?php endif; ?>
						<div>
							<span class="info_caption"><?php esc_html_e( 'Profile', 'CP_TD' ); ?></span>
							<span class="info">
							<?php
								$profile_link = add_query_arg(
									array( 'view' => 'profile', 'student_id' => $student_id )
								);
								printf( '<a href="%s"><i class="fa fa-user"></i></a>', $profile_link );
							?>
							</span>
						</div>
					</td>
				</tr>
			</table>
			<hr />
		<?php
	}

	public static function display() {
		$student_id = (int) $_GET['student_id'];
		$student = get_userdata( $student_id );
		?>
		<div class="wrap nocoursesub assessment student-workbook cp-wrap">
			<h2><?php esc_html_e( 'Student Workbook', 'CP_TD' ); ?></h2>
			<hr />
			<?php
				self::profile();

				$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );
				$first = array_shift( $courses );
				$selected_course = ! empty( $_GET['course_id'] ) ? (int) $_GET['course_id'] : $first->ID;
				$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $selected_course );

			if ( 0 == count( $courses ) ) :
			?>
				<div class="zero-courses">
					<?php echo $student->user_login; esc_html_e( ' did not enroll to any courses yet.', 'CP_TD' ); ?>
				</div>
			<?php else : ?>
				<div class="tablenav">
					<span class="info_caption"><?php esc_html_e( 'Select Course', 'CP_TD' ); ?></span>
					<?php
						echo CoursePress_Helper_UI::get_course_dropdown(
							'course-list',
							'course-list',
							$courses,
							array(
								'class' => 'medium',
								'value' => $selected_course,
							)
						);
					?>
<!--
					<label class="ungraded-elements">
						<input type="checkbox" value="0" />
						<span><?php esc_html_e( 'Ungraded elements only.', 'CP_TD' ); ?></span>
					</label>
					<label class="submitted-elements">
						<input type="checkbox" value="0" />
						<span><?php esc_html_e( 'Submitted elements only.', 'CP_TD' ); ?></span>
					</label>
					<label class="expand-all-students">
						<a><?php esc_html_e( 'Expand List', 'CP_TD' ); ?></a>
					</label>
					<label class="collapse-all-students">
						<a><?php esc_html_e( 'Collapse List', 'CP_TD' ); ?></a>
					</label>
-->
				</div>
			<?php
				$units = CoursePress_Data_Course::get_units_with_modules( $selected_course, array( 'publish', 'draft' ) );
				$active_unit_id = ! empty( $_GET['unit_id'] ) ? (int) $_GET['unit_id'] : null;
				$active_unit = null;

			if ( ! empty( $units ) ) :
			?>
				<div class="units-tab-container">
					<span class="info_caption"><?php esc_html_e( 'Select Unit', 'CP_TD' ); ?></span>
					<div class="units-tab">
					<?php
					$tab = 1;
					foreach ( $units as $unit_id => $unit ) :
							$unit_url = add_query_arg(
								array(
									'course_id' => $selected_course,
									'unit_id' => $unit_id,
								)
							);

							if ( ! $active_unit_id ) :
								if ( 1 == $tab ) :
									$active_unit = $unit;
									$active_unit_id = $unit_id;
								endif;
							elseif ( $active_unit_id == $unit_id ) :
								$active_unit = $unit;
							endif;
					?>
							<a href="<?php echo $unit_url; ?>" class="unit-tab"><?php echo $tab; ?></a>
					<?php
							$tab++;
						endforeach;
					?>
					</div>
				</div>
				<hr />
			<?php
				endif;
			?>
				<h3><?php echo $active_unit['unit']->post_title; ?></h3>
				<table class="widefat" id="modules-table">
					<thead>
						<tr>
							<th></th>
							<th><?php esc_html_e( 'Activity', 'CP_TD' ); ?></th>
							<th><?php esc_html_e( 'Submission', 'CP_TD' ); ?></th>
							<th><?php esc_html_e( 'Response', 'CP_TD' ); ?></th>
							<th><?php esc_html_e( 'Grade', 'CP_TD' ); ?></th>
							<th><?php esc_html_e( 'Feedback', 'CP_TD' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$pages = $active_unit['pages'];

						if ( $pages ) :
							foreach ( $pages as $page_number => $page ) :
								?>
								<tr>
									<th colspan="6">
										<?php echo $page['title']; ?>
									</th>
								</tr>
								<?php
									$modules = $page['modules'];

								foreach ( $modules as $module_id => $module ) :
										$response = CoursePress_Data_Student::get_response(
											$student_id,
											$selected_course,
											$active_unit_id,
											$module_id,
											false,
											$student_progress
										);
										$grade = CoursePress_Data_Student::get_grade(
											$student_id,
											$selected_course,
											$active_unit_id,
											$module_id,
											false,
											false,
											$student_progress
										);
										$feedback = CoursePress_Data_Student::get_feedback(
											$student_id,
											$selected_course,
											$active_unit_id,
											$module_id,
											false,
											false,
											$student_progress
										);
										$view_link = '';

										if ( $response ) :
											$view_link = add_query_arg(
												array(
													'page' => 'coursepress_assessments',
													'course_id' => $selected_course,
													'unit_id' => $active_unit_id,
													'module_id' => $module_id,
													'student_id' => $student_id,
												),
												admin_url( 'admin.php' )
											);
											$view_link = sprintf( '<a href="%s&view_answer">%s</a>', $view_link, __( 'View', 'CP_TD' ) );
										endif;
								?>
										<tr>
											<td width="1%"></td>
											<td><?php echo $module->post_title; ?></td>
											<td>
												<?php
												if ( $response && ! empty( $response['date'] ) ) :
														$date_format = get_option( 'date_format' );
														echo date_i18n( $date_format, strtotime( $response['date'] ) );
													endif;
												?>
											</td>
											<td><?php echo $view_link; ?></td>
											<td>
												<?php
													echo (-1 == $grade['grade'] ? __( '--', 'CP_TD' ) : $grade['grade'] );
												?>
											</td>
											<td id="instructor-feedback">
												<?php
													$first_last = CoursePress_Helper_Utility::get_user_name( (int) $feedback['feedback_by'] );
													echo ! empty( $feedback['feedback'] ) ? '<div class="feedback"><div class="comment">' . $feedback['feedback'] . '</div><div class="instructor"> – <em>' . esc_html( $first_last ) . '</em></div></div>' : '';
												?>
											</td>
										</tr>
								<?php
									endforeach;
								?>
							<?php
								endforeach;
							endif;
						?>
					</tbody>
				</table>
			<?php
				endif;
			?>
		</div><!-- end .wrap -->
		<?php
	}
}
