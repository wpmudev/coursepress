<?php

class CoursePress_View_Admin_Course_UnitBuilder {

	private static $options = array();

	public static function render() {

		//add_action( 'wp_ajax_get_units', array( __CLASS__, 'handle_fetch' ) );

		$content = '';

		foreach ( self::view_templates() as $key => $template ) {
			$content .= $template;
		}

		error_log( print_r( CoursePress_Helper_Utility::attachment_from_url( 'http://network1.dev/wp-content/uploads/2015/07/4wettenhall.jpg' ), true ) );

		$content .= '<div id="unit-builder"><div class="loading">' . esc_html__( 'Unit Builder is loading...', CoursePress::TD ) . '</div></div>';

		return $content;
	}

	public static function render__() {

		self::$options = array(
			'course_id' => isset( $_GET['id'] ) ? (int) $_GET['id'] : 0,
			'action'    => isset( $_GET['action'] ) ? $_GET['action'] : 'edit',
			'page'      => isset( $_GET['page'] ) ? $_GET['page'] : 'coursepress_course',
			'tab'       => isset( $_GET['tab'] ) ? $_GET['tab'] : 'units',
			//'unit_id' => isset( $_GET['unit_id'] ) ? (int) $_GET['unit_id'] : 0,
		);

		$admin_url = '';
		$first     = true;
		foreach ( self::$options as $key => $value ) {
			if ( $first ) {
				$first = false;
			} else {
				$admin_url .= '&amp;';
			}
			$admin_url .= $key . '=' . $value;
		}

		$content = '';

		// Get/Set the course ID
		$course_id = CoursePress_Model_Course::last_course_id();
		if ( empty( $course_id ) && isset( $_GET['id'] ) ) {
			$course_id = (int) $_GET['id'];
			CoursePress_Model_Course::set_last_course_id( $course_id );
		}

		$units = CoursePress_Model_Course::get_units( $course_id, 'any' );

		$tabs  = array();
		$first = true;
		foreach ( $units as $unit ) {

			$class = 'publish' === $unit->post_status ? 'unit-live' : 'unit-draft';
			$class = $first ? $class . ' active' : $class;

			$tabs[ $unit->ID ] = array(
				'title' => $unit->post_title,
				'order' => 10,
				'url'   => admin_url( 'admin.php?' . $admin_url . '&amp;unit_id' . $unit->ID ),
				'class' => 'coursepress-ub-tab ' . $class
			);

			$first = false;

		}

		//$tabs = array(
		//	'21' => array(
		//		'title' => __( 'Unit 1', CoursePress::TD ),
		//		'order' => 10,
		//		'url' => admin_url( 'admin.php?' . $admin_url . '&amp;unit_id=21' ),
		//		'class' => 'coursepress-ub-tab unit-draft'
		//	),
		//	'24' => array(
		//		'title' => __( 'Unit 2', CoursePress::TD ),
		//		'order' => 10,
		//		'url' => admin_url( 'admin.php?' . $admin_url . '&amp;unit_id=24' ),
		//		'class' => 'coursepress-ub-tab unit-live'
		//	),
		//);

		$content .= CoursePress_Helper_Tabs::render_tabs( $tabs, 'AA', array(), 'BBB', '21', false );


		//
		//$content = CoursePress_Helper_UI_Module::render();


		return $content;

	}


