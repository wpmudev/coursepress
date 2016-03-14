<?php

class CoursePress_View_Admin_Student_Workbook {

	public static function display() {
		$student_id = (int) $_GET[ 'student_id' ];
		$student = get_userdata( $student_id );
		?>
		<div class="wrap nocoursesub student-workbook cp-wrap">
			<h2><?php esc_html_e( 'Student Workbook', 'CP_TD' ); ?></h2>
			<hr />

			<div class="course">
				<div id="edit-sub" class="course-holer-wrap mp-wrap">
					<div class="course-holder">
						<?php echo get_avatar( $student_id, 80 ); ?>
						<div class="student_additional_info">
							<div>
								<span class="info_caption"><?php esc_html_e( 'Student ID', 'CP_TD' ); ?></span>
								<span class="info"><?php echo $student_id; ?></span>
							</div>
							<div>
								<span class="info_caption"><?php esc_html_e( 'First Name', 'CP_TD' ); ?></span>
								<span class="info"><?php echo $student->first_name; ?></span>
							</div>
							<div>
								<span class="info_caption"><?php esc_html_e( 'Surname', 'CP_TD' ); ?></span>
								<span class="info"><?php echo $student->last_name; ?></span>
							</div>
							<div>
								<span class="info_caption"><?php esc_html_e( 'Courses', 'CP_TD' ); ?></span>
								<span class="info">
									<?php
										$courses = CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );
										echo count( $courses );
									?>
								</span>
							</div>
							<div>
								<span class="info_caption"><?php esc_html_e( 'Edit', 'CP_TD' ); ?></span>
								<span class="info">
									<?php
										$edit_link = get_edit_user_link( $student_id );
										printf( '<a href="%s"><i class="fa fa-pencil"></i></a>', $edit_link );
									?>
								</span>
							</div>
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
						</div>
						<hr />

						<?php
							$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );
							$first = array_shift( $courses );
							$selected_course = $first->ID;
						?>
						<div id="units_wrap">
							<div class="sidebar-name no-movecursor">
								<?php if ( count( $courses ) == 0 ) : ?>
									<div class="zero-courses">
										<?php esc_html_e( 'Student did not enroll in any courses yet.', 'CP_TD' ); ?>
									</div>
								<?php else : ?>
									<div class="tablenav">
										<strong><?php esc_html_e( 'Select Course', 'CP_TD' ); ?></strong>
										<?php
											echo CoursePress_Helper_UI::get_course_dropdown(
												'course-list', 'course-list',
												$courses,
												array(
													'class' => 'medium',
													'value' => $selected_course
												)
											);
										?>
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
									</div>
									<hr />

									<?php
										$units = CoursePress_Data_Course::get_units_with_modules( $selected_course, array( 'publish', 'draft' ) );
										$active_unit_id = ! empty( $_GET[ 'unit_id' ] ) ? (int) $_GET[ 'unit_id' ] : null;
										$active_unit = null;
										$active_page = ! empty( $_GET[ 'page_number' ] ) ? (int) $_GET[ 'page_number' ] : 1;
										
										if ( ! empty( $units ) ) :
									?>
										<div class="unit-tabs-container">
											<span><?php esc_html_e( 'Select Unit', 'CP_TD' ); ?></span>
											<div class="unit-tabs">
												<?php
													$tab = 1;
													foreach( $units as $unit_id => $unit ) :
														$the_unit = $unit[ 'unit' ];
														$unit_url = add_query_arg(
															array(
																'unit_id' => $unit_id
															)
														);

														if ( ! $active_unit_id ) {
															if ( $tab == 1 ) {
																$active_unit = $unit;
															}
														} elseif ( $active_unit_id == $unit_id ) {
															$active_unit = $unit;
														}
												?>
													<a href="<?php echo $unit_url; ?>" class="unit-tab"><?php echo $tab; ?></a>
												<?php
													$tab++;
													endforeach;
												?>
											</div>
										</div>

										<?php echo $selected_course;
											$pages = $active_unit[ 'pages' ];
											$pages_number = array_keys( $pages );
											$pages_number = array_filter( $pages_number );
											$student_progress = CoursePress_Data_Student::get_completion_data(
												$student_id,
												(int) $selected_course
											);

											if ( count( $pages_number ) > 1 ) {
										?>
											<div id="pages-numbers">
												<?php
													foreach ( $pages_number as $number ) :
														$page_link = add_query_arg( 'page_id', $number );
												?>
													<a href="<?php echo $page_link; ?>"><?php echo $number; ?></a>
												<?php
													endforeach;
												?>
											</div>
										<?php
											}
										?>
										<table cellspacing="0" class="widefat">
											<thead>
												<tr>
													<th><?php esc_html_e( 'Activity', 'CP_TD' ); ?></th>
													<th><?php esc_html_e( 'Submission', 'CP_TD' ); ?></th>
													<th><?php esc_html_e( 'Response', 'CP_TD' ); ?></th>
													<th><?php esc_html_e( 'Grade', 'CP_TD' ); ?></th>
													<th><?php esc_html_e( 'Feedback', 'CP_TD' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td colspan="6">
														<?php
															foreach ( $pages as $page_number => $unit_page ) :
														?>
															<div id="page-unit-<?php echo $page_number; ?>">
																<table cellspacing="0">
																<?php
																	$modules = $unit_page[ 'modules' ];

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
																					'student_id' => $student_id
																				),
																				admin_url( '?view_anser' )
																			);
																			$view_link = sprintf( '<a href="%s">%s</a>', $view_link );
																		endif;
																?>
																	<tr>
																		<td>
																			<?php echo $module->post_title; ?>
																		</td>
																		<td>
																			<?php
																				if ( $response && ! empty( $response['date'] ) ) {
																					$date_format = get_option( 'date_format' );
																					echo date_i18n( $date_format, strtotime( $response[ 'date' ] ) );
																				}
																			?>
																		</td>
																		<td>
																			<?php echo $view_link; ?>
																		</td>
																		<td>
																			<?php
																				echo (-1 == $grade['grade'] ? __( '--', 'CP_TD' ) : $grade['grade'] );
																			?>
																		</td>
																		<td>
																			<?php
																			?>
																		</td>
																	</tr>
																<?php
																	endforeach;
																?>
																</table>
															</div>
														<?php
															endforeach;
														?>
													</td>
												</tr>
											</tbody>
										</table>
									<?php
										endif;
									?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}