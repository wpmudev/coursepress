<?php
if ( isset( $_GET['student_id'] ) && is_numeric( $_GET['student_id'] ) ) {
	$student = new Student( $_GET['student_id'] );
}

if ( isset( $_POST['course_id'] ) ) {
	if ( wp_verify_nonce( $_POST['save_class_and_group_changes'], 'save_class_and_group_changes' ) ) {
		$course = new Course( $_POST['course_id'] );
		if ( current_user_can( 'manage_options' ) || ( current_user_can( 'coursepress_change_students_group_class_cap' ) ) || ( current_user_can( 'coursepress_change_my_students_group_class_cap' ) && $course->details->post_author == get_current_user_id() ) ) {
			$student->update_student_group( $_POST['course_id'], $_POST['course_group'] );
			$student->update_student_class( $_POST['course_id'], $_POST['course_class'] );
			$message = __( 'Group and Class for the student has been updated successfully.', 'cp' );
		} else {
			$message = __( 'You do not have required permissions to change course group and/or class for the student.', 'cp' );
		}
	}
}
?>
<div class="wrap nocoursesub student-workbook cp-wrap">
	<a href="<?php echo admin_url( 'admin.php?page=students' ); ?>" class="back_link">&laquo; <?php _e( 'Back to Students', 'cp' ); ?></a>

	<h2><?php _e( 'Student Workbook', 'cp' ); ?></h2>

	<div class="course">

		<?php
		if ( isset( $message ) ) {
			?>
			<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
		<?php
		}
		?>

		<div id="course">

			<div id="edit-sub" class="course-holder-wrap mp-wrap">

				<div class="course-holder">

					<div class='student-profile-info'>
						<?php echo get_avatar( $student->ID, '80' ); ?>

						<div class="student_additional_info">
							<div>
								<span class="info_caption"><?php _e( 'Student ID', 'cp' ); ?></span>
								<span class="info"><?php echo $student->ID; ?></span>
							</div>
							<div>
								<span class="info_caption"><?php _e( 'First Name', 'cp' ); ?></span>
								<span class="info"><?php echo $student->user_firstname; ?></span>
							</div>
							<div>
								<span class="info_caption"><?php _e( 'Surname', 'cp' ); ?></span>
								<span class="info"><?php echo $student->user_lastname; ?></span>
							</div>
							<div>
								<span class="info_caption"><?php _e( 'Email', 'cp' ); ?></span>
								<span class="info"><a href="mailto:<?php echo $student->user_email; ?>"><?php echo $student->user_email; ?></a></span>
							</div>
							<div>
								<span class="info_caption"><?php _e( 'Courses', 'cp' ); ?></span>
								<span class="info"><?php echo Student::get_courses_number( $student->ID ); ?></span>
							</div>
							<div>
								<span class="info_caption"><?php _e( 'Edit', 'cp' ); ?></span>
								<span class="info"><a href="user-edit.php?user_id=<?php echo $student->ID; ?>"><i class="fa fa-pencil"></i></a></span>
							</div>
							<div>
								<span class="info_caption"><?php _e( 'Profile', 'cp' ); ?></span>
								<span class="info"><a href="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $student->ID ); ?>"><i class="fa fa-user"></i></a></span>
							</div>
						</div>
						<div class="full border-divider"></div>
					</div>
					<!--student-profile-info-->

					<div id="units-wrap">
						<div class="sidebar-name no-movecursor">

							<?php
							$enrolled_courses = $student->get_enrolled_courses_ids();

							if ( count( $enrolled_courses ) == 0 ) {
								?>
								<div class="zero-courses"><?php _e( 'Student did not enroll in any course yet.', 'cp' ); ?></div>
							<?php } else {
								?>
								<div class="tablenav">
									<form method="get" id="course-filter">

										<input type="hidden" name="action" value="workbook"/>
										<input type="hidden" name="student_id" value="<?php echo esc_attr( $_GET['student_id'] ); ?>"/>
										<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>"/>

										<div class="alignleft actions">
											<select name="course_id" id="dynamic_courses" class="chosen-select">

												<?php
												$assessment_page = 1;

												//$courses = get_posts( $args );
												$courses_with_students = 0;
												$course_num            = 0;
												$first_course_id       = 0;
												$count                 = 0;

												foreach ( $enrolled_courses as $course_id ) {

													if ( $course_num == 0 ) {
														$first_course_id = $course_id;
													}

													$course_obj    = new Course( $course_id );
													$course_object = $course_obj->get_course();

													//$count = Unit_Module::get_ungraded_response_count( $course->ID );
													$x = $course_obj->get_number_of_students();
													if ( $course_obj->get_number_of_students() >= 1 ) {
														$courses_with_students ++;
														if ( isset( $course_object->ID ) ) {
															?>
															<option value="<?php echo $course_object->ID; ?>" <?php echo( ( isset( $_GET['course_id'] ) && $_GET['course_id'] == $course_object->ID ) ? 'selected="selected"' : '' ); ?>><?php echo $course_object->post_title; ?></option>
														<?php
														}
													}
													$course_num ++;
												}

												if ( $courses_with_students == 0 ) {
													?>
													<option value=""><?php _e( 'Student did not enroll into any course yet.', 'cp' ); ?></option>
												<?php
												}
												?>
											</select>
											<?php
											$current_course_id = 0;

											if ( isset( $_GET['course_id'] ) ) {
												$current_course_id = ( int ) $_GET['course_id'];
											} else {
												$current_course_id = $first_course_id;
											}
											?>

											<?php
											if ( $current_course_id !== 0 ) {//courses exists, at least one
												$course       = new Course( $current_course_id );
												$course_units = $course->get_units( $current_course_id, 'publish' );

												if ( count( $course_units ) >= 1 ) {
													?>

													<label class="ungraded"><?php _e( 'Ungraded Elements Only', 'cp' ); ?>
														<?php
														if ( isset( $_GET['ungraded'] ) && $_GET['ungraded'] == 'yes' ) {
															$ungraded_filter = 'yes';
														} else {
															$ungraded_filter = 'no';
														}
														?>
														<input type="checkbox" id="ungraded" name="ungraded" value="yes" <?php checked( $ungraded_filter, 'yes', true ); ?> />
													</label>

												<?php
												}
											}
											?>

										</div>
										<!--alignleft actions-->
									</form>
								</div><!--tablenav-->

								<div class="full border-divider"></div>
								<?php if ( count( $course_units ) >= 1 ) { ?>
									<div id="units_accordion" class="units_accordion">
										<?php foreach ( $course_units as $unit ) { ?>
											<div class="sidebar-name no-movecursor">
												<h3><?php echo $unit->post_title; ?></h3>
											</div>
											<div class="accordion-inner">
												<?php
												$columns = array(
													"module"          => __( 'Element', 'cp' ),
													"title"           => __( 'Title', 'cp' ),
													"submission_date" => __( 'Submitted', 'cp' ),
													"response"        => __( 'Response', 'cp' ),
													"grade"           => __( 'Grade', 'cp' ),
													"comment"         => __( 'Comment', 'cp' ),
												);


												$col_sizes = array(
													'12',
													'36',
													'15',
													'10',
													'10',
													'5'
												);

												?>
												<table cellspacing="0" class="widefat shadow-table assessment-archive-table">
													<thead>
													<tr>
														<?php
														$n = 0;
														foreach ( $columns as $key => $col ) {
															?>
															<th class="manage-column column-<?php echo str_replace( '_', '-', $key ); ?>" width="<?php echo $col_sizes[ $n ] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
															<?php
															$n ++;
														}
														?>
													</tr>
													</thead>

													<?php
													$user_object = new Student( $_GET['student_id'] );

													$modules = Unit_Module::get_modules( $unit->ID );

													$input_modules_count = 0;

													foreach ( $modules as $mod ) {
														$class_name = $mod->module_type;
														if ( class_exists( $class_name ) ) {
															if ( constant( $class_name . '::FRONT_SAVE' ) ) {
																$input_modules_count ++;
															}
														}
													}

													$current_row = 0;
													$style       = '';
													foreach ( $modules as $mod ) {
														$class_name = $mod->module_type;

														if ( class_exists( $class_name ) ) {

															if ( constant( $class_name . '::FRONT_SAVE' ) ) {
																$response         = call_user_func( $class_name . '::get_response', $user_object->ID, $mod->ID );
																$visibility_class = ( count( $response ) >= 1 ? '' : 'less_visible_row' );

																if ( count( $response ) >= 1 ) {
																	$grade_data = Unit_Module::get_response_grade( $response->ID );
																}

																$assessable = get_post_meta( $mod->ID, 'gradable_answer', true );

																if ( isset( $_GET['ungraded'] ) && $_GET['ungraded'] == 'yes' ) {
																	if ( count( $response ) >= 1 && ! $grade_data && $assessable == 'yes' ) {
																		$general_col_visibility = true;
																	} else {
																		$general_col_visibility = false;
																	}
																} else {
																	$general_col_visibility = true;
																}

																$style = ( isset( $style ) && 'alternate' == $style ) ? '' : ' alternate';
																?>
																<tr id='user-<?php echo $user_object->ID; ?>' class="<?php
																echo $style;
																echo 'row-' . $current_row;
																?>">

																	<?php
																	if ( $general_col_visibility ) {
																		?>
																		<td class="column-module <?php echo $style . ' ' . $visibility_class; ?>">
																			<?php echo $mod->label;
																			?>
																		</td>

																		<td class="column-title <?php echo $style . ' ' . $visibility_class; ?>">
																			<?php echo $mod->post_title; ?>
																			<div class="extra-information visible-extra-small">
																				<?php _e( 'Submitted:', 'cp' ); ?>
																				<br/> <?php echo( count( $response ) >= 1 ? $response->post_date : __( 'Not submitted', 'cp' ) ); ?>
																			</div>
																		</td>

																		<td class="coloumn-submission-date <?php echo $style . ' ' . $visibility_class; ?>">
																			<?php echo( count( $response ) >= 1 ? $response->post_date : __( 'Not submitted', 'cp' ) ); ?>
																		</td>

																		<td class="column-response <?php echo $style . ' ' . $visibility_class; ?>">
																			<?php
																			if ( count( $response ) >= 1 ) {
																				?>

																				<a class="assessment-view-response-link button button-units" href="<?php echo admin_url( 'admin.php?page=assessment&course_id=' . $current_course_id . '&unit_id=' . $unit->ID . '&user_id=' . $user_object->ID . '&module_id=' . $mod->ID . '&response_id=' . $response->ID . '&assessment_page=' . $assessment_page ); ?>"><?php _e( 'View', 'cp' ); ?></a>

																			<?php
																			} else {
																				echo '-';
																			}
																			?>
																		</td>

																		<td class="column-grade <?php echo $style . ' ' . $visibility_class; ?>">
																			<?php
																			if ( $assessable == 'yes' ) {
																				if ( isset( $grade_data ) ) {
																					$grade           = $grade_data['grade'];
																					$instructor_id   = $grade_data['instructor'];
																					$instructor_name = get_userdata( $instructor_id );
																					$grade_time      = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $grade_data['time'] );
																				}
																				if ( count( $response ) >= 1 ) {
																					if ( isset( $grade_data ) ) {
																						?>
																						<a class="response_grade" alt="<?php
																						_e( 'Grade by ', 'cp' );
																						echo $instructor_name->display_name;
																						echo sprintf( __( ' on %s', 'cp' ), $grade_time );
																						?>" title="<?php
																						_e( 'Grade by ', 'cp' );
																						echo $instructor_name->display_name;
																						echo sprintf( __( ' on %s', 'cp' ), $grade_time );
																						?>"><?php echo $grade; ?>%</a>
																					<?php
																					} else {
																						_e( 'Pending grade', 'cp' );
																					}
																				} else {
																					echo '-';
																				}
																			} else {
																				_e( 'Non-assessable', 'cp' );
																			}
																			?>
																		</td>

																		<td class="column-comment <?php echo $style . ' ' . $visibility_class; ?>">
																			<?php
																			if ( count( $response ) >= 1 ) {
																				$comment = Unit_Module::get_response_comment( $response->ID );
																			}
																			if ( isset( $comment ) && $comment !== '' ) {
																				?>
																				<a alt="<?php echo $comment; ?>" title="<?php echo $comment; ?>"><i class="fa fa-comment"></i></a>
																			<?php
																			} else {
																				echo '<i class="fa fa-comment-o"></i>';
																			}
																			?>
																		</td>
																	<?php }//general col visibility      ?>
																</tr>
																<?php
																$current_row ++;
															}
														}
													}


													if ( ! isset( $input_modules_count ) || isset( $input_modules_count ) && $input_modules_count == 0 ) {
														?>
														<tr>
															<td colspan="7"><?php _e( '0 input elements in the selected unit.', 'cp' ); ?></td>
														</tr>
													<?php
													}
													?>

												</table>
											</div>
										<?php } ?>
									</div>
								<?php } else { ?>
									<div class="zero-courses"><?php _e( '0 Units in the course', 'cp' ); ?></div>
								<?php
								}
							}
							?>
						</div>
					</div>

				</div>
				<!-- course holder -->
			</div>

		</div>
	</div>
	<!-- course -->

</div>