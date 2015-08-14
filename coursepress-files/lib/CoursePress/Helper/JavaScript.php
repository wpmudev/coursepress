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

		$valid_pages = array( 'coursepress_settings', 'coursepress_course', 'coursepress' );

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
				'jquery-ui-droppable',
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
			$localize_array['student_delete_confirm']        = __( 'Please confirm that you want to remove the student from this course.', CoursePress::TD );
			$localize_array['student_delete_all_confirm']        = __( 'Please confirm that you want to remove ALL students from this course. Warning: This can not be undone. Please make sure this is what you want to do.', CoursePress::TD );

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
		if ( ! empty( $post_type ) && $post_type === 'course' ) {

			$script = CoursePress_Core::$plugin_lib_url . 'scripts/CoursePressFront.js';

			wp_enqueue_script( 'coursepress_object', $script, array(
				'jquery'
			), CoursePress_Core::$version );

		}



	}

}