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
		$defaults = array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'posts_per_page' => 20
		);
		$args = wp_parse_args( $args, $defaults );

		$courses = get_posts( $args );

		return $courses;
	}

	function add_course_meta( $course_id, $meta_key, $meta_value ) {
		add_post_meta( $course_id, $meta_key, $meta_value );
	}

	function delete_course_meta( $course_id, $meta_key, $meta_value ) {
		delete_post_meta( $course_id, $meta_key, $meta_value );
	}
}