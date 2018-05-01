<?php

class CoursePress_View_Admin_Course_Edit {

	public static $slug = 'coursepress_course';
	private static $title = '';
	private static $menu_title = '';
	private static $action = 'new';
	private static $allowed_actions = array(
		'new',
		'edit',
	);
	private static $tabs = array();
	public static $current_course = false;
	private static $capability = 'manage_options';

	public static function init() {

		self::$action = isset( $_GET['action'] ) && in_array( $_GET['action'], self::$allowed_actions ) ? sanitize_text_field( $_GET['action'] ) : 'new';

		self::$title = __( 'Edit Course/CoursePress', 'coursepress' );

		switch ( self::$action ) {
			case 'new':
				self::$menu_title = __( 'New Course', 'coursepress' );
				self::$capability = 'coursepress_create_course_cap';
			break;
			case 'edit':
				if ( isset( $_GET['id'] ) && 0 !== (int) $_GET['id'] ) {
					self::$current_course = get_post( (int) $_GET['id'] );
				}
				self::$menu_title = __( 'Edit Course', 'coursepress' );
				/**
				 * set cap
				 */
				if ( is_object( self::$current_course )
					&& CoursePress_Data_Capabilities::can_update_course( self::$current_course->ID ) ) {
					self::$capability = 'coursepress_create_course_cap';
				}
			break;
		}

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'process_form' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );

		// Update Course
		add_action( 'wp_ajax_update_course', array( __CLASS__, 'update_course' ) );

		// Update UnitBuilder
		add_action( 'wp_ajax_unit_builder',
			array( 'CoursePress_View_Admin_Course_UnitBuilder', 'unit_builder_ajax' )
		);

