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
                            <option value="<?php echo $course->ID; ?>" <?php selected( $course->ID, $course_id ); ?>><?php
							echo $course->post_title;
							echo $course->get_numeric_identifier_to_course_name( $course->ID );
?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="cp-div">
					<label class="label"><?php _e( 'Student progress', 'cp' ); ?></label>
					<select name="student_progress">
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
						<input type="text" name="s" placeholder="<?php _e( 'Enter search query here...', 'cp' ); ?>" value="<?php echo $search; ?>" />
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
					<tr class="<?php echo $odd ? 'odd' : 'even cp-assessment-main'; ?>">

						<?php foreach ( array_keys( $columns ) as $column_id ) : ?>
							<td class="column-<?php echo $column_id; echo in_array( $column_id, $hidden_columns ) ? ' hidden': ''; ?>">
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
										$last_active = $student->get_last_activity_time();
										echo $last_active ? date_i18n( get_option( 'date_format' ), $last_active ) : '--';
										break;
									case 'grade' :
										$grade = $student->grade;
										echo ( empty( $grade ) ? 0 : $grade ) . '%';
										break;
									case 'modules_progress' :
										echo '<div class="cp-assessment-progress-hidden">';
										echo '<a href="javascript:void(0);" class="cp-expand-collapse">' . __( 'Expand', 'cp' ) . '</a>';
										echo '|';
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
											<span class="<?php echo $student->has_pass_course_unit( $course_id, $unit->ID ) ? 'cp-tick-icon' : 'cp-cross-icon'; ?>"><?php echo empty( $unit_grade ) ? 0 : $unit_grade; ?>%</span>
											<span class="cp-plus-icon"></span>
										</span>
										<?php if ( ! empty( $unit->modules ) ) : ?>
											<div class="cp-assesments-module-expanded">
												<?php foreach ( $unit->modules as $module_id => $module ) : ?>
													<div class="cp-assessments-table-container inactive">
														<table class="cp-assesments-questions-expanded">
															<?php $step_count = 0; ?>
															<?php if ( ! empty( $module['steps'] ) ) : ?>
																<?php foreach ( $module['steps'] as $step_id => $step ) : ?>
																	<?php if ( ! $step->is_answerable() ) : continue; endif; ?>
																		<?php if ( $step_count == 0 ) : ?>
																			<tr>
																				<th colspan="3"><?php echo $module['title']; ?></th>
																			</tr>
																		<?php endif; ?>
																		<tr class="cp-question-title">
																			<th colspan="3">
																				<span class="cp-title"><?= $step->get_the_title() ?></span>
																				<span class="pull-right cp-title">
																					<?= round( $student->get_step_grade( $course_id, $unit->ID, $step_id ) ) ?>%
																					<?php $step_status = $student->get_step_grade_status( $course_id, $unit->ID, $step_id ); ?>
																					<span class="<?= $step_status == 'pass' ? 'cp-green' : 'cp-red' ?>"><?= $step_status ? strtoupper( $step_status ) : __( 'FAILED', 'cp' ) ?></span>
																				</span>
																			</th>

																		</tr>
																		<tr>
																			<th class="cp-assessments-strong"><?php _e( 'Question', 'cp' ); ?></th>
																			<th class="cp-assessments-strong"><?php _e( 'Student answer', 'cp' ); ?></th>
																			<th class="cp-assessments-strong"><?php _e( 'Correct answer', 'cp' ); ?></th>
																		</tr>
<?php
if ( isset( $step->questions ) && is_array( $step->questions ) ) {
	foreach ( $step->questions as $qkey => $question ) {
?>
																			<tr>
																				<td><?php echo $question['title']; ?></td>
																				<td>
																					<?php $response = $step->get_user_response( $student->ID ); ?>
																					<?php if ( isset( $response[ $qkey ] ) ) : ?>
																						<ul class="cp-assessments-answers">
																							<?php if ( in_array( $question['type'], array( 'single', 'select' ) ) ) : ?>
																								<li>
																									<?php $ans_span_class = empty( $question['options']['checked'][ $response[ $qkey ] ] ) ? '' : 'cp-right-answer'; ?>
																									<span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $response[ $qkey ] ] ?></span>
																								</li>
																							<?php elseif ( $question['type'] == 'multiple' ) : ?>
<?php
foreach ( $response[ $qkey ] as $an_key => $answer ) {
?>
																							<li>
																								<?php $ans_span_class = empty( $question['options']['checked'][ $an_key ] ) ? '':'cp-right-answer'; ?>
																								- <span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $an_key ] ?></span>
																							</li>
																						<?php } ?>
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
<?php
foreach ( ( $question['options']['checked'] ) as $checked_key => $checked ) {
	if ( ! empty( $checked ) ) {
	?>
																					<li>
																						<?= $list_sep . $question['options']['answers'][ $checked_key ]; ?>
																					</li>
<?php
	}
}
?>
																					</ul>
																				</td>
																			</tr>
<?php
	}
}
?>
																	<?php $step_count++; ?>
																<?php endforeach; ?>
															<?php endif; ?>
															<?php if ( $step_count < 1 ) : ?>
																<tr>
																	<td colspan="3"><?php _e( 'No answerable modules found', 'cp' ); ?></td>
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
	</div>
</div>
