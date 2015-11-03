<?php

class CoursePress_Helper_JavaScript {

	public static function init() {

		// These don't work here because of core using wp_print_styles()
		//add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		add_action( 'admin_footer', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( __CLASS__, 'enqueue_front_scripts' ) );

	}


	public static function enqueue_admin_scripts() {
		// Enqueue needed scripts for UI

		wp_enqueue_media();
	}

	public static function enqueue_scripts() {

		$course_js_pages = array(
			'coursepress_course',
			'coursepress_assessments',
			'coursepress_reports',
			'coursepress_notifications',
			'coursepress_discussions'
		);

		$valid_pages = array_merge( $course_js_pages, array(
			'coursepress_settings',
			'coursepress',
		) );


		if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $valid_pages ) ) {
			return;
		}

		$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePress.js';

		wp_enqueue_script( 'coursepress_object', $script, array(
			'jquery',
			'backbone',
			'underscore'
		), CoursePress_Core::$version );

		// Create a dummy editor to by used by the CoursePress JS object
		ob_start();
		wp_editor( 'dummy_editor_content', 'dummy_editor_id', array( 'wpautop'       => false,
		                                                             "textarea_name" => 'dummy_editor_name',
		) );
		$dummy_editor = ob_get_clean();

		$localize_array = array(
			'_ajax_url'                 => CoursePress_Helper_Utility::get_ajax_url(),
			'_dummy_editor'             => $dummy_editor,
			'allowed_video_extensions'  => wp_get_video_extensions(),
			'allowed_audio_extensions'  => wp_get_audio_extensions(),
			'allowed_image_extensions'  => CoursePress_Helper_Utility::get_image_extensions(),
			'allowed_extensions'        => apply_filters( 'coursepress_custom_allowed_extensions', false ),
			'date_format'               => get_option( 'date_format' ),
			'editor_visual'             => __( 'Visual', CoursePress::TD ),
			'editor_text'               => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', CoursePress::TD ),
			'invalid_extension_message' => __( 'Extension of the file is not valid. Please use one of the following:', CoursePress::TD ),
			'assessment_grid_url'       => admin_url( 'admin.php?page=coursepress_assessments' ),
			'assessment_report_url'       => admin_url( 'admin.php?page=coursepress_reports' )
		);


		// Models

		/** COURSEPRESS_COURSE */
		if ( in_array( $_GET['page'], $course_js_pages ) ) {
			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePress/Course.js';
			wp_enqueue_script( 'coursepress_course', $script, array(
				'jquery-ui-accordion',
				'jquery-effects-highlight',
				'jquery-effects-core',
				'jquery-ui-datepicker',
				'jquery-ui-spinner',
				'jquery-ui-droppable',
				'backbone',
			), CoursePress_Core::$version );

			$script = CoursePress_Core::$plugin_lib_url . 'scripts/external/jquery.treegrid.min.js';

			wp_enqueue_script( 'jquery-treegrid', $script, array(
				'jquery'
			), CoursePress_Core::$version );

			$localize_array['instructor_role_defined']          = defined('COURSEPRESS_INSTRUCTOR_ROLE');
			$localize_array['instructor_avatars']               = CoursePress_Helper_UI::get_user_avatar_array();
			$localize_array['instructor_delete_confirm']        = __( 'Please confirm that you want to remove the instructor from this course.', CoursePress::TD );
			$localize_array['instructor_delete_invite_confirm'] = __( 'Please confirm that you want to remove the instructor invitation from this course.', CoursePress::TD );
			$localize_array['instructor_empty_message']         = __( 'Please Assign Instructor', CoursePress::TD );
			$localize_array['instructor_pednding_status']       = __( 'Pending', CoursePress::TD );
			$localize_array['email_validation_pattern']         = __( '.+@.+', CoursePress::TD );
			$localize_array['student_delete_confirm']        = __( 'Please confirm that you want to remove the student from this course.', CoursePress::TD );
			$localize_array['student_delete_all_confirm']        = __( 'Please confirm that you want to remove ALL students from this course. Warning: This can not be undone. Please make sure this is what you want to do.', CoursePress::TD );

			// Discussion / Notification
			$localize_array['notification_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected notifications. Warning: This cannot be undone. Please make sure this is what you want to do.', CoursePress::TD );
			$localize_array['notification_delete'] = __( 'Please confirm that you want to delete this notification. Warning: This cannot be undone.', CoursePress::TD );

			$localize_array['discussion_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected discussions. Warning: This cannot be undone. Please make sure this is what you want to do.', CoursePress::TD );
			$localize_array['discussion_delete'] = __( 'Please confirm that you want to delete this discussion. Warning: This cannot be undone.', CoursePress::TD );


			if ( ! empty( $_REQUEST['id'] ) ) {
				$localize_array['course_id'] = (int) $_REQUEST['id'];
			}
		}

		/** COURSEPRESS_COURSE|UNIT BUILDER */
		if ( 'coursepress_course' === $_GET['page'] && isset( $_GET['tab'] ) && "units" === $_GET['tab'] ) {

			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePress/UnitsBuilder.js';

			wp_enqueue_script( 'coursepress_unit_builder', $script, array(
				'coursepress_course',
			), CoursePress_Core::$version );

			$localize_array['unit_builder_templates']     = CoursePress_Helper_UI_Module::get_template( true );
			$localize_array['unit_builder_module_types']  = CoursePress_Helper_UI_Module::get_types();
			$localize_array['unit_builder_module_labels'] = CoursePress_Helper_UI_Module::get_labels();
			$localize_array['unit_builder_delete_module_confirm'] = __( 'Please confirm that you want to remove this module and possible student responses.', CoursePress::TD );
			$localize_array['unit_builder_delete_page_confirm'] = __( 'Please confirm that you want to remove this page. All modules will be moved to the first available page (or you can drop them on other pages first before deleting this page).', CoursePress::TD );
			$localize_array['unit_builder_delete_unit_confirm'] = __( 'Please confirm that you want to remove this unit and all its modules and student responses.', CoursePress::TD );
			$localize_array['unit_builder_new_unit_title'] = __( 'Untitled Unit', CoursePress::TD );
			$localize_array['unit_builder_add_answer_label'] = __( 'Add Answer', CoursePress::TD );

		}

		/** COURSE LIST */
		if ( 'coursepress' === $_GET['page'] ) {
			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePress/CourseList.js';
			wp_enqueue_script( 'coursepress_course_list', $script, array(
				'jquery-ui-accordion',
				'jquery-effects-highlight',
				'jquery-effects-core',
				'jquery-ui-datepicker',
				'jquery-ui-spinner',
				'jquery-ui-droppable',
				'backbone',
			), CoursePress_Core::$version );

			$localize_array['courselist_bulk_delete'] = __( 'Please confirm that you want to delete ALL selected courses. Warning: This cannot be undone. Please make sure this is what you want to do.', CoursePress::TD );
			$localize_array['courselist_delete_course'] = __( 'Please confirm that you want to delete this courses. Warning: This cannot be undone.', CoursePress::TD );
			$localize_array['courselist_duplicate_course'] = __( 'Are you sure you want to create a duplicate copy of this course?', CoursePress::TD );
		}


		wp_localize_script( 'coursepress_object', '_coursepress', $localize_array );

	}

	public static function enqueue_front_scripts() {
		global $wp_query;

		$post_type = get_post_type();

		$valid_cpt = array(
			CoursePress_Model_Course::get_post_type_name(),
			'course_notifications_archive',
			'course_workbook',
			'course_discussion_archive',
			'course_discussion',
			'course_archive',
			'coursepress_instructor', // virtual post type
			'coursepress_student_dashboard',
			'coursepress_student_login',
			'coursepress_student_signup',
			CoursePress_Model_Discussion::get_post_type_name(),
		);

		if (
			( ! empty( $post_type ) && in_array( $post_type, $valid_cpt ) ) ||
		    array_key_exists( 'course', $wp_query->query ) || array_key_exists( 'coursename', $wp_query->query )
		) {

			// CoursePress Object
			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePress.js';
			wp_enqueue_script( 'coursepress_object', $script, array(
				'jquery',
				'backbone',
				'underscore'
			), CoursePress_Core::$version );


			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePressFront.js';

			$localize_array = array(
				'_ajax_url'                  => CoursePress_Helper_Utility::get_ajax_url(),
				'allowed_video_extensions'   => wp_get_video_extensions(),
				'allowed_audio_extensions'   => wp_get_audio_extensions(),
				'allowed_image_extensions'   => CoursePress_Helper_Utility::get_image_extensions(),
				'allowed_extensions'         => apply_filters( 'coursepress_custom_allowed_extensions', false ),
				'allowed_student_extensions' => CoursePress_Helper_Utility::allowed_student_mimes(),
				'no_browser_upload'          => __( 'Please try a different browser to upload your file.', CoursePress::TD ),
				'invalid_upload_message'     => __( 'Please only upload any of the following files: ', CoursePress::TD ),
				'file_uploaded_message'      => __( 'Your file has been submitted successfully.', CoursePress::TD ),
				'file_upload_fail_message'   => __( 'There was a problem processing your file.', CoursePress::TD ),
				'response_saved_message'     => __( 'Your response was recorded successfully.', CoursePress::TD ),
				'response_fail_message'      => __( 'There was a problem saving your response. Please reload this page and try again.', CoursePress::TD ),
				'current_course'             => CoursePress_Helper_Utility::the_course( true ),
				'course_url'                 => get_permalink( CoursePress_Helper_Utility::the_course( true ) ),
				'home_url'                   => home_url(),
				'current_student'            => get_current_user_id(),
				'workbook_view_answer'       => __( 'View', CoursePress::TD ),
				'labels'                     => CoursePress_Helper_UI_Module::get_labels(),
				'signup_errors'              => array(
					'all_fields' => __( 'All fields required.', CoursePress::TD ),
					'email_invalid' => __( 'Invalid e-mail address.', CoursePress::TD ),
					'email_exists' => __( 'That e-mail address is already taken.', CoursePress::TD ),
					'user_exists' => __( 'That usernam is already taken.', CoursePress::TD ),
					'weak_password' => __( 'Weak passwords not allowed.', CoursePress::TD ),
					'mismatch_password' => __( 'Passwords do not match.', CoursePress::TD )
				)
			);

			wp_enqueue_script( 'coursepress_front', $script, array(
				'jquery',
				'jquery-ui-dialog',
				'underscore',
				'backbone'
			), CoursePress_Core::$version );

			wp_localize_script( 'coursepress_object', '_coursepress', $localize_array );

			//$script = CoursePress_Core::$plugin_lib_url . 'scripts/external/jquery.knob.js';
			//
			//wp_enqueue_script( 'jquery-knob', $script, array(
			//	'jquery'
			//), CoursePress_Core::$version );

			$script = CoursePress_Core::$plugin_lib_url . 'scripts/external/circle-progress.min.js';
			wp_enqueue_script( 'circle-progress', $script, array(
				'jquery'
			), CoursePress_Core::$version );

			$script = CoursePress_Core::$plugin_lib_url . 'scripts/external/backbone.modal-min.js';
			wp_enqueue_script( 'backbone-modal', $script, array(
				'backbone',
				'password-strength-meter'
			), CoursePress_Core::$version );

			$fontawesome  = CoursePress_Core::$plugin_lib_url . 'styles/font-awesome.min.css';
			wp_enqueue_style( 'fontawesome', $fontawesome, array(), CoursePress_Core::$version );

		}



	}

}
