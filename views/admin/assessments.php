<div class="wrap coursepress-wrap coursepress-assessments" id="coursepress-assessments">
    <h1 class="wp-heading-inline"><?php _e( 'Assessments', 'cp' ); ?></h1>
	    <div class="coursepress-page">
<?php if ( empty( $courses ) ) { ?>
<div class="cp-alert cp-alert-info">
	<p><?php esc_html_e( 'No courses found.', 'cp' ); ?></p>
</div>
<?php } else { ?>
		<form method="get" class="cp-action-form" id="cp-search-form">
			<div class="cp-flex cp-students">
				<div class="cp-div">
					<label for="course_id" class="label"><?php _e( 'Select course', 'cp' ); ?></label>
					<select id="course_id" name="course_id" data-placeholder="<?php _e( 'Select a course', 'cp' ); ?>">
						<option></option>
						<?php if ( ! empty( $courses ) ) : ?>
							<?php foreach ( $courses as $course ) : ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected( $course->ID, $course_id ); ?>><?php
							echo $course->post_title;
							echo $course->get_numeric_identifier_to_course_name( $course->ID ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="cp-div">
					<label for="student_progress" class="label"><?php _e( 'Student progress', 'cp' ); ?></label>
					<select id="student_progress" name="student_progress">
						<option value=""><?php _e( 'Show all assessable students', 'cp' ); ?></option>
						<?php if ( ! empty( $units ) ) : ?>
							<?php foreach ( $units as $unit ) : ?>
								<option value="<?php echo $unit->ID; ?>" <?php selected( $unit->ID, $unit_id ); ?>><?php echo $unit->post_title; ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<div class="cp-div cp-input-group-div">
					<ul class="cp-flex cp-input-group">
						<li class="cp-div-flex <?php echo ! in_array( $graded, array( 'graded', 'ungraded' ) ) ? 'active' : ''; ?>">
							<label>
								<input type="radio" name="graded_ungraded" value="all" <?php checked( ! in_array( $graded, array( 'graded', 'ungraded' ) ) ); ?> />
								<?php _e( 'All Students', 'cp' ); ?>
							</label>
						</li>
						<li class="cp-div-flex <?php echo $graded == 'graded' ? 'active' : ''; ?>">
							<label>
								<input type="radio" name="graded_ungraded" value="graded" <?php checked( $graded, 'graded' ); ?> />
								<?php _e( 'Graded Students', 'cp' ); ?>
							</label>
						</li>
						<li class="cp-div-flex <?php echo $graded == 'ungraded' ? 'active' : ''; ?>">
							<label>
								<input type="radio" name="graded_ungraded" value="ungraded" <?php checked( $graded, 'ungraded' ); ?> />
								<?php _e( 'Ungraded Students', 'cp' ); ?>
							</label>
						</li>
					</ul>
				</div>
			</div>
			<div class="cp-flex">
				<div class="cp-div">
					<label class="label"><?php _e( 'Search students by name, username or email.', 'cp' ); ?></label>
					<div class="cp-input-clear">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
						<input type="text" name="student_search" placeholder="<?php _e( 'Enter search query here...', 'cp' ); ?>" value="<?php echo $search; ?>" />
						<button type="button" id="cp-search-clear" class="cp-btn-clear"><?php _e( 'Clear', 'cp' ); ?></button>
					</div>
					<button type="submit" class="cp-btn cp-btn-active"><?php _e( 'Search', 'cp' ); ?></button>
				</div>
			</div>
		</form>
		<?php if ( isset( $assessments['pass_grade'] ) ) : ?>
			<ul class="cp-assessments-overview">
				<li><?php _e( 'Showing students' ); ?>: <span class="cp-assessments-strong"><?php echo $assessments['students_count']; ?></span></li>
				<li><?php _e( 'Modules' ); ?>: <span class="cp-assessments-strong"><?php echo $assessments['modules_count']; ?></span></li>
				<li><?php _e( 'Pass grade' ); ?>: <span class="cp-assessments-strong"><?php echo $assessments['pass_grade']; ?>%</span></li>
				<li><?php _e( 'Grade system' ); ?>: <span class="cp-assessments-strong"><?php echo $assessments['grade_system']; ?></span></li>
			</ul>
		<?php endif; ?>
		<table class="coursepress-table" id="cp-assessments-table" cellspacing="0">
			<thead>
			<tr>
				<?php foreach ( $columns as $column_id => $column_label ) : ?>
					<th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
						<?php echo $column_label; ?>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php $odd = true; ?>
			<?php if ( ! empty( $assessments['students'] ) ) : ?>
				<?php foreach ( $assessments['students'] as $student ) : ?>
					<tr class="<?php echo $odd ? 'odd' : 'even cp-assessment-main'; ?>" data-student="<?php echo $student->ID; ?>">
						<?php foreach ( array_keys( $columns ) as $column_id ) : ?>
						<?php
						$column_class = '';
						if ( 'grade' == $column_id ) {
							$column_class .= 'final-grade';
						}
						?>
							<td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; echo ' ' . $column_class; ?>">
								<?php
								$details_args = array(
									'tab' => 'details',
									'student_id' => $student->ID,
								);
								// Remove unwanted items from details link.
								$details_link = remove_query_arg( array( 's', 'student_progress', 'graded_ungraded' ) );
								$details_link = add_query_arg( $details_args, $details_link );
								switch ( $column_id ) :
									// @todo Add profile link if required.
									case 'student' :
										echo '<div class="cp-flex">';
										echo '<span class="gravatar">';
										echo get_avatar( $student->ID, 30 );
										echo '</span>';
										echo ' ';
										echo '<span class="user_login">';
										echo $student->user_login;
										echo '</span>';
										echo ' ';
										echo '<span class="display_name">(';
										echo $student->get_name();
										echo ')</span>';
										echo '</div>';
										break;
									case 'last_active' :
										// Last activity time.
										echo $student->get_last_activity_time();
										break;
									case 'grade' :
										$grade = $student->grade;
										echo ( empty( $grade ) ? 0 : $grade ) . '%';
										break;
									case 'modules_progress' :
										echo '<div class="cp-assessment-progress-hidden">';
										echo '<a href="javascript:void(0);" class="cp-expand-collapse">' . __( 'Expand', 'cp' ) . '</a>';
										echo '<span class="separator">|</span>';
										echo '<a href="' . $details_link . '" target="_blank">' . __( 'Open in new tab', 'cp' ) . '</a>';
										echo '</div>';
										echo '<div class="cp-assessment-progress-expand inactive">';
										echo '<button class="cp-expand-collapse cp-collapse-btn">Collapse</button>';
										echo '</div>';
										break;
									default :
										/**
										 * Trigger to allow custom column value
										 *
										 * @since 3.0
										 * @param string $column_id
										 * @param CoursePress_Student object $student
										 */
										do_action( 'coursepress_studentlist_column', $column_id, $student );
										break;
								endswitch;
								?>
							</td>
						<?php endforeach; ?>
					</tr>
					<?php if ( ! empty( $student->units ) ) : ?>
					<tr class="cp-assessments-details inactive">
						<td colspan="5" class="cp-tr-expanded">
							<ul class="cp-assessments-units-expanded">
								<?php foreach ( $student->units as $unit_id => $unit ) : ?>
									<?php if ( empty( $unit->is_answerable ) ) : continue; endif; ?>
									<li>
										<span class="pull-left"><span class="cp-units-icon"></span><?php echo $unit->get_the_title(); ?></span>
										<span class="pull-right">
											<?php $unit_grade = $student->get_unit_grade( $course_id, $unit->ID ); ?>
											<span class="<?php echo $student->has_pass_course_unit( $course_id, $unit->ID ) ? 'cp-tick-icon' : 'cp-cross-icon'; ?> cp-unit-div" data-unit="<?php echo $unit->ID; ?>" data-student="<?php echo $student->ID; ?>"><?php echo empty( $unit_grade ) ? 0 : floor( $unit_grade ); ?>%</span>
											<span class="cp-plus-icon"></span>
										</span>
										<?php if ( ! empty( $unit->modules ) ) : ?>
											<div class="cp-assessments-module-expanded">
												<?php foreach ( $unit->modules as $module_id => $module ) : ?>
													<div class="cp-assessments-table-container inactive">
														<table class="cp-assessments-questions-expanded">
															<tr class="module-title">
																<th colspan="3"><?php echo $module['title']; ?></th>
															</tr>
															<?php $step_count = 0; ?>
                                                            <?php if ( empty( $module['steps'] ) ) : ?>
																<tr>
																	<td colspan="3"><?php _e( 'No answerable modules found.', 'cp' ); ?></td>
																</tr>
<?php else : ?>
																<?php foreach ( $module['steps'] as $step_id => $step ) : ?>
																	<?php if ( ! $step->is_answerable() ) : continue; endif; ?>
																	<?php if ( $step_count == 0 ) : ?>
																	<?php endif; ?>
																	<tr class="cp-question-title">
																		<th colspan="3">
																			<span class="cp-title"><?= $step->get_the_title() ?></span>
                                      <span class="pull-right">
																			<?php $grade = $student->get_step_grade( $course_id, $unit->ID, $step_id ); ?>
                                      <?php // No need to show grade if not entered by instructor -->
  																			if ( $step->type !== 'fileupload' || ( ! empty( $grade ) && $grade !== 'pending' ) ) : ?>
  																				<span class="cp-title cp-module-grade-info">
  																					<span class="cp-current-grade"><?= round( $grade ) ?>%</span>
  																					<?php $step_status = $student->get_step_grade_status( $course_id, $unit->ID, $step_id ); ?>
  																					<span class="<?= $step_status == 'pass' ? 'cp-green' : 'cp-red' ?> cp-check"><?= $step_status ? strtoupper( $step_status ) : __( 'FAILED', 'cp' ) ?></span>
  																				</span>
  																			<?php endif; ?>
													              <?php
                                          $is_assessable = ! empty( $step->assessable ) && coursepress_is_true( $step->assessable );
    																			// Will only allow feedback for 'written','fileupload'.
    																			$allowed_for_feedback = array( 'written', 'fileupload' );
    																			$response = $step->get_user_response( $student->ID );
    																			if ( ! empty( $response ) && $is_assessable && in_array( $step->type, $allowed_for_feedback ) ) :
										                         $no_feedback_button_label = __( 'Submit Grade without Feedback', 'cp' );
									                           $with_feedback_button_label = __( 'Submit Grade with Feedback', 'cp' );

            																	$response = $student->get_response( $course_id, $unit->ID, $step_id );
            																	$graded_by = coursepress_get_array_val( $response, 'graded_by' );
            																	if ( ! empty( $graded_by ) && 'auto' !== $graded_by ) {
            																			$no_feedback_button_label = __( 'Edit Grade without Feedback', 'cp' );
            																			$with_feedback_button_label = __( 'Edit Grade with Feedback', 'cp' );
            																	}
            																	?>
					                                    <span>
                                                 <button type="button" class="cp-btn cp-btn-active edit-no-feedback"><?php echo $no_feedback_button_label; ?></button>
                                                 <button type="button" class="cp-btn cp-btn-active edit-with-feedback"><?php echo $with_feedback_button_label; ?></button>
                                              </span>
									                         <?php endif;?>
																				</span>
																		</th>
																	</tr>
													<?php
													$module_assessable_class = '';
													if ( ! empty( $response ) && $is_assessable && in_array( $step->type, $allowed_for_feedback ) ) :
														$module_assessable_class .= ' module-assessable';
													?>
                                                   <tr class="cp-grade-editor <?php echo $module_assessable_class; ?>" style="display:none;">
                                                      <td colspan="3">
															<?php
															  $feedback   = $student->get_instructor_feedback( $course_id, $unit->ID, $step_id );
										  					$has_feedback = ! empty( $feedback['feedback'] );
										  					$feedback_class = $has_feedback ? ' cp-active' : '';
										  					$feedback_text = $has_feedback ? $feedback['feedback'] : '';
										  					$feedback_by = '';
															if ( $has_feedback ) {
																$feedback_user = new CoursePress_User( $feedback['feedback_by'] );
																$feedback_by = '- ' . $feedback_user->get_name();
															}
															$student_id = $student->ID;
															$min_grade  = empty( $step->minimum_grade ) ? 0 : (int) $step->minimum_grade;
															$pass_label = sprintf( __( 'The minimum grade to pass: %s', 'cp' ), $min_grade );
									   					$pass_label .= '<br />';
									   					$pass_label .= __( 'You can change this minimum score from course settings.', 'cp' );
															?>
                                                         <div class="cp-grade-editor-box">
                                                           <div class="cp-feedback-editor" style="display:none;">
                                                               <label class="cp-feedback-title"><?php _e( 'Feedback', 'cp' ); ?></label>
                                                               <p class="description"><?php _e( 'Your feedback will be emailed to the student after submission.', 'cp' ); ?></p>
                                                               <textarea class="cp_feedback_content" style="display:none;"><?php echo esc_textarea( $feedback_text ); ?></textarea>
                                                           </div>
                                                             <div class="coursepress-tooltip pull-right cp-edit-grade-box">
                                                                 <label class="cp-assess-label"><?php _e( 'Grade', 'cp' ); ?></label>
                                                                 <input type="number" name="module-grade" data-courseid="<?php echo $course_id; ?>" data-unit="<?php echo $unit->ID; ?>" data-module="<?php echo $step_id; ?>" data-minimum="<?php echo esc_attr( $min_grade ); ?>" data-student="<?php echo $student_id; ?>" class="module-grade small-text" data-grade="<?= round( $grade ) ?>" value="<?= round( $grade ) ?>" min="0" max="100" />
                                                                 <button type="button" class="cp-btn cp-btn-default cp-right cp-save-as-draft disabled"><?php _e( 'Save Feeback as Draft', 'cp' ); ?></button>
                                                                 <button type="button" class="cp-btn cp-btn-default cp-submit-grade disabled"><?php _e( 'Submit Grade', 'cp' ); ?></button>
                                                                 <button type="button" class="cp-btn cp-btn-default cp-cancel"><?php _e( 'Cancel', 'cp' ); ?></button>
                                                                 <p class="description"><?php echo $pass_label; ?></p>
                                                             </div>
                                                         </div>
                                                      </td>
                                                   </tr>
                                  <?php endif; ?>
																	<?php if ( isset( $step->questions ) && is_array( $step->questions ) ) : ?>
																		<tr>
																			<th class="cp-assessments-strong"><?php _e( 'Question', 'cp' ); ?></th>
																			<th class="cp-assessments-strong"><?php _e( 'Student answer', 'cp' ); ?></th>
																			<?php if ( $step->type != 'written' ) :  ?>
																				<th class="cp-assessments-strong"><?php _e( 'Correct answer', 'cp' ); ?></th>
																			<?php endif; ?>
																		</tr>
																		<?php foreach ( $step->questions as $qkey => $question ) : ?>
																			<?php $response = $step->get_user_response( $student->ID ); ?>
																			<tr>
																				<td><?php echo $question['title']; ?></td>
																				<?php if ( $question['type'] == 'written' ) :  ?>
																					<?php $written_answer = $response[ $step->course_id ][ $step->unit_id ][ $step->ID ][ $qkey ]; ?>
																					<td>
																						<?php if ( $written_answer ) :  ?>
																							<?php echo stripslashes( $written_answer ); ?>
																						<?php else : ?>
																							<span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span>
																						<?php endif; ?>
																					</td>
																				<?php else : ?>
																				<td>
																					<?php if ( isset( $response[ $qkey ] ) ) : ?>
																						<ul class="cp-assessments-answers">
																							<?php if ( in_array( $question['type'], array( 'single', 'select' ) ) ) : ?>
																								<li>
																									<?php $ans_span_class = empty( $question['options']['checked'][ $response[ $qkey ] ] ) ? '' : 'cp-right-answer'; ?>
																									<span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $response[ $qkey ] ] ?></span>
																								</li>
																							<?php elseif ( $question['type'] == 'multiple' ) : ?>
																								<?php foreach ( $response[ $qkey ] as $an_key => $answer ) : ?>
																									<li>
																										<?php $ans_span_class = empty( $question['options']['checked'][ $an_key ] ) ? '':'cp-right-answer'; ?>
																										- <span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $an_key ] ?></span>
																									</li>
																								<?php endforeach; ?>
																							<?php endif; ?>
																						</ul>
																					<?php else : ?>
																						<ul class="cp-assessments-answers">
																							<li><span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span</li>
																						</ul>
																					<?php endif; ?>
																				</td>
																				<td>
																					<ul class="cp-assessments-answers">
																						<?php $list_sep = in_array( $question['type'], array( 'single', 'select' ) ) ? '' : '- '; ?>
																						<?php if ( $question['options'] ) :  ?>
																						<?php foreach ( ( $question['options']['checked'] ) as $checked_key => $checked ) : ?>
																							<?php if ( ! empty( $checked ) ) : ?>
																								<li>
																									<?= $list_sep . $question['options']['answers'][ $checked_key ]; ?>
																								</li>
																							<?php endif; ?>
																						<?php endforeach; ?>
																						<?php endif; ?>
																					</ul>
																				</td>
																				<?php endif; ?>
																			</tr>
																		<?php endforeach; ?>
																	<?php elseif ( $step->type === 'fileupload' ) :  ?>
																		<tr>
																			<td colspan="3">
																				<?php $uploaded_files = $step->get_user_response( $student->ID ); ?>
																				<?php if ( $uploaded_files && isset( $uploaded_files['url'] ) ) :  ?>
																					<a href="<?php echo $uploaded_files['url']; ?>"><?php _e( 'Uploaded File', 'cp' ); ?></a>
																				<?php else : ?>
																					<span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span>
																				<?php endif; ?>
																			</td>
																		</tr>
																	<?php else : ?>
																		<tr>
																			<td colspan="3">
																				<ul class="cp-assessments-answers">
																					<li><span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span></li>
																				</ul>
																			</td>
																		</tr>
																	<?php endif; ?>
													<?php
                          $response = $step->get_user_response( $student->ID );
													if ( ! empty( $response ) && $is_assessable && in_array( $step->type, $allowed_for_feedback ) ) {
														$hide = ' style="display:none;"';
														$is_draft = $has_feedback && ! empty( $feedback['draft'] );
													?>
													<tr class="cp-instructor-feedback" data-courseid="<?php echo $course_id; ?>" data-unit="<?php echo $unit->ID; ?>" data-module="<?php echo $step_id; ?>" data-student="<?php echo $student_id; ?>" <?php echo ( ! empty( $feedback ) ? '' : $hide ); ?>>
                                                      <td colspan="3">
                                                         <div class="cp-instructor-feedback">
                                                         <h4><?php _e( 'Instructor Feedback', 'cp' ); ?> <span class="cp-draft-icon" style="display: <?php echo $is_draft ? 'inline-block' : 'none'; ?>;">[<?php _e( 'Draft', 'cp' ); ?>]</span></h4>
															<?php
															printf( '<div class="cp-feedback-details%s">%s</div><cite>%s</cite>', empty( $feedback_text ) ? ' empty' : '', $feedback_text, $feedback_by );
															printf( '<p class="description" %s>%s</p>', empty( $feedback_text ) ? '' : $hide, __( 'Write your feedback!', 'cp' ) );
															?>
														  </div>
													</td>
													</tr>
															<?php
													}
													?>
																	<?php $step_count++; ?>
																<?php endforeach; ?>
															<?php endif; ?>
														</table>
                                          <input type="hidden" class="cp-total-unit-modules" data-unit="<?php echo $unit->ID; ?>" value="<?php echo $step_count; ?>" />
													</div>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<?php endif; ?>
					<?php $odd = $odd ? false : true; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="odd empty-assessments">
					<td colspan="<?php echo count( $columns ); ?>"><?php _e( 'No assessable students found.', 'cp' ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php if ( ! empty( $list_table ) ) : ?>
			<div class="tablenav cp-admin-pagination">
				<?php $list_table->pagination( 'bottom' ); ?>
			</div>
		<?php endif; ?>
<?php }  ?>
	</div>
</div>
