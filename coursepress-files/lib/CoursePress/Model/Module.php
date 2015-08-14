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

	public static function module_attributes( $module ) {

		if( ! is_object( $module ) ) {
			$module = get_post( $module );
		}

		$meta = get_post_meta( $module->ID );

		$legacy = self::legacy_map();
		$module_type = $meta['module_type'][0];

		if ( array_key_exists( $module_type, $legacy ) ) {

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
			if( isset( $meta['limit_attempts'] ) ) {

			}



			//switch( $module_type ) {
			//	case 'input-textarea':
			//		break;
			//}







		}


		$input = preg_match( '/^input-/', $module_type );

		$attributes = array(
			'module_type' => $module_type,
			'mode' => $input ? 'input' : 'output',
			'duration' => $meta['duration'][0],
			'show_title' => CoursePress_Helper_Utility::fix_bool( $meta['show_title'][0] ),
		);

		if( $input ) {
			$attributes = array_merge( $attributes, array(
				'allow_retries' => CoursePress_Helper_Utility::fix_bool( $meta['allow_retries'][0] ),
				'retry_attempts' => (int) $meta['retry_attempts'][0],
				'minimum_grade' => floatval( $meta['minimum_grade'][0] ),
				'assessable' => CoursePress_Helper_Utility::fix_bool( $meta['assessable'][0] ),
				'mandatory' => CoursePress_Helper_Utility::fix_bool( $meta['mandatory'][0] ),
			) );
		}


		return $attributes;

	}

}