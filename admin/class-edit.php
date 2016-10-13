<?php
/**
 * CoursePress
 **/
class CoursePress_Admin_Edit {
	public static $slug = 'coursepress_course';
	private static $action = 'new';
	private static $allowed_actions = array(
		'new',
		'edit',
	);
	private static $tabs = array();
	private static $current_course = false;

	public static function init_hooks( $post ) {
		$post_type = CoursePress_Data_Course::get_post_type_name();

		if ( $post->post_type != $post_type ) {
			return;
		}
		self::$current_course = $post;

		if ( 'auto-draft' !== $post->post_status || ! empty( $_GET['post'] ) ) {
			self::$action = 'edit';
		}

		$tab = empty( $_GET['tab'] ) ? 'setup' : $_GET['tab'];
		add_action( 'edit_form_top', array( __CLASS__, 'edit_tabs' ) );

		// No extra metabox please !!!
		remove_all_actions( 'add_meta_boxes' );

		if ( 'setup' == $tab ) {
			// Disable permalink
			add_filter( 'get_sample_permalink_html', array( __CLASS__, 'disable_permalink' ), 100, 5 );
	
			// Start wrapper
			add_action( 'edit_form_after_editor', array( __CLASS__, 'start_wrapper' ) );
	
			// Step 1
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_1' ) );
			// Step 2
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_2' ) );
			// Step 3
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_3' ) );
			// Step 4
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_4' ) );
			// Step 5
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_5' ) );
			// Step 6
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_6' ) );
			// Step 7
			add_action( 'edit_form_after_editor', array( __CLASS__, 'step_7' ) );
			// Allow hooks for additional steps
			add_action( 'edit_form_after_editor', array( __CLASS__, 'other_steps' ) );

			// End wrapper
			add_action( 'edit_form_after_editor', array( __CLASS__, 'end_wrapper' ) );
		} else {
			$_GET['id'] = $_REQUEST['id'] = self::$current_course->ID;
			add_action( 'add_meta_boxes', array( __CLASS__, 'disable_meta_boxes' ), 1 );
		}
	}

