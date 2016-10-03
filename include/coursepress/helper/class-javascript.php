<?php

class CoursePress_Helper_JavaScript {
	public static $scripts = array();
	public static $styles = array();

	public static function init() {
		// These don't work here because of core using wp_print_styles()
		// add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		add_action( 'admin_footer', array( __CLASS__, 'enqueue_scripts' ) );
		//add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_front_scripts' ) );
	}

	/**
	 * Check if current page is CP page.
	 **/
	public static function is_valid_page() {
		$course_js_pages = array(
			'coursepress_course',
			'coursepress_assessments',
			'coursepress_reports',
			'coursepress_notifications',
			'coursepress_discussions',
		);

		$valid_pages = array_merge( $course_js_pages, array(
			'coursepress_settings',
			'coursepress',
		) );

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $valid_pages ) ) {
			return true;
		}

		return false;
	}

	public static function enqueue_admin_scripts() {
		if ( self::is_valid_page() ) {
			// Enqueue needed scripts for UI
			wp_enqueue_media();
		}
	}

	public static function enqueue_scripts() {
		$is_valid_page = self::is_valid_page();

		if ( false === $is_valid_page ) {
			return;
		}

		$script = CoursePress::$url . 'asset/js/coursepress.js';

		wp_enqueue_script( 'coursepress_object', $script, array(
			'jquery',
			'backbone',
			'underscore',
		), CoursePress::$version );

		// Create a dummy editor to by used by the CoursePress JS object
		ob_start();
		wp_editor( 'dummy_editor_content', 'dummy_editor_id', array(
			'wpautop' => true,
			'textarea_name' => 'dummy_editor_name',
		) );
		$dummy_editor = ob_get_clean();

		$is_super_admin = user_can( 'manage_options', get_current_user_id() );

		$localize_array = array(
			'_ajax_url' => CoursePress_Helper_Utility::get_ajax_url(),
			'_dummy_editor' => $dummy_editor,
			'allowed_video_extensions' => wp_get_video_extensions(),
			'allowed_audio_extensions' => wp_get_audio_extensions(),
			'allowed_image_extensions' => CoursePress_Helper_Utility::get_image_extensions(),
			'allowed_extensions' => apply_filters( 'coursepress_custom_allowed_extensions', false ),
			'date_format' => get_option( 'date_format' ),
			'editor_visual' => __( 'Visual', 'cp' ),
			'editor_text' => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', 'cp' ),
			'invalid_extension_message' => __( 'Extension of the file is not valid. Please use one of the following:', 'cp' ),
			'assessment_grid_url' => admin_url( 'admin.php?page=coursepress_assessments' ),
			'assessment_report_url' => admin_url( 'admin.php?page=coursepress_reports' ),
			'is_campus' => CP_IS_CAMPUS,
			'is_super_admin' => $is_super_admin,
			'user_caps' => CoursePress_Data_Capabilities::get_user_capabilities(),
			'server_error' => __( 'An error occur while processing your request. Please try again later!', 'cp' ),
			'labels' => array(
				'user_dropdown_placeholder' => __( 'Enter username, first name and last name, or email', 'cp' ),
				'required_fields' => __( 'Required fields must not be empty!', 'cp' ),
			),
		);

		// Models
		/** COURSEPRESS_COURSE */
		if ( $is_valid_page ) {

			$coursepress_course_depends_array = array(
				'jquery-ui-accordion',
				'jquery-effects-highlight',
				'jquery-effects-core',
				'jquery-ui-datepicker',
				'jquery-ui-spinner',
				'jquery-ui-droppable',
				'backbone',
			);

			if ( apply_filters( 'coursepress_use_select2_student_selector', true ) ) {
				/**
				 * Deregister script to avoid conflicts, we can do it,we just
				 * load this on CP related pages.
				 */
				wp_deregister_script( 'jquery-select2' );
				wp_register_script(
					'jquery-select2',
					CoursePress::$url . 'asset/js/external/select2.min.js',
					array( 'jquery' ),
					'4.0.2',
					true
				);
				/**
				 * Deregister style to avoid conflicts, we can do it,we just
				 * load this on CP related pages.
				 */
				wp_deregister_style( 'select2' );
				$coursepress_course_depends_array[] = 'jquery-select2';
				$src = CoursePress::$url . 'asset/css/external/select2.min.css';
				wp_enqueue_style(
					'select2',
					$src,
					array(),
					'4.0.2'
				);
			}
			$script = CoursePress::$url . 'asset/js/coursepress-course.js';
			wp_enqueue_script( 'coursepress_course', $script, $coursepress_course_depends_array, CoursePress::$version );

			$script = CoursePress::$url . 'asset/js/external/jquery.treegrid.min.js';
			wp_enqueue_script( 'jquery-treegrid', $script, array(
				'jquery'
			), CoursePress::$version );

			$ui_script = CoursePress::$url . 'asset/js/coursepress-ui.js';
			wp_enqueue_script( 'coursepress_ui', $ui_script, array(), CoursePress::$version );

			$localize_array['instructor_role_defined'] = defined( 'COURSEPRESS_INSTRUCTOR_ROLE' );
			$localize_array['instructor_avatars'] = CoursePress_Helper_UI::get_user_avatar_array();
			$localize_array['instructor_delete_confirm'] = __( 'Please confirm that you want to remove the instructor from this course.', 'cp' );
			$localize_array['instructor_delete_invite_confirm'] = __( 'Please confirm that you want to remove the instructor invitation from this course.', 'cp' );
			$localize_array['facilitator_delete_confirm'] = __( 'Please confirm that you want to remove the facilitator from this course.', 'cp' );
			$localize_array['facilitator_delete_invite_confirm'] = __( 'Please confirm that you want to remove the facilitator invitation from this course.', 'cp' );
			$localize_array['instructor_empty_message'] = __( 'Please Assign Instructor', 'cp' );
			$localize_array['facilitator_empty_message'] = __( 'Assign Facilitator', 'cp' );
			$localize_array['instructor_pednding_status'] = __( 'Pending', 'cp' );
			$localize_array['email_validation_pattern'] = __( '.+@.+', 'cp' );
			$localize_array['student_delete_confirm'] = __( 'Please confirm that you want to remove the student from this course.', 'cp' );
			$localize_array['student_delete_all_confirm'] = __( 'Please confirm that you want to remove ALL students from this course. Warning: This can not be undone. Please make sure this is what you want to do.', 'cp' );

			// Discussion / Notification
			$localize_array['notification_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected notifications. Warning: This cannot be undone. Please make sure this is what you want to do.', 'cp' );
			$localize_array['notification_delete'] = __( 'Please confirm that you want to delete this notification. Warning: This cannot be undone.', 'cp' );

			$localize_array['discussion_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected discussions. Warning: This cannot be undone. Please make sure this is what you want to do.', 'cp' );
			$localize_array['discussion_delete'] = __( 'Please confirm that you want to delete this discussion. Warning: This cannot be undone.', 'cp' );

			if ( ! empty( $_REQUEST['id'] ) ) {
				$localize_array['course_id'] = (int) $_REQUEST['id'];
				$localize_array['course_title'] = get_the_title( $_REQUEST['id'] );
			}
		}

		/** COURSEPRESS_COURSE|UNIT BUILDER */
		if ( 'coursepress_course' == $_GET['page'] && isset( $_GET['tab'] ) && 'units' == $_GET['tab'] ) {
			$script = CoursePress::$url . 'asset/js/coursepress-unitsbuilder.js';

			wp_enqueue_script( 'coursepress_unit_builder', $script, array(
				'coursepress_course',
			), CoursePress::$version );

			$localize_array['unit_builder_templates'] = CoursePress_Helper_UI_Module::get_template( true );
			$localize_array['unit_builder_module_types'] = CoursePress_Helper_UI_Module::get_types();
			$localize_array['unit_builder_module_labels'] = CoursePress_Helper_UI_Module::get_labels();
			$localize_array['unit_builder_delete_module_confirm'] = __( 'Please confirm that you want to remove this module and possible student responses.', 'cp' );
			$localize_array['unit_builder_delete_page_confirm'] = __( 'Please confirm that you want to remove this page. All modules will be moved to the first available page (or you can drop them on other pages first before deleting this page).', 'cp' );
			$localize_array['unit_builder_delete_unit_confirm'] = __( 'Please confirm that you want to remove this unit and all its modules and student responses.', 'cp' );
			$localize_array['unit_builder_new_unit_title'] = __( 'Untitled Unit', 'cp' );
			$localize_array['unit_builder_add_answer_label'] = __( 'Add Answer', 'cp' );
			$localize_array['unit_builder_form_pleaceholder_label'] = __( 'Placeholder Text', 'cp' );
			$localize_array['unit_builder_form_pleaceholder_desc'] = __( 'Placeholder text to put inside the textbox (additional information)', 'cp' );

		}

		/** COURSE LIST */
		if ( 'coursepress' === $_GET['page'] ) {
			$script = CoursePress::$url . 'asset/js/coursepress-courselist.js';
			wp_enqueue_script( 'coursepress_course_list', $script, array(
				'jquery-ui-accordion',
				'jquery-effects-highlight',
				'jquery-effects-core',
				'jquery-ui-datepicker',
				'jquery-ui-spinner',
				'jquery-ui-droppable',
				'backbone',
			), CoursePress::$version );

			$localize_array['courselist_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected courses. Warning: This cannot be undone. Please make sure this is what you want to do.', 'cp' );
			$localize_array['courselist_delete_course'] = __( 'Please confirm that you want to delete this courses. Warning: This cannot be undone.', 'cp' );
			$localize_array['courselist_duplicate_course'] = __( 'Are you sure you want to create a duplicate copy of this course?', 'cp' );
			$localize_array['courselist_export'] = __( 'Please select at least one course to export.', 'cp' );
		}

		if ( 'coursepress_assessments' === $_GET['page'] ) {
			$script = CoursePress::$url . '/asset/js/coursepress-assessment.js';
			wp_enqueue_script( 'coursepress_assessment',
				$script,
				null,
			CoursePress::$version );
			$localize_array['courseinstructor_id'] = get_current_user_id();
			$localize_array['instructor_name'] = CoursePress_Helper_Utility::get_user_name( get_current_user_id(), true );
			$localize_array['assessment_labels'] = array(
				'pass' => __( 'Pass', 'cp' ),
				'fail' => __( 'Fail', 'cp' ),
				'add_feedback' => __( 'Add Feedback', 'cp' ),
				'edit_feedback' => __( 'Edit Feedback', 'cp' ),
				'cancel_feedback' => __( 'Cancel', 'cp' ),
				'help_tooltip' => __( 'If the submission of this grade makes a student completes the course, an email with certificate will be automatically sent.', 'cp' ),
				'minimum_help' => __( 'You may change this minimum grade from course setting.', 'cp' ),
			);
		}
		wp_localize_script( 'coursepress_object', '_coursepress', $localize_array );
	}

	public static function enqueue_front_scripts() {
		global $wp_query;

		// See if we are inside a course.
		$is_cp = CoursePress_Helper_Utility::the_course( true );

		if ( ! $is_cp ) {
			// See if we are on a special CoursePress page.
			$post_type = get_post_type();
			$valid_cpt = array(
				CoursePress_Data_Course::get_post_type_name(),
				'course_notifications_archive',
				'course_workbook',
				'course_discussion_archive',
				'course_discussion',
				'course_archive',
				'coursepress_instructor', // virtual post type
				'coursepress_student_dashboard',
				'coursepress_student_login',
				'coursepress_student_signup',
				CoursePress_Data_Discussion::get_post_type_name(),
			);
			$is_cp = in_array( $post_type, $valid_cpt );
		}
		if ( ! $is_cp ) {
			// Check if there is a course object in wp_query.
			$is_cp = isset( $wp_query->query['course'] );
		}
		if ( ! $is_cp ) {
			// Check if there is a course object in wp_query.
			$is_cp = isset( $wp_query->query['coursename'] );
		}

		// Stop here, if front-end page does not contain CoursePress data!
		if ( ! $is_cp ) { return; }

		// CoursePress Object
		$script = CoursePress::$url . 'asset/js/coursepress.js';
		wp_enqueue_script(
			'coursepress_object',
			$script,
			array(
				'jquery',
				'backbone',
				'underscore',
			),
			CoursePress::$version
		);

		$script = CoursePress::$url . 'asset/js/coursepress-front.js';
		wp_enqueue_script(
			'coursepress_front',
			$script,
			array(
				'jquery',
				'jquery-ui-dialog',
				'underscore',
				'backbone',
			),
			CoursePress::$version
		);

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$localize_array = array(
			'_ajax_url' => CoursePress_Helper_Utility::get_ajax_url(),
			'allowed_video_extensions' => wp_get_video_extensions(),
			'allowed_audio_extensions' => wp_get_audio_extensions(),
			'allowed_image_extensions' => CoursePress_Helper_Utility::get_image_extensions(),
			'allowed_extensions' => apply_filters( 'coursepress_custom_allowed_extensions', false ),
			'allowed_student_extensions' => CoursePress_Helper_Utility::allowed_student_mimes(),
			'no_browser_upload' => __( 'Please try a different browser to upload your file.', 'cp' ),
			'invalid_upload_message' => __( 'Please only upload any of the following files: ', 'cp' ),
			'file_uploaded_message' => __( 'Your file has been submitted successfully.', 'cp' ),
			'file_upload_fail_message' => __( 'There was a problem processing your file.', 'cp' ),
			'response_saved_message' => __( 'Your response was recorded successfully.', 'cp' ),
			'response_fail_message' => __( 'There was a problem saving your response. Please reload this page and try again.', 'cp' ),
			'current_course' => $course_id,
			'current_course_is_paid' => CoursePress_Data_Course::is_paid_course( $course_id )? 'yes':'no',
			'course_url' => get_permalink( CoursePress_Helper_Utility::the_course( true ) ),
			'home_url' => home_url(),
			'current_student' => get_current_user_id(),
			'workbook_view_answer' => __( 'View', 'cp' ),
			'labels' => CoursePress_Helper_UI_Module::get_labels(),
			'signup_errors' => array(
				'all_fields' => __( 'All fields required.', 'cp' ),
				'email_invalid' => __( 'Invalid e-mail address.', 'cp' ),
				'email_exists' => __( 'That e-mail address is already taken.', 'cp' ),
				'user_exists' => __( 'That usernam is already taken.', 'cp' ),
				'weak_password' => __( 'Weak passwords not allowed.', 'cp' ),
				'mismatch_password' => __( 'Passwords do not match.', 'cp' ),
			),
			'comments' => array(
				'require_valid_comment' => __( 'Please type a comment.', 'cp' ),
			),
		);

		/**
		 * add unit-not-available url
		 */
		$url = $localize_array['course_url'].CoursePress_Core::get_slug( 'units/' );
		$localize_array['course_url_unit_nor_available'] = CoursePress_Helper_Message::add_message_query_arg( $url, 'unit-not-available' );

		/**
		 * Filter localize script to allow data insertion.
		 *
		 * @since 2.0
		 *
		 * @param (array) $localize_array.
		 **/
		$localize_array = apply_filters( 'coursepress_localize_object', $localize_array );

		wp_localize_script(
			'coursepress_object',
			'_coursepress',
			$localize_array
		);

		$script = CoursePress::$url . 'asset/js/external/circle-progress.min.js';
		wp_enqueue_script(
			'circle-progress',
			$script,
			array( 'jquery' ),
			CoursePress::$version
		);

		$script = CoursePress::$url . 'asset/js/external/backbone.modal-min.js';
		wp_enqueue_script(
			'backbone-modal',
			$script,
			array(
				'backbone',
				'password-strength-meter',
			),
			CoursePress::$version
		);

		$fontawesome = CoursePress::$url . 'asset/css/external/font-awesome.min.css';
		wp_enqueue_style(
			'fontawesome',
			$fontawesome,
			array(),
			CoursePress::$version
		);

		$front_css = CoursePress::$url . 'asset/css/front.css';
		wp_enqueue_style( 'coursepress-front', $front_css, array(), CoursePress::$version );
	}

	public static function front_assets() {
		$script_url = CoursePress::$url . 'asset/js/';
		$css_url = CoursePress::$url . 'asset/css/';
		$version = CoursePress::$version;

		wp_enqueue_script( 'comment-reply' );
		wp_enqueue_style( 'coursepress-front-css', $css_url . 'front.css', array( 'dashicons' ), $version );
		wp_enqueue_script( 'coursepress-front-js', $script_url . 'front.js', array( 'jquery', 'backbone', 'underscore' ), $version );

		$localize_array = array(
			'_ajax_url' => CoursePress_Helper_Utility::get_ajax_url(),
			'cpnonce' => wp_create_nonce( 'coursepress_nonce' ),
			'allowed_video_extensions' => wp_get_video_extensions(),
			'allowed_audio_extensions' => wp_get_audio_extensions(),
			'allowed_image_extensions' => CoursePress_Helper_Utility::get_image_extensions(),
			'allowed_extensions' => apply_filters( 'coursepress_custom_allowed_extensions', false ),
			'allowed_student_extensions' => CoursePress_Helper_Utility::allowed_student_mimes(),
			'no_browser_upload' => __( 'Please try a different browser to upload your file.', 'cp' ),
			'invalid_upload_message' => __( 'Please only upload any of the following files: ', 'cp' ),
			'file_uploaded_message' => __( 'Your file has been submitted successfully.', 'cp' ),
			'file_upload_fail_message' => __( 'There was a problem processing your file.', 'cp' ),
			'response_saved_message' => __( 'Your response was recorded successfully.', 'cp' ),
			'response_fail_message' => __( 'There was a problem saving your response. Please reload this page and try again.', 'cp' ),
		//	'current_course_is_paid' => CoursePress_Data_Course::is_paid_course( $course_id )? 'yes':'no',
			'course_url' => get_permalink( CoursePress_Helper_Utility::the_course( true ) ),
			'home_url' => home_url(),
			'current_student' => get_current_user_id(),
			'workbook_view_answer' => __( 'View', 'cp' ),
			'labels' => CoursePress_Helper_UI_Module::get_labels(),
			'signup_errors' => array(
				'all_fields' => __( 'All fields required.', 'cp' ),
				'email_invalid' => __( 'Invalid e-mail address.', 'cp' ),
				'email_exists' => __( 'That e-mail address is already taken.', 'cp' ),
				'user_exists' => __( 'That usernam is already taken.', 'cp' ),
				'weak_password' => __( 'Weak passwords not allowed.', 'cp' ),
				'mismatch_password' => __( 'Passwords do not match.', 'cp' ),
			),
			'comments' => array(
				'require_valid_comment' => __( 'Please type a comment.', 'cp' ),
			),
		);

		wp_localize_script( 'coursepress-front-js', '_coursepress', $localize_array );
	}
}
