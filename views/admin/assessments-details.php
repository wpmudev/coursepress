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
						<td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
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
									$grade = $assessments['student']->grade;
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
											<span class="cp-cross-icon"><?= $assessments['student']->get_unit_grade( $course_id, $unit->ID ) ? : 0 ?>%</span>
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
																		<?php if ( $step->is_graded ) : ?>
																			<span class="pull-right cp-title">
																				<?= $step->grade ?>%
																				<?php $step_status = $assessments['student']->get_step_grade_status( $course_id, $unit->ID, $step_id ); ?>
																				<span class="<?= $step_status === 'pass' ? 'cp-green' : 'cp-red' ?>"><?= $step_status ? strtoupper( $step_status ) : __( 'FAILED', 'cp' ) ?></span>
																			</span>
																		<?php endif; ?>
																	</th>
																</tr>
																<tr>
																	<th class="cp-assessments-strong"><?php _e( 'Question', 'cp' ); ?></th>
																	<th class="cp-assessments-strong"><?php _e( 'Student answer', 'cp' ); ?></th>
																	<?php if ( $step->type != 'written' ) :  ?>
																		<th class="cp-assessments-strong"><?php _e( 'Correct answer', 'cp' ); ?></th>
																	<?php endif; ?>
																</tr>
																<?php if ( isset( $step->questions ) && is_array( $step->questions ) ) : ?>
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
																		</tr>
																	<?php endforeach; ?>
																<?php else : ?>
																<tr>
																	<td colspan="3">
																		<ul class="cp-assessments-answers">
																			<li><span class="cp-no-answer"><?php _e( 'No answer!' ); ?></span</li>
																		</ul>
																	</td>
																</tr>
																<?php endif; ?>
																<?php $step_count++; ?>
															<?php endforeach; ?>
														<?php endif; ?>
														<?php if ( $step_count < 1 ) : ?>
															<tr>
																<td colspan="2"><?php _e( 'No answerable modules found', 'cp' ); ?></td>
															</tr>
														<?php endif; ?>
													</table>
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
