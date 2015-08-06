<?php

class CoursePress_Helper_JavaScript {

	public static function init() {

		// These don't work here because of core using wp_print_styles()
		//add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		add_action( 'admin_footer', array( __CLASS__, 'enqueue_scripts' ) );
		//add_action( 'wp_footer', array( __CLASS__, 'enqueue_scripts' ) );

	}


	public static function enqueue_admin_scripts() {
		// Enqueue needed scripts for UI
		wp_enqueue_media();
	}

	public static function enqueue_scripts() {

		$valid_pages = array( 'coursepress_settings', 'coursepress_course' );

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
			'date_format'               => get_option( 'date_format' ),
			'editor_visual'             => __( 'Visual', CoursePress::TD ),
			'editor_text'               => _x( 'Text', 'Name for the Text editor tab (formerly HTML)', CoursePress::TD ),
			'invalid_extension_message' => __( 'Extension of the file is not valid. Please use one of the following:', CoursePress::TD ),
		);


		// Models

		/** COURSEPRESS_COURSE */
		if ( 'coursepress_course' === $_GET['page'] ) {
			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePress/Course.js';

			wp_enqueue_script( 'coursepress_course', $script, array(
				'jquery-ui-accordion',
				'jquery-effects-highlight',
				'jquery-effects-core',
				'jquery-ui-datepicker',
				'jquery-ui-spinner',
				'backbone',
			), CoursePress_Core::$version );

			$script = CoursePress_Core::$plugin_lib_url . 'scripts/external/jquery.treegrid.min.js';

			wp_enqueue_script( 'jquery-treegrid', $script, array(
				'jquery'
			), CoursePress_Core::$version );

			$localize_array['instructor_avatars']               = CoursePress_Helper_UI::get_user_avatar_array();
			$localize_array['instructor_delete_confirm']        = __( 'Please confirm that you want to remove the instructor from this course.', CoursePress::TD );
			$localize_array['instructor_delete_invite_confirm'] = __( 'Please confirm that you want to remove the instructor invitation from this course.', CoursePress::TD );
			$localize_array['instructor_empty_message']         = __( 'Please Assign Instructor', CoursePress::TD );
			$localize_array['instructor_pednding_status']       = __( 'Pending', CoursePress::TD );
			$localize_array['email_validation_pattern']         = __( '.+@.+', CoursePress::TD );

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
		}

		wp_localize_script( 'coursepress_object', '_coursepress', $localize_array );

	}

}