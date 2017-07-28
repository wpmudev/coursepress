<?php
/**
 * Class CoursePress_Step_Audio
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Audio extends CoursePress_Step {
	protected $type = 'audio';

	protected function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'audio_url',
			'loop',
			'autoplay',
		));

		return $keys;
	}

	function get_question() {
		// @todo: Do
		return 'AUDIO MODULE HERE';
	}
}