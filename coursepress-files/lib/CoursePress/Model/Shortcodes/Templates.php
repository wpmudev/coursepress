<?php

class CoursePress_Model_Shortcodes_Templates {

	public static function init() {

		add_shortcode( 'course_archive', array( __CLASS__, 'course_archive' ) );
		add_shortcode( 'course_enroll_box', array( __CLASS__, 'course_enroll_box' ) );
		add_shortcode( 'course_list_box', array( __CLASS__, 'course_list_box' ) );
		add_shortcode( 'course_page', array( __CLASS__, 'course_page' ) );
		add_shortcode( 'instructor_page', array( __CLASS__, 'instructor_page' ) );
		add_shortcode( 'coursepress_dashboard', array( __CLASS__, 'coursepress_dashboard' ) );
		add_shortcode( 'coursepress_focus_item', array( __CLASS__, 'coursepress_focus_item' ) );
		add_shortcode( 'coursepress_quiz_result', array( __CLASS__, 'coursepress_quiz_result' ) );

	}

	public static function course_archive( $a ) {
		global $wp;

		$a = shortcode_atts( array(
			'category'       => CoursePress_Helper_Utility::the_course_category(),
			'posts_per_page' => 10,
			'show_pager'     => true,
			'echo'           => false,
		), $a, 'course_archive' );

		$category   = sanitize_text_field( $a['category'] );
		$perPage    = (int) $a['posts_per_page'];
		$show_pager = CoursePress_Helper_Utility::fix_bool( $a['show_pager'] );
		$echo       = CoursePress_Helper_Utility::fix_bool( $a['echo'] );

		$paged  = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
		$offset = $paged - 1;

		$post_args = array(
			'post_type'      => CoursePress_Model_Course::get_post_type_name(),
			'post_status'    => 'publish',
			'posts_per_page' => $perPage,
			'offset'         => $offset,
			'paged'          => $paged
		);

		// Add category filter
		if ( $category && 'all' !== $category ) {
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => 'course_category',
					'field'    => 'slug',
					'terms'    => array( $category ),
				)
			);
		}

		$query = new WP_Query( $post_args );

		$content = '';

		$template = trim( '[course_list_box]' );

		$template = apply_filters( 'coursepress_template_course_archive', $template, $a );

		foreach ( $query->posts as $post ) {

			CoursePress_Helper_Utility::set_the_course( $post );
			$content .= do_shortcode( $template );

		}

		// Pager
		if ( $show_pager ) {
			$big = 999999999; // need an unlikely integer
			$content .= paginate_links( array(
				'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'  => '?paged=%#%',
				'current' => $paged,
				'total'   => $query->max_num_pages
			) );
		}

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

	public static function course_enroll_box( $a ) {

		$a = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'echo'      => false,
		), $a, 'course_enroll_box' );

		$course_id = (int) $a['course_id'];
		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );

		$template = '
				<div class="enroll-box">
					<div class="enroll-box-left">
						<div class="course-box">
							[course_dates show_alt_display="yes" course_id="' . $course_id . '"]
							[course_enrollment_dates show_enrolled_display="no" course_id="' . $course_id . '"]
							[course_class_size course_id="' . $course_id . '"]
							[course_enrollment_type course_id="' . $course_id . '"]
							[course_language course_id="' . $course_id . '"]
							[course_cost course_id="' . $course_id . '"]
			            </div>
					</div>
					<div class="enroll-box-right">
						<div class="apply-box">
							[course_join_button course_id="' . $course_id . '"]
						</div>
					</div>
				</div>
		';

		$template = apply_filters( 'coursepress_template_course_enroll_box', $template, $course_id, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

	public static function course_list_box( $a ) {

		$a = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'clickable' => false,
			'clickable_label' => __( 'Course Details', CoursePress::TD ),
			'override_button_text' => '',
			'override_button_link' => '',
			'echo'      => false,
		), $a, 'course_list_box' );

		$course_id = (int) $a['course_id'];
		$clickable_label = sanitize_text_field( $a['clickable_label'] );
		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );
		$clickable = CoursePress_Helper_Utility::fix_bool( $a['clickable'] );
		$url = trailingslashit( CoursePress_Core::get_slug('courses', true ) ) . get_post_field( 'post_name', $course_id );

		$course_image = CoursePress_Model_Course::get_setting( $course_id, 'listing_image' );
		$has_thumbnail = ! empty( $course_image );

		$clickable_link = $clickable ? 'data-link="' . esc_url( $url ) . '"' : '';
		$clickable_class = $clickable ? 'clickable' : '';
		$clickable_text = $clickable ? '<div class="clickable-label">' . $clickable_label . '</div>' : '';
		$button_text = ! $clickable ? '[course_join_button list_page="yes" course_id="' . $course_id . '"]' : '';
		$instructor_link = $clickable ? 'no' : 'yes';
		$thumbnail_class = $has_thumbnail ? 'has-thumbnail' : '';

		// Override button
		if( ! empty( $a['override_button_text'] ) && ! empty( $a['override_button_link'] ) ) {
			$button_text = '<button class="coursepress-course-link" data-link="' . esc_url( $a['override_button_link'] ) . '">' . esc_attr( $a['override_button_text'] ) . '</button>';
		}

		$template = '<div class="course course_list_box_item course_' . $course_id . ' ' . $clickable_class . ' ' . $thumbnail_class . '" ' . $clickable_link . '>
			[course_thumbnail course_id="' . $course_id . '"]
			<div class="course-information">
				[course_title course_id="' . $course_id . '"]
				[course_summary course_id="' . $course_id . '"]
				[course_instructors style="list-flat" link="' . $instructor_link . '" course_id="' . $course_id . '"]
				<div class="course-meta-information">
					[course_start label="" course_id="' . $course_id . '"]
					[course_language label="" course_id="' . $course_id . '"]
					[course_cost label="" course_id="' . $course_id . '"]
				</div>' .
	            $button_text . $clickable_text . '
		    </div>
		</div>
		';

		$template = apply_filters( 'coursepress_template_course_list_box', $template, $course_id, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

	public static function course_page( $a ) {

		$a = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'echo'      => false,
		), $a, 'course_page' );

		$course_id = (int) $a['course_id'];
		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );

		$template = '<div class="course-wrapper">
			[course_media course_id="' . $course_id . '"]
			[course_social_links course_id="' . $course_id . '"]
			[course_enroll_box course_id="' . $course_id . '"]
			[course_instructors course_id="' . $course_id . '" avatar_position="top" summary_length="50" link_all="yes" link_text=""]
			[course_description label="' . __( 'About this course', CoursePress::TD ) . '" course_id="' . $course_id . '"]
			[course_structure course_id="' . $course_id . '"]
		</div>
		';

		$template = apply_filters( 'coursepress_template_course_page', $template, $course_id, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

	public static function instructor_page( $a ) {

		$a = shortcode_atts( array(
			'instructor_id' => CoursePress_View_Front_Instructor::$last_instructor,
			'echo'      => false,
		), $a, 'instructor_page' );

		$instructor_id = (int) $a['instructor_id'];
		if( empty( $instructor_id ) ) {
			return '';
		}
		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );

		$template = '<div class="instructor-wrapper">
			[course_instructor_avatar instructor_id="' . $instructor_id . '" force_display="true" thumb_size="200"]
			<div class="instructor-bio">' . CoursePress_Helper_Utility::filter_content( get_user_meta( $instructor_id, 'description', true ) ) . '</div>
			<h3 class="courses-title">' . esc_html__( 'Courses', CoursePress::TD ) . '</h3>
			[course_list instructor="' . $instructor_id . '" class="course" left_class="enroll-box-left" right_class="enroll-box-right" course_class="enroll-box" title_link="yes" show_media="yes"]
		</div>
		';

		$template = apply_filters( 'coursepress_template_instructor_page', $template, $instructor_id, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

	public static function coursepress_dashboard( $a ) {

		$a = shortcode_atts( array(
			'user_id' => get_current_user_id(),
			'echo'      => false,
		), $a, 'coursepress_dashboard' );

		$user_id = (int) $a['user_id'];
		if( empty( $user_id ) ) {
			return '';
		}
		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );

		$template = '<div class="coursepress-dashboard-wrapper">
			[course_list instructor="' . $user_id . '" dashboard="true"]
			[course_list student="' . $user_id . '" dashboard="true" context="enrolled"]
			[course_list student="' . $user_id . '" dashboard="true" context="completed"]
		</div>
		';

		$template = apply_filters( 'coursepress_template_dashboard_page', $template, $user_id, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

	public static function coursepress_focus_item( $a ) {

		$a = shortcode_atts( array(
			'course' => '',
			'unit' => '',
			'type' => '',
			'item_id' => 0,
			'pre_text' => __('&laquo; Previous', CoursePress::TD),
			'next_text' => __('Next &raquo;', CoursePress::TD),
			'next_section_text' => __('Next Section', CoursePress::TD),
			'echo'      => false,
		), $a, 'coursepress_focus_item' );

		$course_id = (int) $a['course'];
		$unit_id = (int) $a['unit'];
		if( empty( $course_id ) && empty( $unit_id ) ) {
			return '';
		}

		CoursePress_Helper_Utility::set_the_course( $course_id );

		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );
		$item_id = (int) $a['item_id'];
		$type = sanitize_text_field( $a['type'] );
		$pre_text = sanitize_text_field( $a['pre_text'] );
		$next_text = sanitize_text_field( $a['next_text'] );
		$next_section_text = sanitize_text_field( $a['next_section_text'] );

		$titles = get_post_meta( $unit_id, 'page_title', true );

		$breadcrumbs = true;

		$page_count = count( $titles );

		if( 'section' === $type ) {
			$page = $item_id;
		} else {
			// Get page from module meta
			$page = get_post_meta( $item_id, 'module_page', true );
		}

		$page_info = CoursePress_Model_Unit::get_page_meta( $unit_id, $page );

		$breadcrumb_trail = '';

		$u_link_url = '';
		$bcs = '<span class="breadcrumb-milestone"></span>'; // Breadcrumb Separator
		$progress_spinner = '<span class="loader hidden"><i class="fa fa-spinner fa-pulse"></i></span>';

		if( $breadcrumbs ) {

			// Course
			$c_link = get_the_permalink( $course_id );
			$a_link = trailingslashit( $c_link . CoursePress_Core::get_slug( 'units' ) );
			$u_link = trailingslashit( $a_link . get_post_field( 'post_name', $unit_id ) );

			$c_link = '<a href="' . esc_url( $c_link ) . '" class="breadcrumb-course crumb">' . get_post_field( 'post_title', $course_id ) . '</a>';
			$a_link = '<a href="' . esc_url( $a_link ) . '" class="breadcrumb-course-units crumb">' . esc_html__( 'Units', CoursePress::TD ) . '</a>';
			$u_link_url = $u_link;
			$u_link = '<a href="' . esc_url( $u_link ) . '#section-1" class="breadcrumb-course-unit crumb" data-id="1">' . get_post_field( 'post_title', $unit_id ) . '</a>';

			$breadcrumb_trail = $c_link . $bcs . $a_link . $bcs . $u_link;
		}

		$can_view = true;

		$student_id = get_current_user_id();
		$enrolled = ! empty( $student_id ) ? CoursePress_Model_Course::student_enrolled( $student_id, $course_id ) : false;
		$instructors = array_filter( CoursePress_Model_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		if( ! $enrolled && ! $is_instructor ) {
			if ( 'section' == $type ) {
				$can_view = CoursePress_Model_Course::can_view_page( $course_id, $unit_id, $page, $student_id );
			}
			if ( 'module' == $type ) {
				$attributes = CoursePress_Model_Module::attributes( $item_id );
				if ( 'output' === $attributes['mode'] ) {
					$can_view = CoursePress_Model_Course::can_view_module( $course_id, $unit_id, $item_id, $page, $student_id );
				} else {
					$can_view = false;
				}

			}
		}

		$type = $can_view ? $type : 'no_access';

		$template = '';
		switch( $type ) {

			case 'section':

				$preview = CoursePress_Model_Course::previewability( $course_id );

				$breadcrumb_trail .= '<span class="breadcrumb-leaf">'. $bcs . '<span class="breadcrumb-course-unit-section crumb end">' . esc_html( $page_info['title'] ) . '</span></span>';

				$content = '<div class="focus-wrapper">';

				$content .= '<div class="focus-main section">';

				$template = '<div class="focus-item focus-item-' . esc_attr( $type ) . '">
					' . $page_info['title'] . '
				</div>
				';

				$template = apply_filters( 'coursepress_template_focus_item_section', $template, $a );

				$content .= $template;

				$content .= '</div>'; // .focus-main

				$content .= '<div class="focus-nav">';
				// Previous Navigation
				if( $page > 1 ) {
					// Get previous section modules
					$pre_modules = CoursePress_Model_Course::get_unit_modules( $unit_id, array('publish'), true, false, array( 'page' => ( $page - 1) ) );

					$content .= '
							<div class="focus-nav-prev" data-id="' . $pre_modules[ ( count( $pre_modules ) - 1 ) ] . '" data-type="module"><a href="#module-' . esc_attr( $pre_modules[ ( count( $pre_modules ) - 1 ) ] ) . '">' . $pre_text . '</a></div>
						';
				}

				// Next Navigation
				$next_modules = CoursePress_Model_Course::get_unit_modules( $unit_id, array('publish'), true, false, array( 'page' => $page ) );
				$next_module = CoursePress_Model_Course::next_accessible( $course_id, $unit_id, $preview, false, $page );
				if( true === $next_module ) {
					$next_module = $next_modules[ 0 ];
				}

				if( ! empty( $next_modules ) ) {
					$content .= '
							<div class="focus-nav-next" data-id="' . $next_module . '" data-type="module"><a href="#module-' . esc_attr( $next_module ) . '">' . $next_text . '</a></div>
						';
				}
				$content .= '</div>'; // .focus-nav

				$content .= '</div>'; // .focus-wrapper

				$template = $content;

				break;

			case 'module':

				//$breadcrumb_trail .= esc_html( $page_info['title'] );

				// Title retrieved below
				$breadcrumb_trail .= $bcs . '<a href="' .esc_url( $u_link_url ) . '#section-' . $page . '" class="breadcrumb-course-unit-section crumb" data-id="' . $page . '">' . $page_info['title'] . '</a>';

				$student_id = get_current_user_id();
				$instructors = CoursePress_Model_Course::get_instructors( $course_id );
				$is_instructor = in_array( $student_id, $instructors );

				// Page access
				$preview = CoursePress_Model_Course::previewability( $course_id );
				$enrolled = ! empty( $student_id ) ? CoursePress_Model_Course::student_enrolled( $student_id, $course_id ) : false;

				$can_preview_page = isset( $preview['has_previews'] ) && isset( $preview['structure'][ $unit_id ] ) && isset( $preview['structure'][ $unit_id ][ $page ] ) && ! empty( $preview['structure'][ $unit_id ][ $page ] );
				$can_preview_page = ! $can_preview_page && isset( $preview['structure'][ $unit_id ] ) && true === $preview['structure'][ $unit_id ] ? true : $can_preview_page;

				$modules = CoursePress_Model_Course::get_unit_modules( $unit_id, array('publish'), true, false, array( 'page' => $page ) );

				// Navigation Vars
				$module_index = array_search( $item_id, $modules );

				$goto_section = false;
				$goto_next_section = false;

				$next_module = CoursePress_Model_Course::next_accessible( $course_id, $unit_id, $preview, $item_id, $page );
				if( true === $next_module ) {
					$next_module = $module_index !== ( count( $modules ) - 1 ) ? $module_index + 1 : false;
					$next_module = false !== $next_module ? $modules[ $next_module ] : $next_module;
				}
				$previous_module = CoursePress_Model_Course::previous_accessible( $course_id, $unit_id, $preview, $item_id, $page );
				if( true === $previous_module ) {
					$previous_module = $module_index !== 0 ? $module_index - 1 : false;
					$previous_module = false !== $previous_module  ? $modules[ $previous_module ] : $previous_module;
				}

				$breadcrumb_trail .= '<span class="breadcrumb-leaf">'. $bcs . '<span class="breadcrumb-course-unit-section-module crumb end">' . esc_html( get_post_field('post_title', $modules[ $module_index ] ) ) . '</span></span>';

				// Show section if we're at the first module
				if( $previous_module === false ) {
					$goto_section = true;
				}

				// Show the next section if this is the last module
				if ( $next_module === false ) {
					$goto_next_section = true;
				}

				$content = '<div class="focus-wrapper">';

				// Main content
				$content .= '<div class="focus-main">';

				$module = get_post( $item_id );
				$attributes = CoursePress_Model_Module::attributes( $module );
				$attributes['course_id'] = $course_id;

				$method = 'render_' . str_replace( '-', '_', $attributes['module_type'] );
				$template = 'CoursePress_Template_Module';
				$next_module_class = '';

				// Make sure we're allowed to move on
				if( 'input-quiz' == $attributes['module_type'] && ! empty( $attributes['mandatory'] ) ) {
					$quiz_result = CoursePress_Model_Module::get_quiz_results( $student_id, $course_id, $unit_id, $module->ID );
					$next_module_class = empty( $quiz_result['passed'] ) ? 'not-active' : $next_module_class;

				}

				$preview_modules = isset( $preview['structure'][ $unit_id ][ $page ] ) ? array_keys( $preview['structure'][ $unit_id ][ $page ] ) : array();
				$can_preview_module = in_array( $module->ID, $preview_modules ) || ( isset( $preview['structure'][ $unit_id ] ) && ! is_array( $preview['structure'][ $unit_id ] ) );

				if( ! $enrolled && ! $can_preview_module && ! $is_instructor ) {
					$content = '';
				} else {

					if( method_exists( $template, $method ) && ( ( $enrolled || $is_instructor ) || ( ! $enrolled && 'output' === $attributes['mode'] ) ) ) {
						$content .= call_user_func( $template . '::' . $method, $module, $attributes );
					}

				}

				$content .= '</div>'; // .focus-main

				$content .= '<div class="focus-nav">';
				// Previous Navigation
				if( $goto_section || $previous_module !== false ) {
					$content .= $goto_section ? '
							<div class="focus-nav-prev" data-id="' . $page . '" data-type="section"><a href="#section-' . esc_attr( $page ) . '">' . $pre_text . '</a></div>
						' : '
							<div class="focus-nav-prev" data-id="' . $previous_module . '" data-type="module"><a href="#module-' . esc_attr( $previous_module ) . '">' . $pre_text . '</a></div>
						';
				}

				// Next Navigation
				if( ( $goto_next_section && $page_count >= ( $page + 1 ) ) || $next_module !== false ) {
					$content .= $goto_next_section ? '
							<div class="focus-nav-next ' . $next_module_class . ' next-section" data-id="' . ( $page + 1 ) . '" data-type="section"><a href="#section-' . esc_attr( ( $page + 1 ) ) . '">' . $next_section_text . '</a></div>
						' : '
							<div class="focus-nav-next ' . $next_module_class . '" data-id="' . $next_module . '" data-type="module"><a href="#module-' . esc_attr( $next_module ) . '">' . $next_text . '</a></div>
						';
				}
				$content .= '</div>'; // .focus-nav

				$content .= '</div>'; // .focus-wrapper

				$template = $content;

				break;

			case 'no_access':
				$template = "No access";
				break;

		}

		$content = $progress_spinner . do_shortcode( $template );

		if( $breadcrumbs ) {
			$content = '<div class="coursepress-breadcrumbs ' . $type . '">' . $breadcrumb_trail . '</div>' . $content;
		}

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}


	public static function coursepress_quiz_result( $a ) {

		$a = shortcode_atts( array(
			'course_id' => false,
			'unit_id' => false,
			'module_id' => false,
			'student_id' => false,
			'echo'      => false,
		), $a, 'coursepress_dashboard' );

		$course_id = (int) $a['course_id'];
		$unit_id = (int) $a['unit_id'];
		$module_id = (int) $a['module_id'];
		$student_id = (int) $a['student_id'];
		$echo      = CoursePress_Helper_Utility::fix_bool( $a['echo'] );

		if( empty( $course_id ) || empty( $unit_id ) || empty( $module_id ) || empty( $student_id ) ) {
			return '';
		}

		$template = CoursePress_Model_Module::quiz_result_content( $student_id, $course_id, $unit_id, $module_id );
		$template = apply_filters( 'coursepress_template_quiz_results_shortcode', $template, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;

	}

}
