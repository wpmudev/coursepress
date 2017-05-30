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
			//'show_ui' => false,
			'show_in_nav_menu' => false,
		) );
	}

	function update_course() {}

	function delete_course() {}

	function get_courses( $args = array() ) {
		$posts_per_page = coursepress_get_option( 'posts_per_page', 20 );
		$defaults = array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
		);
		$args = wp_parse_args( $args, $defaults );

		$courses = get_posts( $args );

		return $courses;
	}

	/**
	 * Helper method to add post meta to a course.
	 *
	 * @since 3.0
	 * @param int $course_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 */
	function add_course_meta( $course_id, $meta_key, $meta_value ) {
		add_post_meta( $course_id, $meta_key, $meta_value );
	}

	/**
	 * Helper method to remove post meta from a course.
	 *
	 * @since 3.0
	 * @param int $course_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 */
	function delete_course_meta( $course_id, $meta_key, $meta_value ) {
		delete_post_meta( $course_id, $meta_key, $meta_value );
	}
}