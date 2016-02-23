<?php
/**
 * Shortcode handlers.
 *
 * @package CoursePress
 */

/**
 * Templating shortcodes.
 *
 * Those shortcodes provide front-end elements that combine several other
 * courseptress-shortcodes.
 */
class CoursePress_Data_Shortcode_Template {

	/**
	 * Register the shortcodes.
	 *
	 * @since  2.0.0
	 */
	public static function init() {
		add_shortcode(
			'course_archive',
			array( __CLASS__, 'course_archive' )
		);
		add_shortcode(
			'course_enroll_box',
			array( __CLASS__, 'course_enroll_box' )
		);
		add_shortcode(
			'course_list_box',
			array( __CLASS__, 'course_list_box' )
		);
		add_shortcode(
			'course_page',
			array( __CLASS__, 'course_page' )
		);
		add_shortcode(
			'instructor_page',
			array( __CLASS__, 'instructor_page' )
		);
		add_shortcode(
			'coursepress_dashboard',
			array( __CLASS__, 'coursepress_dashboard' )
		);
		add_shortcode(
			'coursepress_focus_item',
			array( __CLASS__, 'coursepress_focus_item' )
		);
		add_shortcode(
			'coursepress_quiz_result',
			array( __CLASS__, 'coursepress_quiz_result' )
		);

		add_shortcode(
			'cp_pages',
			array( __CLASS__, 'cp_pages' )
		);
		add_shortcode(
			'course_signup_form',
			array( __CLASS__, 'course_signup_form' )
		);
		if ( apply_filters( 'coursepress_custom_signup', true ) ) {
			add_shortcode(
				'course_signup',
				array( __CLASS__, 'course_signup' )
			);
		}

		add_shortcode(
			'messaging_submenu',
			array( __CLASS__, 'messaging_submenu' )
		);

		add_shortcode(
			'course_unit_single',
			array( __CLASS__, 'course_unit_single' )
		);
		add_shortcode(
			'course_units_loop',
			array( __CLASS__, 'course_units_loop' )
		);
		add_shortcode(
			'course_notifications_loop',
			array( __CLASS__, 'course_notifications_loop' )
		);
		add_shortcode(
			'courses_loop',
			array( __CLASS__, 'courses_loop' )
		);
		add_shortcode(
			'course_discussion_loop',
			array( __CLASS__, 'course_discussion_loop' )
		);
	}

	public static function course_archive( $a ) {
		global $wp;

		$a = shortcode_atts( array(
			'category' => CoursePress_Helper_Utility::the_course_category(),
			'posts_per_page' => 10,
			'show_pager' => true,
			'echo' => false,
		), $a, 'course_archive' );

		$category = sanitize_text_field( $a['category'] );
		$per_page = (int) $a['posts_per_page'];
		$show_pager = cp_is_true( $a['show_pager'] );
		$echo = cp_is_true( $a['echo'] );

		$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;
		$offset = $paged - 1;

		$post_args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'offset' => $offset,
			'paged' => $paged,
		);

		// Add category filter
		if ( $category && 'all' !== $category ) {
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => 'course_category',
					'field' => 'slug',
					'terms' => array( $category ),
				),
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

