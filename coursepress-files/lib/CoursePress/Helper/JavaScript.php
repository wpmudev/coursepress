<?php

class CoursePress_Helper_JavaScript {

	public static function init() {

		// These don't work here because of core using wp_print_styles()
		//add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		add_action( 'admin_footer', array( __CLASS__, 'enqueue_scripts' ) );
		//add_action( 'wp_footer', array( __CLASS__, 'enqueue_scripts' ) );

		//add_filter( 'script_loader_src', array( __CLASS__, 'disable_autosave' ), 10, 2 );

		//add_action( 'wp_print_scripts', array( __CLASS__, 'disable' ) );
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

	public static function disable_autosave( $src, $handle ) {

		global $wp_scripts;
		///** COURSEPRESS_COURSE|UNIT BUILDER */
		//if ( isset( $_GET['page'] ) && 'coursepress_course' === $_GET['page'] && isset( $_GET['tab'] ) && "units" === $_GET['tab']  ) {
		//	// We're taking care of saving, so 'autosave' gets in the way.
		//	wp_dequeue_script('autosave');
		//	wp_deregister_script( 'autosave' );
		//	error_log('this happened');
		//
		//}
		$no_script = array(
			'autosave',
			'revisions',
			//'wpemoji',
			//'twemoji',
			//'jquery-core',
			//'jquery-migrate',
			//						'utils',
			//						'plupload',
			//'json2',
			//						'hoverIntent',
			//'common',
			//'admin-bar',
			//						'svg-painter',
			//						'heartbeat',
			//						'wp-auth-check',
			//'underscore',
			//'shortcode',
			//'backbone',
			//'wp-util',
			//'wp-backbone',
			//'media-models',
			//'wp-plupload',
			//'jquery-ui-core',
			//'jquery-ui-widget',
			//'jquery-ui-mouse',
			//'jquery-ui-sortable',
			//						'mediaelement',
			//						'wp-mediaelement',
			//						'media-views',
			//						'media-editor',
			//						'media-audiovideo',
			//						'wp-playlist',
			//						'mce-view',
			//'imgareaselect',
			//'image-edit',
			//'query-monitor',
			//'coursepress_admin_general_js',
			//'sticky_js',
			//'chosen_js',
			//'coursepress_object',
			//'jquery-ui-accordion',
			//'jquery-effects-core',
			//'jquery-effects-highlight',
			//'jquery-ui-datepicker',
			//'jquery-ui-button',
			//'jquery-ui-spinner',
			//'coursepress_course',
			//'jquery-treegrid',
			//'coursepress_unit_builder',
			'word-count',
			//'editor',
			//'quicktags',
			//'wplink',
			//'thickbox',
			//'media-upload',
		);

		//
		error_log( $handle );
		//$registered = array_keys( $wp_scripts->registered );
		if ( ! in_array( $handle, $no_script ) ) {
			return $src;
		};


	}

	public static function disable() {

		global $wp_scripts;

		$no_script = array(
			//'word-count',
			//'editor-expand',
			//'set-post-thumbnail',
			//'swfupload-handlers',
			//									'comment-reply',
			//'json2',
			//'underscore',
			//'backbone',
			//									'wp-util',
			//									'wp-backbone',
			//									'revisions',
			//									'imgareaselect',
			//									'mediaelement',
			//									'wp-mediaelement',
			//									'froogaloop',
			//									'wp-playlist',
			//									'zxcvbn-async',
			//									'password-strength-meter',
			//									'user-profile',
			//									'language-chooser',
			//									'user-suggest',
			//									'admin-bar',
			//									'wplink',
			//									'wpdialogs',
			//									'media-upload',
			//									'hoverIntent',
			//									'customize-base',
			//									'customize-loader',
			//									'customize-preview',
			//									'customize-models',
			//									'customize-views',
			//									'customize-controls',
			//									'customize-widgets',
			//									'customize-preview-widgets',
			//									'accordion',
			//									'shortcode',
			//									'media-models',
			//									'media-views',
			//									'media-editor',
			//									'media-audiovideo',
			//									'mce-view',
			//										'admin-tags',
			//										'admin-comments',
			//									'xfn',
			//									'postbox',
			//									'tags-box',
			//									'post',
			//									'press-this',
			//									'link',
			//									'comment',
			//									'admin-gallery',
			//									'admin-widgets',
			//									'theme',
			//									'inline-edit-post',
			//									'inline-edit-tax',
			//									'plugin-install',
			//									'updates',
			//									'farbtastic',
			//									'iris',
			//									'wp-color-picker',
			//									'dashboard',
			//									'list-revisions',
			//									'media-grid',
			//									'media',
			//									'image-edit',
			//									'nav-menu',
			//									'custom-header',
			//									'custom-background',
			//									'media-gallery',
			//									'svg-painter',
			//									'debug-bar',
			//									'query-monitor',
			//'coursepress_admin_general_js',
			//'sticky_js',
			//'chosen_js',
		);

		foreach ( $no_script as $handle ) {
			//error_log( $key );
			unset( $wp_scripts->registered[ $handle ] );
		}

		//unset( $wp_scripts->registered['autosave'] );

		//error_log( isset( $wp_scripts->registered['autosave'] ) ? 'still there' : 'all gone!' );

	}

}