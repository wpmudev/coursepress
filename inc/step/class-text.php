<?php
/**
 * Class CoursePress_Step_Text
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Step_Text extends CoursePress_Step {
	protected $type = 'text';

	/**
	 * Always show step content.
	 *
	 * Override parent class method to always show
	 * text type step contents.
	 *
	 * @return bool
	 */
	public function is_show_content() {
		return true;
	}
}