<?php

class CoursePress_Helper_UI_Module {

	const OUTPUT_TEXT = 'text';
	const OUTPUT_IMAGE = 'image';
	const OUTPUT_VIDEO = 'video';
	const OUTPUT_AUDIO = 'audio';
	const OUTPUT_DOWNLOAD = 'download';
	const OUTPUT_ZIPPED_OBJECT = 'zipped';
	const OUTPUT_SECTION = 'section';
	const INPUT_MULTIPLE_CHOICE = 'input-checkbox';
	const INPUT_SINGLE_CHOICE = 'input-radio';
	const INPUT_SELECT_CHOICE = 'input-select';
	const INPUT_SHORT_TEXT = 'input-text';
	const INPUT_LONG_TEXT = 'input-textarea';
	const INPUT_UPLOAD = 'input-upload';
	const INPUT_ADVANCED = 'input-mixed';

	public static function render( $data = 'TODO' ) {
		$content = '';



		return $content;
	}


	public static function render_test( $data = 'TODO' ) {

		$types  = self::get_types();
		$labels = self::get_labels();



		$data = array(

			'id'             => 12345,
			'title'          => 'This is the title',
			'type'           => self::INPUT_SHORT_TEXT,
			'duration'       => '1:00',
			'show_title'     => 1,
			'mandatory'      => 1,
			'assessable'     => 1,
			'minimum_grade'  => 100,
			'allow_retries'  => 1,
			'retry_attempts' => 10,
			'content'        => 'Explain the meaning of life, the universe and everything else.',
			'order'          => 0,
			'components'     => array(
				//array(
				//	//'id' => '12345_1',
				//	'order' => 0,
				//	'items' => array(
				//		array(
				//			'text' => 'this is for later',
				//			'selected' => 0,
				//			'item_placeholder' => 'not always needed',
				//			'placeholder' => 'this goes on UI side',
				//			'button_primary' => 'Button 1',
				//			'button_secondary' => 'Button 2',
				//			'button_other' => 'Button 3',
				//			'answer' => 'Not always used',
				//			'keywords' => 'this, could, be, useful'
				//		) // item
				//	) // items
				//),
				array(
					//'id' => '12345_1',
					'order' => 0,
					'items' => array(
						array(
							'text'             => 'this is for later',
							'selected'         => 0,
							'item_placeholder' => 'not always needed',
							'placeholder'      => 'this goes on UI side',
							'button_primary'   => 'Button 1',
							'button_secondary' => 'Button 2',
							'button_other'     => 'Button 3',
							'answer'           => 'Not always used',
							'keywords'         => 'this, could, be, useful'
						) // item
					) // items
				),
				array(
					'order' => 1,
					'items' => array(
						array()

					)
				),

				// component
			) // components
		);

		//error_log( self::get_template( self::INPUT_SHORT_TEXT ) );
		$data = json_decode( self::get_template( self::INPUT_SHORT_TEXT ) );
		$data = CoursePress_Helper_Utility::object_to_array( $data );

		// If its not an accepted type there is no point trying to render it
		if ( ! in_array( $data['type'], array_keys( $types ) ) ) {
			return '';
		}

		$module_mode = $types[ $data['type'] ]['mode'];

		$content = '
			<div class="module-holder module-type-' . esc_attr( $data['type'] ) . ' mode-' . esc_attr( $module_mode ) . '" data-id="' . esc_attr( $data['id'] ) . '">
				<h3 class="module-title"><span class="label">' . esc_html( $data['title'] ) . '</span><span class="module-type">' . esc_html( $types[ $data['type'] ]['title'] ) . '</span></h3>';

		// Display the body of the module?
		if ( ( isset( $types[ $data['type'] ]['body'] ) && 'hidden' !== $types[ $data['type'] ]['body'] ) || ! isset( $types[ $data['type'] ]['body'] ) ) {
			$content .= '
				<div class="module-header">
					<label class="module-title"><span class="label">' . $labels['module_title'] . '</span>
		                <span class="description">' . $labels['module_title_desc'] . '</span>
						<input type="text" name="title" value="' . $data['title'] . '" />
					</label>
					<label class="module-duration"><span class="label">' . $labels['module_duration'] . '</span>
						<input type="text" name="meta_duration" value="' . $data['duration'] . '" />
					</label>';

			// Show Title
			$content .= '
					<label class="module-show-title">
						<input type="checkbox" name="meta_show_title" value="1" ' . checked( $data['show_title'], 1, false ) . ' />
						<span class="label">' . $labels['module_show_title'] . '</span>
						<span class="description">' . $labels['module_show_title_desc'] . '</span>
					</label>';

			// Only for user inputs
			if ( 'input' === $module_mode ) {

				// Mandatory
				$content .= '
					<label class="module-mandatory">
						<input type="checkbox" name="meta_mandatory" value="1" ' . checked( $data['mandatory'], 1, false ) . ' />
						<span class="label">' . $labels['module_mandatory'] . '</span>
						<span class="description">' . $labels['module_mandatory_desc'] . '</span>
					</label>';

				// Assessable
				$content .= '
					<label class="module-assessable">
						<input type="checkbox" name="meta_assessable" value="1" ' . checked( $data['assessable'], 1, false ) . ' />
						<span class="label">' . $labels['module_assessable'] . '</span>
						<span class="description">' . $labels['module_assessable_desc'] . '</span>
					</label>';

				// Minimum Grade
				$content .= '
					<label class="module-minimum_grade">
						<span class="label">' . $labels['module_minimum_grade'] . '</span>
						<input type="text" name="meta_minimum_grade" value="' . $data['minimum_grade'] . '" />
						<span class="description">' . $labels['module_minimum_grade_desc'] . '</span>
					</label>';

				// Allow Retries
				$content .= '
					<label class="module-allow-retries">
						<input type="checkbox" name="meta_allow_retries" value="1" ' . checked( $data['allow_retries'], 1, false ) . ' />
						<span class="label">' . $labels['module_allow_retries'] . '</span>
						<input type="text" name="meta_retry_attempts" value="' . $data['retry_attempts'] . '" />
						<span class="description">' . $labels['module_allow_retries_desc'] . '</span>
					</label>';

			}

			// Excerpt
			if ( ( isset( $types[ $data['type'] ]['excerpt'] ) && 'hidden' !== $types[ $data['type'] ]['excerpt'] ) || ! isset( $types[ $data['type'] ]['excerpt'] ) ) {
				$args = array(
					"textarea_name" => 'module_excerpt_' . $data['id'],
				);

				ob_start();
				wp_editor( $data['content'], 'moduleExcerpt' . $data['id'], $args );
				$content_editor = ob_get_clean();

				$content_label = 'input' === $module_mode ? $labels['module_question'] : $labels['module_content'];
				$content .= '
					<label class="module-excerpt">
						<span class="label">' . $content_label . '</span>
						' . $content_editor . '
					</label>';
			}

			// Now it gets tricky...
			$content .= '
				</div>
				<div class="module-components">
					' . self::render_components( $data ) . '
				</div>';

		}
		$content .= '
			</div>
		';

		return $content;
	}


