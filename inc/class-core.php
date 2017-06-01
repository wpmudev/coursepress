<?php
/**
 * Class CoursePress_Data_Core
 *
 * @since 3.0
 * @package CoursePress
 */
class CoursePress_Core extends CoursePress_Utility {
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	function register_post_types() {
		// Unit
		register_post_type( 'unit', array(
			'public' => true,
			//'show_ui' => false,
			'hierarchical' => true,
			'label' => 'Units', // debugging only
		) );

		// Module
		register_post_type( 'module', array(
			'public' => true,
			'hierarchical' => true,
			'label' => 'Modules', // dbugging only
		) );
	}
}