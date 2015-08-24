<?php

class CoursePress_Template_Course {

	public static function course_enroll_box() {

		$content = '
				<div class="enroll-box">
				<div class="enroll-box-left">
					<div class="course-box">' .
		           do_shortcode( '[course_dates show_alt_display="yes"]' ) .
		           do_shortcode( '[course_enrollment_dates show_enrolled_display="no"]' ) .
		           do_shortcode( '[course_class_size]' ) .
		           do_shortcode( '[course_enrollment_type]' ) .
		           do_shortcode( '[course_language]' ) .
		           do_shortcode( '[course_cost]' ) .
		           '</div>
				</div>
				<div class="enroll-box-right">
					<div class="apply-box">' .
		           do_shortcode( '[course_join_button]' ) .
		           '</div>
				</div>
			</div>
		';

		return $content;
	}

	public static function course_instructors() {
		$content = '[COURSE INSTRUCTORS]';

		return $content;
	}

	public static function course_about() {
		global $post;
		$content = '
		<div class="course-about">
			<h3>' . esc_html__( 'About this course', CoursePress::TD ) . '</h3>' .
		           $post->post_excerpt .
		           '</div>
		';

		return $content;
	}

	public static function course_structure() {
		global $post;

		$data = CoursePress_Model_Course::get_units_with_modules( $post->ID );

		$content = '
		<div class="course-about">
			<h3>' . esc_html__( 'Course structure', CoursePress::TD ) . '</h3>' .
		           $post->post_excerpt .
		           '</div>
		';

		return $content;
	}

	public static function course_archive() {

		$category = CoursePress_Helper_Utility::the_course_category();

		return 'Course Archive';

	}

	public static function course() {

	}

	public static function test_shortcodes() {

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$content = '' .
			           //do_shortcode( '[course_instructors]' ) .
			           //do_shortcode( '[coursecourse_media_instructor_avatar instructor_id="1"]' ) .
			           //do_shortcode( '[course_instructor_avatar instructor_id="1"]' ) .
			           //do_shortcode( '[instructor_profile_url instructor_id="1"]' ) .
			           //do_shortcode( '[course_details]' ) .
			           //do_shortcode( '[courses_student_dashboard]' ) .
		           //do_shortcode( '[courses_student_settings]' ) .
		           //do_shortcode( '[student_registration_form]' ) .  /// YOU ARE HERE
		           //do_shortcode( '[courses_urls]' ) .
		           //do_shortcode( '[course_units]' ) .
		           //do_shortcode( '[course_units_loop]' ) .
		           //do_shortcode( '[course_notifications_loop]' ) .
		           //do_shortcode( '[courses_loop]' ) .
		           //do_shortcode( '[course_discussion_loop]' ) .
		           //do_shortcode( '[course_unit_single]' ) .
		           //do_shortcode( '[course_unit_details]' ) .
		           //do_shortcode( '[course_unit_archive_submenu]' ) .
		           //do_shortcode( '[course_breadcrumbs]' ) .
		           //do_shortcode( '[course_discussion]' ) .
		           //do_shortcode( '[get_parent_course_id]' ) .
		           //do_shortcode( '[units_dropdown]' ) .
		           //do_shortcode( '[course_list]' ) .
		           //do_shortcode( '[course_calendar]' ) .
		           //do_shortcode( '[course_featured]' ) .
		           //do_shortcode( '[course_structure]' ) .
		           //do_shortcode( '[module_status]' ) .
		           //do_shortcode( '[student_workbook_table]' ) .
			           //do_shortcode( '[course]' ) .


		           //// Sub-shortcodes  DONE!!!!
		           do_shortcode( '[course_title]' ) .
		           do_shortcode( '[course_link]' ) .
		           do_shortcode( '[course_summary]' ) .
		           do_shortcode( '[course_description]' ) .
		           do_shortcode( '[course_start]' ) .
		           do_shortcode( '[course_end]' ) .
		           do_shortcode( '[course_dates]' ) .
		           //do_shortcode( '[course_enrollment_start]' ) .
		           //do_shortcode( '[course_enrollment_end]' ) .
		           //do_shortcode( '[course_enrollment_dates]' ) .
		           //do_shortcode( '[course_enrollment_type]' ) .
		           //do_shortcode( '[course_class_size]' ) .
		           //do_shortcode( '[course_cost]' ) .
		           //do_shortcode( '[course_language]' ) .
		           //do_shortcode( '[course_category]' ) .
		           //do_shortcode( '[course_list_image]' ) .
		           do_shortcode( '[course_featured_video]' ) .
		           do_shortcode( '[course_join_button]' ) .
		           //do_shortcode( '[course_thumbnail]' ) .
		           //do_shortcode( '[course_media]' ) .
		           //do_shortcode( '[course_action_links]' ) .
		           //do_shortcode( '[course_random featured_title=""]' ) .
		           //do_shortcode( '[course_time_estimation wrapper="true"]' );


		           //// Course-progress
		           //do_shortcode( '[course_progress]' ) .
		           //do_shortcode( '[course_unit_progress]' ) .
		           //do_shortcode( '[course_mandatory_message]' ) .
		           //do_shortcode( '[course_unit_percent]' ) .
		           //// Other shortcodes
		           //do_shortcode( '[unit_discussion]' ) .
		           //// Page Shortcodes
		           //do_shortcode( '[course_signup]' ) .
		           //do_shortcode( '[cp_pages]' ) .
		           //
		           //$GLOBALS[ 'units_breadcrumbs' ] = '';
		           //
		           ////Messaging shortcodes
		           //do_shortcode( '[messaging_submenu]' );
		'';

		return $content;
	}


}