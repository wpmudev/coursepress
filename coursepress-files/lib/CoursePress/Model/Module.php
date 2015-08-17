<?php

class CoursePress_Model_Module {

	private static $post_type = 'module';

	public static function get_format() {

		return array(
			'post_type' => self::$post_type,
			'post_args' => array(
				'labels'			 => array(
					'name'				 => __( 'Modules', 'cp' ),
					'singular_name'		 => __( 'Module', 'cp' ),
					'add_new'			 => __( 'Create New', 'cp' ),
					'add_new_item'		 => __( 'Create New Module', 'cp' ),
					'edit_item'			 => __( 'Edit Module', 'cp' ),
					'edit'				 => __( 'Edit', 'cp' ),
					'new_item'			 => __( 'New Module', 'cp' ),
					'view_item'			 => __( 'View Module', 'cp' ),
					'search_items'		 => __( 'Search Modules', 'cp' ),
					'not_found'			 => __( 'No Modules Found', 'cp' ),
					'not_found_in_trash' => __( 'No Modules found in Trash', 'cp' ),
					'view'				 => __( 'View Module', 'cp' )
				),
				'public'			 => false,
				'show_ui'			 => true,
				'publicly_queryable' => false,
				'capability_type'	 => 'module',
				'map_meta_cap'		 => true,
				'query_var'			 => true
			)
		);

	}

	public static function get_post_type_name( $with_prefix = false ) {
		if ( ! $with_prefix ) {
			return self::$post_type;
		} else {
			$prefix = defined( 'COURSEPRESS_CPT_PREFIX' ) ? COURSEPRESS_CPT_PREFIX : '';
			$prefix = empty( $prefix ) ? '' : sanitize_text_field( $prefix ) . '_';

			return $prefix . self::$post_type;
		}
	}

