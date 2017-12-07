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
							if ( 'select' === $question['type'] ) {
								if ( $answer_pos == $user_response ) {
									if ( $checked[ $user_response ] ) {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer correct' ),
											$answer
										);
									} else {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer wrong' ),
											$answer
										);
									}
								}
							} else {
								if ( isset( $user_response[ $answer_pos ] ) ) {
									if ( $checked[ $answer_pos ] ) {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer correct' ),
											$answer
										);
									} else {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer wrong' ),
											$answer
										);
									}
								}
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

	public function get_student_answer( $user_id = 0 ) {

		$template = parent::get_answer_template( $user_id );

		$questions = $this->__get( 'questions' );

		if ( $questions ) {
			$list = '';
			$response = $this->get_user_response( $user_id );
			echo '<pre>'; print_r($response); exit;
			foreach ( $questions as $pos => $question ) {
				$q = $this->create_html( 'p', array( 'class' => 'question' ), $question['question'] );
				$a = '';
				if ( isset( $response[ $pos ] ) ) {
					$user_response = $response[ $pos ];
					$answers = $question['options']['answers'];
					$checked = $question['options']['checked'];
					if ( $answers ) {
						foreach ( $answers as $answer_pos => $answer ) {
							if ( 'select' === $question['type'] ) {
								if ( $answer_pos == $user_response ) {
									if ( $checked[ $user_response ] ) {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer correct' ),
											$answer
										);
									} else {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer wrong' ),
											$answer
										);
									}
								}
							} else {
								if ( isset( $user_response[ $answer_pos ] ) ) {
									if ( $checked[ $answer_pos ] ) {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer correct' ),
											$answer
										);
									} else {
										$a .= $this->create_html(
											'p',
											array( 'class' => 'chosen-answer wrong' ),
											$answer
										);
									}
								}
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

	public function validate_response( $response = array() ) {
		if ( ! empty( $response ) ) {
			$user = coursepress_get_user();
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
						$user_response = null;
						switch ( $question['type'] ) {
							case 'single':
								$user_response = array_shift( $response3 );
							break;
							default:
								if ( isset( $response3[ $pos ] ) ) {
									$user_response = $response3[ $pos ];
								}
							break;
						}
						$answers = $question['options']['answers'];
						$checked = $question['options']['checked'];
						$count += count( $answers );
						$checked_count += count( array_filter( $checked ) );
						if ( $answers ) {
							foreach ( $answers as $answer_pos => $answer ) {
								if ( 'select' === $question['type'] ) {
									if ( $checked[ $user_response ] && $user_response == $answer_pos ) {
										$correct++;
									}
								} else {
									if ( isset( $user_response[ $answer_pos ] ) ) {
										if ( $checked[ $answer_pos ] ) {
											$correct ++;
										} else {
											$wrong ++;
										}
									}
								}
							}
						}
					}
					if ( $wrong > 0 ) {
						$ratio = 100 / $count;
					} else {
						$ratio = 100 / $checked_count;
					}
					$grade = $correct * $ratio;
					if ( $correct > 0 && $wrong > 0 ) {
						//$wrong = $wrong * $ratio;
						//grade -= $wrong;
					}
					$data['grade'] = max( 0, $grade );
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
