<?php
/**
 * Class CoursePress_Step_Select
 * Note*: Legacy class to handle `input-select`
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Select extends CoursePress_Step_Quiz {
	protected function get_questions_data() {
		$answers = $this->__get( 'answers' );
		$selected_answers = $this->__get( 'answers_selected' );
		$checked = array();

		foreach ( $answers as $pos => $answer ) {
			$checked[ $pos ] = (int) $pos == (int) $selected_answers;
		}

		return array(
			array(
				'type' => 'select',
				'question' => '',
				'options' => array(
					'answers' => $answers,
					'checked' => $checked,
				)
			)
		);
	}
}