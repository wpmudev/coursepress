<?php

class CoursePress_Data_Unit {

	private static $post_type = 'unit';

	/**
	 * Add hooks.
	 *
	 * @since 2.0.0
	 */
	public static function init_hooks() {
		/**
		 * Show by default new module on course list.
		 *
		 * @since 2.0.0
		 */
		add_action( 'coursepress_unit_added', array( __CLASS__, 'show_new_on_list' ), 10, 3 );
	}

	public static function get_format() {
		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Units', 'coursepress' ),
					'singular_name' => __( 'Unit', 'coursepress' ),
					'add_new' => __( 'Create New', 'coursepress' ),
					'add_new_item' => __( 'Create New Unit', 'coursepress' ),
					'edit_item' => __( 'Edit Unit', 'coursepress' ),
					'edit' => __( 'Edit', 'coursepress' ),
					'new_item' => __( 'New Unit', 'coursepress' ),
					'view_item' => __( 'View Unit', 'coursepress' ),
					'search_items' => __( 'Search Units', 'coursepress' ),
					'not_found' => __( 'No Units Found', 'coursepress' ),
					'not_found_in_trash' => __( 'No Units found in Trash', 'coursepress' ),
					'view' => __( 'View Unit', 'coursepress' ),
				),
				'public' => false,
				'show_ui' => false,
				'publicly_queryable' => false,
				'capability_type' => 'unit',
				'map_meta_cap' => true,
				'query_var' => true,
				'rewrite' => false,
			),
		);
	}

	public static function get_post_type_name() {
		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	/**
	 * Get time to end tasks.
	 */
	public static function get_time_estimation( $unit_id, $data, $default = '1:00' ) {
		$estimations = array();
		if ( ! isset( $data[ $unit_id ]['pages'] ) ) {
			$data[ $unit_id ]['pages'] = array();
		}
		$unit_seconds = 0;
		foreach ( $data[ $unit_id ]['pages'] as $page_id => $page ) {
			$page_seconds = 0;
			foreach ( $page['modules'] as $module_id => $module ) {
				$duration = CoursePress_Data_Module::get_time_estimation( $module_id, $default );
				$parts = explode( ':', $duration );
				$seconds = (int) array_pop( $parts );
				$minutes = (int) array_pop( $parts );
				$hours = 0;
				if ( ! empty( $parts ) ) {
					$hours = (int) array_pop( $parts );
				}
				$time = CoursePress_Helper_Utility::get_time( $seconds, $minutes, $hours );
				/**
				 * Increase page time
				 */
				$page_seconds += $time['total_seconds'];
			}
			/**
			 * Page time
			 */
			$time = CoursePress_Helper_Utility::get_time( $page_seconds );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/estimation', $time['time'] );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/components/hours', $time['hours'] );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/components/minutes', $time['minutes'] );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/components/seconds', $time['seconds'] );
			/**
			 * Increase unit time
			 */
			$unit_seconds += $time['total_seconds'];
		}
		/**
		 * Unit time
		 */
		$time = CoursePress_Helper_Utility::get_time( $unit_seconds );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/estimation', $time['time'] );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/components/hours', $time['hours'] );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/components/minutes', $time['minutes'] );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/components/seconds', $time['seconds'] );
		/**
		 * Allow to change duration for unit.
		 *
		 * @since 2.0.6
		 *
		 * @param array $duration Current duration array.
		 * @param integer $unit_id Unit ID.
		 */
		return apply_filters( 'coursepress_unit_get_time_estimation', $estimations, $unit_id );
	}

	static function by_name( $slug, $id_only, $post_parent = '' ) {
		$res = false;

		// First try to fetch the unit by the slug (name).
		$args = array(
			'name' => $slug,
			'post_type' => self::get_post_type_name(),
			'post_status' => 'any',
			'posts_per_page' => 1,
		);

		if ( $id_only ) { $args['fields'] = 'ids'; }
		if ( $post_parent ) { $args['post_parent'] = (int) $post_parent; }

		$post = get_posts( $args );

		if ( $post ) {
			$res = $post[0];
		} else {
			// If we did not find a unit by name, try to fetch it via ID.
			$post = get_post( $slug );
			/**
			 * Check it... is a post at all?
			 */
			if ( ! is_a( $post, 'WP_Post' ) ) {
				return $res;
			}
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

	public static function is_unit_available( $course, $unit, $previous_unit, $status = false ) {
		if ( ! $status ) {
			$status = self::get_unit_availability_status( $course, $unit, $previous_unit );
		}
		return isset( $status['available'] )? $status['available'] : false;
	}

	public static function get_page_meta( $unit_id, $item_id ) {
		if ( empty( $item_id ) || empty( $unit_id ) ) {
			return array(
				'title' => '',
				'description' => '',
				'feature_image' => '',
				'visible' => false,
			);
		}

		$unit_id = is_object( $unit_id ) ? $unit_id->ID : (int) $unit_id;

		$meta = get_post_meta( $unit_id );
		$titles = isset( $meta['page_title'] ) && ! empty( $meta['page_title'] ) ? maybe_unserialize( $meta['page_title'][0] ) : array();
		$descriptions = isset( $meta['page_description'] ) && ! empty( $meta['page_description'] ) ? maybe_unserialize( $meta['page_description'][0] ) : array();
		$images = isset( $meta['page_feature_image'] ) && ! empty( $meta['page_feature_image'] ) ? maybe_unserialize( $meta['page_feature_image'][0] ) : array();
		$visibilities = isset( $meta['show_page_title'] ) && ! empty( $meta['show_page_title'] ) ? maybe_unserialize( $meta['show_page_title'][0] ) : array();

		$return = array(
			'title' => $titles[ 'page_' . $item_id ],
			'description' => isset( $descriptions[ 'page_' . $item_id ] ) ? $descriptions[ 'page_' . $item_id ] : '',
			'feature_image' => isset( $images[ 'page_' . $item_id ] ) ? $images[ 'page_' . $item_id ] : '',
		);

		if ( isset( $visibilities[ ( $item_id - 1 ) ] ) ) {
			$return['visible'] = $visibilities[ ( $item_id - 1 ) ];
		}

		return $return;
	}

	public static function get_unit_availability_status( $course, $unit, $previous_unit = 0 ) {
		$status = array(
			'mandatory_required' => array(
				'enabled' => false,
				'result' => false,
			),
			'completion_required' => array(
				'enabled' => false,
				'result' => false,
			),
			'passed_required' => array(
				'enabled' => false,
				'result' => false,
			),
		);
		if ( ! is_object( $unit ) ) {
			$unit = get_post( $unit );
		}
		/**
		 * Check it... is a post at all?
		 */
		if ( ! is_a( $unit, 'WP_Post' ) ) {
			return $status;
		}

		$course_id = is_object( $course ) ? $course->ID : (int) $course;

		$unit_id = $unit->ID;
		$previous_unit_id = false;
		if ( $previous_unit ) {
			$previous_unit_id = is_object( $previous_unit ) ? $previous_unit->ID : (int) $previous_unit ;
		}

		$student_id = get_current_user_id();
		$is_student = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
		$due_date = self::get_unit_availability_date( $unit_id, $course_id );
		$is_available = empty( $due_date ) || ( 'expired' === $due_date && $is_student );

		/* Check if previous has conditions */
		$force_current_unit_completion = false;
		$force_current_unit_successful_completion = false;

		if ( $previous_unit_id ) {
			$force_current_unit_completion = cp_is_true(
				get_post_meta( $previous_unit_id, 'force_current_unit_completion', true )
			);
			$force_current_unit_successful_completion = cp_is_true(
				get_post_meta( $previous_unit_id, 'force_current_unit_successful_completion', true )
			);
		}

		/**
		 * If there is NO MANDATORY modules, then this parameter can not be
		 * true!
		 */
		if ( $previous_unit_id && $force_current_unit_completion ) {
			$number_of_mandatory = self::get_number_of_mandatory( $previous_unit_id );

			if ( 0 == $number_of_mandatory ) {
				$force_current_unit_completion = false;
				$force_current_unit_successful_completion = false;
			}
		}

		if ( $previous_unit_id && $is_available ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			$mandatory_done = CoursePress_Data_Student::is_mandatory_done(
				$student_id, $course_id, $previous_unit_id, $student_progress
			);

			$unit_completed = CoursePress_Data_Student::is_unit_complete(
				$student_id, $course_id, $previous_unit_id, $student_progress
			);

			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'mandatory_required/enabled', $force_current_unit_completion
			);
			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'mandatory_required/result', $mandatory_done
			);

			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'completion_required/enabled', $force_current_unit_successful_completion
			);
			$status = CoursePress_Helper_Utility::set_array_value(
				$status, 'completion_required/result', $unit_completed
			);

			if ( $status['completion_required']['enabled'] ) {
				$is_available = $status['completion_required']['result'];
			} elseif ( $status['mandatory_required']['enabled'] ) {
				$is_available = $status['mandatory_required']['result'];
			}

			/**
			 * User also needs to pass all required assessments
			 *
			 * @since 2.0.6
			 */
			if ( $is_available && $force_current_unit_successful_completion ) {
				$is_available = CoursePress_Data_Student::unit_answers_are_correct( $student_id, $course_id, $previous_unit );
				CoursePress_Helper_Utility::set_array_value( $status, 'passed_required/enabled', true );
				CoursePress_Helper_Utility::set_array_value( $status, 'passed_required/result', $is_available );
			}
		}

		/**
		 * Perform action if unit is available.
		 *
		 * @since 1.2.2
		 * */
		do_action( 'coursepress_unit_availble', $is_available, $unit_id );

		/**
		 * Return filtered value.
		 *
		 * Can be used by other plugins to filter unit availability.
		 *
		 * @since 1.2.2
		 * */
		$is_available = apply_filters(
			'coursepress_filter_unit_availability',
			$is_available,
			$unit_id
		);
		$status['available'] = $is_available;

		return $status;
	}

	/**
	 * Get the unit availability date in GMT timezone.
	 *
	 * @since 2.0
	 *
	 * @param  int    $unit_id
	 * @param  int    $course_id
	 * @param  string $date_format Optional. Define the date format of return value.
	 *                NOTE: If a date format is defined then the function will not
	 *                return an empty value for units that are available today!
	 *
	 * @return $date the unit becomes available or null. Date is in GMT timezone.
	 **/
	public static function get_unit_availability_date( $unit_id, $course_id, $date_format = null, $student_id = 0 ) {
		if ( empty( $student_id ) ) {
			$student_id = get_current_user_id();
		}

		$is_open_ended = CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended' );
		$course_start = CoursePress_Data_Course::get_setting( $course_id, 'course_start_date' );
		$course_end = CoursePress_Data_Course::get_setting( $course_id, 'course_end_date' );
		$start_date = CoursePress_Data_Course::strtotime( $course_start ); // Converts date to UTC.
		$end_date = CoursePress_Data_Course::strtotime( $course_end ); // Converts date to UTC.
		$is_open_ended = cp_is_true( $is_open_ended );

		// Use common current timestamp for CP
		$always_return_date = false; // Return empty value if unit is available!
		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
		} else {
			$always_return_date = true; // Return formatted date, even when unit is available.
		}
		$now = CoursePress_Data_Course::time_now();
		$is_available = true;
		$availability_date = '';
		$return_date = $start_date; // UTC value.

		// Check for course start/end dates.
		if ( $now < $start_date ) {
			// 1. Start date reached?
			$is_available = false;
			$availability_date = date_i18n( $date_format, $start_date );
		} elseif ( ! $is_open_ended && $now > $end_date ) {
			// 2. End date reached?
			// Check if student is currently enrolled
			$is_student = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );

			if ( $is_student ) {
				$is_available = true;
			} else {
				$is_available = false;
				$availability_date = 'expired';
			}
		}

		// Course is active today, so check for unit-specific limitations.
		$status_type = get_post_meta( $unit_id, 'unit_availability', true );

		if ( 'after_delay' == $status_type ) {
			$delay_val = get_post_meta( $unit_id, 'unit_delay_days', true );
			$delay_days = (int) $delay_val;

			if ( $delay_days > 0 ) {

				// Delay is added to the base-date. In future this could be
				// changed to enrollment date or completion of prev-unit, etc.
				$base_date = CoursePress_Data_Course::strtotime( $course_start ); // UTC value.
				$release_date = $base_date + ($delay_days * DAY_IN_SECONDS);
				$return_date = $release_date; // UTC value.

				if ( $now < $release_date ) {
					$is_available = false;
					$availability_date = date_i18n( $date_format, $release_date );
				}
			}
		} elseif ( 'on_date' == $status_type ) {
			$due_on = get_post_meta( $unit_id, 'unit_date_availability', true );
			$due_date = CoursePress_Data_Course::strtotime( $due_on ); // UTC value.
			$return_date = $due_date; // UTC value.

			// Unit-Start date reached?
			if ( $now < $due_date ) {
				$is_available = false;
				$availability_date = date_i18n( $date_format, $due_date );
			}
		}

		if ( $always_return_date ) {
			return $return_date;
		}

		return $availability_date;
	}

	public static function get_previous_unit_id( $course_id, $unit_id ) {
		$previous_unit = false;
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$status = array( 'publish' );

		if ( $can_update_course ) {
			$status[] = 'draft';
		}

		$units = CoursePress_Data_Course::get_units( $course_id, $status );
		$valid = true;

		if ( $units ) {
			foreach ( $units as $unit_index => $unit ) {
				if ( $unit->ID === $unit_id ) {
					$valid = false;
					continue;
				}
				if ( $valid ) {
					$previous_unit = $unit;
				}
			}
		}

		return $previous_unit ? $previous_unit->ID : false;
	}

	/**
	 * Returns the permalink to the specific unit.
	 *
	 * @since  2.0.0
	 * @param  int    $unit_id Unit ID.
	 * @param  string $page Optional. Page-key inside the unit.
	 * @return string The URL.
	 */
	public static function get_url( $unit_id, $page = false ) {
		$unit = get_post( $unit_id );
		/**
		 * Check it... is a post at all?
		 */
		if ( ! is_a( $unit, 'WP_Post' ) ) {
			return '';
		}
		$course_id = wp_get_post_parent_id( $unit_id );

		$unit_url = sprintf(
			'%s%s%s',
			get_permalink( $course_id ),
			CoursePress_Core::get_slug( 'unit/' ),
			$unit->post_name
		);

		if ( $page ) {
			$unit_url .= '/page/' . $page;
		}

		return trailingslashit( $unit_url );
	}

	/**
	 * Number of required modules.
	 *
	 * Return number of required modules based on unit id.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit id..
	 * @return integer Number of required modules.
	 */
	public static function get_number_of_mandatory( $unit_id ) {
	    $modules = CoursePress_Data_Course::get_unit_modules( $unit_id );
	    $found = 0;

	    if ( $modules ) {
	        foreach ( $modules as $module ) {
	            $module_id = $module->ID;
	            $attributes = CoursePress_Data_MOdule::attributes( $module_id );

	            if ( ! empty( $attributes['mandatory'] ) ) {
	                $found++;
                }
            }
        }

		return $found;
	}

	/**
	 * Get course ID by unit
	 *
	 * @since 2.0.0
	 *
	 * @param integer/WP_Post $unit unit ID or unit WP_Post object.
	 *
	 * @return integer Returns course id.
	 */
	public static function get_course_id_by_unit( $unit ) {
		$post_type = self::get_post_type_name();
		if ( ! is_object( $unit ) && preg_match( '/^\d+$/', $unit ) ) {
			$unit = get_post( $unit );
		}
		/**
		 * Check it... is a post at all?
		 */
		if ( ! is_a( $unit, 'WP_Post' ) ) {
			return 0;
		}
		if ( $unit->post_type == $post_type ) {
			return $unit->post_parent;
		}
		return 0;
	}

	/**
	 * Get instructors.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit ID or unit WP_Post object.
	 *
	 * @return array Array of instructors assigned to course.
	 */
	public static function get_instructors( $unit_id, $objects = false ) {
		$course_id = self::get_course_id_by_unit( $unit_id );
		return CoursePress_Data_Course::get_instructors( $course_id, $objects );
	}

	/**
	 * Check if unit structure is visible
	 *
	 * @since 2.0
	 * @param (int) $course_id
	 * @param (int) $unit_id
	 * @param (int) $user_id	Optional. Will use current user ID if empty.
	 * @return (bool)			Returns true if unit is visible otherwise false.
	 **/
	public static function is_unit_structure_visible( $course_id, $unit_id, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id );

		if (  $can_update_course ) {
			// Always make the unit visible to administrator, instructors and facilitators.
			return true;
		}

		$structure_visibility = CoursePress_Data_Course::structure_visibility( $course_id );

		return ! empty( $structure_visibility['structure'][ $unit_id ] );
	}

	/**
	 * Check if page is visible
	 *
	 * @since 2.0
	 * @param (int) $course_id
	 * @param (int) $unit_id
	 * @param (int) $page_number
	 * @param (int) $user_id		Optional. Will use current user ID if empty.
	 * @return (bool)				Returns true if page is set to be visible otherwise false.
	 **/
	public static function is_page_structure_visible( $course_id, $unit_id, $page_number, $user_id = 0 ) {
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id );

		if (  $can_update_course ) {
			// Always make the unit visible to administrator, instructors and facilitators.
			return true;
		}

		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );
		$page_modules = CoursePress_Helper_Utility::get_array_val(
			$visibility,
			'structure/' . $unit_id . '/' . $page_number
		);

		return ! empty( $page_modules );
	}

	/**
	 * Check if module structure is visible.
	 *
	 * @since 2.0
	 *
	 * @param (int) $course_id
	 * @param (int) $unit_id
	 * @param (int) $module_id
	 * @param (int) $user_id		Optional. Will use current user ID if empty.
	 * @return (bool)				Returns true if the module check set to be visible otherwise false.
	 **/
	public static function is_module_structure_visible( $course_id, $unit_id, $module_id, $user_id = 0 ) {
		if ( ! empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id, $user_id );

		if (  $can_update_course ) {
			// Always make the unit visible to administrator, instructors and facilitators.
			return true;
		}

		$visibility = CoursePress_Data_Course::structure_visibility( $course_id );
		if ( ! empty( $visibility['structure'][ $unit_id ] ) && is_array( $visibility['structure'][ $unit_id ] ) ) {
			$unit_modules = $visibility['structure'][ $unit_id ];

			foreach ( $unit_modules as $page_number => $modules ) {
				if ( ! empty( $modules[ $module_id ] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/*
	 * Get units ids by course ids.
	 *
	 * @since 2.0.0
	 *
	 * @param array $ids Course IDs.
	 * @return array Array of unit IDs.
	 */
	public static function get_unit_ids_by_course_ids( $ids ) {
		$args = array(
			'post_type' => self::$post_type,
			'nopaging' => true,
			'suppress_filters' => true,
			'ignore_sticky_posts' => true,
			'fields' => 'ids',
		);
		if ( ! empty( $ids ) ) {
			$args['post_parent__in'] = $ids;
		}
		$query = new WP_Query( $args );
		return $query->posts;
	}

	/**
	 * New unit will be shown on course structure list by default.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id unit ID.
	 * @param integer $course_id course ID.
	 */
	public static function show_new_on_list( $unit_id, $course_id ) {
		/**
		 * unit
		 */
		$visible_units = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_units', array() );
		$visible_units[ $unit_id ] = 1;
		CoursePress_Data_Course::update_setting( $course_id, 'structure_visible_units', $visible_units );
		self::show_page( $unit_id, 1, $course_id );
	}

	/**
	 * Made a selected page visible.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id unit ID.
	 * @param integer $page_id page ID.
	 * @param integer $course_id course ID, default false.
	 */
	public static function show_page( $unit_id, $page_id, $course_id = false ) {
		if ( empty( $course_id ) ) {
			$course_id = CoursePress_Data_Unit::get_course_id_by_unit( $unit_id );
		}
		if ( empty( $course_id ) ) {
			return;
		}
		$visible_pages = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_pages', array() );
		$id = sprintf( '%d_%d', $unit_id, $page_id );
		$visible_pages[ $id ] = 1;
		CoursePress_Data_Course::update_setting( $course_id, 'structure_visible_pages', $visible_pages );
	}

	/**
	 * Select which pages should be visible.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id unit ID.
	 * @param array $meta Meta data.
	 */
	public static function show_new_pages( $unit_id, $meta ) {
		if ( ! isset( $meta['page_title'] ) ) {
			return;
		}
		if ( ! is_array( $meta['page_title'] ) ) {
			return;
		}
		$old_pages = get_post_meta( $unit_id, 'page_title', true );
		foreach ( $meta['page_title'] as $key => $value ) {
			if ( array_key_exists( $key, $old_pages ) ) {
				continue;
			}
			$page_id = preg_replace( '/[^\d]+/', '', $key );
			self::show_page( $unit_id, $page_id );
		}
	}

	/**
	 * Generate the unit url
	 *
	 * @param (int) $unit_id
	 * @return Returns unit url structure.
	 **/
	public static function get_unit_url( $unit_id = 0 ) {
		if ( ! empty( $unit_id ) ) {
			$course_id = get_post_field( 'post_parent', $unit_id );
			$course_url = CoursePress_Data_Course::get_course_url( $course_id );
			$unit_url = CoursePress_Core::get_slug( 'units/' );
			$unit = get_post( $unit_id );
			/**
			 * Check it... is a post at all?
			 */
			if ( is_a( $unit, 'WP_Post' ) ) {
				$unit_slug = $unit->post_name;
				return $course_url . $unit_url . trailingslashit( $unit_slug );
			}
		}

		return '';
	}

	/**
	 * Check entry - is this a unit?
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post|integer|null $course Variable to check.
	 * @return boolean Answer is that course or not?
	 */
	public static function is_unit( $unit = null ) {
		$unit_id = 0;
		if ( empty( $unit ) ) {
			global $post;
			if ( ! is_object( $post ) ) {
				return false;
			}
			$unit = $post;
		}
		$post_type = get_post_type( $unit );
		if ( $post_type == self::$post_type ) {
			return true;
		}
		return false;
	}

	public static function delete_section( $unit_id, $page_number ) {
		if ( ! self::is_unit( $unit_id ) ) {
			return;
		}
		/**
		 * move module from deleted page/section to first
		 */
		CoursePress_Data_Module::move_to_first_page( $unit_id, $page_number );
		/**
		 * move modules one page down
		 */
		CoursePress_Data_Module::decrease_page_number( $unit_id, $page_number );
		/**
		 * rewrite pages/sections attributes
		 */
		$keys = array( 'page_title', 'show_page_title', 'page_description' );
		foreach ( $keys as $key ) {
			$$key = get_post_meta( $unit_id, $key, true );
		}
		$size = count( $page_title );
		for ( $i = $page_number; $i < $size; $i++ ) {
			$show_page_title[ $i - 1 ] = $show_page_title[ $i ];
			$index_new = sprintf( 'page_%d', $i );
			$index_old = sprintf( 'page_%d', $i + 1 );
			$page_title[ $index_new ] = $page_title[ $index_old ];
			$page_description[ $index_new ] = $page_description[ $index_old ];
		}
		/**
		 * save new data
		 */
		foreach ( $keys as $key ) {
			/**
			 * delete last element
			 */
			array_pop( $$key );
			update_post_meta( $unit_id, $key, $$key );
		}
	}

	/**
	 * Check free preview of unit.
	 *
	 * @since 2.0.4
	 *
	 * @param integer $unit_id unit ID.
	 * @return boolean Is free preview available for this unit?
	 */
	public static function can_be_previewed( $unit_id ) {
		$course_id = self::get_course_id_by_unit( $unit_id );
		if ( empty( $course_id ) ) {
			return false;
		}
		$preview = CoursePress_Data_Course::previewability( $course_id );
		if (
			! empty( $preview )
			&& is_array( $preview )
			&& isset( $preview['structure'] )
			&& is_array( $preview['structure'] )
			&& isset( $preview['structure'][ $unit_id ] )
			&& is_array( $preview['structure'][ $unit_id ] )
			&& isset( $preview['structure'][ $unit_id ]['unit_has_previews'] )
			&& cp_is_true( $preview['structure'][ $unit_id ]['unit_has_previews'] )
		) {
			return true;
		}
		return false;
	}
}
