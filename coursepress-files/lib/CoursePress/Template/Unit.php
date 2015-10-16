<?php

class CoursePress_Template_Unit {

	// Focus Mode
	public static function unit_with_modules_() {

		$course = CoursePress_Helper_Utility::the_course();
		$course_id = $course->ID;
		$unit_id = CoursePress_Helper_Utility::the_post( true );
		$page = (int) CoursePress_Helper_Utility::the_post_page();

		$student_id = get_current_user_id();
		$instructors = CoursePress_Model_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		// Page access
		$preview = CoursePress_Model_Course::previewability( $course_id );
		$enrolled = ! empty( $student_id ) ? CoursePress_Model_Course::student_enrolled( $student_id, $course_id ) : false;

		$can_preview_page = isset( $preview['has_previews'] ) && isset( $preview['structure'][ $unit_id ] ) && isset( $preview['structure'][ $unit_id ][ $page ] ) && ! empty( $preview['structure'][ $unit_id ][ $page ] );
		$can_preview_page = ! $can_preview_page && isset( $preview['structure'][ $unit_id ] ) && true === $preview['structure'][ $unit_id ] ? true : $can_preview_page;
		if( ! $enrolled && ! $can_preview_page && ! $is_instructor ) {
			return __( 'Sorry. You are not permitted to view this part of the course.', CoursePress::TD );
		}

		// Student Tracking:
		if( $enrolled ) {
			//CoursePress_Model_Student::visited_page( $student_id, $course_id, $unit->ID, $page, $student_progress );
			CoursePress_Model_Student::visited_page( $student_id, $course_id, $unit_id, $page );
		}

		return '<div class="coursepress-focus-view" data-course="' . $course_id . '" data-unit="' . $unit_id . '" data-page="' . $page . '"></div>';
	}

