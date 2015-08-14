<?php

class CoursePress_Model_Unit {

	private static $post_type = 'unit';

	public static function get_format() {

		return array(
			'post_type' => self::$post_type,
			'post_args' => array(
				'labels'             => array(
					'name'               => __( 'Units', 'cp' ),
					'singular_name'      => __( 'Unit', 'cp' ),
					'add_new'            => __( 'Create New', 'cp' ),
					'add_new_item'       => __( 'Create New Unit', 'cp' ),
					'edit_item'          => __( 'Edit Unit', 'cp' ),
					'edit'               => __( 'Edit', 'cp' ),
					'new_item'           => __( 'New Unit', 'cp' ),
					'view_item'          => __( 'View Unit', 'cp' ),
					'search_items'       => __( 'Search Units', 'cp' ),
					'not_found'          => __( 'No Units Found', 'cp' ),
					'not_found_in_trash' => __( 'No Units found in Trash', 'cp' ),
					'view'               => __( 'View Unit', 'cp' )
				),
				'public'             => false,
				'show_ui'            => false,
				'publicly_queryable' => false,
				'capability_type'    => 'unit',
				'map_meta_cap'       => true,
				'query_var'          => true,
				'rewrite'            => false
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

	public static function get_time_estimation( $unit_id, $data, $default = '1:00' ) {

		$estimations = array();

		$unit_hours   = 0;
		$unit_minutes = 0;
		$unit_seconds = 0;

		if ( ! isset( $data[ $unit_id ]['pages'] ) ) {
			$data[ $unit_id ]['pages'] = array();
		}
		foreach ( $data[ $unit_id ]['pages'] as $page_id => $page ) {

			$page_hours   = 0;
			$page_minutes = 0;
			$page_seconds = 0;

			foreach ( $page['modules'] as $module_id => $module ) {
				$duration = CoursePress_Model_Module::get_time_estimation( $module_id, $default );

				$parts   = explode( ':', $duration );
				$seconds = (int) array_pop( $parts );
				$minutes = (int) array_pop( $parts );
				if ( ! empty( $parts ) ) {
					$hours = (int) array_pop( $parts );
				} else {
					$hours = 0;
				}

				$page_seconds += $seconds;
				$page_minutes += $minutes;
				$page_hours += $hours;

				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/estimation', sprintf( "%02d:%02d:%02d", $page_hours, $page_minutes, $page_seconds ) );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $page_hours );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/minutes', $page_minutes );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $page_seconds );
			}

			$total_seconds = $page_seconds + ( $page_minutes * 60 ) + ( $page_hours * 3600 );

			$page_hours    = floor( $total_seconds / 3600 );
			$total_seconds = $total_seconds % 3600;
			$page_minutes  = floor( $total_seconds / 60 );
			$page_seconds  = $total_seconds % 60;

			$unit_hours += $page_hours;
			$unit_minutes += $page_minutes;
			$unit_seconds += $page_seconds;

		}

		$total_seconds = $unit_seconds + ( $unit_minutes * 60 ) + ( $unit_hours * 3600 );

		$unit_hours    = floor( $total_seconds / 3600 );
		$total_seconds = $total_seconds % 3600;
		$unit_minutes  = floor( $total_seconds / 60 );
		$unit_seconds  = $total_seconds % 60;

		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/estimation', sprintf( "%02d:%02d:%02d", $unit_hours, $unit_minutes, $unit_seconds ) );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/hours', $unit_hours );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/minutes', $unit_minutes );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/hours', $unit_seconds );

		return $estimations;
	}

	static function by_name( $slug, $id_only, $post_parent = '' ) {

		$args = array(
			'name'           => $slug,
			'post_type'      => self::get_post_type_name( true ),
			'post_status'    => 'any',
			'posts_per_page' => 1,
		);

		if ( $id_only ) {
			$args['fields'] = 'ids';
		}

		if ( ! empty( $post_parent ) ) {
			$args['post_parent'] = (int) $post_parent;
		}

		$post = get_posts( $args );

		if ( $post ) {
			if ( $id_only ) {
				return (int) $post[0];
			}

			return $post[0];
		} else {
			return false;
		}
	}

	public static function is_unit_available( $unit, $previous_unit, $status = false ) {

		if ( ! $status ) {
			$status = self::get_unit_availability_status( $unit, $previous_unit );
		}

		return $status['available'];
	}

	public static function get_unit_availability_status( $unit, $previous_unit ) {

		if ( ! is_object( $unit ) ) {
			$unit = get_post( $unit );
		}
		if ( ! is_object( $previous_unit ) ) {
			$previous_unit = get_post( $previous_unit );
		}

		$unit_id          = $unit->ID;
		$previous_unit_id = ! empty( $previous_unit ) ? $previous_unit->ID : false;

		$unit_available_date = get_post_meta( $unit_id, 'unit_availability', true );

		/* Not filtering date format as it could cause conflicts.  Only filter date on display. */
		$current_date = ( date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );

		/* Check if previous has conditions */
		$force_current_unit_completion            = ! empty( $previous_unit_id ) ? get_post_meta( $previous_unit_id, 'force_current_unit_completion', true ) : false;
		$force_current_unit_successful_completion = ! empty( $previous_unit_id ) ? get_post_meta( $previous_unit_id, 'force_current_unit_successful_completion', true ) : false;
		$force_current_unit_completion = CoursePress_Helper_Utility::fix_bool( $force_current_unit_completion );
		$force_current_unit_successful_completion = CoursePress_Helper_Utility::fix_bool( $force_current_unit_successful_completion );

		$status = array();

		$available = true;

		// COMPLETION LOGIC
		$student_id = get_current_user_id();
		//$mandatory_done	 = Student_Completion::is_mandatory_complete( $student_id, $unit->course_id, $previous_unit_id );
		//$unit_completed	 = Student_Completion::is_unit_complete( $student_id, $unit->course_id, $previous_unit_id );
		$mandatory_done = true;
		$unit_completed = true;

		CoursePress_Helper_Utility::set_array_val( $status, 'mandatory_required/enabled', $force_current_unit_completion );
		CoursePress_Helper_Utility::set_array_val( $status, 'mandatory_required/result', $mandatory_done );

		CoursePress_Helper_Utility::set_array_val( $status, 'completion_required/enabled', $force_current_unit_successful_completion );
		CoursePress_Helper_Utility::set_array_val( $status, 'completion_required/result', $unit_completed );

		$available = $status['mandatory_required']['enabled'] ? $status['mandatory_required']['result'] : $available;
		$available = $status['completion_required']['enabled'] ? $status['completion_required']['result'] : $available;

		CoursePress_Helper_Utility::set_array_val( $status, 'date_restriction/result', ( $current_date >= $unit_available_date ) );

		if ( ! $status['date_restriction']['result'] || ! $available ) {
			$available = false;
		} else {
			$available = true;
		}

		/**
		 * Perform action if unit is available.
		 *
		 * @since 1.2.2
		 * */
		do_action( 'coursepress_unit_availble', $available, $unit_id );

		/**
		 * Return filtered value.
		 *
		 * Can be used by other plugins to filter unit availability.
		 *
		 * @since 1.2.2
		 * */
		$available = apply_filters( 'coursepress_filter_unit_availability', $available, $unit_id );

		$status['available'] = $available;

		return $status;
	}

}