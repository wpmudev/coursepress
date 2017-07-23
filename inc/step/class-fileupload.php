<?php
/**
 * Class CoursePress_Step_FileUpload
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_FileUpload extends CoursePress_Step {
	protected $type = 'fileupload';

	function setUpStepMeta() {
		parent::setUpStepMeta();

		if ( ! $this->__get( 'allowed_file_types' ) ) {
			$allowed = array( 'zip', 'pdf', 'txt' );
			$this->__set( 'allowed_file_types', $allowed );
			$this->__set( 'meta_allowed_file_types', $allowed );
		}
	}

	function get_question() {
		// @todo: Do
		return 'FILE UPLOAD HERE';
	}
}