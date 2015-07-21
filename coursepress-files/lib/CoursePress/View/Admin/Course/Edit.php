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

		add_action( 'wp_ajax_update_course', array( __CLASS__, 'update_course' ) );
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
			//error_log( 'MEthod: '. $method);
			$content = call_user_func( __CLASS__ . '::' . $method );
		}

		unset( $_GET['_wpnonce'] );
		$hidden_args = $_GET;

		$content = '<div class="coursepress_settings_wrapper">' .
		           '<h3>' . esc_html( CoursePress_Core::$name ) . ' : ' . esc_html( self::$menu_title ) . '</h3>
		            <hr />' .
		           CoursePress_Helper_Tabs::render_tabs( $tabs, $content, $_GET, self::$slug, $tab, false, 'horizontal', '<div style="width:100%; display:block; background:blue; color: white; padding:20px;">TODO: Add publish toggle here.</div>' ) .
		           '</div>';

		//echo CoursePress_Helper_Tabs::render_tabs( $tabs, 'MOO ' . $tab, $_GET, self::$slug, $tab, false, 'horizontal', '<div style="width:100%; display:block; background:blue; color: white; padding:20px;">Testing</div>' );
		//error_log( print_r( CoursePress_Helper_Settings::get_page_references(), true ) );

		echo $content;
	}

	private static function render_tab_setup() {

		$content = '
        <div class="step-container">
			<div id="course-setup-steps">
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

		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		//CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 0 );
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_1', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 1 ) === 0 ? $setup_class . ' setup_marker' : $setup_class;
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
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 1 ) === 2 ? $setup_class . ' setup_marker' : $setup_class;
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

			$unit_view_checked    = CoursePress_Helper_Utility::checked( $visible_units[ $unit['unit']->ID ] );
			$unit_preview_checked = CoursePress_Helper_Utility::checked( $preview_units[ $unit['unit']->ID ] );
			$content .= '
								<tr class="unit unit-' . $unit['unit']->ID . ' treegrid-' . $count . ' ' . $draft_class . ' ' . $alt . '">
			                        <td>' . $status . $unit['unit']->post_title . '</td>
			                        <td><input type="checkbox" name="meta_structure_visible_units[' . $unit['unit']->ID . ']" value="1" ' . $unit_view_checked . '/></td>
			                        <td><input type="checkbox" name="meta_structure_preview_units[' . $unit['unit']->ID . ']" value="1" ' . $unit_preview_checked . '/></td>
			                        <td>' . $estimations['unit']['estimation'] . '</td>
			                    </tr>
			';

			$unit_parent = $count;
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
					<input type="button" class="button step next step-2" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
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
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 1 ) === 3 ? $setup_class . ' setup_marker' : $setup_class;
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
						<input type="text" name="invite_instructor_last_name" placeholder="' . esc_attr__( 'Last Name', CoursePress::TD ). '"/>
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
					<input type="button" class="button step next step-3" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
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
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 1 ) === 4 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-4">' . esc_html__( 'Step 4 – Course Dates', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-4">
				<input type="hidden" name="meta_setup_step_4" value="saved" />
			';


		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_4', '', $course_id );

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-4" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
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
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 1 ) === 5 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-5">' . esc_html__( 'Step 5 – Classes, Discussion & Workbook', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-5">
				<input type="hidden" name="meta_setup_step_5" value="saved" />
			';


		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_5', '', $course_id );

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-5" value="' . esc_attr__( 'Next', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}

	private static function render_setup_step_6() {
		$course_id   = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
		$setup_class = CoursePress_Model_Course::get_setting( $course_id, 'setup_step_6', '' );
		$setup_class = (int) CoursePress_Model_Course::get_setting( $course_id, 'setup_marker', 1 ) === 6 ? $setup_class . ' setup_marker' : $setup_class;
		$content     = '
			<div class="step-title step-6">' . esc_html__( 'Step 6 – Enrollment & Course Cost', CoursePress::TD ) . '
				<div class="status ' . $setup_class . '"></div>
			</div>
			<div class="step-content step-6">
				<!-- depending on gateway setup, this could be save-attention -->
				<input type="hidden" name="meta_setup_step_6" value="saved" />
			';

		/**
		 * Add additional fields.
		 *
		 * Names must begin with meta_ to allow it to be automatically added to the course settings
		 */
		$content .= apply_filters( 'coursepress_course_setup_step_6', '', $course_id );

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-6" value="' . esc_attr__( 'Finish', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';

		return $content;
	}


	private static function render_tab_units() {

		$content = '';

		$units = CoursePress_Model_Course::get_unit_ids( (int) $_GET['id'] );
		//error_log( print_r( CoursePress_Model_Course::get_unit_ids( (int) $_GET['id'] ), true ) );

		$first_unit = ! empty( $units ) && is_array( $units ) ? $units[0] : false;

		$unit_id = isset( $_REQUEST['unit_id'] ) ? (int) $_REQUEST['unit_id'] : $first_unit;

		$titles = array();
		foreach ( $units as $unit ) {
			$titles[ $unit ] = get_the_title( $unit );
		}

		$unit = get_post( $unit_id );

		$content = var_dump( $titles );

		$content .= $unit->post_content;

		return $content;
	}

	private static function render_tab_students() {
		return "Students";
	}


	public static function get_tabs() {

		// Make it a filter so we can add more tabs easily
		self::$tabs = apply_filters( self::$slug . '_tabs', self::$tabs );

		//error_log( self::$slug );

		self::$tabs['setup'] = array(
			'title'       => __( 'Course Setup', CoursePress::TD ),
			'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
			'order'       => 10,
			'buttons'     => 'none'
		);

		if ( 'edit' == self::_current_action() ) {

			self::$tabs['units'] = array(
				'title'       => __( 'Units', CoursePress::TD ),
				'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
				'order'       => 20,
				'buttons'     => 'none'
			);

			self::$tabs['students'] = array(
				'title'       => __( 'Students', CoursePress::TD ),
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

		$action = sanitize_text_field( $data->action );
		$json_data['action'] = $action;

		switch ( $action ) {

			// Update Course
			case 'update_course':

				if ( isset( $step_data->step ) ) {

					$step = (int) $step_data->step;

					$res = CoursePress_Model_Course::update( $step_data->course_id, $step_data );

					$next_step = $step + 1;
					$next_step = 6 < $next_step ? 6 : $next_step;

					$json_data['last_step'] = $step;
					$json_data['next_step'] = $next_step;

					$success = true;
				}

				break;

			// Delete Instructor
			case 'delete_instructor':
				CoursePress_Model_Course::remove_instructor( $data->data->course_id, $data->data->instructor_id );
				$json_data['instructor_id'] = $data->data->instructor_id;
				$json_data['course_id'] = $data->data->course_id;
				$success = true;
				break;

			// Add Instructor
			case 'add_instructor':
				CoursePress_Model_Course::add_instructor( $data->data->course_id, $data->data->instructor_id );
				$json_data['instructor_id'] = $data->data->instructor_id;
				$json_data['instructor_name'] = $data->data->instructor_name;
				$json_data['course_id'] = $data->data->course_id;
				$success = true;
				break;

			// Invite Instructor
			case 'invite_instructor':
				$email_data = CoursePress_Helper_Utility::object_to_array( $data->data );
				$response = CoursePress_Model_Instructor::send_invitation( $email_data );
				$json_data['message'] = $response['message'];
				$json_data['data'] = $data->data;
				$json_data['invite_code'] = $response['invite_code'];
				$success = $response['success'];
				break;

			// Delete Invite
			case 'delete_instructor_invite':
				CoursePress_Model_Instructor::delete_invitation( $data->data->course_id, $data->data->invite_code );
				$json_data['course_id'] = $data->data->course_id;
				$json_data['invite_code'] = $data->data->invite_code;
				$success = true;
				break;

		}

		if ( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}


	}

}