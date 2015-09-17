<?php

class CoursePress_Model_Shortcodes_Templates {

	public static function init() {

		add_shortcode( 'course_archive', array( __CLASS__, 'course_archive' ) );
		add_shortcode( 'course_enroll_box', array( __CLASS__, 'course_enroll_box' ) );
		add_shortcode( 'course_list_box', array( __CLASS__, 'course_list_box' ) );
		add_shortcode( 'course_page', array( __CLASS__, 'course_page' ) );
		add_shortcode( 'instructor_page', array( __CLASS__, 'instructor_page' ) );
		add_shortcode( 'coursepress_dashboard', array( __CLASS__, 'coursepress_dashboard' ) );

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
		), $a, 'course_page' );

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
		), $a, 'course_page' );

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

}
