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

		self::$action = isset( $_GET['action'] ) && in_array( $_GET['action'], self::$allowed_actions) ? sanitize_text_field( $_GET['action'] ) : 'new';

		self::$title      = __( 'Edit Course/CoursePress', CoursePress::TD );

		switch( self::$action ) {
			case 'new':
				self::$menu_title = __( 'New Course', CoursePress::TD );
				break;
			case 'edit':
				if( isset( $_GET['id'] ) && 0 !== (int) $_GET['id'] ) {
					self::$current_course = get_post( (int) $_GET['id'] );
				}
				self::$menu_title = __( 'Edit Course', CoursePress::TD );
				break;
		}

		add_filter( 'coursepress_admin_valid_pages', array( __CLASS__, 'add_valid' ) );
		add_filter( 'coursepress_admin_pages', array( __CLASS__, 'add_page' ) );
		add_action( 'coursepress_admin_' . self::$slug, array( __CLASS__, 'process_form' ));
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

		$tabs = self::get_tabs();
		$tab_keys  = array_keys( $tabs );
		$first_tab = ! empty( $tab_keys ) ? $tab_keys[0] : '';
		$tab     = empty( $_GET['tab'] ) ? $first_tab : ( in_array( $_GET['tab'], $tab_keys ) ? sanitize_text_field( $_GET['tab'] ) : '' );

		$method = preg_replace( '/\_$/', '', 'render_tab_' . $tab );

		if ( method_exists( __CLASS__, $method ) ) {
			error_log( 'MEthod: '. $method);
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
		$content = '
			<div class="step-title step-1">' . esc_html__( 'Step 1 – Course Overview', CoursePress::TD ) . '
				<div class="status"></div>
			</div>
			<div class="step-content step-1">';

		// Course ID
		$course_id = ! empty( self::$current_course ) ? self::$current_course->ID : 0;
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
		$editor_name	 = "course_excerpt";
		$editor_id		 = "courseExcerpt";
		$editor_content = ! empty( self::$current_course ) ? htmlspecialchars_decode( self::$current_course->post_excerpt ) : '';
		//$editor_content	 = htmlspecialchars_decode( ( isset( $_GET[ 'course_id' ] ) ? $course_details->post_excerpt : '' ) );
		//$editor_content = "whatup!";

		$args = array(
			"textarea_name"	 => $editor_name,
			"editor_class"	 => 'cp-editor cp-course-overview',
			"textarea_rows"	 => 4,
			"media_buttons"	 => false,
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

		//$meta = get_post_meta( self::$current_course->ID );
		// Listing Image
		$content .= CoursePress_Helper_UI::browse_media_field(
			'listing_image',
			'listing_image',
			array(
				'placeholder' => __( 'Add Image URL or Browse for Image', CoursePress::TD ),
				'title' => __( 'Listing Image', CoursePress::TD ),
				'value' => CoursePress_Model_Course::get_listing_image( $course_id )
			)
		);

		// Course Category
		$category = CoursePress_Model_Course::get_post_category_name( true );
		$cpt = CoursePress_Model_Course::get_post_type_name( true );
		$url = 'edit-tags.php?taxonomy=' . $category . '&post_type=' . $cpt;
		$terms = CoursePress_Model_Course::get_terms();
		$course_terms_array = CoursePress_Model_Course::get_course_terms( (int) $_GET['id'], true );

		$class_extra = is_rtl() ? 'chosen-rtl' : '';

		$content .= '
				<div class="wide">
					<label for="courseExcerpt" class="medium">' .
		            esc_html__( 'Course Category', CoursePress::TD ) . '
		                <a class="context-link" href="' . esc_url_raw( $url ) . '">' . esc_html__( 'Manage Categories', CoursePress::TD ) . '</a>
					</label>
					<select name="course_categories" class="medium chosen-select chosen-select-course ' . $class_extra . '" multiple="true">';

		foreach ( $terms as $terms ) {
			$selected = in_array( $terms->term_id, $course_terms_array ) ? 'selected="selected"' : '';
			$content .= '<option value="' . $terms->term_id . '" ' . $selected . '>' . $terms->name . '</option>';
		}

		$content .= '
					</select>
				</div>';

		// Course Language
		$language = CoursePress_Model_Course::get_course_language( $course_id );
		$content .= '
				<div class="wide">
						<label for="course_language">' .
		            esc_html__( 'Course Language', CoursePress::TD ) . '
						</label>
						<input class="medium" type="text" name="course_language" id="course_language" value="' . $language . '"/>
				</div>';

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-1" value="' .  esc_attr__( 'Next', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';
		return $content;
	}

	private static function render_setup_step_2() {
		$content = '
			<div class="step-title step-2">' . esc_html__( 'Step 2 – Course Description', CoursePress::TD ) . '
				<div class="status"></div>
			</div>
			<div class="step-content step-2">';

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-2" value="' .  esc_attr__( 'Next', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';
		return $content;
	}

	private static function render_setup_step_3() {
		$content = '
			<div class="step-title step-3">' . esc_html__( 'Step 3 – Instructors', CoursePress::TD ) . '
				<div class="status save-process"></div>
			</div>
			<div class="step-content step-3">';

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-3" value="' .  esc_attr__( 'Next', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';
		return $content;
	}

	private static function render_setup_step_4() {
		$content = '
			<div class="step-title step-4">' . esc_html__( 'Step 4 – Course Dates', CoursePress::TD ) . '
				<div class="status save-attention"></div>
			</div>
			<div class="step-content step-4">';

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-4" value="' .  esc_attr__( 'Next', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';
		return $content;
	}

	private static function render_setup_step_5() {
		$content = '
			<div class="step-title step-5">' . esc_html__( 'Step 5 – Classes, Discussion & Workbook', CoursePress::TD ) . '
				<div class="status save-error"></div>
			</div>
			<div class="step-content step-5">';

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-5" value="' .  esc_attr__( 'Next', CoursePress::TD ) . '" />
				</div>';

		// End
		$content .= '
			</div>
		';
		return $content;
	}

	private static function render_setup_step_6() {
		$content = '
			<div class="step-title step-6">' . esc_html__( 'Step 6 – Enrollment & Course Cost', CoursePress::TD ) . '
				<div class="status saved"></div>
			</div>
			<div class="step-content step-6">';

		// Buttons
		$content .= '
				<div class="wide">
					<input type="button" class="button step next step-6" value="' .  esc_attr__( 'Finish', CoursePress::TD ) . '" />
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
		foreach( $units as $unit ) {
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
			'title' => __( 'Course Setup', CoursePress::TD ),
			'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
			'order' => 10,
			'buttons' => 'none'
		);

		if( 'edit' == self::_current_action() ) {

			self::$tabs['units'] = array(
				'title' => __( 'Units', CoursePress::TD ),
				'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
				'order' => 20,
				'buttons' => 'none'
			);

			self::$tabs['students'] = array(
				'title' => __( 'Students', CoursePress::TD ),
				'description' => __( 'Edit your course specific settings below.', CoursePress::TD ),
				'order' => 30,
				'buttons' => 'none'
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

		$data = json_decode( file_get_contents( 'php://input' ) );
		$data = $data->data;
		$json_data = array();
		$success = true;

		error_log( print_r( $data, true));

		if( isset( $data->step ) ) {

			$step = (int) $data->step;

			$res = CoursePress_Model_Course::update( $data->course_id, $data );

			$next_step = $step + 1;
			$next_step = 6 < $next_step ? 6 : $next_step;

			$json_data['last_step'] = $step;
			$json_data['next_step'] = $next_step;

		} else {
			$success = false;
		}


		if( $success ) {
			wp_send_json_success( $json_data );
		} else {
			wp_send_json_error( $json_data );
		}


	}

}