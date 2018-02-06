<?php

class CoursePress_Data_Module {

	/**
	 * List of ids of andatory modules.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $unit_id Unit id.
	 *
	 * @return array List of mandatory modules.
	 */

	public static function get_mandatory_modules( $unit_id ) {

		if ( ! empty( self::$mandatory_modules[ $unit_id ] ) ) {
			return self::$mandatory_modules[ $unit_id ];
		}

		$args = self::get_args_mandatory_modules( $unit_id );
		$the_query = new WP_Query( $args );
		$mandatory_modules = array();
		if ( $the_query->have_posts() ) {
			foreach ( $the_query->posts as $module_id ) {
				$mandatory_modules[ $module_id ] = get_post_meta( $module_id, 'module_type', true );
			}
		}

		// Store mandatory modules
		self::$mandatory_modules[ $unit_id ] = $mandatory_modules;

		return $mandatory_modules;
	}

	/**
	 * Check is module done by student?
	 *
	 * @since 2.0.0
	 *
	 * @param integer $module_id Modue ID to check
	 * @param integer $student_id student to check. Default empty.
	 *
	 * @return boolean is module done?
	 */
	public static function is_module_done_by_student( $module_id, $student_id ) {

		if ( ! $student_id ) {
			$student_id = get_current_user_id();
		}

		$unit_id = wp_get_post_parent_id( $module_id );
		$mandatory_modules = self::get_mandatory_modules( $unit_id );

		if ( isset( $mandatory_modules[ $module_id ] ) ) {
			switch ( $mandatory_modules[ $module_id ] ) {
				case 'discussion':
					$args = array(
						'post_id' => $module_id,
						'user_id' => $student_id,
						'order' => 'ASC',
						'number' => 1, // We only need one to verify if current user posted a comment.
						'fields' => 'ids',
					);
					$comments = get_comments( $args );

					return count( $comments ) > 0;
				break;

				default:
					$course_id = wp_get_post_parent_id( $unit_id );
					$student = coursepress_get_user( $student_id );
					$response = $student->get_response( $course_id, $unit_id, $module_id );

					$is_done = false;
					$last_answer = is_array( $response ) ? array_pop( $response ) : false;

					if ( ! empty( $last_answer ) ) {
						$is_done = true;
					}

					return $is_done;

			}
		}

		return true;
	}

	/**
	 * Get the attributes of module.
	 *
	 * @param int|object $module
	 * @param bool $meta
	 *
	 * @return array|bool
	 */
	public static function attributes( $module, $meta = false ) {

		if ( is_object( $module ) ) {
			$module_id = $module->ID;
		} else {
			$module_id = (int) $module;
		}

		$meta = empty( $meta ) ? get_post_meta( $module_id ) : $meta;

		$legacy = self::legacy_map();
		$module_type = isset( $meta['module_type'] ) ? $meta['module_type'][0] : false;

		if ( false === $module_type ) {
			return false;
		}

		if ( array_key_exists( $module_type, $legacy ) && empty( $meta['legacy_updated'] ) ) {
			$meta = self::fix_legacy_meta( $module_id, $meta );
			//$meta = get_post_meta( $module_id );
			$module_type = $meta['module_type'][0];
		}

		$input = preg_match( '/^input-/', $module_type );

		$attributes = array(
			'module_type' => $module_type,
			'mode' => $input ? 'input' : 'output',
		);

		if ( 'section' != $module_type ) {
			$attributes = array_merge( $attributes, array(
				'duration' => isset( $meta['duration'] ) ? $meta['duration'][0] : '0:00',
				'show_title' => cp_is_true( $meta['show_title'][0] ),
				'allow_retries' => isset( $meta['allow_retries'] ) ? cp_is_true( $meta['allow_retries'][0] ) : true,
				'retry_attempts' => isset( $meta['retry_attempts'] ) ? (int) $meta['retry_attempts'][0] : 0,
				'minimum_grade' => isset( $meta['minimum_grade'][0] ) ? floatval( $meta['minimum_grade'][0] ) : floatval( 100 ),
				'assessable' => isset( $meta['assessable'] ) ? cp_is_true( $meta['assessable'][0] ) : false,
				'mandatory' => isset( $meta['mandatory'] ) ? cp_is_true( $meta['mandatory'][0] ) : false,
			) );
		}

		foreach ( $meta as $key => $value ) {
			if ( ! array_key_exists( $key, $attributes ) ) {
				$attributes[ $key ] = maybe_unserialize( $value[0] );
			}
		}

		return $attributes;
	}
}