	private static function render_components( $data ) {

		$types  = self::get_types();
		$labels = self::get_labels();

		$content = '';

		$module_mode = $types[ $data['type'] ]['mode'];

		$components = is_array( $data['components'] ) ? $data['components'] : array();

		//if ( 'input' === $module_mode && ! empty( $components ) ) {
		//	$content .= '
		//	<label class="module-question-label">
		//		<span class="label">' . $labels['module_answer'] . '</span>
		//		<span class="description">' . $labels['module_answer_desc'] . '</span>
		//	</label>';
		//}

		// Now deal with each component
		foreach ( $components as $key => $component ) {

			$component_id = isset( $component['id'] ) ? $component['id'] : 0;
			$content .= '
				<div class="module-component module-component-' . $key . '">
					<label data-key="label">
						<span class="label">' . $component['label'] . '</span>
						<span class="description">' . $component['description'] . '</span>
				';
			foreach( (array) $component['items'] as $idx => $item ) {

				switch( $item['type'] ) {

					case 'text-input':
						$attr = isset( $item['name'] ) ? ' name="' . $item['name'] .'"' : '';
						$attr .= isset( $item['class'] ) ? ' class="' . $item['class'] .'"' : '';
						$content .= '<input type="text"' . $attr . ' />';
						break;

					case 'text':
						$attr = isset( $item['name'] ) ? ' name="' . $item['name'] .'"' : '';
						$attr .= isset( $item['class'] ) ? ' class="' . $item['class'] .'"' : '';
						$text = isset( $item['text'] ) ? $item['text']  : '';
						$content .= '<span' . $attr . '>' . $text  . '</span>';
						break;

				}

			}

			$content .= '
					</label>
				</div>
			';


		}


		return $content;
	}


