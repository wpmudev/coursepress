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
			/**
			 * Listen to signup form submission
			 **/
			add_action( 'after_setup_theme', array( __CLASS__, 'process_registration_form' ) );

			add_shortcode(
				'course_signup',
				array( __CLASS__, 'course_signup' )
			);
		}

		add_shortcode(
			'course_categories',
			array( __CLASS__, '_the_categories' )
		);

		add_shortcode(
			'messaging_submenu',
			array( __CLASS__, 'messaging_submenu' )
		);

		add_filter( 'term_link', array( __CLASS__, 'term_link' ), 10, 3 );

		add_action('coursepress_after_signup_email', array( 'CoursePress_Helper_UI', 'password_strength_meter'));
	}

	public static function course_archive( $a ) {
		global $wp;

		$a = shortcode_atts( array(
			'category' => CoursePress_Helper_Utility::the_course_category(),
			'posts_per_page' => 10,
			'show_pager' => true,
			'echo' => false,
			'courses_type' => 'current_and_upcoming',
		), $a, 'course_archive' );

		$category = sanitize_text_field( $a['category'] );
		$per_page = (int) $a['posts_per_page'];
		$show_pager = cp_is_true( $a['show_pager'] );
		$echo = cp_is_true( $a['echo'] );

		$paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 1;

		$post_args = array(
			'post_type' => CoursePress_Data_Course::get_post_type_name(),
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'meta_key' => 'cp_course_start_date',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'suppress_filters' => true,
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

		if ( ! empty( $a['courses_type'] ) && 'current_and_upcoming' == $a['courses_type'] ) {
			$query = CoursePress_Data_Course::current_and_upcoming_courses( $post_args );
		} else {
			$query = new WP_Query( $post_args );
		}

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
			'clickable_label' => __( 'Course Details', 'cp' ),
			'override_button_text' => '',
			'override_button_link' => '',
			'button_label' => __( 'Details', 'cp' ),
			'echo' => false,
			'show_withdraw_link' => false,
		), $a, 'course_list_box' );

		$course_id = (int) $a['course_id'];
		$clickable_label = sanitize_text_field( $a['clickable_label'] );
		$echo = cp_is_true( $a['echo'] );
		$clickable = cp_is_true( $a['clickable'] );
		$url = CoursePress_Data_Course::get_course_url( $course_id );

		$course_image = CoursePress_Data_Course::get_setting( $course_id, 'listing_image' );
		$has_thumbnail = ! empty( $course_image );

		$clickable_link = $clickable ? 'data-link="' . esc_url( $url ) . '"' : '';
		$clickable_class = $clickable ? 'clickable' : '';
		$clickable_text = $clickable ? '<div class="clickable-label">' . $clickable_label . '</div>' : '';
		$button_label = $a['button_label'];
		$button_link = $url;
		$withdraw_from_course = '';

		if ( ! empty( $a['override_button_link'] ) ) {
			$button_link = $a['override_button_link'];
		}

		$button_text = sprintf( '<a href="%s" rel="bookmark" class="button apply-button apply-button-details">%s</a>', esc_url( $button_link ), $button_label );
		$instructor_link = $clickable ? 'no' : 'yes';
		$thumbnail_class = $has_thumbnail ? 'has-thumbnail' : '';

		$completed = false;
		$student_progress = false;
		if ( is_user_logged_in() ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( get_current_user_id(), $course_id );
			$completed = isset( $student_progress['completion']['completed'] ) && ! empty( $student_progress['completion']['completed'] );
			/**
			 * Withdraw from course
			 */
			$show_withdraw_link = cp_is_true( $a['show_withdraw_link'] );
			if ( $show_withdraw_link && ! $completed ) {
				$withdraw_link = add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( 'coursepress_student_withdraw' ),
						'course_id' => $course_id,
						'student_id' => get_current_user_id(),
					)
				);
				$withdraw_from_course = sprintf( '<a href="%s" class="cp-withdraw-student">%s</a>', esc_url( $withdraw_link ), __( 'Withdraw', 'cp' ) );
			}
		}
		$completion_class = CoursePress_Data_Course::course_class( $course_id );
		$completion_class = implode( ' ', $completion_class );

		// Add filter to post classes

		// Override button
		if ( ! empty( $a['override_button_text'] ) && ! empty( $a['override_button_link'] ) ) {
			$button_text = '<button class="coursepress-course-link" data-link="' . esc_url( $a['override_button_link'] ) . '">' . esc_attr( $a['override_button_text'] ) . '</button>';
		}

		/**
		 * schema.org
		 */
		$schema = apply_filters( 'coursepress_schema', '', 'itemscope' );
		$course_title = do_shortcode( sprintf( '[course_title course_id="%s"]', $course_id ) );
		$course_title = sprintf( '<a href="%s" rel="bookmark">%s</a>', esc_url( $url ), $course_title );

		$template = '<div class="course course_list_box_item course_' . $course_id . ' ' . $clickable_class . ' ' . $completion_class . ' ' . $thumbnail_class . '" ' . $clickable_link . ' ' . $schema .'>
			[course_thumbnail course_id="' . $course_id . '"]
			<div class="course-information">
				' . $course_title . '
				[course_summary course_id="' . $course_id . '"]
				[course_instructors style="list-flat" link="' . $instructor_link . '" course_id="' . $course_id . '"]
				<div class="course-meta-information">
					[course_start label="" course_id="' . $course_id . '"]
					[course_language label="" course_id="' . $course_id . '"]
					[course_cost label="" course_id="' . $course_id . '"]
					[course_categories course_id="' . $course_id . '"]
