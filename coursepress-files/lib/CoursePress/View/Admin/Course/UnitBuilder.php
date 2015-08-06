<?php

class CoursePress_View_Admin_Course_UnitBuilder {

	private static $options = array();

	public static function render() {

		//add_action( 'wp_ajax_get_units', array( __CLASS__, 'handle_fetch' ) );

		$content = '';

		foreach ( self::view_templates() as $key => $template ) {
			$content .= $template;
		}

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
				<div class="unit-detail" data-cid="<%- unit_cid %>">
					<h3><i class="fa fa-cog"></i>' . esc_html__( 'Unit Settings', CoursePress::TD ) . '<div class="unit-state">' .
                      CoursePress_Helper_UI::toggle_switch( 'unit-live-toggle', 'unit-live-toggle', array(
						'left' => __( 'Draft', CoursePress::TD ),
                        'right' => __( 'Live', CoursePress::TD )
                      )) . '</h3>
					<label for="unit_name">Unit Title</label>
					<input id="unit_name" class="wide" type="text" value="<%= unit_title %>" name="post_title" spellcheck="true">
					<label for="unit_availability">Unit Availability</label>
					<input id="dp1437965877649" class="dateinput hasDatepicker" type="text" value="<%= unit_availability %>" name="meta_unit_availability" spellcheck="true">
					<label><input id="force_current_unit_completion" type="checkbox" value="on" name="meta_force_current_unit_completion" <%= unit_force_completion_checked %>><span>User needs to <strong><em>answer</em></strong>all mandatory assessments and view all pages in order to access the next unit</span></label>
					<label><input id="force_current_unit_successful_completion" type="checkbox" value="on" name="meta_force_current_unit_successful_completion" <%= unit_force_successful_completion_checked %>><span>User also needs to <strong><em>pass</em></strong>all mandatory assessments</span></label>
				</div>
				<div class="unit-buttons"><div class="button unit-save-button">' . __( 'Save', CoursePress::TD ) . '</div><div class="button unit-delete-button"><i class="fa fa-trash-o"></i> ' . __( 'Delete Unit', CoursePress::TD ) . '</div></div>
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
					<div class="section unit-builder-footer"></div>
				</script>
			',
			'unit_builder_content_pager'       => '
				<script type="text/template" id="unit-builder-pager-template">
					<label>' . esc_html__( 'Unit Page(s)', CoursePress::TD ) . '</label>
					<ul>
			            <% for ( var i = 1; i <= unit_page_count; i++ ) { %>
			                <li data-page="<%- i %>">
			                    <%- i %>
			                </li>
			            <% }; %>
			            <li>+</li>
			        </ul>
				</script>
			',
			'unit_builder_content_pager_info'  => '
				<script type="text/template" id="unit-builder-pager-info-template">
					<div class="page-info-holder">
					<label class="bigger">' . esc_html__( 'Page Label', CoursePress::TD ) . '</label>
					<p class="description">' . esc_html__( 'The label will be displayed on the Course Overview and Unit page', CoursePress::TD ) . '</p>
					<input type="text" value="<%= page_label_text %>" name="page_title" class="wide" />
					<label><input type="checkbox" value="on" name="show_page_title" <%= page_label_checked %> /><span>' . esc_html__( 'Show page label on unit', CoursePress::TD ) . '</span></label>
					</div>
				</script>
			',
			'unit_builder_modules'                 => '
				<script type="text/template" id="unit-builder-modules-template">
				  Modules! This template wont be used... its just here for testing.
				</script>
			',
			'unit_builder_footer'              => '
				<script type="text/template" id="unit-builder-footer-template">
				<div class="button unit-save-button">' . __( 'Save', CoursePress::TD ) . '</div>' .
                  CoursePress_Helper_UI::toggle_switch( 'unit-live-toggle-2', 'unit-live-toggle-2', array(
                      'left' => __( 'Draft', CoursePress::TD ),
                      'right' => __( 'Live', CoursePress::TD )
                  ))
                  . '</script>',
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
					$meta  = get_post_meta( $unit->ID );
					foreach( $meta as $key => $value ) {
						$meta[ $key ] = is_array( $value )  ? maybe_unserialize( $value[0] ) : $value;
					}

					$unit->meta = $meta;
					$json_data[] = $unit;
				}

