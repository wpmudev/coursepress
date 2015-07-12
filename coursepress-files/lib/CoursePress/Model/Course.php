<?php

class CoursePress_Model_Course {


	public static function get_format() {

		return array(
			'post_type' => 'course',
			'post_args' => array(
				'labels'              => array(
					'name'               => __( 'Courses', 'cp' ),
					'singular_name'      => __( 'Course', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Course', 'cp' ),
					'edit_item'          => __( 'Edit Course', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Course', 'cp' ),
					'view_item'          => __( 'View Course', 'cp' ),
					'search_items'       => __( 'Search Courses', 'cp' ),
					'not_found'          => __( 'No Courses Found', 'cp' ),
					'not_found_in_trash' => __( 'No Courses found in Trash', 'cp' ),
					'view'               => __( 'View Course', 'cp' )
				),
				'public'              => false,
				'exclude_from_search' => false,
				'has_archive'         => true,
				'show_ui'             => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'course',
				'map_meta_cap'        => true,
				'query_var'           => true,
				'rewrite'             => array(
					'slug'       => CoursePress_Core::get_slug( 'course' ),
					'with_front' => false
				),
				'supports'            => array( 'thumbnail' ),
				'taxonomies'          => array( 'course_category' ),
			)
		);

	}

}