<?php
/**
 * Class CoursePress_Data_Units
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Data_Units {
	protected $post_type = 'unit';

	public function __construct() {
		// Reigister post type
		add_action( 'init', array( $this, 'register' ) );
	}

	function register() {
		register_post_type( $this->post_type, array(
			'public' => false,
			'show_ui' => false,
		) );
	}

	function get_course_units( $course_id = 0, $status = 'any' ) {

		if ( (int) $course_id === 0 )
			return array();

		$args = array(
			'post_type' => $this->post_type,
			'post_status' => $status,
			'post_parent' => $course_id,
			'order_by' => 'menu_order',
			'order' => 'ASC',
		);

		$units = get_posts( $args );

		return $units;
	}
}