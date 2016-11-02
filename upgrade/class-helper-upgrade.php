<?php
class CoursePress_Helper_Upgrade {
	private static $settings = array();

	public static function update_settings() {
		$settings = array(
			'slugs' => array(
				'course' => get_option( 'coursepress_course_slug', 'courses' ),
				'category' => get_option( 'coursepress_course_category_slug', 'course_category' ),
				'units' => get_option( 'coursepress_units_slug', 'units' ),
				'notifications' => get_option( 'coursepress_notifications_slug', 'notifications' ),
				'discussions' => get_option( 'coursepress_discussion_slug', 'discussions' ),
				'discussions_new' => get_option( 'coursepress_discussion_slug_new', 'add_new_discussion' ),
				'grades' => get_option( 'coursepress_grades_slug', 'grades' ),
				'workbook'=> get_option( 'coursepress_workbook_slug', 'workbook' ),
				'enrollment' => get_option( 'enrollment_process_slug', 'enrollment_process' ),
				'login' => get_option( 'login_slug', 'student-login' ),
				'signup' => get_option( 'signup_slug', 'courses-signup' ),
				'student_dashboard' => get_option( 'student_dashboard_slug', 'courses-dashboard' ),
				'student_settings' => get_option( 'student_settings_slug', 'student-settings' ),
				'instructor_profile' => get_option( 'instructor_profile_slug', 'instructor' ),
			),
			'pages' => array(
				'enrollment' => get_option( 'coursepress_enrollment_process_page', 0 ),
				'login' => get_option( 'coursepress_login_page', 0 ),
				'signup' => get_option( 'coursepress_signup_page', 0 ),
				'student_settings' => get_option( 'coursepress_student_settings_page', 0 ),
				'student_dashboard' => get_option( 'coursepress_student_dashboard_page', 0 ),
			),
			'general' => array(
				'show_coursepress_menu' => get_option( 'display_menu_items', 1 ),
				'use_custom_login' => get_option( 'use_custom_login_form', 1 ),
				'redirect_after_login' => get_option( 'redirect_students_to_dashboard', 1 ),
				'add_structure_data' => 1, // Not available in 1.x
			),
			'instructor' => array(
				'use_username' => get_option( 'show_instructor_username', 1 ),
			),
			'course' => array(
				'details_media_type' => get_option( 'details_media_type', 'default' ),
				'details_media_priority' => get_option( 'details_media_priority', 'video' ),
				'listing_media_type' => get_option( 'listings_media_type', 'default' ),
				'listing_media_priority' => get_option( 'listings_media_priority', 'image' ),
				'image_width' => get_option( 'course_image_width', 235 ),
				'image_height' => get_option( 'course_image_height',225 ),
				'order_by' => get_option( 'course_order_by', 'post_date' ),
				'order_by_direction' => get_option( 'course_order_by_type', 'DESC' ),
				'enrollment_type_default' => 'anyone',
			),
			'reports' => array(
				'font' => get_option( 'reports_font', 'helvetica' ),
			)
		);

		// Email settings
		$settings['email'] = array(
			'registration' => array(
				'from' => get_option( 'registration_from_name', get_option( 'blogname' ) ),
				'email' => get_option( 'registration_from_email', get_option( 'admin_email' ) ),
				'subject' => get_option( 'registration_email_subject', __( 'Registration Status', 'cp' ) )
			)
		);

		// Update CP2 settings
		$network = is_multisite();
		if ( $network ) {
			update_site_option( 'coursepress_settings', $settings );
		} else {
			update_option( 'coursepress_settings', $settings );
		}

		// Marked settings as updated
		update_option( 'cp_settings_done', true );

		return true;
	}

	public static function update_course( $course_id ) {
		$course = get_post( $course_id );
		$found_error = 0;

		// Update course instructors
		if ( false == self::update_course_instructors( $course_id ) ) {
			$found_error += 1;
		}
		// Update course meta
		if ( false == self::update_course_meta( $course_id ) ) {
			$found_error += 1;
		}

		// Now update the course settings
		if ( false == self::update_setting( $course_id, self::$settings ) ) {
			$found_error += 1;
		}
		
		$result = ( 0 == $found_error );
		if ( $result ) update_post_meta($course_id, '_cp_updated_to_version_2', 1);
		
		return $result;
	}

