<?php

class CoursePress_Model_Course {

	private static $post_type = 'course';
	private static $post_taxonomy = 'course_category';
	public static $messages;
	private static $last_course_id = 0;
	private static $where_post_status;

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

	public static function get_message( $key, $alternate = '' ) {

		$message_keys = array_keys( self::$messages );

		if ( ! in_array( $key, $message_keys ) ) {
			self::$messages = self::get_default_messages( $key );
		}

		return ! empty( self::$messages[ $key ] ) ? CoursePress_Helper_Utility::filter_content( self::$messages[ $key ] ) : CoursePress_Helper_Utility::filter_content( $alternate );
	}

	public static function get_default_messages( $key = '' ) {
		return apply_filters( 'coursepress_course_messages', array(
			'ca'  => __( 'New Course added successfully!', CoursePress::TD ),
			'cu'  => __( 'Course updated successfully.', CoursePress::TD ),
			'usc' => __( 'Unit status changed successfully', CoursePress::TD ),
			'ud'  => __( 'Unit deleted successfully', CoursePress::TD ),
			'ua'  => __( 'New Unit added successfully!', CoursePress::TD ),
			'uu'  => __( 'Unit updated successfully.', CoursePress::TD ),
			'as'  => __( 'Student added to the class successfully.', CoursePress::TD ),
			'ac'  => __( 'New class has been added successfully.', CoursePress::TD ),
			'dc'  => __( 'Selected class has been deleted successfully.', CoursePress::TD ),
			'us'  => __( 'Selected student has been withdrawed successfully from the course.', CoursePress::TD ),
			'usl' => __( 'Selected students has been withdrawed successfully from the course.', CoursePress::TD ),
			'is'  => __( 'Invitation sent sucessfully.', CoursePress::TD ),
			'ia'  => __( 'Successfully added as instructor.', CoursePress::TD ),
		), $key );
	}


