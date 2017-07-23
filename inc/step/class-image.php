<?php
/**
 * Class CoursePress_Step_Image
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Image extends CoursePress_Step {
	protected $type = 'image';

	function get_keys() {
		$keys = parent::get_keys();
		$keys = array_merge( $keys, array(
			'image_url',
			'show_media_caption',
			'caption_field',
			'caption_custom_text',
		));

		return $keys;
	}

	function get_question() {
		// @todo: Do
		return 'IMAGE STEP HERE';
	}
}