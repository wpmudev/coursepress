<?php
/**
 * Class CoursePress_Data_Courses
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Data_Courses extends CoursePress_Utility {
	protected $post_type = 'course';

	public function __construct() {
		// Register custom post_type
		add_action( 'init', array( $this, 'register' ) );
	}

	function register() {
		register_post_type( $this->post_type, array(
			'public' => true,
			'label' => __( 'CoursePress', 'cp' ),
			'show_ui' => false,
			'show_in_nav_menu' => false,
		) );
	}

	function save_course() {
		$course_meta = array();

		if ( ! empty( $_POST['course_meta'] ) ) {
			$course_meta = $_POST['course_meta'];
		}

		/**
		 * Trigger whenever a course is created or updated.
		 *
		 * @since 3.0
		 * @param int $course_id The ID of the course created or updated.
		 * @param array $meta An array of course meta data.
		 */
		do_action( 'coursepress_course_updated', $course_id, $course_meta );
	}

	function delete_course( $course_id ) {
		/**
		 * Trigger whenever a course is deleted.
		 *
		 * @since 3.0
		 * @param int $course_id The ID deleted course.
		 */
		do_action( 'coursepress_deleted_course', $course_id );
	}
}