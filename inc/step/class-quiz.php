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
		return $this->__get( 'questions' );
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
				$name = sprintf( 'module[%d][%d][%d][%d][%d]', $course_id, $unit_id, $step_id, $index, $pos );
				$attr = array(
					'type' => 'checkbox',
					'value' => $pos,
					'name' => $name,
				);

				$input = $this->create_html( 'input', $attr );
				$label = $this->create_html( 'label', array(), $input . $answer );
				$template .= $this->create_html( 'li', array(), $label );
			}

			$template = $this->create_html( 'ul', array( 'class' => 'quiz-multiple' ), $template );
		}

		return $template;
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
				$name = sprintf( 'module[%d][%d][%d][%d]', $course_id, $unit_id, $step_id, $index );
				$attr = array(
					'type' => 'radio',
					'value' => $pos,
					'name' => $name,
				);

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

			$name = sprintf( 'module[%d][%d][%d][%d]', $course_id, $unit_id, $step_id, $index );
			$attr = array(
				'name' => $name
			);

			$template = $this->create_html( 'select', $attr, $template );
		}

		return $template;
	}

	function get_question() {
		$template = '';

		$questions = $this->get_questions_data();

		if ( ! empty( $questions ) ) {
			foreach ( $questions as $index => $question ) {
				$method = 'get_question_' . $question['type'];

				if ( ! empty( $question['question'] ) )
					$template .= $this->create_html( 'p', array( 'class' => 'question' ), $question['question'] );

				$template .= call_user_func( array( $this, $method ), $index, $question );
			}
		}

		return $template;
	}
}