	public static function get_time_estimation( $module_id, $default = '1:00', $formatted = false ) {

		$estimation = get_post_meta( $module_id, 'time_estimation', true );
		$estimation = empty( $estimation ) ? $default : $estimation;

		if( ! $formatted ) {
			return empty( $estimation ) ? $default : $estimation;
		} else {
			$parts = explode( ':', $estimation );
			$seconds = (int) array_pop( $parts );
			$minutes = (int) array_pop( $parts );
			if( ! empty( $parts ) ) {
				$hours = (int) array_pop( $parts );
			} else {
				$hours = 0;
			}

			return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds );
		}


	}

	public static function legacy_map() {
		return array(
			'audio_module'          => 'audio',
			'chat_module'           => 'chat',
			'checkbox_input_module' => 'input-checkbox',
			'file_module'           => 'download',
			'file_input_module'     => 'input-upload',
			'image_module'          => 'image',
			'page_break_module'     => 'legacy',
			'radio_input_module'    => 'input-radio',
			'page_break_module'     => 'section',
			'section_break_module'  => 'section',
			'text_module'           => 'text',
			'text_input_module'     => 'input-text',
			'textarea_input_module' => 'input-textarea',
			'video_module'          => 'video'
		);
	}

	public static function fix_legacy_meta( $module_id, $meta ) {

		$module_type = $meta['module_type'][0];
		$legacy = self::legacy_map();

		// Get correct new type
		if( 'text_input_module' == $module_type ) {
			if( isset( $meta['checked_length'] ) && 'multi' == $meta['checked_length'] ) {
				$module_type = $legacy[ 'textarea_input_module' ];
			} else {
				$module_type = $legacy[ $module_type ];
			}
		} else {
			$module_type = $legacy[ $module_type ];
		}

		// Fix legacy meta
		if( isset( $meta['checked_answer'] ) ) {
			$value = $meta['checked_answer'][0];
			update_post_meta( $module_id, 'answers_selected', $value );
			delete_post_meta( $module_id, 'checked_answer' );
		}
		if( isset( $meta['checked_answers'] ) ) {
			$value = get_post_meta( $module_id, 'checked_answers', true );
			update_post_meta( $module_id, 'answers_selected', $value );
			delete_post_meta( $module_id, 'checked_answers' );
		}

		if( isset( $meta['time_estimation'] ) ) {
			$value = $meta['time_estimation'][0];
			update_post_meta( $module_id, 'duration', $value );
			delete_post_meta( $module_id, 'time_estimation' );
		}

		if( isset( $meta['show_title_on_front'] ) ) {
			$value = CoursePress_Helper_Utility::fix_bool( $meta['show_title_on_front'][0] );
			update_post_meta( $module_id, 'show_title', $value );
			delete_post_meta( $module_id, 'show_title_on_front' );
		}

		if( isset( $meta['mandatory_answer'] ) ) {
			$value = CoursePress_Helper_Utility::fix_bool( $meta['mandatory_answer'][0] );
			update_post_meta( $module_id, 'mandatory', $value );
			delete_post_meta( $module_id, 'mandatory_answer' );
		}

		if( isset( $meta['gradable_answer'] ) ) {
			$value = CoursePress_Helper_Utility::fix_bool( $meta['gradable_answer'][0] );
			update_post_meta( $module_id, 'assessable', $value );
			delete_post_meta( $module_id, 'gradable_answer' );
		}

		if( isset( $meta['minimum_grade_required'] ) ) {
			$value = floatval( $meta['minimum_grade_required'][0] );
			update_post_meta( $module_id, 'minimum_grade', $value );
			delete_post_meta( $module_id, 'minimum_grade_required' );
		}

		if( isset( $meta['limit_attempts_value'] ) ) {
			$value = (int) $meta['limit_attempts_value'][0];
			update_post_meta( $module_id, 'retry_attempts', $value );
			delete_post_meta( $module_id, 'limit_attempts_value' );
		}

		if( isset( $meta['limit_attempts'] ) ) {
			$value = CoursePress_Helper_Utility::fix_bool( $meta['limit_attempts'][0] );
			$value = ! $value; // inverse
			update_post_meta( $module_id, 'allow_retries', $value );
			delete_post_meta( $module_id, 'limit_attempts' );
		}

		// Update Type
		update_post_meta( $module_id, 'module_type', $module_type );

	}

	public static function module_attributes( $module ) {

		if( is_object( $module ) ) {
			$module_id = $module->ID;
		} else {
			$module_id = (int) $module;
		}

		$meta = get_post_meta( $module_id );

		$legacy = self::legacy_map();
		$module_type = $meta['module_type'][0];

		if ( array_key_exists( $module_type, $legacy ) ) {
			self::fix_legacy_meta( $module_id, $meta );
			$meta = get_post_meta( $module_id );
			$module_type = $meta['module_type'][0];
		}

		$input = preg_match( '/^input-/', $module_type );

		$attributes = array(
			'module_type' => $module_type,
			'mode' => $input ? 'input' : 'output',
		);

		if( 'section' != $module_type ) {
			$attributes = array_merge( $attributes, array(
				'duration'       => $meta['duration'][0],
				'show_title'     => CoursePress_Helper_Utility::fix_bool( $meta['show_title'][0] ),
				'allow_retries'  => isset( $meta['allow_retries'] ) ? CoursePress_Helper_Utility::fix_bool( $meta['allow_retries'][0] ) : true,
				'retry_attempts' => isset( $meta['retry_attempts'] ) ? (int) $meta['retry_attempts'][0] : 0,
				'minimum_grade'  => isset( $meta['minimum_grade'][0] ) ? floatval( $meta['minimum_grade'][0] ) : floatval( 100 ),
				'assessable'     => isset( $meta['assessable'] ) ? CoursePress_Helper_Utility::fix_bool( $meta['assessable'][0] ) : false,
				'mandatory'      => isset( $meta['mandatory'] ) ? CoursePress_Helper_Utility::fix_bool( $meta['mandatory'][0] ) : false,
			) );
		}

		foreach( $meta as $key => $value ) {
			if( ! array_key_exists( $key, $attributes ) ) {
				$attributes[ $key ] = maybe_unserialize( $value[0] );
			}
		}

		return $attributes;

	}

}