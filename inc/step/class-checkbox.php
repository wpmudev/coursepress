<?php
/**
 * Class CoursePress_Step_Checkbox
 *
 * Note*: Legacy class to handle `input-checkbox` module type.
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Checkbox extends CoursePress_Step_Quiz {
	function setUpStepMeta() {
		parent::setUpStepMeta();

		$questions = $this->get_questions_data();
		error_log(print_r($questions,true));
		$this->__set( 'questions', $questions );
		$this->__set( 'meta_questions', $questions );
	}

	protected function get_questions_data() {
		$answers = $this->__get( 'answers' );
		$selected_answers = $this->__get( 'answers_selected' );
		$checked = array();

		foreach ( $answers as $pos => $answer ) {
			$checked[ $pos ] = in_array( $pos, $selected_answers );
		}
		$question = $this->__get( 'post_content' );

		return array(
			array(
				'type' => 'multiple',
				'question' => strip_tags( $question ),
				'options' => array(
					'answers' => $answers,
					'checked' => $checked,
				)
			)
		);
	}
}