	public static function unit_with_modules() {

		$course = CoursePress_Helper_Utility::the_course();
		$course_id = $course->ID;
		$unit = CoursePress_Helper_Utility::the_post();
		$unit_id = $unit->ID;
		$page = (int) CoursePress_Helper_Utility::the_post_page();

		$student_id = get_current_user_id();
		$student_progress = CoursePress_Model_Student::get_completion_data( $student_id, $course_id );
		$instructors = CoursePress_Model_Course::get_instructors( $course_id );
		$is_instructor = in_array( $student_id, $instructors );

		// Page access
		$preview = CoursePress_Model_Course::previewability( $course_id );
		$enrolled = ! empty( $student_id ) ? CoursePress_Model_Course::student_enrolled( $student_id, $course_id ) : false;

		$can_preview_page = isset( $preview['has_previews'] ) && isset( $preview['structure'][ $unit->ID ] ) && isset( $preview['structure'][ $unit->ID ][ $page ] ) && ! empty( $preview['structure'][ $unit->ID ][ $page ] );
		$can_preview_page = ! $can_preview_page && isset( $preview['structure'][ $unit->ID ] ) && true === $preview['structure'][ $unit->ID ] ? true : $can_preview_page;
		if( ! $enrolled && ! $can_preview_page && ! $is_instructor ) {
			return __( 'Sorry. You are not permitted to view this part of the course.', CoursePress::TD );
		}

		$view_mode = CoursePress_Model_Course::get_setting( $course_id, 'course_view', 'normal' );

		// Let BackboneJS take over if its in Focus mode
		if( 'focus' === $view_mode ) {
			return '<div class="coursepress-focus-view" data-course="' . $course_id . '" data-unit="' . $unit_id . '" data-page="' . $page . '"><span class="loader hidden"><i class="fa fa-spinner fa-pulse"></i></span></div>';
		}


		$page_titles = get_post_meta( $unit->ID, 'page_title', true );
		$show_page_titles = get_post_meta( $unit->ID, 'show_page_title', true );

		$total_pages = count( $page_titles );
		$page = $page > $total_pages ? $total_pages : $page; // Can't exceed total pages, so do the last one

		// Sub Menu
		$content = do_shortcode( '[course_unit_submenu]' );

		// Get modules for the current page only;
		$modules = CoursePress_Model_Course::get_unit_modules( $unit->ID, array('publish'), false, false, array( 'page' => $page ) );

		$content .= '<div class="unit-wrapper unit-' . $unit->ID . ' course-' . $course_id . '">';


		// Page Title
		$show_page_title = isset( $show_page_titles[ $page - 1 ] ) ? CoursePress_Helper_Utility::fix_bool( $show_page_titles[ $page - 1] ) : false;
		if( $show_page_title ) {
			$page_title = isset( $page_titles[ 'page_' . $page ] ) ? CoursePress_Helper_Utility::filter_content( $page_titles[ 'page_' . $page ] ) : '';
			if( ! empty( $page_title ) ) {

				$content .= '<div class="unit-page-header unit-section-header"><h3 class="page-title unit-section-title">' . $page_title . '</h3></div>';

			}
		}

		// Modules
		foreach( $modules as $module ) {

			$attributes = CoursePress_Model_Module::attributes( $module );

			$method = 'render_' . str_replace( '-', '_', $attributes['module_type'] );
			$template = 'CoursePress_Template_Module';

			$preview_modules = isset( $preview['structure'][ $unit->ID ][ $page ] ) ? array_keys( $preview['structure'][ $unit->ID ][ $page ] ) : array();
			$can_preview_module = in_array( $module->ID, $preview_modules ) || ( isset( $preview['structure'][ $unit->ID ] ) && ! is_array( $preview['structure'][ $unit->ID ] ) );
			if( ! $enrolled && ! $can_preview_module && ! $is_instructor ) {
				continue;
			}

			if( method_exists( $template, $method ) && ( ( $enrolled || $is_instructor ) || ( ! $enrolled && 'output' === $attributes['mode'] ) ) ) {
				$content .= call_user_func( $template . '::' . $method, $module, $attributes );
			}

		}

		// Pager
		$preview_pages = isset( $preview['structure'][ $unit->ID ] ) ? array_keys( $preview['structure'][ $unit->ID ] ) : array();

		$url_path = trailingslashit( CoursePress_Core::get_slug( 'course', true ) ) . trailingslashit( $course->post_name ) .
		            trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . trailingslashit( $unit->post_name ) . 'page/';
		$content .= '<div class="pager unit-pager">';
		for( $i = 1; $i <= $total_pages; $i++ ) {
			$unit_url = $url_path . $i;
			if( ( $enrolled || $is_instructor ) || ( ! $enrolled && ! $is_instructor && in_array( $i, $preview_pages ) ) || ( ! $enrolled && ! $is_instructor && ! is_array( $preview['structure'][ $unit->ID ] ) ) ) {
				$content .= '<span class="page page-' . $i . '"><a href="' . esc_url_raw( $unit_url ) . '">' . $i . '</a></span> ';
			}
		}

		// Next Page
		if( ! $enrolled && ! $is_instructor ) {
			$next_page = 0;
			for( $i = ( $page + 1 ); $i <= $total_pages; $i++ ) {
				if( empty( $next_page ) && ( in_array( $i, $preview_pages ) || ! is_array( $preview['structure'][ $unit->ID ] ) ) ) {
					$next_page = $i;
				}
			}
		} else {
			$next_page = $total_pages === $page || $total_pages === 1 ? '' : $page + 1;
		}
		if( ! empty( $next_page ) ) {
			$unit_url = $url_path . $next_page;
			$content .= '<span class="next-button page page-' . $i .'"><a href="' . esc_url_raw( $unit_url ) . '"><button>' . esc_html( 'Next', CoursePress::TD ) . '</button></a></span> ';
		}

		// Next unit
		$units = CoursePress_Model_Course::get_unit_ids( $course_id );
		$unit_index = array_search( $unit->ID, $units );
		$next_unit = 0;
		for( $i = $unit_index; $i < count( $units ); $i++ ) {
			if( empty( $next_unit ) && $unit_index !== $i && $unit_index < ( count( $units ) - 1 ) ) {

				$preview_units = isset( $preview['structure'] ) ? array_keys( $preview['structure'] ) : array();
				$can_preview_unit = in_array( $units[ $i ], $preview_units );
				$unit_available = CoursePress_Model_Unit::is_unit_available( $course_id, $units[ $i ], $units[ $unit_index ] ) || $is_instructor;

				if( ( ( $enrolled || $is_instructor ) && $unit_available ) || ( ! $enrolled && ! $is_instructor && $can_preview_unit && $unit_available ) ) {
					$next_unit = $units[ $i ];
				}
			}
		}
		if( ! empty( $next_unit ) && empty( $next_page ) ) {
			$unit_url = trailingslashit( CoursePress_Core::get_slug( 'course', true ) ) . trailingslashit( $course->post_name ) .
			            trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . trailingslashit( get_post_field( 'post_name', $next_unit ) );
			$content .= '<span class="next-button unit unit-' . $next_unit .'"><a href="' . esc_url_raw( $unit_url ) . '"><button>' . esc_html( 'Next Unit', CoursePress::TD ) . '</button></a></span> ';
		}


		$content .= '</div>'; // .pager

		$content .= '</div>'; // .unit-wrapper

		// Student Tracking:
		if( $enrolled ) {
			CoursePress_Model_Student::visited_page( $student_id, $course_id, $unit->ID, $page, $student_progress );
		}

		return $content;

	}


	public static function unit_archive() {

		$content = '';
		$content .= do_shortcode( '[course_unit_submenu]' );

		$content .= '
			<div class="instructors-content">
				' . do_shortcode( '[course_instructors style="list-flat" link="true"]' ) . '
			</div>
		';

		// COMPLETION LOGIC
		//if ( 100 == (int) $progress ) {
		//	echo sprintf( '<div class="unit-archive-course-complete">%s %s</div>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', CoursePress::TD ) );
		//}

		$content .= do_shortcode( '[unit_archive_list]' );

		return $content;

	}



}