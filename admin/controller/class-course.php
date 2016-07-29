<?php
/**
 * Admin course
 **/
class CoursePress_Admin_Controller_Course {
	/**
	 * Delete a course and it's units and modules
	 *
	 * @param (int) $course_id		The course ID to delete.
	 **/
	public static function delete_course( $course_id ) {
		// Verify that current user can delete a course
		$can_delete = CoursePress_Data_Capabilities::can_delete_course( $course_id );

		if ( ! $can_delete ) {
			return; // Bail if unable to delete a course
		}

		wp_delete_post( $course_id, false );

		// Get units
		$status = array( 'publish', 'draft', 'private' );

		$units_ids = CoursePress_Data_Course::get_units( $course_id, $status, true );

		if ( is_array( $units_ids ) && ! empty( $units_ids ) ) {

			// Units found, delete them as well
			foreach ( $units_ids as $unit_id ) {
				wp_delete_post( $unit_id, false );

				/**
				 * Notify others that a unit is deleted
				 **/
				do_action( 'coursepress_unit_deleted', $unit_id );

				$modules_ids = CoursePress_Data_Course::get_unit_modules( $unit_id, $status, true );

				if ( is_array( $modules_ids ) && count( $modules_ids ) > 0 ) {
					// Modules found, delete them
					foreach ( $modules_ids as $module_id ) {
						wp_delete_post( $module_id, true );

						/**
						 * Notify others that a module is deleted
						 **/
						do_action( 'coursepress_module_deleted', $module_id );
					}
				}
			}
		}

		/**
		 * Notify others that a course is deleted
		 **/
		do_action( 'coursepress_course_deleted', $course_id );

		return true;
	}
}