	public static function get_types() {

		$input_types  = self::get_input_types();
		$output_types = self::get_output_type();

		return apply_filters( 'coursepress_module_types', CoursePress_Helper_Utility::merge_distinct( $input_types, $output_types ) );

	}

	public static function get_output_types() {

		$types = array(
			self::OUTPUT_TEXT          => array(
				'title' => __( 'Text', CoursePress::TD ),
				'mode'  => 'output',
				'icon'  => 'default',
			),
			self::OUTPUT_IMAGE         => array(
				'title'   => __( 'Image', CoursePress::TD ),
				'mode'    => 'output',
				'excerpt' => 'hidden',
				'icon'  => 'default',
			),
			self::OUTPUT_VIDEO         => array(
				'title'   => __( 'Video', CoursePress::TD ),
				'mode'    => 'output',
				'excerpt' => 'hidden',
				'icon'  => 'default',
			),
			self::OUTPUT_AUDIO         => array(
				'title'   => __( 'Audio', CoursePress::TD ),
				'mode'    => 'output',
				'excerpt' => 'hidden',
				'icon'  => 'default',
			),
			self::OUTPUT_DOWNLOAD      => array(
				'title'   => __( 'File Download', CoursePress::TD ),
				'mode'    => 'output',
				'excerpt' => 'hidden',
				'icon'  => 'default',
			),
			self::OUTPUT_ZIPPED_OBJECT => array(
				'title'   => __( 'Zipped Object', CoursePress::TD ),
				'mode'    => 'output',
				'excerpt' => 'hidden',
				'icon'  => 'default',
			),
			self::OUTPUT_SECTION       => array(
				'title'   => __( 'Section Break', CoursePress::TD ),
				'mode'    => 'output',
				'excerpt' => 'hidden',
				'body'    => 'hidden',
				'icon'  => 'default',
			),
		);

		return apply_filters( 'coursepress_module_output_types', $types );
	}

	public static function get_input_types() {

		$types = array(
			self::INPUT_MULTIPLE_CHOICE => array(
				'title' => __( 'Multiple Choice', CoursePress::TD ),
				'mode'  => 'input',
				'icon'  => 'default',
			),
			self::INPUT_SINGLE_CHOICE   => array(
				'title' => __( 'Single Choice', CoursePress::TD ),
				'mode'  => 'input',
				'icon'  => 'default',
			),
			self::INPUT_SELECT_CHOICE   => array(
				'title' => __( 'Selectable', CoursePress::TD ),
				'mode'  => 'input',
				'icon'  => 'default',
			),
			self::INPUT_SHORT_TEXT      => array(
				'title' => __( 'Short Answer', CoursePress::TD ),
				'mode'  => 'input',
				'icon'  => 'default',
			),
			self::INPUT_LONG_TEXT       => array(
				'title' => __( 'Long Answer', CoursePress::TD ),
				'mode'  => 'input',
				'icon'  => 'default',
			),
			self::INPUT_UPLOAD          => array(
				'title' => __( 'File Upload', CoursePress::TD ),
				'mode'  => 'input',
				'icon'  => 'default',
			),
			//self::INPUT_ADVANCED        => array(
			//	'title' => __( 'Advanced Action', CoursePress::TD ),
			//	'mode'  => 'input',
			//	'icon'  => 'default',
			//),
		);

		return apply_filters( 'coursepress_module_input_types', $types );
	}