				break;

			case 'modules':
				$modules = CoursePress_Model_Course::get_unit_modules( (int) $_REQUEST['unit_id'], 'any', false, false, array( 'page' => (int) $_REQUEST['page'] ) );

				foreach ( $modules as $module ) {
					$meta = get_post_meta( $module->ID );
					foreach( $meta as $key => $value ) {
						$meta[ $key ] = is_array( $value )  ? maybe_unserialize( $value[0] ) : $value;
					}

					$module->meta = $meta;
					$json_data[]  = $module;
				}

				break;

			case 'units_update':

				$data      = json_decode( file_get_contents( 'php://input' ) );
				$data      = CoursePress_Helper_Utility::object_to_array( $data );

				foreach( $data as $unit ) {

					unset( $unit['post_modified'] );
					unset( $unit['post_modified_gmt'] );

					if( 0 === (int) $unit['ID'] ) {
						unset( $unit['ID'] );
					}

					$update = isset( $unit['flag'] ) && 'dirty' === $unit['flag'];
					unset( $unit['flag'] );

					if( $update ) {

						$meta = ! empty( $unit['meta'] ) ? $unit['meta'] : array();
						unset( $unit['meta'] );

						$id = wp_insert_post( $unit );

						foreach( $meta as $key => $value ) {
							update_post_meta( $id, $key, $value );
						}

					}

				}


				break;

			case 'modules_update':

				$data      = json_decode( file_get_contents( 'php://input' ) );
				$data      = CoursePress_Helper_Utility::object_to_array( $data );

				$unit_id = (int) $_REQUEST['unit_id'];

				$modules = array();

				foreach( $data as $module ) {
					if( empty( $module ) ) {
						continue;
					}
					unset( $module['post_modified'] );
					unset( $module['post_modified_gmt'] );

					$module_id = $module['ID'];
					if( 0 === (int) $module['ID'] ) {
						unset( $module['ID'] );
					}

					$update = isset( $module['flag'] ) && 'dirty' === $module['flag'];
					unset( $module['flag'] );

					$module['post_type'] = 'module';
					$module['post_parent'] = $unit_id;
					if( $update ) {

						$meta = ! empty( $module['meta'] ) ? $module['meta'] : array();
						unset( $module['meta'] );

						$id = wp_insert_post( $module );
						$modules[] = $id;
						foreach( $meta as $key => $value ) {
							update_post_meta( $id, $key, $value );
						}

					} else {
						if( ! empty( $module_id ) ) {
							$modules[] = $module_id;
						}
					}

				}

				// Check for removed units and delete if needed
				$saved_modules = CoursePress_Model_Course::get_unit_modules( (int) $_REQUEST['unit_id'], 'any', true, false, array( 'page' => (int) $_REQUEST['page'] ) );
				foreach( $saved_modules as $mod_id ) {
					if( ! in_array( $mod_id, $modules ) ) {
						wp_delete_post( $mod_id );
					}
				}

				break;

			case 'unit_update':

				$data      = json_decode( file_get_contents( 'php://input' ) );
				$data = CoursePress_Helper_Utility::object_to_array( $data );


				break;

			case 'module_add':
				$data      = json_decode( file_get_contents( 'php://input' ) );
				$data = CoursePress_Helper_Utility::object_to_array( $data );

				$new_module = false;
				$meta = ! empty( $data['meta'] ) ? $data['meta'] : array();
				unset( $data['meta'] );

				if( (int) $data['ID'] === 0 ) {
					$new_module = true;
					unset( $data['ID'] );
				}

				$data['ping_status'] = 'closed';
				$data['comment_status'] = 'closed';
				$data['post_parent'] = (int) $_REQUEST['unit_id'];
				$data['post_type'] = 'module';
				$data['post_status'] = 'publish';

				$id = wp_insert_post( $data );

				foreach( $meta as $key => $value ) {
					update_post_meta( $id, $key, $value );
				}

				break;
		}

		if ( ! empty( $json_data ) ) {
			CoursePress_Helper_Utility::send_bb_json( $json_data );
		}


	}


}