<?php
/**
 * Class CoursePress_Admin_Course
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Admin_Course extends CoursePress_Course {
	/**
	 * CoursePress_Admin_Course constructor.
	 *
	 * @param int|WP_POST $course
	 */
	public function __construct( $course = 0 ) {
		if ( null == $course || (int) $course <= 0 ) {
			// Create draft course
			$course = get_default_post_to_edit( $this->post_type, true );
		}

		parent::__construct( $course );
	}
}