	// Could've done this inline, but this is needed for JS translation
	public static function get_labels() {

		return apply_filters( 'coursepress_module_labels', array(
			'module_title'              => __( 'Title', CoursePress::TD ),
			'module_title_desc'         => __( 'The title is used to identify this module element and is useful for assessment.', CoursePress::TD ),
			'module_duration'           => __( 'Duration ([hh:]mm:ss)', CoursePress::TD ),
			'module_show_title'         => __( 'Show Title', CoursePress::TD ),
			'module_show_title_desc'    => __( 'Show title in unit view', CoursePress::TD ),
			'module_mandatory'          => __( 'Mandatory', CoursePress::TD ),
			'module_mandatory_desc'     => __( 'A response is required', CoursePress::TD ),
			'module_assessable'         => __( 'Assessable', CoursePress::TD ),
			'module_assessable_desc'    => __( 'This is a gradable item', CoursePress::TD ),
			'module_minimum_grade'      => __( 'Minimum', CoursePress::TD ),
			'module_minimum_grade_desc' => __( 'Minimum grade (%) required to pass', CoursePress::TD ),
			'module_minimum_grade'      => __( 'Minimum', CoursePress::TD ),
			'module_allow_retries'      => __( 'Allow Retries', CoursePress::TD ),
			'module_allow_retries_desc' => __( 'Allow and set amount of retries (0 unlimited)', CoursePress::TD ),
			'module_question'           => __( 'Question/Task', CoursePress::TD ),
			'module_content'            => __( 'Content', CoursePress::TD ),
			'module_answer'             => __( 'Answer', CoursePress::TD ),
			'module_answer_desc'        => __( 'Set the correct answer', CoursePress::TD ),
			'module_answer_add_new'     => __( 'Add', CoursePress::TD ),
			'module_delete'             => __( 'Delete', CoursePress::TD ),

		) );

	}

	public static function get_template( $component = false ) {

		$components = array(
			self::OUTPUT_TEXT           => '

			',
			self::OUTPUT_IMAGE          => '

			',
			self::OUTPUT_VIDEO          => '

			',
			self::OUTPUT_AUDIO          => '

			',
			self::OUTPUT_DOWNLOAD       => '

			',
			self::OUTPUT_ZIPPED_OBJECT  => '

			',
			self::OUTPUT_SECTION        => '

			',
			self::INPUT_MULTIPLE_CHOICE => '

			',
			self::INPUT_SINGLE_CHOICE   => '

			',
			self::INPUT_SELECT_CHOICE   => '

			',
			self::INPUT_SHORT_TEXT      => '
				{
					"id": "0",
					"title": "' . __( 'Untitled', CoursePress::TD ) . '",
					"duration": "1:00",
					"type": "' . self::INPUT_SHORT_TEXT . '",
					"show_title": "1",
					"mandatory": "0",
					"assessable": "0",
					"minimum_grade": "100",
					"allow_retries": "1",
					"retry_attempts": "0",
					"content": "",
					"order": "0",
					"components": [
						{
							"label": "' . __('Placeholder Text', CoursePress::TD ) . '",
							"description": "' . __('Placeholder text to put inside the textbox (additional information)', CoursePress::TD ) . '",
							"items": [
								{
									"type": "text-input",
									"class": "component-placeholder-text",
									"name": "meta_component[placeholder_text]"
								}
							]
						}
					]
				}
			',
			self::INPUT_LONG_TEXT => '

			',
			self::INPUT_UPLOAD => '
			',
			self::INPUT_ADVANCED => '

			',
		);

		if( $component && true !== $component ) {
			return $components[ $component ];
		} else {
			return $components;
		}

	}



	// Just leaving this here for when you need to know how to deal with the JS
	// meta_items = $( '.module-holder [name^="meta_"]').serializeArray()
	// CoursePress.utility.fix_checkboxes( meta_items, '.module-holder', "0" )

}