	public static function update( $course_id, $data ) {
		global $user_id;

		$new_course = 0 === $data->course_id ? true : false;

		$course = $new_course ? false : get_post( $data->course_id );

		// Publishing toggle
		//$post_status = empty( $this->data[ 'status' ] ) ? 'publish' : $this->data[ 'status' ];

		$post = array(
			'post_author' => $course ? $course->post_author : $user_id,
			'post_status' => $course ? $course->post_status : 'private',
			'post_type'   => self::get_post_type_name( true ),
		);

		// Make sure we get existing settings if not all data is being submitted
		if ( ! $new_course ) {
			$post['post_excerpt'] = $course && isset( $data->course_excerpt ) ? CoursePress_Helper_Utility::filter_content( $data->course_excerpt ) : $course->post_excerpt;
			$post['post_content'] = $course && isset( $data->course_description ) ? CoursePress_Helper_Utility::filter_content( $data->course_description ) : $course->post_content;
			$post['post_title']   = $course && isset( $data->course_name ) ? CoursePress_Helper_Utility::filter_content( $data->course_name ) : $course->post_title;
			if ( ! empty( $data->course_name ) ) {
				$post['post_name'] = wp_unique_post_slug( sanitize_title( $post['post_title'] ), $course_id, 'publish', 'course', 0 );
			}
		} else {
			$post['post_excerpt'] = CoursePress_Helper_Utility::filter_content( $data->course_excerpt );
			if ( isset( $data->course_description ) ) {
				$post['post_content'] = CoursePress_Helper_Utility::filter_content( $data->course_description );
			}
			$post['post_title'] = CoursePress_Helper_Utility::filter_content( $data->course_name );
			$post['post_name']  = wp_unique_post_slug( sanitize_title( $post['post_title'] ), 0, 'publish', 'course', 0 );
		}

		// Set the ID to trigger update and not insert
		if ( ! empty ( $data->course_id ) ) {
			$post['ID'] = $data->course_id;
		}

		// Turn off ping backs
		$post['ping_status'] = 'closed';

		// Insert / Update the post
		$post_id = wp_insert_post( apply_filters( 'coursepress_pre_insert_post', $post ) );

		// Course Settings
		$settings = self::get_setting( $data->course_id, true );


		// @todo: remove this, its just here to help set initial meta that got missed during dev
		//$meta = get_post_meta( $course_id );
		//self::set_setting( $settings, 'structure_visible', self::upgrade_meta_val( $meta, 'course_structure_options', '' ) );

		// Upgrade old settings
		if ( empty( $settings ) && ! $new_course ) {
			self::upgrade_settings( $data->course_id );
		}

		if ( ! empty( $post_id ) ) {

			foreach ( $data as $key => $value ) {

				// Its easier working with arrays here
				$value = CoursePress_Helper_Utility::object_to_array( $value );

				// Set fields based on meta_ name prefix
				if ( preg_match( "/meta_/i", $key ) ) {//every field name with prefix "meta_" will be saved as post meta automatically
					//error_log( 'meh: ' . $key );
					self::set_setting( $settings, str_replace( 'meta_', '', $key ), CoursePress_Helper_Utility::filter_content( $value ) );
				}

				// MP Stuff.. is this needed?
				//if ( preg_match( "/mp_/i", $key ) ) {
				//	update_post_meta( $post_id, $key, cp_filter_content( $value ) );
				//}

				// Add taxonomy terms
				if ( $key == 'course_category' || $key == 'meta_course_category' ) {
					if ( isset( $data->meta_course_category ) ) {
						self::set_setting( $settings, 'course_category', CoursePress_Helper_Utility::filter_content( $value ) );

						if ( is_array( CoursePress_Helper_Utility::object_to_array( $data->meta_course_category ) ) ) {
							$sanitized_array = array();
							foreach ( $data->meta_course_category as $cat_id ) {
								$sanitized_array[] = (int) $cat_id;
							}

							wp_set_object_terms( $post_id, $sanitized_array, self::get_post_category_name( true ), false );
						} else {
							$cat = array( (int) $data->meta_course_category );
							if ( $cat ) {
								wp_set_object_terms( $post_id, $cat, self::get_post_category_name( true ), false );
							}
						}
					} // meta_course_category
				}

				//Add featured image
				if ( 'meta_listing_image' == $key ) {

					$course_image_width  = CoursePress_Core::get_setting( 'course/image_width', 235 );
					$course_image_height = CoursePress_Core::get_setting( 'course/image_height', 225 );

					$upload_dir_info = wp_upload_dir();

					$fl = trailingslashit( $upload_dir_info['path'] ) . basename( $value );

					$image = wp_get_image_editor( $fl ); // Return an implementation that extends <tt>WP_Image_Editor</tt>

					if ( ! is_wp_error( $image ) ) {

						$image_size = $image->get_size();

						if ( ( $image_size['width'] < $course_image_width || $image_size['height'] < $course_image_height ) || ( $image_size['width'] == $course_image_width && $image_size['height'] == $course_image_height ) ) {
							// legacy
							update_post_meta( $post_id, '_thumbnail_id', CoursePress_Helper_Utility::filter_content( $value ) );
						} else {
							$ext           = pathinfo( $fl, PATHINFO_EXTENSION );
							$new_file_name = str_replace( '.' . $ext, '-' . $course_image_width . 'x' . $course_image_height . '.' . $ext, basename( $value ) );
							$new_file_path = str_replace( basename( $value ), $new_file_name, $value );
							// legacy
							update_post_meta( $post_id, '_thumbnail_id', CoursePress_Helper_Utility::filter_content( $new_file_path ) );
						}
					} else {
						// legacy
						update_post_meta( $post_id, '_thumbnail_id', CoursePress_Helper_Utility::filter_content( $value, true ) );
					}
				}

				//Add instructors
				if ( 'instructor' == $key ) {

					//Get last instructor ID array in order to compare with posted one
					$old_post_meta = self::get_setting( $course_id, 'instructors', false );

					if ( serialize( array( $value ) ) !== serialize( $old_post_meta ) || 0 == $value ) {//If instructors IDs don't match
						delete_post_meta( $post_id, 'instructors' );
						self::delete_setting( $course_id, 'instructors' );
						CoursePress_Helper_Utility::delete_user_meta_by_key( 'course_' . $post_id );
					}

					if ( 0 != $value ) {

						update_post_meta( $post_id, 'instructors', CoursePress_Helper_Utility::filter_content( $value ) ); //Save instructors for the Course


						foreach ( $value as $instructor_id ) {
							$global_option = ! is_multisite();
							update_user_option( $instructor_id, 'course_' . $post_id, $post_id, $global_option ); //Link courses and instructors ( in order to avoid custom tables ) for easy MySql queries ( get instructor stats, his courses, etc. )
						}
					} // only add meta if array is sent
				}

			}

			// @todo
			if ( isset( $data->payment_paid_course ) ) {
				//$this->update_mp_product( $post_id );
			}

			// Update Meta
			self::update_setting( $course_id, true, $settings );

			if ( $new_course ) {

				/**
				 * Perform action after course has been created.
				 *
				 * @since 1.2.1
				 */
				do_action( 'coursepress_course_created', $post_id );
			} else {

				/**
				 * Perform action after course has been updated.
				 *
				 * @since 1.2.1
				 */
				do_action( 'coursepress_course_updated', $post_id );
			}

			return $post_id;

		}


	}

