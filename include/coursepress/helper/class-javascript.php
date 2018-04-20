<?php
class CoursePress_Helper_JavaScript {
	protected static $is_cp_called = false;

	public static $scripts = array();
	public static $styles = array();

	// Depracated!!!
	public static function init() {}

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

		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $valid_pages ) || 'course' == get_post_type() ) {
			if ( 'course' == get_post_type() ) {
				$_GET['page'] = $_REQUEST['page'] = 'coursepress_course';
			}
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
		global $pagenow;

		$is_valid_page = self::is_valid_page();

		if ( false === $is_valid_page ) {
			return;
		}

		$course_type = CoursePress_Data_Course::get_post_type_name();
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
			'editor_visual' => __( 'Visual', 'coursepress' ),
			'editor_text' => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', 'coursepress' ),
			'invalid_extension_message' => __( 'Extension of the file is not valid. Please use one of the following:', 'coursepress' ),
			'assessment_grid_url' => admin_url( 'admin.php?page=coursepress_assessments' ),
			'assessment_report_url' => admin_url( 'admin.php?page=coursepress_reports' ),
			'is_campus' => CP_IS_CAMPUS,
			'is_super_admin' => $is_super_admin,
			'user_caps' => CoursePress_Data_Capabilities::get_user_capabilities(),
			'server_error' => __( 'An error occur while processing your request. Please try again later!', 'coursepress' ),
			'labels' => array(
				'user_dropdown_placeholder' => __( 'Enter username, first name and last name, or email', 'coursepress' ),
				'required_fields' => __( 'Required fields must not be empty!', 'coursepress' ),
			),
		);

		// Add course_id in edit page
		if ( $course_type == get_post_type() ) {
			$localize_array['course_id'] = get_the_ID();
		}

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

			$localize_array['instructor_role_defined'] = defined( 'COURSEPRESS_INSTRUCTOR_ROLE' );
			$localize_array['instructor_avatars'] = CoursePress_Helper_UI::get_user_avatar_array();
			$localize_array['instructor_delete_confirm'] = __( 'Please confirm that you want to remove the instructor from this course.', 'coursepress' );
			$localize_array['instructor_delete_invite_confirm'] = __( 'Please confirm that you want to remove the instructor invitation from this course.', 'coursepress' );
			$localize_array['facilitator_delete_confirm'] = __( 'Please confirm that you want to remove the facilitator from this course.', 'coursepress' );
			$localize_array['facilitator_delete_invite_confirm'] = __( 'Please confirm that you want to remove the facilitator invitation from this course.', 'coursepress' );
			$localize_array['instructor_empty_message'] = __( 'Please Assign Instructor', 'coursepress' );
			$localize_array['facilitator_empty_message'] = __( 'Assign Facilitator', 'coursepress' );
			$localize_array['instructor_pednding_status'] = __( 'Pending', 'coursepress' );
			$localize_array['email_validation_pattern'] = __( '.+@.+', 'coursepress' );
			$localize_array['student_delete_confirm'] = __( 'Please confirm that you want to remove the student from this course.', 'coursepress' );
			$localize_array['student_delete_all_confirm'] = __( 'Please confirm that you want to remove ALL students from this course. Warning: This can not be undone. Please make sure this is what you want to do.', 'coursepress' );

			// Discussion / Notification
			$localize_array['notification_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected notifications. Warning: This cannot be undone. Please make sure this is what you want to do.', 'coursepress' );
			$localize_array['notification_delete'] = __( 'Please confirm that you want to delete this notification. Warning: This cannot be undone.', 'coursepress' );

			$localize_array['discussion_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected discussions. Warning: This cannot be undone. Please make sure this is what you want to do.', 'coursepress' );
			$localize_array['discussion_delete'] = __( 'Please confirm that you want to delete this discussion. Warning: This cannot be undone.', 'coursepress' );

			if ( ! empty( $_REQUEST['id'] ) ) {
				$localize_array['course_id'] = (int) $_REQUEST['id'];
				$localize_array['course_title'] = get_the_title( $_REQUEST['id'] );
			}
		}

		$style_global = CoursePress::$url . 'asset/css/admin-global.css';
		$timepicker_css = CoursePress::$url . 'asset/css/external/jquery-ui-timepicker-addon.min.css';
		$timepicker_js = CoursePress::$url . 'asset/js/external/jquery-ui-timepicker-addon.min.js';
		wp_enqueue_style( 'coursepress_admin_timepicker', $timepicker_css, false, CoursePress::$version );
		wp_enqueue_script( 'coursepress_admin_timepicker', $timepicker_js, array( 'jquery-ui-slider', 'jquery-ui-datepicker' ), CoursePress::$version, true );
		wp_enqueue_style( 'coursepress_admin_global', $style_global, array( 'jquery-datepicker' ), CoursePress::$version );

		/** COURSEPRESS_COURSE|UNIT BUILDER */
		if ( 'coursepress_course' == $_GET['page'] && isset( $_GET['tab'] ) && 'units' == $_GET['tab'] ) {
			$script = CoursePress::$url . 'asset/js/coursepress-unitsbuilder.js';

			wp_enqueue_script( 'coursepress_unit_builder', $script, array(
				'coursepress_course',
			), CoursePress::$version );

			$localize_array['unit_builder_templates'] = CoursePress_Helper_UI_Module::get_template( true );
			$localize_array['unit_builder_module_types'] = CoursePress_Helper_UI_Module::get_types();
			$localize_array['unit_builder_module_labels'] = CoursePress_Helper_UI_Module::get_labels();
			$localize_array['unit_builder_delete_module_confirm'] = __( 'Please confirm that you want to remove this module and possible student responses.', 'coursepress' );
			$localize_array['unit_builder_delete_page_confirm'] = __( 'Please confirm that you want to remove this page. All modules will be moved to the first available page (or you can drop them on other pages first before deleting this page).', 'coursepress' );
			$localize_array['unit_builder_delete_unit_confirm'] = __( 'Please confirm that you want to remove this unit and all its modules and student responses.', 'coursepress' );
			$localize_array['unit_builder_new_unit_title'] = __( 'Untitled Unit', 'coursepress' );
			$localize_array['unit_builder_add_answer_label'] = __( 'Add Answer', 'coursepress' );
			$localize_array['unit_builder_form_pleaceholder_label'] = __( 'Placeholder Text', 'coursepress' );
			$localize_array['unit_builder_form_pleaceholder_desc'] = __( 'Placeholder text to put inside the textbox (additional information)', 'coursepress' );
			$localize_array['unit_builder_form']['messages']['required_fields'] = __( "Answer fields must not be empty!\nPlease check modules:", 'coursepress' );
			$localize_array['unit_builder_form']['messages']['saving_unit'] = __( 'Unit is saving now...', 'coursepress' );
			$localize_array['unit_builder_form']['messages']['successfully_saved'] = __( 'Unit was successfully saved!', 'coursepress' );
			$localize_array['unit_builder_form']['messages']['error_while_saving'] = __( 'Something went wrong. Unit was not saved!', 'coursepress' );
			$localize_array['unit_builder']['question_type'] = array(
				'single' => __( 'Single Choice', 'coursepress' ),
				'multiple' => __( 'Multiple Choice', 'coursepress' ),
				'short' => __( 'Short Answer', 'coursepress' ),
				'long' => __( 'Long Answer', 'coursepress' ),
				'selectable' => __( 'Selectable Choice', 'coursepress' ),
			);
			$localize_array['unit_builder_form']['messages']['adding_module'] = __( 'Wait, module adding now...', 'coursepress' );
			$localize_array['unit_l8n'] = array(
				'pre_answers' => array(
					'a' => __( 'Answer A', 'coursepress' ),
					'b' => __( 'Answer B', 'coursepress' ),
				),
			);
		}

		/** COURSE LIST */
		$screen = get_current_screen();
		if ( 'coursepress_course' === $_GET['page'] && 'edit.php' == $pagenow && 'edit-course' == $screen->id ) {
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

			$localize_array['courselist_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected courses. Warning: This cannot be undone. Please make sure this is what you want to do.', 'coursepress' );
			$localize_array['courselist_delete_course'] = __( 'Please confirm that you want to delete this courses. Warning: This cannot be undone.', 'coursepress' );
			$localize_array['courselist_duplicate_course'] = __( 'Are you sure you want to create a duplicate copy of this course?', 'coursepress' );
			$localize_array['courselist_export'] = __( 'Please select at least one course to export.', 'coursepress' );
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
				'pass' => __( 'Pass', 'coursepress' ),
				'fail' => __( 'Fail', 'coursepress' ),
				'add_feedback' => __( 'Add Feedback', 'coursepress' ),
				'edit_feedback' => __( 'Edit Feedback', 'coursepress' ),
				'cancel_feedback' => __( 'Cancel', 'coursepress' ),
				'help_tooltip' => __( 'If the submission of this grade makes a student completes the course, an email with certificate will be automatically sent.', 'coursepress' ),
				'minimum_help' => __( 'You may change this minimum grade from course setting.', 'coursepress' ),
			);
		}

		/**
		 * save unit message.
		 */
			$localize_array['unit_builder_form']['messages']['setup']['saving'] = __( 'Step is saving now...', 'coursepress' );
			$localize_array['unit_builder_form']['messages']['setup']['saved'] = __( 'Step was successfully saved!', 'coursepress' );
			$localize_array['unit_builder_form']['messages']['setup']['error'] = __( 'Something went wrong. Step was not saved!', 'coursepress' );

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

		self::coursepress_front_js();

		$course_id = CoursePress_Helper_Utility::the_course( true );

		$localize_array = array(
			'_ajax_url' => CoursePress_Helper_Utility::get_ajax_url(),
			'allowed_video_extensions' => wp_get_video_extensions(),
			'allowed_audio_extensions' => wp_get_audio_extensions(),
			'allowed_image_extensions' => CoursePress_Helper_Utility::get_image_extensions(),
			'allowed_extensions' => apply_filters( 'coursepress_custom_allowed_extensions', false ),
			'allowed_student_extensions' => CoursePress_Helper_Utility::allowed_student_mimes(),
			'no_browser_upload' => __( 'Please try a different browser to upload your file.', 'coursepress' ),
			'invalid_upload_message' => __( 'Please only upload any of the following files: ', 'coursepress' ),
			'file_uploaded_message' => __( 'Your file has been submitted successfully.', 'coursepress' ),
			'file_upload_fail_message' => __( 'There was a problem processing your file.', 'coursepress' ),
			'response_saved_message' => __( 'Your response was recorded successfully.', 'coursepress' ),
			'response_fail_message' => __( 'There was a problem saving your response. Please reload this page and try again.', 'coursepress' ),
			'current_course' => $course_id,
			'current_course_is_paid' => CoursePress_Data_Course::is_paid_course( $course_id )? 'yes':'no',
			'current_course_type' => CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type', 'manually' ),
			'course_url' => get_permalink( CoursePress_Helper_Utility::the_course( true ) ),
			'home_url' => home_url(),
			'current_student' => get_current_user_id(),
			'workbook_view_answer' => __( 'View', 'coursepress' ),
			'labels' => CoursePress_Helper_UI_Module::get_labels(),
			'signup_errors' => array(
				'all_fields' => __( 'All fields required.', 'coursepress' ),
				'email_invalid' => __( 'Invalid e-mail address.', 'coursepress' ),
				'email_exists' => __( 'That e-mail address is already taken.', 'coursepress' ),
				'user_exists' => __( 'That username is already taken.', 'coursepress' ),
				'weak_password' => __( 'Weak passwords not allowed.', 'coursepress' ),
				'mismatch_password' => __( 'Passwords do not match.', 'coursepress' ),
			),
			'comments' => array(
				'require_valid_comment' => __( 'Please type a comment.', 'coursepress' ),
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

	public static function maybe_print_assets() {
		if ( CoursePress_Core::$is_cp_page ) {
			if ( false == self::$is_cp_called ) {
				// Load the script
				self::front_assets();
			}
		}

		if ( self::$is_cp_called && false === is_user_logged_in() ) {
			// Print enrollment templates
			echo do_shortcode( '[coursepress_enrollment_templates]' );
		}
	}

	public static function front_assets() {
		if ( false === CoursePress_Core::$is_cp_page ) {
			return;
		}

		self::$is_cp_called = true;
		$script_url = CoursePress::$url . 'asset/js/';
		$css_url = CoursePress::$url . 'asset/css/';
		$version = CoursePress::$version;
		$course_id = CoursePress_Helper_Utility::the_course( true );

		// Fontawesome
		$fontawesome = $css_url . 'external/font-awesome.min.css';
		wp_enqueue_style( 'fontawesome', $fontawesome, array(), $version );
		$bbm_modal_css = $css_url . 'bbm.modal.css';
		wp_enqueue_style( 'coursepress-modal-css', $bbm_modal_css, array(), $version );
		// Front CSS
		wp_enqueue_style( 'coursepress-front-css', $css_url . 'front.css', array( 'dashicons', 'wp-mediaelement' ), $version );

		wp_enqueue_script( 'comment-reply' );

		$script = $script_url . 'external/circle-progress.min.js';
		wp_enqueue_script( 'circle-progress', $script, array( 'jquery' ), $version );

		$modal_script_url = $script_url . 'external/backbone.modal-min.js';
		wp_enqueue_script( 'coursepress-backbone-modal', $modal_script_url, array( 'jquery', 'backbone', 'underscore', 'password-strength-meter' ) );

		$deps = array( 'underscore', 'wp-mediaelement' );
		wp_enqueue_script( 'coursepress-front-js', $script_url . 'front.js', $deps, $version );

		$localize_array = array(
			'_ajax_url' => CoursePress_Helper_Utility::get_ajax_url(),
			'cpnonce' => wp_create_nonce( 'coursepress_nonce' ),
			'allowed_video_extensions' => wp_get_video_extensions(),
			'allowed_audio_extensions' => wp_get_audio_extensions(),
			'allowed_image_extensions' => CoursePress_Helper_Utility::get_image_extensions(),
			'allowed_extensions' => apply_filters( 'coursepress_custom_allowed_extensions', false ),
			'allowed_student_extensions' => CoursePress_Helper_Utility::allowed_student_mimes(),
			'no_browser_upload' => __( 'Please try a different browser to upload your file.', 'coursepress' ),
			'invalid_upload_message' => __( 'Invalid file format!', 'coursepress' ),
			'file_uploaded_message' => __( 'Your file has been submitted successfully.', 'coursepress' ),
			'file_upload_fail_message' => __( 'There was a problem processing your file.', 'coursepress' ),
			'response_saved_message' => __( 'Your response was recorded successfully.', 'coursepress' ),
			'response_fail_message' => __( 'There was a problem saving your response. Please reload this page and try again.', 'coursepress' ),
			'current_course_is_paid' => CoursePress_Data_Course::is_paid_course( $course_id )? 'yes':'no',
			'current_course_type' => CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type', 'manually' ),
			'course_url' => get_permalink( CoursePress_Helper_Utility::the_course( true ) ),
			'home_url' => home_url(),
			'current_student' => get_current_user_id(),
			'workbook_view_answer' => __( 'View', 'coursepress' ),
			'labels' => CoursePress_Helper_UI_Module::get_labels(),
			'signup_errors' => array(
				'all_fields' => __( 'All fields are required.', 'coursepress' ),
				'email_invalid' => __( 'Invalid e-mail address.', 'coursepress' ),
				'email_exists' => __( 'That e-mail address is already taken.', 'coursepress' ),
				'user_exists' => __( 'That username is already taken.', 'coursepress' ),
				'weak_password' => __( 'Weak passwords not allowed.', 'coursepress' ),
				'mismatch_password' => __( 'Passwords do not match.', 'coursepress' ),
			),
			'login_errors' => array(
				'required' => __( 'Your username and/or password is required!', 'coursepress' ),
			),
			'comments' => array(
				'require_valid_comment' => __( 'Ooops! You did not write anything!', 'coursepress' ),
			),
			'server_error' => __( 'An unexpected error occur while processing. Please try again.', 'coursepress' ),
			'module_error' => array(
				'required' => __( 'You need to complete this module!', 'coursepress' ),
				'normal_required' => __( 'You need to complete all required modules!', 'coursepress' ),
				'participate' => __( 'Your participation to the discussion is required!', 'coursepress' ),
				'passcode_required' => __( 'Enter PASSCODE!', 'coursepress' ),
				'invalid_passcode' => __( 'Invalid PASSCODE!', 'coursepress' ),
			),
			'confirmed_withdraw' => __( 'Please confirm that you want to withdraw from the course. If you withdraw, all your records and access to this course will also be removed.', 'coursepress' ),
			'confirmed_edit' => __( 'Please confirm that you want to edit this course.', 'coursepress' ),
			'buttons' => array(
				'ok' => __( 'OK', 'coursepress' ),
				'cancel' => __( 'Cancel', 'coursepress' ),
			),
			'password_strength_meter_enabled' => CoursePress_Helper_Utility::is_password_strength_meter_enabled()
		);

		/**
		 * Filter localize script to allow data insertion.
		 *
		 * @since 2.0
		 *
		 * @param (array) $localize_array.
		 **/
		$localize_array = apply_filters( 'coursepress_localize_object', $localize_array );

		wp_localize_script( 'coursepress-front-js', '_coursepress', $localize_array );

		$script = CoursePress::$url . 'asset/js/external/video.min.js';
		wp_enqueue_script(
			'coursepress-video-js',
			$script,
			array('jquery'),
			CoursePress::$version
		);

		$script = CoursePress::$url . 'asset/js/external/video-youtube.min.js';
		wp_enqueue_script(
			'coursepress-video-youtube-js',
			$script,
			array('jquery', 'coursepress-video-js'),
			CoursePress::$version
		);

		$script = CoursePress::$url . 'asset/js/external/videojs-vimeo.min.js';
		wp_enqueue_script(
			'coursepress-videojs-vimeo-js',
			$script,
			array('jquery', 'coursepress-video-js'),
			'3.0.0'
		);

		$video_js = CoursePress::$url . 'asset/css/external/video-js.min.css';
		wp_enqueue_style(
			'coursepress-video-js-style',
			$video_js,
			array(),
			CoursePress::$version
		);
	}

	/**
	 * coursepress-front_js
	 *
	 * @since 2.0.7
	 */
	public function coursepress_front_js() {
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
			CoursePress::$version,
			true
		);
	}
}