'.$withdraw_from_course.'
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

	public static function _the_categories( $atts ) {
		$atts = shortcode_atts(
			array(
				'course_id' => CoursePress_Helper_Utility::the_course( true ),
				'before' => '',
				'after' => ', ',
				'icon' => '<span class="dashicons dashicons-category"></span>',
			),
			$atts,
			'course_categories'
		);

		$categories = self::the_categories( $atts['course_id'], $atts['before'], $atts['after'] );

		if ( ! empty( $categories ) ) {
			$format = '<div class="course-category course-category-%s">%s %s</div>';
			$categories = sprintf( $format, $atts['course_id'], $atts['icon'], $categories );
		}
		return $categories;
	}

	public static function the_categories( $course_id, $before = '', $after = '' ) {
		$taxonomy = CoursePress_Data_Course::get_post_category_name();
		$terms = wp_get_object_terms( (int) $course_id, array( $taxonomy ) );

		if ( ! empty( $terms ) ) {
			$links = array();

			foreach ( $terms as $term ) {
				$link = get_term_link( $term->term_id, $taxonomy );
				$links[] = sprintf( '<a href="%s">%s</a>', esc_url( $link ), $term->name );
			}

			$links = $before . implode( $after . $before, $links );

			return $links;
		}

		return '';
	}

	public static function course_page( $a ) {
		$a = shortcode_atts( array(
			'course_id' => CoursePress_Helper_Utility::the_course( true ),
			'echo' => false,
		), $a, 'course_page' );

		$course_id = (int) $a['course_id'];
		$echo = cp_is_true( $a['echo'] );

		/**
		 * schema.org
		 */
		$schema = apply_filters( 'coursepress_schema', '', 'itemscope' );

		$template = '<div class="course-wrapper"'.$schema.'>
			[course_media course_id="' . $course_id . '"]
			[course_social_links course_id="' . $course_id . '"]
			[course_enroll_box course_id="' . $course_id . '"]
			[course_instructors course_id="' . $course_id . '" avatar_position="top" summary_length="50" link_all="yes" link_text=""]
			[course_description label="' . __( 'About this course', 'cp' ) . '" course_id="' . $course_id . '"]
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
			'heading_title' => __( 'Courses', 'cp' ),
		), $a, 'instructor_page' );

		$instructor_id = (int) $a['instructor_id'];
		if ( empty( $instructor_id ) ) { return ''; }

		$echo = cp_is_true( $a['echo'] );

		/**
		 * schema.org
		 */
		$schema = apply_filters( 'coursepress_schema', '', 'itemscope-person' );

		$template = '<div class="instructor-wrapper"'.$schema.'>
			[course_instructor_avatar instructor_id="' . $instructor_id . '" force_display="true" thumb_size="200"]';
		/**
		 * schema.org
		 */
		$schema = apply_filters( 'coursepress_schema', '', 'description' );

		$template .= '<div class="instructor-bio"'.$schema.'>' . CoursePress_Helper_Utility::filter_content( get_user_meta( $instructor_id, 'description', true ) ) . '</div>
			<h3 class="courses-title">' . $a['heading_title'] . '</h3>
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
			[course_list instructor="%1$s" dashboard="true"]
			[course_list facilitator="%1$s" dashboard="true"]
			[course_list student="%1$s" dashboard="true" current_label="%2$s" show_labels="true"]
		</div>';

		$template = sprintf( $template, $user_id, __( 'Enrolled Courses', 'cp' ) );

		$template = apply_filters( 'coursepress_template_dashboard_page', $template, $user_id, $a );

		$content = do_shortcode( $template );

		if ( $echo ) {
			echo $content;
		}

		return $content;
	}

	public static function coursepress_focus_item( $a ) {
		$a = shortcode_atts(
			array(
				'course' => '',
				'unit' => '',
				'type' => '',
				'item_id' => 0,
				'pre_text' => __( '&laquo; Previous', 'cp' ),
				'next_text' => __( 'Next &raquo;', 'cp' ),
				'next_section_title' => __( 'Proceed to the next section', 'cp' ),
				'next_module_title' => __( 'Proceed to the next module', 'cp' ),
				'next_section_text' => __( 'Next Section', 'cp' ),
				'echo' => false,
			),
			$a,
			'coursepress_focus_item'
		);

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
		$next_module_title = sanitize_text_field( $a['next_module_title'] );
		$next_section_title = sanitize_text_field( $a['next_section_title'] );
		$next_section_text = sanitize_text_field( $a['next_section_text'] );

		$titles = get_post_meta( $unit_id, 'page_title', true );
		$preview = CoursePress_Data_Course::previewability( $course_id );
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$page_count = count( $titles );

		$can_view = true;
		$student_progress = false;
		$student_id = get_current_user_id();
		$is_enrolled = CoursePress_Data_Course::student_enrolled( $student_id, $course_id );
		if ( $is_enrolled ) {
			$student_progress = CoursePress_Data_Student::get_completion_data( $student_id, $course_id );
		}
		$instructors = array_filter( CoursePress_Data_Course::get_instructors( $course_id ) );
		$is_instructor = in_array( $student_id, $instructors );

		/**
		 * validate module exists?
		 */
		$module = '';
		if ( 'module' === $type ) {
			$module = get_post( $item_id );
			if ( ! is_object( $module ) ) {
				$item_id = 0;
				$type = '404';
			} else if ( $module->post_parent != $unit_id ) {
				$item_id = 0;
				$type = '404_module';
			}
		}

		$breadcrumbs = true; // This is always true, maybe add a filter/shortcode param?
		$breadcrumb_trail = '';
		$u_link_url = '';
		$bcs = '<span class="breadcrumb-milestone"></span>'; // Breadcrumb Separator
		$progress_spinner = '<span class="loader hidden"><i class="fa fa-spinner fa-pulse"></i></span>';

		if ( 'section' === $type ) {
			$page = $item_id;

			if ( CoursePress_Data_Course::get_setting( $course_id, 'focus_hide_section', false ) ) {
				$next_modules = CoursePress_Data_Course::get_unit_modules(
					$unit_id,
					array( 'publish' ),
					true,
					false,
					array( 'page' => $page )
				);
				$mod = 0;
				if ( ! empty( $next_modules ) ) {
					$mod = (int) $next_modules[0];
					$page = (int) get_post_meta( $mod, 'module_page', true );
					$type = 'module';
				}

				// "Redirect" to module.
				$item_id = $mod;

				if ( empty( $mod ) ) {
					$type = 'no_access';
				} elseif ( ! CoursePress_Data_Course::can_view_module( $course_id, $unit_id, $mod, $page ) ) {
					$type = 'no_access';
				}
			}
			if ( 0 === $unit_id ) {
				$type = '404';
			}
		} else {
			// Get page from module meta.
			$page = self::get_module_page( $course_id, $unit_id, $item_id ); //get_post_meta( $item_id, 'module_page', true );
		}

		$page_info = CoursePress_Data_Unit::get_page_meta( $unit_id, $page );

		if ( $breadcrumbs ) {
			// Course.
			$c_link = CoursePress_Data_Course::get_course_url( $course_id );
			$a_link = $c_link . CoursePress_Core::get_slug( 'units/' );
			$u_post_name = get_post_field( 'post_name', $unit_id );
			$u_link = $a_link . ( ! $u_post_name ? $unit_id : $u_post_name );

			$c_link = '<a href="' . esc_url( $c_link ) . '" class="breadcrumb-course crumb">' . get_post_field( 'post_title', $course_id ) . '</a>';
			$a_link = '<a href="' . esc_url( $a_link ) . '" class="breadcrumb-course-units crumb">' . esc_html__( 'Units', 'cp' ) . '</a>';
			$u_link_url = $u_link;
			$u_link = '<a href="' . esc_url( $u_link ) . '/page/1" class="breadcrumb-course-unit crumb" data-id="1">' . get_post_field( 'post_title', $unit_id ) . '</a>';

			$breadcrumb_trail = $c_link . $bcs . $a_link . $bcs . $u_link;
		}

		if ( ! $is_enrolled && ! $is_instructor ) {
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
		/**
		 * view type
		 */
		$view_type = 'normal';
		/**
		 * Can be preview?
		 */
		$can_be_previewed = false;
		if ( 'module' == $type && ! $can_view && ! $can_update_course ) {
			$can_be_previewed = CoursePress_Data_Module::can_be_previewed( $item_id );
			$view_type = 'preview';
		}
		/**
		 * sanitize type
		 */
		$type = $can_be_previewed || $can_view || $can_update_course ? $type : 'no_access';
		$template = '';

		// Get restriction message when applicable.
		$error_message = CoursePress_Data_Course::can_access( $course_id, $unit_id, $item_id, $student_id, $page, $type );

		switch ( $type ) {
			case 'section':
				if ( $is_enrolled ) {
					CoursePress_Data_Student::visited_page(
						$student_id,
						$course_id,
						$unit_id,
						$page,
						$student_progress
					);
				}

				$is_course_available = CoursePress_Data_Course::is_course_available( $course_id );
				$is_unit_available = $is_course_available ? CoursePress_Data_Unit::is_unit_available( $course_id, $unit_id, null ) : $is_course_available;

				$breadcrumb_trail .= '<span class="breadcrumb-leaf">' . $bcs . '<span class="breadcrumb-course-unit-section crumb end">' . esc_html( $page_info['title'] ) . '</span></span>';

				$content = '<div class="focus-wrapper">';

				$content .= '<div class="focus-main section">';

					$template = '<div class="focus-item focus-item-' . esc_attr( $type ) . '">';

				if ( empty( $error_message ) ) {
					if ( ! empty( $page_info['feature_image'] ) ) {
						$feature_image = sprintf( '<img src="%s" alt="%s" />', esc_url( $page_info['feature_image'] ), esc_attr( basename( $page_info['feature_image'] ) ) );
						$template .= '<div class="section-thumbnail">' . $feature_image . '</div>';
					}

					$template .= '<h3>'. $page_info['title'] . '</h3>';

					if ( ! empty( $page_info['description'] ) ) {
						$template .= wpautop( htmlspecialchars_decode( $page_info['description'] ) );
					}
				} else {
					// Show restriction message
					$content .= sprintf( '<div class="focus-item focus-item-'. esc_attr( $type ) . '">%s</div>', $error_message );
				}

					$template .= '</div>';

					$template = apply_filters( 'coursepress_template_focus_item_section', $template, $a );

					$content .= $template;

					$content .= '</div>'; // .focus-main

					$next = CoursePress_Data_Course::get_next_accessible_module(
						$course_id,
						$unit_id,
						$page,
						false
					);
					$prev = CoursePress_Data_Course::get_prev_accessible_module(
						$course_id,
						$unit_id,
						$page,
						false
					);

					if ( ! empty( $error_message ) ) {
						$next = array(
							'id' => '',
							'not_done' => true,
						);
					}

					if ( $is_enrolled || $can_update_course ) {
						$content .= '<div class="focus-nav">';
						// Previous Navigation
						$content .= self::show_nav_button(
							$prev,
							$pre_text,
							array( 'focus-nav-prev' ),
							'',
							false,
							'prev'
						);

						// Next Navigation
						$content .= self::show_nav_button(
							$next,
							$next_text,
							array( 'focus-nav-next' ),
							$next_section_title,
							false,
							'next'
						);

						$content .= '</div>'; // .focus-nav
					}

					$content .= '</div>'; // .focus-wrapper

					$template = $content;
				break;

			case 'module':
				if ( $is_enrolled ) {
					CoursePress_Data_Student::visited_module(
						$student_id,
						$course_id,
						$unit_id,
						$item_id,
						$student_progress
					);
				}

				// Title retrieved below
				$breadcrumb_trail .= $bcs . '<a href="' . esc_url( $u_link_url ) . '/page/' . $page . '" class="breadcrumb-course-unit-section crumb" data-id="' . $page . '">' . $page_info['title'] . '</a>';

				$modules_status = $can_update_course ? 'any' : array( 'publish' );

				$modules = CoursePress_Data_Course::get_unit_modules( $unit_id, $modules_status, true, false, array( 'page' => $page ) );

				// Navigation Vars
				$module_index = array_search( $item_id, $modules );

				$next = CoursePress_Data_Course::get_next_accessible_module(
					$course_id,
					$unit_id,
					$page,
					$item_id
				);

				$prev = CoursePress_Data_Course::get_prev_accessible_module(
					$course_id,
					$unit_id,
					$page,
					$item_id
				);

				$breadcrumb_trail .= '<span class="breadcrumb-leaf">' . $bcs . '<span class="breadcrumb-course-unit-section-module crumb end">' . esc_html( get_post_field( 'post_title', $modules[ $module_index ] ) ) . '</span></span>';

				// Show the next section if this is the last module
				$module = get_post( $item_id );
				$attributes = CoursePress_Data_Module::attributes( $module );
				$attributes['course_id'] = $course_id;
				$is_mandatory = ! empty( $attributes['mandatory'] ) && cp_is_true( $attributes['mandatory'] );
				$is_assessable = ! empty( $attributes['assessable'] ) && cp_is_true( $attributes['assessable'] );

				// Get completion states
				$module_seen = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'completion/' . $unit_id . '/modules_seen/' . $item_id
				);
				$module_passed = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'completion/' . $unit_id . '/passed/' . $item_id
				);
				$module_answered = CoursePress_Helper_Utility::get_array_val(
					$student_progress,
					'completion/' . $unit_id . '/answered/' . $item_id
				);

				$focus_class = array();
				if ( ! empty( $module_seen ) ) { $focus_class[] = 'module-seen'; }
				if ( ! empty( $module_passed ) && $attributes['assessable'] ) { $focus_class[] = 'module-passed'; }
				if ( ! empty( $module_answered ) && $attributes['mandatory'] ) { $focus_class[] = 'module-answered'; }
				if ( ! empty( $module_passed ) && $attributes['assessable'] && $attributes['mandatory'] ) {
					$focus_class[] = 'module-completed';
				} elseif ( isset( $module_passed ) && ! empty( $module_answered ) && ! $attributes['assessable'] && $attributes['mandatory'] ) {
					$focus_class[] = 'module-completed';
				}

				$content = '<div class="focus-wrapper">';

				// Main content
				$content .= '<div class="focus-main ' . implode( ' ', $focus_class ) . '">';

				$method = 'template';
				$template = 'CoursePress_Template_Module';
				$next_module_class = array( 'focus-nav-next' );

				// Make sure we're allowed to move on
				if ( 'input-quiz' == $attributes['module_type'] && $is_mandatory ) {
					$quiz_result = CoursePress_Data_Module::get_quiz_results(
						$student_id,
						$course_id,
						$unit_id,
						$module->ID
					);
				}

				$preview_modules = array();
				if ( isset( $preview['structure'][ $unit_id ][ $page ] ) && is_array( $preview['structure'][ $unit_id ][ $page ] ) ) {
					$preview_modules = array_keys( $preview['structure'][ $unit_id ][ $page ] );
				}
				$can_preview_module = in_array( $module->ID, $preview_modules ) || ( isset( $preview['structure'][ $unit_id ] ) && ! is_array( $preview['structure'][ $unit_id ] ) );

				if ( ! empty( $error_message ) ) {
					$content .= sprintf( '<div class="focus-item focus-item-section">%s</div>', $error_message );
					$next = array(
						'id' => '',
						'not_done' => true,
					);
				} else {
					$content .= call_user_func( array( $template, $method ), $module->ID, true, $view_type );
				}

				$content .= '</div>'; // .focus-main

				/**
				 * Navigation
				 */
				if ( 'normal' == $view_type && ( $is_enrolled || $can_update_course ) ) {
					$content .= '<div class="focus-nav">';

					// Previous Navigation
					$content .= self::show_nav_button(
						$prev,
						$pre_text,
						array( 'focus-nav-prev' ),
						'',
						false,
						'prev'
					);

					// Next Navigation
					if ( ! empty( $next['type'] ) && 'section' == $next['type'] ) {
						$next_module_class[] = 'next-section';
						$title = '';
						$text = $next_section_text;
					} else {
						$title = $next_module_title;
						$text = $next_text;
					}

					if ( ! empty( $next['not_done'] ) ) {
						// Student has to complete current module first...
						$next_module_class[] = 'module-is-not-done';
						$content .= self::tpl_mandatory_not_completed();
						$title = __( 'You need to complete this REQUIRED module before you can continue.', 'cp' );
					}

					$content .= self::show_nav_button(
						$next,
						$text,
						$next_module_class,
						$title,
						true,
						'next'
					);

					$content .= '</div>'; // .focus-nav
				}
				$content .= '</div>'; // .focus-wrapper

				$template = sprintf( '<form method="post" enctype="multipart/form-data" class="cp cp-form">%s</form>', $content );
				$template = apply_filters( 'coursepress_focus_mode_module_template', $template, $content, $item_id );

				break;

			case 'no_access':
				$content = do_shortcode( '[coursepress_enrollment_templates]' );
				$content .= '<div class="focus-wrapper">';
				$content .= '<div class="focus-main section">';

				$content .= '<div class="no-access-message">' . __( 'You do not currently have access to this part of the course. Signup now to get full access to the course.', 'cp' ) . '</div>';
				$content .= do_shortcode( '[course_join_button course_id="' . $course_id . '"]' );

				$content .= '</div>'; // .focus-main
				$content .= '</div>'; // .focus-wrapper

				$template = apply_filters( 'coursepress_no_access_message', $content, $course_id, $unit_id );
				break;

			case '404':
			case '404_module':

				$content = do_shortcode( '[coursepress_enrollment_templates]' );
				$content .= '<div class="focus-wrapper">';
				$content .= '<div class="focus-main section">';
				$content .= '<div class="no-access-message"><p>';
				switch ( $type ) {
					case '404':
						$content .= __( 'This unit does not exist.', 'cp' );
					break;
					case '404_module':
						$content .= __( 'This module does not exist.', 'cp' );
					break;
				}
				$content .= '</p></div>';
				$content .= do_shortcode(
					sprintf(
						'[course_join_button course_id="%s" details_text="%s"]',
						esc_attr( $course_id ),
						esc_attr__( 'Show course details', 'cp' )
					)
				);
				$content .= '</div>'; // .focus-main
				$content .= '</div>'; // .focus-wrapper

				$template = apply_filters( 'coursepress_404_message', $content, $course_id, $unit_id );

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

	/**
	 * Returns the JS template for a popup: "Mandatory module not completed yet"
	 *
	 * @since  2.0.0
	 * @return string JS template code.
	 */
	protected static function tpl_mandatory_not_completed() {
		ob_start();
		?>
		<script type="text/template" id="modal-template">
			<div class="enrollment-modal-container"></div>
		</script>
		<script type="text/template" id="modal-next-step-is-mandatory" data-type="modal-step" data-modal-action="mandatory">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php _e( 'This is a REQUIRED module.', 'cp' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
<?php
		$first_line = __( 'You need to complete this REQUIRED module before you can continue.', 'cp' );
		echo CoursePress_Helper_UI::get_message_required_modules( $first_line );
?>
			</div>
			<div class="bbm-modal__bottombar"><a class="cancel-link"><?php _e( 'Close', 'cp' ); ?></a></div>
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate HTML code for a navigation button (prev/next)
	 *
	 * @since  2.0.0
	 * @since  2.0.8 Added the 'rel' attribute.
	 *
	 * @param  array  $button Result of ::get_next_accessible_module().
	 * @param  string $title Link title.
	 * @param  array  $classes List of CSS classes of the button.
	 * @param  string $link_title Tooltip title of the link.
	 * @param  string $rel Rel attribute.
	 * @return string HTML code of the button.
	 */
	public static function show_nav_button( $button, $title, $classes, $link_title = '', $next = false, $rel = '' ) {
		$res = '';

		/**
		 * The rel attribute
		 */
		$rel_attribute = '';
		if ( ! empty( $rel ) ) {
			$allowed = array( 'alternate', 'author', 'bookmark', 'external', 'help', 'license', 'next', 'nofollow', 'noreferrer', 'noopener', 'prev', 'search', 'tag' );
			if ( in_array( $rel, $allowed ) ) {
				$rel_attribute = sprintf( ' rel="%s"', esc_attr( $rel ) );
			}
		}

		if ( $button['id'] ) {
			$c = is_array( $classes ) ? implode( ' ', $classes ) : $classes;
			if ( $next ) {
				if ( 'completion_page' == $button['id'] ) {
					$title = __( 'Finish', 'cp' );
				}
				$format = '<button type="submit" name="type-%s" class="button %s" title="%s" data-url="%s"%s>%s</button>';
				$res = sprintf( $format,
					$button['type'],
					esc_attr( $c ),
					esc_attr( $link_title ),
					esc_url( $button['url'] ),
					$rel_attribute,
					$title
				);
			} else {
				$res = sprintf(
					'<button type="button" class="button %5$s" data-course="%8$s" data-id="%1$s" data-type="%2$s" data-unit="%4$s" data-title="%6$s" data-url="%7$s"%9$s>%3$s</button>',
					esc_attr( $button['id'] ),
					esc_attr( $button['type'] ),
					$title,
					esc_attr( $button['unit'] ),
					esc_attr( $c ),
					esc_attr( $link_title ),
					isset( $button['url'] )? esc_url( $button['url'] ) : '',
					isset( $button['course_id'] )?  $button['course_id'] : 0,
					$rel_attribute
				);
			}
		} else {
			$res = sprintf(
				'<div class="%2$s" data-title="%3$s"><span title="%3$s">%1$s</span></div>',
				$title,
				esc_attr( implode( ' ', $classes ) ),
				esc_attr( $link_title )
			);
		}
		/**
		 * Allow to change nex/prev buttons content.
		 *
		 * @since 2.0.6
		 *
		 * @param string $res HTML code of the button.
		 * @param  array  $button Result of ::get_next_accessible_module().
		 * @param  string $title Link title.
		 * @param  array  $classes List of CSS classes of the button.
		 * @param  string $link_title Tooltip title of the link.
		 */
		return apply_filters( 'coursepress_data_shortcode_template_show_nav_button', $res, $button, $title, $classes, $link_title, $next );
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
		ob_start();
		extract( shortcode_atts(
			array(
				'page' => '',
			),
			$atts
		) );

		switch ( $page ) {
			case 'enrollment_process':
				CoursePress_View_Front_Student::render_enrollment_process_page();
				break;

			case 'student_login':
				CoursePress_View_Front_Login::render_student_login_page();
				break;

			case 'student_signup':
				CoursePress_View_Front_Signup::render_student_signup_page();
				break;

			case 'student_dashboard':
				CoursePress_View_Front_Student::render_student_dashboard_page( false, $atts );
				break;

			case 'student_settings':
				CoursePress_View_Front_Student::render_student_settings_page();
				break;

			default:
				_e( 'Page cannot be found', 'cp' );
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
						esc_html_e( 'Inbox', 'cp' );
						echo $unread_display;
						?>
					</a></li>
				<li class="submenu-item submenu-sent-messages <?php echo esc_attr( $class_messages ); ?>">
					<a href="<?php echo esc_url( $url_messages ); ?>">
					<?php esc_html_e( 'Sent', 'cp' ); ?>
					</a>
				</li>
				<li class="submenu-item submenu-new-message <?php echo esc_attr( $class_compose ); ?>">
					<a href="<?php echo esc_url( $url_compose ); ?>">
					<?php esc_html_e( 'New Message', 'cp' ); ?>
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
	 * Helper function to load registration process/validation if user is not logged-in.
	 **/
	public static function process_registration_form() {
		if ( ! is_user_logged_in() ) {
			CoursePress_UserLogin::process_registration_form();
		}
	}

	public static function course_signup( $atts ) {

		if ( is_user_logged_in() ) {
			return __( 'You are already logged in.', 'cp' );
		}

		$allowed = array( 'signup', 'login' );

		extract(
			shortcode_atts(
				array(
					'failed_login_class' => 'red',
					'failed_login_text' => __( 'Invalid username or password.', 'cp' ),
					'login_tag' => 'h3',
					'login_title' => __( 'Login', 'cp' ),
					'login_url' => '',
					'logout_url' => '',
					'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
					'redirect_url' => '', // Redirect on successful login or signup.
					'signup_tag' => 'h3',
					'signup_title' => __( 'Signup', 'cp' ),
					'signup_url' => '',
				),
				$atts,
				'course_signup'
			)
		);

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

		$form_message = '';
		$form_message_class = '';

		$content = '';
		switch ( $page ) {
			case 'signup':
				if ( ! is_user_logged_in() ) {
					if ( CoursePress_Helper_Utility::users_can_register() ) {
						$content .= CoursePress_UserLogin::get_registration_form( $redirect_url, $login_url, $signup_title, $signup_tag );
					} else {
						$content .= __( 'Registrations are not allowed.', 'cp' );
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
				$content = '<div class="coursepress-form coursepress-form-login">';
				if ( ! empty( $login_title ) ) {
					$content .= '<' . $login_tag . '>' . $login_title . '</' . $login_tag . '>';
				}
				// Attempt a login if submitted.
				if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {
					if ( apply_filters( 'cp_course_signup_form_show_messages', false ) ) {
						$form_message = $failed_login_text;
						$form_message_class = $failed_login_class;
					}
				}
				$content .= '<p class="form-info-' . esc_attr( apply_filters( 'signup_form_message_class', sanitize_text_field( $form_message_class ) ) ) . '">' . esc_html( apply_filters( 'signup_form_message', sanitize_text_field( $form_message ) ) ) . '</p>';
				ob_start();
				do_action( 'coursepress_before_login_form' );
				$content .= ob_get_clean();
				$content .= '<form name="loginform" id="student-settings" class="student-settings login-form" method="post">';
				ob_start();
				do_action( 'coursepress_after_start_form_fields' );
				$content .= ob_get_clean();
				/**
				 * Username
				 */
				$content .= '<label class="username">';
				$content .= '<span>' . esc_html__( 'Username or Email Address', 'cp' ) . '</span>';
				$content .= '<input type="text" name="log" value="' . ( isset( $_POST['log'] ) ? esc_attr( $_POST['log'] ) : '' ) . '"/>';
				$content .= '</label>';
				/**
				 * password
				 */
				$content .= '<label class="password">';
				$content .= '<span>' . esc_html__( 'Password', 'cp' ) . '</span>';
				$content .= '<input type="password" name="pwd" value="' . ( isset( $_POST['pwd'] ) ? esc_attr( $_POST['pwd'] ) : '' ) . '"/>';
				$content .= '</label>';
				ob_start();
				do_action( 'coursepress_form_fields' );
				$content .= ob_get_clean();

				$redirect_to = CoursePress_Core::get_slug( 'student_dashboard', true );
				if ( isset( $_REQUEST['redirect_to'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
					if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'redirect_to' ) ) {
						$redirect_to = $_REQUEST['redirect_to'];
					}
				}

				$content .= '<label class="signup-link full">' . ( CoursePress_Helper_Utility::users_can_register() ? sprintf( __( 'Don\'t have an account? %s%s%s now!', 'cp' ), '<a href="' . $signup_url . '">', __( 'Create an Account', 'cp' ), '</a>' ) : '' ) . '</label>';
				$content .= '<label class="forgot-link half-left"><a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot Password?', 'cp' ) . '</a></label>';
				$content .= '<label class="submit-link half-right"><input type="submit" name="wp-submit" id="wp-submit" class="apply-button-enrolled" value="' . esc_attr__( 'Log In', 'cp' ) . '"></label>';
				$content .= '<input name="redirect_to" value="' . esc_url( $redirect_to ) . '" type="hidden">';
				$content .= '<input name="testcookie" value="1" type="hidden">';
				$content .= '<input name="course_signup_login" value="1" type="hidden">';

				ob_start();
				do_action( 'coursepress_before_end_form_fields' );
				$content .= ob_get_clean();

				$content .= '</form>';

				ob_start();
				do_action( 'coursepress_after_login_form' );
				$content .= ob_get_clean();
				$content .= '</div>';

				break;
		}

		return $content;
	}

	public static function cookie_test() {
		// Set a cookie now to see if they are supported by the browser.
		setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
		};
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
				'login_link_label' => __( 'Already have an account? <a href="%s" class="%s" id="%s">Login to your account</a>!', 'cp' ),
				'signup_link_url' => '#',
				'signup_link_id' => '',
				'signup_link_class' => '',
				'signup_link_label' => __( 'Don\'t have an account? <a href="%s" class="%s" id="%s">Create an Account</a> now!', 'cp' ),
				'forgot_password_label' => __( 'Forgot Password?', 'cp' ),
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

						// First name.
						$content .= '
							<input type="hidden" name="course_id" value="' . esc_attr( $course_id ) . '"/>
							<label class="firstname">
								<span>' . esc_html__( 'First Name', 'cp' ) . ':</span>
								<input type="text" name="first_name" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_first_name' );
						$content .= ob_get_clean();

						// Last name.
						$content .= '
							<label class="lastname">
								<span>' . esc_html__( 'Last Name', 'cp' ) . ':</span>
								<input type="text" name="last_name" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_last_name' );
						$content .= ob_get_clean();

						// Username.
						$content .= '
							<label class="username">
								<span>' . esc_html__( 'Username', 'cp' ) . ':</span>
								<input type="text" name="username" />
							</label>
						';
						ob_start();
						do_action( 'coursepress_after_signup_username' );
						$content .= ob_get_clean();

						// Email.
						$content .= '<label class="email">';
						$content .= '<span>' . esc_html__( 'E-mail', 'cp' ) . ':</span>';
						$content .= '<input type="text" name="email" />';
						$content .= '</label> ';
						ob_start();
						do_action( 'coursepress_after_signup_email' );
						$content .= ob_get_clean();

						// Password.
						$content .= ' <label class="password">';
						$content .= '<span>' . esc_html__( 'Password', 'cp' ) . ':</span>';
						$content .= '<input type="password" name="password" value=""/>';
						$content .= '</label> ';
						ob_start();
						do_action( 'coursepress_after_signup_password' );
						$content .= ob_get_clean();

						// Confirm.
						$content .= ' <label class="password-confirm right">';
						$content .= '<span>' . esc_html__( 'Confirm Password', 'cp' ) . ':</span>';
						$content .= '<input type="password" name="password_confirmation" value=""/>';
						$content .= '</label> ';

						if ( $strength_meter_placeholder ) {
							$content .= '<span id="password-strength"></span>';
						}
						$content .= '<label class="weak-password-confirm">';
						$content .= '<input type="checkbox" name="confirm_weak_password" value="1" /> ' . __( 'Confirm use of weak password', 'cp' );
						$content .= '</label> ';

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
						$content .= __( 'Registrations are not allowed.', 'cp' );
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
						<span>' . esc_html__( 'Username', 'cp' ) . '</span>
						<input type="text" name="log" />
					</label>
					<label class="password">
						<span>' . esc_html__( 'Password', 'cp' ) . '</span>
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
							<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Forgot Password?', 'cp' ) . '</a>
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

	/**
	 * Get the correct module page number
	 *
	 * Note: The module_page number meta is inconsistent/inaccurate to the actual page number the module is.
	 **/
	public static function get_module_page( $course_id, $current_unit_id, $current_module_id ) {
		$can_update_course = CoursePress_Data_Capabilities::can_update_course( $course_id );
		$status = array( 'publish' );

		if ( $can_update_course ) {
			$status[] = 'draft';
		}

		$units = CoursePress_Data_Course::get_units_with_modules( $course_id, $status );
		$current_page_number = 1; // Default to always 1

		foreach ( $units as $unit_id => $unit ) {
			if ( $unit_id == $current_unit_id ) {
				foreach ( $unit['pages'] as $page_number => $modules ) {
					foreach ( $modules['modules'] as $module_id => $module ) {
						if ( $current_module_id == $module_id ) {
							$current_page_number = $page_number;
							continue;
						}
					}
				}
			}
		}

		return $current_page_number;
	}

	/**
	 * Fix course category link.
	 *
	 * @since 2.0.0
	 */
	public static function term_link( $termlink, $term, $taxonomy ) {
		$course_category_name = CoursePress_Data_Course::get_post_category_name();
		if ( $course_category_name != $taxonomy ) {
			return $termlink;
		}
		$courses_slug = CoursePress_Core::get_setting( 'slugs/course', 'courses' );
		$re = sprintf( '@/%s/@', $course_category_name );
		$to = sprintf( '/%s/%s/', $courses_slug, $course_category_name );
		$termlink = preg_replace( $re, $to, $termlink );
		return $termlink;
	}
}
