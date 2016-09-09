<?php

class CoursePress_Data_Course {

	private static $post_type = 'course';
	private static $post_taxonomy = 'course_category';
	private static $post_count_title_name = 'course_number_by_title';
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
					'name' => __( 'Courses', 'cp' ),
					'singular_name' => __( 'Course', 'cp' ),
					'add_new' => __( 'Create New', 'cp' ),
					'add_new_item' => __( 'Create New Course', 'cp' ),
					'edit_item' => __( 'Edit Course', 'cp' ),
					'edit' => __( 'Edit', 'cp' ),
					'new_item' => __( 'New Course', 'cp' ),
					'view_item' => __( 'View Course', 'cp' ),
					'search_items' => __( 'Search Courses', 'cp' ),
					'not_found' => __( 'No Courses Found', 'cp' ),
					'not_found_in_trash' => __( 'No Courses found in Trash', 'cp' ),
					'view' => __( 'View Course', 'cp' ),
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
						'name' => __( 'Course Categories', 'cp' ),
						'singular_name' => __( 'Course Category', 'cp' ),
						'search_items' => __( 'Search Course Categories', 'cp' ),
						'all_items' => __( 'All Course Categories', 'cp' ),
						'edit_item' => __( 'Edit Course Categories', 'cp' ),
						'update_item' => __( 'Update Course Category', 'cp' ),
						'add_new_item' => __( 'Add New Course Category', 'cp' ),
						'new_item_name' => __( 'New Course Category Name', 'cp' ),
						'menu_name' => __( 'Course Category', 'cp' ),
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
				'ca' => __( 'New Course added successfully!', 'cp' ),
				'cu' => __( 'Course updated successfully.', 'cp' ),
				'usc' => __( 'Unit status changed successfully', 'cp' ),
				'ud' => __( 'Unit deleted successfully', 'cp' ),
				'ua' => __( 'New Unit added successfully!', 'cp' ),
				'uu' => __( 'Unit updated successfully.', 'cp' ),
				'as' => __( 'Student added to the class successfully.', 'cp' ),
				'ac' => __( 'New class has been added successfully.', 'cp' ),
				'dc' => __( 'Selected class has been deleted successfully.', 'cp' ),
				'us' => __( 'Selected student has been withdrawed successfully from the course.', 'cp' ),
				'usl' => __( 'Selected students has been withdrawed successfully from the course.', 'cp' ),
				'is' => __( 'Invitation sent sucessfully.', 'cp' ),
				'ia' => __( 'Successfully added as instructor.', 'cp' ),
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

		/**
		 * update post counter for posts with the same title
		 */
		self::save_course_number( $course_id, $post_type['title'] );

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
		if ( empty( $instructor_id ) || (int) $instructor_id == 0 ) {
			return; // Bail
		}

