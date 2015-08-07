<?php

class CoursePress_Model_Unit {

	private static $post_type = 'unit';

	public static function get_format() {

		return array(
			'post_type' => self::$post_type,
			'post_args' => array(
				'labels'			 => array(
					'name'				 => __( 'Units', 'cp' ),
					'singular_name'		 => __( 'Unit', 'cp' ),
					'add_new'			 => __( 'Create New', 'cp' ),
					'add_new_item'		 => __( 'Create New Unit', 'cp' ),
					'edit_item'			 => __( 'Edit Unit', 'cp' ),
					'edit'				 => __( 'Edit', 'cp' ),
					'new_item'			 => __( 'New Unit', 'cp' ),
					'view_item'			 => __( 'View Unit', 'cp' ),
					'search_items'		 => __( 'Search Units', 'cp' ),
					'not_found'			 => __( 'No Units Found', 'cp' ),
					'not_found_in_trash' => __( 'No Units found in Trash', 'cp' ),
					'view'				 => __( 'View Unit', 'cp' )
				),
				'public'			 => false,
				'show_ui'			 => false,
				'publicly_queryable' => false,
				'capability_type'	 => 'unit',
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

	public static function get_time_estimation( $unit_id, $data, $default = '1:00' ) {

		$estimations = array();

		$unit_hours = 0;
		$unit_minutes = 0;
		$unit_seconds = 0;

		if( ! isset( $data[ $unit_id ]['pages'] ) ) {
			$data[ $unit_id ]['pages'] = array();
		}
		foreach( $data[ $unit_id ]['pages'] as $page_id => $page ) {

			$page_hours = 0;
			$page_minutes = 0;
			$page_seconds = 0;

			foreach( $page['modules'] as $module_id => $module ) {
				$duration = CoursePress_Model_Module::get_time_estimation( $module_id, $default );

				$parts = explode( ':', $duration );
				$seconds = (int) array_pop( $parts );
				$minutes = (int) array_pop( $parts );
				if( ! empty( $parts ) ) {
					$hours = (int) array_pop( $parts );
				} else {
					$hours = 0;
				}

				$page_seconds += $seconds;
				$page_minutes += $minutes;
				$page_hours += $hours;

				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/estimation', sprintf("%02d:%02d:%02d", $page_hours, $page_minutes, $page_seconds ) );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $page_hours );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/minutes', $page_minutes );
				CoursePress_Helper_Utility::set_array_val( $estimations, 'pages/' . $page_id . '/components/hours', $page_seconds );
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

		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/estimation', sprintf("%02d:%02d:%02d", $unit_hours, $unit_minutes, $unit_seconds ) );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/hours', $unit_hours );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/minutes', $unit_minutes );
		CoursePress_Helper_Utility::set_array_val( $estimations, 'unit/components/hours', $unit_seconds );

		return $estimations;
	}

}