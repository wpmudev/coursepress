<?php

class CoursePress_Upgrade {

	private static $map = array(
		'2.0' => '_2p0',
	);


	public static function init() {

		// If this setting does not exist, then default to last 1.0 release
		$last_version = CoursePress_Core::get_network_setting( 'general/version', '1.2.5.8' );
		$last_version = '1.2.5.8';

		foreach ( self::$map as $v => $f ) {
			if ( version_compare( $last_version, $v ) < 0 ) {
				call_user_func( __CLASS__ . '::' . $f );
			}
		}

	}


	private static function _2p0() {

		// $settings = get_option( 'coursepress_settings', array() );
		// error_log( print_r( CoursePress_Core::get_network_setting('general/version'), true ) );
		return false;

		/**
		 * Upgrade blog options
		 *
		 * Store settings in one key rather than all over the options in the table
		 */
		// delete_option( 'coursepress_settings' );
		$settings = get_option( 'coursepress_settings', array() );

		// General Meta
		CoursePress_Helper_Utility::set_array_val( $settings, 'general/show_coursepress_menu', get_option( 'display_menu_items', 1 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'general/use_custom_login', get_option( 'use_custom_login_form', 1 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'general/redirect_after_login', get_option( 'redirect_students_to_dashboard', 1 ) );

		// Slugs
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/course', get_option( 'coursepress_course_slug', 'courses' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/category', get_option( 'coursepress_course_category_slug', 'course_category' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/module', get_option( 'coursepress_module_slug', 'module' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/units', get_option( 'coursepress_units_slug', 'units' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/notifications', get_option( 'coursepress_notifications_slug', 'notifications' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/discussions', get_option( 'coursepress_discussion_slug', 'discussion' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/discussions_new', get_option( 'coursepress_discussion_slug_new', 'add_new_discussion' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/grades', get_option( 'coursepress_grades_slug', 'grades' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/workbook', get_option( 'coursepress_workbook_slug', 'workbook' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/enrollment', get_option( 'enrollment_process_slug', 'enrollment_process' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/login', get_option( 'login_slug', 'student-login' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/signup', get_option( 'signup_slug', 'courses-signup' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/student_dashboard', get_option( 'student_dashboard_slug', 'courses-dashboard' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/student_settings', get_option( 'student_settings_slug', 'student-settings' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/instructor_profile', get_option( 'instructor_profile_slug', 'instructor' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/inbox', get_option( 'coursepress_inbox_slug', 'student-inbox' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/sent_messages', get_option( 'coursepress_sent_messages_slug', 'student-sent-messages' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'slugs/new_messages', get_option( 'coursepress_new_message_slug', 'student-new-message' ) );

		// Pages
		CoursePress_Helper_Utility::set_array_val( $settings, 'pages/enrollment', get_option( 'coursepress_enrollment_process_page', 0 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'pages/login', get_option( 'coursepress_login_page', 0 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'pages/signup', get_option( 'coursepress_signup_page', 0 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'pages/student_dashboard', get_option( 'coursepress_student_dashboard_page', 0 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'pages/student_settings', get_option( 'coursepress_student_settings_page', 0 ) );

		// Course
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/details_media_type', get_option( 'details_media_type', 'default' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/details_media_priority', get_option( 'details_media_priority', 'video' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/listing_media_type', get_option( 'listings_media_type', 'default' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/listing_media_priority', get_option( 'listings_media_priority', 'image' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/order_by', get_option( 'course_order_by', 'post_date' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/order_by_direction', get_option( 'course_order_by_type', 'DESC' ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/image_width', get_option( 'course_image_width', 235 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'course/image_height', get_option( 'course_image_height', 235 ) );

		// Reports
		CoursePress_Helper_Utility::set_array_val( $settings, 'reports/font', get_option( 'reports_font', 'helvetica' ) );

		// Instructor
		CoursePress_Helper_Utility::set_array_val( $settings, 'instructor/show_username', get_option( 'show_instructor_username', 1 ) );
		$default_capabilities = CoursePress_Model_Capabilities::get_instructor_capabilities();
		CoursePress_Helper_Utility::set_array_val( $settings, 'instructor/capabilities', get_option( 'coursepress_instructor_capabilities', $default_capabilities ) );

		// Basic Certificate
		/**
		 * @todo Use method in basic certificate class when implemented.
		 */
		$options = get_option( 'coursepress_basic_certificate' );
		$value = isset( $options['basic_certificate_enable'] ) ? $options['basic_certificate_enable'] : 1;
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/enabled', $value );
		$value = isset( $options['certificate_content'] ) ? $options['certificate_content'] : CoursePress_View_Admin_Settings_BasicCertificate::default_certificate_content();
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/content', $value );
		$value = isset( $options['background_url'] ) ? $options['background_url'] : '';
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/background_image', $value );
		$value = isset( $options['padding_top'] ) ? $options['padding_top'] : 0;
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/padding/top', $value );
		$value = isset( $options['padding_bottom'] ) ? $options['padding_bottom'] : 0;
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/padding/bottom', $value );
		$value = isset( $options['padding_left'] ) ? $options['padding_left'] : 0;
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/padding/left', $value );
		$value = isset( $options['padding_right'] ) ? $options['padding_right'] : 0;
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/padding/right', $value );
		$value = isset( $options['orientation'] ) ? $options['orientation'] : 'L';
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/orientation', $value );
		$value = isset( $options['styles'] ) ? $options['styles'] : '';
		CoursePress_Helper_Utility::set_array_val( $settings, 'basic_certificate/styles', $value );

		// Email Settings
		$default_settings = CoursePress_Helper_Settings_Email::get_defaults();
		$value = isset( $options['auto_email'] ) ? $options['auto_email'] : 1;
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/basic_certificate/auto_email', $value );
		$value = isset( $options['from_name'] ) ? $options['from_name'] : $default_settings['basic_certificate']['from_name'];
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/basic_certificate/from', $value );
		$value = isset( $options['from_email'] ) ? $options['from_email'] : $default_settings['basic_certificate']['from_email'];
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/basic_certificate/email', $value );
		$value = isset( $options['email_subject'] ) ? $options['email_subject'] : $default_settings['basic_certificate']['subject'];
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/basic_certificate/subject', $value );
		$value = isset( $options['email_content'] ) ? $options['email_content'] : $default_settings['basic_certificate']['content'];
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/basic_certificate/content', $value );

		$value = get_option( 'registration_from_name', $default_settings['registration']['from_name'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/registration/from', $value );
		$value = get_option( 'registration_from_email', $default_settings['registration']['from_email'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/registration/email', $value );
		$value = get_option( 'registration_email_subject', $default_settings['registration']['subject'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/registration/subject', $value );
		$value = get_option( 'registration_content_email', $default_settings['registration']['content'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/registration/content', $value );

		$value = get_option( 'enrollment_from_name', $default_settings['enrollment_confirm']['from_name'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/enrollment_confirm/from', $value );
		$value = get_option( 'enrollment_from_email', $default_settings['enrollment_confirm']['from_email'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/enrollment_confirm/email', $value );
		$value = get_option( 'enrollment_email_subject', $default_settings['enrollment_confirm']['subject'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/enrollment_confirm/subject', $value );
		$value = get_option( 'enrollment_content_email', $default_settings['enrollment_confirm']['content'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/enrollment_confirm/content', $value );

		$value = get_option( 'invitation_from_name', $default_settings['course_invitation']['from_name'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation/from', $value );
		$value = get_option( 'invitation_from_email', $default_settings['course_invitation']['from_email'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation/email', $value );
		$value = get_option( 'invitation_email_subject', $default_settings['course_invitation']['subject'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation/subject', $value );
		$value = get_option( 'invitation_content_email', $default_settings['course_invitation']['content'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation/content', $value );

		$value = get_option( 'invitation_passcode_from_name', $default_settings['course_invitation_password']['from_name'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation_password/from', $value );
		$value = get_option( 'invitation_passcode_from_email', $default_settings['course_invitation_password']['from_email'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation_password/email', $value );
		$value = get_option( 'invitation_passcode_email_subject', $default_settings['course_invitation_password']['subject'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation_password/subject', $value );
		$value = get_option( 'invitation_content_passcode_email', $default_settings['course_invitation_password']['content'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/course_invitation_password/content', $value );

		$value = get_option( 'instructor_invitation_from_name', $default_settings['instructor_invitation']['from_name'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/instructor_invitation/from', $value );
		$value = get_option( 'instructor_invitation_from_email', $default_settings['instructor_invitation']['from_email'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/instructor_invitation/email', $value );
		$value = get_option( 'instructor_invitation_email_subject', $default_settings['instructor_invitation']['subject'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/instructor_invitation/subject', $value );
		$value = get_option( 'instructor_invitation_email', $default_settings['instructor_invitation']['content'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/instructor_invitation/content', $value );

		$value = get_option( 'mp_order_from_name', $default_settings['new_order']['from_name'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/new_order/from', $value );
		$value = get_option( 'mp_order_from_email', $default_settings['new_order']['from_email'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/new_order/email', $value );
		$value = get_option( 'mp_order_email_subject', $default_settings['new_order']['subject'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/new_order/subject', $value );
		$value = get_option( 'mp_order_content_email', $default_settings['new_order']['content'] );
		CoursePress_Helper_Utility::set_array_val( $settings, 'email/new_order/content', $value );

		// WooCommerce Integration
		CoursePress_Helper_Utility::set_array_val( $settings, 'woocommerce/use', get_option( 'use_woo', 0 ) );
		CoursePress_Helper_Utility::set_array_val( $settings, 'woocommerce/redirect_to_course', get_option( 'redirect_woo_to_course',0 ) );

		// Terms of Service Integration
		CoursePress_Helper_Utility::set_array_val( $settings, 'tos/use', get_option( 'show_tos', 0 ) );

		update_option( 'coursepress_settings', $settings );

		/**
		 * Upgrade site meta (or blog option)
		 *
		 * Store settings in one key rather than all over the options in the table
		 */
		if ( ! is_multisite() ) {
			$settings = get_option( 'coursepress_settings' );
		} else {
			$settings = get_site_option( 'coursepress_settings', array() );
		}

		CoursePress_Helper_Utility::set_array_val( $settings, 'general/version', CoursePress::$version );

		if ( ! is_multisite() ) {
			update_option( 'coursepress_settings', $settings );
		} else {
			update_site_option( 'coursepress_settings', $settings );
		}

		/**
		 * Clean up time
		 * DO NOT DELETE THOSE OPTIONS - most of them are used in CP 2.0!!
		 *
		 * @todo  remove this block once 2.0 is stable or document this list somewhere else...
		 */
		// delete_option( 'display_menu_items' );
		// delete_option( 'use_custom_login_form' );
		// delete_option( 'redirect_students_to_dashboard' );
		// delete_option( 'coursepress_course_slug' );
		// delete_option( 'coursepress_course_category_slug' );
		// delete_option( 'coursepress_module_slug' );
		// delete_option( 'coursepress_units_slug' );
		// delete_option( 'coursepress_notifications_slug' );
		// delete_option( 'coursepress_discussion_slug' );
		// delete_option( 'coursepress_discussion_slug_new' );
		// delete_option( 'coursepress_grades_slug' );
		// delete_option( 'coursepress_workbook_slug' );
		// delete_option( 'enrollment_process_slug' );
		// delete_option( 'student_dashboard_slug' );
		// delete_option( 'student_settings_slug' );
		// delete_option( 'instructor_profile_slug' );
		// delete_option( 'coursepress_inbox_slug' );
		// delete_option( 'coursepress_sent_messages_slug' );
		// delete_option( 'coursepress_new_message_slug' );
		// delete_option( 'enrollment_process_slug' );
		// delete_option( 'coursepress_enrollment_process_page' );
		// delete_option( 'coursepress_login_page' );
		// delete_option( 'coursepress_signup_page' );
		// delete_option( 'coursepress_student_dashboard_page' );
		// delete_option( 'coursepress_student_settings_page' );
		// delete_option( 'details_media_type' );
		// delete_option( 'details_media_priority' );
		// delete_option( 'listings_media_type' );
		// delete_option( 'listings_media_priority' );
		// delete_option( 'course_order_by' );
		// delete_option( 'course_order_by_type' );
		// delete_option( 'course_image_width' );
		// delete_option( 'course_image_height' );
		// delete_option( 'reports_font' );
		// delete_option( 'show_instructor_username' );
		// delete_option( 'coursepress_instructor_capabilities' );
		// delete_option( 'coursepress_basic_certificate' );
		// delete_option( 'registration_from_name' );
		// delete_option( 'registration_from_email' );
		// delete_option( 'registration_email_subject' );
		// delete_option( 'registration_content_email' );
		// delete_option( 'enrollment_from_name' );
		// delete_option( 'enrollment_from_email' );
		// delete_option( 'enrollment_email_subject' );
		// delete_option( 'enrollment_content_email' );
		// delete_option( 'invitation_from_name' );
		// delete_option( 'invitation_from_email' );
		// delete_option( 'invitation_email_subject' );
		// delete_option( 'invitation_content_email' );
		// delete_option( 'invitation_passcode_from_name' );
		// delete_option( 'invitation_passcode_from_email' );
		// delete_option( 'invitation_passcode_email_subject' );
		// delete_option( 'invitation_content_passcode_email' );
		// delete_option( 'instructor_invitation_from_name' );
		// delete_option( 'instructor_invitation_from_email' );
		// delete_option( 'instructor_invitation_email_subject' );
		// delete_option( 'instructor_invitation_email' );
		// delete_option( 'mp_order_from_name' );
		// delete_option( 'mp_order_from_email' );
		// delete_option( 'mp_order_email_subject' );
		// delete_option( 'mp_order_content_email' );
		// delete_option('redirect_woo_to_course' );
		// delete_option( 'use_woo' );
		// delete_option( 'show_tos' );
	}
}
