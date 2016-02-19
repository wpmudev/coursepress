<?php

class CoursePress_Data_Unit {

	private static $post_type = 'unit';

	public static function get_format() {

		return array(
			'post_type' => self::get_post_type_name(),
			'post_args' => array(
				'labels' => array(
					'name' => __( 'Units', CoursePress::TD ),
					'singular_name' => __( 'Unit', CoursePress::TD ),
					'add_new' => __( 'Create New', CoursePress::TD ),
					'add_new_item' => __( 'Create New Unit', CoursePress::TD ),
					'edit_item' => __( 'Edit Unit', CoursePress::TD ),
					'edit' => __( 'Edit', CoursePress::TD ),
					'new_item' => __( 'New Unit', CoursePress::TD ),
					'view_item' => __( 'View Unit', CoursePress::TD ),
					'search_items' => __( 'Search Units', CoursePress::TD ),
					'not_found' => __( 'No Units Found', CoursePress::TD ),
					'not_found_in_trash' => __( 'No Units found in Trash', CoursePress::TD ),
					'view' => __( 'View Unit', CoursePress::TD ),
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

	public static function get_post_type_name( $with_prefix = true ) {
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

		$unit_hours = 0;
		$unit_minutes = 0;
		$unit_seconds = 0;

		if ( ! isset( $data[ $unit_id ]['pages'] ) ) {
			$data[ $unit_id ]['pages'] = array();
		}
		foreach ( $data[ $unit_id ]['pages'] as $page_id => $page ) {

			$page_hours = 0;
			$page_minutes = 0;
			$page_seconds = 0;

			foreach ( $page['modules'] as $module_id => $module ) {
				$duration = CoursePress_Data_Module::get_time_estimation( $module_id, $default );

				$parts = explode( ':', $duration );
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

				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/estimation', sprintf( '%02d:%02d:%02d', $page_hours, $page_minutes, $page_seconds ) );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $page_hours );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/minutes', $page_minutes );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/seconds', $page_seconds );
			}

			$total_seconds = $page_seconds + ( $page_minutes * 60 ) + ( $page_hours * 3600 );

			$page_hours = floor( $total_seconds / 3600 );
			$total_seconds = $total_seconds % 3600;
			$page_minutes = floor( $total_seconds / 60 );
			$page_seconds = $total_seconds % 60;

			$unit_hours += $page_hours;
			$unit_minutes += $page_minutes;
			$unit_seconds += $page_seconds;

		}

		$total_seconds = $unit_seconds + ( $unit_minutes * 60 ) + ( $unit_hours * 3600 );

		$unit_hours = floor( $total_seconds / 3600 );
		$total_seconds = $total_seconds % 3600;
		$unit_minutes = floor( $total_seconds / 60 );
		$unit_seconds = $total_seconds % 60;

		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/estimation', sprintf( '%02d:%02d:%02d', $unit_hours, $unit_minutes, $unit_seconds ) );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/hours', $unit_hours );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/minutes', $unit_minutes );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/seconds', $unit_seconds );

		return $estimations;
	}

	static function by_name( $slug, $id_only, $post_parent = '' ) {

		$args = array(
			'name' => $slug,
			'post_type' => self::get_post_type_name( true ),
			'post_status' => 'any',
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

	public static function is_unit_available( $course, $unit, $previous_unit, $status = false ) {

		if ( ! $status ) {
			$status = self::get_unit_availability_status( $course, $unit, $previous_unit );
		}

		return $status['available'];
	}

	public static function get_page_meta( $unit_id, $item_id ) {

		if ( empty( $item_id ) ) {
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

		return array(
			'title' => $titles[ 'page_' . $item_id ],
			'description' => isset( $descriptions[ 'page_' . $item_id ] ) ? $descriptions[ 'page_' . $item_id ] : '',
			'feature_image' => isset( $images[ 'page_' . $item_id ] ) ? $images[ 'page_' . $item_id ] : '',
			'visible' => $visibilities[ ( $item_id - 1 ) ],
		);

	}

	public static function get_unit_availability_status( $course, $unit, $previous_unit ) {

		if ( ! is_object( $unit ) ) {
			$unit = get_post( $unit );
		}

		$course_id = is_object( $course ) ? $course->ID : (int) $course;

		$unit_id = $unit->ID;
		$previous_unit_id = false;
		if ( ! empty( $previous_unit ) ) {
			$previous_unit_id = is_object( $previous_unit ) ? $previous_unit->ID : (int) $previous_unit ;
		}

		$unit_available_date = get_post_meta( $unit_id, 'unit_availability', true );

		/* Not filtering date format as it could cause conflicts.  Only filter date on display. */
		$current_date = ( date( 'Y-m-d', current_time( 'timestamp', 0 ) ) );

		/* Check if previous has conditions */
		$force_current_unit_completion = ! empty( $previous_unit_id ) ? get_post_meta( $previous_unit_id, 'force_current_unit_completion', true ) : false;
		$force_current_unit_successful_completion = ! empty( $previous_unit_id ) ? get_post_meta( $previous_unit_id, 'force_current_unit_successful_completion', true ) : false;
		$force_current_unit_completion = cp_is_true( $force_current_unit_completion );
		$force_current_unit_successful_completion = cp_is_true( $force_current_unit_successful_completion );

		$status = array();

		$available = true;

		$student_id = get_current_user_id();
		$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		$mandatory_done = CoursePress_Data_Student::is_mandatory_done( $student_id, $course_id, $unit_id, $student_progress );
		$unit_completed = CoursePress_Data_Student::is_unit_complete( $student_id, $course_id, $unit_id, $student_progress );

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
