<?php

class CoursePress_Data_Course {

	private static $post_type = 'course';
	private static $post_taxonomy = 'course_category';
	public static $messages;
	private static $last_course_id = 0;
	private static $where_post_status;
	private static $email_type;
	public static $last_course_category = '';
	public static $last_course_subpage = '';
	public static $previewability = false;
	public static $structure_visibility = false;

	public static function get_format() {
		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Courses', 'CP_TD' ),
					'singular_name' => __( 'Course', 'CP_TD' ),
					'add_new' => __( 'Create New', 'CP_TD' ),
					'add_new_item' => __( 'Create New Course', 'CP_TD' ),
					'edit_item' => __( 'Edit Course', 'CP_TD' ),
					'edit' => __( 'Edit', 'CP_TD' ),
					'new_item' => __( 'New Course', 'CP_TD' ),
					'view_item' => __( 'View Course', 'CP_TD' ),
					'search_items' => __( 'Search Courses', 'CP_TD' ),
					'not_found' => __( 'No Courses Found', 'CP_TD' ),
					'not_found_in_trash' => __( 'No Courses found in Trash', 'CP_TD' ),
					'view' => __( 'View Course', 'CP_TD' ),
				),
				'public' => false,
				'exclude_from_search' => false,
				'has_archive' => true,
				'show_ui' => false,
				'publicly_queryable' => true,
				'capability_type' => 'course',
				'map_meta_cap' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => CoursePress_Core::get_slug( 'course' ),
					'with_front' => false,
				),
				'supports' => array( 'thumbnail' ),
				'taxonomies' => array( 'course_category' ),
			),
		);

	}

	public static function get_taxonomy() {
		return array(
			'taxonomy_type' => self::get_post_category_name(),
			'post_type' => self::get_post_type_name(),
			'taxonomy_args' => apply_filters(
				'coursepress_register_course_category',
				array(
					'labels' => array(
						'name' => __( 'Course Categories', 'CP_TD' ),
						'singular_name' => __( 'Course Category', 'CP_TD' ),
						'search_items' => __( 'Search Course Categories', 'CP_TD' ),
						'all_items' => __( 'All Course Categories', 'CP_TD' ),
						'edit_item' => __( 'Edit Course Categories', 'CP_TD' ),
						'update_item' => __( 'Update Course Category', 'CP_TD' ),
						'add_new_item' => __( 'Add New Course Category', 'CP_TD' ),
						'new_item_name' => __( 'New Course Category Name', 'CP_TD' ),
						'menu_name' => __( 'Course Category', 'CP_TD' ),
					),
					'hierarchical' => true,
					'sort' => true,
					'args' => array( 'orderby' => 'term_order' ),
					'rewrite' => array(
						'slug' => CoursePress_Core::get_setting(
							'slugs/category',
							'course_category'
						),
					),
					'show_admin_column' => true,
					'capabilities' => array(
						'manage_terms' => 'coursepress_course_categories_manage_terms_cap',
						'edit_terms' => 'coursepress_course_categories_edit_terms_cap',
						'delete_terms' => 'coursepress_course_categories_delete_terms_cap',
						'assign_terms' => 'coursepress_courses_cap',
					),
				)
			),
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
		return apply_filters(
			'coursepress_course_messages',
			array(
				'ca' => __( 'New Course added successfully!', 'CP_TD' ),
				'cu' => __( 'Course updated successfully.', 'CP_TD' ),
				'usc' => __( 'Unit status changed successfully', 'CP_TD' ),
				'ud' => __( 'Unit deleted successfully', 'CP_TD' ),
				'ua' => __( 'New Unit added successfully!', 'CP_TD' ),
				'uu' => __( 'Unit updated successfully.', 'CP_TD' ),
				'as' => __( 'Student added to the class successfully.', 'CP_TD' ),
				'ac' => __( 'New class has been added successfully.', 'CP_TD' ),
				'dc' => __( 'Selected class has been deleted successfully.', 'CP_TD' ),
				'us' => __( 'Selected student has been withdrawed successfully from the course.', 'CP_TD' ),
				'usl' => __( 'Selected students has been withdrawed successfully from the course.', 'CP_TD' ),
				'is' => __( 'Invitation sent sucessfully.', 'CP_TD' ),
				'ia' => __( 'Successfully added as instructor.', 'CP_TD' ),
			),
			$key
		);
	}

	public static function update( $course_id, $data ) {
		global $user_id;

		do_action( 'coursepress_course_pre_update', $course_id, $data );
		$new_course = empty( $course_id ) ? true : false;
		$course = $new_course ? false : get_post( $course_id );

		// Publishing toggle.
		$post = array(
			'post_author' => $course ? $course->post_author : $user_id,
			'post_status' => $course ? $course->post_status : 'private',
			'post_type' => self::get_post_type_name(),
		);

		// Make sure we get existing settings if not all data is being submitted
		if ( ! $new_course ) {
			$post['post_excerpt'] = $course && isset( $data->course_excerpt ) ? CoursePress_Helper_Utility::filter_content( $data->course_excerpt ) : $course->post_excerpt;
			$post['post_content'] = $course && isset( $data->course_description ) ? CoursePress_Helper_Utility::filter_content( $data->course_description ) : $course->post_content;
			$post['post_title'] = $course && isset( $data->course_name ) ? CoursePress_Helper_Utility::filter_content( $data->course_name ) : $course->post_title;
			if ( ! empty( $data->course_name ) ) {
				$post['post_name'] = wp_unique_post_slug( sanitize_title( $post['post_title'] ), $course_id, 'publish', 'course', 0 );
			}
		} else {
			$post['post_excerpt'] = CoursePress_Helper_Utility::filter_content( $data->course_excerpt );
			if ( isset( $data->course_description ) ) {
				$post['post_content'] = CoursePress_Helper_Utility::filter_content( $data->course_description );
			}
			$post['post_title'] = CoursePress_Helper_Utility::filter_content( $data->course_name );
			$post['post_name'] = wp_unique_post_slug( sanitize_title( $post['post_title'] ), 0, 'publish', 'course', 0 );
		}

		// Set the ID to trigger update and not insert
		if ( ! empty( $course_id ) ) {
			$post['ID'] = $course_id;
		}

		// Turn off ping backs
		$post['ping_status'] = 'closed';

		// Insert / Update the post
		$course_id = wp_insert_post( apply_filters( 'coursepress_pre_insert_post', $post ) );

		// Course Settings
		$settings = self::get_setting( $course_id, true );

		// @todo: remove this, its just here to help set initial meta that got missed during dev
		// $meta = get_post_meta( $course_id );
		// self::set_setting( $settings, 'structure_visible', self::upgrade_meta_val( $meta, 'course_structure_options', '' ) );
		// Upgrade old settings
		if ( empty( $settings ) && ! $new_course ) {
			self::upgrade_settings( $course_id );
		}

		if ( ! empty( $course_id ) ) {

			foreach ( $data as $key => $value ) {

				// Its easier working with arrays here
				$value = CoursePress_Helper_Utility::object_to_array( $value );

				// Set fields based on meta_ name prefix
				if ( preg_match( '/meta_/i', $key ) ) {// every field name with prefix "meta_" will be saved as post meta automatically
					self::set_setting( $settings, str_replace( 'meta_', '', $key ), CoursePress_Helper_Utility::filter_content( $value ) );
				}

				// MP Stuff.. this is no longer dealt with here!
				// if ( preg_match( "/mp_/i", $key ) ) {
				// update_post_meta( $course_id, $key, cp_filter_content( $value ) );
				// }
				// Add taxonomy terms
				if ( 'course_category' == $key || 'meta_course_category' == $key ) {
					if ( isset( $data->meta_course_category ) ) {
						self::set_setting(
							$settings,
							'course_category',
							CoursePress_Helper_Utility::filter_content( $value )
						);

						if ( is_array( CoursePress_Helper_Utility::object_to_array( $data->meta_course_category ) ) ) {
							$sanitized_array = array();
							foreach ( $data->meta_course_category as $cat_id ) {
								$sanitized_array[] = (int) $cat_id;
							}

							wp_set_object_terms(
								$course_id,
								$sanitized_array,
								self::get_post_category_name(),
								false
							);
						} else {
							$cat = array( (int) $data->meta_course_category );
							if ( $cat ) {
								wp_set_object_terms(
									$course_id,
									$cat,
									self::get_post_category_name(),
									false
								);
							}
						}
					} // meta_course_category
				}

				// Add featured image
				if ( 'meta_listing_image' == $key ) {
					// Legacy, breaks theme support
					// $course_image_width = CoursePress_Core::get_setting( 'course/image_width', 235 );
					// $course_image_height = CoursePress_Core::get_setting( 'course/image_height', 225 );
					//
					// $upload_dir_info = wp_upload_dir();
					//
					// $fl = trailingslashit( $upload_dir_info['path'] ) . basename( $value );
					//
					// $image = wp_get_image_editor( $fl ); // Return an implementation that extends <tt>WP_Image_Editor</tt>
					//
					// if ( ! is_wp_error( $image ) ) {
					//
					// $image_size = $image->get_size();
					//
					// if ( ( $image_size['width'] < $course_image_width || $image_size['height'] < $course_image_height ) || ( $image_size['width'] == $course_image_width && $image_size['height'] == $course_image_height ) ) {
					// legacy
					// update_post_meta( $course_id, '_thumbnail_id', CoursePress_Helper_Utility::filter_content( $value ) );
					// } else {
					// $ext = pathinfo( $fl, PATHINFO_EXTENSION );
					// $new_file_name = str_replace( '.' . $ext, '-' . $course_image_width . 'x' . $course_image_height . '.' . $ext, basename( $value ) );
					// $new_file_path = str_replace( basename( $value ), $new_file_name, $value );
					// legacy
					// update_post_meta( $course_id, '_thumbnail_id', CoursePress_Helper_Utility::filter_content( $new_file_path ) );
					// }
					// } else {
					// legacy
					// update_post_meta( $course_id, '_thumbnail_id', CoursePress_Helper_Utility::filter_content( $value, true ) );
					// }
					// Remove Thumbnail
					delete_post_meta( $course_id, '_thumbnail_id' );
				}

				// Add instructors.
				if ( 'instructor' == $key ) {

					// Get last instructor ID array in order to compare with posted one.
					$old_post_meta = self::get_setting( $course_id, 'instructors', false );

					if ( serialize( array( $value ) ) !== serialize( $old_post_meta ) || 0 == $value ) {
						// If instructors IDs don't match.
						delete_post_meta( $course_id, 'instructors' );
						self::delete_setting( $course_id, 'instructors' );
						CoursePress_Helper_Utility::delete_user_meta_by_key( 'course_' . $course_id );
					}

					if ( 0 != $value ) {
						// Save instructors for the Course.
						update_post_meta(
							$course_id,
							'instructors',
							CoursePress_Helper_Utility::filter_content( $value )
						);

						foreach ( $value as $instructor_id ) {
							$global_option = ! is_multisite();
							// Link courses and instructors ( in order to avoid custom tables ) for easy MySql queries ( get instructor stats, his courses, etc. )
							update_user_option(
								$instructor_id, 'course_' . $course_id,
								$course_id,
								$global_option
							);
						}
					} // only add meta if array is sent.
				}
			}

			// Update Meta.
			$settings = apply_filters(
				'coursepress_course_update_meta',
				$settings,
				$course_id
			);

			self::update_setting( $course_id, true, $settings );

			if ( $new_course ) {

				/**
				 * Perform action after course has been created.
				 *
				 * @since 1.2.1
				 */
				do_action( 'coursepress_course_created', $course_id, $settings );
			} else {

				/**
				 * Perform action after course has been updated.
				 *
				 * @since 1.2.1
				 */
				do_action( 'coursepress_course_updated', $course_id, $settings );
			}

			return $course_id;
		}
	}

	public static function add_instructor( $course_id, $instructor_id ) {
		$instructors = maybe_unserialize( self::get_setting( $course_id, 'instructors', false ) );
		$instructors = empty( $instructors ) ? array() : $instructors;
		$global_option = ! is_multisite();

		if ( ! in_array( $instructor_id, $instructors ) ) {
			CoursePress_Data_Instructor::added_to_course( $instructor_id, $course_id );
			$instructors[] = $instructor_id;
			/**
			 * update information to instructor
			 */
			update_user_option(
				$instructor_id,
				'course_' . $course_id,
				$course_id,
				$global_option
			);
		}

		self::update_setting( $course_id, 'instructors', $instructors );

		/**
		 * update instructor roles
		 */
		CoursePress_Data_Capabilities::assign_role_capabilities( $instructor_id, '', '' );
	}

	public static function remove_instructor( $course_id, $instructor_id ) {
		$instructors = maybe_unserialize( self::get_setting( $course_id, 'instructors', false ) );
		$global_option = ! is_multisite();

		foreach ( $instructors as $idx => $instructor ) {
			if ( (int) $instructor === $instructor_id ) {
				CoursePress_Data_Instructor::removed_from_course( $instructor_id, $course_id );
				unset( $instructors[ $idx ] );
				/**
				 * delete information to instructor
				 */
				delete_user_option(
					$instructor_id,
					'course_' . $course_id,
					$global_option
				);
			}
		}

		self::update_setting( $course_id, 'instructors', $instructors );

		/**
		 * update instructor roles
		 */
		CoursePress_Data_Capabilities::assign_role_capabilities( $instructor_id, '', '' );
	}

	public static function get_setting( $course_id, $key = true, $default = null ) {
		$settings = get_post_meta( $course_id, 'course_settings', true );

		// Return all settings.
		if ( true === $key ) {
			return $settings;
		}

		$setting = CoursePress_Helper_Utility::get_array_val( $settings, $key );
		$setting = is_null( $setting ) ? $default : $setting;
		$setting = ! is_array( $setting ) ? trim( $setting ) : $setting;

		return apply_filters(
			'coursepress_get_course_setting_' . $key,
			maybe_unserialize( $setting ),
			$course_id
		);
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
	 * Warning: This does not save the settings, it just updates the passed in array.
	 *
	 * @param $settings
	 * @param $key
	 * @param $value
	 */
	public static function set_setting( &$settings, $key, $value ) {
		CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
	}

	public static function allow_pages( $course_id ) {
		$pages = array(
			'course_discussion' => cp_is_true( self::get_setting( $course_id, 'allow_discussion', true ) ),
			'workbook' => cp_is_true( self::get_setting( $course_id, 'allow_workbook', true ) ),
			'grades' => cp_is_true( self::get_setting( $course_id, 'allow_grades', true ) ),
		);

		return $pages;
	}

	public static function upgrade_settings( $course_id ) {
		$settings = array();

		$map = array(
			'allow_discussion' => array( 'key' => 'allow_course_discussion', 'default' => '' ),
			'allow_grades' => array( 'key' => 'allow_grades_page', 'default' => '' ),
			'allow_workbook' => array( 'key' => 'allow_workbook_page', 'default' => true ),
			'course_category' => array( 'key' => 'course_category', 'default' => '' ),
			'class_size' => array( 'key' => 'class_size', 'default' => 0 ),
			'class_limited' => array( 'key' => 'limit_class_size', 'default' => '' ),
			'course_open_ended' => array( 'key' => 'open_ended_course', 'default' => true ),
			'course_start_date' => array( 'key' => 'course_start_date', 'default' => '' ),
			'course_end_date' => array( 'key' => 'course_end_date', 'default' => '' ),
			'course_order' => array( 'key' => 'course_order', 'default' => 0 ),
			'enrollment_open_ended' => array( 'key' => 'open_ended_enrollment', 'default' => true ),
			'enrollment_start_date' => array( 'key' => 'enrollment_start_date', 'default' => '' ),
			'enrollment_end_date' => array( 'key' => 'enrollment_end_date', 'default' => '' ),
			'enrollment_type' => array( 'key' => 'enroll_type', 'default' => 'manually' ),
			'enrollment_prerequisite' => array( 'key' => 'prerequisite', 'default' => '' ),
			'enrollment_passcode' => array( 'key' => 'passcode', 'default' => '' ),
			'listing_image' => array( 'key' => 'featured_url', 'default' => '' ),
			'instructors' => array( 'key' => 'instructors', 'default' => '' ),
			'course_language' => array( 'key' => 'course_language', 'default' => '' ),
			'payment_paid_course' => array( 'key' => 'paid_course', 'default' => '' ),
			'payment_auto_sku' => array( 'key' => 'auto_sku', 'default' => '' ),
			'payment_product_id' => array( 'key' => 'mp_product_id', 'default' => array() ),
			'setup_complete' => array( 'key' => 'course_setup_complete', 'default' => '' ),
			'structure_visible' => array( 'key' => 'course_structure_options', 'default' => '' ),
			'structure_show_duration' => array( 'key' => 'course_structure_time_display', 'default' => '' ),
			'structure_visible_units' => array( 'key' => 'show_unit_boxes', 'default' => '' ),
			'structure_preview_units' => array( 'key' => 'preview_unit_boxes', 'default' => '' ),
			'structure_visible_pages' => array( 'key' => 'show_page_boxes', 'default' => '' ),
			'structure_preview_pages' => array( 'key' => 'preview_page_boxes', 'default' => '' ),
			'featured_video' => array( 'key' => 'course_video_url', 'default' => '' ),
		);

		$meta = get_post_meta( $course_id );

		foreach ( $map as $key => $old ) {
			self::set_setting(
				$settings,
				$key,
				self::upgrade_meta_val( $meta, $old['key'], $old['default'] )
			);
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

	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	public static function get_post_category_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_taxonomy );
	}

	public static function get_terms() {
		$args = array(
			'orderby' => 'name',
			'order' => 'ASC',
			'hide_empty' => false,
			'fields' => 'all',
			'hierarchical' => true,
		);

		return get_terms(
			array( self::get_post_category_name() ),
			$args
		);
	}

	public static function get_course_terms( $course_id, $array = false ) {
		$course_terms = wp_get_object_terms(
			(int) $course_id,
			array( self::get_post_category_name() )
		);

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

	public static function get_course_categories( $course_id = false ) {
		$terms = self::get_terms();
		$categories = array();

		if ( ! $course_id ) {
			foreach ( $terms as $term ) {
				$categories[ $term->term_id ] = $term->name;
			}
		} else {
			$course_terms_array = self::get_course_terms( (int) $course_id, true );
			foreach ( $terms as $term ) {
				if ( in_array( (int) $term->term_id, $course_terms_array ) ) {
					$categories[ $term->term_id ] = $term->name;
				}
			}
		}

		return $categories;
	}

	public static function get_units(
		$course_id, $status = array( 'publish' ), $ids_only = false, $include_count = false
	) {

		$post_args = array(
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_parent' => $course_id,
			'post_status' => $status,
			'posts_per_page' => - 1,
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'meta_key' => 'unit_order',
		);

		if ( $ids_only ) {
			$post_args['fields'] = 'ids';
		}

		$query = new WP_Query( $post_args );

		if ( $include_count ) {
			// Handy if using pagination.
			return array(
				'units' => $query->posts,
				'found' => $query->found_posts,
			);
		} else {
			return $query->posts;
		}
	}

	public static function get_unit_ids( $course_id, $status = array( 'publish' ), $include_count = false ) {
		return self::get_units( $course_id, $status, true, $include_count );
	}

	// META.
	public static function get_listing_image( $course_id ) {
		$url = CoursePress_Data_Course::get_setting(
			$course_id,
			'listing_image'
		);

		if ( empty( $url ) ) {
			$url = get_post_meta( $course_id, '_thumbnail_id', true );
		}

		return apply_filters(
			'coursepress_course_listing_image',
			$url,
			$course_id
		);
	}

	public static function get_units_with_modules( $course_id, $status = array( 'publish' ) ) {
		self::$last_course_id = $course_id;
		$combine = array();

		if ( ! array( $status ) ) {
			$status = array( $status );
		};

		$sql = 'AND ( ';
		foreach ( $status as $filter ) {
			$sql .= '%1$s.post_status = \'' . $filter . '\' OR ';
		}
		$sql = preg_replace( '/(OR.)$/', '', $sql );
		$sql .= ' )';

		self::$where_post_status = $sql;

		add_filter( 'posts_where', array( __CLASS__, 'filter_unit_module_where' ) );

		$post_args = array(
			'post_type' => array(
				CoursePress_Data_Unit::get_post_type_name(),
				CoursePress_Data_Module::get_post_type_name(),
			),
			'post_parent' => $course_id,
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'menu_order',
		);

		$query = new WP_Query( $post_args );

		$unit_cpt = CoursePress_Data_Unit::get_post_type_name();
		$module_cpt = CoursePress_Data_Module::get_post_type_name();

		foreach ( $query->posts as $post ) {
			$previous_parent = 0;
			$previous_meta = array();

			if ( $module_cpt == $post->post_type ) {
				$post->module_order = get_post_meta(
					$post->ID,
					'module_order',
					true
				);

				if ( $previous_parent !== $post->post_parent ) {
					$meta = get_post_meta( $post->post_parent );
					$previous_meta = $meta;
				} else {
					$meta = $previous_meta;
				}

				$titles = isset( $meta['page_title'] ) ? maybe_unserialize( $meta['page_title'][0] ) : array();
				$descriptions = isset( $meta['page_description'] ) ? maybe_unserialize( $meta['page_description'][0] ) : array();
				$feature_images = isset( $meta['page_feature_image'] ) ? maybe_unserialize( $meta['page_feature_image'][0] ) : array();
				$visibilities = isset( $meta['show_page_title'] ) ? maybe_unserialize( $meta['show_page_title'][0] ) : array();

				$page = get_post_meta( $post->ID, 'module_page', true );
				$page = ! empty( $page ) ? $page : 1;
				$page_title = ! empty( $titles ) && isset( $titles[ 'page_'.$page ] ) ? esc_html( $titles[ 'page_'.$page ] ) : '';
				$page_description = ! empty( $descriptions ) && isset( $descriptions[ 'page_'.$page ] ) ? $descriptions[ 'page_'.$page ] : '';
				$page_image = ! empty( $feature_images ) && isset( $feature_images[ 'page_'.$page ] ) ? $feature_images[ 'page_'.$page ] : '';
				$page_visibility = ! empty( $visibilities ) && isset( $visibilities[ ( $page - 1 ) ] ) ? $visibilities[ ( $page - 1 ) ] : false;

				$path = $post->post_parent . '/pages/' . $page;
				CoursePress_Helper_Utility::set_array_val( $combine, $path . '/title', $page_title );
				CoursePress_Helper_Utility::set_array_val( $combine, $path . '/description', $page_description );
				CoursePress_Helper_Utility::set_array_val( $combine, $path . '/feature_image', $page_image );
				CoursePress_Helper_Utility::set_array_val( $combine, $path . '/visible', $page_visibility );

				$path = $post->post_parent . '/pages/' . $page . '/modules/' . $post->ID;
				CoursePress_Helper_Utility::set_array_val( $combine, $path, $post );

				$previous_parent = $post->post_parent;

			} elseif ( $unit_cpt == $post->post_type ) {
				CoursePress_Helper_Utility::set_array_val( $combine, $post->ID . '/order', get_post_meta( $post->ID, 'unit_order', true ) );
				CoursePress_Helper_Utility::set_array_val( $combine, $post->ID . '/unit', $post );
			}
		}

		// Fix legacy orphaned posts and page titles
		foreach ( $combine as $post_id => $unit ) {
			if ( ! isset( $unit['unit'] ) ) {
				unset( $combine[ $post_id ] );
			}

			// Fix broken page titles
			$page_titles = get_post_meta( $post_id, 'page_title', true );
			if ( empty( $page_titles ) ) {
				$page_titles = array();
				$page_visible = array();
				foreach ( $unit['pages'] as $key => $page ) {
					$page_titles[ 'page_' . $key ] = $page['title'];
					$page_visible[] = true;
				}
				update_post_meta( $post_id, 'page_title', $page_titles );
				update_post_meta( $post_id, 'show_page_title', $page_visible );
			}
		}

		remove_filter( 'posts_where', array( __CLASS__, 'filter_unit_module_where' ) );

		return $combine;
	}


	public static function get_unit_modules(
		$unit_id, $status = array( 'publish' ), $ids_only = false, $include_count = false, $args = array()
	) {

		$post_args = array(
			'post_type' => CoursePress_Data_Module::get_post_type_name(),
			'post_parent' => $unit_id,
			'post_status' => $status,
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'meta_key' => 'module_order',
		);

		if ( $ids_only ) {
			$post_args['fields'] = 'ids';
		}

		// Get modules for specific page
		if ( isset( $args['page'] ) && (int) $args['page'] ) {
			$post_args['meta_query'] = array(
				array(
					'key' => 'module_page',
					'value' => (int) $args['page'],
					'compare' => '=',
				),
			);
		}

		$query = new WP_Query( $post_args );

		if ( $include_count ) {
			// Handy if using pagination.
			return array(
				'units' => $query->posts,
				'found' => $query->found_posts,
			);
		} else {
			return $query->posts;
		}
	}

	public static function filter_unit_module_where( $sql ) {
		global $wpdb;

		/* @todo build in post type prefixing */
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

	public static function is_paid_course( $course_id ) {
		$is_paid = self::get_setting( $course_id, 'payment_paid_course', false );
		$is_paid = empty( $is_paid ) || 'off' === $is_paid ? false : true;
		return $is_paid;
	}

	public static function get_users( $args ) {
		return new WP_User_Query( $args );
	}

	public static function get_students( $course_id, $per_page = 0, $offset = 0 ) {
		global $wpdb;

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $course_id;
		}

		$args = array(
			'meta_key' => 'last_name',
			'orderby' => 'meta_value',
			'meta_query' => array(
				array(
					'key' => $course_meta_key,
					'compare' => 'EXISTS',
				),
			),
		);

		if ( $per_page > 0 ) {
			$args['number'] = $per_page;
			$args['offset'] = $offset;
		}

		$students = self::get_users( $args );

		return $students->get_results();
	}

	public static function get_student_ids( $course_id, $count = false ) {
		global $wpdb;

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $course_id;
		}

		$students = self::get_users(
			array(
				'meta_key' => 'last_name',
				'orderby' => 'meta_value',
				'meta_query' => array(
					array(
						'key' => $course_meta_key,
						'compare' => 'EXISTS',
					),
				),
				'fields' => 'ID',
			)
		);

		if ( ! $count ) {
			return $students->get_results();
		} else {
			return $students->get_total();
		}
	}

	public static function count_students( $course_id ) {
		$count = self::get_student_ids( $course_id, true );
		return empty( $count ) ? 0 : $count;
	}

	public static function student_enrolled( $student_id, $course_id ) {
		global $wpdb;

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $course_id;
		}
		$enrolled = get_user_option( $course_meta_key, $student_id );

		return ! empty( $enrolled ) ? $enrolled : '';
	}

	public static function student_completed( $student_id, $course_id ) {
		// COMPLETION LOGIC
		return false;
	}

	public static function enroll_student( $student_id, $course_id, $class = '', $group = '' ) {
		$current_time = current_time( 'mysql' );

		$global_option = ! is_multisite();

		// If student doesn't exist, exit.
		$student = get_userdata( $student_id );
		if ( empty( $student ) ) {
			return false;
		}

		// If student is already enrolled, exit.
		$enrolled = self::student_enrolled( $student_id, $course_id );
		if ( ! empty( $enrolled ) ) {
			return $course_id;
		}

		/**
		 * Update metadata with relevant details.
		 *
		 * Link courses and student (in order to avoid custom tables) for
		 * easy MySql queries (get courses stats, student courses, etc.)
		 */
		update_user_option(
			$student_id,
			'enrolled_course_date_' . $course_id,
			$current_time,
			$global_option
		);
		update_user_option(
			$student_id,
			'enrolled_course_class_' . $course_id,
			$class,
			$global_option
		);
		update_user_option(
			$student_id,
			'enrolled_course_group_' . $course_id,
			$group,
			$global_option
		);
		update_user_option(
			$student_id,
			'role',
			'student',
			$global_option
		);

		self::$email_type = CoursePress_Helper_Email::ENROLLMENT_CONFIRM;

		$email_args = array();
		$email_args['course_id'] = $course_id;
		$email_args['email'] = sanitize_email( $student->user_email );
		$email_args['first_name'] = $student->user_firstname;
		$email_args['last_name'] = $student->user_lastname;

		if ( is_email( $email_args['email'] ) ) {
			$sent = CoursePress_Helper_Email::send_email(
				self::$email_type,
				$email_args
			);

			if ( $sent ) {
				// Could add something on successful email
			} else {
				// Could add something if email fails
			}
		}

		/**
		 * Setup actions for when a student enrolls.
		 * Can be used to create notifications or tracking student actions.
		 */
		$instructors = self::get_setting( $course_id, 'instructors', false );

		do_action(
			'student_enrolled_instructor_notification',
			$student_id,
			$course_id,
			$instructors
		);
		do_action(
			'student_enrolled_student_notification',
			$student_id,
			$course_id
		);

		/**
		 * Perform action after a Student is enrolled.
		 *
		 * @since 1.2.2
		 */
		do_action( 'coursepress_student_enrolled', $student_id, $course_id );

		return true;
	}

	public static function withdraw_student( $student_id, $course_id ) {
		$global_option = ! is_multisite();
		$current_time = current_time( 'mysql' );

		delete_user_option( $student_id, 'enrolled_course_date_' . $course_id, $global_option );
		delete_user_option( $student_id, 'enrolled_course_class_' . $course_id, $global_option );
		delete_user_option( $student_id, 'enrolled_course_group_' . $course_id, $global_option );
		delete_user_option( $student_id, 'role', $global_option );
		delete_user_option( $student_id, sprintf( 'course_%d_progress', $course_id ), $global_option );

		update_user_option( $student_id, 'withdrawn_course_date_' . $course_id, $current_time, $global_option );

		$instructors = self::get_setting( $course_id, 'instructors', false );
		do_action( 'student_withdraw_from_course_instructor_notification', $student_id, $course_id, $instructors );
		do_action( 'student_withdraw_from_course_student_notification', $student_id, $course_id );
		do_action( 'coursepress_student_withdrawn', $student_id, $course_id );
	}

	public static function withdraw_all_students( $course_id ) {
		$students = self::get_student_ids( $course_id );

		foreach ( $students as $student ) {
			self::withdraw_student( $student, $course_id );
		}
	}

	public static function send_invitation( $email_data ) {
		// So that we can use it later.
		CoursePress_Data_Course::set_last_course_id( (int) $email_data['course_id'] );
		$course_id = (int) $email_data['course_id'];

		$type = self::get_setting( $course_id, 'enrollment_type', 'manually' );

		// Not clear yet, why this email has 2 different types.
		// @see CoursePress_Data_Course::send_invitation()
		if ( 'passcode' == $type ) {
			$type = CoursePress_Helper_Email::COURSE_INVITATION_PASSWORD;
		} else {
			$type = CoursePress_Helper_Email::COURSE_INVITATION;
		}

		self::$email_type = $type;

		$email_args['course_id'] = $email_data['course_id'];
		$email_args['email'] = sanitize_email( $email_data['email'] );

		$user = get_user_by( 'email', $email_args['email'] );
		if ( $user ) {
			$email_data['user'] = $user;
			$email_args['first_name'] = $email_data['first_name'];
			$email_args['last_name'] = $email_data['last_name'];
		}

		$sent = CoursePress_Helper_Email::send_email(
			self::$type,
			$email_args
		);

		return $sent;
	}

	public static function is_full( $course_id ) {
		$limited = cp_is_true( self::get_setting( $course_id, 'class_size' ) );

		if ( $limited ) {
			$limit = self::get_setting( $course_id, 'class_size' );
			$students = self::count_students( $course_id );

			return $limit <= $students;
		}

		return false;
	}

	public static function get_time_estimation( $course_id ) {
		$units = self::get_units_with_modules( $course_id );

		$seconds = 0;
		$minutes = 0;
		$hours = 0;

		foreach ( $units as $unit ) {
			$estimations = CoursePress_Data_Unit::get_time_estimation( $unit['unit']->ID, $units );
			$components = explode( ':', $estimations['unit']['estimation'] );

			$part = array_pop( $components );
			$seconds += ! empty( $part ) ? (int) $part : 0;
			$part = count( $components > 0 ) ? array_pop( $components ) : 0;
			$minutes += ! empty( $part ) ? (int) $part : 0;
			$part = count( $components > 0 ) ? array_pop( $components ) : 0;
			$hours += ! empty( $part ) ? (int) $part : 0;
		}

		$total_seconds = $seconds + ( $minutes * 60 ) + ( $hours * 3600 );

		$hours = floor( $total_seconds / 3600 );
		$total_seconds = $total_seconds % 3600;
		$minutes = floor( $total_seconds / 60 );
		$seconds = $total_seconds % 60;

		$estimation = sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds );

		return $estimation;
	}

	public static function get_instructors( $course_id, $objects = false ) {
		$instructors = maybe_unserialize( self::get_setting( $course_id, 'instructors', false ) );
		$instructors = empty( $instructors ) ? array() : $instructors;

		$instructor_objects = array();
		if ( ! $objects ) {
			return array_filter( $instructors );
		} else {
			foreach ( $instructors as $instructor ) {
				$instructor_id = (int) $instructor;
				if ( ! empty( $instructor_id ) ) {
					$instructor_objects[] = get_userdata( $instructor_id );
				}
			}
			return array_filter( $instructor_objects );
		}
	}

	public static function structure_visibility( $course_id ) {
		if ( empty( self::$structure_visibility ) ) {
			$units = array_filter(
				CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_visible_units',
					array()
				)
			);

			$pages = array_filter(
				CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_visible_pages',
					array()
				)
			);

			$modules = array_filter(
				CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_visible_modules',
					array()
				)
			);

			$visibility = array();

			foreach ( array_keys( $units ) as $key ) {
				$visibility[ $key ] = true;
			}

			foreach ( array_keys( $pages ) as $key ) {
				list( $unit, $page ) = explode( '_', $key );
				CoursePress_Helper_Utility::set_array_val(
					$visibility,
					$unit . '/' . $page ,
					true
				);
			}

			foreach ( array_keys( $modules ) as $key ) {
				list( $unit, $page, $module ) = explode( '_', $key );
				CoursePress_Helper_Utility::set_array_val(
					$visibility,
					$unit . '/' . $page . '/' . $module,
					true
				);
			}

			self::$structure_visibility['structure'] = $visibility;

			if ( ! empty( $units ) || ! empty( $page ) || ! empty( $modules ) ) {
				self::$structure_visibility['has_visible'] = true;
			} else {
				self::$structure_visibility['has_visible'] = false;
			}
		}

		return self::$structure_visibility;
	}

	public static function previewability( $course_id ) {
		if ( empty( self::$previewability ) ) {
			$units = array_filter(
				CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_preview_units',
					array()
				)
			);

			$pages = array_filter(
				CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_preview_pages',
					array()
				)
			);

			$modules = array_filter(
				CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_preview_modules',
					array()
				)
			);

			$preview_structure = array();

			foreach ( array_keys( $units ) as $key ) {
				$preview_structure[ $key ] = true;
			}

			foreach ( array_keys( $pages ) as $key ) {
				list( $unit, $page ) = explode( '_', $key );
				CoursePress_Helper_Utility::set_array_val(
					$preview_structure,
					$unit . '/' . $page,
					true
				);
				CoursePress_Helper_Utility::set_array_val(
					$preview_structure,
					$unit . '/unit_has_previews',
					true
				);
			}

			foreach ( array_keys( $modules ) as $key ) {
				list( $unit, $page, $module ) = explode( '_', $key );
				CoursePress_Helper_Utility::set_array_val(
					$preview_structure,
					$unit . '/' . $page . '/' . $module,
					true
				);
				CoursePress_Helper_Utility::set_array_val(
					$preview_structure,
					$unit . '/' . $page . '/page_has_previews',
					true
				);
				CoursePress_Helper_Utility::set_array_val(
					$preview_structure,
					$unit . '/unit_has_previews',
					true
				);
			}

			self::$previewability['structure'] = $preview_structure;

			if ( ! empty( $units ) || ! empty( $page ) || ! empty( $modules ) ) {
				self::$previewability['has_previews'] = true;
			} else {
				self::$previewability['has_previews'] = false;
			}
		}

		return self::$previewability;
	}

	public static function can_view_page( $course_id, $unit_id, $page = 1, $student_id = false ) {
		if ( ! empty( self::$previewability ) ) {
			$preview = self::$previewability;
		} else {
			$preview = self::previewability( $course_id );
		}

		if ( false === $student_id ) {
			$student_id = get_current_user_id();
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		$can_preview_page = isset( $preview['has_previews'] ) && isset( $preview['structure'][ $unit_id ] ) && isset( $preview['structure'][ $unit_id ][ $page ] ) && ! empty( $preview['structure'][ $unit_id ][ $page ] );
		$can_preview_page = ! $can_preview_page && isset( $preview['structure'][ $unit_id ] ) && true === $preview['structure'][ $unit_id ] ? true : $can_preview_page;
		if ( ! $enrolled && ! $can_preview_page && ! $is_instructor ) {
			return false;
		}

		return true;
	}

	public static function can_view_module( $course_id, $unit_id, $module_id, $page = 1, $student_id = false ) {
		if ( ! empty( self::$previewability ) ) {
			$preview = self::$previewability;
		} else {
			$preview = self::previewability( $course_id );
		}

		if ( false === $student_id ) {
			$student_id = get_current_user_id();
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = CoursePress_Data_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		$preview_modules = isset( $preview['structure'][ $unit_id ][ $page ] ) ? array_keys( $preview['structure'][ $unit_id ][ $page ] ) : array();
		$can_preview_module = in_array( $module_id, $preview_modules ) || ( isset( $preview['structure'][ $unit_id ] ) && ! is_array( $preview['structure'][ $unit_id ] ) );

		if ( ! $enrolled && ! $can_preview_module && ! $is_instructor ) {
			return false;
		}

		return true;
	}

	public static function can_view_unit( $course_id, $unit_id, $student_id = false ) {
		if ( ! empty( self::$previewability ) ) {
			$preview = self::$previewability;
		} else {
			$preview = self::previewability( $course_id );
		}

		if ( false === $student_id ) {
			$student_id = get_current_user_id();
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		$can_preview_unit = isset( $preview['structure'][ $unit_id ] ) && isset( $preview['structure'][ $unit_id ]['unit_has_previews'] ) && $preview['structure'][ $unit_id ]['unit_has_previews'];

		if ( ! $enrolled && ! $can_preview_unit && ! $is_instructor ) {
			return false;
		}

		return true;
	}

	public static function next_accessible(
		$course_id, $unit_id, $preview, $current_module = false, $current_page = 1
	) {
		$view_mode = CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'normal' );
		$next = false;

		$student_id = get_current_user_id();
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		if ( $enrolled || $is_instructor ) {
			return true;
		}

		foreach ( $preview['structure'][ $unit_id ] as $page_number => $page ) {

			if ( 0 == $page_number || $page_number < $current_page ) {
				continue;
			}

			if ( is_array( $preview['structure'][ $unit_id ][ $page_number ] ) && $preview['structure'][ $unit_id ][ $page_number ]['page_has_previews'] ) {
				unset( $preview['structure'][ $unit_id ][ $page_number ]['page_has_previews'] );
				$modules = array_keys( $preview['structure'][ $unit_id ][ $page_number ] );
				$index = false !== $current_module ? array_search( $current_module, $modules ) : 0;
				$modules = false !== $current_module ? array_slice( $modules, $index + 1 ) : $modules;
			}

			foreach ( $modules as $module_id ) {

				if ( false === $next ) {
					if ( 'focus' === $view_mode ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );
						if ( 'input' == $attributes['mode'] ) {
							continue;
						}
					}

					if ( CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $module_id, $current_page ) ) {
						$next = $module_id;
					}
				}
			}
		}

		return $next;
	}

	public static function previous_accessible( $course_id, $unit_id, $preview, $current_module, $current_page = 1 ) {
		$view_mode = CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'normal' );
		$prev = false;

		$student_id = get_current_user_id();
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		if ( $enrolled || $is_instructor ) {
			return true;
		}

		foreach ( $preview['structure'][ $unit_id ] as $page_number => $page ) {

			if ( 0 == $page_number || $page_number < $current_page ) {
				continue;
			}

			if ( is_array( $preview['structure'][ $unit_id ][ $page_number ] ) && $preview['structure'][ $unit_id ][ $page_number ]['page_has_previews'] ) {
				unset( $preview['structure'][ $unit_id ][ $page_number ]['page_has_previews'] );
				$modules = array_keys( $preview['structure'][ $unit_id ][ $page_number ] );
				$index = array_search( $current_module, $modules );
				$modules = array_reverse( array_splice( $modules, 0, $index ) );
			}

			foreach ( $modules as $module_id ) {
				if ( false === $prev ) {

					if ( 'focus' === $view_mode ) {
						$attributes = CoursePress_Data_Module::attributes( $module_id );
						if ( 'input' == $attributes['mode'] ) {
							continue;
						}
					}

					if ( CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $module_id, $current_page ) ) {
						$prev = $module_id;
					}
				}
			}
		}

		return $prev;
	}

	/**
	 * Return the course that is associated with current page.
	 * i.e. this function returns the course ID that is currently displayed on
	 * front end.
	 *
	 * @since  2.0.0
	 * @return int The course ID or 0 if not called inside a course/unit/module.
	 */
	public static function get_current_course_id() {
		global $wp;

		if ( empty( $wp->query_vars ) ) { return 0; }
		if ( ! is_array( $wp->query_vars ) ) { return 0; }
		if ( empty( $wp->query_vars['coursename'] ) ) { return 0; }

		$coursename = $wp->query_vars['coursename'];
		$course_id = CoursePress_Data_Course::by_name( $coursename, true );

		return (int) $course_id;
	}

	public static function by_name( $slug, $id_only ) {
		$res = false;

		// First try to fetch the course by the slug (name).
		$args = array(
			'name' => $slug,
			'post_type' => self::get_post_type_name(),
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		if ( $id_only ) { $args['fields'] = 'ids'; }

		$post = get_posts( $args );

		if ( $post ) {
			$res = $post[0];
		} elseif ( is_numeric( $slug ) ) {
			// If we did not find a course by name, try to fetch it via ID.
			$post = get_post( $slug );

			if ( self::get_post_type_name() == $post->post_type ) {
				if ( $id_only ) {
					$res = $post->ID;
				} else {
					$res = $post;
				}
			}
		}

		return $res;
	}

	/**
	 * Returns the permalink to the specified course.
	 *
	 * @since  2.0.0
	 * @param  int $course_id The course-ID.
	 * @return string The absolute URL to the main course page.
	 */
	public static function get_permalink( $course_id ) {
		$base_url = CoursePress_Core::get_slug( 'courses/', true );
		$slug = get_post_field( 'post_name', $course_id );

		return trailingslashit( $base_url . $slug );
	}

	/**
	 * Count number of courses.
	 *
	 * @since 2.0.0
	 *
	 * @return integer number of courses
	 */
	public static function count_courses() {
		return array_sum( get_object_vars( wp_count_posts( self::get_post_type_name() ) ) );
	}
}