		$instructors = self::get_setting( $course_id, 'instructors', array() );
		$instructors = empty( $instructors ) ? array() : maybe_unserialize( $instructors );
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

	}

	public static function remove_instructor( $course_id, $instructor_id ) {
		$instructors = maybe_unserialize( self::get_setting( $course_id, 'instructors', false ) );
		$global_option = ! is_multisite();

		foreach ( $instructors as $idx => $instructor ) {
			if ( (int) $instructor === $instructor_id ) {
				unset( $instructors[ $idx ] );
			}
		}

		CoursePress_Data_Instructor::removed_from_course( $instructor_id, $course_id );
		/**
		 * delete information to instructor
		 */
		delete_user_option( $instructor_id, 'course_' . $course_id, $global_option );

		self::update_setting( $course_id, 'instructors', $instructors );
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
		$old_settings = $settings;

		if ( true === $key ) {
			// Replace all settings
			$settings = $value;
		} else {
			// Replace only one setting
			CoursePress_Helper_Utility::set_array_val( $settings, $key, $value );
		}

		/**
		 * Save course settings as single post_meta to help
		 * quick manipulation to courses.
		 *
		 * @since 2.0
		 **/
		if ( is_array( $settings ) ) {

			if ( isset( $old_settings ) && is_array( $old_settings ) ) {
				foreach ( $old_settings as $old_key => $old_value ) {
					delete_post_meta( $course_id, "cp_{$old_key}" );
				}
			}

			$date_types = array(
				'course_start_date',
				'course_end_date',
				'enrollment_start_date',
				'enrollment_end_date',
			);

			$course_open_ended = ! empty( $settings['course_open_ended'] );
			$enrollment_open_ended = ! empty( $settings['enrollment_open_ended'] );

			foreach ( $settings as $meta_key => $meta_value ) {
				if ( in_array( $meta_key, $date_types ) ) {
					$meta_value = trim( $meta_value );
					$meta_value = ! empty( $meta_value ) ? self::strtotime( $meta_value ) : 0;
					$meta_value = (int) $meta_value;

					if ( ( true === $course_open_ended && 'course_end_date' == $meta_key )
						|| ( true === $enrollment_open_ended && 'enrollment_end_date' == $meta_key )
					   ) {
						$meta_value = 0;
					}
				}
				update_post_meta( $course_id, "cp_{$meta_key}", $meta_value );
			}
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
			'suppress_filters' => true,
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
		$items = array();

		// Get units
		$units = self::get_units( $course_id, $status );

		foreach ( $units as $unit ) {
			CoursePress_Helper_Utility::set_array_val( $items, $unit->ID . '/order', get_post_meta( $unit->ID, 'unit_order', true ) );
			CoursePress_Helper_Utility::set_array_val( $items, $unit->ID . '/unit', $unit );

			$page_titles = get_post_meta( $unit->ID, 'page_title', true );
			$page_description = (array) get_post_meta( $unit->ID, 'page_description', true );
			$page_feature_image = (array) get_post_meta( $unit->ID, 'page_feature_image', true );
			$show_page_title = (array) get_post_meta( $unit->ID, 'show_page_title', true );
			$page_path = $unit->ID . '/pages';

			if ( is_array( $page_titles ) ) {
				$pos = 0;
				foreach ( $page_titles as $page_id => $page_title ) {
					$page_number = str_replace( 'page_', '', $page_id );

					CoursePress_Helper_Utility::set_array_val(
						$items,
						$page_path . '/' . $page_number . '/title',
						$page_title
					);

					CoursePress_Helper_Utility::set_array_val(
						$items,
						$page_path . '/' . $page_number . '/description',
						! empty( $page_description[ $page_id ] ) ? $page_description[ $page_id ] : ''
					);
					CoursePress_Helper_Utility::set_array_val(
						$items,
						$page_path . '/' . $page_number . '/feature_image',
						! empty( $page_feature_image[ $page_id ] ) ? $page_feature_image[ $page_id ] : ''
					);
					CoursePress_Helper_Utility::set_array_val(
						$items,
						$page_path . '/' . $page_number . '/visible',
						isset( $show_page_title[ $page_number - 1 ] ) ? $show_page_title[ $page_number -1 ] : false
					);

					$modules = self::get_unit_modules( $unit->ID, $status, false, false, array( 'page' => $page_number ) );
					uasort( $modules, array( __CLASS__, 'uasort_modules' ) );

					CoursePress_Helper_Utility::set_array_val(
						$items,
						$page_path . '/' . $page_number . '/modules',
						array()
					);

					foreach ( $modules as $module ) {
						CoursePress_Helper_Utility::set_array_val(
							$items,
							$page_path . '/' . $page_number . '/modules/' . $module->ID,
							$module
						);
					}
					ksort( $items[ $unit->ID ]['pages'], SORT_NUMERIC );
				}
			}
		}

		// Fix legacy orphaned posts and page titles
		foreach ( $items as $post_id => $unit ) {
			if ( ! isset( $unit['unit'] ) ) {
				unset( $items[ $post_id ] );
			}

			// Fix broken page titles
			$page_titles = get_post_meta( $post_id, 'page_title', true );
			if ( empty( $page_titles ) && ! empty( $unit['pages'] ) ) {
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

		return $items;
	}

	//@todo:
	public static function get_units_with_modules3( $course_id, $status = array( 'publish' ) ) {
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
			if ( empty( $page_titles ) && ! empty( $unit['pages'] ) ) {
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

		// Sort modules (they use metakey "module_order" instead of "menu_order")
		foreach ( $combine as $unit_id => $unit ) {
			if ( ! is_array( $unit['pages'] ) ) {
				$unit['pages'] = array();
			}
			foreach ( $unit['pages'] as $page_num => $page ) {
				uasort( $page['modules'], array( __CLASS__, 'uasort_modules' ) );
				$combine[ $unit_id ]['pages'][ $page_num ] = $page;
			}

			if ( isset( $combine[ $unit_id ]['pages'] ) ) { ksort( $combine[ $unit_id ]['pages'], SORT_NUMERIC ); }
		}

		return $combine;
	}

	public static function uasort_modules( $a, $b ) {
		if ( $a->module_order == $b->module_order ) {
			return 0;
		} elseif ( $a->module_order > $b->module_order ) {
			return 1;
		} else {
			return -1;
		}
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
		self::$last_course_id = intval( $course_id );
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

	public static function get_students( $course_id, $per_page = 0, $offset = 0, $fields = 'all' ) {
		global $wpdb;

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $course_id;
		}

		$args = array(
			'meta_key' => $course_meta_key,
			'meta_compare' => 'EXISTS',
			'orderby' => 'nicename',
			'fields' => $fields,
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
				'meta_key' => $course_meta_key,
				'compare' => 'EXISTS',
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

		if ( empty( $student_id ) ) {
			return false;
		}

		if ( is_multisite() ) {
			$course_meta_key = $wpdb->prefix . 'enrolled_course_date_' . $course_id;
		} else {
			$course_meta_key = 'enrolled_course_date_' . $course_id;
		}
		$enrolled = get_user_option( $course_meta_key, $student_id );

		return ! empty( $enrolled ) ? $enrolled : '';
	}

	public static function enroll_student( $student_id, $course_id, $class = '', $group = '' ) {
		if ( empty( $student_id ) ) {
			return false;
		}
		global $wpdb;

		$current_time = current_time( 'mysql' );

		$global_option = ! is_multisite();

		// If student doesn't exist, exit.
		$student = get_userdata( $student_id );
		if ( empty( $student ) ) {
			return false;
		}

		// Check invitation list then remove it exist.
		$invited_students = self::get_setting( $course_id, 'invited_students', array() );
		if ( is_array( $invited_students ) && ! empty( $invited_students[ $student->user_email ] ) ) {
			unset( $invited_students[ $student->user_email ] );
			self::update_setting( $course_id, 'invited_students', $invited_students );
		}

		// If student is already enrolled, exit.
		$enrolled = self::student_enrolled( $student_id, $course_id );
		if ( ! empty( $enrolled ) ) {
			return $course_id;
		}

		/**
		 * Filter allow to stop enrolled process.
		 *
		 * Return false to stop enrolled process. See more in Woo Integration class.
		 *
		 * @since 2.0.0
		 *
		 * @param boolean $enroll_student Allow student to enroll? Default true.
		 * @param integer $student_id Student ID.
		 * @param integer $course_id Course ID.
		 */
		if ( ! apply_filters( 'coursepress_enroll_student', true, $student_id, $course_id ) ) {
			return;
		}

		/**
		 * Update metadata with relevant details.
		 *
		 * Link courses and student (in order to avoid custom tables) for
		 * easy MySql queries (get courses stats, student courses, etc.)
		 */

		$prefix = '';
		if ( is_multisite() ) {
			$prefix = $wpdb->prefix;
		}
		update_user_option(
			$student_id,
			$prefix . 'enrolled_course_date_' . $course_id,
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

		/**
		 * add student to course
		 */
		add_post_meta( $course_id, 'course_enrolled_student_id', $student_id );

		self::$email_type = CoursePress_Helper_Email::ENROLLMENT_CONFIRM;

		/**
		 * Allow others to whether or not send the notification email.
		 *
		 * @param (bool) $true			Set to false to disable notification.
		 **/
		$notify_student = apply_filters( 'coursepress_notify_student', true );

		$email_args = array();
		$email_args['course_id'] = $course_id;
		$email_args['email'] = sanitize_email( $student->user_email );
		$email_args['first_name'] = $student->user_firstname;
		$email_args['last_name'] = $student->user_lastname;

		if ( is_email( $email_args['email'] ) && $notify_student ) {
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

		// Update course count
		CoursePress_Data_Student::count_enrolled_courses_ids( $student_id, true );

		// Reset students count
		CoursePress_Data_Instructor::reset_students_count( $instructors );
		return true;
	}

	public static function withdraw_student( $student_id, $course_id ) {

		$global_option = ! is_multisite();
		$current_time = current_time( 'mysql' );
		$unit_ids = self::get_unit_ids( $course_id );
		$meta_keys = array();

		$meta_keys[] = 'enrolled_course_date_' . $course_id;
		$meta_keys[] = 'enrolled_course_class_' . $course_id;
		$meta_keys[] = 'enrolled_course_group_' . $course_id;

		$meta_keys[] = sprintf( 'course_%d_progress', $course_id );

		// Used by class-emailalert.php
		$meta_keys[] = CoursePress_Helper_EmailAlert::META_NOTICE_PREFIX . 'course_' . $course_id;
		foreach ( $unit_ids as $unit_id ) {
			$meta_keys[] = CoursePress_Helper_EmailAlert::META_NOTICE_PREFIX . 'unit_' . $unit_id;
		}

		// Delete the marked usermeta values.
		foreach ( $meta_keys as $key ) {
			delete_user_option( $student_id, $key, $global_option );
		}

		/**
		 * Check and delete certificate.
		 */
		$certificate_id = CoursePress_Data_Certificate::get_certificate_id( $student_id, $course_id );
		if ( ! empty( $certificate_id ) ) {
			CoursePress_Data_Certificate::delete_certificate( $certificate_id );
		}

		update_user_option( $student_id, 'withdrawn_course_date_' . $course_id, $current_time, $global_option );

		$instructors = self::get_setting( $course_id, 'instructors', false );
		do_action( 'student_withdraw_from_course_instructor_notification', $student_id, $course_id, $instructors );
		do_action( 'student_withdraw_from_course_student_notification', $student_id, $course_id );
		do_action( 'coursepress_student_withdrawn', $student_id, $course_id );

		// Update student course count
		$enrolled_courses = CoursePress_Data_Student::count_enrolled_courses_ids( $student_id, true );

		// Reset student's count
		CoursePress_Data_Instructor::reset_students_count( $instructors );

		if ( 0 == $enrolled_courses ) {
			delete_user_option( $student_id, 'role', $global_option );
		}
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

		/**
		 * Check the type of email to send.
		 *
		 * @type passcode 	Use for courses which require passcode to access.
		 * @type default 	Use for normal courses.
		 **/
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
		}
		$email_args['first_name'] = $email_data['first_name'];
		$email_args['last_name'] = $email_data['last_name'];

		$sent = CoursePress_Helper_Email::send_email(
			$type,
			$email_args
		);

		return $sent;
	}

	public static function is_full( $course_id ) {
		$limited = cp_is_true( self::get_setting( $course_id, 'class_limited' ) );

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
		$instructors = self::get_setting( $course_id, 'instructors', array() );
		$instructors = empty( $instructors ) ? array() : maybe_unserialize( $instructors );
		$instructors = array_filter( $instructors );

		if ( $objects ) {
			$instructors = array_map( 'get_userdata', $instructors );
			$instructors = array_filter( $instructors );
		}

		return $instructors;
	}

	/**
	 * Get Course Facilitators
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID
	 * @param array $objects Array of facilitators
	 * @return array Array of Facilitators.
	 */
	public static function get_facilitators( $course_id, $objects = false ) {
		$facilitators = CoursePress_Data_Facilitator::get_course_facilitators( $course_id );
		$facilitators = array_filter( $facilitators );
		if ( $objects ) {
			$facilitators = array_map( 'get_userdata', $facilitators );
			$facilitators = array_filter( $facilitators );
		}
		return $facilitators;
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

				// Include only pages of existing unit
				if ( in_array( $unit, array_keys( $units ) ) ) {
					CoursePress_Helper_Utility::set_array_val(
						$visibility,
						$unit . '/' . $page ,
						true
					);
				}
			}

			foreach ( array_keys( $modules ) as $key ) {
				list( $unit, $page, $module ) = explode( '_', $key );

				$is_visible = CoursePress_Helper_Utility::get_array_val(
					$visibility,
					$unit . '/' . $page
				);

				if ( $is_visible ) {
					CoursePress_Helper_Utility::set_array_val(
						$visibility,
						$unit . '/' . $page . '/' . $module,
						true
					);
				}
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
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $student_id );

		if ( ! $enrolled && ! $can_update_course ) {
			$can_preview = CoursePress_Helper_Utility::get_array_val(
				$preview,
				'structure/' . $unit_id . '/unit_has_previews'
			);

			return cp_is_true( $can_preview );
		}

		return true;

	}

	/**
	 * Return the module ID of the next available module.
	 *
	 * @since  2.0.0
	 * @param  int $course_id
	 * @param  int $unit_id
	 * @param  int $current_page
	 * @param  in  $current_module
	 * @return int ID of next available module.
	 */
	public static function get_next_accessible_module(
		$course_id, $unit_id, $current_page = 1, $current_module = 0
	) {
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$student_id = get_current_user_id();
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );
		$is_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
		$current_module_done = true;
		$current_page = (int) $current_page > 1 ? $current_page : 1;
		$next = array( 'id' => false );

		// Optionally check if current module is completed.
		if ( $is_enrolled && $current_module ) {
			$current_module_done = CoursePress_Data_Module::is_module_done_by_student(
				$current_module,
				$student_id
			);
		}

		if ( $is_enrolled && ! $is_instructor && ! $can_update_course ) {
			if ( $current_module && ! $current_module_done ) {
				// Student did not complete the current module. Do not allow to
				// navigate to next page.
				$next['not_done'] = true;
				return $next;
			}
		}

		$nav_sequence = self::get_course_navigation_items( $course_id );

		// Remove "prev" items from the nav-sequence
		$new_sequence = array();
		$valid = false;
		foreach ( $nav_sequence as $ind => $item ) {
			if ( $valid ) {
				$new_sequence[] = $item;
			}

			if ( $unit_id == $item['unit'] ) {
				if ( $current_page == $item['id'] ) {
					$valid = true;
				}
			}
		}

		if ( $current_module > 0 ) {
			$valid = false;
			$new_sequence2 = array();

			foreach ( $new_sequence as $ind => $item ) {
				if ( $valid ) {
					$new_sequence2[] = $item;
				}

				if ( $item['id'] == $current_module ) {
					$valid = true;
				}
			}

			$new_sequence = $new_sequence2;
		}

		$nav_sequence = $new_sequence;

		// Return the next item in the navigation sequence.
		if ( count( $nav_sequence ) > 0 ) {
			$next = $nav_sequence[0];
		}

		return $next;
	}

	/**
	 * Return the module ID of the previous available module.
	 *
	 * @since  2.0.0
	 * @param  int $course_id
	 * @param  int $unit_id
	 * @param  int $current_page
	 * @param  int $current_module
	 * @return int ID of next available module.
	 */
	public static function get_prev_accessible_module(
		$course_id, $unit_id, $current_page = 1, $current_module = 0
	) {
		$nav_sequence = self::get_course_navigation_items( $course_id );
		$current_index = self::_get_current_index( $nav_sequence, $unit_id, $current_page, $current_module );

		/**
		 * Check and remove units, sections, or modules that are not yet accessible.
		 **/
		$has_required = false;
		$prev_unit_id = 0;
		$new_sequence = array();
		$valid = true;

		foreach ( $nav_sequence as $item ) {
			if ( 'completion_page' === $item['id'] ) {
				continue;
			}

			if ( $valid ) {
				$new_sequence[] = $item;
			}

			if ( $current_module ) {
				if ( $current_module == $item['id'] ) {
					$valid = false;
				}
			} else {
				if ( $unit_id == $item['unit'] && $current_page == $item['id'] ) {
					$valid = false;
				}
			}
			/*
			if ( ! $has_required && empty( $item['restricted'] ) ) {
				$new_sequence[] = $item;
			}

			if ( 'module' == $item['type'] ) {
				$is_done = CoursePress_Data_Module::is_module_done_by_student( $item['id'], 0 );

				if ( ! $is_done ) {
					$has_required = true;
				}
			}
			*/
		}
		$nav_sequence = $new_sequence;

		if ( 1 > $current_index || $current_index > count( $nav_sequence ) ) {
			//return array( 'id' => false );
			$current_index = count( $nav_sequence );
		}

		return $nav_sequence[ $current_index - 1 ];
	}

	/**
	 * Returns a flat, ordered array of all navigation items in the course.
	 *
	 * i.e. list of units / sections / modules in the correct sequene for the
	 * next/prev navigation.
	 *
	 * @since  2.0.0
	 * @param  int  $course_id The course.
	 * @param  bool $for_preview If true then only return previewable items.
	 * @return array Ordered list of navigation points.
	 */
	public static function get_course_navigation_items( $course_id ) {
		static $Items = array();

		if ( ! isset( $Items[ $course_id ] ) ) {
			$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
			$student_id = get_current_user_id();
			$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
			$is_instructor = in_array( $student_id, $instructors );
			$is_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
			$has_full_access = false;
			$is_student = false;

			// 1. Find out if we need to return preview items or full item list.
			//
			if ( $can_update_course ) {
				// User is admin or instructor, he can access all modules.
				$has_full_access = true;
			} elseif ( $is_instructor ) {
				// User is instructor, he can access all modules.
				$has_full_access = true;
			} elseif ( $is_enrolled ) {
				// User is enrolled to the course, allow access to all modules.
				$has_full_access = true;
				$is_student = true;
			}

			// 2. Generate the list of navigation items.
			//
			$items = array();

			$course_slug = get_post_field( 'post_name', $course_id );
			$course_link = sprintf(
				'%s%s',
				CoursePress_Core::get_slug( 'courses/', true ),
				$course_slug
			);

			// First node always is the course overview (clicking prev on first page).
			$items[] = array(
				'id' => $course_id,
				'type' => 'course',
				'section' => 0,
				'unit' => 0,
				'url' => trailingslashit( $course_link ),
			);

			if ( $has_full_access ) {
				$statuses = $can_update_course ? array( 'publish', 'private', 'draft' ) : array( 'publish' );
				$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $statuses );
				$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );
				$prev_unit_id = false;
				$unit_restricted = false;

				// Get a full list of all modules in the course.
				foreach ( $units as $unit_id => $unit ) {

					if ( $is_student ) {
						// For students we observe the available-date options.
						// Note: If not a student, the user is admin/instructor.
						$is_available = CoursePress_Data_Unit::is_unit_available(
							$course_id,
							$unit_id,
							$prev_unit_id
						);

						$prev_unit_id = $unit_id;

						if ( ! $is_available && ! $unit_restricted ) {
							$is_available = true;
							//$unit_restricted = true;
						}

						if ( ! $is_available ) { continue; }
					}

					$unit_link = sprintf(
						'%s/%s%s',
						$course_link,
						CoursePress_Core::get_slug( 'unit/' ),
						$unit['unit']->post_name
					);

					if ( empty( $unit['pages'] ) ) {
						$unit['pages'] = array();
					}

					foreach ( $unit['pages'] as $page_id => $page ) {
						$page_link = sprintf( '%s/page/%s', $unit_link, $page_id );

						$items[] = array(
							'id' => $page_id,
							'type' => 'section',
							'unit' => $unit_id,
							'url' => $page_link,
							'restricted' => $unit_restricted,
						);

						foreach ( $page['modules'] as $module_id => $module ) {
							$module_link = sprintf( '%s#module-%s', $page_link, $module_id );

							$items[] = array(
								'id' => $module_id,
								'type' => 'module',
								'section' => $page_id,
								'unit' => $unit_id,
								'url' => $module_link,
								'restricted' => $unit_restricted,
							);
						}
					}
				}

				$completion_page = array(
					'id' => 'completion_page',
					'type' => 'section',
					'section' => null,
					'unit' => true,
					'url' => CoursePress_Core::get_slug( 'courses/', true ) . $course_slug . '/' . CoursePress_Core::get_slug( 'completion' ),
				);
				array_push( $items, $completion_page );
			} else {
				// Get a list of all previewable modules in the course.
				$preview_course = CoursePress_Data_Course::get_setting(
					$course_id,
					'structure_preview_modules',
					array()
				);

				foreach ( $preview_course as $key => $flag ) {
					if ( empty( $flag ) ) { continue; }
					list( $unit, $page, $module ) = explode( '_', $key );

					$items[] = array(
						'id' => $module,
						'type' => 'module',
						'section' => $section,
						'unit' => $unit,
					);
				}
			}

			$Items[ $course_id ] = $items;
		}

		return $Items[ $course_id ];
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

	public static function get_course( $course_id = 0 ) {
		$course_id = ! $course_id ? get_the_ID() : $course_id;
		$course = get_post( $course_id );

		// Set duration
		$date_format = get_option( 'date_format' );
		$start_date = self::get_setting( $course_id, 'course_start_date' );
		$end_date = self::get_setting( $course_id, 'course_end_date' );
		$duration = ceil( ( CoursePress_Data_Course::strtotime( $end_date ) - CoursePress_Data_Course::strtotime( $start_date ) ) / 86400 );

		$course->start_date = date_i18n( $date_format, CoursePress_Data_Course::strtotime( $start_date ) );
		$course->end_date = $duration > 0 ? date_i18n( $date_format, CoursePress_Data_Course::strtotime( $end_date ) ) : '--';
		$course->duration = $duration > 0 ? sprintf( _n( '%s Day', '%s Days', $duration, 'cp' ), $duration ) : __( 'Open-ended', 'cp' );

		// Links
		$course->permalink = get_permalink( $course_id );
		$course->edit_link = add_query_arg(
			array(
				'page' => CoursePress_View_Admin_Course_Edit::$slug,
				'id' => $course_id,
				'action' => 'edit',
			),
			admin_url( 'admin.php' )
		);

		$course = apply_filters( 'coursepress_get_course', $course, $course_id );

		return $course;
	}

	/**
	 * duplciate course
	 *
	 * @since 1.0.0
	 *
	 * @param array $data
	 *
	 */
	static public function duplicate_course( $data ) {
		$course_id = (int) $data->data->course_id;

		$the_course = get_post( $course_id );

		if ( empty( $the_course ) ) {
			return array();
		}

		$the_course = CoursePress_Helper_Utility::object_to_array( $the_course );
		$the_course['post_author'] = get_current_user_id();
		$the_course['comment_count'] = 0;
		if ( apply_filters( 'coursepress_course_duplicated_add_copy', true ) ) {
			$the_course['post_title'] = sprintf(
				_x( '%s Copy', 'Default title for a duplicated course. Variable is original title.', 'cp' ),
				$the_course['post_title']
			);
		}
		$the_course['post_status'] = 'draft';
		unset( $the_course['ID'] );
		unset( $the_course['post_date'] );
		unset( $the_course['post_date_gmt'] );
		unset( $the_course['post_name'] );
		unset( $the_course['post_modified'] );
		unset( $the_course['post_modified_gmt'] );
		unset( $the_course['guid'] );

		$new_course_id = wp_insert_post( $the_course );

		/**
		 * update post counter for posts with the same title
		 */
		self::save_course_number( $new_course_id, $the_course['post_title'] );

		$course_meta = get_post_meta( $course_id );
		foreach ( $course_meta as $key => $value ) {
			/**
			 * do not copy students to new course
			 */
			if ( 'course_enrolled_student_id' == $key ) {
				continue;
			}
			if ( ! preg_match( '/^_/', $key ) ) {
				foreach ( $value as $key_value ) {
					add_post_meta( $new_course_id, $key, maybe_unserialize( $key_value ), true );
				}
			}
		}

		$visible_units = self::get_setting( $course_id, 'structure_visible_units', array() );
		$preview_units = self::get_setting( $course_id, 'structure_preview_units', array() );
		$visible_pages = self::get_setting( $course_id, 'structure_visible_pages', array() );
		$preview_pages = self::get_setting( $course_id, 'structure_preview_pages', array() );
		$visible_modules = self::get_setting( $course_id, 'structure_visible_modules', array() );
		$preview_modules = self::get_setting( $course_id, 'structure_preview_modules', array() );

		$instructors = (array) self::get_setting( $course_id, 'instructors', array() );
		$instructors = array_filter( $instructors );

		if ( ! empty( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				self::remove_instructor( $new_course_id, $instructor );
				self::add_instructor( $new_course_id, $instructor );
			}
		}

		$course_data = CoursePress_Helper_Utility::object_to_array( CoursePress_Data_Course::get_units_with_modules( $course_id, array(
			'publish',
			'draft',
		) ) );
		$course_data = CoursePress_Helper_Utility::sort_on_key( $course_data, 'order' );

		foreach ( $course_data as $unit_id => $unit_schema ) {

			$unit = $unit_schema['unit'];
			// Set Fields
			$unit['post_author'] = get_current_user_id();
			$unit['post_parent'] = $new_course_id;
			$unit['comment_count'] = 0;

			unset( $unit['ID'] );
			unset( $unit['post_date'] );
			unset( $unit['post_date_gmt'] );
			unset( $unit['post_name'] );
			unset( $unit['post_modified'] );
			unset( $unit['post_modified_gmt'] );
			unset( $unit['guid'] );

			$new_unit_id = wp_insert_post( $unit );
			$unit_meta = get_post_meta( $unit_id );
			foreach ( $unit_meta as $key => $value ) {
				if ( ! preg_match( '/^_/', $key ) ) {
					$success = add_post_meta( $new_unit_id, $key, maybe_unserialize( $value[0] ), true );
					if ( ! $success ) {
						update_post_meta( $new_unit_id, $key, maybe_unserialize( $value[0] ) );
					}
				}
			}

			// Update visible units
			if ( isset( $visible_units[ $unit_id ] ) ) {
				$visible_units[ $new_unit_id ] = $visible_units[ $unit_id ];
				unset( $visible_units[ $unit_id ] );
			}
			if ( isset( $preview_units[ $unit_id ] ) ) {
				$preview_units[ $new_unit_id ] = $preview_units[ $unit_id ];
				unset( $preview_units[ $unit_id ] );
			}

			$pages = isset( $unit_schema['pages'] ) ? $unit_schema['pages'] : array();
			foreach ( $pages as $page_number => $page ) {
				// Update visible pages
				$old_page_key = $unit_id . '_' . $page_number;
				$new_page_key = $new_unit_id . '_' . $page_number;

				if ( isset( $visible_pages[ $old_page_key ] ) ) {
					$visible_pages[ $new_page_key ] = $visible_pages[ $old_page_key ];
					unset( $visible_pages[ $old_page_key ] );
				}
				if ( isset( $preview_pages[ $old_page_key ] ) ) {
					$preview_pages[ $new_page_key ] = $preview_pages[ $old_page_key ];
					unset( $preview_pages[ $old_page_key ] );
				}

				$modules = $page['modules'];
				foreach ( $modules as $module_id => $module ) {

					$module['post_author'] = get_current_user_id();
					$module['post_parent'] = $new_unit_id;
					$module['comment_count'] = 0;
					unset( $module['ID'] );
					unset( $module['post_date'] );
					unset( $module['post_date_gmt'] );
					unset( $module['post_name'] );
					unset( $module['post_modified'] );
					unset( $module['post_modified_gmt'] );
					unset( $module['guid'] );

					$new_module_id = wp_insert_post( $module );

					$module_meta = get_post_meta( $module_id );
					foreach ( $module_meta as $key => $value ) {
						if ( ! preg_match( '/^_/', $key ) ) {
							update_post_meta( $new_module_id, $key, maybe_unserialize( $value[0] ) );
						}
					}
					// Update visible module
					$old_module_key = $unit_id . '_' . $page_number . '_' . $module_id;
					$new_module_key = $new_unit_id . '_' . $page_number . '_' . $new_module_id;

					if ( isset( $visible_modules[ $old_module_key ] ) ) {
						$visible_modules[ $new_module_key ] = $visible_modules[ $old_module_key ];
						unset( $visible_modules[ $old_module_key ] );
					}
					if ( isset( $preview_modules[ $old_module_key ] ) ) {
						$preview_modules[ $new_module_key ] = $preview_modules[ $old_module_key ];
						unset( $preview_modules[ $old_module_key ] );
					}
				}
			}
		}

		// Update course meta
		self::update_setting( $new_course_id, 'structure_visible_units', $visible_units );
		self::update_setting( $new_course_id, 'structure_preview_units', $preview_units );
		self::update_setting( $new_course_id, 'structure_visible_pages', $visible_pages );
		self::update_setting( $new_course_id, 'structure_preview_pages', $preview_pages );
		self::update_setting( $new_course_id, 'structure_visible_modules', $visible_modules );
		self::update_setting( $new_course_id, 'structure_preview_modules', $preview_modules );

		$json_data['course_id'] = $new_course_id;
		$json_data['data'] = $data->data;
		$json_data['nonce'] = wp_create_nonce( 'duplicate_course' );
		$json_data['success'] = true;
		$json_data['action'] = 'duplicate_course';

		do_action( 'coursepress_course_duplicated', $new_course_id, $course_id );

		return $json_data;

	}

	public static function get_course_url( $course_id ) {
		$course = get_post( $course_id );

		if ( $course ) {
			// Check if current course is not yet live.
			if ( 'publish' != $course->post_status ) {
				return CoursePress_Core::get_slug( 'courses', true ) . $course->post_name . '/';
			} else {
				return get_permalink( $course_id );
			}
		}
	}

	public static function is_course_preview( $course_id ) {
		$post_status = get_post_status( $course_id );
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
	}

	/**
	 * Get the current time in GMT timezone.
	 *
	 * Use as single current time source.
	 *
	 * @since  2.0.0
	 * @return int The current GMT timestamp.
	 **/
	public static function time_now() {
		$now = current_time( 'timestamp', 1 );

		return $now;
	}

	/**
	 * Change string date into numeric timestamp in GMT timezone.
	 *
	 * @since 2.0
	 *
	 * @param  string $date_string A formatted date string.
	 * @return int|0 Timestamp in GMT timezone.
	 **/
	public static function strtotime( $date_string ) {
		$timestamp = 0;

		if ( is_numeric( $date_string ) ) {
			// Apparently we got a timestamp already. Simply return it.
			$timestamp = (int) $date_string;
		} elseif ( $date_string ) {
			/*
			 * Convert the date-string into a timestamp; PHP assumes that the
			 * date string is in servers default timezone.
			 * We assume that date string is in "yyyy-mm-dd" format, not a
			 * relative date and also without timezone suffix.
			 */
			$timestamp = strtotime( $date_string . ' UTC' );
		}

		return $timestamp;
	}

	/**
	 * Check course availability status.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id	The ID of the course being checked.
	 * @return (bool) Return true if course is avaiable or false.
	 **/
	public static function is_course_available( $course_id, $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$course_id = ! $course_id ? get_the_ID() : $course_id;
		$course = get_post( $course_id );

		$now = self::time_now();
		$is_open_ended = self::get_setting( $course_id, 'course_open_ended' );
		$start_date = self::get_setting( $course_id, 'course_start_date' );
		$start_date = self::strtotime( $start_date );

		$is_available = empty( $start_date ) || $start_date < $now;

		if ( $is_available ) {
			// Check end-date
			$end_date = self::get_setting( $course_id, 'course_end_date' );
			$end_date = self::strtotime( $end_date );

			if ( ! $is_open_ended && ! empty( $end_date ) ) {
				$is_available = $end_date > $now;

				if ( false === $is_available ) {
					// Check if student is currently enrolled
					$is_student = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );

					if ( $is_student ) {
						// Keep the course open for students
						$is_available = true;
					}
				}
			}
		}

		// Check for enrollment dates if current student is not enrolled.
		$is_student = CoursePress_Data_Student::is_enrolled_in_course( $student_id, $course_id );
		$enrollment_open = self::get_setting( $course_id, 'enrollment_open_ended' );

		if ( ! cp_is_true( $enrollment_open ) && ! cp_is_true( $is_student ) ) {
			$enrollment_start_date = self::get_setting( $course_id, 'enrollment_start_date' );
			$enrollment_start_date = self::strtotime( $enrollment_start_date );

			if ( ! empty( $enrollment_start_date ) ) {
				$is_available = $now > $enrollment_start_date;

				// Check if enrollment is closed.
				$enrollment_end_date = self::get_setting( $course_id, 'enrollment_end_date' );
				$enrollment_end_date = self::strtotime( $enrollment_end_date );

				if ( ! empty( $enrollment_end_date ) ) {
					$is_available = $enrollment_end_date > $now;
				}
			}
		}

		return cp_is_true( $is_available );
	}

	public static function reorder_modules( $results ) {
		$posts = array();

		if ( is_array( $results ) ) {
			foreach ( $results as $post ) {
				$post_id = is_object( $post ) ? $post->ID : $post;
				$module_order = (int) get_post_meta( $post_id, 'module_order', true );
				if ( isset( $posts[ $module_order ] ) ) {
					$module_order++;
				}
				$posts[ $module_order ] = $post;
			}
		}
		ksort( $posts );

		return $posts;
	}

	public static function get_course_availability_status( $course_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$is_course_available = self::is_course_available( $course_id );
		$date_format = get_option( 'date_format' );
		$now = self::time_now();
		$status = '';

		if ( ! $is_course_available ) {
			$start_date = self::get_setting( $course_id, 'course_start_date' );
			$start_date = self::strtotime( $start_date );

			if ( $start_date > $now ) {
				$status = sprintf( __( 'This course will open on %s', 'cp' ), date_i18n( $date_format, $start_date ) );
			} else {
				// Check if it has end date
				$is_open_ended = self::get_setting( $course_id, 'course_open_ended' );
				$end_date = self::get_setting( $course_id, 'course_end_date' );
				$status = $end_date;

				if ( ! $is_open_ended && ! empty( $end_date ) ) {
					$end_date = self::strtotime( $end_date );

					if ( $end_date < $now ) {
						$status = __( 'This course is already closed.', 'cp' );
					}
				}
			}
		}

		if ( ! empty( $status ) ) {
			/**
			 * Filter status messages.
			 *
			 * @since 2.0
			 *
			 * @param (string) $status		The status message.
			 * @param (int) $course_id
			 **/
			$status = apply_filters( 'coursepress_course_availability_status', $status, $course_id );
		}

		return $status;
	}

	/**
	 * Check if current course, unit, or module is accessable
	 *
	 * @since 2.0
	 **/
	public static function can_access( $course_id, $unit_id = 0, $module_id = 0, $student_id = 0, $page = 1, $type ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$error_message = '';
		$date_format = get_option( 'date_format' );
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$page = ! $page ? 1 : $page;

		// If administrator or instructor, bail
		if ( $can_update_course ) {
			return '';
		}

		// Check if the course is already available
		$error_message = self::get_course_availability_status( $course_id );

		if ( empty( $error_message ) ) {
			if ( ! empty( $unit_id ) ) {
				$previous_unit_id = CoursePress_Data_Unit::get_previous_unit_id( $course_id, $unit_id );
				$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $unit_id, $previous_unit_id );

				if ( ! $is_unit_available ) {
					$unit_availability_date = CoursePress_Data_Unit::get_unit_availability_date( $unit_id, $course_id );

					if ( ! empty( $unit_availability_date ) ) {
						$error_message = sprintf( __( 'This unit will be available on %s', 'cp' ), date_i18n( $date_format, self::strtotime( $unit_availability_date ) ) );
					} else {
						if ( $previous_unit_id > 0 ) {
							$shortcode = sprintf( '[module_status unit_id="%s" previous_unit="%s"]', $unit_id, $previous_unit_id );
							$error_message = strip_tags( do_shortcode( $shortcode ) );
						}
					}
				}

				$validate = false;
				$has_answerable = false;
				$previous_modules = array();

				if ( empty( $error_message ) && ! empty( $previous_unit_id ) ) {
					$previous_modules = self::get_unit_modules(
						$previous_unit_id,
						array( 'publish' )
					);
					$previous_modules = array_map( array( __CLASS__, 'get_course_id' ), $previous_modules );

					if ( $previous_modules ) {
						foreach ( $previous_modules as $prev_module_index => $_module_id ) {
							$is_done = CoursePress_Data_Module::is_module_done_by_student( $_module_id, $student_id );

							if ( ! $is_done ) {
								$first_line = __( 'You need to complete all the REQUIRED modules before this unit.', 'cp' );
								$error_message = CoursePress_Helper_UI::get_message_required_modules( $first_line );
								continue;
							}
						}
					}
				}

				if ( empty( $error_message ) && $page > 1 ) {
					// Get previous modules
					$previous_modules = array();

					for ( $i = 1; $i < $page; $i++ ) {
						$prev_section = self::get_unit_modules(
							$unit_id,
							array( 'publish' ),
							false,
							false,
							array(
								'page' => $i,
							)
						);
						$previous_modules = array_merge( $previous_modules, $prev_section );
					}
					$previous_modules = array_map( array( __CLASS__, 'get_course_id' ), $previous_modules );

					foreach ( $previous_modules as $prev_module_index => $_module_id ) {
						$is_done = CoursePress_Data_Module::is_module_done_by_student( $_module_id, $student_id );

						if ( ! $is_done ) {
							$first_line = __( 'You need to complete all the REQUIRED modules before this section.', 'cp' );
							$error_message = CoursePress_Helper_UI::get_message_required_modules( $first_line );
							continue;
						}
					}
				}

				$modules = self::get_unit_modules( $unit_id, array( 'publish' ), false, false, array( 'page' => (int) $page ) );
				$modules = array_map( array( __CLASS__, 'get_course_id' ), $modules );
				$modules = self::reorder_modules( $modules );

				$module_index = 0;

				foreach ( $modules as $index => $_module_id ) {
					if ( $module_id == $_module_id ) {
						$module_index = $index;
					}
				}

				if ( $module_index > 0 ) {
					$modules = array_slice( $modules, 0, $module_index );

					// Remove the last module
					array_pop( $modules );
				} else {
					$modules = array();
				}

				if ( count( $modules ) ) {
					foreach ( $modules as $module_index => $_module_id ) {
						$is_done = CoursePress_Data_Module::is_module_done_by_student( $_module_id, $student_id );
						$title = get_the_title( $_module_id );

						if ( ! $is_done ) {
							$first_line = __( 'You need to complete all the REQUIRED modules before this module.', 'cp' );
							$error_message = CoursePress_Helper_UI::get_message_required_modules( $first_line );
							continue;
						} else {
							/**
							 * Check current student pass the minimum grade requirement.
							 **/
							$attributes = CoursePress_Data_Module::attributes( $_module_id );
							$is_assessable = $attributes['assessable'];
							$is_required = $attributes['mandatory'];
							$module_type = $attributes['module_type'];

							if ( cp_is_true( $is_assessable ) && cp_is_true( $is_required ) ) {
								$minimum_grade = $attributes['minimum_grade'];
								$grades = CoursePress_Data_Student::get_grade( $student_id, $course_id, $unit_id, $_module_id );
								$grade = CoursePress_Helper_Utility::get_array_val( $grades, 'grade' );
								$pass = (int) $grade >= (int) $minimum_grade;
								$excluded_modules = array(
									'input-textarea',
									'input-text',
								);

								if ( ! $pass && ! in_array( $module_type, $excluded_modules ) ) {
									$first_line = __( 'You need to complete all the REQUIRED modules before this module.', 'cp' );
									$error_message = CoursePress_Helper_UI::get_message_required_modules( $first_line );
									continue;
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Filter the error message to show
		 *
		 * @since 2.0
		 *
		 * @param (string) $error_message
		 * @param (int) $course_id
		 * @param (int) $unit_id
		 * @param (int) $module_id
		 **/
		$error_message = apply_filters( 'coursepress_inaccessable_error_message', $error_message, $course_id, $unit_id, $module_id );

		return $error_message;
	}

	/**
	 * Helper function to get IDs
	 **/
	public static function get_course_id( $course ) {
		if ( is_object( $course ) ) {
			return $course->ID;
		}
		return false;
	}

	/**
	 * Get courses by course ids.
	 *
	 * @since 2.0.0
	 *
	 * @param array $ids Course IDS.
	 * @return array Array of WP_Post objects.
	 */
	public static function get_courses_by_ids( $ids ) {
		$args = array(
			'post_type' => self::$post_type,
			'nopaging' => true,
			'suppress_filters' => true,
			'ignore_sticky_posts' => true,
		);
		if ( ! empty( $ids ) ) {
			$args['post__in'] = $ids;
		}
		$query = new WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Get current index in navigation units/modules list.
	 *
	 * @since 2.0.0
	 *
	 * @access private
	 *
	 * @param array $nav_sequence Whole navigation over course.
	 * @param integer $unit_id Unit ID.
	 * @param integer $current_page Currently dislayed page.
	 * @param integer $current_module Currently displayed module (0 if
	 * section)
	 * @return integer current index in $nav_sequence.
	 */
	private static function _get_current_index( $nav_sequence, $unit_id, $current_page, $current_module ) {
		foreach ( $nav_sequence as $ind => $item ) {
			switch ( $item['type'] ) {
				case 'module':
					if ( ! $current_module ) { break; }
					if ( $current_module != $item['id'] ) { break; }
					return $ind;

				case 'section':
					if ( 0 != $current_module ) { break; }
					if ( $unit_id != $item['unit'] ) { break; }
					if ( $current_page != $item['id'] ) { break; }
					return $ind;
			}
		}

		return 0;
	}

	public static function course_class( $course_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$is_course_available = self::is_course_available( $course_id );
		$date_format = get_option( 'date_format' );
		$now = self::time_now();
		$status = array( 'course-list-box' );
		$is_enrolled = false;
		$is_completed = false;
		$start_date = self::get_setting( $course_id, 'course_start_date' );
		$start_date = self::strtotime( $start_date );
		$open_ended = self::get_setting( $course_id, 'course_open_ended' );
		$end_date = self::get_setting( $course_id, 'course_end_date' );
		$end_date = self::strtotime( $end_date );
		$has_ended = false == cp_is_true( $open_ended ) && $end_date < $now;
		$course_image = CoursePress_Data_Course::get_setting( $course_id, 'listing_image' );
		$is_enrolled = false;

		if ( empty( $course_image ) ) {
			$status[] = 'no-thumb';
		}

		if ( $user_id > 0 ) {
			$is_enrolled = CoursePress_Data_Student::is_enrolled_in_course( $user_id, $course_id );
			$is_enrolled = cp_is_true( $is_enrolled );

			if ( $is_enrolled ) {
				$student_progress = CoursePress_Data_Student::get_completion_data( $user_id, $course_id );
				$is_completed = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'completion/completed'
				);
			}
		}

		if ( $is_course_available && false === $has_ended ) {
			$status[] = 'course-available';
		} else {

			if ( $start_date > $now ) {
				$status[] = 'course-starting-soon';
			} else {

				if ( $end_date > 0 && $end_date <= $now ) {
					if ( $is_enrolled && ! $is_completed ) {
						$status[] = 'course-incomplete';
					}
				}
			}
		}

		if ( $user_id > 0 ) {
			if ( cp_is_true( $is_enrolled ) ) {
				$status[] = 'student-enrolled';

				if ( $is_completed ) {
					$status[] = 'course-completed';
				}
			}
		}

		return $status;
	}

	/**
	 * Get course data and create substitutions array.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id course ID.
	 * @return array Array of substitutions.
	 */
	public static function get_vars( $course_id ) {
		$vars = array(
			'COURSE_NAME' => html_entity_decode( get_the_title( $course_id ) ),
			'UNIT_LIST' => self::get_units_html_list( $course_id ),
		);
		return $vars;
	}

	/**
	 * Retrive HTML units list.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @return string Unit list.
	 */
	public function get_units_html_list( $course_id ) {
		$units_list = '';
		$units = CoursePress_Data_Course::get_units( $course_id );

		if ( $units ) {
			$list = array();
			$previous_unit_id = null;

			foreach ( $units as $unit ) {
				$is_unit_available = CoursePress_Data_Unit::is_unit_available( $course_id, $unit->ID, $previous_unit_id );
				$previous_unit_id = $unit->ID;

				if ( $is_unit_available ) {
					$list[] = sprintf( '<li>%s</li>', $unit->post_title );
				}
			}
			$units_list = sprintf( '<ul class="course-simple-units-list">%s</ul>', implode( ' ', $list ) );
		}
		return $units_list;
	}

	/**
	 * We use custom SQL to avoid overcaps
	 * @TODO: Create and hooked into `POSTS_JOIN` as counter part to orig CP
	 **/
	public static function get_expired_courses( $per_page = 20 ) {
		global $wpdb;

		$paged = get_query_var( 'paged' );
		$paged = (int) $paged > 1 ? $paged : 1;
		$limit = $per_page;
		$offset = ($paged - 1 ) * $limit;
		$limit = $paged * $limit;

		$now = self::time_now();
		$sql = "SELECT m.`post_id`, p.`ID` FROM {$wpdb->postmeta} AS m, {$wpdb->posts} AS p
			WHERE (m.`meta_key`='cp_course_end_date' AND ( m.`meta_value` > 0 AND m.`meta_value` < %d ))
			AND ( p.ID=m.post_id AND p.post_status IN ('publish') )
			LIMIT %d, %d
		";
		$sql = $wpdb->prepare( $sql, $now, $offset, $limit );

		$course_ids = $wpdb->get_results( $sql, ARRAY_A );
		$course_ids = array_map( array( __CLASS__, 'return_id' ), $course_ids );

		return $course_ids;
	}

	/**
	 * @todo: Create and hooked into `POSTS_JOIN` as counter part to orig CP
	 **/
	public static function get_enrollment_ended_courses( $per_page = 20 ) {
		global $wpdb;

		$paged = get_query_var( 'paged' );
		$paged = (int) $paged > 1 ? $paged : 1;
		$limit = $per_page;
		$offset = ($paged - 1 ) * $limit;
		$limit = $paged * $limit;

		$now = self::time_now();
		$sql = "SELECT m.`post_id`, p.`ID` FROM {$wpdb->postmeta} AS m, {$wpdb->posts} AS p
			WHERE (m.`meta_key`='cp_enrollment_end_date' AND ( m.`meta_value` > 0 AND m.`meta_value` <= %d ))
			AND ( p.ID=m.post_id AND p.post_status IN ('publish') )
			LIMIT %d, %d
		";
		$sql = $wpdb->prepare( $sql, $now, $offset, $limit );

		$course_ids = $wpdb->get_results( $sql, ARRAY_A );
		$course_ids = array_map( array( __CLASS__, 'return_id' ), $course_ids );

		return $course_ids;
	}

	public static function return_id( $a ) {
		return $a['post_id'];
	}

	public static function current_and_upcoming_courses( $args = array(), $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$args = wp_parse_args(
			$args,
			array(
				'post_type' => self::get_post_type_name(),
				'post_status' => 'publish',
				'suppress_filters' => true,
				'meta_key' => 'cp_course_start_date',
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
				'suppress_filters' => true,
				'posts_per_page' => get_option( 'posts_per_page' ),
			)
		);

		// Get expired courses
		$expired_courses = self::get_expired_courses( $args['posts_per_page'] );
		$enrollment_ended_courses = array();

		// Get enrollment ended courses for non-admin
		$is_admin = user_can( $student_id, 'manage_options' );

		if ( false === $is_admin ) {
			$enrollment_ended_courses = self::get_enrollment_ended_courses( $args['posts_per_page'] );
			$enrolled_courses = (array) CoursePress_Data_Student::get_enrolled_courses_ids( $student_id );

			if ( ! empty( $enrollment_ended_courses ) ) {
				foreach ( $enrollment_ended_courses as $pos => $post_id ) {
					$is_instructor = CoursePress_Data_Capabilities::can_update_course( $post_id, $student_id );

					// If current student is enrolled, remove from exclusion
					if ( in_array( $post_id, $enrolled_courses ) || true === $is_instructor ) {
						unset( $enrollment_ended_courses[ $pos ] );
					}
				}
			}
		}

		$excludes = array_merge( $expired_courses, $enrollment_ended_courses );
		$excludes = array_unique( $excludes );

		if ( ! empty( $excludes ) ) {
			$args['post__not_in'] = $excludes;
		}

		$query = new WP_Query( $args );

		return $query;
	}

	public static function sort_courses( $courses ) {
		$ordered_courses = array();

		foreach ( $courses as $index => $course ) {
			$course_id = is_object( $course ) ? $course->ID : $course;
			$start_date = get_post_meta( $course_id, 'cp_course_start_date', true );
			$ordered_courses[] = $start_date;
		}
		array_multisort( $ordered_courses, $courses );

		return $courses;
	}

	public static function get_course_status( $course_id ) {
		$setting = self::get_setting( $course_id );
		$start_date = ! empty( $setting['course_start_date'] ) ? self::strtotime( $setting['course_start_date'] ) : 0;
		$end_date = ! empty( $setting['course_end_date'] ) ? self::strtotime( $setting['course_end_date'] ) : 0;
		$open_ended = ! empty( $setting['course_open_ended'] ) && $setting['course_open_ended'];
		$now = self::time_now();
		$status = 'open';

		if ( $start_date > 0 && $start_date > $now ) {
			$status = 'future';
		} elseif ( ! $open_ended && ! empty( $end_date ) && $end_date < $now ) {
			$status = 'closed';
		}

		return $status;
	}

	public static function get_enrollment_status( $course_id ) {
		$setting = self::get_setting( $course_id );
		$start_enrollment = ! empty( $setting['enrollment_start_date'] ) ? self::strtotime( $setting['enrollment_start_date'] ) : 0;
		$end_enrollment = ! empty( $setting['enrollment_end_date'] ) ? self::strtotime( $setting['enrollment_end_date'] ) : 0;
		$enrollment_open = ! empty( $setting['enrollment_open_ended'] ) && $setting['enrollment_open_ended'];
		$now = self::time_now();
		$status = 'open';

		if ( ! $enrollment_open ) {
			if ( $start_enrollment > $now ) {
				$status = 'future';
			} elseif ( $end_enrollment < $now ) {
				$status = 'closed';
			}
		}
		return $status;
	}

	/**
	 * Check post type - is it course?
	 *
	 * @since 2.0.0
	 *
	 * @param integer|WP_Post Post id or WP Post object
	 * @return boolean Is a course post
	 */
	public static function check_post_type_by_post( $post ) {
		$post_type = get_post_type( $post );
		$course_post_type = self::get_post_type_name();
		if ( $post_type == $course_post_type ) {
			return true;
		}
		return false;
	}

	/**
	 * Add custom filed with counter for posts with indetical title
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_id Post ID
	 * @param string $post_title Post title.
	 * @param array $excludes Array of excluded Post IDs
	 */
	public static function save_course_number( $post_id, $post_title, $excludes = array() ) {
		if ( ! self::check_post_type_by_post( $post_id ) ) {
			return $post_title;
		}
		global $wpdb;
		$course_post_type = self::get_post_type_name();
		$sql = $wpdb->prepare(
			"select ID from {$wpdb->posts} where post_title = ( select a.post_title from {$wpdb->posts} a where id = %d ) and post_type = %s and post_status in ( 'publish', 'draft', 'pending', 'future' ) order by id asc",
			$post_id,
			$course_post_type
		);
		$posts = $wpdb->get_results( $sql );
		$limit = 2 + count( $excludes );
		if ( count( $posts ) < $limit ) {
			delete_post_meta( $post_id, self::$post_count_title_name );
			return;
		}
		$count = 1;
		foreach ( $posts as $post ) {
			if ( ! empty( $excludes ) && in_array( $post->ID, $excludes ) ) {
				continue;
			}
			/**
			 * we need it only once
			 */
			if ( ! add_post_meta( $post->ID, self::$post_count_title_name, $count, true ) ) {
				update_post_meta( $post->ID, self::$post_count_title_name, $count );
			}
			$count++;
		}
	}

	/**
	 * Function called by filter "the_title" to add number.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_title Post title.
	 * @param integer $post_id Post ID.
	 * @return string Post title.
	 */
	public static function add_numeric_identifier_to_course_name( $post_title, $post_id ) {
		if ( ! is_admin() ) {
			return $post_title;
		}
		if ( ! self::check_post_type_by_post( $post_id ) ) {
			return $post_title;
		}
		$number = get_post_meta( $post_id, self::$post_count_title_name, true );
		if ( empty( $number ) ) {
			return $post_title;
		}
		return sprintf( '%s %d', $post_title, $number );
	}

	/**
	 * Function called on action "before_delete_post" to clear custom fileds
	 * with course number.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function delete_course_number( $post_id ) {
		if ( ! self::check_post_type_by_post( $post_id ) ) {
			return;
		}
		global $wpdb;
		$sql = $wpdb->prepare(
			"select id from {$wpdb->posts} where post_title = ( select a.post_title from {$wpdb->posts} a where a.id = %d )",
			$post_id
		);
		$results = $wpdb->get_results( $sql );
		foreach ( $results as $post ) {
			delete_post_meta( $post->id, self::$post_count_title_name );
		}
		self::save_course_number( $post_id, $post_type, array( $post_id ) );
	}
}
