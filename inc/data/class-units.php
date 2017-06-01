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
			'public' => true,
			//'show_ui' => false,
			'hierarchical' => true,
			'label' => 'Units', // debugging only
		) );
	}
}