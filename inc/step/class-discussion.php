<?php
/**
 * Class CoursePress_Step_Discussion
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Discussion extends CoursePress_Step {
	protected $type = 'discussion';

	function validate_response( $response = array() ) {
		$request = $_POST;

		if ( empty( $request['submit_module'] ) ) {
			$commentClass = new CoursePress_Discussion( $this->__get( 'ID' ) );
			$commentClass->add_comment( $request );
		}
	}

	function get_question() {
		$commentClass = new CoursePress_Discussion( $this->__get( 'ID' ) );
		$template = $commentClass->render();

		return $template;
	}
}