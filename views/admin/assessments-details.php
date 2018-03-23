<?php
$student = $assessments['student'];
?>
<div class="wrap coursepress-wrap coursepress-assessments" id="coursepress-assessments">
	<h1 class="wp-heading-inline"><?php _e( 'Assessments', 'cp' ); ?></h1>

	<div class="coursepress-page">
		<form method="get" class="cp-search-form" id="cp-search-form">
			<div class="cp-flex">

				<div class="cp-div">
					<label class="label"><?php _e( 'Select course', 'cp' ); ?></label>
					<select name="course_id" data-placeholder="<?php _e( 'Select a course', 'cp' ); ?>">
						<option></option>
						<?php if ( ! empty( $courses ) ) : ?>
							<?php foreach ( $courses as $course ) : ?>
								<option value="<?php echo $course->ID; ?>" <?php selected( $course->ID, $course_id ); ?>><?php echo $course->post_title; echo $course->get_numeric_identifier_to_course_name( $course->ID ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="cp-div">
					<label class="label"><?php _e( 'Select display', 'cp' ); ?></label>
					<select name="display">
						<option value="all" <?php selected( 'all', $display ); ?>><?php _e( 'Show all modules', 'cp' ); ?></option>
						<option value="all_assessable" <?php selected( 'all_assessable', $display ); ?>><?php _e( 'Show all assessable modules', 'cp' ); ?></option>
					</select>
					<input type="hidden" name="page" value="<?= esc_attr( $page ) ?>" />
					<input type="hidden" name="tab" value="details" />
					<input type="hidden" name="student_id" value="<?= $student_id ?>" />
				</div>
			</div>
		</form>

		<table class="coursepress-table" id="cp-assessments-table" cellspacing="0">
			<thead>
				<tr>
					<?php foreach ( $columns as $column_id => $column_label ) : ?>
						<th class="manage-column column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>" id="<?php echo $column_id; ?>">
							<?php echo $column_label; ?>
						</th>
					<?php endforeach; ?>
				</tr>
				<tr>
					<?php foreach ( array_keys( $columns ) as $column_id ) : ?>
						<?php
						$column_class = '';
						if( 'grade' === $column_id ) {
							$column_class .= 'final-grade';
						}
						?>
						<td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; echo ' ' . $column_class; ?>">
							<?php
							$details_args = array(
								'tab' => 'details',
								'student_id' => $assessments['student']->ID,
							);
							// Remove unwanted items from details link.
							$details_link = remove_query_arg( array( 's', 'student_progress', 'graded_ungraded' ) );
							$details_link = add_query_arg( $details_args, $details_link );
							switch ( $column_id ) :
								// @todo Add profile link if required.
								case 'student' :
									echo '<div class="cp-flex">';
									echo '<span class="gravatar">';
									echo get_avatar( $assessments['student']->ID, 30 );
									echo '</span>';
									echo ' ';
									echo '<span class="user_login">';
									echo $assessments['student']->user_login;
									echo '</span>';
									echo ' ';
									echo '<span class="display_name">(';
									echo $assessments['student']->get_name();
									echo ')</span>';
									echo '</div>';
									break;
								case 'last_active' :
									// Last activity time.
									$last_active = $assessments['student']->get_last_activity_time();
									echo $last_active ? date_i18n( get_option( 'date_format' ), $last_active ) : '--';
									break;
								case 'grade' :
									$grade = $assessments['grade'];
									echo ( empty( $grade ) ? 0 : $grade ) . '%';
									break;
								case 'modules_progress' :
									echo '<div class="cp-assessment-progress-hidden">';
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
									do_action( 'coursepress_studentlist_column', $column_id, $assessments['student'] );
									break;
							endswitch;
							?>
						</td>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $assessments['units'] ) ) : ?>
				<tr class="cp-assessments-details">
					<td colspan="4" class="cp-tr-expanded">
						<ul class="cp-assessments-units-expanded">
							<?php foreach ( $assessments['units'] as $unit ) : ?>
								<?php if ( empty( $unit->is_answerable ) ) : continue; endif; ?>
								<li>
									<span class="pull-left"><span class="cp-units-icon"></span><?php echo $unit->get_the_title(); ?></span>
									<?php if ( $unit->is_graded ) : ?>
										<span class="pull-right">
											<span class="<?php echo $student->has_pass_course_unit( $course_id, $unit->ID ) ? 'cp-tick-icon' : 'cp-cross-icon'; ?> cp-unit-div" data-unit="<?php echo $unit->ID; ?>" data-student="<?php echo $student->ID; ?>"><?= floor( $assessments['student']->get_unit_grade( $course_id, $unit->ID ) ) ? : 0 ?>%</span>
											<span class="cp-minus-icon"></span>
										</span>
									<?php endif; ?>
									<?php if ( ! empty( $unit->modules ) ) : ?>
										<div class="cp-assessments-module-expanded">
											<?php foreach ( $unit->modules as $module_id => $module ) : ?>
												<div class="cp-assessments-table-container">
													<table class="cp-assessments-questions-expanded">
														<?php $step_count = 0; ?>
														<?php if ( ! empty( $module['steps'] ) ) : ?>
															<?php foreach ( $module['steps'] as $step_id => $step ) : ?>
																<?php if ( $step_count == 0 ) : ?>
																	<tr>
																		<th colspan="3"><?php echo $module['title']; ?></th>
																	</tr>
																<?php endif; ?>
																<tr class="cp-question-title">
																	<th colspan="3">
																		<span class="cp-title"><?= $step->get_the_title() ?></span>
																		<?php
																		$grade = $student->get_step_grade( $course_id, $unit->ID, $step_id );
																		$is_assessable = ! empty( $step->assessable ) && coursepress_is_true( $step->assessable );
																		?>
																		<?php // No need to show grade if not entered by instructor -->
																		if ( $step->type !== 'fileupload' || ( ! empty( $grade ) && $grade !== 'pending' ) ) : ?>
																			<span class="pull-right cp-title cp-module-grade-info">
																				<span class="cp-current-grade"><?= round( $grade ) ?>%</span>
																				<?php $step_status = $student->get_step_grade_status( $course_id, $unit->ID, $step_id ); ?>
																				<span class="<?= $step_status == 'pass' ? 'cp-green' : 'cp-red' ?> cp-check"><?= $step_status ? strtoupper( $step_status ) : __( 'FAILED', 'cp' ) ?></span>
																				<?php
																				$response = $step->get_user_response( $student->ID );
																			  if ( ! empty( $response ) && $is_assessable ) :
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
																		<?php endif; ?>
																	</th>
																</tr>
																<?php
																$module_assessable_class = '';
																if ( $is_assessable ) {
																	$module_assessable_class .= ' module-assessable';
																}
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
																				  <button type="button" class="cp-btn pull-right cp-save-as-draft disabled" disabled="disabled"><?php _e( 'Save Feeback as Draft', 'cp' ); ?></button>
																				  <button type="button" class="cp-btn cp-submit-grade disabled" disabled="disabled"><?php _e( 'Submit Grade', 'cp' ); ?></button>
																				  <button type="button" class="cp-btn cp-btn-default cp-cancel"><?php _e( 'Cancel', 'cp' ); ?></button>
																				  <p class="description"><?php echo $pass_label; ?></p>
																			 </div>
																		</div>
																	</td>
																</tr>
																<?php if ( isset( $step->questions ) && is_array( $step->questions ) ) : ?>
																<tr>
																	<th class="cp-assessments-strong"><?php _e( 'Question', 'cp' ); ?></th>
																	<th class="cp-assessments-strong"><?php _e( 'Student answer', 'cp' ); ?></th>
																	<?php if ( $step->type != 'written' ) :  ?>
																		<th class="cp-assessments-strong"><?php _e( 'Correct answer', 'cp' ); ?></th>
																	<?php endif; ?>
																</tr>
																	<?php foreach ( $step->questions as $qkey => $question ) : ?>
																		<tr>
																			<td><?php echo $question['title']; ?></td>
																			<td>
																				<?php $response = $step->get_user_response( $student_id ); ?>
																				<?php if ( $question['type'] == 'written' ) :  ?>
																					<?php $written_answer = $response[ $step->course_id ][ $step->unit_id ][ $step->ID ][ $qkey ]; ?>
																						<?php if ( $written_answer ) :  ?>
																							<?php echo stripslashes( $written_answer ); ?>
																						<?php else : ?>
																							<span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span>
																						<?php endif; ?>
																				<?php else : ?>
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
																				<?php endif; ?>
																			</td>
																			<td>
																				<ul class="cp-assessments-answers">
																					<?php $list_sep = in_array( $question['type'], array( 'single', 'select' ) ) ? '' : '- '; ?>
																					<?php if ( ! empty( $question['options'] ) ) :  ?>
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
																			<li><span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span</li>
																		</ul>
																	</td>
																</tr>
																<?php endif; ?>
																<?php
																$response = $step->get_user_response( $student->ID );
																if ( ! empty( $response ) && $is_assessable ) {
																	$hide = ' style="display:none;"';
																	$is_draft = $has_feedback && ! empty( $feedback['draft'] );
																?>
																<tr class="cp-instructor-feedback" data-courseid="<?php echo $course_id; ?>" data-unit="<?php echo $unit->ID; ?>" data-module="<?php echo $step_id; ?>" data-student="<?php echo $student_id; ?>" <?php echo ( ! empty( $feedback ) ? '' : $hide ); ?>>
																	<td colspan="3">
																		<div class="cp-instructor-feedback" style="display: <?php echo ( ! empty( $feedback ) ? 'block' : 'none' ); ?>">
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
														<?php if ( $step_count < 1 ) : ?>
															<tr>
																<td colspan="2"><?php _e( 'No answerable modules found', 'cp' ); ?></td>
															</tr>
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
			</tbody>
		</table>
	</div>
</div>
