<?php

class CoursePress_Template_Unit {

	public static function unit_with_modules() {

		$course = CoursePress_Helper_Utility::the_course();
		$course_id = $course->ID;
		$unit = CoursePress_Helper_Utility::the_post();
		$page = (int) CoursePress_Helper_Utility::the_post_page();

		$page_titles = get_post_meta( $unit->ID, 'page_title', true );
		$show_page_titles = get_post_meta( $unit->ID, 'show_page_title', true );

		$total_pages = count( $page_titles );
		$page = $page > $total_pages ? $total_pages : $page; // Can't exceed total pages, so do the last one

		$meta = get_post_meta( $unit->ID, 'input_modules', true );

		// Sub Menu
		$content = do_shortcode( '[course_unit_archive_submenu]' );

		// Get modules for the current page only;
		$modules = CoursePress_Model_Course::get_unit_modules( $unit->ID, array('publish'), false, false, array( 'page' => $page ) );

		$content .= '<div class="unit-wrapper unit-' . $unit->ID . ' course-' . $course_id . '">';


		// Page Title
		$show_page_title = isset( $show_page_titles[ $page - 1 ] ) ? CoursePress_Helper_Utility::fix_bool( $show_page_titles[ $page - 1] ) : false;
		if( $show_page_title ) {
			$page_title = isset( $page_titles[ 'page_' . $page ] ) ? CoursePress_Helper_Utility::filter_content( $page_titles[ 'page_' . $page ] ) : '';
			if( ! empty( $page_title ) ) {

				$content .= '<h3 class="page-title">' . $page_title . '</h3>';

			}
		}

		// Modules
		foreach( $modules as $module ) {

			$attributes = CoursePress_Model_Module::module_attributes( $module );
			$content .= $module->ID . ' ' . $attributes['module_type'] . ' [' . $attributes['mode'] . ']<br />';
			//
			//if( array_key_exists( $module->ID, $input_modules ) ) {
			//	$content .= $module->ID . ' INPUT<br />';
			//} else {
			//	$content .= $module->ID . ' OUTPUT<br />';
			//}



		}

		// Pager
		if( $total_pages > 1 ) {
			$url_path = trailingslashit( CoursePress_Core::get_slug( 'course', true ) ) . trailingslashit( $course->post_name ) .
			            trailingslashit( CoursePress_Core::get_slug( 'unit' ) ) . trailingslashit( $unit->post_name ) . 'page/';
			$content .= '<div class="pager">';
				for( $i = 1; $i <= $total_pages; $i++ ) {
					$unit_url = $url_path . $i;
					$content .= '<span class="page page-' . $i .'"><a href="' . esc_url_raw( $unit_url ) . '">' . $i . '</a></span> ';
				}
			$content .= '</div>';
		}

		$content .= '</div>'; // .unit-wrapper


		return $content;

		//$post = CoursePress_Helper_Utility::the_post();
		//if( empty( $post ) ) {
		//	return __( 'Unit not found.', CoursePress::TD );
		//}


		//return print_r( $post, true );
	}


	public static function unit_archive() {

		$content = do_shortcode( '[course_unit_archive_submenu]' );

		$content .= '
			<div class="instructors-content">
				' . do_shortcode( '[course_instructors style="list-flat" link="true"]' ) . '
			</div>
		';

		// COMPLETION LOGIC
		//if ( 100 == (int) $progress ) {
		//	echo sprintf( '<div class="unit-archive-course-complete">%s %s</div>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', 'cp' ) );
		//}

		$content .= do_shortcode( '[unit_archive_list]' );


		return $content;

	}



}