	public static function view_templates( $template = false ) {

		$templates = array(

			'unit_builder'                     => '
				<script type="text/template" id="unit-builder-template">
				  <div class="tab-container vertical unit-builder-container">
				  	<div class="tab-tabs unit-builder-tabs">
						<div id="sticky-wrapper" class="sticky-wrapper">
						</div>
					</div>
					<div class="tab-content tab-content-vertical unit-builder-content">
						<div class="section static unit-builder-header"></div>
						<div class="section static unit-builder-body"></div>
					</div>
				  </div>
				</script>
			',
			'unit_builder_tab'                 => '
				<script type="text/template" id="unit-builder-tab-template">
				  <li class="coursepress-ub-tab <%= unit_live_class %> <%= unit_active_class %>" data-tab="<%= unit_id %>"><a><%= unit_title %></a></li>
				</script>
			',
			'unit_builder_header'              => '
				<script type="text/template" id="unit-builder-header-template">
				<div class="unit-detail">
					<h3><i class="fa fa-cog"></i>' . esc_html__( 'Unit Settings', CoursePress::TD ) . '<div class="unit-state"></h3>
					<label for="unit_name">Unit Title</label>
					<input id="unit_name" class="wide" type="text" value="<%= unit_title %>" name="unit_name" spellcheck="true">
					<label for="unit_availability">Unit Availability</label>
					<input id="dp1437965877649" class="dateinput hasDatepicker" type="text" value="<%= unit_availability %>" name="unit_availability" spellcheck="true">
					<label><input id="force_current_unit_completion" type="checkbox" value="on" name="force_current_unit_completion" <%= unit_force_completion_checked %>><span>User needs to <strong><em>answer</em></strong>all mandatory assessments and view all pages in order to access the next unit</span></label>
					<label><input id="force_current_unit_successful_completion" type="checkbox" value="on" name="force_current_unit_successful_completion" <%= unit_force_successful_completion_checked %>><span>User also needs to <strong><em>pass</em></strong>all mandatory assessments</span></label>
				</div>
				<div class="unit-buttons">[SAVE] [PREVIEW] [DELETE] [TOGGLE]</div>
				</script>
			',
			'unit_builder_content_placeholder' => '
				<script type="text/template" id="unit-builder-content-placeholder">
				<div class="loading">
				' . esc_html__( 'Loading modules...', CoursePress::TD ) . '
				</div>
				</script>
			',
			'unit_builder_content'             => '
				<script type="text/template" id="unit-builder-content-template">
					<div class="section unit-builder-pager"></div>
					<div class="section unit-builder-pager-info"></div>
					<div class="section unit-builder-components"></div>
					<div class="section unit-builder-modules"></div>
				</script>
			',
			'unit_builder_content_pager'       => '
				<script type="text/template" id="unit-builder-pager-template">
					<label>' . esc_html__( 'Unit Page(s)', CoursePress::TD ) . '</label>
					<ul>
			            <% for ( var i = 0; i < unit_page_count; i++ ) { %>
			                <li data-page="<%- i %>">
			                    <%- (i+1) %>
			                </li>
			            <% }; %>
			            <li>+</li>
			        </ul>
				</script>
			',
			'unit_builder_content_pager_info'  => '
				<script type="text/template" id="unit-builder-pager-info-template">
					<label class="bigger">' . esc_html__( 'Page Label', CoursePress::TD ) . '</label>
					<p class="description">' . esc_html__( 'The label will be displayed on the Course Overview and Unit page', CoursePress::TD ) . '</p>
					<input type="text" value="<%= page_label_text %>" name="" class="wide" />
					<label><input type="checkbox" value="on" name="" <%= page_label_checked %> /><span>' . esc_html__( 'Show page label on unit', CoursePress::TD ) . '</span></label>
				</script>
			',
			'unit_builder_modules'                 => '
				<script type="text/template" id="unit-builder-modules-template">
				  Modules! This template wont be used... its just here for testing.
				</script>
			',

		);

		$templates['unit_builder_content_components']  = '
				<script type="text/template" id="unit-builder-components-template">
					<label class="bigger">' . esc_html__( 'Unit Modules', CoursePress::TD ) . '</label>
					<p class="description">' . esc_html__('Click to add module elements to the unit', CoursePress::TD) . '</p>';

		$ouputs = CoursePress_Helper_UI_Module::get_output_types();
		foreach( $ouputs as $key => $output ) {
			$templates['unit_builder_content_components'] .= '
			<div class="output-element module-' . $key . '" data-type="' . $key . '">
				<a></a>
				<span class="element-label">' . $output['title'] . '</span>
			</div>
           ';
		}

		$templates['unit_builder_content_components'] .= '<div class="elements-separator"></div>';

		$inputs = CoursePress_Helper_UI_Module::get_input_types();
		foreach( $inputs as $key => $input ) {
			$templates['unit_builder_content_components'] .= '
			<div class="input-element module-' . $key . '" data-type="' . $key . '">
				<a id="text_module" class="add-element"></a>
				<span class="element-label">' . $input['title'] . '</span>
			</div>
           ';
		}

		$templates['unit_builder_content_components']  .= '
				</script>
			';

		return $templates;

	}


	public static function unit_builder_ajax() {

		$json_data = array();

		switch ( $_REQUEST['task'] ) {

			case 'units':
				$units = CoursePress_Model_Course::get_units( $_REQUEST['course_id'], 'any' );

				foreach ( $units as $unit ) {
					$unit->meta  = get_post_meta( $unit->ID );
					$json_data[] = $unit;
				}

				break;

			case 'modules':
				$modules = CoursePress_Model_Course::get_unit_modules( (int) $_REQUEST['unit_id'], 'any' );

				foreach ( $modules as $module ) {
					$module->meta = get_post_meta( $module->ID );
					$json_data[]  = $module;
				}

				break;
		}

		if ( ! empty( $json_data ) ) {
			CoursePress_Helper_Utility::send_bb_json( $json_data );
		}


	}


}