	public static function disable_meta_boxes() {
		global $wp_meta_boxes;
		$wp_meta_boxes = array();
		?>
		<style>
		#postbox-container-1,
		#postbox-container-2 {
			display: none;
		}
		</style>
		<?php
	}

	public static function disable_permalink( $return, $post_id, $new_title, $new_slug, $post ) {
		return '';
	}

	private static function _current_action() {
		return self::$action;
	}

	public static function get_tabs() {

		// Make it a filter so we can add more tabs easily
		self::$tabs = apply_filters( self::$slug . '_tabs', self::$tabs );

		self::$tabs['setup'] = array(
			'title' => __( 'Course Setup', 'cp' ),
			'description' => __( 'Edit your course specific settings below.', 'cp' ),
			'order' => 10,
			'buttons' => 'none',
		);
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		if ( 'edit' == self::_current_action() ) {
			if ( CoursePress_Data_Capabilities::can_view_course_units( $course_id ) ) {
				$units = CoursePress_Data_Course::get_unit_ids( $course_id, array( 'publish', 'draft' ) );
				self::$tabs['units'] = array(
					'title' => sprintf( __( 'Units (%s)', 'cp' ), count( $units ) ),
					'description' => __( 'Edit your course specific settings below.', 'cp' ),
					'order' => 20,
					'buttons' => 'none',
					'is_form' => false,
				);
			}

			if ( CoursePress_Data_Capabilities::can_view_course_students( $course_id ) ) {
				self::$tabs['students'] = array(
					'title' => sprintf(
						__( 'Students (%s)', 'cp' ),
						CoursePress_Data_Course::count_students( $course_id )
					),
					'description' => __( 'Edit your course specific settings below.', 'cp' ),
					'order' => 30,
					'buttons' => 'none',
				);
			}
		}

		// Make sure that we have all the fields we need
		foreach ( self::$tabs as $key => $tab ) {
			self::$tabs[ $key ]['url'] = add_query_arg( 'tab', $key );
			self::$tabs[ $key ]['buttons'] = isset( $tab['buttons'] ) ? $tab['buttons'] : 'both';
			self::$tabs[ $key ]['class'] = isset( $tab['class'] ) ? $tab['class'] : '';
			self::$tabs[ $key ]['is_form'] = isset( $tab['is_form'] ) ? $tab['is_form'] : true;
			self::$tabs[ $key ]['order'] = isset( $tab['order'] ) ? $tab['order'] : 999; // Set default order to 999... bottom of the list
		}

		// Order the tabs
		self::$tabs = CoursePress_Helper_Utility::sort_on_key( self::$tabs, 'order' );

		return self::$tabs;
	}

	public static function edit_tabs() {
		$course = self::$current_course;
		$tabs = self::get_tabs();
		$tab_keys = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';
		$tab = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );

		$method = preg_replace( '/\_$/', '', 'render_tab_' . $tab );
		$content = '';

		if ( method_exists( __CLASS__, $method ) ) {
			$content = call_user_func( __CLASS__ . '::' . $method );
		}

		$hidden_args = $_GET;
		unset( $hidden_args['_wpnonce'] );

		// Publish Course Toggle
		$course_id = $course->ID;
		$status = get_post_status( $course_id );
		$user_id = get_current_user_id();
		$publish_toggle = '';

		$ui = array(
			'label' => 'Publish Course',
			'left' => '<i class="fa fa-ban"></i>',
			'left_class' => 'red',
			'right' => '<i class="fa fa-check"></i>',
			'right_class' => 'green',
			'state' => 'publish' === $status ? 'on' : 'off',
			'data' => array(
				'nonce' => wp_create_nonce( 'publish-course' ),
			),
		);
		$ui['class'] = 'course-' . $course_id;
		$publish_toggle = '';
		if ( CoursePress_Data_Capabilities::can_change_course_status( $course_id ) ) {
			$publish_toggle = ! empty( $course_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-course-toggle', 'publish-course-toggle', $ui ) : '';
		}
		echo CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $hidden_args, self::$slug, $tab, false, 'horizontal', $publish_toggle );
	}

	public static function start_wrapper() {
		// Setup Nonce
		$setup_nonce = wp_create_nonce( 'setup-course' );

		CoursePress_View_Admin_Course_Edit::$current_course = self::$current_course;

		echo '<div class="coursepress-course-step-container">
			<div id="course-setup-steps" data-nonce="' . $setup_nonce . '">';
	}

	public static function end_wrapper() {
		echo '</div></div>';
	}

	private static function render_tab_units() {
		return CoursePress_Admin_Controller_Unit::render();
	}

	private static function render_tab_students() {
		return CoursePress_View_Admin_Course_Student::render();
	}

	/**
	 * Build course step buttons
	 *
	 * @since 2.0.0
	 *
	 * @param integer $course_id Course ID.
	 * @param integer $step Step.
	 * @param array $args Array of buttons to show info, default is true, use
	 * to disable selected button e.g. 'next' => false
	 *
	 * @return string Buttons.
	 */
	private static function get_buttons( $course_id, $step, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'previous' => true,
				'next' => true,
				'update' => true,
			)
		);
		$content = '';

		if ( $args['previous'] ) {
			$content .= sprintf(
				'<input type="button" class="button step prev step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Previous', 'cp' )
			);
		}

		if ( $args['next'] ) {
			$content .= sprintf(
				'<input type="button" class="button step next step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Next', 'cp' )
			);
		}

		// Finish button
		if ( 7 == $step ) {
			$content .= sprintf(
				'<input type="button" class="button step finish step-7" value="%s" />',
				esc_attr__( 'Finish', 'cp' )
			);
		}
		/**
		 * update button
		 */
		if ( $args['update'] && CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
			$content .= sprintf(
				'<input type="button" class="button step update hidden step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Update', 'cp' )
			);
		}
		/**
		 * if empty, do not use wrapper!
		 */
		if ( empty( $content ) ) {
			return $content;
		}
		return sprintf( '<div class="wide course-step-buttons">%s</div>', $content );
	}

	/**
	 * Get Wp Editor.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $editor_id WP Editor ID
	 * @param string $editor_name WP Editor name
	 * @param string $editor_content Edited content.
	 * @param array $args WP Editor args, see
	 * https://codex.wordpress.org/Function_Reference/wp_editor#Parameters
	 * @return string WP Editor.
	 */
	private static function get_wp_editor( $editor_id, $editor_name, $editor_content = '', $args = array() ) {
		$defaults = array(
			'textarea_name' => $editor_name,
			'editor_class' => 'cp-editor cp-course-overview',
			'media_buttons' => false,
			'tinymce' => array(
				'height' => '300',
			),
		);
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );
		ob_start();
		wp_editor( $editor_content, $editor_id, $args );
		$editor_html = ob_get_clean();
		return $editor_html;
	}

	public static function step_1() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_1', '' );
		$setup_class = ( (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 6 ) || ( (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 0 ) ? $setup_class . ' setup_marker' : $setup_class;

		$content = '
			<div class="step-title step-1">' . esc_html__( 'Step 1 – Course Overview', 'cp' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-1">
				<input type="hidden" name="meta_setup_step_1" value="saved" />
			';

		// Course ID
		$content .= '<input type="hidden" name="course_id" value="' . $course_id . '" />';

		// Course Name
		$course_name = ! empty( self::$current_course ) ? self::$current_course->post_title : '';
		$content .= '
				<div class="wide">
						<label for="course_name" class="required first">' .
					esc_html__( 'Title', 'cp' ) . '
						</label>
						<input class="wide" type="text" name="course_name" id="course_name" value="' . $course_name . '"/>
				</div>';

		$content .= apply_filters( 'coursepress_course_setup_step_1_after_title', '', $course_id );

		// Course Excerpt / Short Overview
		$editor_content = ! empty( self::$current_course ) ? htmlspecialchars_decode( self::$current_course->post_excerpt ) : '';
		$editor_html = self::get_wp_editor( 'courseExcerpt', 'course_excerpt', $editor_content, array( 'teeny' => true ) );

		$content .= '
				<div class="wide">
						<label for="courseExcerpt" class="required drop-line">' .
					esc_html__( 'Short Overview', 'cp' ) . '
						</label>
						' . $editor_html . '
				</div>';

		$content .= apply_filters( 'coursepress_course_setup_step_1_after_excerpt', '', $course_id );

		// Listing Image
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_listing_image',
			'meta_listing_image',
			array(
				'placeholder' => __( 'Add Image URL or Browse for Image', 'cp' ),
				'title' => __( 'Feature Image', 'cp' ),
				'value' => CoursePress_Data_Course::get_listing_image( $course_id ),
			)
		);

		// Course Category
		$category = CoursePress_Data_Course::get_post_category_name();
		$cpt = CoursePress_Data_Course::get_post_type_name();
		$url = 'edit-tags.php?taxonomy=' . $category . '&post_type=' . $cpt;
		$terms = CoursePress_Data_Course::get_terms();
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$course_terms_array = CoursePress_Data_Course::get_course_terms( $id, true );

		$class_extra = is_rtl() ? 'chosen-rtl' : '';

		// Course Language
		$language = CoursePress_Data_Course::get_setting( $course_id, 'course_language' );
		if ( empty( $language ) ) {
			$language = __( 'English', 'cp' );
		}
		$content .= '
				<div class="wide">
						<label for="meta_course_language">' .
					esc_html__( 'Language', 'cp' ) . '
						</label>
						<input class="medium" type="text" name="meta_course_language" id="meta_course_language" value="' . $language . '"/>
				</div>';

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_1', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 1, array( 'previous' => false ) );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_2() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_2', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 1 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-2">' . esc_html__( 'Step 2 – Course Details', 'cp' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-2">
				<input type="hidden" name="meta_setup_step_2" value="saved" />
			';

		// Featured Video
		$supported_ext = implode( ', ', wp_get_video_extensions() );
		$placeholder = sprintf( __( 'Add URL or Browse ( %s )', 'cp' ), $supported_ext );
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_featured_video',
			'meta_featured_video',
			array(
				'placeholder' => $placeholder,
				'title' => __( 'Featured Video', 'cp' ),
				'value' => CoursePress_Data_Course::get_setting( $course_id, 'featured_video' ),
				'type' => 'video',
				'description' => __( 'This is used on the Course Overview page and will be displayed with the course description.', 'cp' ),
			)
		);

		// Course Description
		$editor_content = ! empty( self::$current_course ) ? htmlspecialchars_decode( self::$current_course->post_content ) : '';
		$args = array(
			'media_buttons' => true,
		);
		$editor_html = self::get_wp_editor( 'courseDescription', 'course_description', $editor_content, $args );

		$content .= '
				<div class="wide">
						<label for="courseDescription" class="required">' .
					esc_html__( 'Full Description', 'cp' ) . '
						</label><br />
						' . $editor_html . '
				</div>';

		$content .= '
				<div class="wide">
						<label>' .
						esc_html__( 'View Mode', 'cp' ) . '
						</label>
						<label class="checkbox">
							<input type="radio" name="meta_course_view" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'normal' ), 'normal' ) . ' value="normal">' . esc_html__( 'Normal: Show full unit pages', 'cp' ) . '<br />
							<input type="radio" name="meta_course_view" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'focus' ), 'focus' ) . ' value="focus">' . esc_html__( 'Focus: Focus on one item at a time', 'cp' ) . '<br />
							<input type="checkbox" name="meta_focus_hide_section" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'focus_hide_section', true ) ) . ' value="unit">' . esc_html__( 'Don\'t render section titles in focus mode.', 'cp' ) . '<br />
							<p class="description">' . esc_html__( 'Choose if your course will show in "normal" mode or step by step "focus" mode.', 'cp' ) . '</p>
						</label>
						<label class="checkbox">
							<input type="radio" name="meta_structure_level" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_level', 'unit' ), 'unit' ) . ' value="unit">' . esc_html__( 'Unit list only', 'cp' ) . '<br />
							<input type="radio" name="meta_structure_level" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_level', 'section' ), 'section' ) . ' value="section">' . esc_html__( 'Expanded unit list', 'cp' ) . '<br />
							<p class="description">' . esc_html__( 'Choose if course Unit page shows units only or in expanded view.', 'cp' ) . '</p>
						</label>
				</div>';

		// Course Structure
		$content .= '
				<div class="wide">
					<label>' . esc_html__( 'Course Structure', 'cp' ) . '</label>
					<p>' . esc_html__( 'This gives you the option to show/hide Course Units, Lessons, Estimated Time and Free Preview options on the Course Overview page', 'cp' ) . '</p>

					<div class="course-structure">

						<label class="checkbox">
							<input type="checkbox" name="meta_structure_visible" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_visible', true ) ) . ' />
							<span>' . esc_html__( 'Show the Course Overview structure and Preview Options', 'cp' ) . '</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" name="meta_structure_show_duration" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_show_duration', true ) ) . ' />
							<span>' . esc_html__( 'Display Time Estimates for Units and Lessons', 'cp' ) . '</span>
						</label>


						<table class="course-structure-tree">
							<thead>
								<tr>
									<th class="column-course-structure">' . esc_html__( 'Course Structure', 'cp' ) . ' <small>' . esc_html__( 'Units and Pages with Modules selected will automatically be visible (only selected Modules accessible).', 'cp' ) . '</small></th>
									<th class="column-show">' . esc_html__( 'Show', 'cp' ) . '</th>
									<th class="column-free-preview">' . esc_html__( 'Free Preview', 'cp' ) . '</th>
									<th class="column-time">' . esc_html__( 'Time', 'cp' ) . '</th>
								</tr>
								<tr class="break"><th colspan="4"></th></tr>
							</thead>
							<tfoot>
								<tr class="break"><th colspan="4"></th></tr>
								<tr>
									<th class="column-course-structure">' . esc_html__( 'Course Structure', 'cp' ) . '</th>
									<th class="column-show">' . esc_html__( 'Show', 'cp' ) . '</th>
									<th class="column-free-preview">' . esc_html__( 'Free Preview', 'cp' ) . '</th>
									<th class="column-time">' . esc_html__( 'Time', 'cp' ) . '</th>
								</tr>
							</tfoot>
							<tbody>';

		$units = CoursePress_Data_Course::get_units_with_modules( $course_id, array( 'publish', 'draft' ) );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		$count = 0;
		$visible_units = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_units', array() );
		$preview_units = CoursePress_Data_Course::get_setting( $course_id, 'structure_preview_units', array() );
		$visible_pages = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_pages', array() );
		$preview_pages = CoursePress_Data_Course::get_setting( $course_id, 'structure_preview_pages', array() );
		$visible_modules = CoursePress_Data_Course::get_setting( $course_id, 'structure_visible_modules', array() );
		$preview_modules = CoursePress_Data_Course::get_setting( $course_id, 'structure_preview_modules', array() );

		foreach ( $units as $unit ) {

			$estimations = CoursePress_Data_Unit::get_time_estimation( $unit['unit']->ID, $units );
			$count += 1;
			$status = 'publish' === $unit['unit']->post_status ? '' : __( '[DRAFT] ', 'cp' );
			$draft_class = 'publish' === $unit['unit']->post_status ? '' : 'draft';

			$alt = $count % 2 ? 'even' : 'odd';

			$unit_view_checked = isset( $visible_units[ $unit['unit']->ID ] ) ? CoursePress_Helper_Utility::checked( $visible_units[ $unit['unit']->ID ] ) : false;
			$unit_preview_checked = isset( $preview_units[ $unit['unit']->ID ] ) ? CoursePress_Helper_Utility::checked( $preview_units[ $unit['unit']->ID ] ) : false;
			$content .= '
								<tr class="unit unit-' . $unit['unit']->ID . ' treegrid-' . $count . ' ' . $draft_class . ' ' . $alt . '" data-unitid="'. $unit['unit']->ID . '">
									<td>' . $status . $unit['unit']->post_title . '</td>
									<td><input type="checkbox" name="meta_structure_visible_units[' . $unit['unit']->ID . ']" value="1" ' . $unit_view_checked . '/></td>
									<td><input type="checkbox" name="meta_structure_preview_units[' . $unit['unit']->ID . ']" value="1" ' . $unit_preview_checked . '/></td>
									<td>' . $estimations['unit']['estimation'] . '</td>
								</tr>
			';

			$unit_parent = $count;
			if ( ! isset( $unit['pages'] ) ) {
				$unit['pages'] = array();
			}

			foreach ( $unit['pages'] as $key => $page ) {
				$count += 1;
				$page_title = ! empty( $page['title'] ) ? $page['title'] : sprintf( __( 'Page %s', 'cp' ), $key );

				$page_key = (int) $unit['unit']->ID . '_' . (int) $key;

				$page_view_checked = isset( $visible_pages[ $page_key ] ) && '' !== $visible_pages[ $page_key ] ? CoursePress_Helper_Utility::checked( $visible_pages[ $page_key ] ) : '';
				$page_preview_checked = isset( $preview_pages[ $page_key ] ) && '' != $preview_pages[ $page_key ] ? CoursePress_Helper_Utility::checked( $preview_pages[ $page_key ] ) : '';
				$alt = $count % 2 ? 'even' : 'odd';
				$duration = ! empty( $estimations['pages'][ $key ]['estimation'] ) ? $estimations['pages'][ $key ]['estimation'] : '';
				$content .= '
								<tr class="page page-' . $key . ' treegrid-' . $count . ' treegrid-parent-' . $unit_parent . ' ' . $draft_class . ' ' . $alt . '" data-unitid="'. $unit['unit']->ID . '" data-pagenumber="'. $key . '">
									<td>' . $page_title . '</td>
									<td><input type="checkbox" name="meta_structure_visible_pages[' . $page_key . ']" value="1" ' . $page_view_checked . '/></td>
									<td><input type="checkbox" name="meta_structure_preview_pages[' . $page_key . ']" value="1" ' . $page_preview_checked . '/></td>
									<td>' . $duration . '</td>
								</tr>
				';

				$page_parent = $count;

				$page['modules'] = CoursePress_Helper_Utility::sort_on_object_key( $page['modules'], 'module_order' );

				foreach ( $page['modules'] as $module ) {
					$count += 1;
					$alt = $count % 2 ? 'even' : 'odd';
					$module_title = ! empty( $module->post_title ) ? $module->post_title : __( 'Untitled Module', 'cp' );

					$mod_key = $page_key . '_' . (int) $module->ID;

					$mod_view_checked = isset( $visible_modules[ $mod_key ] ) ? CoursePress_Helper_Utility::checked( $visible_modules[ $mod_key ] ) : '';
					$mod_preview_checked = isset( $preview_modules[ $mod_key ] ) ? CoursePress_Helper_Utility::checked( $preview_modules[ $mod_key ] ) : '';

					// Legacy, use it just to update
					$mod_view_checked = empty( $mod_view_checked ) && isset( $visible_modules[ $module->ID ] ) ? CoursePress_Helper_Utility::checked( $visible_modules[ $module->ID ] ) : $mod_view_checked;
					$mod_preview_checked = empty( $mod_preview_checked ) && isset( $preview_modules[ $module->ID ] ) ? CoursePress_Helper_Utility::checked( $preview_modules[ $module->ID ] ) : $mod_preview_checked;

					$content .= '
								<tr class="module module-' . $module->ID . ' treegrid-' . $count . ' treegrid-parent-' . $page_parent . ' ' . $draft_class . ' ' . $alt . '" data-unitid="'. $unit['unit']->ID . '" data-pagenumber="'. $key . '">
									<td>' . $module_title . '</td>
									<td><input type="checkbox" name="meta_structure_visible_modules[' . $mod_key . ']" value="1" ' . $mod_view_checked . '/></td>
									<td><input type="checkbox" name="meta_structure_preview_modules[' . $mod_key . ']" value="1" ' . $mod_preview_checked . '/></td>
									<td>' . CoursePress_Data_Module::get_time_estimation( $module->ID, '1:00', true ) . '</td>
								</tr>
					';

				}
			}
		}

		$content .= '
							</tbody>
						</table>

					</div>
				</div>
		';

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_2', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 2 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_3() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_3', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 2 ? $setup_class . ' setup_marker' : $setup_class;
		$can_assign_instructor = CoursePress_Data_Capabilities::can_assign_course_instructor( $course_id );

		$content = '
			<div class="step-title step-3">' . esc_html__( 'Step 3 – Instructors and Facilitators', 'cp' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-3">
				<input type="hidden" name="meta_setup_step_3" value="saved" />
			';

		if ( $can_assign_instructor ) {
			$search_nonce = wp_create_nonce( 'coursepress_instructor_search' );

			$content .= '
				<div class="wide">
						<label for="course_name" class="">' .
					esc_html__( 'Course Instructor(s)', 'cp' ) . '
						<p class="description">' . esc_html__( 'Select one or more instructor to facilitate this course', 'cp' ) . '</p>
						</label>
						<select id="instructors" style="width:350px;" name="instructors" data-nonce-search="' . $search_nonce . '" class="medium"></select>
						<input type="button" class="button button-primary instructor-assign" value="' . esc_attr__( 'Assign', 'cp' ) . '" />
				</div>';
		}

		$content .= '<div class="instructors-info medium" id="instructors-info">';
		if ( $can_assign_instructor ) {
			$content .= '<p>' . esc_html__( 'Assigned Instructors:', 'cp' ) . '</p>';
		} else {
			$content .= '<p>' . esc_html__( 'You do not have sufficient permission to add instructor!', 'cp' );
		}

		$args = array(
			'remove_buttons' => true,
			'count' => true,
		);
		$number_of_instructors = CoursePress_Helper_UI::course_instructors_avatars( $course_id, $args );

		if ( 0 >= $number_of_instructors ) {
			if ( $can_assign_instructor ) {
				$content .= '
					<div class="instructor-avatar-holder empty">
						<span class="instructor-name">' . esc_html__( 'Please Assign Instructor', 'cp' ) . '</span>
					</div>
';
				$content .= CoursePress_Helper_UI::course_pendings_instructors_avatars( $course_id );
			}
		} else {
			$content .= CoursePress_Helper_UI::course_instructors_avatars( $course_id, array(), true );
		}

		$content .= '
				</div>';
		// Facilitators
		$can_assign_facilitator = CoursePress_Data_Capabilities::can_assign_facilitator( $course_id );
		$facilitators = CoursePress_Data_Facilitator::get_course_facilitators( $course_id );

		if ( $can_assign_facilitator ) {
			$search_nonce = wp_create_nonce( 'coursepress_search_users' );

			$content .= '
				<div class="wide">
						<label for="course_name" class="">' .
					esc_html__( 'Course Facilitator(s)', 'cp' ) . '
						<p class="description">' . esc_html__( 'Select one or more facilitator to facilitate this course', 'cp' ) . '</p>
						</label>
			<select data-nonce-search="'. $search_nonce . '" name="facilitators" style="width:350px;" id="facilitators" class="user-dropdown medium"></select>
			<input type="button" class="button button-primary facilitator-assign" value="' . esc_attr__( 'Assign', 'cp' ) . '" />
				</div>';
		} else {

			if ( ! empty( $facilitators ) ) {
				$content .= '<div class="wide">
					<label>' . __( 'Course Facilitators', 'cp' ) . '</label>
					</div>
				';
			}
		}

		$content .= '<br><div class="wide facilitator-info medium" id="facilitators-info">';
		$content .= CoursePress_Helper_UI::course_facilitator_avatars( $course_id, array(), true );
		$content .= '</div><br>';

		if ( $can_assign_instructor || $can_assign_facilitator ) {

			$label = '';
			$description = '';
			$placeholder = '';

			if ( $can_assign_instructor && $can_assign_facilitator ) {
				$label = esc_html__( 'Invite New Instructor or Facilitator', 'cp' );
				$description = esc_html__( 'If the instructor or the facilitator can not be found in the list above, you will need to invite them via email.', 'cp' );
				$placeholder = __( 'instructor-or-facilitator@email.com', 'cp' );
			} else if ( $can_assign_instructor ) {
				$label = esc_html__( 'Invite New Instructor', 'cp' );
				$description = esc_html__( 'If the instructor can not be found in the list above, you will need to invite them via email.', 'cp' );
				$placeholder = __( 'facilitator@email.com', 'cp' );
			} else if ( $can_assign_facilitator ) {
				$label = esc_html__( 'Invite New Facilitator', 'cp' );
				$description = esc_html__( 'If the facilitator can not be found in the list above, you will need to invite them via email.', 'cp' );
				$placeholder = __( 'instructor@email.com', 'cp' );
			}

			// Instructor/Facilitator Invite
			$content .= '
					<div class="wide">
						<hr />
						<label>' . $label .'
							<p class="description">' . $description . '</p>
						</label>
						<div class="instructor-invite">';
			if ( $can_assign_instructor && $can_assign_facilitator ) {
				$content .= '<label>'.__( 'Instructor or Facilitator', 'cp' ).'</label>
							<ul>
<li><label><input type="radio" name="invite_instructor_type" value="instructor" checked="checked" /> ' . __( 'Instructor', 'cp' ) . '</label></li>
<li><label><input type="radio" name="invite_instructor_type" value="facilitator" /> ' . __( 'Facilitator', 'cp' ) . '</label></li>
							</ul>';
			} else if ( $can_assign_instructor ) {
				$content .= '<input type="hidden" name="invite_instructor_type="instructor" />';
			} else if ( $can_assign_facilitator ) {
				$content .= '<input type="hidden" name="invite_instructor_type="facilitator" />';
			}
			$content .= '<label for="invite_instructor_first_name">' . esc_html__( 'First Name', 'cp' ) . '</label>
							<input type="text" name="invite_instructor_first_name" placeholder="' . esc_attr__( 'First Name', 'cp' ) . '"/>
							<label for="invite_instructor_last_name">' . esc_html__( 'Last Name', 'cp' ) . '</label>
							<input type="text" name="invite_instructor_last_name" placeholder="' . esc_attr__( 'Last Name', 'cp' ) . '"/>
							<label for="invite_instructor_email">' . esc_html__( 'E-Mail', 'cp' ) . '</label>
							<input type="text" name="invite_instructor_email" placeholder="' . esc_attr( $placeholder ) . '"/>

							<div class="submit-message">
								<input class="button-primary" name="invite_instructor_trigger" id="invite-instructor-trigger" type="button" value="' . esc_attr__( 'Send Invite', 'cp' ) . '">
							</div>
						</div>
					</div>
					';
		}

		/**
		 * add javascript templates
		 */
		$content .= CoursePress_Template_Course::javascript_templates();

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_3', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 3 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_4() {
				$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_4', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 3 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-4">' . esc_html__( 'Step 4 – Course Dates', 'cp' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-4">
				<input type="hidden" name="meta_setup_step_4" value="saved" />
			';

		$open_ended_checked = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'course_open_ended', true ) );
		$open_ended_course = ! empty( $open_ended_checked );
		$content .= '
				<div class="wide course-dates">
					<label>' .
					esc_html__( 'Course Availability', 'cp' ) . '
					</label>
					<p class="description">' . esc_html__( 'These are the dates that the course will be available to students', 'cp' ) . '</p>
					<label class="checkbox medium">
						<input type="checkbox" name="meta_course_open_ended" ' . $open_ended_checked . ' />
						<span>' . esc_html__( 'This course has no end date', 'cp' ) . '</span>
					</label>
					<div class="date-range">
						<div class="start-date">
							<label for="meta_course_start_date" class="start-date-label required">' . esc_html__( 'Start Date', 'cp' ) . '</label>

							<div class="date">
								<input type="text" class="dateinput timeinput" name="meta_course_start_date" value="' . CoursePress_Data_Course::get_setting( $course_id, 'course_start_date', date( 'Y-m-d' ) ) . '"/><i class="calendar"></i>
							</div>
						</div>
						<div class="end-date ' . ( $open_ended_course ? 'disabled' : '' ) . '">
							<label for="meta_course_end_date" class="end-date-label required">' . esc_html__( 'End Date', 'cp' ) . '</label>
							<div class="date">
								<input type="text" class="dateinput" name="meta_course_end_date" value="' . CoursePress_Data_Course::get_setting( $course_id, 'course_end_date', '' ) . '" ' . ( $open_ended_course ? 'disabled="disabled"' : '' ) . ' />
							</div>
						</div>
					</div>
				</div>';

		$open_ended_checked = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'enrollment_open_ended', true ) );
		$open_ended = ! empty( $open_ended_checked );
		$content .= '
				<div class="wide enrollment-dates">
					<label>' .
					esc_html__( 'Course Enrollment Dates', 'cp' ) . '
					</label>
					<p class="description">' . esc_html__( 'These are the dates that students will be able to enroll in a course.', 'cp' ) . '</p>
					<label class="checkbox medium">
						<input type="checkbox" name="meta_enrollment_open_ended" ' . $open_ended_checked . ' />
						<span>' . esc_html__( 'Students can enroll at any time', 'cp' ) . '</span>
					</label>
					<div class="date-range enrollment">
						<div class="start-date ' . ( $open_ended ? 'disabled' : '' ) . '">
							<label for="meta_enrollment_start_date" class="start-date-label required">' . esc_html__( 'Start Date', 'cp' ) . '</label>

							<div class="date">
								<input type="text" class="dateinput" name="meta_enrollment_start_date" value="' . CoursePress_Data_Course::get_setting( $course_id, 'enrollment_start_date', '' ) . '"/><i class="calendar"></i>
							</div>
						</div>
						<div class="end-date ' . ( $open_ended ? 'disabled' : '' ) . '">
							<label for="meta_enrollment_end_date" class="end-date-label required">' . esc_html__( 'End Date', 'cp' ) . '</label>
							<div class="date">
								<input type="text" class="dateinput" name="meta_enrollment_end_date" value="' . CoursePress_Data_Course::get_setting( $course_id, 'enrollment_end_date', '' ) . '" ' . ( $open_ended ? 'disabled="disabled"' : '' ) . ' />
							</div>
						</div>
					</div>
				</div>';

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_4', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 4 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_5() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_5', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 4 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-5">' . esc_html__( 'Step 5 – Classes, Discussion & Workbook', 'cp' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-5">
				<input type="hidden" name="meta_setup_step_5" value="saved" />
			';

		$limit_checked = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'class_limited', false ) );
		$limited = ! empty( $limit_checked );
		$content .= '
				<div class="wide class-size">
					<label>' .
					esc_html__( 'Class Size', 'cp' ) . '
					</label>
					<p class="description">' . esc_html__( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size(s).', 'cp' ) . '</p>
					<label class="narrow col">
						<input type="checkbox" name="meta_class_limited" ' . $limit_checked . ' />
						<span>' . esc_html__( 'Limit class size', 'cp' ) . '</span>
					</label>

					<label class="num-students narrow col ' . ( $limited ? '' : 'disabled' ) . '">
						' . esc_html__( 'Number of students', 'cp' ) . '
						<input type="text" class="spinners" name="meta_class_size" value="' . CoursePress_Data_Course::get_setting( $course_id, 'class_size', '' ) . '" ' . ( $limited ? '' : 'disabled="disabled"' ) . '/>
					</label>
				</div>';

		$content .= '
				<div class="wide">
					<label>' .
					esc_html__( 'Course Discussion', 'cp' ) . '
					</label>
					<p class="description">' . esc_html__( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', 'cp' ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_allow_discussion" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'allow_discussion', false ) ) . ' />
						<span>' . esc_html__( 'Allow course discussion', 'cp' ) . '</span>
					</label>
				</div>';

		$content .= '
				<div class="wide">
					<label>' .
					esc_html__( 'Student Workbook', 'cp' ) . '
					</label>
					<p class="description">' . esc_html__( 'If checked, students can see their progress and grades.', 'cp' ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_allow_workbook" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'allow_workbook', false ) ) . ' />
						<span>' . esc_html__( 'Show student workbook', 'cp' ) . '</span>
					</label>
				</div>';

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_5', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 5 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_6() {
				$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		// Payment can be disabled using the COURSEPRESS_DISABLE_PAYMENT constant or hooking the filter
		$disable_payment = defined( 'COURSEPRESS_DISABLE_PAYMENT' ) && true == COURSEPRESS_DISABLE_PAYMENT;
		$disable_payment = apply_filters( 'coursepress_disable_course_payments', $disable_payment, $course_id );

		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_6', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 5 ? $setup_class . ' setup_marker' : $setup_class;

		$payment_tagline = ! $disable_payment ? __( ' & Course Cost', 'cp' ) : '';

		$content = '
			<div class="step-title step-6">' . esc_html( sprintf( __( 'Step 6 – Enrollment%s', 'cp' ), $payment_tagline ) ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-6">
				<!-- depending on gateway setup, this could be save-attention -->
				<input type="hidden" name="meta_setup_step_6" value="saved" />
			';

		// Enrollment Options
		$enrollment_types = CoursePress_Data_Course::get_enrollment_types_array( $course_id );

		$content .= '<div class="wide">';
		$content .= sprintf( '<label>%s</label>', esc_html__( 'Enrollment Restrictions', 'cp' ) );

		$content .= '<p class="description">' . esc_html__( 'Select the limitations on accessing and enrolling in this course.', 'cp' ) . '</p>';
		/**
		 * select
		 */
		$enrollment_type_default = CoursePress_Data_Course::get_enrollment_type_default( $course_id );
		$selected = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_type', $enrollment_type_default );
		$content .= CoursePress_Helper_UI::select( 'meta_enrollment_type', $enrollment_types, $selected, 'chosen-select medium' );
		$content .= '</div>';

		$class = 'prerequisite' === $selected ? '' : 'hidden';
		$content .= '
				<div class="wide enrollment-type-options prerequisite ' . $class . '">';

		$class_extra = is_rtl() ? 'chosen-rtl' : '';
		$content .= '
					<label>' .
					esc_html__( 'Prerequisite Courses', 'cp' ) .
					'</label>
					<p class="description">' . esc_html__( 'Select the courses a student needs to complete before enrolling in this course', 'cp' ) . '</p>
					<select name="meta_enrollment_prerequisite" class="medium chosen-select chosen-select-course ' . $class_extra . '" multiple="true" data-placeholder=" ">
			';

		$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );

		$saved_settings = CoursePress_Data_Course::get_setting( $course_id, 'enrollment_prerequisite', array() );
		if ( ! is_array( $saved_settings ) ) {
			$saved_settings = array( $saved_settings );
		}

		foreach ( $courses as $course ) {
			$post_id = $course->ID;
			if ( $post_id !== $course_id ) {
				$selected_item = in_array( $post_id, $saved_settings ) ? 'selected="selected"' : '';
				$content .= '<option value="' . $post_id . '" ' . $selected_item . '>' . $course->post_title . '</option>';
			}
		}

		$content .= '
					</select>
				</div>
			';

		$class = 'passcode' === $selected ? '' : 'hidden';
		$content .= '
				<div class="wide enrollment-type-options passcode ' . $class . '">';

		$content .= '
				<label>' .
					esc_html__( 'Course Passcode', 'cp' ) .
					'</label>
				<p class="description">' . esc_html__( 'Enter the passcode required to access this course', 'cp' ) . '</p>
				<input type="text" name="meta_enrollment_passcode" value="' . CoursePress_Data_Course::get_setting( $course_id, 'enrollment_passcode', '' ) . '" />
			';

		$content .= '
				</div>
			';

		$paid_checked = CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'payment_paid_course', false ) );
		$is_paid = ! empty( $paid_checked );

		if ( ! $disable_payment ) {
			$content .= '
				<hr class="separator" />
				<div class="wide">
					<label>' .
						esc_html__( 'Course Payment', 'cp' ) . '
					</label>
					<p class="description">' . esc_html__( 'Payment options for your course. Additional plugins are required and settings vary depending on the plugin.', 'cp' ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_payment_paid_course" ' . $paid_checked . ' />
						<span>' . esc_html__( 'This is a paid course', 'cp' ) . '</span>
					</label>
				</div>';
		}

		/**
		 * Hook this filter to add payment plugin support
		 */
		$payment_supported = CoursePress_Helper_Utility::is_payment_supported();

		if ( ! $payment_supported && ! $disable_payment ) {

			if ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins ' ) ) {
				$install_message = sprintf( __( '<p>To start selling your course, please install and activate MarketPress here:</p>
								<a href="%s">Activate MarketPress</a>', 'cp' ), esc_url_raw( admin_url( 'admin.php?page=coursepress_settings&tab=extensions' ) ) );
			} else {
				$install_message = __( '<p>Please contact your administrator to enable MarketPress for your site.</p>', 'cp' );
			}

			if ( CP_IS_PREMIUM ) {
				$version_message = __( '<p>The full version of MarketPress has been bundled with CoursePress Pro.</p>', 'cp' );
			} else {
				$version_message = __( '<p>You can use the free or premium version of MarketPress to sell your courses.</p>', 'cp' );
			}

			$class = $is_paid ? '' : 'hidden';

			/**
			 * Hook this filter to get rid of the payment message
			 */
			$payment_message = apply_filters( 'coursepress_course_payment_message', sprintf( __( '
				<div class="payment-message %s">
					<h3>Sell your courses online with MarketPress.</h3>
					%s
					%s
					<p>Other supported plugins: WooCommerce</p>
				</div>
			', 'cp' ), $class, $version_message, $install_message ), $course_id );

			// It's already been filtered, but because we're dealing with HTML, lets be sure
			$content .= CoursePress_Helper_Utility::filter_content( $payment_message );

		}

		if ( $payment_supported ) {

			$class = $is_paid ? '' : 'hidden';
			$content .= '<div class="is_paid_toggle ' . $class . '">';
			/**
			 * Add additional fields if 'This is a paid course' is selected.
			 *
			 * Field names must begin with meta_ to allow it to be automatically added to the course settings
			 *
			 * * This is the ideal filter to use for integrating payment plugins
			 */
			$content .= apply_filters( 'coursepress_course_setup_step_6_paid', '', $course_id );

			$content .= '</div>';
		}

		/**
		 * Add additional fields.
		 *
		 * Field names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_6', '', $course_id );

		// Buttons
		$content .= self::get_buttons( $course_id, 6 );

		// End
		$content .= '
			</div>
		';

		echo $content;
	}

	public static function step_7() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_7', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 7 ? $setup_class . ' setup_marker' : $setup_class;

		/**
		 * Pre-Completion Page
		 */
		$pre_completion_title = CoursePress_Data_Course::get_setting( $course_id, 'pre_completion_title', __( 'Almost there!', 'cp' ) );
		$pre_completion_content = sprintf( '<h3>%s</h3>', __( 'You have completed the course!', 'cp' ) );
		$pre_completion_content .= sprintf( '<p>%s</p>', __( 'Your submitted business plan will be reviewed, and you\'ll hear back from me on whether you pass or fail.', 'cp' ) );
		$pre_completion_content = CoursePress_Data_Course::get_setting( $course_id, 'pre_completion_content', $pre_completion_content );
		$pre_completion_content = htmlspecialchars_decode( $pre_completion_content );

		/**
		 * Course Completion Page
		 */
		$completion_title = CoursePress_Data_Course::get_setting( $course_id, 'course_completion_title', __( 'Congratulations, You Passed!', 'cp' ) );
		$completion_content = sprintf( '<p>%s</p>', __( 'Woohoo! You\'ve passed COURSE_NAME!', 'cp' ) );
		$completion_content = CoursePress_Data_Course::get_setting( $course_id, 'course_completion_content', $completion_content );
		$completion_content = htmlspecialchars_decode( $completion_content );

		$content = '<div class="step-title step-7">'
			. esc_html( 'Step 7 - Course Completion', 'cp' )
			. '<div class="status '. $setup_class . '"></div>'
			. '</div>';

		$content .= '<div class="step-content step-7">
			<input type="hidden" name="meta_setup_step_7" value="saved" />';

		// Course completion
		$minimum_grade = CoursePress_Data_Course::get_setting( $course_id, 'minimum_grade_required', 100 );

		$content .= '<div class="wide minimum-grade">';
		$content .= sprintf( '<label class="required" for="meta_minimum_grade_required">%s</label> ', __( 'Minimum Grade Required', 'cp' ) );
		$content .= sprintf( '<input type="number" id="meta_minimum_grade_required" name="meta_minimum_grade_required" value="%d" min="0" max="100" class="text-small" />', esc_attr__( $minimum_grade ) );
		$content .= sprintf(
			'<p class="description">%s</p>',
			__( 'The minimum grade required to marked course completion and send course certficates.', 'cp' )
		);
		$content .= '</div>';

		$tokens = array(
			'COURSE_NAME',
			'COURSE_SUB_TITLE',
			'COURSE_OVERVIEW',
			'COURSE_UNIT_LIST',
			'DOWNLOAD_CERTIFICATE_LINK',
			'DOWNLOAD_CERTIFICATE_BUTTON',
			'STUDENT_WORKBOOK',
		);
		$token_info = '<p class="description" style="margin-bottom: -25px;">'. __( sprintf( 'Use these tokens to display actual course details: %s', implode( ', ', $tokens ) ), 'cp' ) . '</p>';

		// Pre-completion page
		$content .= '<div class="wide page-pre-completion">'
			. '<label>' . __( 'Pre-Completion Page', 'cp' ) . '</label>'
			. '<p class="description">' . __( 'Use the fields below to show custom pre-completion page after the student completed the course but require final assessment from instructors.', 'cp' ) . '</p>'
			. '<label for="meta_pre_completion_title" class="required">' . __( 'Page Title', 'cp' ) . '</label>'
			. '<input type="text" class="wide" name="meta_pre_completion_title" value="'. esc_attr( $pre_completion_title ) . '" />'
			. '<label for="meta_pre_completion_content" class="required">' . __( 'Page Content', 'cp' ) . '</label>'
			. $token_info
		;
		$content .= self::get_wp_editor( 'pre-completion-content', 'meta_pre_completion_content', $pre_completion_content );
		$content .= '</div>';

		$content .= '<div class="wide page-completion">'
			. '<label>' . __( 'Course Completion Page', 'cp' ) . '</label>'
			. '<p class="description">' . __( 'Use the fields below to show a custom page after successfull course completion.', 'cp' ) . '</p>'
			. '<label for="meta_course_completion_title" class="required">' . __( 'Page Title', 'cp' ) . '</label>'
			. '<input type="text" class="widefat" name="meta_course_completion_title" value="'. esc_attr( $completion_title ) . '" />'
		;

		$content .= '<label for="meta_course_completion_content" class="required">' . __( 'Page Content', 'cp' ) . '</label>' . $token_info;
		$content .= self::get_wp_editor( 'course-completion-editor-content', 'meta_course_completion_content', $completion_content );
		$content .= '</div>';

		// Fail info
		$failed_title = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_title', __( 'Sorry, you did not pass this course!', 'cp' ) );
		$failed_content = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_content', '' );
//=======
//		$failed_content = __( 'I\'m sorry to say you didn\'t pass JavaScript for COURSE_NAME. Better luck next time!', 'cp' );
//		$failed_content = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_content', $failed_content );
//>>>>>>> coursepress/2.0-dev
		$failed_content = htmlspecialchars_decode( $failed_content );

		$content .= '<div class="wide page-failed">
			<label>' . __( 'Failed Page', 'cp' ) . '</label>
			<p class="description">'. __( 'Use the fields below to display failure page when an student completed a course but fail to reach the minimum required grade.', 'cp' ) . '</p>
			<label for="meta_course_failed_title" class="required">'. __( 'Page Title', 'cp' ) . '</label>
			<input type="text" class="widefat" name="meta_course_failed_title" value="'. esc_attr__( $failed_title ) . '" />
			<label for="meta_course_field_content" class="required">'. __( 'Page Content', 'cp' ) . '</label>'
			. $token_info;
		$content .= self::get_wp_editor( 'course-failed-content', 'meta_course_failed_content', $failed_content );
		$content .= '</div>';

		// Basic certificate
		$fields = apply_filters( 'coursepress_basic_certificate_vars',
			array(
				'FIRST_NAME' => '',
				'LAST_NAME' => '',
				'COURSE_NAME' => '',
				'COMPLETION_DATE' => '',
				'CERTIFICATE_NUMBER' => '',
				'UNIT_LIST' => '',
				),
			null
		);
		$field_keys = array_keys( $fields );
		$default_layout = CoursePress_View_Admin_Setting_BasicCertificate::default_certificate_content();
		$certficate_content = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate_layout', $default_layout );
		$certficate_content = htmlspecialchars_decode( $certficate_content );
		$certificate_link = add_query_arg(
			array(
				'nonce' => wp_create_nonce( 'cp_certificate_preview' ),
				'course_id' => $course_id,
			)
		);
		$test_mail_link = add_query_arg(
			array(
				'nonce' => wp_create_nonce( 'cp_certificate_mail' ),
				'course_id' => $course_id,
			)
		);

		$value = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate' );
		$class = cp_is_true( $value )? '':'hidden';

		$content .= '<div class="wide course-certificate">';
		$content .= sprintf( '<br /><h3>%s</h3>', esc_html__( 'Course Certificate', 'cp' ) );
		$content .= sprintf(
			'<a href="%s" target="_blank" class="button button-default btn-cert %s" style="float:right;margin-top:-35px;">%s</a>',
			esc_url( $certificate_link ),
			esc_attr( $class ),
			esc_html__( 'Preview', 'cp' )
		);
		$content .= '<label>';
		$content .= '<input type="checkbox" name="meta_basic_certificate" value="1" '. checked( 1, $value, false ) . ' /> '. __( 'Override course certificate.', 'cp' )

			. '</label>'
			. '<p class="description">' . __( 'Use this field to override general course certificate setting.', 'cp' ) . '</p>';
		$content .= sprintf( '<div class="options %s">', cp_is_true( $value )? '':'hidden' );
		$content .= '<label for="meta_basic_certificate_layout">' . __( 'Certificate Content', 'cp' ) . '</label>'
			. '<p class="description" style="float:left;">' . __( 'Useful tokens: ', 'cp' ) . implode( ', ', $field_keys ) . '</p>'
		;
		$content .= self::get_wp_editor( 'basic-certificate-layout', 'meta_basic_certificate', $certficate_content );
		$content .= '<table class="wide"><tr><td style="width:20%;">'
			. '<label>' . __( 'Background Image', 'cp' ) . '</label>'
			. '</td><td>';
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_certificate_background',
			'meta_certificate_background',
			array(
				'placeholder' => __( 'Choose background image', 'cp' ),
				'type' => 'image',
				'value' => CoursePress_Data_Course::get_setting( $course_id, 'certificate_background', '' ),
			)
		);
		$content .= '</td></tr>';
		$cert_padding = CoursePress_Data_Course::get_setting( $course_id, 'cert_padding', array() );
		$padding_top = CoursePress_Helper_Utility::get_array_val( $cert_padding, 'top', '' );
		$padding_bottom = CoursePress_Helper_Utility::get_array_val( $cert_padding, 'bottom', '' );
		$padding_left = CoursePress_Helper_Utility::get_array_val( $cert_padding, 'left', '' );
		$padding_right = CoursePress_Helper_Utility::get_array_val( $cert_padding, 'right', '' );
		$content .= '<tr><td><label>' . __( 'Content Padding', 'cp' ) . '</label></td><td>';
		$content .= __( 'Top', 'cp' ) . ': <input type="text" size="10" name="meta_cert_padding[top]" value="'. esc_attr( $padding_top ) . '" />';
		$content .= __( 'Bottom', 'cp' ) . ': <input type="text" size="10" name="meta_cert_padding[bottom]" value="'. esc_attr( $padding_bottom ) .'" />';
		$content .= __( 'Left', 'cp' ) . ': <input type="text" size="10" name="meta_cert_padding[left]" value="'. esc_attr( $padding_left ) . '" />';
		$content .= __( 'Right', 'cp' ) . ': <input type="text" size="10" name="meta_cert_padding[right]" value="'. esc_attr( $padding_right ) . '" />';
		$content .= '</td></tr>';
		$content .= '<tr><td><label>' . __( 'Page Orientation', 'cp' ) . '</label></td><td>';
		$content .= '<label style="float:left;margin-right:25px;"><input type="radio" name="meta_page_orientation" value="L" '. checked( 'L', CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', 'L' ), false ) .' /> ' . __( 'Landscape', 'cp' ) . '</label>';
		$content .= '<label style="float:left;"><input type="radio" name="meta_page_orientation" value="P" '. checked( 'P', CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', '' ), false ) .'/>' . __( 'Portrait', 'cp' ) . '</label>';
		$content .= '</td></tr>';
		$content .= '</table></div>';
		$content .= '</div>';

		// Buttons
		$content .= self::get_buttons( $course_id, 7, array( 'next' => false ) );
		$content .= '</div>';

		echo $content;
	}

	public static function other_steps( $course ) {
		/**
		 * Hook to course edit
		 *
		 * @since 2.0
		 *
		 * @param (object) $course			WP_Post Object.
		 **/
		do_action( 'coursepress_course_edit_steps', $course );
	}

	public static function certificate_preview() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'cp_certificate_preview' ) ) {
			$course_id = (int) $_REQUEST['course_id'];

			if ( $course_id > 0 ) {
				$background = CoursePress_Data_Course::get_setting( $course_id, 'certificate_background', '' );
				$paddings = CoursePress_Data_Course::get_setting( $course_id, 'cert_padding', array() );
				$orientation = CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', 'L' );
				$html = CoursePress_Data_Course::get_setting( $course_id, 'basic_certificate_layout' );
				$html = apply_filters( 'coursepress_basic_certificate_html', $html, $course_id, get_current_user_id() );

				$filename = 'cert-preview-' . $course_id . '.pdf';
				$styles = array();

				foreach ( $paddings as $padding => $value ) {
					$value = empty( $value ) ? 0 : $value;
					$styles[] = "padding-{$padding}: {$value};";
				}

				$styles = '.basic_certificate {' . implode( ' ', $styles ) . ' }';

				$userdata = get_userdata( get_current_user_id() );
				$course = get_post( $course_id );
				$date_format = apply_filters( 'coursepress_basic_certificate_date_format', get_option( 'date_format' ) );
				$vars = array(
					'FIRST_NAME' => $userdata->first_name,
					'LAST_NAME' => $userdata->last_name,
					'COURSE_NAME' => $course->post_title,
					'COMPLETION_DATE' => date_i18n( $date_format, CoursePress_Data_Course::time_now() ),
					'CERTIFICATE_NUMBER' => uniqid( rand(), true ),
				);
				$html = CoursePress_Helper_Utility::replace_vars( $html, $vars );
				$html = sprintf( '<div class="basic_certificate">%s</div>', $html );

				// Set PDF args
				$args = array(
					'title' => __( 'Course Completion Certificate', 'cp' ),
					'orientation' => $orientation,
					'image' => $background,
					'filename' => $filename,
					'format' => 'F',
					'uid' => '12345',
					'style' => '<style>' . $styles . '</style>',
				);
				CoursePress_Helper_PDF::make_pdf( $html, $args );
				// Print preview
				$args['format'] = 'I';
				CoursePress_Helper_PDF::make_pdf( $html, $args );

			}

			exit;
		}
	}
}
