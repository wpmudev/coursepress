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

		if ( $this->is_required() && empty( $request['comment'] ) ) {
			// Redirect back.
			$referer = filter_input( INPUT_POST, 'referer_url' );
			$error   = __( 'Response is required for all fields.', 'cp' );
			coursepress_set_cookie( 'cp_step_error', $error, time() + 120 );
			wp_safe_redirect( $referer );
			exit;
		}

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
