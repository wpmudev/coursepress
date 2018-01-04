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
					<th class="column-student">
						<div class="cp-flex">
							<span class="gravatar"><?= get_avatar( $student_id, 30 ) ?></span>
							<span class="user_login"><?= $assessments['student']->user_login ?></span>
							<span class="display_name">(<?= $assessments['student']->get_name() ?>)</span>
						</div>
					</th>
					<th class="column-course">
						<div class="cp-flex">
							<h3><?= $assessments['course']->get_the_title() ?></h3>
						</div>
					</th>
					<th class="column-grade">
						<div class="cp-flex">
							<h3><?= $assessments['grade'] ? : 0; ?>%</h3>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $assessments['units'] ) ) : ?>
				<tr class="cp-assessments-details">
					<td colspan="3" class="cp-tr-expanded">
						<ul class="cp-assessments-units-expanded">
							<?php foreach ( $assessments['units'] as $unit ) : ?>
								<?php if ( empty( $unit->is_answerable ) ) : continue; endif; ?>
								<li>
									<span class="pull-left cp-title"><span class="cp-units-icon"></span><?php echo $unit->get_the_title(); ?></span>
									<?php if ( $unit->is_graded ) : ?>
										<span class="pull-right">
											<span class="cp-title"><?= $assessments['student']->get_unit_grade( $course_id, $unit->ID ) ? : 0 ?>%</span>
											<span class="cp-minus-icon"></span>
										</span>
									<?php endif; ?>
									<?php if ( ! empty( $unit->modules ) ) : ?>
										<div class="cp-assesments-module-expanded">
											<?php foreach ( $unit->modules as $module_id => $module ) : ?>
												<div class="cp-assessments-table-container">
													<table class="cp-assesments-questions-expanded">
														<?php $step_count = 0; ?>
														<?php if ( ! empty( $module['steps'] ) ) : ?>
															<?php foreach ( $module['steps'] as $step_id => $step ) : ?>
																<?php if ( $step_count == 0 ) : ?>
																	<tr>
																		<th colspan="2"><?php echo $module['title']; ?></th>
																	</tr>
																<?php endif; ?>
																<tr class="cp-question-title">
																	<th colspan="2">
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
																</tr>
																<?php if ( isset( $step->questions ) && is_array( $step->questions ) ) : ?>
																	<?php foreach ( $step->questions as $qkey => $question ) : ?>
																		<tr>
																			<td><?php echo $question['title']; ?></td>
																			<td>
																				<?php $response = $step->get_user_response( $student_id ); ?>
																				<?php if ( isset( $response[ $qkey ] ) ) : ?>
																					<ul class="cp-assessments-answers">
																						<?php if ( in_array( $question['type'], array( 'single', 'select' ) ) ) : ?>
																							<li>
																								<?php $ans_span_class = empty( $question['options']['checked'][ $response[ $qkey ] ] ) ? 'cp-cross-icon' : 'cp-tick-icon'; ?>
																								<span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $response[ $qkey ] ] ?></span>
																							</li>
																						<?php elseif ( $question['type'] == 'multiple' ) : ?>
																							<?php foreach ( $response[ $qkey ] as $an_key => $answer ) : ?>
																								<li>
																									<?php $ans_span_class = empty( $question['options']['checked'][ $an_key ] ) ? 'cp-cross-icon' : 'cp-tick-icon'; ?>
																									<span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $an_key ] ?></span>
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
