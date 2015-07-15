<?php

class CoursePress_Model_Course {

	private static $post_type = 'course';
	private static $post_taxonomy = 'course_category';

	public static function get_format() {

		return array(
			'post_type' => self::$post_type,
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

	public static function get_taxonomy() {
		$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
		$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';

		return array(
			'taxonomy_type' => self::$post_taxonomy,
			'post_type'     => $prefix . self::$post_type,
			'taxonomy_args' => apply_filters( 'coursepress_register_course_category', array(
					'labels'            => array(
						'name'          => __( 'Course Categories', 'cp' ),
						'singular_name' => __( 'Course Category', 'cp' ),
						'search_items'  => __( 'Search Course Categories', 'cp' ),
						'all_items'     => __( 'All Course Categories', 'cp' ),
						'edit_item'     => __( 'Edit Course Categories', 'cp' ),
						'update_item'   => __( 'Update Course Category', 'cp' ),
						'add_new_item'  => __( 'Add New Course Category', 'cp' ),
						'new_item_name' => __( 'New Course Category Name', 'cp' ),
						'menu_name'     => __( 'Course Category', 'cp' ),
					),
					'hierarchical'      => true,
					'sort'              => true,
					'args'              => array( 'orderby' => 'term_order' ),
					'rewrite'           => array( 'slug' => CoursePress_Core::get_setting( 'slugs/category' ) ),
					'show_admin_column' => true,
					'capabilities'      => array(
						'manage_terms' => 'coursepress_course_categories_manage_terms_cap',
						'edit_terms'   => 'coursepress_course_categories_edit_terms_cap',
						'delete_terms' => 'coursepress_course_categories_delete_terms_cap',
						'assign_terms' => 'coursepress_courses_cap'
					),
				)
			)
		);

	}


	public static function update( $course_id, $data ) {
		global $user_id;

		$new_course = 0 === $data->course_id ? true : false;

		$course = $new_course ? false : get_post( $data->course_id );

		// Publishing toggle
		//$post_status = empty( $this->data[ 'status' ] ) ? 'publish' : $this->data[ 'status' ];

		$post = array();

		$post = array(
			'post_author'	 => $course ? $course->post_author : $user_id,
			'post_status'	 => $course ? $course->post_status : 'private',
			'post_type'		 => self::get_post_type_name( true ),
		);

		// Make sure we get existing settings if not all data is being submitted
		if( ! $new_course ) {
			$post[ 'post_excerpt' ]  = $course && isset( $data->course_excerpt) ? CoursePress_Helper_Utility::filter_content( $data->course_excerpt ) : $course->post_excerpt;
			$post[ 'post_content' ]  = $course && isset( $data->course_description) ? CoursePress_Helper_Utility::filter_content( $data->course_description ) : $course->post_content;
			$post[ 'post_title' ]  = $course && isset( $data->course_name) ? CoursePress_Helper_Utility::filter_content( $data->course_name ) : $course->post_title;
			if ( !empty( $data->course_name ) ) {
				$post[ 'post_name' ] = wp_unique_post_slug( sanitize_title( $post[ 'post_title' ] ), $course_id, 'publish', 'course', 0 );
			}
		} else {
			$post[ 'post_excerpt' ]	 = CoursePress_Helper_Utility::filter_content( $data->course_excerpt );
			if ( isset( $data->course_description ) ) {
				$post[ 'post_content' ] = CoursePress_Helper_Utility::filter_content( $data->course_description );
			}
			$post[ 'post_title' ]	 = CoursePress_Helper_Utility::filter_content( $data->course_name );
			$post[ 'post_name' ]	 = wp_unique_post_slug( sanitize_title( $post[ 'post_title' ] ), 0, 'publish', 'course', 0 );
		}

		// Set the ID to trigger update and not insert
		if( ! empty ( $data->course_id ) ) {
			$post[ 'ID' ] = $data->course_id;
		}

		// Turn off ping backs
		$post['ping_status'] = 'closed';

		// Insert / Update the post
		//$post_id = wp_insert_post( apply_filters( 'coursepress_pre_insert_post', $post ) );

		// Course Settings
		$settings = self::get_setting( $data->course_id, true );

		// Upgrade old settings
		if( empty( $settings ) && ! $new_course ) {
			self::upgrade_settings( $data->course_id );
		}



	}


	public static function get_setting( $course_id, $key = true, $default = null ) {

		$settings = get_post_meta( $course_id, 'course_settings' );

		// Return all settings
		if( true === $key ) {
			return $settings;
		}

		$setting = CoursePress_Helper_Utility::get_array_val( $settings, $key );
		$setting = is_null( $setting ) ? $default : $setting;
		$setting = !is_array( $setting ) ? trim( $setting ) : $setting;

		return $setting;
	}

	public static function update_setting( $course_id, $key = true, $value ) {

		$settings = get_post_meta( $course_id, 'course_settings' );

		if( true === $key ) {
			// Replace all settings
			$settings = $value;
		} else {
			// Replace only one setting
			CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
		}

		return update_post_meta( $course_id, 'course_settings', $settings );
	}

	/**
	 *
	 * Warning: This does not save the settings, it just updates the passed in array.
	 *
	 * @param $settings
	 * @param $key
	 * @param $value
	 */
	public static function set_setting( &$settings, $key, $value ) {
		CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
	}

	public static function upgrade_settings( $course_id ) {

		$settings = array();

		$course_order = get_post_meta( $course_id, 'course_order', true );
		$course_order = empty( $course_order ) ? 0 : $course_order;
		self::set_setting( $settings, 'course_order', $course_order );






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

	public static function get_post_category_name( $with_prefix = false ) {
		if ( ! $with_prefix ) {
			return self::$post_taxonomy;
		} else {
			$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
			$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';

			return $prefix . self::$post_taxonomy;
		}
	}

	public static function get_terms() {
		$prefix   = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
		$prefix   = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';
		$category = $prefix . self::get_post_category_name();

		$args = array(
			'orderby'      => 'name',
			'order'        => 'ASC',
			'hide_empty'   => false,
			'fields'       => 'all',
			'hierarchical' => true,
		);

		return get_terms( array( $category ), $args );
	}

	public static function get_course_terms( $course_id, $array = false ) {
		$prefix   = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
		$prefix   = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';
		$category = $prefix . self::get_post_category_name();

		$course_terms = wp_get_object_terms( (int) $course_id, array( $category ) );

		if ( ! $array ) {
			return $course_terms;
		} else {
			$course_terms_array = array();
			foreach ( $course_terms as $course_term ) {
				$course_terms_array[] = $course_term->term_id;
			}

			return $course_terms_array;
		}

	}

	public static function get_course_categories() {
		$terms = self::get_terms();
		$categories = array();
		foreach( $terms as $term ) {
			$categories[ $term->term_id ] = $term->name;
		}
		return $categories;
	}

	public static function get_units( $course_id ) {

		$post_args = array(
			'post_type'   => 'unit',
			'post_parent' => $course_id,
			'post_status' => array( 'publish', 'private' )
		);

		$query = new WP_Query( $post_args );

		return $query->posts;
	}

	public static function get_unit_ids( $course_id ) {

		$post_args = array(
			'post_type'   => 'unit',
			'post_parent' => $course_id,
			'post_status' => array( 'publish', 'private' ),
			'fields'      => 'ids'
		);

		$query = new WP_Query( $post_args );

		return $query->posts;
	}

	// META
	public static function get_listing_image( $course_id ) {
		$url = get_post_meta( $course_id, 'featured_url', true );
		$url = empty( $url ) ? get_post_meta( $course_id, '_thumbnail_id', true ) : $url;

		return apply_filters( 'coursepress_course_listing_image', $url, $course_id );
	}

	public static function get_course_language( $course_id ) {
		return apply_filters( 'coursepress_course_language', get_post_meta( $course_id, 'course_language', true ), $course_id );
	}

	public static function get_setup_marker( $course_id ) {
		return get_post_meta( $course_id, 'course_setup_marker', true );
	}

	public static function update_setup_marker( $course_id, $value ) {
		return update_post_meta( $course_id, 'course_setup_marker', $value );
	}

}