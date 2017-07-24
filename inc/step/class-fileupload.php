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
		$unit = $this->get_unit();
		$course_id = $unit->__get( 'course_id' );
		$unit_id = $unit->__get( 'ID' );
		$step_id = $this->__get( 'ID' );
		$types = $this->__get( 'allowed_file_types' );
		$name = sprintf( 'module[%d][%d][%d][%d]', $course_id, $unit_id, $step_id );

		$attr = array(
			'type' => 'file',
			'name' => $name,
			'data-types' => implode(',', $types ),
		);
		if ( $this->is_preview() ) {
			$attr['readonly'] = 'readonly';
			$attr['disabled'] = 'disabled';
		}

		$input = coursepress_create_html( 'input', $attr );

		return $input;
	}
}