		// Certificate preview
		//self::certificate_preview();
		add_action( 'init', array( __CLASS__, 'certificate_preview' ) );
		// Test certificate mail
		add_action( 'init', array( __CLASS__, 'test_mail_certificate' ) );
		//self::test_mail_certificate();
	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title' => self::$title,
			'menu_title' => self::$menu_title,
			/** This filter is documented in include/coursepress/helper/class-setting.php */
			'cap' => apply_filters( 'coursepress_capabilities', self::$capability ),
			'order' => 10,
		);

		if ( 'new' == self::$action ) {
			$pages[ self::$slug ]['cap'] = 'coursepress_create_course_cap';
		}

		return $pages;
	}

	private static function _current_action() {
		return self::$action;
	}

	public static function process_form() {
	}

	public static function render_page() {

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
		$course_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$status = get_post_status( $course_id );
		$user_id = get_current_user_id();
		$publish_toggle = '';

		$ui = array(
			'label' => __( 'Publish Course', 'coursepress' ),
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

		$content = '<div class="coursepress_settings_wrapper wrap">' .
			CoursePress_Helper_UI::get_admin_page_title( self::$menu_title ).
			CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $hidden_args, self::$slug, $tab, false, 'horizontal', $publish_toggle ) .
			'</div>';

		echo $content;
	}

	private static function render_tab_setup() {

		// Setup Nonce
		$setup_nonce = wp_create_nonce( 'setup-course' );

		$course_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$course = false;
		if ( ! empty( $course_id ) ) {
			$course = get_post( $course_id );
		}

		ob_start();
		do_meta_boxes( self::$slug, 'side', $course );
		$metabox_side = ob_get_clean();

		global $wp_meta_boxes;
		$has_metaboxes = ! empty( $wp_meta_boxes ) && array_key_exists( self::$slug, $wp_meta_boxes );

		$metabox_class = $has_metaboxes ? 'metaboxes' : '';
		$preview_button = '';

		if ( $course_id > 0 ) {
			$preview_button = sprintf(
				'<div><a href="%s" target="_blank" class="button button-preview">%s</a></div>',
				CoursePress_Data_Course::get_course_url( $course_id ),
				__( 'Preview', 'coursepress' )
			);
		}

		$content = '
		<div class="coursepress-course-step-container ' . $metabox_class . '">
			'. $preview_button . '
			<div id="course-setup-steps" data-nonce="' . $setup_nonce . '">
				' . self::render_setup_step_1() . '
				' . self::render_setup_step_2() . '
				' . self::render_setup_step_3() . '
				' . self::render_setup_step_4() . '
				' . self::render_setup_step_5() . '
				' . self::render_setup_step_6() . '
				' . self::render_setup_step_7() . '
			</div>
		</div>
		';

		if ( $has_metaboxes ) {
			$content .= '<div class="course-edit-metaboxes">' . $metabox_side . '</div>';
		}

		return $content;
	}

	public static function render_setup_step_1() {

		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_1', '' );
		$setup_class = ( (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 6 ) || ( (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 0 ) ? $setup_class . ' setup_marker' : $setup_class;

		$content = '
			<div class="step-title step-1">' . esc_html__( 'Step 1 – Course Overview', 'coursepress' ) . '
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
					esc_html__( 'Course Name', 'coursepress' ) . '
						</label>
						<input class="wide" type="text" name="course_name" id="course_name" value="' . $course_name . '"/>
				</div>';

		$content .= apply_filters( 'coursepress_course_setup_step_1_after_title', '', $course_id );

		// Course Excerpt / Short Overview
		$editor_content = ! empty( self::$current_course ) ? htmlspecialchars_decode( self::$current_course->post_excerpt ) : '';
		$editor_html = self::get_wp_editor( 'courseExcerpt', 'course_excerpt', $editor_content );

		$content .= '
				<div class="wide">
						<label for="courseExcerpt" class="required drop-line">' .
					esc_html__( 'Course Excerpt / Short Overview', 'coursepress' ) . '
						</label>
						' . $editor_html . '
				</div>';

		$content .= apply_filters( 'coursepress_course_setup_step_1_after_excerpt', '', $course_id );

		// Listing Image
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_listing_image',
			'meta_listing_image',
			array(
				'placeholder' => __( 'Add Image URL or Browse for Image', 'coursepress' ),
				'title' => __( 'Listing Image', 'coursepress' ),
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
		$manage_category_link = '';
		$can_manage_categories = CoursePress_Data_Capabilities::can_manage_categories();

		if ( $can_manage_categories ) {
			$manage_category_link = sprintf( '<a href="%s" class="context-link">%s</a>', esc_url_raw( $url ), esc_html__( 'Manage Categories', 'coursepress' ) );
		}

		$content .= sprintf( '<div class="wide %s">', $can_manage_categories ? '' : 'hidden' );
		$content .= '
					<label for="meta_course_category" class="medium">' .
					esc_html__( 'Course Category', 'coursepress' ) . $manage_category_link . '
					</label>
					<select name="meta_course_category" class="medium chosen-select chosen-select-course ' . $class_extra . '" multiple="true">';

		foreach ( $terms as $terms ) {
			$selected = in_array( $terms->term_id, $course_terms_array ) ? 'selected="selected"' : '';
			$content .= '<option value="' . $terms->term_id . '" ' . $selected . '>' . $terms->name . '</option>';
		}

		$content .= '
					</select>
				</div>';

		// Course Language
		$language = CoursePress_Data_Course::get_setting( $course_id, 'course_language' );
		if ( empty( $language ) ) {
			$language = __( 'English', 'coursepress' );
		}
		$content .= '
				<div class="wide">
						<label for="meta_course_language">' .
					esc_html__( 'Course Language', 'coursepress' ) . '
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

		return $content;

	}

	private static function render_setup_step_2() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_2', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 1 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-2">' . esc_html__( 'Step 2 – Course Details', 'coursepress' ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-2">
				<input type="hidden" name="meta_setup_step_2" value="saved" />
			';

		// Featured Video
		$supported_ext = implode( ', ', wp_get_video_extensions() );
		$placeholder = sprintf( __( 'Add URL or Browse ( %s )', 'coursepress' ), $supported_ext );
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_featured_video',
			'meta_featured_video',
			array(
				'placeholder' => $placeholder,
				'title' => __( 'Featured Video', 'coursepress' ),
				'value' => CoursePress_Data_Course::get_setting( $course_id, 'featured_video' ),
				'type' => 'video',
				'description' => __( 'This is used on the Course Overview page and will be displayed with the course description.', 'coursepress' ),
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
					esc_html__( 'Course Description', 'coursepress' ) . '
						</label><br />
						' . $editor_html . '
				</div>';

		$content .= '
				<div class="wide">
						<label>' .
						esc_html__( 'Course View Mode', 'coursepress' ) . '
						</label>
						<label class="checkbox">
							<input type="radio" name="meta_course_view" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'normal' ), 'normal' ) . ' value="normal">' . esc_html__( 'Normal: Show full unit pages', 'coursepress' ) . '<br />
							<input type="radio" name="meta_course_view" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'course_view', 'focus' ), 'focus' ) . ' value="focus">' . esc_html__( 'Focus: Focus on one item at a time', 'coursepress' ) . '<br />
							<input type="checkbox" name="meta_focus_hide_section" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'focus_hide_section', true ) ) . ' value="unit">' . esc_html__( 'Don\'t render section titles in focus mode.', 'coursepress' ) . '<br />
							<p class="description">' . esc_html__( 'Choose if your course will show in "normal" mode or step by step "focus" mode.', 'coursepress' ) . '</p>
						</label>
						<label class="checkbox">
							<input type="radio" name="meta_structure_level" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_level', 'unit' ), 'unit' ) . ' value="unit">' . esc_html__( 'Unit list only', 'coursepress' ) . '<br />
							<input type="radio" name="meta_structure_level" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_level', 'section' ), 'section' ) . ' value="section">' . esc_html__( 'Expanded unit list', 'coursepress' ) . '<br />
							<p class="description">' . esc_html__( 'Choose if course Unit page shows units only or in expanded view.', 'coursepress' ) . '</p>
						</label>
				</div>';

		// Course Structure
		$content .= '
				<div class="wide">
					<label>' . esc_html__( 'Course Structure', 'coursepress' ) . '</label>
					<p>' . esc_html__( 'This gives you the option to show/hide Course Units, Lessons, Estimated Time and Free Preview options on the Course Overview page', 'coursepress' ) . '</p>

					<div class="course-structure">

						<label class="checkbox">
							<input type="checkbox" name="meta_structure_visible" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_visible', true ) ) . ' />
							<span>' . esc_html__( 'Show the Course Overview structure and Preview Options', 'coursepress' ) . '</span>
						</label>
						<label class="checkbox">
							<input type="checkbox" name="meta_structure_show_duration" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'structure_show_duration', true ) ) . ' />
							<span>' . esc_html__( 'Display Time Estimates for Units and Lessons', 'coursepress' ) . '</span>
						</label>


						<table class="course-structure-tree">
							<thead>
								<tr>
									<th class="column-course-structure">' . esc_html__( 'Course Structure', 'coursepress' ) . ' <small>' . esc_html__( 'Units and Pages with Modules selected will automatically be visible (only the selected Modules will be accessible).', 'coursepress' ) . '</small></th>
									<th class="column-show">' . esc_html__( 'Show', 'coursepress' ) . '</th>
									<th class="column-free-preview">' . esc_html__( 'Free Preview', 'coursepress' ) . '</th>
									<th class="column-time">' . esc_html__( 'Time', 'coursepress' ) . '</th>
								</tr>
								<tr class="break"><th colspan="4"></th></tr>
							</thead>
							<tfoot>
								<tr class="break"><th colspan="4"></th></tr>
								<tr>
									<th class="column-course-structure">' . esc_html__( 'Course Structure', 'coursepress' ) . '</th>
									<th class="column-show">' . esc_html__( 'Show', 'coursepress' ) . '</th>
									<th class="column-free-preview">' . esc_html__( 'Free Preview', 'coursepress' ) . '</th>
									<th class="column-time">' . esc_html__( 'Time', 'coursepress' ) . '</th>
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
			$status = 'publish' === $unit['unit']->post_status ? '' : __( '[DRAFT] ', 'coursepress' );
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
				$page_title = ! empty( $page['title'] ) ? $page['title'] : sprintf( __( 'Page %s', 'coursepress' ), $key );

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
					$module_title = ! empty( $module->post_title ) ? $module->post_title : __( 'Untitled Module', 'coursepress' );

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

		return $content;
	}

	private static function render_setup_step_3() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_3', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 2 ? $setup_class . ' setup_marker' : $setup_class;
		$can_assign_instructor = CoursePress_Data_Capabilities::can_assign_course_instructor( $course_id );

		$content = '
			<div class="step-title step-3">' . esc_html__( 'Step 3 – Instructors and Facilitators', 'coursepress' ) . '
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
					esc_html__( 'Course Instructor(s)', 'coursepress' ) . '
						<p class="description">' . esc_html__( 'Select one or more instructor to facilitate this course', 'coursepress' ) . '</p>
						</label>
						<select id="instructors" style="width:350px;" name="instructors" data-nonce-search="' . $search_nonce . '" class="medium"></select>
						<input type="button" class="button button-primary instructor-assign" value="' . esc_attr__( 'Assign', 'coursepress' ) . '" />
				</div>';
		}

		$content .= '<div class="instructors-info medium" id="instructors-info">';
		if ( $can_assign_instructor ) {
			$content .= '<p>' . esc_html__( 'Assigned Instructors:', 'coursepress' ) . '</p>';
		} else {
			$content .= '<p>' . esc_html__( 'You do not have sufficient permission to add instructor!', 'coursepress' );
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
						<span class="instructor-name">' . esc_html__( 'Please Assign Instructor', 'coursepress' ) . '</span>
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
					esc_html__( 'Course Facilitator(s)', 'coursepress' ) . '
						<p class="description">' . esc_html__( 'Select one or more facilitator to facilitate this course', 'coursepress' ) . '</p>
						</label>
			<select data-nonce-search="'. $search_nonce . '" name="facilitators" style="width:350px;" id="facilitators" class="user-dropdown medium"></select>
			<input type="button" class="button button-primary facilitator-assign" value="' . esc_attr__( 'Assign', 'coursepress' ) . '" />
				</div>';
		} else {

			if ( ! empty( $facilitators ) ) {
				$content .= '<div class="wide">
					<label>' . __( 'Course Facilitators', 'coursepress' ) . '</label>
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
				$label = esc_html__( 'Invite New Instructor or Facilitator', 'coursepress' );
				$description = esc_html__( 'If the instructor or the facilitator can not be found in the list above, you will need to invite them via email.', 'coursepress' );
				$placeholder = __( 'instructor-or-facilitator@email.com', 'coursepress' );
			} else if ( $can_assign_instructor ) {
				$label = esc_html__( 'Invite New Instructor', 'coursepress' );
				$description = esc_html__( 'If the instructor can not be found in the list above, you will need to invite them via email.', 'coursepress' );
				$placeholder = __( 'facilitator@email.com', 'coursepress' );
			} else if ( $can_assign_facilitator ) {
				$label = esc_html__( 'Invite New Facilitator', 'coursepress' );
				$description = esc_html__( 'If the facilitator can not be found in the list above, you will need to invite them via email.', 'coursepress' );
				$placeholder = __( 'instructor@email.com', 'coursepress' );
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
				$content .= '<label>'.__( 'Instructor or Facilitator', 'coursepress' ).'</label>
							<ul>
<li><label><input type="radio" name="invite_instructor_type" value="instructor" checked="checked" /> ' . __( 'Instructor', 'coursepress' ) . '</label></li>
<li><label><input type="radio" name="invite_instructor_type" value="facilitator" /> ' . __( 'Facilitator', 'coursepress' ) . '</label></li>
							</ul>';
			} else if ( $can_assign_instructor ) {
				$content .= '<input type="hidden" name="invite_instructor_type="instructor" />';
			} else if ( $can_assign_facilitator ) {
				$content .= '<input type="hidden" name="invite_instructor_type="facilitator" />';
			}
			$content .= '<label for="invite_instructor_first_name">' . esc_html__( 'First Name', 'coursepress' ) . '</label>
							<input type="text" name="invite_instructor_first_name" placeholder="' . esc_attr__( 'First Name', 'coursepress' ) . '"/>
							<label for="invite_instructor_last_name">' . esc_html__( 'Last Name', 'coursepress' ) . '</label>
							<input type="text" name="invite_instructor_last_name" placeholder="' . esc_attr__( 'Last Name', 'coursepress' ) . '"/>
							<label for="invite_instructor_email">' . esc_html__( 'E-Mail', 'coursepress' ) . '</label>
							<input type="text" name="invite_instructor_email" placeholder="' . esc_attr( $placeholder ) . '"/>

							<div class="submit-message">
								<input class="button-primary" name="invite_instructor_trigger" id="invite-instructor-trigger" type="button" value="' . esc_attr__( 'Send Invite', 'coursepress' ) . '">
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

		return $content;
	}

	private static function render_setup_step_4() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_4', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 3 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-4">' . esc_html__( 'Step 4 – Course Dates', 'coursepress' ) . '
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
					esc_html__( 'Course Availability', 'coursepress' ) . '
					</label>
					<p class="description">' . esc_html__( 'These are the dates that the course will be available to students', 'coursepress' ) . '</p>
					<label class="checkbox medium">
						<input type="checkbox" name="meta_course_open_ended" ' . $open_ended_checked . ' />
						<span>' . esc_html__( 'This course has no end date', 'coursepress' ) . '</span>
					</label>
					<div class="date-range">
						<div class="start-date">
							<label for="meta_course_start_date" class="start-date-label required">' . esc_html__( 'Start Date', 'coursepress' ) . '</label>

							<div class="date">
								<input type="text" class="dateinput timeinput" name="meta_course_start_date" value="' . CoursePress_Data_Course::get_setting( $course_id, 'course_start_date', date( 'Y-m-d' ) ) . '"/><i class="calendar"></i>
							</div>
						</div>
						<div class="end-date ' . ( $open_ended_course ? 'disabled' : '' ) . '">
							<label for="meta_course_end_date" class="end-date-label required">' . esc_html__( 'End Date', 'coursepress' ) . '</label>
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
					esc_html__( 'Course Enrollment Dates', 'coursepress' ) . '
					</label>
					<p class="description">' . esc_html__( 'These are the dates that students will be able to enroll in a course.', 'coursepress' ) . '</p>
					<label class="checkbox medium">
						<input type="checkbox" name="meta_enrollment_open_ended" ' . $open_ended_checked . ' />
						<span>' . esc_html__( 'Students can enroll at any time', 'coursepress' ) . '</span>
					</label>
					<div class="date-range enrollment">
						<div class="start-date ' . ( $open_ended ? 'disabled' : '' ) . '">
							<label for="meta_enrollment_start_date" class="start-date-label required">' . esc_html__( 'Start Date', 'coursepress' ) . '</label>

							<div class="date">
								<input type="text" class="dateinput" name="meta_enrollment_start_date" value="' . CoursePress_Data_Course::get_setting( $course_id, 'enrollment_start_date', '' ) . '"/><i class="calendar"></i>
							</div>
						</div>
						<div class="end-date ' . ( $open_ended ? 'disabled' : '' ) . '">
							<label for="meta_enrollment_end_date" class="end-date-label required">' . esc_html__( 'End Date', 'coursepress' ) . '</label>
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

		return $content;
	}

	private static function render_setup_step_5() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_5', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 4 ? $setup_class . ' setup_marker' : $setup_class;
		$content = '
			<div class="step-title step-5">' . esc_html__( 'Step 5 – Classes, Discussion & Workbook', 'coursepress' ) . '
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
					esc_html__( 'Class Size', 'coursepress' ) . '
					</label>
					<p class="description">' . esc_html__( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size(s).', 'coursepress' ) . '</p>
					<label class="narrow col">
						<input type="checkbox" name="meta_class_limited" ' . $limit_checked . ' />
						<span>' . esc_html__( 'Limit class size', 'coursepress' ) . '</span>
					</label>

					<label class="num-students narrow col ' . ( $limited ? '' : 'disabled' ) . '">
						' . esc_html__( 'Number of students', 'coursepress' ) . '
						<input type="text" class="spinners" name="meta_class_size" value="' . CoursePress_Data_Course::get_setting( $course_id, 'class_size', '' ) . '" ' . ( $limited ? '' : 'disabled="disabled"' ) . '/>
					</label>
				</div>';

		$content .= '
				<div class="wide">
					<label>' .
					esc_html__( 'Course Discussion', 'coursepress' ) . '
					</label>
					<p class="description">' . esc_html__( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', 'coursepress' ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_allow_discussion" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'allow_discussion', false ) ) . ' />
						<span>' . esc_html__( 'Allow course discussion', 'coursepress' ) . '</span>
					</label>
				</div>';

		$content .= '
				<div class="wide">
					<label>' .
					esc_html__( 'Student Workbook', 'coursepress' ) . '
					</label>
					<p class="description">' . esc_html__( 'If checked, students can see their progress and grades.', 'coursepress' ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_allow_workbook" ' . CoursePress_Helper_Utility::checked( CoursePress_Data_Course::get_setting( $course_id, 'allow_workbook', false ) ) . ' />
						<span>' . esc_html__( 'Show student workbook', 'coursepress' ) . '</span>
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

		return $content;
	}

	private static function render_setup_step_6() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		// Payment can be disabled using the COURSEPRESS_DISABLE_PAYMENT constant or hooking the filter
		$disable_payment = defined( 'COURSEPRESS_DISABLE_PAYMENT' ) && true == COURSEPRESS_DISABLE_PAYMENT;
		$disable_payment = apply_filters( 'coursepress_disable_course_payments', $disable_payment, $course_id );

		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_6', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 5 ? $setup_class . ' setup_marker' : $setup_class;

		$payment_tagline = ! $disable_payment ? __( ' & Course Cost', 'coursepress' ) : '';

		$content = '
			<div class="step-title step-6">' . esc_html( sprintf( __( 'Step 6 – Enrollment%s', 'coursepress' ), $payment_tagline ) ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-6">
				<!-- depending on gateway setup, this could be save-attention -->
				<input type="hidden" name="meta_setup_step_6" value="saved" />
			';

		// Enrollment Options
		$enrollment_types = CoursePress_Data_Course::get_enrollment_types_array( $course_id );

		$content .= '<div class="wide">';
		$content .= sprintf( '<label>%s</label>', esc_html__( 'Enrollment Restrictions', 'coursepress' ) );

		$content .= '<p class="description">' . esc_html__( 'Select the limitations on accessing and enrolling in this course.', 'coursepress' ) . '</p>';
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
					esc_html__( 'Prerequisite Courses', 'coursepress' ) .
					'</label>
					<p class="description">' . esc_html__( 'Select the courses a student needs to complete before enrolling in this course', 'coursepress' ) . '</p>
					<select name="meta_enrollment_prerequisite" class="medium chosen-select chosen-select-course ' . $class_extra . '" multiple="true" data-placeholder=" ">
			';

		$courses = CoursePress_Data_Instructor::get_accessable_courses( wp_get_current_user(), true );

		$saved_settings = CoursePress_Data_Course::get_prerequisites( $course_id );

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
					esc_html__( 'Course Passcode', 'coursepress' ) .
					'</label>
				<p class="description">' . esc_html__( 'Enter the passcode required to access this course', 'coursepress' ) . '</p>
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
						esc_html__( 'Course Payment', 'coursepress' ) . '
					</label>
					<p class="description">' . esc_html__( 'Payment options for your course. Additional plugins are required and settings vary depending on the plugin.', 'coursepress' ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_payment_paid_course" ' . $paid_checked . ' />
						<span>' . esc_html__( 'This is a paid course', 'coursepress' ) . '</span>
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
								<a href="%s">Activate MarketPress</a>', 'coursepress' ), esc_url_raw( admin_url( 'admin.php?page=coursepress_settings&tab=extensions' ) ) );
			} else {
				$install_message = __( '<p>Please contact your administrator to enable MarketPress for your site.</p>', 'coursepress' );
			}

			if ( CP_IS_PREMIUM ) {
				$version_message = __( '<p>The full version of MarketPress has been bundled with CoursePress Pro.</p>', 'coursepress' );
			} else {
				$version_message = __( '<p>You can use the free or premium version of MarketPress to sell your courses.</p>', 'coursepress' );
			}

			$class = $is_paid ? '' : 'hidden';

			/**
			 * Hook this filter to get rid of the payment message
             */
            $message = sprintf(
                '<div class="payment-message %%s"><h3>%s</h3>%%s%%s<p>%s</p></div>',
                esc_html__( 'Sell your courses online with MarketPress.', 'coursepress' ),
                esc_html__( 'Other supported plugins: WooCommerce', 'coursepress' )
            );
            $payment_message = apply_filters( 'coursepress_course_payment_message',
                sprintf( $message, $class, $version_message, $install_message ), $course_id
            );
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

		return $content;
	}

	private static function render_setup_step_7() {
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		$setup_class = CoursePress_Data_Course::get_setting( $course_id, 'setup_step_7', '' );
		$setup_class = (int) CoursePress_Data_Course::get_setting( $course_id, 'setup_marker', 0 ) === 7 ? $setup_class . ' setup_marker' : $setup_class;

		/**
		 * Get defaults
		 */
		$defaults = CoursePress_Data_Course::get_defaults_setup_pages_content();
		/**
		 * Pre-Completion Page
		 */
		$pre_completion_title = CoursePress_Data_Course::get_setting( $course_id, 'pre_completion_title', $defaults['pre_completion']['title'] );
		$pre_completion_content = CoursePress_Data_Course::get_setting( $course_id, 'pre_completion_content', $defaults['pre_completion']['content'] );
		$pre_completion_content = htmlspecialchars_decode( $pre_completion_content );

		/**
		 * Course Completion Page
		 */
		$completion_title = CoursePress_Data_Course::get_setting( $course_id, 'course_completion_title', $defaults['course_completion']['title'] );
		$completion_content = CoursePress_Data_Course::get_setting( $course_id, 'course_completion_content', $defaults['course_completion']['content'] );
		$completion_content = htmlspecialchars_decode( $completion_content );

		$content = '<div class="step-title step-7">'
			. esc_html__( 'Step 7 - Course Completion', 'coursepress' )
			. '<div class="status '. $setup_class . '"></div>'
			. '</div>';

		$content .= '<div class="step-content step-7">
			<input type="hidden" name="meta_setup_step_7" value="saved" />';

		// Course completion
		$minimum_grade = CoursePress_Data_Course::get_setting( $course_id, 'minimum_grade_required', 100 );

		$content .= '<div class="wide minimum-grade">';
		$content .= sprintf( '<label class="required" for="meta_minimum_grade_required">%s</label> ', __( 'Minimum Grade Required', 'coursepress' ) );
		$content .= sprintf( '<input type="number" id="meta_minimum_grade_required" name="meta_minimum_grade_required" value="%d" min="0" max="100" class="text-small" />', esc_attr__( $minimum_grade ) );
		$content .= sprintf(
			'<p class="description">%s</p>',
			__( 'The minimum grade required to marked course completion and send course certficates.', 'coursepress' )
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
		$token_info = '<p class="description" style="margin-bottom: -25px;">'. __( sprintf( __( 'Use these tokens to display actual course details: %s', 'coursepress' ), implode( ', ', $tokens ) ), 'coursepress' ) . '</p>';

		// Pre-completion page
		$content .= '<div class="wide page-pre-completion">'
			. '<label>' . __( 'Pre-Completion Page', 'coursepress' ) . '</label>'
			. '<p class="description">' . __( 'Use the fields below to show custom pre-completion page after the student completed the course but require final assessment from instructors.', 'coursepress' ) . '</p>'
			. '<label for="meta_pre_completion_title" class="required">' . __( 'Page Title', 'coursepress' ) . '</label>'
			. '<input type="text" class="wide" name="meta_pre_completion_title" value="'. esc_attr( $pre_completion_title ) . '" />'
			. '<label for="meta_pre_completion_content" class="required">' . __( 'Page Content', 'coursepress' ) . '</label>'
			. $token_info
		;
		$content .= self::get_wp_editor( 'pre-completion-content', 'meta_pre_completion_content', $pre_completion_content );
		$content .= '</div>';

		$content .= '<div class="wide page-completion">'
			. '<label>' . __( 'Course Completion Page', 'coursepress' ) . '</label>'
			. '<p class="description">' . __( 'Use the fields below to show a custom page after successfull course completion.', 'coursepress' ) . '</p>'
			. '<label for="meta_course_completion_title" class="required">' . __( 'Page Title', 'coursepress' ) . '</label>'
			. '<input type="text" class="widefat" name="meta_course_completion_title" value="'. esc_attr( $completion_title ) . '" />'
		;

		$content .= '<label for="meta_course_completion_content" class="required">' . __( 'Page Content', 'coursepress' ) . '</label>' . $token_info;
		$content .= self::get_wp_editor( 'course-completion-editor-content', 'meta_course_completion_content', $completion_content );
		$content .= '</div>';

		/**
		 * Course Fail Page
		 */
		$failed_title = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_title', $defaults['course_failed']['title'] );
		$failed_content = CoursePress_Data_Course::get_setting( $course_id, 'course_failed_content', $defaults['course_failed']['content'] );
		$failed_content = htmlspecialchars_decode( $failed_content );

		$content .= '<div class="wide page-failed">
			<label>' . __( 'Failed Page', 'coursepress' ) . '</label>
			<p class="description">'. __( 'Use the fields below to display failure page when an student completed a course but fail to reach the minimum required grade.', 'coursepress' ) . '</p>
			<label for="meta_course_failed_title" class="required">'. __( 'Page Title', 'coursepress' ) . '</label>
			<input type="text" class="widefat" name="meta_course_failed_title" value="'. esc_attr__( $failed_title ) . '" />
			<label for="meta_course_field_content" class="required">'. __( 'Page Content', 'coursepress' ) . '</label>'
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
		$content .= sprintf( '<br /><h3>%s</h3>', esc_html__( 'Course Certificate', 'coursepress' ) );
		$content .= sprintf(
			'<a href="%s" target="_blank" class="button button-default btn-cert %s" style="float:right;margin-top:-35px;">%s</a>',
			esc_url( $certificate_link ),
			esc_attr( $class ),
			esc_html__( 'Preview', 'coursepress' )
		);
		$content .= '<label>';
		$content .= '<input type="checkbox" name="meta_basic_certificate" value="1" '. checked( 1, $value, false ) . ' /> '. __( 'Override course certificate.', 'coursepress' )

			. '</label>'
			. '<p class="description">' . __( 'Use this field to override general course certificate setting.', 'coursepress' ) . '</p>';
		$content .= sprintf( '<div class="options %s">', cp_is_true( $value )? '':'hidden' );
		$content .= '<label for="meta_basic_certificate_layout">' . __( 'Certificate Content', 'coursepress' ) . '</label>'
			. '<p class="description" style="float:left;">' . __( 'Useful tokens: ', 'coursepress' ) . implode( ', ', $field_keys ) . '</p>'
		;
		$content .= self::get_wp_editor( 'basic-certificate-layout', 'meta_basic_certificate', $certficate_content );
		$content .= '<table class="wide"><tr><td style="width:20%;">'
			. '<label>' . __( 'Background Image', 'coursepress' ) . '</label>'
			. '</td><td>';
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_certificate_background',
			'meta_certificate_background',
			array(
				'placeholder' => __( 'Choose background image', 'coursepress' ),
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
		$content .= '<tr><td><label>' . __( 'Content Padding', 'coursepress' ) . '</label></td><td>';
		$content .= __( 'Top', 'coursepress' ) . ': <input type="text" size="10" name="meta_cert_padding[top]" value="'. esc_attr( $padding_top ) . '" />';
		$content .= __( 'Bottom', 'coursepress' ) . ': <input type="text" size="10" name="meta_cert_padding[bottom]" value="'. esc_attr( $padding_bottom ) .'" />';
		$content .= __( 'Left', 'coursepress' ) . ': <input type="text" size="10" name="meta_cert_padding[left]" value="'. esc_attr( $padding_left ) . '" />';
		$content .= __( 'Right', 'coursepress' ) . ': <input type="text" size="10" name="meta_cert_padding[right]" value="'. esc_attr( $padding_right ) . '" />';
		$content .= '</td></tr>';
		$content .= '<tr><td><label>' . __( 'Page Orientation', 'coursepress' ) . '</label></td><td>';
		$content .= '<label style="float:left;margin-right:25px;"><input type="radio" name="meta_page_orientation" value="L" '. checked( 'L', CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', 'L' ), false ) .' /> ' . __( 'Landscape', 'coursepress' ) . '</label>';
		$content .= '<label style="float:left;"><input type="radio" name="meta_page_orientation" value="P" '. checked( 'P', CoursePress_Data_Course::get_setting( $course_id, 'page_orientation', '' ), false ) .'/>' . __( 'Portrait', 'coursepress' ) . '</label>';
		$content .= '</td></tr>';
		$content .= '</table></div>';
		$content .= '</div>';

		// Buttons
		$content .= self::get_buttons( $course_id, 7, array( 'next' => false ) );
		$content .= '</div>';

		return $content;
	}

	private static function render_tab_units() {
		return CoursePress_View_Admin_Course_UnitBuilder::render();
	}

	private static function render_tab_students() {
		return CoursePress_View_Admin_Course_Student::render();
	}


	public static function get_tabs() {

		// Make it a filter so we can add more tabs easily
		self::$tabs = apply_filters( self::$slug . '_tabs', self::$tabs );

		self::$tabs['setup'] = array(
			'title' => __( 'Course Setup', 'coursepress' ),
			'description' => __( 'Edit your course specific settings below.', 'coursepress' ),
			'order' => 10,
			'buttons' => 'none',
		);
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;

		if ( 'edit' == self::_current_action() ) {
			if ( CoursePress_Data_Capabilities::can_view_course_units( $course_id ) ) {
				$units = CoursePress_Data_Course::get_unit_ids( $course_id, array( 'publish', 'draft' ) );
				self::$tabs['units'] = array(
					'title' => sprintf( __( 'Units (%s)', 'coursepress' ), count( $units ) ),
					'description' => __( 'Edit your course specific settings below.', 'coursepress' ),
					'order' => 20,
					'buttons' => 'none',
				);
			}

			if ( CoursePress_Data_Capabilities::can_view_course_students( $course_id ) ) {
				self::$tabs['students'] = array(
					'title' => sprintf(
						__( 'Students (%s)', 'coursepress' ),
						CoursePress_Data_Course::count_students( $course_id )
					),
					'description' => __( 'Edit your course specific settings below.', 'coursepress' ),
					'order' => 30,
					'buttons' => 'none',
				);
			}
		}

		// Make sure that we have all the fields we need
		foreach ( self::$tabs as $key => $tab ) {
			self::$tabs[ $key ]['buttons'] = isset( $tab['buttons'] ) ? $tab['buttons'] : 'both';
			self::$tabs[ $key ]['class'] = isset( $tab['class'] ) ? $tab['class'] : '';
			self::$tabs[ $key ]['is_form'] = isset( $tab['is_form'] ) ? $tab['is_form'] : true;
			self::$tabs[ $key ]['order'] = isset( $tab['order'] ) ? $tab['order'] : 999; // Set default order to 999... bottom of the list
		}

		// Order the tabs
		self::$tabs = CoursePress_Helper_Utility::sort_on_key( self::$tabs, 'order' );

		return self::$tabs;
	}

	public static function update_course() {

		$data = json_decode( file_get_contents( 'php://input' ) );
		$step_data = $data->data;
		$json_data = array();
		$success = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Course Update: No action.', 'coursepress' );
			wp_send_json_error( $json_data );
		}

		$action = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		switch ( $action ) {

			// Update Course
			case 'update_course':

				if (
					isset( $step_data->step )
					&& wp_verify_nonce( $data->data->nonce, 'setup-course' )
				) {

					$step = (int) $step_data->step;

					$course_id = CoursePress_Data_Course::update( $step_data->course_id, $step_data );
					$json_data['course_id'] = $course_id;

					$next_step = (int) $data->next_step;
					$json_data['last_step'] = $step;
					$json_data['next_step'] = $next_step;
					$json_data['redirect'] = $data->data->is_finished;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
					$settings = CoursePress_Data_Course::get_setting( $course_id, true );

					/*
					 * save course start date as separate field, we need it to
					 * sort courses on courses list page. The post-meta field
					 * contains the numeric timestamp, not a formated string!
					 */
					$start_date = 0;
					if ( isset( $settings['course_start_date'] ) ) {
						$start_date = $settings['course_start_date'];
					}
					$start_date = strtotime( $start_date );
					update_post_meta( $course_id, 'course_start_date', $start_date );

					/**
					 * save enrollment_end_date
					 */
					$course_open_ended = isset( $settings['course_open_ended'] ) && cp_is_true( $settings['course_open_ended'] );
					if ( $course_open_ended ) {
						delete_post_meta( $course_id, 'course_enrollment_end_date' );
					} else {
						$enrollment_end_date = 0;
						if ( isset( $settings['enrollment_end_date'] ) ) {
							$enrollment_end_date = $settings['enrollment_end_date'];
						}
						$enrollment_end_date = strtotime( $enrollment_end_date );
						update_post_meta( $course_id, 'course_enrollment_end_date', $enrollment_end_date );
					}

					/** This action is documented in include/coursepress/data/class-course.php */
					do_action( 'coursepress_course_updated', $course_id, $settings );
				}

				break;

			case 'toggle_course_status':

				$course_id = $data->data->course_id;

				if (
					wp_verify_nonce( $data->data->nonce, 'publish-course' )
					&& CoursePress_Data_Capabilities::can_update_course( $data->data->course_id )
				) {

					wp_update_post( array(
						'ID' => $course_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-course' );
					$success = true;
					$settings = CoursePress_Data_Course::get_setting( $course_id, true );
					/** This action is documented in include/coursepress/data/class-course.php */
					do_action( 'coursepress_course_updated', $course_id, $settings );

				}

				$json_data['course_id'] = $course_id;
				$json_data['state'] = $data->data->state;

				break;

			// Delete Instructor
			case 'delete_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$json_data['who'] = 'instructor';
					if ( isset( $data->data->who ) && 'facilitator' === $data->data->who ) {
						CoursePress_Data_Facilitator::remove_course_facilitator(
							$data->data->course_id,
							$data->data->instructor_id
						);
						$json_data['who'] = 'facilitator';
					} else {
						CoursePress_Data_Course::remove_instructor(
							$data->data->course_id,
							$data->data->instructor_id
						);
					}
					$json_data['instructor_id'] = $data->data->instructor_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}

				break;

			// Add Instructor
			case 'add_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Course::add_instructor( $data->data->course_id, $data->data->instructor_id );
					$user = get_userdata( $data->data->instructor_id );
					$json_data['id'] = $data->data->instructor_id;
					$json_data['display_name'] = $user->display_name;
					$json_data['course_id'] = $data->data->course_id;
					$json_data['avatar'] = get_avatar( $data->data->instructor_id, 80 );
					$json_data['who'] = 'instructor';

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}

				break;

			// Invite Instructor
			case 'invite_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$response = '';
					if ( isset( $data->data->who ) && 'facilitator' === $data->data->who ) {
						$response = CoursePress_Data_Facilitator::send_invitation(
							(int) $data->data->course_id,
							$data->data->email,
							$data->data->first_name,
							$data->data->last_name
						);
					} else {
						$response = CoursePress_Data_Instructor::send_invitation(
							(int) $data->data->course_id,
							$data->data->email,
							$data->data->first_name,
							$data->data->last_name
						);
					}
					$json_data['message'] = $response['message'];
					$json_data['data'] = $data->data;
					$json_data['invite_code'] = $response['invite_code'];
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = $response['success'];
				}
				break;

			// Delete Invite
			case 'delete_instructor_invite':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$json_data['who'] = 'instructor';
					if ( isset( $data->data->who ) && 'facilitator' === $data->data->who ) {
						CoursePress_Data_Facilitator::delete_invitation(
							$data->data->course_id,
							$data->data->invite_code
						);
						$json_data['who'] = 'facilitator';
					} else {
						CoursePress_Data_Instructor::delete_invitation(
							$data->data->course_id,
							$data->data->invite_code
						);
					}
					$json_data['course_id'] = $data->data->course_id;
					$json_data['invite_code'] = $data->data->invite_code;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			case 'enroll_student':

				if ( wp_verify_nonce( $data->data->nonce, 'add_student' ) ) {
					CoursePress_Data_Course::enroll_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'add_student' );
					$success = true;
				}
				break;

			case 'withdraw_student':
				if ( wp_verify_nonce( $data->data->nonce, 'withdraw-single-student' ) ) {
					CoursePress_Data_Course::withdraw_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw-single-student' );
					$success = true;
				}
				break;

			case 'withdraw_all_students':

				if ( wp_verify_nonce( $data->data->nonce, 'withdraw_all_students' ) ) {
					CoursePress_Data_Course::withdraw_all_students( $data->data->course_id );
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw_all_students' );
					$success = true;
				}
				break;

			case 'invite_student':

				if ( wp_verify_nonce( $data->data->nonce, 'invite_student' ) ) {
					$email_data = CoursePress_Helper_Utility::object_to_array( $data->data );
					$response = CoursePress_Data_Course::send_invitation( $email_data );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'invite_student' );

					// Save invited student
					$email = sanitize_email( $email_data['email'] );
					$course_id = (int) $email_data['course_id'];
					$invited_students = CoursePress_Data_Course::get_setting( $course_id, 'invited_students', array() );
					$invite_data = array(
						'first_name' => $email_data['first_name'],
						'last_name' => $email_data['last_name'],
						'email' => $email_data['email'],
					);
					$invited_students[ $email ] = $invite_data;

					// Save invited data
					CoursePress_Data_Course::update_setting( $course_id, 'invited_students', $invited_students );

					$success = $response;
				}
				break;

			case 'remove_student_invitation':
				if ( wp_verify_nonce( $data->data->nonce, 'coursepress_remove_invite' ) ) {
					$course_id = (int) $data->data->course_id;
					$student_email = sanitize_email( $data->data->email );
					$invited_students = CoursePress_Data_Course::get_setting( $course_id, 'invited_students', array() );

					if ( ! empty( $invited_students[ $student_email ] ) ) {
						unset( $invited_students[ $student_email ] );
					}
					// Resaved invited students
					CoursePress_Data_Course::update_setting( $course_id, 'invited_students', $invited_students );
					$success = true;
				}
				break;

			// Add facilitator
			case 'add_facilitator':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Facilitator::add_course_facilitator( $data->data->course_id, $data->data->facilitator_id );
					$json_data['who'] = 'facilitator';
					$json_data['id'] = $data->data->facilitator_id;
					$json_data['display_name'] = get_user_option( 'display_name', $data->data->facilitator_id );
					$json_data['course_id'] = $data->data->course_id;

					$user = get_userdata( $data->data->facilitator_id );
					$json_data['avatar'] = get_avatar( $user->user_email, 80 );

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				} else {
					$json_data['facilitator_id'] = $data->data->facilitator_id;
					$json_data['message'] = __( 'Unable to add facilitator!', 'coursepress' );
				}

				break;
			// Remove facilitator
			case 'remove_facilitator':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Data_Facilitator::remove_course_facilitator( $data->data->course_id, $data->data->facilitator_id );
					$json_data['facilitator_id'] = $data->data->facilitator_id;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success = true;
				}
				break;

			case 'bulk_actions':

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					$courses = $data->data->courses;
					$action = $data->data->the_action;

					foreach ( $courses as $course_id ) {
						switch ( $action ) {

							case 'publish':
								if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
									continue;
								}
								wp_update_post( array(
									'ID' => $course_id,
									'post_status' => 'publish',
								) );
							break;
							case 'unpublish':
								if ( ! CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
									continue;
								}
								wp_update_post( array(
									'ID' => $course_id,
									'post_status' => 'draft',
								) );
							break;
							case 'delete':
								CoursePress_Admin_Controller_Course::delete_course( $course_id );
							break;

						}
					}

					$settings = CoursePress_Data_Course::get_setting( $course_id, true );
					/** This action is documented in include/coursepress/data/class-course.php */
					do_action( 'coursepress_course_updated', $course_id, $settings );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'bulk_action_nonce' );
					$success = true;
				}
				break;

			case 'delete_course':

				if ( wp_verify_nonce( $data->data->nonce, 'delete_course' ) ) {

					$course_id = (int) $data->data->course_id;
					CoursePress_Admin_Controller_Course::delete_course( $course_id );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'delete_course' );
					$success = true;
				}

				break;

			case 'duplicate_course':
				// Check wp nonce.
				if ( wp_verify_nonce( $data->data->nonce, 'duplicate_course' ) ) {
					$json_data = CoursePress_Data_Course::duplicate_course( $data );
					$success = ! empty( $json_data );
				}

				break;

			case 'send_email':
				if ( wp_verify_nonce( $data->data->nonce, 'send_email_to_enroled_students' ) ) {
					$course_id = $data->data->course_id;
					$students = CoursePress_Data_Course::get_students( $course_id );
					$error_message = __( 'No email sent!', 'coursepress' );

					// Filter list of students to send email to
					if ( ! empty( $data->data->send_to ) && 'all' != $data->data->send_to ) {
						$send_to = $data->data->send_to;
						$filtered_students = array();

						foreach ( $students as $student ) {
							$student_progress = CoursePress_Data_Student::get_completion_data( $student->ID, $course_id );
							$units_progress = CoursePress_Helper_Utility::get_array_val(
								$student_progress,
								'completion/progress'
							);

							if ( 'all_with_submission' === $send_to && intval( $units_progress ) > 0 ) {
								$filtered_students[] = $student;
							} elseif ( intval( $send_to ) > 0 ) {
								$per_unit_progress = CoursePress_Helper_Utility::get_array_val(
									$student_progress,
									'completion/' . $send_to . '/progress'
								);

								if ( intval( $per_unit_progress ) > 0 ) {
									$filtered_students[] = $student;
								}
							}
						}

						if ( count( $filtered_students ) > 0 ) {
							$students = $filtered_students;
						} else {
							$error_message = __( 'No students found!', 'coursepress' );
							$students = array();
						}
					}

					/**
					 * post body vars
					 */
					$post = get_post( $course_id );
					$course_name = $post->post_title;
					$course_summary = $post->post_excerpt;
					$valid_stati = array( 'draft', 'pending', 'auto-draft' );

					if ( in_array( $post->post_status, $valid_stati ) ) {
						$course_address = CoursePress_Core::get_slug( 'course/', true ) . $post->post_name . '/';
					} else {
						$course_address = get_permalink( $course_id );
					}

					if ( CoursePress_Core::get_setting( 'general/use_custom_login', true ) ) {
						$login_url = CoursePress_Core::get_slug( 'login', true );
					} else {
						$login_url = wp_login_url();
					}
					$json_data['message'] = array(
						'body' => $data->data->body,
						'subject' => $data->data->subject,
						'to' => array(),
					);

					// Email Content.
					$vars = array(
						'COURSE_ADDRESS' => esc_url( $course_address ),
						'COURSE_EXCERPT' => $course_summary,
						'COURSE_NAME' => $course_name,
						'COURSE_OVERVIEW' => $course_summary,
						'COURSES_ADDRESS' => CoursePress_Core::get_slug( 'course', true ),
					);
					$vars = CoursePress_Helper_Utility::add_site_vars( $vars );

					$count = 0;
					/**
					 * send mail to each student
					 */
					foreach ( $students as $student ) {
						$vars['STUDENT_FIRST_NAME'] = empty( $student->first_name ) && empty( $student->last_name ) ? $student->display_name : $student->first_name;
						$vars['STUDENT_LAST_NAME'] = $student->last_name;
						$vars['STUDENT_LOGIN'] = $student->data->user_login;
						$body = CoursePress_Helper_Utility::replace_vars( $data->data->body, $vars );
						$args = array(
							'subject' => $data->data->subject,
							'to' => $student->user_email,
							'message' => $body,
						);
						if ( CoursePress_Helper_Email::send_email( '', $args ) ) {
							$count++;
						}
					}
					/**
					 * add message
					 */
					if ( $count ) {
						$success = true;
						$json_data['message']['info'] = sprintf(
							_n(
								'%d email have been sent successfully.',
								'%d emails have been sent successfully.',
								$count,
								'coursepress'
							),
							$count
						);
					} else {
						$success = false;
						$json_data['message']['info'] = $error_message;
					}
				} else {
					$json_data['message']['to'] = 0;
					$json_data['message']['info'] = __( 'Something went wrong.', 'coursepress' );
				}
				break;

		}
		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}

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
	public static function get_buttons( $course_id, $step, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'previous' => true,
				'next' => true,
				'update' => true,
			)
		);
		$content = '';
		/**
		 * previous button
		 */
		if ( $args['previous'] ) {
			$content .= sprintf(
				'<input type="button" class="button step prev step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Previous', 'coursepress' )
			);
		}
		/**
		 * next button
		 */
		if ( $args['next'] ) {
			$content .= sprintf(
				'<input type="button" class="button step next step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Next', 'coursepress' )
			);
		}

		// Finish button
		if ( 7 == $step ) {
			$content .= sprintf(
				'<input type="button" class="button step finish step-7" value="%s" />',
				esc_attr__( 'Finish', 'coursepress' )
			);
		}
		/**
		 * update button
		 */
		if ( $args['update'] && CoursePress_Data_Capabilities::can_update_course( $course_id ) ) {
			$content .= sprintf(
				'<input type="button" class="button step update hidden step-%d" value="%s" />',
				esc_attr( $step ),
				esc_attr__( 'Update', 'coursepress' )
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
	 * return slug.
	 *
	 * @since 2.0.0
	 *
	 * @return string slug
	 */
	public static function get_slug() {
		return self::$slug;
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
					'title' => __( 'Course Completion Certificate', 'coursepress' ),
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

	static $certificate = null;
	public static function test_mail_certificate() {
		if ( isset( $_REQUEST['nonce'] ) && wp_verify_nonce( $_REQUEST['nonce'], 'cp_certificate_mail' ) ) {
			$course_id = (int) $_REQUEST['course_id'];
			$course = get_post( $course_id );
			$filename = 'cert-preview-' . $course_id . '.pdf';
			$filename = CoursePress_Helper_PDF::cache_path() . $filename;
			self::$certificate = $filename;
			$userdata = get_userdata( get_current_user_id() );

			$mail_args = array(
				'email' => $userdata->user_email,
				'course_id' => $course_id,
				'first_name' => empty( $userdata->first_name ) && empty( $userdata->last_name ) ? $userdata->display_name : $userdata->first_name,
				'last_name' => $userdata->last_name,
				'completion_date' => 'NOW!',
				'certificate_id' => '12345',
				'course_name' => $course->post_title,
				'course_address' => CoursePress_Core::get_slug( 'courses/', true ) . $course->post_name,
				'unit_list' => '',
			);

			add_filter( 'wp_mail', array( __CLASS__, 'attached_pdf_certificate' ), 100 );
			CoursePress_Helper_Email::send_email(
				CoursePress_Helper_Email::BASIC_CERTIFICATE,
				$mail_args
			);

			?>
			<html>
				<head>
					<style>
						body { background-color: #F4F4F4; text-align: center; padding: 100px; }
					</style>
				</head>
				<body>
					<h1><?php _e( 'Test mail sent!', 'coursepress' ); ?></h1>
					<p><?php _e( 'Please check your inbox at '. $userdata->user_email . '!', 'coursepress' ); ?></p>
				</body>
			</html>
			<?php
			exit;
		}
	}

	public static function attached_pdf_certificate( $mail_atts ) {
		if ( self::$certificate ) {
			$mail_atts['attachments'] = array( self::$certificate );
		}
		return $mail_atts;
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
}
