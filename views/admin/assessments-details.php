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
						<option value="all"><?php _e( 'Show all modules', 'cp' ); ?></option>
						<option value="all_assessable"><?php _e( 'Show all assessable modules', 'cp' ); ?></option>
					</select>
				</div>
			</div>
		</form>

		<table class="coursepress-table" id="cp-assessments-table" cellspacing="0">
			<thead>
				<tr>
					<td class="column-student">
						<div class="cp-flex">
							<span class="gravatar"><?php echo get_avatar( $assessments['student']->ID, 30 ); ?></span>
							<span class="user_login"><?php echo $assessments['student']->user_login; ?></span>
							<span class="display_name">(<?php echo $assessments['student']->get_name(); ?>)</span>
						</div>
					</td>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! empty( $assessments['units'] ) ) : ?>
				<tr class="cp-assessments-details cp-assessments-details-full">
					<td colspan="5" class="cp-tr-expanded">
						<ul class="cp-assessments-units-expanded">
							<?php foreach ( $assessments['units'] as $unit ) : ?>
								<li>
									<span class="pull-left"><span class="cp-units-icon"></span><?php echo $unit->get_the_title(); ?></span>
									<span class="pull-right">
										<?php $unit_grade = $assessments['student']->get_unit_grade( $course_id, $unit->ID ); ?>
										<span class="<?= $assessments['student']->has_pass_course_unit( $course_id, $unit->ID ) ? 'cp-tick-icon' : 'cp-cross-icon' ?>"><?= empty( $unit_grade ) ? 0 : $unit_grade ?>%</span>
									</span>
									<?php if ( ! empty( $unit->modules ) ) : ?>
										<div class="cp-assesments-module-expanded">
											<?php foreach ( $unit->modules as $module_id => $module ) : ?>
												<div class="cp-assessments-table-container">
													<table class="cp-assesments-questions-expanded">
														<?php $step_count = 0; ?>
														<?php if ( ! empty( $module['steps'] ) ) : ?>
															<?php foreach ( $module['steps'] as $step_id => $step ) : ?>
																<?php if ( ! $step->is_answerable() ) : continue; endif; ?>
																<?php if ( $step_count == 0 ) : ?>
																	<tr>
																		<th colspan="2"><?php echo $module['title']; ?></th>
																	</tr>
																<?php endif; ?>
																<tr class="cp-question-title">
																	<td colspan="2"><strong><?= $step->get_the_title() ?></strong></td>
																</tr>
																<tr>
																	<th class="cp-assessments-strong"><?php _e( 'Question', 'cp' ); ?></th>
																	<th class="cp-assessments-strong"><?php _e( 'Student answer', 'cp' ); ?></th>
																</tr>
																<?php foreach ( $step->questions as $qkey => $question ) : ?>
																	<tr>
																		<td><?php echo $question['title']; ?></td>
																		<td>
																			<?php $response = $step->get_user_response( $assessments['student']->ID ); ?>
																			<?php if ( isset( $response[ $qkey ] ) ) : ?>
																				<ul class="cp-assessments-answers">
																					<?php if ( in_array( $question['type'], array( 'single', 'select' ) ) ) : ?>
																						<li>
																							<?php $ans_span_class = empty( $question['options']['checked'][$response[ $qkey ]] ) ? 'cp-cross-icon' : 'cp-tick-icon'; ?>
																							<span class="<?= $ans_span_class ?>"><?= $question['options']['answers'][ $response[ $qkey ] ] ?></span>
																						</li>
																					<?php elseif ( $question['type'] == 'multiple' ) : ?>
																						<?php foreach ( $response[ $qkey ] as $an_key => $answer ) : ?>
																							<li>
																								<?php $ans_span_class = empty( $question['options']['checked'][$an_key] ) ? 'cp-cross-icon' : 'cp-tick-icon'; ?>
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
