<?php

class CoursePress_Data_Unit {


	public static function get_post_type_name() {

		return CoursePress_Data_PostFormat::prefix( self::$post_type );
	}

	/**
	 * Get time to end tasks.
	 *
	 * @param int $unit_id Unit ID.
	 * @param array $data
	 * @param string $default
	 *
	 * @return array
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
				// Increase page time.
				$page_seconds += $time['total_seconds'];
			}

			// Page time.
			$time = CoursePress_Helper_Utility::get_time( $page_seconds );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/estimation', $time['time'] );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/components/hours', $time['hours'] );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/components/minutes', $time['minutes'] );
			$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'pages/' . $page_id . '/components/seconds', $time['seconds'] );
			// Increase unit time.
			$unit_seconds += $time['total_seconds'];
		}

		// Unit time.
		$time = CoursePress_Helper_Utility::get_time( $unit_seconds );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/estimation', $time['time'] );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/components/hours', $time['hours'] );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/components/minutes', $time['minutes'] );
		$estimations = CoursePress_Helper_Utility::set_array_value( $estimations, 'unit/components/seconds', $time['seconds'] );

		return $estimations;
	}
}