	public static function add_instructor( $course_id, $instructor_id ) {

		$instructors = maybe_unserialize( self::get_setting( $course_id, 'instructors', false ) );

		if( ! in_array( $instructor_id, $instructors ) ) {
			CoursePress_Model_Instructor::added_to_course( $instructor_id, $course_id );
			$instructors[] = $instructor_id;
		}

		self::update_setting( $course_id, 'instructors', $instructors );

	}

	public static function remove_instructor( $course_id, $instructor_id ) {

		$instructors = maybe_unserialize( self::get_setting( $course_id, 'instructors', false ) );

		foreach( $instructors as $idx => $instructor ) {
			if( (int) $instructor === $instructor_id ) {
				CoursePress_Model_Instructor::removed_from_course( $instructor_id, $course_id );
				unset( $instructors[ $idx ] );
			}
		}

		self::update_setting( $course_id, 'instructors', $instructors );

	}

	public static function get_setting( $course_id, $key = true, $default = null ) {

		$settings = get_post_meta( $course_id, 'course_settings', true );

		// Return all settings
		if ( true === $key ) {
			return $settings;
		}

		$setting = CoursePress_Helper_Utility::get_array_val( $settings, $key );
		$setting = is_null( $setting ) ? $default : $setting;
		$setting = ! is_array( $setting ) ? trim( $setting ) : $setting;

		return maybe_unserialize( $setting );
	}

	public static function update_setting( $course_id, $key = true, $value ) {

		$settings = get_post_meta( $course_id, 'course_settings', true );

		if ( true === $key ) {
			// Replace all settings
			$settings = $value;
		} else {
			// Replace only one setting
			CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
		}

		return update_post_meta( $course_id, 'course_settings', $settings );
	}

