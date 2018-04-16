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
	global $coursepress_unit;
	if ( empty( $unit_id ) ) {
		// Assume current unit
		if ( $coursepress_unit instanceof CoursePress_Unit ) {
			return $coursepress_unit;
		}
	} else {
		$unit = new CoursePress_Unit( $unit_id );
		return $unit;
	}
	return null;
}

/**
 * Get current unit id.
 *
 * @param int|object $unit Unit object or id.
 *
 * @return int|bool
 */
function coursepress_get_unit_id( $unit = 0 ) {
	$unit = coursepress_get_unit( $unit );
	if ( is_wp_error( $unit ) ) {
		return false;
	}
	return $unit->__get( 'ID' );
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
	if ( is_wp_error( $unit ) ) {
		return null;
	}
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
	if ( is_wp_error( $unit ) ) {
		return null;
	}
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
	if ( is_wp_error( $course ) ) {
		return null;
	}
	$unit = coursepress_get_unit( $unit_id );
	if ( is_wp_error( $unit ) ) {
		return null;
	}
	$student = coursepress_get_user();
	if ( is_wp_error( $student ) ) {
		return null;
	}
	return $unit->get_unit_structure( $items_only, $show_details );
}

/**
 * CoursePress delete unit by id
 *
 * @since 3.0.0
 *
 * @param integer $unit_id Unit ID to delete.
 */
function coursepress_delete_unit( $unit_id = 0 ) {
	$unit = coursepress_get_unit( $unit_id );
	if ( is_wp_error( $unit ) ) {
		return false;
	}
	$course = $unit->get_course();
	if ( is_wp_error( $course ) ) {
		return false;
	}
	// Remove the unit from course structures
	$course_structures = array(
		'structure_visible_units',
		'structure_preview_units',
		'structure_visible_pages',
		'structure_preview_pages',
		'structure_visible_module',
		'structure_preview_module',
	);
	foreach ( $course_structures as $structure ) {
		$structures = $course->__get( $structure );
		if ( ! empty( $structures ) ) {
			foreach ( $structures as $key => $value ) {
				if ( preg_match( '%' . $unit_id . '%', $key ) ) {
					unset( $structures[ $key ] );
				}
			}
		}
	}
	// Remove unit steps
	$steps = $unit->get_steps( false );
	if ( ! empty( $steps ) ) {
		foreach ( $steps as $step_id => $step ) {
			wp_delete_post( (int) $step_id, true );
		}
	}
	// Finally, delete the unit
	wp_delete_post( $unit_id, true );
	/**
	 * Fired after a unit is deleted from DB
	 *
	 * @since 3.0
	 */
	do_action( 'coursepress_course_deleted_unit', $unit_id );
	return true;
}

function coursepress_create_unit( $unit, $unit_meta = array() ) {
	$unit['post_name'] = sanitize_title( $unit['post_title'] );
	if ( empty( $unit['ID'] ) ) {
		$unit_id = wp_insert_post( $unit );
	} else {
		$unit_id = wp_update_post( $unit );
	}
	$unit_object = coursepress_get_unit( $unit_id );
	if ( is_wp_error( $unit_object ) ) {
		return false;
	}
	$unit_object->update_settings( true, $unit_meta );
	/**
	 * Fired whenever a new unit is created or updated.
	 *
	 * @param (int) $unit_id
	 * @param (array) $unit_meta
	 */
	do_action( 'coursepress_unit_created', $unit_id, $unit_meta );
	return $unit_id;
}

function coursepress_create_step( $step_array, $step_meta = array() ) {
	if ( empty( $step_array['ID'] ) ) {
		$step_id = wp_insert_post( $step_array );
	} else {
		$step_id = wp_update_post( $step_array );
	}
	if ( ! empty( $step_meta ) ) {
		foreach ( $step_meta as $key => $value ) {
			update_post_meta( $step_id, $key, $value );
		}
	}
	/**
	 * Fired whenever a step is created
	 *
	 * @param (int) $step_id
	 * @param (int) $step_meta
	 */
	do_action( 'coursepress_step_created', $step_id, $step_meta );
	return $step_id;
}

function coursepress_delete_step( $step_id = 0 ) {
	if ( $step_id > 0 ) {
		wp_delete_post( $step_id, true );
		/**
		 * Fired whenever a step is deleted
		 */
		do_action( 'coursepress_step_deleted', $step_id );
	}
}

/**
 * Returns the unit's URL structure.
 *
 * @param int $unit_id
 *
 * @return string
 */
function coursepress_get_unit_permalink( $unit_id = 0 ) {
	$unit = coursepress_get_unit( $unit_id );
	if ( is_wp_error( $unit ) ) {
		return null;
	}
	return $unit->get_permalink();
}
