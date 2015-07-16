<?php

class CoursePress_Model_Module {

	private static $post_type = 'module';

	public static function get_format() {

		return array(
			'post_type' => self::$post_type,
			'post_args' => array(
				'labels'			 => array(
					'name'				 => __( 'Modules', 'cp' ),
					'singular_name'		 => __( 'Module', 'cp' ),
					'add_new'			 => __( 'Create New', 'cp' ),
					'add_new_item'		 => __( 'Create New Module', 'cp' ),
					'edit_item'			 => __( 'Edit Module', 'cp' ),
					'edit'				 => __( 'Edit', 'cp' ),
					'new_item'			 => __( 'New Module', 'cp' ),
					'view_item'			 => __( 'View Module', 'cp' ),
					'search_items'		 => __( 'Search Modules', 'cp' ),
					'not_found'			 => __( 'No Modules Found', 'cp' ),
					'not_found_in_trash' => __( 'No Modules found in Trash', 'cp' ),
					'view'				 => __( 'View Module', 'cp' )
				),
				'public'			 => false,
				'show_ui'			 => true,
				'publicly_queryable' => false,
				'capability_type'	 => 'module',
				'map_meta_cap'		 => true,
				'query_var'			 => true
			)
		);

	}

	public static function get_post_type_name( $with_prefix = false ) {
		if ( ! $with_prefix ) {
			return self::$post_type;
		} else {
			$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
			$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';

			return $prefix . self::$post_type;
		}
	}

}