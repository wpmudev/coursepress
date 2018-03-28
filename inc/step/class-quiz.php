<?php
/**
 * Class CoursePress_Step_Quiz
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Quiz extends CoursePress_Step {
	protected $type = 'quiz';

	protected function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'questions',
			'answers',
			'answers_selected', // Legacy for `input-checkbox`
		));
		return $keys;
	}

	protected function get_questions_data() {
		$questions = $this->__get( 'questions' );
		if ( is_array( $questions ) ) {
			$questions = $this->to_array( $questions );
		}
		return $questions;
	}

	/**
	 * Create answers list.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $user_id User ID.
	 * @return string $template Results.
	 */
	public function get_answer_template( $user_id = 0 ) {
		$template = parent::get_answer_template( $user_id );
		$questions = $this->__get( 'questions' );
		if ( $questions ) {
			$list = '';
			$response = $this->get_user_response( $user_id );
			foreach ( $questions as $pos => $question ) {
				$q = $this->create_html( 'p', array( 'class' => 'question' ), $question['question'] );
				$a = '';
				if ( isset( $response[ $pos ] ) ) {
					$user_response = $response[ $pos ];
					$answers = $question['options']['answers'];
					$checked = $question['options']['checked'];
					if ( $answers ) {
						foreach ( $answers as $answer_pos => $answer ) {
							$classes = array(
								'chosen-answer',
							);
							$show = false;
							switch ( $question['type'] ) {
								case 'select':
								case 'single':
									if ( $answer_pos == $user_response ) {
										$classes[] = $checked[ $user_response ]? 'correct':'wrong';
										$show = true;
									}
								break;
								default:
									if ( isset( $user_response[ $answer_pos ] ) ) {
										$classes[] = $checked[ $answer_pos ]? 'correct':'wrong';
										$show = true;
									}
							}
							if ( $show ) {
								$a .= $this->create_html(
									'p',
									array( 'class' => implode( ' ', $classes ) ),
									$answer
								);
							}
						}
					}
				}
				$list .= $this->create_html(
					'li',
					array( 'class' => 'question' ),
					$q . $a
				);
			}
			$template .= $this->create_html( 'ul', array( 'class' => 'cp-answers user-answers' ), $list );
		}
		return $template;
	}

	/**
	 * Validate response
	 *
	 * @since 3.0.0
	 *
	 * @input array $response Response.
	 */
	public function validate_response( $response = array() ) {
		$user              = coursepress_get_user();
		$previous_response = $this->get_user_response( $user->ID );
		$course_id         = $this->__get( 'course_id' );
		$unit_id           = $this->__get( 'unit_id' );
		$step_id           = $this->__get( 'ID' );

		$status = $user->get_step_grade_status( $course_id, $unit_id, $step_id );

		if ( ! empty( $response ) ) {
			$user = coursepress_get_user();
			$progress = $user->get_completion_data( $this->__get( 'course_id' ) );
			$data = array(
				'response' => array(),
				'grade' => 0,
			);
			$step_id = $this->__get( 'ID' );
			$min_grade = (int) $this->__get( 'minimum_grade' );
			$total_grade = 0;
			foreach ( $response as $course_id => $response2 ) {
				foreach ( $response2 as $unit_id => $response3 ) {
					$response3 = array_shift( $response3 );
					$questions = $this->__get( 'questions' );
					$count = 0;
					$checked_count = 0;
					$correct = 0;
					$wrong = 0;
					$data['response'] = $response3;
					foreach ( $questions as $pos => $question ) {
						$answers = $question['options']['answers'];
						$checked = $question['options']['checked'];
						$count += count( $answers );
						$checked_count += count( array_filter( $checked ) );
						if ( isset( $response3[ $pos ] ) ) {
							$user_response = $response3[ $pos ];
							if ( $answers ) {
								foreach ( $answers as $answer_pos => $answer ) {
									switch ( $question['type'] ) {
										case 'select':
										case 'single':
											if ( $checked[ $user_response ] && $user_response == $answer_pos ) {
												$correct++;
											}
										break;
										case 'multiple':
											if ( isset( $user_response[ $answer_pos ] ) ) {
												$user_ans = intval( $user_response[ $answer_pos ] );
												if (
												$checked[ $answer_pos ]
												&& $user_ans === $checked[ $answer_pos ]
												) {
													$correct++;
												} else {
													$wrong++;
												}
											}
										break;
									}
								}
							}
						} else {
							$has_previous_answer = false;
							if ( isset( $previous_response[ $pos ] ) ) {
								$has_previous_answer = ( 'multiple' === $question['type'] ) ? ! empty( $previous_response[ $pos ] ) : ! is_null( $previous_response[ $pos ] );
							}
							if ( 'pass' !== $status && $this->is_required() && ! $has_previous_answer ) {
								// Redirect back.
								$referer = filter_input( INPUT_POST, 'referer_url' );
								$error   = __( 'Response is required for all fields.', 'cp' );
								coursepress_set_cookie( 'cp_step_error', $error, time() + 120 );
								wp_safe_redirect( $referer );
								exit;
							}
						}
					}
					if ( $wrong > 0 ) {
						$ratio = 100 / $count;
					} else {
						$ratio = 100 / $checked_count;
					}
					$grade = $correct * $ratio;
					/**
					 * normalize grade: 0 <= $grade <= 100
					 */
					$grade = min( 100, max( 0, $grade ) );
					$data['grade'] = $grade;
					$total_grade += $grade;
					$user->record_response( $course_id, $unit_id, $step_id, $data );
				}
			}
			$pass = $total_grade >= $min_grade;
			if ( $this->is_assessable() && ! $pass ) {
				// Redirect back
				$referer = filter_input( INPUT_POST, 'referer_url' );
				if ( ! empty( $referer ) ) {
					//coursepress_set_cookie( '')
					wp_safe_redirect( $referer );
					exit;
				}
			}
		} elseif ( 'pass' !== $status && $this->is_required() && empty( $previous_response ) ) {
			// Redirect back.
			$referer = filter_input( INPUT_POST, 'referer_url' );
			$error   = __( 'Response is required for all fields.', 'cp' );
			coursepress_set_cookie( 'cp_step_error', $error, time() + 120 );
			wp_safe_redirect( $referer );
			exit;
		}
	}

	protected function get_question_multiple( $index, $question ) {
		$template = '';
		$unit = $this->get_unit();
		$course_id = $unit->__get( 'course_id' );
		$unit_id = $unit->__get( 'ID' );
		$step_id = $this->__get( 'ID' );
		if ( ! empty( $question['options'] ) ) {
			$answers = $question['options']['answers'];
			foreach ( $answers as $pos => $answer ) {
				$name = sprintf( 'module[%d][%d][%d][%s][%d]', $course_id, $unit_id, $step_id, $index, $pos );
				$attr = array(
					'type' => 'checkbox',
					'value' => 1,
					'name' => $name,
				);
				if ( $this->is_preview() ) {
					$attr['readonly'] = 'readonly';
					$attr['disabled'] = 'disabled';
				}
				$input = $this->create_html( 'input', $attr );
				$label = $this->create_html( 'label', array(), $input . $answer );
				$template .= $this->create_html( 'li', array(), $label );
			}
			$template = $this->create_html( 'ul', array( 'class' => 'quiz-multiple' ), $template );
		}
		return $template;
	}

	protected function get_question_radio( $index, $question ) {
		return $this->get_question_single( $index, $question );
	}

	protected function get_question_single( $index, $question ) {
		$template = '';
		$unit = $this->get_unit();
		$course_id = $unit->__get( 'course_id' );
		$unit_id = $unit->__get( 'ID' );
		$step_id = $this->__get( 'ID' );
		if ( ! empty( $question['options'] ) ) {
			$answers = $question['options']['answers'];
			foreach ( $answers as $pos => $answer ) {
				$name = '';
				switch ( $question['type'] ) {
					case 'single':
						$name = sprintf( 'module[%d][%d][%d][%s]', $course_id, $unit_id, $step_id, $index );
					break;
					default:
						$name = sprintf( 'module[%d][%d][%d][%s][%d]', $course_id, $unit_id, $step_id, $index, $pos );
					break;
				}
				$attr = array(
					'type' => 'radio',
					'value' => $pos,
					'name' => $name,
				);
				if ( $this->is_preview() ) {
					$attr['readonly'] = 'readonly';
					$attr['disabled'] = 'disabled';
				}
				$input = $this->create_html( 'input', $attr );
				$label = $this->create_html( 'label', array(), $input . $answer );
				$template .= $this->create_html( 'li', array(), $label );
			}
			$template = $this->create_html( 'ul', array( 'class' => 'quiz-single' ), $template );
		}
		return $template;
	}

	protected function get_question_select( $index, $question ) {
		$template = '';
		$unit = $this->get_unit();
		$course_id = $unit->__get( 'course_id' );
		$unit_id = $unit->__get( 'ID' );
		$step_id = $this->__get( 'ID' );
		if ( ! empty( $question['options'] ) ) {
			$answers = $question['options']['answers'];
			foreach ( $answers as $pos => $answer ) {
				$attr = array(
					'value' => $pos,
				);
				$template .= $this->create_html( 'option', $attr, $answer );
			}
			$name = sprintf( 'module[%d][%d][%d][%s]', $course_id, $unit_id, $step_id, $index );
			$attr = array(
				'name' => $name,
			);
			if ( $this->is_preview() ) {
				$attr['readonly'] = 'readonly';
				$attr['disabled'] = 'disabled';
			}
			$template = $this->create_html( 'select', $attr, $template );
		}
		return $template;
	}

	public function get_question() {
		$template = '';
		$questions = $this->get_questions_data();
		if ( ! empty( $questions ) ) {
			foreach ( $questions as $index => $question ) {
				$method = 'get_question_' . $question['type'];
				if ( ! empty( $question['question'] ) ) {
					$template .= $this->create_html( 'p', array( 'class' => 'question' ), $question['question'] );
				}
				$template .= call_user_func( array( $this, $method ), $index, $question );
			}
		}
		return $template;
	}
}