		// Pager.
		if ( $show_pager ) {
			$big = 999999999; // need an unlikely integer.
			$content .= paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => $paged,
				'total' => $query->max_num_pages,
			) );
		}

		$content = apply_filters( 'coursepress_course_archive_content', $content, $a );

		if ( $echo ) {
			echo $content;
		}

		return $content;
	}

	public static function course_enroll_box( $a ) {
		$a = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'echo' => false,
		), $a, 'course_enroll_box' );

		$course_id = (int) $a['course_id'];
		$echo = cp_is_true( $a['echo'] );

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
			'clickable_label' => __( 'Course Details', 'CP_TD' ),
			'override_button_text' => '',
			'override_button_link' => '',
			'echo' => false,
		), $a, 'course_list_box' );

		$course_id = (int) $a['course_id'];
		$clickable_label = sanitize_text_field( $a['clickable_label'] );
		$echo = cp_is_true( $a['echo'] );
		$clickable = cp_is_true( $a['clickable'] );
		$url = trailingslashit( CoursePress_Core::get_slug( 'courses', true ) ) . get_post_field( 'post_name', $course_id );

		$course_image = CoursePress_Data_Course::get_setting( $course_id, 'listing_image' );
		$has_thumbnail = ! empty( $course_image );

		$clickable_link = $clickable ? 'data-link="' . esc_url( $url ) . '"' : '';
		$clickable_class = $clickable ? 'clickable' : '';
		$clickable_text = $clickable ? '<div class="clickable-label">' . $clickable_label . '</div>' : '';
		$button_text = ! $clickable ? '[course_join_button list_page="yes" course_id="' . $course_id . '"]' : '';
		$instructor_link = $clickable ? 'no' : 'yes';
		$thumbnail_class = $has_thumbnail ? 'has-thumbnail' : '';

		$completed = false;
		$student_progress = false;
		if ( is_user_logged_in() ) {
			$student_progress = CoursePress_Data_Student::calculate_completion( get_current_user_id(), $course_id );
			$completed = ( $student_progress['completion']['completed'] ) && ! empty( $student_progress['completion']['completed'] );
		}
		$completion_class = $completed ? 'course-completed' : '';

		// Override button
		if ( ! empty( $a['override_button_text'] ) && ! empty( $a['override_button_link'] ) ) {
			$button_text = '<button class="coursepress-course-link" data-link="' . esc_url( $a['override_button_link'] ) . '">' . esc_attr( $a['override_button_text'] ) . '</button>';
		}

		$template = '<div class="course course_list_box_item course_' . $course_id . ' ' . $clickable_class . ' ' . $completion_class . ' ' . $thumbnail_class . '" ' . $clickable_link . '>
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
			'echo' => false,
		), $a, 'course_page' );

		$course_id = (int) $a['course_id'];
		$echo = cp_is_true( $a['echo'] );

		$template = '<div class="course-wrapper">
			[course_media course_id="' . $course_id . '"]
			[course_social_links course_id="' . $course_id . '"]
			[course_enroll_box course_id="' . $course_id . '"]
			[course_instructors course_id="' . $course_id . '" avatar_position="top" summary_length="50" link_all="yes" link_text=""]
			[course_description label="' . __( 'About this course', 'CP_TD' ) . '" course_id="' . $course_id . '"]
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
			'echo' => false,
		), $a, 'instructor_page' );

		$instructor_id = (int) $a['instructor_id'];
		if ( empty( $instructor_id ) ) { return ''; }

		$echo = cp_is_true( $a['echo'] );

		$template = '<div class="instructor-wrapper">
			[course_instructor_avatar instructor_id="' . $instructor_id . '" force_display="true" thumb_size="200"]
			<div class="instructor-bio">' . CoursePress_Helper_Utility::filter_content( get_user_meta( $instructor_id, 'description', true ) ) . '</div>
			<h3 class="courses-title">' . esc_html__( 'Courses', 'CP_TD' ) . '</h3>
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
			'echo' => false,
		), $a, 'coursepress_dashboard' );

		$user_id = (int) $a['user_id'];
		if ( empty( $user_id ) ) { return ''; }

		$echo = cp_is_true( $a['echo'] );

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
			'pre_text' => __( '&laquo; Previous', 'CP_TD' ),
			'next_text' => __( 'Next &raquo;', 'CP_TD' ),
			'next_section_text' => __( 'Next Section', 'CP_TD' ),
			'echo' => false,
		), $a, 'coursepress_focus_item' );

		do_action( 'coursepress_focus_item_preload', $a );

		$course_id = (int) $a['course'];
		$unit_id = (int) $a['unit'];
		if ( empty( $course_id ) && empty( $unit_id ) ) {
			return '';
		}

		CoursePress_Helper_Utility::set_the_course( $course_id );

		$echo = cp_is_true( $a['echo'] );
		$item_id = (int) $a['item_id'];
		$type = sanitize_text_field( $a['type'] );
		$pre_text = sanitize_text_field( $a['pre_text'] );
		$next_text = sanitize_text_field( $a['next_text'] );
		$next_section_text = sanitize_text_field( $a['next_section_text'] );

		$titles = get_post_meta( $unit_id, 'page_title', true );

		$breadcrumbs = true;
		$page_count = count( $titles );

		$preview = CoursePress_Data_Course::previewability( $course_id );

		if ( 'section' === $type ) {
			$page = $item_id;

			if ( CoursePress_Data_Course::get_setting( $course_id, 'focus_hide_section', true ) ) {
				$next_modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => $page ) );
				$mod = 0;
				if ( ! empty( $next_modules ) ) {
					$mod = (int) $next_modules[0];
					$page = (int) get_post_meta( $mod, 'module_page', true );
					$type = 'module';
				}

				// "Redirect" to module.
				$item_id = $mod;

				if ( empty( $mod ) || ! CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $mod, $page ) ) {
					$type = 'no_access';
				}
			}
		} else {
			// Get page from module meta.
			$page = get_post_meta( $item_id, 'module_page', true );
		}

		$page_info = CoursePress_Data_Unit::get_page_meta( $unit_id, $page );
		$breadcrumb_trail = '';

		$u_link_url = '';
		$bcs = '<span class="breadcrumb-milestone"></span>'; // Breadcrumb Separator
		$progress_spinner = '<span class="loader hidden"><i class="fa fa-spinner fa-pulse"></i></span>';

		if ( $breadcrumbs ) {
			// Course.
			$c_link = get_the_permalink( $course_id );
			$a_link = trailingslashit( $c_link . CoursePress_Core::get_slug( 'units' ) );
			$u_link = trailingslashit( $a_link . get_post_field( 'post_name', $unit_id ) );

			$c_link = '<a href="' . esc_url( $c_link ) . '" class="breadcrumb-course crumb">' . get_post_field( 'post_title', $course_id ) . '</a>';
			$a_link = '<a href="' . esc_url( $a_link ) . '" class="breadcrumb-course-units crumb">' . esc_html__( 'Units', 'CP_TD' ) . '</a>';
			$u_link_url = $u_link;
			$u_link = '<a href="' . esc_url( $u_link ) . '#section-1" class="breadcrumb-course-unit crumb" data-id="1">' . get_post_field( 'post_title', $unit_id ) . '</a>';

			$breadcrumb_trail = $c_link . $bcs . $a_link . $bcs . $u_link;
		}

		$can_view = true;

		$student_id = get_current_user_id();
		$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;
		$student_progress = $enrolled ? CoursePress_Data_Student::get_completion_data( $student_id, $course_id ) : false;
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		if ( ! $enrolled && ! $is_instructor ) {
			if ( 'section' == $type ) {
				$can_view = CoursePress_Data_Course::can_view_page( $course_id, $unit_id, $page, $student_id );
			}
			if ( 'module' == $type ) {
				$attributes = CoursePress_Data_Module::attributes( $item_id );
				if ( 'output' === $attributes['mode'] ) {
					$can_view = CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $item_id, $page, $student_id );
				} else {
					$can_view = false;
				}
			}
		}

		$type = $can_view ? $type : 'no_access';
		$template = '';

		switch ( $type ) {
			case 'section':
				if ( $enrolled ) {
					CoursePress_Data_Student::visited_page( $student_id, $course_id, $unit_id, $page, $student_progress );
				}

				$breadcrumb_trail .= '<span class="breadcrumb-leaf">' . $bcs . '<span class="breadcrumb-course-unit-section crumb end">' . esc_html( $page_info['title'] ) . '</span></span>';

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
				if ( $page > 1 ) {
					// Get previous section modules
					$pre_modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => ( $page - 1 ) ) );

					$content .= '
							<div class="focus-nav-prev" data-id="' . $pre_modules[ ( count( $pre_modules ) - 1 ) ] . '" data-type="module"><a href="#module-' . esc_attr( $pre_modules[ ( count( $pre_modules ) - 1 ) ] ) . '">' . $pre_text . '</a></div>
						';
				}

				// Next Navigation
				$next_modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => $page ) );
				$next_module = CoursePress_Data_Course::next_accessible( $course_id, $unit_id, $preview, false, $page );
				if ( true === $next_module ) {
					$next_module = $next_modules[0];
				}

				if ( ! empty( $next_modules ) ) {
					$content .= '
							<div class="focus-nav-next" data-title="" data-id="' . $next_module . '" data-type="module"><a href="#module-' . esc_attr( $next_module ) . '">' . $next_text . '</a></div>
						';
				}
				$content .= '</div>'; // .focus-nav
				$content .= '</div>'; // .focus-wrapper

				$template = $content;
				break;

			case 'module':
				// $breadcrumb_trail .= esc_html( $page_info['title'] );
				if ( $enrolled ) {
					CoursePress_Data_Student::visited_module( $student_id, $course_id, $unit_id, $item_id, $student_progress );
				}

				// Title retrieved below
				$breadcrumb_trail .= $bcs . '<a href="' . esc_url( $u_link_url ) . '#section-' . $page . '" class="breadcrumb-course-unit-section crumb" data-id="' . $page . '">' . $page_info['title'] . '</a>';

				$student_id = get_current_user_id();
				$instructors = CoursePress_Data_Course::get_instructors( $course_id );
				$is_instructor = in_array( $student_id, $instructors );

				// Page access
				$enrolled = ! empty( $student_id ) ? CoursePress_Data_Course::student_enrolled( $student_id, $course_id ) : false;

				$can_preview_page = isset( $preview['has_previews'] ) && isset( $preview['structure'][ $unit_id ] ) && isset( $preview['structure'][ $unit_id ][ $page ] ) && ! empty( $preview['structure'][ $unit_id ][ $page ] );
				$can_preview_page = ! $can_preview_page && isset( $preview['structure'][ $unit_id ] ) && true === $preview['structure'][ $unit_id ] ? true : $can_preview_page;

				$modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => $page ) );

				// Navigation Vars
				$module_index = array_search( $item_id, $modules );

				$goto_section = false;
				$goto_next_section = false;

				$next_module = CoursePress_Data_Course::next_accessible(
					$course_id,
					$unit_id,
					$preview,
					$item_id,
					$page
				);
				$previous_module = CoursePress_Data_Course::previous_accessible(
					$course_id,
					$unit_id,
					$preview,
					$item_id,
					$page
				);

				if ( true === $next_module ) {
					$last_module = count( $modules ) - 1;
					$next_module = ($last_module != $module_index ? $module_index + 1 : false);
					$next_module = false !== $next_module ? $modules[ $next_module ] : $next_module;
				}

				if ( true === $previous_module ) {
					$previous_module = (0 != $module_index ? $module_index - 1 : false);
					$previous_module = false !== $previous_module ? $modules[ $previous_module ] : $previous_module;
				}

				$breadcrumb_trail .= '<span class="breadcrumb-leaf">' . $bcs . '<span class="breadcrumb-course-unit-section-module crumb end">' . esc_html( get_post_field( 'post_title', $modules[ $module_index ] ) ) . '</span></span>';

				// Show section if we're at the first module.
				if ( false === $previous_module ) {
					if ( CoursePress_Data_Course::get_setting( $course_id, 'focus_hide_section', true ) ) {
						if ( (int) $page > 1 ) {
							$modules = CoursePress_Data_Course::get_unit_modules( $unit_id, array( 'publish' ), true, false, array( 'page' => ( $page - 1 ) ) );
							$previous_module = array_pop( $modules );
						}
					} else {
						$goto_section = true;
					}
				}

				// Show the next section if this is the last module
				if ( false === $next_module ) {
					$goto_next_section = true;
				}

				$module = get_post( $item_id );
				$attributes = CoursePress_Data_Module::attributes( $module );
				$attributes['course_id'] = $course_id;

				// Get completion states
				$module_seen = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/modules_seen/' . $item_id );
				$module_passed = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/passed/' . $item_id );
				$module_answered = CoursePress_Helper_Utility::get_array_val( $student_progress, 'completion/' . $unit_id . '/answered/' . $item_id );

				$seen_class = isset( $module_seen ) && ! empty( $module_seen ) ? 'module-seen' : '';
				$passed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] ? 'module-passed' : '';
				$answered_class = isset( $module_answered ) && ! empty( $module_answered ) && $attributes['mandatory'] ? 'module-answered' : '';
				$completed_class = isset( $module_passed ) && ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';
				$completed_class = empty( $completed_class ) && isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ? 'module-completed' : '';

				$content = '<div class="focus-wrapper">';

				// Main content
				$content .= '<div class="focus-main ' . $seen_class . ' ' . $passed_class . ' ' . $answered_class . ' ' . $completed_class . '">';

				$method = 'render_' . str_replace( '-', '_', $attributes['module_type'] );
				$template = 'CoursePress_Template_Module';
				$next_module_class = '';

				// Make sure we're allowed to move on
				if ( 'input-quiz' == $attributes['module_type'] && ! empty( $attributes['mandatory'] ) ) {
					$quiz_result = CoursePress_Data_Module::get_quiz_results( $student_id, $course_id, $unit_id, $module->ID );
					$next_module_class = empty( $quiz_result['passed'] ) ? 'not-active' : $next_module_class;
				}

				$preview_modules = isset( $preview['structure'][ $unit_id ][ $page ] ) ? array_keys( $preview['structure'][ $unit_id ][ $page ] ) : array();
				$can_preview_module = in_array( $module->ID, $preview_modules ) || ( isset( $preview['structure'][ $unit_id ] ) && ! is_array( $preview['structure'][ $unit_id ] ) );

				if ( ! $enrolled && ! $can_preview_module && ! $is_instructor ) {
					$content = '';
				} else {
					if ( method_exists( $template, $method ) && ( ( $enrolled || $is_instructor ) || ( ! $enrolled && 'output' === $attributes['mode'] ) ) ) {
						$content .= call_user_func( $template . '::' . $method, $module, $attributes );
					}
				}

				$content .= '</div>'; // .focus-main

				$content .= '<div class="focus-nav">';
				// Previous Navigation
				if ( $goto_section || false !== $previous_module ) {
					$content .= $goto_section ? '
						<div class="focus-nav-prev" data-id="' . $page . '" data-type="section"><a href="#section-' . esc_attr( $page ) . '">' . $pre_text . '</a></div>
					' : '
						<div class="focus-nav-prev" data-id="' . $previous_module . '" data-type="module"><a href="#module-' . esc_attr( $previous_module ) . '">' . $pre_text . '</a></div>
					';
				}

				// Next Navigation
				if ( ( $goto_next_section && $page_count >= ( $page + 1 ) ) || false !== $next_module ) {
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
				$content = do_shortcode( '[coursepress_enrollment_templates]' );
				$content .= '<div class="focus-wrapper">';
				$content .= '<div class="focus-main section">';

				$content .= '<div class="no-access-message">' . __( 'You do not currently have access to this part of the course. Signup now to get full access to the course.', 'CP_TD' ) . '</div>';
				$content .= do_shortcode( '[course_join_button course_id="' . $course_id . '"]' );

				$content .= '</div>'; // .focus-main
				$content .= '</div>'; // .focus-wrapper

				$template = apply_filters( 'coursepress_no_access_message', $content, $course_id, $unit_id );
				break;
		}

		$content = $progress_spinner . do_shortcode( $template );

		if ( $breadcrumbs ) {
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
			'echo' => false,
		), $a, 'coursepress_dashboard' );

		$course_id = (int) $a['course_id'];
		$unit_id = (int) $a['unit_id'];
		$module_id = (int) $a['module_id'];
		$student_id = (int) $a['student_id'];
		$echo = cp_is_true( $a['echo'] );

		if ( empty( $course_id ) ) { return ''; }
		if ( empty( $unit_id ) ) { return ''; }
		if ( empty( $module_id ) ) { return ''; }
		if ( empty( $student_id ) ) { return ''; }

		$template = CoursePress_Data_Module::quiz_result_content(
			$student_id,
			$course_id,
			$unit_id,
			$module_id
		);
		$template = apply_filters(
			'coursepress_template_quiz_results_shortcode',
			$template,
			$a
		);

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;
	}

	/**
	 * @todo: Migrate those templates to 2.0 code!
	 */
	public static function cp_pages( $atts ) {
		global $plugin_dir;
		ob_start();
		extract( shortcode_atts(
			array(
				'page' => '',
			),
			$atts
		) );

		switch ( $page ) {
			case 'enrollment_process':
				require( $plugin_dir . '_deprecated/templates/enrollment-process.php' );
				break;

			case 'student_login':
				require( $plugin_dir . '_deprecated/templates/student-login.php' );
				break;

			case 'student_signup':
				require( $plugin_dir . '_deprecated/templates/student-signup.php' );
				break;

			case 'student_dashboard':
				require( $plugin_dir . '_deprecated/templates/student-dashboard.php' );
				break;

			case 'student_settings':
				require( $plugin_dir . '_deprecated/templates/student-settings.php' );
				break;

			default:
				_e( 'Page cannot be found', 'CP_TD' );
		}

		$content = wpautop( ob_get_clean(), apply_filters( 'coursepress_pages_content_preserve_line_breaks', true ) );

		return $content;
	}

	/**
	 * Display navigation links for messaging: Inbox/Messages/Compose
	 *
	 * @since  2.0.0
	 * @param  array $atts Shortcode attributes. No options available.
	 * @return string HTML code for navigation block.
	 */
	public static function messaging_submenu( $atts ) {
		global $coursepress;

		if ( isset( $coursepress->inbox_subpage ) ) {
			$subpage = $coursepress->inbox_subpage;
		} else {
			$subpage = '';
		}

		$unread_display = '';
		$show_messaging = cp_is_true( get_option( 'show_messaging', 0 ) );

		if ( $show_messaging ) {
			$unread_count = cp_messaging_get_unread_messages_count();
			if ( $unread_count > 0 ) {
				$unread_display = sprintf( ' (%d)', $unread_count );
			} else {
				$unread_display = '';
			}
		}

		$url_inbox = $coursepress->get_inbox_slug( true );
		$url_messages = $coursepress->get_sent_messages_slug( true );
		$url_compose = $coursepress->get_new_message_slug( true );
		$class_inbox = 'inbox' == $subpage ? 'submenu-active' : '';
		$class_messages = 'sent_messages' == $subpage ? 'submenu-active' : '';
		$class_compose = 'new_message' == $subpage ? 'submenu-active' : '';

		ob_start();
		?>
		<div class="submenu-main-container submenu-messaging">
			<ul id="submenu-main" class="submenu nav-submenu">
				<li class="submenu-item submenu-inbox <?php echo esc_attr( $class_inbox ); ?>">
					<a href="<?php echo esc_url( $url_inbox ); ?>">
						<?php
						esc_html_e( 'Inbox', 'CP_TD' );
						echo $unread_display;
						?>
					</a></li>
				<li class="submenu-item submenu-sent-messages <?php echo esc_attr( $class_messages ); ?>">
					<a href="<?php echo esc_url( $url_messages ); ?>">
					<?php esc_html_e( 'Sent', 'CP_TD' ); ?>
					</a>
				</li>
				<li class="submenu-item submenu-new-message <?php echo esc_attr( $class_compose ); ?>">
					<a href="<?php echo esc_url( $url_compose ); ?>">
					<?php esc_html_e( 'New Message', 'CP_TD' ); ?>
					</a>
				</li>
			</ul>
		</div>
		<br clear="all"/>
		<?php
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
	public static function course_unit_single( $atts ) {
		global $wp;

		shortcode_atts(
			array( 'unit_id' => 0 ),
			$atts
		);

		$atts['unit_id'] = (int) $atts['unit_id'];

		if ( empty( $atts['unit_id'] ) ) {
			if ( array_key_exists( 'unitname', $wp->query_vars ) ) {
				$unit_name = $wp->query_vars['unitname'];
				$unit = new Unit(); // @check
				$atts['unit_id'] = $unit->get_unit_id_by_name( $unit_name );
			}
		}

		if ( empty( $atts['unit_id'] ) ) { return ''; }

		$args = array(
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post__in' => array( $atts['unit_id'] ),
			'post_status' => cp_can_see_unit_draft() ? 'any' : 'publish',
		);

		ob_start();
		query_posts( $args );
		ob_clean();
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
	public static function course_units_loop( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'course_id' => 0 ), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp', 0 ) );

		$args = array(
			'order' => 'ASC',
			'post_type' => CoursePress_Data_Unit::get_post_type_name(),
			'post_status' => ( cp_can_see_unit_draft() ? 'any' : 'publish' ),
			'meta_key' => 'unit_order',
			'orderby' => 'meta_value_num',
			'posts_per_page' => '-1',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
			),
		);

		query_posts( $args );
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
	public static function courses_loop( $atts ) {
		global $wp;

		if ( array_key_exists( 'course_category', $wp->query_vars ) ) {
			$page = ( isset( $wp->query_vars['paged'] ) ) ? $wp->query_vars['paged'] : 1;
			$query_args = array(
				'post_type' => CoursePress_Data_Course::get_post_type_name(),
				'post_status' => 'publish',
				'paged' => $page,
				'tax_query' => array(
					array(
						'taxonomy' => 'course_category',
						'field' => 'slug',
						'terms' => array( $wp->query_vars['course_category'] ),
					),
				),
			);

			$selected_course_order_by_type = get_option( 'course_order_by_type', 'DESC' );
			$selected_course_order_by = get_option( 'course_order_by', 'post_date' );

			if (  'course_order' == $selected_course_order_by ) {
				$query_args['meta_key'] = 'course_order';
				$query_args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key' => 'course_order',
						'compare' => 'NOT EXISTS',
					),
				);
				$query_args['orderby'] = 'meta_value';
				$query_args['order'] = $selected_course_order_by_type;
			} else {
				$query_args['orderby'] = $selected_course_order_by;
				$query_args['order'] = $selected_course_order_by_type;
			}

			query_posts( $query_args );
		}
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
	public static function course_notifications_loop( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'course_id' => 0 ), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$args = array(
			'category' => '',
			'order' => 'ASC',
			'post_type' => 'notifications',
			'post_mime_type' => '',
			'post_parent' => '',
			'post_status' => 'publish',
			'orderby' => 'meta_value_num',
			'posts_per_page' => '-1',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'course_id',
					'value' => $course_id,
				),
				array(
					'key' => 'course_id',
					'value' => '',
				),
			),
		);

		query_posts( $args );
	}

	/**
	 * @todo: THIS FUNCTION DOES NOT RETURN A VALUE!!
	 */
	public static function course_discussion_loop( $atts ) {
		global $wp;

		extract( shortcode_atts( array( 'course_id' => 0 ), $atts ) );

		$course_id = (int) $course_id;

		if ( empty( $course_id ) ) {
			if ( array_key_exists( 'coursename', $wp->query_vars ) ) {
				$course_id = Course::get_course_id_by_name( $wp->query_vars['coursename'] );
			} else {
				$course_id = 0;
			}
		}

		$args = array(
			'order' => 'DESC',
			'post_type' => 'discussions',
			'post_mime_type' => '',
			'post_parent' => '',
			'post_status' => 'publish',
			'posts_per_page' => '-1',
			'meta_key' => 'course_id',
			'meta_value' => $course_id,
		);

		query_posts( $args );
	}

	public static function course_signup( $atts ) {
		$allowed = array( 'signup', 'login' );

		extract( shortcode_atts(
			array(
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'failed_login_text' => __( 'Invalid login.', 'CP_TD' ),
				'failed_login_class' => 'red',
				'logout_url' => '',
				'signup_tag' => 'h3',
				'signup_title' => __( 'Signup', 'CP_TD' ),
				'login_tag' => 'h3',
				'login_title' => __( 'Login', 'CP_TD' ),
				'signup_url' => '',
				'login_url' => '',
				'redirect_url' => '', // Redirect on successful login or signup.
			),
			$atts,
			'course_signup'
		) );

		$failed_login_text = sanitize_text_field( $failed_login_text );
		$failed_login_class = sanitize_html_class( $failed_login_class );
		$logout_url = esc_url_raw( $logout_url );
		$signup_tag = sanitize_html_class( $signup_tag );
		$signup_title = sanitize_text_field( $signup_title );
		$login_tag = sanitize_html_class( $login_tag );
		$login_title = sanitize_text_field( $login_title );
		$signup_url = esc_url_raw( $signup_url );
		$redirect_url = esc_url_raw( $redirect_url );

		$page = in_array( $page, $allowed ) ? $page : 'signup';

		$signup_prefix = empty( $signup_url ) ? '&' : '?';
		$login_prefix = empty( $login_url ) ? '&' : '?';

		$signup_url = empty( $signup_url ) ? CoursePress_Core::get_slug( 'signup', true ) : $signup_url;
		$login_url = empty( $login_url ) ? CoursePress_Core::get_slug( 'login', true ) : $login_url;

		if ( ! empty( $redirect_url ) ) {
			$signup_url = $signup_url . $signup_prefix . 'redirect_url=' . urlencode( $redirect_url );
			$login_url = $login_url . $login_prefix . 'redirect_url=' . urlencode( $redirect_url );
		}
		if ( ! empty( $_POST['redirect_url'] ) ) {
			$signup_url = $signup_url . '?redirect_url=' . $_POST['redirect_url'];
			$login_url = $login_url . '?redirect_url=' . $_POST['redirect_url'];
		}

		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
		};

		$form_message = '';
		$form_message_class = '';

		// Attempt a login if submitted.
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {

			$auth = wp_authenticate_username_password( null, $_POST['log'], $_POST['pwd'] );
			if ( ! is_wp_error( $auth ) ) {
				$user = get_user_by( 'login', $_POST['log'] );
				$user_id = $user->ID;
				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );
				if ( ! empty( $redirect_url ) ) {
					wp_redirect( urldecode( esc_url_raw( $redirect_url ) ) );
				} else {
					wp_redirect( esc_url_raw( CoursePress_Core::get_slug( 'student_dashboard', true ) ) );
				}
				exit;
			} else {
				$form_message = $failed_login_text;
				$form_message_class = $failed_login_class;
			}
		}

		$content = '';
		switch ( $page ) {
			case 'signup':
				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Helper_Utility::users_can_register() ) {
						$form_message_class = '';
						$form_message = '';

						if ( isset( $_POST['student-settings-submit'] ) ) {
							check_admin_referer( 'student_signup' );
							$min_password_length = apply_filters( 'coursepress_min_password_length', 6 );

							$student_data = array();
							$form_errors = 0;

							do_action( 'coursepress_before_signup_validation' );

							$username = $_POST['username'];
							$firstname = $_POST['first_name'];
							$lastname = $_POST['last_name'];
							$email = $_POST['email'];
							$passwd = $_POST['password'];
							$passwd2 = $_POST['password_confirmation'];

							if ( $username && $firstname && $lastname && $email && $passwd && $passwd2 ) {

								if ( ! username_exists( $username ) ) {
									if ( ! email_exists( $email ) ) {
										if ( $passwd == $passwd2 ) {
											if ( ! preg_match( '#[0-9a-z]+#i', $passwd ) || strlen( $passwd ) < $min_password_length ) {
												$form_message = sprintf( __( 'Your password must be at least %d characters long and have at least one letter and one number in it.', 'CP_TD' ), $min_password_length );
												$form_message_class = 'red';
												$form_errors++;
											} else {

												if ( $_POST['password_confirmation'] ) {
													$student_data['user_pass'] = $_POST['password'];
												} else {
													$form_message = __( "Passwords don't match", 'CP_TD' );
													$form_message_class = 'red';
													$form_errors++;
												}
											}
										} else {
											$form_message = __( 'Passwords don\'t match', 'CP_TD' );
											$form_message_class = 'red';
											$form_errors++;
										}

										$student_data['role'] = get_option( 'default_role', 'subscriber' );
										$student_data['user_login'] = $_POST['username'];
										$student_data['user_email'] = $_POST['email'];
										$student_data['first_name'] = $_POST['first_name'];
										$student_data['last_name'] = $_POST['last_name'];

										if ( ! is_email( $_POST['email'] ) ) {
											$form_message = __( 'E-mail address is not valid.', 'CP_TD' );
											$form_message_class = 'red';
											$form_errors++;
										}

										if ( isset( $_POST['tos_agree'] ) ) {
											if ( ! cp_is_true( $_POST['tos_agree'] ) ) {
												$form_message = __( 'You must agree to the Terms of Service in order to signup.', 'CP_TD' );
												$form_message_class = 'red';
												$form_errors++;
											}
										}

										if ( ! $form_errors ) {

											$student_data = CoursePress_Helper_Utility::sanitize_recursive( $student_data );
											$student_id = wp_insert_user( $student_data );
											if ( ! empty( $student_id ) ) {
												CoursePress_Data_Student::send_registration( $student_id );

												$creds = array();
												$creds['user_login'] = $student_data['user_login'];
												$creds['user_password'] = $student_data['user_pass'];
												$creds['remember'] = true;
												$user = wp_signon( $creds, false );

												if ( is_wp_error( $user ) ) {
													$form_message = $user->get_error_message();
													$form_message_class = 'red';
												}

												if ( isset( $_POST['course_id'] ) && is_numeric( $_POST['course_id'] ) ) {
													$course = new Course( $_POST['course_id'] ); // @check
													wp_redirect( $course->get_permalink() );
												} else {
													if ( ! empty( $redirect_url ) ) {
														wp_redirect( esc_url_raw( apply_filters( 'coursepress_redirect_after_signup_redirect_url', $redirect_url ) ) );
													} else {
														wp_redirect( esc_url_raw( apply_filters( 'coursepress_redirect_after_signup_url', CoursePress_Core::get_slug( 'student_dashboard', true ) ) ) );
													}
												}
												exit;
											} else {
												$form_message = __( 'An error occurred while creating the account. Please check the form and try again.', 'CP_TD' );
												$form_message_class = 'red';
											}
										}
									} else {
										$form_message = __( 'Sorry, that email address is already used!', 'CP_TD' );
										$form_message_class = 'error';
									}
								} else {
									$form_message = __( 'Username already exists. Please choose another one.', 'CP_TD' );
									$form_message_class = 'red';
								}
							} else {
								$form_message = __( 'All fields are required.', 'CP_TD' );
								$form_message_class = 'red';
							}
						} else {
							$form_message = __( 'All fields are required.', 'CP_TD' );
						}

						if ( ! empty( $signup_title ) ) {
							$content .= '<' . $signup_tag . '>' . $signup_title . '</' . $signup_tag . '>';
						}

						$content .= '
							<p class="form-info-' . esc_attr( apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ) ) . '">' . esc_html( apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ) ) . '</p>
						';

						ob_start();
						do_action( 'coursepress_before_signup_form' );
						$content .= ob_get_clean();

						$content .= '
							<form id="student-settings" name="student-settings" method="post" class="student-settings signup-form">
						';

						ob_start();
						do_action( 'coursepress_before_all_signup_fields' );
						$content .= ob_get_clean();

						// First name
						$content .= '
							<input type="hidden" name="course_id" value="' . esc_attr( isset( $_GET['course_id'] ) ? $_GET['course_id'] : ' ' ) . '"/>
							<input type="hidden" name="redirect_url" value="' . esc_url( $redirect_url ) . '"/>
							<label class="firstname">
								<span>' . esc_html__( 'First Name', 'CP_TD' ) . ':</span>
								<input type="text" name="first_name" value="' . ( isset( $_POST['first_name'] ) ? esc_html( $_POST['first_name'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_first_name' );
						$content .= ob_get_clean();

						// Last name
						$content .= '
							<label class="lastname">
								<span>' . esc_html__( 'Last Name', 'CP_TD' ) . ':</span>
								<input type="text" name="last_name" value="' . ( isset( $_POST['last_name'] ) ? esc_attr( $_POST['last_name'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_last_name' );
						$content .= ob_get_clean();

						// Username.
						$content .= '
							<label class="username">
								<span>' . esc_html__( 'Username', 'CP_TD' ) . ':</span>
								<input type="text" name="username" value="' . ( isset( $_POST['username'] ) ? esc_attr( $_POST['username'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_username' );
						$content .= ob_get_clean();

						// Email.
						$content .= '
							<label class="email">
								<span>' . esc_html__( 'E-mail', 'CP_TD' ) . ':</span>
								<input type="text" name="email" value="' . ( isset( $_POST['email'] ) ? esc_attr( $_POST['email'] ) : '' ) . '"/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_email' );
						$content .= ob_get_clean();

						// Password.
						$content .= '
							<label class="password">
								<span>' . esc_html__( 'Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password" value=""/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_password' );
						$content .= ob_get_clean();

						// Confirm.
						$content .= '
							<label class="password-confirm right">
								<span>' . esc_html__( 'Confirm Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password_confirmation" value=""/>
							</label>
						';

						if ( shortcode_exists( 'signup-tos' ) ) {
							if ( get_option( 'show_tos', 0 ) == '1' ) {
								$content .= '<label class="tos full">';
								ob_start();
								echo do_shortcode( '[signup-tos]' );
								$content .= ob_get_clean();
								$content .= '</label>';
							}
						}

						ob_start();
						do_action( 'coursepress_after_all_signup_fields' );
						$content .= ob_get_clean();

						$content .= '
							<label class="existing-link full">
								' . sprintf( __( 'Already have an account? %s%s%s!', 'CP_TD' ), '<a href="' . esc_url( $login_url ) . '">', __( 'Login to your account', 'CP_TD' ), '</a>' ) . '
							</label>
							<label class="submit-link full-right">
								<input type="submit" name="student-settings-submit" class="apply-button-enrolled" value="' . esc_attr__( 'Create an Account', 'CP_TD' ) . '"/>
							</label>
						';

						ob_start();
						do_action( 'coursepress_after_submit' );
						$content .= ob_get_clean();

						$content .= wp_nonce_field( 'student_signup', '_wpnonce', true, false );
						$content .= '
							</form>
							<div class="clearfix" style="clear: both;"></div>
						';

						ob_start();
						do_action( 'coursepress_after_signup_form' );
						$content .= ob_get_clean();

					} else {
						$content .= __( 'Registrations are not allowed.', 'CP_TD' );
					}
				} else {

					if ( ! empty( $redirect_url ) ) {
						wp_redirect( esc_url_raw( urldecode( $redirect_url ) ) );
					} else {
						wp_redirect( esc_url_raw( CoursePress_Core::get_slug( 'student_dashboard', true ) ) );
					}
					exit;
				}
				break;

			case 'login':
				$content = '';

				if ( ! empty( $login_title ) ) {
					$content .= '<' . $login_tag . '>' . $login_title . '</' . $login_tag . '>';
				}

				$content .= '
					<p class="form-info-' . esc_attr( apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ) ) . '">' . esc_html( apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ) ) . '</p>
				';
				ob_start();
				do_action( 'coursepress_before_login_form' );
				$content .= ob_get_clean();
				$content .= '
					<form name="loginform" id="student-settings" class="student-settings login-form" method="post">
				';
				ob_start();
				do_action( 'coursepress_after_start_form_fields' );
				$content .= ob_get_clean();

				$content .= '
						<label class="username">
							<span>' . esc_html__( 'Username', 'CP_TD' ) . '</span>
							<input type="text" name="log" value="' . ( isset( $_POST['log'] ) ? esc_attr( $_POST['log'] ) : '' ) . '"/>
						</label>
						<label class="password">
							<span>' . esc_html__( 'Password', 'CP_TD' ) . '</span>
							<input type="password" name="pwd" value="' . ( isset( $_POST['pwd'] ) ? esc_attr( $_POST['pwd'] ) : '' ) . '"/>
						</label>

				';

				ob_start();
				do_action( 'coursepress_form_fields' );
				$content .= ob_get_clean();

				$content .= '
						<label class="signup-link full">
						' . ( CoursePress_Helper_Utility::users_can_register() ? sprintf( __( 'Don\'t have an account? %s%s%s now!', 'CP_TD' ), '<a href="' . $signup_url . '">', __( 'Create an Account', 'CP_TD' ), '</a>' ) : '' ) . '
						</label>
						<label class="forgot-link half-left">
							<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot Password?', 'CP_TD' ) . '</a>
						</label>
						<label class="submit-link half-right">
							<input type="submit" name="wp-submit" id="wp-submit" class="apply-button-enrolled" value="' . esc_attr__( 'Log In', 'CP_TD' ) . '"><br>
						</label>
						<input name="redirect_to" value="' . esc_url( CoursePress_Core::get_slug( 'student_dashboard', true ) ) . '" type="hidden">
						<input name="testcookie" value="1" type="hidden">
						<input name="course_signup_login" value="1" type="hidden">
				';

				ob_start();
				do_action( 'coursepress_before_end_form_fields' );
				$content .= ob_get_clean();

				$content .= '</form>';

				ob_start();
				do_action( 'coursepress_after_login_form' );
				$content .= ob_get_clean();

				break;
		}

		return $content;
	}

	public static function course_signup_form( $atts ) {
		$allowed = array( 'signup', 'login' );

		extract( shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'class' => '',
				'login_link_url' => '#',
				'login_link_id' => '',
				'login_link_class' => '',
				'login_link_label' => __( 'Already have an account? <a href="%s" class="%s" id="%s">Login to your account</a>!', 'CP_TD' ),
				'signup_link_url' => '#',
				'signup_link_id' => '',
				'signup_link_class' => '',
				'signup_link_label' => __( 'Don’t have an account? <a href="%s" class="%s" id="%s">Create an Account</a> now!', 'CP_TD' ),
				'forgot_password_label' => __( 'Forgot Password?', 'CP_TD' ),
				'submit_button_class' => '',
				'submit_button_attributes' => '',
				'submit_button_label' => '',
				'show_submit' => 'yes',
				'strength_meter_placeholder' => 'yes',
			),
			$atts,
			'course_signup_form'
		) );

		$course_id = (int) $course_id;
		$class = sanitize_text_field( $class );

		$login_link_id = sanitize_text_field( $login_link_id );
		$login_link_class = sanitize_text_field( $login_link_class );
		$login_link_url = esc_url_raw( $login_link_url );
		$login_link_url = ! empty( $login_link_url ) ? $login_link_url : '#' . $login_link_id;

		$login_link_label = sprintf( $login_link_label, $login_link_url, $login_link_class, $login_link_id );
		$signup_link_id = sanitize_text_field( $signup_link_id );
		$signup_link_class = sanitize_text_field( $signup_link_class );
		$signup_link_url = esc_url_raw( $signup_link_url );
		$signup_link_label = sprintf( $signup_link_label, $signup_link_url, $signup_link_class, $signup_link_id );
		$forgot_password_label = sanitize_text_field( $forgot_password_label );
		$submit_button_class = sanitize_text_field( $submit_button_class );
		$submit_button_attributes = sanitize_text_field( $submit_button_attributes );
		$submit_button_label = sanitize_text_field( $submit_button_label );

		$show_submit = cp_is_true( $show_submit );
		$strength_meter_placeholder = cp_is_true( $strength_meter_placeholder );

		$page = in_array( $page, $allowed ) ? $page : 'signup';

		$signup_prefix = empty( $signup_url ) ? '&' : '?';
		$login_prefix = empty( $login_url ) ? '&' : '?';

		$signup_url = CoursePress_Core::get_slug( 'signup', true );
		$login_url = CoursePress_Core::get_slug( 'login', true );
		$forgot_url = wp_lostpassword_url();

		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
		};

		$content = '';
		switch ( $page ) {
			case 'signup':
				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Helper_Utility::users_can_register() ) {
						$form_message_class = '';
						$form_message = '';

						ob_start();
						do_action( 'coursepress_before_signup_form' );
						$content .= ob_get_clean();

						$content .= '
							<form id="student-settings" name="student-settings" method="post" class="student-settings signup-form">
						';

						ob_start();
						do_action( 'coursepress_before_all_signup_fields' );
						$content .= ob_get_clean();

						if ( $strength_meter_placeholder ) {
							$content .= '<span id="error-messages"></span>';
						}

						// First name.
						$content .= '
							<input type="hidden" name="course_id" value="' . esc_attr( $course_id ) . '"/>
							<label class="firstname">
								<span>' . esc_html__( 'First Name', 'CP_TD' ) . ':</span>
								<input type="text" name="first_name" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_first_name' );
						$content .= ob_get_clean();

						// Last name.
						$content .= '
							<label class="lastname">
								<span>' . esc_html__( 'Last Name', 'CP_TD' ) . ':</span>
								<input type="text" name="last_name" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_last_name' );
						$content .= ob_get_clean();

						// Username.
						$content .= '
							<label class="username">
								<span>' . esc_html__( 'Username', 'CP_TD' ) . ':</span>
								<input type="text" name="username" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_username' );
						$content .= ob_get_clean();

						// Email.
						$content .= '
							<label class="email">
								<span>' . esc_html__( 'E-mail', 'CP_TD' ) . ':</span>
								<input type="text" name="email" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_email' );
						$content .= ob_get_clean();

						// Password.
						$content .= '
							<label class="password">
								<span>' . esc_html__( 'Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password" value=""/>
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_password' );
						$content .= ob_get_clean();

						// Confirm.
						$content .= '
							<label class="password-confirm right">
								<span>' . esc_html__( 'Confirm Password', 'CP_TD' ) . ':</span>
								<input type="password" name="password_confirmation" value=""/>
							</label>
						';

						if ( $strength_meter_placeholder ) {
							$content .= '<span id="password-strength"></span>';
						}

						if ( shortcode_exists( 'signup-tos' ) ) {
							if ( get_option( 'show_tos', 0 ) == '1' ) {
								$content .= '<label class="tos full">';
								ob_start();
								echo do_shortcode( '[signup-tos]' );
								$content .= ob_get_clean();
								$content .= '</label>';
							}
						}

						ob_start();
						do_action( 'coursepress_after_all_signup_fields' );
						$content .= ob_get_clean();

						$content .= '
							<label class="existing-link full">
								' . $login_link_label . '
							</label>
						';

						if ( $show_submit ) {
							$content .= '
							<label class="submit-link full-right">
								<input type="submit" ' . esc_attr( $submit_button_attributes ) . ' class="' . esc_attr( $course_id ) . '" value="' . esc_attr( $submit_button_label ) . '"/>
							</label>
							';
						}

						ob_start();
						do_action( 'coursepress_after_submit' );
						$content .= ob_get_clean();

						$content .= wp_nonce_field( 'student_signup', '_wpnonce', true, false );
						$content .= '
							</form>
							<div class="clearfix" style="clear: both;"></div>
						';

						ob_start();
						do_action( 'coursepress_after_signup_form' );
						$content .= ob_get_clean();

					} else {
						$content .= __( 'Registrations are not allowed.', 'CP_TD' );
					}
				}
				break;

			case 'login':
				$content = '';

				ob_start();
				do_action( 'coursepress_before_login_form' );
				$content .= ob_get_clean();
				$content .= '
					<form name="loginform" id="student-settings" class="student-settings login-form" method="post">
				';
				ob_start();
				do_action( 'coursepress_after_start_form_fields' );
				$content .= ob_get_clean();

				$content .= '
					<label class="username">
						<span>' . esc_html__( 'Username', 'CP_TD' ) . '</span>
						<input type="text" name="log" />
					</label>
					<label class="password">
						<span>' . esc_html__( 'Password', 'CP_TD' ) . '</span>
						<input type="password" name="pwd" />
					</label>';

				ob_start();
				do_action( 'coursepress_form_fields' );
				$content .= ob_get_clean();

				if ( apply_filters( 'coursepress_popup_allow_account', true ) ) {
					$content .= '
						<label class="existing-link full">
							' . $signup_link_label . '
						</label>
						<label class="forgot-link half-left">
							<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot Password?', 'CP_TD' ) . '</a>
						</label>';
				}

				if ( $show_submit ) {
					$content .= '
						<label class="submit-link full-right">
							<!--<input type="submit" ' . esc_attr( $submit_button_attributes ) . ' class="' . esc_attr( $course_id ) . '" value="' . esc_attr( $submit_button_label ) . '"/>-->
						</label>';
				}

				$content .= '
						<input name="testcookie" value="1" type="hidden">
						<input name="course_signup_login" value="1" type="hidden">
				';

				ob_start();
				do_action( 'coursepress_before_end_form_fields' );
				$content .= ob_get_clean();

				$content .= '</form>';

				ob_start();
				do_action( 'coursepress_after_login_form' );
				$content .= ob_get_clean();
				break;
		}

		return $content;
	}
}
