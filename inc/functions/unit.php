<?php
/**
 * CoursePress unit functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */

/**
 * Returns course current unit serve or unit base on set ID.
 *
 * @param int $unit_id     Optional. If omitted, will return current unit serve.
 *
 * @return object|null
 */
function coursepress_get_unit( $unit_id = 0 ) {
	global $CoursePress_Unit;

	if ( empty( $unit_id ) ) {
		// Assume current unit
		if ( $CoursePress_Unit instanceof CoursePress_Unit )
			return $CoursePress_Unit;
	} else {
		$unit = new CoursePress_Unit( $unit_id );

		if ( is_wp_error( $unit ) )
			return null;

		return $unit;
	}

	return null;
}

/**
 * Returns unit title base on set unit ID or current unit title.
 *
 * @param int $unit_id  Optional. If omitted, will return current serve unit.
 *
 * @return string|null
 */
function coursepress_get_unit_title( $unit_id = 0 ) {
	$unit = coursepress_get_unit( $unit_id );

	if ( is_wp_error( $unit ) )
		return null;

	return $unit->get_the_title();
}

/**
 * Returns unit description if setting is on or null.
 *
 * @param int $unit_id
 *
 * @return string|null
 */
function coursepress_get_unit_description( $unit_id = 0 ) {
	$unit = coursepress_get_unit( $unit_id );

	if ( is_wp_error( $unit ) )
		return null;

	return $unit->get_description();
}

/**
 * Returns unit structure.
 *
 * @param int $course_id
 * @param int $unit_id
 * @param bool $items_only
 * @param bool $show_details
 *
 * @return null
 */
function coursepress_get_unit_structure( $course_id = 0, $unit_id = 0, $items_only = true, $show_details = false ) {
	$course = coursepress_get_course( $course_id );

	if ( is_wp_error( $course ) )
		return null;

	$unit = coursepress_get_unit( $unit_id );

	if ( is_wp_error( $unit ) )
		return null;

	$student = coursepress_get_user();

	if ( is_wp_error( $student ) )
		return null;

	return $unit->get_unit_structure( $items_only, $show_details );
}