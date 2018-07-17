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
		global $coursepress_admin_page;

		if ( ! $coursepress_admin_page instanceof CoursePress_Admin_Page ) {
			return false; }

		$screen_id = get_current_screen()->id;

		$pttrn = '%toplevel_page_|coursepress-pro_page_|coursepress-base_page_|coursepress_page%';
		$id = preg_replace( $pttrn, '', $screen_id );

		if ( in_array( $screen_id, $coursepress_admin_page->__get( 'screens' ) ) ) {
			return $id; }

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
		global $coursepress_user;

		return $coursepress_user->get_accessable_courses( $publish, $ids, $all );
	}
endif;

if ( ! function_exists( 'coursepress_get_enrollment_types' ) ) :
	function coursepress_get_enrollment_types() {
		return array(
			'manually' => __( 'Manually added', 'cp' ),
			'registered' => __( 'Any registered users', 'cp' ),
			'passcode' => __( 'Any registered users with a pass code', 'cp' ),
			'prerequisite' => __( 'Registered users who completed the prerequisite course(s).', 'cp' ),
		);
	}
endif;

if ( ! function_exists( 'coursepress_get_categories' ) ) :
	function coursepress_get_categories() {
		$terms = get_terms( array(
			'taxonomy' => 'course_category',
			'hide_empty' => false,
		) );
		$cats = array();

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$cats[ $term->term_id ] = $term->name; }
		}

		return $cats;
	}
endif;