	public static function delete_setting( $course_id, $key = true ) {

		$settings = get_post_meta( $course_id, 'course_settings', true );

		if ( true === $key ) {
			// Replace all settings
			$settings = array();
		} else {
			// Replace only one setting
			CoursePress_Helper_Utility::unset_array_val( $settings, $key );
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

		$map = array(
			'allow_discussion'        => array( 'key' => 'allow_course_discussion', 'default' => '' ),
			'allow_grades'            => array( 'key' => 'allow_grades_page', 'default' => '' ),
			'allow_workbook'          => array( 'key' => 'allow_workbook_page', 'default' => true ),
			'course_category'         => array( 'key' => 'course_category', 'default' => '' ),
			'class_size'              => array( 'key' => 'class_size', 'default' => 0 ),
			'class_limited'           => array( 'key' => 'limit_class_size', 'default' => '' ),
			'course_open_ended'       => array( 'key' => 'open_ended_course', 'default' => true ),
			'course_start_date'       => array( 'key' => 'course_start_date', 'default' => '' ),
			'course_end_date'         => array( 'key' => 'course_end_date', 'default' => '' ),
			'course_order'            => array( 'key' => 'course_order', 'default' => 0 ),
			'enrollment_open_ended'   => array( 'key' => 'open_ended_enrollment', 'default' => true ),
			'enrollment_start_data'   => array( 'key' => 'enrollment_start_date', 'default' => '' ),
			'enrollment_end_date'     => array( 'key' => 'enrollment_end_date', 'default' => '' ),
			'enrollment_type'         => array( 'key' => 'enroll_type', 'default' => 'manually' ),
			'enrollment_prerequisite' => array( 'key' => 'prerequisite', 'default' => '' ),
			'enrollment_passcode'     => array( 'key' => 'passcode', 'default' => '' ),
			'listing_image'           => array( 'key' => 'featured_url', 'default' => '' ),
			'instructors'             => array( 'key' => 'instructors', 'default' => '' ),
			'course_language'         => array( 'key' => 'course_language', 'default' => '' ),
			'payment_paid_course'     => array( 'key' => 'paid_course', 'default' => '' ),
			'payment_auto_sku'        => array( 'key' => 'auto_sku', 'default' => '' ),
			'payment_product_id'      => array( 'key' => 'mp_product_id', 'default' => array() ),
			'setup_complete'          => array( 'key' => 'course_setup_complete', 'default' => '' ),
			'structure_visible'       => array( 'key' => 'course_structure_options', 'default' => '' ),
			'structure_show_duration' => array( 'key' => 'course_structure_time_display', 'default' => '' ),
			'structure_visible_units' => array( 'key' => 'show_unit_boxes', 'default' => '' ),
			'structure_preview_units' => array( 'key' => 'preview_unit_boxes', 'default' => '' ),
			'structure_visible_pages' => array( 'key' => 'show_page_boxes', 'default' => '' ),
			'structure_preview_pages' => array( 'key' => 'preview_page_boxes', 'default' => '' ),
			'featured_video'          => array( 'key' => 'course_video_url', 'default' => '' ),
		);

		$meta = get_post_meta( $course_id );

		foreach ( $map as $key => $old ) {
			self::set_setting( $settings, $key, self::upgrade_meta_val( $meta, $old['key'], $old['default'] ) );
		}

		self::update_setting( $course_id, true, $settings );

	}

	private static function upgrade_meta_val( $meta, $val, $default = '' ) {

		$val = isset( $meta[ $val ] ) ? $meta[ $val ] : $default;

		if ( is_array( $val ) ) {
			$val = $val[0];
		}

		if ( empty( $val ) ) {
			$val = $default;
		}

		return $val;
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
		$terms      = self::get_terms();
		$categories = array();
		foreach ( $terms as $term ) {
			$categories[ $term->term_id ] = $term->name;
		}

		return $categories;
	}

	public static function get_units( $course_id, $status = array( 'publish' ), $ids_only = false, $include_count = false ) {

		$post_args = array(
			'post_type'     => 'unit',
			'post_parent'   => $course_id,
			'post_status'   => $status,
			'posts_per_page'=> - 1,
			'order'         => 'ASC',
			'orderby'       => 'meta_value_num',
			'meta_key'      => 'unit_order'
		);

		if ( $ids_only ) {
			$post_args['fields'] = 'ids';
		}

		$query = new WP_Query( $post_args );

		if ( $include_count ) {
			// Handy if using pagination
			return array( 'units' => $query->posts, 'found' => $query->found_posts );
		} else {
			return $query->posts;
		}

	}

	public static function get_unit_ids( $course_id, $status = array( 'publish' ), $include_count = false ) {
		return self::get_units( $course_id, $status, true, $include_count );
	}

	// META
	public static function get_listing_image( $course_id ) {
		$url = CoursePress_Model_Course::get_setting( $course_id, 'listing_image' );
		$url = empty( $url ) ? get_post_meta( $course_id, '_thumbnail_id', true ) : $url;

		return apply_filters( 'coursepress_course_listing_image', $url, $course_id );
	}

	public static function get_units_with_modules( $course_id, $status = array( 'publish' ) ) {

		self::$last_course_id = $course_id;

		if( ! array( $status ) ) {
			$status = array( $status );
		};

		$sql = 'AND ( ';
		foreach( $status as $filter ) {
			$sql .= '%1$s.post_status = \'' . $filter . '\' OR ';
		}
		$sql = preg_replace('/(OR.)$/', '', $sql);
		$sql .= ' )';

		self::$where_post_status = $sql;

		add_filter( 'posts_where', array( __CLASS__, 'filter_unit_module_where' ) );

		$post_args = array(
			'post_type'     => array( 'unit', 'module' ),
			'post_parent'   => $course_id,
			'posts_per_page' => -1,
			'order'         => 'ASC',
			'orderby'       => 'menu_order',
		);

		$query = new WP_Query( $post_args );

		$combine = array();

		foreach( $query->posts as $post ) {
			if( 'module' == $post->post_type ) {
				$pages = get_post_meta( $post->post_parent, 'page_title', true );
				$page = get_post_meta( $post->ID, 'module_page', true );
				$page = ! empty( $page ) ? $page : 1;
				$page_title = ! empty( $pages ) && isset( $pages[ 'page_'.$page ] ) ? esc_html( $pages[ 'page_'.$page ] ) : '';

				$path = $post->post_parent . '/pages/' . $page . '/title';
				CoursePress_Helper_Utility::set_array_val( $combine, $path, $page_title );

				$path = $post->post_parent . '/pages/' . $page . '/modules/' . $post->ID;
				CoursePress_Helper_Utility::set_array_val( $combine, $path, $post );
			} elseif( 'unit' == $post->post_type ) {

				CoursePress_Helper_Utility::set_array_val( $combine, $post->ID . '/unit', $post );
			}
		}

		remove_filter( 'posts_where', array( __CLASS__, 'filter_unit_module_where' ) );

		return $combine;

	}

	public static function filter_unit_module_where( $sql ) {
		global $wpdb;

		$sql = 'AND ( %1$s.post_type = \'module\' AND %1$s.post_parent IN (SELECT ID FROM %1$s AS wpp WHERE wpp.post_type = \'unit\' AND wpp.post_parent = %2$d) OR (%1$s.post_type = \'unit\' AND %1$s.post_parent = %2$d ) ) ' . self::$where_post_status;
		$sql = $wpdb->prepare( $sql, $wpdb->posts, self::$last_course_id );

		return $sql;
	}

	public static function set_last_course_id( $course_id ) {
		self::$last_course_id = $course_id;
	}

	public static function last_course_id() {
		return self::$last_course_id;
	}

}