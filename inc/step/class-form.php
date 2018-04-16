<?php
/**
 * Class CoursePress_Step_Form
 * Note*: Legacy class to handle `input-form`
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Form extends CoursePress_Step_Quiz {
	protected function get_questions_data() {
		$questions = parent::get_questions_data();

		if ( $questions ) {
			foreach ( $questions as $index => $data ) {
				if ( 'selectable' === $data['type'] )
					$data['type'] = 'select';
				if ( 'long' === $data['type'] )
					$data['type'] = 'short';

				$questions[ $index ] = $data;
			}
		}

		return $questions;
	}

	public function get_question_short( $index, $question ) {
		$unit = $this->get_unit();
		$course_id = $unit->__get( 'course_id' );
		$unit_id = $unit->__get( 'ID' );
		$step_id = $this->__get( 'ID' );
		$name = sprintf( 'module[%d][%d][%d][%d]', $course_id, $unit_id, $step_id, $index );

		$attr = array(
			'name' => $name,
			'cols' => 5,
			'rows' => 3,
			'data-limit' => 0,
		);

		return $this->create_html( 'textarea', $attr );
	}
}