	public static function strtotime( $timestamp ) {
		if ( ! is_numeric( $timestamp ) ) {
			error_log( "STRTOTIME: $timestamp" );
			$timestamp = strtotime( $timestamp . ' UTC' ); //@todo: Need hook to change timestamp
		}

		return $timestamp;
	}

	public static function update_setting( $course_id, $settings ) {
		$settings = array_filter( $settings );
		update_post_meta( $course_id, 'course_settings', $settings );

		$date_types = array(
			'course_start_date',
			'course_end_date',
			'enrollment_start_date',
			'enrollment_end_date',
		);

		$course_open_ended = ! empty( $settings['course_open_ended'] );
		$enrollment_open_ended = ! empty( $settings['enrollment_open_ended'] );

		foreach ( $settings as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, $date_types ) ) {
				$meta_value = trim( $meta_value );
				$meta_value = ! empty( $meta_value ) ? self::strtotime( $meta_value ) : 0;
				$meta_value = (int) $meta_value;

				if ( ( true === $course_open_ended && 'course_end_date' == $meta_key )
					|| ( true === $enrollment_open_ended && 'enrollment_end_date' == $meta_key )
				   ) {
					$meta_value = 0;
				}
				update_post_meta( $course_id, "cp_{$meta_key}", $meta_value );
			}
		}

		$message = "Course: $course_id updated! \n" . print_r( $settings, true );
		error_log( $message );
		return true;
	}

	private static function update_course_instructors( $course_id ) {
		$instructors = (array) get_post_meta( $course_id, 'instructors', true );
		$instructors = array_filter( $instructors );
		self::$settings['instructors'] = $instructors;

		return true;
	}

	private static function update_course_meta( $course_id ) {
		$course_metas = array(
			'course_view' => 'normal',
			'minimum_grade_required' => 100,
			'pre_completion_title' => __( 'Almost There', 'cp' ),
			'pre_completion_content' => '',
			'course_completion_title' => __( 'Congratulations, you passed!', 'cp' ),
			'course_completion_content' => '',
			'course_failed_title' => __( 'Sorry, you did not pass this course!', 'cp' ),
			'course_failed_content' => '',
			'setup_step_1' => 'saved',
			'setup_step_2' => 'saved',
			'setup_step_3' => 'saved',
			'setup_step_4' => 'saved',
			'setup_step_5' => 'saved',
			'setup_step_6' => 'saved',
			'setup_step_7' => 'saved',
		);
		$meta_keys = array(
			'feature_url' => 'listing_image',
			'video_url' => 'featured_video',
			'course_structure_options' => 'structure_visible',
			'course_structure_time_display' => 'structure_show_duration',
			/** Course Dates **/
			'open_ended_course' => 'course_open_ended',
			'course_start_date' => 'course_start_date',
			'course_end_date' => 'course_end_date',
			'open_ended_enrollment' => 'enrollment_open_ended',
			'enrollment_start_date' => 'enrollment_start_date',
			'enrollment_end_date' => 'enrollment_end_date',
			/** Classes, Discussions **/
			'limit_class_size' => 'class_limited',
			'class_size' => 'class_size',
			'allow_course_discussion' => 'allow_discussion',
			'allow_workbook_page' => 'allow_workbook',
			/** Enrollment & Cost **/
			'enroll_type' => 'enrollment_type',
			'paid_course' => 'payment_paid_course',
		);

		foreach ( $meta_keys as $old_meta => $new_meta ) {
			$meta_value = get_post_meta( $course_id, $old_meta, true );

			if ( 'enroll_type' == $old_meta ) {
				// Fix enrollment type
				if ( 'registered' == $meta_value ) {
					$meta_value = 'anyone';
				}
			}

			$course_metas[ $new_meta ] = $meta_value;
		}

		self::$settings = wp_parse_args( self::$settings, $course_metas );

		return true;
	}
}
