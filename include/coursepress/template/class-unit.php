<?php
/**
 * Front-End template file.
 *
 * @package CoursePress
 */

/**
 * Template data to display a single course unit in front-end.
 */
class CoursePress_Template_Unit {

	/**
	 * Render the complete contents of a single course unit.
	 *
	 * This function is called by:
	 * CoursePress_View_Front_Course::render_course_unit()
	 *
	 * It renders the contents of a VirtualPage.
	 *
	 * @since  2.0.0
	 * @return string HTML Content of the page.
	 */
	public static function unit_with_modules() {
		$course = CoursePress_Helper_Utility::the_course();
		$course_id = $course->ID;
		$unit = CoursePress_Helper_Utility::the_post();
		$unit_id = $unit->ID;
		$page = (int) CoursePress_Helper_Utility::the_pagination();

		$student_id = get_current_user_id();
		$is_instructor = false;
		$student_progress = 0;
		$enrolled = false;
		$can_preview_page = true;
		$show_page_title = false;
		$page_title = '';
		$preview_pages = array();
		$next_page = false;
		$next_unit = false;

		if ( $student_id ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
			$instructors = CoursePress_Data_Course::get_instructors( $course_id );
			$is_instructor = in_array( $student_id, $instructors );
			$enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
		}

		$preview = CoursePress_Data_Course::previewability( $course_id );

		if ( ! isset( $preview['has_previews'] ) ) {
			$can_preview_page = false;
		} elseif ( ! isset( $preview['structure'][ $unit->ID ] ) ) {
			$can_preview_page = false;
		} elseif ( ! isset( $preview['structure'][ $unit->ID ][ $page ] ) ) {
			$can_preview_page = false;
		} elseif ( empty( $preview['structure'][ $unit->ID ][ $page ] ) ) {
			$can_preview_page = false;
		} elseif ( isset( $preview['structure'][ $unit->ID ] ) ) {
			$can_preview_page = $preview['structure'][ $unit->ID ];
		}

		if ( ! $enrolled && ! $can_preview_page && ! $is_instructor ) {
			return __( 'Sorry. You are not permitted to view this part of the course.', 'CP_TD' );
		}

		$view_mode = CoursePress_Data_Course::get_setting(
			$course_id,
			'course_view',
			'normal'
		);

		// Let BackboneJS take over if its in Focus mode.
		if ( 'focus' == $view_mode ) {
			// TODO: We need to enqueue correct javascript here to make focus-
			//       mode work!

			return '<div class="coursepress-focus-view" data-course="' . $course_id . '" data-unit="' . $unit_id . '" data-page="' . $page . '"><span class="loader hidden"><i class="fa fa-spinner fa-pulse"></i></span></div>';
		}

		$page_titles = get_post_meta( $unit->ID, 'page_title', true );
		$show_page_titles = get_post_meta( $unit->ID, 'show_page_title', true );

		$total_pages = count( $page_titles );

		// Can't exceed total pages, so do the last one.
		$page = min( $total_pages, $page );

		// Sub Menu.
		$content = do_shortcode( '[course_unit_submenu]' );

		// Get modules for the current page only.
		$modules = CoursePress_Data_Course::get_unit_modules(
			$unit->ID,
			array( 'publish' ),
			false,
			false,
			array( 'page' => $page )
		);

		$content .= '<div class="unit-wrapper unit-' . $unit->ID . ' course-' . $course_id . '">';

		// Page Title.
		if ( isset( $show_page_titles[ $page - 1 ] ) ) {
			$show_page_title = cp_is_true( $show_page_titles[ $page - 1 ] );
		}

		if ( $show_page_title && isset( $page_titles[ 'page_' . $page ] ) ) {
			$page_title = CoursePress_Helper_Utility::filter_content(
				$page_titles[ 'page_' . $page ]
			);

			if ( $page_title ) {
				$content .= '<div class="unit-page-header unit-section-header"><h3 class="page-title unit-section-title">' . $page_title . '</h3></div>';
			}
		}

		// Modules.
		foreach ( $modules as $module ) {
			$preview_modules = array();
			$can_preview_module = false;
			$attributes = CoursePress_Data_Module::attributes( $module );
			$attributes['course_id'] = $course_id;

			$method = 'render_' . str_replace( '-', '_', $attributes['module_type'] );
			$template = 'CoursePress_Template_Module';

			if ( isset( $preview['structure'][ $unit->ID ][ $page ] ) ) {
				$preview_modules = array_keys( $preview['structure'][ $unit->ID ][ $page ] );
			}
			if ( in_array( $module->ID, $preview_modules ) ) {
				$can_preview_module = true;
			} elseif ( isset( $preview['structure'][ $unit->ID ] ) ) {
				$can_preview_module = ! is_array( $preview['structure'][ $unit->ID ] );
			} else {
				$can_preview_module = false;
			}

			if ( ! $enrolled && ! $can_preview_module && ! $is_instructor ) {
				continue;
			}

			if ( $enrolled || $is_instructor || 'output' == $attributes['mode'] ) {
				if ( method_exists( $template, $method ) ) {
					$content .= call_user_func(
						array( $template, $method ),
						$module,
						$attributes
					);
				}
			}
		}

		// Pager.
		if ( isset( $preview['structure'][ $unit->ID ] ) ) {
			$preview_pages = array_keys( $preview['structure'][ $unit->ID ] );
		}

		$url_path = CoursePress_Core::get_slug( 'course/', true ) . trailingslashit( $course->post_name ) .
					CoursePress_Core::get_slug( 'unit/' ) . $unit->post_name . '/page/';

		$content .= '<div class="pager unit-pager">';
		for ( $i = 1; $i <= $total_pages; $i++ ) {
			$unit_url = $url_path . $i;

			if ( $enrolled
				|| $is_instructor
				|| in_array( $i, $preview_pages )
				|| ! is_array( $preview['structure'][ $unit->ID ] )
			) {
				$content .= '<span class="page page-' . $i . '"><a href="' . esc_url_raw( $unit_url ) . '">' . $i . '</a></span> ';
			}
		}

		// Next Page.
		if ( ! $enrolled && ! $is_instructor ) {
			for ( $i = $page + 1; $i <= $total_pages; $i++ ) {
				if ( in_array( $i, $preview_pages ) ) {
					$next_page = $i;
				} elseif ( ! is_array( $preview['structure'][ $unit->ID ] ) ) {
					$next_page = $i;
				}

				if ( $next_page ) {
					break;
				}
			}
		} else {
			if ( 1 != $total_pages && $total_pages != $page ) {
				$next_page = $page + 1;
			}
		}

		if ( $next_page ) {
			$unit_url = $url_path . $next_page;
			$content .= '<span class="next-button page page-' . $i .'"><a href="' . esc_url_raw( $unit_url ) . '"><button>' . esc_html( 'Next', 'CP_TD' ) . '</button></a></span> ';
		}

		// Next unit.
		$units = CoursePress_Data_Course::get_unit_ids( $course_id );
		$unit_index = array_search( $unit->ID, $units );

		for ( $i = $unit_index; $i < count( $units ); $i++ ) {
			$preview_units = array();

			if ( $unit_index == $i ) { continue; }
			if ( $unit_index >= count( $units ) - 1 ) { continue; }

			if ( isset( $preview['structure'] ) ) {
				$preview_units = array_keys( $preview['structure'] );
			}

			$can_preview_unit = in_array( $units[ $i ], $preview_units );
			if ( $is_instructor || $enrolled ) {
				$unit_available = true;
			} elseif ( $can_preview_unit ) {
				$unit_available = CoursePress_Data_Unit::is_unit_available(
					$course_id,
					$units[ $i ],
					$units[ $unit_index ]
				);
			} else {
				$unit_available = false;
			}

			if ( $unit_available ) {
				$next_unit = $units[ $i ];
			}

			if ( $next_unit ) {
				break;
			}
		}

		if ( ! empty( $next_unit ) && empty( $next_page ) ) {
			$unit_url = CoursePress_Core::get_slug( 'course/', true ) . trailingslashit( $course->post_name ) .
						CoursePress_Core::get_slug( 'unit/' ) . get_post_field( 'post_name', $next_unit );

			$content .= '<span class="next-button unit unit-' . $next_unit .'"><a href="' . esc_url_raw( $unit_url ) . '"><button>' . esc_html( 'Next Unit', 'CP_TD' ) . '</button></a></span> ';
		}

		$content .= '</div>'; // .pager
		$content .= '</div>'; // .unit-wrapper

		// Student Tracking:
		if ( $enrolled ) {
			CoursePress_Data_Student::visited_page(
				$student_id,
				$course_id,
				$unit->ID,
				$page,
				$student_progress
			);
		}

		return $content;
	}

	/**
	 * Render the unit archive of a single course.
	 *
	 * This function is called by:
	 * CoursePress_View_Front_Course::render_course_unit_archive()
	 *
	 * It renders the contents of a VirtualPage.
	 *
	 * @since  2.0.0
	 * @return string HTML Content of the page.
	 */
	public static function unit_archive() {
		$content = '';
		$content .= do_shortcode( '[course_unit_submenu]' );

		$content .= '
			<div class="instructors-content">
				' . do_shortcode( '[course_instructors style="list-flat" link="true"]' ) . '
			</div>
		';

		// COMPLETION LOGIC
		// if ( 100 == (int) $progress ) {
		// echo sprintf( '<div class="unit-archive-course-complete">%s %s</div>', '<i class="fa fa-check-circle"></i>', __( 'Course Complete', 'CP_TD' ) );
		// }
		$content .= do_shortcode( '[unit_archive_list description="true"]' );

		return $content;
	}
}
