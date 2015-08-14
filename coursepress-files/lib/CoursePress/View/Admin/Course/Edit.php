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
	private static $current_course = false;

	public static function init() {

		self::$action = isset( $_GET['action'] ) && in_array( $_GET['action'], self::$allowed_actions ) ? sanitize_text_field( $_GET['action'] ) : 'new';

		self::$title = __( 'Edit Course/CoursePress', CoursePress::TD );

		switch ( self::$action ) {
			case 'new':
				self::$menu_title = __( 'New Course', CoursePress::TD );
				break;
			case 'edit':
				if ( isset( $_GET['id'] ) && 0 !== (int) $_GET['id'] ) {
					self::$current_course = get_post( (int) $_GET['id'] );
				}
				self::$menu_title = __( 'Edit Course', CoursePress::TD );
				break;
		}

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'process_form' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'render_page' ) );

		// Update Course
		add_action( 'wp_ajax_update_course', array( __CLASS__, 'update_course' ) );

		// Update UnitBuilder
		add_action( 'wp_ajax_unit_builder', array( 'CoursePress_View_Admin_Course_UnitBuilder', 'unit_builder_ajax' ) );

	}

	public static function add_valid( $valid_pages ) {
		$valid_pages[] = self::$slug;

		return $valid_pages;
	}

	public static function add_page( $pages ) {
		$pages[ self::$slug ] = array(
			'title'      => self::$title,
			'menu_title' => self::$menu_title,
		);

		return $pages;
	}

	private static function _current_action() {
		return self::$action;
	}

	public static function process_form() {

		//error_log( print_r( $_REQUEST, true ) );

	}

	public static function render_page() {

		$tabs      = self::get_tabs();
		$tab_keys  = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';
		$tab       = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );

		$method = preg_replace( '/\_$/', '', 'render_tab_' . $tab );

		if ( method_exists( __CLASS__, $method ) ) {
			$content = call_user_func( __CLASS__ . '::' . $method );
		}

		$hidden_args = $_GET;
		unset( $hidden_args['_wpnonce'] );

		// Publish Course Toggle
		$course_id      = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$status         = get_post_status( $course_id );
		$ui             = array(
			'label'       => 'Publish Course',
			'left'        => '<i class="fa fa-ban"></i>',
			'left_class'  => 'red',
			'right'       => '<i class="fa fa-check"></i>',
			'right_class' => 'green',
			'state'       => 'publish' === $status ? 'on' : 'off',
			'data'        => array(
				'nonce' => wp_create_nonce( 'publish-course' ),
			)
		);
		$ui['class']    = 'course-' . $course_id;
		$publish_toggle = ! empty( $course_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-course-toggle', 'publish-course-toggle', $ui ) : '';

		$content = '<div class="coursepress_settings_wrapper">' .
		           '<h3>' . esc_html( CoursePress_Core::$name ) . ' : ' . esc_html( self::$menu_title ) . '</h3>
		            <hr />' .
		           CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $hidden_args, self::$slug, $tab, false, 'horizontal', $publish_toggle ) .
		           '</div>';

		echo $content;
	}

	private static function render_tab_setup() {

		// Setup Nonce
		$setup_nonce = wp_create_nonce( 'setup-course' );

		$content = '
        <div class="step-container">
			<div id="course-setup-steps" data-nonce="' . $setup_nonce . '">
				' . self::render_setup_step_1() . '
				' . self::render_setup_step_2() . '
				' . self::render_setup_step_3() . '
				' . self::render_setup_step_4() . '
				' . self::render_setup_step_5() . '
				' . self::render_setup_step_6() . '
			</div>
		</div>
		';

		return $content;
	}

	private static function render_setup_step_1() {

		$course_id   = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_1', '' );
		$setup_class = ( (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 6 ) || ( (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 0 ) ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-1">' . esc_html__( 'Step 1 – Course Overview', CoursePress::TD ) . '
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
		            esc_html__( 'Course Name', CoursePress::TD ) . '
						</label>
						<input class="wide" type="text" name="course_name" id="course_name" value="' . $course_name . '"/>
				</div>';

		// Course Excerpt / Short Overview
		$editor_name    = "course_excerpt";
		$editor_id      = "courseExcerpt";
		$editor_content = ! empty( self::$current_course ) ? htmlspecialchars_decode( self::$current_course->post_excerpt ) : '';
		//$editor_content	 = htmlspecialchars_decode( ( isset( $_GET[ 'course_id' ] ) ? $course_details->post_excerpt : '' ) );
		//$editor_content = "whatup!";

		$args = array(
			"textarea_name" => $editor_name,
			"editor_class"  => 'cp-editor cp-course-overview',
			"textarea_rows" => 4,
			"media_buttons" => false,
			//"quicktags"		 => false,
		);

		// Filter $args
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		ob_start();
		wp_editor( $editor_content, $editor_id, $args );
		$editor_html = ob_get_clean();

		$content .= '
				<div class="wide">
						<label for="courseExcerpt" class="required drop-line">' .
		            esc_html__( 'Course Excerpt / Short Overview', CoursePress::TD ) . '
						</label>
						' . $editor_html . '
				</div>';

		// Listing Image
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_listing_image',
			'meta_listing_image',
			array(
				'placeholder' => __( 'Add Image URL or Browse for Image', CoursePress::TD ),
				'title'       => __( 'Listing Image', CoursePress::TD ),
				'value'       => CoursePress_Model_Course::get_listing_image( $course_id ),
			)
		);

		// Course Category
		$category           = CoursePress_Model_Course::get_post_category_name( true );
		$cpt                = CoursePress_Model_Course::get_post_type_name( true );
		$url                = 'edit-tags.php?taxonomy=' . $category . '&post_type=' . $cpt;
		$terms              = CoursePress_Model_Course::get_terms();
		$course_terms_array = CoursePress_Model_Course::get_course_terms( (int) $_GET['id'], true );

		$class_extra = is_rtl() ? 'chosen-rtl' : '';

		$content .= '
				<div class="wide">
					<label for="meta_course_category" class="medium">' .
		            esc_html__( 'Course Category', CoursePress::TD ) . '
		                <a class="context-link" href="' . esc_url_raw( $url ) . '">' . esc_html__( 'Manage Categories', CoursePress::TD ) . '</a>
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
		$language = CoursePress_Model_Course::get_setting( $course_id, 'course_language' );
		$content .= '
				<div class="wide">
						<label for="meta_course_language">' .
		            esc_html__( 'Course Language', CoursePress::TD ) . '
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
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-1" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
					<input type="button" class="button step update step-1" value="' . esc_attr__( 'Update', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}

	private static function render_setup_step_2() {
		$course_id   = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_2', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 1 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-2">' . esc_html__( 'Step 2 – Course Description', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-2">
				<input type="hidden" name="meta_setup_step_2" value="saved" />
			';

		// Featured Video
		$supported_ext = implode( ', ', wp_get_video_extensions() );
		$placeholder   = sprintf( __( 'Add URL or Browse ( %s )', CoursePress::TD ), $supported_ext );
		$content .= CoursePress_Helper_UI::browse_media_field(
			'meta_featured_video',
			'meta_featured_video',
			array(
				'placeholder' => $placeholder,
				'title'       => __( 'Featured Video', CoursePress::TD ),
				'value'       => CoursePress_Model_Course::get_setting( $course_id, 'featured_video' ),
				'type'        => 'video',
				'description' => __( 'This is used on the Course Overview page and will be displayed with the course description.', CoursePress::TD )
			)
		);

		// Course Description
		$editor_name    = "course_description";
		$editor_id      = "courseDescription";
		$editor_content = ! empty( self::$current_course ) ? htmlspecialchars_decode( self::$current_course->post_content ) : '';

		$args = array(
			"textarea_name" => $editor_name,
			"editor_class"  => 'cp-editor cp-course-overview',
			"textarea_rows" => 10,
			"media_buttons" => true,
		);

		// Filter $args
		$args = apply_filters( 'coursepress_element_editor_args', $args, $editor_name, $editor_id );

		ob_start();
		wp_editor( $editor_content, $editor_id, $args );
		$editor_html = ob_get_clean();

		$content .= '
				<div class="wide">
						<label for="courseDescription" class="required">' .
		            esc_html__( 'Course Description', CoursePress::TD ) . '
						</label><br />
						' . $editor_html . '
				</div>';

		// Course Structure
		$content .= '
				<div class="wide">
					<label>' . esc_html__( 'Course Structure', CoursePress::TD ) . '</label>
					<p>' . esc_html__( 'This gives you the option to show/hide Course Units, Lessons, Estimated Time and Free Preview options on the Course Overview page', CoursePress::TD ) . '</p>

					<div class="course-structure">

						<label class="checkbox">
							<input type="checkbox" name="meta_structure_visible" ' . CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'structure_visible', true ) ) . ' />
							<span>' . esc_html__( 'Show the Course Overview structure and Preview Options', CoursePress::TD ) . '</span>
			            </label>
			            <label class="checkbox">
							<input type="checkbox" name="meta_structure_show_duration" ' . CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'structure_show_duration', true ) ) . ' />
							<span>' . esc_html__( 'Display Time Estimates for Units and Lessons', CoursePress::TD ) . '</span>
						</label>

						<table class="course-structure-tree">
							<thead>
								<tr>
									<th class="column-course-structure">' . esc_html__( 'Course Structure', CoursePress::TD ) . ' <small>' . esc_html__( 'Units and Pages with Modules selected will automatically be visible (only selected Modules accessible).', CoursePress::TD ) . '</small></th>
									<th class="column-show">' . esc_html__( 'Show', CoursePress::TD ) . '</th>
									<th class="column-free-preview">' . esc_html__( 'Free Preview', CoursePress::TD ) . '</th>
									<th class="column-time">' . esc_html__( 'Time', CoursePress::TD ) . '</th>
								</tr>
					            <tr class="break"><th colspan="4"></th></tr>
							</thead>
							<tfoot>
								<tr class="break"><th colspan="4"></th></tr>
								<tr>
									<th class="column-course-structure">' . esc_html__( 'Course Structure', CoursePress::TD ) . '</th>
									<th class="column-show">' . esc_html__( 'Show', CoursePress::TD ) . '</th>
									<th class="column-free-preview">' . esc_html__( 'Free Preview', CoursePress::TD ) . '</th>
									<th class="column-time">' . esc_html__( 'Time', CoursePress::TD ) . '</th>
								</tr>
							</tfoot>
							<tbody>';

		$units = CoursePress_Model_Course::get_units_with_modules( $course_id, array( 'publish', 'draft' ) );
		$units = CoursePress_Helper_Utility::sort_on_key( $units, 'order' );

		$count           = 0;
		$visible_units   = CoursePress_Model_Course::get_setting( $course_id, 'structure_visible_units', array() );
		$preview_units   = CoursePress_Model_Course::get_setting( $course_id, 'structure_preview_units', array() );
		$visible_pages   = CoursePress_Model_Course::get_setting( $course_id, 'structure_visible_pages', array() );
		$preview_pages   = CoursePress_Model_Course::get_setting( $course_id, 'structure_preview_pages', array() );
		$visible_modules = CoursePress_Model_Course::get_setting( $course_id, 'structure_visible_modules', array() );
		$preview_modules = CoursePress_Model_Course::get_setting( $course_id, 'structure_preview_modules', array() );

		foreach ( $units as $unit ) {

			$estimations = CoursePress_Model_Unit::get_time_estimation( $unit['unit']->ID, $units );
			$count += 1;
			$status      = 'publish' === $unit['unit']->post_status ? '' : __( '[DRAFT] ', CoursePress::TD );
			$draft_class = 'publish' === $unit['unit']->post_status ? '' : 'draft';

			$alt = $count % 2 ? 'even' : 'odd';

			$unit_view_checked    = isset( $visible_units[ $unit['unit']->ID ] ) ? CoursePress_Helper_Utility::checked( $visible_units[ $unit['unit']->ID ] ) : false;
			$unit_preview_checked = isset( $preview_units[ $unit['unit']->ID ] ) ? CoursePress_Helper_Utility::checked( $preview_units[ $unit['unit']->ID ] ) : false;
			$content .= '
								<tr class="unit unit-' . $unit['unit']->ID . ' treegrid-' . $count . ' ' . $draft_class . ' ' . $alt . '">
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
				$page_title = ! empty( $page['title'] ) ? $page['title'] : sprintf( __( 'Page %s', CoursePress::TD ), $key );

				$page_key = (int) $unit['unit']->ID . '_' . (int) $key;

				$page_view_checked    = isset( $visible_pages[ $page_key ] ) ? CoursePress_Helper_Utility::checked( $visible_pages[ $page_key ] ) : '';
				$page_preview_checked = isset( $preview_pages[ $page_key ] ) ? CoursePress_Helper_Utility::checked( $preview_pages[ $page_key ] ) : '';
				$alt                  = $count % 2 ? 'even' : 'odd';
				$content .= '
								<tr class="page page-' . $key . ' treegrid-' . $count . ' treegrid-parent-' . $unit_parent . ' ' . $draft_class . ' ' . $alt . '">
			                        <td>' . $page_title . '</td>
			                        <td><input type="checkbox" name="meta_structure_visible_pages[' . $page_key . ']" value="1" ' . $page_view_checked . '/></td>
			                        <td><input type="checkbox" name="meta_structure_preview_pages[' . $page_key . ']" value="1" ' . $page_preview_checked . '/></td>
			                        <td>' . $estimations['pages'][ $key ]['estimation'] . '</td>
			                    </tr>
				';

				$page_parent = $count;

				$page['modules'] = CoursePress_Helper_Utility::sort_on_object_key( $page['modules'], 'module_order' );

				foreach ( $page['modules'] as $module ) {
					$count += 1;
					$alt          = $count % 2 ? 'even' : 'odd';
					$module_title = ! empty( $module->post_title ) ? $module->post_title : __( 'Untitled Module', CoursePress::TD );

					$mod_view_checked    = isset( $visible_modules[ $module->ID ] ) ? CoursePress_Helper_Utility::checked( $visible_modules[ $module->ID ] ) : '';
					$mod_preview_checked = isset( $preview_modules[ $module->ID ] ) ? CoursePress_Helper_Utility::checked( $preview_modules[ $module->ID ] ) : '';

					$content .= '
								<tr class="module module-' . $module->ID . ' treegrid-' . $count . ' treegrid-parent-' . $page_parent . ' ' . $draft_class . ' ' . $alt . '">
			                        <td>' . $module_title . '</td>
			                        <td><input type="checkbox" name="meta_structure_visible_modules[' . $module->ID . ']" value="1" ' . $mod_view_checked . '/></td>
			                        <td><input type="checkbox" name="meta_structure_preview_modules[' . $module->ID . ']" value="1" ' . $mod_preview_checked . '/></td>
			                        <td>' . CoursePress_Model_Module::get_time_estimation( $module->ID, '1:00', true ) . '</td>
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
		$content .= '
				<div class="wide">
					<input type="button" class="button step prev step-2" value="' . esc_attr__( 'Previous', CoursePress::TD ) . '" />
					<input type="button" class="button step next step-2" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
					<input type="button" class="button step update step-2" value="' . esc_attr__( 'Update', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}

	private static function render_setup_step_3() {
		$course_id   = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_3', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 2 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-3">' . esc_html__( 'Step 3 – Instructors', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-3">
				<input type="hidden" name="meta_setup_step_3" value="saved" />
			';

		// Instructors
		$content .= '
				<div class="wide">
						<label for="course_name" class="">' .
		            esc_html__( 'Course Instructor(s)', CoursePress::TD ) . '
		                <p class="description">' . esc_html__( 'Select one or more instructor to facilitate this course', CoursePress::TD ) . '</p>
						</label>
						' . CoursePress_Helper_UI::get_user_dropdown( 'instructors', 'instructors', array(
				'placeholder' => __( 'Choose a Course Instructor...', CoursePress::TD ),
				'class'       => 'chosen-select medium'
			) ) . '
						<input type="button" class="button button-primary instructor-assign" value="' . esc_attr__( 'Assign', CoursePress::TD ) . '" />
				</div>
				<div class="instructors-info medium" id="instructors-info">
					<p>' . esc_html__( 'Assigned Instructors:', CoursePress::TD ) . '</p>
				';

		if ( 0 >= CoursePress_Helper_UI::course_instructors_avatars( $course_id, array(
				'remove_buttons' => true,
				'count'          => true
			) )
		) {
			$content .= '
					<div class="instructor-avatar-holder empty">
						<span class="instructor-name">' . esc_html__( 'Please Assign Instructor', CoursePress::TD ) . '</span>
					</div>
			';
		} else {
			$content .= CoursePress_Helper_UI::course_instructors_avatars( $course_id, array(), true );
		}

		$content .= '
				</div>';

		// Instructor Invite
		$content .= '
				<div class="wide">
					<hr />

					<label>' .
		            esc_html__( 'Invite New Instructor', CoursePress::TD ) . '
		                <p class="description">' . esc_html__( 'If the instructor can not be found in the list above, you will need to invite them via email.', CoursePress::TD ) . '</p>
					</label>
					<div class="instructor-invite">
						<label for="invite_instructor_first_name">' . esc_html__( 'First Name', CoursePress::TD ) . '</label>
						<input type="text" name="invite_instructor_first_name" placeholder="' . esc_attr__( 'First Name', CoursePress::TD ) . '"/>
						<label for="invite_instructor_last_name">' . esc_html__( 'Last Name', CoursePress::TD ) . '</label>
						<input type="text" name="invite_instructor_last_name" placeholder="' . esc_attr__( 'Last Name', CoursePress::TD ) . '"/>
						<label for="invite_instructor_email">' . esc_html__( 'E-Mail', CoursePress::TD ) . '</label>
						<input type="text" name="invite_instructor_email" placeholder="' . esc_attr__( 'instructor@email.com', CoursePress::TD ) . '"/>

						<div class="submit-message">
							<input class="button-primary" name="invite_instructor_trigger" id="invite-instructor-trigger" type="button" value="' . esc_attr__( 'Send Invite', CoursePress::TD ) . '">
						</div>
					</div>


				</div>
				';


		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_3', '', $course_id );

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step prev step-3" value="' . esc_attr__( 'Previous', CoursePress::TD ) . '" />
					<input type="button" class="button step next step-3" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
					<input type="button" class="button step update step-3" value="' . esc_attr__( 'Update', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}

	private static function render_setup_step_4() {
		$course_id   = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_4', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 3 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-4">' . esc_html__( 'Step 4 – Course Dates', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-4">
				<input type="hidden" name="meta_setup_step_4" value="saved" />
			';


		$open_ended_checked = CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'course_open_ended', true ) );
		$open_ended_course  = ! empty( $open_ended_checked );
		$content .= '
				<div class="wide course-dates">
					<label>' .
		            esc_html__( 'Course Availability', CoursePress::TD ) . '
					</label>
	                <p class="description">' . esc_html__( 'These are the dates that the course will be available to students', CoursePress::TD ) . '</p>
					<label class="checkbox medium">
						<input type="checkbox" name="meta_course_open_ended" ' . $open_ended_checked . ' />
						<span>' . esc_html__( 'This course has no end date', CoursePress::TD ) . '</span>
		            </label>
		            <div class="date-range">
						<div class="start-date">
							<label for="meta_course_start_date" class="start-date-label required">' . esc_html__( 'Start Date', CoursePress::TD ) . '</label>

							<div class="date">
								<input type="text" class="dateinput" name="meta_course_start_date" value="' . CoursePress_Model_Course::get_setting( $course_id, 'course_start_date', '' ) . '"/><i class="calendar"></i>
							</div>
						</div>
						<div class="end-date ' . ( $open_ended_course ? 'disabled' : '' ) . '">
							<label for="meta_course_end_date" class="end-date-label required">' . esc_html__( 'End Date', CoursePress::TD ) . '</label>
							<div class="date">
								<input type="text" class="dateinput" name="meta_course_end_date" value="' . CoursePress_Model_Course::get_setting( $course_id, 'course_end_date', '' ) . '" ' . ( $open_ended_course ? 'disabled="disabled"' : '' ) . ' />
							</div>
						</div>
					</div>
				</div>';

		$open_ended_checked = CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'enrollment_open_ended', true ) );
		$open_ended         = ! empty( $open_ended_checked );
		$content .= '
				<div class="wide enrollment-dates">
					<label>' .
		            esc_html__( 'Course Enrollment Dates', CoursePress::TD ) . '
					</label>
	                <p class="description">' . esc_html__( 'These are the dates that students will be able to enroll in a course.', CoursePress::TD ) . '</p>
					<label class="checkbox medium">
						<input type="checkbox" name="meta_enrollment_open_ended" ' . $open_ended_checked . ' />
						<span>' . esc_html__( 'Students can enroll at any time', CoursePress::TD ) . '</span>
		            </label>
		            <div class="date-range enrollment">
						<div class="start-date ' . ( $open_ended ? 'disabled' : '' ) . '">
							<label for="meta_enrollment_start_date" class="start-date-label required">' . esc_html__( 'Start Date', CoursePress::TD ) . '</label>

							<div class="date">
								<input type="text" class="dateinput" name="meta_enrollment_start_date" value="' . CoursePress_Model_Course::get_setting( $course_id, 'enrollment_start_date', '' ) . '"/><i class="calendar"></i>
							</div>
						</div>
						<div class="end-date ' . ( $open_ended ? 'disabled' : '' ) . '">
							<label for="meta_enrollment_end_date" class="end-date-label required">' . esc_html__( 'End Date', CoursePress::TD ) . '</label>
							<div class="date">
								<input type="text" class="dateinput" name="meta_enrollment_end_date" value="' . CoursePress_Model_Course::get_setting( $course_id, 'enrollment_end_date', '' ) . '" ' . ( $open_ended ? 'disabled="disabled"' : '' ) . ' />
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
		$content .= '
				<div class="wide">
					<input type="button" class="button step prev step-4" value="' . esc_attr__( 'Previous', CoursePress::TD ) . '" />
					<input type="button" class="button step next step-4" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
					<input type="button" class="button step update step-4" value="' . esc_attr__( 'Update', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}

	private static function render_setup_step_5() {
		$course_id   = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_5', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 4 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-5">' . esc_html__( 'Step 5 – Classes, Discussion & Workbook', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-5">
				<input type="hidden" name="meta_setup_step_5" value="saved" />
			';


		$limit_checked = CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'class_limited', false ) );
		$limited       = ! empty( $limit_checked );
		$content .= '
				<div class="wide class-size">
					<label>' .
		            esc_html__( 'Class Size', CoursePress::TD ) . '
					</label>
					<p class="description">' . esc_html__( 'Use this setting to set a limit for all classes. Uncheck for unlimited class size(s).', CoursePress::TD ) . '</p>
					<label class="narrow col">
						<input type="checkbox" name="meta_class_limited" ' . $limit_checked . ' />
						<span>' . esc_html__( 'Limit class size', CoursePress::TD ) . '</span>
		            </label>

		            <label class="num-students narrow col ' . ( $limited ? '' : 'disabled' ) . '">
		                ' . esc_html__( 'Number of students', CoursePress::TD ) . '
						<input type="text" class="spinners" name="meta_class_size" value="' . CoursePress_Model_Course::get_setting( $course_id, 'class_size', '' ) . '" ' . ( $limited ? '' : 'disabled="disabled"' ) . '/>
					</label>
				</div>';

		$content .= '
				<div class="wide">
					<label>' .
		            esc_html__( 'Course Discussion', CoursePress::TD ) . '
					</label>
					<p class="description">' . esc_html__( 'If checked, students can post questions and receive answers at a course level. A \'Discusssion\' menu item is added for the student to see ALL discussions occuring from all class members and instructors.', CoursePress::TD ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_allow_discussion" ' . CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'allow_discussion', false ) ) . ' />
						<span>' . esc_html__( 'Allow course discussion', CoursePress::TD ) . '</span>
		            </label>
				</div>';

		$content .= '
				<div class="wide">
					<label>' .
		            esc_html__( 'Student Workbook', CoursePress::TD ) . '
					</label>
					<p class="description">' . esc_html__( 'If checked, students can see their progress and grades.', CoursePress::TD ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_allow_workbook" ' . CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'allow_workbook', false ) ) . ' />
						<span>' . esc_html__( 'Show student workbook', CoursePress::TD ) . '</span>
		            </label>
				</div>';

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_5', '', $course_id );

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step prev step-5" value="' . esc_attr__( 'Previous', CoursePress::TD ) . '" />
					<input type="button" class="button step next step-5" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
					<input type="button" class="button step update step-5" value="' . esc_attr__( 'Update', CoursePress::TD ) . '" />
				</div>';

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

		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_6', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 ) === 5 ? $setup_class . ' setup_marker' : $setup_class;

		$payment_tagline = ! $disable_payment ? __( ' & Course Cost', CoursePress::TD ) : '';

		$content = '
			<div class="step-title step-6">' . esc_html( sprintf( __( 'Step 6 – Enrollment%s', CoursePress::TD ), $payment_tagline ) ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-6">
				<!-- depending on gateway setup, this could be save-attention -->
				<input type="hidden" name="meta_setup_step_6" value="saved" />
			';

		// Enrollment Options
		$enrollment_types = array(
			'manually' => __( 'Manually added only', CoursePress::TD ),
		);
		if ( CoursePress_Helper_Utility::users_can_register() ) {
			$enrollment_types = array_merge( $enrollment_types, array(
				'anyone'       => __( 'Anyone', CoursePress::TD ),
				'passcode'     => __( 'Anyone with a pass code', CoursePress::TD ),
				'prerequisite' => __( 'Anyone who completed the prerequisite course(s)', CoursePress::TD ),
			) );
		} else {
			$enrollment_types = array_merge( $enrollment_types, array(
				'registered'   => __( 'Registered users', CoursePress::TD ),
				'passcode'     => __( 'Registered users with a pass code', CoursePress::TD ),
				'prerequisite' => __( 'Registered users who completed the prerequisite course(s)', CoursePress::TD ),
			) );
		}
		$enrollment_types = apply_filters( 'coursepress_course_enrollment_types', $enrollment_types, $course_id );

		$content .= '
				<div class="wide">
					<label>' .
		            esc_html__( 'Enrollment Restrictions', CoursePress::TD ) . '
					</label>
					<p class="description">' . esc_html__( 'Select the limitations on accessing and enrolling in this course.', CoursePress::TD ) . '</p>
					<select name="meta_enrollment_type" class="chosen-select medium">';

		$selected = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_type', 'manually' );
		foreach ( $enrollment_types as $key => $type ) {
			$content .= '<option value="' . $key . '" ' . selected( $selected, $key, false ) . '>' . esc_html( $type ) . '</option>';
		}
		$content .= '
					</select>
				</div>';


		$class = 'prerequisite' === $selected ? '' : 'hidden';
		$content .= '
				<div class="wide enrollment-type-options prerequisite ' . $class . '">';

		$class_extra = is_rtl() ? 'chosen-rtl' : '';
		$content .= '
					<label>' .
		            esc_html__( 'Prerequisite Courses', CoursePress::TD ) .
		            '</label>
		            <p class="description">' . esc_html__( 'Select the courses a student needs to complete before enrolling in this course', CoursePress::TD ) . '</p>
		            <select name="meta_enrollment_prerequisite" class="medium chosen-select chosen-select-course ' . $class_extra . '" multiple="true" data-placeholder=" ">
			';

		$courses = CoursePress_Model_Instructor::get_accessable_courses( wp_get_current_user(), true );

		$saved_settings = CoursePress_Model_Course::get_setting( $course_id, 'enrollment_prerequisite', array() );
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
		            esc_html__( 'Course Passcode', CoursePress::TD ) .
		            '</label>
	            <p class="description">' . esc_html__( 'Enter the passcode required to access this course', CoursePress::TD ) . '</p>
	            <input type="text" name="meta_enrollment_passcode" value="' . CoursePress_Model_Course::get_setting( $course_id, 'enrollment_passcode', '' ) . '" />
			';

		$content .= '
				</div>
			';

		$paid_checked = CoursePress_Helper_Utility::checked( CoursePress_Model_Course::get_setting( $course_id, 'payment_paid_course', false ) );
		$is_paid      = ! empty( $paid_checked );

		if ( ! $disable_payment ) {
			$content .= '
				<hr class="separator" />
				<div class="wide">
					<label>' .
			            esc_html__( 'Course Payment', CoursePress::TD ) . '
					</label>
					<p class="description">' . esc_html__( 'Payment options for your course. Additional plugins are required and settings vary depending on the plugin.', CoursePress::TD ) . '</p>
					<label class="checkbox narrow">
						<input type="checkbox" name="meta_payment_paid_course" ' . $paid_checked . ' />
						<span>' . esc_html__( 'This is a paid course', CoursePress::TD ) . '</span>
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
								<a href="%s">Activate MarketPress</a>', CoursePress::TD ), esc_url_raw( admin_url( 'admin.php?page=coursepress_settings&tab=extensions' ) ) );
			} else {
				$install_message = __( '<p>Please contact your administrator to enable MarketPress for your site.</p>', CoursePress::TD );
			}

			if ( CoursePress_Model_Capabilities::is_pro() ) {
				$version_message = __( '<p>The full version of MarketPress has been bundled with CoursePress Pro.</p>', CoursePress::TD );
			} else {
				$version_message = __( '<p>You can use the free or premium version of MarketPress to sell your courses.</p>', CoursePress::TD );
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
					<p>Other supported plugins:  WooCommerce</p>
				</div>
			', CoursePress::TD ), $class, $version_message, $install_message ), $course_id );

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
		$content .= '
				<div class="wide">
					<input type="button" class="button step prev step-6" value="' . esc_attr__( 'Previous', CoursePress::TD ) . '" />
					<input type="button" class="button step finish step-6" value="' . esc_attr__( 'Finish', CoursePress::TD ) . '" />
					<input type="button" class="button step update step-6" value="' . esc_attr__( 'Update', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}


	private static function render_tab_units() {
		return CoursePress_View_Admin_Course_UnitBuilder::render();
	}

	private static function render_tab_students() {
		return CoursePress_View_Admin_Course_Students::render();
	}


	public static function get_tabs() {

		// Make it a filter so we can add more tabs easily
		self::$tabs = apply_filters( self::$slug . '_tabs', self::$tabs );

		self::$tabs['setup'] = array(
			'title'       => __( 'Course Setup', CoursePress::TD ),
			'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
			'order'       => 10,
			'buttons'     => 'none'
		);

		if ( 'edit' == self::_current_action() ) {

			$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
			$units     = CoursePress_Model_Course::get_unit_ids( $course_id, array( 'publish', 'draft' ) );

			self::$tabs['units'] = array(
				'title'       => sprintf( __( 'Units (%s)', CoursePress::TD ), count( $units ) ),
				'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
				'order'       => 20,
				'buttons'     => 'none'
			);

			self::$tabs['students'] = array(
				'title'       => sprintf( __( 'Students (%s)', CoursePress::TD ), CoursePress_Model_Course::count_students( $course_id ) ),
				'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
				'order'       => 30,
				'buttons'     => 'none'
			);

		}

		// Make sure that we have all the fields we need
		foreach ( self::$tabs as $key => $tab ) {
			self::$tabs[ $key ]['buttons'] = isset( $tab['buttons'] ) ? $tab['buttons'] : 'both';
			self::$tabs[ $key ]['class']   = isset( $tab['class'] ) ? $tab['class'] : '';
			self::$tabs[ $key ]['is_form'] = isset( $tab['is_form'] ) ? $tab['is_form'] : true;
			self::$tabs[ $key ]['order']   = isset( $tab['order'] ) ? $tab['order'] : 999; // Set default order to 999... bottom of the list
		}

		// Order the tabs
		self::$tabs = CoursePress_Helper_Utility::sort_on_key( self::$tabs, 'order' );

		return self::$tabs;
	}

	public static function update_course() {

		$data      = json_decode( file_get_contents( 'php://input' ) );
		$step_data = $data->data;
		$json_data = array();
		$success   = false;

		if ( empty( $data->action ) ) {
			$json_data['message'] = __( 'Course Update: No action.', CoursePress::TD );
			wp_send_json_error( $json_data );
		}

		$action              = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		switch ( $action ) {

			// Update Course
			case 'update_course':

				if ( isset( $step_data->step ) && wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {

					$step = (int) $step_data->step;

					$course_id = CoursePress_Model_Course::update( $step_data->course_id, $step_data );
					$json_data['course_id'] = $course_id;

					$next_step              = (int) $data->next_step;
					$json_data['last_step'] = $step;
					$json_data['next_step'] = $next_step;
					$json_data['redirect'] = $data->data->is_finished;
					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}

				break;

			case 'toggle_course_status':

				$course_id = $data->data->course_id;

				if ( wp_verify_nonce( $data->data->nonce, 'publish-course' ) ) {

					wp_update_post( array(
						'ID'          => $course_id,
						'post_status' => $data->data->status,
					) );

					$json_data['nonce'] = wp_create_nonce( 'publish-course' );
					$success            = true;

				}

				$json_data['course_id'] = $course_id;
				$json_data['state']     = $data->data->state;

				break;

			// Delete Instructor
			case 'delete_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Model_Course::remove_instructor( $data->data->course_id, $data->data->instructor_id );
					$json_data['instructor_id'] = $data->data->instructor_id;
					$json_data['course_id']     = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}

				break;

			// Add Instructor
			case 'add_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Model_Course::add_instructor( $data->data->course_id, $data->data->instructor_id );
					$user = get_userdata( $data->data->instructor_id );
					$json_data['instructor_id']   = $data->data->instructor_id;
					$json_data['instructor_name'] = $user->display_name;
					$json_data['course_id']       = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}

				break;

			// Invite Instructor
			case 'invite_instructor':

				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					$email_data               = CoursePress_Helper_Utility::object_to_array( $data->data );
					$response                 = CoursePress_Model_Instructor::send_invitation( $email_data );
					$json_data['message']     = $response['message'];
					$json_data['data']        = $data->data;
					$json_data['invite_code'] = $response['invite_code'];

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = $response['success'];
				}
				break;

			// Delete Invite
			case 'delete_instructor_invite':
				if ( wp_verify_nonce( $data->data->nonce, 'setup-course' ) ) {
					CoursePress_Model_Instructor::delete_invitation( $data->data->course_id, $data->data->invite_code );
					$json_data['course_id']   = $data->data->course_id;
					$json_data['invite_code'] = $data->data->invite_code;

					$json_data['nonce'] = wp_create_nonce( 'setup-course' );
					$success            = true;
				}
				break;

			case 'enroll_student':

				if ( wp_verify_nonce( $data->data->nonce, 'add_student' ) ) {
					CoursePress_Model_Course::enroll_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id']  = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'add_student' );
					$success            = true;
				}
				break;

			case 'withdraw_student':
				if ( wp_verify_nonce( $data->data->nonce, 'withdraw-single-student' ) ) {
					CoursePress_Model_Course::withdraw_student( $data->data->student_id, $data->data->course_id );
					$json_data['student_id'] = $data->data->student_id;
					$json_data['course_id']  = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw-single-student' );
					$success            = true;
				}
				break;

			case 'withdraw_all_students':

				if ( wp_verify_nonce( $data->data->nonce, 'withdraw_all_students' ) ) {
					CoursePress_Model_Course::withdraw_all_students( $data->data->course_id );
					$json_data['course_id'] = $data->data->course_id;

					$json_data['nonce'] = wp_create_nonce( 'withdraw_all_students' );
					$success            = true;
				}
				break;

			case 'invite_student':

				if ( wp_verify_nonce( $data->data->nonce, 'invite_student' ) ) {
					$email_data = CoursePress_Helper_Utility::object_to_array( $data->data );
					$response   = CoursePress_Model_Course::send_invitation( $email_data );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'invite_student' );
					$success            = $response;
				}
				break;

			case 'bulk_actions':

				if ( wp_verify_nonce( $data->data->nonce, 'bulk_action_nonce' ) ) {

					$courses = $data->data->courses;
					$action = $data->data->the_action;

					foreach( $courses as $course_id ) {
						switch ( $action ) {

							case 'publish':
								wp_update_post( array(
									'ID'          => $course_id,
									'post_status' => 'publish',
								) );
								break;
							case 'unpublish':
								wp_update_post( array(
									'ID'          => $course_id,
									'post_status' => 'draft',
								) );
								break;
							case 'delete':
								wp_delete_post( $course_id );
								do_action( 'coursepress_course_deleted', $course_id );
								break;

						}
					}

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'bulk_action_nonce' );
					$success            = true;
				}
				break;

			case 'delete_course':

				if ( wp_verify_nonce( $data->data->nonce, 'delete_course' ) ) {

					$course_id = (int) $data->data->course_id;
					wp_delete_post( $course_id );
					do_action( 'coursepress_course_deleted', $course_id );

					$json_data['data'] = $data->data;

					$json_data['nonce'] = wp_create_nonce( 'delete_course' );
					$success            = true;
				}

				break;

			case 'duplicate_course':

				if ( wp_verify_nonce( $data->data->nonce, 'duplicate_course' ) ) {

					$course_id = (int) $data->data->course_id;

					$the_course = get_post( $course_id );

					if( ! empty( $the_course ) ) {

						$the_course = CoursePress_Helper_Utility::object_to_array( $the_course );
						$the_course['post_author'] = get_current_user_id();
						$the_course['comment_count'] = 0;
						$the_course['post_title'] = $the_course['post_title'] . ' ' . __( 'Copy', CoursePress::TD );
						$the_course['post_status'] = 'draft';
						unset( $the_course['ID'] );
						unset( $the_course['post_date'] );
						unset( $the_course['post_date_gmt'] );
						unset( $the_course['post_name'] );
						unset( $the_course['post_modified'] );
						unset( $the_course['post_modified_gmt'] );
						unset( $the_course['guid'] );

						$new_course_id = wp_insert_post( $the_course );

						$course_meta = get_post_meta( $course_id );
						foreach( $course_meta as $key => $value ) {
							if( ! preg_match( '/^_/', $key ) ) {
								update_post_meta( $new_course_id, $key, maybe_unserialize( $value[0] ) );
							}
						}

						$course_data = CoursePress_Helper_Utility::object_to_array( CoursePress_Model_Course::get_units_with_modules( $course_id, array(
							'publish',
							'draft'
						) ) );
						$course_data = CoursePress_Helper_Utility::sort_on_key( $course_data, 'order' );

						foreach ( $course_data as $unit_id => $unit_schema ) {

							$unit = $unit_schema['unit'];
							// Set Fields
							$unit['post_author'] = get_current_user_id();
							$unit['post_parent'] = $new_course_id;
							$unit['comment_count'] = 0;
							$unit['post_status'] = 'draft';
							unset( $unit['ID'] );
							unset( $unit['post_date'] );
							unset( $unit['post_date_gmt'] );
							unset( $unit['post_name'] );
							unset( $unit['post_modified'] );
							unset( $unit['post_modified_gmt'] );
							unset( $unit['guid'] );

							$new_unit_id = wp_insert_post( $unit );
							$unit_meta = get_post_meta( $unit_id );
							foreach( $unit_meta as $key => $value ) {
								if( ! preg_match( '/^_/', $key ) ) {
									update_post_meta( $new_unit_id, $key, maybe_unserialize( $value[0] ) );
								}
							}

							$pages = isset( $unit_schema['pages'] ) ? $unit_schema['pages'] : array();
							foreach( $pages as $page ) {

								$modules = $page['modules'];
								foreach( $modules as $module_id => $module ) {


									$module['post_author'] = get_current_user_id();
									$module['post_parent'] = $new_unit_id;
									$module['comment_count'] = 0;
									unset( $module['ID'] );
									unset( $module['post_date'] );
									unset( $module['post_date_gmt'] );
									unset( $module['post_name'] );
									unset( $module['post_modified'] );
									unset( $module['post_modified_gmt'] );
									unset( $module['guid'] );

									$new_module_id = wp_insert_post( $module );

									$module_meta = get_post_meta( $module_id );
									foreach( $module_meta as $key => $value ) {
										if( ! preg_match( '/^_/', $key ) ) {
											update_post_meta( $new_module_id, $key, maybe_unserialize( $value[0] ) );
										}
									}

								}

							}

						}

						$json_data['course_id'] = $new_course_id;

						do_action( 'coursepress_course_duplicated', $new_course_id, $course_id );

						$json_data['data'] = $data->data;

						$json_data['nonce'] = wp_create_nonce( 'duplicate_course' );
						$success            = true;
					}
				}

				break;

		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}


	}

}