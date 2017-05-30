<?php
/**
 * CoursePress admin functions and definitions.
 *
 * @since 3.0
 * @package CoursePress
 */
if ( ! function_exists( 'coursepress_is_admin' ) ) :
	/**
	 * Check if current page is CoursePress admin page.
	 *
	 * @return bool|string Returns CoursePress screen ID on success or false.
	 */
	function coursepress_is_admin() {
		global $CoursePress_Admin_Page;

		if ( ! $CoursePress_Admin_Page instanceof CoursePress_Admin_Page )
			return false;

		$screen_id = get_current_screen()->id;

		$pttrn = '%toplevel_page_|coursepress-pro_page_|coursepress-base_page_|coursepress_page%';
		$id = preg_replace( $pttrn, '', $screen_id );

		if ( in_array( $screen_id, $CoursePress_Admin_Page->__get( 'screens' ) ) )
			return $id;

		return false;
	}
endif;

if ( ! function_exists( 'coursepress_get_accessable_courses' ) ) :
	/**
	 * Helper function get accessable courses by current user.
	 *
	 * @param bool $publish
	 * @param bool $ids
	 * @param bool $all
	 *
	 * @return array
	 */
	function coursepress_get_accessable_courses( $publish = true, $ids = false, $all = false ) {
		global $CoursePress_User;

		return $CoursePress_User->get_accessable_courses( $publish, $ids, $all );
	}
endif;
