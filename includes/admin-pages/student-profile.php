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
<div class="wrap nocoursesub cp-wrap">
	<a href="<?php echo admin_url( 'admin.php?page=students' ); ?>" class="back_link">&laquo; <?php _e( 'Back to Students', 'cp' ); ?></a>

	<h2><?php _e( 'Student Profile', 'cp' ); ?></h2>

	<form action="" name="course-add" method="post">

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
									<span class="info_caption"><?php _e( 'Username', 'cp' ); ?></span>
									<span class="info"><?php echo $student->user_login; ?></span>
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
							</div>
							<div class="full border-divider"></div>
						</div>
						<!--student-profile-info-->

						<?php
						$columns = array(
							"course"          => __( ' ', 'cp' ),
							"additional_info" => __( ' ', 'cp' ),
						);
						?>

						<div class="courses" id="student-profile-courses">
							<div class="sidebar-name no-movecursor">
								<h3><?php _e( 'Courses', 'cp' ); ?></h3>

								<?php
								$enrolled_courses = $student->get_enrolled_courses_ids();

								if ( count( $enrolled_courses ) == 0 ) {
									?>
									<div class="zero-courses"><?php _e( 'Student did not enroll in any course yet.', 'cp' ); ?></div>
								<?php
								}

								foreach ( $enrolled_courses as $course_id ) {

									$course_object = new Course( $course_id );
									$course_object = $course_object->get_course();

									if ( $course_object ) {
										?>
										<div class="student-course">

											<div class="student-course-top">
												<a href="<?php echo admin_url( 'admin.php?page=students&action=workbook&student_id=' . $student->ID . '&course_id=' . $course_object->ID ); ?>" class="button button-units workbook-button"><?php _e( 'View Workbook', 'cp' ); ?>
													<i class="fa fa-book cp-move-icon"></i></a>

												<div class="course-title">
													<a href="<?php echo admin_url( 'admin.php?page=course_details&course_id=' . $course_object->ID ); ?>"><?php echo $course_object->post_title; ?></a>
													<a href="<?php echo admin_url( 'admin.php?page=course_details&course_id=' . $course_object->ID ); ?>"><i class="fa fa-pencil"></i></a>
													<a href="<?php echo get_permalink( $course_object->ID ); ?>" target="_blank"><i class="fa fa-external-link"></i></a>
												</div>
											</div>

											<div class="student-course-bottom">

												<div class="course-summary"><?php echo cp_get_the_course_excerpt( $course_object->ID ); ?></div>

												<div class="course-info-holder">
													<span class="course_info_caption"><?php _e( 'Start', 'cp' ); ?>
														<i class="fa fa-calendar"></i></span>
                                                    <span class="course_info">
                                                        <?php
                                                        if ( $course_object->open_ended_course == 'on' ) {
	                                                        _e( 'Open-ended', 'cp' );
                                                        } else {
	                                                        echo $course_object->course_start_date;
                                                        }
                                                        ?>
                                                    </span>

													<span class="course_info_caption"><?php _e( 'End', 'cp' ); ?>
														<i class="fa fa-calendar"></i></span>
                                                    <span class="course_info">
                                                        <?php
                                                        if ( $course_object->open_ended_course == 'on' ) {
	                                                        _e( 'Open-ended', 'cp' );
                                                        } else {
	                                                        echo $course_object->course_end_date;
                                                        }
                                                        ?>
                                                    </span>

													<span class="course_info_caption"><?php _e( 'Duration', 'cp' ); ?>
														<i class="fa fa-clock-o"></i></span>
                                                    <span class="course_info">
                                                        <?php
                                                        if ( $course_object->open_ended_course == 'on' ) {
	                                                        echo '&infin;';
                                                        } else {
	                                                        echo cp_get_number_of_days_between_dates( $course_object->course_start_date, $course_object->course_end_date );
                                                        }
                                                        ?> <?php _e( 'Days', 'cp' ); ?>
                                                    </span>
												</div>

											</div>
											<!--student-course-right-->

											<?php if ( ( ( current_user_can( 'coursepress_change_students_group_class_cap' ) ) || ( current_user_can( 'coursepress_change_my_students_group_class_cap' ) && $course_object->post_author == get_current_user_id() ) ) && 1 == 0 /* moving for the next release */ ) { ?>
												<div class="course-controls alternate">

													<form name="form_student_<?php echo $course_object->ID; ?>" id="form_student_<?php echo $course_object->ID; ?>" method="post" action="<?php echo admin_url( 'admin.php?page=students&action=view&student_id=' . $student->ID ); ?>">
														<?php wp_nonce_field( 'save_class_and_group_changes', 'save_class_and_group_changes' ); ?>

														<input type="hidden" name="course_id" value="<?php echo $course_object->ID; ?>"/>
														<input type="hidden" name="student_id" value="<?php echo $student->ID; ?>"/>

														<div class="changable">
															<label class="class-label">
																<?php _e( 'Class', 'cp' ); ?>

																<select name="course_class" data-placeholder="'<?php _e( 'Choose a Class...', 'cp' ); ?>'" id="course_class_<?php echo $course_object->ID; ?>">

																	<option value=""<?php echo( $student->{'enrolled_course_class_' . $course_object->ID} == '' ? ' selected="selected"' : '' ); ?>><?php _e( 'Default', 'cp' ); ?></option>
																	<?php
																	$course_classes = get_post_meta( $course_object->ID, 'course_classes', true );
																	if ( ! empty( $course_classes ) ) {
																		foreach ( $course_classes as $class ) {
																			?>
																			<option value="<?php echo $class; ?>"<?php echo( $student->{'enrolled_course_class_' . $course_object->ID} == $class ? ' selected="selected"' : '' ); ?>><?php echo $class; ?></option>
																		<?php
																		}
																	}
																	?>
																</select>
															</label>

															<label class="group-label">
																<?php _e( 'Group', 'cp' ); ?>
																<select name="course_group" id="course_group_<?php echo $course_object->ID; ?>" data-placeholder="<?php esc_attr_e( 'Choose a Group...', 'cp' ); ?>">
																	<option value=""<?php echo( $student->{'enrolled_course_group_' . $course_object->ID} == '' ? ' selected="selected"' : '' ); ?>><?php _e( 'Default', 'cp' ); ?></option>
																	<?php
																	$groups = get_option( 'course_groups' );
																	if ( count( $groups ) >= 1 && $groups != '' ) {
																		foreach ( $groups as $group ) {
																			?>
																			<option value="<?php echo $group; ?>"<?php echo( $student->{'enrolled_course_group_' . $course_object->ID} == $group ? ' selected="selected"' : '' ); ?>><?php echo $group; ?></option>
																		<?php
																		}
																	}
																	?>
																</select>
															</label>

															<?php submit_button( __( 'Save Changes', 'cp' ), 'secondary', 'save-group-class-changes', '' ) ?>

														</div>

													</form>

												</div>
											<?php } else { ?>
												<div class="full border-divider"></div>
											<?php } ?>
										</div>
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

	</form>

</div>